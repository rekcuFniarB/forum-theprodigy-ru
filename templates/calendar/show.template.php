<form action="." method="get">
  <table border="0" width="100%" cellspacing="0" cellpadding="0" class="bordercolor">
    <tr>
      <td>
        <table cellspacing="1" cellpadding="0" width="100%">
          <caption>
            <font size="5" color="#<?= $this->conf->cal_captioncolor ?>">
              <?= $this->monthy ?> <?= $this->year ?>
            </font>
          </caption>
          <tr>
            <?php foreach($this->days as $day): ?>
              <td class="windowbg" width="14%" align="center">
                <?= $day ?>
              </td>
            <?php endforeach; ?>
          </tr>
          
          <?php foreach($this->rows as $row): ?>
            <tr>
              <?php foreach($row as $col): ?>
                <td class="windowbg" valign="top" height="100">
                  <table cellspacing="0" cellpadding="0" width="100%">
                    <tr valign="top">
                      <td>
                        <?php if($col['nday']): ?>
                          <?php if($col['currentday']): ?>
                            <table cellspacing="0" cellpadding="0" width="100%">
                              <tr>
                                <td bgcolor="#<?= $this->conf->cal_todaycolor ?>" valign="top" height="100" style="border:2px;border-style:outset">
                          <?php endif; ?>
                          
                          <?php if($col['isLink']): ?>
                            <a href="./newevent/?month=<?= $col['month'] ?>&amp;year=<?= $col['year'] ?>&amp;day=<?= $col['nDay'] ?>"><?= $col['nDay'] ?></a>
                            <font size="1"><?= $col['week'] ?></font>
                          <?php else: ?>
                            <?= $col['nDay'] ?><font size="1"><?= $col['week'] ?></font>
                          <?php endif; ?>
                          
                          <?php if(isset($col['holiday'])): ?>
                            <br>
                            <font size="1" color="#<?= $this->conf->cal_holidaycolor ?>">
                              <?= $col['holiday'] ?>
                            </font>
                            <br>
                          <?php endif; ?>
                          
                          <?php if(isset($col['bday'])): ?>
                            <br>
                            <font size="1"><?= $col['bday'] ?></font>
                            <br>
                          <?php endif; ?>
                          
                          <?php if(isset($col['event'])): ?>
                            <br>
                            <font size="1"><?= $col['event'] ?></font>
                            <br>
                          <?php endif; ?>
                          
                          <?php if($col['currentday']): ?>
                                </td>
                              </tr>
                            </table>
                          <?php endif; ?>
                        
                        <?php endif; // if nday?>
                        
                      </td>
                    </tr>
                  </table>
                </td>
                
              <?php endforeach; // cols ?>
            </tr>
          <?php endforeach; // rows ?>
          
          <tr class="windowbg2">
            <td>
              <?php if(isset($this->prev)): ?>
                <a href="./?year=<?= $this->prev['year'] ?>&amp;month=<?= $this->prev['month'] ?>"><?= $this->prev['name'] ?></a>
              <?php endif; ?>
            </td>
            
            <td align="center">
              <?php if($this->canpost): ?>
                <a href="./newevent/?year=<?= $this->year?>&amp;month=<?= $this->month ?>"><?= $this->locale->img['postEvent'] ?></a>
              <?php endif; ?>
            </td>
            
            <td colspan="3" align="center">
              <table border="0" cellpadding="2">
                <tr>
                  <td>
                    <select name="month">
                      <?php foreach($this->months as $month): ?>
                        <option value="<?= $month[0] ?>" <?= $month[2] ?>><?= $month[1] ?></option>
                      <?php endforeach; ?>
                    </select>&#160;
                    
                    <select name="year">
                      <?php foreach($this->years as $year): ?>
                        <option value="<?= $year[0] ?>" <?= $year[1] ?>><?= $year[0] ?></option>
                      <?php endforeach; ?>
                    </select>&#160;&#160;&#160;
                    
                    <input type="submit" value="<?= $this->locale->txt[305] ?>">
                  </td>
                </tr>
              </table>
            </td>
            
            <td align="center">
              <?php if($this->canpost): ?>
                <a href="./newevent/?year=<?= $this->year?>&amp;month=<?= $this->month ?>"><?= $this->locale->img['postEvent'] ?></a>
              <?php endif; ?>
            </td>
            
            <td align="right">
              <?php if(isset($this->next)): ?>
                <a href="./?year=<?= $this->next['year'] ?>&amp;month=<?= $this->next['month'] ?>"><?= $this->next['name'] ?></a>
              <?php endif; ?>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</form>
  
