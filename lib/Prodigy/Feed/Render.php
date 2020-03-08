<?php
namespace Prodigy\Feed;

/*
 * Pages renderer class
 */

Class Render extends \Prodigy\Respond\Respond {
//     private $app;
//     private $service;
//     private $request;
//     private $response;
//     
//     public function __construct($router) {
//         parent::__construct($router);
//         $this->app = $app;
//         $this->service = $app->main->service();
//         $this->request = $app->main->request();
//         $this->response = $app->main->response();
//         $this->service->namespace = $router->getNamespace();
//     }
//// Above already done in parent constructor.

    public function root() {
    //// Goto default category
        $dbprefix = $this->app->db->db_prefix;
        $r = $this->app->db->query(
            "SELECT ID_CAT FROM {$dbprefix}categories ORDER BY catOrder LIMIT 1"
        );
        $row = $r->fetch();
        $r = null;
        if (!$row)
            return $this->app->feedsrvc->abort('Error', 'No category found.');
        
        $redirect_url = $this->request->uri() . "{$row['ID_CAT']}/";
        //// Redirect to first category if no category selected
        return $this->redirect($redirect_url);

    } // root()
    
    /*
     * Category page renderer
     */
    public function category($request, $response, $service, $app) {
        if (isset($request->all)) $all = true;
        else $all = false;
        $service->unfiltered = $all;
        $service->displayFilterLnk = true;
    
        if ($all) {
            $posts = $app->feedData->getNonAnnotatedCat();
        } else {
            if($service->before == 0) {
                //// Get sticky posts
                $posts_sticky = array_merge(
                    $app->feedData->getAnnotatedCat(2),
                    $app->feedData->getAnnotatedCat(1)
                );
            }
            //// Get non sticky posts
            $posts = $app->feedData->getAnnotatedCat(0);
        }
        
        $service->cat = $request->cat;
        $service->board = null;
        
        $app->feedData->buildPagination($posts, 'cat');
    
        if ($all || $service->before != 0)
            $service->posts = $posts;
        else
            $service->posts = array_merge($posts_sticky, $posts);
        
        if (count($service->posts) == 0) {
            return $app->errors->abort('Error', 'No posts in this category.');
        }
        
        if ($request->cat == 0) {
            $service->title = $app->locale->txt['feed_all_cats'];
        } else {
            $service->title = $service->menuCatNames[$request->cat];
        }
        
        if ($all)
            $service->title .= " ({$app->locale->txt['feed_unfiltered']})";
        
        //// Build opengraph data
        $GLOBALS['opengraph'] = array(
            'title' => mb_convert_encoding($service->title, 'HTML-ENTITIES', $app->db->db_charset),
            'url' => $service->siteurl . $request->uri(),
            'image' => "{$service->protocol}{$service->host}/YaBBImages/opengraph_bg.png"
        );
        
        $service->rss_link = true;
        
        $this->render('feed/articles.php');
    } // category()
    
    /*
     * Board view page renderer
     */
    public function board() {
        if (isset($this->request->all)) $all = true;
        else $all = false;
        $this->service->unfiltered = $all;
        $this->service->displayFilterLnk = true;
        
        if ($all) {
            $posts = $this->app->feedData->getNonAnnotatedBoard();
        } else {
            if ($this->service->before == 0) {
                $posts_sticky = array_merge(
                    $this->app->feedData->getAnnotatedCat(2),
                    $this->app->feedData->getAnnotatedCat(1)
                );
            }
            $posts = $this->app->feedData->getAnnotatedBoard();
        }
        
        $this->service->cat = $this->request->cat;
        $this->service->board = $this->request->board;
    
        if (count($posts) > 0 && $posts[0]['ID_CAT'] != $this->request->cat) {
            // Board was moved to other category, redirect user to proper cat.
            $redirect_url = "{$this->service->httphost}{$this->service->namespace}/{$posts[0]['ID_CAT']}/{$this->request->board}/";
            return $this->response->redirect($redirect_url);
        }
    
        $this->app->feedData->buildPagination($posts, 'board');
    
        if ($all || $this->service->before != 0)
            $this->service->posts = $posts;
        else
            $this->service->posts = array_merge($posts_sticky, $posts);
        
        if (count($this->service->posts) == 0) {
            return $this->app->feedsrvc->abort('Error', 'No posts in this board.');
            //return $this->error('No posts in this board.');
        }
        
        $this->service->title = $this->service->menuCatNames[$this->request->cat] . ' &#12299; ' . $this->service->menu[$this->request->cat][$this->request->board]['boardname'];
        
        if ($all)
            $this->service->title .= " ({$this->app->locale->txt['feed_unfiltered']})";
        
        //// Build opengraph data
        $GLOBALS['opengraph'] = array(
            'title' => mb_convert_encoding($this->service->title, 'HTML-ENTITIES', $this->app->db->db_charset),
            'url' => $this->service->siteurl . $this->request->uri(),
            'image' => $this->service->httphost . STATIC_ROOT . "/img/YaBBImages/opengraph_bg.png"
        );
        
        $this->service->rss_link = true;
        
        $this->render('feed/articles.php');
     
    } // board()

    /**
     * Articles by topic controller
     */
    public function topic($request, $response, $service, $app) {
        $this->service->displayFilterLnk = true;
        
        
        if ($this->service->before == 0) {
            $posts_sticky = array_merge(
                $this->app->feedData->getAnnotatedCat(2),
                $this->app->feedData->getAnnotatedCat(1)
            );
        }
        
        $posts = $app->feedData->getPostsByTopic($request->topic);
        
        
        $service->cat = $request->cat;
        $service->board = $request->board;
    
        if (count($posts) > 0 && $posts[0]['ID_CAT'] != $request->cat) {
            // Board was moved to other category, redirect user to proper cat.
            $redirect_url = "{$service->httphost}{$service->namespace}/{$posts[0]['ID_CAT']}/{$request->board}/";
            return $this->redirect($redirect_url);
        }
    
        $app->feedData->buildPagination($posts, 'topic');
    
        if ($service->before != 0)
            $service->posts = $posts;
        else
            $service->posts = array_merge($posts_sticky, $posts);
        
        if (count($service->posts) == 0) {
            return $app->feedsrvc->abort('Error', 'No posts in this board.');
        }
        
        $service->title = $service->menuCatNames[$request->cat] . ' &#12299; ' . $service->menu[$request->cat][$request->board]['boardname'];
        
        if (!empty($posts)) {
            $topic_info = $app->feedData->getTopicInfo($posts[0]['fID']);
            $service->title .= ' &#12299; ' . $topic_info['subject'];
        }
        
        //// Build opengraph data
        $GLOBALS['opengraph'] = array(
            'title' => mb_convert_encoding($service->title, 'HTML-ENTITIES', $app->db->db_charset),
            'url' => $service->siteurl . $request->uri(),
            'image' => $service->httphost . STATIC_ROOT . "/img/YaBBImages/opengraph_bg.png"
        );
        
        //$service->rss_link = true;
        
        return $this->render('feed/articles.php');
     
    } // topic()

    
    /*
     * Article edit page renderer
     * and POST request handler.
     */
    public function article_edit() {
        $dbprefix = $this->app->db->db_prefix;
        $r = $this->app->db->prepare(
            "SELECT STRAIGHT_JOIN m.ID_MSG, posterTime as date, lm.memberName AS annotatedByName, IFNULL(lm.realName, lm.memberName) AS annotatedByRN, IFNULL(l.subject, m.subject) as subject, annotation, body, IFNULL(mem.realName, mem.memberName) as realname, mem.memberName as author, b.name as boardname, b.ID_CAT, b.ID_BOARD, l.sticky, l.ID_MSG as fID
                FROM {$dbprefix}messages m
                LEFT JOIN {$dbprefix}topics t ON m.ID_TOPIC = t.ID_TOPIC
                LEFT JOIN {$dbprefix}boards b ON t.ID_BOARD = b.ID_BOARD
                LEFT JOIN {$dbprefix}members mem ON m.ID_MEMBER = mem.ID_MEMBER
                LEFT JOIN {$dbprefix}feed l ON m.ID_MSG = l.ID_MSG
                LEFT JOIN {$dbprefix}members lm ON annotatedBy = lm.ID_MEMBER
                WHERE m.ID_MSG = ?"
        );
        $r->execute(array($this->request->postid));
        
        $this->service->cat = $this->request->cat;
        $this->service->board = $this->request->board;
        
        $post = $r->fetch();
        $r = null;
        if (empty($post)) {
            return $this->app->feedsrvc->abort('Error', 'Post not found.');
        }
        
        $this->service->sticky = $post['sticky'];
        
        $redirect_url = "{$this->service->httphost}{$this->service->namespace}/{$post['ID_CAT']}/{$post['ID_BOARD']}/{$this->request->postid}/";
    
        if($this->request->cat != $post['ID_CAT'] || $this->request->board != $post['ID_BOARD']) {
            //// Redirect to proper place if post was moved
            return $this->redirect("{$redirect_url}edit/");
        }
        
        if (!$this->app->feedsrvc->editAllowed($this->request->cat, $this->request->board)) {
            $this->app->feedsrvc->abort('Error', 'Access denied.', 403);
            return;
        }
        
        if (is_null($post['fID'])) {
            //// Try to create subject from content
            $autosubject = $this->app->feedsrvc->autosubject($post['subject'], $post['body']);
            if (is_array($autosubject)) {
                $post['subject'] = "{$autosubject[0]} &#12299; {$autosubject[1]}";
            }
        }
        
        // CSRF token
        $this->service->sessid = $this->app->session->id;
        
        if($this->request->method() == 'POST') {
            $this->service->validateParam('subject', 'Empty subject.')->isLen(1, 256);
            //$this->service->validateParam('annotation', 'Empty annotation.')->isLen(1, 256);
            $this->service->validateParam('sticky', 'Form not properly filled')->isInt();
            $this->service->build_menu = true;
            if ($this->request->param('preview', false)) {
                //// Preview button pressed
                $post['subject'] = $this->request->param('subject');
                $post['annotation'] = $this->request->param('annotation', '');
                $this->service->sticky = $this->request->param('sticky');
                $this->app->feedsrvc->build_menu();
            }
            elseif ($this->request->param('save', false)) {
                //// Save annotation
                $req = $this->app->db->prepare("INSERT INTO {$dbprefix}feed
                    (ID_MSG, sticky, subject, annotation, annotatedBy)
                      values (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                      sticky = ?, subject = ?, annotation = ?, annotatedBy = ?"
                    );
                $req->execute(array(
                    $this->request->postid,
                    $this->request->param('sticky', 0),
                    $this->request->param('subject'),
                    $this->request->param('annotation'),
                    $this->app->user->id,
                    $this->request->param('sticky', 0),
                    $this->request->param('subject'),
                    $this->request->param('annotation'),
                    $this->app->user->id,
                ));
                $req = null;
                return $this->redirect($redirect_url);
            }
            elseif ($this->request->param('delete', false)) {
                //// Delete annotation
                $req = $this->app->db->prepare("DELETE FROM {$dbprefix}feed WHERE ID_MSG = ?");
                $req->execute(array($this->request->postid));
                $req = null;
                return $this->redirect($redirect_url);
            }
            else {
                return $this->app->feedsrvc->abort('Error', 'Bad request.');
            }
        } // If method POST
        
        $this->service->post_view = true;
        $this->service->post = $post;
        $this->service->title = $this->service->menuCatNames[$this->request->cat] . ' &#12299; ' . $post['boardname'];
        
        $this->render('feed/edit.php');
    } // article_edit()
    
    /*
     * Article page renderer
     */
    public function article() {
        $check_roskolhoznadzor = $this->app->conf->get('roskolhoznadzor');
        
        $dbprefix = $this->app->db->db_prefix;
        $r = $this->app->db->prepare(
            "SELECT STRAIGHT_JOIN m.ID_MSG, m.status, posterTime as date, lm.memberName AS annotatedByName, IFNULL(lm.realName, lm.memberName) AS annotatedByRN, IFNULL(f.subject, m.subject) as subject, annotation, body, IFNULL(mem.realName, mem.memberName) as realname,  mem.memberName as author, b.name as boardname, b.ID_CAT, b.ID_BOARD, f.sticky, f.ID_MSG AS fID
                FROM {$dbprefix}messages m
                LEFT JOIN {$dbprefix}topics t ON m.ID_TOPIC = t.ID_TOPIC
                LEFT JOIN {$dbprefix}boards b ON t.ID_BOARD = b.ID_BOARD
                LEFT JOIN {$dbprefix}members mem ON m.ID_MEMBER = mem.ID_MEMBER
                LEFT JOIN {$dbprefix}feed f ON m.ID_MSG = f.ID_MSG
                LEFT JOIN {$dbprefix}members lm ON annotatedBy = lm.ID_MEMBER
                WHERE m.ID_MSG = ?"
        );
        $r->execute(array($this->request->postid));
        $posts = $r->fetchAll();
        $r = null;
        $this->service->cat = $this->request->cat;
        $this->service->board = $this->request->board;
        
        if (empty($posts)) {
            return $this->app->feedsrvc->abort('Error', 'Post not found.');
            //return $this->error('Post not found.');
        }
        
        foreach ($posts as $post) {
            if ($check_roskolhoznadzor) {
                if ($post['status'] >= 400) {
                    // Article is blocked
                    return $this->app->feedsrvc->httpError($row['status']);
                }
                
                if ($this->service->username == 'Guest' && $is_roskolhoz = $this->app->collection->Security->is_roskolhoznadzor()) {
                    $this->app->collection->Display->roskolhoznadzor_block($row,  $is_roskolhoz); // block the artticle
                    
                    return $this->response->redirect($this->service->httphost . $this->request->uri()); // reload the page
                }
            }
        }
    
        $redirect_url = "{$this->service->httphost}{$this->service->namespace}/{$posts[0]['ID_CAT']}/{$posts[0]['ID_BOARD']}/{$this->request->postid}/";
        if($this->request->cat != $posts[0]['ID_CAT'] || $this->request->board != $posts[0]['ID_BOARD']) {
            //// Redirect to proper place if post was moved
            return $this->response->redirect($redirect_url);
        }
        
        $this->service->title = $posts[0]['subject'];
        
        if (is_null($posts[0]['fID'])) {
            //// Try to create subject from content
            $autosubject = $this->app->feedsrvc->autosubject($posts[0]['subject'], $posts[0]['body']);
            if (is_array($autosubject)) {
                $posts[0]['subject'] = "{$autosubject[0]} &#12299; {$autosubject[1]}";
                $this->service->title = $autosubject[1];
            }
        }
        
        $annotation = is_null($posts[0]['annotation']) ? '' : "{$posts[0]['annotation']} ";
        
        $AnnotationAndBody = $annotation . $posts[0]['body'];
        
        //// Build opengraph data
        $GLOBALS['opengraph'] = array(
            'title' => mb_convert_encoding($posts[0]['subject'], 'HTML-ENTITIES', $this->app->db->db_charset),
            'description' => mb_convert_encoding(
                $this->app->feedsrvc->plainText($AnnotationAndBody, 200),
                'HTML-ENTITIES', $this->app->db->db_charset),
            'url' => $redirect_url
        );
        
        if (preg_match('#\[img.*?\](.+?)\[/img\]#', $AnnotationAndBody, $matches) > 0) {
            // Image found in article, using it for opengraph image
            $GLOBALS['opengraph']['image'] = $matches[1];
        }
        elseif (preg_match('#https?://youtu.be/([a-zA-Z0-9_-]+)|https?://www\.youtube\.com/watch?.*v=([a-zA-Z0-9_-]+)#', $AnnotationAndBody, $matches)) {
            // A Youtube found in article, using video thumbnail for opengraph image
            if (!empty($matches[1]))
                $thumbID = $matches[1];
            elseif (!empty($matches[2]))
                $thumbID = $matches[2];
            else
                $thumbID = '';
            
            if ($thumbID != '')
                $GLOBALS['opengraph']['image'] = "https://img.youtube.com/vi/$thumbID/mqdefault.jpg";
            else
                $GLOBALS['opengraph']['image'] = "{$this->service->protocol}{$this->service->host}/YaBBImages/opengraph_bg.png";
        }
        else {
            // Using default image if no image found in article
            $GLOBALS['opengraph']['image'] = "{$this->service->protocol}{$this->service->host}/YaBBImages/opengraph_bg.png";
        }
        
        $this->service->posts = $posts;
        $this->service->post_view = true;
        
        $this->service->topic = $this->app->feedData->getTopicInfo($this->request->postid);
        
        $this->render('feed/articles.php');
        return $this->response;
    } // article()
    
    public function boardrss() {
        return $this->rss('board');
    }
    public function catrss() {
        return $this->rss('cat');
    }
    
    /**
     * Render RSS
     * @param string $what what to render. Possible values 'cat' for category or 'board' for board.
     * @returns returns response.
     */
    public function rss($what) {
        $this->service->paginateBy = 200;
        $posts = array();
        if ($what == 'cat') {
            if ($this->request->cat == 0) {
                $this->service->title = $this->app->locale->txt['feed_all_cats'];
            } else {
                $this->service->title = $this->service->menuCatNames[$this->request->cat];
            }
            
            if (isset($this->request->all)) {
                $posts = $this->app->feedData->getNonAnnotatedCat();
            } else {
                $posts = $this->app->feedData->getAnnotatedCat();
            }
            
            $this->service->main_link = "{$this->service->siteurl}/feed/{$this->request->cat}/";
        } else {
            $this->service->title = $this->service->menuCatNames[$this->request->cat] . ' &#12299; ' . $this->service->menu[$this->request->cat][$this->request->board]['boardname'];
            
            if (isset($this->request->all)) {
                $posts = $this->app->feedData->getNonAnnotatedBoard();
            } else {
                $posts = $this->app->feedData->getAnnotatedBoard();
            }
            
            $this->service->main_link = "{$this->service->siteurl}/feed/{$this->request->cat}/{$this->request->board}/";
        }
        
        if (isset($this->request->all))
                $this->service->title .= " ({$this->app->locale->txt['feed_unfiltered']})";
        
        foreach ($posts as &$post) {
            //// Preparing all output strings 
            $post['subject'] = $this->app->feedsrvc->string4rss($post['subject']);
            $post['body'] = $this->app->feedsrvc->plainText($post['body'], 256);
            $post['body'] = $this->app->feedsrvc->string4rss($post['body']);
            $post['annotation'] = $this->app->feedsrvc->plainText($post['annotation']);
            $post['annotation'] = $this->app->feedsrvc->string4rss($post['annotation']);
            $post['rss-cat'] = $this->app->feedsrvc->string4rss($this->service->menu[$post['ID_CAT']][$post['ID_BOARD']]['boardname']);
        }
        
        $this->service->pub_date = date('r', $posts[0]['date']);
        
        $this->service->title = $this->app->feedsrvc->string4rss($this->service->title);
        
        $this->service->posts = $posts;
        
        $this->response->header('Content-Type', 'application/rss+xml; charset=UTF-8');
        $this->service->layout(PROJECT_ROOT . '/templates/feed/rss.php');
        $this->service->render(PROJECT_ROOT . '/templates/feed/rss.php'); // Have to set twice for some reason.
        return $this->response;
    } // rss()
}

?>
