<?php $this->partial('feed/header.php'); ?>

<form method="post" action=".">
  <div>
    <input type="text" name="subject" placeholder="subject" maxlength="120" autocomplete="off" value="<?=$this->esc($this->post['subject']) ?>" required>
  </div>
  <div>
    <textarea name="annotation" maxlength="256" autocomplete="off" placeholder="<?= $this->locale->txt['feed_textarea'] ?>"><?=$this->esc($this->post['annotation']) ?></textarea>
  </div>
  <div>
    <?= $this->locale->txt['feed_sticky'] ?>:
      <input type="radio" name="sticky" value="0" <?= $this->sticky == 0 ? 'checked' : '' ?> id="stickySwitch0">
        <label for="stickySwitch0"><?= $this->locale->txt['feed_sticky_no'] ?></label>
      <input type="radio" name="sticky" value="1" <?= $this->sticky == 1 ? 'checked' : '' ?> id="stickySwitch1">
        <label for="stickySwitch1"><?= $this->locale->txt['feed_sticky_cat'] ?></label>
      <input type="radio" name="sticky" value="2" <?= $this->sticky == 2 ? 'checked' : '' ?> id="stickySwitch2">
        <label for="stickySwitch2"><?= $this->locale->txt['feed_sticky_global'] ?></label>
    <input type="submit" name="delete" <?= is_null($this->post['fID'])? 'disabled' : '' ?> value="<?= $this->locale->txt['feed_del'] ?>">
    <input type="submit" name="preview" value="<?= $this->locale->txt['feed_preview'] ?>">
    <input type="submit" name="save" value="<?= $this->locale->txt['feed_save'] ?>">
    <input type="hidden" name="csrf" value="<?= $this->sessid ?>">
  </div>
</form>

<article class="windowbg2" id="article<?= $this->post['ID_MSG'] ?>">
  <h2 class="windowbg"><?=$this->esc($this->post['boardname']) ?>
    <span class="article-title-delimeter">»</span>
    <a href="<?= $this->namespace ?>/<?= $this->post['ID_CAT'] ?>/<?= $this->post['ID_BOARD'] ?>/<?= $this->post['ID_MSG'] ?>/"><?=$this->esc($this->post['subject']); ?></a>
  </h2>
  <div class="article-annotation"><?= $this->DoUBBC($this->post['annotation']) ?></div>
  <div class="article-content"><?= $this->DoUBBC($this->post['body']); ?></div>
  <div class="article-info windowbg3">
    <!-- article info -->
    <div>
      <div class="article-author"><?= $this->locale->txt['feed_post_by'] ?> <a href="<?= $this->siteurl ?>/people/<?= $this->post['author'] ?>/"><?=$this->esc($this->post['realname']); ?></a>, <span class="article-date"><?= $this->app->subs->timeformat($this->post['date']); ?></span></div>
      <div class="article-annotated-by">
        <?php if (!empty($this->post['annotatedByName'])): ?>
          <?= $this->locale->txt['feed_check_by'] ?> <a href="<?= $this->siteurl ?>/people/<?= rawurlencode($this->post['annotatedByName']) ?>/"><?=$this->esc($this->post['annotatedByRN']) ?></a>
        <?php endif; ?>
      </div> <!-- .article-annotated-by -->
      <div class="article-goto-forum"><a href="<?= $this->siteurl?>/<?= $this->post['ID_MSG'] ?>/"><?= $this->locale->txt['feed_discuss'] ?></a></div>
    </div> 
  </div> <!-- .article-info -->
</article>

<?php $this->partial('feed/footer.php') ?>
