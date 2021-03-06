<?php
namespace Prodigy\Respond;

class InstantMessages extends Respond
{
    protected $notify_admins;
    
    //public function __construct($router)
    //{
    //    parent::__construct($router);
    //}

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
    
    /**
     * Send emails
     * @param array $to array of recepient emails
     * @param string $subject Message subject.
     * @param string $message Email body
     * @param string $fromName Display name mail came from
     * @param bool $html should we also include html format
     */
    public function sendmail($to, $subject, $message, $fromName = null, $html = false)
    {
        // If the recipient list isn't an array, make it one.
        if (!is_array($to))
            $to = array($to);

        // Get rid of slashes and entities.
        $subject = stripslashes($subject);
        $subject = mb_convert_encoding($subject, 'UTF-8', 'cp1251');
        $subject = html_entity_decode($subject, ENT_QUOTES, 'utf-8');
        $subject = '=?UTF-8?B?'.base64_encode($subject).'?=';
        $boundary = "===EMAIL=BOUNDARY=" . base_convert(time(), 10, 36) . "===";

        if ($html) {
            $message_html = $this->service->utf8($this->service->doUBBC($message, 'links,inline,blocks'));
            //$message_html = html_entity_decode($message_html, ENT_QUOTES, 'utf-8');
            $message_html = str_replace(array("\r", "\n"), array('', "\r\n"), stripslashes($message_html));
        }
        
        // Make the message use \r\n's only.
        $message = str_replace(array("\r", "\n"), array('', "\r\n"), stripslashes($message));
        
        $message = $this->service->utf8($message);
        $message = html_entity_decode($message, ENT_QUOTES, 'utf-8');

        if ($html) {
            $multipart_message = "This is a multi-part message in MIME format.\r\n";
            $multipart_message .= "--$boundary\r\n";
            $multipart_message .= "Content-Type: text/plain; charset=utf-8\r\n";
            $multipart_message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $multipart_message .= $message . "\r\n\r\n";
            
            $multipart_message .= "--$boundary\r\n";
            $multipart_message .= "Content-Type: text/html; charset=utf-8\r\n";
            $multipart_message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $multipart_message .= "<html>\r\n  <head>\r\n    <meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">\r\n";
            $multipart_message .= "    <style>\r\n      table {background-color: #b0b0b0; border: 1px solid #808080;}\r\n";
            $multipart_message .= "      div.spoiler {display: block !important; background-color: #a0a0a0;}\r\n    </style>\r\n";
            $multipart_message .= "  </head>\r\n<body>\r\n";
            $multipart_message .= "$message_html\r\n</body>\r\n\r\n";
            $multipart_message .= "--$boundary--";
            $message = &$multipart_message;
        }
        
        $umbname = $this->service->utf8($this->app->conf->mbname);
        if (!empty($fromName))
            $fromName = base64_encode($this->service->utf8($fromName));
        else
            $fromName = base64_encode($umbname);
        
        // Construct the mail headers...
        $headers = "From: \"=?UTF-8?B?$fromName?=\" <webmaster@{$this->service->host}>\r\n";
        $headers .= "Reply-To: \"=?UTF-8?B?$fromName?=\" <{$this->app->conf->webmaster_email}>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        
        if ($html)
            $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
        else
        {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "Content-Transfer-Encoding: 8bit\r\n";
        }
        
        $headers .= "Return-Path: {$this->app->conf->webmaster_email}\r\n";
        $headers .= 'Date: ' . gmdate('D, d M Y H:i:s') . ' +0000';
        
        // SMTP or sendmail?
        if ($this->app->conf->mail_type == 'sendmail')
            foreach ($to as $email)
                $mail_result = mail(str_replace(array("\r", "\n"), array('', ' '), $email), str_replace(array("\r", "\n"), array('', ' '), $subject), $message, $headers);
        else
            $mail_result = $this->smtp_mail($to, $subject, $message, $headers);
        
        // Everything go smoothly?
        return $mail_result;
    } // sendmail()
    
