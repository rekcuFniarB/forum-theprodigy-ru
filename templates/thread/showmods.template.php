<?php if (sizeof($showmods) > 0): ?>
  <?php if (sizeof($showmods) == 1): ?>
    (<?= $this->locale->txt[298] ?>:
  <?php else: ?>
    (<?= $this->locale->txt[299] ?>:
  <?php endif; ?>
  
  <?php $this->menu_begin() ?>
  <?php foreach($showmods as $moder): ?>
    <?= $this->menusep(',') ?>
    <a href="<?= SITE_ROOT ?>/people/<?= rawurlencode($moder['name']) ?>/"><acronym title="<?= $this->locale->txt[62] ?>"><?= $this->esc($moder['realName']) ?></acronym></a>
  <?php endforeach; ?>
  )
<?php endif; ?>
