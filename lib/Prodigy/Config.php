<?php

namespace Prodigy;

class Config {
    private $router;
    private $app;
    public $txt;
    public $mods;
    public $settings;
    public $db_ready;
    private $modsettings;
    private $request;
    
    public function __construct($router) {
        $this->router = $router;
        $this->app = $router->app();
        $this->modsettings = NULL;
        $this->db_ready = false;
        $this->request = $router->request();
        
        ## Some default values, may be overriden in config files
        $this->YaBBversion = 'YaBB SE 2.0 alpha';
        $this->MaxSigLen = 1000;
        $this->loginregex = "/^[\s0-9A-Za-z#,-\.:=?@^_�������������������������������������Ũ��������������������������]+$/";
        $this->pwseed = 'ys';
        ## end default values
        
        require_once(PROJECT_ROOT . '/settings.php');
        
        $vars = get_defined_vars();
        foreach ($vars as $k => $v) {
            $this->$k = $v;
        }
        
        require_once(PROJECT_ROOT . '/settings.local.php');
        
        foreach ($config as $k => $v) {
            $this->$k = $v;
        }
        
        // set default charset
        if (empty($vars['charset']) && empty($config['charset']))
            $this->charset = 'UTF-8';
        
        ini_set('default_charset', $this->charset);
        
        define('SITE_CHARSET', $this->charset);
        
        //// Prepare modify URLs for SSL
        //$GLOBALS['cookiename_orig'] = $cookiename;
        $this->cookiename_orig = $this->cookiename;
        //// Fix URLs defined in the Settings if using SSL
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            list($this->boardurl, $this->facesurl, $this->imagesdir, $this->scripturl, $this->cgi) =
                str_replace('http://', 'https://',
                    array($this->boardurl, $this->facesurl, $this->imagesdir, $this->scripturl, $this->cgi)
                );
            
            if(!$this->ssl_by_default)
            {
                // It's SSL but not by default, use separate cookie for SSL
                $this->cookiename .= '_S';
            }

            $HSTSAge = $this->get('HSTS_Age', null);
            if($HSTSAge !== null && $this->get('HSTS', false))
                header("Strict-Transport-Security: max-age=$HSTSAge; includeSubDomains");
            
            $this->ssl = true;
        } else {
            $this->ssl = false;
        }
        
        $bu = parse_url($boardurl);
        if(!empty($bu['host'])){
            $this->board_hostname = $bu['host'];
        }
        $this->burl = $bu;
        
        // FIXME these all are temporary, should be removed in future
        $this->scripturl = "$boardurl/index.php";
        $this->boardurl_ssl = str_replace('http://', 'https://', $boardurl);
        

        $board = $this->request->param('board', '');
        $this->cgi = "{$this->scripturl}?board=$board";
    
        //// Include files from path relative to this
        //if (is_null(getConfig('project_root', null))) setConfig('project_root', '');
        //// Root URL for static files (not filesystem path)
        //if (is_null(getConfig('static_root', null))) setConfig('static_root', '');
        //if (isset($config['site_root'])) {
        //    define('SITE_ROOT', $config['site_root']);
        //} else {
        //    define('SITE_ROOT', '');
        //}
        
        if (isset($config['static_root'])) {
            define('STATIC_ROOT', $config['static_root']);
        } else {
            define('STATIC_ROOT', '/static');
        }
        
        if (empty($this->facesurl))
            $this->facesurl = STATIC_ROOT . "/img/YaBBImages/avatars";
        
        //if (!empty($config['debug'])) {
            //error_reporting(E_ALL);
            //ini_set('display_errors', 1);
        //}
    }
    
    private function _load_modsettings() {
        $this->modsettings = array();
        $request = $this->app->db->query("SELECT variable,value FROM {$this->app->db->prefix}settings WHERE (variable != 'agreement')");
        while ($row = $request->fetch())
            $this->modsettings[$row['variable']] = $row['value'];
        
        $this->modsettings['karmaMemberGroups'] = explode(',', $this->modsettings['karmaMemberGroups']);
    }
    
    private function get_modsettings($name) {
        if (is_null($this->modsettings)) {
            if ($this->db_ready) {
                $this->_load_modsettings();
            }
        }
        
        if (is_null($this->modsettings))
            return NULL;
        
        if (isset($this->modsettings[$name])) {
            return $this->modsettings[$name];
        } else {
            $name = strtolower($name);
            if (isset($this->modsettings[$name]))
                return $this->modsettings[$name];
            else
                return NULL;
        }
    }
    
    /**
     * Store something in modsettings
     * @param string $name variable name
     * @param string $value value to store
     * @return bool returns true on success or false
     */
    public function modSet($name, $value)
    {
        if (isset($this->modsettings[$name]) && $this->modsettings[$name] === $value)
            return true;
        
        $dbst = $this->app->db->prepare("INSERT INTO {$this->app->db->prefix}settings (variable, value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE variable = ?, value = ?");
        $dbst->execute(array($name, $value, $name, $value));
        $dbst = null;
        $this->modsettings[$name] = $value;
    }
    
    /**
     * Get value from settings or default value
     * @param string $name     setting name
     * @param mixed  $default  default value to return if undefined
     * @return mixed
     */
    
    public function get($name, $default=null) {
        if (empty($this->$name)) {
            $val = $this->get_modsettings($name);
            if (empty($val))
                return $default;
            else
                return $val;
        }
        else
            return $this->$name;
    }
    
    public function __get($name) {
        return $this->get($name);
    }
    
    
}
