<?php
namespace Prodigy\Respond;

class Profile extends Respond
{
    public function show($request, $response, $service, $app)
    {
        $user = $request->paramsNamed()->get('user');
        
        $db_prefix = $app->db->prefix;
        $dbst = $app->db->prepare("
            SELECT memberName, realName, passwd, emailAddress, websiteTitle, websiteUrl, signature, posts, memberGroup, ICQ, AIM, YIM, gender, personalText, avatar, dateRegistered, location, birthdate, timeFormat, timeOffset, hideEmail, ID_MEMBER, usertitle, karmaBad, karmaGood, lngfile, MSN, memberIP
            FROM {$db_prefix}members
            WHERE memberName=?");
        $dbst->bind_param('s', $user);
        $dbst->execute();
        $dbrs = $dbst->get_result();
        $dbst->close();
        
        if ($dbrs->num_rows == 0)
        {
            $errmsg = "$user: " . $app->locale->txt[453];
            return $this->error($errmsg);
        }
        
        $meminf = $dbrs->fetch_assoc();
        $dbrs->free();
        
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
            $dbst->bind_param('ii', $app->user->id, $meminf['ID_MEMBER']);
            $dbst->execute();
            $dbst->store_result();
            if ($dbst->num_rows > 0)
                $meminf['following'] = true;
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
                $dbrq = $app->db->query("
                    SELECT COUNT(ID_MSG)
                    FROM {$db_prefix}messages
                    WHERE ID_MEMBER={$meminf['ID_MEMBER']}
                    AND posterTime>" . (time() - (time() % 86400)), false);
                list ($post_count) = $dbrq->fetch_row();
                $dbrq->free();
                $meminf['posts'] .= ' (' . $post_count . ' ' . $app->locale->postsper_today . ')';
            }
            // Posts: xxxx (yy.yy per day, zz today)
            elseif ($app->conf->postsPerDay == 3)
            {
                // Get the posts made today.
                $dbrq = $app->db->query("
                     SELECT COUNT(ID_MSG)
                    FROM {$db_prefix}messages
                    WHERE ID_MEMBER={$meminf['ID_MEMBER']}
                    AND posterTime>" . (time() - (time() % 86400)));
                list ($post_count) = $dbrq->fetch_row();
                $dbrq->free();
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


}
