<!-- Feed style. It's not good place for it here.
     Should be moved to <header> somehow. -->
<style>
  /* Main Container */
  body > table:nth-of-type(2),
  body > table:nth-of-type(2) > tbody,
  body > table:nth-of-type(2) > tbody > tr,
  body > table:nth-of-type(2) > tbody > tr > td
  {
      display: block;
      /*vertical-align: initial;*/
  }
  
  .feed-main-container {
      display: flex;
      flex-direction: row;
  }
  
  aside {
    display: inline-block;
    /*border: 1px dotted green;*/
    max-width: 25%;
    margin: 0;
    padding-right: 0.5em;
    vertical-align: top;
    box-sizing: border-box;
  }
  
  nav.cats-menu ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
  }
  nav.cats-menu > ul > li > ul {display: none;}
  nav.cats-menu > ul > li > ul.current {display: block;}
  nav.cats-menu ul:nth-child(2) > li {
    margin-left: 0.6em;
  }
  nav.cats-menu li > a.current {
      /*background-color: rgba(255,255,255,0.7);*/
      opacity: 0.7;
  }

  a.nav-item {
      display: block;
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
      text-decoration: none;
      margin: 0.2em;
      padding: 0.2em;
      /*background-color: unset;*/
      color: initial;
      /* border: 1px dotted blue; */
  }
  a.nav-item:visited {
      text-decoration: none;
      color: inherit;
      /*background-color: unset;*/
  }
  a.nav-item:link {
      text-decoration: none;
      color: inherit;
      /*background-color: unset;*/
  }
  a.nav-item:hover {
      text-decoration: none;
      color: inherit;
      /*background-color: inherit;*/
      opacity: 0.7;
  }
  a.nav-item:active {
      text-decoration: none;
      color: initial;
      /*background-color: unset;*/
  }
  
  section.posts-list {
      display: inline-block;
      box-sizing: border-box;
      width: 75%;
      margin-top: 2px;
      padding-left: 0.2em;
      background: transparent;
      flex: auto;
  }
  
  section.posts-list > h1 {
      text-align: center;
      text-align: center;
      margin: 0;
      padding: 0.2em;
  }
  
  section.posts-list > article {
      /*margin: 0.4em;*/
      margin: 1em 0 4em 0;
      padding: 1px;
  }
  section.posts-list > article > h2 {
      margin: 0;
      padding: 0.4em;
  }
  section.posts-list > article > h2 > a {
      font-weight: bold;
  }
  section.posts-list > article > div.article-info {
      padding: 0.4em;
      margin-top: 0.4em;
      text-align: right;
  }
  section.posts-list > article > div.article-info > div {
      display: inline-block;
      /*text-align: left;*/
  }
  section.posts-list > article > div.article-annotation {
    margin: 1em;
  }
  .article-annotation::after {
    display: block;
    content: " ";
    width: 10%;
    border-bottom: 1px solid #808080;
    margin-top: 1em;
  }

  section.posts-list > article > div.article-content {
      margin: 1em;
      /*margin-bottom: 1em;*/
  }
  
  section.posts-list > article > div.article-content iframe {
      max-width: 100%;
  }
  
  section.posts-list > form {
      margin-top: 0.4em;
  }
  section.posts-list > form > div {
      width: 95%;
      margin: auto;
      text-align: right;
  }
  section.posts-list > form > div > input[type="text"],
  section.posts-list > form > div > textarea {
      width: 100%;
  }
  section.posts-list > form > div > textarea {
      width: 100%;
      height: 7em;
  }
  a.articles-filter-btn {
      font-size: 0.8em;
  }
  
  .articles-pagination > a {
      /*border: 1px solid;
      padding: 0.1em 0.4em;*/
      font-size: 2em;
      position: relative;
      top: 0.1em;
  }
  
  article.feed .topic-lnk {
    margin-top: 2em;
    text-align: right;
  }
    
  <?php $this->include("feed/css/{$this->app->user->skin}.css"); ?>
</style>

<div class="feed-main-container">
<aside>
<nav class="cats-menu">
  <?php if(isset($this->menu)): ?>
  <ul>
  <!-- categories list -->
  <?php foreach ($this->menu as $ck => $cv): ?>
    <li><a class="quote nav-item<?= $ck == $this->cat ? ' current' : '' ?>" href="<?= $this->namespace ?>/<?= $ck ?>/"><?= $this->esc($this->menuCatNames[$ck]); ?></a>
        <?php if($ck==$this->cat): ?>
        <ul class="current">
        <?php else: ?>
        <ul>
        <?php endif; ?>
          <!-- boards list -->
          <?php foreach ($cv as $bk => $bv): ?>
          <li><a class="quote nav-item<?= $bv['ID_BOARD'] == $this->board ? ' current' : '' ?>" href="<?= $this->namespace ?>/<?= $ck ?>/<?= $bv['ID_BOARD'] ?>/"><?= $this->esc($bv['boardname']); ?></a></li>
           <?php endforeach; ?>
        </ul>
    </li>
  <?php endforeach; ?>
    <li><a class="quote nav-item<?= $this->cat == 0 ? ' current ' : '' ?>" href="<?= $this->namespace ?>/0/"><?= $this->locale->txt['feed_all_cats'] ?></a></li>
  </ul>
  <?php endif; ?>
  
  <?php if($this->displayFilterLnk): ?>
    <br>
    <?php if($this->unfiltered): ?>
      <a class="articles-filter-btn" href="../"><?= $this->locale->txt['feed_show_approved'] ?></a>
    <?php else: ?>
      <a class="articles-filter-btn" href="./all/"><?= $this->locale->txt['feed_show_non_approved'] ?></a>
    <?php endif; ?>
  <?php endif; ?>
</nav>
<?php if(isset($this->rss_link)): ?>
  <a href="./rss.xml" type="application/rss+xml" class="rss-link" title="<?= $this->locale->txt['feed_subscribe_to'] ?> <?= $this->esc($this->title) ?>">RSS</a>
<?php endif; ?>
</aside>

<section id="main" class="posts-list catbg">
<h1 class="titlebg"><?= $this->title ?></h1>


