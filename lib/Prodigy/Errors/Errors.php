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
            $respond = $this->app->main;
        
        if (!$this->service->ajax)
        {
            $this->response->code($code);
            
            if($respond->layout_ready())
            {
                // FIXME this is temporary behavior. We should reset buffers and render new template.
                // Page already rendered, just print error
                echo "<script>alert('    $title\\n[$code] $msg');</script>";
                //echo "<script>alert('Error');</script>";
            }
            else
            {
                // Otherwise generate error page.
                $respond->render('templates/error.php');
            }
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
