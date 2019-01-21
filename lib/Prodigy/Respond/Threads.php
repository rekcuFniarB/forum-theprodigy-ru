<?php
namespace Prodigy\Respond;

class Threads extends Respond
{
    public function Display($request, $response, $service, $app)
    {
        $GET = $request->paramsGet();
        $SERVER = $request->server();
        $namedParams = $request->paramsNamed();
        $currentboard = $namedParams->get('board');
        $threadid = $namedParams->get('thread');
        $page = $namedParams->get('page');
        $startmsg = $namedParams->get('startmsg');
        $start = $namedParams->get('start', 0);
        
        $service->thread = $threadid;
        $service->board = $currentboard;
        
        $service->videoTypes = array('ogm', 'ogv', 'webm', 'mp4');
        $service->audioTypes = array('ogg', 'mp3', 'opus', 'm4a', 'aac', 'wav', 'flac');
        $service->imageTypes = array('jpg', 'jpeg', 'gif', 'png', 'webp');
        
        if(!empty($page)) {
            $start = $page;
        }
        elseif(!empty($startmsg)) {
            $start = "msg$startmsg";
        }
        
        $service->viewResults = $GET->get('viewResults');
        
        if ($currentboard == 0 || empty($currentboard))
            return $this->findBoard($currentboard, $threadid);
        
        $service->board_moderators = $app->user->LoadBoardModerators($currentboard);
        
        $db_prefix = $app->db->prefix;
        $db = $app->db;
        
        // If someone arrived here via a previous or next link, we must determine
        // the appropriate ID_TOPIC.  If no such topic exists, we must present
        // the user with an appropriate warning.
        if ($start === 'prev' || $start === 'next' )
        {
            // Just prepare some variables that are used in the query
            $gt_lt = ($start === 'prev') ? '>' : '<';
            $order = ($start === 'prev') ? 'ASC' : 'DESC';
            //$error = ($start === 'prev') ? $app->locale->txt['a1'] : $app->locale->txt['a2'];
            
            $query = "SELECT t2.ID_TOPIC FROM {$db_prefix}topics as t, {$db_prefix}topics as t2, {$db_prefix}messages as mes, {$db->db_prefix}messages as mes2
                WHERE (mes.ID_MSG=t.ID_LAST_MSG && t.ID_BOARD=$currentboard && t.ID_TOPIC=$threadid && ((mes2.posterTime $gt_lt mes.posterTime && t2.isSticky $gt_lt= t.isSticky) || t2.isSticky $gt_lt t.isSticky) && t2.ID_LAST_MSG=mes2.ID_MSG && t2.ID_BOARD=t.ID_BOARD)
                ORDER BY t2.isSticky $order, mes2.posterTime $order LIMIT 1";

            $dbrq = $db->query($query, false);
            if ($dbrq->num_rows > 0){
                list ($threadid) = $dbrq->fetch_row();
                return $service->redirect("/b$currentboard/t$threadid/");
            }
            elseif($dbrq->num_rows == 0)
                return $app->errors->abort('', 'You have reached the end of the topic list');
        }
        
        // at this point it is certain that $threadid holds the correct ID_TOPIC
        // for the topic the user wants to view
        //$viewnum = $threadid;
        
        //check for blog board
        $dbrq = $db->query("SELECT ID_BOARD FROM {$db_prefix}boards WHERE ID_BOARD=$currentboard AND isBlog=1;", false);
        $service->isBlog = ($dbrq->num_rows > 0) ? true : false;
        if ($service->isBlog && $app->conf->blogmod_newblogontop)
            $app->conf->viewNewestFirst = 1;

        $dbrq = $db->query("SELECT b.ID_CAT,b.name,c.name,c.memberGroups FROM {$db_prefix}boards as b,{$db_prefix}categories as c WHERE (b.ID_BOARD=$currentboard && b.ID_CAT=c.ID_CAT)", false);
        list($curcat,$boardname,$cat,$temp2) = $dbrq->fetch_row();
        $memgroups = explode(',',$temp2);
        
        $service->curcat = $curcat;
        $service->boardname = $boardname;
        $service->catname = $cat;

        if (!(in_array($app->user->group, $memgroups) || $memgroups[0] == null || $app->user->accessLevel() > 2 ))
            return $app->errors->abort($app->locale->txt[106], $app->locale->txt[1]);
        
        $ID_MEMBER = $app->user->id;
        
        //Redirect to page & post with new messages -- Omar Bazavilvazo
        if ($start === 'new')
        {
            // Check if a log exists, so we can go to next unreaded message page
            $dbrq = $db->query("
                SELECT GREATEST(IFNULL(lt.logTime, 0), IFNULL(lmr.logTime,0)) AS logtime, COUNT(m.ID_MSG), MAX(m.ID_MSG)
                FROM {$db_prefix}topics AS t
                LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.ID_TOPIC=t.ID_TOPIC AND lt.ID_MEMBER=$ID_MEMBER)
                LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD=t.ID_BOARD AND lmr.ID_MEMBER=$ID_MEMBER)
                LEFT JOIN {$db_prefix}messages AS m ON (m.ID_TOPIC=t.ID_TOPIC)
                WHERE (t.ID_TOPIC = $threadid)
                GROUP BY t.ID_TOPIC", false);
            
            list($ltLastRead, $numMessages, $newestMessage) = $dbrq->fetch_row();
            
            if ($dbrq->num_rows == 0)
                return $app->errors->abort($app->locale->txt[106], $app->locale->txt[472]);
            
            $dbrq = $db->query("
                SELECT COUNT(*)
                FROM {$db_prefix}messages
                WHERE ID_TOPIC = $threadid
                AND posterTime <= $ltLastRead", false);
            
            list($numReadMessages) = $dbrq->fetch_row();
            $numUnreadMessages = $numMessages - $numReadMessages;
            
            if ($app->conf->viewNewestFirst)
                $Page2Show = floor(($numUnreadMessages == 0 ? 0 : $numUnreadMessages - 1) / $app->conf->maxmessagedisplay) * $app->conf->maxmessagedisplay;
            else
                $Page2Show = floor(($numReadMessages  == $numMessages ? $numReadMessages - 1 : $numReadMessages) / $app->conf->maxmessagedisplay) * $app->conf->maxmessagedisplay;
            
            if ($numUnreadMessages > 0)
            {
                $dbrq = $db->query("
                    SELECT MIN(ID_MSG)
                    FROM {$db_prefix}messages
                    WHERE ID_TOPIC = $threadid
                    AND posterTime > $ltLastRead");
                list($firstUnreadMessage) = $dbrq->fetch_row();
                $newMsgID = "#msg$firstUnreadMessage";
            }
            elseif ($app->conf->viewNewestFirst)
                $newMsgID = "#msg$newestMessage";
            else
                $newMsgID = '#lastPost';
            
            if (!$app->user->guest)
            {
                // mark board as seen if we came using notification
                $db->query("
                    REPLACE INTO {$db->db_prefix}log_boards (logTime, ID_MEMBER, ID_BOARD)
                    VALUES (" . time() . ", $ID_MEMBER, $currentboard)", false);
            }
            return $service->redirect("/b$currentboard/t$threadid/$Page2Show/$newMsgID");
        } // if start = new
        elseif (substr($start, 0, 3) == 'msg')
        {
            $msg = (int) substr($start, 3);
            $dbrq = $db->query("SELECT COUNT(*) FROM {$db_prefix}messages WHERE (ID_MSG < $msg && ID_TOPIC=$threadid)", false);
            list($start) = $dbrq->fetch_row();
        }
        
        // do the previous next stuff
        // Create a previous next string if the selected theme has it
        // as a selected option
        $previousNext = $app->conf->enablePreviousNext ? '<a href="' . SITE_ROOT . "/b$currentboard/t$threadid/prev/\">{$app->conf->PreviousNext_back}</a> <a href=\"" . SITE_ROOT . "/b$currentboard/t$threadid/prev/\">{$app->conf->PreviousNext_forward}</a>" : '';
        
        // Load membrgroups.
        $service->membergroups = $app->user->memberGroups();
        
        // get all the topic info
        $dbrq = $db->query("
            SELECT t.numReplies, t.numViews, t.locked, ms.subject, t.isSticky, ms.posterName, ms.ID_MEMBER, t.ID_POLL, t.ID_MEMBER_STARTED, tr.POSITIVE AS topicPosRating, tr.NEGATIVE AS topicNegRating
            FROM {$db_prefix}topics as t
            JOIN {$db_prefix}messages as ms ON (t.ID_FIRST_MSG = ms.ID_MSG)
            LEFT JOIN {$db_prefix}topic_ratings AS tr ON (t.ID_TOPIC = tr.TOPIC_ID)
            WHERE t.ID_BOARD = $currentboard
                AND t.ID_TOPIC = $threadid
                AND ms.ID_MSG = t.ID_FIRST_MSG", false);

        if ($dbrq->num_rows == 0) {
            return $this->findBoard($currentboard, $threadid);
        }
        
        $topicinfo = $dbrq->fetch_assoc();
        
        // read topic rating
        $service->topicPosRating = !isset($topicinfo['topicPosRating']) ? 0 : $topicinfo['topicPosRating'];
        $service->topicNegRating = !isset($topicinfo['topicNegRating']) ? 0 : $topicinfo['topicNegRating'];
        
        if (!$app->user->guest)
        {
            // mark the topic as read :)
            $db->query("
                REPLACE INTO {$db->db_prefix}log_topics (logTime, ID_MEMBER, ID_TOPIC, notificationSent,unreadComments,otherComments,subscribedComments)
                VALUES (" . time() . ", $ID_MEMBER, $threadid, 0,0,0,0)", false);
            
            $referer = $SERVER->get('HTTP_REFERER');
            // mark board as seen if we came using last post link from BoardIndex
            //if (isset($boardseen))
            if ($referer == "{$service->siteurl}/") {
                // if came from main page
                $db->query("
                    REPLACE INTO {$db->db_prefix}log_boards (logTime, ID_MEMBER, ID_BOARD)
                    VALUES (" . time() . ", $ID_MEMBER, $currentboard)", false);
            }
            
            // Add 1 to the number of views of this thread.
            $db->query("
                UPDATE {$db->db_prefix}topics
                SET numViews = numViews + 1
                WHERE ID_TOPIC = $threadid");
        }
        
        // Check to make sure this thread isn't locked.
        $noposting = $topicinfo['locked'];
        $mreplies = $topicinfo['numReplies'];
        $mstate = $topicinfo['locked'];
        $msubthread = $topicinfo['subject'];
        $yytitle = str_replace("\$", "&#36;", $topicinfo['subject']);
        
        // Get the class of this thread, based on lock status and number of replies.
        $threadclass = '';
        if ($mstate == 1 || $mstate == 2)
            $threadclass = 'locked';
        elseif ($mreplies > 24)
            $threadclass = 'veryhotthread';
        elseif ($mreplies > 14)
            $threadclass = 'hotthread';
        elseif ($mstate == 0)
            $threadclass = 'thread';
        
        if ($app->conf->enableStickyTopics && $topicinfo['isSticky'] == 1)
            $threadclass = 'sticky';
        
        if (($mstate == 1 || $mstate == 2) && ($app->conf->enableStickyTopics && $topicinfo['isSticky'] == '1'))
            $threadclass = 'lockedsticky';

        $service->msubthread = $app->subs->CensorTxt($msubthread);
        $service->title = $app->subs->CensorTxt($yytitle);
        $service->mstate = $mstate;
        $service->threadclass = $threadclass;
        
        // Build a list of this board's moderators.
        $showmods = '';		// create an empty string
        $tmp = array();		// used to temporarily store the list
        
        if (sizeof($service->board_moderators) > 0)
        {
            if (sizeof($service->board_moderators) == 1)    // if only one mod - use a different string
                $showmods = "({$app->locale->txt[298]}: ";
            else
                $showmods = "({$app->locale->txt[299]}: ";
            
            foreach ($service->board_moderators as $modername => $moderinfo)
            {
                $euser = urlencode($modername);
                $tmp[] = '<a href="' . SITE_ROOT . '/people/' . $euser . '/"><acronym title="' . $app->locale->txt[62] . '">' . $service->esc($moderinfo['realName']) . '</acronym></a>';
            }

            $showmods .= implode(", ", $tmp) . ')';	// stitch the list together
        }
        
        $service->showmods = $showmods;
        
        $service->jumptoform = $this->prepareJumptoForm($currentboard);
        
        // Build the page links list.
        $max = $mreplies + 1;
        $start = (($start > $max) ? $max : $start);
        $start = (floor($start / $app->conf->maxmessagedisplay)) * $app->conf->maxmessagedisplay;
        
        $service->start = $start;
        
        $maxmessagedisplay = ($start === 'all' ? $mreplies + 1 : $app->conf->maxmessagedisplay);
        error_log("__DEBUG__: STARTNUM: $start, $maxmessagedisplay,". $app->conf->maxmessagedisplay);
        if ($app->conf->compactTopicPagesEnable == 0)
        {
            $tmpa = $start - $maxmessagedisplay;
            $pageindex = (($start == 0) ? '' : '<a href="' . SITE_ROOT . "/b$currentboard/t$threadid/$tmpa/\">&#171;</a>");
            $tmpa = 1;
            for ($counter = 0; $counter < $max; $counter += $maxmessagedisplay)
            {
                $pageindex .= (($start == $counter) ? ' <b>' . $tmpa . '</b>' : ' <a href="' . SITE_ROOT . "/b$currentboard/t$threadid/$counter/\">$tmpa</a>");
                $tmpa++;
            }
            $tmpa = $start + $maxmessagedisplay;
            $tmpa = ($tmpa > $mreplies ? $mreplies : $tmpa);
            if ($start != $counter-$maxmessagedisplay)
                $pageindex .= (($tmpa > $counter - $maxmessagedisplay) ? ' ' : ' <a href="' . SITE_ROOT . "/b$currentboard/t$threadid/$tmpa/\">&#187;</a> ");
        }
        else
        {
            $pageindex = '';
            if (($app->conf->compactTopicPagesContiguous % 2) == 1)    //1,3,5,...
                $PageContiguous = (int)(($app->conf->compactTopicPagesContiguous - 1) / 2);
            else
                $PageContiguous = (int)($app->conf->compactTopicPagesContiguous / 2);  //invalid value, but let's deal with it
            
            if ($start > $maxmessagedisplay * $PageContiguous)  // first
                $pageindex.= '<a class="navPages" href="' . SITE_ROOT . "/b$currentboard/t$threadid/\">1</a> ";
            
            if ($start > $maxmessagedisplay * ($PageContiguous + 1))  // ...
                $pageindex.= '<b> ... </b>';
            
            for ($nCont=$PageContiguous; $nCont >= 1; $nCont--)  // 1 & 2 before
                if ($start >= $maxmessagedisplay * $nCont)
                {
                    $tmpStart = $start - $maxmessagedisplay * $nCont;
                    $tmpPage = $tmpStart / $maxmessagedisplay + 1;
                    if ($app->conf->viewNewestFirst)
                        $tmpStart = floor($max / $maxmessagedisplay) * $maxmessagedisplay - $tmpStart;
                    $pageindex .= '<a class="navPages" href="' . SITE_ROOT . "/b$currentboard/t$threadid/$tmpStart/\">$tmpPage</a> ";
                }
            
            $tmpPage = $start / $maxmessagedisplay + 1;  // page to show
            $pageindex .= ' <span class="pageindex-bracket">[</span><b class="pageindex-current">' . $tmpPage . '</b><span class="pageindex-bracket">]</span> ';

            $tmpMaxPages = (int)(($max - 1) / $maxmessagedisplay) * $maxmessagedisplay;  // 1 & 2 after
            for ($nCont=1; $nCont <= $PageContiguous; $nCont++)
            {
                if ($start + $maxmessagedisplay * $nCont <= $tmpMaxPages)
                {
                    $tmpStart = $start + $maxmessagedisplay * $nCont;
                    $tmpPage = $tmpStart / $maxmessagedisplay + 1;
                    if ($app->conf->viewNewestFirst)
                        $tmpStart = floor($max / $maxmessagedisplay) * $maxmessagedisplay - $tmpStart;
                        $pageindex .= '<a class="navPages" href="' . SITE_ROOT . "/b$currentboard/t$threadid/$tmpStart/\">$tmpPage</a> ";
                }
            }
                
            if ($start + $maxmessagedisplay * ($PageContiguous + 1) < $tmpMaxPages) // ...
                $pageindex .= '<b> ... </b>';
            
            if ($start + $maxmessagedisplay * $PageContiguous < $tmpMaxPages)	//	last
            {
                $tmpPage = $tmpMaxPages / $maxmessagedisplay + 1;
                $pageindex .= '<a class="navPages" href="' . SITE_ROOT . "/b$currentboard/t$threadid/$tmpMaxPages/\">$tmpPage</a> ";
            }
        } // building pageindex
        error_log("__DEBUG__: STARTNUM: $start, $maxmessagedisplay");
        // view all mod
        if ($maxmessagedisplay < $mreplies + 1)
            $pageindex .= '<a class="navPages" href="' . SITE_ROOT . "/b$currentboard/t$threadid/all/\">{$app->locale->txt[190]}</a> ";
        
        $service->pageindex = $pageindex;
        
        //topics locked by a user should get the same icon as topics locked by a moderator
        $topicinfo['locked'] = ($topicinfo['locked'] == 2 ? 1 : $topicinfo['locked']);
        
        $service->topicinfo = $topicinfo;
        
        //chatWall mod by dig7er
        if ($app->conf->boardBillsEnabled)
        {
            $dbrq = $db->query("SELECT chatWall, chatWallMsgAuthor FROM {$db->db_prefix}boards WHERE ID_BOARD = $currentboard;");
            $row = $dbrq->fetch_assoc();
            $row['chatWall'] = $service->unicodeentities($row['chatWall']);
            
            $dbrq = $db->query("SELECT billboard, billboardAuthor, realName, gender FROM {$db_prefix}topics AS t LEFT JOIN {$db_prefix}members AS m ON (t.billboardAuthor = m.memberName) WHERE ID_TOPIC = $threadid;");
            $row2 = $dbrq->fetch_assoc();
            $row['topicBillboard'] = stripslashes($row2['billboard']);
            $row['topicBillboardAuthor'] = $row2['billboardAuthor'];
            $row['topicBillboardAuthorName'] = $row2['realName'];
            $row['topicBillboardAuthorGender'] = $row2['gender'];
            $service->billboard = $row;
        }
        
        $service->showTopicBillboard = !empty($row['topicBillboard']);
        $service->showTopicRating = false;  //$ID_MEMBER != -1;
        
        if ($topicinfo['ID_POLL'] != '-1' && $app->conf->pollMode)
        {
            $dbrq = $db->query("
                SELECT question, votingLocked, votedMemberIDs, option1, option2, option3, option4, option5, option6, option7, option8, option9, option10, option11, option12, option13, option14, option15, option16, option17, option18, option19, option20, votes1, votes2, votes3, votes4, votes5, votes6, votes7, votes8, votes9, votes10, votes11, votes12, votes13, votes14, votes15, votes16, votes17, votes18, votes19, votes20
                FROM {$db_prefix}polls
                WHERE ID_POLL = '$topicinfo[ID_POLL]'
                LIMIT 1", false);
            
            $pollinfo = $dbrq->fetch_assoc();
            $pollinfo['image'] = ($pollinfo['votingLocked'] != '0' )?'locked_poll':'poll';
            
            $pollinfo['totalvotes'] = 0;
            
            for ($i=0; $i<20; $i++) {
              $pollinfo['totalvotes'] += $pollinfo["votes$i"];
            }
            $pollinfo['divisor'] = (($pollinfo['totalvotes'] == 0) ? 1 : $pollinfo['totalvotes']);
                        
            $service->pollinfo = $pollinfo;
        }
        
        # Load background color list.
        $bgcolors = array($app->conf->color['windowbg'], $app->conf->color['windowbg2']);
        $bgcolornum = sizeof($bgcolors);
        $cssvalues = array('windowbg', 'windowbg2');
        $cssnum = sizeof($bgcolors);
        
        if ($app->conf->MenuType == 0)
            $sm = 1;
        
        $counter = $start;
    
        # For each post in this thread
        $dbrq = $db->query("
            SELECT ID_MSG
            FROM {$db_prefix}messages
            WHERE ID_TOPIC=$threadid
            ORDER BY ID_MSG " . ($app->conf->viewNewestFirst ? 'DESC' : '') . "
            LIMIT $start,$maxmessagedisplay");

        $messages = array();
        
        while ($row = $dbrq->fetch_assoc())
            $messages[] = $row['ID_MSG'];
        
        if (count($messages))
            $dbrq = $db->query("
                SELECT m.ID_MSG, m.subject, m.posterName, m.posterEmail, m.posterTime, m.ID_MEMBER, m.icon, m.posterIP, m.body, m.smiliesEnabled, m.modifiedTime, m.modifiedName, m.attachmentFilename, m.attachmentSize, m.nowListening, m.multinick, IFNULL(mem.realName, m.posterName) AS posterDisplayName, IFNULL(lo.logTime, 0) AS isOnline, m.comments, mem.blockComments POST_COMMENTS_BLOCKED, m.closeComments CLOSED_COMMENTS, m.agent, qpolls.POLL_TITLE, cs.notify
                FROM {$db->db_prefix}messages AS m
                LEFT JOIN {$db->db_prefix}members AS mem ON (mem.ID_MEMBER=m.ID_MEMBER)
                LEFT JOIN {$db->db_prefix}log_online AS lo ON (lo.identity=mem.ID_MEMBER)
                LEFT JOIN {$db->db_prefix}quickpolls AS qpolls ON (m.ID_MSG = qpolls.ID_MSG)
                LEFT JOIN {$db->db_prefix}comment_subscriptions AS cs ON (m.ID_MSG = cs.messageID AND cs.memberID = {$ID_MEMBER})
                WHERE m.ID_MSG IN (" . implode(',', $messages) . ")
                ORDER BY ID_MSG " . ($app->conf->viewNewestFirst ? 'DESC' : ''), false);
        
        $messages = array();
        
        while ($message = $dbrq->fetch_array())
        {
            if ($app->conf->hard_ignore && $app->user->inIgnore($message['posterName'])) {
                // Skip ignored
                continue;
            }
            
            $msgID = $message['ID_MSG'];
            
            if (!empty($message['POLL_TITLE']))
            {
                $message['quickpoll'] = array();
                $message['quickpoll']['title'] = $message['POLL_TITLE'];
                $message['quickpoll']['voters'] = array();
                $message['quickpoll']['voters']['yes'] = array();
                $message['quickpoll']['voters']['no'] = array();
                $message['quickpoll']['voters']['neutral'] = array();
                
                $req = $db->query("SELECT qvotes.POLL_OPTION, qvotes.MEMBER_NAME, m.memberName, m.realName FROM {$db_prefix}quickpoll_votes AS qvotes LEFT JOIN {$db_prefix}members AS m ON (qvotes.ID_USER = m.ID_MEMBER) WHERE qvotes.ID_MSG = $msgID", false);
                
                while ($vote = $req->fetch_assoc())
                {
                    if (!empty($vote['memberName']))
                        $voter = array($vote['memberName'], $vote['realName']);
                   else
                        $voter = $vote['MEMBER_NAME'];
                   
                   if ($vote['POLL_OPTION'] == "yes")
                       $message['quickpoll']['voters']['yes'][] = $voter;
                   else if ($vote['POLL_OPTION'] == "no")
                       $message['quickpoll']['voters']['no'][] = $voter;
                   else if ($vote['POLL_OPTION'] == "neutral")
                       $message['quickpoll']['voters']['neutral'][] = $voter;
                }
            }
            
            if ($msgID == 1068850)
                $message['ID_MSG'] = -1;
            
            $message['windowbg'] = $bgcolors[($counter % $bgcolornum)];
            $message['css'] = $cssvalues[($counter % $cssnum)];
            
            # Should we show "last modified by?"
            if ($app->conf->showmodify && !empty($message['modifiedTime']) && !empty($message['modifiedName']))
            {
                $message['lastmodified'] = true;
            }
            
            $message['subject'] = (isset($message['subject']) ? $message['subject'] : $app->locale->txt[24]);
            
            $message['posterIP'] = ($app->user->accessLevel() > 2 ? $message['posterIP'] : $app->locale->txt[511]);
            
            $message['guest'] = true;
            
            if ($message['ID_MEMBER'] != -1)
            {
                # If user is not in memory, s/he must be loaded.
                $userset = $app->user->loadDisplay($message['posterName']);
                if($userset['found'])
                {
                    $message['userset'] = $userset;
                    $message['guest'] = false;
                    
                    if($app->conf->check_avatar_size)
                    {
                        preg_match("/\<img src\=\"(\S+)\"/", $userset['avatar'], $matches);
                        if (remote_file_size($matches[1]) > 30720)
                        $message['avatar'] = "Размер аватара превышает 30 Кб";
                    }
                    else
                        $message['avatar'] = $userset['avatar'];
                    
                }
            }
            
            if ($app->user->inIgnore($message['posterName']))
                    $message['body'] = $app->locale->ignore_user1;
            else
                $message['body'] = $app->subs->CensorTxt($message['body']);
            
            $message['subject'] = $app->subs->CensorTxt($message['subject']);
            $message['counter'] = $counter;
            
            // add watch-karma-modifiers link
            if (false)
            {
                $message['karmaModifiers'] = array();
                $namesQuery = $db->query("SELECT k.user1, m.realName, k.action FROM {$db_prefix}karmawatch as k LEFT JOIN {$db_prefix}members as m ON (k.user1 = m.memberName) WHERE k.ID_MSG = {$message['ID_MSG']}", false);
                if ($namesQuery->num_rows > 0)
                {
                    while( $row = $namesQuery->fetch_assoc() )
                    {
                        $message['karmaModifiers'][] = $row;
                    }
                }
            }
            
            // Preparing for  LJ sharing buttons
            $ljMessage = (strlen($message['body']) > 512) ? substr($message['body'], 0, 512) . "..." : $message['body'];
            $message['LJSubject'] = mb_convert_encoding($message['subject'], "UTF-8", "CP1251");
            $shareMessage = $ljMessage . '<br><br>Источник: <a href="' . $service->siteurl . '/' .$msgID.'" target="_blank">forum.theprodigy.ru/'.$msgID.'</a>';
            $message['LJMessage'] = mb_convert_encoding($shareMessage, "UTF-8", "CP1251");
            
            // Blog Mod
            if($service->isBlog)
            {
                $requestBlog = $db->query("SELECT m.ID_MEMBER_LAST_COMMENT, m.numComments, IFNULL(lbc.logTime, 0) AS logTime,
                        bc.postedTime AS lastPosterTime, bc.posterName, IFNULL(mem.realName, bc.posterName) AS displayName
                    FROM {$db_prefix}messages AS m, {$db_prefix}blog_comments AS bc
                    LEFT JOIN {$db->db_prefix}log_blog_comments AS lbc ON (lbc.ID_MSG=m.ID_MSG AND lbc.ID_MEMBER=$ID_MEMBER)
                    LEFT JOIN {$db->db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER_LAST_COMMENT)
                    WHERE m.ID_MSG=$msgID
                    AND m.ID_LAST_COMMENT=bc.ID_COMMENT LIMIT 1;", false);
                $messsage['blog'] = $requestBlog->fetch_assoc();
            }
            
            
            // Karma
            $karmaQuery = $db->query("SELECT karmaGood, karmaBad, karmaGoodExecutors, karmaBadExecutors FROM `messages` WHERE ID_MSG = {$msgID} LIMIT 1;");
            $karma = $karmaQuery->fetch_array();
            if ($karma)
            {
                $karma['actions'] = false;
                
                $KarmaGoodIDs = explode(",", $karma['karmaGoodExecutors']);
                $KarmaBadIDs = explode(",", $karma['karmaBadExecutors']);
                
                if (($app->user->group == 'Administrator') or (($app->user->posts >= $app->conf->karmaMinPosts) && ($app->user->name != 'Guest') && ($message['ID_MEMBER'] != $app->user->id) && !(in_array($app->user->id, $KarmaGoodIDs)) && !(in_array($app->user->id, $KarmaBadIDs))))
                    $karma['actions'] = true;
                
                $karma['css'] = ($karma['karmaGood'] > 0 or $karma['karmaBad'] > 0) ? 'table-row' : 'none';

                $message['karma'] = $karma;
            }
            
            $message['attachment'] = false;
            // attachments
            if ($app->conf->attachmentShowImages && $app->conf->attachmentEnable && !empty($message['attachmentFilename']))
            {
                $message['attachment'] = true;
                $message['attachmentExtension'] = strtolower(substr(strrchr($message['attachmentFilename'], '.'), 1));
                if (in_array($message['attachmentExtension'], $service->imageTypes))
                    $message['attachmentType'] = 'image';
                elseif (in_array($message['attachmentExtension'], $service->audioTypes))
                {
                    $message['attachmentType'] = 'audio';
                    $app->conf->mediaplayer = true;
                }
                elseif (in_array($message['attachmentExtension'], $service->videoTypes))
                {
                    $message['attachmentType'] = 'video';
                    $app->conf->mediaplayer = true;
                }
                else
                    $message['attachmentType'] = null;
            } // attachments
            
            if (stripos($message['body'], '[/audio]') !== false || stripos($message['body'], '[/video]') !== false || stripos($message['comments'], '[/audio]') !== false)
            {
                // message contains audio/video player,
                // we should include media player js and css code in the footer
                $app->conf->mediaplayer = true;
            }

            $message['cmnt_display'] = $message['CLOSED_COMMENTS'] == 1 ? 'none' : 'inline';
            $message['comments'] = $app->comments->prepare($message['comments'], $message['posterName'], $message['notify']);
                        
            $message['agent'] = explode(' #|# ', $message['agent']);
            
            $messages[$message['ID_MSG']] = $message;
            $counter ++;
        } // while messages from DB
        
        $service->messages = $messages;
        
        $service->allow_locking = false;
        if ($app->user->accessLevel() > 1 || ($app->user->name == $topicinfo['posterName'] && $app->user->name != 'Guest' && $app->conf->enableUserTopicLocking))
            $service->allow_locking = true;
        
        //topics locked by a user should get the same icon as topics locked by a moderator.
        $service->img_locked_thread = ($topicinfo['locked'] == 2 ? 'lockthread1' : 'lockthread'.$topicinfo['locked']);
        
        $service->calendar_enabled = false;
        if ($app->conf->cal_enabled && ($app->user->accessLevel() > 1 || ($app->user->name == $topicinfo['posterName'] && $app->user-name != 'Guest')))
        {
            if ($app->calendar->CanPost())
                $service->calendar_enabled = true;
        }
        
        if($currentboard == 16)
        {
            $app->locale->applause = 'плюс пицот';
            $app->locale->smite = 'иди нах';
            $app->locale->applauses = 'плюспицотов';
            $app->locale->smites = 'идинахов';
        }
        else
        {
            $app->locale->applause = 'поощрить';
            $app->locale->smite = 'покарать';
            $app->locale->applauses = 'поощрений';
            $app->locale->smites = 'покараний';
        }
        
        if($app->conf->QuickReply && $app->user->QuickReply && (!$mstate || $app->user->accessLevel() > 1))
            $service->quickReplyForm = true;
        else
            $service->quickReplyForm = false;
        
        if($app->conf->QuickReplyExtended && $app->user->accessLevel() > 1)
            $service->quickReplyExtendedForm = true;
        else
            $service->quickReplyExtendedForm = false;
        
        $service->boardviewers = $this->getBoardViewersList($currentboard);
            
        $this->render('templates/thread/thread.template.php');
        
    } // Display()
    
    public function findBoard($prevboard, $threadid) {
        $dbrq = $this->app->db->query(
            "SELECT ID_BOARD FROM {$this->app->db->prefix}topics AS board WHERE ID_TOPIC = $threadid", false
        );
        if ($dbrq->num_rows == 0)
            $this->app->errors->abort($this->app->locale->txt[106], $this->app->locale->txt[472]);
        else {
            list($board) = $dbrq->fetch_row();
            
            $request_uri = $this->request->uri();
            $new_uri = str_replace("/b$prevboard/", "/b$board/", $request_uri);
            
            
            $this->app->errors->log("__DEBUG__: redirecting to $new_uri.");
            return $this->service->redirect($new_uri);
        }
    } // findBoard()
    
    public function reply($request, $response, $service, $app)
    {
        $GET = $request->paramsGet();
        $SERVER = $request->server();
        $COOKIE = $request->cookies();
        $POST = $request->paramsPost();
        $namedParams = $request->paramsNamed();
        $service->board = $namedParams->get('board');
        $service->thread = $namedParams->get('thread');
        $service->quotemsg = $namedParams->get('quote');
        
        if(!isset($service->action))
            $service->action = 'reply';
        
        $service->cValue=(((date('YmdH')%113)*107)%113)+113;
        
        if ($app->user->name == 'Guest')
        {
            if ($app->conf->enable_guestposting)
            {
                // show confirm dialog if not confirmed before
                if (!$app->session->get('guestRulesConfirmed', false) && intval($POST->get('cconfirm')) != $service->cValue)
                    return $this->render('templates/thread/confirm.template.php');
                // remember confirmation
                if ($POST->get('cconfirm') == $service->cValue) {
                    $app->session->check('post');
                    $app->session->store('guestRulesConfirmed', true);
                }
            }
            else
            {
                // guest posting disabled, show error
                return $this->error($app->locale->txt[165]);
            }
        }
        
        if ($POST->get('naztem') != null) {
            // A post was submitted
            return $this->postReply($request, $response, $service, $app);
        }
        
        if ($GET->get('linkcalendar') != null)
        {
            $app->calendar->ValidatePost();
        }
        
        $threadinfo = array('locked' => 0, 'ID_MEMBER_STARTED'=>'-1');
        $mstate = 0;
        
        $db_prefix = $app->db->prefix;
        
        //check for blog board
        $blogmod_groups = explode(',', $app->conf->blogmod_groups);
        $requestBlog = $app->db->query("SELECT ID_BOARD FROM {$db_prefix}boards WHERE ID_BOARD={$service->board} AND isBlog=1", false);
        $isBlog = ($requestBlog->num_rows > 0) ? true : false;
        $requestBlog = $app->db->query("SELECT posts FROM {$db_prefix}members WHERE ID_MEMBER={$app->user->id}", false);
        $post_count = $requestBlog->fetch_row();
        
        //check posting permission
        if ($isBlog && $app->user->accessLevel() < 2)
        {
        //check for new topic post
            if ($service->thread == '')
            {
                //if postcount not enough and membergroup isn't allowed, show error message
                if ( ($post_count[0] < $app->conf->blogmod_minpost) && ($app->conf->blogmod_groups == '' || ($app->conf->blogmod_groups != '' && !in_array($app->user->group, $blogmod_groups))) )
                    return $app->errors->abort('', $app->locale->blogmod11);
                $requestBlog = $app->db->query("SELECT COUNT(*) FROM {$db_prefix}topics WHERE ID_BOARD={$service->board} AND ID_MEMBER_STARTED={$app->user->id}", false);
                $blog_total = $requestBlog->fetch_row();
                if ($blog_total[0] >= $app->conf->blogmod_maxblogsperuser)
                    return $app->errors->abort('', $app->locale->blogmod12);
            }
            //check for post reply
            else
            {
                $requestBlog = $app->db->query("SELECT ID_TOPIC FROM {$db_prefix}topics WHERE ID_BOARD={$service->board} AND ID_MEMBER_STARTED={$app->user->id}", false);
                if ($requestBlog->num_rows == 0)
                    return $this->error($app->locale->blogmod13);
            }
        }
        
        if ($service->thread != '')
        {
            $dbrq = $app->db->query("SELECT * FROM {$db_prefix}topics WHERE ID_TOPIC={$service->thread}");
            $threadinfo = $dbrq->fetch_array();
            $mstate = $threadinfo['locked'];
        }
        else if ($service->action != 'newthread')
            return $app->errors->abort('', $app->locale->txt[472] . ' Error ' . $service->action);
        
        if ($threadinfo['locked'] != 0 && $app->user->accessLevel() < 2)  // don't allow a post if it's locked
            return $app->errors->abort('', $app->locale->txt[90]);
        
        # Determine what category we are in.
        $dbrq = $app->db->query("
            SELECT b.ID_BOARD as bid, b.name as bname, c.ID_CAT as cid, c.memberGroups, c.name as cname, b.isAnnouncement
            FROM {$db_prefix}boards as b, {$db_prefix}categories as c
            WHERE (b.ID_BOARD = {$service->board}
                AND b.ID_CAT=c.ID_CAT)", false);
        if ($dbrq->num_rows == 0)
            return $app->errors->abort('', $app->locale->yse232);
        $bcinfo = $dbrq->fetch_array();
        $service->cat = $bcinfo['cid'];
        $service->catname = $bcinfo['cname'];
        $service->board = $bcinfo['bid'];
        $service->boardname = $bcinfo['bname'];
        
        if ($bcinfo['isAnnouncement'] && $service->thread == '' && $app->user->accessLevel() < 2)
            return $app->errors->abort('', $app->locale->announcement1);
        
        $memgroups = explode(',', $bcinfo['memberGroups']);
        if (!(in_array($app->user->group, $memgroups) || $memgroups[0] == null || $app->user->accessLevel() > 2))
            return $app->errors->abort('', $app->locale->txt[1]);
        
        if ($service->action == 'newthread')
            $service->title = $app->locale->txt[33];
        elseif ($service->action == 'reply')
            $service->title = $app->locale->txt[25];
        
        $service->msubject = $service->mname = $service->memail = $service->mdate = $service->musername = $service->micon = $service->mip = $service->mmessage = $service->mns = $service->mid = '';
        $service->form_message = '';
        $service->form_subject = '';
        
        if ($service->thread != '' && $service->quotemsg != '')
        {
            // $app->session->check('get'); // FIXME why do we check session here?
            
            $dbrq = $app->db->query("
                SELECT m.subject, m.posterName, m.posterEmail, m.posterTime, m.icon, m.posterIP, m.body, m.smiliesEnabled, m.ID_MEMBER, m.comments
                FROM {$db_prefix}messages as m, {$db_prefix}topics as t, {$db_prefix}boards as b, {$db_prefix}categories as c
                WHERE (m.ID_MSG = {$service->quotemsg}
                    AND m.ID_TOPIC = t.ID_TOPIC
                    AND t.ID_BOARD = b.ID_BOARD
                    AND b.ID_CAT = c.ID_CAT
                    AND (FIND_IN_SET('{$app->user->group}', c.memberGroups) != 0 || c.memberGroups = '' || '{$app->user->group}' LIKE 'Administrator' || '{$app->user->group}' LIKE 'Global Moderator')
                )", false);

            list($service->msubject, $service->mname, $service->memail, $service->mdate, $service->micon, $service->mip, $service->mmessage, $service->mns, $service->mi, $service->mcomments) = $dbrq->fetch_row();
            
            if ($service->mi != '-1')
            {
                $dbrq = $app->db->query("
                    SELECT realName
                    FROM {$db_prefix}members
                    WHERE ID_MEMBER='{$service->mi}'
                    LIMIT 1", false);
                if ($request->num_rows != 0)
                    list($service->mname) = $dbrq->fetch_row();
            }
            
            $service->form_message = $service->un_html_entities(preg_replace("|<br( /)?[>]|","\n",$service->mmessage));
            $service->form_message = $app->subs->CensorTxt($service->form_message);
            
            if ($app->conf->removeNestedQuotes)
            {
                $service->form_message = preg_replace("-\n*\[quote([^\\]]*)\]((.|\n)*?)\[/quote\]([\n]*)-", "\n", $service->form_message);
                $service->form_message = preg_replace("/\n*\[\/quote\]\n*/", "\n", $service->form_message);
            }
            
            $service->form_message = "[quote author={$service->mname} msg={$service->quotemsg} date={$service->mdate}]\n{$service->form_message}\n[/quote]\n";
            
            if (!empty($service->postcommentquote))
            {
                $csvdata = explode("\r\n", $mcomments);
                $csvline = $csvdata[$service->postcommentquote-1];
                $postComment = explode("#;#", $csvline);
    
                $service->form_message = "[quote author={$postComment[0]} msg={$service->quotemsg}-{$service->postcommentquote} date={$postComment[2]}]\n{$service->form_message}\n\n" . stripslashes($postComment[3]) . "\n[/quote]\n";
            }
        
             // Replace embedded media with links
            $find_tags = array(
                "/\[url\=.*?\]\[img\]/si",
                "/\[\/img\]\[\/url\]/si",
                "/\[img.*?\]/si",
                "/\[\/img\]/si",
                "/\[media.*?\]/si",
                "/\[\/media\]/si",
                "/\[youtube.*?\]/si",
                "/\[\/youtube\]/si",
                "/\[y\](.+?)\[\/y\]/si",
                "/(?<=^|\s)(https?:\/\/youtu.be\/.+?)(?=\s|$)/",
                "/(?<=^|\s)(https?:\/\/www.youtube.com\/watch\?.+?)(?=\s|$)/"
            );
            $replacement_tags = array(
                "[url class=img]",
                "[/url]",
                "[url class=img]",
                "[/url]", "[url]",
                "[/url]",
                "[url class=img]",
                "[/url]",
                "[url class=img]\\1[/url]",
                "[url class=img]\\1[/url]",
                "[url class=img]\\1[/url]"
            );
            $service->form_message = preg_replace($find_tags, $replacement_tags, $service->form_message);
            
            $service->form_subject = $app->subs->CensorTxt($service->msubject);
            
            if (!stristr(substr($service->msubject, 0, 3), 're:'))
                $service->form_subject = '' . $service->form_subject;
            
            if (!empty($service->quickreplyquote))
                return $this->ajax(str_replace(array('&quot;', '&lt;', '&gt;'), array('"', '<', '>'), $service->form_message), 'txt');
        }
        else if ($service->thread != '' && $service->quotemsg == '')
        {
            $dbrq = $app->db->query("SELECT subject, posterName, posterEmail, posterTime, icon, posterIP, body, smiliesEnabled, ID_MEMBER
                FROM {$db_prefix}messages
                WHERE ID_TOPIC={$service->thread}
                ORDER BY ID_MSG
                LIMIT 1", false);
            
            list($service->msubject, $service->mname, $service->memail, $service->mdate, $service->micon, $service->mip, $service->mmessage, $service->mns, $service->mi) = $dbrq->fetch_row();

            $service->form_subject = $app->subs->CensorTxt($service->msubject);
            
            if (!stristr(substr($service->msubject, 0, 3),'re:'))
                $service->form_subject = '' . $service->form_subject;
        }
        
        if (!$service->form_subject)
            $service->sub = '<i>' . $service->esc($app->locale->txt[33]) . '</i>';
        else
            $service->sub = $service->esc($service->form_subject);
        
        if ($isBlog)
            $service->form_subject = '';
        
        $service->guestname = $COOKIE->get('guestname', '');
        $service->guestemail = $COOKIE->get('guestemail', '');
        
        if ($app->conf->attachmentEnable)
            $service->attachment_fields = true;
        
        if ($app->user->name == 'Guest' && $app->conf->attachmentEnableGuest == 0)
            $service->attachment_fields = false;
        
        if (!(in_array($app->user->group, explode(',', trim($app->conf->attachmentMemberGroups))) || $app->user->accessLevel() > 2))
            $service->attachment_fields = false;
        
        $service->ses_id = $app->session->id;
        
        if ($service->thread > 0)
            $service->thread_summary = $this->thread_summary($service->thread);
        
        $this->render('templates/thread/reply.template.php');
    } // reply()
    
    public function thread_summary($thread) {
        // how many messages to show
        $limitString = ($this->app->conf->topicSummaryPosts < 0) ? '' : (' LIMIT ' . (!is_numeric($this->app->conf->topicSummaryPosts) ? '0' : $this->app->conf->topicSummaryPosts));
        
        $db_prefix = $this->app->db->prefix;
        $usergroup = $this->app->user->group;
        $dbrq = $this->app->db->query("
            SELECT m.posterName, m.posterTime, m.body, m.smiliesEnabled
            FROM {$db_prefix}messages AS m, {$db_prefix}topics as t, {$db_prefix}boards as b, {$db_prefix}categories as c
            WHERE m.ID_TOPIC='$thread'
                AND t.ID_TOPIC=m.ID_TOPIC
                AND b.ID_BOARD=t.ID_BOARD
                AND c.ID_CAT=b.ID_CAT
                AND (FIND_IN_SET('$usergroup', c.memberGroups) != 0 OR c.memberGroups = '' OR '$usergroup' LIKE 'Administrator' OR '$usergroup' LIKE 'Global Moderator')
            ORDER BY ID_MSG DESC
            $limitString", false);
        
        $messages = array();
        while($row = $dbrq->fetch_assoc()) {
            if ($this->app->user->inIgnore($row['posterName']))
                continue;
            
            $messages[] = array(
                'userinfo' => $this->app->user->loadDisplay($row['posterName']),
                'time' => $this->app->subs->timeformat($row['posterTime']),
                'body' => $this->app->subs->CensorTxt($row['body']),
                'smilies' => $row['smiliesEnabled']
            );
        }
        
        return $messages;
    } // thread_summary()
    
    public function postReply($request, $response, $service, $app)
    {
        $app->session->check('post');
        $POST = $request->paramsPost();
        $PARAMS = $request->paramsNamed();
        $COOKIES = $request->cookies();
        $SERVER = $request->server();
        $REMOTE_ADDR = $SERVER->get('REMOTE_ADDR');
        
        $input_waction = $POST->get('waction');
        $input_name = $POST->get('name');
        $input_message = $POST->get('message');
        $input_serial = $POST->get('serial');
        $input_subject = $POST->get('naztem');
        $input_lock = $POST->get('lock');
        $input_ns = $POST->get('ns');
        
        $service->thread = $PARAMS->get('thread');
        
        $mreplies = 0;

        $app->board->load($PARAMS->get('board'));
        
        if ($app->user->posts < 100 && $app->security->isTOR()){
            $app->im->notifyAdmins("предотвращён постинг через TOR", "Пользователь: {$service->siteurl}/people/" . urlencode($username) . '/');
            return $app->errors->abort('', "Вам запрещено отвечать в этом разделе Форума!");
        }
        
        if ($app->security->containsBannedNickPart($app->user->realname))
        {
            $app->im->notifyAdminsLater("Nick contains banned parts", var_export($_SERVER, true));
            return $app->errors->abort('', 'Ошибка размещения сообщения.');
        }
        
        $waction = $POST->get('waction');
        
        if ($waction == 'preview')
            return $this->preview($request, $response, $service, $app);
        
        if ($app->security->containsForbiddenText($input_message))
            return $app->errors->abort('', "Вам запрещено отвечать на Форуме! Обратитесь к администраторам за разъяснением!");
        
        if ($input_serial != $app->conf->serial)
        {
            $app->im->notifyAdminsLater("Возможный спам: " . $input_subject, "Введён неверный серийный номер:\r\n" . $input_message . "\r\n" . $input_name . ", " . $app->user->realname . " (" . $app->user->username . "), $REMOTE_ADDR\r\n" . $request->uri());
            return $this->error("Неверный серийный ключ! Обратитесь к администратору за разъяснением!");
        }
        
        if ($POST->get('linkcalendar') != null)
        { 
            $app->calendar->ValidatePost();
        }
        
        if ($app->user->name == 'Guest' && $app->conf->enable_guestposting == 0)
            return $this->error($txt[165]);
        
        $db = $app->db;
        $db_prefix = $app->db->prefix;
        
        if ($service->thread != '' && $app->user->accessLevel() < 2)
        {
            $dbrq = $app->db->query("
                SELECT locked
                FROM {$db_prefix}topics
                WHERE ID_TOPIC={$service->thread}", false);
            list($tmplocked) = $dbrq->fetch_array();
            if ($tmplocked != 0)
                return $this->error($txt[90]); // don't allow a post if it's locked
        }
        
        if (empty($service->thread))
            $dbrq = $app->db->query("
                SELECT b.ID_BOARD
                FROM {$db_prefix}boards AS b, {$db_prefix}categories AS c
                WHERE b.ID_BOARD={$service->board}
                AND c.ID_CAT=b.ID_CAT
                AND (FIND_IN_SET('{$app->user->group}', c.memberGroups) != 0 OR c.memberGroups = '' OR '{$app->user->group}' LIKE 'Administrator' OR '{$app->user->group}' LIKE 'Global Moderator')", false);
        else
            $dbrq = $app->db->query("
                SELECT t.ID_TOPIC
                FROM {$db_prefix}topics AS t, {$db_prefix}boards AS b, {$db_prefix}categories AS c
                WHERE t.ID_TOPIC={$service->thread}
                AND b.ID_BOARD={$service->board}
                AND b.ID_BOARD=t.ID_BOARD
                AND c.ID_CAT=b.ID_CAT
                AND (FIND_IN_SET('{$app->user->group}', c.memberGroups) != 0 OR c.memberGroups = '' OR '{$app->user->group}' LIKE 'Administrator' OR '{$app->user->group}' LIKE 'Global Moderator')", false);
        
        if ($dbrq->num_rows == 0)
            return $this->error($txt[1]);
        
        // If poster is a Guest then evaluate the legality of name and email
        if ($app->user->name == 'Guest')
        {
            //now make sure that guestname is not containing banned nicknames
            if ($app->security->containsBannedNickPart($input_name))
                return $this->error("Вам не разрешено отвечать на форуме! Обратитесь к администраторам за разъяснением!");
            
            $input_name = trim($input_name);
            
            $service->validate($input_name, $app->locale->txt[75])->isAlnum();
            $service->validate($input_name, $app->locale->txt[568])->isLen(1, 25);
            
            if ($input_name == '_')
                return $this->error($txt[75]);
            
            if (empty($input_email))
                return $this->error($txt[76]);

            $service->validate($input_email, $app->locale->txt[243])->isRegex("/^[0-9A-Za-z@\._\-]+$/");
        }
        
        // did they toggle lock topic after post?
        $locked = ($app->user->accessLevel() > 1 && $input_lock == 'on')? 1 : 0 ;
        $isLocked = ($locked ? ', locked=1' : '');
        
        $input_subject = trim($input_subject);
        $input_message = trim($input_message);
        
        if(empty($input_subject))
            return $this->error($app->locale->txt[77]);
        if($input_subject == ' ')
            return $this->error($app->locale->txt[77]);
        if(empty($input_message))
            return $this->error($app->locale->txt[78]);
        $service->validate($input_message, $app->locale->txt[499])->isLen(1, $app->conf->MaxMessLen);
        
        if ($app->user->guest && !$app->session->get('guestRulesConfirmed', false))
        { // FIXME
            //notifyAdminsLater("Попытка взлома на форуме", "Скрипт Sources/Post.php\r\n".(var_export($_SERVER, true))."\r\n\r\n".var_export($_REQUEST, true));
            //return $this->error("Попытка спама! Твой IP сохранён.");
            return $this->error($app->locale->txt['yse304']);
        }
        
        $app->security->spam_protection();
        
        if (strlen($input_subject) > 80)
        {
            $input_subject = substr($input_subject, 0, 80);
            if (substr($input_subject, -1) == '\\')
                $input_subject = substr($input_subject, 0, 79);
        }
        
        if ($app->user->name != 'Guest') // If not guest, get name and email.
        {
            $input_name = $app->user->name;
            $input_email = $app->user->email;
        }
        
        // Preparse code (zef)
        $input_message = $app->subs->preparsecode($input_message);
        
        $e_name = $app->db->escape_string($input_name);
        
        if ($app->user->guest)
        {
            # If user is Guest, then make sure the chosen name
            # is not reserved or used by a member.
            
            $dbrq = $app->db->query("
                ELECT ID_MEMBER
                FROM {$db_prefix}members
                WHERE (memberName='$e_name' || realName='$e_name')", false);
            
            if ($dbrq->num_rows != 0)
                $this->error(473);
            
            //now make sure that guestname is not containing banned nicknames
            if ($app->security->containsBannedNickPart($input_name))
                return $this->error("Вам не разрешено отвечать на форуме! Обратитесь к администраторам за разъяснением!");
            
            // now make sure they arn't trying to use a reserved name
            $dbrq = $app->db->query("
                SELECT *
                FROM {$db_prefix}reserved_names
                ORDER BY setting", false);
            
            $matchword = $matchcase = $matchuser = $matchname = '';

            for ($i = 0; $i < 4; $i++)
            {
                $tmp = $dbrq->fetch_row();
                ${$tmp[0]}=$tmp[1];
            }
            
            $namecheck = $matchcase ? $name : strtolower ($name);
            
            while ($tmp = $dbrq->fetch_row())
            {
                if ($tmp[0] == 'word')
                {
                    $reserved = $tmp[1];
                    $reservecheck = $matchcase ? $reserved : strtolower ($reserved);
                    if ($matchname)
                    {
                        if ($matchword)
                        {
                            if ($namecheck == $reservecheck)
                                return $this->error("{$app->locale->txt[244]} $reserved");
                        }
                        else
                        {
                            if (strstr($namecheck, $reservecheck))
                                return $this->error("{$app->locale->txt[244]} $reserved");
                        }
                    }
                }
            }
        } // if guest
        
        // multinick check mod (by dig7er, 14 May 2008)
        $rname = !empty($app->user->realname) ? $app->user->realname : $input_name;
        
        // fix names in cookies -> make them hashed
        $notHashNameFound = false;
        $cookie_nicks = $COOKIES->get('nicks');
        if (is_array($cookie_nicks))
        {    
            foreach ($cookie_nicks as $nname => $nvalue)
            {
                if (!is_int($nname) or abs($nname) < 1000)
                {
                    $notHashNameFound = true;
                    break;
                }
            }
        }
        
        if ($notHashNameFound)
        {
            $pattern = "/^\-?[0-9]+$/si";
            if (is_array($cookie_nicks))
            {
                foreach ($cookie_nicks as $nick => $value)
                {
                    if (preg_match($pattern, $nick, $matches) == 0)
                        $response->cookie("nicks[$nick]", null);
                    unset($cookie_nicks[$nick]);
                }
                $response->cookie("nicks", null);
                //unset($cookie_nicks);
                $cookie_nicks = null;
                unset($app->session->userInfo);
            }
        } // if $NotHashNameFound
        
        $value = $app->user->guest ? "$rname (Гость)" : '<a href="'.SITE_ROOT.'/people/'.urlencode($app->user->name).'/">'.$service->esc($rname).'</a>';
        $value .= ", ".date("d-m-Y H:i:s"). ", $REMOTE_ADDR, <a href=\"" . SITE_ROOT . '/b' . $service->board . '/t' . $service->thread . '/">тема</a>';
        if (is_array($cookie_nicks))
        {
            if ($app->user->guest and !in_array(crc32($rname), array_keys($cookie_nicks)) and sizeof($cookie_nicks) >= 2)
            {
                $app->in->notifyAdminsLater("Возможный мультиник", print_r($cookie_nicks, true) . "\r\n\r\n" . $rname . ", $REMOTE_ADDR");
                return $this->error("Ты уже использовал другой ник для ответа на форуме. Пожалуйста, используй его и далее. С вопросами обращайся на <a href=\"mailto:dig7er@gmail.com\">dig7er@gmail.com</a>");
            }
        }
        
        if (isset($app->session->userInfo) and ($app->session->userInfo['name'] != $rname or $app->session->userInfo['username'] != $app->user->name))
        {
            if ($app->user->guest and !in_array(crc32($rname), array_keys($app->session->userInfo['nicks'])) and sizeof($app->session->userInfo['nicks']) >= 2)
            {
                $app->im->notifyAdminsLater("Possible multinick", $rname . " ({$app->user->name}), $REMOTE_ADDR, ".$SERVER->get('HTTP_USER_AGENT')."\r\n\r\nЗаблокирован: ".((!in_array($rname, array_keys($app->session->userInfo['nicks'])) and sizeof($app->session->userInfo['nicks']) >= 2)?"ДА":"НЕТ")."\r\n\r\n<".SITE_ROOT."/b{$service->board}/t{$service->thread}/>\r\n\r\n" . print_r($app->session->userInfo, true) . "\r\n\r\n" . print_r($_SERVER, true) . "\r\n\r\n" . print_r($cookie_nicks, true));
                return $this->error("Ты уже использовал другой ник для ответа на форуме. Пожалуйста, используй его и далее. С вопросами обращайся на <a href=\"mailto:dig7er@gmail.com\">dig7er@gmail.com</a>");
            }
        
            // combine cookie and session nicks arrays
            $nicks = $app->session->userInfo['nicks'];
            if (is_array($cookie_nicks))
            {
                foreach ($cookie_nicks as $key => $value)
                    $nicks[$key] = $value;
            }
            
            // update cookie for each nick
            foreach ($nicks as $nick => $v)
            {
                $response->cookie("nicks[$nick]", stripslashes($v), time()+60*60*24*14);
                $cookie_nicks[$nick] = stripslashes($v);
            }
        }
        
        if ($app->user->guest)
        {
            $response->cookie("guestname", $rname, time()+60*60*24*14);
            $response->cookie("guestemail", $input_email, time()+60*60*24*14);
        }
        
        $response->cookie("nicks[".crc32($rname)."]", $value, time()+60*60*24*14);
        $cookie_nicks[crc32($rname)] = $value;
        
        $app->session->userInfo = array(
            'name' => $rname,
            'username' => $app->user->name,
            'IP' => $REMOTE_ADDR,
            'browser' => $SERVER->get('HTTP_USER_AGENT'),
            'nicks' => $cookie_nicks
        );
        
        $multinick = "";
        if (is_array($cookie_nicks)){
            foreach ($cookie_nicks as $v)
            $multinick .= "$v<br />";
        }
        
        // Store client IP behind the proxy if available
        if ($SERVER->get('HTTP_X_FORWARDED_FOR') !== null || $SERVER->get('HTTP_CLIENT_IP') !== null)
        {
            $FWD = array();
            $multinick .= 'X_Forwarded_For: ';
            if ($SERVER->get('HTTP_X_FORWARDED_FOR') !== null)
                $FWD[] = $SERVER->get('HTTP_X_FORWARDED_FOR');
            if ($SERVER->get('HTTP_CLIENT_IP') !== null)
                $FWD[] = $SERVER->get('HTTP_CLIENT_IP');
            $multinick .= implode(', ', $FWD) . '<br />';
        }
        
        // Validate the attachment if there is one
        
        $FILES = $request->files();
        // replace as much special characters as possible and remove all other characters
        $attachment = $FILES->get('attachment');
        if ($attachment !== null)
        {
            $attachment['name'] = preg_replace(
                array("/\s/", "/[дегвба]/", "/[ДЕБВАГ]/", "/[цтуфхшр]/", "/[ЦШФТФХ]/", "/[йикл]/", "/[ЙКЛИ]/", "/ [ыьщъ]/", "/[ЬЫЪЩ]/", "/[помн]/", "/[НОП]/", "/[з]/", "/[с]/",	"/[С]/", "/[^\w_.-]/"),
                array('_', 'a', 'A', 'o', 'O', 'e', 'E', 'u', 'U', 'i', 'I', 'c', 'n', 'N', ''),
                $attachment['name']);
            $FILES->set('attachment', $attachment);
            
            if ($attachment['name'] != '' && $app->conf->attachmentEnable > 0 && ($app->conf->attachmentMemberGroups == "" || in_array($app->user->group, explode(',', trim($app->conf->attachmentMemberGroups))) || $app->user->accessLevel() > 2))
            {
                if ($attachment['size'] > $app->conf->attachmentSizeLimit * 1024)
                    return $this->error("{$app->locale->txt['yse122']} {$app->conf->attachmentSizeLimit} {$app->locale->txt['yse211']}.");
                
                if ($app->conf->attachmentCheckExtensions == '1')
                    if (!in_array(strtolower(substr(strrchr($attachment['name'], '.'), 1)), explode(',', strtolower($app->conf->attachmentExtensions))))
                    {
                        $failed = $attachment['name'];
                        return $this->error("$failed.<br />{$app->locale->txt['yse123']} {$app->conf->attachmentExtensions}.");
                    }
                
                // make sure they aren't trying to upload a nasty file
                $disabledFiles = array('CON','COM1','COM2','COM3','COM4','PRN','AUX','LPT1');
                if (in_array(strtoupper(substr(strrchr($attachment['name'], '.'), 1)), $disabledFiles))
                {
                    $failed = $attachment['name'];
                    return $this->error("$failed.<br />{$app->locale->txt['yse130b']}.");
                }
                
                if (file_exists($app->conf->attachmentUploadDir . "/" . $attachment['name']))
                    return $this->error('yse125');
                
                $dirSize = '0';
                $dir = opendir($app->conf->attachmentUploadDir);
                while ($file = readdir($dir))
                    $dirSize = $dirSize + filesize($app->conf->attachmentUploadDir . '/' . $file);
                
                if ($attachment['size'] + $dirSize > $app->conf->attachmentDirSizeLimit * 1024)
                    return $this->error('yse126');
                
                $parts = ($attachment !== null) ? preg_split("~(\\|/)~", $_FILES['attachment']['name']) : array();
                $destName = array_pop($parts);
                
                if (!move_uploaded_file($attachment['tmp_name'], $app->conf->attachmentUploadDir . '/' . $destName))
                    return $this->error("yse124");
                $attachment_size = $attachment['size'];
                
                chmod ("{$app->conf->attachmentUploadDir}/$destName",0644) || $chmod_failed = 1;
            }
            else
            {
                $attachment['name'] = 'NULL';
                $attachment_size = 0;
            }
        } // if attachment not null
        
        // If no thread specified, this is a new thread.
        // Find a valid random ID for it.
        $newtopic = ($service->thread == '') ? true : false;
        $time = time();
        $se = ($input_ns ? 0 : 1);
        
        $agent_fp = array();
        $agent_fp[] = $SERVER->get('HTTP_USER_AGENT');
        $agent_fp[] = ($POST->get('tfp') === null) ? 'none' : $POST->get('tfp');
        $agent_fp[] = ($POST->get('bfp') === null) ? 'none' : $POST->get('bfp');
        $agent_fp = implode(' #|# ', $agent_fp);
        
        $naztem = $db->escape_string($input_subject);
        $message = $db->escape_string($input_message);
        $nowListening = $db->escape_string($POST->get('nowListening'));
        $agent_fp = $db->escape_string($agent_fp);
        $multinick = $db->escape_string($multinick);
        $e_email = $db->escape_string($input_email);
        $icon = $db->escape_string($POST->get('icon'));
        $quickPollTitle = $POST->get('quickPoll');
        $quickPollTitle = $db->escape_string($quickPollTitle);
                
        // Guest Rules Confirmation mod, by dig7er
        $app->session->store('guestRulesConfirmed', true);
        
        if ($newtopic)        // This is a new topic. Save it.
        {
            if ($app->board->isAnnouncement() && $app->user->accessLevel() < 2)
                return $this->error('announcement1');
            
            $tmpname = ($attachment['name'] == 'NULL') ? 'NULL' : "'" . $db->escape_string($attachment['name']) . "'";
            $dbrq = $db->query("
                INSERT INTO {$db_prefix}messages (ID_MEMBER, subject, posterName, posterEmail, posterTime, posterIP, smiliesEnabled, body, icon, attachmentSize, attachmentFilename, nowListening, multinick, closeComments, agent)
                VALUES ({$app->user->id}, '$naztem', '$e_name', '$e_email', $time, '$REMOTE_ADDR', $se, '$message', '$icon', '$attachment_size', $tmpname, '$nowListening', '$multinick', ".($app->user->guest?0:$app->user->closeCommentsByDefault).", '$agent_fp')", false);
                
            $ID_MSG = $db->insert_id;
            if ($ID_MSG > 0)
            {
                $dbrq = $db->query("
                    INSERT INTO {$db_prefix}topics (ID_BOARD, ID_MEMBER_STARTED, ID_MEMBER_UPDATED, ID_FIRST_MSG, ID_LAST_MSG, locked, numViews)
                    VALUES ({$service->board}, {$app->user->id}, {$app->user->id}, $ID_MSG, $ID_MSG, $locked, 0)", false);
                
                if ($db->insert_id > 0)
                {
                    $threadid = $db->insert_id;
                    $dbrq = $db->query("
                        UPDATE {$db_prefix}messages
                        SET ID_TOPIC = $threadid
                        WHERE (ID_MSG = $ID_MSG)", false);
                    
                    $dbrq = $db->query("
                        UPDATE {$db_prefix}boards
                        SET numPosts = numPosts + 1, numTopics = numTopics + 1
                        WHERE (ID_BOARD = {$service->board})", false);
                    
                    $mreplies = 0;
                    
                    if ($app->conf->trackStats)
                    {
                        $date = getdate(time() + $app->conf->timeoffset * 3600);
                        $statsquery = $db->query("
                            UPDATE {$db_prefix}log_activity
                            SET topics = topics + 1, posts = posts + 1
                            WHERE month = {$date['mon']}
                            AND day = {$date['mday']}
                            AND year = {$date['year']}", false);
                        
                        if ($db->affected_rows == 0)
                        $statsquery = $db->query("
                            INSERT INTO {$db_prefix}log_activity
                            (month, day, year, topics, posts)
                            VALUES ($date[mon], $date[mday], $date[year], 1, 1)", false);
                    }
                    
                    if ($POST->get('linkcalendar') !== null)
                        $app->calendarInsertEvent($service->board, $threadid, $POST->get('evtitle'), $app->user->id, $POST->get('month'), $POST->get('day'), $POST->get('year'), $POST->get('span'));
                    
                    if ($app->board->isAnnouncement())
                    {
                        $reqAnn = $db->query("
                            SELECT b.notifyAnnouncements
                            FROM {$db_prefix}boards as b, {$db_prefix}categories as c
                            WHERE (b.ID_BOARD = {$service->board}
                            AND b.ID_CAT = c.ID_CAT)", false);
                        
                        $rowAnn = $reqAnn->fetch_array();
                        
                        if ($rowAnn['notifyAnnouncements'])
                            $this->NotifyUsersNewAnnouncement();
                    }
                    
                    $app->subs->updateStats('topic');
                    $app->subs->updateStats('message');
                    $app->subs->UpdateLastMessage($service->board);
                }
                
                // quick poll mod by dig7er, 14.04.2010
                if ($quickPollTitle !== null)
                {
                    $request = $db->query("REPLACE INTO {$db_prefix}quickpolls
                        (ID_MSG, POLL_TITLE)
                        VALUES ($ID_MSG, '$quickPollTitle')", false);
                }
            } // $ID_MSG > 0
        } // if $newtopic
        else
        {    // This is an old thread. Save it.
            // QuickReplyExtended
            if($app->conf->QuickReply && $app->conf->QuickReplyExtended && $app->user->accessLevel() > 1)
            {
                $csubject = $POST->get('csubject');
                if(strlen($csubject) >= 3)
                {
                    $naztem = $db->escape_string($csubject);
                    $changesubject = $db->query("UPDATE {$db_prefix}messages SET subject='{$naztem}' WHERE ID_TOPIC='{$service->thread}'");
                }
                $modaction = $POST->get('modaction');
                if($modaction != 1)
                {
                    if($modaction == 2)
                    {
                        $dbrq = $db->query("
                            SELECT locked
                            FROM {$db_prefix}topics
                            WHERE ID_TOPIC={$service->threadid}", false);
                        $row = $dbrq->fetch_row();
                        
                        $quicklock = ($row[0] != 0) ? 0 : 1;
                        $dbrq = $db->query("UPDATE {$db_prefix}topics SET locked='{$quicklock}' WHERE ID_TOPIC='{$threadid}'");
                    }
                    elseif($modaction == 3)
                    {
                        $dbrq = $db->query("
                            SELECT isSticky
                            FROM {$db_prefix}topics
                            WHERE ID_TOPIC={$service->thread}", false);
                        $row = $dbrq->fetch_row();
                        
                        $quicksticky = ($row[0] != 0) ? 0 : 1;
                        $dbrq = $db->query("UPDATE {$db_prefix}topics SET isSticky='{$quicksticky}' WHERE ID_TOPIC='{$service->thread}'", false);
                    }
                    elseif($modaction == 4)
                    {
                        $dbrq = $db->query("
                            SELECT locked,isSticky
                            FROM {$db_prefix}topics
                            WHERE ID_TOPIC=$threadid") or database_error(__FILE__, __LINE__, $db);
                        $row = $dbrq->fetch_row();
                        
                        $quicklock = ($row[0] != 0) ? 0 : 1;
                        $quicksticky = ($row[1] != 0) ? 0 : 1;
                        $dbrq = $db->query("
                            UPDATE {$db_prefix}topics SET locked='{$quicklock}',isSticky='{$quicksticky}' WHERE ID_TOPIC='{$service->thread}'", false);
                    }
                }
                
                $movethread = $POST->get('movethread');
                if ($movethread != '' && substr($movethread, 0, 1) != '#' && $movethread != $service->board)
                {
                    $dbrq = $db->query("
                        SELECT numReplies,ID_BOARD FROM {$db_prefix}topics WHERE ID_TOPIC='{$service->thread}'", false);
                    $row = $dbrq->fetch_row();
                    $numReplies = $row[0]+1;
                    $boardid = $row[1];
                    $updateboards = $db->query("
                        UPDATE {$db_prefix}boards SET numPosts=numPosts-'{$numReplies}',numTopics=numTopics-1 WHERE ID_BOARD='{$boardid}'");
                    $newboard = $db->escape_string($movethread);
                    $updateboards2 = $db->query("
                        UPDATE {$db_prefix}boards SET numPosts=numPosts+'{$numReplies}',numTopics=numTopics+1 WHERE ID_BOARD='{$newboard}'", false);
                    $movethread = $db->query("
                        UPDATE {$db_prefix}topics SET ID_BOARD='{$newboard}' WHERE ID_TOPIC='{$service->thread}'", false);
                    
                    $app->subs->updateStats('topic');
                    $app->subs->updateStats('message');
                    $app->subs->updateLastMessage($board);
                    $app->subs->updateLastMessage($newboard);
                    
                    $db->query("
                         UPDATE {$db_prefix}calendar SET id_board='$newboard' WHERE id_topic='{$service->thread}'", false);
                    
                    $service->board = $newboard;
                }
            }
            
            // QuickReplyExtended
            $tmpname = ($attachment['name'] == 'NULL') ? 'NULL' : "'" . $db->escape_string($attachment['name']) . "'";
            //--- Unite two posts if they're last in the topic and made by the same user (by Dig7er)
            $um = false;
            // don't unite messages if we upload an attachment
            if ($attachment['name'] == 'NULL')
            {
                $query = $db->query("SELECT * FROM `messages` WHERE ID_TOPIC = {$service->thread} ORDER BY ID_MSG DESC LIMIT 1");
                $lastPost = $query->fetch_array();
                // return $this->error($lastPost['ID_MEMBER'].' '.$app->user->id);
                $lineBreak = "\n\n";
                if ($lastPost['ID_MEMBER'] == $app->user->id && $lastPost['posterIP'] == $REMOTE_ADDR && (strlen($lastPost['body']) + strlen($message))<65356 && ((time() - $lastPost['posterTime']) < 600))
                {
                    $lastMsgEditQuery = $db->query("UPDATE `messages` SET body = CONCAT(body, \"$lineBreak\", \"$message\") WHERE ID_MSG = {$lastPost['ID_MSG']}", false);
                    $ID_MSG = $lastPost['ID_MSG'];
                    $um = true;
                }
            }
            if (!$um)
            {
                // if not uniting messages
                $app->user->closeCommentsByDefault = empty($app->user->closeCommentsByDefault) ? 0 : $app->user->closeCommentsByDefault;
                $dbrq = $db->query("
                    INSERT INTO {$db_prefix}messages (ID_TOPIC, ID_MEMBER, subject, posterName, posterEmail, posterTime, posterIP, smiliesEnabled, body, icon, attachmentSize, attachmentFilename, nowListening, multinick, closeComments, agent)
                    VALUES ({$service->thread}, {$app->user->id}, '$naztem', '$e_name', '$e_email', $time, '$REMOTE_ADDR', $se, '$message', '$icon', '$attachment_size', $tmpname, '$nowListening', '$multinick', {$app->user->closeCommentsByDefault}, '$agent_fp')", false);
                $ID_MSG = $db->insert_id;
            }
            
            if ($ID_MSG > 0)
            {
                if (!$um) // united message (dig7er)
                    $dbrq = $db->query("
                        UPDATE {$db_prefix}topics
                        SET ID_MEMBER_UPDATED = {$app->user->id}, ID_LAST_MSG = $ID_MSG, numReplies = numReplies + 1 $isLocked
                        WHERE (ID_TOPIC = {$service->thread})", false);
                
                if (!$um) // united message (dig7er)
                    $dbrq = $db->query("
                        UPDATE {$db_prefix}boards
                        SET numPosts = numPosts + 1
                        WHERE (ID_BOARD = {$service->board})", false);
                
                if (!$um) // united message (dig7er)
                    $mreplies++;
                
                if ($app->conf->trackStats == 1 and !$um)
                {
                    $date = getdate(time() + $app->conf->timeoffset * 3600);
                    $statsquery = $db->query("
                        UPDATE {$db_prefix}log_activity
                        SET posts = posts + 1
                        WHERE month = {$date['mon']}
                        AND day = {$date['mday']}
                        AND year = {$date['year']}", false);
                    
                    if ($db->affected_rows == 0)
                        $statsquery = $db->query("
                            INSERT INTO {$db_prefix}log_activity
                            (month, day, year, posts)
                            VALUES ({$date['mon']}, {$date['mday']}, {$date['year']}, 1)", false);
                }
                
                $app->subs->updateStats('message');
                $app->subs->UpdateLastMessage($service->board);
                
                // quick poll mod by dig7er, 14.04.2010
                if (!empty($quickPollTitle))
                    $dbrq = $db->query("
                        REPLACE INTO {$db_prefix}quickpolls
                        (ID_MSG, POLL_TITLE)
                        VALUES ($ID_MSG, '$quickPollTitle')", false);
            } // if $ID_MSG > 0
        } // if not $newtopic
        
        if (!$app->user->guest)
	{
            $dbrq = $db->query("
                SELECT * FROM {$db_prefix}boards
                WHERE ID_BOARD = '{$service->board}'", false);
            
            $pcount = $dbrq->fetch_array();
            $pcounter = $pcount['count'];
            
            if ($pcounter != 1 and !$um) // united message (dig7er)
            {
                ++$app->user->posts;
                $dbrq = $db->query("
                    UPDATE {$db_prefix}members
                    SET posts = posts + 1
                    WHERE ID_MEMBER = {$app->user->id}
                ", false);
            }
            
            # Mark thread as read for the member.
            $dbrq = $db->query("
                REPLACE INTO {$db_prefix}log_topics
                (logTime, ID_MEMBER, ID_TOPIC)
                VALUES (" . time() . ", {$app->user->id}, {$service->thread})", false);
        }
        
        # The thread ID, regardless of whether it's a new thread or not.
        $thread = $service->thread;
        
        # Notify any members who have notification turned on for this thread.
        $app->im->NotifyUsers();
        
        $notify = $POST->get('notify');
        // turn notification on
        if (!empty($notify))
        {
            //include_once("$sourcedir/Notify.php");
            //Notify2(); FIXME
        }
        
        # Let's figure out what page number to show
        $start = (floor($mreplies / $app->conf->maxmessagedisplay)) * $app->conf->maxmessagedisplay;
        
        //  Remove this comment and comment out the other SetLocation so that you are returned
        //  to the same thread after posting.
        if ($app->conf->returnToPost == '1')
            $yySetLocation = "/b{$service->board}/t$thread/new/";
        else
            $yySetLocation = "/b{$service->board}/";
        
        return $this->redirect($yySetLocation);
        
    } // postReply()
    
    public function preview($request, $response, $service, $app)
    {
        $app->session->check('post');
        $POST = $request->paramsPost();
        $message = $POST->get('message');
        
        if(empty($message))
            return $app->errors->abort('', 'Empty message');
        
        $message = $service->unicodeentities($message);
        $message = $service->doubbc($message);
        return $this->ajax_response($message, 'html');
    }
}

?>
