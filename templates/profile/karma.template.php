<table border="0" width="100%" cellpadding="3" cellspacing="1" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor karma-page">
  <tr>
    <td colspan="2">
      <span style="font-size: 18px; font-weight: bold">Заценки сообщений пользователя</span> <a href="../"><span style="font-size: 24px; font-weight: bold"><?= $this->realName ?></span></a>
      <br>
      <?php if(!empty($this->topApplauds)): ?>
        <table border="0" cellpadding="3" cellspacing="1" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor karma-page-summary">
          <tr>
            <td colspan="2" class="windowbg" align="center"><b>ТОП 10 поощрений</b></td>
          </tr>
          <?php foreach($this->topApplauds as $act): ?>
            <tr class="karma-page-summary-item">
              <td class="windowbg">
                <a href="<?= SITE_ROOT ?>/people/<?= rawurlencode($act['user1']) ?>/"><?= $this->esc($act['user1_name']) ?> (<?= $this->esc($act['user1']) ?>)</a>
              </td>
              <td class="windowbg"><?= $act['k'] ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
      
      <?php if(!empty($this->topSmites)): ?>
        <table border="0" cellpadding="3" cellspacing="1" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor karma-page-summary">
          <tr>
            <td colspan="2" class="windowbg" align="center"><b>ТОП 10 покараний</b></td>
          </tr>
          <?php foreach($this->topSmites as $act): ?>
            <tr class="karma-page-summary-item">
              <td class="windowbg">
                <a href="<?= SITE_ROOT ?>/people/<?= rawurlencode($act['user1']) ?>/"><?= $this->esc($act['user1_name']) ?> (<?= $this->esc($act['user1']) ?>)</a>
              </td>
              <td class="windowbg"><?= $act['k'] ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
      
      <?php if(!empty($this->weekApplauds)): ?>
        <table border="0" cellpadding="3" cellspacing="1" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor karma-page-summary">
          <tr>
            <td colspan="2" class="windowbg" align="center"><b>ТОП 10 поощрений за 7 дней</b></td>
          </tr>
          <?php foreach($this->weekApplauds as $act): ?>
            <tr class="karma-page-summary-item">
              <td class="windowbg">
                <a href="<?= SITE_ROOT ?>/people/<?= rawurlencode($act['user1']) ?>/"><?= $this->esc($act['user1_name']) ?> (<?= $this->esc($act['user1']) ?>)</a>
              </td>
              <td class="windowbg"><?= $act['k'] ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
      
      <?php if(!empty($this->weekSmites)): ?>
        <table border="0" cellpadding="3" cellspacing="1" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor karma-page-summary">
          <tr>
            <td colspan="2" class="windowbg" align="center"><b>ТОП 10 поощрений за 7 дней</b></td>
          </tr>
          <?php foreach($this->weekSmites as $act): ?>
            <tr class="karma-page-summary-item">
              <td class="windowbg">
                <a href="<?= SITE_ROOT ?>/people/<?= rawurlencode($act['user1']) ?>/"><?= $this->esc($act['user1_name']) ?> (<?= $this->esc($act['user1']) ?>)</a>
              </td>
              <td class="windowbg"><?= $act['k'] ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
      
    </td>
  </tr>
  
  <tr>
    <td class="windowbg" colspan="2">
      <form method="get"><br />
        <input type="submit" value="Показать" /> <input type="text" name="num" size="2" maxlength="3" value="<?= $this->num ?>" /> заценок на странице, начиная с <input type="text" name="offset" size="2" maxlength="3" value="<?= $this->offset ?>" /><br /><br />
      </form>
    </td>
  </tr>
  
  <?php foreach($this->actions as $id => $karmas): ?>
    <tr class="karma-page-actions">
      <td class="windowbg" bgcolor="<?= $this->color['windowbg'] ?>" valign="top">
        <?php if(empty($karmas['body'])): ?>
          Сообщение было удалено.
        <?php else: ?>
          <div align="right">[<a href="<?= SITE_ROOT ?>/<?= $id ?>/">перейти к сообщению</a>]</div><br /><br />
          <?php if($this->conf->enable_ubbc): ?>
            <?= $this->doubbc($karmas['body']) ?>
          <?php else: ?>
            <?= $this->esc($karmas['body']) ?>
          <?php endif; ?>
        <?php endif; ?>
        <div class="bordercolor" style="width: 10em; height: 1px; margin-top: 1em;"></div>
        <ul>
          <?php foreach($karmas['karmas'] as $karma): ?>
            <li>
              <a href="<?= SITE_ROOT ?>/people/<?= rawurlencode($karma['user1']) ?>/"><?= $this->esc($karma['user1_name']) ?> (<?= $this->esc($karma['user1']) ?>)</a>
              <b><?= $karma['action'] ?></b>
              <?php if($karma['dupes'] > 1): ?>
                <b>x<?= $karma['dupes'] ?></b>
              <?php endif; ?>
              <font size="1">
                <i><?= $karma['time'][4] ?>:<?= $karma['time'][5] ?>:<?= $karma['time'][6] ?> <?= $karma['time'][3] ?>/<?= $karma['time'][2] ?>/<?= $karma['time'][1] ?></i>
              </font>
              <?php if($this->delbtn): ?>
                [<a href="./remove/<?= $id ?>/?<?= $karma['dellnk'] ?>">Удалить</a>]
              <?php endif;?>
            </li>
          <?php endforeach; ?>
        </ul>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
<br />
<center><a href="javascript:history.go(-1)">Обратно</a></center>
