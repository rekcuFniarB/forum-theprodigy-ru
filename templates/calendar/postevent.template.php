<div align="center">
  <form action="." method="post">
    <input type="hidden" name="year" value="<?= $this->esc($this->year) ?>">
    <input type="hidden" name="month" value="<?= $this->esc($this->month) ?>">
    <input type="hidden" name="title" value="<?= $this->locale->txt[464] ?>">
    <input type="hidden" name="linkcalendar" value="1">
    <table border="0" cellpadding="4">
      <tr>
        <td align="right"><?= $this->locale->calendar9 ?></td>
        <td align="left"><?= $this->monthy ?></td>
      </tr>
      
      <tr>
        <td align="right"><?= $this->locale->calendar10 ?></td>
        <td align="left"><?= $this->esc($this->year) ?></td>
      </tr>
      
      <tr>
        <td align="right"><?= $this->locale->calendar11 ?></td>
        <td align="left">
          <select name="day">
            <?php foreach($this->days as $day): ?>
              <option value="<?= $day[0] ?>" <?= $day[1] ?>><?= $day[0] ?></option>
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
        <td align="right"><?= $this->locale->calendar13 ?></td>
        <td align="left">
          <select name="board">
            <?php foreach($this->boards as $board): ?>
              <option value="<?= $board[0] ?>" <?= $board[2] ?>><?= $this->esc($board[1]) ?></option>
            <?php endforeach; ?>
            </select>
        </td>
      </tr>
      
      <tr>
        <td align="center" colspan="2">
          <input type="submit" value="<?= $this->locale->calendar23 ?>">
        </td>
      </tr>
    </table>
  </form>
</div>