    protected function smtp_mail($mail_to_array, $subject, $message, $headers)
    {
        if (!$socket = fsockopen($this->app->conf->smtp_host, 25, $errno, $errstr, 20))
            return $this->error("Could not connect to smtp host : $errno : $errstr");

        if (!$this->server_parse($socket, '220'))
            return false;
        if ($this->app->conf->smtp_username && $this->app->conf->smtp_password)
        {
            fputs($socket, 'EHLO ' . $this->app->conf->smtp_host . "\r\n");
            if (!$this->server_parse($socket, '250'))
                return false;
            fputs($socket, "AUTH LOGIN\r\n");
            if (!$this->server_parse($socket, '334'))
                return false;
            fputs($socket, base64_encode($this->app->conf->smtp_username) . "\r\n");
            if (!$this->server_parse($socket, '334'))
                return false;
            fputs($socket, base64_encode($this->app->conf->smtp_password) . "\r\n");
            if (!$this->server_parse($socket, '235'))
                return false;
        }
        else
        {
            fputs($socket, 'HELO ' . $this->app->conf->smtp_host . "\r\n");
            if (!$this->server_parse($socket, '250'))
                return false;
        }
        
        foreach ($mail_to_array as $mail_to)
        {
            fputs($socket, 'MAIL FROM: <' . $this->app->conf->webmaster_email . ">\r\n");
            if (!$this->server_parse($socket, '250'))
                return false;
            fputs($socket, 'RCPT TO: <' . $mail_to . ">\r\n");
            if (!$this->server_parse($socket, '250'))
                return false;
            fputs($socket, "DATA\r\n");
            if (!$this->server_parse($socket, '354'))
                return false;
            fputs($socket, 'Subject: ' . $subject . "\r\n");
            if (strlen($mail_to))
                fputs($socket, 'To: <' . $mail_to . ">\r\n");
            fputs($socket, $headers . "\r\n\r\n");
            fputs($socket, $message . "\r\n");
            fputs($socket, ".\r\n");
            if (!$this->server_parse($socket, '250'))
                return false;
            fputs($socket, "RSET\r\n");
            if (!$this->server_parse($socket, '250'))
                return false;
        }
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        return true;
    } // smtp_mail()
    
    // Parse a message to the SMTP server.
    protected function server_parse($socket, $response)
    {
        // No response yet.
        $server_response = '';
        
        while (substr($server_response, 3, 1) != ' ')
            if (!($server_response = fgets($socket, 256)))
                return $this->error('Couldn\'t get mail server response codes');
        
        if (substr($server_response, 0, 3) != $response)
            return $this->error("Ran into problems sending Mail. Error: $server_response");

        return true;
    } // server_parse()
    
    /**
     * Sends notifications to users.
     * @param array $receivers - list of ID's of users notice send to
     * @param string $subject - subject
     * @param string $message - notification message
     * @param int $fromID - sender ID.
     * @param string $fromName - user name (login).
     */
    public function send_notice($receivers, $subject, $message='', $fromID=null, $fromName=null)
    {
        if (! is_array($receivers))
            $receivers = array($receivers);
        
        $subject = "[NOTICE] $subject";
        
        $db_prefix = $this->app->db->prefix;
        
        $placeholders = $this->app->db->build_placeholders($receivers);
        
        $dbst = $this->app->db->prepare("SELECT memberName, ID_MEMBER, emailAddress, im_email_notify FROM {$db_prefix}members WHERE ID_MEMBER IN ($placeholders)");
        $dbst->execute($receivers);
        $receivers = $dbst->fetchAll();
        $dbst = null; // closing this statement
        
        $insert_values = array();
        $sent_time = time();
        $mail_to = array();
        
        if (empty($fromID) || empty($fromName))
        {
            $fromID = 0;
            $fromName = '';
        }
        
        if (count($receivers) > 0) {
            $dbst = $this->app->db->prepare("INSERT INTO {$db_prefix}instant_messages (ID_MEMBER_FROM, fromName, ID_MEMBER_TO, toName, msgtime, subject, body, deletedBy) VALUES (?,?,?,?,?,?,?,?)");
            foreach($receivers as $receiver)
            {
                $dbst->execute(array($fromID, $fromName, $receiver['ID_MEMBER'], $receiver['memberName'], $sent_time, $subject, $message, 0));
    
                if ($receiver['im_email_notify'])
                    // Preparing email receivers
                    $mail_to[] = $receiver['emailAddress'];
                
            } // foreach $receivers
            $dbst = null;
        } // if $receivers
        
        if (!empty($mail_to))
            $this->sendmail($mail_to, $subject, $message, null, true);
    } // send_notice()
    
