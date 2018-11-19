<?php

$this->bbcode = array(
    'from' => array(
        'links' => array(
            "#\[url\](https?://)($hostname)(?:/*|(/+(.+?)))\[/url\]#is",
            "#\[url=https?://$hostname(?:/*?|/+(.*?))\](?:(https?://$hostname))?(.*?)\[/url\]#is",
            '/\[url\](.+?)\[\/url\]/is',
            '/\[url=(.+?)\](.+?)\[\/url\]/is',
            '/\[iurl\](.+?)\[\/iurl\]/is',
            '/\[iurl=(.+?)\](.+?)\[\/iurl\]/is',
            '/\[url class=img\](.+?)\[\/url\]/is',
            '/\[ftp\](.+?)\[\/ftp\]/is',
            '/\[ftp=(.+?)\](.+?)\[\/ftp\]/is',
            '/\[email\](.+?)\[\/email\]/is',
            '/\[email=(.+?)\](.+?)\[\/email\]/is'
    	),
        'inline' => array(
            '/\[b\](.+?)\[\/b\]/is',
            '/\[i\](.+?)\[\/i\]/is',
            '/\[u\](.+?)\[\/u\]/is',
            '/\[s\](.+?)\[\/s\]/is',
            '/\[color=([\w#]+)\](.*?)\[\/color\]/is',
            '/\[black\](.+?)\[\/black\]/is',
            '/\[white\](.+?)\[\/white\]/is',
            '/\[red\](.+?)\[\/red\]/is',
            '/\[green\](.+?)\[\/green\]/is',
            '/\[blue\](.+?)\[\/blue\]/is',
            '/\[font=(.+?)\](.+?)\[\/font\]/is',
            '/\[size=(.+?)\](.+?)\[\/size\]/is',
            '/\[pre\](.+?)\[\/pre\]/is',
            '/\[left\](.+?)\[\/left\]/is',
            '/\[right\](.+?)\[\/right\]/is',
            '/\[(?:center|c)\]/i',
            '/\[\/(?:center|c)\]/i',
            '/\[sub\](.+?)\[\/sub\]/is',
            '/\[sup\](.+?)\[\/sup\]/is',
            '/\[tt\](.+?)\[\/tt\]/is'
        ),
        'blocks' => array(
            '/\[move\](.+?)\[\/move\]/is',
            '/\[move=(.+?)\](.+?)\[\/move\]/is',
            //'/\n?\[(?:quote|q) author=(.+?) link=(.+?) date=(.+?)\](?:\n|\<br \/\>)*/ei',
            '/\[\/(?:quote|q)\]/i',
            '/\n?\[(?:quote|q)\](?:\n|\<br \/\>)*/i',
            '/\[me=([^\]]+)\](.+?)\[\/me\]/is',
            '/\[img\](.+?)\[\/img\]/i',
            '/\[img width=([0-9]+) height=([0-9]+)\s*\](.+?)\[\/img\]/i',
            '/\[img height=([0-9]+) width=([0-9]+)\s*\](.+?)\[\/img\]/i',
            '/\[table\]\s*(.+?)\s*\[\/table\]\s?/is',
            '/\s*\[tr\]\s*(.*?)\s*\[\/tr\]\s*/is',
            '/\s*\[td\]\s*(.*?)\s*\[\/td\]\s*/is',
            '/\[hr\]/i',
            '~\[(?:y|youtube\S*?)\]https?://(?:\S+?\.)?youtube\.com/playlist(\?(?:\S+?)?list=(\S+?)(?:\&\S+)?)\[/(?:y|youtube\S*?)\]~i',
            '~\[(?:y|youtube\S*?)\]https?://(?:\S+?\.)?youtube\.com/watch(\?(?:\S+?)?v=(\S+?)(?:\&\S+)?)\[/(?:y|youtube)\]~i',
            '~\[(?:y|youtube\S*?)\]https?://youtu.be/(\S+?)(\?[^\s\[]*)?\[/(?:y|youtube)\]~i',
            '/\[rutube\]\S+\?v\=(\S+?)\[\/rutube\]/is',
            '/\[list\]/',
            '/\[\/list\]/',
            '/(<\/?table>|<\/?tr>|<\/td>)<br \/>/',
            
            '/\[(?:hidden|h)\]/is',
            
            '/\[(?:hidden|h)\=(.+?)\]/is',
            
            '/\[\/(?:hidden|h)\]/is',
            
            '/\[(?:html5audio|h5a)\](.+?)\[\/(?:html5audio|h5a)\]/is',
            '/\[(?:html5video|h5v)\](.+?)\[\/(?:html5video|h5v)\]/is',
            
            '/\[soundcloud url=(?:&quot;|")https?:\/\/api\.soundcloud\.com\/tracks\/(\d+)(?:&quot;|").*?\]/is',
            '/\[soundcloud url=(?:&quot;|")https?:\/\/api\.soundcloud\.com\/playlists\/(\d+)(?:&quot;|").*?\]/is',
            '~\[soundcloud\](https?://soundcloud.com/(.+?)/(?:sets/)?(.+?))\[/soundcloud\]~i',
            
            '/\[(?:jamendo|jm)]http:\/\/www.jamendo\.com\/[a-zA-Z]{2}\/album\/(\d+)\[\/(?:jamendo|jm)\]/is',
            
            '/\[audio\](.+?)\[\/audio\]/is',
            '/\[audio\=([^\]]+)\](.+?)\[\/audio\]/is',
            
            '/\[video\](.+?)\[\/video\]/is',
            '/\[video\=([^\]]+)\](.+?)\[\/video\]/is',
            
            "#\[(?:media|m)=(\d+(?:px|%)),(\d+(?:px|%))\]https?://($hostname/[^\s\]]+?)\[/(?:media|m)\]#i",
            '#\[(?:media|m)=(\d+(?:px|%)),(\d+(?:px|%))\](https?://[^\s\]]+?)\[/(?:media|m)\]#i',
            
            '/\[think\](.+?)\[\/think\]/is',
            
            '/\[scroll\](.+?)\[\/scroll\]/is',
            
            '/\[nb\](.+?)\[\/nb\]/is',
            
            '/\[fall\](.+?)\[\/fall\]/is',
            
            '/\[news=(.+?),(.+?)\](.+?)\[\/news\]/is',
            
            '/\[news=(.+?)\](.+?)\[\/news\]/is',
        )
    ),
    'to'=> array(
        'links' => array(
            '<a href="/$4" class="msgurl localurl">$1$2$3</a>',
            '<a href="/$1" class="msgurl localurl">$2$3</a>',
            '<a href="$1" target="_blank" class="msgurl" rel="nofollow">$1</a>',
            '<a href="\\1" target="_blank" class="msgurl" rel="nofollow">\2</a>',
            '<a href="\\1" class="msgurl" rel="nofollow">\\1</a>',
            '<a href="\1" class="msgurl" rel="nofollow">\\2</a>',
            '<a href="$1" target="_blank" class="msgurl comment-image-preview" rel="nofollow">$1</a>',
            '<a href="\\1" target="_blank" rel="nofollow">\\1</a>',
            '<a href="\\1" target="_blank" rel="nofollow">\\2</a>',
            '<a href="mailto:\\1">\\1</a>',
            '<a href="mailto:\\1">\\2</a>'
        ),
        'inline' => array(
            '<b>\\1</b>',
            '<i>\\1</i>',
            '<u>\\1</u>',
            '<s>\\1</s>',
            '<font color="\\1">\\2</font>',
            '<font color="#000000">\\1</font>',
            '<font color="#FFFFFF">\\1</font>',
            '<font color="#FF0000">\\1</font>',
            '<font color="#00FF00">\\1</font>',
            '<font color="#0000FF">\\1</font>',
            '<font face="\\1">\\2</font>',
            '<font size="\\1">\\2</font>',
            '<pre>\\1</pre>',
            '<div align="left">\\1</div>',
            '<div align="right">\\1</div>',
            '<div align="center"><!-- [center] -->',
            '<!-- [/center] --></div>',
            '<sub>\\1</sub>',
            '<sup>\\1</sup>',
            '<tt>\\1</tt>'
        ),
        'blocks' => array(
            '<marquee>\\1</marquee>',
            '<marquee direction="\\1">\\2</marquee>',
            //"'<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td><font size=\"1\"><b><a href=\"$scripturl?action=display;\\2\">$txt[yse239]: \\1 $txt[176] '.timeformat('\\3').'</a></b></font></td></tr></table><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#000000\"><tr><td><table border=\"0\" cellspacing=\"1\" cellpadding=\"2\" width=\"100%\"><tr><td class=\"quote\">'",
            "</td></tr></table></td></tr></table>",
            "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\" class=\"quote-msg-meta\"><tr><td><font size=\"1\"><b>{$this->app->locale->yse240}:</b></font></td></tr></table><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#000000\" role=\"presentation\" class=\"quote-msg\"><tr><td><table border=\"0\" cellspacing=\"1\" cellpadding=\"2\" width=\"100%\" role=\"presentation\"><tr><td class=\"quote\">",
            "<font class=\"meaction\">* \\1 \\2</font>",
            '<img src="\\1" alt="" border="0" class="msg-embed-img">',
            '<img src="\\3" alt="" border="0" width="\\1" height="\\2" class="msg-embed-img">',
            '<img src="\\3" alt="" border="0" width="\\2" height="\\1" class="msg-embed-img">',
            '<table class="msg-embed-table">\\1</table>',
            '<tr>\\1</tr>',
            '<td>\\1</td>',
            '<hr>',
            '<div class="youtube-embed youtube-playlist"><a href="https://www.youtube.com/playlist?list=$2" yt-params="$1" target="_blank"></a></div>',
            '<div class="youtube-embed"><a href="https://youtu.be/$2" yt-params="$1" target="_blank"><img src="//img.youtube.com/vi/$2/mqdefault.jpg"></a></div>',
            '<div class="youtube-embed"><a href="https://youtu.be/$1" yt-params="$2" target="_blank"><img src="//img.youtube.com/vi/$1/mqdefault.jpg"></a></div>',
            '<OBJECT width="470" height="353"><PARAM name="movie" value="//video.rutube.ru/\\1"></PARAM><PARAM name="wmode" value="window"></PARAM><PARAM name="allowFullScreen" value="true"></PARAM><EMBED src="//video.rutube.ru/\\1" type="application/x-shockwave-flash" wmode="window" width="470" height="353" allowFullScreen="true" ></EMBED></OBJECT>',
            '<ul>',
            '</ul>',
            '\\1',
            
            '<div><div class="spoiler" onclick="Forum.Utils.Spoiler.showHide(this);" onmouseover="this.style.cursor=\'pointer\';"><a href="'.str_replace("&showHiddenText=1", "", $URI).'&showHiddenText=1" onclick="if (this.childNodes) return false;"><img src="/YaBBImages/'.($showHiddenText==1?'collapse':'expand').'.jpg" border="0" class="expantionImg" alt="Раскрыть" /> скрытый текст</a></div><div class="spoiler" style="display: '.($showHiddenText==1?'block':'none').'">',
            
            '<div><div class="spoiler" onclick="Forum.Utils.Spoiler.showHide(this);" onmouseover="this.style.cursor=\'pointer\';"><a href="'.str_replace("&showHiddenText=1", "", $URI).'&showHiddenText=1" onclick="if (this.childNodes) return false;"><img src="/YaBBImages/'.($showHiddenText==1?'collapse':'expand').'.jpg" border="0" class="expantionImg" alt="Раскрыть" /> \\1</a></div><div class="spoiler" style="display: '.($showHiddenText==1?'block':'none').'">',
            
            '</div></div>',
            
            '<audio src="\\1" controls="controls"><a href="\\1" target="_blank" rel="nofollow">\\1</a></audio>',
            '<video src="\\1" controls="controls" width="480" height="360"><a href="\\1" target="_blank" rel="nofollow">\\1</a></video>',
            
            '<iframe width="100%" height="166" scrolling="no" frameborder="no" src="//w.soundcloud.com/player/?url=http%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F\\1&show_artwork=true"></iframe>',
            '<iframe width="100%" height="450" scrolling="no" frameborder="no" src="//w.soundcloud.com/player/?url=http%3A%2F%2Fapi.soundcloud.com%2Fplaylists%2F\\1&show_artwork=true"></iframe>',
            '<div class="soundcloud-embed"><div class="soundcloud-placeholder"><div class="soundcloud-play-btn"></div><div class="soundcloud-waveform"><div class="soundcloud-title">$3 by $2</div></div></div><a href="$1" class="soundcloud-embed-lnk" onclick="Forum.Utils.showImage(event)" onmouseover="Forum.Utils.getSoundcloudMeta(event)"></a></div>',
            
            '<object width="200" height="300" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" align="middle"><param name="allowScriptAccess" value="always" /><param name="wmode" value="transparent" /><param name="movie" value="//widgets.jamendo.com/ru/album/?album_id=\\1&playertype=2008&refuid=524863" /><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF" /><embed src="//widgets.jamendo.com/ru/album/?album_id=\\1&playertype=2008&refuid=524863" quality="high" wmode="transparent" bgcolor="#FFFFFF" width="200" height="300" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">&nbsp;</embed>&nbsp;</object>', // jamendo
           
            '<object id="audioplayer455" width="500" height="60"><param name="allowScriptAccess" value="always" /><param name="wmode" value="transparent" /><param name="movie" value="uppod/uppod.swf" /><param name="flashvars" value="comment= &amp;st=uppod/styles/audio24-416-1.txt&amp;file=\\1" /><embed src="uppod/uppod.swf" type="application/x-shockwave-flash" allowscriptaccess="always" wmode="transparent" flashvars="comment= &amp;st=uppod/styles/audio24-416-1.txt&amp;file=\\1" width="500" height="60"></embed></object>',
            '<object id="audioplayer455" width="500" height="60"><param name="allowScriptAccess" value="always" /><param name="wmode" value="transparent" /><param name="movie" value="uppod/uppod.swf" /><param name="flashvars" value="comment=\\1&amp;st=uppod/styles/audio24-416.txt&amp;file=\\2" /><embed src="uppod/uppod.swf" type="application/x-shockwave-flash" allowscriptaccess="always" wmode="transparent" flashvars="comment=\\1&amp;st=uppod/styles/audio24-416.txt&amp;file=\\2" width="500" height="60"></embed></object>',
            
            '<object id="videoplayer" width="512" height="410"><param name="allowFullScreen" value="true"></param><param name="allowScriptAccess" value="always" /><param name="wmode" value="transparent" /><param name="movie" value="uppod/uppod.swf" /><param name="flashvars" value="comment= &amp;st=uppod/styles/video24-881.txt&amp;file=\\1" /><embed src="uppod/uppod.swf" type="application/x-shockwave-flash" allowFullScreen="true" allowscriptaccess="always" wmode="transparent" flashvars="comment= &amp;st=uppod/styles/video24-881.txt&amp;file=\\1" width="512" height="410"></embed></object>',
            '<object id="videoplayer" width="512" height="410"><param name="allowFullScreen" value="true"></param><param name="allowScriptAccess" value="always" /><param name="wmode" value="transparent" /><param name="movie" value="uppod/uppod.swf" /><param name="flashvars" value="comment=\\1&amp;st=uppod/styles/video24-882.txt&amp;file=\\2" /><embed src="uppod/uppod.swf" type="application/x-shockwave-flash" allowFullScreen="true" allowscriptaccess="always" wmode="transparent" flashvars="comment=\\1&amp;st=uppod/styles/video24-882.txt&amp;file=\\2" width="512" height="410"></embed></object>',
            
            // [media]
            '<div class="media-embed" style="width: $1; height: $2;"><iframe src="//$3" allow="fullscreen"><a href="//$3" target="_blank" rel="nofollow noopener">$3</a></iframe></div>',
            '<div class="media-embed" style="width: $1; height: $2;"><a href="$3" target="_blank" rel="nofollow noopener" title="$3" onclick="Forum.Utils.mediaEmbed(event)"></a></div>',
            
            '<table cellpadding="0" cellspacing="0"><tr><td style="margin: 0;padding: 0;border: 0;"><div style=" background-color: #FFEBD5;color: #000000; -moz-border-radius: 20px; -webkit-border-radius: 20px; border: 1px solid #000000; padding: 10px;font-size: 14px;font-variant:small-caps;" >&nbsp; \\1 &nbsp;</div><img src="smilies/think.gif"></td></tr></table>', // [think]
            
            '<div style="width:auto; height:200px; background:#transparent; overflow:auto; border:1px solid #000000; padding:8px;">\\1</div>', // [scroll]
            
            '<table style="border:5px double red; padding:2px;" cellpadding="0" cellspacing="0"><tr><td style="font-size: 15px;font-variant:small-caps;font-style:bold;">&nbsp; \\1&nbsp;</td></tr></table>', // [nb]
            
            '<marquee direction="down">\\1</marquee>', // [fall]
    
            '<table style="border: 5px double grey;" cellpadding="0" cellspacing="0" align="center">
               <tr align="center" valign="middle">
                 <td style="height:30" align="center" valign="middle">
                   <div style="color:red;font-size: 24px;font-variant:small-caps;">&nbsp;&nbsp;\\2&nbsp;&nbsp;<a href="\\1" target="_blank" rel="nofollow">(источник)&nbsp;&nbsp;</a></div>
                  </td>
                 </tr>
                 <tr>
                   <td style="margin: 0;padding: 0;border: 0;">
                     <hr noshade align="center" size="2" width="80%">
                     <div style="max-height:200px; overflow:auto; padding: 5px;font-size: 14px;text-align:justify;" >\\3</div>
                   </td>
                 </tr>
             </table>', // [news]
             
             '<table style="border: 5px double grey;" cellpadding="0" cellspacing="0" align="center">
                <tr align="center" valign="middle">
                  <td style="height:30" align="center" valign="middle">
                    <div style="color:red;font-size: 24px;font-variant:small-caps;">&nbsp;&nbsp;\\1&nbsp;&nbsp;</div>
                  </td>
                </tr>
                <tr>
                  <td style="margin: 0;padding: 0;border: 0;"><hr noshade align="center" size="2" width="80%">
                    <div style="max-height:200px; overflow:auto; padding: 5px;font-size: 14px;text-align:justify;">\\2</div>
                  </td>
                </tr>
              </table>' // [news]
        )
    )
);

