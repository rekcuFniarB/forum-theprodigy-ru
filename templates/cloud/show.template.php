<div class="bordercolor">
  <div class="windowbg2 cloud">
    <h1 class="titlebg">Prodigy Cloud</h1>
    
      <div class="show-embed">
        <h2 class="titlebg"><?= $this->esc($this->info['title']) ?></h2>
        <div>
          <?php if($this->type == 'img'): ?>
            <div><img src="<?= $this->esc($this->BaseUrl) ?>"></div>
          <?php elseif($this->type == 'video'): ?>
            <div class="video-player">
              <video class="mejs" controls preload="metadata" title="<?= $this->esc($this->info['title']) ?>">
                <source src="<?= $this->esc($this->uri) ?>">
              </video>
            </div>
          <?php elseif($this->type == 'audio'): ?>
            <div class="audio-player">
              <audio class="mejs" controls preload="metadata" title="<?= $this->esc($this->info['title']) ?>">
                <source src="<?= $this->esc($this->uri) ?>">
              </audio>
            </div>
          <?php else: ?>
            Not an image <?= $this->info['mime'] ?><br>
            <a href="<?= $this->esc($this->uri) ?>?sesc=<?= $this->sesc ?>">Download</a>
            <!-- this is only for testing, should be removed later -->
            <div><img src="<?= $this->esc($this->BaseUrl) ?>"></div>
          <?php endif; ?>
          <div>
            <h3>Description:</h3>
            <?= $this->esc($this->info['description']) ?>
          </div>
          <br>
          <div class="uploaded-by">
            Uploaded by <a href="<?= SITE_ROOT ?>/people/<?= $this->esc($this->info['user']) ?>/"><?= $this->esc($this->info['realName']) ?></a>
          </div>
          <br>
          <?php if($this->embed_code): ?>
            <div>
              <b>Embed code:</b>
              <input type="text" value="[<?= $this->type ?>]<?= $this->esc($this->uri) ?>[/<?= $this->type ?>]">
            </div>
          <?php endif; ?>
        </div>
      </div> <!-- .show-embed -->
  </div>
</div>
