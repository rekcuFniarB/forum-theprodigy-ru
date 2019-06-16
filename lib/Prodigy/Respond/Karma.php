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
}
