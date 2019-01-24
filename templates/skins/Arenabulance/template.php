<!DOCTYPE html>
<html>
<head>
<title><?= $this->esc($this->title) ?></title>
<meta charset="windows-1251">
<meta name=Description content="TheProdigy.ru - эксклюзивный сайт, посвященный легендарной британской команде The Prodigy. Полная и достоверная информация о группе и свежие новости. Аудио файлы для скачивания.">
<meta content="Prodigy, The Prodigy, Продиджи, новый трэк Prodigy, Baby's got a temper, baby got a temper, Prodigy, audio, Prodigy mp3, Always Outnumbered, Never Outgunned, BGAT, Prodigy single, Prodigy forum, Prodigy audio, The Prodigy, Prodigy, новая песня Prodigy" name=Keywords>
<meta name="revisit-after" content="1 day">
<meta name="ROBOTS" content="ALL">
<meta name="rating" content="General">
<meta name="Other.Language" content="Russian">

<?php /* <yabb head> */ ?>
<?php $this->partial(PROJECT_ROOT.'/templates/head.php'); ?>

<style type=text/css>
A:link {
	COLOR: #469EC0;
	BACKGROUND-COLOR: transparent;
	TEXT-DECORATION: none
}
A:visited {
	COLOR: #469EC0;
	BACKGROUND-COLOR: transparent;
	TEXT-DECORATION: none
}
A:hover {
	COLOR: #ACD5E3;
	BACKGROUND-COLOR: transparent;
	TEXT-DECORATION: underline
}
.nav {
	COLOR: #CCCCCC;
	BACKGROUND-COLOR: #333333;
	TEXT-DECORATION: none
}
.nav:link {
	COLOR: #469EC0;
	BACKGROUND-COLOR: transparent;
	TEXT-DECORATION: none
}
.nav:visited {
	COLOR: #469EC0;
	BACKGROUND-COLOR: transparent;
	TEXT-DECORATION: none
}
.nav:hover {
	COLOR: #F97BA4;
	BACKGROUND-COLOR: transparent;
	TEXT-DECORATION: underline
}
BODY {
	FONT-SIZE: 12px;
	FONT-FAMILY: Verdana, arial, helvetica, serif;
	background-color: #000000;
}
TABLE {
	empty-cells: show
}
TD {
	FONT-SIZE: 12px;
	COLOR: #FFFFFF;
	FONT-FAMILY: Verdana, arial, helvetica, serif
}
INPUT {
	FONT-SIZE: 9pt;
	COLOR: #000000;
	FONT-FAMILY: Verdana,arial, helvetica, serif;
	BACKGROUND-COLOR: #999999
}
TEXTAREA {
	FONT-SIZE: 9pt;
	COLOR: #000000;
	FONT-FAMILY: Verdana,arial, helvetica, serif;
	BACKGROUND-COLOR: #999999
}
SELECT {
	FONT-SIZE: 7pt;
	COLOR: #000000;
	FONT-FAMILY: Verdana,arial, helvetica, serif;
	BACKGROUND-COLOR: #666666
}
.windowbg {
	FONT-SIZE: 12px;
	COLOR: #CCCCCC;
	FONT-FAMILY: Verdana, arial, helvetica, serif;
	BACKGROUND-COLOR: #333333
}
.windowbg2 {
	FONT-SIZE: 12px;
	COLOR: #CCCCCC;
	FONT-FAMILY: Verdana, arial, helvetica, serif;
	BACKGROUND-COLOR: #333333
}
.windowbg3 {
	FONT-SIZE: 12px;
	COLOR: #CCCCCC;
	FONT-FAMILY: Verdana, arial, helvetica, serif;
	BACKGROUND-COLOR: #333333
}
.calendar {
	FONT-SIZE: 10px;
	COLOR: #CCCCCC;
	FONT-FAMILY: Verdana, arial, helvetica, serif;
}
.hr {
	COLOR: #999999;
	BACKGROUND-COLOR: transparent
}
.titlebg {
	COLOR: #CCCCCC;
	BACKGROUND-COLOR: #333333
}
.text1 {
	FONT-WEIGHT: bold;
	FONT-SIZE: 12px;
	COLOR: #CCCCCC;
	FONT-STYLE: normal;

}
.catbg {
/* 	FONT-WEIGHT: bold; */
	FONT-SIZE: 13px;
	COLOR: #CCCCCC;
	background-color: #333333;
}
.bordercolor {
	FONT-SIZE: 12px;
	FONT-FAMILY: Verdana, arial, helvetica, serif;
	BACKGROUND-COLOR: #000000;
	color: #CCCCCC;
}
.quote {
	FONT-SIZE: 10px;
	COLOR: #CCCCCC;
	FONT-FAMILY: Arial, verdana, helvetica, serif;
	BACKGROUND-COLOR: #595959;
	border: #808080;
}
.code {
	FONT-SIZE: 10px;
	COLOR: #CCCCCC;
	FONT-FAMILY: Courier New, helvetica, Times New Roman, serif;
	BACKGROUND-COLOR: #797979;
	border: #808080;
}
.spoiler {
	FONT-SIZE: 10px;
	COLOR: #CCCCCC;
	FONT-FAMILY: Arial, verdana, helvetica, serif;
	BACKGROUND-COLOR: #595959;
	border: #808080 solid 1px;
	padding: 3px;
}
.help {
	CURSOR: help; BACKGROUND-COLOR: transparent
}
.meaction {
	COLOR: red; BACKGROUND-COLOR: transparent
}
.editor {
	width : 100%
}

