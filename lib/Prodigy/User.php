<?php

namespace Prodigy;

class User {
    private $router;
    private $app;
    private $service;
    private $request;
    //private $response;
    private $_settings;
    private $cache;
    private $_membergroups;
    private $_ignores;
    
    public $sessionConfig;

    public function __construct($router) {
        $this->router = $router;
        $this->app = $router->app();
        $this->service = $router->service();
        $this->request = $router->request();
        //$this->response = $router->response();
        $this->cache = array();
        
        // Load cookie {
        $cookies = $this->request->cookies();
        $authcookie = $cookies->get($this->app->conf->cookiename);
        if (!empty($authcookie)) {
            list($username, $password) = @unserialize(stripslashes($authcookie));
            $username = ($username != '') ? $username : 'Guest';
        } else {
            $password = '';
            $username = 'Guest';
        }
        error_log("__AUTH___: $authcookie, $username, $password");
        
        if ($username == 'Guest' || empty($this->sessionConfig) || !is_array($this->sessionConfig)){
            $this->sessionConfig = array();
        } else {
            $this->sessionConfig = $this->app->session->config;
        }
        
        $cfg = $cookies->get('cfg');
        if (!empty($cfg)){
            $cfg = json_decode(base64_decode($cfg), TRUE);
            if (is_array($cfg)){
                foreach ($cfg as $v){
                    if (!empty($v['name']) && !empty($v['value'])){
                        $this->sessionConfig[$v['name']] = $v['value'];
                    }
                }
            }
        }
        // } end load cookie
    
        $settings = array();
        $action = $this->request->param('action');
        
        // load user settings {
        if ($username != 'Guest') {
            /* Only load this stuff if the username isn't Guest */
            $username = (int) $username;
            $request = $this->app->db->query("SELECT 
                passwd,
                realName,
                emailAddress,
                websiteTitle,
                websiteUrl,
                signature,
                posts,
                memberGroup,
                ICQ,
                AIM,
                YIM,
                gender,
                personalText,
                avatar,
                dateRegistered,
                location,
                birthdate,
                timeFormat,
                timeOffset,
                hideEmail,
                ID_MEMBER,
                memberName,
                MSN,
                lngfile,
                skin,
                showComments,
                closeCommentsByDefault,
                collapsedBoardIDs,
                collapsedCategories,
                QuickReply
            FROM {$this->app->db->prefix}members WHERE ID_MEMBER='$username' LIMIT 1;", false);
            /* If we found the user... */
            error_log('__DEBUG__: User class after db request');
            if ($request->num_rows != 0) {
                /* Initialize the settings array */
                $settings = $request->fetch_array();
                /* compare a crypted version of the password in the database
                   with the password stored in the cookie.  Yes, the password
                    stored in the cookie is doubly encrypted
                */
                error_log('__DEBUG__: User class before init');
                
                $spass = $this->app->subs->md5_hmac($settings['passwd'], $this->app->conf->pwseed);
                
                if ($spass != $password && $action != 'logout')
                    $this->name = null;
                else {
                    error_log('__DEBUG__: User class init');
                    $this->name = $settings['memberName'];
                    $this->realname = (empty($settings['realName']) ? $username : $settings['realName']);
                    //$this->realNames[$username] = $realname;
                    $this->email = $settings['emailAddress'];
                    $this->id = $settings['ID_MEMBER'];
                    $this->group = $settings['memberGroup'];
                    $this->guest = false;
                    
                    
                    $this->collapsedBoards = $settings['collapsedBoardIDs'] == "" ? array() : explode(",", $settings['collapsedBoardIDs']);
                    $this->collapsedCategories = $settings['collapsedCategories'] == "" ? array() : explode(",", $settings['collapsedCategories']);

                    // Secret agents (hidden global moderators)
                    if (in_array($this->id, $this->app->conf->hiddenModerators) && $action != 'profile2') {
                        $this->group = 'Global Moderator';
                    }
                    
                    if(strpos($settings['passwd'], ':') !== false)
                        $this->banned = true;
                    else
                        $this->banned = false;
                    
                    // setup skin
                    if (empty($settings['skin'])) {
                        $this->skin = $this->app->conf->get('default_skin', 'default');
                    } else {
                        $this->skin = $settings['skin'];
                    }
                    
                    // Use alternative skin for current session if defined
                    if (!empty($this->sessionConfig['force_skin']) && $this->sessionConfig['force_skin'] != 'null') {
                        $this->skin = $this->sessionConfig['force_skin'];
                    }
                    
                    
                    // Localization setup
                    if ($this->app->conf->userLanguage == 1) {
                        if (empty($settings['lngfile'])) {
                            $this->app->locale->set_locale($this->app->conf->language);
                        } else {
                            $this->app->locale->set_locale($settings['lngfile']);
                        }
                    } else {
                        $this->app->locale->set_locale($this->app->conf->language);
                    }
                    
                    $this->_settings = $settings;
                    
                    // Ignore list placeholder ($settings[29])
                    //$settings[] = null;
                } // password OK
            } // if user found
            /* Otherwise clear everything */
            else
                $this->name = null;
        } // if not Guest
        
        error_log("__DEBUG__: User class middle $username");
        
        /* If the user is a guest, initialize all the critial user settings */
        if (empty($username) || $username == 'Guest' || empty($this->name)) {
            $this->name = 'Guest';
            $this->guest = true;
            $this->group = null;
            $this->password = '';
            $this->_settings = array();
            $this->realname = $this->app->locale->txt[28];
            $this->email = '';
            $this->id = '-1';
            $this->timeOffset = 0;
            $this->banned = false;
            $this->posts = -1;
            
            $this->skin = $this->app->conf->get('default_skin', 'default');
            $this->collapsedBoards = array();
            $this->collapsedCategories = array();
            $this->app->locale->set_locale($this->app->conf->language);
        } // If Guest
        
        $SERVER = $this->request->server();
        $USER_AGENT = $SERVER->get('HTTP_USER_AGENT');
        $GET = $this->request->paramsGet();
        
        if (stristr($USER_AGENT,'mobi') ||  stristr($USER_AGENT,'mini') ||  stristr($USER_AGENT,'android') || stristr($USER_AGENT,'tab') || stristr($USER_AGENT,'pad')) {
            $this->isMobile = true;
        } else {
            $this->isMobile = false;
        }
        
        $mobilemode = $GET->get('mobilemode');
        // Display mode switch pressed
        if(!empty($mobilemode)){
            if ($mobilemode == 'on'){
                $this->app->session->compact_mode = 'on';
            } else {
                $this->app->session->compact_mode = 'off';
            }
        }
        
        // If there is no user defined mode, detect mobile user agent,
        // otherwise use settings defined by user.
        if (is_null($this->app->session->compact_mode)) {
            if (empty($this->sessionConfig['compact_mode']) || $this->sessionConfig['compact_mode'] == 'auto'){
                if ($this->isMobile){
                    $this->mobileMode = true;
                } else {
                    $this->mobileMode = false;
                }
            } elseif ($this->sessionConfig['compact_mode'] == 'on'){
                $this->mobileMode = true;
            } else {
                $this->mobileMode = false;
            }
        } else {
        // User temporarily has switched display mode
            if ($this->app->session->compact_mode == 'on') {
                $this->mobileMode = true;
            } else {
                $this->mobileMode = false;
            }
        }
        
        if (!empty($this->sessionConfig['matfilter'])){
            $this->matFilterEnabled = true;
        }
        
        $imagesdir = STATIC_ROOT . '/skins/' . $this->skin . '/YaBBImages';

        // xmas switch, also disable xmas for "no_xmas" skin
        if (strpos(strtolower($this->skin), 'no_xmas') === FALSE) {
            if ($this->app->conf->xmas === 'auto') {
                $date_xmas = getdate();
                if (($date_xmas['mon'] == 12 && $date_xmas['mday'] > 14) || ($date_xmas['mon'] == 1 && $date_xmas['mday'] < 16)) {
                    $imagesdir .= '_xmas';
                    $this->app->conf->is_xmas = true;
                }
            } elseif ($this->app->conf->xmas === 'enable') {
                $imagesdir .= '_xmas';
                $this->app->conf->is_xmas = true;
            }
        } // if not "no_xmas" skin
        
        // setup imagesdir conf
        $this->app->conf->imagesdir = $imagesdir;
        // } end load user settings
    } // __construct()
    
    /**
     * cache user info
     * @param   string $name user name
     * @returns array
     */
    public function loadDisplay($user) {
        if (isset($this->cache[$user]))
            return $this->cache[$user];
        
        $euser = $this->app->db->escape_string($user);
        $request = $this->app->db->query("SELECT * FROM {$this->app->db->prefix}members WHERE memberName='$euser' LIMIT 1", false) or database_error(__FILE__, __LINE__, $this->app->db);
        if ($request->num_rows > 0) {
            $this->cache[$user] = $request->fetch_array();
            $this->cache[$user]['found'] = true;
            $this->cache[$user]['name'] = $user;
            if (!isset($this->cache[$user]['realName']))
                $this->cache[$user]['realName'] = $user;
            if (!isset($this->cache[$user]['signature']))
                $this->cache[$user]['signature'] = '';
            if (!isset($this->cache[$user]['websiteUrl']))
                $this->cache[$user]['websiteUrl'] = '';
            if (!isset($this->cache[$user]['websiteTitle']))
                $this->cache[$user]['websiteUrl'] = '';
            if (!isset($this->cache[$user]['location']))
                $this->cache[$user]['location'] = '';
            if (!isset($this->cache[$user]['ICQ']))
                $this->cache[$user]['ICQ'] = '';
            $this->cache[$user]['icqad'] = '';
            if (!isset($this->cache[$user]['AIM']))
                $this->cache[$user]['AIM'] = '';
            if (!isset($this->cache[$user]['YIM']))
                $this->cache[$user]['YIM'] = '';
            $this->cache[$user]['yimon'] = '';
            if (!isset($this->cache[$user]['MSN']))
                $this->cache[$user]['MSN'] = '';
            if (!isset($this->cache[$user]['gender']))
                $this->cache[$user]['gender'] = '';
            if (!isset($this->cache[$user]['personalText']))
                $this->cache[$user]['personalText'] = '';
            if (!isset($this->cache[$user]['memberGroup']))
                $this->cache[$user]['memberGroup'] = '';
            $this->cache[$user]['collapsedBoards'] = explode(",", $this->cache[$user]['collapsedBoardIDs']);
            $this->cache[$user]['collapsedCategories'] = explode(",", $this->cache[$user]['collapsedCategories']);
            $this->cache[$user]['modinfo'] = '';
            $this->cache[$user]['memberinfo'] = '';
            $this->cache[$user]['memberstar'] = '';
            $this->cache[$user]['groupinfo'] = '';
            if (!isset($this->cache[$user]['avatar']))
                $this->cache[$user]['avatar'] = '';
            if(strpos($this->cache[$user]['passwd'], ':') !== false)
                $this->cache[$user]['banned'] = true;
            else
                $this->cache[$user]['banned'] = false;
        } else {
            // No such user, fill initial data
            $this->cache[$user] = array();
            $this->cache[$user]['found'] = false;
            $this->cache[$user]['realName'] = $user;
            $this->cache[$user]['signature'] = '';
            $this->cache[$user]['websiteUrl'] = '';
            $this->cache[$user]['websiteUrl'] = '';
            $this->cache[$user]['location'] = '';
            $this->cache[$user]['avatar'] = '';
            $this->cache[$user]['hideEmail'] = '0';
            $this->cache[$user]['ICQ'] = '';
            $this->cache[$user]['icqad'] = '';
            $this->cache[$user]['AIM'] = '';
            $this->cache[$user]['YIM'] = '';
            $this->cache[$user]['MSN'] = '';
            $this->cache[$user]['yimon'] = '';
            $this->cache[$user]['gender'] = '';
            $this->cache[$user]['personalText'] = '';
            $this->cache[$user]['memberGroup'] = '';
            $this->cache[$user]['websiteUrl_IM'] = '';
            $this->cache[$user]['posts'] = '';
            $this->cache[$user]['emailAddress'] = '';
            $this->cache[$user]['ID_MEMBER'] = -1;
            $this->cache[$user]['banned'] = true;
            $this->cache[$user]['modinfo'] = '';
            $this->cache[$user]['memberinfo'] = '';
            $this->cache[$user]['memberstar'] = '';
            $this->cache[$user]['groupinfo'] = '';
            //$yyUDLoaded[$user]=0;
            
            return $this->cache[$user];
        }
        
        $img = $this->app->locale->img;
        $txt = $this->app->locale->txt;
        $membergroups = $this->memberGroups();
        $imagesdir = $this->app->conf->imagesdir; //STATIC_ROOT . '/skins/' . $this->skin . '/YaBBImages';
        
        /* Load the website image/link stuff */
        $this->cache[$user]['websiteUrl_IM'] = (($this->cache[$user]['websiteUrl']  != "") ? "<a href=\"" . $this->cache[$user]['websiteUrl'] . "\" target=\"_blank\" rel=\"nofollow noopener\"><img src=\"{$img['im_website']}\" alt=\"".$this->cache[$user]['websiteTitle']."\" title=\"".$this->cache[$user]['websiteTitle']."\" border=\"0\" /></a>" : "");
        if ($this->app->conf->MenuType == 1)
            $this->cache[$user]['websiteUrl'] = (($this->cache[$user]['websiteUrl'] != "") ? "<a href=\"" . $this->cache[$user]['websiteUrl'] . "\" target=\"_blank\" rel=\"nofollow noopener\"><img src=\"$imagesdir/www_sm.gif\" alt=\"".$this->cache[$user]['websiteTitle']."\" title=\"" . $this->cache[$user]['websiteTitle'] . "\" border=\"0\" /></a>" : "");
        else
            $this->cache[$user]['websiteUrl'] = (($this->cache[$user]['websiteUrl']  != "") ? "<a href=\"" . $this->cache[$user]['websiteUrl'] . "\" target=\"_blank\" rel=\"nofollow noopener\"><img src=\"{$img[website]}\" alt=\"{$this->cache[$user]['websiteTitle']}\" title=\"{$this->cache[$user]['websiteTitle']}\" border=\"0\" /></a>" : "");
        
        /* load the signature, replace the breaks in it */
        $breaks = array("\n\r", "\r\n", "\n", "\r");
        $this->cache[$user]['signature'] = str_replace($breaks, '<br>', $this->cache[$user]['signature']);
        $this->cache[$user]['signature'] = (($this->cache[$user]['signature'] != '') ? '<hr width="100%" size="1" class="windowbg3" style="color: ' . $this->app->conf->color['windowbg3'] . '" /><div class="signature"><font size="1">' . $this->cache[$user]['signature'] . '</font></div>' : '');
        
        /* # do some ubbc on the signature if enabled */
        if ($this->app->conf->enable_ubbc)
            $this->cache[$user]['signature'] = $this->service->DoUBBC($this->cache[$user]['signature']);
        
        /* ICQ and AIM, and YIM should be initialized in load user */
        if ($this->cache[$user]['ICQ'] != "" && is_numeric($this->cache[$user]['ICQ'])) {
            $this->cache[$user]['icqad'] = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=" . $this->cache[$user]['ICQ'] . "\" target=\"_blank\" rel=\"nofollow noopener\"><img src=\"{$this->app->conf->imagesdir}/icqadd.gif\" alt=\"" . $this->cache[$user]['ICQ'] . "\" border=\"0\" /></a>";
            $this->cache[$user]['ICQ'] = "<a href=\"{$this->app->conf->cgi}&amp;action=icqpager&amp;UIN=" . $this->cache[$user]['ICQ'] . "\" target=\"_blank\"><img src=\"http://status.icq.com/online.gif?icq=" . $this->cache[$user]['ICQ'] . "&amp;img=5\" alt=\"" . $this->cache[$user]['ICQ'] . "\" title=\"" . $this->cache[$user]['ICQ'] . "\" border=\"0\" /></a>";
        }
        $this->cache[$user]['AIM'] = (($this->cache[$user]['AIM'] != "") ? "<a href=\"skype:" . $this->cache[$user]['AIM'] . "?chat&topic=Forum.theProdigy.ru\"><img src=\"".STATIC_ROOT."/img/YaBBImages/Skype-icon-x17.png\" alt=\"Skype: " . $this->cache[$user]['AIM'] . "\" border=\"0\" /></a>" : "");
        if ($this->cache[$user]['YIM'] != "")
            $this->cache[$user]['yimon'] = "<a href=\"{$this->cache[$user]['YIM']}\" target=\"_blank\" rel=\"nofollow noopener\"><img src=\"".STATIC_ROOT."/img/YaBBImages/livejournal.gif\" border=\"0\" alt=\"" . $this->cache[$user]['YIM'] . "\" title=\"" . $txt['lj'] . "\" /></a>";
        
        $this->cache[$user]['MSN'] = (($this->cache[$user]['MSN'] != '') ? "<a href=\"http://members.msn.com/" . $this->app->subs->htmlescape($this->cache[$user]['MSN']) . "\" target=\"blank\" rel=\"nofollow noopener\"><img src=\"{$this->app->conf->imagesdir}/msntalk.gif\" border=\"0\" alt=\"\" /></a>" : "");

        /* if showing the gender image, and if the gender is specified */
        if ($this->app->conf->showgenderimage && $this->cache[$user]['gender'] != "") {
            $this->cache[$user]['gender'] = (stristr($this->cache[$user]['gender'], 'Female') ? 'Female' : 'Male');
            $gendertxt = (($this->cache[$user]['gender'] == 'Female') ? $txt[239] : $txt[238]);
            $this->cache[$user]['gender'] = "$txt[231]: <img src=\"{$this->app->conf->imagesdir}/" . $this->cache[$user]['gender'].".gif\" border=\"0\" alt=\"" . $gendertxt . "\" /><br />";
        }
        else
            $this->cache[$user]['gender'] = '';
        
        /* if user text is enabled, add a <br /> other wise erase it */
        $this->cache[$user]['personalText'] = ($this->app->conf->showusertext ? $this->cache[$user]['personalText'] . "<br />" : '');
        
        /* user pics is enabled */
        if ($this->app->conf->showuserpic && $this->app->conf->allowpics) {
            $this->cache[$user]['avatar'] = (($this->cache[$user]['avatar'] == '') ? 'blank.gif' : $this->cache[$user]['avatar']);
            $this->cache[$user]['avatar'] = (preg_match('~^https?://~', $this->cache[$user]['avatar']) ? "<br /><div class=\"avatar\"><img src=\"{$this->cache[$user]['avatar']}\" border=\"0\" alt=\"\" /></div><br />" : "<br /><img src=\"{$this->app->conf->facesurl}/{$this->cache[$user]['avatar']}\" border=\"0\" alt=\"\" /><br /><br />");
        }
        else
            $this->cache[$user]['avatar'] = '<br />';
        
        /* ### Censor it ### */
        //$this->cache[$user]['signature'] = CensorTxt($this->cache[$user]['signature']);
        //$this->cache[$user]['personalText'] = CensorTxt($this->cache[$user]['personalText']);

        /* create the memberinfo and memberstars entries */
        $starImg = ($this->cache[$user]['memberGroup'] != '') ? STATIC_ROOT . "/img/YaBBImages/prodstar.gif" : "$imagesdir/star.gif";
        if ($this->cache[$user]['memberName'] == "e-punk")
            $starImg = "$imagesdir/grin.gif";
        if ($this->cache[$user]['posts'] > $this->app->conf->GodPostNum) {
            $this->cache[$user]['memberinfo'] = "$membergroups[6]";
            $this->cache[$user]['memberstar'] = "<img src=\"$starImg\" border=\"0\" alt=\"*\" /><img src=\"$starImg\" border=\"0\" alt=\"*\" /><img src=\"$starImg\" border=\"0\" alt=\"*\" /><img src=\"$starImg\" border=\"0\" alt=\"*\" /><img src=\"$starImg\" border=\"0\" alt=\"*\" />";
        }
        elseif ($this->cache[$user]['posts'] > $this->app->conf->SrPostNum) {
            $this->cache[$user]['memberinfo'] = "$membergroups[5]";
            $this->cache[$user]['memberstar'] = "<img src=\"$starImg\" border=\"0\" alt=\"*\" /><img src=\"$starImg\" border=\"0\" alt=\"*\" /><img src=\"$starImg\" border=\"0\" alt=\"*\" /><img src=\"$starImg\" border=\"0\" alt=\"*\" />";
        }
        elseif ($this->cache[$user]['posts'] > $this->app->conf->FullPostNum) {
            $this->cache[$user]['memberinfo'] = "$membergroups[4]";
            $this->cache[$user]['memberstar'] = "<img src=\"$starImg\" border=\"0\" alt=\"*\" /><img src=\"$starImg\" border=\"0\" alt=\"*\" /><img src=\"$starImg\" border=\"0\" alt=\"*\" />";
        }
        elseif ($this->cache[$user]['posts'] > $this->app->conf->JrPostNum) {
            $this->cache[$user]['memberinfo'] = "$membergroups[3]";
            $this->cache[$user]['memberstar'] = "<img src=\"$starImg\" border=\"0\" alt=\"*\" /><img src=\"$starImg\" border=\"0\" alt=\"*\" />";
        }
        else {
            $this->cache[$user]['memberinfo'] = "$membergroups[2]";
            $this->cache[$user]['memberstar'] = "<img src=\"$starImg\" border=\"0\" alt=\"*\" />";
        }

        
        if ($this->cache[$user]['memberGroup'] == 'Administrator') {
            $this->cache[$user]['memberstar'] = "<img src=\"{$this->app->conf->imagesdir}/staradmin.gif\" border=\"0\" alt=\"*\" /><img src=\"{$this->app->conf->imagesdir}/staradmin.gif\" border=\"0\" alt=\"*\" /><img src=\"{$this->app->conf->imagesdir}/staradmin.gif\" border=\"0\" alt=\"*\" /><img src=\"{$this->app->conf->imagesdir}/staradmin.gif\" border=\"0\" alt=\"*\" /><img src=\"{$this->app->conf->imagesdir}/staradmin.gif\" border=\"0\" alt=\"*\" />";
            $this->cache[$user]['memberinfo'] = "<b class=\"user-group\">$membergroups[0]</b>";
        }
        elseif ($this->cache[$user]['memberGroup'] == 'Global Moderator') {
            $this->cache[$user]['memberstar'] = "<img src=\"{$this->app->conf->imagesdir}/stargmod.gif\" border=\"0\" alt=\"*\" /><img src=\"{$this->app->conf->imagesdir}/stargmod.gif\" border=\"0\" alt=\"*\" /><img src=\"{$this->app->conf->imagesdir}/stargmod.gif\" border=\"0\" alt=\"*\" /><img src=\"{$this->app->conf->imagesdir}/stargmod.gif\" border=\"0\" alt=\"*\" /><img src=\"{$this->app->conf->imagesdir}/stargmod.gif\" border=\"0\" alt=\"*\" />";
            $this->cache[$user]['memberinfo'] = "<b class=\"user-group\">$membergroups[7]</b>";
        }
        elseif ($this->isBoardModerator($user)) {
            $this->cache[$user]['modinfo'] = "<b>$membergroups[1]</b><br />";
            $this->cache[$user]['memberstar'] = "<img src=\"{$this->app->conf->imagesdir}/starmod.gif\" border=\"0\" alt=\"*\" /><img src=\"{$this->app->conf->imagesdir}/starmod.gif\" border=\"0\" alt=\"*\" /><img src=\"{$this->app->conf->imagesdir}/starmod.gif\" border=\"0\" alt=\"*\" /><img src=\"{$this->app->conf->imagesdir}/starmod.gif\" border=\"0\" alt=\"*\" /><img src=\"{$this->app->conf->imagesdir}/starmod.gif\" border=\"0\" alt=\"*\" />";
        }
        
        // if the karma mod is enabled, append the karma information after the stars
        $karmaString = '';
        if ($this->app->conf->karmaMode == '1')
            $karmaString = "<br />{$this->app->conf->karmaLabel} " . ($this->cache[$user]['karmaGood']-$this->cache[$user]['karmaBad']);
        else if ($this->app->conf->karmaMode == '2')
            $karmaString = "<br />{$this->app->conf->karmaLabel} +{$this->cache[$user]['karmaGood']}/-{$this->cache[$user]['karmaBad']}";
        
        if ($this->posts >= $this->app->conf->karmaMinPosts && ($this->app->conf->karmaMemberGroups[0] == '' || (sizeof($this->app->conf->karmaMemberGroups) >= 1 && in_array($this->group, $this->app->conf->karmaMemberGroups)) || $this->accessLevel() > 2) && $this->app->conf->karmaMode != '0' && $this->name != 'Guest' && $this->name != $user)
            $karmaString .= '<br /><a href="'.SITE_ROOT."/karma/applaud/{$this->cache[$user]['ID_MEMBER']}/\">{$this->app->conf->karmaApplaudLabel}</a> <a href=\"".SITE_ROOT ."/karma/smite/{$this->cache[$user]['ID_MEMBER']}/\">{$this->app->conf->karmaSmiteLabel}</a>";
        if ($this->app->conf->karmaMode != '0')
            $this->cache[$user]['memberstar'] .= $karmaString;
        
        
        if ($this->cache[$user]['memberGroup'] != "" && $this->cache[$user]['memberGroup'] != 'Administrator' && $this->cache[$user]['memberGroup'] != 'Global Moderator')
            $this->cache[$user]['groupinfo'] = $this->cache[$user]['memberGroup'] . "<br />";
        if ($this->cache[$user]['memberGroup'] != 'Administrator' && $this->cache[$user]['memberGroup'] != 'Global Moderator')
            $this->cache[$user]['memberinfo'] = "{$this->cache[$user]['modinfo']}{$this->cache[$user]['groupinfo']}{$this->cache[$user]['memberinfo']}";
        if ($this->cache[$user]['posts'] > 100000)
            $this->cache[$user]['posts'] = "$txt[683]";
        
        /* we've successfully loaded the user */
        //$yyUDLoaded[$user] = 1;
        return $this->cache[$user];
    } // loadDisplay()
    
    /**
     * Get user's real name
     * @param string $user  user login name
     * @return string
     */
    public function realName($user = null) {
        if(null === $user || $user == $this->name)
            return $this->realname;
        $userdata = $this->loadDisplay($user);
        return $userdata['realName'];
    }
    
    /**
     * Load member groups of forum
     * @return array
     */
    public function memberGroups() {
        if (null === $this->_membergroups) {
            $request = $this->app->db->query("
                SELECT membergroup
                FROM {$this->app->db->prefix}membergroups
                ORDER BY ID_GROUP"
            );

            $this->_membergroups = array();
            while ($row = $request->fetch_row())
                $this->_membergroups[] = $row[0];
            return $this->_membergroups;
        } else {
            // already cached
            return $this->_membergroups;
        }
    } // memberGroups()
    
    /**
     * Check if user is a board moderator
     * @param string $user  user name (current user when ommited)
     * @return bool
     */
    public function isBoardModerator($user = null) {
        if (!isset($this->service->board_moderators) && !is_array($this->service->board_moderators)) {
            //$this->app->errors->abort('Error', 'Could not check board moderators, list is empty');
            return false;
        }
        if (null === $user) $user = $this->name;
        
        return in_array($user, $this->service->board_moderators) || isset($this->service->board_moderators[$user]);
    } // isBoardModerator()

    /**
     * Load moderators of specified board or prepare array of moderators from supplied list
     * @param mixed $query   int board number or comma separated list of moderators
     * @return array
     */
    public function LoadBoardModerators($query = null)
    {
        $board = (int) $query;
        /* load the moderators for specified board, if the board is specified */
        if ($board > 0)
        {
            $db = $this->app->db;
            $dbrq = $db->query ("SELECT b.moderators FROM {$db->prefix}boards AS b WHERE (b.ID_BOARD='$board')", false);
            /* if there aren't any, skip */
            if ($dbrq->num_rows > 0)
            {
                $row = $dbrq->fetch_array();
                $moderators = explode(',', trim($row['moderators']));
            }
            else
                return array();
        }
        elseif ($query !== null)
        {
            $moderators = explode(',', trim($query));
        }
        else
            $this->error('LoadBoardModerators failed, bad request supplied.', 'u1');
        
        $_moderators = array();
        /* now load the real names of the moderators
           into the realNames array */
        for ($i = 0; $i < sizeof($moderators); $i++)
        {
            $moderators[$i] = trim($moderators[$i]);
            $_moderators[$moderators[$i]] = $this->LoadDisplay($moderators[$i]);
        }
        return $_moderators;
    } // LoadBoardModerators()
    
    /**
     * Get user access level
     * @return int
     */
    public function accessLevel($board = null) { // FIXME
        if($this->name == 'Guest')
            return 0;
        elseif($this->group == 'Global Moderator')
            return 3;
        elseif($this->group == 'Administrator')
            return 4;
        else
        {
            $board_moderators = $this->app->board->moderators($board);
            if(isset($board_moderators[$this->name]))
                return 2;
            else
                return 1;
        }
    } // getAccessLevel()

    
    public function allowedToReply($ID_MEMBER = null, $board = null)
    {
        if(is_null($ID_MEMBER))
            $ID_MEMBER = $this->id;
        
        if(is_null($board))
            $board = $this->service->board;
            
        //$banned[0][0] = 18655;	//Феникс
        //$banned[1][0] = 39;		//Радио Арены
        
        //$banned[0][1] = 599;	//dig7er
        //$banned[1][1] = 39;	//Радио Арены
        
        $banned[0][0] = 26740;	//Rain
        $banned[1][0] = 42;		//Политика, история и философия
        
        //$banned[0][1] = 24171;	//corpuscul2
        //$banned[1][1] = 42;	//Politics, History and Philosophy
        for ($k=0; $k < sizeof($banned[0]); $k++) {
            if (!empty($banned[0]) && ($key = ($ID_MEMBER == $banned[0][$k])? $k : FALSE) !== FALSE) {
                for ($i = 0; $i < sizeof($banned); $i++) {
                    if ($banned[$i][$key] == $board)
                        return FALSE;
                }
            }
        }
        return TRUE;
    } // allowedToReply()

    /**
     * Check if specified user is in ignore list
     * @param string $member - member name to search in ignore list
     * @param int $id - ignore list owner, self by default
     * @return bool
     */
    public function inIgnore ($member, $id = null) {
        if (! $this->app->conf->hard_ignore) return false;
        
        if (empty($this->id) || $this->id < 1) {
            return false;
        }    
        
        if (null === $id) {
            $id = $this->id;
        }
        
        // search in static predefined ignore list in Config.php
        $predefined = $this->app->conf->forced_ignore;
        if (is_null($predefined)) $predefined = array();
        if (isset($predefined[$id]) && in_array($member, $predefined[$id])) {
            return true;
        }
        
        $imember = intval($member);
        if ($imember > 0) {
            // get member by id
            $request = $this->app->db->query("SELECT memberName{$this->app->db->prefix} FROM members WHERE ID_MEMBER = $imember");
            if ($request->num_rows > 0) {
                $row = $request->fetch_row();
                $member = $row[0];
            } else {
                return false;
            }
        }
        
        $ignorelist = $this->getIgnoreList($id);
        return in_array($member, $ignorelist);
    } // inIgnore()
    
    /**
     * Load user's ignore list
     * @param int $id ID_MEMBER
     * @return array
     */
    public function getIgnoreList($id = null) {
        if (null === $id) {
            $id = $this->id;
        }
        $id = intval($id);
        if ($id < 1) {
            return array();
        }
        
        $ignore = array();
        
        if ($id != $this->id || null === $this->_ignores) {
            $request = $this->app->db->query("SELECT im_ignore_list FROM {$this->app->db->prefix}members WHERE ID_MEMBER=$id", false);
            $row = $request->fetch_row();
            $ignore = explode(',', $row[0]);
            foreach ($ignore as $key => $value) {
                $ignore[$key] = trim($value);
            }
            
            if ($id == $this->id && null === $this->_ignores) { // FIXME
                // Cache self ignore list for further using
                $this->_ignores = $ignore;
            }
        }
        
        //if ($id == $this->id && is_array($this->_ignores)) {
            //// Return cached self ignore list
            //return $this->_ignores;
        //} else {
            //return $ignore;
        //}
        
        return $ignore;
    } // getIgnoreList()

    public function __get($name) {
        if (empty($this->_settings[$name])) {
            return NULL;
        } else {
            return $this->_settings[$name];
        }
    }
    
    public function loginform($request, $response, $service, $app) {
        if ($request->method('POST'))
            return $this->login($request, $response, $service, $app);
        
        $service->title = $app->locale->txt[34];
        $service->inputuser = $request->param('user', '');
        $app->main->render('templates/login.php');
    }
    
    public function login($request, $response, $service, $app) {
        $POST = $request->paramsPost();
        $input_user = $POST->get('user');
        $input_password = $POST->get('password');
        
        if (empty($input_user))
            return $app->errors->abort('Error', $app->locale->txt[37] . ' - ' . $input_password);
        
        if (empty($input_password))
            $app->errors->abort('Error', $app->locale->txt[38]);
        
        $cookielength = $POST->get('cookielength');
        $cookieneverexp = $POST->get('cookieneverexp');
        
        // FIXME cyrillic list
        if (!preg_match($app->conf->loginregex, $input_user))
            return $app->errors->abort('Error', $app->locale->txt[240]);

        if ($cookielength == $app->locale->yse50)
        {
            $cookielength   = 1;
            $cookieneverexp   = 'on';
        }
        
        if (!is_numeric($cookielength) && empty($cookieneverexp))
            return $app->errors->abort('Error', $cookieLength . ' ' . $app->locale->txt[337]);
        
        $db_prefix = $app->db->prefix;
        
        $quser = $app->db->escape_string($input_user);
        $dbrq = $app->db->query("
            SELECT passwd,realName,emailAddress,websiteTitle,websiteUrl,signature,posts,memberGroup,ICQ,AIM,YIM,gender,personalText,avatar,dateRegistered,location,birthdate,timeFormat,timeOffset,hideEmail,ID_MEMBER FROM {$db_prefix}members
             WHERE memberName='$quser'", false);
        
        $attempt = str_repeat('*', strlen($input_password));
        
        if ($dbrq->num_rows == 0)
        {
            return $app->errors->abort('Error', $app->locale->txt[40] . ' - ' . $input_user . ': ' . $attempt);
        }
        
        $settings = $dbrq->fetch_array();
        
        if(strpos($settings[0], ':') !== false){
            // User was banned, check if should be unbanned and try again
            if ($app->security->enhanced_banning($user) == 0){
                if(!$app->session->login_second_pass) {
                    $app->session->login_second_pass = true;
                    return $this->login($request, $response, $service, $app);
                }
                else {
                    $app->session->login_second_pass = false;
                    return $app->errors->abort('Error', 'Login failed, maybe profile corrupt.');
                }
            }
        }
        
        $md5_password = $app->subs->md5_hmac($input_password, strtolower($input_user));
        
        if ($settings[0] != $md5_password)
        {
            if ($settings[0] == crypt($input_password, substr($input_password, 0, 2)) || $settings[0] == md5($input_password))
            {
                $app->db->query("
                    UPDATE {$db_prefix}members
                    SET passwd='$md5_password'
                        WHERE memberName='$quser'");
                $settings[0] = $md5_password;
            } else {
                $settings[7] = '';
                return $app->errors->abort('Error', "{$app->locale->txt[39]} - $input_user: $attempt");
            }
        }
        
        //$userid = $settings['ID_MEMBER']; // FIXME why?
        
        if ($cookielength < 1 || $cookielength > 525600)
            $cookielength = $app->conf->Cookie_Length;
        
        if (!isset($cookieneverexp) || $cookieneverexp == '')
            $Cookie_Length = $cookielength;
        else
            $Cookie_Length = 525600;   // about 1 year
        
        $password = $app->subs->md5_hmac($md5_password, $app->conf->pwseed);
        
        $cookie_url = explode('<yse_sep>', $app->subs->url_parts());
        $cookie = serialize(array($settings['ID_MEMBER'], $password));
        $ctime = time() + (60 * $Cookie_Length);
        
        $SSL = $request->isSecure();
        
        // Set cookie
        $response->cookie($app->conf->cookiename, $cookie, $ctime, $cookie_url[1], $cookie_url[0], $SSL);
        
//         if ($SSL) {
//             $app->conf->HSTS = true;
//             //if ($Cookie_Length > 43800)
//                 //$config['HSTS-Age'] = 43800 * 60;
//             //else
//             $app->conf->HSTS_Age = $Cookie_Length * 60;
//             $response->header('Strict-Transport-Security', "max-age={$app->conf->HSTS_Age}; includeSubDomains");
//         }
        
        $lastLog = time();
        $memIP = $app->db->escape_string($request->server()->get('REMOTE_ADDR'));
        
        $app->db->query("UPDATE {$db_prefix}members SET lastLogin='$lastLog',memberIP='$memIP' WHERE memberName='$quser'");

        //LoadUserSettings();
        
        $app->db->query("DELETE FROM {$db_prefix}log_online WHERE identity=INET_ATON('$memIP')");
        
        /*$lngfile_result = $db->query("SELECT lngfile FROM {$db_prefix}members WHERE memberName='$userid'") or database_error(__FILE__, __LINE__, $db);
        
        $temp = $lngfile_result->fetch_array();
        $chklngfile = $temp[0];
        
        if ($modSettings['userLanguage'] == 1 && $chklngfile != $language)
            if ($chklngfile != Null)
                include($chklngfile); */
        
        if (!($app->conf->maintenance == 1 && $settings[7] != 'Administrator'))
        {
            //$app->main->WriteLog();
            $service->redirect('/');
        } else {
            $this->logout();
        }
    } // login()
    
    public function logout($request, $response, $service, $app)
    {
        $app->session->check('get');
        
        // Write log
        $app->db->query("DELETE FROM {$app->db->prefix}log_online WHERE identity='{$app->user->id}'");
        
        $cookie_url = explode('<yse_sep>', $app->subs->url_parts());
        
        $SSL = $request->isSecure();
        
        $response->cookie($app->conf->cookiename, '', time() - 3600, $cookie_url[1], $cookie_url[0], $SSL);
        
        if ($SSL) {
            // tell client to cease HSTS
            header("Strict-Transport-Security: max-age=0; includeSubDomains");
        }
        
        $app->session->erase();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            $response->cookie(session_name(), '', time() - 42000,
               $params["path"], $params["domain"],
               $params["secure"], $params["httponly"]
            );
        }

        //$app->security->banning();

        if ($app->conf->maintenance == 1 && $app->user->group != 'Administrator')
        {
            //InMaintenance();
            $service->redirect('/maintenance/');
        }
        
        if ($app->conf->guestaccess){
            $service->redirect('/');
        }
        else
            $this->kickguest($request, $response, $service, $app);
    } //logout
    
    public function kickguest($request, $response, $service, $app) {
        $service->title = $app->locale->txt[34];
        $app->main->render('templates/kickguest.template.php');
    }
    
    public function error($message = '', $code = null) {
        throw new UserException($message, $code);
    }
}

class UserException extends \Exception
{
    public function __toString() {
        return __CLASS__ . ": [{$this->code}] {$this->message}\n";
    }
}

?>
