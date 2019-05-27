<?php

namespace Prodigy\Respond;

abstract class Respond {
    public $app;
    public $service;
    public $request;
    public $response;
    public $router;
    private static $layout_ready;
    private static $count = 0;
    public $instance_id;
    protected $css;
    protected $js;

    public function __construct($router) {
        $this->app = $router->app();
        $this->service = $router->service();
        $this->request = $router->request();
        $this->response = $router->response();
        $this->router = $router;
        if(!$this->instance_id) $this->instance_id = base_convert(microtime(true)*10000, 10, 36);
        //self::$layout_ready = false;
        self::$count ++;
        error_log("__CONSTRUCT__: RESPOND ". get_called_class() . ' count: ' . self::$count);
        
        if (!$this->service->ajax)
            $this->WriteLog();
        
        if(!isset($this->app->respond))
            $this->app->respond = $this;
        
        $this->css = array();
        $this->js = array();
    }
    
//     public function __invoke($name) {
//         // doesn't invoked :(
//         $this->app->errors->log("__DEBUG__: INVOKE $name");
//     }
//     
//     public function __call($name, $args) {
//         $this->app->errors->log("__DEBUG__: CALL $name," . count($args));
//         $file = __DIR__ . '/' . $name;
//         if (!file_exists($file)) {
//             $this->app->errors->log("__DEBUG__: RESPOND NOT EXISTS: $file");
//         } else {
//             require_once($file);
//         }
//     }
//     
//     public function respond($name) {
//         // Dynamic responce code load
//         $file = __DIR__ . '/' . $name;
//         if (!file_exists($file)) {
//             $this->app->errors->log("__DEBUG__: RESPOND NOT EXISTS: $file");
//         } else {
//             require_once($file);
//         }
//     }
    
    public function layout_ready() {
        return self::$layout_ready;
    }
    
    public function x10() {
        self::$count = 10;
    }
    
    /**
     * Custom render method wrapper
     */
    public function render($view, array $data = array()) {
        //if (!self::$layout_ready)
            //$this->prepare_layout();
        if ($this->service->layout() === null)
            $this->prepare_layout();
        $this->service->render($view, $data);
        //return $this->response->send();
        //$this->router->skipRemaining();
        return $this->response;
    } // render()
    
