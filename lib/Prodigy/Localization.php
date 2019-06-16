<?php

namespace Prodigy;

class Localization {

    private $app;
    private $router;
    public $lngfile;
    
    public $txt;
    public $img;
    public $menusep;
    
    public function __construct($router) {
        $this->router = $router;
        $this->app = $router->app();
        $lngfile = null;
    }
    
    /**
     * Load localization file (translation strigs)
     * @param string $locale language name
     */
    public function set_locale ($locale) {
        // define some variables from config which are used in localization file
        $imagesdir = $this->app->conf->imagesdir;
        $langimages = $this->app->conf->get('langimages', ''); // FIXME
        $MenuType = $this->app->conf->MenuType;
        $db_prefix = $this->app->conf->db_prefix;
        $YaBBversion = $this->app->conf->YaBBversion;
        $boardurl = $this->app->conf->boardurl;
        $mbname = $this->app->conf->mbname;
        $menusep = $this->app->conf->menusep;
        $webmaster_email = $this->app->conf->webmaster_email;
        $color = $this->app->conf->color;
        $ClickLogTime = '$ClickLogTime'; // FIXME
        $MaxSigLen = $this->app->conf->MaxSigLen;
        $faketruncation = '$faketruncation'; // FIXME
        $GodPostNum = $this->app->conf->GodPostNum;
        $TopAmmount = $this->app->conf->TopAmmount;
        $SrPostNum = $this->app->conf->SrPostNum;
        $FullPostNum = $this->app->conf->FullPostNum;
        $JrPostNum = $this->app->conf->JrPostNum;
        
        require_once('Localization/' . $locale);
        
        $vars = get_defined_vars();
        
        foreach ($vars as $k => $v) {
            $this->$k = $v;
        }
        
        $this->lngfile = $locale;
    } // set_locale()
    
    public function txt($name, $default = '') {
        if (null === $this->lngfile)
            $this->set_locale($this->app->conf->language);
        
        if (isset($this->txt[$name]))
            return $this->txt[$name];
        elseif (isset($this->img[$name]))
            return $this->img[$name];
        else
            return $default;
    }
    
    public function __get($name) {
        return $this->txt($name);
    }
    
    public function __invoke($name, $default = '') {
        return $this->txt($name, $default);
    }

}
