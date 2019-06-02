<!DOCTYPE html>
<html>
<head>
<title><?= $this->esc($this->title) ?></title>
<meta charset="windows-1251">
<?php if(isset($GLOBALS['opengraph'])): ?>
  <meta name="twitter:card" content="summary" />
  <meta name="twitter:site" content="@theprodigy_ru" />
  <?php foreach($GLOBALS['opengraph'] as $prop => $val): ?>
    <meta property="og:<?= $prop ?>" content="<?= $val ?>" />
  <?php endforeach; ?>
<?php else: ?>
  <meta name=Description content="TheProdigy.ru - эксклюзивный сайт, посвященный легендарной британской команде The Prodigy. Полная и достоверная информация о группе и свежие новости. Аудио файлы для скачивания.">
  <meta content="Prodigy, The Prodigy, Продиджи, новый трэк Prodigy, Baby's got a temper, baby got a temper, Prodigy, audio, Prodigy mp3, Always Outnumbered, Never Outgunned, BGAT, Prodigy single, Prodigy forum, Prodigy audio, The Prodigy, Prodigy, новая песня Prodigy" name=Keywords>
<?php endif; ?>
<meta NAME="revisit-after" content="1 day">
<meta NAME="ROBOTS" content="ALL">
<meta NAME="rating" content="General">
<meta name="Other.Language" content="Russian">

<?php /* <yabb head> */ ?>
<?php $this->partial(PROJECT_ROOT.'/templates/head.php'); ?>

