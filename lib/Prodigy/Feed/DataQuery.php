<?php
/*
 * Data query methods
 */

namespace Prodigy\Feed;

class DataQuery {
    private $app;
    private $service;
    private $request;
    
    public function __construct($router) {
        $this->app = $router->app();
        $this->db = $this->app->db;
        $this->service = $router->service();
        $this->request = $router->request();
    }
    
    public function getAnnotatedCat($sticky=0) {
        $dbprefix = $this->db->db_prefix;
        $q_before = '';
        $before = $this->service->before;
        if ($before > 0)
            $q_before = "AND f.ID_MSG < $before";
        
        if ($this->request->cat == 0 || $sticky == 2)
            $whereCat = "<> 0";
        else
            $whereCat = "= {$this->request->cat}";
        
        $qlimit = $this->service->paginateBy + 1;
        
        $posts = array();
    
        //// Get annotated messages from selected category
        $r = $this->db->query(
            "SELECT STRAIGHT_JOIN f.ID_MSG, posterTime as date, lm.memberName AS annotatedByName, IFNULL(lm.realName, lm.memberName) AS annotatedByRN, IFNULL(f.subject, m.subject) as subject, annotation, body, IFNULL(mem.realName, mem.memberName) as realname, mem.memberName as author, b.name as boardname, b.ID_CAT, b.ID_BOARD, f.sticky, f.ID_MSG AS fID
                FROM {$dbprefix}feed f
                JOIN {$dbprefix}messages m ON f.ID_MSG = m.ID_MSG
                LEFT JOIN {$dbprefix}topics t ON m.ID_TOPIC = t.ID_TOPIC
                LEFT JOIN {$dbprefix}boards b ON t.ID_BOARD = b.ID_BOARD
                LEFT JOIN {$dbprefix}members mem ON m.ID_MEMBER = mem.ID_MEMBER
                LEFT JOIN {$dbprefix}members lm ON annotatedBy = lm.ID_MEMBER
                WHERE b.ID_CAT $whereCat $q_before AND f.sticky = $sticky
                ORDER BY f.ID_MSG DESC LIMIT $qlimit"
        ) or database_error(__FILE__, __LINE__, $this->db);
        
        if ($r->num_rows == 0) {
            return $posts;
        }
        
        while ($row = $r->fetch_assoc()) {
            $posts[] = $row;
        }
    
        if ($r->num_rows > $this->service->paginateBy) {
            $this->service->next_page_available = true;
            unset($posts[$this->service->paginateBy]);
        }
        
        return $posts;
    } // getAnnotatedCat()
    
    public function getNonAnnotatedCat() {
        $dbprefix = $this->db->db_prefix;
        $q_before = '';
        $before = $this->service->before;
        if ($before > 0)
            $q_before = "AND ID_MSG < $before";
        
        if ($this->request->cat == 0)
            $whereCat = "<> 0";
        else
            $whereCat = "= {$this->request->cat}";
        
        $qlimit = $this->service->paginateBy + 1;
        
        $posts = array();
        
        //// Get non annotated messages of category
        $r = $this->db->query(
            "SELECT STRAIGHT_JOIN tmp.ID_MSG, date, IFNULL(f.subject, tmp.subject) as subject, body, author, tmp.realname, boardname, ID_CAT, ID_BOARD, annotation, lm.memberName AS annotatedByName, IFNULL(lm.realName, lm.memberName) AS annotatedByRN, f.ID_MSG AS fID
                FROM (
                    SELECT STRAIGHT_JOIN ID_MSG, posterTime as date, subject, body, mem.memberName AS author, IFNULL(mem.realName, mem.memberName) AS realname, b.name AS boardname, b.ID_CAT, b.ID_BOARD
                    FROM {$dbprefix}messages m
                    LEFT JOIN {$dbprefix}topics t ON m.ID_TOPIC = t.ID_TOPIC
                    LEFT JOIN {$dbprefix}boards b ON t.ID_BOARD = b.ID_BOARD
                    LEFT JOIN {$dbprefix}members mem ON m.ID_MEMBER = mem.ID_MEMBER
                    WHERE b.ID_CAT $whereCat $q_before
                    ORDER BY m.ID_MSG DESC LIMIT $qlimit
                ) tmp
                LEFT JOIN {$dbprefix}feed f ON f.ID_MSG = tmp.ID_MSG
                LEFT JOIN {$dbprefix}members lm ON annotatedBy = lm.ID_MEMBER
                ORDER BY tmp.ID_MSG DESC"
            ) or database_error(__FILE__, __LINE__, $this->db);
            
        if ($r->num_rows == 0) {
            return $posts;
        }
        
        while ($row = $r->fetch_assoc()) {
            if (is_null($row['fID'])) {
                //// Try to create subject from content
                $autosubject = $this->service->app->srvc->autosubject($row['subject'], $row['body']);
                if (is_array($autosubject)) {
                    $row['subject'] = "{$autosubject[0]} &#12299; {$autosubject[1]}";
                }
            }
            $posts[] = $row;
        }
        
        if ($r->num_rows > $this->service->paginateBy) {
            $this->service->next_page_available = true;
            unset($posts[$this->service->paginateBy]);
        }
        
        return $posts;
    } // getNonAnnotatedCat()
    
