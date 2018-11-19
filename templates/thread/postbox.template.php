              <?php if($this->conf->enable_ubbc && $this->conf->showyabbcbutt): ?>
                <tr>
                  <td align="right">
                    <font size="2"><b><?= $this->locale->txt[252] ?>:</b></font>
                  </td>
                  <td valign="middle">
                    <a href="javascript:surroundText('[b]','[/b]')"><img src="<?= $this->conf->imagesdir ?>/bold.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[253] ?>" border="0"></a>
                    <a href="javascript:surroundText('[i]','[/i]')"><img src="<?= $this->conf->imagesdir ?>/italicize.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[254] ?>" border="0"></a>
                    <a href="javascript:surroundText('[u]','[/u]')"><img src="<?= $this->conf->imagesdir ?>/underline.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[255] ?>" border="0"></a>
                    <a href="javascript:surroundText('[s]','[/s]')"><img src="<?= $this->conf->imagesdir ?>/strike.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[441] ?>" border="0"></a>
                    <!--<a href="javascript:surroundText('[glow=red,2,300]','[/glow]')"><img src="<?= $this->conf->imagesdir ?>/glow.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[442] ?>" border="0"></a>
                    <a href="javascript:surroundText('[shadow=red,left]','[/shadow]')"><img src="<?= $this->conf->imagesdir ?>/shadow.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[443] ?>" border="0"></a>-->
                    <a href="javascript:surroundText('[move]','[/move]')"><img src="<?= $this->conf->imagesdir ?>/move.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[439] ?>" border="0"></a>
                    <a href="javascript:surroundText('[size=2]','[/size]')"><img src="<?= $this->conf->imagesdir ?>/size.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[532] ?>" border="0"></a>
                    <a href="javascript:surroundText('[font=Verdana]','[/font]')"><img src="<?= $this->conf->imagesdir ?>/face.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[533] ?>" border="0"></a>
                    <a href="javascript:surroundText('[sup]','[/sup]')"><img src="<?= $this->conf->imagesdir ?>/sup.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[447] ?>" border="0"></a>
                    <a href="javascript:surroundText('[sub]','[/sub]')"><img src="<?= $this->conf->imagesdir ?>/sub.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[448] ?>" border="0"></a>

                    <select name="txtcolor" onchange="surroundText('[color='+this.options[this.selectedIndex].value+']','[/color]')">
                      <option value="Black" selected="selected"><?= $this->locale->txt[262] ?></option>
                      <option value="Red"><?= $this->locale->txt[263] ?></option>
                      <option value="Yellow"><?= $this->locale->txt[264] ?></option>
                      <option value="Pink"><?= $this->locale->txt[265] ?></option>
                      <option value="Green">'. $txt[266] ?></option>
                      <option value="Orange"><?= $this->locale->txt[267] ?></option>
                      <option value="Purple"><?= $this->locale->txt[268] ?></option>
                      <option value="Blue"><?= $this->locale->txt[269] ?></option>
                      <option value="Beige"><?= $this->locale->txt[270] ?></option>
                      <option value="Brown"><?= $this->locale->txt[271] ?></option>
                      <option value="Teal"><?= $this->locale->txt[272] ?></option>
                      <option value="Navy"><?= $this->locale->txt[273] ?></option>
                      <option value="Maroon"><?= $this->locale->txt[274] ?></option>
                      <option value="LimeGreen"><?= $this->locale->txt[275] ?></option>
                    </select>
                    <br>
                    
                    <a href="javascript:surroundText('[pre]','[/pre]')"><img src="<?= $this->conf->imagesdir ?>/pre.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[444] ?>" border="0"></a>
                    <a href="javascript:surroundText('[left]','[/left]')"><img src="<?= $this->conf->imagesdir ?>/left.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[445] ?>" border="0"></a>
                    <a href="javascript:surroundText('[center]','[/center]')"><img src="<?= $this->conf->imagesdir ?>/center.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[256] ?>" border="0"></a>
                    <a href="javascript:surroundText('[right]','[/right]')"><img src="<?= $this->conf->imagesdir ?>/right.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[446] ?>" border="0"></a>
                    <a href="javascript:replaceText('[list][*][*][*][/list]')"><img src="<?= $this->conf->imagesdir ?>/list.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[261] ?>" border="0"></a>
                    <a href="javascript:surroundText('[table]','[/table]')"><img src="<?= $this->conf->imagesdir ?>/table.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[436] ?>" border="0"></a>
                    <a href="javascript:surroundText('[tr]','[/tr]')"><img src="<?= $this->conf->imagesdir ?>/tr.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[449] ?>" border="0"></a>
                    <a href="javascript:javascript:surroundText('[td]','[/td]')"><img src="<?= $this->conf->imagesdir ?>/td.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[437] ?>" border="0"></a>
                    <a href="javascript:replaceText('[hr]')"><img src="<?= $this->conf->imagesdir ?>/hr.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[531] ?>" border="0"></a><br />

                    <a href="javascript:surroundText('[url]','[/url]')"><img src="<?= $this->conf->imagesdir ?>/url.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[257] ?>" border="0"></a>
                    <a href="javascript:surroundText('[img]','[/img]')"><img src="<?= $this->conf->imagesdir ?>/img.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[435] ?>" border="0"></a>
                    <a href="javascript:surroundText('[youtube=640,360]','[/youtube]')"><img src="<?= $this->conf->imagesdir ?>/yt.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[258] ?>" border="0"></a>
                    <a href="javascript:surroundText('[media=640,360]','[/media]')"><img src="<?= $this->conf->imagesdir ?>/media.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[258] ?>" border="0"></a>
                    <a href="javascript:surroundText('[video]','[/video]')"><img src="<?= $this->conf->imagesdir ?>/video.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[433] ?>" border="0"></a>
                    <a href="javascript:surroundText('[audio]','[/audio]')"><img src="<?= $this->conf->imagesdir ?>/audio.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[434] ?>" border="0"></a>
                    <a href="javascript:surroundText('[hidden]','[/hidden]')"><img src="<?= $this->conf->imagesdir ?>/hid.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[440] ?>" border="0"></a>
                    <a href="javascript:surroundText('[code]','[/code]')"><img src="<?= $this->conf->imagesdir ?>/code.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[259] ?>" border="0"></a>
                    <a href="javascript:surroundText('[quote]','[/quote]')"><img src="<?= $this->conf->imagesdir ?>/quote2.gif" align="bottom" width="23" height="22" alt="<?= $this->locale->txt[260] ?>" border="0"></a>
                    <a href="javascript:surroundText('[news=Внезапно!]','[/news]')"><img src="<?= $this->conf->imagesdir ?>/1news.gif" align="bottom" width="23" height="22" alt="[news]" border="0"></a>
                    <a href="javascript:surroundText('[think]','[/think]')"><img src="<?= $this->conf->imagesdir ?>/think.gif" align="bottom" width="23" height="22" alt="[think]" border="0"></a>
                    <a href="javascript:surroundText('[nb]','[/nb]')"><img src="<?= $this->conf->imagesdir ?>/nb.gif" align="bottom" width="23" height="22" alt="[nb]" border="0"></a>
                    <a href="javascript:surroundText('[scroll]','[/scroll]')"><img src="<?= $this->conf->imagesdir ?>/scroll.gif" align="bottom" width="23" height="22" alt="[scroll]" border="0"></a>
                  </td>
                </tr>
              <?php endif; ?>
                
              <?php if($this->conf->vbsmiliesEnable != '1' || $this->conf->blockEnable == '1'): ?>
                <tr>
                  <td align="right">
                    <font size="2"><b><?= $this->locale->txt[297] ?>:</b></font>
                  </td>
                  <td valign="middle">
                    <a href="javascript:replaceText(' :)')"><img src="<?= $this->conf->imagesdir ?>/smiley.gif" align="bottom" alt="<?= $this->locale->txt[287] ?>" border="0" /></a>
                    <a href="javascript:replaceText(' ;)')"><img src="<?= $this->conf->imagesdir ?>/wink.gif" align="bottom" alt="<?= $this->locale->txt[292] ?>" border="0" /></a>
                    <a href="javascript:replaceText(' :D')"><img src="<?= $this->conf->imagesdir ?>/cheesy.gif" align="bottom" alt="<?= $this->locale->txt[289] ?>" border="0" /></a>
                    <a href="javascript:replaceText(' ;D')"><img src="<?= $this->conf->imagesdir ?>/grin.gif" align="bottom" alt="<?= $this->locale->txt[293] ?>" border="0" /></a>
                    <a href="javascript:replaceText(' >:(')"><img src="<?= $this->conf->imagesdir ?>/angry.gif" align="bottom" alt="<?= $this->locale->txt[288] ?>" border="0" /></a>
                    <a href="javascript:replaceText(' :(')"><img src="<?= $this->conf->imagesdir ?>/sad.gif" align="bottom" alt="<?= $this->locale->txt[291] ?>" border="0" /></a>
                    <a href="javascript:replaceText(' :o')"><img src="<?= $this->conf->imagesdir ?>/shocked.gif" align="bottom" alt="<?= $this->locale->txt[294] ?>" border="0" /></a>
                    <a href="javascript:replaceText(' 8-)')"><img src="<?= $this->conf->imagesdir ?>/cool.gif" align="bottom" alt="<?= $this->locale->txt[295] ?>" border="0" /></a>
                    <a href="javascript:replaceText(' ???')"><img src="<?= $this->conf->imagesdir ?>/huh.gif" align="bottom" alt="<?= $this->locale->txt[296] ?>" border="0" /></a>
                    <a href="javascript:replaceText(' ::)')"><img src="<?= $this->conf->imagesdir ?>/rolleyes.gif" align="bottom" alt="<?= $this->locale->txt[450] ?>" border="0" /></a>
                    <a href="javascript:replaceText(' :P')"><img src="<?= $this->conf->imagesdir ?>/tongue.gif" align="bottom" alt="<?= $this->locale->txt[451] ?>" border="0" /></a>
                    <a href="javascript:replaceText(' :-[')"><img src="<?= $this->conf->imagesdir ?>/embarassed.gif" align="bottom" alt="<?= $this->locale->txt[526] ?>" border="0" /></a>
                    <a href="javascript:replaceText(' :-X')"><img src="<?= $this->conf->imagesdir ?>/lipsrsealed.gif" align="bottom" alt="<?= $this->locale->txt[527] ?>" border="0" /></a>
                    <a href="javascript:replaceText(' :-\\\')"><img src="<?= $this->conf->imagesdir ?>/undecided.gif" align="bottom" alt="<?= $this->locale->txt[528] ?>" border="0" /></a>
                    <a href="javascript:replaceText(' :-*')"><img src="<?= $this->conf->imagesdir ?>/kiss.gif" align="bottom" alt="<?= $this->locale->txt[529] ?>" border="0" /></a>
                    <a href="javascript:replaceText(' :\'(')"><img src="<?= $this->conf->imagesdir ?>/cry.gif" align="bottom" alt="<?= $this->locale->txt[530] ?>" border="0" /></a>
                    <a href="javascript:replaceText(' :-D')"><img src="<?= $this->conf->imagesdir ?>/laugh.gif" align="bottom" alt="laugh" border="0" /></a>
                    <a href="javascript:replaceText(' :[-')"><img src="<?= $this->conf->imagesdir ?>/rusty.gif" align="bottom" alt="rusty" border="0" /></a>
                    <a href="javascript:replaceText(' :F')"><img src="<?= $this->conf->imagesdir ?>/facepalm.gif" align="bottom" alt="facepalm" border="0" /></a>
                    <a href="javascript:replaceText(' :№')"><img src="<?= $this->conf->imagesdir ?>/crazy.gif" align="bottom" alt="facepalm" border="0" /></a>
                    <a href="javascript:replaceText(' :-O')"><img src="<?= $this->conf->imagesdir ?>/singing.gif" align="bottom" alt="Поющий" title="Поющий" border="0" /></a>
                    <a href="javascript:replaceText(' :-#')"><img src="<?= $this->conf->imagesdir ?>/sleep.gif" align="bottom" alt="Ругается" title="Ругается" border="0" /></a>
                    <a href="javascript:replaceText(' :ykypok:')"><img src="<?= $this->conf->imagesdir ?>/insane.gif" align="bottom" alt="Укурок" title="В жопу укуренный алкаш" border="0" /></a>
                    <a href="javascript:replaceText(' :hm:')"><img src="<?= $this->conf->imagesdir ?>/hm.gif" align="bottom" alt="Хм" title="Хм" border="0" /></a>
                    <a href="javascript:replaceText(' ;] ')"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/trollface.gif" align="bottom" alt="Тролль" title="Тролль" border="0" /></a>
                    <a href="javascript:replaceText(' ЪЪ')"><img src="<?= $this->conf->imagesdir ?>/thumbup.gif" align="bottom" alt="Ъ" title="Ъ" border="0" /></a>
                    <a href="javascript:replaceText(' !Ъ')"><img src="<?= $this->conf->imagesdir ?>/thumbdown.gif" align="bottom" alt="!Ъ" title="!Ъ" border="0" /></a>
                    <a href="javascript:replaceText(' (!)')"><img src="<?= $this->conf->imagesdir ?>/exclamation.gif" align="bottom" alt="(!)" title="!" border="0" /></a>
                    <a href="javascript:replaceText(' (?)')"><img src="<?= $this->conf->imagesdir ?>/question.gif" align="bottom" alt="(?)" title="?" border="0" /></a>
                  </td>
                </tr>
              <?php endif; ?>
              
              <tr></tr>
              
              <tr>
              <?php if($this->conf->vbsmiliesEnable && $this->conf->blockEnable != '1'): ?>
                  <td valign=top align="right">
                    <font size=2><b><?= $this->locale->txt[72] ?>:</b></font>
                    <br><br>
                    <center>
                      <table border="0" cellpadding="0" width="90%">
                        <tr>
                          <td width="100%" colspan="3">
                            <p align="center">
                              <font size="2"><b><?= $this->locale->asmtxt[10] ?></b></font>
                            </p>
                          </td>
                        </tr>
                        <tr>
                          <td width="30%" align="center">
                            <a href="javascript:replaceText(' :)')"><img src="<?= $thid->conf->imagesdir ?>/smiley.gif" alt="<?= $this->locale->txt[287] ?>" border="0" /></a>
                          </td>
                          <td width="30%" align="center">
                            <a href="javascript:replaceText(' ;)')"><img src="<?= $thid->conf->imagesdir ?>/wink.gif" alt="<?= $this->locale->txt[292] ?>" border="0" /></a>
                          </td>
                          <td width="30%" align="center">
                            <a href="javascript:replaceText(' :D')"><img src="<?= $thid->conf->imagesdir ?>/cheesy.gif" alt="<?= $this->locale->txt[289] ?>" border="0" /></a>
                          </td>
                        </tr>
                        <tr>
                          <td width="30%" align="center">
                            <a href="javascript:replaceText(' ;D')">
                              <img src="<?= $thid->conf->imagesdir ?>/grin.gif" alt="<?= $this->locale->txt[293] ?>" border="0" /></a>
                          </td>
                          <td width="30%" align="center">
                            <a href="javascript:replaceText(' >:(')"><img src="<?= $thid->conf->imagesdir ?>/angry.gif" alt="<?= $this->locale->txt[288] ?>" border="0" /></a>
                          </td>
                          <td width="30%" align="center">
                            <a href="javascript:replaceText(' :(')"><img src="<?= $thid->conf->imagesdir ?>/sad.gif" alt="<?= $this->locale->txt[291] ?>" border="0" /></a>
                          </td>
                        </tr>
                        <tr>
                          <td width="30%" align="center">
                            <a href="javascript:replaceText(' :o')"><img src="<?= $thid->conf->imagesdir ?>/shocked.gif" alt="<?= $this->locale->txt[294] ?>" border="0" /></a>
                          </td>
                          <td width="30%" align="center">
                            <a href="javascript:replaceText(' 8-)')"><img src="<?= $thid->conf->imagesdir ?>/cool.gif" alt="<?= $this->locale->txt[295] ?>" border="0" /></a>
                          </td>
                          <td width="30%" align="center">
                            <a href="javascript:replaceText(' ???')"><img src="<?= $thid->conf->imagesdir ?>/huh.gif" alt="<?= $this->locale->txt[296] ?>" border="0" /></a>
                          </td>
                        </tr>
                        <tr>
                          <td width="30%" align="center">
                            <a href="javascript:replaceText(' ::)')"><img src="<?= $thid->conf->imagesdir ?>/rolleyes.gif" alt="<?= $this->locale->txt[450] ?>" border="0" /></a>
                          </td>
                          <td width="30%" align="center">
                            <a href="javascript:replaceText(' :P')"><img src="<?= $thid->conf->imagesdir ?>/tongue.gif" alt="<?= $this->locale->txt[451] ?>" border="0" /></a>
                          </td>
                          <td width="30%" align="center">
                            <a href="javascript:replaceText(' :-[')"><img src="<?= $thid->conf->imagesdir ?>/embarassed.gif" alt="<?= $this->locale->txt[526] ?>" border="0" /></a>
                          </td>
                        </tr>
                        <tr>
                          <td width="30%" align="center">
                            <a href="javascript:replaceText(' :-X')"><img src="<?= $thid->conf->imagesdir ?>/lipsrsealed.gif" alt="<?= $this->locale->txt[527] ?>" border="0" /></a>
                          </td>
                          <td width="30%" align="center">
                            <a href="javascript:replaceText(' :-\\\\')"><img src="<?= $thid->conf->imagesdir ?>/undecided.gif" alt="<?= $this->locale->txt[528] ?>" border="0" /></a>
                          </td>
                          <td width="30%" align="center">
                            <a href="javascript:replaceText(' :-*')"><img src="<?= $thid->conf->imagesdir ?>/kiss.gif" alt="<?= $this->locale->txt[529] ?>" border="0" /></a>
                          </td>
                        </tr>
                        <tr>
                          <td width="30%"></td>
                          <td width="30%" align="center">
                            <a href="javascript:replaceText(' :\\'(')"><img src="<?= $thid->conf->imagesdir ?>/cry.gif" alt="<?= $this->locale->txt[530] ?>" border="0" /></a>
                          </td>
                          <td width="30%"></td>
                        </tr>
                        <tr>
                          <td width="100%" colspan="3">
                            <font size="1"><br>
                              <?= $this->locale->asmtxt[19] ?> 16<br>
                              [<a href="javascript:smileywin()"><?= $this->locale->asmtxt[18] ?></a>]
                            </font>
                          </td>
                        </tr>
                      </table>
                    </center>
                  </td>
              <?php else: ?>
                  <td valign=top align="right"><font size="2"><b><?= $this->locale->txt[72] ?>:</b></font></td>
              <?php endif; ?>
                  <td>
                    <textarea class="editor" name="message" id="messagebody" rows="20" cols="120" onselect="javascript:storeCaret(this);" onclick="javascript:storeCaret(this);" onkeyup="javascript:storeCaret(this);" onchange="javascript:storeCaret(this);"><?= $this->get('form_message') ?></textarea>
                    <a href="javascript: tinyMCE.execCommand('mceToggleEditor', false, 'messagebody');void(0);">Включить режим WYSIWYG</a>
                  </td>
                </tr>
                
