<?php

namespace Prodigy\Admin;

// Admin controller
Class Admin extends \Prodigy\Respond\Respond {
    
    // Bans list controller
    public function bans($request, $response, $service, $app)
    {
        if (!$app->user->isStaff())
            return $this->error(null, 403);
        
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
        
        $db_prefix = $app->db->db_prefix;
        
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
        
        
        // Get requested member detail and fill fields with his info
        if (!empty($GET->memid))
        {
            $dbst = $app->db->prepare("
                SELECT * FROM {$db_prefix}members
                WHERE ID_MEMBER = ?
                LIMIT 1");
            $dbst->execute(array($GET->memid));
            $aMember = $dbst->fetch();
        }
        
        return $this->render('admin/bans.phtml',
            array(
                'aDesc' => $aDesc,
                'banned_members' => $banned_members,
            )
        );
        
    } // bans()
}