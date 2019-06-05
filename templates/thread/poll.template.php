      <table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor" align="center">
        <tr>
          <td>
            <table cellpadding="3" cellspacing="0" width="100%">
              <tr>
                <td valign="middle" align="left" bgcolor="<?= $this->conf->color['titlebg'] ?>" class="titlebg">
                  <font size="2" class="text1" color="<?= $this->conf->color['titletext'] ?>">&nbsp;<img src="<?= $this->conf->imagesdir ?>/<?= $this->pollinfo['pollimage'] ?>.gif" alt=""> <b><?= $this->locale->yse43 ?></b></font>
                </td>
              </tr>
            </table>
            <table cellpadding="3" cellspacing="1" border="0" width="100%">
              <tr>
                <td valign="middle" align="left" bgcolor="<?= $this->conf->color['windowbg'] ?>" class="windowbg">
                  <table border="0">
                    <tr>
                      <td class="windowbg" valign="top">
                        <font size="2"><b><?= $this->locale->yse21?>:</b></font>
                      </td>
                      <td class="windowbg">
                        <font size="2">
                          <?= $this->doUBBC($this->pollinfo['question']) ?>
                        </font>
                        
                        <?php $this->start_cache('pollLockEditBtns') ?>
                          <?php if($this->editpoll_btn): ?>
                            <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $this->thread ?>/editpoll/"><?= $this->locale->yse39 ?></a>
                          <?php endif; ?>
                        <?php $this->end_cache(); ?>
                        
                        <?php if($this->viewResults): ?>
                          <table>
                            <tr>
                              <td>
                                <br>
                                <table border="0" cellpadding="0" cellspacing="0">
                                  <?php for ($i = 1; $i <= 20; $i++): ?>
                                    <?php if($this->pollinfo["option$i"] != ''): ?>
                                      <?php 
                                        $bar = floor(($this->pollinfo["votes$i"] / $this->pollinfo['divisor']) * 100);
                                        $barWide = (($bar == 0) ? 1 : floor(($bar * 5) / 3));
                                      ?>
                                      <tr>
                                        <td class="windowbg"><?= $this->doUBBC($this->pollinfo["option$i"]) ?></td>
                                        <td width="7">&nbsp;</td>
                                        <td class="windowbg" nowrap="nowrap">
                                          <img src="<?= $this->conf->imagesdir ?>/poll_left.gif" alt=""><img src="<?= $this->conf->imagesdir ?>/poll_middle.gif" width="<?= $barWide ?>" height="12" alt=""><img src="<?= $this->conf->imagesdir ?>/poll_right.gif" alt=""><?= $this->pollinfo["votes$i"] ?> (<?= $bar ?>%)
                                        </td>
                                      </tr>
                                    <?php endif; ?>
                                  <?php endfor; ?>
                                </table>
                              </td>
                            <td width="15">&nbsp;</td>
                            <td valign="bottom"><?php $this->get_cache('pollLockEditBtns'); ?></td>
                          </tr>
                          <tr>
                            <td>
                              <b><?= $this->locale->yse24 ?>: <?= $this->pollinfo['totalvotes'] ?></b>
                            </td>
                            <td>&nbsp;</td>
                          </tr>
                        </table>
                        <br>
                        <?php else: ?>
                          <form action="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $this->thread ?>/vote/<?= $this->topicinfo['ID_POLL'] ?>/" method="post">
                            <table>
                              <tr>
                                <td>
                                  <?php for($i = 1; $i <= 20; $i++): ?>
                                    <?php if ($this->pollinfo["option$i"] != ''): ?>
                                      <font><input type="radio" name="option" value="<?=  $i ?>"><?= $this->doUBBC($this->pollinfo["option$i"]) ?>
                                      </font><br>
                                    <?php endif; ?>
                                  <?php endfor; ?>
                                </td>
                                <td width="15">&nbsp;</td>
                                <td valign="bottom">
                                  <a href="./?viewResults=1"><?= $this->locale->yse29 ?></a> <?php $this->get_cache('pollLockEditBtns'); ?>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <input type="submit" value="<?= $this->locale->yse23 ?>">
                                </td>
                                <td>&nbsp;</td>
                              </tr>
                            </table>
                            <input type="hidden" name="sc" value="<?= $this->app->session->id ?>">
                          </form>
                        <?php endif; ?>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