$emoji = array(
    'from' => array(
        '::)',
        '>:(',
        '&gt;:(',
        '>:D',
        '&gt;:D',
        ':)',
        ';)',
        ':D',
        ';D',
        ':(',
        ':o',
        '8-)',
        ':P',
        '???',
        ':-[',
        ':-X',
        ':-*',
        ":'(",
        ':&#039;(',
        ':-\\',
        '^-^',
        'O0',
        ':-D',
        ':[-',
        ':F',
        ':№',
        ':hm:',
        ':-#',
        ':-O',
        ':ykypok:',
        'ЪЪ',
        '!Ъ',
        '(!)',
        '(?)',
        ';]'
    ),
    'to' => array(
        'rolleyes.gif',
        'angry.gif',
        'angry.gif',
        'evil.gif',
        'evil.gif',
        'smiley.gif',
        'wink.gif',
        'cheesy.gif',
        'grin.gif',
        'sad.gif',
        'shocked.gif',
        'cool.gif',
        'tongue.gif',
        'huh.gif',
        'embarassed.gif',
        'lipsrsealed.gif',
        'kiss.gif',
        'cry.gif',
        'cry.gif',
        'undecided.gif',
        'azn.gif',
        'afro.gif',
        'laugh.gif',
        'rusty.gif',
        'facepalm.gif',
        'crazy.gif',
        'hm.gif',
        'sleep.gif',
        'singing.gif',
        'insane.gif',
        'thumbup.gif',
        'thumbdown.gif',
        'exclamation.gif',
        'question.gif',
        'trollface.gif'
    )

);

?>
