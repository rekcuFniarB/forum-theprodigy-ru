<?php
namespace Prodigy;

class Service extends \Klein\ServiceProvider
{
    protected $output_cache;
    protected $compiled_templates;
    public $bbcode;
    public $emoji;
    public $menu_initial;
    
    public function __construct(\Klein\Request $request = null, \Klein\AbstractResponse $response = null)
    {
        parent::__construct($request, $response);
        $this->output_cache = array();
        $this->cached_template = array();
        $this->compiled_templates = array();
        $this->active_cache_name = null;
        $this->bbcode = null;
        $this->emoji = null;
        $this->menusep = ' | ';
        
        $this->addValidator('empty', function ($str) {
            $str = trim($str);
            return empty($str);
        });
        
        error_log("__CONSTRUCT__: SERVICE ". __NAMESPACE__);
    }
    
    /**
     * Look for stored key, escape and return corresponding value.
     * @param string $key key to look for. WARNING: don't pass variables here, it's insecure.
     * @return string
     */
    
    public function get(string $key='')
    {
        if (empty($key))
            return '';
        if ($this->shared_data->exists($key))
        {
            $val = $this->shared_data->get($key);
            return $this->esc($val);
        }
        else
            return '';
    }
    
    /**
     * Parent class already has such method but doesn't allow to set charset
     * HtmlSpecialChars wrapper
     * @param string $str       input string or key
     * @param string $charset   input encoding
     * @return string
     */
    public function esc($string, $charset = null) {
        if (empty($string)) return '';
        
        $string = strval($string);
        
        if ($charset === null) {
            if(defined('SITE_CHARSET'))
                $charset = SITE_CHARSET;
            else
                $charset = 'UTF-8';
        }
        
        $string = str_replace(array('<br />', '<br>'), "\n", $string);
        return htmlspecialchars($string, ENT_COMPAT, $charset, false);
    } // escape
    
    /**
     * Remove spaces and then escape
     * @param string $string  input string
     * @param string $charset input charset
     * @return string
     */
    public function rse($string, $charset = null) {
        $string = preg_replace('/\s/', '', trim($string));
        return $this->esc($string, $charset);
    }
    
    /**
     * Custom templates path
     */
    public function partial($view, array $data = array())
    {
        return parent::partial(PROJECT_ROOT . '/templates/' . $view, $data);
    }
    
    public function include($view, array $data = array())
    {
        $template = PROJECT_ROOT . '/templates/' . $view;
        if (file_exists($template))
            return parent::partial($template, $data);
    }
    
    /**
     * This is $this->partial() wrapper allowing to cache first call output
     * and then just print already rendered part from cache.
     * @param string $view  template names
     * @param array $data   data
     */
    public function cpartial($view, array $data = array(), $extract = false) {
        if(isset($this->output_cache[$view])) {
            echo $this->output_cache[$view];
        } else {
            ob_start();
            
            if ($extract)
                $this->_partial($view, $data);
            else
                $this->partial($view, $data);
            
            $this->output_cache[$view] = ob_get_contents();
            ob_end_clean();
            echo $this->output_cache[$view];
        }
    } // cpartial()
    
    /**
     * Simplified version of $this->partial()
     * Works same way except that input data is exctracted.
     */
    public function _partial($view, array $data = null) {
        if(is_array($data))
            extract($data, EXTR_SKIP);
        
        require PROJECT_ROOT . '/templates/' . $view;
        
        if (false !== $this->response->chunked) {
            $this->response->chunk();
        }
    }
    
