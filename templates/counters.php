<div align=center>
  <!-- Rating@Mail.ru counter -->
  <script type="text/javascript">
  var _tmr = window._tmr || (window._tmr = []);
  _tmr.push({id: "363144", type: "pageView", start: (new Date()).getTime()});
  (function (d, w, id) {
    if (d.getElementById(id)) return;
    var ts = d.createElement("script"); ts.type = "text/javascript"; ts.async = true; ts.id = id;
    ts.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//top-fwz1.mail.ru/js/code.js";
    var f = function () {var s = d.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ts, s);};
    if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); }
  })(document, window, "topmailru-code");
  </script><noscript><div>
  <img src="//top-fwz1.mail.ru/counter?id=363144;js=na" style="border:0;position:absolute;left:-9999px;" alt="" />
  </div></noscript>
  <!-- //Rating@Mail.ru counter -->
  
  <!-- Rating@Mail.ru logo -->
  <!--<a href="https://top.mail.ru/jump?from=363144">
  <img src="//top-fwz1.mail.ru/counter?id=363144;t=464;l=1" 
  style="border:0;" height="31" width="88" alt="Рейтинг@Mail.ru" /></a>-->
  <!-- //Rating@Mail.ru logo -->

  <!-- Rambler Top100 (Kraken) Counter -->
  <script>
  (function (w, d, c) {
      (w[c] = w[c] || []).push(function() {
          var options = {
              project: 353245,
              attributes_dataset: [ 'cerber-topline' ]
          };
          try {
              w.top100Counter = new top100(options);
          } catch(e) { }
      });

      var n = d.getElementsByTagName("script")[0],
          s = d.createElement("script"),
          f = function () { n.parentNode.insertBefore(s, n); };
      s.type = "text/javascript";
      s.async = true;
      s.src =
          (d.location.protocol == "https:" ? "https:" : "http:") +
          "//st.top100.ru/top100/top100.js";

      if (w.opera == "[object Opera]") {
          d.addEventListener("DOMContentLoaded", f, false);
      } else { f(); }
  })(window, document, "_top100q");
  </script>
  <noscript><img src="//counter.rambler.ru/top100.cnt?pid=353245"></noscript>
  <!--<a href="//top100.rambler.ru/top100/"><img src="//counter.rambler.ru/top100.cnt?353245" alt="" width=1 height=1 border=0></a>-->
  <!-- END Rambler Top100 (Kraken) Counter -->

  <!-- HotLog -->
  <?php if($this->SSL): ?>
     <a href="//click.hotlog.ru/?40712" target="_blank"><img src="//hit.hotlog.ru/cgi-bin/hotlog/count?s=40712&amp;im=457" border="0" alt="HotLog" style="width: 1px; height: 1px;"></a>
  <?php else: ?>
    <span id="hotlog_counter"></span>
    <span id="hotlog_dyn"></span>
    <script type="text/javascript">
      var hot_s = document.createElement('script');
      hot_s.type = 'text/javascript'; hot_s.async = true;
      hot_s.src = '//js.hotlog.ru/dcounter/40712.js';
      hot_d = document.getElementById('hotlog_dyn');
      hot_d.appendChild(hot_s);
    </script>
    <noscript>
      <a href="//click.hotlog.ru/?40712" target="_blank"><img src="//hit.hotlog.ru/cgi-bin/hotlog/count?s=40712&amp;im=457" border="0" alt="HotLog"></a>
    </noscript>
  <?php endif; ?>
  <!-- /HotLog -->
  
  <!--LiveInternet counter-->
  <script type="text/javascript">
    document.write("<a href='//www.liveinternet.ru/click' "+
      "target=_blank><img src='//counter.yadro.ru/hit?t15.3;r"+
      escape(document.referrer)+((typeof(screen)=="undefined")?"":
      ";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?
      screen.colorDepth:screen.pixelDepth))+";u"+escape(document.URL)+
      ";h"+escape(document.title.substring(0,150))+";"+Math.random()+
      "' alt='' title='LiveInternet: показано число просмотров за 24"+
      " часа, посетителей за 24 часа и за сегодня' "+
      "border='0' width='88' height='31'><\/a>")
  </script>
  <!--/LiveInternet-->
  
  <br>
  Реклама:
<?php
if ($this->app->conf->sape){
    if (!defined('_SAPE_USER')){
       define('_SAPE_USER', '35cffc2e45e75319980061a17e79df73');
    }
    require_once(realpath(PROJECT_ROOT.'/'._SAPE_USER.'/sape.php'));
    $sape = new SAPE_client();
    echo $sape->return_links(5);
}
?>
<br />
</div>
