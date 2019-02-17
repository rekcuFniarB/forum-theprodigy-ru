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
            WHERE (ID_MEMBER_TO={$ID_MEMBER} AND deletedBy != 1) GROUP BY readBy", false);
        
        if ($request->num_rows == 0)
            $munred = $mnum = 0;
        elseif ($request->num_rows == 1)
        {
            list($mnum, $readBy) = $request->fetch_row();
            if ($readBy == 0)
                $munred = $mnum;
            else
                $munred = 0;
        }
        else
        {
            list($munred, $dummy) = $request->fetch_row();
            list($mnum, $dummy) = $request->fetch_row();
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
        
        $threadid = $this->app->db->escape_string($threadid);
        
        $dbrq = $this->app->db->query("
            SELECT notifies
            FROM {$db_prefix}topics
            WHERE (ID_TOPIC = $threadid
                AND notifies != '')
            LIMIT 1");
        
        if ($dbrq->num_rows != 0)
        {
            $row = $dbrq->fetch_row();
            
            $members = $this->app->db->query("
                SELECT emailAddress, notifyOnce, ID_MEMBER, lngfile
                FROM {$db_prefix}members
                WHERE ID_MEMBER IN ('" . implode("','", explode(',', $row[0])) . "')
                AND emailAddress != ''
                AND ID_MEMBER != {$this->app->user->id}
                ORDER BY lngfile");
            
            $curlanguage = $this->app->locale->lngfile;

            while ($rowmember = $members->fetch_array())
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
                    $dbrq = $this->app->db->query("
                        SELECT notificationSent
                        FROM {$db_prefix}log_topics
                        WHERE ID_MEMBER={$rowmember['ID_MEMBER']}
                        AND ID_TOPIC = $threadid");
                    
                    if ($dbrq->num_rows == 0)
                        $notificationSent = 0;
                    else
                        list($notificationSent) = $dbrq->fetch_row();
                    
                    if ($notificationSent == 0)
                    {
                        $send_body = "$txt[128] $txt[129]
                        " . SITE_URL ."/b{$service->board}/t$threadid/new/\n\n{$txt['notifyXOnce2']}\n\n$txt[130]";
                        $this->app->im->sendmail($rowmember['emailAddress'], $send_subject, $send_body);
                        
                        if ($dbrq->num_rows == 0)
                            $dbrq = $db->query("
                                INSERT INTO {$db_prefix}log_topics
                                (logTime, ID_MEMBER, ID_TOPIC, notificationSent)
                                VALUES (0, '{$rowmember['ID_MEMBER']}', $threadid, 1)");
                        else
                            $dbrq = $db->query("
                                UPDATE {$db_prefix}log_topics
                                SET notificationSent = 1
                                WHERE (ID_MEMBER = '$rowmember[ID_MEMBER]'
                                AND ID_TOPIC=$threadid)");
                    }
                }
                else
                {
                    $send_body = "$txt[128] $txt[129]  " . SITE_URL . "/b{$service->board}/t$threadid/new/\n\n$txt[130]";
                    $this->app->im->sendmail($rowmember['emailAddress'], $send_subject, $send_body);
                }
            } // while fetch_array()
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
        
        $dbrq = $this->app->db->query("
            SELECT notifies FROM {$db_prefix}topics
            WHERE (ID_TOPIC = $threadid)
            LIMIT 1");
        
        list($notification) = $dbrq->fetch_row();
        $notifies = explode(',', $notification);
        $notifications2 = array();
        
        foreach ($notifies as $note) 
            if ($note != $this->app->user->id && $note != '')
                $notifications2[] = $note;
        
        if (!in_array($this->app->user->id, $notifications2))
            $notifications2[] = $this->app->user->id;
        
        $notification = implode(",", $notifications2);
        $dbrq = $this->app->db->query("
            UPDATE {$db_prefix}topics
            SET notifies = '$notification'
            WHERE ID_TOPIC = $threadid");
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
