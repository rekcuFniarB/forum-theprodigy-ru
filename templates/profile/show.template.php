  <table id="profileTable" border="0" cellpadding="0" cellspacing="1" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor" align="center" style="margin: 0 auto; width: <?= $this->width ?>;" role="presentation">
    <tr>
      <td class="titlebg" bgcolor="<?= $this->color['titlebg'] ?>" width="50%">
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
          <tr>
            <td height="30">
              <img src="<?= $this->imagesdir ?>/profile_sm.gif" alt="" border="0">
              <font size="2" class="text1" color="<?= $this->color['titletext'] ?>">
                <b><?= $this->locale->txt[35] ?>: <?= $this->esc($this->meminf['memberName']) ?></b>
              </font>
              <?php if($this->follow_btn): ?>
                  <?php if($this->meminf['following']): ?>
                      <a href="javascript:followRequest(<?= $this->meminf['ID_MEMBER'] ?>);"><img id="followButton" src="<?= STATIC_ROOT ?>/img/YaBBImages/unfollow.png" border="0" width="20" height="20" title="Перестать следить за сообщениями и комментариями <?= $this->esc($this->meminf['realName']) ?>" alt="Перестать следить за <?= $this->esc($this->meminf['realName']) ?>" style="vertical-align: middle; margin: 3px 3px 3px 5px"/></a>
                  <?php else: ?>
                      <a href="javascript:followRequest(<?= $this->meminf['ID_MEMBER'] ?>);"><img id="followButton" src="<?= STATIC_ROOT ?>/img/YaBBImages/follow.png" border="0" width="20" height="20" title="Следить за сообщениями и комментариями <?= $this->esc($this->meminf['realName']) ?>" alt="Следить за <?= $this->esc($this->meminf['realName']) ?>" style="vertical-align: middle; margin: 3px 3px 3px 5px"/></a>
                  <?php endif; ?>
              <?php endif; /* follow_btn */?>
              
              <?php if($this->activate_btn): ?>
                  <button id="activate_button" name="uid" value="<?= $this->meminf['ID_MEMBER'] ?>">Активировать</button>
              <?php endif; ?>
            </td>
            <td align="center">
              <font size="2" class="text1" color="<?= $this->color['titletext'] ?>">
                <a href="javascript:showLastMsgsNCmnts('<?= $this->esc($this->meminf['memberName']) ?>');void(0);" class="orange-arrow-right" style="display: <?= $this->hideLastMsgsNCmnts?'inline-block':'none' ?>;"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/orange_arrow_right.png" id="showMsgsNCmntsBtn" border="0" alt="Последние сообщения и комментарии" title="Показать последние сообщения и комментарии"></a>
                <?php if($this->modify_btn): ?>
                    <a href="./modify/"><font size="2" class="text1" color="<?= $this->color['titletext'] ?>"><?= $this->locale->txt[17] ?></font></a>
                <?php endif; ?>
                
                <?php if($this->self): ?>
                    <a href="<?= SITE_ROOT ?>/sessionconf/" class="session-settings-btn" title="Настройка просмотра"></a>
                <?php endif; ?>
              </font>
            </td>
          </tr>
        </table>
      </td>
      
      <td class="titlebg" bgcolor="<?= $this->color['titlebg'] ?>" width="50%" valign="middle" id="followersColTitle" style="padding: 3px;<?= $this->hideLastMsgsNCmnts? 'display:none;':''?>">
        <div style="margin: 5px; float: left;">
          <b>Последние <input type="checkbox" checked="checked" style="position: relative; top: 2px;" onclick="toggleLatestMessages(this)"> сообщения и <input type="checkbox" checked="checked" style="position: relative; top: 2px;" onclick="toggleLatestComments(this)">комментарии</b>
        </div>
        <div style="margin-right: 5px; float: right" align="center">
          <a href="javascript:hideLastMsgsNCmnts();void(0);"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/orange_arrow_left.png" alt="Cкрыть" title="Cкрыть" border="0"></a>
        </div>
      </td>
    </tr>
    <tr>
      <td bgcolor="<?= $this->color['windowbg'] ?>" class="windowbg userinfo" valign="top">
        <table border="0" cellspacing="0" cellpadding="5" width="100%">
          <tr>
            <td>
              <font size="2"><b><?= $this->locale->txt[68] ?>: </b></font>
            </td>
            <td>
              <font size="2"><?= $this->esc($this->meminf['realName']) ?></font>
            </td>
          </tr>
          <?php if(!empty($this->meminf['usertitle'])): ?>
              <tr>
                <td>
                  <font size="2"><b><?= $this->locale->title1 ?>: </b></font>
                </td>
                <td>
                  <font size="2"><?= $this->esc($this->meminf['usertitle']) ?></font>
                </td>
              </tr>
          <?php endif; ?>
          <tr>
            <td>
              <font size="2"><b><?= $this->locale->txt[87] ?>: </b></font>
            </td>
            <td>
              <font size="2"><?= $this->esc($this->meminf['memberinfo']) ?></font>
            </td>
          </tr>
          <!-- Avatar -->
          <tr>
            <td>
              <font size="2"><b><?= $this->locale->txt[232] ?>: </b></font>
            </td>
            <td>
              <?php if($this->self_avatar): ?>
                  <img src="<?= $this->esc($this->meminf['avatar']) ?>" <?= $this->avatar_width ?> <?= $this->avatar_height ?>  border="0" alt="avatar">
                  <br><br>
              <?php else: ?>
                  <img src="<?= $this->conf->faceurl ?>/<?= urlencode($this->meminf['avatar']) ?>" border="0" alt="avatar">
                  <br><br>
              <?php endif; ?>
              <font size="2"><?= $this->esc($this->meminf['personalText']) ?></font>
            </td>
          </tr>
          
          <!-- Signature -->
          <?php if(!empty($this->meminf['signature'])): ?>
              <tr>
                <td colspan="2">
                  <hr width="100%" size="1">
                  <font size="2"><b><?= $this->locale->txt[85] ?>:</b></font>
                  <br>
                  <?= $this->doUBBC($this->meminf['signature']) ?>
                  <hr width="100%" size="1">
                </td>
              </tr>
          <?php endif; ?>
          
          <!-- Posts count -->
          <tr>
            <td>
              <font size="2"><b><?= $this->locale->txt[86] ?>: </b></font>
            </td>
            <td>
              <font size="2"><?= $this->meminf['posts'] ?></font>
            </td>
          </tr>
          
          <?php if(!empty($this->meminf['memberIP'])): ?>
              <!-- Member IP -->
              <tr>
                <td width="45%">
                  <font size="2"><b><?= $this->locale->txt[512] ?>: </b></font>
                </td>
                <td>
                  <font size="2"><a href="http://ipinfo.io/<?= urlencode($this->meminf['memberIP']) ?>" target="_blank"><?= $this->esc($this->meminf['memberIP']) ?></a></font>
                </td>
              </tr>
          <?php endif; ?>
          
          <?php if($this->meminf['karma']): ?>
              <!-- Karma -->
              <tr>
                <td>
                  <font size="2"><b><?= $this->conf->karmaLabel ?></b></font>
                </td>
                <td>
                  <font size="2">
                    <a href="<?= SITE_ROOT ?>/people/<?= urlencode($this->meminf['memberName']) ?>/karma/"><?= $this->meminf['karma'] ?></a>
                  </font>
                </td>
              </tr>
          <?php endif; ?>
          
          <!-- Comments -->
          <tr>
            <td>
              <font size="2"><b>Комментарии: </b></font>
            </td>
            <td>
              <font size="2">
                <ul>
                  <?php if($this->self): /* viewing self profile */?>
                      <li><a href="<?= SITE_ROOT ?>/comments/subscribed/">к подписанным сообщениям</a></li>
                  <?php endif; ?>
                  <li><a href="<?= SITE_ROOT ?>/people/<?= urlencode($this->meminf['memberName']) ?>/comments/subscribed/">к сообщениям <?= $this->esc($this->meminf['realName']) ?></a></li>
                  <li><a href="<?= SITE_ROOT ?>/people/<?= urlencode($this->meminf['memberName']) ?>/comments/">с участием <?= $this->esc($this->meminf['realName']) ?></a></li>
                </ul>
              </font>
            </td>
          </tr>
          
          <!-- Date registered -->
          <tr>
            <td>
              <font size="2"><b><?= $this->locale->txt[233] ?>: </b></font>
            </td>
            <td>
              <font size="2"><?= $this->meminf['dateReg'] ?></font>
            </td>
          </tr>
          
          <tr>
            <td colspan="2">
              <hr size="1" width="100%" class="windowbg3">
            </td>
          </tr>
          
          <?php if(!empty($this->meminf['ICQ'])): ?>
              <tr>
                <td>
                  <font size="2"><b><?= $this->locale->txt[513] ?>:</b></font>
                </td>
                <td>
                  <font size="2"><?= $this->esc($this->meminf['ICQ']) ?></font>
                </td>
              </tr>
          <?php endif; ?>
          
          <?php if(!empty($this->meminf['AIM'])): ?>
              <!-- Skype -->
              <tr>
                <td>
                  <font size="2"><b><?= $this->locale->txt[603] ?>: </b></font>
                </td>
                <td>
                  <font size="2"><a href="skype:<?= urlencode($this->meminf['AIM']) ?>?chat&amp;topic=Forum.theProdigy.ru"><?= $this->esc($this->meminf['AIM']) ?></a></font>
                </td>
              </tr>
          <?php endif; ?>
          
          <?php if(!empty($this->meminf['MSN'])): ?>
              <tr>
                <td>
                  <font size="2"><b>MSN: </b></font>
                </td>
                <td>
                  <font size="2">
                    <a href="http://members.msn.com/<?= urlencode($this->meminf['MSN']) ?>" target="blank" rel="nofollow noopener"><?= $this->esc($this->meminf['MSN']) ?></a>
                  </font>
                </td>
              </tr>
          <?php endif; ?>
          
          <?php if(!empty($this->meminf['YIM'])): ?>
              <!-- Blog -->
              <tr>
                <td>
                  <font size="2"><b><?= $this->locale->txt[604] ?>: </b></font>
                </td>
                <td>
                  <font size="2">
                    <a href="<?= $this->esc($this->meminf['YIM']) ?>" target="_blank" rel="nofollow noopener"><?= $this->esc($this->meminf['YIM']) ?></a>
                  </font>
                </td>
              </tr>
          <?php endif; ?>
          
          <?php if(!empty($this->meminf['emailAddress'])): ?>
              <!-- Email -->
              <tr>
                <td>
                  <font size="2"><b><?= $this->locale->txt[69] ?>: </b></font>
                </td>
                <td>
                  <font size="2">
                    <a href="mailto:<?= $this->esc($this->meminf['emailAddress']) ?>"><?= $this->esc($this->meminf['emailAddress']) ?></a>
                  </font>
                </td>
              </tr>
          <?php endif; ?>
          
          <?php if(!empty($this->meminf['websiteUrl'])): ?>
              <!-- Web site URL -->
              <tr>
                <td>
                  <font size="2"><b><?= $this->locale->txt[96] ?>: </b></font>
                </td>
                <td>
                  <font size="2">
                    <a href="<?= $this->esc($this->meminf['websiteUrl']) ?>" target="_blank" rel="nofollow noopener"><?= $this->esc($this->meminf['websiteTitle']) ?></a>
                  </font>
                </td>
              </tr>
          <?php endif; ?>
          
          <tr>
            <td colspan="2">
              <hr size="1" width="100%" class="windowbg3">
            </td>
          </tr>
          
          <?php if(!empty($this->meminf['gender'])): ?>
              <tr>
                <td>
                  <font size="2"><b><?= $this->locale->txt[231] ?>: </b></font>
                </td>
                <td>
                  <font size="2"><?= $this->meminf['gender'] ?></font>
                </td>
              </tr>
          <?php endif; ?>
          
          <!-- Age -->
          <tr>
            <td>
              <font size="2"><b><?= $this->locale->txt[420] ?>:</b></font>
            </td>
            <td>
              <font size="2"><?= $this->esc($this->meminf['age']) ?></font>
              <?php if($this->meminf['isbday']): ?>
                  <img src="<?= $this->imagesdir?>/bdaycake.gif" width="40" alt="Birthday">
              <?php endif; ?>
            </td>
          </tr>
          
          <?php if(!empty($this->meminf['location'])): ?>
              <!-- Locatioin -->
              <tr>
                <td>
                  <font size="2"><b><?= $this->locale->txt[227] ?>: </b></font>
                </td>
                <td>
                  <font size="2"><?= $this->esc($this->meminf['location']) ?></font>
                </td>
              </tr>
          <?php endif; ?>
          
          <?php if(!empty($this->meminf['usrlng'])): ?>
              <tr>
                <td>
                  <font size="2"><b><?= $this->locale->yse225 ?>:</b></font>
                </td>
                <td>
                  <font size="2"><?= $this->esc($this->meminf['usrlng']) ?></font>
                </td>
              </tr>
          <?php endif; ?>
          
          <?php if($this->staff): ?>
            <!-- enhanced ban mod -->
              <tr>
                <td colspan="2"><hr size="1" width="100%" class="windowbg3"></td>
              </tr>
              <tr>
                <td colspan="2" class="windowbg2" bgcolor="<?= $this->color['windowbg2'] ?>">
                  <?php if($this->meminf['banned']): ?>
                      <form action="<?= SITE_ROOT ?>/security/unban/<?= $this->meminf['ID_MEMBER'] ?>/" name="banform" method="POST" onsubmit="if(!confirm('<?= $this->locale->enhancedban24 ?>')) return false;">
                  <?php else: ?>
                      <form action="<?= SITE_ROOT ?>/security/ban/<?= $this->meminf['ID_MEMBER'] ?>/" name="banform" method="POST">
                  <?php endif; ?>
                  <input type="hidden" name="sc" value="<?= $this->sessionid ?>">
                  
                  <script language="JavaScript1.2" type="text/javascript"><!--
                    function Set_Enable (oControl,oObject)
                    {
                        if (document.all[oControl].checked)
                        {
                            document.all[oObject].value = '';
                        }
                        else
                        {
                            document.all[oObject].focus();
                        }
                    }
                    
                    function UnCheck (oObject)
                    {
                        document.all[oObject].checked = false;
                    }
                    //-->
                  </script>
                  
                  <?php if(!$this->meminf['banned']): ?>
                      <div class="profile-ban-buttons">
                        <input type="button" onclick="Moderation.toggleProfileBanDialog(event)" value="<?= $this->locale->enhancedban5 ?>">
                      </div>
                      <table class="profile-ban-dialog">
                  <?php else: ?>
                      <table>
                  <?php endif; ?>
                  
                        <tr>
                          <td class="windowbg2" bgcolor="<?= $this->color['windowbg2'] ?>" align="left">
                            <font size="2"><b><?= $this->locale->enhancedban22 ?>:</b>
                            <br>
                            <?php if($this->meminf['banned']): ?>
                                    <?= $this->locale->enhancedban16 ?> <?= $this->locale->enhancedban18 ?> <?= $this->meminf['banneduntil'] ?>
                                  </td>
                                </tr>
                                <tr>
                                <td class="windowbg2" bgcolor="<?= $this->color['windowbg2'] ?>" align="left" width="100%">
                                  <br>
                                  <b><?= $this->locale->enhancedban13 ?>:</b>
                                  <br>
                                  <?= $this->esc($this->meminf['banreason']) ?>
                                </td>
                              </tr>
                              <tr>
                              <td bgcolor="<?= $this->color['windowbg2'] ?>" class="windowbg2" align="center">
                                <br>
                                  <input type="submit" value="<?= $this->locale->enhancedban19 ?>">
                            <?php else: /* not banned */?>
                                    <?= $this->locale->enhancedban16 ?> <?= $this->locale->enhancedban17 ?> <?= $this->locale->enhancedban18 ?>
                                  </font>
                                </td>
                              </tr>
                              <tr>
                                <td class="windowbg2" bgcolor="<?= $this->color['windowbg2'] ?>" align="left">
                                  <font size="2">
                                    <?= $this->locale->enhancedban5 ?> <?= $this->locale->enhancedban7 ?>:&nbsp;<input name="timecheck" id="timecheck" class="windowbg2" type="checkbox" style="background-color:<?= $this->color['windowbg2'] ?>" onclick="Set_Enable('timecheck','timetoban')" checked>
                                  </font>
                                </td>
                              </tr>
                              <tr>
                                <td class="windowbg2" bgcolor="<?= $this->color['windowbg2'] ?>" align="left">
                                  <font size="2">
                                    <?= $this->locale->enhancedban8 ?>&nbsp;<input name="timetoban" type="text" id="timetoban" value="" size="20" onfocus="UnCheck('timecheck')">
                                  </font>
                                </td>
                              </tr>
                              <tr>
                                <td class="windowbg2" bgcolor="<?= $this->color['windowbg2'] ?>" align="left">
                                  <font size="2">
                                    <?= $this->locale->enhancedban11 ?>
                                    <br>
                                    <textarea name="reason" id="reason" cols="60" rows="4"></textarea>
                                  </font>
                                </td>
                              </tr>
                              <tr>
                                <td bgcolor="<?= $this->color['windowbg2'] ?>" class="windowbg2" align="center">
                                  <br>
                                  <input type="submit" value="<?= $this->locale->enhancedban5 ?>">
                                  <input type="button" value="<?= $this->locale->txt[752] ?>" onclick="Moderation.toggleProfileBanDialog(event)">
                            <?php endif; /* banned / not banned */?>
                            </td>
                        </tr>
                      </table>
                    </form>
                </td>
              </tr>
          <?php else: /* not staff */?>
              <?php if($this->meminf['banned']): ?>
                  <!-- Ban reason message -->
                  <tr>
                    <td colspan="2">
                      <hr size="1" width="100%" class="windowbg3">
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2" class="windowbg" bgcolor="<?= $this->color['windowbg2'] ?>">
                      <?= $this->locale->enhancedban16 ?>  <?= $this->locale->enhancedban18 ?> <?= $this->meminf['banneduntil'] ?>
                      <br>
                      <b><?= $this->locale->enhancedban13 ?></b>: <?= $this->esc($this->meminf['banreason']) ?>
                    </td>
                  </tr>
              <?php endif; /* banned */?>
              <!-- /enhanced ban mod -->
          <?php endif; ?>
          
          </table>
        </td>
        
        <?php if($this->enableFollowingMod): ?>
            <td rowspan="3" bgcolor="<?= $this->color['windowbg'] ?>" class="windowbg" valign="top" id="followersCol"<?= $this->hideLastMsgsNCmnts ?' style="display: none;"':'' ?>>
              <?php if(!$this->hideLastMsgsNCmnts): ?>
                  <script type="text/javascript">
                    showUserLatestMessagesAndComments('<?= $this->meminf['memberName'] ?>');
                  </script>
              <?php endif; ?>
            </td>
        <?php endif; ?>
      </tr>
      
      <tr>
        <td class="titlebg userinfo" bgcolor="<?= $this->color['titlebg'] ?>" height="25">
          <font size="2" class="text1" color="<?= $this->color['titletext'] ?>">
            <b><?= $this->locale->txt[459] ?>:</b>
          </font>
        </td>
      </tr>
      
      <tr>
        <td bgcolor="<?= $this->color['windowbg2'] ?>" class="windowbg2 userinfo" valign="top">
          <form action="' . $cgi . ';action=usersrecentposts;userid=' . $memsettings[20] . ';user=' . $user . '" method="post">
            <font size="2">
              <?= $this->locale->txt['113'] ?>
              <i><a href="<?= SITE_ROOT ?>/im/send/<?= urlencode($this->meminf['memberName']) ?>/"><!--
              <?php if($this->meminf['online'] > 0): ?>
                --><?= $this->locale->online2 ?><!--
              <?php else: ?>
                --><?= $this->locale->online3 ?><!--
              <?php endif; ?>
              --></a></i>
              <br><br>
              <a href="<?= SITE_ROOT ?>/im/send/<?= urlencode($this->meminf['memberName']) ?>/"><?= $this->locale->txt[688] ?></a>.
              <br><br>
              <?= $this->locale->txt[460] ?>
              <select name="viewscount" size="1">
                <option value="5">5</option>
                <option value="10" selected="selected">10</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="200">200</option>
                <option value="500">500</option>
                <option value="1000">1000</option>
                <option value="0"><?= $this->locale->txt[190] ?></option>
              </select>
              <?= $this->locale->txt[461] ?>
              <input type="submit" value="<?= $this->locale->txt[462] ?>">
            </font>
          </form>
        </td>
      </tr>
    </table>
    
    <?php if($this->staff && $this->meminf['passwd'] == 'INACTIVE'): ?>
        <script type="text/javascript">
          $("#activate_button").on("click", function(){
              var uid = $(this).val();
              var url = "<?= SITE_ROOT ?>/people/<?= $this->meminf['memberName'] ?>/activate/";
              $.ajax(url,{
                  type: 'POST',
                  data: {requesttype: "ajax", sc: "<?= $this->sessionid ?>"},
                  timeout: 10000,
                  error: function(){alert("Произошла ошибка, повторите позже.");},
                  success: function(result){
                      switch(result){
                        case 'ERROR':
                          alert('Произошла ошибка.');
                          break;
                        case 'ACCESS_DENIED':
                          alert('Этот комментарий изменять нельзя.');
                          break;
                        case 'OK':
                          $("#activate_button").text("Активирован").attr("disabled", "true");
                      }
                  }
              });
          });
        </script>
    <?php endif; ?>
