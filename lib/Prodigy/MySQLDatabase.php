<?php

namespace Prodigy;

class MySQLDatabase extends \Mysqli {
    private $db_server;
    private $db_user;
    private $db_passwd;
    private $db_name;
    public $db_prefix;
    public $prefix;
    private static $_instance = null;
    private $escaped_http_request;
    private $autoescape;
    private $magic_quotes;
    
    public function __construct($router) {
        $this->router = $router;
        $this->app = $router->app();
        $this->db_server = $this->app->conf->db_server;
        $this->db_user = $this->app->conf->db_user;
        $this->db_passwd = $this->app->conf->db_passwd;
        $this->db_name = $this->app->conf->db_name;
        $this->db_prefix = $this->app->conf->db_prefix;
        $this->prefix = $this->app->conf->db_prefix;
        $this->db_charset = strtolower(str_replace('-', '', $this->app->conf->get('charset', 'UTF-8')));
        $this->db_driver = 'mysqli';
        $this->escaped_http_request = null;
        $this->autoescape = $this->app->conf->get('sql_autoescape', false);
        $this->magic_quotes = get_magic_quotes_gpc();
    }
    
    /**
     * Connects to the mysql database.
     */
    public function connect($host = NULL, $user = NULL, $password = NULL, $database = NULL, $port = NULL, $socket = NULL) {
        if (is_null(self::$_instance)) {
            parent::__construct($this->db_server, $this->db_user, $this->db_passwd, $this->db_name);
            if ($this->connect_errno) {
                die($this->error);
            }
            if (!$this->set_charset($this->db_charset)) {
                printf("DB codepage error: %s\n", $db->error);
                exit();
            }
            self::$_instance = $this;
        }
        return true;
    }
    
    static public function getInstance() {
        return self::$_instance;
    }
    
    /**
     * Returns the database prefix.
     */
    public function getPrefix() {
        return $this->db_prefix;
    }
    
    public function escape_string($string) {
        if($this->magic_quotes)
            return $this->real_escape_string(stripslashes($string));
        else
            return $this->real_escape_string($string);
    }
    
    private function escape_http_request($data = array()) {
        foreach($data as $k => $v) {
            if (is_array($v)) {
                $this->escape_http_request($v);
            } else {
                if (!empty($v)) {
                    $esc = $this->real_escape_string($v);
                    if ($v != $esc) {
                        $this->escaped_http_request[$k] = array($v, $esc);
                    }
                }
            }
        }
    } // escape_http_request()
    
    /**
     * Run SQL query
     * @param string $query     SQL query string
     * @param bool $autoescape  auto check for non escaped input
     * @return request object
     */
    public function query($query = '', $autoescape = true, $resultmode = MYSQLI_STORE_RESULT) {
        if ($this->autoescape && $autoescape){
            //// Search for unescaped parts in query string and escape
            if (is_null($this->escaped_http_request)) {
                $this->escaped_http_request = array();
                $this->escape_http_request(array_merge($_GET, $_POST, $_COOKIE));
            }
            // Crazy method check for non escaped input appears in SQL string
            $_query = str_replace('\\\\', '', $query);
            foreach ($this->escaped_http_request as $k => $v) {
                if  (strpos($_query, $v[0]) !== false) {
                    notifyAdminsLater('Probably SQL injection attempt detected', "Key:\n[code]{$k}[/code]\nString [code]{$v[0]}[/code]\nin request\n[code]{$query}[/code]\nRequest URI:\n[code]{$_SERVER['REQUEST_URI']}[/code]\nUser: {$GLOBALS['username']}, IP: [url=https://ipinfo.io/{$_SERVER['REMOTE_ADDR']}]{$_SERVER['REMOTE_ADDR']}[/url]");
                    if (mb_strlen($v[0], $this->db_charset) > 15)
                        $query = str_replace($v[0], $v[1], $query);
                    else
                        $query = str_replace("'$v[0]", "'$v[1]'", $query);
                }
            }
        } // if autoescape
        // run SQL query
        $request =  parent::query($query, $resultmode);
        if (!$request)
            throw new Errors\MySQLException($this->error, $this->errno);
        
        return $request;
    }
    
    /**
     * mysqli->prepare() wrapper
     * @param string $query SQL query string
     * @return object statement
     */
    public function prepare($query)
    {
        $stmt = parent::prepare($query);
        if ($stmt === false)
            throw new Errors\MySQLException($this->error, $this->errno);
        else
            return $stmt;
    }
    
    public function show_error($file='none', $line='none') {
        error_log("__SQL_ERROR__: [{$this->errno}] {$this->error} File: $file at line $line");
        return; // FIXME
        
        // log the error
        if ($this->app->conf->enableErrorLogging == 1) {
            $request = $this->router->request();
            $REMOTE_ADDR = $this->escape_string($request->ip());
            $REQUEST_URI = $this->escape_string($request->uri());
            $storemsg = $this->escape_string("{$this->app->locale->txt[1001]}: {$this->error}\r\n{$this->app->locale->txt[1003]}: $file\r\n{$this->app->locale->txt[1004]}: $line");
            $r = $this->query("
                INSERT INTO {$this->prefix}log_errors (logTime, ID_MEMBER, IP, url, message)
                VALUES ('" . time() . "', {$this->app->user->id}, '$REMOTE_ADDR', '$REQUEST_URI', '$storemsg')", false);
        }
        // show an error message
        $error = ($this->app->conf->debug || $this->app->user->accessLevel() > 2) ?  "[{$this->errno}] {$this->error}<br>{$this->app->locale->txt[1003]}: $file<br>{$this->app->locale->txt[1004]}: $line" : $this->app->locale->txt[1002];
        $this->app->errors->abort($txt[1001], $error, 500);
    }

}
?>
