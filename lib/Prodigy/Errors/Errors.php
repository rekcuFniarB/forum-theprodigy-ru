<?php

namespace Prodigy\Errors;

class Errors {
    private $app;
    private $service;
    private $request;
    private $response;
    private $router;

    public function __construct($router) {
        $this->app = $router->app();
        $this->service = $router->service();
        $this->request = $router->request();
        $this->response = $router->response();
        $this->router = $router;
        $this->service->backtrace = array();
    }
    
    public function log($msg) {
        if ($this->app->conf->debug) {
            error_log($msg);
        }
    }
    
    //// Custom error page
    public function abort($title='Error', $msg='', $code=404) {
        if(empty($title)) $title = $this->app->locale->txt(106);
        //$this->app->respond->prepare_layout();
        $this->log("__ERROR__: [$title] $msg");
        $this->service->title = $title;
        $this->service->message = $msg;
        
        if(isset($this->app->respond))
            $respond = $this->app->respond;
        else
            $respond = $this->app->dumb;
        
        if (!$this->service->ajax)
        {
            $this->response->code($code);
            
            if($respond->layout_ready())
            {
                if (!empty(ob_get_length()))
                {
                    ob_clean();
                    // reset layout
                    $respond->prepare_layout();
                }
            }
            
            $respond->render('templates/error.php');
        }
        else
            $respond->ajax_response("    $title\n[$code] $msg", 'text');
        
        if(!$this->response->isSent())
            $this->response->send();
        else
            $this->log('__DEBUG__: errors->abort(): response already sent.');
        
        //return $this->response;
        exit();
    }
    
    public function backtrace($title = 'Fatal Error', $msg, $type, $err) {
        if ($this->app->conf->debug || $this->app->user->accessLevel() > 2) {
            $this->service->backtrace = array_reverse(debug_backtrace());
            if (!empty($err)) {
                $this->service->backtrace2 = $err;
                $this->log("__TRACE__:\n $err");
            }
        }
        return $this->abort($title, $msg, 400);
    }
    
    public function handler($errno = '-', $errstr = '-', $errfile = '-', $errline = '-') {
        // Errors handler
        $msg = "#$errno $errstr;    File: $errfile:$errline";
        error_log("__ERROR__: HANDLER $msg");
        $this->abort($this->app->locale->txt[106], $msg, 502);
        die();
        //return false;
    }

}

?>
