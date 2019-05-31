<?php
namespace Prodigy\Respond;

class Board extends Respond
{
    protected $board;
    protected $moderators;
    protected $announcement;
    
    
    public function __construct($router)
    {
        parent::__construct($router);
        $this->moderators = null;
        $this->announcement = false;
        $this->board = $this->service->board;
    }
    
    /**
     * Get board info (moderators list etc)
     */
    public function load($board = null)
    {
        if ($board === null)
            $board = $this->board;
        
        $board = (int) $board;
        if ($board == 0)
            return $this->error("Board info requested but no board ID specified.");
        elseif ($board == -1)
        {
            $this->service->board_moderators = $this->moderators = array();
            $this->announcement = false;
            $this->board = $this->service->board = -1;
            return;
        }
        
        if (is_array($this->moderators))
        {
            // we already got here earlier
            
            return;
        }
        
        // Initial data
        $this->board = $board;
        $this->service->board = $board;
        $this->service->board_moderators = $this->moderators = array();
        $this->announcement = false;
        
        $db = $this->app->db;
        $dbrq = $db->prepare("SELECT b.moderators, b.isAnnouncement FROM {$db->prefix}boards AS b WHERE (b.ID_BOARD=?)");
        $dbrq->execute(array($board));
        $row = $dbrq->fetch();
        $dbrq = null;
        /* if there aren't any, skip */
        if ($row)
        {
            $moderators = explode(',', trim($row['moderators']));
            $this->announcement = $row['isAnnouncement'];
            
            $_moderators = array();
            for ($i = 0; $i < sizeof($moderators); $i++)
            {
                $moderators[$i] = trim($moderators[$i]);
                $_moderators[$moderators[$i]] = $this->app->user->LoadDisplay($moderators[$i]);
            }
            $this->moderators = $this->service->board_moderators = $_moderators;
        }
    } // load()
    
    public function isAnnouncement($board = null)
    {
        $this->load($board);
        return $this->announcement;
    }
    
    public function moderators($board = null)
    {
        $this->load($board);
        return $this->moderators;
    }
    