    /**
     * "Compile" template and reuse on next calls
     * Actually this method not needed. Tests show that
     * it's not faster than simple include().
     * @param string $template  template filename.
     * @param mixed  $data      optional data to pass to template
     */
    public function cTemplate($template, $data = null)
    {
        if(isset($this->compiled_templates[$template]))
            $this->compiled_templates[$template]($data);
        else
        {
            $fn_body = file_get_contents($template);
            $fn_body = 'return function($data){
              if(is_array($data)) {
                  extract($data, EXTR_SKIP);
              } ?>
            '.$fn_body.' <?php };';
            $compiled_template = eval($fn_body);
            
            if ($compiled_template === false)
                throw new Errors\TemplateException("Template compiling failed: $template", 1);
            
            $this->compiled_templates[$template] = $compiled_template;
            $this->compiled_templates[$template]($data);
        }
    }
    
    /**
     * Start caching output. A helper function
     * to prevent duplicate code in templates.
     * Call as <?php start_cache('cache_name') ?> from templates.
     * Stop caching by calling $this-end_cache()
     * @param string $name  cache name.
     */
    public function start_cache($name) {
        if(null === $this->active_cache_name) {
            $this->active_cache_name = $name;
            ob_start();
        }
        else
            throw new Errors\TemplateException("Cache is already active: {$this->active_cache_name}.\nShould be closed before starting new one.", 2);
    }
    
    /**
     * Stop caching started by start_cache().
     * Call as <?php $this->end_cache() ?> from templates.
     */
    public function end_cache() {
        if(!is_null($this->active_cache_name)) {
            $this->output_cache[$this->active_cache_name] = ob_get_contents();
            ob_end_clean();
            $this->active_cache_name = null;
        }
    }
    
    /**
     * Get cached output created by $this->start_cache()
     * Call as <?php $this->get_cache('cache_name') ?> from templates
     * @param string $name  cache name or active cache if null. Null value is valid only for active cache.
     */
    public function get_cache($name = null) {
        if ($name === null){
            if($this->active_cache_name !== null) {
                $name = $this->active_cache_name;
                $this->end_cache();
            } else {
                throw new Errors\TemplateException("Cache name not specified.", 3);
            }
        }
        if(isset($this->output_cache[$name]))
            echo $this->output_cache[$name];
        elseif($this->active_cache_name !== null && $this->active_cache_name == $name) {
            $this->end_cache();
            echo $this->output_cache[$name];
        }
        else {
            //throw new Errors\TemplateException("No such cache '$name'.", 4);
            echo '';
        }
    }
    
    /**
     * Alias of esc()
     * simply call as <?= $this($string) ?> from templates to print escaped values.
     */
    public function __invoke($str, $charset = null){
        return $this->esc($str, $charset);
    }
    
    /**
     * Convert unicode chars to HTML entities.
     * @param string $string - input string in UTF-8 charset
     * @param string $charset - output charset, defaults to defined in config.
     * @returns string
     */
    public function unicodeentities($string, $charset = null)
    {
        if ($charset === null)
            $charset = $this->app->conf->get('charset', 'UTF-8');
        return html_entity_decode(mb_convert_encoding($string, 'HTML-ENTITIES', 'utf-8'), ENT_QUOTES, $charset);
    }
    
    public function load_bbcode() {
        $hostname = $this->request->server()->get('HTTP_HOST');
        $URI = $this->request->uri();
        $showHiddenText = $this->request->param('showHiddenText');
        
        require_once('bbcode.php');
        
        // This smiley regex makes sure it doesn't parse smilies within code tags (so [url=mailto:David@bla.com] doesn't parse the :D smiley)
        $_emoji = array('from' => array(), 'to' => array());
        for ($i = 0; $i < count($emoji['from']); $i++)
          {
            $_emoji['from'][] = 
                '~(?<=^|\s|\])' // look behind, allows beginning of string or ] or space
                . quotemeta(str_replace(array('~', '|'), array('\~', '\|'), $emoji['from'][$i]))
                . '(?=\s|$|\[)~'; // look ahead, allows space, [ or EOL
            $_emoji['to'][] = "\\1<img src=\"{$this->app->conf->imagesdir}/{$emoji['to'][$i]}\" alt=\"".$this->esc($emoji['from'][$i])."\" border=\"0\">";
          }
        $this->emoji = $_emoji;
    }
    
    public function buildUBBCTagPairs($message, $tag) {
        $compare = preg_match_all("/\[$tag(\s+[^\]]+)?\]/si", $message, $m1) - preg_match_all("/\[\/$tag\]/si", $message, $m2);
        
        if ($compare < 0) {
            for ($i=0; $i<-$compare; $i++)
            $message = "[$tag]\n$message";
        }
        else if ($compare > 0) {
            for ($i=0; $i<$compare; $i++)
                $message = "$message\n[/$tag]";
        }
        
        return $message;
    } // buildUBBCTagPairs()
    
    public function doUBBC($message, $type = 'links,inline,blocks,emoji') {
        if(is_null($this->bbcode))
            $this->load_bbcode();
        
        $locale = $this->app->locale;
        $subs = $this->app->subs;
        
        $type = explode(',', str_replace(' ', '', $type));
        
        $hostname = $this->request->server()->get('HTTP_HOST');
        
        $maxwidth = $this->app->conf->maxwidth;
        $maxheight = $this->app->conf->maxheight;
        
        $lists_codes = array(
            '*' => '',
            '@' => ' type="disc"',
            '+' => ' type="square"',
            'x' => ' type="square"',
            '#' => ' type="square"',
            'o' => ' type="circle"',
            '0' => ' type="circle"'
        );
        
        $message = $this->esc($message);        
        
        if(in_array('inline', $type) || in_array('blocks', $type)) {
            // not only emoji
            $message = $this->buildUBBCTagPairs($message, "quote");
            $message = $this->buildUBBCTagPairs($message, "td");
            $message = $this->buildUBBCTagPairs($message, "tr");
            $message = $this->buildUBBCTagPairs($message, "table");
            $message = $this->buildUBBCTagPairs($message, "code");
            //$message = $this->buildUBBCTagPairs($message, "hidden");
            
            $parts = preg_split('/\[\/?code\]/', ' ' . $message);
            
            for ($i = 0; $i < count($parts); $i++) {
                if ($i % 2 == 0) {
                    $parts[$i] = str_replace(array('$', '[[', ']]'), array('&#36;', '{<{', '}>}'), $parts[$i]);
                    if ($i > 0)
                        $parts[$i] = '</font></td></tr></table></td></tr></table>' . $parts[$i];
                    
                    // Replace raw youtube and soundcloud links with player
                    $special_urls = array(
                        '~\[url\](https?://(?:(?:\S+?\.)?youtube\.com/(?:watch|playlist)\?|youtu\.be/)\S+?)\[/url\]~i',
                        '~\[url\](https?://soundcloud\.com/[^\]/\s]+/[^\]/\s]+?)\[/url\]~i',
                        '~\[url\](https?://soundcloud\.com/[^\]/\s]+/sets/[^\]/\s]+?)\[/url\]~i'
                    );
                    
                    if (in_array('blocks', $type)){
                        $parts[$i] = preg_replace(
                            $special_urls,
                            array(
                                '[y]$1[/y]',
                                '[soundcloud]$1[/soundcloud]',
                                '[soundcloud]$1[/soundcloud]'
                            ),
                            $parts[$i]
                        );
                    } else {
                        $parts[$i] = preg_replace(
                            $special_urls,
                            '[url class=img]$1[/url]',
                            $parts[$i]
                        );
                    }
                    
                    $parts[$i] = $this->prepare_urls($parts[$i]);
                                        
                    //// Put all embedded objects into the spoiler for mobile devices
                    //if ($this->app->user->mobileMode){
                    //    $parts[$i] = preg_replace(array(
                    //        //'~(\[youtube=(?:.+?)\](?:.+?)\[/youtube\])~i',
                    //        //'~(\[y\](?:.+?)\[/y\])~i',
                    //        //'~(\[(?:media|m)=(?:.+?)\[/(?:media|m)\])~i',
                    //        //'~(\[(?:audio|html5audio|h5a)(?:.+?)\[/(?:audio|html5audio|h5a)\])~i',
                    //        //'~(\[(?:video|html5video|h5v|rutube)(?:.+?)\[/(?:video|html5video|h5v|rutube)\])~i',
                    //        '~(\[soundcloud (?:.+?)/\])~i',
                    //    ),
                    //    array(
                    //        //'[h=Youtube]$1[/h]',
                    //       //'[h=Youtube]$1[/h]',
                    //        //'[h=Media]$1[/h]',
                    //        //'[h=Audio]$1[/h]',
                    //        //'[h=Video]$1[/h]',
                    //        '[h=SoundCloud]$1[/h]',
                    //    ), $parts[$i]);
                    //}
                    
                    // prepare [media] tag, make size values of type "px" if type not specivied
                    $parts[$i] = preg_replace(
                        array(
                            '~\[media=(\d+),(\d+)\]~i',
                            '~\[media=(\d+%),(\d+)\]~i',
                            '~\[media=(\d+),(\d+%)\]~i'
                        ),
                        array(
                            '[media=$1px,$2px]',
                            '[media=$1,$2px]',
                            '[media=$1px,$2]'
                        ),
                        $parts[$i]);
                    
                    if(in_array('emoji', $type))
                        $parts[$i] = $this->parse_emoji($parts[$i]);
                    
                    $parts[$i] = preg_replace($this->bbcode['from']['links'], $this->bbcode['to']['links'], $parts[$i]);
                    
                    if(in_array('inline', $type))
                        $parts[$i] = preg_replace($this->bbcode['from']['inline'], $this->bbcode['to']['inline'], $parts[$i]);
                    
                    if(in_array('blocks', $type)) {
                        $parts[$i] = preg_replace($this->bbcode['from']['blocks'], $this->bbcode['to']['blocks'], $parts[$i]);
                        $parts[$i] = preg_replace_callback(
                            '/\n?\[(?:quote|q) author=(.+?) msg=(\d+?) date=(\d+?)\](?:\n|\<br \/\>)*/i',
                            function($m) use($locale, $subs) {
                                return "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\" class=\"quote-msg-meta\"><tr><td><font size=\"1\"><b><a href=\"".SITE_ROOT."/{$m[2]}/\">{$locale->yse239}: $m[1] {$locale->txt[176]} " . $subs->timeformat($m[3]) . "</a></b></font></td></tr></table><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#000000\" role=\"presentation\" class=\"quote-msg\"><tr><td><table border=\"0\" cellspacing=\"1\" cellpadding=\"2\" width=\"100%\" role=\"presentation\"><tr><td class=\"quote\">";
                            },
                            $parts[$i]
                        );
                        
                        // Old quotes format
                        $parts[$i] = preg_replace_callback(
                            '/\n?\[(?:quote|q) author=(.+?) link=.+?#msg(\d+?) date=(\d+?)\](?:\n|\<br \/\>)*/i',
                            function($m) use($locale, $subs) {
                                return "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\" class=\"quote-msg-meta\"><tr><td><font size=\"1\"><b><a href=\"".SITE_ROOT."/{$m[2]}/\">{$locale->yse239}: $m[1] {$locale->txt[176]} " . $subs->timeformat($m[3]) . "</a></b></font></td></tr></table><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#000000\" role=\"presentation\" class=\"quote-msg\"><tr><td><table border=\"0\" cellspacing=\"1\" cellpadding=\"2\" width=\"100%\" role=\"presentation\"><tr><td class=\"quote\">";
                            },
                            $parts[$i]
                        );
                        
                        if (!($maxwidth == '0' && $maxheight == '0')) {
                            preg_match_all('/<img src="(http:\/\/.+?)" alt="" border="0" \/>/is', $parts[$i], $imgmatches, PREG_PATTERN_ORDER);
                            $imgnew = array();
                            for ($j = 0; $j < count($imgmatches[1]); $j++) {
                                $imagesize = @getimagesize($imgmatches[1][$j]);
                                $width = $imagesize[0];
                                $height = $imagesize[1];
                                if ($width > $maxwidth || $height > $maxheight) {
                                    if ($width > $maxwidth && $maxwidth != '0') {
                                        $height = floor($maxwidth / $width * $height);
                                        $width = $maxwidth;
                                        if ($height > $maxheight && $maxheight != '0') {
                                            $width = floor($maxheight / $height * $width);
                                            $height = $maxheight;
                                        }
                                    } else {
                                        if ($height > $maxheight && $maxheight != '0') {
                                            $width = floor($maxheight / $height * $width);
                                            $height = $maxheight;
                                        }
                                    }
                                }
                                $imgnew[$j] = "<img src=\"{$imgmatches[1][$j]}\" width=\"$width\" height=\"$height\" alt=\"\" border=\"0\" />";
                            }
                            if(sizeof($imgnew) > 0)
                                $parts[$i] = str_replace($imgmatches[0], $imgnew, $parts[$i]);
                        }
                        
                        // parse lists
                        $parts[$i] = preg_replace_callback(
                            '!\[([*@+x#o0])\]!Ui',
                            function($m) use($lists_codes) {return '<li' . $lists_codes[$m[1]] . '>';},
                            $parts[$i]
                        );
                    } // if codes type block
                }
                elseif ($i <= count($parts) - 1)
                    $parts[$i] = '<table border="0" cellpadding="0" cellspacing="0"><tr><td><font size="1"><b>' . $locale->yse238 . ':</b></font></td></tr></table><table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#000000"><tr><td><table border="0" cellspacing="1" cellpadding="2" width="100%"><tr><td class="code"><font face="Courier new">' . $parts[$i];
                
            } // for loop
        
            $message = substr(implode('', $parts), 1);
        
        } // if not only emoji
        else {
            $message = $this->parse_emoji($message);
            $message = preg_replace($this->bbcode['from']['links'], $this->bbcode['to']['links'], $message);
        }
        $message = str_replace(
            array('{<{', '}>}', '  ', "\t", "\r", "\n"),
            array('[', ']', '&nbsp; ', '&nbsp;&nbsp;&nbsp;&nbsp;', '', "<br>\n"), $message
        );
        
        // html
        if (preg_match_all("/\[html\].*\[\/html\]/si", $message, $HTMLparts) > 0) {
            foreach ($HTMLparts as $HTMLpart)
                $message = str_replace($HTMLpart, str_replace(array('<br />', '&nbsp;'), array("", ""), $HTMLpart), $message);
                $message = str_replace(array("[html]", "[/html]"), array("", ""), $message);
        }
        
        if ($this->app->user->matFilterEnabled) $message = $this->matFilter($message);
        
        return $message;
    } // doUBBC()
    
    public function parse_emoji($message) {
        $message = preg_replace($this->emoji['from'], $this->emoji['to'], $message);
        return $message;
    }
    
    public function prepare_urls($message, $output_type = 'bb') {
        // https://jkwl.io/php/regex/2015/05/18/url-validation-php-regex.html
        // with some modifications for our forum
        $urlRegex = array('~(?<=[\s>\.(\']|^)(' .
            // protocol identifier
            "https?:\\/\\/" .
            // user:pass authentication
            "(?:\\S+(?::\\S*)?@)?" .
            "(?:" .
            // IP address exclusion
            // private & local networks
            "(?!(?:10|127)(?:\\.\\d{1,3}){3})" .
            "(?!(?:169\\.254|192\\.168)(?:\\.\\d{1,3}){2})" .
            "(?!172\\.(?:1[6-9]|2\\d|3[0-1])(?:\\.\\d{1,3}){2})" .
            // IP address dotted notation octets
            // excludes loopback network 0.0.0.0
            // excludes reserved space >= 224.0.0.0
            // excludes network & broacast addresses
            // (first & last IP address of each class)
            "(?:[1-9]\\d?|1\\d\\d|2[01]\\d|22[0-3])" .
            "(?:\\.(?:1?\\d{1,2}|2[0-4]\\d|25[0-5])){2}" .
            "(?:\\.(?:[1-9]\\d?|1\\d\\d|2[0-4]\\d|25[0-4]))" .
            "|" .
            // host name
            "(?:(?:[a-z\\x80-\\xFF0-9]-*)*[a-z\\x80-\\xFF0-9]+)" .
            // domain name
            "(?:\\.(?:[a-z\\x80-\\xFF0-9]-*)*[a-z\\x80-\\xFF0-9]+)*" .
            // TLD identifier
            "(?:\\.(?:[a-z\\x80-\\xFF]{2,}))" .
            ")" .
            // port number
            "(?::\\d{2,5})?" .
            // resource path
            '(?:/[^\s<>"\'\[\]\\\\]*)?' .
            ')~i',
            
            '~(?<=[\s>\.(;\'"]|^)((?:ftp|ftps)://[\w\-_%@:|]+(?:\.[\w\-_%]+)*(?::\d+)?(?:/[\w\-_\~%\.@,\?&;=#(){}+:\'\\\\]*)*[/\w\-_\~%@\?;=#}\\\\])~i',
            '~(?<=[\s>(\'<]|^)(www(?:\.[\w\-_]+)+(?::\d+)?(?:/[\w\-_\~%\.@!,\?&;=#(){}+:\'\\\\]*)*[/\w\-_\~%@\?;=#}\\\\])~i'
        );
        
        $cut_url_expr = '(([^\s<>"\'\[\]\\\\]{70})[^\s<>"\'\[\]\\\\]+?)';
        
        if ($output_type == 'bb') {
            $urlsResultCode = array(
                '[url]$1[/url]',
                '[ftp]$1[/ftp]',
                '[url=http://$1]$1[/url]'
            );
        } else {
            $urlsResultCode = array(
                '<a href="$1" rel="nofollow">$1</a>',
                '<a href="$1" rel="nofollow">$1</a>',
                '<a href="http://$1" rel="nofollow">$1</a>'
            );
        }
        
        // Fix local and some other links scheme when viewed through encrypted connections
        if($this->request->isSecure()){
            if(!$this->app->conf->CSPUIR){
                $ssl_fix_links_from = array(
                    //"http://$hostname",
                    "[img]http://$hostname/",
                    //'http://www.youtube.com',
                    //'http://youtu.be',
                    'http://vk.com',
                    'http://pp.vk.me',
                    'http://pp.userapi.com',
                    //'http://player.vimeo.com',
                    'http://i.imgur.com',
                    //'http://coub.com',
                    //'http://api.soundcloud.com',
                    //'http://promodj.com'
                );
                $ssl_fix_links_to = array(
                    //"https://$hostname",
                    "[img]https://$hostname/",
                    //'https://www.youtube.com',
                    //'https://youtu.be',
                    'https://vk.com',
                    'https://pp.vk.me',
                    'https://pp.userapi.com',
                    //'https://player.vimeo.com',
                    'https://i.imgur.com',
                    //'https://coub.com',
                    //'https://api.soundcloud.com',
                    //'https://promodj.com'
                );
                $message = str_replace($ssl_fix_links_from, $ssl_fix_links_to, $message);
            } // if !CSPUIR
        } // if $SSL
                                                
        // Validate raw urls and embed them in [url] tags
        $message = preg_replace($urlRegex, $urlsResultCode, $message);
        
        // Cut too long urls
        $result = preg_replace(
            array('~\[url\]' . $cut_url_expr . '\[/url\]~i', '~\[url=(\S+?)\]' . $cut_url_expr . '\[/url\]~i'),
            array('[url=$1]$2...[/url]', '[url=$1]$3...[/url]'),
            $message
        );
        // Only do this if the preg survives.
        if (is_string($result))
            return $result;
        else
            return $message;
    } // prepare_urls()
    
    public function matFilter($message) {
        $badWords = array('/[ıxX’][Ûy”Y][…È»Ë]/is', '/[œÔn]Ë[«Á3][‰ƒ]/is', '/[∆Ê][ÓoOŒ0][œÔn]/is', '/[®∏E≈eÂ][6·¡Ôœ]([‡¿aAÌÕHÛ”yË»Ú“T])/is', '/[·6¡ÏÃM][ÀÎ][ﬂˇ][^\s<]*/is', '/[ıxX’][Ûy”Y][ÀÎ][»Ë]/is', '/[ıx’X][Ûy”Y][ﬂˇ]/is', '/[ıxX’][Ûy”Y][®∏eÂE≈Ë»][^\s<]*/is', '/[œÔn][»Ë][ƒ‰][ÓoOŒ0‡¿aA][P–p]/is', '/[ÃMÏ][”Ûy][ƒ‰][¿‡Aa][ ÍKk]/is');
        
        $goodWords = array("ÙË„", "‚‡„ËÌ", "ÔÓÔ", "¯Î∏Ô$1", "ÛıÚ˚", "ÙË„ÎË", "ÙË„‡", "Ú‡Í ÒÂ·Â", "ÍÓÏÏÛÌËÒÚ", "„ÛÒ¸"); // ;D
        
        $message = preg_replace($badWords, $goodWords, $message);
        return $message;
    }
    
    /**
     * Start new menu
     */
    public function menu_begin() {
        $this->menu_initial = true;
    }
    
    /**
     * Print menu separator skipping first one.
     * Used in templates like this:
     * <?php $this->menu_begin() ?>
     * <?php foreach($this->menu as $item) ?>
     *   <?= $this->menusep() ?>
     *   <?= $item ?>
     * <?php endforeach; ?>
     * @param string $menusep  optional menu separator
     */
    public function menusep($menusep = null) {
        if(null === $menusep)
            $menusep = $this->menusep;
        
        if ($this->menu_initial) {
            $this->menu_initial = false;
        } else {
            echo $menusep;
        }
    }
    
    /**
     * Redirect relative to SITE_ROOT
     * @param string $path
     * @return \Kkein\AbstractResponse
     */
    public function redirect($path) {
        $redirect_url = "{$this->siteurl}$path";
        error_log("__REDIRECT__: $redirect_url");
        return $this->response->redirect("$redirect_url");
    }
    
    public function un_html_entities ($string)
    {
        $trans_tbl = get_html_translation_table (HTML_ENTITIES);
        $trans_tbl = array_flip ($trans_tbl);
        $trans_tbl['&#039;'] = "'";
        return strtr ($string, $trans_tbl);
    }
    
    /**
     * Convert text to utf-8 if charset is not utf-8
     */
    public function utf8($txt)
    {
        if(stripos($this->app->conf->charset, 'utf-8') === false && stripos($this->app->conf->charset, 'utf8') === false)
        {
            // charset is not UTF-8, convert to UTF-8
            return  mb_convert_encoding($txt, 'utf-8', $this->app->conf->charset);
        }
        else
            // probably already utf-8, just return as is
            return $txt;
    } // utf8
    
    public function strip_bb_code($text) {
        $pattern = '~\[[^\]]+\]~';
        return preg_replace($pattern, ' ', $text);
    }
}

?>