    /**
     * Sends notifications to admins.
     * @param $subject - subject
     * @param $message - notification message
     * @param $fromID - sender ID.
     * @param $fromName - user name (login).
     */
    public function notifyAdmins($subject, $message, $fromID=null, $fromName=null){
        $db_prefix = $this->app->db->db_prefix;
        $r = $this->app->db->query("SELECT memberName, ID_MEMBER, emailAddress, im_email_notify FROM {$db_prefix}members WHERE memberGroup='Administrator'");
        $admins = $r->fetchAll();
        $r = null;
        $receivers = array();
        // List of admins who don't want to receive notifications
        $dontNotifyAdmins = $this->app->conf->get('dontNotifyAdmins', array());
        
        foreach($admins as $admin){
            if (!in_array($admin['ID_MEMBER'], $dontNotifyAdmins) && !in_array($admin['memberName'], $dontNotifyAdmins)){
                $receivers[] = $admin['ID_MEMBER'];
            }
        }
        if (!empty($receivers)){
            $this->send_notice($receivers, $subject, $message, $fromID, $fromName);
        }
    } // NotifyAdmins()
    
    /**
     * Store messages to send to admins in the end of process
     * Calling without params sends stored messages.
     * @param $subject - subject
     * @param $message - message
     */
    public function notifyAdminsLater($subject = null, $message = null) {
        if (!isset($this->notify_admins))
            $this->notify_admins = array();
        
        if (!is_null($subject) && !is_null($message)) {
            //// Store messages to send to admins later
            $this->notify_admins[] = array($subject, $message);
        } else {
            //// Send stored messages to admins
            if (isset($this->notify_admins)) {
                foreach ($this->notify_admins as $message) {
                    $this->notifyAdmins($message[0], $message[1]);
                }
                // Erase sent messages list
                $this->notify_admins = array();
            }
        }
    } // notifyAdminsLater()
    
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
            'windowbg' => $bgcolors[(++ $counter % $bgcolornum)],
            'switch_folder' => 'outbox/',
            'switch_folder_name' => $app->locale->txt[320],
            'messages' => $messages,
            'linktree' => array(
                array('url' => '/im/', 'name' => $app->locale->txt[144]),
                array('name' => $app->locale->txt[316])
            )
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
        register_shutdown_function(function() use ($app, $service)
        {
            if (is_array($service->imcount) && $service->imcount[1] > 0)
            {
                if(session_id())
                    session_write_close();
                //fastcgi_finish_request(); // this doesn't work expected way on PHP FPM
                $app->db->prepare("UPDATE {$app->db->prefix}instant_messages USE INDEX(ID_MEMBER_TO) SET readBy=1 WHERE ID_MEMBER_TO=? AND readBy='0' LIMIT ?")->
                    execute(array($app->user->id, $service->imcount[1]));
            }
        });
        
        return $this->render('im/inbox.template.php', $data);
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
            'windowbg' => $bgcolors[(++ $counter % $bgcolornum)],
            'switch_folder' => '',
            'switch_folder_name' => $app->locale->txt[316],
            'messages' => $messages,
            'linktree' => array(
                 array('url' => '/im/', 'name' => $app->locale->txt[144]),
                 array('name' => $app->locale->txt[320])
            )
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
        