<style type="text/css">
<!--
/* General layout */
A:link        { font-weight: normal; text-decoration: none; color: #FFFFFF; }
A:visited     { text-decoration: none; color: #FFFFFF; font-weight: normal; }
A:hover       { text-decoration: none; color: #FFC020; }
BODY          { scrollbar-face-color: #D07010; scrollbar-shadow-color: #B05040; scrollbar-highlight-color: #FFA040;
                scrollbar-3dlight-color: #D07010; scrollbar-darkshadow-color: #000000;
                scrollbar-track-color: #E8882B; scrollbar-arrow-color: #000000; font-family: Verdana, Helvetica, Arial;
                font-size:12px; margin-top: 0; margin-left: 0; margin-right: 0; padding-top: 0; padding-left: 0;
                padding-right: 0;
                background-color: #666666; color: #FFFFFF;}
text          { font-family: Verdana, Helvetica, Arial; font-size: 11px; }
TD            { font-family: Verdana, Helvetica, Arial; color: #000000; font-size: 11px; }
input         { background-color: #BBBBBB; font-family: Verdana, Helvetica, Arial; font-size: 9pt; color: #000000; }
textarea      { background-color: #DDDDDD; font-family: Verdana, Helvetica, Arial; font-size: 9pt; color: #000000; }
select        { background-color: #A0A0A0; font-family: Verdana, Helvetica, Arial; font-size: 7pt; color: #000000; }
.copyright    { font-family: Verdana, Helvetica, Arial; font-size: 10px; }

/* YaBB navigation links */
.nav          { font-size: 10px; text-decoration: none; color: #FEFEFE; }
.nav:link     { font-size: 10px; text-decoration: none; color: #FEFEFE; }
.nav:visited  { font-size: 10px; text-decoration: none; color: #FEFEFE; }
.nav:hover    { font-size: 10px; text-decoration: none; color: #FFC020; }

/* YaBB alternating bgcolors */
.windowbg     { background-color: #888888; font-size: 11px; font-family: Verdana; color: #FFFFFF; }
.windowbg2    { background-color: #989898; font-size: 11px; font-family: Verdana; color: #FFFFFF; }

/* Messages and comments links */
.msgurl:link     { text-decoration: none; color: #000060; }
.msgurl:visited  { text-decoration: none; color: #000060; }
.msgurl:hover    { text-decoration: none; color: #000000; }


.windowbg3    { background-color: #808080; font-size: 11px; font-family: Verdana; color: #FFFFFF; }
.calendar    { font-size: 10px; font-family: Verdana; color: #FFFFFF; }

/* Misc./title/category colors */
.hr           { color: #909090; }
.titlebg      { background-color: #707070; color: #FFFFFF; }
.text1        { font-style: normal; font-weight: bold; font-size: 12px; color: #FFFFFF; }
.catbg        { background-color: #808080; color: #FFFFFF; font-size: 13px; }
.bordercolor  { background-color: #606060; }

/* Image fonts */
.imgbg        { font-style: normal; font-size: 10px; color: #000060; }
.imgcatbg     { font-style: normal; font-size: 10px; color: #000060; font-weight: bold; }
.imgtitlebg   { font-style: normal; font-size: 10px; color: #FFFFFF;  font-weight: bold; }
.imgwindowbg  { font-style: normal; font-size: 9px; color: #000060; }
.imgmenu      { font-style: normal; font-size: 11px; color: #000090; font-weight: bold; letter-spacing: 0.1em; }

/* Post quote/code colors */
.quote        { font-size: 10px; font-family: Verdana, Helvetica, Arial; color: #000000; background-color: #B0B0B0; }
.code         { font-size: 10px; font-family: Courier New; color: #000000; background-color: #CCCCCC; }
.spoiler {
	font-size: 10px; font-family: Verdana, Helvetica, Arial; color: #000000; background-color: #B0B0B0; border: #606060 solid 1px;
	padding: 3px;
}

div.signature
{
/*   width: 100%;
   height: 80;
   overflow: hidden; */
}
div.avatar
{
   width: 128px;
   height: 128px;
   overflow: hidden;
}
<?php /* <yabb style> */ ?>
-->
</style>
<?php if ($this->mobileMode): ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="<?= STATIC_ROOT ?>/includes/mobile.css?v=1521975872"/>
    <?php /* <yabb mobile> */ ?>
<?php endif; ?>

</head>
<body>
<?php $this->partial('templates/body.inc.php'); // <yabb body> ?>
<br>
<header>
<table width="<?= ($this->user->mobileMode?'100%':'92%') ?>" cellspacing="1" cellpadding="0" border="0" align="CENTER" class="bordercolor" role="presentation">
  <tr>
    <td class="bordercolor" width="100%">
      <table bgcolor="#FFFFFF" width="100%" cellspacing="0" cellpadding="0" role="presentation">
        <tr>
          <td>
            <table border="0" width="100%" cellpadding="0" cellspacing="0" bgcolor="#CCCCCC" role="presentation">
              <tr>
                <td class="logo-block" bgcolor="#CCCCCC" height="50" style="line-height: 0px;"><a href="<?= SITE_ROOT ?>/" target="_blank"><img src="<?= STATIC_ROOT ?>/skins/prodigy/YaBBImages/prodlogo.gif" alt="TheProdigy.ru" border="0"></a></td>
                <td bgcolor="#CCCCCC">
                <font size="2">
                <?= $this->yyuname ?> <?php $this->partial('templates/im_head.php') ?><BR>
                <?= $this->yytime ?><?php /* <yabb time> */ ?>
                </font><BR>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr><tr>
    <td class="bordercolor" align="center">
      <table bgcolor="#A0A0A0" width="100%" cellspacing="0" cellpadding="0" align="center" role="presentation">
        <tr>
          <td width="100%" align="center">
            <table border="0" width="100%" cellpadding="3" cellspacing="0" bgcolor="#A0A0A0" align="center" role="presentation">
              <tr>
                <td valign="middle" bgcolor="#A0A0A0" align="center" class="menubar"><font size="1"><nav><?php $this->partial('templates/yymenu.php') ?></nav></font></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr><tr>
    <td class="bordercolor" align="center">
      <table bgcolor="#FFFFFF" width="100%" cellspacing="0" cellpadding="0" align="center" role="presentation">
        <tr>
          <td width="100%" align="center">
            <table border="0" width="100%" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF" align="center" role="presentation">
              <tr>
                <td valign="middle" bgcolor="#FFFFFF" align="center">
                <font size="2" color="#707070"><B><?= $this->yyboardname ?></B>  « <?= $this->yyposition ?> »</font></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</header>
<BR>

<?php if(!empty($this->app->conf->banner_top_enabled) && !empty($this->app->banner_top)): ?>
  <!-- event banner -->
  <div class="event-banner">
    <?= $this->app->conf->banner_top ?>
  </div>
  <!-- end of event banner -->
<?php endif; ?>

<?= $this->infopane ?>

<table width="<?= $this->mobileMode?'100%':'92%' ?>" align="center" border="0" role="presentation">
  <tr>
    <td><font size="2">
    <?php $this->yieldView(); ?>

    </font></td>
  </tr>
</table>
<BR>
<footer>
<table width="<?= $this->mobileMode?'100%':'92%' ?>" align="center" border="0" role="presentation">
  <tr>
    <td align="center"><font class="copyright"><?= $this->yycopyright ?></font></td>
  </tr>

  <tr>
    <td align="center"><?php $this->partial(PROJECT_ROOT . "/templates/counters.php"); ?></td>
  </tr>
</table>
<?php $this->partial(PROJECT_ROOT.'/templates/footer.php'); ?>
</footer>
</body>
</html>
