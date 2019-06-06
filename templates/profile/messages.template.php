    <h3><?= $this->get('title') ?></h3>
    <p align=left>
      <a href="<?= SITE_ROOT ?>/people/<?= urlencode($this->profile['name']) ?>/"><font size="2"><b><?= $this->locale->txt[92] ?> <?= $this->esc($this->profile['realName']) ?></b></font></a>
    </p>
    <?php foreach($this->messages as $n => $msg): ?>
      <table border="0" width="100%" cellspacing="1" bgcolor="<?= $this->color['bordercolor'] ?>" class="comments-page">
        <tr>
          <td align=left bgcolor="<?= $this->color['titlebg'] ?>" class="titlebg">
            <font class="text1" color="<?= $this->color['titletext'] ?>" size="2">&nbsp;<?= $n ?>&nbsp;</font>
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
          <td colspan="3" bgcolor="<?= $this->color['catbg'] ?>" class="windowbg message-buttons">
            <font size="2">
              &nbsp;<a href="<?= SITE_ROOT ?>/b<?= $msg['ID_BOARD'] ?>/t<?= $msg['ID_TOPIC'] ?>/reply/" class="button"><?= $this->locale->img['reply_sm'] ?></a><?= $this->menusep ?><a href="<?= SITE_ROOT ?>/b<?= $msg['ID_BOARD'] ?>/t<?= $msg['ID_TOPIC'] ?>/reply/<?= $msg['ID_MSG'] ?>/" class="button"><?= $this->locale->img['replyquote'] ?></a>
              <?php if($this->notify): ?>
                  <?= $this->menusep ?><a href="<?= SITE_ROOT ?>/b<?= $msg['ID_BOARD'] ?>/t<?= $msg['ID_TOPIC'] ?>/notify/" class="button"><?= $this->locale->img['notify_sm'] ?></a>
              <?php endif; ?>
            </font>
          </td>
        </tr>
      </table>
      <br>
    <?php endforeach; ?>
    
    <?php $this->partial('templates/parts/simple_pagination.template.php') ?>
    
    <p align=left>
      <a href="<?= SITE_ROOT ?>/people/<?= urlencode($this->profile['name']) ?>/"><font size="2"><b><?= $this->locale->txt[92] ?> <?= $this->esc($this->profile['realName']) ?></b></font></a>
    </p>
