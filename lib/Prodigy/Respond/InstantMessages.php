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
     * FIXME
     */
    public function NotifyUsers()
    {
        // see Post.php
        return;
    }

}
?>
