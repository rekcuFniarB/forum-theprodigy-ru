<?php
namespace Prodigy\Respond;

class Karma extends Respond
{
    public function view($request, $response, $service, $app)
    {
        if ($app->user->guest)
            return $this->error($app->locale->txt[1]);
        
        $PARAMS = $request->paramsNamed();
        $GET = $request->paramsGet();
        
        $db_prefix = $app->db->prefix;
        
        $dbst = $app->db->prepare("SELECT memberName, realName, memberGroup FROM {$db_prefix}members WHERE memberName = ?");
        $dbst->execute(array($PARAMS->user));
        $member = $dbst->fetch();
        $dbst = null;
        
        if (empty($member))
            return $this->error('No such user.');
        
        $data = array(
            'member' => $member['memberName'],
            'realName' => $member['realName']
        );
        
        // Get Top 10 applauds
        $dbst = $app->db->prepare("SELECT user1_name, user1, COUNT(*) AS k FROM karmawatch WHERE action = 'поощрил' AND user2 = ? GROUP BY user1 ORDER BY k DESC LIMIT 10");
        $dbst->execute(array($member['memberName']));
        $data['topApplauds'] = $dbst->fetchAll();
        $dbst = null;
        
        // Get Top 10 smites
        $dbst = $app->db->prepare("SELECT user1, user1_name, COUNT(*) AS k FROM `karmawatch` WHERE action = 'покарал' AND user2 = ? GROUP BY user1 ORDER BY k DESC LIMIT 10");
        $dbst->execute(array($member['memberName']));
        $data['topSmites'] = $dbst->fetchAll();
        $dbst = null;
        
        // Get Top 10 applauds of week
        $dbst = $app->db->prepare("SELECT user1, user1_name, COUNT(*) AS k FROM `karmawatch` WHERE UNIX_TIMESTAMP( time ) > ( UNIX_TIMESTAMP( ) - ( 7 *24 *60 *60 ) ) AND action = 'поощрил' AND user2 = ? GROUP BY user1 ORDER BY k DESC LIMIT 10");
        $dbst->execute(array($member['memberName']));
        $data['weekApplauds'] = $dbst->fetchAll();
        $dbst = null;
        
        // Get Top 10 smites of week
        $dbst = $app->db->prepare("SELECT user1, user1_name, COUNT(*) AS k FROM `karmawatch`
            WHERE UNIX_TIMESTAMP( time ) > ( UNIX_TIMESTAMP( ) - ( 7 *24 *60 *60 ) )
            AND action = 'покарал' AND user2 = ? GROUP BY user1 ORDER BY k DESC LIMIT 10");
        $dbst->execute(array($member['memberName']));
        $data['weekSmites'] = $dbst->fetchAll();
        $dbst = null;
        
        $data['offset'] = (int) $GET->offset;
        $data['num'] = (int) $GET->num;
        if ($data['num'] > 100)
            $data['num'] = 100;
        if ($data['num'] < 1)
            $data['num'] = 10;
        
        $data['actions'] = array();
        $dbst = $app->db->prepare("SELECT ID_MSG, action, user1, user1_name, user2, ID_MEMBER, time FROM karmawatch JOIN `members` ON (user1 = memberName) WHERE user2 = ? ORDER BY time DESC LIMIT ?, ?");
        $dbst->execute(array($member['memberName'], $data['offset'], $data['num']));
        while ($karma = $dbst->fetch())
        {
            $karma['dupes'] = 1;
            $karma_key = crc32("{$karma['ID_MSG']}_-_{$karma['user1']}_-_{$karma['action']}");
            
            // store info or increase duplicate value
            if (isset($data['actions'][$karma['ID_MSG']]['karmas'][$karma_key]))
                // duplicate action
                $data['actions'][$karma['ID_MSG']]['karmas'][$karma_key]['dupes'] ++;
            else
            {
                $karma['dellnk'] = http_build_query(array('t'=>$karma['time'],'u1'=>$karma['user1']), '', '&amp;');
                preg_match("/(\d\d\d\d)(\d\d)(\d\d)(\d\d)(\d\d)(\d\d)/", $karma['time'], $m);
                $karma['time'] = $m;
                $karma['action'] = $karma['action'] == "покарал" ? "покарал" : "поощрил";
                $data['actions'][$karma['ID_MSG']]['karmas'][$karma_key] = $karma;
            }
            
            // get message body
            if (!isset($data['actions'][$karma['ID_MSG']]['body']))
            {
                $dbst2 = $app->db->query("SELECT body, smiliesEnabled FROM `messages` WHERE ID_MSG = {$karma['ID_MSG']}");
                $data['actions'][$karma['ID_MSG']]['body'] = $dbst2->fetchColumn();
                $dbst2 = null;
            }
            
            if ($app->user->isAdmin())
                $data['delbtn'] = true;
        }
        $dbst = null;
        
        
        $this->render('templates/profile/karma.template.php', $data);
    } // view()
    
    public function remove($request, $response, $service, $app)
    {
        if (!$app->user->isAdmin())
            // For admins only
            return $this->error($app->locale->txt[1]);
        
        $GET = $request->paramsGet();
        $PARAMS = $request->paramsNamed();
        
        if (empty($GET->t) || empty($GET->u1))
            return $this->error('Bad request.', 400);
        
        $db_prefix = $app->db->prefix;
        
        // Get action direction
        $dbst = $app->db->prepare("SELECT action from {$db_prefix}karmawatch
            WHERE ID_MSG = ? AND time = ? AND user1 = ? AND user2 = ? LIMIT 1");
        $dbst->execute(array($PARAMS->msgid, $GET->t, $GET->u1, $PARAMS->user));
        $action = $dbst->fetchColumn();
        $dbst = null; // closing this statement
        
        if (empty($action))
            return $this->error('Karma action not found.');
        
        // Setting board to none. We have to do it before using function loadDisplay()
        $app->board->load(-1);
        
        // Get info of actor
        $actor = $app->user->loadDisplay($GET->u1);
        if (!$actor['found'])
            return $this->error("Actor {$GET->u1} not found.");
        
        // Get info of member
        $member = $app->user->loadDisplay($PARAMS->user);
        if (!$member['found'])
            return $this->error("Member {$PARAMS->usere} not found.");
        
        $dbst = $app->db->prepare("DELETE FROM {$db_prefix}karmawatch 
            WHERE ID_MSG = ? AND time = ? AND user1 = ? AND user2 = ? and action = ?");
        $dbst->execute(array($PARAMS->msgid, $GET->t, $GET->u1, $PARAMS->user, $action));
        $num = $dbst->rowCount();
        $dbst = null;
        
        if ($num > 0)
        {
            if ($action == 'поощрил')
            {
                $app->db->prepare("UPDATE messages SET karmaGood = karmaGood - ?, karmaGoodExecutors = REPLACE(karmaGoodExecutors, ?,'') WHERE ID_MSG = ?")->
                    execute(array($num, ",{$actor['ID_MEMBER']}", $PARAMS->msgid));
                $app->db->prepare("UPDATE members SET karmaGood = karmaGood-? WHERE ID_MEMBER = ?")->
                    execute(array($num, $member['ID_MEMBER']));
            } // if karma+
            elseif ($action == 'покарал')
            {
                $app->db->prepare("UPDATE messages SET karmaBad = karmaBad - ?, karmaBadExecutors = REPLACE(karmaBadExecutors, ?,'') WHERE ID_MSG = ?")->
                    execute(array($num, ",{$actor['ID_MEMBER']}", $PARAMS->msgid));
                $app->db->prepare("UPDATE members SET karmaBad = karmaBad-? WHERE ID_MEMBER = ?")->
                    execute(array($num, $member['ID_MEMBER']));
            }
        }
        
        return $this->message('Удаление заценок', "Удалено $num заценок пользователя {$actor['realName']} в отношении {$member['realName']}.");
    } // remove()
    
    // modify carma request handler
    public function action($request, $response, $service, $app)
    {
        if ($app->user->guest)
            return $this->error($app->locale->txt[1]);
        
        /* if the mod is disabled, error */
            if (!$app->conf->karmaMode)
                return $this->error($app->locale->txt['yse63']);
        
        // FIXME this check was looking wrong in original code, I've changed it a bit...
        /* if we've defined any member groups to restrict it to, and if you're
           not part of one of said membergroups, kick you're ass to the curb */
        if ($app->conf->karmaMemberGroups[0] && sizeof($app->conf->karmaMemberGroups) >= 1 && !in_array($app->user->group, $app->conf->karmaMemberGroups) && !$app->user->isStaff())
            return $this->error($app->locale->txt[1]);
        
        /* if you don't have enough posts, tough luck */
        if ($app->user->posts < $app->conf->karmaMinPosts)
            return $this->error("{$app->locale->yse60} {$app->conf->karmaMinPosts}.");
        
        $PARAMS = $request->paramsNamed();
        
        $db_prefix = $app->db->prefix;
        
        $dbst = $app->db->prepare("SELECT ID_MEMBER FROM {$db_prefix}messages WHERE ID_MSG = ? LIMIT 1");
        $dbst->execute(array($PARAMS->msgid));
        $uid = $dbst->fetchColumn();
        if (empty($uid))
            return $this->error("Поощряемого сообщения не существует!");
        
        /* and you can't modify you're own punk! */
        if ($uid == $app->user->id)
            return $this->error($app->locale->yse61);
        
        /* List of members disallowed to do karma actions */
        if (in_array($app->user->id, $app->conf->get('karmaActionDisallowed', array())))
            return $this->error("Тебе запрещено поощрять/карать других форумчан из-за манипуляции их рейтингом!");
        
        $notPermitedPerUser = $app->conf->get('karmaNotPermittedPerUser', array());
        if (array_key_exists($app->user->id, $notPermitedPerUser) && in_array($uid, $notPermitedPerUser[$app->user->id]))
            return $this->error("Тебе запрещено поощрять/карать данного форумчанина из-за очевидно
        неравнодушного к нему отношения! Манипуляции рейтингом запрещены по правилам!
        Если ты считаешь, что система сработала несправедливо, то обратись к
        администратору за разъяснением!");
        
        $smite_not_allowed = $app->conf->get('smite_not_allowed', array());
        if ($PARAMS->action == 'smite' && (in_array($app->user->name, $smite_not_allowed) || in_array($app->user->id, $smite_not_allowed)))
            return $this->error("Тебе запрещено карать.");
        
        if ($PARAMS->action == 'applaud')
            $dir = '+';
        elseif ($PARAMS->action == 'smite')
            $dir = '-';
        else
            return $this->error('Wrong action.');
        
        $nowtime = time();
        $dbst = $app->db->prepare("SELECT memberName FROM {$db_prefix}members WHERE ID_MEMBER = ? LIMIT 1");
        $dbst->execute(array($uid));
        $member1 = $dbst->fetchColumn();
        $dbst = null;
        $dbst = $app->db->prepare("SELECT memberName FROM {$db_prefix}members WHERE ID_MEMBER = ? LIMIT 1");
        $dbst->execute(array($app->user->id));
        $member2 = $dbst->fetchColumn();
        $dbst = null;
        $minimumTime = gmdate("YmdHis", $nowtime - $app->conf->karmaWaitTime * 3600);
        $t = gmdate("YmdHis", $nowtime);
        $dbst = $app->db->prepare("SELECT user2_name, time, action FROM {$db_prefix}karmawatch WHERE user1 = ? AND user2 = ? AND time > ? LIMIT 1");
        $dbst->execute(array($member2, $member1, $minimumTime));
        $logAction = $dbst->fetch();
        $dbst = null;
        if (!empty($logAction) and !$app->user->isAdmin())
        {
            if ($dir == "-" && $logAction['action'] == 'покарал')
                return $this->error("Ты уже покарал недавно {$logAction['user2_name']}. Будь милосердней, будь выше всяких ссор! Не карай так часто!");
            else
                return $this->error("Ты уже и так заценил недавно одно из сообщений этого форумчанина. Подожди теперь! Перерыв должен составлять {$app->conf->karmaWaitTime} ч");
        }
        
        $change = 0;

        $dbst = $app->db->prepare("SELECT karmaGoodExecutors, karmaBadExecutors FROM {$db_prefix}messages WHERE ID_MSG = ? LIMIT 1");
        $dbst->execute(array($PARAMS->msgid));
        $karmaInfo = $dbst->fetch();
        $dbst = null;
        $goodExec = explode(",", $karmaInfo['karmaGoodExecutors']);
        $badExec = explode(",", $karmaInfo['karmaBadExecutors']);
        
        if ((!in_array($app->user->id, $goodExec) && !in_array($app->user->id, $badExec)) or $app->user->isAdmin()) // there are no entries for you and that user logged
            $change = 1;
        else
        {
            if ((in_array($app->user->id, $goodExec) && $dir == "+") || (in_array($app->user->id, $badExec) && $dir == "-"))    // if you're trying to repeat
                return $this->error("Ты уже заценил это сообщение!");
            else
                $change = 2;
        }
        
        //-------------------------------Updated by dig7er on July 22, 2004. Keeping an eye on everybody.
        $execKarmaWatch = TRUE;
        /*$dbst = $app->db->prepare("SELECT * FROM {$db_prefix}log_karma WHERE ID_EXECUTOR = ?");
        $dbst->execute(array($app->user->id));
        while ($r = $dbst->fetch()) {
            if ($uid == $r['ID_TARGET'])
                if ($nowtime - $r['logTime'] < ($app->conf->karmaWaitTime * 3600))
                    $execKarmaWatch = FALSE;
        }*/
        
        if ($execKarmaWatch)
        {
            $dbst = $app->db->prepare("SELECT * FROM {$db_prefix}members WHERE ID_MEMBER = ? LIMIT 1");
            $dbst->execute(array($uid));
            $executor = $dbst->fetch();
            $dbst = null;
            $dbst = $app->db->prepare("SELECT * FROM {$db_prefix}members WHERE ID_MEMBER = ? LIMIT 1");
            $dbst->execute(array($app->user->id));
            $member = $dbst->fetch();
            $dbst = null;
            $action = (($dir == "+") ? "поощрил" : "покарал");
            $realName = $member['realName'];
            $memIP = $member['memberIP'];
            if ($executor['memberIP'] == $member['memberIP'])
                return $this->error("Накрутчик! Фууу! Всё админам расскажу!");
            $realName = str_replace("\\", "\\\\", $realName);
            
            $app->db->prepare("INSERT INTO {$db_prefix}karmawatch (ID_MSG, user1, user1_name, user1_ip, action, user2, user2_name, user2_ip, time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")->
                execute(array($PARAMS->msgid, $member['memberName'], $realName, $memIP, $action, $executor['memberName'], $executor['realName'], $executor['memberIP'], $t));
            
        }
        //-------------------------------End of dig7ers part.
        
        if ($change != 0)
        {
            $field = (($dir == '+') ? 'karmaGood' : 'karmaBad');
            // Karma bug fix
            if ($change == 2)
            {
                $field2 = (($dir == '+') ? 'karmaBad' : 'karmaGood');
                $app->db->prepare("UPDATE {$db_prefix}members SET $field=$field+1, $field2=$field2-1 WHERE ID_MEMBER=?")->
                    execute(array($uid));
                $app->db->prepare("UPDATE {$db_prefix}messages SET $field=$field+1, $field2=$field2-1 WHERE ID_MSG=?")->
                    execute(array($PARAMS->msgid));
                $dbst = $db->prepare("SELECT karmaGoodExecutors, karmaBadExecutors FROM `messages` WHERE ID_MSG = ? LIMIT 1");
                $dbst->execute(array($PARAMS->msgid));
                $karmaMsg = $dbst->fetch();
                $dbst= null;
                $goodExecutors = $karmaMsg['karmaGoodExecutors'];
                $badExecutors = $karmaMsg['karmaBadExecutors'];
                if ($dir=="+")
                {
                    $goodExecutors .= ",{$app->user->id}";
                    $badExecutors = str_replace(",{$app->user->id}","",$badExecutors);
                }
                elseif ($dir=="-")
                {
                    $badExecutors .= ",{$app->user->id}";
                    $goodExecutors = str_replace(",{$app->user->id}","",$badExecutors);
                }
                $app->db->prepare("UPDATE {$db_prefix}messages SET karmaGoodExecutors = ?, karmaBadExecutors = ? WHERE ID_MSG = ? LIMIT 1")->
                    execute(array($goodExecutors, $badExecutors, $PARAMS->msgid));
            } // if change == 2
            else
            {
                $app->db->prepare("UPDATE {$db_prefix}members SET $field=$field+$change WHERE ID_MEMBER=?")->
                    execute(array($uid));
                $app->db->prepare("UPDATE {$db_prefix}messages SET $field=$field+$change WHERE ID_MSG=?")->
                    execute(array($PARAMS->msgid));
                if ($dir=="+")
                    $app->db->prepare("UPDATE {$db_prefix}messages SET karmaGoodExecutors=CONCAT(karmaGoodExecutors, ?) WHERE ID_MSG=?")->
                        execute(array(",{$app->user->id}", $PARAMS->msgid));
                elseif ($dir=="-")
                    $app->db->prepare("UPDATE {$db_prefix}messages SET karmaBadExecutors=CONCAT(karmaBadExecutors, ?) WHERE ID_MSG=?")->
                        execute(array(",{$app->user->id}", $PARAMS->msgid));
            }
            
            $app->db->prepare("UPDATE {$db_prefix}members SET karmaBad = karmaGood WHERE ID_MEMBER = ? AND karmaBad > karmaGood")->
                execute(array($uid));
        } // if change != 0
        
        if ($service->ajax)
            return $this->ajax_response($PARAMS->action, 'text');
        else
            return $this->back();
    } // action()
}
