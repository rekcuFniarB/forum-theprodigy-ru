      <form action="<?= SITE_ROOT ?>/go/" method="get">
        <font size="1"><?= $this->locale->txt[160] ?>:</font>
          <select name="to" onchange="if (this.options[this.selectedIndex].value) window.location.href='<?= SITE_ROOT ?>/' + this.options[this.selectedIndex].value;">
            <option value=""><?= $this->locale->txt[251] ?></option>
            <?php foreach($this->jumptoform as $jumpcatid => $jumpcat): ?>
              <option value="">-----------------------------</option>
              <option value="#<?= $jumpcatid ?>"><?= $this->esc($jumpcat['name']) ?></option>
              <option value="">-----------------------------</option>
              <?php foreach($jumpcat['boards'] as $jumpboardid => $jumpboard): ?>
                <?php if($jumpboard['current']): ?>
                  <option value="b<?= $jumpboardid ?>/" selected="selected"> =><?= $this->esc($jumpboard['name']) ?></option>
                <?php else: ?>
                  <option value="b<?= $jumpboardid ?>/"> =><?= $this->esc($jumpboard['name']) ?></option>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </select>&nbsp;<input type="button" value="<?= $this->locale->txt[161] ?>" onclick="if (values.options[values.selectedIndex].value) window.location.href='<?= SITE_ROOT ?>/' + values.options[values.selectedIndex].value;" />
      </form>
