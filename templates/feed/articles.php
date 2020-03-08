<?php $this->partial('feed/header.php') ?>

<?php foreach ($this->posts as $k => $article) { ?>
  <article class="windowbg2" id="article<?= $article['ID_MSG'] ?>">
    <h2 class="windowbg"><?= $this->esc($article['boardname']) ?>
      <span class="article-title-delimeter">&#12299;</span>
      <a href="<?= $this->appDir ?>/<?= $article['ID_CAT'] ?>/<?= $article['ID_BOARD'] ?>/<?= $article['ID_MSG'] ?>/"><?= $this->esc($article['subject']); ?></a>
    </h2>
    <div class="article-annotation"><?= $this->DoUBBC($article['annotation']) ?></div>
    <div class="article-content">
      <?php if($this->post_view): ?>
        <?= $this->DoUBBC($article['body']) ?>
      <?php else: ?>
        <?= $this->app->feedsrvc->cutstr($this->DoUBBC($article['body'])) ?>
        <?php if($this->cut): ?>
          ... <a href="<?= $this->appDir ?>/<?= $article['ID_CAT'] ?>/<?= $article['ID_BOARD'] ?>/<?= $article['ID_MSG'] ?>/" class="msgurl read-more-link"><?= $this->txt['feed_read_more'] ?></a>
        <?php endif; ?>
      <?php endif; ?>
      
    </div>
    <div class="article-info windowbg3">
      <!-- article info -->
      <div>
        <div class="article-author"><?= $this->txt['feed_post_by'] ?> <a href="<?= $this->static_root ?>?action=viewprofile;user=<?= urlencode($article['author']) ?>"><?= $this->esc($article['realname']); ?></a>, <time datetime="<?= date('c', $article['date']) ?>" class="article-date"><?= $this->app->subs->timeformat($article['date']); ?></time></div>
        <div class="article-annotated-by">
        <?php if (!empty($article['annotatedByName'])): ?>
          <?= $this->txt['feed_check_by'] ?> <a href="<?= $this->static_root ?>?action=viewprofile;user=<?= urlencode($article['annotatedByName']) ?>"><?= $this->esc($article['annotatedByRN']) ?></a>
        <?php endif; ?>
        </div>
        
        <div class="article-goto-forum"><a href="<?= $this->static_root ?>?action=gotomsg;msg=<?= $article['ID_MSG'] ?>"><?= $this->txt['feed_discuss'] ?></a></div>
        
        <?php if($this->app->feedsrvc->editAllowed($article['ID_CAT'], $article['ID_BOARD'])): ?>
        <div class="article-edit-btn">
          <a href="<?= $this->appDir ?>/<?= $article['ID_CAT'] ?>/<?= $article['ID_BOARD'] ?>/<?= $article['ID_MSG'] ?>/edit/"><?= is_null($article['fID']) ? $this->txt['feed_add'] : $this->txt['feed_edit'] ?></a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </article>
<?php } ?>

<!-- Pagination -->
<?php if ($this->pagePrev != 0 || $this->pageNext != 0 ): ?>
<div class="articles-pagination"><?= $this->txt['feed_pages'] ?>:
  <?php if ($this->pagePrev > 0): ?>
    <a href="./?before=<?= $this->pagePrev ?>">&#8656;</a>
  <?php elseif ($this->pagePrev < 0): ?>
    <a href=".">&#8656;</a>  
  <?php endif; ?>
  <?php if($this->pageNext > 0): ?>
    <a href="./?before=<?= $this->pageNext ?>">&#8658;</a>
  <?php endif; ?>
</div>
<?php endif; ?>
<!-- /Pagination -->

<?php $this->partial('feed/footer.php');
