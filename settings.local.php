<?php
/* ### Some custom settings ### */

$config = array();

$config['charset'] = 'cp1251';

$config['static_root'] = '/reforum/static';
$config['site_root'] = '/reforum';

$config['serial'] = 'TPR-F-S';

/**
 * Infect Bad Guy settings
 */
$config['badIPs'] = array(
        "218.144.146.202",
	"178.210.45.208"
	); 
$config['badAgents'] = array(
			//"Opera/9.80 (Windows NT 6.1; U; ru) Presto/2.6.30 Version/10.63"
		);
$config['clearIps'] = array(
	);
$config['clearAgents'] = array(
	);
		

// List of user IDs which have global moderators priveleges without official status
$config['hiddenModerators'] = array(71393);

// Exclude some admins from admin notificatins
// ID's or nicknames accepted
$config['dontNotifyAdmins'] = array(1);

/**
 * List of dumbfucks not allowed to smite
 */
$config['smite_not_allowed'] = array('DX');

/**
 * Don't show messages and comments of ignored users
 */
 $config['hard_ignore'] = true;

/**
 * Static ignore list
 */
 $config['forced_ignore'] = array(
     // ID_MEMBER => ('memberName', 'memberName', ...),
     182 => array('Chega932006', 'DX'),
     1234 => array()
 );
 
/**
 * Default session config
 */
$sessionconfig = array('compact_mode' => 'auto');

/**
 * Temporary settings for local debug
 */
// $boardurl = "http://forum.babushka.club";
// $facesdir = "/var/www/forum/new/YaBBImages/avatars";

// $session_conf['mobile_view'] = false;
// $Mobile_UA = false;

/**
 * Optional headers config
 */
// Content-Security-Policy: upgrade-insecure-requests
$config['CSPUIR'] = false;
// Strict Transport Security
$config['HSTS'] = true;
$config['HSTS_Age'] = 86400;

// Debug mode true/false
$config['debug'] = true;

// Autoescape SQL requests
$config['sql_autoescape'] = true;

// Xmas icons: enable|disable|auto
$config['xmas'] = 'auto';

// New Year snowing animation
$config['snowing_enabled'] = true;

// Notify users to IM of moderators actions
$config['notify_users'] = true;

// Disable countinuous ajax updates
$config['disable_ajax_updates'] = true;

// Enable infectBadGuy function
$config['InfectBadGuy'] = false;

// Sphinx search engine config
$config['sphinx'] = array(
    'enabled' => true,
    'client' => '/usr/share/sphinxsearch/api/sphinxapi.php',
    //'client' => '/opt/sphinx/api/sphinxapi-fixed.php',
    'server' => 'localhost',
    'port' => '9312',
    'index' => 'prodigy_forum',
    'index_primary' => 'prodigy_forum_main',
    'pagesize' => 25,
    'max_matches' => 250,
);

// Cloud settings
$config['cloud'] = array(
    "scopes" => array(
        'https://www.googleapis.com/auth/photoslibrary.readonly',
        //'https://www.googleapis.com/auth/photoslibrary',
        'https://www.googleapis.com/auth/photoslibrary.sharing',
        'https://www.googleapis.com/auth/photoslibrary.appendonly',
        'https://www.googleapis.com/auth/photoslibrary.readonly.appcreateddata'
    ),
    "albumID" => 'qwerty',
    "credentials" => array(
        "client_id" => "qwerty.apps.googleusercontent.com",
        "project_id" => "qwerty",
        "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
        "token_uri" => "https://www.googleapis.com/oauth2/v3/token",
        "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
        "client_secret" => "qwerty",
        "redirect_uris" => array(
            "urn:ietf:wg:oauth:2.0:oob",
            "http://localhost"
        ),
        "refresh_token" => 'qwerty'
    )
);

?>
