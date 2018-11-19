                    <?php $this->start_cache('post_comments_format'); ?>
                      <?php foreach ($msg['comments']['comments'] as $ln => $comment): ?>
                        <div class="comment" id="comment<?= $msgid ?>-<?= $ln ?>">
                          <div class="comment-content">
                            <a href="<?= SITE_ROOT ?>/people/<?= urlencode($comment['username']) ?>" <?= $comment['userinfo']['banned'] ? 'class="banned-user"' : '' ?> data-userid="<?= $comment['userinfo']['ID_MEMBER'] ?>"><b><?= $this->esc($comment['userinfo']['realName']) ?></b></a>:
                            <?= $this->DoUBBC($comment['comment'], 'links,inline,emoji') ?>
                            <div class="comment-control">
                              <a class="comment-date" href="<?= SITE_ROOT ?>/<?= $msgid ?>-<?= $ln ?>" rel="nofollow"><?= $this->app->subs->timeformat($comment['date']); ?></a>
                              <div class="comment-buttons">
                                <?php if($this->user->name != 'Guest'): ?>
                                  <a href="<?= SITE_ROOT ?>/b<?= $this->board ?>/t<?= $this->thread ?>/msg<?= $msgid ?>/quotecomment/<?= $ln ?>/?sesc=<?= $this->app->session->id ?>" onclick="return quickQuoteComment(event, this)"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/quote.png" alt="цитировать" title="цитировать комментарий" border="0" width="11" height="11"></a>
                                <?php endif; ?>
                                <?php if($this->user->accessLevel() > 1 || $comment['allow_modify']): ?>
                                  <a href="#" onclick="Forum.Utils.MessageCommentsForm.modify(<?= $msgid ?>, <?= $ln ?>, <?= $comment['date'] ?>, <?= $this->board ?>, event)"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/edit.png" alt="править" title="Редактировать комментарий" border="0" width="10" height="10"></a>
                                <?php endif; ?>
                                <?php if ($this->user->accessLevel() > 1 || $this->user->name == $comment['username']): ?>
                                  <a href="javascript: deletePostComment(<?= $msgid ?>, <?= $ln ?>, <?= $this->board ?>, <?= $this->thread ?>, <?= $this->start ?>, '<?= $this->app->session->id ?>'); void(0);"><img border="0" src="<?= STATIC_ROOT ?>/img/YaBBImages/delete.png" alt="удалить" title="удалить комментарий" width="10" height="10" /><img id="loading<?= $msgid ?>-<?= $ln ?>" border="0" src="<?= STATIC_ROOT ?>/img/YaBBImages/loading.gif" width="10" height="10" style="display: none" /></a>
                                <?php endif; ?>
                              </div>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php $this->end_cache(); ?>

                  <?php if($this->user->showComments == 1 or $this->user->name == "Guest"): ?>
                    <hr width="100%" size="1" />
                    <?php if($this->user->name != "Guest"): ?>
                      <div>
                        <a href="javascript: Forum.Utils.MessageCommentsForm.showHide(<?= $msgid ?>);" class="msgCommentBtn">комментировать (<?= $msg['comments']['comment_count'] ?>)</a>
                        <?= $this->menusep ?>
                        <?php if($msg['comments']['subscribed']): ?>
                          <a href="<?= SITE_ROOT ?>/comments/unsubscribe/<?= $msgid ?>/" onclick="return Forum.Utils.MessageCommentsForm.unsubscribe(<?= $msgid ?>, this);" class="msgCommentsUnsub" title="отписаться от оповещений о новых комментариях к этому сообщению">отписаться</a>
                        <?php else: ?>
                          <a href="<?= SITE_ROOT ?>/comments/subscribe/<?= $msgid ?>/" onclick="return Forum.Utils.MessageCommentsForm.subscribe(<?= $msgid ?>, this);" class="msgCommentsSub" title="подписаться на оповещения о новых комментариях к этому сообщению">подписаться</a>
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>
                    
                    <div style="display: none; margin: 10px 0 0 0" id="commentForm<?= $msgid ?>">
                      <form action="<?= SITE_ROOT ?>/comments/submit/" onsubmit="comment(<?= $msgid ?>, <?= $this->board ?>, <?= $this->thread ?>, <?= $this->start ?>, '<?= $this->app->session->id ?>'); return false;">
                        <div class="commentBox">
                          <input type="text" id="commentBox<?= $msgid ?>" maxlength="256" autocomplete="off" style="display: <?= $msg['cmnt_display'] ?>;">
                        </div>
                        <div class="commentBoxTotalLength">
                          <input type="text" value="<?= $msg['comments']['remaining_char_count'] ?>" id="commentBoxTotalLength<?= $msgid ?>" style="text-align: center; display: <?= $msg['cmnt_display'] ?>;" size="5" readonly/>
                        </div>
                        <div>
                          <input id="commentBtn<?= $msgid ?>" type="submit" value="&#8629;" style="display: <?= $msg['cmnt_display'] ?>;" />
                        </div>
                        
                        <?php if($msg['CLOSED_COMMENTS'] == 1): ?>
                            <?php if($this->user->accessLevel() > 1 or ($this->user->id == $msg['ID_MEMBER'] && $this->user->id != -1)): ?>
                                <div class="commentLockBtn">
                                  <a href="javascript: lockUnlock(<?= $msgid ?>, <?= $this->board ?>); void(0);"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/lock_closed.png" alt="закрыть/открыть комментирование" border="0" title="закрыть/открыть комментирование" id="lock<?= $msgid ?>" width="18" height="18"/></a>
                                  <img src="<?= STATIC_ROOT ?>/img/YaBBImages/loading.gif" height="10" width="10" id="lockloading<?= $msgid ?>" style="display: none;" />
                                </div>
                            <?php else: ?>
                                <div class="commentLockBtn">
                                  <img src="<?= STATIC_ROOT ?>/img/YaBBImages/lock_closed.png" alt="статус комментирования" title="статус комментирования" border="0" width="18" height="18"/>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if($this->user->accessLevel() > 1 or ($this->user->id == $msg['ID_MEMBER'] && $this->user->id != -1)): ?>
                                <div class="commentLockBtn">
                                  <a href="javascript: lockUnlock(<?= $msgid ?>, <?= $this->board ?>); void(0);"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/lock_open.png" alt="открыть/закрыть комментирование" id="lock<?= $msgid ?>" border="0" width="18" height="18"/></a>
                                  <img src="<?= STATIC_ROOT ?>/img/YaBBImages/loading.gif" height="10" width="10" id="lockloading<?= $msgid ?>" style="display: none;" />
                                </div>
                            <?php endif; ?>
                        <?php endif; /* closed comments */ ?>
                      </form>
                    </div>
                    
                    <div id="comments<?= $msgid ?>" class="comments">
                      <?php $this->get_cache('post_comments_format'); ?>
                    </div>
                      
                  <?php else: /* hidden comments */ ?>
                    <hr width="100%" size="1" />
                    <div>
                      <a href="javascript: Forum.Utils.MessageCommentsForm.showHide(<?= $msgid ?>);" class="msgCommentBtn">комментариев (<?= $msg['comments']['comment_count'] ?>)</a>
                      <?= $this->menusep ?>
                        <?php if($msg['comments']['subscribed']): ?>
                          <a href="<?= SITE_ROOT ?>/comments/unsubscribe/<?= $msgid ?>/" onclick="return Forum.Utils.MessageCommentsForm.unsubscribe(<?= $msgid ?>, this);" class="msgCommentsUnsub" title="отписаться от оповещений о новых комментариях к этому сообщению">отписаться</a>
                        <?php else: ?>
                          <a href="<?= SITE_ROOT ?>/comments/subscribe/<?= $msgid ?>/" onclick="return Forum.Utils.MessageCommentsForm.subscribe(<?= $msgid ?>, this);" class="msgCommentsSub" title="подписаться на оповещения о новых комментариях к этому сообщению">подписаться</a>
                        <?php endif; ?>
                    </div>
                    <div style="display: none; margin: 10px 0 0 0" id="commentForm<?= $msgid ?>">
                      <form action="<?= SITE_ROOT ?>/comments/submit/" onsubmit="comment(<?= $msgid ?>, <?= $this->board ?>, <?= $this->thread ?>, <?= $this->start ?>, '<?= $this->app->session->id ?>'); return false;">
                        <div class="commentBox">
                          <input type="text" id="commentBox<?= $msgid ?>" maxlength="256" autocomplete="off" style="display: <?= $msg['cmnt_display'] ?>;" />
                        </div>
                        <div class="commentBoxTotalLength">
                          <input type="text" value="<?= $msg['comments']['remaining_char_count'] ?>" id="commentBoxTotalLength<?= $msgid ?>" style="text-align: center; display: <?= $msg['cmnt_display'] ?>;" size="5" readonly/>
                        </div>
                        <div>
                          <input id="commentBtn<?= $msgid ?>" type="submit" value="&#8629;" style="display: <?= $msg['cmnt_display'] ?>;" />
                        </div>
                          <?php if($msg['CLOSED_COMMENTS'] == 1): ?>
                              <?php if($this->user->accessLevel() > 1 or ($this->user->id == $msg['ID_MEMBER'] && $this->user->id != -1)): ?>
                                  <div class="commentLockBtn">
                                    <a href="javascript: lockUnlock(<?= $msgid ?>, <?= $this->board ?>); void(0);"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/lock_closed.png" alt="закрыть/открыть комментирование" title="закрыть/открыть комментирование" border="0" id="lock<?= $msgid ?>" width="18" height="18"/></a>
                                    <img src="<?= STATIC_ROOT ?>/img/YaBBImages/loading.gif" height="10" width="10" id="lockloading<?= $msgid ?>" style="display: none;" />
                                  </div>
                              <?php else: ?>
                                  <div class="commentLockBtn"><img src="/YaBBImages/lock_closed.png" alt="статус комментирования" title="статус комментирования" border="0" width="18" height="18"/></div>
                              <?php endif; ?>
                            )
                          <?php else: ?>
                              <?php if($this->user->accessLevel() > 1 or ($this->user->id == $msg['ID_MEMBER'] && $this->user->id != -1)): ?>
                                  <div class="commentLockBtn">
                                    <a href="javascript: lockUnlock(<?= $msgid ?>, <?= $this->board ?>; void(0);"><img src="<?= STATIC_ROOT ?>/img/YaBBImages/lock_open.png" alt="статус комментирования" border="0" id="lock<?= $msgid ?>" width="18" height="18"/></a>
                                    <img src="<?= STATIC_ROOT ?>/img/YaBBImages/loading.gif" height="10" width="10" id="lockloading<?= $msgid ?>" style="display: none;" />
                                  </div>
                              <?php endif; ?>
                          <?php endif; ?>
                      </form>
                    </div>
                    <div id="comments<?= $msgid ?>" class="comments comments-collapsed">
                      <?php $this->get_cache('post_comments_format'); ?>
                    </div>
                  <?php endif; ?>
