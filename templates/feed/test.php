<?php include('base.php'); ?>

<div>BASE TEMPLATE</div>

<?php
// echo $this->appConf['config']['charset'];
  
  //extract($this->appConf, EXTR_REFS);

// $appConf = $this->appConf;
// extract($appConf, EXTR_REFS);

// error_log("__DEBUG__: __DIR__: ". __DIR__);

  


// echo "TEST TEMPLATE\n\n";
// 
// 
// echo isset($this->appConf);
// 
// $db = $this->db;
// 
// echo "\n\n{$this->db->db_charset}\n\n";

//var_dump($vars);

// $GLOBALS['config'] = $config;
// $GLOBALS['db'] = $this->db;
?>

<div>
  <?= $this->escape($this->info); ?>
</div>

<?= $this->app->db->escape_string("QWERTY'QWERTY"); ?>

<div>TEST: <?= htmlescape($this->test); ?></div>

<?php /* echo $this->app->qwerty(); */ ?>

<div>htmlescape(): <?= htmlescape('<i>хуй</i> © &amp;'); ?>
</div>

<div>Esc 2<br>
  
</div>

<?php footer(); ?>
