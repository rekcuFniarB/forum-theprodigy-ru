      <form action="<?= SITE_ROOT ?>/login/" method="post">
        <table border="0" cellspacing="1" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor" align="center">
          <tr>
            <td class="titlebg" bgcolor="<?= $this->conf->color['titlebg'] ?>">
              <font size="2" class="text1" color="<?= $this->conf->color['titletext'] ?>">
                <b><?= $this->locale->txt[633] ?></b>
              </font>
            </td>
          </tr>
          <tr>
            <td class="windowbg" bgcolor="<?= $this->conf->color['windowbg'] ?>">
              <font size="2">
                <br>
                <?= $this->locale->txt[634] ?>
                <br>
                <?= $this->locale->txt[635] ?> <a href="<?= SITE_ROOT ?>/register/"><?= $this->locale->txt[636] ?></a> <?= $this->locale->txt[637] ?>
                <br>
                <br>
              </font>
            </td>
          </tr>
          <tr>
            <td class="titlebg" bgcolor="<?= $this->conf->color['titlebg'] ?>">
              <font size="2" class="text1" color="<?= $this->conf->color['titletext'] ?>">
                <b><?= $this->locale->txt[34] ?></b>
              </font>
            </td>
          </tr>
          <tr>
            <td class="windowbg" bgcolor="<?= $this->conf->color['windowbg'] ?>">
              <?php if(!$this->request->isSecure()): ?>
                <div style="text-align: right;">
                  <a href="https://<?= $this->host ?><?= SITE_ROOT ?>/login/" title="<?= $this->locale->txt['ssl-link-info'] ?>" class="ssl-login"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/lock_closed.png">SSL</a>
                </div>
              <?php endif; ?>
              <font size="2">
                <table border="0" align="left">
                  <tr>
                    <td align="right">
                      <font size="2">
                        <b><?= $this->locale->txt[35] ?>:</b>
                      </font>
                    </td>
                    <td>
                      <font size="2"><input type="text" name="user" size="20"></font>
                    </td>
                  </tr>
                  <tr>
                    <td align="right">
                      <font size="2"><b><?= $this->locale->txt[36] ?>:</b></font>
                    </td>
                    <td>
                      <font size="2"><input type="password" name="password" size="20"></font>
                    </td>
                  </tr>
                  <tr>
                    <td align="right">
                      <font size="2"><b><?= $this->locale->txt[497] ?>:</b></font>
                    </td>
                    <td>
                      <font size="2"><input type="text" name="cookielength" size="4" maxlength="4" value="<?= $this->conf->Cookie_Length ?>"></font>
                    </td>
                  </tr>
                  <tr>
                    <td align="right">
                      <font size="2"><b><?= $this->locale->txt[508] ?>:</b></font>
                    </td>
                    <td>
                      <font size="2"><input type="checkbox" name="cookieneverexp"></font>
                    </td>
                  </tr>
                  <tr>
                    <td align="center" colspan="2">
                      <br>
                      <input type="submit" value="<?= $this->locale->txt[34] ?>">
                    </td>
                  </tr>
                  <tr>
                    <td align="center" colspan="2">
                      <small><a href="<?= SITE_ROOT ?>/passwordreset/?what=input_user"><?= $this->locale->txt[315] ?></small></a>
                    <br>
                    <br>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </form>
