                <?php if($this->conf->enableInlineLinks): ?>
                  <nav class="linktree"><font size="1">
                    <b><a href="<?= SITE_ROOT ?>/" class="nav"><?= $this->esc($this->conf->mbname) ?></a></b><!--
                    <?php foreach($this->linktree as $_lnk): ?>
                      -->&nbsp;<?= $this->linktreesep ?>&nbsp;<b><!--
                      <?php if(isset($_lnk['url'])): ?>
                        --><a href="<?= SITE_ROOT ?><?= $_lnk['url'] ?>" class="nav<?= isset($_lnk['class'])?" {$_lnk['class']}":'' ?>"><?= $this->esc($_lnk['name']) ?></a><!--
                      <?php else: ?>
                        --><?= $this->esc($_lnk['name']) ?><!--
                      <?php endif; ?>
                      --></b><!--
                    <?php endforeach; ?>
               --></font>
                <?php else: ?>
                  <nav class="linktree"><font size="2">
                    <b>
                      <img src="<?= $this->conf->imagesdir ?>/open.gif" border="0" alt="" />&nbsp;&nbsp;<a href="<?= SITE_ROOT ?>/" class="nav"><?= $this->esc($this->conf->mbname) ?></a><br />
                      <?php if(isset($this->linktree[0])): ?>
                          <img src="<?= $this->conf->imagesdir ?>/tline.gif" border="0" alt=""><img src="<?= $this->conf->imagesdir ?>/open.gif" border="0" alt="">
                          <?php if(isset($this->linktree[0]['url'])): ?>
                            <a href="<?= SITE_ROOT ?><?= $this->linktree[0]['url'] ?>" class="nav"><?= $this->esc($this->linktree[0]['name']) ?></a><br />
                          <?php else: ?>
                            <?= $this->esc($this->linktree[0]['name']) ?><br>
                          <?php endif; ?>
                          
                          <?php if(isset($this->linktree[1])): ?>
                            <img src="<?= $this->conf->imagesdir ?>/tline2.gif" border="0" alt=""><img src="<?= $this->conf->imagesdir ?>/open.gif" border="0" alt="">
                            <?php if(isset($this->linktree[1]['url'])): ?>
                              <a href="<?= SITE_ROOT ?><?= $this->linktree[1]['url'] ?>" class="nav"><?= $this->esc($this->linktree[1]['name']) ?></a><br />
                            <?php else: ?>
                              <?= $this->esc($this->linktree[1]['name']) ?>
                            <?php endif; ?>
                            
                            <?php if(isset($this->linktree[2])): ?>
                              <img src="<?= $this->conf->imagesdir ?>/tline3.gif" border="0" alt=""><img src="<?= $this->conf->imagesdir ?>/open.gif" border="0" alt="">
                              <?php if(isset($this->linktree[2]['url'])): ?>
                                <a href="<?= SITE_ROOT ?><?= $this->linktree[2]['url'] ?>" class="nav"><?= $this->esc($this->linktree[2]['name']) ?></a><br />
                              <?php else: ?>
                                <?= $this->esc($this->linktree[2]['name']) ?>
                              <?php endif; ?>
                            <?php endif; // linktree[2] ?>
                          <?php endif; // linktree[1] ?>
                      <?php endif; // linktree[0] ?>
                    </b>
                  </font></nav>
                <?php endif; ?>
