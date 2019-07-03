<input type="hidden" name="year" value="<?= $this->esc($this->calendar_year) ?>">
<input type="hidden" name="month" value="<?= $this->esc($this->calendar_month) ?>">
<input type="hidden" name="day" value="<?= $this->esc($this->calendar_day) ?>">
<input type="hidden" name="evtitle" value="<?= $this->esc($this->calendar_evtitle) ?>">
<input type="hidden" name="linkcalendar" value="1">
<input type="hidden" name="board" value="<?= $this->esc($this->calendar_board) ?>">

<?php if(!empty($this->calendar_span)): ?>
  <input type="hidden" name="span" value="<?= $this->esc($this->calendar_span) ?>">
<?php endif; ?>
