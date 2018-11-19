<a href="<?= SITE_ROOT ?>/" class="button"><?= $this->locale->txt('home') ?></a>
<?= $this->menusep ?>
<a href="//www.theprodigy.ru/" class="button site"><?= $this->locale->txt('site') ?></a>
<?= $this->menusep ?>
<a href="<?= SITE_ROOT ?>/feed/"><?= $this->locale->txt('feed') ?></a>
<?= $this->menusep ?>
<a href="<?= SITE_ROOT ?>/<?= $this->search_action ?>/" class="button searchbtn"><?= $this->locale->txt('search') ?></a>

<?php if($this->user->group == 'Administrator'): ?>
  <?= $this->menusep ?>
  <a href="<?= SITE_ROOT ?>/admin/"><?= $this->locale->txt('admin') ?></a>
<?php elseif ($this->user->group == 'Global Moderator'): ?>
  <?= $this->menusep ?>
  <a href="<?= SITE_ROOT ?>/undelete/"><?= $this->locale->txt('undelete1') ?></a></span>
<?php else: ?>
  <?= $this->menusep ?>
  <a href="<?= SITE_ROOT ?>/register/" class="button rules"><?= $this->locale->txt('rules') ?></a>
<?php endif; ?>

<?php if ($this->user->name == 'Guest') : ?>
  <?= $this->menusep ?>
  <a href="<?= $this->app->conf->helpfile ?>" target="_blank"><?= $this->locale->txt('help') ?></a>
  <?= $this->menusep ?>
  <a href="<?= SITE_ROOT ?>/login/"><?= $this->locale->txt('login') ?></a>
  <?= $this->menusep ?>
  <a href="<?= SITE_ROOT ?>/register/"><?= $this->locale->txt('register') ?></a>
<?php else: ?>
  <?= $this->menusep ?>
  <a href="<?= SITE_ROOT ?>/people/<?= $this->e_username ?>/"><span class="dig_big">Профиль</span></a>
  <?= $this->menusep ?>
  <a href="<?= SITE_ROOT ?>/people/<?= $this->e_username ?>/edit/"><?= $this->locale->txt('profile') ?></a>
  
  <?php if ($this->conf->enable_notification): ?>
    <?= $this->menusep ?>
    <a href="<?= SITE_ROOT ?>/shownotify/" class="button notify"><?= $this->locale->txt('notification') ?></a>
  <?php endif; ?>
  
  <?php if ($this->conf->cal_enabled): ?>
    <?= $this->menusep ?>
    <a href="<?= SITE_ROOT ?>/calendar/" class="button calbtn"><?= $this->locale->txt('calendar') ?></a>
  <?php endif; ?>
                
  <?= $this->menusep ?>
  <a href="<?= SITE_ROOT ?>/<?= $this->conf->helpfile ?>" target="_blank" class="button help"><?= $this->locale->txt('help') ?></a>
  <?= $this->menusep ?>
  <a href="<?= SITE_ROOT ?>/logout/?sesc=<?= $this->app->session->id ?>"><?= $this->locale->txt('logout') ?></a>
<?php endif; ?>


<?php if($this->yyimbar): ?>
  <?= $this->menusep ?> <span class="yyimbar">
    <?php if ($this->imcount[1] > 0): ?>
      <a href="<?= SITE_ROOT ?>/im/" title="Новые приватные письма" class="imlink"><span class="value"><?= $this->imcount[1] ?></span><img src="<?= STATIC_ROOT ?>/img/YaBBImages/private_message.png"></a>
    <?php endif; ?>
    
    <?php if ($this->numUnreadComments > 0): ?>
      <a href="<?= SITE_ROOT ?>/people/<?= urlencode($this->user->name) ?>/recentcomments/" title="комментарии к твоему сообщению" class="numUnreadComments"><span class="value"><?= $this->numUnreadComments ?></span><img src="<?= STATIC_ROOT ?>/img/YaBBImages/sendpm.gif"></a>
    <?php endif; ?>
    
    <?php if ($this->numOtherComments > 0 ): ?>
      <a href="<?= SITE_ROOT ?>/subscribedmessagecomments/" title="комментарии к чужим сообщениям" class="numOtherComments"><span class="value"><?= $this->numOtherComments ?></span><img src="<?= STATIC_ROOT ?>/img/YaBBImages/addComment.png"></a>
    <?php endif; ?>
  </span>
<?php endif; ?>
