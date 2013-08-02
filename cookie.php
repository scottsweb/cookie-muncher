<?php

/*
	Cookie Muncher
	------------------------
	
	Plugin Name: EU Cookie Muncher
	Plugin URI: http://codecanyon.net/item/eu-cookie-muncher/2300176?ref=scottsweb
	Description: Instant compliance for the EU Cookie Directive / EU Cookie Law
	Author: Scott Evans
	Version: 1.3
	Author URI: http://scott.ee

*/
	
	/** define some constants **/
	define('COOKIE_MUNCH_JS_URL',plugins_url('/assets/js',__FILE__));
	define('COOKIE_MUNCH_CSS_URL',plugins_url('/assets/css',__FILE__));
	define('COOKIE_MUNCH_IMAGES_URL',plugins_url('/assets/images',__FILE__));
	define('COOKIE_MUNCH_CACHE_URL',plugins_url('/assets/cache',__FILE__));
	define('COOKIE_MUNCH_PATH', dirname(__FILE__));
	define('COOKIE_MUNCH_CACHE', COOKIE_MUNCH_PATH.'/assets/cache');
	define('COOKIE_MUNCH_BASE', plugin_basename(__FILE__));
	define('COOKIE_MUNCH_FILE', __FILE__);
	
	/** load language files **/
	load_plugin_textdomain('cm', false, dirname(COOKIE_MUNCH_BASE) . '/assets/languages/' );
	
	/** boot the plugin **/
	if (is_admin()) { include(COOKIE_MUNCH_PATH . '/assets/inc/cookie-admin.php'); }
	if (!is_admin()) { include(COOKIE_MUNCH_PATH . '/assets/inc/cookie-theme.php'); }
	if (!is_admin()) { include(COOKIE_MUNCH_PATH . '/assets/inc/cookie-shortcode.php'); }

?>