    /**
     * Display selected board
     */
    public function index($request, $response, $service, $app)
    {
        $this->load($request->param('board'));
        $user = $app->user;
        $site_root = SITE_ROOT;
        
        $db_prefix = $app->db->prefix;
        
        // Get the board and category information
        $result = $app->db->prepare("
            SELECT b.name,b.description,b.moderators,c.name,b.ID_CAT,c.memberGroups,b.isAnnouncement,b.numTopics
            FROM {$db_prefix}boards as b,{$db_prefix}categories as c
            WHERE b.ID_BOARD=?
            AND b.ID_CAT=c.ID_CAT");
        $result->execute(array($this->board));
        $row = $result->fetch(\PDO::FETCH_NUM);
        $result = null;
        if (!$row)
            return $this->error($app->locale->txt('yse232', 'No such board'));
        list ($boardname,$bdescrip,$bdmods,$cat,$currcat,$temp2,$isAnnouncement,$topiccount) = $row;
        
        $memgroups = explode(',',$temp2);
        if (!(in_array($user->group, $memgroups) || $memgroups[0] == null || $user->accessLevel() > 2))
            return $this->error($app->locale->txt[1]); // access denied
        
        $view = $request->param('start', null);
        $start = intval($view);
        
        if (!is_null($view) && $view !== 'all' && $start == 0) {
            return $response->redirect(SITE_ROOT . "/b{$this->board}/");
        }
        
        // are we views all the topics, or just a few?
        $maxindex = ($view === 'all' ? $topiccount : $app->conf->maxdisplay);
        
        // Make sure the starting place makes sense.
        if ($start > $topiccount)
            $start = ( $topiccount % $maxindex ) * $maxindex;
        elseif ($start < 0)
            $start = 0;
        
        $start = (int)($start / $maxindex) * $maxindex;
        
        
        // FIXME this html shoud be moved to templates
        $pageindex = '';
        // Construct the page links for this board.
        if ($app->conf->compactTopicPagesEnable == 0)
        {
            $tmpa = $start - $maxindex;
            $pageindex .= (($start == 0 ) ? ' ' : "<a href=\"$site_root/b{$this->board}/$tmpa/\">&#171;</a> ");
            $tmpa = 1;
            for ( $counter = 0; $counter < $topiccount; $counter += $maxindex )
            {
                $pageindex .= (($start == $counter) ? "<b>$tmpa</b> " : "<a href=\"$site_root/b{$this->board}/$counter/\">$tmpa</a> ");
                ++$tmpa;
            }
            $tmpa = $start + $maxindex;
            $tmpa = (($tmpa > $topiccount) ? $topiccount : $tmpa);
            if ($start != $counter-$topiccount)
                $pageindex .= ($tmpa > $counter-$maxindex ? " " : "<a href=\"$site_root/b{$this->board}/$tmpa/\">&#187;</a> ");
        }
        else
        {
            if (($app->conf->compactTopicPagesContiguous % 2) == 1)	//1,3,5,...
                $PageContiguous = (int)(($app->conf->compactTopicPagesContiguous - 1) / 2);
            else
                $PageContiguous = (int)($app->conf->compactTopicPagesContiguous / 2);
                //invalid value, but let's deal with it
            
            if ($start > $maxindex * $PageContiguous)	//	first
                $pageindex.= "<a class=\"navPages\" href=\"$site_root/b{$this->board}/\">1</a> ";
            if ($start > $maxindex * ($PageContiguous + 1))	// ...
                $pageindex.= "<b> ... </b>";
            
            for ($nCont=$PageContiguous; $nCont >= 1; $nCont--)	// 1 & 2 before
                if ($start >= $maxindex * $nCont)
                {
                    $tmpStart = $start - $maxindex * $nCont;
                    $tmpPage = $tmpStart / $maxindex + 1;
                    $pageindex.= "<a class=\"navPages\" href=\"$site_root/b{$this->board}/$tmpStart/\">$tmpPage</a> ";
                }
            
            $tmpPage = $start / $maxindex + 1;	// page to show
            $pageindex.= ' <span class="pageindex-bracket">[</span><b class="pageindex-current">' . $tmpPage . '</b><span class="pageindex-bracket">]</span> ';
            
            $tmpMaxPages = (int)(($topiccount - 1) / $maxindex) * $maxindex;	// 1 & 2 after
            for ($nCont=1; $nCont <= $PageContiguous; $nCont++)
                if ($start + $maxindex * $nCont <= $tmpMaxPages)
                {
                    $tmpStart = $start + $maxindex * $nCont;
                    $tmpPage = $tmpStart / $maxindex + 1;
                    $pageindex.= "<a class=\"navPages\" href=\"$site_root/b{$this->board}/$tmpStart/\">$tmpPage</a> ";
                }
            
            if ($start + $maxindex * ($PageContiguous + 1) < $tmpMaxPages)	// ...
                $pageindex.= "<b> ... </b>";
            
            if ($start + $maxindex * $PageContiguous < $tmpMaxPages)	 // last
            {
                $tmpPage = $tmpMaxPages / $maxindex + 1;
                $pageindex.= "<a class=\"navPages\" href=\"$site_root/b{$this->board}/$tmpMaxPages/\">$tmpPage</a> ";
            }
        } // if compactTopicPagesEnable
        
        // view all mod
        if ($maxindex < $topiccount)
            $pageindex .= "<a class=\"navPages\" href=\"$site_root/b{$this->board}/all/\">{$app->locale->txt[190]}</a>";
        
        // Build a list of the board's moderators.
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
        
        $canPostPoll = ($app->conf->pollMode == '1' && (($app->conf->pollPostingRestrictions == '2' && ($user->accessLevel() > 1)) || ($app->conf->pollPostingRestrictions == '1' && $user->group == 'Administrator') || $app->conf->pollPostingRestrictions == '0'));
        
        $buttonArray = array();
        if ($user->name != 'Guest')
            if ($app->conf->showmarkread)
                $buttonArray[] = "<a href=\"$site_root/b{$this->board}/markasread/\"><font size=\"1\" class=\"imgcatbg\">{$app->locale->img['markboardread']}</font></a>";
        if (!$isAnnouncement || $user->accessLevel() > 1)
        {
            if ($app->user->allowedToReply($user->id)) {
                $buttonArray[] = "<a href=\"$site_root/b{$this->board}/post/?title=" . urlencode($app->locale->txt[464]) . "\"><font size=\"1\" class=\"imgcatbg\">{$app->locale->img['newthread']}</font></a>";
                if ($canPostPoll)
                    $buttonArray[] =  "<a href=\"$site_root/b{$this->board}/postpoll/\"><font size=\"1\" class=\"imgcatbg\">{$app->locale->img['newpoll']}</font></a>";
            }
        }
        
        $service->jumptoform = $this->prepareJumpToForm($this->board);
        
        if ($user->name != 'Guest') {
            // mark current board as seen
            $app->db->prepare("REPLACE INTO {$db_prefix}log_boards (logTime,ID_MEMBER,ID_BOARD) VALUES (?,?,?)")->
                execute(array(time(), $user->id, $this->board));
        }
        
        $service->title = $boardname;
        $service->currcat = $currcat;
        $service->catname = $cat;
        $service->bdescrip = $bdescrip;
        $service->showTopicRatings = false;
        $service->start = $start;
        $service->pageindex = $pageindex;
        $service->buttonArray = $buttonArray;
        $service->orderby = $request->param('orderby');
        $service->search = $request->param('search', '');
        
        // template_header();
        
        // chatWall mod by dig7er
        if ($app->conf->boardBillsEnabled)
        {
            $dbrequest = $app->db->query("SELECT chatWall, chatWallMsgAuthor FROM {$db_prefix}boards WHERE ID_BOARD = {$this->board}");
            $row = $dbrequest->fetch();
            $dbrequest = null;
            $row['chatWall'] = $app->subs->unicodeentities(stripslashes($row['chatWall']));
            $service->chatWall = $row;
        }
        
        // topic ordering
        $orderStr = "";
        if ($service->orderby == "numreplies")
            $orderStr = "numReplies DESC,";
        else if ($service->orderby == "numviews")
            $orderStr = "numViews DESC,";
        else if ($service->orderby == "posrating")
            $orderStr = "POSITIVE DESC,";
        else if ($service->orderby == "negrating")
            $orderStr = "NEGATIVE DESC,";
        
        // Grab the appropriate topic information
        $stickyOrder = (($app->conf->enableStickyTopics and empty($service->orderby))? 't.isSticky DESC,' : '');
        /* ### query optimization by dig7er ### */
        $dbst = $app->db->prepare("
            SELECT t.ID_TOPIC FROM {$db_prefix}topics t LEFT JOIN {$db_prefix}messages m
            ON (t.ID_LAST_MSG = m.ID_MSG) WHERE t.ID_BOARD = ?
            ORDER BY $stickyOrder m.posterTime DESC LIMIT ?,?");
        $dbst->execute(array($this->board, $start, $maxindex));
        $_topics = $dbst->fetchAll(\PDO::FETCH_COLUMN);
        $dbst = null;
        
        $topics = array();
        
        if (count($_topics))
        {
            if(!empty($service->search))
            {
                $search_query_part = "AND (m2.subject LIKE '%{$search_query}%' OR mem2.realName LIKE '%{$search_query}%' OR m2.posterName LIKE '%{$search_query}%')";
                $search_query_params = array("%{$search_query}%","%{$search_query}%","%{$search_query}%");
            }
            else
            {
                $search_query_part = '';
                $search_query_params = array();
            }
            
            /* ### query optimization by dig7er ### */
            $dbst = $app->db->prepare("
                SELECT t.ID_LAST_MSG, t.ID_TOPIC, t.numReplies, t.locked, m.posterName, m.ID_MEMBER, IFNULL(mem.realName, m.posterName) AS posterDisplayName, t.numViews, m.posterTime, m.modifiedTime, t.ID_FIRST_MSG, t.isSticky, t.ID_POLL, m2.posterName as mname, m2.ID_MEMBER as mid, IFNULL(mem2.realName, m2.posterName) AS firstPosterDisplayName, m2.subject as msub, m2.icon as micon, m2.body as mbody, IFNULL(lt.logTime, 0) AS isRead, IFNULL(lmr.logTime, 0) AS isMarkedRead, tr.POSITIVE AS topicPosRating, tr.NEGATIVE AS topicNegRating
                    FROM {$db_prefix}topics as t JOIN {$db_prefix}messages as m ON (m.ID_MSG=t.ID_LAST_MSG)
                    JOIN {$db_prefix}messages as m2 ON (m2.ID_MSG=t.ID_FIRST_MSG) LEFT JOIN {$db_prefix}topic_ratings as tr ON (t.ID_TOPIC = tr.TOPIC_ID)
                    LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER=m.ID_MEMBER)
                    LEFT JOIN {$db_prefix}members AS mem2 ON (mem2.ID_MEMBER=m2.ID_MEMBER)
                    LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.ID_TOPIC=t.ID_TOPIC AND lt.ID_MEMBER=?)
                    LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD=? AND lmr.ID_MEMBER=?)
                    WHERE t.ID_TOPIC IN (" . implode(',', $_topics) . ")
                    $search_query_part
                    ORDER BY $orderStr $stickyOrder m.posterTime DESC");
            $dbst->execute(array_merge(array($app->user->id, $this->board, $app->user->id), $search_query_params));
            
            while ($row = $dbst->fetch())
            {
                if ($row['ID_POLL'] != '-1' && $app->conf->pollMode == 0)
                    continue;
                
                // Skip topics of ignored users
                if ($app->user->inIgnore($row['mname'])) continue;
                
                $mnum = $row['ID_TOPIC'];
                $topics[$mnum] = $row;
                $topics[$mnum]['msub'] = $app->subs->CensorTxt($row['msub']);
                $topics[$mnum]['lastposter'] = $row['posterName'];
                $topics[$mnum]['lastPosterID'] = $row['ID_MEMBER'];
                $topics[$mnum]['mdate'] = $row['posterTime'];
                $topics[$mnum]['firstMessage'] = "Сообщение: ";
                $topics[$mnum]['firstMessage'] .= $service->doUBBC($row['mbody'], 'links,inline,blocks');
                $topics[$mnum]['firstMessage'] = preg_replace(array("/(<[^>]+>)/i", "/&nbsp;/i"), array("", " "), $topics[$mnum]['firstMessage']);
                $topics[$mnum]['firstMessage'] = addslashes($service->esc($topics[$mnum]['firstMessage']));
                $topics[$mnum]['mreplies'] = $row['numReplies'];
                $topics[$mnum]['mstate'] = $row['locked'];
                $topics[$mnum]['views'] = $row['numViews'];
                $topics[$mnum]['pollID'] = $row['ID_POLL'];
                $topics[$mnum]['topicEditedTime'] = $row['posterTime'];
                $topics[$mnum]['name1'] = $row['posterDisplayName'];
                $topics[$mnum]['name2'] = $row['firstPosterDisplayName'];
                
                if (!isset($row['topicPosRating']))
                    $topics[$mnum]['topicPosRating'] = 0;
                if (!isset($row['topicNegRating']))
                    $topics[$mnum]['topicNegRating'] = 0;
                
                // Set thread class depending on locked status and number of replies.
                if ( $topics[$mnum]['mstate'] == 1 || $topics[$mnum]['mstate'] == 2 )
                    $topics[$mnum]['threadclass'] = 'locked';
                elseif ( $topics[$mnum]['mreplies'] > 24 )
                    $topics[$mnum]['threadclass'] = 'veryhotthread';
                elseif ( $topics[$mnum]['mreplies'] > 14 )
                    $topics[$mnum]['threadclass'] = 'hotthread';
                elseif ( $topics[$mnum]['mstate'] == 0)
                    $topics[$mnum]['threadclass'] = 'thread';
                
                if ($app->conf->enableStickyTopics && $topics[$mnum]['isSticky'] == 1)
                    $topics[$mnum]['threadclass'] = 'sticky';
                
                if (($topics[$mnum]['mstate'] == 1 || $topics[$mnum]['mstate'] == 2 )&&($app->conf->enableStickyTopics == 1 && $topics[$mnum]['isSticky'] == 1))
                    $topics[$mnum]['threadclass'] = 'lockedsticky';
                
                if ($app->conf->pollMode == '1' && $topics[$mnum]['pollID'] != '-1')
                    $topics[$mnum]['threadclass'] = 'poll';
                
                if ($app->conf->pollMode == '1' && $topics[$mnum]['pollID'] != '-1' && ( $topics[$mnum]['mstate'] == 1 || $topics[$mnum]['mstate'] == 2 ))
                    $topics[$mnum]['threadclass'] = 'locked_poll';
                
                // Decide if thread should have the "NEW" indicator next to it.
                // Do this by reading the user's log for last read time on thread,
                // and compare to the last post time on the thread.
                $topics[$mnum]['new'] = ($row['isRead'] >= $topics[$mnum]['topicEditedTime'] || $row['isMarkedRead'] >= $topics[$mnum]['topicEditedTime'] ? false : true);
                
                // Get last comment time
                $result2 = $app->db->query("SELECT ID_MSG, comments FROM {$db_prefix}messages WHERE ID_TOPIC = $mnum AND last_comment_time IS NOT NULL ORDER BY last_comment_time DESC LIMIT 1");
                list($msgID, $comments) = $result2->fetch(\PDO::FETCH_NUM);
                $result2 = null;
                $topics[$mnum]['topicLastCommentTime'] = 0;
                if (strlen($comments) > 0) {
                    $csvdata = explode("\r\n", $comments);
                    $csvdata = array_reverse($csvdata);
                    foreach ($csvdata as $csvline) {
                        if (empty($csvline)) continue;
                        $data = explode("#;#", $csvline);
                        if ($data[1] == $app->user->name) continue;
                        $topics[$mnum]['topicLastCommentTime'] = $data[2];
                        break;
                    }
                }
                
                $topics[$mnum]['newComments'] = ($row['isRead'] >= $topics[$mnum]['topicLastCommentTime'] || $row['isMarkedRead'] >= $topics[$mnum]['topicLastCommentTime'] ? false : $msgID);
                
            
            } // while fetch_assoc()
            $dbst = null; // closing statement
            if (!count($topics))
                $service->search_not_found = true;
        } // if count($_topics);
        
        $service->boardviewers = $this->getBoardViewersList($this->board);
        
        $service->topics = $topics;
        
        $this->render('templates/board/index.php');
        
    } // index()
    
