        <form action="." method="post" onsubmit="submitonce(this);" name="postmodify">
                  <table  width="75%" align="center" cellpadding="0" cellspacing="0">
                    <tr>
                      <td valign="bottom" colspan="2">
                        <!-- LinkTree -->
                        <?php $this->partial('templates/parts/linktree.template.php') ?>
                      </td>
                    </tr>
                  </table>
                  <table border="0"  width="75%" align="center" cellspacing="1" cellpadding="3" bgcolor="<? $this->color['bordercolor'] ?>" class="bordercolor">
                    <tr>
                      <td class="titlebg" bgcolor="<? $this->color['titlebg'] ?>">
                        <font size="2" class="text1" color="<?= $this->color['titletext'] ?>">
                          <b><?= $this->locale->yse39 ?></b>
                        </font>
                      </td>
                    </tr>
                    <tr>
                      <td class="windowbg" bgcolor="<?= $this->color['windowbg'] ?>">
                        <input type="hidden" name="threadid" value="<? $this->threadid ?>" />
                        <input type="hidden" name="poll" value="<?= $this->poll['ID_POLL'] ?>" />
                        <table border="0" cellpadding="3" width="100%">
                          <tr>
                            <td align="right">
                              <font size="2">
                                <b><?= $this->locale->yse21 ?>:</b>
                              </font>
                            </td>
                            <td align="left">
                              <input type="text" name="question" size="40" value="<?= $this->poll['question'] ?>" />
                            </td>
                          </tr>
                          <tr>
                            <td>&nbsp;</td>
                            <td>
                              <font size="2">
                                <?php for($i=1; $i<21; $i++): ?>
                                    <?= $this->locale->yse22 ?> <?= $i ?>: <input type="text" name="option<?= $i ?>" size="25" value="<?= $this->esc($this->poll["option$i"]) ?>" /> (<?= $this->poll["votes$i"] ?> <?= $this->locale->yse42 ?>)<br />
                                <?php endfor; ?>
                              </font>
                            </td>
                          </tr>
                          <tr>
                            <td align="right">
                              <font size="2"><b><?= $this->locale->yse40 ?>:</b></font>
                            </td>
                            <td>
                              <font size="2"><input type="checkbox" name="resetVoteCount" value="on" /></font>
                              <font size="1"><?= $this->locale->yse41 ?></font>
                            </td>
                          </tr>
                          <tr>
                            <td align="right">
                              <font size="2"><b><?= $this->locale->yse30 ?>:</b></font>
                            </td>
                            <td>
                              <font size="2"><input type="checkbox" name="votingLocked" value="on" <?= $this->poll_locked ?>/></font>
                            </td>
                          </tr>
                          <tr>
                            <td align="center" colspan="2">
                              <font size="1" class="text1" color="#000000">
                                <font style="font-weight:normal" size="1"><?= $this->locale->yse25 ?></font>
                              </font>
                              <br>
                              <input type="hidden" name="waction" value="post" />
                              <input type="submit" name="post" value="<?= $this->locale->txt[105] ?>" onclick="WhichClicked('post');" accesskey="s" />
                              <input type="reset" value="<?= $this->locale->txt[278] ?>" accesskey="r" />
                            </td>
                          </tr>
                          <tr>
                            <td colspan="2"></td>
                          </tr>
                        </table>
                        </td>
                      </tr>
                    </table>
                    <input type="hidden" name="sc" value="<?= $this->sessionid ?>" />
            </form>
