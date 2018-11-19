      <?php /* included from board_index.php template */ ?>
      <!-- News Fader -->
      <table border="0" width="100%" cellspacing="0" cellpadding="0" class="bordercolor"><tr><td>
        <table border="0" width="100%" cellspacing="1" cellpadding="5" class="bordercolor">
          <tr>
            <td class="titlebg" align="center">
              <b> <?= $this->locale->txt(102) ?></b>
            </td>
          </tr>
          <tr>
            <td class="windowbg2" valign="middle" align="center" height="170">
              <script language="javascript1.2" type="text/javascript">
                <!--
                var delay = <?= $this->conf->fadertime ?>;
                var bcolor = "<?= $this->conf->color['windowbg2'] ?>";
                var tcolor = "<?= $this->conf->color['fadertext2'] ?>";
                var fcontent = new Array();
                var begintag = '<font size="2"><b>';
                
                <?= $this->fcontent ?>
                
                var closetag = '<\/b><\/font>';
                // -->
              </script>
              
              <script language="javascript1.2" src="<?= $this->conf->faderpath ?>" type="text/javascript"></script>
              
              <script language="JavaScript1.2" type="text/javascript">
                <!--
                if (navigator.appVersion.substring(0, 1) < 5 && navigator.appName == "Netscape")
                  {
                    var fwidth = screen.availWidth / 2;
                    var bwidth = screen.availWidth / 4;
                    document.write('<ilayer id="fscrollerns" width=' + fwidth + ' height=35 left=' + bwidth + ' top=0><layer id="fscrollerns_sub" width=' + fwidth + ' height=35 left=0 top=0><\/layer><\/ilayer>');
                  }
                else if (navigator.userAgent.search(/Opera/) != -1 || (navigator.platform != "Win32" && navigator.userAgent.indexOf('Gecko') == -1))
                  {
                    /*document.open();
                    for (i=0;i<fcontent.length;++i)
                      {
                        document.write(begintag+fcontent[0]+closetag+"<br />");
                      }
                    document.close();*/
                    document.write('<span id="fscroller" style="width:90% height:15px; padding:2px"><\/span>');
                  }
                else
                  {
                    document.write('<span id="fscroller" style="width:90% height:15px; padding:2px"><\/span>');
                  }
                window.onload = fade;
                // -->
              </script>
            </td>
          </tr>
        </table>
      </td></tr></table>
    <!-- / News Fader -->