        return $this->render('im/inbox.template.php', $data);
    } // outbox
    
    public function quote($request, $response, $service, $app)
    {
        $service->quote = true;
        return $this->impost($request, $response, $service, $app);
    }
    
    public function report($request, $response, $service, $app)
    {
        $service->report_msgid = $request->paramsNamed()->get('msgid');
        $service->report = true;
        return $this->impost($request, $response, $service, $app);
    }
    
    public function impost($request, $response, $service, $app)
    {
        if ($app->user->guest)
            return $this->error($app->locale->txt[147]);
        
        $PARAMS = $request->paramsNamed();
        $COOKIES = $request->cookies();
        
        $db_prefix = $app->db->prefix;
        
        if($request->method('GET'))
        {
            $GET = $request->paramsGet();
            $form_subject = $GET->form_subject;
            $service->imto = $GET->to;
            
            if (!empty($PARAMS->imsg))
            {
                $dbst = $app->db->prepare("SELECT * FROM {$db_prefix}instant_messages WHERE (ID_IM=? AND (ID_MEMBER_TO=? || ID_MEMBER_FROM=?))");
                $dbst->execute(array($PARAMS->imsg, $app->user->id, $app->user->id));
                $imsg = $dbst->fetch();
                $dbst = null; // closing this statement
                if (empty($imsg))
                    return $this->error('Hacker?');
                
                $imsg['msgtime'] = $app->subs->timeformat($imsg['msgtime']);
                $form_subject = $imsg['subject'];
                if (!stristr(substr($form_subject,0,3), 're:'))
                    $form_subject = "Re: $form_subject";
                
                $service->imto = $imsg['fromName'];
                $imsg['author'] = $app->user->loadDisplay($imsg['fromName']);
                
                if ($service->quote)
                {
                    $msg['body'] =  preg_replace("|<br( /)?[>]|","\n", $imsg['body']);
                    if ($app->conf->removeNestedQuotes)
                    {
                        $imsg['body'] = preg_replace("-\n*\[quote([^\\]]*)\]((.|\n)*?)\[/quote\]([\n]*)-", '', $imsg['body']);
                        $imsg['body'] = preg_replace("/\n*\[\/quote\]\n*/", '', $imsg['body']);
                    }
                    $form_message = "[quote] {$imsg['body']} [/quote]\n";
                }
                
                $service->reply_msg = $imsg;
                $service->is_reply = true;
            } // if is reply or quote
            
            if ($service->report)
            {
                $cookieName = "reportedTopicsByUser";
                $topicSignature = $service->board . "|" . $service->threadid;
                
                $service->show_warning = false;
                
                $report_cookie = $COOKIES->get($cookieName);
                if (is_array($report_cookie))
                    if (in_array($topicSignature, $report_cookie))
                        $service->show_warning = true;
                
                $response->cookie($cookieName."[]", $topicSignature, time()+900);  /* expire in 15 minutes */
                
                $service->report_msgid = intval($service->report_msgid);
                if ($service->report_msgid == 0)
                    return $this->error('Bad Request.');
                
                $dbst = $app->db->query("SELECT * FROM {$db_prefix}messages WHERE ID_MSG={$service->report_msgid}");
                $report_msg = $dbst->fetch();
                $dbst = null; // closing this statement
                if (empty($report_msg))
                    return $this->error('Hacker?');
                
                $report_msg['body'] =  preg_replace("|<br( /)?[>]|","\n", $report_msg['body']);
                $form_message = "[quote] {$report_msg['body']} [/quote]\n";
                $form_message .= "[iurl]". SITE_URL . "/{$service->report_msgid}/[/iurl]";
                $form_subject = $service->form_subject;
                
                $service->is_report_field = true;
            } // if is report
            
            $form_subject = !empty($form_subject) ? $form_subject : $app->locale->txt[24];
            
            $data = array(
                'title' => $app->locale->txt[148],
                'form_subject' => $form_subject,
                'form_message' => empty($form_message) ? '' : $form_message,
                'switch_folder' => '',
                'switch_folder_name' => $app->locale->txt[316],
                'linktree' => array(
                    array('url' => '/im/', 'name' => $app->locale->txt[144]),
                    array('name' => $app->locale->txt[321])
                )
            );
            
            $this->addJS('ubbc.js');
            return $this->render('im/impost.template.php', $data);
        } // if GET
        elseif ($request->method('POST'))
        {
            $app->session->check('post');
            
            $POST = $request->paramsPost();
            
            $nouser = array();
            
            $POST->naztem = trim($POST->naztem);
            $service->validate($POST->naztem, $app->locale->txt[77])->notEmpty();
            
            $service->validate($POST->message, $app->locale->txt[499])->notEmpty()->isLen(1,$app->conf->MaxMessLen);
            
            $service->validate($POST->to, $app->locale->txt[747])->notEmpty();
            
            $is_report = false;
            
            if (!empty($POST->is_report))
            {
                $is_report = true;
                $POST->naztem = "[NOTICE] {$POST->naztem}";
            }
            
            $message = $POST->message;
            $subject = $POST->naztem;
            
            $message = $app->subs->preparsecode($message);
            
            $nouser = array();
            $multiple = array_unique(explode(',', $POST->to));
            foreach ($multiple as $dbm)
            {
                $dbm = trim($dbm);
                $ignored = 0;
                $dbm = preg_replace("/[^0-9A-Za-z�-��-�#%+\s,-\.:=?@^_������������������������������]/", '', $dbm);
                # Check Ignore-List
                $dbst = $app->db->prepare("SELECT im_ignore_list,ID_MEMBER,im_email_notify,emailAddress,lngfile FROM {$db_prefix}members WHERE memberName=? LIMIT 1");
                $dbst->execute(array($dbm));
                $row = $dbst->fetch();
                $dbst = null;
                if (empty($row))
                {
                    #adds invalid user's name to array which error list will be built from later
                    $nouser[] = $dbm;
                    $ignored = 1;
                }
                else
                {
                    $ignore = explode(',', $row['im_ignore_list']);
                    $toID = $row['ID_MEMBER'];
                    $emailNotify = $row['im_email_notify'];
                    $notifyAddress = $row['emailAddress'];
                    $userlng = $row['lngfile'];
                    
                    # If User is on Recipient's Ignore-List, show Error Message
                    foreach ($ignore as $igname)
                    {
                        #adds ignored user's name to array which error list will be built from later
                         if ($igname == $app->user->name || $igname == "*")
                        {
                            $nouser[] = $dbm;
                            $ignored = 1;
                        }
                    }
                    
                    //$dbst = $app->db->prepare("SELECT ID_MEMBER FROM {$db_prefix}members WHERE memberName=?");
                    //$dbst->execute(array($app->user->name));
                    //$fromID = $dbst->fetchColumn();
                    //$dbst = null;
                    if($is_report)
                    {
                        // It's a report to moderators, don't show in outbox
                        $app->db->prepare("
                            INSERT INTO {$db_prefix}instant_messages (ID_MEMBER_FROM,ID_MEMBER_TO,fromName,toName,msgtime,subject,body,deletedBy)
                            VALUES (?,?,?,?,?,?,?,?)")->
                            execute(array($app->user->id, $toID, $app->user->name, $dbm, time(), $subject, $message, 0));
                    }
                    else 
                    {
                        $app->db->prepare("
                            INSERT INTO {$db_prefix}instant_messages (ID_MEMBER_FROM,ID_MEMBER_TO,fromName,toName,msgtime,subject,body)
                            VALUES (?,?,?,?,?,?,?)")->
                            execute(array($app->user->id,$toID,$app->user->name,$dbm,time(),$subject,$message));
                    }
                    $imID = $app->db->lastInsertId();
                    // Log the message
                    $app->db->prepare("INSERT INTO {$db_prefix}log_latest_actions (type, actorID, addresseeID, subject, value)
                    VALUES ('NEW_INSTANT_MESSAGE', ?, ?, ?, ?)")->
                    execute(array($app->user->id, $toID, $subject, $message));

                    # Send notification
                    if ($emailNotify == 1)
                    {
                        $mydate = $app->subs->timeformat(time());
                        if ($notifyAddress != '')
                        {
                            if ($app->conf->userLanguage)
                            {
                                $desiredlanguage = (($userlng == Null || $userlng == '') ? $app->conf->language : $userlng);
                                if ($desiredlanguage != $this->locale->lngfile)
                                {
                                    $app->locale->set_locale($desiredlanguage);
                                }
                            }
                            $fromname = $app->user->realname;
                            $email_subject = str_replace ('SUBJECT', $subject, $app->locale->txt[561]);
                            $email_subject = str_replace ('SENDER', $fromname, $email_subject);
                            $email_message = str_replace ('DATE', strip_tags($mydate), $app->locale->txt[562]);
                            $email_message = str_replace ('MESSAGE', strip_tags($message), $email_message);
                            $email_message = str_replace ('SENDER', $fromname, $email_message);
                            $this->sendmail($notifyAddress, $email_subject, $email_message, null, true);
                        }
                    }
                    
                    # Log congratulation
                    if ($POST->form_type == "ny")
                        $app->db->query("INSERT LOW_PRIORITY INTO {$db_prefix}log_congratulations VALUES (?, ?)")->
                        execute(array($app->user->id, $imID));
                } // if $row
            } // foreach 
            
            #if there were invalid usernames in the recipient list, these names are listed after all valid users have been IMed
            if (sizeof($nouser) > 0)
            {
                $badusers = implode(', ', $nouser);
                return $this->error("$badusers {$app->locale->txt[747]}");
            }
            
            return $this->redirect('/im/');
        }// if POST
    } // impost()
    
    public function prefs($request, $response, $service, $app)
    {
        if ($app->user->guest)
            return $this->error($app->locale->txt[147]);
        
        $db_prefix = $app->db->prefix;
        
        if ($request->method('GET'))
        {
            $dbst = $app->db->prepare("SELECT im_ignore_list,im_email_notify FROM {$db_prefix}members WHERE ID_MEMBER=?");
            $dbst->execute(array($app->user->id));
            $imconfig = $dbst->fetch();
            $dbst = null;
            $data = array(
                'title' => "{$app->locale->txt[144]}: {$app->locale->txt[323]}",
                'sel0' => $imconfig['im_email_notify'] ? '' : 'selected="selected"',
                'sel1' => $imconfig['im_email_notify'] ? 'selected="selected"' : '',
                'ignores' => str_replace(',', "\n", $imconfig['im_ignore_list']),
                'linktree' => array(
                    array('url' => '/im/', 'name' => $app->locale->txt[144]),
                    array('name' => $app->locale->txt[323])
                )
            );
            $this->render('im/prefs.template.php', $data);
        } // if GET
        elseif ($request->method('POST'))
        {
            $app->session->check('post');
            $POST = $request->paramsPost();
            $ignorelist = str_replace(array("\r\n", "\n\r", "\n"), ',', trim($POST->ignore));
            
            $app->db->prepare("UPDATE {$db_prefix}members SET im_ignore_list=?,im_email_notify=? WHERE ID_MEMBER=?")->
                execute(array($ignorelist, $POST->notify, $app->user->id));
            
            return $this->back();
        } // if POST
    } // prefs()
    
    public function removeFromOutbox($request, $response, $service, $app)
    {
         $service->delFromOutbox = true;
         return $this->remove($request, $response, $service, $app);
    } // removeFromOutbox
    
    public function remove($request, $response, $service, $app)
    {
        if ($app->user->guest)
            return $this->error($app->locale->txt[147]);
        
        if (!$service->delFromOutbox)
            // remove from inbox
            $delBy = 1;
        else
            // remove from outbox
            $delBy = 0;
        
        $db_prefix = $app->db->prefix;
        
        if ($request->method('GET'))
        {
            if (empty($PARAMS->imid))
                return $this->error('No message specified.');
            
            $app->session->check('get');
            $PARAMS = $request->paramsNamed();
            $this->delete($delBy, $PARAMS->imid);
        } // if GET
        elseif ($request->method('POST'))
        {
            $app->session->check('post');
            
            $POST = $request->paramsPost();
            
            foreach( $POST as $postVar => $postVarValue )
            {
                if (strcmp(substr($postVar, 0, 7), 'delete_') == 0)
                {
                    $id = substr($postVar, 7);
                    $this->delete($delBy, $id);
                }
            } // foreach()
        } // if POST
        
        return $this->back();
    } // remove()
    
    /**
     * Delete message.
     * @param int $delBy delete from inbox (1) or outbox (0).
    */
    protected function delete($delBy, $imid)
    {
        if ($delBy === 1)
            // Remove from inbox
            $tofrom = 'TO';
        elseif ($delBy === 0)
            // remove fro outbox
            $tofrom = 'FROM';
        else
            return $this->error('Improper value passed to delete()');
        
        $db_prefix = $this->app->db->prefix;
        $dbst = $this->app->db->prepare("SELECT deletedBy FROM {$db_prefix}instant_messages WHERE ID_IM=? AND ID_MEMBER_$tofrom = ?");
            $dbst->execute(array($imid, $this->app->user->id));
            $check = $dbst->fetchColumn();
            $dbst = null;
            if ($check === false)
                return $this->error("Hacker?");
            
            $dbst = $this->app->db->prepare("SELECT deletedBy FROM {$db_prefix}instant_messages WHERE ID_IM=? AND deletedBy != -1");
            $dbst->execute(array($imid));
            if ($dbst->fetchColumn() !== false)
                // Already marked as deleted from inbox or outbox, now delete completely
                $this->app->db->prepare("DELETE FROM {$db_prefix}instant_messages WHERE ID_IM=?")->
                    execute(array($imid));
            else
                // Mark as deleted from inbox or outbox
                $this->app->db->prepare("UPDATE {$db_prefix}instant_messages SET deletedBy=? WHERE ID_IM=?")->
                    execute(array($delBy, $imid));
            $dbst = null;
    } // delete()
    
    public function removeallFromOutbox($request, $response, $service, $app)
    {
        $service->delFromOutbox = true;
        return $this->removeall($request, $response, $service, $app);
    }
    
    public function removeall($request, $response, $service, $app)
    {
        if ($app->user->guest)
            return $this->error($app->locale->txt[147]);
        
        $data = array();
        
        if (!$service->delFromOutbox)
        {
            // remove from inbox
            $delBy1 = 0;
            $delBy2 = 1;
            $tofrom = 'ID_MEMBER_TO';
            $data['title'] = $app->locale->txt[316];
        }
        else
        {
            // remove from outbox
            $delBy1 = 1;
            $delBy2 = 0;
            $tofrom = 'ID_MEMBER_FROM';
            $data['title'] = $app->locale->txt[320];
        }
        
        $data['question'] = str_replace('IMBOX', $data['title'], $app->locale->txt[412]);
        
        if ($request->paramsGet()->get('sesc') === null)
        {
            // Show prompt
            return $this->render('im/removeall.template.php', $data);
        }
        else
        {
            $app->session->check('get');
            $db_prefix = $app->db->prefix;
            
            // Delete messages already marked as deleted
            $app->db->prepare("DELETE FROM {$db_prefix}instant_messages WHERE $tofrom = ? && deletedBy = ?")->
                execute(array($app->user->id, $delBy1));
            // Mark messages as deleted
            $app->db->prepare("UPDATE {$db_prefix}instant_messages SET deletedBy=? WHERE $tofrom = ?")->
                execute(array($delBy2, $app->user->id));
            
            return $this->redirect('/im/');
        } // if sesc
    } // removeall()
}
?>
