<?php if(is_array($this->pages)): ?>
  <!-- Pagination status -->
  <table border="0" cellspacing="0" cellpadding="3" align="center" width="100%">
    <tr>
      <td bgcolor="<?= $this->color['titlebg'] ?>" align="center" class="titlebg">
        <font size="2" class="text1" color="<?= $this->color['titletext'] ?>">
          <b><?= $this->locale->txt[308] ?> <?= $this->numbegin ?> <?= $this->locale->txt[311] ?> <?= $this->numend ?> (<?= $this->locale->txt[309] ?> <?= $this->memcount ?> <?= $this->locale->txt[310] ?> )</b>
        </font>
      </td>
    </tr>
  </table>
<?php endif; ?>

<table border="0" width="100%" cellspacing="1" cellpadding="4" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor" align="center" role="presentatioin">
  <?php $this->partial('stats/header.part.php') ?>
  
  <?php if($this->notfound): ?>
    <tr>
      <td colspan="11" class="windowbg" bgcolor="<?= $this->color['windowbg'] ?>">
        <?= $this->locale->txt[170] ?>
      </td>
    </tr>
  <?php else: ?>
    <?php $this->partial('stats/listmembers.part.php') ?>
  <?php endif; ?>
</table>


<?php if(is_array($this->pages)): ?>
  <!-- Pagination -->
  <table border="0" width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td>
        <font size="2">
          <b><?= $this->locale->txt[139] ?>:</b>
          
          <?php foreach($this->pages as $page): ?>
            <?php if(is_array($page)): ?>
              <a href="./?start=<?= $page[0] ?>"><?= $page[1] ?></a>
            <?php else: ?>
              <?= $page ?>
            <?php endif; ?>
          <?php endforeach; ?>
        </font>
      </td>
    </tr>
  </table>
<?php endif; ?>
