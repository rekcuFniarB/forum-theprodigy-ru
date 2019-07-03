<script language = "JavaScript">
    function DoConfirm(message, url) { if (confirm(message)) location.href = url; }
</script>

<div align="center">
  <form action="." method="post">
    <input type="hidden" name="eventid" value="<?= $this->esc($this->eventid) ?>">
    <table border="0">
      <tr>
        <td><?= $this->locale->calendar9 ?></td>
        <td>
          <select name="month">
            <?php foreach($this->months as $month): ?>
              <option value="<?= $month[0] ?>" <?= $month[2] ?>><?= $month[1] ?></option>
            <?php endforeach; ?>
          </select>
        </td>
      </tr>
      
      <tr>
        <td><?= $this->locale->calendar10 ?></td>
        <td>
          <select name="year">
            <?php foreach($this->years as $year): ?>
              <option value="<?= $year[0] ?>" <?= $year[1] ?>><?= $year[0] ?></option>
            <?php endforeach; ?>
          </select>
        </td>
      </tr>
      
      <tr>
        <td><?= $this->locale->calendar11 ?></td>
        <td>
          <select name="day">
            <?php foreach($this->days as $day): ?>
              <option value="<?= $day[0] ?>" <?= $day[1] ?>><?= $day[0] ?></option>
            <?php endforeach; ?>
          </select>
        </td>
      </tr>
      
      <tr>
        <td><?= $this->locale->calendar12 ?></td>
        <td>
          <input name="evtitle" type="text" maxlength="30" size="30" value="<?= $this->get('eventTitle') ?>">
        </td>
      </tr>
      
      <tr>
        <td align="center" colspan="2">
          <input type="submit" value="<?= $this->locale->calendar20 ?>">&#160;&#160;&#160;
          <?= $this->locale->calendar22 ?>: <input type="checkbox" name="deleteevent">
        </td>
      </tr>
    </table>
    <input type="hidden" name="sc" value="<?= $this->sessionid ?>">
  </form>
</div>
