<?php
namespace Prodigy\Respond;

class Comments extends Respond
{
    /**
     * Prepare comments data
     * @param string $comments   raw comments data
     * @param string $autonr     message author
     * @param bool   $cubscribed are comments subscribed?
     * @return array
     */
    public function prepare($comments, $author, $subscribed = null)
    {
        $csvdata = is_array($comments) ? $comments : explode("\r\n", $comments);
        $boardStr = (!isset($board) or empty($board) or $board == null or $board == "")? -1 : $board;
        
        $out = array();
        $out['comment_count'] = 0;
        $out['remaining_char_count'] = 0;
        $out['comments'] = array();
        $out['authors'] = array();
        
        $csvLength = count($csvdata) - 1;
        foreach ( $csvdata as $lineNr => $csvline )
        {
            if (empty($csvline)) continue;
        
            $out['comment_count']++;
            $out['remaining_char_count'] += strlen($csvline);
            
            $data = explode("#;#", $csvline);
            
            if(sizeof($data) < 4)
                continue;
            
            // Skip comments of ignored users
            if ($this->app->user->inIgnore($data[1]))
                continue;
            
            $ln = $lineNr + 1;
            
            $out['comments'][$ln] = array(
                'username' => $data[1],
                'realname' => $data[0],
                'date'     => $data[2],
                'comment'  => $data[3],
                'userinfo' => $this->app->user->loadDisplay($data[1]),
                'allow_modify' => ($this->app->user->name == $data[1] && $ln == $csvLength)
            );
            $out['authors'][] = $data[1];
        }
        
        $out['subscribed'] = ($subscribed or ($subscribed === null and ($author == $this->app->user->name or in_array($this->app->user->name, $out['authors']))));
        
        $out['remaining_char_count'] = 10000 - $out['remaining_char_count'];

        return $out;
    } // prepare()
    
