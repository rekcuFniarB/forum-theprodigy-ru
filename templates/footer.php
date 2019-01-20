<?= $this->mobileSwitch ?>

<?php if ($this->conf->mediaplayer): ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mediaelement@4.2.9/build/mediaelementplayer.min.css">
  <script src="https://cdn.jsdelivr.net/npm/mediaelement@4.2.9/build/mediaelement-and-player.min.js"></script>

  <script>
    $(function(){
        $('div.video-player > video, div.audio-player > audio').mediaelementplayer({
            // Do not forget to put a final slash (/)
            pluginPath: 'https://cdn.jsdelivr.net/npm/mediaelement@4.2.9/build/',
            // this will allow the CDN to use Flash without restrictions
            // (by default, this is set as `sameDomain`)
            shimScriptAccess: 'always',
            // more configuration
            videoWidth: '100%',
            videoHeight: '100%',
            defaultAudioWidth: '100%'
        });
    });
  </script>
<?php endif; ?>


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

<?php foreach($this->_include_js as $_js): ?>
  <script src="<?= $_js ?>"></script>
<?php endforeach; ?>

<?php if($this->conf->debug || $this->user->accessLevel() > 2): ?>
    <?php $stats = $this->app->subs->runtime_stats(); ?>
    <div align="center">
      <font size="1">
        Run time: <?= $stats['runtime'] ?>sec; Memory usage: <?= $stats['memory'] ?>k; Memory peak: <?= $stats['memory_peak'] ?>k;
      </font>
    </div>
<?php endif; ?>
