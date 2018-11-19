<?= $this->mobileSwitch ?>

<?php if ($this->conf->timeLoadPageEnable && $this->user->group == 'Administrator'): ?>
  <div align="center"><font size="1"><?= $this->locale->yse301 ?> <?= round(microtime(true)-TIME_START, 3) ?> <?= $this->locale->yse302 ?></font></div>
<?php endif; ?>

<template id="sessioninfo" data-sessioninfo='<?= $this->sessinf_json ?>'></template>

<?php if ($this->app->user->accessLevel() > 2): ?>
    <script type="text/javascript" src="<?= STATIC_ROOT ?>/js/moderator.api.js?v=1505685411"></script>
<?php endif; ?>

<?php if($this->snow): ?>
    <script type="text/javascript" src="<?= STATIC_ROOT ?>/js/snow.js"></script>
<?php endif; ?>

<?php if($this->conf->debug || $this->user->accessLevel() > 2): ?>
    <?php $stats = $this->app->subs->runtime_stats(); ?>
    <div align="center">
      <font size="1">
        Run time: <?= $stats['runtime'] ?>sec; Memory usage: <?= $stats['memory'] ?>k; Memory peak: <?= $stats['memory_peak'] ?>k;
      </font>
    </div>
<?php endif; ?>
