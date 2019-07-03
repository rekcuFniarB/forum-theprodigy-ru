<div align="center">
  <form action="." method="post">
<!--    <input type="hidden" name="board" value="<?= $this->board ?>">
    <input type="hidden" name="threadid" value="<?= $this->threadid ?>">-->
    <table border="0">
      <tr>
        <td align="right"><?= $this->locale->calendar9 ?></td>
        <td align="left">
          <select name="month">
            <?php foreach($this->months as $month): ?>
              <option value="<?= $month[0] ?>" <?= $month[2] ?>><?= $month[1] ?></option>
            <?php endforeach; ?>
          </select>
        </td>
      </tr>
      
      <tr>
        <td align="right"><?= $this->locale->calendar10 ?></td>
        <td align="left">
          <select name="year">
            <?php foreach($this->years as $year): ?>
              <option value="<?= $year[0] ?>" <?= $year[1] ?>><?= $year[0] ?></option>
            <?php endforeach; ?>
          </select>
        </td>
      </tr>
      
      <tr>
        <td align="right"><?= $this->locale->calendar11 ?></td>
        <td align="left">
          <select name="day">
            <?php foreach($this->days as $day): ?>
              <option value="<?=  $day[0] ?>" <?= $day[1] ?>><?= $day[0] ?></option>
            <?php endforeach; ?>
          </select>
        </td>
      </tr>
      
      <?php if(isset($this->spans)): ?>
        <tr>
          <td align="right"><?= $this->locale->calendar54 ?></td>
          <td align="left">
            <select name="span">
              <?php foreach($this->spans as $span): ?>
                <option value="<?= $span[0] ?>" <?= $span[1] ?>><?= $span[0] ?></option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
      <?php endif; ?>
      
      <tr>
        <td align="right"><?= $this->locale->calendar12 ?></td>
        <td align="left">
          <input name="evtitle" type="text" maxlength="30" size="30">
        </td>
      </tr>
      
      <tr>
        <td align="center" colspan="2">
          <input type="submit" value="<?= $this->locale->calendar43 ?>">
        </td>
      </tr>
    </table>
    
    <input type="hidden" name="sc" value="<?= $this->sessionid ?>">
  </form>
</div>
