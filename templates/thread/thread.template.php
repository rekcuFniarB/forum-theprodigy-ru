    <script language="JavaScript1.2" type="text/javascript"><!--
      function DoConfirm(message, url)
      {
        if (confirm(message))
          location.href = url;
      }
    //--></script>
    
    <?php if($this->isBlog): ?>
      <script language="javascript" type="text/javascript">
        <!--
          function blogWin(URL)
          {
            desktop = window.open(URL, "blogComments", "toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=500,height=450,resizable=yes");
          }
        // -->
      </script>
    <?php endif; ?>
    
    <table id="top" width="100%" cellpadding="0" cellspacing="0">
      <tr>
        <td class="board_tree" valign="bottom">
          
          <?php $this->start_cache('curthreadurl') ?>
            <?php if($this->conf->curposlinks): ?>
              <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $this->thread ?>/" class="nav curthreadurl"><?= $this->msubthread ?></a>
            <?php else: ?>
              <?= $this->msubthread ?>
            <?php endif; ?>
          <?php $this->end_cache() ?>
          
          <!-- # the link tree -->
          <?php $this->start_cache('linktree'); ?>
              <?php $this->partial('parts/linktree.template.php') ?>
              <?php $this->_partial('thread/showmods.template.php', array('showmods' => $this->showmods)) ?>
              <?php if($this->conf->enableInlineLinks): ?>
                <br><br>
                <table class="curthread">
                  <tr valign="top" width="100%" align="right">
                    <td valign="top" width="100%" align="left">
                      <font color="#FFFFFF"><?= $this->locale->txt[118] ?>:&nbsp;</font><b><?php $this->get_cache('curthreadurl') ?></b>
                    </td>
                  </tr>
                </table>
              <?php else: ?>
                <br>
                <img src="<?= $this->conf->imagesdir ?>/tline3.gif" border="0" alt=""><img src="<?= $this->conf->imagesdir ?>/open.gif" border="0" alt="">&nbsp;&nbsp;<?= $this->get_cache('curthreadurl') ?>
              <?php endif; ?>
          <?php $this->get_cache('linktree'); ?>
        </td>
        
        <?php /* Create a previous next string if the selected theme has it as a selected option */ ?>
        <?php if($this->conf->enablePreviousNext): ?>
          <?php $this->start_cache('previousNext') ?>
            <a href="<?= SITE_ROOT ?>/b<?= $this->board?>/t<?= $this->thread ?>/prev/"><?= $this->conf->PreviousNext_back?></a> <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $this->thread?>/prev/"><?= $this->conf->PreviousNext_forward?></a>
          <?php $this->end_cache() ?>
        <?php endif; ?>
        
        <td class="board_tree_current" valign="bottom" align="right">
          <font size="1" class="nav"><?= $this->get_cache('previousNext') ?></font>
        </td>
      </tr>
    </table><br>
    <table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor">
      <tr>
        <td>
          <table width="100%" cellpadding="3" cellspacing="1" border="0" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor">
          
            <?php if($this->conf->boardBillsEnabled): ?>
              <tr>
                <td align="left" class="catbg" bgcolor="<?= $this->conf->color['catbg'] ?>" width="100%"  height="35">
                  <table cellpadding="3" cellspacing="0" width="100%">
                    <tr>
                      <td id="chatWall">
                        <font size="1">
                          <div id="chatWallText" style="margin: 0px 0px 10px 0px;">
                            <?= $this->DoUBBC($this->billboard['chatWall']) ?>
                          </div>
                          <div>
                            <?php if($this->user->id != -1 && $this->user->posts >= 100): ?>
                              <a href="javascript: editChatWall(<?= $this->board ?>); void(0);"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/edit.png" alt="редактировать" width="14" height="14" border="0"></a>
                              <a href="javascript: updateChatWall(<?= $this->board ?>); void(0);"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/reload.png" alt="перезагрузить" width="14" height="14" border="0"></a>
                              <img src="<?= STATIC_ROOT ?>/img/YaBBImages/loading.gif" id="loading" alt="loading" style="display: none;">
                            <?php endif; ?>
                            
                            <?php if($this->billboard['chatWallMsgAuthor'] != null): ?>
                              <div style="float: right; font-style: italic;">
                                Последний автор: <?= $this->billboard['chatWallMsgAuthor'] ?>
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
                            <?php $this->partial('parts/boardviewers.template.php'); ?>
                        <?php endif; ?>
                      </td>
                      
                      <?php if (!empty($this->conf->sphinx) && is_array($this->conf->sphinx) && $this->conf->sphinx['enabled']): ?>
                        <td class="sphinx-search-topic-form">
                          <form action="<?= SITE_ROOT ?>/newsearch/" method="get" accept-charset="UTF-8">
                            <input type="text" name="q" maxlength="256" placeholder="<?= $this->locale->txt['sphinx-input-search-in-topic'] ?>" required autocomplete="off">
                            <input type="hidden" name="thread" value="<?= $this->thread ?>">
                            <input type="submit" value="<?= $this->locale->txt['sphinx-btn-submit']?>">
                          </form>
                        </td>
                      <?php endif; ?>
                    </tr>
                  </table>
                </td>
              </tr>
            <?php endif; /* boardBillsEnabled */?>
            
            <tr id="boardTopNav">
              <td align="left" class="catbg" bgcolor="<?= $this->conf->color['catbg'] ?>" width="100%"  height="35">
                <table cellpadding="3" cellspacing="0" width="100%">
                  <tr>
                    <td class="board_pageindex">
                      <font size="1"><b><?= $this->locale->txt[139] ?>:</b>
                      <?= $this->pageindex ?></font>
                      <?php if ($this->conf->topbottomEnable): ?>
                        <?= $this->menusep ?><a href="#bot"><?= $this->locale->tbbottom ?></a>
                      <?php endif; ?>
                    </td>
                    <td class="board_menu" align="right">
                      <font size="-1">
                        <?php $this->start_cache('topicButtons'); ?>
                        <!-- Topic buttons -->
                        <?php $this->menu_begin() ?>
                        <?php if(!$this->mstate || $this->user->accessLevel() > 1): ?>
                          <?php if(!$this->isBlog || ($this->isBlog && $this->topicinfo['ID_MEMBER'] == $this->user->id)): ?>
                            <?php if($this->user->allowedToReply($this->user->id)): ?>
                              <?= $this->menusep() ?>
                              <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $this->thread ?>/reply/"><font size="1" class="imgcatbg"><?= $this->locale->reply ?></font></a>
                            <?php endif; ?>
                          <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if($this->conf->enable_notification): ?>
                          <?= $this->menusep() ?>
                          <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $this->thread ?>/<?= $this->start ?>/notify/"><font size="1" class="imgcatbg"><?= $this->locale->notify ?></font></a>
                        <?php endif; ?>

                        <?php /* ?>
                        <?= $this->menusep() ?>
                        <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $this->thread ?>/sendtopic/"><font size="1" class="imgcatbg"><?= $this->locale->sendtopic ?></font></a> <?php */ ?>
                        
                        <?php /*
                        <?= $this->menusep() ?>
                        <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $this->thread ?>/printpage/" target="_blank"><font size="1" class="imgcatbg"><?= $this->locale->printt ?></font></a> <?php */ ?>
                        
                        <?php if($this->user->id != -1): ?>
                          <?= $this->menusep() ?>
                          <a href="#" onclick="editTopicBillboard(<?= $this->thread ?>); return false;"><font size="1" class="imgcatbg">Редактировать афишу</font></a>
                        <?php endif; ?>
                        <?php $this->end_cache(); ?>
                        <?php $this->get_cache('topicButtons'); ?>
                      </font>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    
    <?php if(isset($this->pollinfo)): ?>
      <?php $this->partial('thread/poll.template.php'); ?>
    <?php endif; /* if poll */ ?>
    
    <table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor" align="center">
      <tr>
        <td>
          <table cellpadding="3" cellspacing="1" border="0" width="100%">
            <tr>
              <td valign="middle" align="left" width="15%" bgcolor="' . $color['titlebg'] . '" class="titlebg topic_img">
                <font size="2" class="text1" color="<?= $this->conf->color['titletext'] ?>">&nbsp;<img src="<?= $this->conf->imagesdir ?>/<?= $this->threadclass ?>.gif" alt="">
                  &nbsp;<b><?= $this->txt[29] ?></b>
                </font>
              </td>
              <td valign="middle" align="left" bgcolor="<?= $this->conf->color['titlebg'] ?>" class="titlebg topic_name" width="<?= $this->showTopicRating?'75%':'85%'?>">
                <font size="2" class="text1" color="<?= $this->conf->color['titletext'] ?>">
                  <b>&nbsp;<?= $this->locale->txt[118] ?>: <?= $this->msubthread ?></b> &nbsp;( <?= $this->locale->txt[641] ?>  <?= $this->topicinfo['numViews'] ?> <?= $this->locale->txt[642] ?>)
                </font>
              </td>
              <?php if($this->showTopicRating): ?>
                <td valign="middle" align="center" bgcolor="<?= $this->conf->color['titlebg'] ?>" class="titlebg" width="10%">
                  <font size="2" class="text1" color="<?= $this->conf->color['titletext'] ?>">
                    <b>Оценки темы:</b>
                    <br>
                    <a href="javascript: raiseTopicRating(<?= $this->thread ?>); void(0);"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/plus.gif" width="10" height="10" border="0"></a>
                    <span id="positiveTopicRating<?= $this->thread?>" style="font-size: 12px"><?= $this->topicPosRating ?></span>&nbsp;&nbsp;<a href="javascript: lowerTopicRating(<?= $this->thread ?>); void(0);"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/minus.gif" width="10" height="10" border="0"></a>
                    <span id="negativeTopicRating<?= $this->thread ?>" style="font-size: 12px"><?= $this->topicNegRating ?></span>
                  </font>
                </td>
              <?php endif; ?>
            </tr>
          </table>
        </td>
      </tr>
      <tr id="topicBillboardArea" <?= $this->showTopicBillboard ? '' : ' style="display: none;"'?>>
        <td align="left" class="catbg" bgcolor="' . $color['catbg'] . '" width="100%"  height="35">
          <table cellpadding="3" cellspacing="1" width="100%">
            <tr>
              <td>
                <font size="2">
                  <div id="topicBillboard" style="margin: 0px 0px 10px 0px;">
                    <?= $this->doUBBC(stripslashes($this->billboard['topicBillboard'])) ?>
                    <?php if(!empty($this->billboard['topicBillboardAuthor'])): ?>
                      <p>
                        <font size="1">
                          <i>Редактировал<?= $this->billboard['topicBillboardAuthorGender']=="Female"?"а":""?> афишу
                            <?php if(isset($this->billboard['topicBillboardAuthorName'])): ?>
                              <a href="<?= SITE_ROOT ?>/people/<?= urlencode($this->billboard['topicBillboardAuthor']) ?>/"><b><?= $this->esc($this->billboard['topicBillboardAuthorName']) ?></b></a>
                            <?php else: ?>
                              <b><?= $this->esc($this->billboard['topicBillboardAuthor']) ?></b>
                            <?php endif; ?>  
                          </i>
                        </font>
                      </p>
                    <?php endif; ?>
                  </div>
                </font>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    
    <table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor" align="center">
    
    <?php foreach($this->messages as $msgid => $msg): ?>
      <tr id="msg<?= $msgid ?>" class="message-table-tr" data-msgid="<?= $msgid ?>" data-userid="<?= $msg['ID_MEMBER'] ?>">
        <td>
          <table class="message-table" cellpadding="3" cellspacing="1" border="0" width="100%">
            <tr>
              <td bgcolor="<?= $msg['windowbg'] ?>" class="<?= $msg['css'] ?>">
                <table width="100%" cellpadding="4" cellspacing="1" class="<?= $msg['css'] ?>" bgcolor="<?= $msg['windowbg']?>">
                  <tr>
                    <td class="userinfo <?= $msg['css'] ?>" bgcolor="<?= $msg['windowbg']?>" valign="top" width="15%" rowspan="2">
                      <?php if(!$msg['guest']): ?>
                        <a href="<?= SITE_ROOT ?>/people/<?=  urlencode($msg['posterName']) ?>/" <?php if($msg['userset']['banned']): ?> class="banned-user" <?php endif; ?>>
                          <font size="2">
                            <b><acronym title="<?= $this->locale->txt[92] ?> <?= $msg['userset']['realName']?>"><?= $msg['userset']['realName']?></acronym></b>
                          </font>
                        </a>
                        <div class="memberinfo">
                          <?php if($this->conf->titlesEnable && $msg['userset']['usertitle'] != ''): ?>
                            <span class="member-custom-status"><?= $this->esc($msg['userset']['usertitle']) ?></span>
                            <br>
                          <?php endif; ?>
                          <span class="member-status">
                            <?php if($this->thread == 28707): ?>
                              <b><?= $this->membergroups[7] ?></b>
                            <?php else: ?>
                              <?= $msg['userset']['memberinfo'] ?>
                            <?php endif; ?>
                          </span>
                          <br>
                          
                          <span class="star"><?= $msg['userset']['memberstar'] ?></span>
                          <br>
                          
                          <?php if($msg['ID_MEMBER'] != 569): ?>
                            <a class="votes_link" href="<?= SITE_ROOT ?>/people/<?= $this->esc($msg['posterName']) ?>/karma/">[Заценки]</a>
                          <?php endif; ?>
                          
                          <br />
                          <a class="comments_link" href="<?= SITE_ROOT ?>/comments/by/<?= $this->esc($msg['posterName']) ?>/">[Комментарии]</a>
                          
                          <span class="brs"><br>
                          <br></span>
                          
                          <p class="online">
                            <?php if($this->conf->onlineEnable): ?>
                              <?php if ($msg['isOnline'] > 0): ?>
                                <a href="<?= SITE_ROOT ?>/im/new/?to=<?= urlencode($msg['posterName']) ?>"><?= $this->locale->online2 ?>
                                <br>
                                <br>
                              <?php else: ?>
                                <a href="<?= SITE_ROOT ?>/im/new/?to=<?= urlencode($msg['posterName']) ?>"><?= $this->locale->online3 ?>
                              <?php endif; ?>
                            <?php endif; ?>
                          </p>
                          <span class="ava"><?= $msg['avatar'] ?></span>
                          <p class="personalText">
                            <?= $msg['userset']['personalText'] ?>
                          </p>
                          <p class="otherinfo">
                            <a class="euser" href="<?= SITE_ROOT ?>/im/new/?to=<?= urlencode($msg['posterName']) ?>" target="_blank"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/private_message.png" title="<?= $this->locale->txt[516] ?>" border="0" /></a> 
                            <?= $msg['userset']['websiteUrl'] ?>
                            <?= $msg['userset']['yimon'] ?>
                            <?= $msg['userset']['ICQ'] ?>
                            <?= $msg['userset']['MSN']?>
                            <?= $msg['userset']['AIM'] ?>
                            <br>
                            <?php if(!empty($msg['userset']['location'])): ?>
                              Город: <a href="<?= SITE_ROOT ?>/people/<?= urlencode($msg['posterName']) ?>/"><img border="0" src="<?= STATIC_ROOT ?>/img/YaBBImages/city.gif" title="<?= $msg['userset']['location'] ?>"></a>
                              <br>
                            <?php endif; ?>
                            
                            <?= $msg['userset']['gender'] ?>
                            
                            <?php if($msg['posterName'] == 'VoloS'): ?>
                            <?php else: ?>
                              <?= $this->locale->txt[26] ?> <?= $msg['userset']['posts'] ?>
                            <?php endif; ?>
                          </p> <!-- .otherinfo -->
                        </div>
                      <?php else: /* if guest */ ?>  
                        <font size="2"><b><?= $msg['posterName'] ?></b></font>
                        <div class="memberinfo">
                          <span class="member-status"><?= $this->locale->txt[28] ?></span>
                          <br>
                          <br /><a href="mailto:<?= $this->esc($msg['posterEmail']) ?>"><?= $this->locale->email_sm ?></a>
                        </div>
                      <?php endif; /* if guest */ ?>
                    </td> <!-- .userinfo -->
                    
                    <td class="post_table <?= $msg['css'] ?>" bgcolor="<?= $msg['windowbg'] ?>" valign="top" width="85%" height="100%">
                      <table width="100%" border="0">
                        <tr>
                          <td class="message_icon" align="left" valign="middle">
                            <img src="<?= $this->conf->imagesdir ?>/<?= urlencode($msg['icon']) ?>.gif" alt="">
                          </td>
                          <td class="message_info" align="left" valign="middle">
                            <font size="2" class="message_title">
                              <b><?= $this->esc($msg['subject']) ?></b>
                            </font>
                            <br>
                            <font size="1" class="msg-id-date">
                              <b class="postcounter">
                                <?php if($msg['counter'] != 0): ?>
                                  <a href="<?= SITE_ROOT ?>/<?= $msgid ?>" rel="nofollow"><?= $this->locale->txt[146] ?>#<?= $msg['counter']?></a>
                                <?php else: ?>
                                  <a href="<?= SITE_ROOT ?>/<?= $msgid ?>" rel="nofollow">#</a>
                                <?php endif; ?>
                              </b>
                              <span class="postdate"><?= $this->app->subs->timeformat($msg['posterTime']) ?></span>
                            </font>
                          </td>
                          <td align="right" valign="bottom" height="20" nowrap="nowrap" class="message-buttons">
                            <font size="-1">
                              <!-- message buttons -->
                              <?php $this->menu_begin() ?>
                              <?php if (!$this->mstate || $this->user->accessLevel() > 1): ?>
                                <?php if (!$this->isBlog || ($this->isBlog && $this->topicinfo['ID_MEMBER'] == $this->user->id)): ?>
                                  <?= $this->menusep() ?>
                                  <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $this->thread ?>/reply/<?= $msgid ?>/" onclick="return quickReplyQuote(event, '<?= SITE_ROOT ?>', <?= $this->thread ?>, <?= $msgid ?>, '<?= urlencode($this->locale->txt[116]) ?>', '<?= $this->start ?>', '<?= $this->app->session->id ?>');"><font size="1" class="imgwindowbg"><?= $this->locale->replyquote ?></font></a>
                                <?php endif; ?>
                                
                                <?php if($this->user->accessLevel() > 1 || ($this->user->id == $msg['ID_MEMBER'] && $this->user->id != -1)): ?>
                                  <?= $this->menusep() ?>
                                  <a href="<?= SITE_ROOT ?>/modify/<?= $msgid ?>/"><font size="1" class="imgwindowbg"><?= $this->locale->modify ?></font></a>
                                  <?= $this->menusep() ?>
                                  <a href="<?= SITE_ROOT ?>/delete/<?= $msgid ?>/" onclick="Forum.Utils.deleteMessage(event)"><font size="1" class="imgwindowbg"><?= $this->locale->delete ?></font></a>
                                
                                <?php elseif($msg['ID_MEMBER'] == -1 and $this->user->id != -1 and $this->user->posts >= 600): ?>
                                  <?php /* Add possibility for registered users with more than 600 msgs to delete guest messages. Added by dig7er, 18.09.2010 */ ?>
                                  <?= $this->menusep() ?>
                                  <a href="<?= SITE_ROOT ?>/delete/<?= $msgid ?>/" onclick="Forum.Utils.deleteMessage(event)"><font size="1" class="imgwindowbg"><?= $this->locale->delete ?></font></a>
                                <?php endif; ?>
                                
                                <?php if($this->user->accessLevel() > 1): ?>
                                  <?= $this->menusep() ?>
                                  <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $this->thread ?>/split/<?= $msgid ?>/"><font size="1" class="imgwindowbg"><?= $this->locale->split ?></font></a>
                                <?php endif; ?>
                                
                                <?php if(!$this->user->mobileMode): ?>
                                  <?php $this->menusep() ?>
                                  <a href="http://vkontakte.ru/share.php?url=<?= $this->siteurl ?>/<?= $msgid ?>" target="_blank"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/vkontakte_bw.png" border="0" width="16" height="16" title="Поделиться ВКонтакте" style="position: relative; top: 4px;" onmouseover="this.src='<?= STATIC_ROOT ?>/img/YaBBImages/vkontakte.gif'" onmouseout="this.src='<?= STATIC_ROOT ?>/img/YaBBImages/vkontakte_bw.png'"></a>
                                  <a href="http://facebook.com/sharer.php?u=<?= $this->siteurl ?>/<?= $msgid ?>" target="_blank"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/facebook_bw.png" width="16" height="16" border="0" title="Поделиться в Facebook" style="position: relative; top: 4px;" onmouseover="this.src='<?= STATIC_ROOT ?>/img/YaBBImages/facebook.png'" onmouseout="this.src='<?= STATIC_ROOT ?>/img/YaBBImages/facebook_bw.png'"></a>
                                  <a href="http://twitter.com/?status=<?= $this->siteurl ?>/<?= $msgid ?>" target="_blank"><img src="/YaBBImages/twitter_bw.png" border="0" width="16" height="16" title="Поделиться в Twitter" style="position: relative; top: 4px;" onmouseover="this.src='<?= STATIC_ROOT ?>/img/YaBBImages/twitter.gif'" onmouseout="this.src='<?= STATIC_ROOT ?>/img/YaBBImages/twitter_bw.png'"></a>
                                  <a href="http://livejournal.com/update.bml?event=<?= urlencode($msg['LJMessage']) ?>&amp;subject=<?= urlencode($msg['LJSubject']) ?>" target="_blank"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/livejournal_bw.png" border="0" width="16" height="16" title="Поделиться в Livejournal" style="position: relative; top: 4px;" onmouseover="this.src='<?= STATIC_ROOT ?>/img/YaBBImages/livejournal.png'" onmouseout="this.src='<?= STATIC_ROOT ?>/img/YaBBImages/livejournal_bw.png'"></a>
                                <?php endif; ?>
                              <?php endif; ?>
                            </font>
                          </td>
                        </tr>
                      </table>
                      <hr width="100%" size="1" color="gray">
                      <div id="<?= $msgid ?>" class="message-content">
                        <font size="2">
                          <?php if($this->conf->enable_ubbc): ?>
                            <?php if($msg['smiliesEnabled']): ?>
                              <?= $this->doUBBC($msg['body']) ?>
                            <?php else: ?>
                              <?= $this->doUBBC($msg['body'], 'links,inline,blocks') ?>
                            <?php endif; ?>
                          <?php else: ?>
                            <?php $this->esc($msg['body']) ?>
                          <?php endif; ?>
                          
                          <?php if($this->conf->nowlistening_enabled && $msg['nowListening'] != ''): ?>
                            <br><br>
                            <?= $this->doUBBC($this->conf->nowlistening_displaytext) ?>
                            <?php if($this->conf->nowlistening_ubbc): ?>
                              <?= $this->doUBBC($this->app->subs->CensorTxt($msg['nowListening'])) ?>
                            <?php else: ?>
                              <?php $this->esc($this->app->subs->CensorTxt($msg['nowListening'])) ?>
                            <?php endif; ?>
                          <?php endif; ?>
                        </font>
                      </div> <!-- .message-content -->
                    </td>
                  </tr>
                  <tr>
                    <td class="<?= $msg['css'] ?>" bgcolor="<?= $msg['windowbg'] ?>" valign="bottom">
                      <table width="100%" border="0">
                        <tr class="post_footer">
                          <td class="attaches" align="left">
                            <font size="1">
                              <?php if($msg['attachmentSize'] > 0 && $this->conf->attachmentEnable): ?>
                                <a href="<?= $this->conf->attachmentUrl ?>/<?= urlencode($msg['attachmentFilename']) ?>" target="_blank"><img src="<?= $this->conf->imagesdir ?>/clip.gif" align="middle" border="0">&nbsp;<font size="1"><?= $this->esc($msg['attachmentFilename']) ?></font></a>
                                <br>
                              <?php endif; ?>
                              
                              <?php if(isset($msg['lastmodified'])): ?>
                                <i><?= $this->locale->txt[211] ?>: <?= $this->esc($msg['modifiedName']) ?> <?= $this->app->subs->timeformat($msg['modifiedTime']) ?></i>
                              <?php endif; ?>
                            </font>
                          </td>
                          <td class="post_actions" align="right">
                            <?php if($this->isBlog && isset($msg['blog'])): ?>
                              
                              <?php if($msg['blog']['numComments'] == 0): ?>
                                <?= $this->locale->blogmod16 ?>
                                <?php if($this->conf->blogmod_guestcomment || $this->user->id > 0): ?>
                                  | <a href="javascript:blogWin('<?= SITE_ROOT ?>/blog_comments/<?= $msgid ?>/#post')"><?= $this->locale->blogmod8 ?></a>
                                <?php endif; ?>
                                <br>
                              <?php else: ?>
                                <a href="javascript:blogWin('<?= SITE_ROOT ?>/blog_comments/<?= $msgid ?>/')"><?= $msg['blog']['numComments'] ?>
                                  <?php if($msg['blog']['numComments'] == 1): ?>
                                    <?= $this->locale->blogmod10 ?>
                                  <?php else: ?>
                                    <? $this->locale->blogmod9 ?>
                                  <?php endif; ?>
                                </a>
                                <?php if($msg['blog']['logTime'] < $msg['blog']['lastPosterTime'] && $this->user->name != 'Guest'): ?>
                                  <?= $this->locale->img['new'] ?>
                                <?php endif; ?>
                              
                                <?php if($this->conf->blogmod_guestcomment || $this->user->id > 0): ?>
                                  | <a href="javascript:blogWin('<?= SITE_ROOT ?>/blog_comments/<?= $msgid ?>/#post')"><?= $this->locale->blogmod8 ?></a>
                                <?php endif; ?>
                              
                                <?php if($this->conf->blogmod_lastposter): ?>
                                  <br>
                                  <?= $this->locale->blogmod27 ?>
                                  <?php if($msg['blog']['ID_MEMBER_LAST_COMMENT'] > 0): ?>
                                    <a href="<?= SITE_ROOT ?>/people/<?= urlencode($msg['blog']['posterName']) ?>/"><?= $this->esc($msg['blog']['displayName']) ?></a>
                                  <?php else: ?>
                                    <?= $msg['blog']['displayName'] ?> (<?= $this->locale->txt[28] ?>)
                                  <?php endif; ?>
                                <?php endif; ?>
                              <?php endif; ?>
                            <?php endif; /* blog comments */ ?>
                            
                            <font size="1">
                            <?php if(isset($msg['karma'])): ?>
                              <table id="Karma<?= $msgid ?>">
                                <?php if($msg['karma']['actions']): ?>
                                  <tr id="KarmaBtns<?= $msgid ?>">
                                    <td align="right">
                                      <a href="<?= SITE_ROOT ?>/karma/applaud/<?= $msgid ?>/" onclick="Forum.Data.rateTheMessage(<?= $msgid ?>, 'applaud');return false;" class="button"><?= $this->locale->applause ?></a>
                                    </td>
                                    <?php if($msg['karma']['smite_not_allowed']): ?>
                                      <td></td><td></td></tr>
                                    <?php else: ?>
                                      <td align="center"><?= $this->menusep ?></td>
                                      <td align="left">
                                        <a href="<?= SITE_ROOT ?>/karma/smite/<?= $msgid ?>/" onclick="Forum.Data.rateTheMessage(<?= $msgid ?>, 'smite');return false;" class="button"><?= $this->locale->smite ?></a>
                                      </td>
                                    <?php endif; /* smite allowed */?>
                                  </tr>
                                <?php endif; /* karma actions */?>
                                
                                <tr id="karmaInfo<?= $msgid ?>" style="display:<?= $msg['karma']['css'] ?>">
                                  <td align="right"><?= $this->locale->applauses ?>:&nbsp;<span id="Applauds<?= $msgid ?>"><?= $msg['karma']['karmaGood'] ?></span></td>
                                  <td><?= $this->menusep ?></td>
                                  <td align="left"><?= $this->locale->smites ?>:&nbsp;<span id="Smites<?= $msgid ?>"><?= $msg['karma']['karmaBad'] ?></span></td>
                                </tr>
                              </table> <!-- #Karma -->
                            <?php endif; /* karma */ ?>
                            
                            <?php if(strlen($msg['multinick']) > 0 and $this->user->accessLevel() > 2): ?>
                              <div id="multinick<?= $msgid ?>" style="display: none; width: 400px; background-color: <?= $this->conf->color['windowbg3'] ?>; border: 1px solid black; padding: 20px;">
                                <?= $msg['multinick'] ?>
                                <br><br>
                                <a href="javascript:document.getElementById('multinick<?= $msgid ?>').style.display='none'; void(0);">закрыть</a>
                                <?php if($this->user->group == "Administrator"): ?>
                                  <br>
                                  <span class="agent-agent"><?= $this->esc($msg['agent'][0]) ?></span>
                                <?php endif; ?>
                                <div>
                                  <?php if(!empty($msg['agent'][1])): ?>
                                    <span class="agent-fingerprint" title="<?= $this->locale->fpsrch ?>"><?= $this->esc($msg['agent'][1]) ?></span>
                                  <?php endif; ?>
                                  <?php if(!empty($msg['agent'][2])): ?>
                                    - <span class="agent-fingerprint" title="<?= $this->locale->fpsrch ?>"><?= $this->esc($msg['agent'][2]) ?></span>
                                  <?php endif; ?>
                                </div>
                              </div> <!-- #multinick -->
                              <a href="javascript:document.getElementById('multinick<?= $msgid ?>').style.display='block'; void(0);">выявленные ники</a> <?= $this->menusep ?>
                            <?php endif; ?>
                            
                            <?php if($this->conf->enableReportToMod && $this->user->name != 'Guest' && $msg['ID_MEMBER'] != $this->user->id): ?>
                              <font size="1">
                                <a class="reportToModerator" href="<?= SITE_ROOT ?>/report/<?= $msgid ?>/" target="_blank"><?= $this->locale->rtm1 ?></a>
                              </font>&nbsp;&nbsp;
                            <?php endif; ?>
                            
                            <?php if($msg['guest'] && $this->user->accessLevel() > 2): ?>
                              | <a href="javascript:DoConfirm('Забанить этот IP на 7 дней?','<?= SITE_ROOT ?>/ban/?ip=<?= $msg['posterIP'] ?>&amp;timetoban=7&amp;reason=автоматический%20бан.%20С%20вопросами%20обращайтесь%20к%20забанившему%20модератору%20или%20пишите%20на%20dig7er@gmail.com&amp;sesc=<?= $this->app->session->id ?>');">забанить по IP</a>
                            <?php endif; ?>
                            
                            <?php if($msg['posterIP'] == $this->locale->txt[511]): ?>
                              <img src="<?= $this->conf->imagesdir ?>/ip.gif" alt="" border="0" class="user-authenticated" > <span class="user-authenticated"><?= $msg['posterIP'] ?></span>
                            <?php else: ?>
                              <a href="http://ipinfo.io/<?= $msg['posterIP'] ?>" target="_blank" class="message-user-ip"><img src="<?= $this->conf->imagesdir ?>/ip.gif" alt="" border="0"><?= $msg['posterIP'] ?></a>
                            <?php endif; ?>
                          </font>
                        </td>
                      </tr>
                    </table>
                    
                    <?php if(isset($msg['quickpoll'])): ?>
                      <hr width="100%" size="1" />
                      <h4 class="quick-poll-title"><?= $this->esc($msg['quickpoll']['title']) ?></h4>
                      <table width="100%" border="0" class="quick-poll-block">
                        <tr>
                          <th width="33%" align="center" class="quote qpollbtn" onclick="document.location.href='<?= SITE_ROOT ?>/quickpollvote/yes/<?= $msgid ?>/';">да</th>
                          <th width="33%" align="center" class="quote qpollbtn" onclick="document.location.href='<?= SITE_ROOT ?>/quickpollvote/no/<?= $msgid ?>/';">нет</th>
                          <th align="center" class="quote qpollbtn" onclick="document.location.href='<?= SITE_ROOT ?>/quickpollvote/neutral/<?= $msgid ?>/';">не знаю</th>
                        </tr>
                        <tr>
                          <?php foreach($msg['quickpoll']['voters'] as $voters): ?>
                            <td align="center" valign="top">
                              <?php foreach($voters as $voter): ?>
                                <?php if(is_array($voter)): ?>
                                  <a href="<?= SITE_ROOT ?>/people/<?= urlencode($voter[0]) ?>/"><b><?= $this->esc($voter[1]) ?></b></a>
                                <?php else: ?>
                                  <b><?= $this->esc($voter) ?></b>
                                <?php endif; ?>
                                <br>
                              <?php endforeach; ?>
                            </td>
                          <?php endforeach; ?>
                        </tr>
                      </table>
                    <?php endif; /* quick poll */ ?>
                    
                    <?php if($this->conf->profilebutton): ?>
                      <?php /* if($msg['ID_MEMBER'] != '-1'): ?>
                        <a href="<?= SITE_ROOT ?>/people/<?= urlencode($msg['posterName']) ?>"><?= $this->locale->viewprofile_sm ?></a>
                      <?php endif; */ ?>
                    
                    <?php if(strlen($msg['userset']['websiteUrl'])): ?>
                      <?= $msg['userset']['websiteUrl'] ?>
                    <?php endif; ?>

                    <?php if($msg['userset']['hideEmail'] != '1' || $this->user->group == 'Administrator' || $this->conf->allow_hide_email != '1'): ?>
                      <a href="mailto:<?= $this->esc($msg['posterEmail']) ?>"><?= $this->locale->email_sm ?></a>
                    <?php endif; ?>

                    <?php if ($this->user->name != 'Guest'): ?>
                      <?php if($msg['isOnline']): ?>
                        <a href="<?= SITE_ROOT ?>/im/new/?to=<?= urlencode($msg['posterName']) ?>"><?= $this->locale->message_sm_on ?></a>
                      <?php else: ?>
                        <a href="<?= SITE_ROOT ?>/im/new/?to=<?= urlencode($msg['posterName']) ?>"><?= $this->locale->message_sm_on ?></a>
                      <?php endif; ?>
                    <?php endif; ?>
                  <?php endif; /* profilebutton */ ?>
                  
                  <?php if($msg['attachment']): ?>
                    <hr color=gray size="1">
                    <?php if($msg['attachmentType'] == 'image'): ?>
                      <?php if($this->conf->maxwidth && $this->conf->maxheight): ?>
                        <a href="<?= $this->conf->attachmentUrl ?>/<?= $msg['attachmentFilename'] ?>" target="_blank" class="msg-attachment"><img src="<?= $this->conf->attachmentUrl ?>/<?= $msg['attachmentFilename'] ?>" title="Увеличить" style="max-width:<?= $this->conf->maxwidth ?>px; max-height:<?= $this->conf->maxheight ?>px;"></a>
                      <?php else: ?>
                        <a href="<?= $this->conf->attachmentUrl ?>/<?= $msg['attachmentFilename'] ?>" target="_blank" class="msg-attachment"><img src="<?= $this->conf->attachmentUrl ?>/<?= $msg['attachmentFilename'] ?>" title="Увеличить"></a>
                      <?php endif; ?>
                    <?php elseif($msg['attachmentType'] == 'audio'): ?>
                      <div class="audio-player">
                        <audio class="mejs" controls preload="none">
                          <source src="<?= $this->conf->attachmentUrl ?>/<?= $msg['attachmentFilename'] ?>">
                        </audio>
                      </div>
                    <?php elseif($msg['attachmentType'] == 'video'): ?>
                      <div class="video-player">
                        <video class="mejs" controls preload="metadata">
                          <source src="<?= $this->conf->attachmentUrl ?>/<?= $msg['attachmentFilename'] ?>">
                        </video>
                      </div>
                    <?php endif; ?>
                  <?php endif; ?>
                  
                  <hr width="100%" size="1" />
                  <?php  $this->_partial('comments/comments.template.php', array('msgid' => $msgid, 'msg' => $msg));  ?>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
    
    <?php endforeach; /* display messages */ ?>
    </table>
    
    <table id="lastPost" border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor">
      <tr>
        <td>
          <table width="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="' . $color['bordercolor'] . '" class="bordercolor">
            <tr>
              <td align="left" class="catbg" bgcolor="<?= $this->conf->color['catbg'] ?>" width="100%" height="30">
                <table cellpadding="3" cellspacing="0" width="100%">
                  <tr>
                    <td id="bot" class="board_pageindex">
                      <font size="1"><b><?= $this->locale->txt[139] ?>:</b> <?= $this->pageindex ?></font>
                      <?php if($this->conf->topbottomEnable): ?>
                        <?= $this->menusep ?><a href="#top"><?= $this->locale->tbtop ?></a>
                      <?php endif; ?>
                    </td>
                    <td class="board_menu" align="right">
                      <font size="-1"><?php $this->get_cache('topicButtons'); ?>&nbsp;</font>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    
    <table width="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor showrecents" valign="top">
      <tr>
        <td height="20" colspan="2" class="windowbg">
          <div style="float: left">
            <font size="1">
              Показать <input type="text" size="2" maxlength="2" id="numLastComments" value="10"> последних комментариев к сообщениям в теме <a href="javascript: recentTopicComments('<?= $this->board ?>', '<?= $this->thread ?>', '<?= $this->start ?>', '<?= $this->app->session->id ?>'); void(0);"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/reload.png" width="18" height="18" style="position: relative; top: 5px;" border="0" alt="загрузить" title="загрузить"></a>
            </font>
          </div>
          <?php if($this->user->name != "Guest"): ?>
            <div style="float: right">
              <font size="1">
                Показать комментарии с моим участием к <input type="text" size="2" maxlength="2" id="numMsgsMyComments" value="3"> сообщениям в теме <a href="javascript: myRecentTopicComments('<?= $this->board ?>', '<?=$this->thread ?>', '<?= $this->start ?>', '<?= $this->app->session->id ?>'); void(0);"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/reload.png" width="18" height="18" style="position: relative; top: 5px;" border="0" alt="загрузить" title="загрузить"></a>
              </font>
            </div>
          <?php endif; ?>
        </td>
      </tr>
    </table>
    <div id="recentcommentstable"></div>
    
    <?php if($this->quickReplyForm): ?>
        <?php $this->partial('thread/quick_reply_form.template.php'); ?>
    <?php endif; ?>
    
    <table border="0" width="100%" cellpadding="0" cellspacing="0">
      <tr width="100%" align="right">
        <td width="100%" align="right">
          <font size="1" class="nav">
            <?= $this->get_cache('previousNext') ?>
          </font>
        </td>
      </tr>
      <?php if($this->conf->enableInlineLinks): ?>
        <tr>
          <td class="board_tree" valign="top" align="left">
            <!-- BottomLinkTree -->
            <?php $this->get_cache('linktree'); ?>
            <div class="go-form">
              <?php if(isset($this->jumptoform)): ?>
                <?php $this->partial('board/jumpto.template.php') ?>
              <?php endif; ?>
            </div>
            
          </td>
        </tr>
      <?php endif; ?>
      
      <tr>
        <td valign="top" align="left" class="moderation-buttons-bar">
          <font size="2">
            <?php $this->menu_begin() ?>
            <?php if($this->user->accessLevel() > 1): ?>
              <?= $this->menusep() ?>
              <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $this->thread ?>/move/"><font size="1" class="imgwindowbg"><?= $this->locale->movethread ?></font></a>
              <?= $this->menusep() ?>
              <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $this->thread ?>/delete/"><font size="1" class="imgwindowbg"><?= $this->locale->removethread ?></font></a>
            <?php endif; ?>
            
            <?php if($this->allow_locking): ?>
              <?= $this->menusep() ?>
              <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $this->thread ?>/lock/?sesc=<?= $this->app->session->id ?>"><font size="1" class="imgwindowbg"><?= $this->locale->img[$this->img_locked_thread] ?></font></a>
            <?php endif; ?>
            
            <?php if($this->conf->enableStickyTopics && $this->user->accessLevel() > 1): ?>
              <?= $this->menusep() ?>
              <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $this->thread ?>/sticky/<?= $this->topicinfo['isSticky'] ?>/?sesc=<?= $this->app->session->id ?>"><font size="1" class="imgwindowbg"><?= $this->locale->img["sticky{$this->topicinfo['isSticky']}"] ?></font></a>
            <?php endif; ?>
            
            <?php if ($this->user->accessLevel() > 1): ?>
              <?= $this->menusep() ?>
              <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $this->thread ?>/merge/"><font size="1" class="imgwindowbg"><?= $this->locale->merge ?></font></a>
            <?php endif; ?>
            
            <?php if($this->calendar_enabled): ?>
              <?= $this->menusep() ?>
              <a href="<?= SITE_ROOT ?>/calendar/linkevent/b<?= $this->board ?>/t<?= $this->thread ?>/"><font size="1" class="imgwindowbg"><?= $this->locale->linkToCalendar ?></font></a>
            <?php endif; ?>
          </font>
        </td>
      </tr>
    </table>    
