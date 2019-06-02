<div class="error-page">
  <h1 class="titlebg"><?= $this->esc($this->title) ?></h1>
  <article class="windowbg2">
    <div class="article-content error-msg"><?= $this->esc($this->message) ?></div><br>
    <div class="error-backtrace">
      <?php foreach ($this->backtrace as $n => $bt): ?>
          <span><?= $n ?></span> <span>
          <?php if(isset($bt['file'])): ?>
            <?= $bt['file'] ?>:<?= $bt['line'] ?><br>
          <?php endif; ?>
          <?php if(isset($bt['function'])): ?>
            &nbsp;&nbsp;&nbsp;&nbsp;<span><?= $bt['function'] ?>()<br><br>
          <?php endif; ?>
      <?php endforeach; ?>
      
      <?php if(isset($this->backtrace2)): ?>
        <pre style="max-width: 1024px; overflow: auto;"><!--
          --><?= $this->backtrace2 ?>
        </pre>
      <?php endif; ?>
    </div>
  </article>
</div>
