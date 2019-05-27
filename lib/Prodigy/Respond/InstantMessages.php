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
        
        $threadid = $this->app->db->escape_string($threadid);
        
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

}
?>
