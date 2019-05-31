        <br><br>
        <form name="frmLogin" action="<?= SITE_ROOT ?>/login/" method="post">
          <table border="0" width="400" cellspacing="1" cellpadding="0" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor" align="center">
            <tr>
              <td class="windowbg" bgcolor="<?= $this->conf->color['windowbg'] ?>" width="100%">
                <table width="100%" cellspacing="0" cellpadding="3">
                  <tr>
                    <td class="titlebg" bgcolor="<?= $this->conf->color['titlebg'] ?>'">
                      <img src="<?= $this->conf->imagesdir ?>/login_sm.gif" alt="">
                      <font size="2" class="text1" color="<?= $this->conf->color['titletext'] ?>">
                        <b><?= $this->locale->txt[34] ?></b></font>
                    </td>
                    <td align="right" class="titlebg">
                      <?php if(!$this->request->isSecure()): ?>
                        <a href="https://<?= $this->host ?><?= SITE_ROOT ?>/login/" title="<?= $this->locale->txt('ssl-link-info') ?>" class="ssl-login"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/lock_closed.png">SSL</a>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2"><?= $this->comment ?></td>
                  </tr>
                  <tr>
                    <td align="right" class="windowbg" bgcolor="<?= $this->conf->color['windowbg'] ?>">
                      <font size="2"><b><?= $this->locale->txt[35] ?>:</b></font>
                    </td>
                    <td class="windowbg" bgcolor="<?= $this->conf->color['windowbg'] ?>">
                      <font size="2">
                        <input type="text" name="user" size="20" value="<?= $this->get('inputuser') ?>">
                      </font>
                    </td>
                  </tr>
                  <tr>
                    <td align="right" class="windowbg" bgcolor="<?= $this->conf->color['windowbg'] ?>">
                      <font size="2"><b><?= $this->locale->txt[36] ?>:</b></font>
                    </td>
                    <td class="windowbg" bgcolor="<?= $this->conf->color['windowbg'] ?>">
                      <font size="2">
                        <input type="password" name="password" size="20">
                      </font>
                    </td>
                  </tr>
                  <tr>
                    <td align="right" class="windowbg" bgcolor="<?= $this->conf->color['windowbg'] ?>">
                      <font size="2"><b><?= $this->locale->txt[497] ?>:</b></font>
                    </td>
                    <td class="windowbg" bgcolor="<?= $this->conf->color['windowbg'] ?>">
                      <font size="2">
                        <input type="text" name="cookielength" size="4" maxlength="4" value="<?= $this->conf->Cookie_Length ?>">
                      </font>
                    </td>
                  </tr>
                  <tr>
                    <td align="right" class="windowbg" bgcolor="<?= $this->conf->color['windowbg'] ?>">
                      <font size="2"><b><?= $this->locale->txt[508] ?>:</b></font>
                    </td>
                    <td class="windowbg" bgcolor="<?= $this->conf->color['windowbg'] ?>">
                      <font size="2">
                        <input type="checkbox" name="cookieneverexp" value="ON" checked>
                      </font>
                    </td>
                  </tr>
                  <tr>
                    <td align="center" colspan="2" class="windowbg" bgcolor="<?= $this->conf->color['windowbg'] ?>">
                      <br/>
                      <input type="submit" value="<?= $this->locale->txt[34] ?>">
                    </td>
                  </tr>
                  <tr>
                    <td align="center" colspan="2" class="windowbg" bgcolor="<?= $this->conf->color['windowbg'] ?>">
                      <a href="<?= SITE_ROOT ?>/passwordreset/?what=input_user"><small><?= $this->locale->txt[315] ?></small></a>
                      <br><br>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </form>
        <script language="JavaScript" type="text/javascript"><!--
          document.frmLogin.user.focus();
        //--></script>
