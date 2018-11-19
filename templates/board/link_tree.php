      <?php if($this->conf->enableInlineLinks): ?>
        <font size="1">
          <b><a href="<?= SITE_ROOT ?>/" class="nav"><?= $this->conf->mbname ?></a></b>&nbsp;|&nbsp;
      <?php else: ?>
        <font size="2">
          <b><img src="<?= $this->conf->imagesdir ?>/open.gif" border="0" alt="">&nbsp;&nbsp;<a href="<?= SITE_ROOT ?>/" class="nav"><?= $this->conf->mbname ?></a><br>
      <?php endif; ?>
      
      <?php if($this->conf->enableInlineLinks): ?>
        <b><a href="<?= SITE_ROOT ?>/#<?= $this->currcat ?>" class="nav"><?= $this->get('catname') ?></a> </b>&nbsp;|&nbsp; 
      <?php else: ?>
        <img src="<?= $this->conf->imagesdir ?>/tline.gif" border="0" alt=""><img src="<?= $this->conf->imagesdir ?>/open.gif" border="0" alt="" />&nbsp;&nbsp;<a href="<?= SITE_ROOT ?>/#<?= $this->currcat ?>" class="nav"><?= $this->get('catname') ?></a><br>
      <?php endif; ?>
      
      <?php if($this->conf->enableInlineLinks): ?>
        <b>
          <?php if($this->conf->curposlinks): ?>
            <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/" class="nav"><?= $this->title ?></a>
          <?php else: ?>
            <?= $this->title ?>
          <?php endif; ?>
        </b> <?= $this->showmods ?>
        </font>
      <?php else: ?>
        <img src="<?= $this->conf->imagesdir ?>/tline2.gif" border="0" alt=""><img src="<?= $this->conf->imagesdir ?>/open.gif" border="0" alt="">&nbsp;&nbsp;
        <?php if($this->conf->curposlinks): ?>
            <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/" class="nav"><?= $this->title ?></a>
        <?php else: ?>
            <?= $this->title ?>
        <?php endif; ?>
        <?= $this->showmods ?></font>
      <?php endif; ?>
