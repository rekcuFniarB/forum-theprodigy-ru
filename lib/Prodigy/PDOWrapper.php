<?php

namespace Prodigy;
use PDO;

class PDOWrapper extends \PDO {
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
        $this->db_driver = $this->app->conf->get('db_driver', 'mysql');
        $this->escaped_http_request = null;
        $this->autoescape = $this->app->conf->get('sql_autoescape', false);
        $this->magic_quotes = get_magic_quotes_gpc();
    }
    
    /**
     * Connects to the mysql database.
     */
    public function connect($host = NULL, $user = NULL, $password = NULL, $database = NULL, $port = NULL, $socket = NULL) {
        if ($host === null)
            $host = $this->db_server;
        if ($user === null)
            $user = $this->db_user;
        if ($password === null)
            $password = $this->db_passwd;
        if ($database === null)
            $database = $this->db_name;
        
        if (is_null(self::$_instance)) {
            parent::__construct("{$this->db_driver}:host=$host;dbname=$database;charset={$this->db_charset}", $user, $password,
                array(
                    // Persistent connections, default FALSE
                    //PDO::ATTR_PERSISTENT         => true,
                    // Emulated prepared statements, default TRUE
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    // Error mode, default  PDO::ERRMODE_SILENT
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    // default FALSE
                    PDO::MYSQL_ATTR_FOUND_ROWS   => true,
                    // default PDO::FETCH_BOTH
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // default TRUE
                    //PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => FALSE
                )
            );
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
    
    /**
     * build placeholders string from array (example: "?, ?, ?")
     * @param array $data Input data
     * @param bool $assoc if should return assoc placeholders
     * @param bool $assignment_list whether return format should be in the
     *             form of assignment list or not.
     * @return string
     */
    public function build_placeholders(array $data, bool $assoc = false, bool $assignment_list = true)
    {
        if (!$assoc)
            return str_repeat('?,', count($data) - 1) . '?';
        else
        {
            $keys = array_keys($data);
            $result = array_map(
                function($key) use ($assignment_list)
                {
                    if ($assignment_list)
                        // Result should be in the form of "foo = :foo, bar = :bar, baz = :baz"
                        return "$key = :$key";
                    else
                        // Result should be in the form of ":foo, :bar, :baz"
                        return ":$key";
                },
                $keys
            );
            
            return implode(', ', $result);
        } // if assoc
    }
}
?>
