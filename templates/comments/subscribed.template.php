<h2><?= $this->get('title') ?></h2>

<p align=left><a href="<?= SITE_ROOT ?>/people/<?= rawurlencode($this->username) ?>/"><font size="2"><b><?= $this->locale->txt[92] ?> <?= $this->esc($this->realname) ?></b></font></a></p>

<p>
  <a href="<?= SITE_ROOT ?>/comments/to/<?= rawurlencode($this->username) ?>/"><b>Смотреть последние комментарии под сообщениями пользователя <?= $this->esc($this->realname) ?></b></a>
</p>
<p>
  <a href="<?= SITE_ROOT ?>/comments/by/<?= rawurlencode($this->username) ?>/"><b>Смотреть последние комментарии от пользователя <?= $this->esc($this->realname) ?></b></a>
</p>

<form action="." method="POST">
  <input type="submit" value="Показать">
  <input type="text" size="3" name="viewscount" maxlength="3" value="<?= $this->messageNumber ?>"> сообщений
</form>

<?php foreach($this->messages as $k => $msg): ?>
    <table class="comments-page" border="0" width="100%" cellspacing="1" bgcolor="<?= $this->color['bordercolor'] ?>">
      <tr>
        <td align=left bgcolor="<?= $this->color['titlebg'] ?>" class="titlebg">
          <font class="text1" color="<?= $this->color['titletext'] ?>" size="2">&nbsp;<?= $k + 1 ?>&nbsp;</font>
        </td>
        <td width=75% bgcolor="<?= $this->color['titlebg'] ?>" class="titlebg">
          <font class="text1" color="<?= $this->color['titletext'] ?>" size="2">
            <b>&nbsp;<?= $this->esc($msg['cname']) ?> / <?= $this->esc($msg['bname']) ?> / <a href="<?= SITE_ROOT ?>/b<?= $msg['ID_BOARD'] ?>/t<?= $msg['ID_TOPIC'] ?>/msg<?= $msg['ID_MSG'] ?>/#msg<?= $msg['ID_MSG'] ?>"><font class="text1" color="<?= $this->color['titletext'] ?>" size="2"><?= $this->esc($msg['subject']) ?></font></a></b>
          </font>
        </td>
        <td align="right" bgcolor="<?= $this->color['titlebg'] ?>" class="titlebg">
          <span style="white-space:nowrap">&nbsp;<font class="text1" color="<?= $this->color['titletext'] ?>" size="2"><?= $msg['posterTime'] ?>&nbsp;</font></span>
        </td>
      </tr>
      <tr height=50>
        <td colspan="3" bgcolor="<?= $this->color['windowbg2'] ?>" valign="top" class="windowbg2">
          <font size="2">
            <?php if($this->ubbc): ?>
              <?= $this->doubbc($msg['body']) ?>
            <?php else: ?>
              <?= $this->esc($msg['body']) ?>
            <?php endif; ?>
          </font>
        </td>
      </tr>
      <tr>
        <td colspan="3" bgcolor="<?= $this->color['windowbg2'] ?>" valign="top" class="windowbg2">
          <?php $this->_partial('comments/comments.template.php', array('msgid' => $msg['ID_MSG'], 'msg' => $msg)); ?>
        </td>
      </tr>
      <tr>
        <td colspan="3" bgcolor="<?= $this->color['catbg'] ?>" class="windowbg message-buttons">
          <font size="2">
            &nbsp;<a href="<?= SITE_ROOT ?>/b<?= $msg['ID_BOARD'] ?>/t<?= $msg['ID_TOPIC'] ?>/reply/"><?= $this->locale->img['reply_sm'] ?></a><?= $this->menusep ?><a href="<?= SITE_ROOT ?>/b<?= $msg['ID_BOARD'] ?>/t<?= $msg['ID_TOPIC'] ?>/reply/<?= $msg['ID_MSG'] ?>/"><?= $this->locale->img['replyquote'] ?></a> 
            <?php if($this->notification): ?>
                <?= $this->menusep ?><a href="<?= SITE_ROOT ?>/b<?= $msg['ID_BOARD'] ?>/t<?= $msg['ID_TOPIC'] ?>/notify/"><?= $this->locale->img['notify_sm'] ?></a>
            <?php endif; ?>
          </font>
        </td>
      </tr>
    </table>
    <br>
<?php endforeach; ?>

<?php if(empty($this->messages)): ?>
    <?= $this->locale->txt[170] ?><br>
<?php endif; ?>
    
<p align=left>
    <a href="<?= SITE_ROOT ?>/people/<?= rawurlencode($this->username) ?>/"><font size="2"><b><?= $this->locale->txt[92] ?> <?= $this->esc($this->realname) ?></b></font></a>
</p>
