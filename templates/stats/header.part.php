  <tr>
    <td class="titlebg" bgcolor="<?= $this->color['titlebg'] ?>" colspan="11">
      <b><font size="2" class="text1" color="<?= $this->color['titletext'] ?>">
        <!-- Sort Menu -->
        <?php if($this->sort == 'staff'): ?>
          <h1><?= $this->locale->yse45 ?></h1>
        <?php else: ?>
          <?php if(empty($this->sort)): ?>
            <?= $this->locale->txt[303] ?>
          <?php else: ?>
            <a href="<?= SITE_ROOT ?>/allmypeople/"><font size="2" class="text1" color="<?= $this->color['titletext'] ?>"><?= $this->locale->txt[303] ?></font></a>
          <?php endif; ?>
          |
          <?php if($this->sort == 'grp'): ?>
            <?= $this->locale->yse288 ?>
          <?php else: ?>
            <a href="<?= SITE_ROOT ?>/allmypeople/grp/"><font size="2" class="text1" color="<?= $this->color['titletext'] ?>"><?= $this->locale->yse288 ?></font></a>
          <?php endif; ?>
          |
          <?php if($this->sort == 'alph'): ?>
            <?= $this->locale->txt[304] ?>
          <?php else: ?>
            <a href="<?= SITE_ROOT ?>/allmypeople/alph/"><font size="2" class="text1" color="<?= $this->color['titletext'] ?>"><?= $this->locale->txt[304] ?></font></a>
          <?php endif; ?>
          |
          <?php if($this->sort == 'top'): ?>
            <?= $this->locale->txt[305] ?> <?= $this->locale->txt[411] ?> <?= $this->conf->TopAmmount ?> <?= $this->locale->txt[306] ?>
          <?php else: ?>
            <a href="<?= SITE_ROOT ?>/allmypeople/top/"><font size="2" class="text1" color="<?= $this->color['titletext'] ?>"><?= $this->locale->txt[305] ?> <?= $this->locale->txt[411] ?> <?= $this->conf->TopAmmount ?> <?= $this->locale->txt[306] ?></font></a>
          <?php endif; ?>
          
          <?php if($this->sort == 'alph'): ?>
            <tr>
              <td class="catbg" bgcolor="'. $color['catbg'] . '" colspan="11">
                <b><font size="2">
                  <?php for($i = 48; $i < 58; $i++): ?>
                    <a href="./?letter=<?= chr($i) ?>"><?= strtoupper(chr($i)) ?></a>
                  <?php endfor; ?>
                  
                  <?php for($i = 97; $i < 123; $i++): ?>
                    <a href="./?letter=<?= chr($i) ?>"><?= strtoupper(chr($i)) ?></a>
                  <?php endfor; ?>
                </font></b>
              </td>
            </tr>
          <?php endif; ?>
        <?php endif; // not staff list?>
      </font></b>
    </td>
  </tr>
  
  <tr>
    <td class="catbg" bgcolor="<?= $this->color['catbg'] ?>" width="20">
      <b><font size="2"><?= $this->locale->online8 ?></font></b>
    </td>
    <td class="catbg" bgcolor="<?= $this->color['catbg'] ?>">
      <b><font size="2"><?= $this->locale->txt[35] ?></font></b>
    </td>
    <td class="catbg" bgcolor="<?= $this->color['catbg'] ?>" width="25" align="center">
      <b><font size="2">
        <img src="<?= $this->imagesdir ?>/email_sm.gif" alt="<?= $this->locale->txt[307] ?>" title="<?= $this->locale->txt[307] ?>" border="0">
      </font></b>
    </td>
    <td class="catbg" bgcolor="<?= $this->color['catbg'] ?>" width="25" align="center">
      <b><font size="2">
        <img src="<?= $this->imagesdir ?>/www.gif" alt="<?= $this->locale->txt[96] ?>" title="<?= $this->locale->txt[96] ?>" border="0">
      </font></b>
    </td>
    <td class="catbg" bgcolor="<?= $this->color['catbg'] ?>" width="110" align="center">
      <b><font size="2">
        <img src="<?= $this->imagesdir ?>/icq.gif" alt="<?= $this->locale->txt[513] ?>" title="<?= $this->locale->txt[513] ?>" border="0">
      </font></b>
    </td>
    <td class="catbg" bgcolor="<?= $this->color['catbg'] ?>" width="25" align="center">
      <b><font size="2">
        <img src="<?= STATIC_ROOT ?>/img/YaBBImages/Skype-icon-x17.png" alt="Skype" title="Skype" border="0">
      </font></b>
    </td>
    <td class="catbg" bgcolor="<?= $this->color['catbg'] ?>" width="25" align="center">
      <b><font size="2">
        <img src="<?= STATIC_ROOT ?>/img/YaBBImages/livejournal.png" alt="<?= $this->locale->lj ?>" title="<?= $this->locale->lj ?>" border="0">
      </font></b>
    </td>
    <td class="catbg" bgcolor="<?= $this->color['catbg'] ?>" width="25" align="center">
      <b><font size="2">
        <img src="<?= $this->imagesdir ?>/msntalk.gif" alt="MSN" title="MSN" border="0">
      </font></b>
    </td>
    <td class="catbg" bgcolor="<?= $this->color['catbg'] ?>">
      <b><font size="2"><?= $this->locale->txt[87] ?></font></b>
    </td>
    <td class="catbg" bgcolor="<?= $this->color['catbg'] ?>" colspan="2">
      <b><font size="2"><?= $this->locale->txt[21] ?></font></b>
    </td>
  </tr>
