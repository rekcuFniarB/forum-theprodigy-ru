    <!-- BoardIndex -->
    <table width="100%" align="center" id="linkTree">
      <tr>
        <td valign="bottom">
          <?php if($this->conf->enableInlineLinks): ?>
            <font class="nav"><b><?= $this->conf->curposlinks ? "<a href=\"".SITE_ROOT."/\" class=\"nav\">{$this->conf->mbname}</a>" : $this->conf->mbname ?></b></font>
          <?php else: ?>
            <font class="nav"><img src="<?= $this->conf->imagesdir ?>/open.gif" border="0" alt=""> <b><?= $this->conf->curposlinks ? "<a href=\"".SITE_ROOT."/\" class=\"nav\">{$this->conf->mbname}</a>" : $this->conf->mbname ?></b></font>
          <?php endif; ?>
        </td>
        <td align="right">
          <?php if ($this->conf->enableSP1Info != 1): ?>
            <font class="calendar"><?= $this->locale->txt(19) ?>: <?= $this->memcount ?> &nbsp;&#8226;&nbsp; <?= $this->locale->txt(95) ?> <?= $this->totalm ?> &nbsp;&#8226;&nbsp; <?= $this->locale->txt(64) ?> <?= $this->totalt ?></font>
          <?php endif; ?>
          <?php if ($this->conf->showlatestmember == 1 && $this->conf->enableSP1Info != 1): ?>
            <br>
            <font size="2">
              <?= $this->app->locale->txt[201] ?> <a href="<?= SITE_ROOT ?>/people/<?= urlencode($this->thelatestmember) ?>/"><b><?= $this->esc($this->thelatestrealname) ?></b></a>, <?= $this->locale->txt[581] ?>.
            </font>
          <?php endif; ?>
        </td>
      </tr>
    </table>
    
    <?php if ($this->conf->shownewsfader == 1): ?>
        <?php $this->partial('templates/newsfader.php'); ?>
    <?php endif; ?>
    
    <table border="0" width="100%" cellspacing="0" cellpadding="0" class="bordercolor"><tr><td>
      <table border="0" width="100%" cellspacing="1" cellpadding="5" class="bordercolor">
        <tr id="forumColNames">
          <td class="titlebg" colspan="2"><b><?= $this->locale->txt(20) ?></b></td>
          <td class="titlebg" width="6%" align="center"><b><?= $this->locale->txt(330) ?></b></td>
          <td class="titlebg" width="6%" align="center"><b><?= $this->locale->txt(21) ?></b></td>
          <td class="titlebg" width="22%" align="center"><b><?= $this->locale->txt(22) ?></b></td>
        </tr>
        
        <?php $curcat = -1; ?>
        <?php foreach ($this->cats as $curcat => $catdata): ?>
          <tr class="category-row">
            <td colspan="5" class="catbg cattitle" height="18">
              <?php if ($this->user->id != -1): ?>
                <img src="<?= STATIC_ROOT ?>/img/YaBBImages/catMenuBtn.gif" onmouseover="showCatMenu(this.parentNode);" alt="меню категории разделов" style="margin: 0px 5px 0px 0px;">
                <div class="catMenu" onmouseout="hideCatMenu(this);">
                  <ul onmouseover="showCatMenu(this.parentNode.parentNode)">
                    <li><a href="collapseexpandcategoryboards/<?= $curcat ?>/">Свернуть / развернуть разделы</a></li>
                    <li><a href="collapseexpandcategory/<?= $curcat ?>/">Свернуть / развернуть категорию</a></li>
                    <li><a href="#" onclick="editBillboard(<?= $curcat ?>); return false;">Редактировать афишу</a></li>
                    <li><a href="#" onclick="updateBillboard(<?= $curcat ?>); return false;">Перезагрузить афишу</a></li>
                    <?php if ($this->user->accessLevel() > 2): ?>
                      <li><a href="#" onclick="backupBillboard(<?= $curcat ?>); return false;">Сделать резервную копию афиши</a></li>
                      <li><a href="#" onclick="restoreBillboard(<?= $curcat ?>); return false;">Восстановить афишу</a></li>
                    <?php endif; ?>
                  </ul>
                </div>
              <?php endif; ?>
              <a name="<?= $curcat ?>" class="catHead"><b><?= $catdata['name'] ?></b></a>
            </td>
          </tr>
          
          <tr<?= (strlen($catdata['billboard']) > 0 ? '' : ' style="display: none;"') ?>>
            <td<?= (strlen($catdata['billboard']) > 0 ? '' : ' style="display: none;"') ?> colspan="5" class="windowbg2" id="billboard<?= $curcat ?>">
              <font size="1"><?= $this->doUBBC($catdata['billboard']) ?></font>
            </td>
          </tr>
          <?php if (in_array($curcat, $this->collapsedCategories)): ?>
            <tr>
              <td colspan="5" class="catbg" style="font-weight: normal"><font size="1">Кликни <a href="/collapseexpandcategory/<?= $curcat ?>/">сюда</a>, чтобы развернуть категорию <?= $catdata['catName'] ?></font></td>
            </tr>
          <?php endif; ?>
          
          <?php foreach ($catdata['boards'] as $idboard => $board): ?>
            <?php if (is_array($this->user->collapsedBoards) and in_array($idboard, $this->user->collapsedBoards) or $this->user->mobileMode): ?>
              <tr class="category-row">
                <td class="windowbg bg'.$n.' collapsed col_cat_td1" width="6%" align="center" valign="middle">
                  <?php if (!$this->user->mobileMode): ?>
                    <a href="<?= SITE_ROOT ?>/?uncollapseboard=<?= $idboard ?>"><img src="<?= $this->conf->imagesdir ?>/<?= $board['new'] ?>.gif" alt="<?= $board['new_txt'] ?>" title="<?= $board['new_txt'] ?>" border="0"></a>
                  <?php else: ?>
                    <img src="<?= $this->conf->imagesdir ?>/<?= $board['new'] ?>.gif" alt="<?= $board['new_txt'] ?>" title="<?= $board['new_txt'] ?>" border="0">
                  <?php endif; ?>
                </td>
                <td class="windowbg2 bg<?= $board['n'] ?> col_cat_td2" align="left" width="60%">
                  <a name="b<?= $idboard ?>"></a>
                  <font size="2"><a href="<?= SITE_ROOT ?>/b<?= $idboard ?>/" title="<?= $board['description'] ?>" class="boardHead"><b class="boardName"><?= $board['boardName'] ?></b></a></font>
                  <?php if($board['boardViewers'] > 0): ?>
                      <div class="boardViewersPane" style="font-size: 10px; float: right; font-weight: bold;">
                        <img src="<?= STATIC_ROOT ?>/img/YaBBImages/user.png" width="13" height="13" onmouseover="Forum.Data.showBoardViewersTooltip('<?= $idboard ?>')" title="Сейчас в разделе <?= $board['boardViewers'] ?> пользователей" alt="Сейчас в разделе <?= $board['boardViewers'] ?> пользователей"/> <span class="numBoardViewers"><?= $board['boardViewers'] ?></span>
                      </div>
                  <?php endif; ?>
                </td>
                <td class="windowbg2 bg<?= $board['n'] ?> col_cat_td3" valign="middle" colspan="3" width="40%">
                  <font size="1"><?= $board['latestPostSubject'] ?><br>
                    <span class="latest-post-info"><?= $board['latestPostTime'] ?>, <?= $board['latestPostName'] ?></span>
                  </font>
                </td>
              </tr>
            <?php else: ?>
              <?php /* neither collapsed nor mobile */ ?>
              <tr class="category-row">
                <td class="windowbg bg<?= $board['n'] ?> cat_td1" width="6%" align="center" valign="middle">
                  <a href="<?= SITE_ROOT ?>/?collapseboard=<?= $idboard ?>">
                    <img src="<?= $this->conf->imagesdir ?>/<?= $board['new'] ?>.gif" alt="<?= $board['new_txt'] ?>" title="<?= $board['new_txt'] ?>" border="0">
                  </a>
                </td>
                <td class="windowbg2 bg<?= $board['n'] ?> cat_td2" align="left" width="60%">
                  <a name="b<?=$idboard ?>"></a>
                  <font size="2"><a href="<?= SITE_ROOT ?>/b<?= $idboard ?>/" class="boardHead"><b><?= $board['boardName'] ?></b></a></font>
                  <?php if($board['boardViewers'] > 0): ?>
                    <div class="boardViewersPane" style="font-size: 10px; float: right; font-weight: bold;">
                      <img src="<?= STATIC_ROOT ?>/img/YaBBImages/user.png" width="13" height="13" onmouseover="Forum.Data.showBoardViewersTooltip('<?= $idboard ?>')" title="Сейчас в разделе <?= $board['boardViewers'] ?> пользователей" alt="Сейчас в разделе <?= $board['boardViewers'] ?> пользователей"/> <span class="numBoardViewers"><?= $board['boardViewers'] ?></span>
                    </div>
                  <?php endif; ?><br>
                  <?= $board['description'] ?><?= $board['showmods'] ?>
                </td>
                <td class="windowbg bg<?= $board['n'] ?> cat_td3" valign="middle" align="center" width="6%">
                  <?= $board['numTopics'] ?>
                </td>
                <td class="windowbg bg<?= $board['n'] ?> cat_td4" valign="middle" align="center" width="6%">
                  <?= $board['numPosts'] ?>
                </td>
                <td class="windowbg2 bg<?= $board['n'] ?> cat_td5" valign="middle" width="22%">
                  <font size="1">
                    <?= $board['latestPostSubject'] ?><br>
                    <?= $board['latestPostName'] ?><br>
                    <?= $board['latestPostTime'] ?>
                  </font>
                </td>
              </tr>
            <?php endif; /* collapsed or mobile */ ?>
          <?php endforeach; /* boards */ ?>
          
        <?php endforeach; /* cats */ ?>
        
        <?php if ($this->user->name != 'Guest' ): ?>
          <tr>
            <td class="titlebg" colspan="6" align="center">
              <table cellpadding="0" border="0" cellspacing="0" width="100%">
                <tr>
                  <td align="left">
                    <img src="<?= $this->conf->imagesdir ?>/new_some.gif" border="0" alt="<?= $this->locale->txt(333) ?>" title="<?= $this->locale->txt(333) ?>">&nbsp;&nbsp;<img src="<?= $this->conf->imagesdir ?>/new_none.gif" border="0" alt="<?= $this->locale->txt(334) ?>" title="<?= $this->locale->txt(334) ?>">
                  </td>
                  <td align="right">
                    <font size="1">&nbsp;
                      <?php if ($this->conf->showmarkread == 1): ?>
                        <a href="<?= SITE_ROOT ?>/?markallasread=1"><font class="imgcatbg" size="1"><?= $this->locale->txt('markallread') ?></font></a>
                      <?php endif; ?>
                    </font>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        <?php endif; /* if guest */ ?>

      </table>
    </td></tr></table>
    
    <span class="main_br1"><br></span><span class="main_br2"><br></span> <?php /* WTF? Probably this made by Dzhyn */ ?>
    
    <table id="infoCenter" border="0" width="100%" cellspacing="0" cellpadding="0" class="bordercolor">
      <tr>
        <td>
          <table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
            <tr class="RecentBarTitle">
              <td class="titlebg" align="center" colspan="2">
                <b><?= $this->locale->txt(685) ?></b>
              </td>
            </tr>
            
            <?php if ($this->conf->Show_RecentBar == 1): ?>
              <tr>
                <td class="catbg RecentPostsTitle" colspan="2"><b><?= $this->locale->txt(214) ?></b></td>
              </tr>
              <tr>
                <td class="windowbg info_img" width="20" valign="middle" align="center">
                  <img src="<?= $this->conf->imagesdir ?>/xx.gif" border="0" alt="">
                </td>
                <td class="windowbg2 RecentPosts">
                  <a href="<?= SITE_ROOT ?>/recent/"><b><?= $this->locale->txt(214) ?></b></a><br>
                  <font size="1">
                    <?= $this->last_post ?>
                  </font>
                </td>
              </tr>
            <?php elseif ($this->conf->Show_RecentBar == 2): ?>
              <tr>
                <td class="catbg" colspan="2"><b><?= $this->locale->txt(214) ?></b></td>
              </tr>
              <tr>
                <td class="windowbg info_img" width="20" valign="middle" align="center">
                  <img src="<?= $this->conf->imagesdir ?>/xx.gif" border="0" alt="">
                </td>
                <td class="windowbg2 RecentComments">
                  <?= $this->last_postings ?>
                </td>
              </tr>
              <tr>
                <td class="catbg RecentCommentsTitle" colspan="2">
                  <a href="javascript: showHideRecentCommentsTable(); void(0);"><b>Последние комментарии</b></a></td>
                </tr>
              <tr>
                <td class="windowbg info_img" width="20" valign="middle" align="center">
                  <img src="<?= $this->conf->imagesdir ?>/xx.gif" border="0" alt="">
                </td>
                <td class="windowbg2">
                  <div id="recentCommentsTableInfo" style="display: <?= $this->recentCommentsTable ? "none" : "block" ?>">
                    Кликни <a href="javascript: showHideRecentCommentsTable(); void(0);">здесь</a> для просмотра последних комментариев к сообщениям
                  </div>
                  <?= $this->last_posts_comments ?>
                </td>
              </tr>
            <?php endif; /* if Show_RecentBar */ ?>
            
            <?php if (isset($this->calendar) && is_array($this->calendar)): ?>
              <?php $this->partial('templates/calendar/board_index.template.php') ?>
            <?php endif; ?>
            
            <?php if ($this->conf->Show_MemberBar == 1): ?>
              <tr class="MainMemberBar">
                <td class="catbg" colspan="2"><b><?= $this->locale->txt(331) ?></b></td>
              </tr>
              <tr class="MainMemberBar">
                <td class="windowbg info_img" width="20" valign="middle" align="center">
                  <img src="<?= $this->conf->imagesdir ?>/guest.gif" border="0" width="20" alt="">
                </td>
                <td class="windowbg2" width="100%">
                  <a href="<?= SITE_ROOT ?>/allmypeople/"><b><?= $this->locale->txt(332) ?></b></a><br>
                  <font size="1"><?= $this->locale->txt(200) ?></font>
                </td>
              </tr>
            <?php endif; /* Show_MemberBar */ ?>

            <?php if ($this->conf->enableSP1Info == 1): ?>
              <tr>
                <td class="catbg" colspan="2"><b><?= $this->locale->txt(645) ?></b></td>
              </tr>
              <tr>
                <td class="windowbg info_img" width="20" valign="middle" align="center">
                  <img src="<?= $this->conf->imagesdir ?>/info.gif" border="0" alt="">
                </td>
                <td class="windowbg2" width="100%">
                  <table border="0" width="90%">
                    <tr>
                      <td>
                        <font size="1">
                          <?= $this->locale->txt(94) ?> <?= $this->locale->txt(64) ?> <b> <?= $this->totalt ?></b> &nbsp;&nbsp;&nbsp;&nbsp; <?= $this->locale->txt(94) ?> <?= $this->locale->txt(95) ?> <b> <?= $this->totalm ?></b><br>
                          <?= $this->locale->txt(659) ?>
                          <?= $this->last_post_admin ?>
                          <br>
                          <a href="<?= SITE_ROOT ?>/recent/"><?= $this->locale->txt(234) ?></a>
                          <?php if ($this->conf->trackStats == 1): ?>
                            <br><a href="<?= SITE_ROOT ?>/stats/"><?= $this->locale->txt('yse223') ?></a>
                          <?php endif; ?>
                        </font>
                      </td>
                      <td>
                        <font size="1">
                          <?= $this->locale->txt(94) ?> <?= $this->locale->txt(19) ?>: <b><a href="<?= SITE_ROOT ?>/allmypeople/"><?= $this->memcount ?></a></b><br>
                          <?= $this->locale->txt[656] ?> <b><a href="<?= SITE_ROOT ?>/people/profile/<?= urlencode($this->thelatestmembe) ?>/"><?= $this->esc($this->thelatestrealname) ?></a></b>
                          <?= $this->thelatestmember2 ?><br>
                          <?php if ($this->user->name != 'Guest'): ?>
                            <?= $this->locale->txt('yse199') ?>: <b><a href="<?= SITE_ROOT ?>/im/"><?= $this->imcount[0] ?></a></b><?= $this->locale->newmessages3 ?>: <b><a href="<?= SITE_ROOT ?>/im/"><?= $this->imcount[1] ?></a></b>
                          <?php endif; ?>
                        </font>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            <?php endif; /*  enableSP1Info */ ?>
            
            <tr>
              <td class="catbg" colspan="2"><b><?= $this->locale->txt(158) ?></b></td>
            </tr>
            <tr>
              <td class="windowbg info_img" width="20" valign="middle" align="center">
                <img src="<?= $this->conf->imagesdir ?>/online.gif" border="0" alt="">
              </td>
              <td class="windowbg2" width="100%">
                <?= $this->guests ?> <?= $this->locale->txt(141) ?>, <?= $this->numusersonline ?> <?= $this->locale->txt(142) ?><br>
                <?= $this->users ?>
                <?php if ($this->conf->trackStats == 1 && $this->conf->enableSP1Info != 1): ?>
                  <br>
                  <font size="1">
                    <a href="<?= SITE_ROOT ?>/stats/"><?= $this->locale->yse223 ?></a>
                  </font>
                <?php endif; ?>
              </td>
            </tr>
            
            <tr>
              <td class="catbg" colspan="2">
                <b>Радио &laquo;Арена Электронной Музыки&raquo;</b>
              </td>
            </tr>
            <tr>
              <td class="windowbg info_img" width="20" valign="middle" align="center">
                <img src="<?= $this->conf->imagesdir?>/guest.gif" border="0" width="20" alt="">
              </td>
              <td class="windowbg2" width="100%" id="radioinfo">
                <p>Радио Арены сейчас не в эфире.</p>
                <p><a href="index.php?board=39">Подробнее о радио >>></a></p>
              </td>
            </tr>
            
            <?php if ($this->user->name != 'Guest' && $this->conf->enableSP1Info != 1): ?>
              <tr class="MainIM">
                <td class="catbg" colspan="2"><b><?= $this->locale->txt(159) ?></b></td>
              </tr>
              <tr class="MainIM">
                <td class="windowbg info_img" width="20" valign="middle" align="center">
                  <img src="<?= $this->conf->imagesdir ?>/message_sm.gif" border="0" alt="">
                </td>
                <td class="windowbg2" valign="top">
                  <a href="<?= SITE_ROOT ?>/im/"><b><?= $this->locale->txt(159) ?></b></a><br>
                  <font size="1">
                    <?= $this->locale->txt(660) ?> <?= $this->imcount[0] ?>
                    <?php if ($this->imcount[0] == 1): ?>
                      <?= $this->locale->txt(471) ?>.
                    <?php else: ?>
                      <?= $this->locale->txt(153) ?>.
                    <?php endif; ?>
                    <?= $this->locale->txt(661) ?> <a href="<?= SITE_ROOT ?>/im/"><?= $this->locale->txt(662) ?></a> <?= $this->locale->txt(663) ?>
                  </font>
                </td>
              </tr>
            <?php endif; ?>
            
            <?php if ($this->conf->enableVBStyleLogin != '1' && $this->user->name == 'Guest'): ?>
              <tr id="login-form">
                <td class="catbg" colspan="2">
                  <b><?= $this->locale->txt(34) ?></b>
                  <a href="<?= SITE_ROOT ?>/passwordreset/?what=input_user"><small>(<?= $this->locale->txt(315) ?>)</small></a>
                </td>
              </tr>
              <tr>
                <td class="windowbg info_img" width="20" align="center">
                  <img src="<?= $this->conf->imagesdir ?>/login_bindex.gif" border="0" alt="">
                </td>
                <td class="windowbg" valign="middle">
                  <form action="<?= SITE_ROOT ?>/login/" method="post">
                    <table border="0" cellpadding="2" cellspacing="0" align="center" width="100%">
                      <tr>
                        <td valign="middle" align="left">
                          <b><?= $this->locale->txt(35) ?>:</b><br>
                          <input type="text" name="user" size="20" />
                        </td>
                        <td valign="middle" align="left">
                          <b><?= $this->locale->txt(36) ?>:</b><br>
                          <input type="password" name="password" size="20" />
                        </td>
                        <td valign="middle" align="left">
                          <b><?= $this->locale->txt(497) ?>:</b><br>
                          <input type="text" name="cookielength" size="4" maxlength="4" value="<?= $this->conf->Cookie_Length ?>">
                        </td>
                        <td valign="middle" align="left">
                          <b><?= $this->locale->txt(508) ?>:</b><br>
                          <input type="checkbox" name="cookieneverexp" checked>
                        </td>
                        <td valign="middle" align="left">
                          <input type="submit" value="<?= $this->locale->txt(34) ?>">
                        </td>
                        
                        <?php if (!$this->request->isSecure()): ?>
                          <td valign="middle" align="left">
                            <a href="https://<?= $this->host ?><?= SITE_ROOT ?>/#login-form" title="<?= $this->locale->txt('ssl-link-info') ?>" class="ssl-login"><img src="<?= STATIC_ROOT?>/img/YaBBImages/lock_closed.png">SSL</a>
                          </td>
                        <?php endif; ?>
                      </tr>
                    </table>
                  </form>
                </td>
              </tr>
            <?php endif; /* enableVBStyleLogin */ ?>
          </table>
        </td>
      </tr>
    </table>
    
