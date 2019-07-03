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
        
        if (empty($currentboard))
            return $this->findBoard($currentboard, $threadid);
        
        $board_moderators = $app->board->moderators($currentboard);
        
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
                WHERE (mes.ID_MSG=t.ID_LAST_MSG && t.ID_BOARD=? && t.ID_TOPIC=? && ((mes2.posterTime $gt_lt mes.posterTime && t2.isSticky $gt_lt= t.isSticky) || t2.isSticky $gt_lt t.isSticky) && t2.ID_LAST_MSG=mes2.ID_MSG && t2.ID_BOARD=t.ID_BOARD)
                ORDER BY t2.isSticky $order, mes2.posterTime $order LIMIT 1";

            $dbst = $db->prepare($query);
            $dbst->execute(array($currentboard, $threadid));
            $threadid = $dbst->fetchColumn();
            $dbst = null;
            if ($threadid){
                return $this->redirect("/b$currentboard/t$threadid/");
            }
            else
                return $this->error('You have reached the end of the topic list');
        }
        
        // at this point it is certain that $threadid holds the correct ID_TOPIC
        // for the topic the user wants to view
        //$viewnum = $threadid;
        
        //check for blog board
        $dbst = $db->prepare("SELECT ID_BOARD FROM {$db_prefix}boards WHERE ID_BOARD=? AND isBlog=1;");
        $dbst->execute(array($currentboard));
        $service->isBlog = ($dbst->fetchColumn()) ? true : false;
        $dbst = null;
        if ($service->isBlog && $app->conf->blogmod_newblogontop)
            $app->conf->viewNewestFirst = 1;

        $dbst = $db->prepare("SELECT b.ID_CAT,b.name,c.name,c.memberGroups FROM {$db_prefix}boards as b,{$db_prefix}categories as c WHERE (b.ID_BOARD=? && b.ID_CAT=c.ID_CAT)");
        $dbst->execute(array($currentboard));
        list($curcat,$boardname,$cat,$temp2) = $dbst->fetch(\PDO::FETCH_NUM);
        $memgroups = explode(',',$temp2);
        $dbst = null;
        
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
            $dbst = $db->prepare("
                SELECT GREATEST(IFNULL(lt.logTime, 0), IFNULL(lmr.logTime,0)) AS logtime, COUNT(m.ID_MSG), MAX(m.ID_MSG)
                FROM {$db_prefix}topics AS t
                LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.ID_TOPIC=t.ID_TOPIC AND lt.ID_MEMBER=?)
                LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD=t.ID_BOARD AND lmr.ID_MEMBER=?)
                LEFT JOIN {$db_prefix}messages AS m ON (m.ID_TOPIC=t.ID_TOPIC)
                WHERE (t.ID_TOPIC = ?)
                GROUP BY t.ID_TOPIC");
            $dbst->execute(array($ID_MEMBER, $ID_MEMBER, $threadid));
            list($ltLastRead, $numMessages, $newestMessage) = $dbst->fetch(\PDO::FETCH_NUM);
            $dbst = null;
            
            if (empty($newestMessage))
                return $this->error($app->locale->txt[472]);
            
            $dbst = $db->prepare("
                SELECT COUNT(*)
                FROM {$db_prefix}messages
                WHERE ID_TOPIC = ?
                AND posterTime <= ?");
            $dbst->execute(array($threadid, $ltLastRead));
            $numReadMessages = $dbst->fetchColumn();
            $dbst = null;
            $numUnreadMessages = $numMessages - $numReadMessages;
            
            if ($app->conf->viewNewestFirst)
                $Page2Show = floor(($numUnreadMessages == 0 ? 0 : $numUnreadMessages - 1) / $app->conf->maxmessagedisplay) * $app->conf->maxmessagedisplay;
            else
                $Page2Show = floor(($numReadMessages  == $numMessages ? $numReadMessages - 1 : $numReadMessages) / $app->conf->maxmessagedisplay) * $app->conf->maxmessagedisplay;
            
            if ($numUnreadMessages > 0)
            {
                $dbst = $db->prepare("
                    SELECT MIN(ID_MSG)
                    FROM {$db_prefix}messages
                    WHERE ID_TOPIC = ?
                    AND posterTime > ?");
                $dbst->execute(array($threadid, $ltLastRead));
                $firstUnreadMessage = $dbrq->fetchColumn();
                $dbst = null;
                $newMsgID = "#msg$firstUnreadMessage";
            }
            elseif ($app->conf->viewNewestFirst)
                $newMsgID = "#msg$newestMessage";
            else
                $newMsgID = '#lastPost';
            
            if (!$app->user->guest)
            {
                // mark board as seen if we came using notification
                $db->prepare("
                    REPLACE INTO {$db->db_prefix}log_boards (logTime, ID_MEMBER, ID_BOARD)
                    VALUES (?, ?, ?)")->
                    execute(array(time(), $ID_MEMBER, $currentboard));
            }
            
            if ($Page2Show == 0)
                $yySetLocation = "/b$currentboard/t$threadid/$newMsgID";
            else
                $yySetLocation = "/b$currentboard/t$threadid/$Page2Show/$newMsgID";
            
            return $service->redirect($yySetLocation);
        } // if start = new
        elseif (substr($start, 0, 3) == 'msg')
        {
            $msg = (int) substr($start, 3);
            $dbst = $db->prepare("SELECT COUNT(*) FROM {$db_prefix}messages WHERE (ID_MSG < ? && ID_TOPIC=?)");
            $dbst->execute(array($msg, $threadid));
            $start = $dbst->fetchColumn();
            $dbst = null;
        }
        
        // do the previous next stuff
        // Create a previous next string if the selected theme has it
        // as a selected option
        $previousNext = $app->conf->enablePreviousNext ? '<a href="' . SITE_ROOT . "/b$currentboard/t$threadid/prev/\">{$app->conf->PreviousNext_back}</a> <a href=\"" . SITE_ROOT . "/b$currentboard/t$threadid/prev/\">{$app->conf->PreviousNext_forward}</a>" : '';
        
        // Load membrgroups.
        $service->membergroups = $app->user->memberGroups();
        
        // get all the topic info
        $dbst = $db->prepare("
            SELECT t.numReplies, t.numViews, t.locked, ms.subject, t.isSticky, ms.posterName, ms.ID_MEMBER, t.ID_POLL, t.ID_MEMBER_STARTED, tr.POSITIVE AS topicPosRating, tr.NEGATIVE AS topicNegRating
            FROM {$db_prefix}topics as t
            JOIN {$db_prefix}messages as ms ON (t.ID_FIRST_MSG = ms.ID_MSG)
            LEFT JOIN {$db_prefix}topic_ratings AS tr ON (t.ID_TOPIC = tr.TOPIC_ID)
            WHERE t.ID_BOARD = ?
                AND t.ID_TOPIC = ?
                AND ms.ID_MSG = t.ID_FIRST_MSG");
            $dbst->execute(array($currentboard, $threadid));
        
        $topicinfo = $dbst->fetch();
        $dbst = null;
        
        if (!$topicinfo) {
            return $this->findBoard($currentboard, $threadid);
        }
        
        // read topic rating
        $service->topicPosRating = !isset($topicinfo['topicPosRating']) ? 0 : $topicinfo['topicPosRating'];
        $service->topicNegRating = !isset($topicinfo['topicNegRating']) ? 0 : $topicinfo['topicNegRating'];
        
        if (!$app->user->guest)
        {
            // mark the topic as read :)
            $db->prepare("
                REPLACE INTO {$db->db_prefix}log_topics (logTime, ID_MEMBER, ID_TOPIC, notificationSent,unreadComments,otherComments,subscribedComments)
                VALUES (?, ?, ?, 0,0,0,0)")->
                execute(array(time(), $ID_MEMBER, $threadid));
            
            $referer = $SERVER->get('HTTP_REFERER');
            // mark board as seen if we came using last post link from BoardIndex
            //if (isset($boardseen))
            if ($referer == "{$service->siteurl}/") {
                // if came from main page
                $db->prepare("
                    REPLACE INTO {$db->db_prefix}log_boards (logTime, ID_MEMBER, ID_BOARD)
                    VALUES (?, ?, ?)")->
                    execute(array(time(), $ID_MEMBER, $currentboard));
            }
            
            // Add 1 to the number of views of this thread.
            $db->prepare("
                UPDATE {$db->db_prefix}topics
                SET numViews = numViews + 1
                WHERE ID_TOPIC = ?")->execute(array($threadid));;
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
        $service->showmods = $board_moderators;
        
        $service->jumptoform = $this->prepareJumptoForm($currentboard);
        
        // Build the page links list.
        $max = $mreplies + 1;
        $start = (($start > $max) ? $max : $start);
        $start = (floor($start / $app->conf->maxmessagedisplay)) * $app->conf->maxmessagedisplay;
        
        $service->start = $start;
        
        $maxmessagedisplay = ($start === 'all' ? $mreplies + 1 : $app->conf->maxmessagedisplay);
        
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
            $dbst = $db->prepare("SELECT chatWall, chatWallMsgAuthor FROM {$db->db_prefix}boards WHERE ID_BOARD = ?");
            $dbst->execute(array($currentboard));
            $row = $dbst->fetch();
            $dbst = null;
            $row['chatWall'] = $service->unicodeentities($row['chatWall']);
            
            $dbst = $db->prepare("SELECT billboard, billboardAuthor, realName, gender FROM {$db_prefix}topics AS t LEFT JOIN {$db_prefix}members AS m ON (t.billboardAuthor = m.memberName) WHERE ID_TOPIC = ?");
            $dbst->execute(array($threadid));
            $row2 = $dbst->fetch();
            $dbst = null;
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
            $dbst = $db->prepare("
                SELECT question, votingLocked, votedMemberIDs, option1, option2, option3, option4, option5, option6, option7, option8, option9, option10, option11, option12, option13, option14, option15, option16, option17, option18, option19, option20, votes1, votes2, votes3, votes4, votes5, votes6, votes7, votes8, votes9, votes10, votes11, votes12, votes13, votes14, votes15, votes16, votes17, votes18, votes19, votes20
                FROM {$db_prefix}polls
                WHERE ID_POLL = ?
                LIMIT 1");
            $dbst->execute(array($topicinfo['ID_POLL']));
            $pollinfo = $dbst->fetch();
            $dbst = null;
            $pollinfo['image'] = ($pollinfo['votingLocked'] != '0' )?'locked_poll':'poll';
            
            $pollinfo['totalvotes'] = 0;
            
            for ($i=1; $i<21; $i++) {
              $pollinfo['totalvotes'] += $pollinfo["votes$i"];
            }
            $pollinfo['divisor'] = (($pollinfo['totalvotes'] == 0) ? 1 : $pollinfo['totalvotes']);
            $pollinfo['pollimage'] = $pollinfo['votingLocked'] != '0'  ? 'locked_poll' : 'poll';
            
            if(($app->user->id == $topicinfo['ID_MEMBER_STARTED'] && $app->conf->pollEditMode == '2') || ($app->user->accessLevel() == 2 && in_array($app->conf->pollEditMode, array('2', '1'))) || $app->user->accessLevel() > 2)
                $service->editpoll_btn = true;
            
            if($app->user->guest || in_array($app->user->id, explode(',', $pollinfo['votedMemberIDs'])) || $pollinfo['votingLocked'] != '0' || $GET->viewResults == '1')
                $service->viewResults = true;
            
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
        $dbrq = $db->prepare("
            SELECT ID_MSG
            FROM {$db_prefix}messages
            WHERE ID_TOPIC=?
            ORDER BY ID_MSG " . ($app->conf->viewNewestFirst ? 'DESC' : '') . "
            LIMIT ?,?");
        $dbrq->execute(array($threadid, $start, $maxmessagedisplay));
        $messages = $dbrq->fetchAll(\PDO::FETCH_COLUMN);
        $dbrq = null; // closing statement
        
        if (count($messages))
            $dbrq = $db->query("
                SELECT m.ID_MSG, m.subject, m.posterName, m.posterEmail, m.posterTime, m.ID_MEMBER, m.icon, m.posterIP, m.body, m.smiliesEnabled, m.modifiedTime, m.modifiedName, m.attachmentFilename, m.attachmentSize, m.nowListening, m.multinick, IFNULL(mem.realName, m.posterName) AS posterDisplayName, IFNULL(lo.logTime, 0) AS isOnline, m.comments, mem.blockComments POST_COMMENTS_BLOCKED, m.closeComments, m.agent, qpolls.POLL_TITLE, cs.notify
                FROM {$db->db_prefix}messages AS m
                LEFT JOIN {$db->db_prefix}members AS mem ON (mem.ID_MEMBER=m.ID_MEMBER)
                LEFT JOIN {$db->db_prefix}log_online AS lo ON (lo.identity=mem.ID_MEMBER)
                LEFT JOIN {$db->db_prefix}quickpolls AS qpolls ON (m.ID_MSG = qpolls.ID_MSG)
                LEFT JOIN {$db->db_prefix}comment_subscriptions AS cs ON (m.ID_MSG = cs.messageID AND cs.memberID = {$ID_MEMBER})
                WHERE m.ID_MSG IN (" . implode(',', $messages) . ")
                ORDER BY ID_MSG " . ($app->conf->viewNewestFirst ? 'DESC' : ''));
        
        $messages = array();
        
        while ($message = $dbrq->fetch())
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
                
                $req = $db->query("SELECT qvotes.POLL_OPTION, qvotes.MEMBER_NAME, m.memberName, m.realName FROM {$db_prefix}quickpoll_votes AS qvotes LEFT JOIN {$db_prefix}members AS m ON (qvotes.ID_USER = m.ID_MEMBER) WHERE qvotes.ID_MSG = $msgID");
                
                while ($vote = $req->fetch())
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
                $req = null; // closing this statement
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
                $namesQuery = $db->query("SELECT k.user1, m.realName, k.action FROM {$db_prefix}karmawatch as k LEFT JOIN {$db_prefix}members as m ON (k.user1 = m.memberName) WHERE k.ID_MSG = {$message['ID_MSG']}");
                $message['karmaModifiers'] = $namesQuery->fetchAll();
                $namesQuery = null; // closing this statement
            }
            
            // Preparing for  LJ sharing buttons
            $ljMessage = (strlen($message['body']) > 512) ? substr($message['body'], 0, 512) . "..." : $message['body'];
            $message['LJSubject'] = mb_convert_encoding($message['subject'], "UTF-8", "CP1251");
            $shareMessage = $ljMessage . '<br><br>Источник: <a href="' . $service->siteurl . '/' .$msgID.'" target="_blank">forum.theprodigy.ru/'.$msgID.'</a>';
            $message['LJMessage'] = mb_convert_encoding($shareMessage, "UTF-8", "CP1251");
            
            // Blog Mod
            if($service->isBlog)
            {
                $requestBlog = $db->prepare("SELECT m.ID_MEMBER_LAST_COMMENT, m.numComments, IFNULL(lbc.logTime, 0) AS logTime,
                        bc.postedTime AS lastPosterTime, bc.posterName, IFNULL(mem.realName, bc.posterName) AS displayName
                    FROM {$db_prefix}messages AS m, {$db_prefix}blog_comments AS bc
                    LEFT JOIN {$db->db_prefix}log_blog_comments AS lbc ON (lbc.ID_MSG=m.ID_MSG AND lbc.ID_MEMBER=?)
                    LEFT JOIN {$db->db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER_LAST_COMMENT)
                    WHERE m.ID_MSG=?
                    AND m.ID_LAST_COMMENT=bc.ID_COMMENT LIMIT 1");
                $requestBlog->execute(array($ID_MEMBER, $msgID));
                $messsage['blog'] = $requestBlog->fetch();
                $requestBlog = null; // closing this statement
            }
            
            
            // Karma
            $karmaQuery = $db->query("SELECT karmaGood, karmaBad, karmaGoodExecutors, karmaBadExecutors FROM `messages` WHERE ID_MSG = {$msgID} LIMIT 1");
            $karma = $karmaQuery->fetch();
            if ($karma)
            {
                $karma['actions'] = false;
                
                $KarmaGoodIDs = explode(",", $karma['karmaGoodExecutors']);
                $KarmaBadIDs = explode(",", $karma['karmaBadExecutors']);
                
                if (($app->user->group == 'Administrator') or (($app->user->posts >= $app->conf->karmaMinPosts) && ($app->user->name != 'Guest') && ($message['ID_MEMBER'] != $app->user->id) && !(in_array($app->user->id, $KarmaGoodIDs)) && !(in_array($app->user->id, $KarmaBadIDs))))
                    $karma['actions'] = true;
                
                $karma['css'] = ($karma['karmaGood'] > 0 or $karma['karmaBad'] > 0) ? 'table-row' : 'none';
                
                $karma['smite_not_allowed'] = false;
                $smite_not_allowed = $app->conf->get('smite_not_allowed', array());
                if (in_array($app->user->name, $smite_not_allowed) || in_array($app->user->id, $smite_not_allowed))
                    $karma['smite_not_allowed'] = true;
                $message['karma'] = $karma;
            }
            $karmaQuery = null; // closing this statement
            
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

            $message['cmnt_display'] = $message['closeComments'] == 1 ? 'none' : 'inline';
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
        
        $service->linktree = array(
            array('url' => "/#{$service->curcat}", 'name' => $service->catname),
            array('url' => "/b{$service->board}/", 'name' => $service->boardname)
        );
                
        $this->render('templates/thread/thread.template.php');
        
    } // Display()
    
    private function getBoard($threadid)
    {
        $threadid = intval($threadid);
        $dbrq = $this->app->db->prepare(
            "SELECT ID_BOARD FROM {$this->app->db->prefix}topics WHERE ID_TOPIC = ?");
        $dbrq->execute(array($threadid));
        $board = $dbrq->fetchColumn();
        if (empty($board))
            $this->error($this->app->locale->txt[472]);
        
        return intval($board);
    }
    
    public function findBoard($prevboard, $threadid) {
        $board = $this->getBoard($threadid);
            
        $request_uri = $this->request->uri();
        $new_uri = str_replace("/b$prevboard/", "/b$board/", $request_uri);
        
        $this->app->errors->log("__DEBUG__: redirecting to $new_uri.");
        return $this->service->redirect($new_uri);
    } // findBoard()
    
    public function newThread($request, $response, $service, $app)
    {
        $service->action = 'newthread';
        return $this->reply($request, $response, $service, $app);
    }
    
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
        
        if ($app->user->guest)
        { // TODO test guest posting
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
        
        $threadinfo = array('locked' => 0, 'ID_MEMBER_STARTED'=>'-1');
        $mstate = 0;
        
        $db_prefix = $app->db->prefix;
        
        //check for blog board
        $blogmod_groups = explode(',', $app->conf->blogmod_groups);
        $requestBlog = $app->db->prepare("SELECT ID_BOARD FROM {$db_prefix}boards WHERE ID_BOARD=? AND isBlog=1");
        $requestBlog->execute(array($service->board));
        $isBlog = ($requestBlog->fetchColumn()) ? true : false;
        $requestBlog = $app->db->prepare("SELECT posts FROM {$db_prefix}members WHERE ID_MEMBER=?");
        $requestBlog->execute(array($app->user->id));
        $post_count = $requestBlog->fetchColumn();
        $requestBlog = null; // closing this statemtnt
        //check posting permission
        
        if ($isBlog && $app->user->accessLevel() < 2)
        {
        //check for new topic post
            if (empty($service->thread))
            {
                //if postcount not enough and membergroup isn't allowed, show error message
                if ( ($post_count < $app->conf->blogmod_minpost) && ($app->conf->blogmod_groups == '' || ($app->conf->blogmod_groups != '' && !in_array($app->user->group, $blogmod_groups))) )
                    return $app->errors->abort('', $app->locale->blogmod11);
                $requestBlog = $app->db->prepare("SELECT COUNT(*) FROM {$db_prefix}topics WHERE ID_BOARD=? AND ID_MEMBER_STARTED=?");
                $requestBlog->execute(array($service->board, $app->user->id));
                $blog_total = $requestBlog->fetchColumn();
                $requestBlog = null;
                if ($blog_total >= $app->conf->blogmod_maxblogsperuser)
                    return $this->error($app->locale->blogmod12);
            }
            //check for post reply
            else
            {
                $requestBlog = $app->db->prepare("SELECT ID_TOPIC FROM {$db_prefix}topics WHERE ID_BOARD=? AND ID_MEMBER_STARTED=?");
                $requestBlog->execute(array($service->board, $app->user->id));
                if (!$requestBlog->fetchColumn())
                    return $this->error($app->locale->blogmod13);
                $requestBlog = null;
            }
        }
        
        if (!empty($service->thread))
        {
            $dbrq = $app->db->prepare("SELECT * FROM {$db_prefix}topics WHERE ID_TOPIC=?");
            $dbrq->execute(array($service->thread));
            $threadinfo = $dbrq->fetch();
            $dbrq = null;
            $mstate = $threadinfo['locked'];
        }
        else if ($service->action != 'newthread')
            return $this->error($app->locale->txt[472] . ' Error ' . $service->action);
        
        if ($threadinfo['locked'] != 0 && $app->user->accessLevel() < 2)  // don't allow a post if it's locked
            return $app->errors->abort('', $app->locale->txt[90]);
        
        # Determine what category we are in.
        $dbrq = $app->db->prepare("
            SELECT b.ID_BOARD as bid, b.name as bname, c.ID_CAT as cid, c.memberGroups, c.name as cname, b.isAnnouncement
            FROM {$db_prefix}boards as b, {$db_prefix}categories as c
            WHERE (b.ID_BOARD = ?
                AND b.ID_CAT=c.ID_CAT)");
        $dbrq->execute(array($service->board));
        $bcinfo = $dbrq->fetch();
        $dbrq = null;
        if (!$bcinfo)
            return $this->error($app->locale->yse232);
        
        $service->cat = $bcinfo['cid'];
        $service->catname = $bcinfo['cname'];
        $service->board = $bcinfo['bid'];
        $service->boardname = $bcinfo['bname'];
        
        if ($bcinfo['isAnnouncement'] && empty($service->thread) && $app->user->accessLevel() < 2)
            return $this->error($app->locale->announcement1);
        
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
        
        if (!empty($service->thread) && !empty($service->quotemsg))
        {
            // $app->session->check('get'); // FIXME why do we check session here?
            
            $dbrq = $app->db->prepare("
                SELECT m.subject, m.posterName, m.posterEmail, m.posterTime, m.icon, m.posterIP, m.body, m.smiliesEnabled, m.ID_MEMBER, m.comments
                FROM {$db_prefix}messages as m, {$db_prefix}topics as t, {$db_prefix}boards as b, {$db_prefix}categories as c
                WHERE (m.ID_MSG = ?
                    AND m.ID_TOPIC = t.ID_TOPIC
                    AND t.ID_BOARD = b.ID_BOARD
                    AND b.ID_CAT = c.ID_CAT
                    AND (FIND_IN_SET(?, c.memberGroups) != 0 || c.memberGroups = '' || ? LIKE 'Administrator' || ? LIKE 'Global Moderator')
                )");
            $dbrq->execute(array($service->quotemsg, $app->user->group, $app->user->group, $app->user->group));

            list($service->msubject, $service->mname, $service->memail, $service->mdate, $service->micon, $service->mip, $service->mmessage, $service->mns, $service->mi, $service->mcomments) = $dbrq->fetch(\PDO::FETCH_NUM);
            $dbrq = null;
            
            if (empty($service->mdate))
                return $this->error('No such post.');
            
            if ($service->mi != '-1')
            {
                $dbrq = $app->db->prepare("
                    SELECT realName
                    FROM {$db_prefix}members
                    WHERE ID_MEMBER=?
                    LIMIT 1");
                $dbrq->execute(array($service->mi));
                $_realName = $dbrq->fetchColumn();
                if ($_realName)
                    $service->mname = $_realName;
                $dbrq = null; // closing this statement
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
                return $this->ajax_response(str_replace(array('&quot;', '&lt;', '&gt;'), array('"', '<', '>'), $service->form_message), 'txt');
        }
        else if (!empty($service->thread) && empty($service->quotemsg))
        {
            $dbrq = $app->db->prepare("SELECT subject, posterName, posterEmail, posterTime, icon, posterIP, body, smiliesEnabled, ID_MEMBER
                FROM {$db_prefix}messages
                WHERE ID_TOPIC=?
                ORDER BY ID_MSG
                LIMIT 1");
            $dbrq->execute(array($service->thread));
            
            list($service->msubject, $service->mname, $service->memail, $service->mdate, $service->micon, $service->mip, $service->mmessage, $service->mns, $service->mi) = $dbrq->fetch(\PDO::FETCH_NUM);
            $dbrq = null; // closing this statement

            $service->form_subject = $app->subs->CensorTxt($service->msubject);
            
            if (!stristr(substr($service->msubject, 0, 3),'re:'))
                $service->form_subject = '' . $service->form_subject;
        }
        
        if ($service->form_subject)
            $service->sub = $service->form_subject;
        
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
        
        if (!empty($service->thread))
            $service->thread_summary = $this->thread_summary($service->thread);
        
        $service->threadinfo = $threadinfo;
        
        $service->notify = $app->conf->enable_notification && !$app->user->guest;
        $service->lock = $app->user->accessLevel() > 2 && !$threadinfo['locked'];
        $service->locked = $threadinfo['locked'];
        
        $service->linktree = array(
            array('url' => "/#{$service->cat}", 'name' => $service->catname),
            array('url' => "/b{$service->board}/", 'name' => $service->boardname),
            array('name' => $service->title . (isset($service->sub) ? " ( {$service->sub} )" : ''))
        );
        
        $this->render('templates/thread/reply.template.php');
    } // reply()
    
    public function thread_summary($thread) {
        // how many messages to show
        $limitString = ($this->app->conf->topicSummaryPosts < 0) ? '' : (' LIMIT ' . (!is_numeric($this->app->conf->topicSummaryPosts) ? '0' : $this->app->conf->topicSummaryPosts));
        
        $db_prefix = $this->app->db->prefix;
        $usergroup = $this->app->user->group;
        $dbrq = $this->app->db->prepare("
            SELECT m.posterName, m.posterTime, m.body, m.smiliesEnabled
            FROM {$db_prefix}messages AS m, {$db_prefix}topics as t, {$db_prefix}boards as b, {$db_prefix}categories as c
            WHERE m.ID_TOPIC=?
                AND t.ID_TOPIC=m.ID_TOPIC
                AND b.ID_BOARD=t.ID_BOARD
                AND c.ID_CAT=b.ID_CAT
                AND (FIND_IN_SET(?, c.memberGroups) != 0 OR c.memberGroups = '' OR ? LIKE 'Administrator' OR ? LIKE 'Global Moderator')
            ORDER BY ID_MSG DESC
            $limitString");
        $dbrq->execute(array($thread, $usergroup, $usergroup, $usergroup));
        $messages = array();
        while($row = $dbrq->fetch())
        {
            if ($this->app->user->inIgnore($row['posterName']))
                continue;
            
            $messages[] = array(
                'userinfo' => $this->app->user->loadDisplay($row['posterName']),
                'time' => $this->app->subs->timeformat($row['posterTime']),
                'body' => $this->app->subs->CensorTxt($row['body']),
                'smilies' => $row['smiliesEnabled']
            );
        }
        $dbrq = null; // closing this statement
        
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

        $app->board->load($PARAMS->board);
        
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
        
        if ($POST->linkcalendar != null)
        { 
            $app->calendar->ValidatePost($request);
        }
        
        if ($app->user->name == 'Guest' && $app->conf->enable_guestposting == 0)
            return $this->error($txt[165]);
        
        $db = $app->db;
        $db_prefix = $app->db->prefix;
        
        if (!empty($service->thread) && $app->user->accessLevel() < 2)
        {
            $dbrq = $app->db->prepare("
                SELECT locked
                FROM {$db_prefix}topics
                WHERE ID_TOPIC=?");
            $dbrq->execute(array($service->thread));
            $tmplocked = $dbrq->fetchColumn();
            $dbrq = null;
            if ($tmplocked != 0)
                return $this->error($txt[90]); // don't allow a post if it's locked
        }
        
        if (empty($service->thread))
        {
            $dbrq = $app->db->prepare("
                SELECT b.ID_BOARD
                FROM {$db_prefix}boards AS b, {$db_prefix}categories AS c
                WHERE b.ID_BOARD=?
                AND c.ID_CAT=b.ID_CAT
                AND (FIND_IN_SET(?, c.memberGroups) != 0 OR c.memberGroups = '' OR ? LIKE 'Administrator' OR ? LIKE 'Global Moderator')");
            $dbrq->execute(array($service->board, $app->user->group, $app->user->group, $app->user->group));
        }
        else
        {
            $dbrq = $app->db->prepare("
                SELECT t.ID_TOPIC
                FROM {$db_prefix}topics AS t, {$db_prefix}boards AS b, {$db_prefix}categories AS c
                WHERE t.ID_TOPIC=?
                AND b.ID_BOARD=?
                AND b.ID_BOARD=t.ID_BOARD
                AND c.ID_CAT=b.ID_CAT
                AND (FIND_IN_SET(?, c.memberGroups) != 0 OR c.memberGroups = '' OR ? LIKE 'Administrator' OR ? LIKE 'Global Moderator')");
            $dbrq->execute(array($service->thread, $service->board, $app->user->group, $app->user->group, $app->user->group));
        }
        if (!$dbrq->fetchColumn())
            return $this->error($txt[1]);
        
        $dbrq = null; // closing this statement
        
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
        
        if ($app->user->guest)
        {
            # If user is Guest, then make sure the chosen name
            # is not reserved or used by a member.
            
            $dbrq = $app->db->prepare("
                ELECT ID_MEMBER
                FROM {$db_prefix}members
                WHERE (memberName=? || realName=?)");
            $dbrq->execute(array($input_name));
            
            if ($dbrq->fetchColumn())
                return $this->error(473);
            $dbrq = null; // closing this statement
            
            //now make sure that guestname is not containing banned nicknames
            if ($app->security->containsBannedNickPart($input_name))
                return $this->error("Вам не разрешено отвечать на форуме! Обратитесь к администраторам за разъяснением!");
            
            // now make sure they arn't trying to use a reserved name
            $dbrq = $app->db->query("
                SELECT *
                FROM {$db_prefix}reserved_names
                ORDER BY setting");
            
            $matchword = $matchcase = $matchuser = $matchname = '';

            for ($i = 0; $i < 4; $i++)
            {
                $tmp = $dbrq->fetch(\PDO::FETCH_NUM);
                ${$tmp[0]}=$tmp[1];
            }
            
            $namecheck = $matchcase ? $name : strtolower ($name);
            
            while ($tmp = $dbrq->fetch(\PDO::FETCH_NUM))
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
            $dbrq = null;
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
        
        if (isset($app->session->userInfo) and isset($app->session->userInfo['name']) and ($app->session->userInfo['name'] != $rname or $app->session->userInfo['username'] != $app->user->name))
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
        
        if (!empty($attachment))
        {
            $attachment['name'] = preg_replace(
                array("/\s/", "/[дегвба]/", "/[ДЕБВАГ]/", "/[цтуфхшр]/", "/[ЦШФТФХ]/", "/[йикл]/", "/[ЙКЛИ]/", "/ [ыьщъ]/", "/[ЬЫЪЩ]/", "/[помн]/", "/[НОП]/", "/[з]/", "/[с]/",	"/[С]/", "/[^\w_.-]/"),
                array('_', 'a', 'A', 'o', 'O', 'e', 'E', 'u', 'U', 'i', 'I', 'c', 'n', 'N', ''),
                $attachment['name']);
            
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
                
                $dirSize = '0';
                $dir = opendir($app->conf->attachmentUploadDir);
                while ($file = readdir($dir))
                    $dirSize = $dirSize + filesize($app->conf->attachmentUploadDir . '/' . $file);
                
                if ($attachment['size'] + $dirSize > $app->conf->attachmentDirSizeLimit * 1024)
                    return $this->error('yse126');
                
                $parts = ($attachment !== null) ? preg_split("~(\\|/)~", $attachment['name']) : array();
                
                $prefix = base_convert(time(), 10, 36);
                
                $attachment['name'] = $prefix . '_' . array_pop($parts) ;
                
                if (file_exists($app->conf->attachmentUploadDir . "/" . $attachment['name']))
                    return $this->error('yse125');
                
                if (!move_uploaded_file($attachment['tmp_name'], $app->conf->attachmentUploadDir . '/' . $attachment['name']))
                    return $this->error("yse124");
                
                chmod ("{$app->conf->attachmentUploadDir}/{$attachment['name']}", 0644) || $chmod_failed = 1;
            }
            else
            {
                $attachment['name'] = null;
                $attachment['size'] = 0;
            }
        } // if attachment not null
        else
            $attachment = array('name' => null, 'size' => 0);
        
        // If no thread specified, this is a new thread.
        // Find a valid random ID for it.
        $newtopic = empty($service->thread);
        
        $time = time();
        $se = ($input_ns ? 0 : 1);
        
        $agent_fp = array();
        $agent_fp[] = $SERVER->get('HTTP_USER_AGENT');
        $agent_fp[] = ($POST->get('tfp') === null) ? 'none' : $POST->get('tfp');
        $agent_fp[] = ($POST->get('bfp') === null) ? 'none' : $POST->get('bfp');
        $agent_fp = implode(' #|# ', $agent_fp);
        
        $nowListening = $POST->get('nowListening', '');
        $icon = $POST->get('icon');
        $quickPollTitle = $POST->get('quickPoll');
                
        // Guest Rules Confirmation mod, by dig7er
        $app->session->store('guestRulesConfirmed', true);
        
        if ($newtopic)        // This is a new topic. Save it.
        {
            if ($app->board->isAnnouncement() && $app->user->accessLevel() < 2)
                return $this->error('announcement1');
            
            if ($app->user->guest)
                $_closeComments = 0;
            else
                $_closeComments = (int) $app->user->closeCommentsByDefault;
            
            $q_params = array($app->user->id, $input_subject, $input_name, $input_email, $time, $REMOTE_ADDR, $se, $input_message, $icon, $attachment['size'], $attachment['name'], $nowListening, $multinick, $_closeComments, $agent_fp);
            $placeholders = $db->build_placeholders($q_params);
            
            $db->prepare("
                INSERT INTO {$db_prefix}messages (ID_MEMBER, subject, posterName, posterEmail, posterTime, posterIP, smiliesEnabled, body, icon, attachmentSize, attachmentFilename, nowListening, multinick, closeComments, agent)
                VALUES ($placeholders)")->execute($q_params);
            $ID_MSG = $db->lastInsertId();
            $dbrq = null;
            
            if ($ID_MSG)
            {
                $db->prepare("
                    INSERT INTO {$db_prefix}topics (ID_BOARD, ID_MEMBER_STARTED, ID_MEMBER_UPDATED, ID_FIRST_MSG, ID_LAST_MSG, locked, numViews)
                    VALUES (?, ?, ?, ?, ?, ?, ?)")->
                    execute(array($service->board, $app->user->id, $app->user->id, $ID_MSG, $ID_MSG, $locked, 0));
                
                $_threadid = $db->lastInsertId();
                if ($_threadid)
                {
                    $threadid = $_threadid;
                    $db->query("
                        UPDATE {$db_prefix}messages
                        SET ID_TOPIC = $threadid
                        WHERE (ID_MSG = $ID_MSG)");
                    
                    $db->query("
                        UPDATE {$db_prefix}boards
                        SET numPosts = numPosts + 1, numTopics = numTopics + 1
                        WHERE (ID_BOARD = {$service->board})");
                    
                    $mreplies = 0;
                    
                    if ($app->conf->trackStats)
                    {
                        $date = getdate(time() + $app->conf->timeoffset * 3600);
                        $statsquery = $db->query("
                            UPDATE {$db_prefix}log_activity
                            SET topics = topics + 1, posts = posts + 1
                            WHERE month = {$date['mon']}
                            AND day = {$date['mday']}
                            AND year = {$date['year']}");
                        
                        if ($statsquery->rowCount() == 0)
                            $db->query("
                                INSERT INTO {$db_prefix}log_activity
                                (month, day, year, topics, posts)
                                VALUES ($date[mon], $date[mday], $date[year], 1, 1)");
                        
                        $statsquery = null;
                    }
                    
                    if ($POST->linkcalendar !== null)
                        $app->calendar->InsertEvent($service->board, $threadid, $POST->evtitle, $app->user->id, $POST->month, $POST->day, $POST->year, $POST->span);
                    
                    if ($app->board->isAnnouncement())
                    {
                        $reqAnn = $db->query("
                            SELECT b.notifyAnnouncements
                            FROM {$db_prefix}boards as b, {$db_prefix}categories as c
                            WHERE (b.ID_BOARD = {$service->board}
                            AND b.ID_CAT = c.ID_CAT)");
                        
                        $rowAnn = $reqAnn->fetch();
                        $reqAnn = null; // closing this statement
                        
                        if ($rowAnn['notifyAnnouncements'])
                            $this->NotifyUsersNewAnnouncement();
                    }
                    
                    $app->subs->updateStats('topic');
                    $app->subs->updateStats('message');
                    $app->subs->UpdateLastMessage($service->board);
                    $service->thread = $threadid;
                }
                
                // quick poll mod by dig7er, 14.04.2010
                if (!empty($quickPollTitle))
                {
                    $dbst = $db->prepare("REPLACE INTO {$db_prefix}quickpolls
                        (ID_MSG, POLL_TITLE)
                        VALUES (?, ?)")->
                        execute(array($ID_MSG, $quickPollTitle));
                }
            } // $ID_MSG > 0
        } // if $newtopic
        else
        {   // This is an old thread. Save it.
            // QuickReplyExtended
            if($app->conf->QuickReply && $app->conf->QuickReplyExtended && $app->user->accessLevel() > 1)
            {
                $csubject = $POST->get('csubject');
                if(strlen($csubject) >= 3)
                {
                    $db->prepare("UPDATE {$db_prefix}messages SET subject=? WHERE ID_TOPIC=?")->
                        execute(array($csubject, $service->thread));
                }
                $modaction = $POST->get('modaction');
                if($modaction != 1)
                {
                    if($modaction == 2)
                    {
                        $dbrq = $db->prepare("
                            SELECT locked
                            FROM {$db_prefix}topics
                            WHERE ID_TOPIC=?");
                        $dbrq->execute(array($service->thread));
                        $row = $dbrq->fetchColumn();
                        $dbrq = null;
                        
                        $quicklock = ($row != 0) ? 0 : 1;
                        $db->prepare("UPDATE {$db_prefix}topics SET locked=? WHERE ID_TOPIC=?")->execute(array($quicklock, $service->thread));
                    }
                    elseif($modaction == 3)
                    {
                        $dbrq = $db->prepare("
                            SELECT isSticky
                            FROM {$db_prefix}topics
                            WHERE ID_TOPIC=?");
                        $dbrq->execute(array($service->thread));
                        $row = $dbrq->fetchColumn();
                        $dbrq = null;
                        
                        $quicksticky = ($row != 0) ? 0 : 1;
                        $db->prepare("UPDATE {$db_prefix}topics SET isSticky=? WHERE ID_TOPIC=?")->execute(array($quicksticky, $service->thread));
                    }
                    elseif($modaction == 4)
                    {
                        $dbrq = $db->prepare("
                            SELECT locked,isSticky
                            FROM {$db_prefix}topics
                            WHERE ID_TOPIC=?")->execute(array($service->thread));
                        $row = $dbrq->fetch(\PDO::FETCH_NUM);
                        $dbrq = null;
                        
                        $quicklock = ($row[0] != 0) ? 0 : 1;
                        $quicksticky = ($row[1] != 0) ? 0 : 1;
                        
                        $db->prepare("
                            UPDATE {$db_prefix}topics SET locked=?, isSticky=? WHERE ID_TOPIC=?")->
                            execute(array($quicklock, $quicksticky, $service->thread));
                    }
                }
                
                $movethread = $POST->get('movethread'); // new board where thread is moving to
                
                if (!empty($movethread) && substr($movethread, 0, 1) != '#' && $movethread != $service->board)
                {
                    $dbrq = $db->prepare("
                        SELECT numReplies, ID_BOARD FROM {$db_prefix}topics WHERE ID_TOPIC=?");
                    $dbrq->execute(array($service->thread));
                    $row = $dbrq->fetch(\PDO::FETCH_NUM);
                    $dbrq = null;
                    $numReplies = $row[0]+1;
                    $boardid = $row[1];
                    $db->query("
                        UPDATE {$db_prefix}boards SET numPosts=numPosts-'{$numReplies}',numTopics=numTopics-1 WHERE ID_BOARD= $boardid");
                    
                    $db->prepare("
                        UPDATE {$db_prefix}boards SET numPosts=numPosts+?, numTopics=numTopics+1 WHERE ID_BOARD=?")->
                        execute(array($numReplies, $movethread));
                    
                    $db->prepare("
                        UPDATE {$db_prefix}topics SET ID_BOARD=? WHERE ID_TOPIC=?")->
                        execute(array($movethread, $service->thread));
                    
                    $app->subs->updateStats('topic');
                    $app->subs->updateStats('message');
                    $app->subs->updateLastMessage($service->board);
                    $app->subs->updateLastMessage($movethread);
                    
                    $db->prepare("
                         UPDATE {$db_prefix}calendar SET id_board=? WHERE id_topic=?")->
                         execute(array($movethread, $service->thread));
                    
                    $service->board = $movethread;
                }
            }
            
            // QuickReplyExtended
            //--- Unite two posts if they're last in the topic and made by the same user (by Dig7er)
            $um = false;
            // don't unite messages if we upload an attachment
            if ($attachment['name'] == null)
            {
                $query = $db->prepare("SELECT * FROM messages WHERE ID_TOPIC = ? ORDER BY ID_MSG DESC LIMIT 1");
                $query->execute(array($service->thread));
                $lastPost = $query->fetch();
                $query = null;
                
                $lineBreak = "\n\n";
                if ($lastPost['ID_MEMBER'] == $app->user->id && $lastPost['posterIP'] == $REMOTE_ADDR && (strlen($lastPost['body']) + strlen($input_message))<65356 && ((time() - $lastPost['posterTime']) < 600))
                {
                    $db->prepare("UPDATE messages SET body = CONCAT(body, ?, ?) WHERE ID_MSG = ?")->
                        execute(array($lineBreak, $input_message, $lastPost['ID_MSG']));
                    $ID_MSG = $lastPost['ID_MSG'];
                    $um = true;
                }
            }
            
            if (!$um)
            {
                // if not uniting messages
                $app->user->closeCommentsByDefault = empty($app->user->closeCommentsByDefault) ? 0 : $app->user->closeCommentsByDefault;
                $q_params = array($service->thread, $app->user->id, $input_subject, $input_name, $input_email, $time, $REMOTE_ADDR, $se, $input_message, $icon, $attachment['size'], $attachment['name'], $nowListening, $multinick, $app->user->closeCommentsByDefault, $agent_fp);
                $placeholders = $db->build_placeholders($q_params);
                
                $dbrq = $db->prepare("
                    INSERT INTO {$db_prefix}messages (ID_TOPIC, ID_MEMBER, subject, posterName, posterEmail, posterTime, posterIP, smiliesEnabled, body, icon, attachmentSize, attachmentFilename, nowListening, multinick, closeComments, agent)
                    VALUES ($placeholders)")->execute($q_params);
                $ID_MSG = $db->lastInsertId();
            }
            
            if ($ID_MSG > 0)
            {
                if (!$um) // united message (dig7er)
                {    $db->prepare("
                        UPDATE {$db_prefix}topics
                        SET ID_MEMBER_UPDATED = ?, ID_LAST_MSG = $ID_MSG, numReplies = numReplies + 1 $isLocked
                        WHERE (ID_TOPIC = ?)")->
                        execute(array($app->user->id, $service->thread));
                
                    $db->prepare("
                        UPDATE {$db_prefix}boards
                        SET numPosts = numPosts + 1
                        WHERE (ID_BOARD = ?)")->
                        execute(array($service->board));
                
                    $mreplies++;
                
                
                    if ($app->conf->trackStats == 1)
                    {
                        $date = getdate(time() + $app->conf->timeoffset * 3600);
                        $statsquery = $db->query("
                            UPDATE {$db_prefix}log_activity
                            SET posts = posts + 1
                            WHERE month = {$date['mon']}
                            AND day = {$date['mday']}
                            AND year = {$date['year']}");
                        
                        if (!$statsquery->rowCount())
                            $db->query("
                                INSERT INTO {$db_prefix}log_activity
                                (month, day, year, posts)
                                VALUES ({$date['mon']}, {$date['mday']}, {$date['year']}, 1)");
                    }
                } // !$um
                
                $app->subs->updateStats('message');
                $app->subs->UpdateLastMessage($service->board);
                
                // quick poll mod by dig7er, 14.04.2010
                if (!empty($quickPollTitle))
                    $dbrq = $db->prepare("
                        REPLACE INTO {$db_prefix}quickpolls
                        (ID_MSG, POLL_TITLE)
                        VALUES (?, ?)")->
                        execute(array($ID_MSG, $quickPollTitle));
            } // if $ID_MSG > 0
        } // if not $newtopic
        
        // BEGIN Poll mode
        $isPoll = false;
        if ($app->subs->isset($POST->poll_question, true))
        {
            $poll_vals = array('question' => trim($POST->poll_question));
            // prepare poll data
            for ($i = 1; $i < 21; $i++)
            {
                $option_name = "poll_option$i";
                if ($app->subs->isset($POST->{$option_name}))
                {
                    $poll_vals["option$i"] = trim($POST->{$option_name});
                    $isPoll = true;
                }
            }
            
            if ($isPoll)
            {
                $keys = implode(', ', array_keys($poll_vals));
                $poll_placeholders = $app->db->build_placeholders($poll_vals, true, false);
                
                $app->db->prepare("INSERT INTO {$db_prefix}polls ($keys) VALUES ($poll_placeholders)")->
                    execute($poll_vals);
                $ID_POLL = $app->db->lastInsertId();
                
                // attach the poll to thread 
                if ($ID_POLL)
                    $app->db->query("UPDATE topics SET ID_POLL = $ID_POLL WHERE ID_TOPIC = {$service->thread}");
            }
        }
        
        // END poll mode
        
        if (!$app->user->guest)
	{
            $dbrq = $db->prepare("
                SELECT count FROM {$db_prefix}boards
                WHERE ID_BOARD = ?");
            $dbrq->execute(array($service->board));
            
            $pcounter = $dbrq->fetchColumn();
            $dbrq = null;
            
            if ($pcounter != 1 and empty($um)) // united message (dig7er)
            {
                ++$app->user->posts;
                $db->prepare("
                    UPDATE {$db_prefix}members
                    SET posts = posts + 1
                    WHERE ID_MEMBER = ?
                ")->execute(array($app->user->id));
            }
            
            # Mark thread as read for the member.
            $dbrq = $db->prepare("
                REPLACE INTO {$db_prefix}log_topics
                (logTime, ID_MEMBER, ID_TOPIC)
                VALUES (?, ?, ?)")->
                execute(array(time(), $app->user->id, $service->thread));
        }
        
        # Notify any members who have notification turned on for this thread.
        $app->im->NotifyUsers($service->thread, $input_subject);
        
        $notify = $POST->get('notify');
        // turn notification on
        if (!empty($notify))
        {
            $app->im->Notify2($service->thread);
        }
        
        # Let's figure out what page number to show
        $start = (floor($mreplies / $app->conf->maxmessagedisplay)) * $app->conf->maxmessagedisplay;
        
        //  Remove this comment and comment out the other SetLocation so that you are returned
        //  to the same thread after posting.
        if ($app->conf->returnToPost == '1')
        {
            if ($newtopic)
                $yySetLocation = "/b{$service->board}/t{$service->thread}/";
            else
                $yySetLocation = "/b{$service->board}/t{$service->thread}/new/";
        }
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
    
    public function modify($request, $response, $service, $app)
    {
        if ($request->method() == 'GET')
        {
            //$app->session->check('get');
            
            // Show post modify form
            
            $PARAMS = $request->paramsNamed();
            $GET = $request->paramsGet();
            //$threadid = intval($PARAMS->thread);
            $msg = intval($PARAMS->msg);
            //$board = $this->getBoard($threadid);
            
            $db_prefix = $app->db->prefix;
            
            $dbrq = $app->db->query("SELECT m.*, t.locked, qpolls.POLL_TITLE FROM {$db_prefix}messages AS m LEFT JOIN {$db_prefix}quickpolls AS qpolls ON (m.ID_MSG = qpolls.ID_MSG), {$db_prefix}topics AS t WHERE (m.ID_MSG=$msg AND m.ID_TOPIC=t.ID_TOPIC) LIMIT 1;");
            $row = $dbrq->fetch();
            $dbrq = null;
            
            $service->quickPollTitle = $row['POLL_TITLE'];
            $threadid = $row['ID_TOPIC'];
            
            if ($row['locked'] == 1 && $app->user->accessLevel < 2)
                return $app->errors->abort('', $app->locale->txt[90], 403);
            
            $service->title = $app->locale->txt[66];
            
            if ($row['ID_MEMBER'] != $app->user->id && $app->user->accessLevel() < 2)
                return $app->errors-abort('', $app->locale->txt[67], 403);
            
            $service->lastmodification = (isset($row['modifiedTime']) ? $app->subs->timeformat($row['modifiedTime']) : '-');
            $service->nosmiley = ($row['smiliesEnabled'] ? '' : ' checked="checked"');
            
            $row['body'] = preg_replace("|<br( /)?[>]|", "\n", $row['body']);
            
            if ($app->conf->notify_users && $app->user->id != $row['ID_MEMBER'])
                $service->modify = true;
            
            if ($app->conf->attachmentEnable == 0)
            {
                // if attachments are disabled, don't show anything
                $service->attachment_fields = 0;
            }
            elseif ($app->conf->attachmentEnable == 1)
            {
                if ($row['attachmentSize'] > 0)
                {
                    // if there is an attachment show only delete
                    $service->attachment_fields = 2;
                }
                else
                    $service->attachment_fields = 1;
            }
            elseif ($app->conf->attachmentEnable == 2)
            {
                if ($row['attachmentSize'] > 0)
                {
                    // if there is an attachment show only delete
                    $service->attachment_fields = 2;
                }
            }
            
            if ($app->conf->attachmentMemberGroups != "")
                if (!(in_array($app->user->group, explode(',', trim($app->conf->attachmentMemberGroups))) || $app->user->accessLevel > 2))
                    $service->attachment_fields = 0;
            
            $dbst = $app->db->prepare("SELECT b.ID_BOARD AS bid, b.name AS bname, c.ID_CAT AS cid, c.name AS cname, t.ID_MEMBER_STARTED FROM {$db_prefix}boards AS b, {$db_prefix}categories AS c, {$db_prefix}topics AS t WHERE (b.ID_BOARD=t.ID_BOARD AND b.ID_CAT=c.ID_CAT AND t.ID_TOPIC = ?)");
            $dbst->execute(array($threadid));
            $bcinfo = $dbst->fetch();
            $dbst = null;
            
            $board = intval($bcinfo['bid']);
            $service->boardname = $bcinfo['bname'];
            $curcat = $bcinfo['cid'];
            $service->catname = $bcinfo['cname'];
            
            // preparing data for template {
            $service->sub = $row['subject'];
            $service->form_subject = $row['subject'];
            $service->form_message = $row['body'];
            $service->nowListening = $row['nowListening'];
            $service->attachmentFilename = $row['attachmentFilename'];
            $service->icon = $row['icon'];
            $service->threadinfo = array('locked' => $row['locked'], 'ID_MEMBER_STARTED' => $bcinfo['ID_MEMBER_STARTED']);
            $service->mn = true;
            $service->msg = $msg;
            $service->ses_id = $app->session->id;
            $service->locked = $row['locked'];
            
            if ($app->user->accessLevel($board) > 1 )
                $service->editfeed = true;
            
            $service->linktree = array(
                array('url' => "/#{$curcat}", 'name' => $service->catname),
                array('url' => "/b{$board}/", 'name' => $service->boardname),
                array('name' => $service->title . (isset($service->sub) ? " ( {$service->sub} )" : ''))
            );
            
            return $this->render('templates/thread/reply.template.php');
        }
        elseif ($request->method() == 'POST')
        {
            if ($app->user->guest) return $app->errors->abort('', $app->locale->txt[223], 403);
            
            $app->session->check('post');
            
            $POST = $request->paramsPost();
            $PARAMS = $request->paramsNamed();
            $input = array();
            
            $input['mdfrzn'] = $POST->get('mdfrzn', '');
            if ($service->ajax) $input['mdfrzn'] = mb_convert_encoding($input['mdfrzn'], $app->conf->charset, 'utf-8');
            
            $input['msg'] = intval($PARAMS->msg);
            if ($input['msg'] < 1) return $app->errors->abort('', 'Wrong message ID');
            
            $ns = $POST->get('ns', '');
            $input['smilies'] = ($ns != '') ? 0 : 1;
            $input['mn'] = $POST->mn;
            $input['subject'] = trim($POST->naztem);
            $input['icon'] = $POST->get('icon', 'xx');
            $service->validate($input['subject'], $app->locale->txt[77])->notEmpty();
            $input['message'] = trim($POST->message);
            $service->validate($input['message'], $app->locale->txt[499])->isLen(1, $app->conf->MaxMessLen);
            $input['message'] = $app->subs->preparsecode($input['message']);
            $input['nowListening'] = $POST->nowListening;
            $input['quickPoll'] = trim($POST->quickPoll);
            //$input['lock'] = $POST->get('lock', 'off');
            $input['notify'] = $POST->get('notify', 'off');
            $input['delAttach'] = $POST->get('delAttach', 'off');
            $input['attachOld'] = $POST->attachOld;
            $attachment = $request->files()->get('attachment');
            
            $db_prefix = $app->db->prefix;
            
            $dbst = $app->db->prepare("
                SELECT m.ID_MSG, m.ID_MEMBER, m.attachmentSize, m.attachmentFilename, m.subject, m.body, t.locked, t.ID_FIRST_MSG, t.ID_LAST_MSG, t.ID_TOPIC, t.ID_POLL, t.numReplies, b.ID_BOARD, b.count, p.POLL_TITLE as quickPollOld
                FROM {$db_prefix}messages AS m, {$db_prefix}topics AS t, {$db_prefix}boards AS b, {$db_prefix}quickpolls as p
                WHERE m.ID_MSG = ? 
                AND t.ID_TOPIC=m.ID_TOPIC
                AND b.ID_BOARD=t.ID_BOARD
                LIMIT 1");
            $dbst->execute(array($input['msg']));
            $row = $dbst->fetch();
            $dbst = null; // closing this statement
            
            // whether user edits self message
            if ($row['ID_MEMBER'] == $app->user->id) $self_edit = true;
            else $self_edit = false;
            
            // Make sure the user is allowed to edit this post.
            // Removed by Hierarchical Categories MOD - Begin
            if (!$self_edit && $app->user->accessLevel() < 2)
                return $app->errors->abort('', $app->locale->txt[67], 403);
            
            if ($row['locked'] == 1 && $app->user->accessLevel() < 2)
                return $app->errors->abort('', $app->locale->txt[90], 403);
            
            if (!$self_edit && empty($input['mdfrzn']))
                // Only author allowed to remove or modify without a reason
                return $app->errors->abort('', $app->locale->mdfrznerr, 403);
            
            $input['threadid'] = $row['ID_TOPIC'];
            
            if ($input['delAttach'] == 'on')
            {
                $service->validate($input['delAttach'], 'Empty filename to remove.')->notEmpty();
                unlink($app->conf->attachmentUploadDir . "/" . $input['attachOld']);
                
                $app->db->prepare("UPDATE {$db_prefix}messages SET attachmentSize='0', attachmentFilename=NULL WHERE ID_MSG=?")->
                    execute(array($input['msg']));
            }
            elseif ($attachment !== null && $attachment['name'] != '' && $app->conf->attachmentEnable > 0 && ($app->conf->attachmentMemberGroups == "" || in_array($app->user->group, explode(',', trim($app->conf->attachmentMemberGroups))) || $app->user->accessLevel() > 2))
            {
                $attachment['name'] = preg_replace(
                    array("/\s/", "/[дегвба]/", "/[ДЕБВАГ]/", "/[цтуфхшр]/", "/[ЦШФТФХ]/", "/[йикл]/", "/[ЙКЛИ]/", "/[ыьщъ]/", "/[ЬЫЪЩ]/", "/[помн]/", "/[НОП]/", "/[з]/", "/[с]/",	"/[С]/", "/[^\w_.-]/"),
                    array('_', 'a', 'A', 'o', 'O', 'e', 'E', 'u', 'U', 'i', 'I', 'c', 'n', 'N', ''), 
                    $attachment['name']
                );
                
                if ($attachment['size'] > $app->conf->attachmentSizeLimit * 1024)
                    return $app->errors->abort('', "{$app->locale->yse122} {$app->conf->attachmentSizeLimit}.");
                if ($app->conf->attachmentCheckExtensions == "1")
                    if (!in_array(strtolower(substr(strrchr($attachment['name'], '.'), 1)), explode(',', strtolower($app->conf->attachmentExtensions))))
                        return $app->errors->abort('', "{$attachment['name']}.<br />{$app->locale->yse123} {$app->conf->attachmentExtensions}.");
                
                // make sure they aren't trying to upload a nasty file
                $disabledFiles = array('CON','COM1','COM2','COM3','COM4','PRN','AUX','LPT1');
                if (in_array(strtoupper(substr(strrchr($attachment['name'], '.'), 1)), $disabledFiles))
                    return $app->errors->abort('', "{$attachment['name']}.<br />{$app->locale->yse130b}.");
                
                if (!file_exists($app->conf->attachmentUploadDir . "/" . $attachment['name']))
                {
                    $dirSize = "0";
                    $dir = opendir($app->conf->attachmentUploadDir);
                    while ($file = readdir($dir))
                        $dirSize = $dirSize + filesize($app->conf->attachmentUploadDir . "/" . $file);
                        if ($attachment['size'] + $dirSize > $app->conf->attachmentDirSizeLimit * 1024)
                            return $app->errors->abort('', $app->locale->yse126);
                    
                    $parts = (!empty($attachment) ? preg_split("~(\\|/)~", $attachment['name']) : array());
                    $destName = array_pop($parts);
                    
                    if (!move_uploaded_file($attachment['tmp_name'], $app->conf->attachmentUploadDir . "/" . $destName))
                        return $app->errors->abort('', $app->locale->yse124);
                    chmod("{$app->conf->attachmentUploadDir}/$destName", 0644) || $chmod_failed = 1;
                    
                    $app->db->prepare("UPDATE {$db_prefix}messages SET attachmentSize=?, attachmentFilename=? WHERE ID_MSG=?")->
                        execute(array($attachment['size'], $attachment['name'], $input['msg']));
                }
            }
            
            $modTime = time();
            
            $dbst = $app->db->prepare("UPDATE {$db_prefix}messages SET subject = ?, icon = ?, body = ?, modifiedTime = ?, modifiedName = ?, smiliesEnabled = ?, nowListening = ? WHERE ID_MSG = ?;");
            $qresult = $dbst->execute(array($input['subject'], $input['icon'], $input['message'], $modTime, $app->user->realname, $input['smilies'], $input['nowListening'], $input['msg']));
            
            if(!$qresult)
            {
                $errmsg = $dbst->errorInfo();
                return $this->error("[1948] update messages error: {$errmsg[2]}", 500);
            }
            $dbst = null;
            
            // quick poll mod by dig7er, 14.04.2010
            if ($input['quickPoll'] != $row['quickPollOld'])
            {
                if (empty($input['quickPoll']))
                {
                    $app->db->prepare("DELETE FROM {$db_prefix}quickpoll_votes WHERE ID_MSG = ?")->
                        execute(array($input['msg']));
                    $app->db->prepare("DELETE FROM {$db_prefix}quickpolls WHERE ID_MSG = ?")->
                        execute(array($input['msg']));
                }
                else
                {
                      $app->db->prepare("DELETE FROM {$db_prefix}quickpoll_votes WHERE ID_MSG = ?")->
                          execute(array($input['msg']));
                      $app->db->prepare("REPLACE INTO {$db_prefix}quickpolls
                          (ID_MSG, POLL_TITLE)
                          VALUES (?, ?)")->
                          execute(array($input['msg'], $input['quickPoll']));
                }
            }
            
            if (!empty($input['mn']))
                $app->im->NotifyUsers($input['threadid'], $row['subject']);
            
            if (!$self_edit && $app->conf->notify_users)
            {
                // Notify users to IM
                $notice_from_user = urlencode($app->user->name);
                $notice_sbj = $app->locale->mdfsbj;
                $notice_msg = "[url=" . SITE_ROOT . "/people/$notice_from_user/]{$app->user->realname}[/url] {$app->locale->mdfdyr} [url=" . SITE_ROOT . "/{$input['msg']}/]{$app->locale->txt[471]}[/url].\n";
                
                $notice_msg .= "[b]{$app->locale->mdfrznlbl}[/b]: {$input['mdfrzn']}\n\n";
                $notice_msg .= "[h={$app->locale->mdfdrgn}][b][u]{$row['subject']}[/u][/b]\n\n{$row['body']}[/h]";
                $app->im->send_notice($row['ID_MEMBER'], $notice_sbj, $notice_msg);
            } // if not self modify
            
            return $this->redirect("/b{$row['ID_BOARD']}/t{$row['ID_TOPIC']}/msg{$input['msg']}/#msg{$input['msg']}");
            
        } // POST
        else
        {
            // normally we shouldn't get here
            $app->errors->abort('', 'Bad request.', 400);
        }
    } // modify()
    
    public function deleteMsg($request, $response, $service, $app)
    {
        
        if ($app->user->guest) return $this->error(147, 403);
        
        $msg = $request->paramsNamed()->get('msg');
        
        $db_prefix = $app->db->prefix;
        
        $dbst = $app->db->prepare("
                SELECT m.ID_MSG, m.ID_MEMBER, m.attachmentSize, m.attachmentFilename, m.subject, m.body, m.posterName, m.posterTime, t.locked, t.ID_FIRST_MSG, t.ID_LAST_MSG, t.ID_TOPIC, t.ID_POLL, t.numReplies, b.ID_BOARD, b.count, p.POLL_TITLE as quickPollOld
                FROM {$db_prefix}messages AS m, {$db_prefix}topics AS t, {$db_prefix}boards AS b, {$db_prefix}quickpolls as p
                WHERE m.ID_MSG = ? 
                AND t.ID_TOPIC=m.ID_TOPIC
                AND b.ID_BOARD=t.ID_BOARD
                LIMIT 1");
        $dbst->execute(array($msg));
        
        $row = $dbst->fetch();
        
        if (!$row)
            return $this->error("Message $msg not found.");
        
        $threadid = $row['ID_TOPIC'];
        
        // whether user deletes self message
        if ($row['ID_MEMBER'] == $app->user->id)
            $selfdel = true;
        else
            $selfdel = false;
        
        // get board id of this thread
        $boardid = $row['ID_BOARD'];
        // load board info
        $app->board->load($boardid);
        
        // Make sure the user is allowed to delete this post.
        if ($app->user->accessLevel() < 2)
        {
            // Non privileged users allowed delete only self post
            if (!$selfdel)
                return $this->error(67, 403);
            
            // Non privileged users not allowed to delete post in locked thread
            if ($row['locked'] == 1)
                return $this->error(90, 403);
        }
        
        $redirect = "/b$boardid/t$threadid/new/";
        $ajaxresponse = '["OK"]';
        
        $firstOrOnly = false;
        if ($row['ID_FIRST_MSG'] == $row['ID_MSG'] || $row['ID_FIRST_MSG'] == $row['ID_LAST_MSG'])
        {
            $firstOrOnly = true;
            // this is the first message or this is the only message in a topic
            if ($row['numReplies'] != 0)
                // don't allow deleting first message with replies, should delete thread instead
                return $this->error($app->locale->delFirstPost, 403);
        }
        
        if ($request->method('GET'))
        {
            // preview deleting message
            $service->subject = $row['subject'];
            $service->body = $row['body'];
            $service->date = $app->subs->timeformat($row['posterTime']);
            $service->poster = $app->user->loadDisplay($row['posterName']);
            $service->sessid = $app->session->id;
            $service->msgid = $msg;
            $service->title = $app->locale->txt(154);
            return $this->render('templates/thread/deletemsg.template.php');
        }
        elseif ($request->method('POST'))
        {
            $app->session->check('post');
            $topicundeleted = false;
            // TODO implement notify author
            if ($firstOrOnly)
            {
                // undelete mod - save entire topic
                if ($app->conf->enableUnDelete)
                {
                    $topicundeleted = true;
                    //$app->undelete->saveTopic($row['ID_TOPIC']); // TODO implement undelete
                }
                
                // quick poll mod by dig7er, 14.04.2010
                $app->db->prepare("DELETE FROM {$db_prefix}quickpoll_votes WHERE ID_MSG IN (SELECT ID_MSG FROM {$db_prefix}messages WHERE ID_TOPIC = ?)")->
                    execute(array($threadid));
                $app->db->prepare("DELETE FROM {$db_prefix}quickpolls WHERE ID_MSG IN (SELECT ID_MSG FROM {$db_prefix}messages WHERE ID_TOPIC = ?) LIMIT 1")->
                    execute(array($threadid));
                
                $app->db->prepare("DELETE FROM {$db_prefix}topics WHERE ID_TOPIC=? LIMIT 1")->
                    execute(array($threadid));
                $app->db->prepare("DELETE FROM {$db_prefix}polls WHERE ID_POLL=? LIMIT 1")->
                    execute(array($row['ID_POLL']));
                $app->db->prepare("UPDATE {$db_prefix}boards SET numPosts=numPosts-1, numTopics=numTopics-1 WHERE ID_BOARD=?")->
                    execute(array($boardid));
                $app->db->prepare("DELETE FROM {$db_prefix}calendar WHERE id_topic = ?")->
                    execute(array($threadid));
                
                $redirect = "/b$boardid/";
                $ajaxresponse = '["REDIRECT", "' . SITE_ROOT . '/b' . $boardid . '/"]';            
            }
            elseif ($row['ID_LAST_MSG'] == $row['ID_MSG'])
            {
                // this is the last message
                $dbst = $app->db->prepare("SELECT ID_MSG FROM {$db_prefix}messages WHERE (ID_TOPIC=? AND ID_MSG != ?) ORDER BY ID_MSG DESC LIMIT 1");
                $dbst->execute(array($threadid, $msg));
                $row2 = $dbst->fetch();
                $dbst = null;
                
                $app->db->prepare("UPDATE {$db_prefix}topics SET ID_LAST_MSG=?, numReplies=numReplies-1 WHERE ID_TOPIC=?")->
                    execute(array($row2['ID_MSG'], $threadid));
                $app->db->prepare("UPDATE {$db_prefix}boards SET numPosts=numPosts-1 WHERE ID_BOARD=?")->
                    execute(array($boardid));
                $app->db->prepare("DELETE FROM {$db_prefix}calendar WHERE id_topic = ?")->
                    execute(array($threadid));
            }
            else
            {
                // this is just "some" message
                $app->db->prepare("UPDATE {$db_prefix}topics SET numReplies=numReplies-1 WHERE ID_TOPIC=?")->
                    execute(array($threadid));
                $app->db->prepare("UPDATE {$db_prefix}boards SET numPosts=numPosts-1 WHERE ID_BOARD=?")->
                    execute(array($boardid));
            }
            
            if ($row['ID_MEMBER'] != '-1' && $row['count'] != 1)
            {
                $app->db->prepare("UPDATE {$db_prefix}members SET posts=posts-1 WHERE (ID_MEMBER=? && posts > 0)")->
                    execute(array($row['ID_MEMBER']));
            }
            
            // undelete mod - save message only (if haven't previously saved topic
            if ( !$topicundeleted && $app->conf->enableUnDelete)
                // $app->undelete->saveMessage($row['ID_MSG']); // TODO implement undelete
            
            $app->db->prepare("DELETE FROM {$db_prefix}messages WHERE ID_MSG=? LIMIT 1")->
                execute(array($msg));
            
            $nowtime = time();
            $app->db->query("DELETE FROM {$db_prefix}log_last_comments WHERE LAST_COMMENT_TIME < FROM_UNIXTIME($nowtime - 7*24*60*60) OR LAST_COMMENT_TIME IS NULL");
            
            // quick poll mod by dig7er, 14.04.2010
            $app->db->prepare("DELETE FROM {$db_prefix}quickpolls WHERE ID_MSG=?")->
                execute(array($msg));
            $app->db->prepare("DELETE FROM {$db_prefix}quickpoll_votes WHERE ID_MSG=?")->
                execute(array($msg));
            
            $app->subs->updateStats('message');
            $app->subs->updateStats('topic');
            $app->subs->updateLastMessage($boardid);
            
            // undelete mod
            if (!$app->conf->unDeleteAttachments)
            {
                // delete attachment, by Meriadoc
                if ($row['attachmentSize'] > 0)
                    unlink($app->conf->attachmentUploadDir . "/" . $row['attachmentFilename']);
            }
            
            if ($service->ajax)
                return $this->ajax_response($ajaxresponse, 'json');
            else
                return $this->redirect($redirect);
            
        }
        else
        {
            // normally we shouldn't get here
            $app->errors->abort('', 'Bad request.', 400);
        }
    } // deleteMsg()
    
    public function deleteThread($request, $response, $service, $app)
    {
        if ($app->user->guest) return $this->error(147, 403);
        
        $threadid = $request->paramsNamed()->get('thread');
        
        $threadid = intval($threadid);
        if ($threadid == 0) return $this->error('Invalid thread ID specified.');
        
        $db_prefix = $app->db->prefix;
        $dbst = $app->db->prepare("SELECT
            t.ID_TOPIC, t.ID_BOARD, t.ID_FIRST_MSG, t.locked, t.numReplies, t.ID_POLL,
            msg.subject, msg.body, msg.posterName, msg.posterTime
            FROM {$db_prefix}topics AS t LEFT JOIN messages AS msg
            ON t.ID_FIRST_MSG = msg.ID_MSG
            WHERE t.ID_TOPIC = ?
        ");
        $dbst->execute(array($threadid));
        $row = $dbst->fetch();
        
        if(!$row)
            return $this->error("No thread with ID $threadid.");
        
        $dbst = null; // closing this statement
        
        $boardid = $row['ID_BOARD'];
        $app->board->load($boardid);
        
        if ($app->user->accessLevel() < 2)
            return $this->errors($app->locale->txt[73], 403);
        
        if ($request->method('GET'))
        {
            // preview deleting message
            $service->subject = $row['subject'];
            $service->body = $row['body'];
            $service->date = $app->subs->timeformat($row['posterTime']);
            $service->poster = $app->user->loadDisplay($row['posterName']);
            $service->sessid = $app->session->id;
            $service->msgid = $row['ID_FIRST_MSG'];
            $service->title = 'Delete thread';
            return $this->render('templates/thread/deletemsg.template.php');
        }
        elseif ($request->method('POST'))
        {
            $app->session->check('post');
            $POST = $request->paramsPost();
            $reason = $POST->get('mdfrzn', 'none');
            if($service->ajax)
                $reason = mb_convert_encoding($reason, $app->conf->charset, 'utf-8');
            
            //// undelete mod
            //if ($app->conf->enableUnDelete)
                // $app->undelete->saveTopic($threadid); // TODO implement undelete topic
            
            // undelete mod
            if (!$app->conf->unDeleteAttachments)
            {
                //Lines 38-43 all there to delete attachments on thread deletion - Jeff
                $dbst = $app->db->prepare("SELECT attachmentFilename FROM {$db_prefix}messages WHERE (ID_TOPIC=? AND (attachmentFilename IS NOT NULL))");
                $dbst->execute(array($threadid));
                while ($attachment = $dbst->fetch())
                {
                    $attachmentFilename = $app->conf->attachmentUploadDir . "/" . $attachment['attachmentFilename'];
                    if (file_exists($attachmentFilename))
                        unlink($attachmentFilename);
                }
                $dbst = null;
            }
            
            // quick poll mod by dig7er, 14.04.2010
            $app->db->query("DELETE FROM {$db_prefix}quickpoll_votes WHERE ID_MSG IN (SELECT ID_MSG FROM {$db_prefix}messages WHERE ID_TOPIC = $threadid)");
            $app->db->query("DELETE FROM {$db_prefix}quickpolls WHERE ID_MSG IN (SELECT ID_MSG FROM {$db_prefix}messages WHERE ID_TOPIC = $threadid)");
            
            $dbst = $app->db->prepare("DELETE FROM {$db_prefix}polls WHERE ID_POLL=? LIMIT 1")->
                execute(array($row['ID_POLL']));
            
            $row['numReplies']++;
            
            $dbst = $app->db->prepare("UPDATE {$db_prefix}boards SET numTopics=numTopics-1, numPosts=numPosts-? WHERE ID_BOARD=?")->
                execute(array($row['numReplies'], $row['ID_BOARD']));
            
            $app->db->query("DELETE FROM {$db_prefix}topics WHERE ID_TOPIC=$threadid");
            $app->db->query("DELETE FROM {$db_prefix}messages WHERE ID_TOPIC=$threadid");
            $app->db->query("DELETE FROM {$db_prefix}calendar WHERE id_topic=$threadid");
            $app->db->query("DELETE FROM {$db_prefix}log_topics WHERE ID_TOPIC=$threadid");
            
            $app->subs->updateStats('message');
            $app->subs->updateStats('topic');
            $app->subs->updateLastMessage($row['ID_BOARD']);
            
            // Check on whether posts count in this board.
            $dbrq = $app->db->query("
                SELECT count
                FROM {$db_prefix}boards
                WHERE ID_BOARD = $row[ID_BOARD]");
            $pcounter = $dbrq->fetchColumn();
            $dbrq = null;
            
            $subject = null;
            $thread_author = null;
            
            $dbrq = $app->db->query("SELECT ID_MEMBER, subject FROM {$db_prefix}messages WHERE ID_TOPIC=$threadid ORDER BY ID_MSG ASC");
            
            // Posts *do* count here, do decrease the poster's post counts.
            if (empty($pcounter))
                while ($temp = $dbrq->fetch(\PDO::FETCH_NUM))
                {
                    if ($thread_author == null){
                        $thread_author = $temp[0];
                        $subject = $temp[1];
                    }
                    if ($temp[0] != '-1')
                        $app->db->query("UPDATE {$db_prefix}members SET posts=posts-1 WHERE ID_MEMBER=$temp[0]");
                }
            else
            {
                if($app->conf->notify_users)
                {
                    $temp = $dbrq->fetch(\PDO::FETCH_NUM);
                    $thread_author = $temp[0];
                    $subject = $temp[1];
                }
            }
            
            $dbrq = null;

            if ($app->conf->notify_users && $thread_author > 0 && $app->user->id != $thread_author)
            {
                // Notify thread author
                $euser = urlencode($username);
                $notice_msg = "[url=" . SITE_ROOT . "/people/$euser]{$app->user->realname}[/url] {$app->locale->dltdyrtpc} [url=" . SITE_ROOT . "/b$boardid/t$threadid/]{$subject}[/url] {$app->locale->dltdtpcrzn} \"$reason\".";
                $app->im->send_notice($thread_author, $app->locale->dltdtpcsbj, $notice_msg);
            }
            
            if ($service->ajax)
                return $this->ajax_response("[\"REDIRECT\", \"" . SITE_ROOT . "/b$boardid/\"]");
            else
                return $this->redirect("/b$boardid/");
        } // method POST
        
    } // deleteThread()
    
    public function editPoll($request, $response, $service, $app)
    {
        if ($app->user->guest)
            return $this->error($app->locale->txt[1]);
        
        $PARAMS = $request->paramsNamed();

        $db_prefix = $app->db->prefix;
        # Determine what category we are in.
        $dbst = $app->db->prepare("SELECT b.ID_BOARD AS bid, b.name AS bname, c.ID_CAT AS cid, c.name AS cname, t.ID_MEMBER_STARTED, m.subject FROM {$db_prefix}boards AS b, {$db_prefix}categories AS c, {$db_prefix}topics AS t, {$db_prefix}messages AS m WHERE (b.ID_BOARD=t.ID_BOARD AND b.ID_CAT=c.ID_CAT AND m.ID_TOPIC = t.ID_TOPIC AND t.ID_TOPIC=?)");
        $dbst->execute(array($PARAMS->thread));
        $bcinfo = $dbst->fetch();
        $dbst = null;
        
        if (!$bcinfo)
            return $this->error('Topic not found.');
        
        $app->board->load($bcinfo['bid']);
        
        if (($app->user->id == $bcinfo['ID_MEMBER_STARTED'] && $app->conf->pollEditMode == '2') || $app->user->isStaff() || ($app->user->isBoardModerator() && in_array($app->conf->pollEditMode, array('2','1'))))
            ;// we're ok
        else
            // Access Denied.
            return $this->error($txt[1]);
        
        $keys = array('p.ID_POLL', 'p.question');
        for ($i = 1; $i < 21; $i++)
        {
            $keys[] = "p.option$i";
            $keys[] = "p.votes$i";
        }
        $keys = implode(', ', $keys);
        
        $dbst = $app->db->prepare("SELECT p.ID_POLL, p.votingLocked as locked, p.question, $keys FROM {$db_prefix}polls AS p,{$db_prefix}topics AS t WHERE (p.ID_POLL=t.ID_POLL && t.ID_TOPIC=?) LIMIT 1");
        $dbst->execute(array($PARAMS->thread));
        $pollinfo = $dbst->fetch();
        $dbst = null;
        
        if (empty($pollinfo))
            return $this->error('Poll not found.');

        $data = array(
            'title' => $app->locale->yse39,
            'sub' => $bcinfo['subject'],
            'boardname' => $bcinfo['bname'],
            'catname' => $bcinfo['cname'],
            'threadid' => $PARAMS->thread,
            'poll' => $pollinfo,
            'linktree' => array(
                array('url' => "/#{$bcinfo['cid']}", 'name' => $bcinfo['cname']),
                array('url' => "/b{$bcinfo['bid']}/", 'name' => $bcinfo['bname']),
                array('name' => "{$app->locale->yse39} ({$bcinfo['subject']})")
            )
        );
        
        if (!empty($pollinfo['locked']))
            $data['poll_locked'] = 'checked';
        else
            $data['poll_locked'] = '';
        
        if ($request->method('GET'))
        {
            return $this->render('templates/thread/editpoll.template.php', $data);
            
        } // if GET
        elseif ($request->method('POST'))
        {
            $POST = $request->paramsPost();
            
            $preparedData = array();
            for ($i=1; $i<21; $i++)
            {
                $val = $POST->get("option$i");
                $preparedData["option$i"] = empty($val) ? null : trim($val);
                
                if ($POST->resetVoteCount == 'on')
                {
                    $preparedData["votes$i"] = 0;
                }
            }
            
            if ($POST->resetVoteCount == 'on')
            {
                $preparedData['votedMemberIDs'] = null;
            }
            
            // Lock/unlock voting
            if ($pollinfo['locked'] == 0 && $POST->votingLocked == 'on')
            {
                // Should lock
                if ($app->user->isAdmin())
                    $preparedData['votingLocked'] = 2;
                else
                    $preparedData['votingLocked'] = 1;
            }
            elseif ($pollinfo['locked'] > 0 && empty($POST->votingLocked))
            {
                // Should unlock
                if ($pollinfo['locked'] == 2 && !$app->user->isAdmin())
                    // Poll locked by admin, disallow unlocking
                    return $this->error($app->locale->yse31);
                
                $preparedData['votingLocked'] = 0;
            }
            
            $preparedData['question'] = $POST->question;
            
            $placeholders = $app->db->build_placeholders($preparedData, true, true);
            
            $preparedData['idpoll'] = $pollinfo['ID_POLL'];
            
            $app->db->prepare("UPDATE {$db_prefix}polls SET $placeholders WHERE ID_POLL=:idpoll")->
                execute($preparedData);
            
            $this->redirect("/b{$bcinfo['bid']}/t{$PARAMS->thread}/");
        } // if POST
    } // editPoll()
    
    public function pollVote($request, $response, $service, $app)
    {
        if ($app->user->guest)
            return $this->error($app->locale->yse28);
        
        $POST = $request->paramsPost();
        $PARAMS = $request->paramsNamed();
        
        $db_prefix = $app->db->prefix;
        $dbst = $app->db->prepare("SELECT votedMemberIDs, votingLocked FROM {$db_prefix}polls WHERE ID_POLL=? AND (FIND_IN_SET(?, votedMemberIDs) = 0 OR votedMemberIDs IS NULL) LIMIT 1");
        $dbst->execute(array($PARAMS->poll, $app->user->id));
        $pollinfo = $dbst->fetch();
        $dbst = null;
        
        if (empty($pollinfo))
            return $this->error($app->locale->yse27);
        
        // No option specified
        if (empty($POST->option))
            return $this->error($app->locale->yse26);
        
        $option = intval($POST->option);
        
        if (!empty($pollinfo['votingLocked']) && $pollinfo['votingLocked'] != '0')
            return $this->error($app->locale->yse27);
        
        if (empty($pollinfo['votedmemberIDs']))
            $votedMemberIDs = array();
        else
            $votedMemberIDs = explode(',', $pollinfo['votedmemberIDs']);
        
        $votedMemberIDs[] = $app->user->id;
        $newIDs = implode(",", $votedMemberIDs);
        if (substr($newIDs,0,1) == ',')
            $newIDs = substr($newIDs,1);

        $selectedoption = "votes$option";
        
        $app->db->prepare("UPDATE {$db_prefix}polls SET $selectedoption = $selectedoption + 1, votedMemberIDs=? WHERE ID_POLL=?")->
            execute(array($newIDs, $PARAMS->poll));
        
        return $this->back();
    } // pollVote()
    
    // redirect to message
    public function gotomsg($request, $response, $service, $app)
    {
        $msg = $request->paramsNamed()->get('msg');
        
        if (empty($msg))
            return $this->redirect('/');
        
        if (strpos($msg, "-"))
        {
            $gomsg = explode("-", $msg);
            $anchor = "comment" .$msg;
            $msg = $gomsg[0];
            if (intval($gomsg[1]) == 0) {
                return $this->redirect('/');
            }
        }
        else $anchor = "msg$msg";
        
        $msg = intval($msg);
        
        if ($msg == 0) {
            return $this->redirect('/');
        }
        
        $dbst = $app->db->query("SELECT m.ID_TOPIC thread, t.ID_BOARD board FROM messages m LEFT JOIN topics t ON (m.ID_TOPIC = t.ID_TOPIC) WHERE m.ID_MSG = $msg LIMIT 1");
        $row = $dbst->fetch();
        $dbst = null;
        if (!empty($row))
            $yySetLocation = "/b{$row['board']}/t{$row['thread']}/msg$msg/#$anchor";
        else
            $yySetLocation = '/';
        
        return $this->redirect($yySetLocation);
    } // gotomsg
    
    public function report($request, $response, $service, $app)
    {
        if ($app->user->guest)
            return $this->error($app->locale->txt[1]);
        
        if ($app->conf->enableReportToMod != '1')
            return $this->error($app->locale->txt[1]);
        
        $msgid = (int) $request->paramsNamed()->get('msgid');
        if (empty($msgid))
            return $this->error('Bad request.');
        
        $db_prefix = $app->db->prefix;
        
        // Get message board ID
        $dbst = $app->db->query("SELECT t.ID_BOARD, m.posterName FROM {$db_prefix}messages AS m
            JOIN topics AS t ON m.ID_TOPIC = t.ID_TOPIC
            WHERE ID_MSG = $msgid");
        $msg_info = $dbst->fetch();
        $dbst = null;
        
        if (empty($msg_info) || empty($msg_info['ID_BOARD']))
            return $this->error("Board not found.");
        
        // lets get some mods...
        $themoderators = $app->board->moderators($msg_info['ID_BOARD']);
        $themoderators = array_keys($themoderators);
        
        $skip_admins = $app->conf->get('dontNotifyAdmins', array());
        
        // Get admins
        $dbst = $app->db->query("SELECT ID_MEMBER, memberName FROM {$db_prefix}members WHERE memberGroup = 'Administrator'");
        
        while ($row = $dbst->fetch())
        {
            if (!in_array($row['memberName'], $themoderators) && !in_array($row['ID_MEMBER'], $skip_admins))
                $themoderators[] = $row['memberName'];
        }
        $dbst = null;
        
        // Жалобы из раздела Флейм только для модераторов и админа
        if ($msg_info['ID_BOARD'] != 16)
        {
            // loop through global moderators
            $dbst = $app->db->query("SELECT memberName FROM {$db_prefix}members WHERE (memberGroup='Global Moderator')");
            
            while ($row = $dbst->fetch())
                if (!in_array ($row['memberName'], $themoderators))
                    $themoderators[] = $row['memberName'];
            $dbst = null;
            
            $secretModerators = $app->conf->get('hiddenModerators', array());
            if(is_array($secretModerators) && sizeof($secretModerators) > 0)
            {
                $placeholders = $app->db->build_placeholders($secretModerators);
                $dbst = $app->db->prepare("SELECT memberName FROM {$db_prefix}members WHERE ID_MEMBER in ($placeholders)");
                $dbst->execute($secretModerators);
                while ($row = $dbst->fetchColumn())
                {
                    if (!in_array($row, $themoderators))
                        $themoderators[] = $row;
                }
                $dbst = null;
            }
        } // board ! 16
        
        $poster = $app->user->loadDisplay($msg_info['posterName']);
        $service->form_subject = "Обратите внимание на сообщение {$poster['realName']}";
        $service->imto = implode(',', $themoderators);
        $service->report = true;
        $service->report_msgid = $msgid;
        
        return $app->im->impost($request, $response, $service, $app);
    }
}
