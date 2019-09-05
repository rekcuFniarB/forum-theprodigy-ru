<table width="100%" align="center">
  <tr>
    <td valign="bottom">
      <?php $this->partial('parts/linktree.template.php') ?>
    </td>
  </tr>
</table>

<table border="0" width="100%" cellspacing="0" cellpadding="0" class="bordercolor">
  <tr>
    <td>
      <table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
        <tr>
          <td class="titlebg" align="center" colspan="4">
            <b><?= $this->get('title') ?></b>
          </td>
        </tr>
        <tr>
          <td class="catbg" colspan="4">
            <b><?= $this->locale->yse_stats_2 ?></b>
          </td>
        </tr>
        <tr>
          <td class="windowbg" width="20" valign="middle" align="center">
            <img src="<?= $this->imagesdir ?>/stats_info.gif" border="0" width="20" height="20" alt="" />
          </td>
          <td class="windowbg2" width="100%" colspan="3">
            <table border="0" cellpadding="1" cellspacing="0" width="100%">
              <tr>
                <td>
                  <font size="2"><?= $this->locale->txt[488] ?></font>
                </td>
                <td align="right">
                  <font size="2">
                    <a href="<?= SITE_ROOT ?>/allmypeople/"><?= $this->memcount ?></a>
                  </font>
                </td>
              </tr>
              <tr>
                <td>
                  <font size="2"><?= $this->locale->txt[489] ?></font>
                </td>
                <td align="right">
                  <font size="2"><?= $this->totalm ?></font>
                </td>
              </tr>
              <tr>
                <td>
                  <font size="2"><?= $this->locale->txt[490] ?></font>
                </td>
                <td align="right">
                  <font size="2"><?= $this->totalt ?></font>
                </td>
              </tr>
              <tr>
                <td>
                  <font size="2"><?= $this->locale->txt[658] ?></font>
                </td>
                <td align="right">
                  <font size="2"><?= $this->numcats ?></font>
                </td>
              </tr>
              <tr>
                <td>
                  <font size="2"><?= $this->locale->txt[665] ?></font>
                </td>
                <td align="right">
                  <font size="2"><?= $this->numboards ?></font>
                </td>
              </tr>
              <tr>
                <td>
                  <font size="2"><?= $this->locale->txt[656] ?></font>
                </td>
                <td align="right">
                  <font size="2">
                    <a href="<?= SITE_ROOT ?>/people/<?= rawurlencode($this->thelatestmember['memberName']) ?>/"><?= $this->esc($this->thelatestmember['memberName']) ?></a></font>
                </td>
              </tr>
              <tr>
                <td>
                  <font size="2"><?= $this->locale->txt[888] ?></font>
                </td>
                <td align="right">
                  <font size="2"><?= $this->mostonline ?> - <?= $this->mostdate ?></font>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="catbg" colspan="2" width="50%">
            <b><?= $this->locale->yse_stats_3 ?></b>
          </td>
          <td class="catbg" colspan="2" width="50%">
            <b><?= $this->locale->yse_stats_4 ?></b>
          </td>
        </tr>
        <tr>
          <td class="windowbg" width="20" valign="middle" align="center">
            <img src="<?= $this->imagesdir ?>/stats_posters.gif" border="0" width="20" height="20" alt="" />
          </td>
          <td class="windowbg2" width="50%" valign="top">
            <!-- Top 10 members -->
            <table border="0" cellpadding="1" cellspacing="0" width="100%">
              <?php foreach($this->top_members as $member): ?>
                <tr>
                  <td>
                    <font size="2">
                      <a href="<?= SITE_ROOT ?>/people/<?= rawurlencode($member['memberName']) ?>/"><?= $this->esc($member['realName']) ?></a>
                    </font>
                  </td>
                  <td align="right">
                    <font size="2"><?= $member['posts'] ?></font>
                  </td>
                </tr>
              <?php endforeach; ?>
            </table>
          </td>
          <td class="windowbg" width="20" valign="middle" align="center" nowrap="nowrap">
            <img src="<?= $this->imagesdir ?>/stats_board.gif" width="20" height="20" border="0" alt="" />
          </td>
          <td class="windowbg2" width="50%" valign="top">
            <!-- Top 10 boards -->
            <table border="0" cellpadding="1" cellspacing="0" width="100%">
              <?php foreach($this->top_boards as $board): ?>
                <tr>
                  <td>
                    <font size="2">
                      <a href="<?= SITE_ROOT ?>/b<?= $board['ID_BOARD'] ?>/"><?= $this->esc($board['name'] ) ?></a>
                    </font>
                  </td>
                  <td align="right">
                    <font size="2"><?= $board['numPosts'] ?></font>
                  </td>
                </tr>
              <?php endforeach; ?>
            </table>
          </td>
        </tr>
        <tr>
          <td class="catbg" colspan="2" width="50%">
            <b><?= $this->locale->yse_stats_11 ?></b>
          </td>
          <td class="catbg" colspan="2" width="50%">
            <b><?= $this->locale->yse_stats_12 ?></b>
          </td>
        </tr>
        <tr>
          <td class="windowbg" width="20" valign="middle" align="center">
            <img src="<?= $this->imagesdir ?>/stats_replies.gif" border="0" width="20" height="20" alt="" />
          </td>
          <td class="windowbg2" width="50%" valign="top">
            <table border="0" cellpadding="1" cellspacing="0" width="100%">
              <!-- Top 10 replies -->
              <?php foreach($this->topic_replies as $topic): ?>
                <tr>
                  <td>
                    <font size="2">
                      <a href="<?= SITE_ROOT ?>/b<?= $topic['ID_BOARD'] ?>/t<?= $topic['ID_TOPIC'] ?>/"><?= $this->esc($topic['subject']) ?></a>
                    </font>
                  </td>
                  <td align="right">
                    <font size="2"><?= $topic['numReplies'] ?></font>
                  </td>
                </tr>
              <?php endforeach; ?>
            </table>
          </td>
          <td class="windowbg" width="20" valign="middle" align="center" nowrap="nowrap">
            <img src="<?= $this->imagesdir ?>/stats_views.gif" width="20" height="20" border="0" alt="" />
          </td>
          <td class="windowbg2" width="50%" valign="top">
            <table border="0" cellpadding="1" cellspacing="0" width="100%">
              <!-- Top 10 topics by views -->
              <?php foreach($this->topic_views as $topic): ?>
                <tr>
                  <td>
                    <font size="2">
                      <a href="<?= SITE_ROOT ?>/b<?= $topic['ID_BOARD'] ?>/t<?= $topic['ID_TOPIC'] ?>/"><?= $this->esc($topic['subject']) ?></a>
                    </font>
                  </td>
                  <td align="right">
                    <font size="2"><?= $topic['numViews'] ?></font>
                  </td>
                </tr>
              <?php endforeach; ?>
            </table>
          </td>
        </tr>
        <tr>
          <td class="catbg" colspan="4">
            <b><?= $this->locale->yse_stats_5 ?></b>
          </td>
        </tr>
        <tr>
          <td class="windowbg" width="20" valign="middle" align="center">
            <img src="<?= $this->imagesdir ?>/stats_history.gif" border="0" width="20" height="20" alt="" />
          </td>
          <td class="windowbg2" colspan="4">
            <table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
              <tr>
                <td class="titlebg" valign="middle" align="center">
                  <?= $this->locale->yse_stats_6 ?>
                </td>
                <td class="titlebg" valign="middle" align="center">
                  <?= $this->locale->yse_stats_7 ?>
                </td>
                <td class="titlebg" valign="middle" align="center">
                  <?= $this->locale->yse_stats_8 ?>
                </td>
                <td class="titlebg" valign="middle" align="center">
                  <?= $this->locale->yse_stats_9 ?>
                </td>
                <td class="titlebg" valign="middle" align="center">
                  <?= $this->locale->yse_stats_14 ?>
                </td>
                
                <?php if($this->conf->hitStats): ?>
                  <td class="titlebg" valign="middle" align="center">
                    <?= $this->locale->yse_stats_10 ?>
                  </td>
                <?php endif; ?>
              </tr>
              
              <?php foreach($this->days as $day): ?>
                <tr class="windowbg2" valign="middle" align="center">
                  <td class="windowbg2">
                    <?= $day['day'] ?>/<?= $day['month'] ?>/<?= $day['year'] ?>
                  </td>
                  <td class="windowbg2">
                    <?= $day['topics'] ?>
                  </td>
                  <td class="windowbg2">
                    <?= $day['posts'] ?>
                  </td>
                  <td class="windowbg2">
                    <?= $day['registers'] ?>
                  </td>
                  <td class="windowbg2">
                    <?= $day['mostOn'] ?>
                  </td>
                  <?php if($this->conf->hitStats): ?>
                    <td class="windowbg2"><?= $day['hits'] ?></td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
            </table>
            <br />
            
            <table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
              <tr>
                <td class="titlebg" valign="middle" align="center">
                  <?= $this->locale->yse_stats_13 ?>
                </td>
                <td class="titlebg" valign="middle" align="center">
                  <?= $this->locale->yse_stats_7 ?>
                </td>
                <td class="titlebg" valign="middle" align="center">
                  <?= $this->locale->yse_stats_8 ?>
                </td>
                <td class="titlebg" valign="middle" align="center">
                  <?= $this->locale->yse_stats_9 ?>
                </td>
                <td class="titlebg" valign="middle" align="center">
                  <?= $this->locale->yse_stats_14 ?>
                </td>
                
                <?php if($this->conf->hitStats): ?>
                  <td class="titlebg" valign="middle" align="center">
                    <?= $this->locale->yse_stats_10 ?>
                  </td>
                <?php endif; ?>
              </tr>
              
              <!-- Months -->
              <?php foreach($this->months as $month): ?>
                <tr class="windowbg2" valign="middle" align="center">
                  <td class="windowbg2">
                    <?= $month['monthy'] ?> <?= $month['year'] ?>
                  </td>
                  <td class="windowbg2">
                    <?= $month['stop'] ?>
                  </td>
                  <td class="windowbg2">
                    <?= $month['spos'] ?>
                  </td>
                  <td class="windowbg2">
                    <?= $month['sreg'] ?>
                  </td>
                  <td class="windowbg2">
                    <?= $month['mOn'] ?>
                  </td>
                  <?php if($this->conf->hitStats): ?>
                    <td class="windowbg2"><?= $month['shit'] ?></td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
