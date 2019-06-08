                <?php if($this->conf->enableInlineLinks): ?>
                  <font size="1">
                    <b><a href="<?= SITE_ROOT ?>/" class="nav"><?= $this->esc($this->conf->mbname) ?></a></b>&nbsp;|&nbsp;<!--
                    --><b><a href="<?= SITE_ROOT ?>/im/" class="nav"><?= $this->esc($this->catname) ?></a></b>&nbsp;|&nbsp;<!--
                    --><b><?= $this->esc($this->boardname) ?></b>
                    <?php if(isset($this->sub)): ?>
                        ( <?= $this->esc($this->sub) ?> )
                    <?php endif; ?>
                    </b>
                  </font>
                <?php else: ?>
                  <font size="2">
                    <b>
                      <img src="<?= $this->conf->imagesdir ?>/open.gif" border="0" alt="" />&nbsp;&nbsp;<a href="<?= SITE_ROOT ?>/" class="nav"><?= $this->esc($this->conf->mbname) ?></a><br />
                      <img src="<?= $this->conf->imagesdir ?>/tline.gif" border="0" alt=""><img src="<?= $this->conf->imagesdir ?>/open.gif" border="0" alt="">&nbsp;&nbsp;<a href="<?= SITE_ROOT ?>/im/" class="nav"><?= $this->esc($this->catname) ?></a><br />
                      <img src="<?= $this->conf->imagesdir ?>/tline2.gif" border="0" alt=""><img src="<?= $this->conf->imagesdir ?>/open.gif" border="0" alt="">&nbsp;&nbsp;<?= $this->esc($this->boardname) ?><br />
                      <?php if(isset($this->sub)): ?>
                          <img src="<?= $this->conf->imagesdir ?>/tline3.gif" border="0" alt=""><img src="<?= $this->conf->imagesdir ?>/open.gif" border="0" alt="">&nbsp;&nbsp;<?= $this->esc($this->title) ?> ( <?=$this->esc($this->sub) ?> )
                      <?php endif; ?>
                    </b>
                  </font>
                <?php endif; ?>
