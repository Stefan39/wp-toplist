<?php
/**
 * Toplist for Wordpress
 *
 * @author		WPler <plugins@wpler.com>
 * @version		1.0
 * @copyright	2012 WPLer <http://www.wpler.com>
 */
/*
Plugin Name:	WP-Toplist
Plugin URI:		http://www.wpler.com
Description:	Build your Toplist into your wordpress in seconds with Banners or text listings
Version:		1.0
Author:			WPler
Author URI:		http://www.wpler.com
License:		GPLv2
Text Domain:	wptoplist
Domain Path:	/i18n
*/
define("WPTOPLIST_SLUG", !defined("WPTOPLIST_SLUG") ? plugin_basename(__FILE__) : WPTOPLIST_SLUG);
define("WPTOPLIST_PATH", !defined("WPTOPLIST_PATH") ? plugin_dir_path(__FILE__) : WPTOPLIST_PATH);
define("WPTOPLIST_URL", !defined("WPTOPLIST_URL") ? plugin_dir_url(__FILE__) : WPTOPLIST_URL);
define("WPTOPLIST_FILE", !defined("WPTOPLIST_FILE") ? __FILE__ : WPTOPLIST_FILE);
// If class not exists/declared, include it
if (!class_exists('wptoplist'))
	include_once(WPTOPLIST_PATH.'/inc/wptoplist.class.php');
// Register the de-/activation hooks
register_activation_hook(WPTOPLIST_SLUG, array('wptoplist','activate'));
register_deactivation_hook(WPTOPLIST_SLUG, array('wptoplist','deactivate'));
// By init, starts the plugin class
add_filter('plugins_loaded', array('wptoplist','get_object'));