div.signature
{
/*   width: 100%;
   height: 100%;
   overflow: hidden; */
}
div.avatar
{
   width: 128px;
   height: 128px;
   overflow: hidden;
}

body,td,th {
	color: #CCCCCC;
}

.qpollbtn {
  background-color: #666666;
  border: 0px;
}
</style>

<?php if ($this->mobileMode): ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="<?= STATIC_ROOT ?>/includes/mobile.css?v=1521975872"/>
    <?php /* <yabb mobile> */ ?>
<?php endif; ?>

</head>
<body>
<?php $this->partial('templates/body.inc.php'); // <yabb body> ?>

<table class=bordercolor cellSpacing=0 cellPadding=0 width="<?= ($this->mobileMode?'100%':'92%') ?>" align=center bgColor=#333333 border=0>
  <tbody>
  <tr>
    <td>
      <table class=bordercolor cellSpacing=1 cellPadding=0 width="100%"  align=center bgColor=#333333 border=0>
        <tbody>
        <tr>
          <td>
            <table cellSpacing=0 cellPadding=0 width="100%" align=center
            border=0>
              <tbody>
                <tr>
                  <td class="logo-block" vAlign="center" align="left" bgColor="#000000" style="line-height: 0px;"><a href="<?= SITE_ROOT ?>/"><img src="<?= STATIC_ROOT ?>/skins/<?= $this->skinname ?>/YaBBImages/We_are_the_Prodigy.png" width="560" height="129" border="0"></a></td>
                  <td vAlign=center bgColor=#000000>
                    <font size=2>
                      <?= $this->yyuname ?> <?php $this->partial('templates/im_head.php') ?><br>
                      <?= $this->yytime ?><?php /* <yabb time> */ ?>
                    </font>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
        <tr vAlign=center align=middle bgColor=#b7bbda>
          <td bgColor=#DAE0E8 class="windowbg menubar">
            <font size=1>
              <nav><?php $this->partial('templates/yymenu.php') ?></nav>
            </font>
          </td>
        </tr>
        <!--<tr>
          <td width="100%" bgcolor="#333333" height="24">
            <yabb news>
          </td>
        </tr>-->
        </tbody>
      </table>
    </td>
  </tr>
  </tbody>
</table>
<br>

<?php if(!empty($this->app->conf->banner_top_enabled) && !empty($this->app->banner_top)): ?>
  <!-- event banner -->
  <div class="event-banner">
    <?= $this->app->conf->banner_top ?>
  </div>
  <!-- end of event banner -->
<?php endif; ?>

<table cellSpacing=0 cellPadding=0 width="<?= ($this->mobileMode?'100%':'90%') ?>" align=center bgColor=#333333  border=0>
  <tbody>
    <tr>
      <td>
        <table cellSpacing=1 cellPadding=0 width="100%" align=center bgColor=#333333 border=0>
          <tbody>
            <tr>
              <td>
                <table cellSpacing=0 cellPadding=10 width="100%" align=center bgColor=#333333 border=0>
                  <tbody>
                    <tr>
                      <td vAlign=top width="100%">
                        <?php $this->yieldView(); ?>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
            </tr>
          </tbody>
       </table>
     </td>
   </tr>
  </tbody>
</table>

<table cellSpacing=0 cellPadding=0 width="<?php echo ($this->mobileMode?'100%':'90%'); ?>" align=center bgColor=#333333 border=0>
  <tbody>
    <tr>
      <td>
        <table cellSpacing=1 cellPadding=0 width="100%" align=center bgColor=#333333 border=0>
          <tbody>
            <tr>
              <td>
                <table cellSpacing=0 cellPadding=3 width="100%" bgColor=#000000 border=0>
                  <tbody>
                    <tr>
                      <td vAlign=center align=right width="25%" bgColor=#333333>&nbsp;</td>
                      <td vAlign=center align=middle width="50%" bgColor=#333333>
                        <?= $this->yycopyright ?>
                      </td>
                      <td vAlign=center align=left width="25%" bgColor=#333333>&nbsp;</td>
                    </tr>
                    <tr>
                      <td align="center" colspan="3">
                        <?php $this->partial(PROJECT_ROOT . "/templates/counters.php"); ?>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
  </tbody>
</table>
<?php $this->partial(PROJECT_ROOT.'/templates/footer.php'); ?>
</body>
</html>