    public function getAnnotatedBoard() {
        //// Pagination param
        $q_before = '';
        $before = $this->service->before;
        if ($before > 0)
            $q_before = "AND f.ID_MSG < $before";
        
        $dbprefix = $this->db->db_prefix;
        
        $qlimit = $this->service->paginateBy + 1;
        
        $posts = array();
        
        //// Get annotated messages of selected board
        $r = $this->db->query(
            "SELECT STRAIGHT_JOIN f.ID_MSG, posterTime as date, lm.memberName AS annotatedByName, IFNULL(lm.realName, lm.memberName) AS annotatedByRN, IFNULL(f.subject, m.subject) as subject, annotation, body, IFNULL(mem.realName, mem.memberName) as realname, mem.memberName as author, b.name as boardname, b.ID_CAT, b.ID_BOARD, f.sticky, f.ID_MSG AS fID
                FROM {$dbprefix}feed f
                JOIN {$dbprefix}messages m ON f.ID_MSG = m.ID_MSG
                LEFT JOIN {$dbprefix}topics t ON m.ID_TOPIC = t.ID_TOPIC
                LEFT JOIN {$dbprefix}boards b ON t.ID_BOARD = b.ID_BOARD
                LEFT JOIN {$dbprefix}members mem ON m.ID_MEMBER = mem.ID_MEMBER
                LEFT JOIN {$dbprefix}members lm ON annotatedBy = lm.ID_MEMBER
                WHERE b.ID_BOARD = {$this->request->board} $q_before AND f.sticky = 0
                ORDER BY ID_MSG DESC LIMIT $qlimit"
        ) or database_error(__FILE__, __LINE__, $this->db);
        
        if ($r->num_rows == 0) {
            return $posts;
        }
        
        while ($row = $r->fetch_assoc()) {
            $posts[] = $row;
        }
    
        if ($r->num_rows > $this->service->paginateBy) {
            $this->service->next_page_available = true;
            unset($posts[$this->service->paginateBy]);
        }
        
        return $posts;
    } // getAnnotatedBoard()
    
