      <script type="text/javascript" language="JavaScript 1.2" src="<?= $this->conf->ubbcjspath ?>"></script>
      <table width="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="<?= $this->conf->color['bordercolor'] ?>" class="bordercolor" valign="top">
        <tr>
          <td height="20" align="center" class="quick-reply-form-caption windowbg">
            <b><?= $this->locale->QuickReply3 ?></b>
          </td>
        </tr>
        <tr>
          <td width="85%" valign="top" align="center" class="windowbg">
            <form method="post" action="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $this->thread ?>/reply/" name="postmodify" onSubmit="submitonce(this);">
              <br>
              <?php if($this->quickReplyExtendedForm): ?>
                <table border=0 width="100%" cellpadding="3" class="quick-reply-form-moderator-actions">
                  <tr>
                    <td style="width:60%;">
                      <font size="1" class="imgwindowbg"><?= $this->locale->rename_theme_to ?></font>
                    </td>
                    <td style="width:20%;">
                      <font size="1" class="imgwindowbg"><?= $this->locale->txt[132] ?></font>
                    </td>
                    <td style="width:20%;">
                      <font size="1" class="imgwindowbg"><?= $this->locale->txt[42] ?></font>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <input type="text" name="csubject" value="" placeholder="<?= $this->locale->rename_theme_to ?>" tabindex="4" maxlength="80" style="width:100%;">
                    </td>
                    <td>
                      <select name="movethread" tabindex="5" style="width:100%;">
                      <?php foreach($this->jumptoform as $jumpcatid => $jumpcat): ?>
                        <option value="">-----------------------------</option>
                        <option value="#<?= $jumpcatid ?>"><?= $this->esc($jumpcat['name']) ?></option>
                        <option value="">-----------------------------</option>
                        <?php foreach($jumpcat['boards'] as $jumpboardid => $jumpboard): ?>
                          <?php if($jumpboard['current']): ?>
                            <option value="<?= $jumpboardid ?>" selected="selected"> =><?= $this->esc($jumpboard['name']) ?></option>
                          <?php else: ?>
                            <option value="<?= $jumpboardid ?>"> =><?= $this->esc($jumpboard['name']) ?></option>
                          <?php endif; ?>
                        <?php endforeach; ?>
                      <?php endforeach; ?>
                      </select>
                    </td>
                    <td>
                      <select name="modaction" tabindex="6" style="width:100%;">
                        <option value=1>Просто ответить</option>
                        <option value=2>Запереть тему</option>
                        <option value=3>Прикрепить тему</option>
                        <option value=4>Прикрепить и запереть</option>
                      </select>
                    </td>
                  </tr>
                </table>
              <?php endif; /* extended form */ ?>
              
              <input type="hidden" name="naztem" value="<?= $this->esc($this->title) ?>" />
              <input type="hidden" name="serial" value="<?= $this->conf->serial ?>" />
              <input type="hidden" name="icon" value="xx" />
              <input type="hidden" name="sc" value="<?= $this->sessionid ?>" />
                
              <table id="quick-reply-form-block" width="100%" border="0" height="150" align="center" cellpadding="0" cellspacing="0">
                <tbody align="left" valign="bottom">
                  <tr>
                    <td class="quick-reply-form-bbcode-btns">
                      <table cellspacing="0" cellpadding="0">
                        <tbody align="left" valign="bottom">
                          <tr>
                            <td width="23" class="bbc-btn">
                              <a href="javascript:surroundText('[b]','[/b]')"><img src="<?= $this->conf->imagesdir ?>/bold.gif" align="bottom" width="23" height="22" alt="Жирный" border="0" /></a>
                            </td>
                            <td width="23" class="bbc-btn">
                              <a href="javascript:surroundText('[i]','[/i]')"><img src="<?= $this->conf->imagesdir ?>/italicize.gif" align="bottom" width="23" height="22" alt="Курсив" border="0" /></a>
                            </td>
                            <td width="23" class="bbc-btn">
                              <a href="javascript:surroundText('[u]','[/u]')"><img src="<?= $this->conf->imagesdir ?>/underline.gif" align="bottom" width="23" height="22" alt="Подчеркивание" border="0" /></a>
                            </td>
                            <td width="23" class="bbc-btn">
                              <a href="javascript:surroundText('[s]','[/s]')"><img src="<?= $this->conf->imagesdir ?>/strike.gif" align="bottom" width="23" height="22" alt="Перечеркивание" border="0" /></a>
                            </td>
                          </tr>
                          <tr>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[sup]','[/sup]')"><img src="<?= $this->conf->imagesdir ?>/sup.gif" align="bottom" width="23" height="22" alt="Надстрочный индекс" border="0" /></a>
                            </td>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[sub]','[/sub]')"><img src="<?= $this->conf->imagesdir ?>/sub.gif" align="bottom" width="23" height="22" alt="Приписка" border="0" /></a>
                            </td>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[move]','[/move]')"><img src="<?= $this->conf->imagesdir ?>/move.gif" align="bottom" width="23" height="22" alt="Marquee-" border="0" /></a>
                            </td>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[size=3]','[/size]')"><img src="<?= $this->conf->imagesdir ?>/size.gif" align="bottom" width="23" height="22" alt="Размер шрифта" border="0" /></a>
                            </td>
                          </tr>
                          <tr>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[font=Comic Sans MS]','[/font]')"><img src="<?= $this->conf->imagesdir ?>/face.gif" align="bottom" width="23" height="22" alt="Название шрифта" border="0" /></a>
                            </td>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[nb]','[/nb]')"><img src="<?= $this->conf->imagesdir ?>/nb.gif" align="bottom" width="23" height="22" alt="[nb]" border="0" /></a>
                            </td>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[scroll]','[/scroll]')"><img src="<?= $this->conf->imagesdir ?>/scroll.gif" align="bottom" width="23" height="22" alt="[scroll]" border="0" /></a>
                            </td>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[pre]','[/pre]')"><img src="<?= $this->conf->imagesdir ?>/pre.gif" align="bottom" width="23" height="22" alt="Предварительно сформатированный текст" border="0" /></a>
                            </td>
                          </tr>
                          <tr>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[left]','[/left]')"><img src="<?= $this->conf->imagesdir ?>/left.gif" align="bottom" width="23" height="22" alt="Выравнивание влево" border="0" /></a>
                            </td>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[center]','[/center]')"><img src="<?= $this->conf->imagesdir ?>/center.gif" align="bottom" width="23" height="22" alt="По центру" border="0" /></a>
                            </td>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[right]','[/right]')"><img src="<?= $this->conf->imagesdir ?>/right.gif" align="bottom" width="23" height="22" alt="Выравнивание вправо" border="0" /></a>
                            </td>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[quote]','[/quote]')"><img src="<?= $this->conf->imagesdir ?>/quote2.gif" align="bottom" width="23" height="22" alt="Цитирование" border="0" /></a>
                            </td>
                          </tr>
                          <tr>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[news=Внезапно!]','[/news]')"><img src="<?= $this->conf->imagesdir ?>/1news.gif" align="bottom" width="23" height="22" alt="Вставить копипасту" border="0" /></a>
                            </td>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[hidden]','[/hidden]')"><img src="<?= $this->conf->imagesdir ?>/hid.gif" align="bottom" width="23" height="22" alt="Спрятать" border="0" /></a>
                            </td>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[media=640,360]','[/media]')"><img src="<?= $this->conf->imagesdir ?>/media.gif" align="bottom" width="23" height="22" alt="Вставить медиа контент" border="0" /></a>
                            </td>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[youtube=640,360]','[/youtube]')"><img src="<?= $this->conf->imagesdir ?>/yt.gif" align="bottom" width="23" height="22" alt="Вставить ролик на YouTube" border="0" /></a>
                            </td>
                          </tr>
                          <tr>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[audio]','[/audio]')"><img src="<?= $this->conf->imagesdir ?>/audio.gif" align="bottom" width="23" height="22" alt="Вставить ссылку на аудио" border="0" /></a>
                            </td>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[video]','[/video]')"><img src="<?= $this->conf->imagesdir ?>/video.gif" align="bottom" width="23" height="22" alt="Вставить видео" border="0" /></a>
                            </td>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[url]','[/url]')"><img src="<?= $this->conf->imagesdir ?>/url.gif" align="bottom" width="23" height="22" alt="Вставить гиперссылку" border="0" /></a>
                            </td>
                            <td class="bbc-btn">
                              <a href="javascript:surroundText('[img]','[/img]')"><img src="<?= $this->conf->imagesdir ?>/img.gif" align="bottom" width="23" height="22" alt="Вставить картинку" border="0" /></a>
                            </td>
                          </tr>
                        </tbody>
                      </table> <!-- .quick-reply-form-bbcode-btns -->
                    </td>
                    <td>
                      <textarea class="editor" name="message" cols="100" rows="6" style="width: 98%; height: 150px;" id="QUICKREPLYAREA" tabindex="1"></textarea>
                    </td>
                    <td class="quick-reply-form-smile-btns">
                      <table cellpadding="0" cellspacing="0">
                        <tbody align="left" valign="bottom">
                        <tr>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20;D')"><img src="<?= $this->conf->imagesdir ?>/grin.gif" alt="Усмешка" align="bottom" border="0"></a>
                          </td>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20:)')"><img src="<?= $this->conf->imagesdir ?>/smiley.gif" alt="Улыбчивый" align="bottom" border="0"></a>
                          </td>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20;)')"><img src="<?= $this->conf->imagesdir ?>/wink.gif" alt="Миг" align="bottom" border="0"></a>
                          </td>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20:D')"><img src="<?= $this->conf->imagesdir ?>/cheesy.gif" alt="Cheesy" align="bottom" border="0"></a>
                          </td>
                        </tr>
                        <tr>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20:-*')"><img src="<?= $this->conf->imagesdir ?>/kiss.gif" alt="поцелуй" align="bottom" border="0"></a>
                          </td>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%208-)')"><img src="<?= $this->conf->imagesdir ?>/cool.gif" alt="Круто!" align="bottom" border="0"></a>
                          </td>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20::)')"><img src="<?= $this->conf->imagesdir ?>/rolleyes.gif" alt="Бегающие глаза" align="bottom" border="0"></a>
                          </td>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20:-O')"><img src="<?= $this->conf->imagesdir ?>/singing.gif" alt="Поющий" align="bottom" border="0"></a>
                          </td>
                        </tr>
                        <tr>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20:o')"><img src="<?= $this->conf->imagesdir ?>/shocked.gif" alt="Потрясение" align="bottom" border="0"></a>
                          </td>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20???')"><img src="<?= $this->conf->imagesdir ?>/huh.gif" alt="Ух!" align="bottom" border="0"></a>
                          </td>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20:-\\\\')"><img src="<?= $this->conf->imagesdir ?>/undecided.gif" alt="нерешенный" align="bottom" border="0"></a>
                          </td>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20:(')"><img src="<?= $this->conf->imagesdir ?>/sad.gif" alt="Печаль" align="bottom" border="0"></a>
                          </td>
                        </tr>
                        <tr>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20:hm:')"><img src="<?= $this->conf->imagesdir ?>/hm.gif" alt="Хм" align="bottom" border="0"></a>
                          </td>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20:-X')"><img src="<?= $this->conf->imagesdir ?>/lipsrsealed.gif" alt="рот на замке" align="bottom" border="0"></a>
                          </td>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20:P')"><img src="<?= $this->conf->imagesdir ?>/tongue.gif" alt="Язык" align="bottom" border="0"></a>
                          </td>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20:-[')"><img src="<?= $this->conf->imagesdir ?>/embarassed.gif" alt="смущенно" align="bottom" border="0"></a>
                          </td>
                        </tr>
                        <tr>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20:\'(')"><img src="<?= $this->conf->imagesdir ?>/cry.gif" alt="Плач" align="bottom" border="0"></a>
                          </td>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20>:(')"><img src="<?= $this->conf->imagesdir ?>/angry.gif" alt="Сердитый" align="bottom" border="0"></a>
                          </td>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20:-#')"><img src="<?= $this->conf->imagesdir ?>/sleep.gif" alt="Сплю" align="bottom" border="0"></a>
                          </td>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20:F')"><img src="<?= $this->conf->imagesdir ?>/facepalm.gif" alt="Пальма лица" title="Пальма лица" align="bottom" border="0"></a>
                          </td>
                        </tr>
                        <tr>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20:ykypok:')"><img src="<?= $this->conf->imagesdir ?>/insane.gif" alt="Дурак" align="bottom" border="0"></a>
                          </td>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20:№')"><img src="<?= $this->conf->imagesdir ?>/crazy.gif" alt="Безумец" align="bottom" border="0"></a>
                          </td>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20:[-')"><img src="<?= $this->conf->imagesdir ?>/rusty.gif" alt="Рыжий" align="bottom" border="0"></a>
                          </td>
                          <td width="20" height="25" class="smile-btn">
                            <a href="javascript:replaceText('%20;]')"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/trollface.gif" alt="Троль" title="Троль" align="bottom" border="0"></a>
                          </td>
                        </tr>
                      </tbody>
                    </table> <!-- .quick-reply-form-smile-btns -->
                  </td>
                </tr>
              </tbody>
            </table> <!-- #quick-reply-form-block -->
            <br>
            <input type="hidden" name="waction" value="post">
            <input type="submit" name="post" value="Ответить" accesskey="s" tabindex="2">
            <input type="reset" value="Стереть" accesskey="r" tabindex="3">
            <input type="button" onclick="javascript: tinyMCE.execCommand('mceToggleEditor',false,'QUICKREPLYAREA');void(0);" value="WYSIWYG">
            </form>
          </td>
        </tr>
        <tr class="reply_instr">
          <td width="15%" valign="top" class="windowbg">
            <br>
            <font size="1"><?= $this->locale->QuickReply4 ?>
              Нажми "Ctrl" и кликни "Процитировать" для цитирования сообщения в форму быстрого ответа.
              Для цитирования выделенного текста в форму быстрого ответа выдели мышкой текст и кликни "Процитировать".
              Сообщение можно отправить нажатием ctrl+enter.
            </font>
          </td>
        </tr>
      </table>
      <br>
