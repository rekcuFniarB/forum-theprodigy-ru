<?php

namespace Prodigy;

class Security
{
    public function __construct($router)
    {
        $this->app = $router->app();
        $this->service = $router->service();
        $this->request = $router->request();
        //$this->response = $router->response();
        $this->router = $router;
    }
    public function isTOR()
    {
        return false; // FIXME
    } // isTOR()
    
    public function containsBannedNickPart()
    {
        return false; // FIXME
    } // containsBannedNickPart()
    
    public function containsForbiddenText($text)
    {
        return false; // FIXME
    }
    
    public function spam_protection()
    {
        $time = time();
        $IP = $this->request->server()->get('REMOTE_ADDR');
        
        $db = $this->app->db;
        $db_prefix = $db->prefix;
        $timeout = $this->app->conf->timeout;
        
        $db->prepare("DELETE FROM {$db_prefix}log_floodcontrol WHERE ($time-logTime > ?)")->
            execute(array($timeout));
        $dbrq = $db->prepare("SELECT ip FROM {$db_prefix}log_floodcontrol WHERE ip=? LIMIT 1");
        $dbrq->execute(array($IP));
        if (!$dbrq->fetchColumn())
        {
            $db->prepare("INSERT INTO {$db_prefix}log_floodcontrol (ip,logTime) VALUES (?,?)")->
                execute(array($IP, $time));
            return (false);
        }
        else
        {
            return $this->app->errors->abort('', "$txt[409] $timeout $txt[410]");
        }
    } // spam_protection()
    
    public function enhanced_banning($username = null, $only_check=false)
    {
        $SERVER = $this->request->server();
        $COOKIES = $this->request->cookies();
        $timenow = time();
        $response = $this->router->response();
        $self = false;
        
        if (empty($username))
            $username = $this->app->user->name;
        
        if ($username == 'Guest') {
            $registeredUserString = "IP = ?";
            $qparams = array($SERVER->REMOTE_ADDR);
        }
        elseif ($this->app->user->name == $username) { // self
            $registeredUserString = "Name = ? OR Mail = ? OR b.ID_MEMBER = ? OR IP = ?";
            $qparams = array($this->app->user->name, $this->app->user->email, $this->app->user->id, $SERVER->REMOTE_ADDR);
            $self = true;
        }
        else {
            $registeredUserString = "Name = ?";
            $qparams = array($username);
        }
        
        $this->remove_expired_bans();
        
        $db_prefix = $this->app->db->prefix;
        
        // Unmark users not found in ban list
        if ($username != 'Guest') {
            $dbst = $this->app->db->prepare("UPDATE {$db_prefix}members m LEFT JOIN {$db_prefix}banned_enh b ON m.ID_MEMBER = b.ID_MEMBER OR emailAddress = Mail OR Name = memberName SET passwd = REPLACE(passwd, 'B:', '') WHERE memberName = ? AND passwd like 'B:%' AND ID_BANNED IS NULL");
            $dbst->execute(array($username)); $dbst = null;
        }
        
        $dbst = $this->app->db->prepare("SELECT BannedUntil, Reason, m.realName, m.memberName, Name, Mail
            FROM {$db_prefix}banned_enh AS b LEFT JOIN {$db_prefix}members AS m ON (b.BannedBy = m.ID_MEMBER)
            WHERE $registeredUserString");
        $dbst->execute($qparams);
        $bannedUser = $dbst->fetch(); $dbst = null;
        if (!empty($bannedUser))
        {
            
            if (empty($bannedUser['Name']) and empty($bannedUser['Mail']) and !$only_check)
            {
                // get banned until time
                $bannedUntil = (empty($bannedUser['BannedUntil'])) ? time()+7*24*60*60 /* +7 days */ : $bannedUser['BannedUntil'];
                
                // take the longest ban time
                if ($this->app->session->get('bannedUntil', 0) > $bannedUntil)
                    $bannedUntil = $this->app->session->get('bannedUntil');
                if ($COOKIES->get('bannedUntil', 0) > $bannedUntil)
                    $bannedUntil = $COOKIES->get('bannedUntil');
                
                $bannedUntil += 2*60*60;
                
                // set cookie and session for the guy to be banned		
                $this->app->session->store('badGuy', TRUE);
                $response->cookie("badGuy", "TRUE", $bannedUntil);
                $response->cookie("bannedUntil", $bannedUntil, $bannedUntil);
                $this->app->session->store('bannedUntil', $bannedUntil);
                
                // update banned until time in the database
                $dbst = $this->app->db->query("UPDATE {$db_prefix}banned_enh SET BannedUntil = ? WHERE IP LIKE ?");
                $dbst->execute(array($bannedUntil, $SERVER->REMOTE_ADDR)); $dbst = null;
            }
            
            if(!$only_check){
                // show the message
                $qfields = array('ip', 'logTime', 'userAgent');
                $qvals = array(
                    $SERVER->REMOTE_ADDR,
                    time(),
                    $SERVER->HTTP_USER_AGENT
                );
                if ($self) {
                    $qfields[] = 'email';
                    $qvals[] = $this->app->user->email;
                }
                $dbst = $this->app->db->prepare("INSERT INTO {$db_prefix}log_banned (".implode(',', $qfields).")
                    VALUES (".$this->app->db->build_placeholders($qvals).")");
                $dbst->execute($qvals); $dbst = null;
                return $this->app->errors->abort('', "{$this->app->locale->txt[678]}{$this->app->locale->txt[430]} {$this->app->locale->txt[750]} " . ((empty($bannedUser['BannedUntil'])) ? "&#8734" : date("d-m-Y H:i:s",$bannedUser['BannedUntil']) . ' МСК') . "!<br /><br />".(!empty($bannedUser['realName']) ? "<b>Забанил:</b> <a href=\"{$this->service->siteurl}/people/".rawurlencode($bannedUser[3])."/\">{$bannedUser['realName']}</a><br /><br />" : "")."<b>{$this->app->locale->txt[751]}:</b> ".$bannedUser['Reason']);		
            }
            else // check only, just return an info
                return $bannedUser;
        } // user is banned
        else {
            // not found in ban table
            return 0;
        }
    } // enhanced_banning()
    
    public function remove_expired_bans() {
        $this->app->db->query("DELETE FROM {$this->app->db->db_prefix}banned_enh WHERE BannedUntil < " . time());
    }
    
    // Is visitor from Roskolhoznadzor
    public function is_roskolhoznadzor() {
        $REMOTE_ADDR = $this->request->server()->get('REMOTE_ADDR');
        $knownIPs = $this->app->conf->roskolhoznadzor['IP'];
        
        if (!is_array($knownIPs))
            return false;
        
        foreach ($knownIPs as $IP => $comment) {
            if (strpos($REMOTE_ADDR, $IP) === 0) {
                // IP match!
                return $comment;
            }
        }
        
        // No match, return false
        return false;
    } // is_roskolhoznadzor()
}

?>
