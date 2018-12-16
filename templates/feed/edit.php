<form method="post" action=".">
  <div>
    <input type="text" name="subject" placeholder="subject" maxlength="120" autocomplete="off" value="<?= htmlescape($this->post['subject']) ?>" required>
  </div>
  <div>
    <textarea name="annotation" maxlength="256" autocomplete="off" placeholder="<?= $this->txt['feed_textarea'] ?>"><?= htmlescape($this->post['annotation']) ?></textarea>
  </div>
  <div>
    <?= $this->txt['feed_sticky'] ?>:
      <input type="radio" name="sticky" value="0" <?= $this->sticky == 0 ? 'checked' : '' ?> id="stickySwitch0">
        <label for="stickySwitch0"><?= $this->txt['feed_sticky_no'] ?></label>
      <input type="radio" name="sticky" value="1" <?= $this->sticky == 1 ? 'checked' : '' ?> id="stickySwitch1">
        <label for="stickySwitch1"><?= $this->txt['feed_sticky_cat'] ?></label>
      <input type="radio" name="sticky" value="2" <?= $this->sticky == 2 ? 'checked' : '' ?> id="stickySwitch2">
        <label for="stickySwitch2"><?= $this->txt['feed_sticky_global'] ?></label>
    <input type="submit" name="delete" <?= is_null($this->post['fID'])? 'disabled' : '' ?> value="<?= $this->txt['feed_del'] ?>">
    <input type="submit" name="preview" value="<?= $this->txt['feed_preview'] ?>">
    <input type="submit" name="save" value="<?= $this->txt['feed_save'] ?>">
    <input type="hidden" name="csrf" value="<?= $this->sessid ?>">
  </div>
</form>

<article class="windowbg2" id="article<?= $this->post['ID_MSG'] ?>">
  <h2 class="windowbg"><?= htmlescape($this->post['boardname']) ?>
    <span class="article-title-delimeter">»</span>
    <a href="<?= $this->appDir ?>/<?= $this->post['ID_CAT'] ?>/<?= $this->post['ID_BOARD'] ?>/<?= $this->post['ID_MSG'] ?>/"><?= htmlescape($this->post['subject']); ?></a>
  </h2>
  <div class="article-annotation"><?= DoUBBC(htmlescape($this->post['annotation'])) ?></div>
  <div class="article-content"><?= DoUBBC($this->post['body']); ?></div>
  <div class="article-info windowbg3">
    <!-- article info -->
    <div>
      <div class="article-author"><?= $this->txt['feed_post_by'] ?> <a href="<?= $this->static_root ?>?action=viewprofile;user=<?= $this->post['author'] ?>"><?= htmlescape($this->post['realname']); ?></a>, <span class="article-date"><?= timeformat($this->post['date']); ?></span></div>
      <div class="article-annotated-by">
        <?php if (!empty($this->post['annotatedByName'])): ?>
          <?= $this->txt['feed_check_by'] ?> <a href="<?= $this->static_root ?>?action=viewprofile;user=<?= urlencode($this->post['annotatedByName']) ?>"><?= htmlescape($this->post['annotatedByRN']) ?></a>
        <?php endif; ?>
      </div> <!-- .article-annotated-by -->
      <div class="article-goto-forum"><a href="<?= $this->static_root ?>?action=gotomsg;msg=<?= $this->post['ID_MSG'] ?>"><?= $this->txt['feed_discuss'] ?></a></div>
    </div> 
  </div> <!-- .article-info -->
</article>