    public function subscribed($request, $response, $service, $app)
    {
        if ($app->user->guest)
            return $this->error($app->locale->txt[1]);
        
        $app->board->load(-1);
        
        $POST = $request->paramsPost();
        
        $messageNumber = (int) $POST->viewscount;
        if ($messageNumber < 1)
            $messageNumber = 10;
        
        $this->clearNotifications($app->user->id);
        
        $data = array(
            'title' => 'Комментарии, на которые ты подписан',
            'ubbc' => $app->conf->enable_ubbc,
            'notification' => $app->conf->enable_notification,
            'username' => $app->user->name,
            'realname' => $app->user->realname,
            'messageNumber' => $messageNumber,
            'messages' => array()
        );
        
        $db_prefix = $app->db->prefix;
        
        $dbst = $app->db->prepare("SELECT DISTINCT * FROM
            (SELECT m.*,t.numReplies,c.memberGroups,c.name as cname,b.name as bname,b.ID_BOARD,IFNULL(mem.blockComments,0) AS blockComments,cs.notify AS subscribedToComments
            FROM {$db_prefix}messages as m
            JOIN {$db_prefix}topics as t ON (m.ID_TOPIC = t.ID_TOPIC)
            JOIN {$db_prefix}boards as b ON (t.ID_BOARD = b.ID_BOARD)
            JOIN {$db_prefix}categories as c ON (b.ID_CAT = c.ID_CAT)
            LEFT JOIN {$db_prefix}members as mem ON (m.ID_MEMBER = mem.ID_MEMBER)
            LEFT JOIN {$db_prefix}comment_subscriptions AS cs ON (m.ID_MSG = cs.messageID AND cs.memberID = ?)
            WHERE m.ID_MEMBER <> ?
            AND m.comments LIKE ?
            AND cs.notify <> 0
            UNION
            SELECT m.*,t.numReplies,c.memberGroups,c.name as cname,b.name as bname,b.ID_BOARD,IFNULL(mem.blockComments,0) AS blockComments,cs.notify AS subscribedToComments
            FROM {$db_prefix}messages as m
            JOIN {$db_prefix}topics as t ON (m.ID_TOPIC = t.ID_TOPIC)
            JOIN {$db_prefix}boards as b ON (t.ID_BOARD = b.ID_BOARD)
            JOIN {$db_prefix}categories as c ON (b.ID_CAT = c.ID_CAT)
            LEFT JOIN {$db_prefix}members as mem ON (m.ID_MEMBER = mem.ID_MEMBER)
            JOIN {$db_prefix}comment_subscriptions AS cs ON (cs.messageID = m.ID_MSG AND cs.memberID = ?)
            WHERE cs.notify = 1
            AND m.ID_MEMBER <> ?) AS others
            ORDER BY last_comment_time DESC LIMIT $messageNumber");
        $dbst->execute(array($app->user->id, $app->user->id, "%#;#{$app->user->name}#;#%", $app->user->id, $app->user->id));
        
        // mysqli returns error on this request:
        // (HY001/1038): Out of sort memory, consider increasing server sort buffer size
        // But PDO doesn't raise exception and just returns empty result.
        // setting "sort_buffer_size = 512K" in my.cnf helps, but why PDO ignores this error?
        // This is probably a PDO bug.
        
        while ($row = $dbst->fetch())
        {
            if ($app->user->inIgnore($row['posterName']))
                continue;
            $row['body'] = $app->subs->censorTxt($row['body']);
            $row['subject'] = $app->subs->censorTxt($row['subject']);
            $row['comments'] = $this->prepare($row['comments'], $row['posterName'], $row['subscribedToComments']);
            $row['posterTime'] = $app->subs->timeformat($row['posterTime']);
            $row['cmnt_display'] = $row['closeComments'] == 1 ? 'none' : 'inline';
            $data['messages'][] = $row;
        }
        $dbst = null; // closing this statement
        
        $this->addJS('ubbc.js');
        return $this->render('templates/comments/subscribed.template.php', $data);
    } // subscribed()
    
    public function commentsTo($request, $response, $service, $app)
    {
        if ($app->user->guest)
            return $this->error($app->locale->txt[1]);
        
        $app->board->load(-1);
        
        $POST = $request->paramsPost();
        
        $messageNumber = (int) $POST->viewscount;
        if ($messageNumber < 1)
            $messageNumber = 10;
        
        // User, comments addressed to
        $user = $app->user->loadDisplay($request->paramsNamed()->get('user'));
        
        if (!$user['found'])
            return $this->error($app->locale->txt[40]);
        
        if ($app->user->inIgnore($user['name']))
            return $this->error($app->locale->txt['ignore_user1']);
        
        $db_prefix = $app->db->prefix;
        
        // set to 0 number of unread comments left by other users under your messages
        if ($user == $app->user->name)
            $app->db->query("UPDATE {$db_prefix}log_topics SET unreadComments = 0 WHERE ID_MEMBER={$app->user->id}");
        
        $data = array(
            'title' => "Последние комментарии к сообщениям {$user['realName']}",
            'ubbc' => $app->conf->enable_ubbc,
            'notification' => $app->conf->enable_notification,
            'username' => $user['name'],
            'realname' => $user['realName'],
            'messageNumber' => $messageNumber,
            'messages' => array()
        );
        
        $permit = 0;
        if ($app->user->isStaff())
            $permit = 1;
        
        $dbst = $app->db->prepare("
            SELECT m.*,t.numReplies,c.memberGroups,c.name as cname,b.name as bname,b.ID_BOARD,m.comments COMMENTS,m.ID_MSG ID_MSG,IFNULL(mem.blockComments,0) AS blockComments,m.closeComments,m.ID_MEMBER, cs.notify
            FROM {$db_prefix}messages as m
            JOIN {$db_prefix}topics as t ON (m.ID_TOPIC=t.ID_TOPIC)
            JOIN {$db_prefix}boards as b ON (t.ID_BOARD=b.ID_BOARD)
            JOIN {$db_prefix}categories as c ON (b.ID_CAT=c.ID_CAT)
            LEFT JOIN {$db_prefix}members as mem ON (mem.ID_MEMBER=m.ID_MEMBER)
            LEFT JOIN {$db_prefix}comment_subscriptions AS cs ON (m.ID_MSG = cs.messageID AND cs.memberID = ?)
            WHERE m.ID_MEMBER=?
                AND (FIND_IN_SET(?,c.memberGroups) != 0 || $permit || c.memberGroups='')
                AND m.comments <> ''
                AND m.last_comment_time IS NOT NULL
            ORDER BY m.last_comment_time DESC LIMIT $messageNumber");
        
        $dbst->execute(array($app->user->id, $user['ID_MEMBER'], $app->user->group));
        
        while ($row = $dbst->fetch())
        {
            if ($app->user->inIgnore($row['posterName']))
                continue;
            $row['body'] = $app->subs->censorTxt($row['body']);
            $row['subject'] = $app->subs->censorTxt($row['subject']);
            $row['comments'] = $this->prepare($row['comments'], $row['posterName'], $row['notify']);
            $row['posterTime'] = $app->subs->timeformat($row['posterTime']);
            $row['cmnt_display'] = $row['closeComments'] == 1 ? 'none' : 'inline';
            $data['messages'][] = $row;
        }
        $dbst = null; // closing this statement
        
        $this->addJS('ubbc.js');
        return $this->render('templates/comments/subscribed.template.php', $data);
    } // commentsTo()
    
    public function commentsBy($request, $response, $service, $app)
    {
        if ($app->user->guest)
            return $this->error($app->locale->txt[1]);
        
        $app->board->load(-1);
        
        $POST = $request->paramsPost();
        
        $messageNumber = (int) $POST->viewscount;
        if ($messageNumber < 1)
            $messageNumber = 10;
        
        // User, comments addressed to
        $user = $app->user->loadDisplay($request->paramsNamed()->get('user'));
        
        if (!$user['found'])
            return $this->error($app->locale->txt[40]);
        
        if ($app->user->inIgnore($user['name']))
            return $this->error($app->locale->txt['ignore_user1']);
        
        $db_prefix = $app->db->prefix;
        
        // set to 0 number of unread comments left by other users under your messages
        if ($user == $app->user->name)
            $app->db->query("UPDATE {$db_prefix}log_topics SET unreadComments = 0 WHERE ID_MEMBER={$app->user->id}");
        
        $data = array(
            'title' => "Комментарии от {$user['realName']}",
            'ubbc' => $app->conf->enable_ubbc,
            'notification' => $app->conf->enable_notification,
            'username' => $user['name'],
            'realname' => $user['realName'],
            'messageNumber' => $messageNumber,
            'messages' => array()
        );
        
        $permit = 0;
        if ($app->user->isStaff())
            $permit = 1;
        
        $dbst = $app->db->prepare("
            SELECT STRAIGHT_JOIN m.*,t.numReplies,c.memberGroups,c.name as cname,b.name as bname,b.ID_BOARD,m.comments,m.ID_MSG,IFNULL(mem.blockComments,0) AS blockComments,m.closeComments,cs.notify AS subscribedToComments
                FROM {$db_prefix}messages as m
                JOIN {$db_prefix}topics as t ON (m.ID_TOPIC = t.ID_TOPIC)
                JOIN {$db_prefix}boards as b ON (t.ID_BOARD = b.ID_BOARD)
                JOIN {$db_prefix}categories as c ON (b.ID_CAT = c.ID_CAT)
                LEFT JOIN {$db_prefix}members as mem ON (m.ID_MEMBER = mem.ID_MEMBER)
                LEFT JOIN {$db_prefix}comment_subscriptions AS cs ON (cs.messageID = m.ID_MSG AND cs.memberID = ?)
                WHERE m.comments LIKE ?
                ORDER BY m.last_comment_time DESC LIMIT $messageNumber");
        $dbst->execute(array($user['ID_MEMBER'], "%#;#{$user['memberName']}#;#%"));
        
        while ($row = $dbst->fetch())
        {
            if ($app->user->inIgnore($row['posterName']))
                continue;
            $row['body'] = $app->subs->censorTxt($row['body']);
            $row['subject'] = $app->subs->censorTxt($row['subject']);
            $row['comments'] = $this->prepare($row['comments'], $row['posterName'], $row['subscribedToComments']);
            $row['posterTime'] = $app->subs->timeformat($row['posterTime']);
            $row['cmnt_display'] = $row['closeComments'] == 1 ? 'none' : 'inline';
            $data['messages'][] = $row;
        }
        $dbst = null; // closing this statement
        
        $this->addJS('ubbc.js');
        return $this->render('templates/comments/subscribed.template.php', $data);
    } // commentsBy
    
    /**
     * Clears the list of notifications for new message comments for the user.
     * @param $memberID ID of the member.
     */
    public function clearNotifications($memberID)
    {
        if (!empty($memberID) and $memberID > -1)
            $this->app->db->prepare("UPDATE LOW_PRIORITY {$this->app->db->prefix}log_topics " . 
                "SET subscribedComments = 0, otherComments = 0 " .
                "WHERE ID_MEMBER = ?")->
                execute(array($memberID));
    } // clearNotifications()
}
