<?php include('base.php'); ?>

<section class="posts-list catbg">
<h1 class="titlebg"><?= $this->title ?></h1>

<?php foreach ($this->posts as $k => $article) { ?>
  <article class="windowbg2" id="article<?= $article['ID_MSG'] ?>">
    <h2 class="windowbg"><?= htmlescape($article['boardname']) ?>
      <span class="article-title-delimeter">»</span>
      <a href="<?= $this->appDir ?>/<?= $article['ID_CAT'] ?>/<?= $article['ID_BOARD'] ?>/<?= $article['ID_MSG'] ?>/"><?= htmlescape($article['subject']); ?></a>
    </h2>
    <div class="article-annotation"><?= DoUBBC(htmlescape($article['annotation'])) ?></div>
    <div class="article-content"><?= DoUBBC($article['body']); ?></div>
    <div class="article-info windowbg3">
      <!-- article info -->
      <div>
        <div class="article-author">Post by <a href="<?= $this->static_root ?>?action=viewprofile;user=<?= $article['author'] ?>"><?= htmlescape($article['realname']); ?></a> <span class="article-date"><?= timeformat($article['date']); ?></span></div>
        <div class="article-annotated-by">
        <?php if (!empty($article['annotatedByName'])): ?>
          Checked by <a href="<?= $this->static_root ?>?action=viewprofile;user=<?= $article['annotatedByName'] ?>"><?= htmlescape($article['annotatedByRN']) ?></a>
        <?php endif; ?>
        </div>
        
        <div class="article-goto-forum"><a href="<?= $this->static_root ?>?action=gotomsg;msg=<?= $article['ID_MSG'] ?>">Discuss on forum</a></div>
        
        <?php if($this->edit_allowed): ?>
        <div class="article-edit-btn"><a href="./edit/">Edit</a></div>
        <?php endif; ?>
      </div>
    </div>
  </article>
<?php } ?>

<!-- Pagination -->
<?php if ($this->pagePrev > 0 || $this->pageNext > 0 ): ?>
<div class="articles-pagination">Pages:
  <?php if ($this->pagePrev > 0): ?>
    <a href="./?before=<?= $this->pagePrev ?>">&lt;prev</a>
  <?php elseif ($this->pagePrev < 0): ?>
    <a href=".">&lt;prev</a>  
  <?php endif; ?>
  <?php if($this->pageNext > 0): ?>
    <a href="./?before=<?= $this->pageNext ?>">next&gt;</a>
  <?php endif; ?>
</div>
<?php endif; ?>

</section>

</div> <!-- .feed-main-container -->

<?php footer(); ?>
