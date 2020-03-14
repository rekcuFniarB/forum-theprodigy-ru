<?php

namespace Prodigy\Feed;

Class Service {
    private $app;
    private $service;
    private $router;
    
    public function __construct($router) {
        $this->app = $router->app();
        $this->service = $router->service();
        $this->router = $router;
    }
    public function build_menu () {
        //// Build menu
        $dbprefix = $this->app->db->db_prefix;
        $r = $this->app->db->query(
            "SELECT ID_BOARD, b.name AS boardname, b.moderators, b.ID_CAT, c.name AS catname, boardOrder, catOrder
              FROM {$dbprefix}boards b LEFT JOIN {$dbprefix}categories c ON b.ID_CAT = c.ID_CAT
              ORDER BY catOrder, boardOrder"
        );
        $boardscats = array();
        $catnames = array();
        $rows = $r->fetchAll();
        $r = null;
        foreach ($rows as $row) {
            if (!isset($boardscats[$row['ID_CAT']])) {
                $boardscats[$row['ID_CAT']] = array();
                $catnames[$row['ID_CAT']] = $row['catname'];
            }
            $r = null;
            $row['moderators'] = explode(',', $row['moderators']);
            $boardscats[$row['ID_CAT']][$row['ID_BOARD']] = $row;
        }
        $this->service->menu = $boardscats;
        $this->service->menuCatNames = $catnames;
    }
    
    public function httpError($code=404) {
        return $this->abort(null, null, $code);
    }
    
    //// Custom error page
    public function abort($title='', $msg='', $code=404) {
        $response = $this->router->response();
        
        if (empty($msg))
            if ($this->app->locale->isset("txt.errors.$code.msg"))
                $msg = $this->app->locale->get("txt.errors.$code.msg");
            else
                $msg = $this->app->locale->get("txt.errors.default.msg");
            
            if (empty($title))
                if ($this->app->locale->isset("txt.errors.$code.title"))
                    $title = $this->app->locale->get("txt.errors.$code.title");
                else
                    $title = $this->app->locale->get("txt.errors.default.title");
        
        $response->code($code);
        $this->service->title = $title;
        $this->service->message = $msg;
        $this->app->dumb->render('feed/error.php');
        $response->send();
        return $response;
    }
    
    //// Cut long strings
    public function cutstr($str='', $len=512) {
        if (!is_string($str)) {
            $this->service->cut = false;
            return $str;
        }
        $enc = $this->app->db->db_charset;
        $tmp_str = strip_tags(str_replace(array('<br />', '<br/>', '<br>'), "\n", $str));
        if (mb_strlen($tmp_str, $enc) > $len) {
            $this->service->cut = true;
            $tmp_str = mb_substr($tmp_str, 0, $len, $enc);
            $tmp_str = preg_replace("#\n+#", "<br>\n", $tmp_str);
            return $tmp_str;
        } else {
            $this->service->cut = false;
            return $str;
        }
    }
    
    //// Generate auto subject
    public function autosubject($subj, $body) {
        $enc = $this->app->db->db_charset;
        if (preg_match('#\[b\](.+?)\[/b\]#i', $body, $matches) > 0) {
            $autosubject = $matches[1];
        }
        elseif (preg_match('#\[size=\d\](.+?)\[/size\]#i', $body, $matches) > 0) {
            $autosubject = $matches[1];
        }
        elseif (preg_match('#\[color=.+?](.+?)\[/color\]#i', $body, $matches) > 0) {
            $autosubject = $matches[1];
        }
        else {
            $autosubject = $body;
        }

        $autosubject = $this->service->strip_bb_code(str_replace(array("\n", "\r", '<br>', '<br />', '<br/>'), ' ', $autosubject));
        //// strip urls
        $autosubject_tmp = preg_replace('#https?://\S+#', '', $autosubject);
        $autosubject = mb_substr($autosubject_tmp, 0, 40, $enc);
        $was_cut = false;
        if ($autosubject != $autosubject_tmp) {
            $was_cut = true;
            //// cut at last space to get rid of broken words
            $last_space = mb_strrpos($autosubject, ' ', 0, $enc);
            if ($last_space !== false && $last_space > 0) {
                $autosubject = mb_substr($autosubject, 0, $last_space, $enc);
            }
        }
        $autosubject = trim($autosubject);
        if ($autosubject != '' && $was_cut)
            $autosubject .= '...';
        if ($autosubject == '') {
            return '';
        }
        $subj_cut = mb_substr($subj, 0, 40, $enc);
        if ($subj_cut != $subj) {
            $last_space = mb_strrpos($subj_cut, ' ', 0, $enc);
            if ($last_space !== false && $last_space > 0) {
                $subj_cut = mb_substr($subj_cut, 0, $last_space, $enc);
            }
        }
        return array($subj_cut, $autosubject);
    }
    
    //// remove bb code and links and return plain text
    public function plainText($str, $cut=0) {
        $str = $this->service->strip_bb_code(str_replace(array("\n", "\r", '<br>', '<br />', '<br/>'), ' ', $str));
        //// strip urls
        $str = preg_replace('#https?://\S+#', '', $str);
        if ($cut > 0) {
            $str = mb_substr($str, 0, $cut, $this->app->db->db_charset);
            $last_space = mb_strrpos($str, ' ', 0, $this->app->db->db_charset);
            if ($last_space !== false && $last_space > 0) {
                $str = mb_substr($str, 0, $last_space, $this->app->db->db_charset);
                if ($str != '')
                    $str .= '...';
            }
        }
        $str = trim($str);
        return $str;
    } // plainText();
    
    //// Is annotation edit allowed?
    public function editAllowed($idcat=null, $idboard=null) {
        if ($this->app->user->guest || $this->app->user->id <= 0)
            return false;
        
        if ($this->app->user->isStaff())
            return true;
        
        if (!is_null($idcat) && !is_null($idboard)) {
            if (!isset ($this->service->menu))
                $this->build_menu();
            
            if (in_array($this->app->user->name, $this->service->menu[$idcat][$idboard]['moderators'])) {
                return true;
            }
        }
        return false;
    } // editAllowed()
    
    public function sessionRequire() {
        $sid = $this->service->startSession();
        if (empty($sid)) {
            if (!isset($_SESSION)) {
                session_start();
            }
            $sid = session_id();
            $this->service->session_id = $sid;
            $this->service->sessid = $sid;
        }
        return $sid;
    }
    
    /**
     * htmlspecialchars() function wrapper
     * @param string $str     input string
     * @param string $charset charset
     * @param int    $mode    mode
     * @returns      string
     */
    public function escape($str, $charset = null, $mode = ENT_QUOTES) {
        if (is_null($charset)) {
            $charset = $this->app->db->db_charset;
        }
        return htmlspecialchars($str, $mode, $charset, false);
    }
    
    /**
     * Prepare strings for RSS
     * @param string $str input string
     * @returns string
     */
    public function string4rss($str) {
        $enc = $this->app->db->db_charset;
        $str = $this->escape(
            html_entity_decode(
                mb_convert_encoding($str, 'UTF-8', $enc),
            ENT_QUOTES, 'UTF-8'),
        'UTF-8', ENT_COMPAT);
        return $str;
    }
    
} // Helpers class
