            <br><br>
            <table cellspacing="1" cellpadding="0" width="75%" align="center" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor">
              <tr>
                <td>
                  <table class="windowbg" cellspacing="1" cellpadding="2" width="100%" align="center" bgcolor="<?= $this->conf->color['windowbg'] ?>">
                    <tr>
                      <td class="titlebg" bgcolor="<?= $this->conf->color['titlebg'] ?>" colspan="2">
                        <font size="2" class="text1" color="<?= $this->conf->color['titletext'] ?>">
                          <b><?= $this->locale->txt[468] ?></b>
                        </font>
                      </td>
                    </tr>
                    <?php foreach($this->thread_summary as $msg): ?>
                        <tr>
                          <td align="left" class="catbg">
                            <font size="1">
                              <?= $this->locale->txt[279] ?>: <?= $msg['userinfo']['realName'] ?>
                            </font>
                          </td>
                          <td class="catbg" align="right">
                            <font size="1">
                              <?= $this->locale->txt[280] ?>: <?= $msg['time'] ?>
                            </font>
                          </td>
                        </tr>
                        <tr>
                          <td class="windowbg2" colspan="2" bgcolor="<?= $this->conf->color['windowbg2'] ?>">
                            <font size="1">
                              <?php if($this->conf->enable_ubbc): ?>
                                  <?= $this->doubbc($msg['body']) ?>
                              <?php else: ?>
                                  <?= $this->esc($msg['body']) ?>
                              <?php endif; ?>
                            </font>
                          </td>
                        </tr>
                    <?php endforeach; ?>
                  </table>
                </td>
              </tr>
            </table>

          
