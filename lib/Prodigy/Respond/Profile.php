<?php
namespace Prodigy\Respond;

class Profile extends Respond
{

    public function loginform($request, $response, $service, $app) {
        if ($request->method('POST'))
            return $this->login($request, $response, $service, $app);
        
        $service->title = $app->locale->txt[34];
        $service->inputuser = $request->param('user', '');
        
        if ($request->paramsGet()->get('newpass'))
            $service->comment = $app->locale->txt[638];
        
        return $app->main->render('templates/login.php');
    } // loginform()

    public function login($request, $response, $service, $app) {
        $POST = $request->paramsPost();
        $input_user = $POST->get('user');
        $input_password = $POST->get('password');
        
        if (empty($input_user))
            return $this->error($app->locale->txt[37] . ' - ' . $input_password);
        
        if (empty($input_password))
            return $this->error($app->locale->txt[38]);
        
        $cookielength = $POST->get('cookielength');
        $cookieneverexp = $POST->get('cookieneverexp');
        
        // FIXME cyrillic list
        if (!preg_match($app->conf->loginregex, $input_user))
            return $this->error($app->locale->txt[240]);

        if ($cookielength == $app->locale->yse50)
        {
            $cookielength   = 1;
            $cookieneverexp   = 'on';
        }
        
        if (!is_numeric($cookielength) && empty($cookieneverexp))
            return $this->error($cookieLength . ' ' . $app->locale->txt[337]);
        
        $db_prefix = $app->db->prefix;
        
        $dbst = $app->db->prepare("
            SELECT passwd,realName,emailAddress,websiteTitle,websiteUrl,signature,posts,memberGroup,ICQ,AIM,YIM,gender,personalText,avatar,dateRegistered,location,birthdate,timeFormat,timeOffset,hideEmail,ID_MEMBER FROM {$db_prefix}members
             WHERE memberName=?");
        $dbst->execute(array($input_user));
        
        $attempt = str_repeat('*', strlen($input_password));
        
        $settings = $dbst->fetch();
        $dbst = null; // closing statement
        if (!$settings)
        {
            return $this->error($app->locale->txt[40] . ' - ' . $input_user . ': ' . $attempt);
        }
        
        if(strpos($settings['passwd'], ':') !== false){
            // User was banned, check if should be unbanned and try again
            if ($app->security->enhanced_banning($user) == 0){
                if(!$app->session->login_second_pass) {
                    $app->session->login_second_pass = true;
                    return $this->login($request, $response, $service, $app);
                }
                else {
                    $app->session->login_second_pass = false;
                    return $this->error('Login failed, maybe profile corrupt.');
                }
            }
        }
        
        $md5_password = $app->subs->md5_hmac($input_password, strtolower($input_user));
        
        if ($settings['passwd'] != $md5_password)
        {
            if ($settings['passwd'] == crypt($input_password, substr($input_password, 0, 2)) || $settings['passwd'] == md5($input_password))
            {
                $app->db->prepare("
                    UPDATE {$db_prefix}members
                    SET passwd=?
                        WHERE memberName=?")->
                        execute(array($md5_password, $input_user));
                $settings['passwd'] = $md5_password;
            } else {
                $settings['memberGroup'] = '';
                return $this->error("{$app->locale->txt[39]} - $input_user: $attempt");
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
        
        $cookie = serialize(array($settings['ID_MEMBER'], $password));
        $ctime = time() + (60 * $Cookie_Length);
        
        $SSL = $request->isSecure();
        
        // Set cookie
        $this->cookie($app->conf->cookiename, $cookie, $ctime, null, null, $SSL);
        
//         if ($SSL) {
//             $app->conf->HSTS = true;
//             //if ($Cookie_Length > 43800)
//                 //$config['HSTS-Age'] = 43800 * 60;
//             //else
//             $app->conf->HSTS_Age = $Cookie_Length * 60;
//             $response->header('Strict-Transport-Security', "max-age={$app->conf->HSTS_Age}; includeSubDomains");
//         }
        
        $lastLog = time();
        $memIP = $request->server()->get('REMOTE_ADDR');
        
        $app->db->prepare("UPDATE {$db_prefix}members SET lastLogin=?, memberIP=? WHERE memberName=?")->
            execute(array($lastLog, $memIP, $input_user));
        
        //LoadUserSettings();
        
        $app->db->query("DELETE FROM {$db_prefix}log_online WHERE identity=INET_ATON('$memIP')");
        
        /*$lngfile_result = $db->prepare("SELECT lngfile FROM {$db_prefix}members WHERE memberName='$userid'");
        $lngfile_result->execute(array($userid));
        $chklngfile = $lngfile_result->fetchColumn();
        $lngfile_result = null;
        
        if ($modSettings['userLanguage'] == 1 && $chklngfile != $language)
            if ($chklngfile != Null)
                include($chklngfile); */
        
        if (!($app->conf->maintenance == 1 && $settings['memberGroup'] != 'Administrator'))
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
        
        $app->user->offline();

        //$app->security->banning();

        if ($app->conf->maintenance == 1 && !$app->user->isAdmin())
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

    public function show($request, $response, $service, $app)
    {
        $user = $request->paramsNamed()->get('user');
        
        $db_prefix = $app->db->prefix;
        $dbst = $app->db->prepare("
            SELECT memberName, realName, passwd, emailAddress, websiteTitle, websiteUrl, signature, posts, memberGroup, ICQ, AIM, YIM, gender, personalText, avatar, dateRegistered, location, birthdate, timeFormat, timeOffset, hideEmail, ID_MEMBER, usertitle, karmaBad, karmaGood, lngfile, MSN, memberIP
            FROM {$db_prefix}members
            WHERE memberName=?");
        $dbst->execute(array($user));
        
        $meminf = $dbst->fetch();
        if (!$meminf)
            return $this->error(sprintf($app->locale->txt(453), $user));
        
        $tmpl = array();
        
        $tmpl['title'] = "{$app->locale->txt['92']} $user";
        
        $meminf['AIM'] = str_replace('+', ' ', $meminf['AIM']);
        
        if (empty($meminf['dateRegistered']))
            $meminf['dateReg'] = $app->locale->txt[470];
        else
            $meminf['dateReg'] = $app->subs->timeformat($meminf['dateRegistered']);
        
        $datearray = getdate(time());
        $meminf['age'] = '';
        $meminf['isbday'] = false;
        if (empty($meminf['birthdate']) || $meminf['birthdate'] == '0000-00-00')
            $meminf['age'] = $app->locale->txt[470];
        else
        {
            $meminf['age'] = substr($meminf['birthdate'], 0, 4) == '0000' ? $app->locale->txt[470] : $datearray['year'] - substr($meminf['birthdate'], 0, 4) - (($datearray['mon'] > substr($meminf['birthdate'], 5, 2) || $datearray['mon'] == substr($meminf['birthdate'], 5, 2) && $datearray['mday'] >= substr($meminf['birthdate'], 8, 2)) ? 0 : 1);
            
            $meminf['isbday'] = ($datearray['mon'] == substr($meminf['birthdate'], 5, 2) && $datearray['mday'] == substr($meminf['birthdate'], 8, 2));
        }
        
        if ($app->conf->userLanguage == '1')
        {
            $meminf['usrlng'] = ucfirst(substr($meminf['lngfile'], 0, (strlen($meminf['lngfile']) - 4)));
        }
        
        $membergroups = $app->user->memberGroups();
        
        if ($meminf['posts'] > $app->conf->GodPostNum)
            $meminf['memberinfo'] = $membergroups[6];
        elseif ($meminf['posts'] > $app->conf->SrPostNum)
            $meminf['memberinfo'] = $membergroups[5];
        elseif ($meminf['posts'] > $app->conf->FullPostNum)
            $meminf['memberinfo'] = $membergroups[4];
        elseif ($meminf['posts'] > $app->conf->JrPostNum)
            $meminf['memberinfo'] = $membergroups[3];
        else
            $meminf['memberinfo'] = $membergroups[2];
        
        if (!empty($meminf['memberGroup']))
            $meminf['memberinfo'] = $meminf['memberGroup'];
        if ($meminf['memberGroup'] == 'Global Moderator')
            $meminf['memberinfo'] = $membergroups[7];
        if ($meminf['memberGroup'] == 'Administrator')
            $meminf['memberinfo'] = $membergroups[0];
        
        if (empty($meminf['websiteTitle']))
            $meminf['websiteTitle'] = $meminf['websiteUrl'];
        
        $tmpl['self'] = false;
        if ($app->user->id == $meminf['ID_MEMBER'])
            $tmpl['self'] = true;
        
        $meminf['following'] = false;
        if (!$app->user->guest and !$tmpl['self'])
        {
            $tmpl['follow_btn'] = true;
            // check whether the user is being followed by the current user
            $dbst = $app->db->prepare("SELECT * FROM {$db_prefix}followers WHERE MEMBER_ID=? AND FOLLOW_TYPE='MEMBER' AND ID=?");
            $dbst->execute(array($app->user->id, $meminf['ID_MEMBER']));
            if ($dbst->fetch())
                $meminf['following'] = true;
            $dbst = null;
        }
        
        if ($app->user->isStaff() && $meminf['passwd'] == 'INACTIVE')
            $tmpl['activate_btn'] = true;
        
        if ($tmpl['self'] || $app->user->isAdmin())
            $tmpl['modify_btn'] = true;
        
        if ($app->conf->allow_hide_email && $meminf['hideEmail'] == '1' && !$app->user->isStaff())
            $meminf['emailAddress'] = null;
        
        if ($app->user->guest && $meminf['posts'] < 100)
        {
            // hide website URLs of recently registered users for guests
            $meminf['websiteUrl'] = null;
            $meminf['YIM'] = null;
        }
        
        if ($meminf['gender'] == 'Male')
            $meminf['gender'] = $app->locale->txt[238];
        elseif ($meminf['gender'] == 'Female')
            $meminf['gender'] = $app->locale->txt[239];
        else
            $meminf['gender'] = null;
        
        if ($app->conf->allowpics)
        {
            $tmpl['avatar'] = true;
            if (preg_match('~^https?://~', $meminf['avatar']))
            {
                $tmpl['self_avatar'] = true;
                if ($app->conf->userpic_width != 0)
                    $tmpl['avatar_width'] = 'width="' . $app->conf->userpic_width . '"';
                else
                    $tmpl['avatar_width'] = '';
                if ($app->conf->userpic_height != 0)
                    $tmpl['avatar_height'] = 'height="' . $app->conf->userpic_height . '"';
                else
                    $tmpl['avatar_height'] = '';
            }
        }
        
        if ($meminf['posts'] > 100000)
            $meminf['posts'] = $app->locale->txt[683];
        else
        {
            // Posts: xxxx (yy.yy per day)
            if ($app->conf->postsPerDay == 1)
                $meminf['posts'] .= ' (' . round($meminf['posts'] / ((time() - $meminf['dateRegistered']) / 86400), 2) . ' ' . $app->locale->postsper_day . ')';
            // Posts: xxxx (yy today)
            elseif ($app->conf->postsPerDay == 2)
            {
                // Get the posts made today.  (note that this is not the last 24 hours, but today.)
                $dbst = $app->db->prepare("
                    SELECT COUNT(ID_MSG)
                    FROM {$db_prefix}messages
                    WHERE ID_MEMBER=?
                    AND posterTime > ?");
                $dbst->execute(array($meminf['ID_MEMBER'], time() - (time() % 86400)));
                $post_count = $dbst->fetchColumn();
                $dbst = null;
                $meminf['posts'] .= ' (' . $post_count . ' ' . $app->locale->postsper_today . ')';
            }
            // Posts: xxxx (yy.yy per day, zz today)
            elseif ($app->conf->postsPerDay == 3)
            {
                // Get the posts made today.
                $dbst = $app->db->prepare("
                    SELECT COUNT(ID_MSG)
                    FROM {$db_prefix}messages
                    WHERE ID_MEMBER=?
                    AND posterTime > ?");
                $dbst->execute(array($meminf['ID_MEMBER'], time() - (time() % 86400)));
                $post_count = $dbst->fetchColumn();
                $dbst = null;
                $meminf['posts'] .= ' (' . round($meminf['posts'] / ((time() - $meminf['dateRegistered']) / 86400), 2) . ' ' . $app->locale->postsper_day . ', ' . $post_count . ' ' . $app->locale->postsper_today . ')';
            }
        }
        
        $meminf['online'] = $app->user->OnlineStatus($meminf['ID_MEMBER']);
        
        if (!$app->conf->titlesEnable)
            $meminf['usertitle'] = null;
        
        if ($app->conf->karmaMode == '1')
            $meminf['karma'] = $meminf['karmaGood'] - $meminf['karmaBad'];
        elseif ($app->conf->karmaMode == '2')
            $meminf['karma'] = "+{$meminf['karmaGood']}/-{$meminf['karmaBad']}";
        
        if ($app->user->isStaff())
        {
            $tmpl['staff'] = true;
        }
        else
        {
            $meminf['memberIP'] = null;
            $tmpl['staff'] = false;
        }
        
        // enhanced ban mod - is this user banned?
        $meminf['banneduntil'] = " {$app->locale->enhancedban7}";
        $meminf['banreason'] = 'Unspecified';
        $meminf['banned'] = 0;
        if(strpos($meminf['passwd'], ':') !== false)
        {
            // User was banned
            $banned_info = $app->security->enhanced_banning($user, true);
            if(is_array($banned_info)){
                if(!empty($banned_info[0]))
                    $meminf['banneduntil'] = ' ' . $app->locale->enhancedban20 . ' ' . $app->subs->timeformat($banned_info[0]);
                if(!empty($banned_info[1]))
                    $meminf['banreason'] = $banned_info[1];
                $meminf['banned'] = 1;
            }
        }
        
        // Don't display banned profile for guests
        if ($app->user->guest || $app->user->posts < 100)
        {
            if ($meminf['passwd'] == 'INACTIVE')
                // Requested user is deactivated
                return $this->error($app->locale->enhancedban_deactivated);
            
            if ($meminf['banned'] == 1)
                // Just show that user is banned instead of showing profile to guests
                return $this->error("{$app->locale->enhancedban16} {$app->locale->enhancedban18} {$meminf['banneduntil']}.<br>{$app->locale->enhancedban13}: {$meminf['banreason']}");
        }
        
        $tmpl['enableFollowingMod'] = true;
        
        if ($app->user->mobileMode)
            $tmpl['hideLastMsgsNCmnts'] = true;
        else
            $tmpl['hideLastMsgsNCmnts'] = $request->cookies()->get('hideLastMsgsNCmnts', false);
        
        if ($tmpl['hideLastMsgsNCmnts'])
            $tmpl['width'] = '600px';
        else
            $tmpl['width'] = '100%';

        $tmpl['meminf'] = $meminf;
       
       return $this->render('templates/profile/show.template.php', $tmpl);        
    } // show()
    
    public function edit($request, $response, $service, $app)
    {
        $user = $request->paramsNamed()->get('user');
        
        if ($user != $app->user->name && !$app->user->isAdmin())
            return $this->error($app->locale->txt[80], 403);
        
        $db_prefix = $app->db->prefix;
        
        if ($request->method('GET'))
        {
            $tdat = array();
            $tdat['title'] = $app->locale->txt[79];
            
            $dbst = $app->db->prepare("
                SELECT passwd, memberName, realName, emailAddress, websiteTitle, websiteUrl, signature, posts, memberGroup, ICQ, AIM, YIM, gender, personalText, avatar, dateRegistered, location, birthdate, timeFormat, timeOffset, hideEmail, ID_MEMBER, usertitle, karmaBad, karmaGood, lngfile, MSN, secretQuestion, secretAnswer, QuickReply, skin, imsound, showComments, blockComments, closeCommentsByDefault
                FROM {$db_prefix}members
                WHERE memberName=?");
            $dbst->execute(array($user));
            $meminf = $dbst->fetch();
            $dbst = null;
            
            if (empty($meminf))
                return $this->error(sprintf($app->locale->txt[453], $user));
            
            $timeadjust = ((isset($app->user->timeOffset) ? $app->user->timeOffset : 0) + $app->conf->timeoffset) * 3600;
            if ($meminf['dateRegistered'] && $meminf['dateRegistered'] != '0000-00-00')
            {
                $fmt = '%d %b %Y ' . (substr_count($app->user->timeFormat, '%H') == 0 ? '%I:%M:%S %p' : '%T');
                $meminf['dateReg'] = strftime($fmt, $meminf['dateRegistered'] + $timeadjust);
            }
            else
                $meminf['dateReg'] = $app->locale->txt[470];
            
            if (isset($meminf['gender']))
            {
                if ($meminf['gender'] == 'Male')
                    $tdat['GenderMale'] = ' selected="selected"';
                elseif ($meminf['gender'] == 'Female')
                    $tdat['GenderFemale'] = ' selected="selected"';
            }
            
            list($meminf['uyear'], $meminf['umonth'], $meminf['uday']) = explode('-', $meminf['birthdate']);
            $meminf['AIM'] = (isset($meminf['AIM']) ? str_replace('+', ' ', $meminf['AIM']) : '');
            // set up the default values
            $meminf['realName'] = (isset($meminf['realName']) ? $meminf['realName'] : '');
            $meminf['usertitle'] = (isset($meminf['usertitle']) ? $meminf['usertitle'] : '');
            $meminf['location'] = (isset($meminf['location']) ? $meminf['location'] : '');
            $meminf['websiteTitle'] = (isset($meminf['websiteTitle']) ? $meminf['websiteTitle'] : '');
            $meminf['websiteUrl'] = (isset($meminf['websiteUrl']) ? $meminf['websiteUrl'] : '');
            $meminf['ICQ'] = (isset($meminf['ICQ']) ? $meminf['ICQ'] : '');
            $meminf['YIM'] = (isset($meminf['YIM']) ? $meminf['YIM'] : '');
            $meminf['timeFormat'] = (isset($meminf['timeFormat']) ? $meminf['timeFormat'] : '');
            $meminf['timeOffset'] = (isset($meminf['timeOffset']) ? $meminf['timeOffset'] : '0');
            $meminf['lngfile'] = (strlen($meminf['lngfile']) > 2 ? $meminf['lngfile'] : $app->conf->language);
            
            $tdat['titlesEnable'] = $app->conf->titlesEnable;
            if ($app->user->isStaff() || $app->useer->posts >= 1000) $tdat['allowTitle'] = true;
            
            $meminf['time'] = date('h:i:s a', time() + $app->conf->timeoffset * 3600);
            $tdat['maxAvatarWidth'] = Max($app->conf->userpic_width, 100);
            $tdat['maxAvatarHeight'] = Max($app->conf->userpic_height, 100);
            $tdat['facesurl'] = $app->conf->facesurl;
            
            if ($app->conf->allowpics)
            {
                $tdat['allowpics'] = true;
                $tdat['piclimits'] = $app->conf->userpic_limits;
                $dir = opendir($app->conf->facesdir);
                $contents = array();
                while ($contents[] = readdir($dir)){;}
                closedir($dir);
                $tdat['images'] = array();
                natcasesort($contents);
                foreach ($contents as $line)
                {
                    $filename = substr($line, 0, (strlen($line) - strlen(strrchr($line, '.'))));
                    $extension = substr(strrchr($line, '.'), 1);
                    $checked = '';
                    if ($line == $meminf['avatar'])
                        $checked = ' selected="selected"';
                    if (preg_match('~^https?://~', $meminf['avatar']) && $line == 'blank.gif')
                        $checked = ' selected="selected"';
                    if (strcasecmp($extension, 'gif') == 0 || strcasecmp($extension,"jpg") == 0 || strcasecmp($extension, 'jpeg') == 0 || strcasecmp($extension, 'png') == 0 )
                    {
                        if ($line == 'blank.gif')
                            $filename = $app->locale->txt[422];
                        $filename = str_replace('_', ' ', $filename);
                        $tdat['images'][] = array($line, $filename, $checked); //"<option value=\"$line\"$checked>$filename</option>\n";
                    }
                }
                if (preg_match('~^https?://~', $meminf['avatar']))
                {
                    $meminf['pic'] = 'blank.gif';
                    $tdat['pic_checked'] = ' checked="checked"';
                    $meminf['perspic'] = $meminf['avatar'];
                }
                else
                {
                    $meminf['pic'] = $meminf['avatar'];
                    $meminf['perspic'] = null; //'http://';
                }
            } // if allowpics
            
            if ($app->user->name == $user && !$app->user->guest)
                $tdat['self'] = True;
            
            $meminf['hideEmail'] = $meminf['hideEmail'] == 1 ? 'checked' : '';
            $meminf['QuickReply'] = $meminf['QuickReply'] == 1 ? 'checked' : '';
            
            $tdat['siglen'] = $app->conf->MaxSigLen;
            $tdat['allow_hide_email'] = $app->conf->allow_hide_email;
            $tdat['QuickReply'] = $app->conf->QuickReply;
            $tdat['userLanguage'] = $app->conf->userLanguage;
            
            if ($app->conf->userLanguage)
            {
                $userLangs = array();
                $dir = dir($app->conf->boarddir);
                while ($entry = $dir->read())
                {
                    $n = ucfirst(substr($entry, 0, (strlen($entry) - 4)));
                    $e = substr($entry, (strlen($entry) - 4), 4);
                    if ($e == '.lng')
                    {
                        $selected = '';
                        if ($entry == $meminf['lngfile'])
                            $selected = 'selected';
                        $userLangs[] = array('entry' => $entry, 'name' => $n, 'selected' => $selected);
                    }
                }
                $meminf['userLangs'] = $userLangs;
            }
            
            // Prepare skins list
            $tdat['skins'] = array();
            $dir = dir(PROJECT_ROOT . '/templates/skins');
            while ($entry = $dir->read())
            {
                if ($entry != '.' && $entry != '..' && substr($entry, -4) != '.php')
                {
                    $selected = "";
                    if ($entry == $meminf['skin'])
                        $selected = "selected";
                    $tdat['skins'][] = array('entry' => $entry, 'selected' => $selected);
                }
            }
            
            // Prepare sounds list
            $tdat['sounds_prodigy'] = array();
            $dir = dir(PROJECT_ROOT . '/static/sounds/IM/Prodigy');
            while ($entry = $dir->read())
            {
                if ($entry != '.' && $entry != '..')
                {
                    $selected = '';
                    $filePath = "Prodigy/$entry";
                    if ($filePath == $meminf['imsound'])
                        $selected = 'selected';
                    $tdat['sounds_prodigy'][] = array('path' => $service->esc($filePath), 'name' => $service->esc($entry), 'selected' => $selected);
                }
            }
            
            $tdat['sounds_other'] = array();
            $dir = dir(PROJECT_ROOT . '/static/sounds/IM/Other');
            while ($entry = $dir->read())
            {
                if ($entry != '.' && $entry != '..')
                {
                    $selected = '';
                    $filePath = "Other/$entry";
                    if ($filePath == $meminf['imsound'])
                        $selected = 'selected';
                    $tdat['sounds_other'][] = array('path' => $service->esc($filePath), 'name' => $service->esc($entry), 'selected' => $selected);
                }
            }
            
            if ($app->user->isAdmin())
            {
                $tdat['admin'] = true;
                
                // building member groups options list
                $memberGroups = $app->user->memberGroups();
                $tdat['member_groups'] = array();
                foreach ($memberGroups as $memberGroup)
                {
                    $tr = $memberGroup;
                    
                    if ($memberGroup == $app->locale->admin)
                        $memberGroup = 'Administrator';
                    elseif ($memberGroup == $app->locale->globmod)
                        $memberGroup = 'Global Moderator';
                    
                    $selected = '';
                    if ($meminf['memberGroup'] == $memberGroup)
                        $selected = 'selected';
                    
                    $tdat['member_groups'][] = array('group' => $memberGroup, 'tr' => $tr, 'selected' => $selected);
                }
            }
            
            $tdat['karmaMode'] = $app->conf->karmaMode;
            $tdat['karmaLabel'] = $app->conf->karmaLabel;
            $tdat['karmaSmiteLabel'] = $app->conf->karmaSmiteLabel;
            $tdat['karmaApplaudLabel'] = $app->conf->karmaApplaudLabel;
            $meminf['totalKarma'] = $meminf['karmaGood'] - $meminf['karmaBad'];
            
            $COOKIES = $request->cookies();
            
            $tdat['flakes'] = (empty($COOKIES->disableSnowflakes2011) && $COOKIES->flakemode == 1) ? 'checked' : '';
            $tdat['chegaflake'] = (empty($COOKIES->disableSnowflakes2011) && $COOKIES->flakemode == 2) ? 'checked' : '';
            $tdat['noflakes'] = isset($COOKIES->disableSnowflakes2011) ? 'checked' : '';
            
            $tdat['mem'] = $meminf;
            
            $this->render('templates/profile/modify.template.php', $tdat);

        } // if GET
        elseif ($request->method('POST'))
        {
            $app->session->check('post');
            $POST = $request->paramsPost();
            
            $qValues = array();
            
            if ($app->user->isAdmin())
            {
                if ($app->conf->karmaMode)
                {
                    $qValues['karmaGood'] = $POST->karmaGood;
                    $qValues['karmaBad']  = $POST->karmaBad;
                }
            }
            else
            {
                $POST->user = $app->user->name;
                $POST->userID = $app->user->id;
                $POST->posts_count = $app->user->posts;
                $POST->user_group = $app->user->group;
            }
            
            $POST->language = (preg_match("/[^\w\.]/", $POST->language) ? '' : $POST->language);
            
            if ($app->user->name == $POST->user)
            {
                if (empty(trim($POST->oldpasswrd)))
                    return $this->error($app->locale->yse243 . ' ' . $app->locale->yse244, 400);
                if ($app->user->passwd != $app->subs->md5_hmac($POST->oldpasswrd, strtolower($POST->user)))
                    return $this->error($app->locale->yse24, 403);
            }
            
            $service->validateParam('posts_count', $app->locale->txt['749'])->isInt();
            
            if (!empty($POST->userpicpersonalcheck))
            {
                $POST->userpic = $POST->userpicpersonal;
                
                if(!preg_match('#^https?://#', $POST->userpic))
                    $POST->userpic = "http://{$POST->userpic}";
                
                // now let's validate the avatar
                $sizes = @getimagesize($POST->userpic);
                if ($sizes && (($sizes[0] > $app->conf->userpic_width && $app->conf->userpic_width != 0) || ($sizes[1] > $app->conf->userpic_height && $app->conf->userpic_height != 0)))
                    return $this->error("{$app->locale->yse227}  {$app->conf->userpic_width} x {$app->conf->userpic_height}");
            }
            
            if (strlen($POST->userpic) < 12)
                $POST->userpic = 'blank.gif';
            if (!$app->conf->allowpics)
                $POST->userpic = 'blank.gif';
            
            $q_pswrd = '';
            
            mt_srand(time());
            
            $POST->email = strtolower($POST->email);
            
            $POST->name = preg_replace("/[\s]/", ' ', $POST->name);
            $POST->name = $app->subs->clean_string($POST->name, array('html_entities' => true));
            
            if (empty($POST->name))
                return $this->error($app->locale->txt[75], 400);
            
            if (empty($POST->email))
                return $this->error($app->locale->txt[76], 400);
            
            $service->validate($POST->email, $app->locale->txt[500])->
              isRegex("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/")->
              isRegex("/^[0-9A-Za-z@\._\-]+$/")->
              notRegex("/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)|(\.$)/");
            
            $newpassemail = false;
            
            if ($app->conf->emailnewpass && $POST->email != $app->user->emailAddress && !$app->user->isAdmin())
            {
                $POST->passwrd1 = crypt(mt_rand(-100000, 100000));
                $POST->passwrd1 = preg_replace("/\W/", '', $POST->passwrd1);
                $POST->passwrd1 = substr($POST->passwrd1, 0, 10);
                $newpassemail = true;
            }
            else
            {
                if ($POST->passwrd1 != $POST->passwrd2)
                    return $this->error("({$POST->user}) {$app->locale->txt[213]}");
                if ($POST->passwrd1 != '')
                    $qValues['passwd'] = $app->subs->md5_hmac($POST->passwrd1, strtolower($POST->user));
            }
            
            if ($POST->moda != '-1')  // if we aren't saying "Delete user";
            {
                //$POST->signature = preg_replace("/(http\:\/\/[\S]+)/i", "", $POST->signature);
                $POST->signature = preg_replace(array("/\[url[^\[]+\[\/url\]/i", "/\[iurl[^\[]+\[\/iurl\]/i", "/[a-zа-я]+:\/\/[\S]+/i", "/[wв]{3}.[\S]+/i", "/[a-zа-я]+\.[a-zа-я]{2,4}/i"), "", $POST->signature);
                if (strlen($POST->signature) > $app->conf->MaxSigLen)
                    $POST->signature = substr($POST->signature, 0, $app->conf->MaxSigLen);
                
                $POST->icq = preg_replace("/[^0-9]/", '', $POST->icq);
                
                $POST->bday1 = preg_replace("/[^0-9]/", '', $POST->bday1);
                $POST->bday2 = preg_replace("/[^0-9]/", '', $POST->bday2);
                $POST->bday3 = preg_replace("/[^0-9]/", '', $POST->bday3);
                
                if (empty($POST->bday1) || empty($POST->bday2) || empty($POST->bday3))
                    $POST->bday = '';
                else
                    $POST->bday = "{$POST->bday3}-{$POST->bday1}-{$POST->bday2}";
                
                $POST->aim = str_replace(' ', '+', $POST->aim);
                $POST->msn = str_replace(' ', '+', $POST->msn);
                
                if (!$app->user->isAdmin() || $POST->dr == $app->locale->txt[470])
                    $POST->dr = '';
                else
                {
                    if (($POST->dr = strtotime($POST->dr)) === -1)
                    {
                        $fmt = '%d %b %Y ' . (substr_count($app->user->timeFormat, '%H') == 0 ? '%I:%M:%S %p' : '%T');
                            $dr = strftime($fmt, time() + ($app->conf->timeoffset * 3600));
                            return $this->error("{$app->locale->txt['yse233']} $dr");
                    }
                    else
                    {
                        $timeadjust = ((empty($app->user->timeOffset) ? 0 : $app->user->timeOffset) + $app->conf->timeoffset) * 3600;
                        $POST->dr = $POST->dr - $timeadjust;
                        $qValues['dateRegistered'] = $POST->dr;
                    }
                }
                
                # store the name temorarily so we can restore any _'s later
                $tempname = $POST->name;
                $POST->name = str_replace('_', ' ', $POST->name);
                
                if (empty(trim($POST->name)))
                    return $this->error($app->locale->txt[75], 400);
                
                if (empty($POST->usertimeoffset))
                    $POST->usertimeoffset = 0;
                else
                {
                    $POST->usertimeoffset = str_replace(',', '.', $POST->usertimeoffset);
                    $POST->usertimeoffset = preg_replace("/[^\d*|\.|\-|w*]/", '', $POST->usertimeoffset);
                }
                
                if ($POST->usertimeoffset < -23.5 || $POST->usertimeoffset > 23.5)
                    return $this-error($app->locale->txt[487]);
                
                $dbst = $app->db->prepare("SELECT ID_MEMBER FROM {$db_prefix}members WHERE memberName != ? AND (realName = ? OR memberName = ?)");
                $dbst->execute(array($POST->user, $POST->name, $POST->name));
                if ($dbst->fetchColumn())
                    return $this->error("({$POST->name}) {$app->locale->txt[473]}");
                
                $dbst = $app->db->prepare("SELECT membergroup FROM {$db_prefix}membergroups WHERE membergroup = ?");
                $dbst->execute(array($POST->name));
                if ($dbst->fetchColumn())
                    return $this->error("{$app->locale->txt[244]} {$POST->name}");
                
                $dbst = $app->db->prepare("SELECT ID_MEMBER FROM {$db_prefix}members WHERE memberName != ? AND  emailAddress = ?");
                $dbst->execute(array($POST->user, $POST->email));
                if ($dbst->fetchColumn())
                    return $this->error("{$app->locale->txt[730]} ({$POST->email}) {$app->locale->txt[731]}");
                $dbst = null; // closing statement
                
                if (!$app->user->isAdmin())
                {
                    $dbrq = $app->db->query("SELECT setting,value FROM {$db_prefix}reserved_names WHERE 1");
                    $reserve = array();
                    $matchcase = $matchname = $matchuser = $matchword = 0;
                    while ($row = $dbrq->fetch(\PDO::FETCH_NUM))
                    {
                        if ($row[0] == "word")
                            $reserve[] = trim($row[1]);
                        else
                            ${$row[0]} = trim($row[1]);
                    }
                    $dbrq = null;
                    $namecheck = ($matchcase ? $POST->name : strtolower($POST->name));
                    
                    if ($reserve)
                    {
                        foreach ($reserve as $reserved)
                        {
                            $reservecheck = $matchcase ? $reserved : strtolower ($reserved);
                            if ($matchname)
                            {
                                if ($matchword && $namecheck == $reservecheck)
                                    return $this->error("{$app->locale->txt[244]} $reserved");
                                elseif (!$matchword && @strstr($namecheck, $reservecheck))
                                    return $this->error("{$app->locale->txt[244]} $reserved");
                            }
                        }
                    }
                
                } // not admin #619
                
                # let's restore the name now
                $POST->name = $tempname;
                
                $hideEmail = $app->subs->isset($POST->hideemail);
                $QuickReply = $app->subs->isset($POST->QuickReply);
                $showCommentsValue = $app->subs->isset($POST->showComments);
                $blockComments = $app->subs->isset($POST->blockComments);
                $closeCommentsByDefault = $app->subs->isset($POST->closeCommentsByDefault);
                
                if ($app->conf->QuickReply)
                    $qValues['QuickReply'] = $QuickReply;
                
                // Validate and normalize user's websiteurl
                if (!empty($POST->websiteurl))
                {
                    $POST->websiteurl = trim($POST->websiteurl);
                    if (!$app->subs->ishttpurl($POST->websiteurl))
                        $POST->websiteurl = 'http://' . $POST->websiteurl;
                    
                    if (strlen($POST->websiteurl) < 11)
                        $POST->websiteurl = '';
                }
                else
                    $POST->websiteurl = '';
               
                if (!empty($POST->yim))
                {
                    $POST->yim = trim($POST->yim);
                    $POST->yim = str_replace(' ', '+', $POST->yim);
                    if (!$app->subs->ishttpurl($POST->yim))
                        $POST->yim = "http://" . $POST->yim;
                }
                else
                    $POST->yim = '';
                
                $flakeCookieExpireDate = time() + 3*31*24*60*60;
                if ($POST->snowflakes > 0)
                {
                    $response->cookie("disableSnowflakes2011", "", time() - 3600);
                    $response->cookie("flakemode", $POST->snowflakes, $flakeCookieExpireDate);
                }
                else
                    $response->cookie("disableSnowflakes2011", "1", $flakeCookieExpireDate);
                
                $q_title = '';
                if ($app->conf->titlesEnable && ($app->user->isStaff()))
                    //$q_title = $POST->usertitle;
                    $qValues['usertitle'] = substr(trim($POST->usertitle),0,20);
                
                $memIP = $request->server()->get('REMOTE_ADDR');
                
                $qValues['realName']               = substr($POST->name, 0, 30);
                $qValues['showComments']           = $showCommentsValue;
                $qValues['blockComments']          = $blockComments;
                $qValues['closeCommentsByDefault'] = $closeCommentsByDefault;
                $qValues['emailaddress']           = $POST->email;
                $qValues['websiteTitle']           = $POST->websitetitle;
                $qValues['websiteUrl']             = $POST->websiteurl;
                $qValues['signature']              = $POST->signature;
                $qValues['posts']                  = $POST->posts_count;
                $qValues['memberGroup']            = $POST->user_group;
                $qValues['ICQ']                    = $POST->icq;
                $qValues['MSN']                    = $POST->msn;
                $qValues['AIM']                    = $POST->aim;
                $qValues['YIM']                    = $POST->yim;
                $qValues['gender']                 = $POST->gender;
                $qValues['personalText']           = $POST->usertext;
                $qValues['avatar']                 = $POST->userpic;
                $qValues['location']               = $POST->location;
                $qValues['birthdate']              = $POST->bday;
                $qValues['lngfile']                = $POST->language;
                if ($app->user->name == $POST->user)
                    $qValues['memberIP']           = $memIP;
                $qValues['timeFormat']             = $POST->usertimeformat;
                $qValues['timeOffset']             = $POST->usertimeoffset;
                $qValues['secretQuestion']         = $POST->secretQuestion;
                $qValues['secretAnswer']           = $POST->secretAnswer;
                $qValues['skin']                   = $POST->skin;
                $qValues['imsound']                = $POST->imsound;
                $qValues['hideEmail']              = $hideEmail;
                
                $assignment_list = $app->db->build_placeholders($qValues, true, true);
                
                $dbst = $app->db->prepare("UPDATE {$db_prefix}members SET $assignment_list
                    WHERE memberName = :whereMember");
                
                $qValues['whereMember'] = $POST->user;
                
                $dbst->execute($qValues);
                $dbst = null; // closing statement
                
                $app->subs->updateStats('member');
                
                if ($POST->allComments == "CLOSE")
                {
                    $app->db->prepare("
                        UPDATE {$db_prefix}messages
                        SET closeComments = 1 WHERE ID_MEMBER = ?")->execute(array($POST->userID));
                }
                else if ($POST->allComments == "OPEN")
                {
                    $app->db->prepare("
                        UPDATE {$db->db_prefix}messages
                        SET closeComments = 0 WHERE ID_MEMBER = ?")->execute(array($POST->userID));
                }
                
                if ($newpassemail)
                {
                    $app->user->offline();
                    
                    $pswd = $app->subs->md5_hmac($POST->passwrd1, strtolower($POST->user));
                    $app->db->prepare("UPDATE {$db_prefix}members SET passwd = ? WHERE memberName= ?")->execute(array($pswd, $POST->user));
                    
                    $euser = urlencode($POST->user);
                    $app->im->sendmail($POST->email, "{$app->locale->txt[700]} {$app->conf->mbname}", "{$app->locale->txt[733]} $pswd {$app->locale->txt[734]} {$POST->user}.\n\n{$app->locale->txt[701]}" . SITE_URL . "/people/$euser/");
                    
                    return $this->redirect("/login/?user=$euser&newpass=1");
                }
                else
                {
                    if ($POST->user == $app->user->name)
                    {
                        if (!empty($POST->passwrd1))
                        {
                            $ctime = time() + (60 * $app->conf->Cookie_Length);
                            
                            $passwrd = $app->subs->md5_hmac($POST->passwrd1, strtolower($POST->user));
                            $password = $app->subs->md5_hmac($passwrd, $app->conf->pwseed);
                            $cookie = serialize(array($POST->userID, $password));
                            
                            $this->cookie($app->conf->cookiename, $cookie, $ctime, null, null, $request->isSecure());
                        }
                    }
                    $euser = urlencode($POST->user);
                    return $this->redirect("/people/$euser/");
                }
            } // moda != -1
            else
            {    // if we did say "delete user"
                if (($app->user->isAdmin() || $POST->user == $app->usr->name))
                {
                    $app->db->prepare("UPDATE {$db_prefix}messages SET ID_MEMBER='-1' WHERE ID_MEMBER=?")->
                        execute(array($POST->userID));
                    $app->db->prepare("DELETE FROM {$db_prefix}members WHERE memberName=?")->
                        execute(array($POST->user));
                    $app->db->prepare("DELETE FROM {$db_prefix}log_topics WHERE ID_MEMBER=?")->
                        execute(array($POST->userID));
                    $app->db->prepare("DELETE FROM {$db_prefix}log_boards WHERE ID_MEMBER=?")->
                        execute(array($POST->userID));
                    $app->db->prepare("DELETE FROM {$db_prefix}log_mark_read WHERE ID_MEMBER=?")->
                        execute(array($POST->userID));
                    $app->db->prepare("DELETE FROM {$db_prefix}instant_messages WHERE toName=? AND deletedBy=0")->
                        execute(array($POST->user));
                    $app->db->prepare("DELETE FROM {$db_prefix}instant_messages WHERE fromName=? AND deletedBy=1")->
                        execute(array($POST->user));
                    $app->db->prepare("UPDATE {$db_prefix}instant_messages SET deletedBy=1 WHERE toName=?")->
                        execute(array($POST->user));
                    $app->db->prepare("UPDATE {$db_prefix}instant_messages SET deletedBy=0 WHERE fromName=?")->
                        execute(array($POST->user));
                    
                    // Remove the user from moderator position (if needed)
                    $dbrq = $app->db->query("SELECT moderators, ID_BOARD FROM {$db_prefix}boards");
                    while ($curboard = $dbrq->fetch())
                    {
                        $moderator2 = array();	$updateboard = 0;
                        foreach (explode(',', $curboard['moderators']) as $key => $mod)
                        {
                            $mod = trim($mod);
                            if ($mod == $POST->user)
                                $updateboard = 1;
                            else
                                $moderator2[] = $mod;
                        }
                        if ($updateboard==1)
                        {
                            $moderator = implode(',', $moderator2);
                            $app->db->prepare("UPDATE {$db_prefix}boards SET moderators=? WHERE ID_BOARD=?")->
                                execute(array($moderator, $curboard['ID_BOARD']));
                        }
                    }
                    $dbrq = null; // closing statement
                } // if admin or self #823
                
                $dbrq = $app->db->query("SELECT ID_TOPIC, notifies FROM {$db_prefix}topics WHERE notifies != ''");
                while ($row = $dbrq->fetch(\PDO::FETCH_NUM))
                {
                    $entries = explode(',', $row[1]);
                    $entries2 = array();
                    
                    foreach($entries as $entry)
                        if (strcasecmp($entry, $POST->userID) != 0)
                            $entries2[] = $entry;
                    
                    $notifies = implode(',', $entries2);
                    $app->db->prepare("UPDATE {$db_prefix}topics SET notifies=? WHERE ID_TOPIC=?")->
                        execute(array($notifies, $row[0]));
                }    
                $dbrq = null;
                $app->subs->updateStats('member');
                    
                if (!$app->user->isAdmin())
                    $app->user->offline();
                
                return $this->redirect('/');
            } //  delete user
        } // if POST
    } // edit()

    public function messages($request, $response, $service, $app)
    {
        $PARAMS = $request->paramsNamed();
        
        if ($app->user->inIgnore($PARAMS->user))
            return $this->error($app->locale->ignore_user1);
        
        $app->board->load(-1);
        
        $profile = $app->user->loadDisplay($PARAMS->user);
        
        if(!$profile['found'])
            return $this->error('Profile not found.');
        
        $permit = 0;
        if ($app->user->isStaff())
            $permit = 1;
        
        // get one additional message
        $limit = empty($app->conf->maxmessagedisplay) ? 21 : (int) $app->conf->maxmessagedisplay + 1;
        
        $start = (int) $PARAMS->start;
        $q_start = $start > 0 ? "AND m.ID_MSG <= $start" : '';

        $db_prefix = $app->db->prefix;
        
        // Get messages number
        $dbst = $app->db->prepare("
            SELECT COUNT(*)
            FROM {$db_prefix}messages as m, {$db_prefix}topics as t, {$db_prefix}boards as b, {$db_prefix}categories as c, {$db_prefix}members as mem
            WHERE m.ID_MEMBER=?
                AND m.ID_TOPIC=t.ID_TOPIC
                AND t.ID_BOARD=b.ID_BOARD
                AND b.ID_CAT=c.ID_CAT
                AND (FIND_IN_SET(?,c.memberGroups) != 0 || $permit || c.memberGroups='')
                AND mem.ID_MEMBER=m.ID_MEMBER
                $q_start");
        $dbst->execute(array($profile['ID_MEMBER'], $app->user->group));
        $number = (int) $dbst->fetchColumn();
        $dbst = null;
        
        $dbst = $app->db->prepare("
            SELECT m.*,t.numReplies,c.memberGroups,c.name as cname,b.name as bname,b.ID_BOARD
            FROM {$db_prefix}messages as m, {$db_prefix}topics as t, {$db_prefix}boards as b, {$db_prefix}categories as c, {$db_prefix}members as mem
            WHERE m.ID_MEMBER=?
                AND m.ID_TOPIC=t.ID_TOPIC
                AND t.ID_BOARD=b.ID_BOARD
                AND b.ID_CAT=c.ID_CAT
                AND (FIND_IN_SET(?,c.memberGroups) != 0 || $permit || c.memberGroups='')
                AND mem.ID_MEMBER=m.ID_MEMBER
                $q_start
            ORDER BY m.ID_MSG DESC LIMIT $limit");
        $dbst->execute(array($profile['ID_MEMBER'], $app->user->group));
        $messages = array();
        while ($msg = $dbst->fetch())
        {
            $msg['body'] = $app->subs->censorTxt($msg['body']);
            $msg['subject'] = $app->subs->censorTxt($msg['subject']);
            $msg['posterTime'] = $app->subs->timeformat($msg['posterTime']);
            $messages[$number] = $msg;
            $number--;
        } // while fetch()
        $dbst = null; // closig this statement
        
        if (empty($messages))
            return $this->error('Messages not found.');
        
        $data = array(
            'title' => $app->locale->txt[214] . ' ' . $service->esc($profile['realName']),
            'profile' => $profile,
            'notify' => $app->conf->enable_notification,
            'ubbc' => $app->conf->enable_ubbc,
            'messages' => $messages
        );
        
        //// BEGIN prepare pagination data
        if (count($messages) > $app->conf->maxmessagedisplay)
        {
            // we no more need additional msg
            $msg = array_pop($data['messages']);
            $data['page_next'] = $msg['ID_MSG'];
        }
        
        if ($start > 0)
        {
            // find start of prev page
            $dbst = $app->db->prepare("SELECT ID_MSG FROM (SELECT m.ID_MSG FROM {$db_prefix}messages as m, {$db_prefix}topics as t, {$db_prefix}boards as b, {$db_prefix}categories as c
                WHERE ID_MEMBER = ?
                AND m.ID_TOPIC=t.ID_TOPIC
                AND t.ID_BOARD=b.ID_BOARD
                AND b.ID_CAT=c.ID_CAT
                AND (FIND_IN_SET(?,c.memberGroups) != 0 || $permit || c.memberGroups='')
                AND ID_MSG > ?
                ORDER BY ID_MSG LIMIT ?) AS m
                ORDER BY ID_MSG DESC");
            $dbst->execute(array($profile['ID_MEMBER'], $app->user->group, reset($messages)['ID_MSG'], $app->conf->maxmessagedisplay + 1));
            $prevIDs = $dbst->fetchAll(\PDO::FETCH_COLUMN);
            $dbst = null;
            
            $cnt = count($prevIDs);
            if ($cnt > 0)
                // Show link to the beginning
                $data['page_start'] = true;
            if ($cnt > $app->conf->maxmessagedisplay)
                // prev page is not a last page, show link to prev page
                $data['page_prev'] = $prevIDs[1];
        }
        
        // find start of end page
        $dbst = $app->db->prepare("SELECT ID_MSG FROM (SELECT ID_MSG FROM {$db_prefix}messages as m, {$db_prefix}topics as t, {$db_prefix}boards as b, {$db_prefix}categories as c
            WHERE m.ID_TOPIC=t.ID_TOPIC
            AND t.ID_BOARD=b.ID_BOARD
            AND b.ID_CAT=c.ID_CAT
            AND (FIND_IN_SET(?,c.memberGroups) != 0 || $permit || c.memberGroups='')
            AND ID_MEMBER = ?
            ORDER BY ID_MSG LIMIT ?) as m ORDER BY ID_MSG DESC LIMIT 1");
        $dbst->execute(array($app->user->group, $profile['ID_MEMBER'], $app->conf->maxmessagedisplay ));
        $lastID = $dbst->fetchColumn();
        $dbst = null;
        
        $data['page_last'] = $lastID;
        
        if (empty($data['page_next']))
            // Next page is last, don't show next link.
            $data['page_last'] = null;
        elseif ($data['page_next'] == $data['page_last'])
            // Next page is last, no need for next page link.
            $data['page_next'] = null;
            
        
        
        $data['page_base_url'] = SITE_ROOT . "/people/".urlencode($PARAMS->user)."/messages";
        
        // END prepare pagination data
        
        return $this->render('templates/profile/messages.template.php', $data);
    } // messages()
}
