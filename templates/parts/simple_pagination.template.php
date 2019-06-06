<div class="simple_pagination search-result-pagination"><!--
  --><div class="pagination-left">
  <?php if($this->page_start): ?>
      <a href="<?= $this->page_base_url ?>/">|&#8592;</a>
  <?php endif; ?>
  
  <?php if($this->page_prev): ?>
    <a href="<?= $this->page_base_url ?>/<?= $this->page_prev?>/">&#8592;</a>
  <?php endif; ?>
  </div><!--
  
  --><div class="pagination-right">
  <?php if($this->page_next): ?>
    <a href="<?= $this->page_base_url ?>/<?= $this->page_next?>/">&#8594;</a>
  <?php endif; ?>
  
  <?php if($this->page_last): ?>
      <a href="<?= $this->page_base_url ?>/<?= $this->page_last?>/">&#8594;|</a>
  <?php endif; ?>
  </div>
</div>
