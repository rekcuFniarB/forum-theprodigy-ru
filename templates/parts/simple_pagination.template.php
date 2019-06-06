<div class="simple_pagination">
  <?php if($this->page_prev): ?>
    <a href="<?= $this->page_base_url ?>/">|&#8592;</a>
    <a href="<?= $this->page_base_url ?>/<?= $this->page_prev?>/">&#8592;</a>
  <?php endif; ?>
  <?php if($this->page_next): ?>
    <a href="<?= $this->page_base_url ?>/<?= $this->page_next?>/">&#8594;</a>
    <a href="<?= $this->page_base_url ?>/<?= $this->page_last?>/">&#8594;|</a>
  <?php endif; ?>
</div>
