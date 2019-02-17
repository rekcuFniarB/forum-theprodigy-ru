                    <font size="1"><b>Сейчас в разделе</b>
                      <?php $this->menu_begin() ?>
                      <?php foreach($this->boardviewers[0] as $boardviewer): ?>
                        <?php $this->menusep(',') ?>
                        <a href="<?= SITE_ROOT ?>/profile/<?= $this->esc($boardviewer['identity']) ?>/"><font class="<?= $this->rse($boardviewer['membergroup']) ?>"><?= $this->esc($boardviewer['realname']) ?></a></font>
                      <?php endforeach; ?>
                      <?php if (count($this->boardviewers[0]) > 0 and $this->boardviewers[1] > 0): ?>
                        и 
                      <?php endif; ?>
                      <?php if ($this->boardviewers[1] > 0): /* guests */?>
                        <?php if ($this->boardviewers[1] % 10  == 1 and $this->boardviewers[1] != 11): ?>
                          <?= $this->boardviewers[1] ?> гость
                        <?php elseif ($this->boardviewers[1] % 10  > 1 and $this->boardviewers[1] % 10  < 5 and !($this->boardviewers[1] > 11 and $this->boardviewers[1] < 15)): ?>
                          <?= $this->boardviewers[1] ?> гостя
                        <?php else: ?>
                          <?= $this->boardviewers[1] ?> гостей
                        <?php endif; ?>
                      <?php endif; /* guests */?>
                    </font>
