<table border="0" width="75%" cellpadding="3" align="center" cellspacing="0">
  <tr>
    <td valign="bottom">
      <?php if($this->show_warning): ?>
        <div class="titlebg" style="margin: 30px 0; padding: 10px; text-align: center; font-weight: bold; font-size: 150%">
          “ы уже отправл€л недавно жалобу на сообщение из этой темы.<br />
          ƒостаточно одной жалобы, чтобы модераторы обратили внимание на все сообщени€ темы!
        </div>
      <?php endif; ?>
      <?php $this->partial('templates/im/linktree.part.php') ?>
    </td>
  </tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="75%" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor" align="center">
  <tr>
    <td>
      <table border="0" width="100%" cellspacing="1" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor">
        <tr>
          <td align="right" valign="bottom" class="catbg" bgcolor="' . $color['catbg'] . '" colspan="4">
            <font size="-1">
              <a href="<?= SITE_ROOT ?>/im/"><?= $this->locale->img['im_inbox'] ?></a><?= $this->menusep ?><a href="<?= SITE_ROOT ?>/im/outbox/"><?= $this->locale->img['im_outbox'] ?></a><?= $this->menusep ?><a href="<?= SITE_ROOT ?>/im/prefs/"><?= $this->locale->img['im_config'] ?></a>
            </font>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<table border="0" width="75%" align="center" cellpadding="3" cellspacing="1" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor message-input-form-container">
  <tr>
    <td class="titlebg" bgcolor="<?= $this->color['titlebg'] ?>">
      <font size="2" class="text1" color="<?= $this->color['titletext'] ?>">
        <?= $this->locale->img['im_new_small'] ?>&nbsp;<b><?= $this->locale->txt[321] ?></b>
      </font>
    </td>
  </tr>
    <tr>
      <td class="windowbg" bgcolor="<?= $this->color['windowbg'] ?>">
        <form action="<?= SITE_ROOT ?>/im/new/" method="post" name="postmodify">
          <input type="hidden" name="form_type" value="<?= $this->form_type ?>"/>
          <table border="0" cellpadding="3" width="100%">
          <tr>
            <td align="right">
              <font size="2">
                <b><?= $this->locale->txt[150] ?>:</b>
              </font>
            </td>
            <td>
              <font size="2">
                <input type="text" name="to" value="<?= $this->get('imto') ?>" size="20" maxlength="128" required/>
                <font size="1"><?= $this->locale->txt[748] ?></font>
              </font>
            </td>
          </tr>
          <tr>
            <td align="right">
              <font size="2"><b><?= $this->locale->txt[70] ?>:</b></font>
            </td>
            <td>
              <font size="2">
                <input type="text" name="naztem" value="<?= $this->get('form_subject') ?>" size="40" maxlength="128" required/>
              </font>
            </td>
          </tr>
          
          <?php $this->partial('templates/thread/postbox.template.php') ?>
          
          <tr>
            <td align="center" colspan="2">
              <input type="hidden" name="waction" value="imsend" />
              <input type="submit" value="<?= $this->locale->txt[148] ?>" onclick="WhichClicked('imsend');" accesskey="s" />
              <!--<input type="submit" name="preview" value="<?= $this->locale->txt[507] ?>" onclick="WhichClicked('previewim');" />-->
              <input type="button" name="preview" value="<?= $this->locale->txt[507] ?>" onclick="Forum.Utils.previewPost(this);" accesskey="p">
              <input type="reset" value="<?= $this->locale->txt[329] ?>" />
            </td>
          </tr>
        </table>
        <input type="hidden" name="sc" value="'<?= $this->sessionid ?>"/>
        <?php if($this->is_report_field): ?>
          <input type="hidden" name="is_report" value="1">
        <?php endif; ?>
      </form>
    </td>
  </tr>
</table>

<?php if($this->is_reply): ?>
  <br /><br />
  <table class="bordercolor" width="100%" border="0" cellspacing="1" cellpadding="4">
    <tr>
      <td colspan="2" class="windowbg" bgcolor="<?= $this->color['windowbg'] ?>">
        <b><?= $this->locale->txt[319] ?>: <?= $this->get('form_subject') ?></b>
      </td>
    </tr>
    <tr>
      <td class="windowbg2" bgcolor="' . $color['windowbg2'] . '"><?= $this->locale->txt[318] ?>:
        <?php if($this->reply_msg['author']['found']): ?>
            <a href="<?= SITE_ROOT ?>/people/<?= urlencode($this->reply_msg['author']['name']) ?>/"><?= $this->esc($this->reply_msg['author']['realName']) ?></a>
        <?php else: ?>
            <?= $this->esc($this->reply_msg['author']['realName']) ?>
        <?php endif; ?>
      </td>
      <td align="right" class="windowbg2" bgcolor="<?= $this->color['windowbg'] ?>">
        <?= $this->locale->txt[30] ?>: <?= $this->reply_msg['msgtime'] ?>
      </td>
    </tr>
    <tr>
      <td class="windowbg" bgcolor="<?= $this->color['windowbg'] ?>" colspan="2">
        <?= $this->doubbc($this->reply_msg['body']) ?>
      </td>
    </tr>
  </table>
<?php endif; ?>
