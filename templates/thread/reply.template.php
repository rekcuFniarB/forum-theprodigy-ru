        <form action="." method="post" name="postmodify" onsubmit="if (document.postmodify.subject.value == '' || document.postmodify.message.value == '') { alert('<?= $this->locale->txt('jsvalidate1', 'Empty subject!') ?>'); return false; } else { submitonce(this); }" enctype="multipart/form-data">
          <table  width="75%" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td valign="bottom" colspan="2">
                <!-- LinkTree -->
                <?php if($this->conf->enableInlineLinks): ?>
                  <font size="1">
                    <b><a href="<?= SITE_ROOT ?>/" class="nav"><?= $this->esc($this->conf->mbname) ?></a></b>&nbsp;|&nbsp;<!--
                    --><b><a href="<?= SITE_ROOT ?>/#<?= $this->cat ?>" class="nav"><?= $this->esc('catname') ?></a></b>&nbsp;|&nbsp;<!--
                    --><b><a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/" class="nav"><?= $this->esc('boardname') ?></a></b>&nbsp;|&nbsp;<!--
                    --><b><?= $this->esc('title') ?>
                    <?php if(isset($this->sub)): ?>
                        ( <?= $this->esc('sub') ?> )
                    <?php endif; ?>
                    </b>
                  </font>
                <?php else: ?>
                  <font size="2">
                    <b>
                      <img src="<?= $this->conf->imagesdir ?>/open.gif" border="0" alt="" />&nbsp;&nbsp;<a href="<?= SITE_ROOT ?>/" class="nav"><?= $this->esc($this->conf->mbname) ?></a><br />
                      <img src="<?= $this->conf->imagesdir ?>/tline.gif" border="0" alt=""><img src="<?= $this->conf->imagesdir ?>/open.gif" border="0" alt="">&nbsp;&nbsp;<a href="<?= SITE_ROOT ?>/#<?= $this->cat ?>" class="nav"><?= $this->esc('catname') ?></a><br />
                      <img src="<?= $this->conf->imagesdir ?>/tline2.gif" border="0" alt=""><img src="<?= $this->conf->imagesdir ?>/open.gif" border="0" alt="">&nbsp;&nbsp;<a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/" class="nav"><?= $this->esc('boardname') ?></a><br />
                      <img src="<?= $this->conf->imagesdir ?>/tline3.gif" border="0" alt=""><img src="<?= $this->conf->imagesdir ?>/open.gif" border="0" alt="">&nbsp;&nbsp;<?= $this->esc('title') ?> ( <?=$this->esc('sub') ?> )
                    </b>
                  </font>
                <?php endif; ?>
              </td>
            </tr>
          </table>
          
          <table border="0"  width="75%" align="center" cellspacing="1" cellpadding="3" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor message-input-form-container">
            <tr>
              <td class="titlebg" bgcolor="<?= $this->conf->color['titlebg'] ?>">
                <font size="2" class="text1" color="<?= $this->conf->color['titletext'] ?>">
                  <b><?= $this->title ?></b>
                </font>
              </td>
            </tr>
            <tr>
              <td class="windowbg" bgcolor="<?= $this->conf->color['windowbg'] ?>">
                <input type="hidden" name="threadid" value="<?= $this->thread ?>">
                <table border="0" cellpadding="3" width="100%">
                  <?php if($this->locked): ?>
                    <tr>
                      <td>&nbsp;</td>
                      <td align="left">
                        <font size="2"><?= $this->locale->yse287 ?></font>
                      </td>
                    </tr>
                  <?php endif; ?>
                  <?php if($this->user->realname == ''): ?>
                    <tr>
                      <td align="right">
                        <font size="2"><b><?= $this->locale->txt[68] ?>:</b></font>
                      </td>
                      <td>
                        <font size="2">
                          <input type="text" name="name" id="name" size="25" value="<?= $this->esc($this->guestname) ?>">
                        </font>
                      </td>
                    </tr>
                  <?php endif; ?>
                  
                  <?php if($this->user->email == ''): ?>
                    <tr>
                      <td align="right">
                        <font size="2">
                          <b><?= $this->locale->txt[69] ?>:</b>
                        </font>
                      </td>
                      <td>
                        <font size="2">
                          <input type="text" name="email" size="25" value="<?= $this->get('guestemail') ?>">
                        </font>
                      </td>
                    </tr>
                  <?php endif; ?>
                  
                    <tr>
                      <td align="right">
                        <font size="2">
                          <b><?= $this->locale->txt[70] ?>:</b>
                        </font>
                      </td>
                      <td>
                        <input type="hidden" name="serial" value="<?= $this->conf->serial ?>">
                        <font size="2">
                          <input type="text" name="naztem" value="<?= $this->get('form_subject') ?>" size="40" maxlength="80">
                        </font>
                      </td>
                    </tr>
                    <tr>
                      <td align="right">
                        <font size="2">
                          <b><?= $this->locale->txt[71] ?>:</b>
                        </font>
                      </td>
                      <td>
                        <select name="icon" onchange="showimage()">
                          <?php if(!empty($this->icon)): ?>
                              <option value="<?= $this->esc('icon') ?>"><?= $this->locale->txt[112] ?></option>
                              <option value="<?= $this->esc('icon') ?>">------------</option>
                          <?php endif; ?>
                          <option value="xx"><?= $this->locale->txt[281] ?></option>
                          <option value="thumbup"><?= $this->locale->txt[282] ?></option>
                          <option value="thumbdown"><?= $this->locale->txt[283] ?></option>
                          <option value="exclamation"><?= $this->locale->txt[284] ?></option>
                          <option value="question"><?= $this->locale->txt[285] ?></option>
                          <option value="lamp"><?= $this->locale->txt[286] ?></option>
                          <option value="smiley"><?= $this->locale->txt[287] ?></option>
                          <option value="angry"><?= $this->locale->txt[288] ?></option>
                          <option value="cheesy"><?= $this->locale->txt[289] ?></option>
                          <option value="grin"><?= $this->locale->txt[293] ?></option>
                          <option value="sad"><?= $this->locale->txt[291] ?></option>
                          <option value="wink"><?= $this->locale->txt[292] ?></option>
                        </select>
                        <?php if(!empty($this->icon)): ?>
                          <img src="<?= $this->conf->imagesdir ?>/<?= $this->esc('icon') ?>.gif" name="icons" border="0" hspace="15" alt="">
                        <?php else: ?>
                          <img src="<?= $this->conf->imagesdir ?>/xx.gif" name="icons" border="0" hspace="15" alt="">
                        <?php endif; ?>
                      </td>
                    </tr>
                    
                    <?php $this->partial('templates/thread/postbox.template.php'); ?>
                    
                  <?php if($this->conf->nowlistening_enabled): ?>
                    <tr>
                      <td valign="top" align="right">
                        <font size="2">
                          <b><?= $this->locale->nowListening5 ?>:</b>
                        </font>
                      </td>
                      <td>
                        <input type="text" name="nowListening" size="50" maxlength="250" value="<?= $this->esc($this->nowListening) ?>">
                      </td>
                    </tr>
                  <?php endif; ?>
                  
                  <?php /* quick poll title by dig7er, 14.04.2010 */ ?>
                    <tr>
                      <td valign="top" align="right">
                        <font size="2"><b>Быстрый опрос:</b></font>
                      </td>
                      <td>
                        <input type="text" name="quickPoll" size="50" maxlength="250" value="<?= $this->esc($this->quickPollTitle) ?>">
                      </td>
                    </tr>
                    
                    <?php if(!empty($this->lastmodification)): ?>
                      <tr>
                        <td valign="top" align="right">
                          <font size="2"><b><?= $this->locale->txt[211] ?>:</b></font>
                        </td>
                        <td>
                          <font size="2"><?= $this->lastmodification ?></font>
                        </td>
                      </tr>
                    <?php endif; ?>
                    
                    <?php if($this->lock): ?>
                      <tr>
                        <td align="right">
                          <font size="2"><b><?= $this->locale->yse13 ?>:</b></font>
                        </td>
                        <td>
                          <font size="2">
                            <input type="checkbox" name="lock">
                          </font>
                          <font size="1"><?= $this->locale->yse15 ?></font>
                        </td>
                      </tr>
                    <?php endif; ?>
                    
                    <?php if($this->notify): ?>
                      <tr>
                        <td align="right">
                          <font size="2"><b><?= $this->locale->txt[131] ?>:</b></font>
                        </td>
                        <td>
                          <font size="2">
                            <input type="checkbox" name="notify">
                          </font>
                          <font size="1"><?= $this->locale->yse14 ?></font>
                        </td>
                      </tr>
                    <?php endif; ?>
                          
                    <tr>
                      <td align="right">
                        <font size="2">
                          <b><?= $this->locale->txt[276] ?>:</b>
                        </font>
                        <br>
                        <br>
                      </td>
                      <td>
                        <input type="checkbox" name="ns" value="NS" <?= $this->nosmiley ?>>
                        <font size="1"><?= $this->locale->txt[277] ?></font>
                        <br>
                        <br>
                      </td>
                    </tr>
                    
                    <?php if($this->mn): ?>
                    <tr>
                      <td align="right">
                        <font size="2"><b><?= $this->locale->Hierarchical18 ?>:</b></font>
                        <br><br>
                      </td>
                      <td>
                        <font size="2"><input type="checkbox" name="mn" value="MN"/></font>
                        <font size="1"><?= $this->locale->txt['Hierarchical19'] ?></font>
                        <br><br>
                      </td>
                    </tr>
                    <?php endif; ?>
                    
                    <?php if($this->attachment_fields): ?>
                      <?php if($this->attachmentFilename): /* show delete checkbox */ ?>
                        <tr>
                          <td align="right">
                            <font size="2"><b><?= $this->locale->yse119 ?>:</b></font>
                            <br><br>
                          </td>
                          <td>
                            <input type="hidden" name="attachOld" value="<?= $this->esc('attachmentFilename') ?>">
                            <input type="checkbox" name="delAttach" value="on">
                            <font size="1"><?= $this->locale->yse130 ?></font>
                            <br><br>
                          </td>
                        </tr>
                      <?php else: ?>
                        <tr>
                          <td align="right" valign="top">
                            <font size="2">
                              <b><?= $this->locale->yse119 ?>:</b>
                            </font>
                            <br><br>
                          </td>
                          <td>
                            <input type="file" size="48" name="attachment" onchange="updateFields();">
                            <br>
                            <font size="1">
                              <?= $this->locale->yse120 ?>: <?= $this->conf->attachmentExtensions ?>
                              <br>
                              <?= $this->locale->yse121 ?>: <?= $this->locale->attachmentSizeLimit ?> KB
                            </font>
                            <input type="hidden" name="attachmentp" value="">
                            <br><br>
                          </td>
                        </tr>
                      <?php endif; ?>
                    <?php endif; /* attachment field */ ?>
                    
                    <?php if($this->modify): ?>
                      <tr>
                        <td valign="top" align="right">
                          <font size="2"><b><?= $this->locale->mdfrznlbl ?>:</b></font>
                        </td>
                        <td>
                          <font size="2">
                            <input type="text" name="mdfrzn" required maxlength="256" placeholder="<?= $this->locale->mdfrznplchldr ?>" size="50">
                          </font>
                        </td>
                      </tr>
                    <?php endif; ?>
                    
                    <tr>
                      <td align="center" colspan="2">
                        <font size="1" class="text1" color="#000000">
                          <font style="font-weight:normal" size="1"><?= $this->locale->yse16 ?></font>
                        </font>
                        <br>
                        <input type="hidden" name="waction" value="post">
                        <input type="submit" name="post" value="<?= $this->locale->txt[105] ?>" onclick="WhichClicked('post');" accesskey="s">
                        <!--<input type="submit" name="preview" value="<?= $this->locale->txt[507] ?>" onclick="WhichClicked('preview');" accesskey="p">-->
                        <input type="button" name="preview" value="<?= $this->locale->txt[507] ?>" onclick="Forum.Utils.previewPost(this);" accesskey="p">
                        <input type="reset" value="<?= $this->locale->txt[278] ?>" accesskey="r">
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2"></td>
                    </tr>
                  </table>
                  <?php if($this->editfeed): ?>
                    <div class="feed-mod-link">
                      <a href="<?= SITE_ROOT ?>/feed/0/0/<?= $this->msg ?>/edit/"><?= $this->locale->feed_mod_lnk ?></a>
                    </div>
                  <?php endif; ?>
                </td>
              </tr>
            </table>
            <input type="hidden" name="sc" value="<?= $this->ses_id ?>">
          </form>
          
          <?php if(isset($this->thread_summary)): ?>
              <?php $this->partial('templates/thread/summary.template.php'); ?>
          <?php else: ?>
            <!--no summary-->
          <?php endif; ?>
          
        <script language="JavaScript" src="<?= $this->conf->ubbcjspath ?>" type="text/javascript"></script>
        <script language="JavaScript" type="text/javascript">
        <!--
            if (typeof document.postmodify.subject === 'undefined' && typeof document.postmodify.naztem === 'object')
                document.postmodify.subject = document.postmodify.naztem;
            function showimage()
            {
                document.images.icons.src="<?= $this->conf->imagesdir ?>/" + document.postmodify.icon.options[document.postmodify.icon.selectedIndex].value + ".gif";
            }
            function updateFields()
            {
                if (document.layers || document.all || document.getElementById)
                {
                    pForm = document.postmodify;
                    pForm.attachmentp.value = pForm.attachment.value;
                }
            }
        //-->
        </script>
