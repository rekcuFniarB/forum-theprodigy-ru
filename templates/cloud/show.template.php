<div class="bordercolor">
  <div class="windowbg2 cloud">
    <h1 class="titlebg">Prodigy Cloud</h1>
    
    <?php if($this->info['type'] == 'image'): ?>
      <div class="show-embed">
        <h2 class="titlebg"><?= $this->esc($this->info['title']) ?></h2>
        <div>
          <div><img src="<?= $this->esc($this->BaseUrl) ?>"></div>
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
              <input type="text" value="[img]<?= $this->esc($this->siteurl . $this->uri) ?>[/img]">
            </div>
          <?php endif; ?>
        </div>
      </div> <!-- .show-embed -->
    <?php else: ?>
        Not an image
    <?php endif; ?>

  </div>
</div>
