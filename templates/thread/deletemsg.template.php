        <h2><?= $this->title ?></h2>
        <h3><a href="<?= SITE_ROOT ?>/<?= $this->msgid ?>/"><?= $this->esc($this->subject) ?></a></h3>
        <div><?= $this->doUBBC($this->body) ?></div>
        
        
        <div style="text-align: right;">
          Author:
          <?php if($this->poster['guest']): ?>
              <?= $this->esc($this->poster['name']) ?>
          <?php else: ?>
              <a href="<?= SITE_ROOT ?>/people/<?= urlencode($this->poster['name']) ?>/"><?= $this->esc($this->poster['realName']) ?></a>,
          <?php endif; ?>
          <i><?= $this->date ?></i>
          <form action="." method="POST">
            <input type="hidden" name="sc" value="<?= $this->sessid ?>">
            <input type="text" name="mdfrzn" placeholder="<?= $this->locale->mdfrznplchldr ?>" size="64" required> <input type="submit" value="<?= $this->locale->txt(31) ?>"> 
          </form>
        </div>
