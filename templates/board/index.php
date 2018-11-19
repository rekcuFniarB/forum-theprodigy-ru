
<table width="100%" cellpadding="0" cellspacing="0" class="boards_table">
  <tr>
    <td class="board_tree">
      <a name="top"></a>
      <?php $this->cpartial('templates/board/link_tree.php'); ?>
      <span class="link_tree_br"><br></span><br>
    </td> <!-- .board_tree -->
  </tr>
</table><!-- .boards_table -->

<?php if($this->conf->ShowBDescrip): ?>
  <table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor">
    <tr>
      <td>
        <table width="100%" cellpadding="3" cellspacing="1" border="0" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor">
          <tr>
            <td align="left" class="catbg" bgcolor="<?= $this->conf->color['catbg'] ?>" width="100%"  height="30">
              <table cellpadding="3" cellspacing="0" width="100%">
                <tr>
                  <td class="bdescrip" width="100%">
                    <font size="1"><?= $this->get('bdescrip') ?></font>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        <?php if($this->conf->boardBillsEnabled): ?>
          <tr>
            <td align="left" class="catbg" bgcolor="<?= $this->conf->color['catbg'] ?>" width="100%">
              <table cellpadding="3" cellspacing="0" width="100%">
                <tr>
                  <td width="100%" id="chatWall">
                    <font size="1">
                      <div id="chatWallText" style="margin: 0px 0px 10px 0px;">
                        <?= $this->doUBBC($this->chatWall['chatWall']) ?>
                      </div>
                      <div>
                      <?php if($this->user->id != -1 && $this->user->posts >= 100): ?>
                        <a href="javascript: editChatWall(<?= $this->board ?>); void(0);"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/edit.png" alt="редактировать" width="14" height="14" border="0"></a> <a href="javascript: updateChatWall(<?= $this->board ?>); void(0);"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/reload.png" alt="перезагрузить" width="14" height="14" border="0"></a><img src="<?= STATIC_ROOT ?>/img/YaBBImages/loading.gif" id="loading" alt="loading" style="display: none;">
                      <?php endif; ?>
                      
                      <?php if($this->chatWall['chatWallMsgAuthor'] !=null): ?>
                        <div style="float: right; font-style: italic;">
                          <?php if ($this->user->accessLevel() > 1): ?>
                            <a href="javascript: restoreChatWall(<?= $this->board ?>); void(0);">[восстановить]</a> <a href="javascript: backupChatWall(<?= $this->board ?>); void(0);">[создать резервную копию]</a>
                          <?php endif; ?>
                          Последний автор: <?= $this->chatWall['chatWallMsgAuthor'] ?>
                        </div>
                      <?php endif; ?>
                      </div>
                    </font>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td align="left" class="catbg" bgcolor="<?= $this->conf->color['catbg'] ?>" width="100%">
              <table cellpadding="3" cellspacing="0" width="100%">
                <tr>
                  <td id="viewers" width="100%">
                    <?php if(isset($this->boardviewers)): ?>
                        <?php $this->partial('templates/parts/boardviewers.template.php'); ?>
                    <?php endif; ?>
                  </td>
                  
                  <td class="sphinx-search-topic-form">
                    <form method="POST" action="<?= SITE_ROOT ?>/b<?= $this->board ?>/all/" onsubmit="if (this.elements[0].value == '') this.elements[1].value = 'notAll'" style="margin: 0px; padding: 0px;">
                      <input type="text" name="search" value="<?= $this->get('search') ?>" placeholder="<?php
                      switch ($this->board) {
                        case 2: echo "Поиск новости, статьи или интервью"; break;
                        case 4: echo "Поиск опроса"; break;
                        case 12: echo "Поиск исполнителя или стиля"; break;
                        case 13: echo "Поиск фильма"; break;
                        case 14: echo "Поиск игры"; break;
                        case 19: echo "Поиск автора или трека"; break;
                        case 24: echo "Поиск книги или автора"; break;
                        case 27: echo "Поиск выступления"; break;
                        case 31: echo "Поиск мероприятия"; break;
                        case 36: echo "Поиск ремикса"; break;
                        case 43: echo "Поиск аудио, видео или фото"; break;
                        case 46: echo "Поиск проекта или релиза"; break;
                        case 47: echo "Поиск блога"; break;
                        default: echo "Поиск темы";
                      }
                      ?>"/>
                      <!-- <input type="hidden" name="view" value="all"/>-->
                      <input type="submit" value="<?= $this->locale->txt['sphinx-btn-submit'] ?>">
                    </form>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        <?php endif; /* boardBillsEnabled */ ?>
        </table>
      </td>
    </tr>
  </table>
