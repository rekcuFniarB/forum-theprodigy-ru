<?php
namespace Prodigy\Respond;

class Main extends Respond {
    /**
     * This is board index page
     */
    public function index($request, $response, $service, $app) {
        $db_prefix = $app->db->prefix;
        $service->board = -1;
        if ($request->param('markallasread') == 1) {
            // Mark all boards as read and exit
            $dbst = $app->db->prepare("
                SELECT b.ID_BOARD
                FROM {$db_prefix}categories as c, {$db_prefix}boards as b
                WHERE c.ID_CAT=b.ID_CAT
                AND (? = ? OR ? = ? OR FIND_IN_SET(?, c.memberGroups) != 0 OR c.memberGroups = ?)");
            $dbst->execute(array($app->user->group, 'Administrator', $app->user->group, 'Global Moderator', $app->user->group, ''));
            $mark_boards = $dbst->fetchAll(\PDO::FETCH_COLUMN);
            $dbst = null;
            
            if ($mark_boards)
            {
                $dbst = $app->db->prepare("
                    REPLACE INTO {$db_prefix}log_mark_read
                    (logTime, ID_MEMBER, ID_BOARD)
                    VALUES (?, ?, ?)");
                $time = time();
                foreach ($mark_boards as $mark_board)
                {
                    $dbst->execute(array($time, $app->user->id, $mark_board));
                }
                $dbst = null;
            }
    
            $dbst = $app->db->prepare("
                SELECT lt.ID_TOPIC
                FROM {$db_prefix}log_topics AS lt, {$db_prefix}topics AS t, {$db_prefix}boards AS b, {$db_prefix}categories AS c
                WHERE t.ID_TOPIC=lt.ID_TOPIC
                AND b.ID_BOARD=t.ID_BOARD
                AND c.ID_CAT=b.ID_CAT
                AND (? = ? OR ? = ? OR FIND_IN_SET(?, c.memberGroups) != 0 OR c.memberGroups=?)
                AND lt.ID_MEMBER=?");
            $dbst->execute(array(
                $app->user->group, 'Administrator',
                $app->user->group, 'Global Moderator',
                $app->user->group, '', $app->user->id
            ));
            $mark_topics = $dbst->fetchAll(\PDO::FETCH_COLUMN);
            $dbst = null;
            if ($mark_topics)
            {
                $placeholders = $app->db->build_placeholders($mark_topics);
                $dbst = $app->db->prepare("DELETE FROM {$db_prefix}log_topics WHERE ID_MEMBER=? AND ID_TOPIC IN ($placeholders)");
                $dbst->execute(array_merge(array($app->user->id), $mark_topics));
                $dbst = null;
            }
            //return $service->redirect('/');
            return $service->back();
        } // if markallasread
        
        $collapseboard = $request->param('collapseboard');
        if (!empty($collapseboard)) {
            if ($app->user->id == -1)
                return $service->back();
            
            $service->validateParam('collapseboard', 'Enter correct board number')->isInt();
            
            // Check if such board already collapsed
            if (array_search($collapseboard, $app->user->collapsedBoards) !== false)
                return $service->back();
            
            // add new board to list
            $app->user->collapsedBoards[] = $collapseboard;
            
            $app->db->prepare("UPDATE {$db_prefix}members SET collapsedBoardIDs = ? WHERE ID_MEMBER = ?")
                ->execute(
                    array(
                        implode(",", $app->user->collapsedBoards),
                        $app->user->id
                    )
                );
            
            // Redirect back
            return $service->back();
        } // if collapseboard
        
        $uncollapseboard = $request->param('uncollapseboard');
        if (!empty($uncollapseboard)) {
            if ($app->user->id == -1)
                return $service->back();
            
            $service->validateParam('uncollapseboard', 'Enter correct board number')->isInt();
            
            // Check if such board already collapsed
            $uncollapseKey = array_search($uncollapseboard, $app->user->collapsedBoards);
            if ($uncollapseKey === false)
                return $service->back();
            
            // add new board to list
            unset($app->user->collapsedBoards[$uncollapseKey]);
            
            $app->db->prepare("UPDATE {$db_prefix}members SET collapsedBoardIDs = ? WHERE ID_MEMBER = {$app->user->id}")
                ->execute(
                    array(
                        implode(",", $app->user->collapsedBoards),
                        $app->user->id
                    )
                );
            
            // Redirect back
            return $service->back();
        } // if uncollapseboard
        
        /* MAIN */
        
        $mbname = $app->conf->mbname;
        $scripturl = $siteroot = SITE_ROOT;
        $ID_MEMBER = $app->user->id;
        
        $latestmember = $app->conf->latestMember;
        $latestRealName = $app->conf->latestRealName;
        $service->memcount = $app->conf->memberCount;
        $service->totalm = $app->conf->totalMessages;
        $service->totalt = $app->conf->totalTopics;
        
        $service->thelatestmember = $latestmember;
        $service->thelatestrealname = $latestRealName;
        
        $yytitle = $app->locale->txt[18];
        $service->title = $yytitle;
        //$service->txt = $app->locale->txt;
        //$service->conf = $app->conf;
        
        //template_header();
        
        $curforumurl = ($app->conf->curposlinks ? "<a href=\"$siteroot\" class=\"nav\">{$app->conf->mbname}</a>" : $mbname);
        // Build the link tree
        $service->displayLinkTree = ($app->conf->enableInlineLinks? "<font class=\"nav\"><b>$curforumurl</b></font>"  :  "<font class=\"nav\"><img src=\"{$app->conf->imagesdir}/open.gif\" border=\"0\" alt=\"\" /> <b>$curforumurl</b></font>");
        
        /*$query = $db->query("SELECT * FROM cites") or database_error(__FILE__, __LINE__, $db);
        $num_cites = $query->num_rows;
        $cite_num = mt_rand(0, $num_cites-1);
        if ($query->data_seek($cite_num))
        $cite_row = $query->fetch_assoc();*/
         

        if ($app->conf->shownewsfader == 1) {
            if (!isset($app->conf->fadertime))
                $app->conf->fadertime = 5000;
            
            $newslines = str_replace("\r", '', trim($app->conf->news));
            $newslines = explode("\n---\n", stripslashes($newslines));
            shuffle($newslines);
            $service->fcontent = '';
            for ($i = 0; $i < sizeof($newslines); $i++) {
                $newslines[$i] = str_replace('"', "&quot;", trim($newslines[$i]));
                if ($app->conf->enable_ubbc == 1)
                    $newslines[$i] = $service->DoUBBC($newslines[$i]);
                $newslines[$i] = str_replace(array('/', '<a href='), array('\/', '<a hre" + "f='), addslashes($newslines[$i]));
                $service->fcontent .= "fcontent[$i] = \"$newslines[$i]\";\n";
            }
        }
        
        if ($app->user->isStaff())
        {
            $condition = '1';
            $condition_params = array();
        }
        else
        {
            $condition = "(FIND_IN_SET(?, c.memberGroups) != 0 || c.memberGroups='')";
            $condition_params = array($app->user->group);
        }
                
        $dbst_boards = $app->db->prepare("
            SELECT DISTINCT c.name AS catName, c.ID_CAT, b.ID_BOARD, b.name AS boardName, b.description, b.moderators, b.numPosts, b.numTopics, c.memberGroups, m.posterName, m.posterTime, m.subject, t.ID_TOPIC, t.numReplies, IFNULL(mem.realName, m.posterName) AS realName, IFNULL(lb.logTime, 0) AS boardTime, IFNULL(lmr.logTime, 0) AS markReadTime, IFNULL(mem.ID_MEMBER, -1) AS ID_MEMBER, IFNULL(lon.viewers, 0) AS numBoardViewers, c.catOrder, b.boardOrder
            FROM {$db_prefix}categories AS c
            LEFT JOIN {$db_prefix}boards AS b ON (b.ID_CAT=c.ID_CAT)
            LEFT JOIN {$db_prefix}topics AS t ON (t.ID_TOPIC=b.ID_LAST_TOPIC)
            LEFT JOIN {$db_prefix}messages AS m ON (m.ID_MSG=t.ID_LAST_MSG)
            LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER=m.ID_MEMBER)
            LEFT JOIN {$db_prefix}log_boards AS lb ON (lb.ID_BOARD=b.ID_BOARD AND lb.ID_MEMBER=?)
            LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD=b.ID_BOARD AND lmr.ID_MEMBER=?)
            LEFT JOIN {$db_prefix}log_online AS lo ON (b.ID_BOARD = lo.ID_BOARD)
            LEFT JOIN (SELECT lo.ID_BOARD, count(*) AS viewers FROM {$db_prefix}log_online AS lo GROUP BY lo.ID_BOARD) AS lon ON (b.ID_BOARD = lon.ID_BOARD)
            WHERE $condition
            ORDER BY c.catOrder, c.ID_CAT, b.boardOrder, b.ID_BOARD");
        $dbst_boards->execute(array_merge(array($app->user->id, $app->user->id), $condition_params));
        
        $curcat = -1;
        $collapsedCategories = (empty($app->conf->collapsedCategories)) ? array() : $app->conf->collapsedCategories;
        $service->collapsedCategories = $collapsedCategories;
        //$service->boards = array();
        //$service->cats = array();
        $cats = array();
        $c = 0;
        while ($row_board = $dbst_boards->fetch()) {
            // if this is a new category
            //$service->boards[] = $row_board;
            if ($row_board['ID_CAT'] != $curcat) {
                $curcat = $row_board['ID_CAT'];
                $cats[$curcat]['boards'] = array();
                $cats[$curcat]['name'] = $row_board['catName'];
                
                $dbst = $app->db->prepare("SELECT billboard, billboardAuthor FROM {$db_prefix}categories WHERE ID_CAT = ?");
                $dbst->execute(array($curcat));
                $row = $dbst->fetch();
                $row['billboard'] = stripslashes($row['billboard']);
                $cats[$curcat]['billboard'] = $row['billboard'];
                $cats[$curcat]['billboardAuthor'] = $row['billboardAuthor'];
                $dbst = null;
            } // row['ID_CAT'] != $curcat
            
            if ($row_board['ID_BOARD'] != '' and !in_array($row_board['ID_CAT'], $collapsedCategories)) {
                $curboard = $row_board['ID_BOARD'];
                //cats[$curcat]['boards'][$curboard] = $row_board;
                //$latestPostID = '-1';
                $latestModTime = $subject = $topicID = '';
                $numReplies = 0;
                $latestPostName = (strlen($row_board['posterName']) ? $row_board['posterName'] : $app->locale->txt[470]);
                $latestPostID = $row_board['ID_MEMBER'];
                $latestPostTime = ($row_board['posterTime'] > 0 ? $row_board['posterTime'] : $app->locale->txt[470]);
                $subject = $row_board['subject'];
                // $subject = CensorTxt($subject);
                $topicID = $row_board['ID_TOPIC'];
                $subject = str_replace (array('&quot;', '&#039;', '&amp;', '&lt;', '&gt;'),  array('"', "'", '&', '<', '>'),  $subject);
                $subject = (strlen($subject) > 80)? $service->esc(substr($subject, 0, 80) . '...') : $service->esc($subject);
                $numReplies = $row_board['numReplies'];
                $startPage = (floor(($numReplies)/$app->conf->maxmessagedisplay)*$app->conf->maxmessagedisplay);
                $latestPostSubject = ($subject ? ' <a href="' . SITE_ROOT . "/b{$row_board['ID_BOARD']}/t$topicID/new/\"><b class=\"latestPostSubject\">$subject</b></a>" : $app->locale->txt[470]);
                $latestPostRealName = $row_board['realName'];
                $log1 = (($row_board['boardTime'] >= $latestPostTime) ? 1 : 0);
                $log2 = (($row_board['markReadTime'] >= $latestPostTime) ? 1 : 0);
                $latestEditTime = $latestModTime = $latestPostTime;
                $themoderators = explode(',', $row_board['moderators']);
                for ($i = 0; $i < sizeof($themoderators); $i++) {
                    $themoderators[$i] = trim($themoderators[$i]);
                    if ($themoderators[$i] != '') {
                        $curModerator = $app->user->loadDisplay($themoderators[$i]);
                        $euser = urlencode($themoderators[$i]);
                        $themoderators[$i] = "<a href=\"$siteroot/people/$euser/\"><acronym title=\"{$app->locale->txt[62]}\">".$service->esc($curModerator['realName'])."</acronym></a>";
                    }
                }
                $showmods = implode (', ', $themoderators);
                if ($showmods != '') {
                    if (sizeof($themoderators) > 1)
                        $showmods = "<br /><font size=\"1\"><i>{$app->locale->txt[299]}: $showmods</i></font>";
                    else
                        $showmods = "<br /><font size=\"1\"><i>{$app->locale->txt[298]}: $showmods</i></font>";
                }
                else
                    $showmods = "";
                
                // set it to off,  only turn it on if there are posts and they are new.
                //$new = $app->locale->img['new_off'];
                $new = 'off';
                if ($latestPostTime != $app->locale->txt[470] && $log1 == 0 && $log2 == 0 && $app->user->name != 'Guest') {
                    //$new = $app->locale->img['new_on'];
                    $new = 'on';
                }
                
                // alt text for button
                if ($app->user->guest) {
                    if($new == 'on') {
                        $new_txt = $app->locale->txt[333];
                    } else {
                        $new_txt = $app->locale->txt[334];
                    }
                } else {
                    $new_txt = '';
                }
                
                if ($app->user->inIgnore($latestPostName)) {
                    // Hide last post name of ignored user
                    $latestPostName = '';
                }
                elseif ($latestPostName != $app->locale->txt[470] && $latestPostID != '-1') {
                    $euser=urlencode($latestPostName);
                    $latestPostName = '<a href="' . SITE_ROOT . "/people/$euser/\"><b class=\"latestPostRealName\">".$service->esc($latestPostRealName)."</b></a>";
                }
                
                if ($latestPostTime != $app->locale->txt[470])
                    $latestPostTime = $app->subs->timeformat($latestPostTime);
                
                $cats[$curcat]['boards'][$curboard] = $row_board;
                $cats[$curcat]['boards'][$curboard]['n'] = (($c++)%2)+1;
                $cats[$curcat]['boards'][$curboard]['boardViewers'] = $row_board['numBoardViewers'];
                $cats[$curcat]['boards'][$curboard]['latestPostSubject'] = $latestPostSubject;
                $cats[$curcat]['boards'][$curboard]['latestPostTime'] = $latestPostTime;
                $cats[$curcat]['boards'][$curboard]['latestPostName'] = $latestPostName;
                $cats[$curcat]['boards'][$curboard]['showmods'] = $showmods;
                $cats[$curcat]['boards'][$curboard]['new'] = $new;
                $cats[$curcat]['boards'][$curboard]['new_txt'] = $new_txt;
            } // if board and non collapsed
        } // fetch_assoc()
        $dbst_boards = null; // closing statement
        
        // load the number of users online right now
        $guests = 0;
        $tmpusers = array();
        $dbst = $app->db->query("
            SELECT m.memberName AS identity,  m.realName,  m.memberGroup
            FROM {$app->db->prefix}log_online AS lo
            LEFT JOIN {$app->db->prefix}members AS m ON (m.ID_MEMBER=lo.identity)
            WHERE 1
            ORDER BY logTime DESC");
        while ($tmp = $dbst->fetch()) {
            $identity = $tmp['identity'];
            $euser = urlencode($identity);
            
            if ($tmp['realName'] != '') {
                if ($tmp['memberGroup'] == 'Administrator')
                    $tmpusers[] = "<a href=\"people/$euser/\"><font color=\"red\">".$service->esc($tmp['realName'])."</font></a>";
                elseif ($tmp['memberGroup'] == 'Global Moderator')
                    $tmpusers[] = "<a href=\"people/$euser/\"><font color=\"blue\">".$service->esc($tmp['realName'])."</font></a>";
                elseif ($tmp['memberGroup'] == 'YaBB SE Developer')
                    $tmpusers[] = "<a href=\"people/$euser/\"><font color=\"green\">".$service->esc($tmp['realName'])."</font></a>";
                elseif ($tmp['memberGroup'] == 'Mod Team')
                    $tmpusers[] = "<a href=\"people/$euser/\"><font color=\"orange\">".$service->esc($tmp['realName'])."</font></a>";
                else
                    $tmpusers[] = "<a href=\"people/$euser/\">".$service->esc($tmp['realName'])."</a>";
            }
            else
                $guests ++;
        }
        $dbst = null;
        
        //change here
        $service->users = '<font size="1">' . implode(', ', $tmpusers) . '</font>';
        $numusersonline = sizeof($tmpusers);
        
        //Determines most user online - both all time and per day
        $total_users = $guests + $numusersonline;
        $tot_date = time();
        if (($app->conf->trackStats == 1) && ($total_users > $app->conf->mostOnline)) {
            $app->db->query("UPDATE {$app->db->prefix}settings SET value='$total_users' WHERE variable='mostOnline'");
            $app->db->query("UPDATE {$app->db->prefix}settings SET value='$tot_date' WHERE variable='mostDate'");
        }
        
        if ($app->conf->trackStats == 1) {
            $mdate = getdate(time() + $app->conf->timeoffset * 3600);
            $monthquery = $app->db->query("
                SELECT MAX(mostOn) as mostOn
                FROM {$app->db->prefix}log_activity
                WHERE month = $mdate[mon]
                    AND day = $mdate[mday]
                    AND year = $mdate[year]");
            
            $oldMost = $monthquery->fetchColumn();
            $monthquery = null; // closing statement
            if ($total_users > $oldMost) {
                $statsquery = $app->db->query("UPDATE {$app->db->prefix}log_activity SET mostOn = $total_users WHERE month = $mdate[mon] AND day = $mdate[mday] AND year = $mdate[year]");
                if ($statsquery->rowCount() == 0)
                    $app->db->query("INSERT INTO {$app->db->prefix}log_activity (month, day, year, mostOn) VALUES ($mdate[mon], $mdate[mday], $mdate[year], $total_users)");
                $statsquery = null; // closing statement
            }
        }

        $service->numusersonline = $numusersonline;
        $service->guests = $guests;

        if ($app->conf->Show_RecentBar == 1) {
            $service->last_post = $this->LastPost();
        }
        elseif ($app->conf->Show_RecentBar == 2) {
            $service->last_postings = $this->LastPostings();
            $service->last_posts_comments = $this->LastPostComments();
        }
        
        if ($app->conf->cal_enabled && ($app->conf->cal_showeventsonindex || $app->conf->cal_showbdaysonindex || $app->conf->cal_showholidaysonindex )) {
            //include_once "$sourcedir/Calendar.php";
            $service->calendar = $this->app->calendar->getEvents();
        } else {
            $service->calendar_index = null;
        }

        if ($app->conf->enableSP1Info == 1) {
            // include_once("$sourcedir/Recent.php");
            // $recentsender = 'admin';
            $service->last_post_admin = $this->LastPost('admin');
        } // if enableSP1Info
        
        $service->cats = $cats;
        
        //$this->template_header();
        $this->render('board_index.php');
        //obExit();
    } // boardIndex()
    
    public function LastPost($recentsender = '') {
        $db_prefix = $this->app->db->prefix;
        
        $usergroup = $this->app->user->group;
        
        $dbst = $this->app->db->prepare("
            SELECT m.posterTime, m2.subject, m.ID_TOPIC, t.ID_BOARD, m.posterName, t.numReplies, t.ID_FIRST_MSG
            FROM {$db_prefix}boards AS b
            JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)
            LEFT JOIN {$db_prefix}topics AS t ON (t.ID_TOPIC=b.ID_LAST_TOPIC)
            LEFT JOIN {$db_prefix}messages AS m ON (m.ID_MSG=t.ID_LAST_MSG)
            LEFT JOIN {$db_prefix}messages AS m2 ON (m2.ID_MSG=t.ID_FIRST_MSG)
            WHERE (FIND_IN_SET(?, c.memberGroups) != 0 OR c.memberGroups='' OR ? LIKE 'Administrator' OR ? LIKE 'Global Moderator')
            ORDER BY m.posterTime DESC
            LIMIT 1");
        $dbst->execute(array($usergroup, $usergroup, $usergroup));
        $row = $dbst->fetch();
        $dbst = null;
        if (empty($row))
            return null;
        
        $row['subject'] = $this->app->subs->CensorTxt($row['subject']);
        if ($recentsender == 'admin') {
            $row['subject'] = strlen($this->service->un_html_entities($row['subject'])) > 25 ? substr($this->service->un_html_entities($row['subject']), 0, 22) . "..." : $row['subject'];
        }
        
        $post = array(
                'board' => $row['ID_BOARD'],
                'topic' => $row['ID_TOPIC'],
                'subject' => $row['subject'],
                'time' => $this->app->subs->timeformat($row['posterTime']),
                'recentsender' => $recentsender,
        );

        return $post;
    } // LastPost()
    
    public function LastPostings() { // TODO rewrite as LastPost()
        $showlatestcount = 15;
        
        if (!isset($recentsender))
            $recentsender = '';
        
        // in order to optimize speed, this query gets the ($showlatestcount * 4) 
        // latest messageID's. guessing that that will be enough to cover
        // ($showlatestcount) topics a user is allowed to see.
        
        $db_prefix = $this->app->db->prefix;
        $usergroup = $this->app->user->group;
        //$scripturl = $this->app->conf->scripturl;
        $siteroot = SITE_ROOT;
        
        $request = $this->app->db->query("
            SELECT m.ID_MSG
            FROM {$db_prefix}messages AS m
            ORDER BY m.posterTime DESC
            LIMIT 0, " . ($showlatestcount * 4));
        $messages = $request->fetchAll(\PDO::FETCH_COLUMN);
        $request = null;
        
        if ($messages)
        {
            $msg_placeholders = $this->app->db->build_placeholders($messages);
            $request = $this->app->db->prepare("
                SELECT m.ID_MSG, m.posterTime, m.subject, m.ID_TOPIC, m.posterName, m.ID_MEMBER, IFNULL(mem.realName, m.posterName) AS posterDisplayName, t.numReplies, t.ID_BOARD, t.ID_FIRST_MSG, b.name AS bName
                    FROM {$db_prefix}messages AS m
                    JOIN {$db_prefix}topics AS t ON (m.ID_TOPIC = t.ID_TOPIC)
                    JOIN {$db_prefix}boards AS b ON (t.ID_BOARD = b.ID_BOARD)
                    JOIN {$db_prefix}categories AS c ON (b.ID_CAT = c.ID_CAT)
                    LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER=m.ID_MEMBER)
                    WHERE m.ID_MSG IN ($msg_placeholders)
                        AND (FIND_IN_SET(?, c.memberGroups) != 0 OR c.memberGroups='' OR ? LIKE 'Administrator' OR ? LIKE 'Global Moderator')
                    ORDER BY m.posterTime DESC
                    LIMIT 0, ?");
            $request->execute(
                array_merge(
                    $messages, array($usergroup, $usergroup, $usergroup, $showlatestcount)
                )
            );
            
            $post = '';
            while ($row = $request->fetch())
            {
                if ($this->app->user->inIgnore($row['posterName']))
                    continue;
                
                $post .= '<tr>';
                if ($row['ID_MEMBER'] != -1) {
                    $euser = urlencode($row['posterName']);
                    $dummy = "<a href=\"$siteroot/people/$euser/\">".$this->service->esc($row['posterDisplayName'])."</a>";
                }
                else
                    $dummy = $this->service->esc($row['posterName']);
                //$row['subject'] = $this->app->subs->htmlescape($row['subject']);
                $post .= '
                        <td align="right" valign="top" nowrap="nowrap" class="info_board">
                            [<a href="' . $siteroot . '/b' . $row['ID_BOARD'] . '/">' . $this->service->esc($row['bName']) . '</a>]
                        </td>
                        <td valign="top" class="info_post">
                            <a href="' . $siteroot . '/b' . $row['ID_BOARD'] . '/t' . $row['ID_TOPIC'] . '/msg' . $row['ID_MSG'] . '/#msg' . $row['ID_MSG'] . '">' . $this->service->esc($row['subject']) . '</a> ' . $this->app->locale->txt[525] . ' ' . $dummy . '
                        </td>
                        <td align="right" nowrap="nowrap" class="info_date">
                            ' . $this->app->subs->timeformat($row['posterTime']) . '
                        </td>
                    </tr>';
            } // while
            
            if ($post == '')
                $post = '---';
            else
            {
                $post = '<table width="100%" border="0">' . $post . '</table>';
            }
        } // if count($messages)
        else $post = '---';

        $post = $this->app->subs->CensorTxt($post);

        return $post;
    } // LastPostings()
    
    public function LastPostComments($recentsender = '') { // TODO rewrite as $this->LastPost()
        $showlatestcount = 15;
        
        $db_prefix = $this->app->db->prefix;
        $usergroup = $this->app->user->group;
        $siteroot = SITE_ROOT;
        
        $this->service->recentCommentsTable = ($this->request->cookies()->get('recentCommentsTable') == "show" ? true : false);
        
        // in order to optimize speed, this query gets the ($showlatestcount * 4) 
        // latest messageID's. guessing that that will be enough to cover
        // ($showlatestcount) topics a user is allowed to see.
        $request = $this->app->db->query("
            SELECT MSG
            FROM {$db_prefix}log_last_comments ORDER BY last_comment_time DESC
            LIMIT ".($showlatestcount*2));
        $messages = $request->fetchAll(\PDO::FETCH_COLUMN);
        $request = null;
        
        if ($messages) {
            $msg_placeholders = $this->app->db->build_placeholders($messages);
            $request = $this->app->db->prepare("
                    SELECT m.ID_MSG, m.last_comment_time LAST_COMMENT_TIME, m.comments COMMENTS, m.subject, m.ID_TOPIC, m.posterName, m.ID_MEMBER, IFNULL(mem.realName, m.posterName) AS posterDisplayName, t.numReplies, t.ID_BOARD, t.ID_FIRST_MSG, b.name AS bName
                    FROM {$db_prefix}messages AS m
                    JOIN {$db_prefix}topics AS t ON (m.ID_TOPIC = t.ID_TOPIC)
                    JOIN {$db_prefix}boards AS b ON (t.ID_BOARD = b.ID_BOARD)
                    JOIN {$db_prefix}categories AS c ON (b.ID_CAT = c.ID_CAT)
                    LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER=m.ID_MEMBER)
                    WHERE m.ID_MSG IN ($msg_placeholders)
                        AND (FIND_IN_SET(?, c.memberGroups) != 0 OR c.memberGroups='' OR ? LIKE 'Administrator' OR ? LIKE 'Global Moderator')
                    ORDER BY m.last_comment_time DESC
                    LIMIT 0, ?");
            $request->execute(
                array_merge(
                    $messages, array($usergroup, $usergroup, $usergroup, $showlatestcount)
                )
            );
            
            $post = '';
            while ($row = $request->fetch()) {
                $csvlines = explode("\r\n", $row['COMMENTS']);
                end($csvlines);
                $lastcsvline = explode("#;#", prev($csvlines));
                if ($this->app->user->inIgnore($lastcsvline[1]))
                    continue;
                $euser = urlencode($lastcsvline[1]);
                $dummy = "<a href=\"$siteroot/people/$euser/\">".$this->service->esc($lastcsvline[0])."</a>";
                //$row['subject'] = htmlspecialchars($row['subject'], ENT_COMPAT, $config['charset'], false);
                $post .= '<tr>';
                $post .= '
                        <td align="right" valign="top" nowrap="nowrap" class="info_board">
                            [<a href="' . $siteroot . '/b' . $row['ID_BOARD'] . '/">' . $this->service->esc($row['bName']) . '</a>]
                        </td>
                        <td valign="top" class="info_post">
                            <a href="' . $siteroot . '/b' . $row['ID_BOARD'] . '/t' . $row['ID_TOPIC'] . '/msg' . $row['ID_MSG'] . '/#msg' . $row['ID_MSG'] . '">' . $this->service->esc($row['subject']) . '</a> ' . $this->app->locale->txt[525] . ' ' . $dummy . '
                        </td>
                        <td align="right" nowrap="nowrap" class="info_date">
                            ' . $this->app->subs->timeformat($lastcsvline[2]) . '
                        </td>
                    </tr>';
            } // while
            
            if ($post == '')
                $post = '---';
            else
                $post =  '<div id="recentcommentstable" style="display: '.($this->service->recentCommentsTable ? "block" : "none").';"><table width="100%" border="0">
                ' . $post . '
                </table></div>';
        } // if $messages
        else
            $post = '---';
        $post = $this->app->subs->CensorTxt($post);
        
        return $post;
    } // LastPostComments()
}
