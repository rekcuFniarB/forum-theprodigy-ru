<!--<table role="presentation" border="0" cellspacing="1" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor">
  <tr>
    <td class="windowbg">
      <form method="get" action='.'><br />
        <input type="submit" value="Показывать" /> <input type="text" name="shownum" size="2" maxlength="3" value="<?= $this->shownum ?>" />
        сообщений на странице
      </form>
    </td>
  </tr>
</table>
<br />-->
<script language="javascript" type="text/javascript"><!--
  function DoConfirm(message, url) {
    if (confirm(message)) location.href = url;
  }
  function invertAll(field, headerfield)
  {
    for (i = 0; i < field.length; i++) {
      if (headerfield.checked == false )
        field[i].checked = true;
      else
        field[i].checked = false;
    }
    headerfield.checked = !(headerfield.checked);
  }
//--></script>

<table role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="bottom">
        <?php $this->partial('parts/linktree.template.php') ?>
    </td>
  </tr>
</table>

<form action="./remove/" method="post" onsubmit="if (!confirm('<?= $this->locale->yse249 ?>')) return false;" style="margin-top: 0px;">
  <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor im-index-table" align="center">
    <tr>
      <td>
        <table role="presentation" border="0" width="100%" cellspacing="1" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor">
          <tr>
            <td align="right" valign="bottom" class="catbg" bgcolor="<?= $this->color['catbg'] ?>" colspan="5">
              <font size="-1">
                <a href="<?= SITE_ROOT ?>/im/<?= $this->switch_folder ?>"><?= $this->switch_folder_name ?></a><?= $this->menusep ?><a href="<?= SITE_ROOT ?>/im/new/"><?= $this->locale->img['im_new'] ?></a><?= $this->menusep ?><a href="<?= SITE_ROOT ?>/im/"><?= $this->locale->img['im_reload'] ?></a><?= $this->menusep ?>
                <?php if(!$this->nomessages): ?>
                    <a href="./removeall/"><?= $this->locale->img['im_delete'] ?></a><?= $this->menusep ?>
                <?php endif; ?>
                <a href="<?= SITE_ROOT ?>/im/prefs/"><?= $this->locale->img['im_config'] ?></a>
              </font>
            </td>
          </tr>
          <tr class="im-index-table-caption">
            <td class="titlebg" bgcolor="<?= $this->color['titlebg'] ?>" width="300">
              <font size="2" class="text1" color="<?= $this->color['titletext'] ?>">&nbsp;<b><?= $this->locale->txt[317] ?></b></font>
            </td>
            <td class="titlebg" bgcolor="<?= $this->color['titlebg'] ?>">
              <font size="2" class="text1" color="<?= $this->color['titletext'] ?>"><b><?= $this->locale->txt[318] ?></b></font>
            </td>
            <td class="titlebg" bgcolor="<?= $this->color['titlebg'] ?>">
              <font size="2" class="text1" color="<?= $this->color['titletext'] ?>">
                <b><?= $this->locale->txt[319] ?></b>
              </font>
            </td>
            <td class="titlebg" bgcolor="<?= $this->color['titlebg'] ?>">
              <font size="2" class="text1" color="<?= $this->color['titletext'] ?>">
                <?= $this->locale->txt['yse138'] ?>
              </font>
            </td>
            <td class="titlebg" bgcolor="<?= $this->color['titlebg'] ?>" align="center">
              <input class="titelbg" style="background-color:<?= $this->color['titlebg'] ?>" type="checkbox" onclick="invertAll(this.form, this)"/>
            </td>
          </tr>
          
          <?php if($this->nomessages): ?>
              <tr>
                <td class="windowbg" colspan="5" bgcolor="<?= $this->color['windowbg'] ?>">
                  <font size="2"><?= $this->locale->txt[151] ?></font>
                </td>
              </tr>
          <?php endif; ?>
          
          <?php foreach($this->messages as $n => $msg): ?>
            <tr class="im-index-item">
              <td class="im-index-date <?= $msg['windowcss'] ?>" bgcolor="<?= $msg['windowbg'] ?>" width="300">
                <font size="2"><?= $msg['msgtime'] ?></font>
              </td>
              <td class="im-index-username <?=  $msg['windowcss'] ?>" bgcolor="<?= $msg['windowbg'] ?>">
                <font size="2"><?= $this->esc($msg['author']['realName']) ?></font>
              </td>
              <td class="im-index-title <?= $msg['windowcss'] ?>" bgcolor="<?= $msg['windowbg'] ?>">
                <?php if($msg['notice']): ?>
                  <img src="<?= $this->imagesdir ?>/exclamation.gif" alt="[NOTICE]" title="NOTICE">
                <?php endif; ?>
                <font size="2"><a href="#<?= $msg['ID_IM'] ?>"><?= $this->esc($msg['subject']) ?></a></font>
              </td>
              <td class="im-index-del <?= $msg['windowcss'] ?>" bgcolor="<?= $msg['windowbg'] ?>">
                <font size="2">
                  <a href="javascript:DoConfirm('<?= addslashes($this->locale->txt[154]) ?>?','./remove/<?= $msg['ID_IM'] ?>/?sesc=<?= $this->sessionid ?>');"><?= $this->locale->img['im_remove'] ?></a>
                </font>
              </td>
              <td class="im-index-checkbox <?= $msg['windowcss'] ?>" bgcolor="<?= $msg['windowbg'] ?>" align="center">
                <input class="<?= $msg['windowcss'] ?>" type="checkbox" name="delete_<?= $msg['ID_IM'] ?>" />
              </td>
            </tr>
          <?php endforeach; ?>
          
          <tr>
            <td bgcolor="<?= $this->windowbg ?>" class="windowbg2" style="padding: 2px;" align="right" colspan="5">
              <input type="submit" value="<?= $this->locale->yse138 ?>" />
              <a href="./removeall/"><?= $this->locale->img['im_delete'] ?></a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <input type="hidden" name="sc" value="<?= $this->sessionid ?>" />
