<?php $GLOBALS['yytitle'] = $this->title; ?>

<?php template_header(); ?>


<!-- Lenta style. It's not good place for it here.
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
  
  nav.cats-menu {
    display: inline-block;
    /*border: 1px dotted green;*/
    max-width: 29%;
    margin: 0;
    vertical-align: top;
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
      background-color: inherit;
  }
  a.nav-item:active {
      text-decoration: none;
      color: initial;
      /*background-color: unset;*/
  }
  
  section.posts-list {
      display: inline-block;
      /*max-width: 70%;*/
      margin-top: 2px;
      margin-left: 1em;
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
      margin: 1em 0;
      padding: 0.4em;
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
    margin-top: 0.4em;
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
      width: 70%;
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
</style>

<div class="feed-main-container">

<nav class="cats-menu">
  <ul>
  <!-- categories list -->
  <?php foreach ($this->menu as $ck => $cv): ?>
    <li><a class="quote nav-item<?= $ck == $this->cat ? ' current' : '' ?>" href="<?= $this->appDir ?>/<?= $ck ?>/"><?= htmlescape($this->menuCatNames[$ck]); ?></a>
        <?php if($ck==$this->cat): ?>
        <ul class="current">
        <?php else: ?>
        <ul>
        <?php endif; ?>
          <!-- boards list -->
          <?php foreach ($cv as $bk => $bv): ?>
          <li><a class="quote nav-item<?= $bv['ID_BOARD'] == $this->board ? ' current' : '' ?>" href="<?= $this->appDir ?>/<?= $ck ?>/<?= $bv['ID_BOARD'] ?>/"><?= htmlescape($bv['boardname']); ?></a></li>
           <?php endforeach; ?>
        </ul>
    </li>
  <?php endforeach; ?>
    <li><a class="quote nav-item<?= $this->cat == 0 ? ' current ' : '' ?>" href="<?= $this->appDir ?>/0/">View all</a></li>
  </ul>
  
  <?php if($this->displayFilterLnk): ?>
    <br>
    <?php if($this->unfiltered): ?>
      <a class="articles-filter-btn" href="../">Show filtered</a>
    <?php else: ?>
      <a class="articles-filter-btn" href="./all/">Unfiltered</a>
    <?php endif; ?>
  <?php endif; ?>
</nav>
