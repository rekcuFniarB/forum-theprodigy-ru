<?php

namespace Prodigy\Admin;

// Admin controller
Class Admin extends \Prodigy\Respond\Respond {
    
    public function __construct($router)
    {
        parent::__construct($router);
        $this->service->namespace = SITE_ROOT . '/admin';
    }
    
    // Admin dashbooard controller
    public function dashboard($request, $response, $service, $app) {
        if (!$app->user->isAdmin())
            return $this->error(null, 403);
        
        $db_prefix = $app->db->prefix;
        
        $data = array(
            'title' => $app->locale->txt[208],
            'adminName' => $app->user->realName,
            'maxdays' => $app->conf->maxdays,
            'totalt' => $app->conf->totalTopics,
            'totalm' => $app->conf->totalMessages,
            'memcount' => $app->conf->memberCount,
            'latestmember' => $app->conf->latestMember,
            'latestRealName' => $app->conf->latestRealName,
            'lastPost' => $app->main->LastPost('admin'),
        );
        
        $dbrq = $app->db->query("SELECT COUNT(*) FROM {$db_prefix}categories");
        $data['numcats'] = $dbrq->fetch(\PDO::FETCH_COLUMN); $dbrq = null;
        
        $dbrq = $app->db->query("SELECT COUNT(*) FROM {$db_prefix}boards");
        $data['numboards'] = $dbrq->fetch(\PDO::FETCH_COLUMN); $dbrq = null;
        
        // and load the administrators
        $dbrq = $app->db->query("SELECT memberName, realName FROM {$db_prefix}members WHERE memberGroup='Administrator'");
        $data['admins'] = $dbrq->fetchAll(); $dbrq = null;
        
        return $this->render('admin/dashboard.phtml', $data);
    } // dashboard()
    
    public function editnews($request, $response, $service, $app) {
        if (!$app->user->isAdmin())
            return $this->error(null, 403);
        
        $data = array(
            'news' => $app->conf->news,
            'title' => $app->locale->txt[7]
        );
        
        if ($request->method('GET')) {
            return $this->render('admin/editnews.phtml', $data);
        }
        elseif ($request->method('POST')) {
            $app->session->check('post');
            $news = $request->paramsPost()->news;
            $app->conf->modSet('news', $news);
            return $this->redirect('/admin/');
        }
    } // editnews()
    
    public function editagreement($request, $response, $service, $app)
    {
        if (!$app->user->isAdmin())
            return $this->error(null, 403);
        
        if ($request->method('GET'))
        {
            
            $data = array(
                'title' => $app->locale->yse11
            );
            
            $dbrq = $app->db->query("SELECT value FROM {$app->db->prefix}settings WHERE variable='agreement'");
            $data['agreement'] = $dbrq->fetchColumn();
            $dbrq = null;
            return $this->render('admin/editagreement.phtml', $data);
        }
        elseif ($request->method('POST'))
        {
            $app->session->check('post');
            $app->conf->modSet('agreement', $request->paramsPost()->agreement);
            return $this->redirect('/admin/');
        }
    }
    
    // Bans list controller
    public function bans($request, $response, $service, $app)
    {
        if (!$app->user->isStaff())
            return $this->error(null, 403);
        
        $db_prefix = $app->db->db_prefix;
        
        if ($request->method('GET'))
        {
            $app->security->remove_expired_bans();
            
            $service->title = $app->locale->txt['enhancedban1'];
            
            $GET = $request->paramsGet();
            $order = $GET->order;
            $desc = $GET->desc;
            
            // Configuring order
            $aDesc = array(0,0,0,0,0);
            if ($order == 1)
            {
                $order = " ORDER BY Name";
                if ($desc == 1)
                {
                    $order .= " DESC";
                    $aDesc[0] = 0;
                }
                else
                    $aDesc[0] = 1;
            }
            elseif ($order == 2)
            {
                $order = " ORDER BY Mail";
                if ($desc == 1)
                {
                    $order .= " DESC";
                    $aDesc[1] = 0;
                }
                else
                    $aDesc[1] = 1;
            }
            elseif ($order == 3)
            {
                $order = " ORDER BY IP";
                if ($desc == 1)
                {
                    $order .= " DESC";
                    $aDesc[2] = 0;
                }
                else
                    $aDesc[2] = 1;
            }
            elseif ($order == 4)
            {
                $order = " ORDER BY Reason";
                if ($desc == 1)
                {
                    $order .= " DESC";
                    $aDesc[3] = 0;
                }
                else
                    $aDesc[3] = 1;
            }
            else
            {
                $order = " ORDER BY BannedUntil";
                if ($desc == 1)
                {
                    $order .= " DESC";
                    $aDesc[4] = 0;
                }
                else
                    $aDesc[4] = 1;
            }
            
            $dbrq = $app->db->query("SELECT bt.*,mem.realName,mem.memberName,mem.emailAddress,mem.memberIP,mem2.memberName AS bbMemberName,mem2.realName AS bbRealName
            FROM {$db_prefix}banned_enh AS bt
            LEFT JOIN {$db_prefix}members as mem ON (bt.ID_MEMBER = mem.ID_MEMBER)
            LEFT JOIN {$db_prefix}members as mem2 ON (bt.BannedBy = mem2.ID_MEMBER)
            WHERE 1" . $order);
            $banned_members = $dbrq->fetchAll();
            $dbrq = null;
            
            $banned_members = array_map(function($ban) use ($app) {
                if ($ban['BannedUntil'] != '')
                    $ban['BannedUntil'] = $app->subs->timeformat($ban['BannedUntil']);
                else
                    $ban['BannedUntil'] = $app->locale->txt['enhancedban7'];
                
                return $ban;
            }, $banned_members);
            
            $aMember = array('memberName' => '', 'emailAddress' => '', 'memberIP' => '');
            
            // Get requested member detail and fill fields with his info
            if (!empty($GET->memid))
            {
                $dbst = $app->db->prepare("
                SELECT * FROM {$db_prefix}members
                WHERE ID_MEMBER = ?
                LIMIT 1");
                $dbst->execute(array($GET->memid));
                $aMember = $dbst->fetch();
                $dbst = null;
            }
            
            return $this->render('admin/bans.phtml',
                array(
                    'aDesc' => $aDesc,
                    'banned_members' => $banned_members,
                    'aMember' => $aMember,
                )
            );
        } // if GET
        elseif ($request->method('POST'))
        {
            $app->session->check('post');
            $POST = $request->paramsPost();
            if($POST->ban == 'ban')
            {
                // ban member
                $aToBan = array();
                $profile_match = false;

                if (!empty($POST->memid)) // request came from profile page
                {
                    $dbst = $app->db->prepare("SELECT * FROM {$db_prefix}members
                        WHERE ID_MEMBER = ?
                        LIMIT 1");
                    $dbst->execute(array($POST->memid));
                    $q_mem = $dbst->fetch(); $dbst = null;
                    if (!empty($q_mem))
                    {
                        $aToBan['ID_MEMBER'] = $q_mem['ID_MEMBER'];
                        $aToBan['Name'] = $q_mem['memberName'];
                        $aToBan['Mail'] = $q_mem['emailAddress'];
                        $aToBan['IP'] = $q_mem['memberIP'];
                        $profile_match = true;
                    }
                    else
                        return $this->error('enhancedban12');
                    // redirect to profile
                    $redirect_on_finish = "/people/" . urlencode($q_mem['memberName']);
                } // request came from profile page
                else
                { // request came from bans list
                    if (!empty($POST->uname))
                    {
                        // Ban by user name
                        $dbst = $app->db->prepare("SELECT ID_MEMBER, memberName, passwd, emailAddress, memberIP from members WHERE memberName = ?");
                        $dbst->execute(array($POST->uname));
                        $q_mem = $dbst->fetch(); $dbst = null;
                        if (!empty($q_mem)){
                            $aToBan['Name'] = $POST->uname;
                            $aToBan['ID_MEMBER'] = $q_mem['ID_MEMBER'];
                            $aToBan['Mail'] = $q_mem['emailAddress'];
                            $aToBan['IP']  = $q_mem['memberIP'];
                            $profile_match = true;
                        }
                        else
                            return $this->error("No such user {$POST->uname}.");
                    }

                    if (!empty($POST->email) && empty($POST->uname))
                    {
                        // Ban by email
                        $aToBan['Mail'] = $POST->email;
                        $dbst = $app->db->prepare("SELECT ID_MEMBER, memberName, passwd, memberIP from members where emailAddress = ?");
                        $dbst->execute(array($POST->email));
                        $q_mem = $dbst->fetch(); $q_mem = null;
                        if (!empty($q_mem)){
                            $aToBan['Name'] = $q_mem['memberName'];
                            $aToBan['ID_MEMBER'] = $q_mem['ID_MEMBER'];
                            $aToBan['IP']= $q_mem['memberIP'];
                            $profile_match = true;
                        }
                    }

                    if (!empty($POST->ipaddy) && !$profile_match){
                        $aToBan['IP'] = $POST->ipaddy;
                    }
                    
                    if (empty($POST->ipaddy) && !empty($POST->fingerprint) && !$profile_match) {
                        // ban fingerprint
                        $aToBan['IP'] = "_fp_{$POST->fingerprint}";
                    }
                } // if request came from bans list
                
                if (!empty($POST->timetoban))
                {
                    $notice_days = $POST->timetoban;
                    // convert timetoban from days into a time from now
                    $timetoban = strtotime("+{$POST->timetoban} days");
                    $aToBan['BannedUntil'] = $timetoban;
                }
                else
                    $notice_days = $app->locale->txt('enhancedban7');
                
                if (!empty($POST->reason))
                    $aToBan['Reason'] = $POST->reason;
                else
                    return $this->error('enhancedban10');
                
                $aToBan['DateBanned'] = time();
                $aToBan['BannedBy'] = $app->user->id;
                $sToBan = "INSERT INTO {$db_prefix}banned_enh SET " . $app->db->build_placeholders($aToBan, true, true);
                $dbst = $app->db->prepare($sToBan);
                $dbst->execute($aToBan); $dbst = null;
                
                // Mark user as banned by prepending "B:" to the password field
                // This will also disallow logging in
                if ($profile_match && strpos($q_mem['passwd'], 'B:') === false)
                {
                    // mark user as banned
                    $app->db->query("UPDATE {$db_prefix}members SET passwd = INSERT(passwd, 1, 0, 'B:') WHERE ID_MEMBER = '{$q_mem['ID_MEMBER']}' AND passwd NOT LIKE 'B:%'");
                    
                    // Notify user
                    $notice_subject = "{$app->locale->enhancedban_subj} {$app->locale->txt[176]} {$app->conf->mbname}";
                    $e_username = rawurlencode($app->user->name);
                    $notice_msg = "{$app->locale->enhancedban_subj} {$app->locale->txt[176]} «{$app->conf->mbname}» {$app->locale->enhancedban25} [url={$service->siteurl}/people/$e_username/]{$app->user->realname}[/url].\n[b]{$app->locale->enhancedban13}[/b]: {$POST->reason}.\n{$app->locale->enhancedban8} $notice_days.";
                    $app->im->send_notice($q_mem['ID_MEMBER'], $notice_subject, $notice_msg);
                    
                    // Notify admins
                    $notice_subject = "{$app->locale->enhancedban1} {$app->locale->txt[176]} {$app->conf->mbname}";
                    $notice_msg = "[url={$service->siteurl}/people/".rawurlencode($q_mem['memberName'])."/]{$q_mem['memberName']}[/url] {$app->locale->enhancedban18} {$app->locale->enhancedban25} [url={$service->siteurl}/people/$e_username/]{$app->user->realname}[/url].\n[b]{$app->locale->enhancedban13}[/b]: {$POST->reason}.\n{$app->locale->enhancedban8} $notice_days.";
                    $app->im->notifyAdmins($notice_subject, $notice_msg);
                } // mark user banned
                
            } // ban request
            elseif ($POST->ban == "remove")
            {
                if (empty($POST->memid)) // requests by checkboxes on bans list page
                {
                    $unban_ids = array();
                    foreach( $POST->all() as $postVar => $postVarValue )
                    {
                        if (strcmp(substr($postVar, 0, 6), 'unban_') == 0)
                        {
                            $id = substr($postVar, 6);
                            $unban_ids[] = intval($id);
                        }
                    }
                    if(count($unban_ids) > 0){
                        $q_unban_ids = implode(', ', $unban_ids);
                        $q_mem_id = array();
                        $q_mem_name = array();
                        $q_mem_mail = array();
                        $dbrq = $app->db->query("SELECT ID_MEMBER, Name, Mail from {$db_prefix}banned_enh WHERE ID_BANNED IN ($q_unban_ids)");
                        $banned_rows = $dbrq->fetchAll(); $dbrq = null;
                        if (!empty($banned_rows)){
                            foreach ($banned_rows as $r_fetch){
                                if (!empty($r_fetch['ID_MEMBER']))
                                    $q_mem_id[] = $r_fetch['ID_MEMBER'];
                                if (!empty($r_fetch['Name']))
                                    $q_mem_name[] = $r_fetch['Name'];
                                if (!empty($r_fetch['Mail']))
                                    $q_mem_mail[] = $r_fetch['Mail'];
                            }
                            $unmarksql = array();
                            if (count($q_mem_id) > 0) {
                                $unmarksql[] = 'm.ID_MEMBER IN (' . $app->db->build_placeholders($q_mem_id) . ') ';
                            }
                            if (count($q_mem_name) > 0){
                                $unmarksql[] = 'memberName IN (' . $app->db->build_placeholders($q_mem_name) . ') ';
                            }
                            if(count($q_mem_mail) > 0){
                                $unmarksql[] = 'emailAddress IN (' . $app->db->build_placeholders($q_mem_mail) . ') ';
                            }
                            // Remove rows of selected ID's from bans list
                            $app->db->query("DELETE FROM {$db_prefix}banned_enh WHERE ID_BANNED IN ($q_unban_ids)");
                            if (count($unmarksql) > 0){
                                $unmarksql = implode(' OR ', $unmarksql);
                                // Unmark people not in ban table
                                $dbst = $app->db->prepare("UPDATE {$db_prefix}members m LEFT JOIN {$db_prefix}banned_enh b ON m.ID_MEMBER = b.ID_MEMBER OR emailAddress = Mail OR Name = memberName OR IP = memberIP SET passwd = REPLACE(passwd, 'B:', '') WHERE ($unmarksql) AND ID_BANNED IS NULL");
                                $dbst->execute(array_merge($q_mem_id, $q_mem_name, $q_mem_mail));
                                $dbst = null;
                            }
                        } // if num_rows > 0
                    } // if unban_ids > 0
                } // if memid == ''
                else
                { // Unban request came from profile page
                    $dbst = $app->db->prepare("SELECT BannedBy FROM banned_enh WHERE ID_MEMBER = ?");
                    $dbst->execute(array($POST->memid));
                    $banned_by = $dbst->fetchAll(\PDO::FETCH_COLUMN); $dbst = null;
                    if (!empty($banned_by)) {
                        $banned_by = array_unique($banned_by);
                        $banned_by = array_filter($banned_by, function($item) use ($app) {
                            return $item != $app->user->id;
                        });
                    }
                    
                    // remove all matching rows in ban table
                    $dbst = $app->db->prepare("DELETE b FROM {$db_prefix}members m INNER JOIN {$db_prefix}banned_enh b ON m.ID_MEMBER = b.ID_MEMBER OR emailAddress = Mail OR Name = memberName OR IP = memberIP WHERE m.ID_MEMBER = ?");
                    $dbst->execute(array($POST->memid)); $dbst = null;
                    // Unmark user
                    $dbst = $app->db->prepare("UPDATE members SET passwd = REPLACE(passwd, 'B:', '') WHERE ID_MEMBER = ?");
                    $dbst->execute(array($POST->memid)); $dbst = null;
                    
                    // Notify user
                    $notice_subject = "{$app->locale->enhancedunban_subj} {$app->locale->txt[176]} {$app->conf->mbname}";
                    $e_username = rawurlencode($app->user->name);
                    $notice_msg = "{$app->locale->enhancedunban_subj} {$app->locale->txt[176]} «{$app->conf->mbname}» {$app->locale->enhancedban25} [url={$service->siteurl}/people/$e_username/]{$app->user->realname}[/url].";
                    $app->im->send_notice($POST->memid, $notice_subject, $notice_msg);
                    
                    $dbst = $app->db->prepare("SELECT memberName, IFNULL(realName, memberName) as realName FROM {$db_prefix}members WHERE ID_MEMBER = ?");
                    $dbst->execute(array($POST->memid));
                    $unban_mem = $dbst->fetch(); $dbst = null;
                    $euname = rawurlencode($unban_mem['memberName']);
                    
                    $notice_subject = "{$app->locale->enhancedban1} {$app->locale->txt[176]} {$app->conf->mbname}";
                    
                    // Notify moderator banned by
                    if (count($banned_by) > 0) {
                        $notice_msg = "[url={$service->siteurl}/people/$e_username/]{$app->user->realname}[/url] {$app->locale->enhancedban26} [url={$service->siteurl}/people/$euname/]{$unban_mem['realName']}[/url] {$app->locale->enhancedban27}.";
                            $app->im->send_notice($banned_by, $notice_subject, $notice_msg);
                    }
                    
                    // Notify admins
                    $notice_msg = "[url={$service->siteurl}/people/$e_username/]{$app->user->realname}[/url] {$app->locale->enhancedban26} [url={$service->siteurl}/people/$euname/]{$unban_mem['realName']}[/url].";
                    $app->im->notifyAdmins($notice_subject, $notice_msg);
                    
                    $redirect_on_finish = "/people/$euname/"; // redirect to profile
                } // unban request from profile page
            } // if unban
            else
                return $this->error('enhancedban9');
            
            if (empty($redirect_on_finish))
                $redirect_on_finish = "/admin/bans/";

            return $this->redirect($redirect_on_finish);
        } // if POST
    } // bans()
}