    /**
     * Prepare data for base layout
     */
    public function prepare_layout() {
        // this was template_header() from subs
        $service = $this->service;
        $ID_MEMBER = $this->app->user->id;
        // print stuff to prevent cacheing of pages
        $this->response->header('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        $this->response->header('Last-Modified', gmdate("D, d M Y H:i:s") . ' GMT');
        
        $SSL = $this->request->isSecure();
        
        if ($SSL && $this->app->conf->CSPUIR) {
            $this->response->header('Content-Security-Policy', 'upgrade-insecure-requests');
        }

        // FIXME this maybe should be placed not here
        if ($SSL && $this->app->conf->HSTS) {
            $this->response->header("Strict-Transport-Security", "max-age={$this->app->conf->HSTS_Age}; includeSubDomains");
        }
        
        if ($this->app->conf->disableCaching == 1) {
            $this->response->header("Cache-Control", "no-cache, must-revalidate");
            $this->response->header("Pragma", "no-cache");
        }
        
        // Use Sphinx search engine if enabled
        if (empty($this->app->conf->sphinx) || !is_array($this->app->conf->sphinx) || !$this->app->conf->sphinx['enabled']) {
            $service->search_action = 'search';
        } else {
            $service->search_action = 'newsearch';
        }
        
        $menusep = $this->app->locale->menusep;
        $img = $this->app->locale->img;
        $locale = $this->app->locale;
        $cgi = SITE_ROOT . '?board=none';
        $scripturl = $this->app->conf->scripturl;
        $db_prefix = $this->app->db->prefix;

        if ($this->app->conf->enable_news == 1) {
            $newsmessages = explode("\n", str_replace("\r", '', trim(stripslashes($this->app->conf->news))));
            // if we've disabled the fader....
            srand(time());
            // then some bum decided we should display a random news item
            $newstring = '';
            if (sizeof($newsmessages) == 1)
                $newstring = $newsmessages[0];
            elseif (sizeof($newsmessages) > 1)
                $newstring = $newsmessages[floor(rand(0,(sizeof($newsmessages) - 1)))];
            $yynews = '<b>' . $locale->txt[102] . ':</b> ' . $service->DoUBBC($newstring);
        }
        
        $service->yyimbar = false;
        
        if ($this->app->user->name != 'Guest') {
            $service->imcount = $this->app->im->getCount();

            $dbst = $this->app->db->query("SELECT SUM(unreadComments) as numUnreadComments, SUM(otherComments) + SUM(subscribedComments) as numOtherComments FROM {$db_prefix}log_topics WHERE ID_MEMBER=$ID_MEMBER");
            $row = $dbst->fetch();
            $numComments = $row['numUnreadComments'] + $row['numOtherComments'];
            $dbst = null;
            
            $service->numComments = $numComments;
            $service->numUnreadComments = $row['numUnreadComments'];
            $service->numOtherComments = $row['numOtherComments'];
            
            if ($service->imcount[1] > 0 || $numComments > 0)
                $service->yyimbar = true;
        } // if not guest

        //ob_start();

        $skinname = $this->app->user->skin;
        
        if (!is_dir(PROJECT_ROOT . '/templates/skins/' . $skinname))
            $skinname = $this->app->conf->default_skin;
        
        $templateFile = PROJECT_ROOT . '/templates/skins/' . $skinname . '/template.php';
        
        if (!file_exists($templateFile))
            $templateFile = PROJECT_ROOT . '/templates/skins/' . $skinname . '/template.html';
        
        $service->layout($templateFile);
        
        $service->_include_js = $this->js;
        $service->_include_css = $this->css;
        
        $service->yyboardname = $this->app->conf->mbname;
        
        $time = $this->app->user->timeOffset;
        
        $service->yytime = $this->app->subs->lang_strftime(time() + (($this->app->conf->timeoffset + $time) * 3600));
        
        // display their username if they haven't set their real name yet.
        $tmp = ($this->app->user->realname == '' ? $this->app->user->name : $this->app->user->realname);
        
        $service->yyuname = ($this->app->user->name == 'Guest' ? "{$locale->txt[248]} <b>{$locale->txt[28]}</b>. {$locale->txt[249]} <a href=\"". SITE_ROOT ."/login/\">{$locale->txt[34]}</a> {$locale->txt[377]} <a href=\"". SITE_ROOT ."/register/\">{$locale->txt[97]}</a>." : "{$locale->txt[247]} <b>$tmp</b><br /> ");
        
        $yycopyin = 0;
        
        $yyVBStyleLogin = '<br />';
        if ($this->app->conf->enableVBStyleLogin == '1' && $this->app->user->name == 'Guest')
            $yyVBStyleLogin = '
                <form action="' . $cgi . ';action=login2" method="post"><br />
                    <input type="text" name="user" size="7" />
                    <input type="password" name="passwrd" size="7" />
                    <select name="cookielength">
                        <option value="60">' . $txt['yse53'] . '</option>
                        <option value="1440">' . $txt['yse47'] . '</option>
                        <option value="10080">' . $txt['yse48'] . '</option>
                        <option value="302400">' . $txt['yse49'] . '</option>
                        <option value="' . $txt['yse50'] . '" selected="selected">' . $txt['yse50'] . '</option>
                    </select>
                    <input type="submit" value="' . $txt[34] .'" /><br />
                    ' . $txt['yse52'] . '
                </form>';
        // ---- ADDED by dig7er 20.03.2006
        $service->yyVBStyleLogin = $yyVBStyleLogin;

        $service->user = $this->app->user;
        $service->e_username = urlencode($this->app->user->name);
        
       if ($this->app->user->name != "Guest") {
            $service->dbTime = $this->getCurrentDBTimeString();
       } else {
           $service->dbTime = "";
       }
        
        $service->mobileMode = $this->app->user->mobileMode;
        //$service->yyim = $yyim;
        //$service->yymenu = $yymenu;
        
        $service->SSL = $SSL;

        // Prepare Infopane {

        $curDay = (int) date('d');
        $curMonth = (int) date('m');
        
        $infopane = '';
        
        // Show the new year congratulations pane on the new years eve.
        if (($curMonth == 12 and $curDay >= 24) or ($curMonth == 1 and $curDay < 3)) {
            $receivers = array();
            if (empty($board) and empty($action)) {
                // Get receiver member names array
                $dbst = $this->app->db->query("
                    SELECT m.memberName AS identity
                    FROM {$db_prefix}log_online AS lo
                    JOIN {$db_prefix}members AS m ON (m.ID_MEMBER=lo.identity)
                    LEFT JOIN {$db_prefix}extended_member_settings as ems ON (m.ID_MEMBER = ems.ID_MEMBER)
                    WHERE ems.disable_congratulations IS NULL OR ems.disable_congratulations = 0");
                while ($tmp = $dbst->fetchColumn())
                    if ($tmp != $this->app->user->name)
                        $receivers[] = $tmp;
                $dbst = null;
                
                // Get number of congratulations sent
                $numCongratulations = $this->app->db->query("
                    SELECT COUNT(*) FROM {$db_prefix}log_congratulations")->fetchColumn();
                
                // Get receive congratulations profile setting
                $disableCongratulations = $this->app->db->query("
                    SELECT disable_congratulations FROM {$db_prefix}extended_member_settings WHERE ID_MEMBER = {$ID_MEMBER}")->fetchColumn();
                
                $infopane = '<a href="'.$cgi.';action=imsend;form_type=ny;to='.implode(",",$receivers).';form_subject=С%20Новым%20Годом!" style="font-size: 20pt;" title="Отправленных поздравлений">'.$numCongratulations.'</a> x <a href="'.$cgi.';action=imsend;form_type=ny;to='.implode(",",$receivers).';form_subject=С%20Новым%20Годом!"><img src="YaBBImages/new-year-letter.png" title="Поздравить с Новым Годом форумчан, которые находятся в данную минуту на Форуме!" with="48" height="48" border="0" /></a>'.($this->app->user->name == 'Guest' ? '' : ' <input type="checkbox" name="disable_congratulations" title="Отключить/Включить получение поздравлений" onclick="Forum.Profile.toggleCongratulations(this);"'.($disableCongratulations!=1?' checked="checked"':'').'/>');
            }
            $this->service->infopane = $infopane;
        }
        
        // } prepare Infopane

        // { prepare footer
        
        if ($this->app->user->isMobile || $this->app->user->mobileMode) {
            // Display button to switch dislpay mobile/desktop mode
            $mobile_onoff = $this->app->user->mobileMode ? 'off' : 'on';
            $query_params_array = array_merge($_GET, array('mobilemode' => $mobile_onoff));
            $query_params = http_build_query($query_params_array);
            $this->service->mobileSwitch = '<div class="display-mode-switch"><a href="' . $this->app->conf->scripturl . '?' . $query_params . '" class="windowbg2" rel="nofollow">' . $this->app->locale->txt["mobile-mode-$mobile_onoff"] . '</a></div><br>' . PHP_EOL;
        } else {
            $this->service->mobileSwitch = '';
        }

        $this->service->yycopyright = strtr($this->app->locale->yycopyright, array('2003' => '2004'));
        
        $sessioninfo = array(
            'username' => $this->app->user->name,
            'realname' => $this->app->user->realname,
            'status' => $this->app->user->group,
            'userid' => $this->app->user->id,
            'sid' => $this->app->session->id,
            'static_root' => STATIC_ROOT,
            'site_root' => SITE_ROOT
        );
        
        if ($this->app->conf->debug && !empty($this->app->conf->disable_ajax_updates)) {
            $sessioninfo['disableajaxupdates'] = true;
        }
        
        foreach ($sessioninfo as &$v){
            $v = mb_convert_encoding($v, 'utf-8', $this->app->conf->charset);
        }
        
        $this->service->sessinf_json = json_encode($sessioninfo, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        
        $cookies = $this->request->cookies();
        
        // SNOWFLAKES MOD
        if ($this->app->conf->is_xmas && !$service->mobileMode && $this->app->conf->snowing_enabled) {
            if (!$cookies->get('disableSnowflakes2011', null) && !$cookies->get('flakemode', null)) {
                if (!headers_sent()) {
                    $flakeCookieExpireDate = time() + 3*31*24*60*60;
                    $this->response->cookie("flakemode", 1, $flakeCookieExpireDate);
                }
                //$_COOKIE['flakemode'] = 1;
            }
            $this->service->snow = true;
        } else {
            $this->service->snow = false;
        }

        // } prepare footer
        
        // Store locale object reference in layout for easy access
        $this->service->locale = $this->app->locale;
        $this->service->conf = $this->app->conf;
        $this->service->subs = $this->app->subs;
        $this->service->menusep = $menusep;
        $this->service->skinname = $skinname;
        $this->service->sessionid = $this->app->session->id;
        $this->service->color = $this->app->conf->color;
        $this->service->imagesdir = $this->app->conf->imagesdir;
        
        self::$layout_ready = true;

    } // prepare_layout()
    
    /**
     * Add css to main template
     * @param string $path css path
     */
    protected function addCSS($path)
    {
        $this->css[] = STATIC_ROOT . '/css/' . $path;
    }
    
    /**
     * Add js to main template
     * @param string $path css path
     */
    protected function addJS($path)
    {
        $this->css[] = STATIC_ROOT . '/js/' . $path;
    }
    
    public function prepareJumpToForm($currentboard) {
        $db_prefix = $this->app->db->prefix;
        $dbst = $this->app->db->prepare("
            SELECT name,ID_CAT
            FROM {$db_prefix}categories
            WHERE (FIND_IN_SET(?, memberGroups) != 0 OR memberGroups='' OR ? = 'Administrator' OR ? = 'Global Moderator')
            ORDER BY catOrder");
        $dbst->execute(array($this->app->user->group, $this->app->user->group, $this->app->user->group));
        $cats = array();
        while ($row = $rq->fetch())
        {
            $cats[$row['ID_CAT']] = array ('name' => $row['name'], 'boards' => array());
            $dbst2 = $this->app->db->query("SELECT name,ID_BOARD FROM {$db_prefix}boards WHERE ID_CAT={$row['ID_CAT']} ORDER BY boardOrder");
            while ($row2 = $dbst2->fetch())
            {
                $cats[$row['ID_CAT']]['boards'][$row2['ID_BOARD']] = array('name' => $row2['name']);
                if ($row2['ID_BOARD'] == $currentboard)
                    $cats[$row['ID_CAT']]['boards'][$row2['ID_BOARD']]['current'] = true;
                else
                    $cats[$row['ID_CAT']]['boards'][$row2['ID_BOARD']]['current'] = false;
            }
            $dbst2 = null;
        }
        $dbst = null;
        return $cats;
    } // prepareJumpToForm()
    
    public function WriteLog() {
        $identity = $this->app->user->id;
        $logTime = time();
        $db_prefix = $this->app->db->prefix;
        $server = $this->request->server();
        $USER_AGENT = $server->get('HTTP_USER_AGENT');
        $REMOTE_ADDR = $server->get('REMOTE_ADDR');
        
        $board = $this->request->paramsNamed()->get('board');
        if(empty($board))
            $board = -1;
        
        if ($identity == -1 && !empty($USER_AGENT)) {
            if (stripos($USER_AGENT, 'robot') !== false || stripos($USER_AGENT, 'http') !== false || stripos($USER_AGENT, 'crawl') !== false) {
                // Prevent duplicate logging of same crawlers coming from different IPs.
                // We are using crc32 of user agent instead of IP.
                $identity = crc32($USER_AGENT);
                if($this->app->conf->dont_log_bots)
                {
                    // Don't log bots
                    return false;
                }
            }
        }

        if ($identity == -1 && !empty($REMOTE_ADDR) && ip2long($REMOTE_ADDR) != -1)
        {
            $this->app->db->prepare ("
                DELETE FROM {$db_prefix}log_online
                WHERE logTime < ?
                OR identity=IFNULL(INET_ATON(?), -1)")->execute(array($logTime - 900, $REMOTE_ADDR));
            
            $dbst = $this->app->db->prepare("
                REPLACE INTO {$db_prefix}log_online
                  (identity, logTime, ID_BOARD)
                VALUES (IFNULL(INET_ATON(?), -1), ?, ?)")
                ->execute(array($REMOTE_ADDR, $logTime, $board));
        }
        else
        {
            if (!is_numeric($identity))
                $identity = -1;
            $this->app->db->query("
                DELETE FROM {$db_prefix}log_online
                WHERE logTime < " . ($logTime - 900) . "
                OR identity=$identity");
            $this->app->db->prepare("
                REPLACE INTO {$db_prefix}log_online
                (identity, logTime, ID_BOARD)
                VALUES (?,?,?)")->
                    execute(array($identity, $logTime, $board));
        }
    } // WriteLog()
    
    /**
     * Makes an SQL query for the current DB time.
     * @returns the current DB time as string with format 'YYYY-MM-DD HH:MM:SS'.
     */
    public function getCurrentDBTimeString()
    {
        $result = null;
        $dbData = $this->app->db->query("SELECT NOW()")->fetchColumn();
        if ($dbData)
            $result = $dbData;
        return $result;
    }
    
    public function getBoardViewersList($board) {
        // load the number of users online right now
        $db_prefix = $this->app->db->prefix;
        $guests = 0;
        $tmpusers = array();
        $logTime = time();
        $rq = $this->app->db->query("
            SELECT m.memberName AS identity,  m.realName,  m.memberGroup
            FROM {$db_prefix}log_online AS lo
            LEFT JOIN {$db_prefix}members AS m ON (m.ID_MEMBER=lo.identity)
            WHERE ID_BOARD = {$board}
            ORDER BY logTime DESC", false);
            while ($tmp = $rq->fetch_assoc())
            {
                if ($tmp['realName'] != '')
                    $tmpusers[] = array(
                        'identity' => $tmp['identity'],
                        'realname' => $tmp['realName'], 
                        'membergroup' => $tmp['memberGroup']
                    );
                else
                    $guests++;
            }
            //change here
            //$guestStr = self::buildGuestString($guests);
            //if (count($tmpusers) > 0 and strlen($guestStr) > 0)
            //$guestStr = " и {$guestStr}";
            error_log("__DEBUG__: Board Wiewers $guests");
            //return '<font size="1"><b>Сейчас в разделе</b> '. implode(', ', $tmpusers) . $guestStr . '</font>';
        return array($tmpusers, $guests);
    } // getBoardViewersList()
    
    /**
     * Redirect relative to SITE_ROOT
     * @param string $path  redirect path, should begin with /
     * @return \Kkein\AbstractResponse
     */
    public function redirect($path) {
        // Note: $this->service->siteurl is set in the index.php
        $redirect_url = "{$this->service->siteurl}$path";
        error_log("__REDIRECT__: $redirect_url");
        return $this->response->redirect("$redirect_url");
    } // redirect()
    
    /**
     * Custom setcookie wrapper.
     */
    public function cookie(
        $key,
        $value = '',
        $expiry = null,
        $path = '/',
        $domain = null,
        $secure = false,
        $httponly = false)
    {
        if ($value === null)
            // unset cookie if value is null
            $expiry = 0;
        
        if ($this->app->conf->localCookies == 1 && $path === null && $domain === null)
        {
            $path = SITE_ROOT . '/';
            $domain = $this->app->conf->board_hostname;
        }
        
        if ($path === null)
            $path = '/';
        
        return $this->response->cookie($key, $value, $expiry, $path, $domain, $secure, $httponly);
    }
    
    /**
     * Display error message
     * Just a shortcut for \Prodigy\Errors\errors->abort()
     * @param string $msg error message
     * @param int $code   http response code
     * @return response
     */
    public function error($msg, $code = 404)
    {
        if (isset($this->app->locale->txt[$msg]))
            $error_message = $this->app->locale->txt[$msg];
        else
            $error_message= $msg;
        return $this->app->errors->abort($this->app->locale->txt[106], $error_message, $code);
    }
    
    public function ajax_response($data, $format = 'json', $charset = null)
    {
        $this->service->layout(null);

        if ($charset === null) {
            $charset = $this->app->conf->charset;
        }
        
        if($charset == 'cp1251') $charset = 'windows-1251';
        
        switch ($format) {
            case "json":
                $content_type = 'application/json';
                break;
            case "jsonp":
                $content_type = 'application/javascript';
                break;
            case "text":
                $content_type = 'text/plain';
                break;
            case "html":
                $content_type = 'text/html';
                break;
            default:
                $content_type = $format;
        }
        $content_type .= "; charset=$charset";
        
        $this->response->header('Content-Type', $content_type);
        $this->response->body($data);
        return $this->response;
    } // ajax_response()
    
}

?>
