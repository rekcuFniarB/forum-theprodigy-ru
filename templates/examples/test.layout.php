<!DOCTYPE html>
<html>
<head>
<title>Testing template</title>
<?php /* <yabb head> */ ?>
<?php //$this->partial(PROJECT_ROOT.'/templates/head.php'); ?>
<style type="text/css">
.titlebg      { backgrou-color: #777;}
.text1        { font-style: normal; font-weight: bold; font-size: 12px; color: #FFFFFF; }
.catbg        { background-color: #808080; color: #FFFFFF; font-size: 13px; }
.bordercolor  { background-color: #606060; }
/* Image fonts */
.imgbg        {font-size: 10px; color: #000060;}
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

</style>

</head>
<body>
<?php //$this->partial(PROJECT_ROOT . 'body.inc.php'); // <yabb body> ?>
<br>
<header>
<table width="92%" cellspacing="1" cellpadding="0" border="0" align="CENTER" class="bordercolor" role="presentation">
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
                <?= $this->yyuname ?> <?php //$this->partial('im_head.php') ?><BR>
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
                <td valign="middle" bgcolor="#A0A0A0" align="center" class="menubar"><font size="1"><nav><?php //$this->partial('yymenu.php') ?></nav></font></td>
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
    <td align="center"><?php //$this->partial(PROJECT_ROOT . "/templates/counters.php"); ?></td>
  </tr>
</table>
<?php //$this->partial(PROJECT_ROOT.'/templates/footer.php'); ?>
</footer>
</body>
</html>