    /**
     * Compose topic pages list
     * This called from template as <?= $this->app->board->topicPages() ?>
     * @param int $threadid    thread id
     * @param int $numreplies  thread replies count
     * @return string
     */
    public function topicPages($threadid, $numreplies) {
        // Decide how many pages the thread should have.
        $threadlength = $numreplies + 1;
        $pages = '';
        if ($threadlength > $this->app->conf->maxmessagedisplay )
          {
            $tmppages = array();
            $tmpa = 1;
            for ( $tmpb = 0; $tmpb < $threadlength; $tmpb += $this->app->conf->maxmessagedisplay )
              {
                if($tmpb == 0) $_tmpb = '';
                else $_tmpb = "$tmpb/";
                $tmppages[] = '<a href="'.SITE_ROOT."/b{$this->board}/t$threadid/$_tmpb\">$tmpa</a>";
                ++$tmpa;
              }
            if (sizeof($tmppages) <= 5 )  // should we show links to ALL the pages?
              {
                $pages = implode(" ",$tmppages);
                $pages = "<font size=\"1\">&#171; $pages &#187;</font>";
              }
            else 
              {// or should we skip some?
                $s1 = sizeof($tmppages)-1;
                $s2 = sizeof($tmppages)-2;
                $pages = "<font size=\"1\">&#171; $tmppages[0] $tmppages[1] ... $tmppages[$s2] $tmppages[$s1] &#187;</font>";
              }
            // view all mod
            $pages = str_replace("&#187",'<a href="'.SITE_ROOT ."/b{$this->board}/t$threadid/new/\">{$this->app->locale->txt[1901]}</a> <a href=\"".SITE_ROOT."/b{$this->board}/t$threadid/all/\">{$this->app->locale->txt[190]}</a> &#187", $pages);
            return $pages;
          } // if $threadlength > $maxmessagedisplay
    } // topicPages()
}
