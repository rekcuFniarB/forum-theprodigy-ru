<?php /* Calendar events info on board index page */ ?>
        <!-- Calendar -->
        <tr>
          <td class="catbg" colspan="2">
            <b><?= $this->locale->calendar47 ?></b>
          </td>
        </tr>
        <tr>
          <td class="windowbg info_img" width="20" valign="middle" align="center">
            <a href="<?= SITE_ROOT ?>/calendar/">
              <img src="<?= $this->conf->imagesdir ?>/calindex.gif" border="0" width="20" alt="<?= $this->locale->calendar24 ?>">
            </a>
          </td>
          <td class="windowbg2" width="100%">
            <?php if(isset($this->calendar['holidays'][$this->calendar['day']])): ?>
              <div><font size="1" color="#<?= $this->conf->cal_holidaycolor ?>"><?= $this->esc($this->calendar['holidays'][$this->calendar['day']]) ?></font></div>
            <?php endif; ?>
            
            <?php if(isset($this->calendar['bday'][$this->calendar['day']])): ?>
              <div><font size="1"><?= $this->calendar['bday'][$this->calendar['day']] ?></font></div>
            <?php endif; ?>
            
            <?php if(isset($this->calendar['events'][$this->calendar['day']])): ?>
              <div><font class="1"><?= $this->calendar['bday'][$this->calendar['day']] ?></font>
            <?php endif; ?>
          </td>
        </tr>
        <!-- /Calendar -->