<?php endif; /* if ShowBDescrip */ ?>

<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor">
  <tr>
    <td>
      <table width="100%" cellpadding="3" cellspacing="1" border="0" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor">
        <tr id="boardTopNav">
          <td align="left" class="catbg" bgcolor="<?= $this->conf->color['catbg'] ?>" width="100%"  height="30">
            <table cellpadding="3" cellspacing="0" width="100%">
              <tr>
                <td class="board_pageindex">
                  <font size="2">
                    <b><?= $this->locale->txt[139] ?>:</b> <?= $this->pageindex ?>
                  </font>
                  <?php if ($this->conf->topbottomEnable): ?>
                    <?= $this->menusep ?><a href="#bot"> <?= $this->locale->img['tbbottom'] ?></a>
                  <?php endif; ?>
                </td>
                <td class="board_menu" align="right" nowrap="nowrap">
                  <font size="-1"><?= implode($this->menusep, $this->buttonArray) ?></font>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor">
  <tr>
    <td>
      <table border="0" width="100%" cellspacing="1" cellpadding="4" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor topic-list">
        <tr class="topicorder">
          <td class="titlebg" bgcolor="<?= $this->conf->color['titlebg'] ?>" width="10%" colspan="2">
            <font size="2">&nbsp;</font>
          </td>
          <td align="center" class="titlebg topictitle" bgcolor="<?= $this->conf->color['titlebg'] ?>" width="48%">
            <font size="2" class="text1" color="<?= $this->conf->color['titletext'] ?>">
              <b><?= $this->locale->txt[70] ?></b>
            </font>
          </td>
          <td align="center" class="titlebg topicstarter" bgcolor="<?= $this->conf->color['titlebg'] ?>" width="<?= $this->showTopicRatings?'13%':'14%' ?>">
            <font size="2" class="text1" color="<?= $this->conf->color['titletext'] ?>">
              <b><?= $this->locale->txt[109] ?></b>
            </font>
          </td>
          <td align="center" class="titlebg topicreplies" bgcolor="<?= $this->conf->color['titlebg'] ?>" width="<?= $this->showTopicRatings?'3%':'4%'?>">
            <font size="2" class="text1" color="<?= $this->conf->color['titletext'] ?>">
              <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/?orderby=numreplies"><b> <?= $this->locale->txt[110] ?></b></a>
              <?php if($this->orderby == "numreplies"): ?>
                <br>
                <img src="<?= STATIC_ROOT ?>/img/YaBBImages/blue_down.png" width="12" height="12" title="Сортировка по кол-ву ответов">
              <?php endif; ?>
            </font>
          </td>
          <td align="center" class="titlebg topicviews" bgcolor="<?= $this->conf->color['titlebg'] ?>" width="<?= $this->showTopicRatings?'3%':'4%' ?>">
            <font size="2" class="text1" color="<?= $this->conf->color['titletext'] ?>">
              <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/?orderby=numviews"><b><?= $this->locale->txt[301] ?></b></a>
              <?php if ($this->orderby=="numviews"): ?>
                <br>
                <img src="<?= STATIC_ROOT ?>/img/YaBBImages/blue_down.png" width="12" height="12" title="Сортировка по кол-ву просмотров">
              <?php endif; ?>
            </font>
          </td>
        
        <?php if($this->showTopicRatings==16): ?>
          <td align="center" class="titlebg" bgcolor="<?= $this->conf->color['titlebg'] ?>" width="3%">
            <font size="2" class="text1" color="<?= $this->conf->color['titletext'] ?>">
              <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/?orderby=<?= $this->orderby=='posrating'?'negrating':'posrating' ?>"><b>Оценки</b></a>
              
              <?php if($this->orderby=="posrating" or $this->orderby=="negrating"): ?>
                <br>
                <img src="<?= STATIC_ROOT ?>/img/YaBBImages/blue_down.png" width="12" height="12" title="Сортировка по <?= $this->orderby=="posrating"?'позитивным':'негативным' ?> оценкам">
              <?php endif; ?>
            </font>
          </td>
        <?php endif; ?>
          
          <td align="center" class="titlebg topiclast" bgcolor="<?= $this->conf->color['titlebg'] ?>" width="27%">
            <font size="2" class="text1" color="<?= $this->conf->color['titletext'] ?>">
              <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/"><b><?= $this->locale->txt[111] ?></b></a>
              <?php if(empty($this->orderby)): ?>
                <br>
                <img src="<?= STATIC_ROOT ?>/img/YaBBImages/blue_down.png" width="12" height="12" title="Сортировка по дате последнего сообщения и прикреплённым темам">
              <?php endif; ?>
            </font>
          </td>
        </tr>
        
      <?php if(!empty($this->search_not_found)): ?>
        <tr>
          <td class="windowbg" valign="middle" width="100%" bgcolor="<?= $this->conf->color['windowbg'] ?>" colspan="8" align="center" style="height: 75px;">
            <font size="2">
              <b>Ни одной темы не найдено <?= !empty($this->search) ? ' по запросу ' . $this->get('search') : '' ?></b>
            </font>
          </td>
        </tr>
      <?php endif; ?>
      
      <?php foreach ($this->topics as $tid => $topic): ?>
        <tr class="topic-item">
          <td class="topic-status windowbg2" valign="middle" align="center" width="6%" bgcolor="<?= $this->conf->color['windowbg2'] ?>">
            <img src="<?= $this->conf->imagesdir ?>/<?= $topic['threadclass'] ?>.gif" alt="">
          </td>
          <td class="topic-icon windowbg2 topicicon" valign="middle" align="center" width="4%" bgcolor="<?= $this->conf->color['windowbg2'] ?>">
            <img src="<?= $this->conf->imagesdir ?>/<?= $topic['micon'] ?>.gif" alt="" border="0" align="middle">
          </td>
          <td class="windowbg topictitle" valign="middle" width="48%" bgcolor="<?= $this->conf->color['windowbg'] ?>">
            <?php if($this->user->name != 'Guest' && $topic['new']): ?>
              <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $tid ?>/new/"><img src="<?= $this->conf->imagesdir ?>/new.gif" alt="<?= $this->locale->txt[302] ?>" title="<?= $this->locale->txt[302] ?>" border="0"></a>
            <?php endif; ?>
            <?php if($this->user->name != 'Guest' && $topic['newComments']): ?>
              <a href="<?= SITE_ROOT ?>/<?= $topic['newComments'] ?>"><img src="<?= $this->conf->imagesdir ?>/newcomments.gif" alt="<?= $this->locale->txt['302a'] ?>" title="<?= $this->locale->txt['302a'] ?>" border="0"></a>
            <?php endif; ?>
            
            <font size="2">
              <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $tid ?>/" title="<?= $topic['firstMessage'] ?>"><b><?= $topic['msub'] ?></b></a>
              <br><?= $this->app->board->topicPages($tid, $topic['mreplies']) ?>
            </font>
          </td>
          <td class="windowbg2 topicstarter" valign="middle" width="<?= $this->showTopicRatings?'13%':'14%' ?>" bgcolor="<?= $this->conf->color['windowbg2'] ?>">
            <font size="1"><center>
              <?php if($topic['mid'] != -1): ?>
                <a href="<?= SITE_ROOT ?>/people/<?= urlencode($topic['mname']) ?>/"><acronym title="<?= $this->locale->txt[92] ?> <?= $topic['name2'] ?>"><?= $topic['name2'] ?></acronym></a>
              <?php else: ?>
                <?= $this($topic['mname']); ?>
              <?php endif; ?>
            </center></font>
          </td>
          <td class="windowbg topicreplies" valign="middle" width="<?= $this->showTopicRatings?'3%':'4%' ?>" align="center" bgcolor="<?= $this->conf->color['windowbg'] ?>">
            <font size="2"><?= $topic['mreplies'] ?></font>
          </td>
          <td class="windowbg topicviews" valign="middle" width="<?= $this->showTopicRatings?'3%':'4%' ?>" align="center" bgcolor="<?= $this->conf->color['windowbg'] ?>">
            <font size="2"><?= $topic['views'] ?></font>
          </td>
        <?php if($this->showTopicRatings): ?>
          <td class="topic-rating windowbg" valign="middle" width="3%" align="center" bgcolor="<?= $this->conf->color['windowbg'] ?>">
            <font size="2">
              <a href="javascript: raiseTopicRating(<?= $tid ?>); void(0);"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/plus.gif" width="10" height="10" border="0"></a> <span id="positiveTopicRating<?= $tid ?>" style="font-size: 12px"><?= $topic['topicPosRating'] ?></span><br /><a href="javascript: lowerTopicRating(<?= $tid ?>); void(0);"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/minus.gif" width="10" height="10" border="0"></a> <span id="negativeTopicRating<?= $tid ?>" style="font-size: 12px"><?= $topic['topicNegRating'] ?></span>
            </font>
          </td>
        <?php endif; /* showTopicRatings */ ?>
          
          <td class="windowbg2 topiclast" valign="middle" width="27%" bgcolor="<?= $this->conf->color['windowbg2'] ?>">
            <font size="1">
              <?php if($this->user->inIgnore($topic['lastposter'])): ?>
                &nbsp;
              <?php elseif($topic['lastPosterID'] != -1): ?>
                <a href="<?= SITE_ROOT ?>/people/<?= urlencode($topic['lastposter']) ?>/"><b><?= $topic['name1'] ?></b></a>
              <?php else: ?>
                <?= $this->esc($topic['lastposter']) ?>
              <?php endif; ?>
              <br class="topiclast_br">
              <span class="topiclast_time"><?= $this->subs->timeformat($topic['mdate']) ?></span>
            </font>
          </td>
        </tr>
      <?php endforeach; ?>
      
      </table> <!-- .topic-list -->
    </td>
  </tr>
