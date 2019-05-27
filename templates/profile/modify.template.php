
                        <form action="."
        <?php if($this->self): ?>
                      onsubmit="if (document.creator.oldpasswrd.value == '') { alert('<?= $this->locale->yse244 ?>'); return false; }"
        <?php endif; ?>
                      method="post" name="creator">
                          <table border="0" width="80%" cellspacing="1" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor" align="center">
                            <tr>
                              <td class="titlebg" bgcolor="<?= $this->color['titlebg'] ?>" height="30">
                                <img src="<?= $this->imagesdir ?>/profile_sm.gif" alt="" border="0">
                                <font size="2" class="text1" color="<?= $this->color['titletext'] ?>"><b><?= $this->locale->txt[79] ?></b></font>
                              </td>
                            </tr>
                            <tr>
                              <td class="windowbg" bgcolor="<?= $this->color['windowbg'] ?>" height="25">
                                <br>
                                <font size="1"><?= $this->locale->txt[698] ?></font>
                                <br><br>
                              </td>
                            </tr>
                            <tr>
                              <td class="catbg" bgcolor="<?= $this->color['catbg'] ?>" height="25">
                                <font size="2"><b><?= $this->locale->txt[517] ?></b></font>
                              </td>
                            </tr>
                            <tr>
                              <td class="windowbg" bgcolor="<?= $this->color['windowbg'] ?>">
                                <table border="0" width="100%" cellpadding="3">
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b><?= $this->locale->txt[35] ?>:</b></font>
                                    </td>
                                    <td>
                                      <font size="2">
                                        <input type="hidden" name="userID" value="<?= $this->mem['ID_MEMBER'] ?>">
                                        <input type="hidden" name="user" value="<?= $this->esc($this->mem['memberName']) ?>">
                                        <?= $this->esc($this->mem['memberName']) ?>
                                      </font>
                                    </td>
                                  </tr>
                                  <?php if($this->titlesEnable): ?>
                                      <?php if($this->allowTitle): ?>
                                          <tr>
                                            <td width="45%">
                                              <font size="2"><b><?= $this->locale->title1 ?>:</b></font>
                                            </td>
                                            <td>
                                              <input type="text" name="usertitle" size="30" maxlength="20"  value="<?= $this->esc($this->mem['usertitle']) ?>">
                                            </td>
                                          </tr>
                                      <?php else: ?>
                                          <tr>
                                            <td width="45%">
                                              <font size="2"><b><?= $this->locale->title1 ?>:</b>
                                                <font size="1">(редактировать звание может только Бог Форума)</font>
                                              </font>
                                            </td>
                                            <td>
                                              <?= $this->esc($this->mem['usertitle']) ?>
                                              <input type="hidden" name="usertitle" value="<?= $this->esc($this->mem['usertitle']) ?>">
                                            </td>
                                          </tr>
                                      <?php endif; ?>
                                  <?php endif; ?>
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b><?= $this->locale->txt[68] ?>:</b></font>
                                      <br>
                                      <font size="1"><?= $this->locale->txt[518] ?></font>
                                    </td>
                                    <td>
                                      <input type="text" name="name" maxlength="30" size="30" value="<?= $this->esc($this->mem['realName']) ?>">
                                    </td>
                                  </tr>
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b><?= $this->locale->txt[69] ?>:</b></font>
                                      <br>
                                      <font size="1"><?= $this->locale->txt[679] ?></font>
                                    </td>
                                    <td>
                                      <input type="text" name="email" size="30" value="<?= $this->esc($this->mem['emailAddress']) ?>">
                                    </td>
                                  </tr>
                                  <?php if($this->self): ?>
                                      <tr>
                                        <td width="45%">
                                          <font size="2"><b><?= $this->locale->yse241 ?>:</b></font>
                                          <br>
                                          <font size="1"><?= $this->locale->yse244 ?></font>
                                        </td>
                                        <td>
                                          <input type="password" name="oldpasswrd" size="20" />
                                        </td>
                                      </tr>
                                  <?php endif; /* self */?>
                                </table>
                                <br>
                              </td>
                            </tr>
                            <tr>
                              <td class="catbg" bgcolor="<?= $this->color['catbg'] ?>" height="25">
                                <font size="2"><b><?= $this->locale->txt[597] ?></b></font>
                              </td>
                            </tr>
                            <tr>
                              <td class="windowbg" bgcolor="<?= $this->color['windowbg'] ?>">
                                <table border="0" width="100%" cellpadding="3">
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b><?= $this->locale->txt[231] ?>:</b></font>
                                    </td>
                                    <td>
                                      <select name="gender" size="1">
                                        <option value=""></option>
                                        <option value="Male" <?= $this->GenderMale ?>><?= $this->locale->txt[238] ?></option>
                                        <option value="Female" <?= $this->GenderFemale ?>><?= $this->locale->txt[239] ?></option>
                                      </select>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b><?= $this->locale->txt[563] ?>:</b></font>
                                    </td>
                                    <td>
                                      <font size="1">
                                        <?= $this->locale->txt[564] ?>
                                        <input type="text" name="bday1" size="2" maxlength="2" value="<?= $this->mem['umonth'] ?>">
                                        <?= $this->locale->txt[565] ?>
                                        <input type="text" name="bday2" size="2" maxlength="2" value="<?= $this->mem['uday'] ?>">
                                        <?= $this->locale->txt[566] ?>
                                        <input type="text" name="bday3" size="4" maxlength="4" value="<?= $this->mem['uyear'] ?>">
                                      </font>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b><?= $this->locale->txt[227] ?>: </b></font>
                                    </td>
                                    <td>
                                      <font size="2">
                                        <input type="text" name="location" size="50" value="<?= $this->esc($this->mem['location']) ?>">
                                      </font>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td colspan="2"><hr width="100%" size="1" class="windowbg3" /></td>
                                  </tr>
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b><?= $this->locale->txt[83] ?>:</b></font>
                                      <br>
                                      <font size="1"><?= $this->locale->txt[598] ?></font>
                                    </td>
                                    <td>
                                      <font size="2">
                                        <input type="text" name="websitetitle" size="50" value="<?= $this->esc($this->mem['websiteTitle']) ?>">
                                      </font>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b><?= $this->locale->txt[84] ?>:</b></font>
                                      <br>
                                      <font size="1"><?= $this->locale->txt[599] ?></font>
                                    </td>
                                    <td>
                                      <font size="2">
                                        <input type="text" name="websiteurl" size="50" value="<?= $this->esc($this->mem['websiteUrl']) ?>">
                                      </font>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td colspan="2"><hr width="100%" size="1" class="windowbg3"></td>
                                  </tr>
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b><?= $this->locale->txt[513] ?>:</b></font>
                                      <br>
                                      <font size="1"><?= $this->locale->txt[600] ?></font>
                                    </td>
                                    <td>
                                      <font size="2">
                                        <input type="text" name="icq" size="20" value="<?= $this->esc($this->mem['ICQ']) ?>">
                                      </font>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b><?= $this->locale->txt[603] ?>:</b></font>
                                      <br>
                                      <font size="1"><?= $this->locale->txt[601] ?></font>
                                    </td>
                                    <td>
                                      <font size="2">
                                        <input type="text" name="aim" size="20" value="<?= $this->esc($this->mem['AIM']) ?>">
                                      </font>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b>MSN: </b></font>
                                      <br>
                                      <font size="1"><?= $this->locale->yse237 ?></font>
                                    </td>
                                    <td>
                                      <font size="2">
                                        <input type="text" name="msn" size="20" value="<?= $this->esc($this->mem['MSN']) ?>">
                                      </font>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b><?= $this->locale->txt[604] ?>:</b></font>
                                      <br>
                                      <font size="1"><?= $this->locale->txt[602] ?></font>
                                    </td>
                                    <td>
                                      <font size="2">
                                        <input type="text" name="yim" size="50" value="<?= $this->esc($this->mem['YIM']) ?>">
                                      </font>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td colspan="2"><hr width="100%" size="1" class="windowbg3"></td>
                                  </tr>
                                  
                                  <?php if($this->allowpics): ?>
                                      <tr>
                                        <td width="45%">
                                          <font size="2"><b><?= $this->locale->txt[229] ?>:</b></font>
                                          <br>
                                          <font size="1"><?= $this->locale->txt[474] ?> <?= $this->piclimits ?></font>
                                        </td>
                                        <td>
                                          <script language="javascript" type="text/javascript">
                                            function showimage()
                                            {
                                                document.images.icons.src="<?= $this->facesurl ?>/"+document.creator.userpic.options[document.creator.userpic.selectedIndex].value;
                                            }
                                          </script>
                                          <select name="userpic" size="6" onchange="showimage()">
                                            <?php foreach($this->images as $image): ?>
                                                <option value="<?= $image[0] ?>" <?= $image[2] ?>><?= $image[1] ?></option>
                                            <?php endforeach; ?>
                                          </select>
                                          &nbsp;&nbsp;<img src="<?= $this->facesurl ?>/<?= urlencode($this->mem['pic']) ?>" name="icons" border="0" hspace="15" alt="">
                                        </td>
                                      </tr>
                                      <tr>
                                        <td width="45%">
                                          <font size="2"><b><?= $this->locale->txt[475] ?></b></font>
                                        </td>
                                        <td>
                                          <input type="checkbox" name="userpicpersonalcheck" <?= $this->pic_checked ?>>
                                          <input type="text" name="userpicpersonal" size="45" value="<?= $this->esc($this->mem['perspic']) ?>" placeholder="http://">
                                        </td>
                                      </tr>
                                  <?php endif; /* allowpics */?>
                                  
                                  <tr>
                                    <td colspan="2"><hr width="100%" size="1" class="windowbg3"></td>
                                  </tr>
                                  
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b><?= $this->locale->txt[228] ?>:</b></font>
                                    </td>
                                    <td>
                                      <font size="2">
                                        <input type="text" name="usertext" size="50" maxlength="100" value="<?= $this->esc($this->mem['personalText']) ?>">
                                      </font>
                                    </td>
                                  </tr>
                                  
                                  <tr>
                                    <td width="45%" valign="top">
                                      <font size="2"><b><?= $this->locale->txt[85] ?>:</b></font>
                                      <br>
                                      <font size="1"><?= $this->locale->txt[606] ?></font>
                                    </td>
                                    <td>
                                      <font size="2">
                                        <textarea name="signature" rows="4" cols="50"><?= $this->esc($this->mem['signature']) ?></textarea>
                                      </font>
                                      <br>
                                      <font size="1">
                                        <?= $this->locale->txt[664] ?>
                                        <input value="<?= $this->siglen ?>" size="3" name="msgCL" readonly="readonly">
                                      </font>
                                      <script language="JavaScript" type="text/javascript">
                                        <!--
                                        var supportsKeys = false;
                                        function tick()
                                        {
                                            calcCharLeft(document.forms[0]);
                                            if (!supportsKeys)
                                                timerID = setTimeout("tick()",' . $MaxSigLen . ');
                                        }
                                        
                                        function calcCharLeft(sig)
                                        {
                                            clipped = false;
                                            maxLength = '<?= $this->siglen ?>';
                                            if (document.creator.signature.value.length > maxLength)
                                            {
                                                document.creator.signature.value = document.creator.signature.value.substring(0,maxLength);
                                                charleft = 0;
                                                clipped = true;
                                            }
                                            else
                                                charleft = maxLength - document.creator.signature.value.length
                                            document.creator.msgCL.value = charleft;
                                            return clipped
                                        }
                                        
                                        tick();
                                        //-->
                                      </script>
                                    </td>
                                  </tr>
                                  
                                </table><!-- 108 -->
                                <br>
                              </td>
                            </tr>
                            <tr>
                              <td class="catbg" bgcolor="<?= $this->color['catbg'] ?>" height="25">
                                <font size="2"><b><?= $this->locale->txt[605] ?></b></font>
                              </td>
                            </tr>
                            <tr>
                              <td class="windowbg" bgcolor="<?= $this->color['windowbg'] ?>">
                                <table border="0" cellpadding="3" cellspacing="0">
                                  <?php if($this->allow_hide_email): ?>
                                    <tr>
                                      <td width="45%">
                                        <font size="2"><b><?= $this->locale->txt[721] ?></b></font>
                                      </td>
                                      <td>
                                        <input type="checkbox" name="hideemail" <?= $this->mem['hideEmail'] ?>>
                                      </td>
                                    </tr>
                                  <?php endif; ?>
                                  
                                  <?php if($this->QuickReply): ?>
                                    <tr>
                                      <td width="45%">
                                        <font size="2"><b><?= $this->locale->QuickReply2 ?></b></font>
                                      </td>
                                      <td>
                                        <input type="checkbox" name="QuickReply" <?= $this->mem['QuickReply'] ?>>
                                      </td>
                                    </tr>
                                  <?php endif; ?>
                                  
                                  <?php if($this->userLanguage): ?>
                                    <tr>
                                      <td width="45%">
                                        <font size="2"><b><?= $this->locale->txt[349] ?>:</b></font>
                                      </td>
                                      <td width="50">
                                        <select name="language">
                                          <?php foreach($this->mem['userLangs'] as $userLang): ?>
                                            <option value="<?= $userLang['entry'] ?>" <?= $userLang['selected'] ?>><?= $userLang['name'] ?></option>
                                          <?php endforeach; ?>
                                        </select>
                                      </td>
                                    </tr>
                                  <?php endif; ?>
                                  
                                  <tr>
                                    <td width="45%">
                                      <script language="javascript" type="text/javascript">
                                        <!--
                                        function reqWin(desktopURL)
                                        {
                                          desktop = window.open(desktopURL,"name","toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=480,height=200,resizable=no");
                                        }
                                        // -->
                                      </script>
                                      <font size="2"><b><?= $this->locale->txt[486] ?>:</b></font>
                                      <br>
                                      <a href="javascript:reqWin('<?= SITE_ROOT ?>/help/12/')" class="help"><img src="<?= $this->imagesdir ?>/helptopics.gif" border="0" alt="<?= $this->locale->txt[119] ?>" align="left"></a>
                                      <font size="1"><?= $this->locale->txt[479] ?></font>
                                    </td>
                                    <td width="50">
                                      <input type="text" name="usertimeformat" value="<?= $this->esc($this->mem['timeFormat']) ?>">
                                    </td>
                                  </tr>
                                  
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b><?= $this->locale->txt[371] ?>:</b></font>
                                      <br>
                                      <font size="1"><?= $this->locale->txt[519] ?></font>
                                    </td>
                                    <td>
                                      <font size="1">
                                        <input name="usertimeoffset" size="5" maxlength="5" value="<?= $this->esc($this->mem['timeOffset']) ?>">
                                        <br>
                                        <?= $this->locale->txt[741] ?>: <i><?= $this->mem['time'] ?></i>
                                      </font>
                                    </td>
                                  </tr>
                                  
                                  <tr>
                                    <td colspan=2>
                                      <hr id="skin" width="100%" size="1" class="windowbg3">
                                    </td>
                                  </tr>
                                  <tr>
                                    <td width="45%">
                                      <font size=2><b><?= $this->locale->skins0 ?>:</b></font>
                                    </td>
                                    <td>
                                      <select name="skin">
                                        <?php foreach($this->skins as $skin): ?>
                                          <option value="<?= $skin['entry'] ?>" <?= $skin['selected'] ?>><?= $skin['entry'] ?></option>
                                        <?php endforeach; ?>
                                      </select>
                                    </td>
                                  </tr>
                                  
                                  <tr>
                                    <td>
                                      <font size="2"><b>На фоне падают:</b></font>
                                    </td>
                                    
                                    <td>
                                      <input type="radio" name="snowflakes" value="1" <?= $this->flakes ?> >
                                      снежинки
                                      <br>
                                      <input type="radio" name="snowflakes" value="2" <?= $this->chegaflake ?>> фото Chega
                                      <br>
                                      <input type="radio" name="snowflakes" value="0" <?= $this->noflakes ?>> ничего
                                    </td>
                                  </tr>
                                  
                                  <tr>
                                    <td>
                                      <font size="2"><b>Звуковое оповещение о новом личном сообщении:</b></font>
                                    </td>
                                    <td>
                                      <select name="imsound" onchange="Forum.Profile.setIMSound(this.options[this.selectedIndex].value);" style="float: left; height: 18px; margin: 0 5px 0 0; padding: 0;">
                                        <option value="">Без звука</option>
                                        <option value=""></option>
                                        <option value="">= PRODIGY =</option>
                                        <?php foreach($this->sounds_prodigy as $sounds): ?>
                                          <option value="<?= $sounds['path'] ?>" <?= $sounds['selected'] ?>><?= $sounds['name'] ?></option>
                                        <?php endforeach; ?>
                                        <option value=""></option>
                                        <option value="">= OTHER =</option>
                                        <?php foreach($this->sounds_other as $sounds): ?>
                                          <option value="<?= $sounds['path'] ?>" <?= $sounds['selected'] ?>><?= $sounds['name'] ?></option>
                                        <?php endforeach; ?>
                                      </select>
                                      <a href="<?= $this->esc($this->mem['imsound']) ?>" id="imSoundPlayer" class="mp3player"></a>
                                      <script type="text/javascript">Forum.Profile.setIMSound('<?= $this->esc($this->mem['imsound']) ?>')</script>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b>Показывать комментарии к сообщениям форумчан в развёрнутом виде</b></font>
                                    </td>
                                    <td>
                                      <input type="checkbox" name="showComments" <?= $this->mem['showComments']==1?'checked':'' ?>>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td colspan=2>
                                      <hr width="100%" size="1" class="windowbg3">
                                    </td>
                                  </tr>
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b><?= $this->locale->txt[81] ?>: </b></font>
                                      <br>
                                      <font size="1"><?= $this->locale->txt[596] ?></font>
                                    </td>
                                    <td>
                                      <input type="password" name="passwrd1" size="20">
                                    </td>
                                  </tr>
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b><?= $this->locale->txt[82] ?>: </b></font>
                                    </td>
                                    <td>
                                      <input type="password" name="passwrd2" size="20">
                                    </td>
                                  </tr>
                                  <tr>
                                    <td colspan="2">
                                      <hr width="100%" size="1" class="windowbg3">
                                    </td>
                                  </tr>
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b><?= $this->locale->txt['pswd1'] ?>:</b></font>
                                    </td>
                                    <td>
                                      <input name="secretQuestion" size="50" value="<?= $this->esc($this->mem['secretQuestion']) ?>">
                                    </td>
                                  </tr>
                                  <tr>
                                    <td width="45%">
                                      <font size="2"><b><?= $this->locale->txt['pswd2'] ?>:</b></font>
                                    </td>
                                    <td>
                                      <input name="secretAnswer" value="<?= $this->esc($this->mem['secretAnswer']) ?>">
                                    </td>
                                  </tr>
                                  
                                  <?php if($this->admin): ?>
                                    <tr>
                                      <td colspan="2">
                                        <hr width="100%" size="1" class="windowbg3">
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width="45%">
                                        <font size="2"><b><?= $this->locale->txt[86] ?>:</b></font>
                                      </td>
                                      <td>
                                        <font size="2">
                                          <input type="text" name="posts_count" size="4" value="<?= $this->mem['posts'] ?>">
                                        </font>
                                      </td>
                                    </tr>
                                    
                                    <?php if($this->karmaMode): ?>
                                      <tr>
                                        <td>
                                          <font size="2"><b><?= $this->karmaLabel ?></b></font>
                                        </td>
                                        <td>
                                          <font size="2"><?= $this->karmaSmiteLabel ?> <input type="text" name="karmaBad" size="4" value="<?= $this->mem['karmaBad'] ?>" >&nbsp;&nbsp;&nbsp;&nbsp;<?= $this->karmaApplaudLabel ?> <input type="text" name="karmaGood" size="4" value="<?= $this->mem['karmaGood'] ?>">&nbsp;&nbsp;&nbsp;&nbsp;(<?= $this->locale->txt[94] ?>: <?= $this->mem['totalKarma'] ?>)</font>
                                        </td>
                                      </tr>
                                    <?php endif; /* karmaMode */ ?>
                                    
                                    <tr>
                                      <td>
                                        <font size="2"><b><?= $this->locale->txt[87] ?>: </b></font>
                                      </td>
                                      <td>
                                        <font size="2">
                                          <select name="user_group">
                                            <option value=""></option>
                                            <?php foreach($this->member_groups as $memgr): ?>
                                              <option value="<?= $memgr['group'] ?>" <?= $memgr['selected'] ?>><?= $memgr['tr'] ?></option>
                                            <?php endforeach; ?>
                                          </select>
                                        </font>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width="45%">
                                        <font size="2"><b><?= $this->locale->txt[233] ?>:</b></font>
                                      </td>
                                      <td>
                                        <input type="text" name="dr" size="35" value="<?= $this->mem['dateReg'] ?>">
                                      </td>
                                    </tr>
                                  <?php endif; /* if admin */ ?>
                                  
                                  <tr>
                                    <td align="center" colspan="2">
                                      <br>
                                      <input type="hidden" name="moda" value="1">
                                      <input type="submit" value="<?= $this->locale->txt[88] ?>" onclick="creator.moda.value='1';">
                                      <input type="button" value="<?= $this->locale->txt[89] ?>" onclick="if (confirm('<?= $this->locale->txt['profileConfirm'] ?>')) { creator.moda.value='-1'; submit(); }">
                                      <br><br>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
                          <input type="hidden" name="sc" value="<?= $this->sessionid ?>">
                        </form>
