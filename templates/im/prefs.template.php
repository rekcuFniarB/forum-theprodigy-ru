<table border="0" width="75%" align="center" cellspacing="0">
  <tr>
    <td valign="bottom">
       <?php $this->partial('templates/parts/linktree.template.php') ?>
    </td>
  </tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="75%" align="center" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor">
  <tr>
    <td>
      <table border="0" width="100%" cellspacing="1" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor">
        <tr>
          <td align="right" valign="bottom" class="catbg" bgcolor="<?= $this->color['catbg'] ?>" colspan="4">
            <font size="-1">
              <a href="../"><?= $this->locale->img['im_inbox'] ?></a><?= $this->menusep ?><a href="../outbox/"><?= $this->locale->img['im_outbox'] ?></a><?= $this->menusep ?><a href="../new/"><?= $this->locale->img['im_new'] ?></a>
            </font>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<table border="0"  width="75%" align="center" cellspacing="1" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor">
  <tr>
    <td class="titlebg" bgcolor="<?= $this->color['titlebg'] ?>">
      <font size="2" class="text1" color="<?= $this->color['titletext'] ?>">
        <?= $this->locale->img['im_config_small'] ?>&nbsp;<b><?= $this->locale->txt[323] ?></b>
      </font>
    </td>
  </tr>
  <tr>
    <td class="windowbg" bgcolor="<?= $this->color['windowbg'] ?>">
      <form action="." method="post">
        <table border="0" width="100%">
          <tr>
            <td valign="top" align="center" colspan="2">
              <font size="2">
                <b><?= $this->locale->txt[325] ?>:</b>
              </font>
              <br />
              <font size="1"><?= $this->locale->txt[326] ?></font><br>
              <font size="2">
                <textarea name="ignore" rows="10" cols="50"><?= $this->ignores ?></textarea>
              </font>
            </td>
          </tr>
          <tr>
            <td valign="top" align="right">
              <font size="2"><b><?= $this->locale->txt[327] ?>:</b></font>
            </td>
            <td>
              <font size="2">
                <select name="notify">
                  <option value="0" <?= $this->sel0 ?>><?= $this->locale->txt[164] ?></option>
                  <option value="1" <?= $this->sel1 ?>><?= $this->locale->txt[163] ?></option>
                </select>
              </font>
            </td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>
              <input type="submit" value="<?= $this->locale->txt[328] ?>" />
              <input type="reset" value="<?= $this->locale->txt[329] ?>" />
            </td>
          </tr>
        </table>
        <input type="hidden" name="sc" value="<?= $this->sessionid ?>" />
      </form>
    </td>
  </tr>
</table>
