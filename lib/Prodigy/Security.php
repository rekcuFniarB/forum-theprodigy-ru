<?php

namespace Prodigy;

class Security
{
    public function __construct($router)
    {
        $this->app = $router->app();
        //$this->service = $router->service();
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
    
    public function enhanced_banning($user, $only_check=false)
    {
        return 0; // TODO
    } // enhanced_banning()
}

?>
