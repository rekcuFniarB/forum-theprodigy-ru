<div class="error-page">
  <h1 class="titlebg"><?= $this->get('title') ?></h1>
  <article class="windowbg2">
    <div class="article-content error-msg">
      <?php if($this->escape): ?>
        <?= $this->get('message') ?>
      <?php else: ?>
        <?= $this->message ?>
      <?php endif; ?>
    </div>
  </article>
  <a href="javascript:history.back()" style="font-size:2em; position: relative; top: 0.155em; text-decoration: none;">&#8656;</a> <a href="javascript:history.back()"><?= $this->locale->txt[236] ?></a>
</div>
