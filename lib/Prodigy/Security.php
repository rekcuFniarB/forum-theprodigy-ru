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
        
        $db->query("DELETE FROM {$db_prefix}log_floodcontrol WHERE ($time-logTime > $timeout)");
        $dbrq = $db->query("SELECT ip FROM {$db_prefix}log_floodcontrol WHERE ip='$IP' LIMIT 1");
        if ($dbrq->num_rows == 0)
        {
            $db->query("INSERT INTO {$db_prefix}log_floodcontrol (ip,logTime) VALUES ('$IP',$time)");
            return (false);
        }
        else
        {
            return $this->app->errors->abort('', "$txt[409] $timeout $txt[410]");
        }
    }
}

?>
