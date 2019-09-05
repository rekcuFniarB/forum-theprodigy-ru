<?php
namespace Prodigy\Respond;

class Stats extends Respond
{
    public function main($request, $response, $service, $app)
    {
        if ($app->user->guest && !$app->conf->show_stats_to_guest)
            return $this->error($app->locale->txt[1]);
        
        $data = array(
            'title' => "{$app->conf->mbname} - {$app->locale->yse_stats_1}"
        );
        
        $db_prefix = $app->db->prefix;
        
        $dbst = $app->db->query("SELECT memberName, realName FROM {$db_prefix}members ORDER BY dateRegistered DESC LIMIT 1");
        $thelatestmember = $dbst->fetch();
        $dbst = null;
        
        $thelatestmember['realName'] = (!isset($thelatestmember['realName']) || $thelatestmember['realName'] == '') ? $thelatestmember['memberName'] : $thelatestmember['realName'];
        $data['thelatestmember'] = $thelatestmember;
        
        $dbst = $app->db->query("SELECT COUNT(*) AS memcount FROM {$db_prefix}members");
        $data['memcount'] = $dbst->fetchColumn();
        $dbst = null;
        
        $dbst = $app->db->query("SELECT COUNT(*) AS totalm FROM {$db_prefix}messages");
        $data['totalm'] = $dbst->fetchColumn();
        $dbst = null;
        
        $dbst = $app->db->query("SELECT COUNT(*) AS totalt FROM {$db_prefix}topics");
        $data['totalt'] = $dbst->fetchColumn();
        $dbst = null;
        
        $dbst = $app->db->query("SELECT COUNT(*) AS totalb FROM {$db_prefix}boards");
        $data['numboards'] = $dbst->fetchColumn();
        $dbst = null;
        
        $dbst = $app->db->query("SELECT COUNT(*) AS totalc FROM {$db_prefix}categories");
        $data['numcats'] = $dbst->fetchColumn();
        $dbst = null;
        
        $dbst = $app->db->query("SELECT value FROM {$db_prefix}settings WHERE variable='mostOnline'");
        $data['mostonline'] = $dbst->fetchColumn();
        $dbst = null;
        
        $dbst = $app->db->query("SELECT value FROM {$db_prefix}settings WHERE variable='mostDate'");
        $data['mostdate'] = $app->subs->timeformat($dbst->fetchColumn());
        $dbst = null;
        
        // Top members
        $dbst = $app->db->query("SELECT memberName, IFNULL(realName, memberName) as realName, posts FROM {$db_prefix}members ORDER BY posts DESC LIMIT 10");
        $data['top_members'] = $dbst->fetchAll();
        $dbst = null;
        
        // Board top 10
        $qparams = array();
        if ($app->user->isStaff())
            $condition = '1';
        else
        {
            $condition = "(FIND_IN_SET(?, c.memberGroups) != 0 || c.memberGroups='')";
            $qparams[] = $app->user->group;
        }
        
        $dbst = $app->db->prepare("
            SELECT DISTINCT b.ID_BOARD, b.name, b.numPosts
            FROM {$db_prefix}categories AS c
            LEFT JOIN {$db_prefix}boards AS b ON (b.ID_CAT=c.ID_CAT)
            WHERE $condition
            ORDER BY b.numPosts DESC LIMIT 10");
        $dbst->execute($qparams);
        $data['top_boards'] = $dbst->fetchAll();
        $dbst = null;
        
        // Topic replies top 10
        $dbst = $app->db->prepare("
            SELECT m.subject, t.ID_TOPIC, t.ID_BOARD, t.numReplies
            FROM {$db_prefix}topics AS t,{$db_prefix}messages AS m, {$db_prefix}messages AS mes, {$db_prefix}boards AS b, {$db_prefix}categories AS c
            WHERE m.ID_MSG=t.ID_FIRST_MSG 
                AND mes.ID_MSG=t.ID_LAST_MSG 
                AND b.ID_BOARD=t.ID_BOARD 
                AND c.ID_CAT=b.ID_CAT
                AND $condition
            ORDER BY t.numReplies DESC LIMIT 10");
        $dbst->execute($qparams);
        $data['topic_replies'] = $dbst->fetchAll();
        $dbst = null;
        
        // Topic views top 10
        $dbst = $app->db->prepare("
            SELECT m.subject, t.ID_TOPIC, t.ID_BOARD, t.numViews
            FROM {$db_prefix}topics AS t,{$db_prefix}messages AS m, {$db_prefix}messages AS mes , {$db_prefix}boards AS b, {$db_prefix}categories AS c
	WHERE m.ID_MSG=t.ID_FIRST_MSG 
            AND mes.ID_MSG=t.ID_LAST_MSG
            AND t.ID_BOARD=b.ID_BOARD
            AND c.ID_CAT=b.ID_CAT
            AND $condition
            ORDER BY t.numViews DESC LIMIT 10");
        $dbst->execute($qparams);
        $data['topic_views'] = array();
        while ( $row = $dbst->fetch())
        {
            $row['subject'] = $app->subs->censorTxt($row['subject']);
            $data['topic_views'][] = $row;
        }
        $dbst = null;
        
        // Days
        $dbst = $app->db->query("SELECT * FROM {$db_prefix}log_activity ORDER BY year DESC, month DESC, day DESC LIMIT 30");
        $data['days'] = $dbst->fetchAll();
        $dbst = null;
        
        // Months
        $dbst = $app->db->query("SELECT month, year, SUM(hits) AS shit, SUM(registers) AS sreg, SUM(topics) AS stop, SUM(posts) AS spos, MAX(mostOn) AS mOn FROM {$db_prefix}log_activity GROUP BY year, month ORDER BY year DESC, month DESC LIMIT 30");
        $data['months'] = array();
        while ($row = $dbst->fetch())
        {
            $m = $row['month']-1;
            $row['monthy'] = $app->locale->monthy[$m];
            $data['months'][] = $row;
        }
        $dbst = null;
        
        $data['linktree'] = array(array('url' => '/stats/', 'name' => $app->locale->yse_stats_1));
        
        return $this->render('stats/main.template.php', $data);
    } // main()
    
    protected function memberCount()
    {
        $db_prefix = $this->app->db->prefix;
        $dbst = $this->app->db->query("SELECT COUNT(*) FROM {$db_prefix}members");
        $num = $dbst->fetchColumn();
        $dbst = null;
        return $num;
    }
    
    protected function maxPosts()
    {
        $db_prefix = $this->app->db->prefix;
        $dbst = $this->app->db->query("SELECT MAX(posts) FROM {$db_prefix}members");
        $num = $dbst->fetchColumn();
        $dbst = null;
        $num = $num == 0 ? 1 : $num;
        return $num;
    }
    
    protected function getMemberList($query, $params = array(), $isByMG = false)
    {
        $MOST_POSTS = $this->maxPosts();
        
        $db_prefix = $this->app->db->prefix;
        
        $dbst = $this->app->db->prepare($query);
        $dbst->execute($params);
        $members = array();
        while ($row = $dbst->fetch())
        {
            if ($this->app->user->OnlineStatus($row['ID_MEMBER']) > 0)
                $row['online'] = $this->app->locale->txt['online6'];
            else
                $row['online'] = $this->app->locale->txt['online7'];
            
            $row['barchart'] = round(($row['posts'] / $MOST_POSTS) * 100);
            if ($row['barchart'] <= 0)
                $row['barchart'] = 1;
            
            $row['memberGroup'] = (isset($row['memberGroup']) ? $row['memberGroup'] : '');
            //Administrator & Global Moderator position shows members description instead of membergroups description
            $row['membergroup'] = $row['memberGroup'];
            $mg_dbst = $this->app->db->query("SELECT membergroup FROM {$db_prefix}membergroups ORDER BY ID_GROUP"); //query membergroups descriptions
            $membergroups = array();
            $membergroups = $mg_dbst->fetchAll(\PDO::FETCH_COLUMN);
            $mg_dbst = null;
            if ($row['membergroup'] == 'Administrator')
                $row['membergroup'] = $membergroups[0]; //admin description
            elseif ($row['membergroup'] == 'Global Moderator')
                $row['membergroup'] = $membergroups[7]; //Global moderator description
            
            if ($row['hideEmail'] && !$this->app->user->isStaff() && $this->app->conf->allow_hide_email)
                $row['emailAddress'] = "";
            
            if ($row['posts'] > 100000)
                $row['posts'] = $this->app->locale->txt[683];
            
            $members[] = $row;
        } // while fetch()
        $dbst = null;
        return $members;
    } // getMemberList()
    
    protected function preparePagination($start, $count)
    {
        $c = 0;
        $pages = array();
        while (($c * $this->app->conf->MembersPerPage) < $count)
        {
            $viewc = $c + 1;
            $strt = $c * $this->app->conf->MembersPerPage;
            if ($strt == $start)
                $pages[$strt] = $viewc;
            else
                $pages[$strt] = array($strt, $viewc);
            $c++;
        }
        return $pages;
    }
    
    public function membersall($request, $response, $service, $app)
    {
        if ($app->user->guest)
            return $this->error($app->locale->txt[223]);
        
        $db_prefix = $app->db->prefix;
        
        $GET = $request->paramsGet();
        
        # Get the number of members
        $memcount = $this->memberCount();
        
        $start = $GET->start;
        
        if (empty($start))
            $start = 0;
        
        $numbegin = $start / $app->conf->MembersPerPage + 1;
        $numend = ceil($memcount / $app->conf->MembersPerPage);
        if ($numend > $memcount)
            $numend = $memcount;

        // Preparing pagination
        $pages = $this->preparePagination($start, $memcount);
        
        $data = array(
            'title' => "{$app->locale->txt[308]} $numbegin {$app->locale->txt[311]} $numend",
            'pages' => $pages,
            'numbegin' => $numbegin,
            'memcount' => $memcount,
            'numend' => $numend,
            'start' => $start,
            'sort' => $request->paramsNamed()->get('sort'),
            'members' => $this->getMemberList(
                "SELECT memberName,realName,websiteTitle,websiteUrl,
                posts,memberGroup,ICQ,AIM,YIM,MSN,
                emailAddress,hideEmail,ID_MEMBER
                FROM {$db_prefix}members WHERE 1 LIMIT ?,?",
                array($start,$app->conf->MembersPerPage)
            )
        );
        
        return $this->render('stats/allmembers.template.php', $data);
    } // members()
    
    public function membersByGroup($request, $response, $service, $app)
    {
        $sort = $request->paramsNamed()->get('sort');
        $GET = $request->paramsGet();
        
        // show forum staff only if requested
        if ($sort == 'staff')
            $staff = true;
        else
            $staff = false;
        
        if ($app->user->guest)
            return $this->error($app->locale->txt[223]);
        
        $db_prefix = $app->db->prefix;
        
        # Get the number of members
        $memcount = $this->memberCount();
        
        $start = $GET->start;
        
        if (empty($start))
            $start = 0;
        
        
        // Get the number of members
        $dbst = $app->db->query("SELECT COUNT(*) FROM {$db_prefix}members WHERE memberGroup != ''");
        $memcount = $dbst->fetchColumn();
        $dbst = null;
        
        $numbegin = $start / $app->conf->MembersPerPage + 1;
        $numend = ceil($memcount / $app->conf->MembersPerPage);
        if ($numend > $memcount)
            $numend = $memcount;
        
        if ($staff)
        {
            $title = $app->locale->yse45;
            $sql_clause = "memberGroup = 'Administrator' OR memberGroup = 'Global Moderator'";
            $sql_limit = '';
        }
        else
        {
            $title = "{$app->locale->txt[308]} $numbegin {$app->locale->txt[311]} $numend";
            $sql_clause = '1';
            $sql_limit = "LIMIT ?,?";
        }
        
        $membergroups = $app->user->memberGroups();
        $query = "
            SELECT memberName,realName,websiteTitle,websiteUrl,posts,IF(memberGroup='Administrator',?,IF(memberGroup='Global Moderator',?,memberGroup)) AS memberGroup,ICQ,YIM,AIM,MSN,emailAddress,hideEmail,IF(memberGroup<>'',1,0) AS belongsToMembergroup, ID_MEMBER
            FROM {$db_prefix}members
            WHERE $sql_clause
            ORDER BY belongsToMembergroup DESC,memberGroup
            $sql_limit";
        $qparams = array($membergroups[0], $membergroups[7]);
        if (!$staff)
        {
            $qparams[] = $start;
            $qparams[] = $app->conf->MembersPerPage;
        }
        
        $data = array(
            'title' => $title,
            'numbegin' => $numbegin,
            'memcount' => $memcount,
            'numend' => $numend,
            'start' => $start,
            'sort' => $sort,
            'members' => $this->getMemberList($query, $qparams)
        );
        
        if (!$staff)
            $data['pages'] = $this->preparePagination($start, $memcount);
        
        return $this->render('stats/allmembers.template.php', $data);
    } // membersByGroup()
    
    public function topmembers($request, $response, $service, $app)
    {
        if ($app->user->guest)
            return $this->error($app->locale->txt[223]);

        $service->title = "{$app->locale->txt[313]} {$app->conf->TopAmmount} {$app->locale->txt[314]}";
        $service->sort = $request->paramsNamed()->get('sort');
        
        $db_prefix = $app->db->prefix;
        $query = "SELECT * FROM {$db_prefix}members WHERE 1 ORDER BY posts DESC LIMIT ?";
        $service->members = $this->getMemberList($query, array($app->conf->TopAmmount));
        return $this->render('stats/allmembers.template.php');
    } // topmembers()
    
    public function membersByLetter($request, $response, $service, $app)
    {
        if ($app->user->guest)
            return $this->error($app->locale->txt[223]);
        
        $GET = $request->paramsGet();
        
        $service->title = $app->locale->txt[312];
        $service->sort = $request->paramsNamed()->get('sort');
        
        $qparams = array();
        
        $db_prefix = $app->db->prefix;
        
        if ($GET->letter !== null && $GET->letter != '')
        {
            $query = "SELECT * FROM {$db_prefix}members WHERE (SUBSTRING(realName,1,1)=?) ORDER BY realName";
            $qparams[] = $GET->letter;
        }
        else
            $query = "
                SELECT *
                FROM {$db_prefix}members
                WHERE ((LOWER(SUBSTRING(realName,1,1)) NOT BETWEEN 'a' AND 'z') AND (SUBSTRING(realName,1,1) NOT BETWEEN '0' AND '9'))
                ORDER BY realName";
        
        $service->members = $this->getMemberList($query, $qparams);
        if(!$service->members)
            $service->notfound = true;
        
        return $this->render('stats/allmembers.template.php');
    } // membersByLetter()
    
    public function allmypeople($request, $response, $service, $app)
    {
        return $this->redirect('/allmypeople/');
    }
}
