<?php

namespace Prodigy;

class Session {
    private $app;
    //private $service;
    //private $request;
    //private $response;
    private $router;
    public $id;
    
    public function __construct($router) {
        $this->app = $router->app();
        //$this->service = $router->service();
        //$this->request = $router->request();
        //$this->response = $router->response();
        $this->router = $router;
        
        $sid = session_id();
        if ($sid === '') {
            session_start();
            $sid = session_id();
        }
        if ($sid === '') {
            $this->app->errors->abort('Error', 'Session start failed.', 500);
        } else {
            $this->id = $sid;
        }
    }
    
    /**
     * Get data stored in session
     * @param string $name     variable name
     * @param mixed  $default  default value to return no value found
     */
    public function get($name, $default = NULL) {
        if (isset($_SESSION[$name]))
            return $_SESSION[$name];
        else
            return $default;
    }
    
    /**
     * Store data into session and lazily start new session if not started yet
     * @param   string $name      variable name
     * @param   mixed  $value     value to store
     * @param   mixed  $default   default value to return
     * @returns previous value if exists or default value
     */
    public function store($name, $value, $default = null) {
        if (isset($_SESSION[$name]))
            $default =  $_SESSION[$name];
        $_SESSION[$name] = $value;
        return $default;
    }
    
    /**
     * Synonym of store()
     */
    public function set($name, $value, $default = nul) {
        return $this->store($name, $value, $default);
    }

    /**
     * Remove stored data from session.
     * @param string $name  variable name
     */
    public function del($name) {
        if (isset($_SESSION[$name]))
            unset($_SESSION[$name]);
    }
    
    public function __get($name) {
        return $this->get($name);
    }
    
    public function __set($name, $value) {
        $this->store($name, $value);
    }
    
    public function __isset($name) {
        return !empty($_SESSION[$name]);
    }
    
    public function __unset($name) {
        $this->del($name);
    }
    
    /**
     * Validate this session
     */
    public function check($type = 'post') {
        $request = $this->router->request();
        
        if ($type == 'post')
        {
            if (!$request->method('POST') || $request->paramsPost()->get('sc') != $this->id)
                return $this->app->errors->abort('', $this->app->locale->yse304);
        }
        else
        {
            if (!$request->method('GET') || $request->paramsGet()->get('sesc') != $this->id)
                return $this->app->errors->abort('', $this->app->locale->yse305);
        }
        
        $server = $request->server();
        $referer = parse_url($server->get('HTTP_REFERER'));
        $host = $server->get('HTTP_HOST');
        
        if (strpos($host,':'))
            $rhost = substr($host, 0, strpos($host, ':'));
        else
            $rhost = $host;
        
        if (strlen($referer['host']) && strlen($rhost) && strtolower($referer['host']) != strtolower($rhost) && $referer['host'] != $server->get('SERVER_ADDR'))
            return $this->app->errors->abort('Error', $this->app->locale->yse306);
    } // check()
    
    /**
     * Get session name
     */
    public function name($name = null) {
        if(is_null($name))
            return session_name();
        else
            return session_name($name);
    }
    
    public function erase() {
        $_SESSION = array();
    }
}

?>