</form>
<br />

<?php $this->partial('parts/simple_pagination.template.php'); ?>

<?php if(!$this->nomessages): ?>
  <table role="presentation" border="0" width="100%" cellspacing="1" cellpadding="4" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor instant-messages-page">
    <tr>
      <td class="titlebg" bgcolor="<? $this->color['titlebg'] ?>">
        <font size="2" class="text1" color="<? $this->color['titletext'] ?>">
          &nbsp;<b><?= $this->locale->txt[29] ?></b>
        </font>
      </td>
      <td class="titlebg" bgcolor="<? $this->color['titlebg'] ?>">
        <font size="2" class="text1" color="<? $this->color['titletext'] ?>">
          <b><?= $this->locale->txt[118] ?></b>
        </font>
      </td>
    </tr>
    
    <?php foreach($this->messages as $n => $msg): ?>
      <tr class="instant-message">
        <td class="instant-message-userinfo <?= $msg['windowcss'] ?>" bgcolor="<?= $msg['windowbg'] ?>" width="160" valign="top" height="100%" rowspan="2">
          <?php if($msg['author']['found']): ?>
            <a href="<?= SITE_ROOT ?>/people/<?= urlencode($msg['author']['name']) ?>" class="instant-message-author"><font class="imgcatbg"><?= $this->esc($msg['author']['realName']) ?></font></a>
          <?php else: ?>
            <span class="instant-message-author"><?= $this->esc($msg['author']['name']) ?></span>
          <?php endif; ?>
          <br />
          <font size="1">
            <?= $this->esc($msg['author']['usertitle']) ?>
            <br>
            <?= $msg['author']['memberinfo'] ?>
            <br />
            <?= $msg['author']['memberstar'] ?>
            <br /><br />
            
            <?php if($this->conf->onlineEnable && $msg['author']['found']): ?>
              <?php if($msg['isOnline']): ?>
                <a href="<?= SITE_ROOT?>/im/new/<?= urlencode($msg['author']['name']) ?>/"><?= $this->locale->online2 ?></a>
              <?php else: ?>
                <a href="<?= SITE_ROOT?>/im/new/<?= urlencode($msg['author']['name']) ?>/"><?= $this->locale->online3 ?></a>
              <?php endif; ?>
              <br><br>
            <?php endif; ?>
            
            <?php if($msg['author']['found']): ?>
              <?= $this->locale->txt[26] ?>: <?= $msg['author']['posts'] ?>
              <br />
            <?php endif; ?>
            <?= $msg['author']['gender'] ?>
            <?= $msg['author']['avatar'] ?>
            <?= $this->esc($msg['author']['personalText']) ?>
            <?= $msg['author']['websiteUrl'] ?>
            <?= $msg['author']['yimon'] ?>
            <?= $msg['author']['ICQ'] ?>
            <?= $msg['author']['MSN']?>
            <?= $msg['author']['AIM'] ?>
          </font>
        </td>
        <td class="instant-message-body-container <?= $msg['windowcss'] ?>" bgcolor="<?= $msg['windowbg'] ?>" valign="top" height="100%">
          <table role="presentation" border="0" cellspacing="0" cellpadding="3" width="100%" align="center" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor">
            <tr class="instant-message-caption <?= $msg['windowcss'] ?>" bgcolor="<?= $msg['windowbg'] ?>" id="<?= $msg['ID_IM'] ?>">
              <td class="instant-message-subject <?= $msg['windowcss'] ?>" bgcolor="<?= $msg['windowbg'] ?>">
                <?php if($msg['notice']): ?>
                  <img src="<?= $this->imagesdir ?>/exclamation.gif" alt="[NOTICE]" title="NOTICE">
                <?php endif; ?>
                <font size="1">&nbsp;<b><?= $this->esc($msg['subject']) ?></b></font>
              </td>
              <td class="insntant-message-date <?= $msg['windowcss'] ?>" bgcolor="<?= $msg['windowbg'] ?>" align="right">
                <font size="1"><?= $msg['msgtime'] ?></font>
              </td>
            </tr>
            <tr>
              <td class="instant-message-body <?= $msg['windowcss'] ?>" bgcolor="<?= $msg['windowbg'] ?>" colspan="2">
                <hr width="100%" size="1" style="color: <?= $this->color['windowbg3'] ?>" />
                <font size="2">
                  <?php if($this->conf->enable_ubbc): ?>
                    <?= $this->doubbc($msg['body']) ?>
                  <?php else: ?>
                    <?= $this->esc($msg['body']) ?>
                  <?php endif; ?>
                </font>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr class="instant-message-footer">
        <td class="<?= $msg['windowcss'] ?>" bgcolor="<?= $msg['windowbg'] ?>" colspan="2" valign="bottom">
          <?= $msg['author']['signature'] ?>
          <hr width="100%" size="1" style="color: <?= $this->color['windowbg3'] ?>" />
          <table role="presentation" width="100%">
            <tr>
              <td class="instant-message-user-links <?= $msg['windowcss'] ?>" bgcolor="<?= $msg['windowbg'] ?>" height="10" align="left" valign="bottom">
                <font class="imgcatbg">
                  <?php $this->menu_begin() ?>
                  <?php if(!empty($msg['author']['websiteUrl_IM'])): ?>
                    <?php $this->menusep() ?>
                    <?= $msg['author']['websiteUrl_IM'] ?>
                  <?php endif; ?>
                  <?php if ($msg['show_email']): ?>
                    <?php $this->menusep() ?>
                    <a href="mailto:<?= $this->esc($msg['author']['emailAddress']) ?>"><font class="imgcatbg"><?= $this->locale->img['email'] ?></font></a>
                  <?php endif; ?>
                  <?php if ($this->profilebutton && $msg['author']['found']): ?>
                    <?php $this->menusep() ?>
                    <a href="<?= SITE_ROOT ?>/people/<? urlencode($msg['author']['name']) ?>/"><?= $this->locle->img['viewprofile'] ?></a>
                  <?php endif; ?>
                </font>
              </td>
              <td class="instant-message-actions <?= $msg['windowcss'] ?>" bgcolor="<?= $msg['windowbg'] ?>" height="10" align="right" valign="bottom">
                <font size="2">
                  <?php if($msg['author']['found']): ?>
                    <a href="<?= SITE_ROOT ?>/im/quote/<?= $msg['ID_IM']?>/"><font class="imgcatbg"><?= $this->locale->img['replyquote'] ?></font></a><font class="imgcatbg"><?= $this->menusep ?></font><a href="<?= SITE_ROOT ?>/im/reply/<?= $msg['ID_IM']?>/"><font class="imgcatbg"><?= $this->locale->img['im_reply'] ?></font></a><font class="imgcatbg"><?= $this->menusep ?></font>
                  <?php endif; ?>
                  
                  <a href="javascript:DoConfirm('<?= addslashes($this->locale->txt[154]) ?>?', './remove/<?= $msg['ID_IM'] ?>/?sesc=<?= $this->sessionid ?>');"><font class="imgcatbg"><?= $this->locale->img['im_remove'] ?></font></a>
                </font>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; // nomessages?>

