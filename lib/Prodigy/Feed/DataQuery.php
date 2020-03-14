<?php
/*
 * Data query methods (models)
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
        $q_params = array();
        
        if ($this->request->cat == 0 || $sticky == 2) {
            $whereCat = "<> 0";
        }
        else {
            $whereCat = "= ?";
            $q_params[] = $this->request->cat;
        }
        if ($before > 0) {
            $q_before = "AND f.ID_MSG < ?";
            $q_params[] = $before;
        }
        
        $q_params[] = $sticky;
        $qlimit = $this->service->paginateBy + 1;
        $q_params[] = $qlimit;
        
        $posts = array();
    
        //// Get annotated messages from selected category
        $r = $this->db->prepare(
            "SELECT STRAIGHT_JOIN f.ID_MSG, posterTime as date, lm.memberName AS annotatedByName, IFNULL(lm.realName, lm.memberName) AS annotatedByRN, IFNULL(f.subject, m.subject) as subject, annotation, body, m.status, IFNULL(mem.realName, mem.memberName) as realname, mem.memberName as author, b.name as boardname, b.ID_CAT, b.ID_BOARD, f.sticky, f.ID_MSG AS fID
                FROM {$dbprefix}feed f
                JOIN {$dbprefix}messages m ON f.ID_MSG = m.ID_MSG
                LEFT JOIN {$dbprefix}topics t ON m.ID_TOPIC = t.ID_TOPIC
                LEFT JOIN {$dbprefix}boards b ON t.ID_BOARD = b.ID_BOARD
                LEFT JOIN {$dbprefix}members mem ON m.ID_MEMBER = mem.ID_MEMBER
                LEFT JOIN {$dbprefix}members lm ON annotatedBy = lm.ID_MEMBER
                WHERE b.ID_CAT $whereCat $q_before AND f.sticky = ?
                ORDER BY f.ID_MSG DESC LIMIT ?"
        );
        $r->execute($q_params);
        $posts = $r->fetchAll();
        $r = null;
        
        if (!$posts) {
            return array();
        }
        
        if (count($posts) > $this->service->paginateBy) {
            $this->service->next_page_available = true;
            unset($posts[$this->service->paginateBy]);
        }
        
        return $posts;
    } // getAnnotatedCat()
    
    public function getNonAnnotatedCat() {
        $dbprefix = $this->db->db_prefix;
        $q_before = '';
        $before = $this->service->before;
        $q_args = array();
        
        if ($this->request->cat == 0)
            $whereCat = "<> 0";
        else {
            $whereCat = "= ?";
            $q_args[] = $this->request->cat;
        }
        
        if ($before > 0) {
            $q_before = "AND ID_MSG < ?";
            $q_args[] = $before;
        }
        
        $q_args[] = $this->service->paginateBy + 1;
        
        $posts = array();
        
        //// Get non annotated messages of category
        $r = $this->db->prepare(
            "SELECT STRAIGHT_JOIN tmp.ID_MSG, date, IFNULL(f.subject, tmp.subject) as subject, body, tmp.status, author, tmp.realname, boardname, ID_CAT, ID_BOARD, annotation, lm.memberName AS annotatedByName, IFNULL(lm.realName, lm.memberName) AS annotatedByRN, f.ID_MSG AS fID
                FROM (
                    SELECT STRAIGHT_JOIN ID_MSG, posterTime as date, subject, body, m.status, mem.memberName AS author, IFNULL(mem.realName, mem.memberName) AS realname, b.name AS boardname, b.ID_CAT, b.ID_BOARD
                    FROM {$dbprefix}messages m
                    LEFT JOIN {$dbprefix}topics t ON m.ID_TOPIC = t.ID_TOPIC
                    LEFT JOIN {$dbprefix}boards b ON t.ID_BOARD = b.ID_BOARD
                    LEFT JOIN {$dbprefix}members mem ON m.ID_MEMBER = mem.ID_MEMBER
                    WHERE b.ID_CAT $whereCat $q_before
                    ORDER BY m.ID_MSG DESC LIMIT ?
                ) tmp
                LEFT JOIN {$dbprefix}feed f ON f.ID_MSG = tmp.ID_MSG
                LEFT JOIN {$dbprefix}members lm ON annotatedBy = lm.ID_MEMBER
                ORDER BY tmp.ID_MSG DESC"
            );
        $r->execute($q_args);
        $rows = $r->fetchAll();
        $r = null;
        if (empty($rows)) {
            return $posts; // empty array
        }
        
        foreach ($rows as $row) {
            if (is_null($row['fID'])) {
                //// Try to create subject from content
                $autosubject = $this->app->feedsrvc->autosubject($row['subject'], $row['body']);
                if (is_array($autosubject)) {
                    $row['subject'] = "{$autosubject[0]} &#12299; {$autosubject[1]}";
                }
            }
            
            $posts[] = $row;
        }
        
        if (count($posts) > $this->service->paginateBy) {
            $this->service->next_page_available = true;
            unset($posts[$this->service->paginateBy]);
        }
        
        return $posts;
    } // getNonAnnotatedCat()
    
    public function getAnnotatedBoard() {
        //// Pagination param
        $q_before = '';
        $before = $this->service->before;
        $q_params = array();
        $q_params[] = $this->request->board;
        
        if ($before > 0) {
            $q_before = "AND f.ID_MSG < ?";
            $q_params[] = $before;
        }
        $dbprefix = $this->db->db_prefix;
        
        $q_params[] = $this->service->paginateBy + 1;
        
        $posts = array();
        
        //// Get annotated messages of selected board
        $r = $this->db->prepare(
            "SELECT STRAIGHT_JOIN f.ID_MSG, posterTime as date, lm.memberName AS annotatedByName, IFNULL(lm.realName, lm.memberName) AS annotatedByRN, IFNULL(f.subject, m.subject) as subject, annotation, body, m.status, IFNULL(mem.realName, mem.memberName) as realname, mem.memberName as author, b.name as boardname, b.ID_CAT, b.ID_BOARD, f.sticky, f.ID_MSG AS fID
                FROM {$dbprefix}feed f
                JOIN {$dbprefix}messages m ON f.ID_MSG = m.ID_MSG
                LEFT JOIN {$dbprefix}topics t ON m.ID_TOPIC = t.ID_TOPIC
                LEFT JOIN {$dbprefix}boards b ON t.ID_BOARD = b.ID_BOARD
                LEFT JOIN {$dbprefix}members mem ON m.ID_MEMBER = mem.ID_MEMBER
                LEFT JOIN {$dbprefix}members lm ON annotatedBy = lm.ID_MEMBER
                WHERE b.ID_BOARD = ? $q_before AND f.sticky = 0
                ORDER BY ID_MSG DESC LIMIT ?"
        );
        $r->execute($q_params);
        $posts = $r->fetchAll();
        $r = null;
        
        if (!$posts) {
            return array();
        }
        
        if (count($posts) > $this->service->paginateBy) {
            $this->service->next_page_available = true;
            unset($posts[$this->service->paginateBy]);
        }
        
        return $posts;
    } // getAnnotatedBoard()

    public function getPostsByTopic($topic) {
        //// Pagination param
        $q_before = '';
        $before = $this->service->before;
        $q_params = array($topic);
        
        if ($before > 0) {
            $q_before = "AND f.ID_MSG < ?";
            $q_params[] = $before;
        }
        $dbprefix = $this->db->db_prefix;
        
        $q_params[] = $this->service->paginateBy + 1;
        
        $posts = array();
        
        //// Get articles by specified topic
        $r = $this->db->prepare(
            "SELECT STRAIGHT_JOIN f.ID_MSG, posterTime as date, lm.memberName AS annotatedByName, IFNULL(lm.realName, lm.memberName) AS annotatedByRN, IFNULL(f.subject, m.subject) as subject, annotation, body, m.status, IFNULL(mem.realName, mem.memberName) as realname, mem.memberName as author, b.name as boardname, b.ID_CAT, b.ID_BOARD, f.sticky, f.ID_MSG AS fID
                FROM {$dbprefix}feed f
                JOIN {$dbprefix}messages m ON f.ID_MSG = m.ID_MSG
                LEFT JOIN {$dbprefix}topics t ON m.ID_TOPIC = t.ID_TOPIC
                LEFT JOIN {$dbprefix}boards b ON t.ID_BOARD = b.ID_BOARD
                LEFT JOIN {$dbprefix}members mem ON m.ID_MEMBER = mem.ID_MEMBER
                LEFT JOIN {$dbprefix}members lm ON annotatedBy = lm.ID_MEMBER
                WHERE m.ID_TOPIC = ? $q_before AND f.sticky = 0
                ORDER BY f.ID_MSG DESC LIMIT ?"
        );
        $r->execute($q_params);
        $posts = $r->fetchAll();
        $r = null;
        
        if (!$posts) {
            return array();
        }
        
        if (count($posts) > $this->service->paginateBy) {
            $this->service->next_page_available = true;
            unset($posts[$this->service->paginateBy]);
        }
        
        return $posts;
    } // getPostsByTopic()
    
    public function getNonAnnotatedBoard() {
        //// Pagination param
        $q_before = '';
        $q_args = array($this->request->board);
        $before = $this->service->before;
        
        if ($before > 0) {
            $q_before = "AND m.ID_MSG < ?";
            $q_args[] = $before;
        }
        
        $dbprefix = $this->db->db_prefix;
        
        $q_args[] = $this->service->paginateBy + 1;
        
        $posts = array();
        
            //// Get non annotated messages of selected board
            $r = $this->db->prepare(
                "SELECT STRAIGHT_JOIN tmp.ID_MSG, date, IFNULL(f.subject, tmp.subject) as subject, body, tmp.status, author, tmp.realname, boardname, ID_CAT, ID_BOARD, annotation, lm.memberName AS annotatedByName, IFNULL(lm.realName, lm.memberName) AS annotatedByRN, f.sticky, f.ID_MSG AS fID
                   FROM (
                     SELECT STRAIGHT_JOIN ID_MSG, posterTime as date, subject, body, m.status, mem.memberName AS author, IFNULL(mem.realName, mem.memberName) AS realname, b.name AS boardname, b.ID_CAT, b.ID_BOARD
                       FROM {$dbprefix}messages m
                       LEFT JOIN {$dbprefix}topics t ON m.ID_TOPIC = t.ID_TOPIC
                       LEFT JOIN {$dbprefix}boards b ON t.ID_BOARD = b.ID_BOARD
                       LEFT JOIN {$dbprefix}members mem ON m.ID_MEMBER = mem.ID_MEMBER
                     WHERE b.ID_BOARD = ? $q_before
                     ORDER BY m.ID_MSG DESC LIMIT ?
                   ) tmp
                   LEFT JOIN {$dbprefix}feed f ON f.ID_MSG = tmp.ID_MSG
                   LEFT JOIN {$dbprefix}members lm ON annotatedBy = lm.ID_MEMBER
                   ORDER BY tmp.ID_MSG DESC"
            );
        $r->execute($q_args);
        $rows = $r->fetchAll();
        $r = null;
        
        if (empty($rows)) {
            return $posts; // empty array
        }
        
        foreach ($rows as $row) {
            if (is_null($row['fID'])) {
                //// Try to create subject from content
                $autosubject = $this->app->feedsrvc->autosubject($row['subject'], $row['body']);
                if (is_array($autosubject)) {
                    $row['subject'] = "{$autosubject[0]} &#12299; {$autosubject[1]}";
                }
            }
            $posts[] = $row;
        }
        
        if (count($posts) > $this->service->paginateBy) {
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
            
            $q_args = array();
            
            if ($mode == 'cat') {
                if ($this->request->cat == 0)
                    $whereCat = "<> 0";
                else {
                    $whereCat = "= ?";
                    $q_args[] = $this->request->cat;
                }
                $q_where = "b.ID_CAT $whereCat";
            }
            elseif ($mode == 'board') {
                $q_where = "b.ID_BOARD = ?";
                $q_args[] = $this->request->board;
            }
            elseif ($mode == 'topic') {
                $q_where = "m.ID_TOPIC = ?";
                $q_args[] = $this->request->topic;
            }
            else return;
            
            $q_args[] = $this->service->before;
            $q_args[] = $this->service->paginateBy;
            
            if ($all) {
                $qString =
                    "SELECT STRAIGHT_JOIN ID_MSG
                    FROM {$dbprefix}messages m
                    LEFT JOIN {$dbprefix}topics t ON m.ID_TOPIC = t.ID_TOPIC
                    LEFT JOIN {$dbprefix}boards b ON t.ID_BOARD = b.ID_BOARD
                    WHERE $q_where AND m.ID_MSG > ?
                    ORDER BY ID_MSG LIMIT ?";
            } else {
                $qString = 
                    "SELECT STRAIGHT_JOIN f.ID_MSG
                    FROM {$dbprefix}feed f
                    JOIN {$dbprefix} messages m ON m.ID_MSG = f.ID_MSG
                    LEFT JOIN {$dbprefix}topics t ON m.ID_TOPIC = t.ID_TOPIC
                    LEFT JOIN {$dbprefix}boards b ON t.ID_BOARD = b.ID_BOARD
                    WHERE $q_where AND f.ID_MSG > ? and f.sticky = 0
                    ORDER BY f.ID_MSG LIMIT ?";
            }
            
            //// get prev page 
            $r = $this->db->prepare($qString);
            $r->execute($q_args);
        
            $prev_pages = array();
            while ($row = $r->fetchColumn()) {
                $prev_pages[] = $row;
            }
            $r = null;
            $prev_pages = array_reverse($prev_pages);
        
            if (count($prev_pages) == $this->service->paginateBy) {
                //// it's not first page, return link to previous page
                $this->service->pagePrev = $prev_pages[0];
            } else {
                //// Link to start page
                $this->service->pagePrev = -1;
            }
        } // $before > 0
    } // buildPagination()
    
    /**
     * Get thread subject
     * @param int $msgid  message ID
     * @return string
     */
    public function getTopicInfo($topic) {
        $db_prefix = $this->db->db_prefix;
        $req = $this->db->prepare("SELECT ID_TOPIC as id, subject FROM messages WHERE ID_TOPIC = (SELECT ID_TOPIC FROM messages WHERE ID_MSG = ?) ORDER BY ID_MSG limit 1");
        $req->execute(array($topic));
        $topic = $req->fetch();
        $req = null;
        return $topic;
    }
    
    /**
     * Remove censored posts
     * @param array $posts  Array of posts
     * @return array        Array of filtered posts
     */
    public function filterForbiddenPosts($posts) {
        return array_filter($posts, function($post) {
            return $post['status'] < 400;
        });
    }
}

?>
