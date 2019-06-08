<?php
namespace Prodigy\Respond;

class InstantMessages extends Respond
{
    public function __construct($router)
    {
        parent::__construct($router);
    }

    public function getCount()
    {
        $ID_MEMBER = $this->app->user->id;
        $db_prefix = $this->app->db->prefix;
        $cgi = SITE_ROOT . '/';
        
        $request = $this->app->db->query("SELECT COUNT(*), readBy FROM {$db_prefix}instant_messages 
            WHERE (ID_MEMBER_TO={$ID_MEMBER} AND deletedBy != 1) GROUP BY readBy");
        $rows = $request->fetchAll(\PDO::FETCH_NUM);
        if (!$rows)
            $munred = $mnum = 0;
        elseif (count($rows) == 1)
        {
            list($mnum, $readBy) = $rows[0];
            if ($readBy == 0)
                $munred = $mnum;
            else
                $munred = 0;
        }
        else
        {
            list($munred, $dummy) = $rows[0];
            list($mnum, $dummy) = $rows[1];
            $mnum += $munred;
        }
        
//         if ($munred == 1)
//             $isare = $this->app->locale->txt['newmessages0'];
//         else
//             $isare = $this->app->locale->txt['newmessages1'];
//         
//         if ($munred == 0)
//             $yyim = "{$this->app->locale->txt[152]} <a href=\"$cgi;action=im\">$mnum {$this->app->locale->txt[153]}</a>";
//         elseif ($mnum == '1')
//             $yyim = "{$this->app->locale->txt[152]} <a href=\"$cgi;action=im\">$mnum {$this->app->locale->txt[471]}</a> $this->app->locale->txt[newmessages2]$munred".")";
//         else
//             $yyim = "{$this->app->locale->txt[152]} <a href=\"$cgi;action=im\">$mnum {$this->app->locale->txt[153]}</a> {$this->app->locale->txt['newmessages2']}$munred)";
// 
//         if ($this->service->ajax)
//         {
//             $request = $this->app->db->query("SELECT imsound FROM {$db_prefix}members WHERE 
//                 ID_MEMBER = {$ID_MEMBER} AND imsound NOT LIKE ''", false);
//             if ($memsettings = $request->fetch_assoc())
//                 $yyim .= '<script type="text/javascript">Forum.Utils.playMP3OnBackground(\''.$memsettings['imsound'].'\');</script>';
//         }
//         
//         return  array($yyim, $munred);
        return array($mnum, $munred);

    } // getNumUnread()
    
    /**
     * 
     */
    public function NotifyUsers($threadid, $subject)
    {
        $db_prefix = $this->app->db->prefix;
        
        $dbrq = $this->app->db->prepare("
            SELECT notifies
            FROM {$db_prefix}topics
            WHERE (ID_TOPIC = $threadid
                AND notifies != '')
            LIMIT 1");
        $dbrq->execute(array($threadid));
        $notifies = $dbrq->fetchColumn();
        $dbrq = null;
        if ($notifies)
        {
            $notifies = explode(',', $notifies);
            $placeholders = $this->app->db->build_placeholders($notifies);
            $members = $this->app->db->prepare("
                SELECT emailAddress, notifyOnce, ID_MEMBER, lngfile
                FROM {$db_prefix}members
                WHERE ID_MEMBER IN ($placeholders)
                AND emailAddress != ''
                AND ID_MEMBER != ?
                ORDER BY lngfile");
            $members->execute(array_merge($notifies, array($this->app->user->id)));
            
            $curlanguage = $this->app->locale->lngfile;

            while ($rowmember = $members->fetch())
            {
                if ($this->app->conf->userLanguage == 1)
                {
                    $desiredlanguage = (($rowmember['lngfile'] == Null) || ($rowmember['lngfile'] == '') ? $this->app->conf->language : $rowmember['lngfile']);
                    
                    if ($desiredlanguage != $curlanguage)
                    {
                        include(PROJECT_ROOT . "/lib/Prodigy/Localization/$desiredlanguage");
                        $curlanguage = $desiredlanguage;
                    }
                }
                else $txt = $this->app->locale->txt;

                $send_subject = $txt[127] . ': ' . $this->app->subs->CensorTxt($subject);
                
                if ($rowmember['notifyOnce'] == 1)
                {
                    $dbrq = $this->app->db->prepare("
                        SELECT notificationSent
                        FROM {$db_prefix}log_topics
                        WHERE ID_MEMBER={$rowmember['ID_MEMBER']}
                        AND ID_TOPIC = $threadid");
                    $dbrq->execute(array($rowmember['ID_MEMBER'], $threadid));
                    $notificationSent = $dbrq->fetchColumn();
                    $dbrq = null;
                    if ($notificationSent === false)
                    {
                        $notificationSent = 0;
                        $rows = false;
                    }
                    else
                        $rows = true;
                    
                    if (!$notificationSent)
                    {
                        $send_body = "$txt[128] $txt[129]
                        " . SITE_URL ."/b{$service->board}/t$threadid/new/\n\n{$txt['notifyXOnce2']}\n\n$txt[130]";
                        $this->app->im->sendmail($rowmember['emailAddress'], $send_subject, $send_body);
                        
                        if (!$rows)
                            $this->app->db->prepare("
                                INSERT INTO {$db_prefix}log_topics
                                (logTime, ID_MEMBER, ID_TOPIC, notificationSent)
                                VALUES (0, ?, ?, 1)")->
                                    execute(array($rowmember['ID_MEMBER'], $threadid));
                        else
                            $this->app->db->prepare("
                                UPDATE {$db_prefix}log_topics
                                SET notificationSent = 1
                                WHERE (ID_MEMBER = ?
                                AND ID_TOPIC=?)")->
                                    execute(array($rowmember['ID_MEMBER'], $threadid));
                    }
                }
                else
                {
                    $send_body = "$txt[128] $txt[129]  " . SITE_URL . "/b{$service->board}/t$threadid/new/\n\n$txt[130]";
                    $this->app->im->sendmail($rowmember['emailAddress'], $send_subject, $send_body);
                }
            } // while fetch()
            $members = null;
        } // if num_rows
    } // NotifyUsers()
    
    public function Notify2 ($threadid)
    {
        if (empty($this->service->board))
            $this->app->errors->abort('', $txt[1], 400);
        
        if ($this->app->user->guest)
            $this->app->errors->abort('', $txt[138], 403);
        
        $db_prefix = $this->app->db->prefix;
        
        $dbrq = $this->app->db->prepare("
            SELECT notifies FROM {$db_prefix}topics
            WHERE (ID_TOPIC = ?)
            LIMIT 1");
        $dbrq->excute(array($threadid));
        $notification = $dbrq->fetchColumn();
        $dbrq = null;
        $notifies = explode(',', $notification);
        $notifications2 = array();
        
        foreach ($notifies as $note) 
            if ($note != $this->app->user->id && $note != '')
                $notifications2[] = $note;
        
        if (!in_array($this->app->user->id, $notifications2))
            $notifications2[] = $this->app->user->id;
        
        $notification = implode(",", $notifications2);
        $dbrq = $this->app->db->prepare("
            UPDATE {$db_prefix}topics
            SET notifies = ?
            WHERE ID_TOPIC = ?")->
                execute(array($notification, $threadid));
    } // Notify2()
    
    public function sendmail()
    {
        // TODO
    }
    
    public function send_notice()
    {
        // TODO
    }
    
    public function inbox($request, $response, $service, $app)
    {
        if ($app->user->guest)
            return $this->error($app->locale->txt[147]);
        
        $GET = $request->paramsGet();
        $PARAMS = $request->paramsNamed();
        
        $app->board->load(-1);
        
        $service->shownum = empty($GET->shownum) ? 30 : (int) $GET->shownum;
        $limit  = $service->shownum + 1;
        
        $bgcolors = array($app->conf->color['windowbg'], $app->conf->color['windowbg2']);
        $bgstyles = array('windowbg', 'windowbg2');
        $bgcolornum = sizeof($bgcolors);
        $bgstylenum = sizeof($bgstyles);
        
        $start = (int) $PARAMS->start;
        $q_start = $start > 0 ? "AND im.ID_IM <= $start" : '';
        
        $db_prefix = $app->db->prefix;
        
        $dbst = $app->db->prepare("
            SELECT im.fromName, im.subject, im.msgtime, im.body, im.ID_IM, IFNULL(mem.ID_MEMBER, 0) AS ID_MEMBER_FROM, IFNULL(mem.realName,im.fromName) AS fromDisplayName, IFNULL(lo.logTime, 0) AS isOnline
            FROM {$db_prefix}instant_messages AS im
            LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER=im.ID_MEMBER_FROM)
            LEFT JOIN {$db_prefix}log_online AS lo ON (lo.identity=mem.ID_MEMBER)
            WHERE ID_MEMBER_TO=? AND deletedBy != 1
            $q_start
            ORDER BY im.ID_IM DESC LIMIT $limit");
        $dbst->execute(array($app->user->id));
        $counter = 0;
        $messages = array();
        while ($msg = $dbst->fetch())
        {
            $counter++;
            $msg['windowbg'] = $bgcolors[($counter % $bgcolornum)];
            $msg['windowcss'] = $bgstyles[($counter % $bgstylenum)];
            
            if (empty($msg['subject']))
                $msg['subject'] = $app->locale->txt[24];
            
            if (strpos($msg['subject'], '[NOTICE]') === 0)
            {
                $msg['notice'] = true;
                $msg['subject'] = str_replace('[NOTICE]', '', $msg['subject']);
            }
            else
                $msg['notice'] = false;
            
            $msg['subject'] = $app->subs->censorTxt($msg['subject']);
            $msg['body'] = $app->subs->censorTxt($msg['body']);
            
            $msg['msgtime'] = $app->subs->timeformat($msg['msgtime']);
            
            $msg['author'] = $app->user->loadDisplay($msg['fromName']);
            
            $msg['show_email'] = false;
            
            if ($msg['author']['emailAddress'])
            {
                if (empty($msg['author']['hideEmail']) || $app->user->isStaff() || empty($app->conf->allow_hide_email))
                    $msg['show_email'] = true;
            }
            
            $messages[$counter] = $msg;
        } // while fetch()
        $dbst = null;
        
        if (empty($messages))
            $service->nomessages = true;
        
        $data = array(
            'title' => $app->locale->txt[143],
            'catname' => $app->locale->txt[144],
            'boardname' => $app->locale->txt[316],
            'windowbg' => $bgcolors[(++ $counter % $bgcolornum)],
            'switch_folder' => 'outbox/',
            'switch_folder_name' => $app->locale->txt[320],
            'messages' => $messages
        );
        
        //// BEGIN prepare pagination data
        if (count($messages) > $service->shownum)
        {
            // next page exists
            // we no more need additional msg
            $msg = array_pop($data['messages']);
            $data['page_next'] = $msg['ID_IM'];
        }
        
        if ($start > 0)
        {
            // find start of prev page
            $dbst = $app->db->prepare("SELECT im.ID_IM FROM (SELECT im.ID_IM
            FROM {$db_prefix}instant_messages AS im
            WHERE ID_MEMBER_TO=? AND deletedBy != 1 AND im.ID_IM > ?
            ORDER BY im.ID_IM LIMIT ?) as im ORDER BY im.ID_IM DESC");
            $dbst->execute(array($app->user->id,  reset($messages)['ID_IM'], $limit));
            $prevIDs = $dbst->fetchAll(\PDO::FETCH_COLUMN);
            $dbst = null;
            
            $cnt = count($prevIDs);
            if ($cnt > 0)
                // Show link to the beginning
                $data['page_start'] = true;
            if ($cnt > $service->shownum)
                // prev page is not a last page, show link to prev page
                $data['page_prev'] = $prevIDs[1];
        }

        // find start of end page
        $dbst = $app->db->prepare("SELECT im.ID_IM FROM (SELECT im.ID_IM
            FROM {$db_prefix}instant_messages AS im
            WHERE ID_MEMBER_TO=? AND deletedBy != 1
            ORDER BY im.ID_IM LIMIT ?) as im ORDER BY im.ID_IM DESC LIMIT 1");
        $dbst->execute(array($app->user->id, $service->shownum));
        $lastID = $dbst->fetchColumn();
        $dbst = null;
        
        $data['page_last'] = $lastID;
        
        if (empty($data['page_next']))
            // Next page is last, don't show next link.
            $data['page_last'] = null;
        elseif ($data['page_next'] == $data['page_last'])
            // Next page is last, no need for next page link.
            $data['page_next'] = null;
            
        $data['page_base_url'] = SITE_ROOT . "/im";
        //// END prepare pagination
        
        if ($app->conf->profilebutton && !$app->user->guest)
            $data['profilebutton'] = true;
        
        // Mark as read after response finished
        if (!empty($service->imcount) && $service->imcount > 0)
        {
            register_shutdown_function(function() use ($app, $service)
            {
                if(session_id()) session_write_close();
                $app->db->query("UPDATE {$db_prefix}instant_messages SET readBy=1 WHERE ID_MEMBER_TO=? AND readBy='0' LIMIT ?")->
                execute(array($app->user->id, $service->imcount));
            });
        }
        
        return $this->render('templates/im/inbox.template.php', $data);
    } // inbox()
    
    public function outbox($request, $response, $service, $app)
    {
        if ($app->user->guest)
            return $this->error($app->locale->txt[147]);
        
        $GET = $request->paramsGet();
        $PARAMS = $request->paramsNamed();
        
        $app->board->load(-1);
        
        $service->shownum = empty($GET->shownum) ? 30 : (int) $GET->shownum;
        $limit  = $service->shownum + 1;
        
        $bgcolors = array($app->conf->color['windowbg'], $app->conf->color['windowbg2']);
        $bgstyles = array('windowbg', 'windowbg2');
        $bgcolornum = sizeof($bgcolors);
        $bgstylenum = sizeof($bgstyles);
        
        $start = (int) $PARAMS->start;
        $q_start = $start > 0 ? "AND im.ID_IM <= $start" : '';
        
        $db_prefix = $app->db->prefix;
        
        $dbst = $app->db->prepare("
            SELECT im.toName, im.subject, im.msgtime, im.body, im.ID_IM, IFNULL(mem.ID_MEMBER, 0) AS ID_MEMBER_TO, IFNULL(mem.realName,im.toName) AS toDisplayName, IFNULL(lo.logTime, 0) AS isOnline
            FROM {$db_prefix}instant_messages AS im
            LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER=im.ID_MEMBER_TO)
            LEFT JOIN {$db_prefix}log_online AS lo ON (lo.identity=mem.ID_MEMBER)
            WHERE ID_MEMBER_FROM=? AND deletedBy != 0
            $q_start
            ORDER BY im.ID_IM DESC LIMIT $limit");
        $dbst->execute(array($app->user->id));
        $counter = 0;
        $messages = array();
        while ($msg = $dbst->fetch())
        {
            $counter++;
            $msg['windowbg'] = $bgcolors[($counter % $bgcolornum)];
            $msg['windowcss'] = $bgstyles[($counter % $bgstylenum)];
            
            if (empty($msg['subject']))
                $msg['subject'] = $app->locale->txt[24];
            
            if (strpos($msg['subject'], '[NOTICE]') === 0)
            {
                $msg['notice'] = true;
                $msg['subject'] = str_replace('[NOTICE]', '', $msg['subject']);
            }
            else
                $msg['notice'] = false;
            
            $msg['subject'] = $app->subs->censorTxt($msg['subject']);
            $msg['body'] = $app->subs->censorTxt($msg['body']);
            
            $msg['msgtime'] = $app->subs->timeformat($msg['msgtime']);
            
            $msg['author'] = $app->user->loadDisplay($msg['toName']);
            
            $msg['show_email'] = false;
            
            if ($msg['author']['emailAddress'])
            {
                if (empty($msg['author']['hideEmail']) || $app->user->isStaff() || empty($app->conf->allow_hide_email))
                    $msg['show_email'] = true;
            }
            
            $messages[$counter] = $msg;
        } // while fetch()
        $dbst = null;
        
        if (empty($messages))
            $service->nomessages = true;
        
        $data = array(
            'title' => "{$app->locale->txt[143]} ({$app->locale->txt[320]})",
            'catname' => $app->locale->txt[144],
            'boardname' => $app->locale->txt[320],
            'windowbg' => $bgcolors[(++ $counter % $bgcolornum)],
            'switch_folder' => '',
            'switch_folder_name' => $app->locale->txt[316],
            'messages' => $messages
        );
        
        //// BEGIN prepare pagination data
        if (count($messages) > $service->shownum)
        {
            // next page exists
            // we no more need additional msg
            $msg = array_pop($data['messages']);
            $data['page_next'] = $msg['ID_IM'];
        }
        
        if ($start > 0)
        {
            // find start of prev page
            $dbst = $app->db->prepare("SELECT im.ID_IM FROM (SELECT im.ID_IM
            FROM {$db_prefix}instant_messages AS im
            WHERE ID_MEMBER_FROM=? AND deletedBy != 0 AND im.ID_IM > ?
            ORDER BY im.ID_IM LIMIT ?) as im ORDER BY im.ID_IM DESC");
            $dbst->execute(array($app->user->id,  reset($messages)['ID_IM'], $limit));
            $prevIDs = $dbst->fetchAll(\PDO::FETCH_COLUMN);
            $dbst = null;
            
            $cnt = count($prevIDs);
            if ($cnt > 0)
                // Show link to the beginning
                $data['page_start'] = true;
            if ($cnt > $service->shownum)
                // prev page is not a last page, show link to prev page
                $data['page_prev'] = $prevIDs[1];
        }

        // find start of end page
        $dbst = $app->db->prepare("SELECT im.ID_IM FROM (SELECT im.ID_IM
            FROM {$db_prefix}instant_messages AS im
            WHERE ID_MEMBER_FROM=? AND deletedBy != 0
            ORDER BY im.ID_IM LIMIT ?) as im ORDER BY im.ID_IM DESC LIMIT 1");
        $dbst->execute(array($app->user->id, $service->shownum));
        $lastID = $dbst->fetchColumn();
        $dbst = null;
        
        $data['page_last'] = $lastID;
        
        if (empty($data['page_next']))
            // Next page is last, don't show next link.
            $data['page_last'] = null;
        elseif ($data['page_next'] == $data['page_last'])
            // Next page is last, no need for next page link.
            $data['page_next'] = null;
            
        $data['page_base_url'] = SITE_ROOT . "/im/outbox";
        //// END prepare pagination
        
        if ($app->conf->profilebutton && !$app->user->guest)
            $data['profilebutton'] = true;
        
        // Mark as read after response finished
        if (!empty($service->imcount) && $service->imcount > 0)
        {
            register_shutdown_function(function() use ($app, $service)
            {
                if(session_id()) session_write_close();
                $app->db->query("UPDATE {$db_prefix}instant_messages SET readBy=1 WHERE ID_MEMBER_TO=? AND readBy='0' LIMIT ?")->
                execute(array($app->user->id, $service->imcount));
            });
        }
        
        return $this->render('templates/im/inbox.template.php', $data);
    } // outbox
}
?>
