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
        
        return $this->render('templates/stats/main.template.php', $data);
    } // main()
}
