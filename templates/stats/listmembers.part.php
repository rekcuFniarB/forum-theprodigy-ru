<?php foreach($this->members as $member): ?>
  <tr>
    <td class="windowbg2" bgcolor="<?= $this->color['windowbg2'] ?>" align="center">
      <font size="2">
        <a href="<?= SITE_ROOT ?>/im/send/<?= rawurlencode($member['memberName']) ?>/"><?= $member['online'] ?>
      </font>
    </td>
    <td class="windowbg" bgcolor="<?=  $this->color['windowbg'] ?>">
      <font size="2">
        <a href="<?= SITE_ROOT ?>/people/<?= rawurlencode($member['memberName']) ?>/"><?= $this->esc($member['realName']) ?></a>
      </font>
    </td>
    <td class="windowbg2" bgcolor="<?= $this->color['windowbg2'] ?>" width="25" align="center">
      <font size="2">
        <a href="mailto:<?= urlencode($member['emailAddress']) ?>"><img src="<?= $this->imagesdir?>/email_sm.gif" alt="<?= $this->locale->txt[69] ?>" title="<?= $this->locale->txt[69] ?> <?= $this->esc($member['realName']) ?>" border="0"></a>
      </font>
    </td>
    <td class="windowbg" bgcolor="<?=  $this->color['windowbg'] ?>" width="25" align="center">
      <font size="2">
        <?php if(!empty($member['websiteUrl'])): ?>
          <a href="<?= $this->esc($member['websiteUrl']) ?>" target="_blank" rel="nofollow noopener">
            <?php if(!empty($member['websiteTitle'])): ?>
              <img src="<?= $this->imagesdir ?>/www.gif" alt="<?= $this->esc($member['websiteTitle']) ?>" title="<?= $this->esc($member['websiteTitle']) ?>" border="0" />
            <?php else: ?>
              <img src="<?= $this->imagesdir ?>/www.gif" alt="<?= $this->locale->txt[96] ?>" border="0">
            <?php endif; ?>
          </a>
        <?php endif; ?>
      </font>&nbsp;
    </td>
    <td class="windowbg2" bgcolor="<?= $this->color['windowbg2'] ?>" width="25" align="center">
      <font size="2">
        <?php if(!empty($member['ICQ'])): ?>
          <img src="http://status.icq.com/online.gif?icq=<?=urlencode($member['ICQ'])?>&amp;img=5" alt="<?=$this->esc($member['ICQ'])?>" border="0"><?=$this->esc($member['ICQ'])?>
        <?php endif; ?>
      </font>&nbsp;
    </td>
    <td class="windowbg2" bgcolor="<?= $this->color['windowbg2'] ?>" width="25" align="center">
      <font size="2">
        <?php if(!empty($member['AIM'])): ?>
          <a href="skype:<?= urlencode($member['AIM']) ?>?chat&amp;topic=Forum.theProdigy.ru" target="_blank"><img src="<?= STATIC_ROOT?>/img/YaBBImages/Skype-icon-x17.png" alt="Skype: <?= $this->esc($member['AIM']) ?>" border="0" /></a>
        <?php endif; ?>
      </font>&nbsp;
    </td>
    <td class="windowbg2" bgcolor="<?= $this->color['windowbg2'] ?>" width="25" align="center">
      <font size="2">
        <?php if(!empty($member['YIM'])): ?>
          <a href="<?= $this->esc($member['YIM']) ?>" target="_blank" rel="nofollow noopener"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/livejournal.png" alt="<?= $this->esc($member['YIM']) ?>" title="<?= $this->locale->lj ?>" border="0"></a>
        <?php endif; ?>
      </font>&nbsp;
    </td>
    <td class="windowbg2" bgcolor="<?= $this->color['windowbg2'] ?>" width="25" align="center">
      <font size="2">
        <?php if(!empty($member['MSN'])): ?>
          <a href="http://members.msn.com/<?= rawurlencode($member['MSN']) ?>" target="_blank"><img src="<?= $this->imagesdir ?>/msntalk.gif" alt="<?= $this->esc($member['MSN']) ?>" border="0" /></a>
        <?php endif; ?>
      </font>&nbsp;
    </td>
    <td class="windowbg" bgcolor="<?=  $this->color['windowbg'] ?>">
      <font size="2"><?= $member['membergroup'] ?></font>&nbsp;
    </td>
    <td class="windowbg2" bgcolor="<?= $this->color['windowbg2'] ?>" align="center" width="15">
      <font size="2"><?= $member['posts'] ?></font>&nbsp;
    </td>
    <td class="windowbg" bgcolor="<?=  $this->color['windowbg'] ?>">
      <img src="<?= $this->imagesdir ?>/bar.gif" width="<?= $member['barchart'] ?>" height="15" border="0" alt="">
    </td>
  </tr>
<?php endforeach; ?>
