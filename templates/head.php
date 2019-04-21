<link rel="shortcut icon" href="<?= STATIC_ROOT ?>/img/YaBBImages/proant.ico">
<link rel="apple-touch-icon" type="image/png" href="<?= STATIC_ROOT ?>/YaBBImages/apple-touch-icon-152x152.png">
<!--[if lt IE 7.]>
    <script defer type="text/javascript" src="<?= STATIC_ROOT ?>/js/pngfix.js"></script>
<![endif]-->
<link rel="stylesheet" type="text/css" href="<?= STATIC_ROOT ?>/css/theprodigy.ru.css?v=1519417894" />

<?php foreach($this->_include_css as $_css): ?>
  <link rel="stylesheet" type="text/css" href="<?= $_css ?>">
<?php endforeach; ?>

<script type="text/javascript" src="<?= STATIC_ROOT ?>/js/jquery-latest.js"></script>
<?php if (!$this->mobileMode): ?>
    <script type="text/javascript" src="<?= STATIC_ROOT ?>/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="<?= STATIC_ROOT ?>/js/jquery.flash.js"></script>
    <script type="text/javascript" src="<?= STATIC_ROOT ?>/js/jquery.bgiframe.js"></script>
    <script type="text/javascript" src="<?= STATIC_ROOT ?>/js/jquery.dimensions.js"></script>
    <script type="text/javascript" src="<?= STATIC_ROOT ?>/js/jquery.tooltip.min.js"></script>
    <script type="text/javascript" src="<?= STATIC_ROOT ?>/tinymce/jscripts/tiny_mce/tiny_mce.js"></script> 
    <script type="text/javascript" src="<?= STATIC_ROOT ?>/js/vkontakte-share.js?10" charset="windows-1251"></script>

    <script type="text/javascript">
    <!--  
        if ((navigator.appVersion.substring(0,1) == "5" && navigator.userAgent.indexOf('Gecko') != -1) || navigator.userAgent.search(/Opera/) != -1) {
            document.write('<META HTTP-EQUIV="pragma" CONTENT="no-cache">');
        }
        
        tinyMCE.init({
            theme : "advanced",
            mode : "none",
            plugins : "bbcode",
            theme_advanced_buttons1 : "bold,italic,underline,undo,redo,link,unlink,image,forecolor,styleselect,removeformat,cleanup,code",
            theme_advanced_buttons2 : "",
            theme_advanced_buttons3 : "",
            theme_advanced_toolbar_location : "bottom",
            theme_advanced_toolbar_align : "center",
            theme_advanced_styles : "Code=codeStyle;Quote=quoteStyle",
            content_css : "css/bbcode.css",
            entity_encoding : "raw",
            add_unload_trigger : false,
            remove_linebreaks : false,
            inline_styles : false,
            convert_fonts_to_spans : false
        });
        
        // On document load check for the new data.
        $(document).ready( function() {
            Forum.Data.actuality = '<?= $this->dbTime ?>';
            setInterval("Forum.Data.continuousUpdate()", Forum.Data.UPDATE_DELAY);
            
            $(".boardViewersPane img").tooltip({
                bodyHandler: function() {
                    return '<img src="<?=STATIC_ROOT ?>/img/YaBBImages/loading2.gif" alt="»дЄт загрузка..." title="»дЄт загрузка..." />';
                },
                showURL: false
            });
        } );
        
        // -->
    </script>
<?php endif; ?>

<script type="text/javascript" src="<?= STATIC_ROOT ?>/js/theprodigy.ru.js?v=1522010601"></script>