    public function getNonAnnotatedBoard() {
        //// Pagination param
        $q_before = '';
        $before = $this->service->before;
        if ($before > 0)
            $q_before = "AND m.ID_MSG < $before";
        
        $dbprefix = $this->db->db_prefix;
        
        $qlimit = $this->service->paginateBy + 1;
        
        $posts = array();
        
            //// Get non annotated messages of selected board
            $r = $this->db->query(
                "SELECT STRAIGHT_JOIN tmp.ID_MSG, date, IFNULL(f.subject, tmp.subject) as subject, body, author, tmp.realname, boardname, ID_CAT, ID_BOARD, annotation, lm.memberName AS annotatedByName, IFNULL(lm.realName, lm.memberName) AS annotatedByRN, f.sticky, f.ID_MSG AS fID
                   FROM (
                     SELECT STRAIGHT_JOIN ID_MSG, posterTime as date, subject, body, mem.memberName AS author, IFNULL(mem.realName, mem.memberName) AS realname, b.name AS boardname, b.ID_CAT, b.ID_BOARD
                       FROM {$dbprefix}messages m
                       LEFT JOIN {$dbprefix}topics t ON m.ID_TOPIC = t.ID_TOPIC
                       LEFT JOIN {$dbprefix}boards b ON t.ID_BOARD = b.ID_BOARD
                       LEFT JOIN {$dbprefix}members mem ON m.ID_MEMBER = mem.ID_MEMBER
                     WHERE b.ID_BOARD = {$this->request->board} $q_before
                     ORDER BY m.ID_MSG DESC LIMIT $qlimit
                   ) tmp
                   LEFT JOIN {$dbprefix}feed f ON f.ID_MSG = tmp.ID_MSG
                   LEFT JOIN {$dbprefix}members lm ON annotatedBy = lm.ID_MEMBER
                   ORDER BY tmp.ID_MSG DESC"
            ) or database_error(__FILE__, __LINE__, $this->db);
        
        if ($r->num_rows == 0) {
            return $posts;
        }
        
        while ($row = $r->fetch_assoc()) {
            if (is_null($row['fID'])) {
                //// Try to create subject from content
                $autosubject = $this->service->app->srvc->autosubject($row['subject'], $row['body']);
                if (is_array($autosubject)) {
                    $row['subject'] = "{$autosubject[0]} &#12299; {$autosubject[1]}";
                }
            }
            $posts[] = $row;
        }
        
        if ($r->num_rows > $this->service->paginateBy) {
            $this->service->next_page_available = true;
            unset($posts[$this->service->paginateBy]);
        }
    
        return $posts;
    } // getNonAnnotatedBoard()
    
    public function buildPagination($posts, $mode='cat') {
        if (isset($this->request->all)) $all = true;
        else $all = false;
    
        $posts_count = count($posts);
        
        if ($this->service->next_page_available)
            $this->service->pageNext = $posts[$posts_count-1]['ID_MSG'];
        
        if ($this->service->before > 0) {
            $dbprefix = $this->db->db_prefix;
            
            if ($this->request->cat == 0)
                $whereCat = "<> 0";
            else
                $whereCat = "= {$this->request->cat}";
            
            if ($mode == 'cat') {
                $q_where = "b.ID_CAT $whereCat";
            }
            elseif ($mode == 'board') {
                $q_where = "b.ID_BOARD = {$this->request->board}";
            }
            else return;
            
            if ($all) {
                $qString =
                    "SELECT STRAIGHT_JOIN ID_MSG
                    FROM {$dbprefix}messages m
                    LEFT JOIN {$dbprefix}topics t ON m.ID_TOPIC = t.ID_TOPIC
                    LEFT JOIN {$dbprefix}boards b ON t.ID_BOARD = b.ID_BOARD
                    WHERE $q_where AND m.ID_MSG > {$this->service->before}
                    ORDER BY ID_MSG LIMIT {$this->service->paginateBy}";
            } else {
                $qString = 
                    "SELECT STRAIGHT_JOIN f.ID_MSG
                    FROM {$dbprefix}feed f
                    JOIN {$dbprefix} messages m ON m.ID_MSG = f.ID_MSG
                    LEFT JOIN {$dbprefix}topics t ON m.ID_TOPIC = t.ID_TOPIC
                    LEFT JOIN {$dbprefix}boards b ON t.ID_BOARD = b.ID_BOARD
                    WHERE $q_where AND f.ID_MSG > {$this->service->before} and f.sticky = 0
                    ORDER BY f.ID_MSG LIMIT {$this->service->paginateBy}";
            }
            
            //// get prev page 
            $r = $this->db->query($qString) or database_error(__FILE__, __LINE__, $this->db);
        
            $prev_pages = array();
            while ($row = $r->fetch_row()) {
                $prev_pages[] = $row[0];
            }
            $prev_pages = array_reverse($prev_pages);
        
            if ($r->num_rows == $this->service->paginateBy) {
                //// it's not first page, return link to previous page
                $this->service->pagePrev = $prev_pages[0];
            } else {
                //// Link to start page
                $this->service->pagePrev = -1;
            }
        } // $before > 0
    } // buildPagination()
    
}

?>
