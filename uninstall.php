<?php
/**
 * If the admin uninstall the plugin over the uninstall routine from wp, do
 * this actions:
 *
 * @author		WPler <plugins@wpler.com>
 * @version 	1.0
 * @copyright	2012 WPler <http://www.wpler.com>
 */

// If not uninstiall defined, exit the script:
if (!defined('WP_UNINSTALL_PLUGIN')){exit;}

// Delete the tables, if exists:
if ($wpdb->query("SHOW TABLES LIKE '{$wpdb->prefix}wptl_sites'") != 0)
	$wpdb->query("DROP TABLE {$wpdb->prefix}wptl_sites");
if ($wpdb->query("SHOW TABLES LIKE '{$wpdb->prefix}wptl_referer'") != 0)
	$wpdb->query("DROP TABLE {$wpdb->prefix}wptl_referer");

// Delete the options, if exists:
$wptl_options = unserialize(get_option('wptl-options'));
if (!empty($wptl_options)) delete_option('wptl-options');