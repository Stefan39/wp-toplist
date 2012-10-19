<?php
/**
 * The Main class for the plugin.
 * Doing the (de-)activation, add the main hooks/filters
 *
 * @author		WPler <plugins@wpler.com>
 * @version 	1.0
 * @copyright	2012 WPler <http://www.wpler.com>
 */
class wptoplist
{
	/**
	 * Class object
	 * @staticvar object
	 * @access private
	 */
	static private $_object = NULL;
	/**
	 * Returns an instantiate object of the class
	 *
	 * @access public
	 * @static
	 * @since 1.0
	 * @return object
	 */
	static public function get_object() {
		if (is_null(self::$_object))
			self::$_object = new self;
		return self::$_object;
	}
	/**
	 * The constructor of the class
	 *
	 * @access public
	 * @since 1.0
	 * @uses load_plugin_textdomain, is_admin,
	 * @return void
	 */
	public function __construct() {
		load_plugin_textdomain($this->get_header('TextDomain'), FALSE, $this->get_header('DomainPath'));
		if (!class_exists('wptoplist_cpt'))
			include_once(WPTOPLIST_PATH.'/inc/wptoplist_cpt.class.php');
		wptoplist_cpt::get_object();
		if (!class_exists('wptoplist_shortcodes'))
			include_once(WPTOPLIST_PATH.'/inc/wptoplist_shortcodes.class.php');
		$shortcodes = new wptoplist_shortcodes();
		// add wp-ajax Action for tracking hits out
		add_action('wp_ajax_nopriv_wptl_trackit', array('wptoplist_tracking','wptl_hit_out'));
		add_action('init', array('wptoplist','wptl_toplist_rewrite'));
		if (!class_exists('wptoplist_tracking'))
			include_once(WPTOPLIST_PATH.'/inc/wptoplist_tracking.class.php');
		add_action('template_redirect', array('wptoplist_tracking','wptl_hit_in'));
		if (is_admin()) {
			if (!class_exists('wptoplist_admin'))
				include_once(WPTOPLIST_PATH.'/inc/wptoplist_admin.class.php');
			add_action('admin_init', array('wptoplist_admin','get_object'));
			add_action('admin_menu', array(wptoplist_admin::get_object(),'add_menu'));
		}
	}
	/**
	 * Shortcode-Parser
	 *
	 * @access public
	 * @since 1.0
	 * @uses wp_verify_nonce, absint, get_post_meta, update_post_meta, wp_die,
	 * @return void
	 */
	public function wptl_hit_out() {
		if (wp_verify_nonce($_REQUEST['nonce'])) wp_die('Busted! Wrong Nonce Key');
		$postid = absint($_REQUEST['id']);
		$hits = absint(get_post_meta($postid,'hits_out',TRUE));
		update_post_meta($postid,'hits_out',$hits+1);
		return $hits;
	}
	/**
	 * Returns the Textdomain of the plugin
	 *
	 * @access public
	 * @static
	 * @since 1.0
	 * @return string
	 */
	static public function textdomain() {
		return self::$_object->get_header('TextDomain');
	}
	/**
	 * Method for activating the plugin to install the tables and neccessary
	 * options.
	 *
	 * @access public
	 * @static
	 * @since 1.0
	 * @uses wp_delta, add_option, flush_rewrite_rules,
	 * @return void
	 */
	static public function activate() {
		global $wpdb;
		$charset = !empty($wpdb->charset) ? "DEFAULT CHARACTER SET {$wpdb->charset}" : '';
		$stmt = "CREATE TABLE {$wpdb->prefix}wptl_sites (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
siteurl varchar(255) NOT NULL,
userid bigint(20) unsigned NOT NULL,
bannerurl varchar(255) NULL,
hits_in bigint(20) unsigned NOT NULL DEFAULT 0,
hits_out bigint(20) unsigned NOT NULL DEFAULT 0,
active timestamp NULL,
PRIMARY KEY  (id),
UNIQUE KEY siteurl (siteurl)
) {$charset};";
		require_once(ABSPATH.'/wp-admin/includes/upgrade.php');
		dbDelta($stmt);
		$stmt2 = "CREATE TABLE {$wpdb->prefix}wptl_referer (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
siteid bigint(20) unsigned NOT NULL,
referer varchar(255) NOT NULL,
PRIMARY KEY  (id)
) {$charset};";
		dbDelta($stmt2);
		add_option('wptl-option',serialize(array('version'=>self::get_object()->get_header('Version'))));
		self::wptl_toplist_rewrite();
		flush_rewrite_rules();
	}
	/**
	 * Methode tfor deactivating the plugin to uninstall the tables and options
	 *
	 * @access public
	 * @static
	 * @since 1.0
	 * @uses delete_option, flush_rewrite_rules,
	 * @return void
	 */
	static public function deactivate() {
		global $wpdb;
		if ($wpdb->query("SHOW TABLES LIKE '{$wpdb->prefix}wptl_sites'") != 0)
			$wpdb->query("DROP TABLE {$wpdb->prefix}wptl_sites");
		if ($wpdb->query("SHOW TABLES LIKE '{$wpdb->prefix}wptl_referer'") != 0)
			$wpdb->query("DROP TABLE {$wpdb->prefix}wptl_referer");
		$options = unserialize(get_option('wptl-options'));
		if (!empty($options)) { delete_option('wptl-options');}
		flush_rewrite_rules();
	}
	/**
	 * Adds a new permalink structure for the toplist
	 *
	 * @access public
	 * @static
	 * @since 1.0
	 * @uses add_rewrite_tag, add_permastruct,
	 * @return void
	 */
	static public function wptl_toplist_rewrite() {
		//add_rewrite_tag('%toplist%','([^/]+)');
		//add_permastruct('toplist','toplist/%toplist%');
		add_rewrite_endpoint('toplist',EP_ALL);
	}
	/**
	 * Tracked incoming hits by entries includes the properties for toplist
	 * and prints the entries over the page-template
	 *
	 * @access public
	 * @static
	 * @uses get_query_var,
	 * @return void
	 */
	static public function wptl_show_toplist() {
		if (get_query_var('toplist')) {
			if (($id=get_query_var('entry'))) {

			}
		}
		$entry = get_query_var('toplist');
		print $entry;
		exit;
	}
	/**
	 * Returns the value of the plugin head-parameter $param
	 *
	 * @param string $param
	 * @access public
	 * @since 1.0
	 * @uses get_plugin_data,
	 * @return mixed
	 */
	public function get_header($param) {
		if (!function_exists('get_plugin_data') && !file_exists(ABSPATH.'/wp-admin/includes/plugin.php'))
			return '';
		if (!function_exists('get_plugin_data'))
			require_once(ABSPATH.'/wp-admin/includes/plugin.php');
		$data = get_plugin_data(WPTOPLIST_FILE);
		return !empty($data[$param]) ? $data[$param] : $param;
	}
}