</table>

<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor">
  <tr>
    <td>
      <table width="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor">
        <tr>
          <td align="left" class="catbg" bgcolor="<?= $this->conf->color['catbg'] ?>" width="100%"  height="30">
            <table cellpadding="3" cellspacing="0" width="100%">
              <tr>
                <td class="board_pageindex">
                  <a name="bot"></a><font size="2"><b><?= $this->locale->txt[139] ?>:</b> <?= $this->pageindex ?></font><?= $this->toplink ?>
                </td>
                <td class="board_menu" align="right">
                  <font size="-1"><?= implode($this->menusep, $this->buttonArray) ?></font>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<table cellpadding="0" cellspacing="0" width="100%">
  <?php if($this->conf->enableInlineLinks): ?>
    <tr>
      <td class="board_tree" colspan="3">
        <br>
        <?php $this->cpartial('templates/board/link_tree.php'); ?>
        <br>
        <br>
      </td>
    </tr>
  <?php endif; ?>
  
  <tr class="boardlegend">
    <td align="left" valign="middle">
      <img src="<?= $this->conf->imagesdir ?>/hotthread.gif" alt="">
      <font size="1"><?= $this->locale->txt[454] ?></font>
      <br>
      <img src="<?= $this->conf->imagesdir ?>/veryhotthread.gif" alt="">
      <font size="1"><?= $this->locale->txt[455] ?></font>
      <?php if($this->conf->enableStickyTopics): ?>
        <br>
        <img src="<?= $this->conf->imagesdir ?>/sticky.gif" alt="">
        <font size="1"><?= $this->locale->yse96 ?></font>
      <?php endif; ?>
      
      <?php if($this->conf->pollMode): ?>
        <br>
        <img src="<?= $this->conf->imagesdir ?>/poll.gif" alt="">
        <font size="1"><?= $this->locale->yse43 ?></font>
      <?php endif; ?>
    </td>
    <td align="left" valign="middle">
      <img src="<?= $this->conf->imagesdir ?>/locked.gif" alt="">
      <font size="1"><?= $this->locale->txt[456] ?></font>
      <br>
      <img src="<?= $this->conf->imagesdir ?>/thread.gif" alt="">
      <font size="1"><?= $this->locale->txt[457] ?></font>
      <?php if($this->conf->enableStickyTopics): ?>
        <br>
        <img src="<?= $this->conf->imagesdir ?>/lockedsticky.gif" alt="">
        <font size="1"><?= $this->locale->yse97 ?></font>
      <?php endif; ?>  
      
      <?php if($this->conf->pollMode): ?>
        <br>
        <img src="<?= $this->conf->imagesdir ?>/locked_poll.gif" alt="">
        <font size="1"><?= $this->locale->yse98 ?></font>
      <?php endif; ?>
    </td>
    <td align="right" valign="middle">
      <?php if(isset($this->jumptoform)): ?>
        <?php $this->partial('templates/board/jumpto.template.php') ?>
      <?php endif; ?>
    </td>
  </tr>
</table>

