<?php
/**
 * This class handles the shortcodes using by the plugin
 *
 * @author		WPler <plugins@wpler.com>
 * @version		1.0
 * @copyright	2012 WPler <http://www.wpler.com>
 */
class wptoplist_shortcodes
{
	/**
	 * Construction of the class
	 *
	 * @access public
	 * @since 1.0
	 * @uses add_shortcode, add_action,
	 * @return void
	 */
	public function __construct() {
		add_shortcode('wptl-register', array($this,'sc_register'));
		add_shortcode('wptl-listings', array($this,'sc_listings'));
		add_action('wp_enqueue_scripts', array($this,'add_script'));
	}
	/**
	 * For tracking hits add the javascript to frontend
	 *
	 * @access public
	 * @since 1.0
	 * @uses wp_enqueue_script, wp_localize_script, admin_url, wp_create_nonce,
	 * @return void
	 */
	public function add_script() {
		wp_enqueue_script('wptl',WPTOPLIST_URL.'/js/wptl-script.js',array('jquery'),'1.0',true);
		wp_localize_script('wptl','wptl',array(
			'ajaxurl' 	=> admin_url('admin-ajax.php'),
			'nonce'		=> wp_create_nonce('wptl'),
		));
	}
	/**
	 * The new registration form for registering new toplist-users
	 *
	 * @access public
	 * @since 1.0
	 * @uses
	 * @return void
	 */
	public function sc_register() {
		if (!isset($_GET['do']) && $_GET['do'] != 'register')
			return FALSE;
		$errors = array();
		if (empty($_POST['user']) || empty($_POST['email']))
			$errors[] = __('Provide a user and email address', wptoplist::textdomain());
		if (!empty($_POST['spam']))
			$errors[] = 'gtfo spammer';
		$user_login = esc_attr($_POST['user']);
		$user_email = esc_attr($_POST['email']);
		require_once(ABSPATH.WPINC.'/registration.php');

		$sanitized_login = sanitize_user($user_login);
		$user_email = apply_filters('user_registration_email', $user_email);

		if (!is_email($user_email))
			$errors[] = __('Invalid E-Mail address', wptoplist::textdomain());
		elseif (email_exists($user_email))
			$errors[] = __('This E-Mail is already registered.', wptoplist::textdomain());

		if (empty($sanitized_login) || !validate_username($sanitized_login))
			$errors[] = __('Invalid Username', wptoplist::textdomain());
		elseif(username_exists($sanitized_login))
			$errors[] = __('Username already exists.', wptoplist::textdomain());

		if (empty($errors)) {
			$user_pass = wp_generate_password();
			$user_id = wp_create_user($sanitized_login, $user_pass, $user_email);
			if (!$user_id)
				$errors[] = __('Registration failed. The user cannot created.',wptoplist::textdomain());
			else {
				update_user_option($user_id, 'default_passwort_nag', TRUE, TRUE);
				wp_new_user_notification($user_id, $user_pass);
			}
		}
		if (!empty($errors))
			define("REGISTRATION_ERROR", serialize($errors));
		else
			define("REGISTERED_A_USER", $user_email);
	}
	/**
	 * Prints the listings from the toplist
	 *
	 * @param array $attr 	The Attributes
	 * @access public
	 * @since 1.0
	 * @uses
	 * @return void
	 */
	public function sc_listings($attr) {
		if (isset($_GET['id'])) {
			print_r($_GET);
			wp_die();
		}
		$options = unserialize(get_option('wptl-options'));
		if (!class_exists('wptoplist_admin'))
			include_once(WPTOPLIST_PATH.'/inc/wptoplist_admin.class.php');
		$options = wp_parse_args($options, wptoplist_admin::get_defaults());
		$out = '';
		$rank = 1;
		$args = array(
			'post_type'			=> 'toplist',
			'orderby'			=> 'meta_value',
			'order'				=> 'DESC',
			'meta_key'			=> 'hits_in',
			'posts_per_page'	=> !empty($attr['show']) ? absint($attr['show']) : 10
		);
		if (isset($attr['banner']) && $attr['banner']==1) {
			$pattern = array('%id%','%pos%','%url%','%title%','%description%','%banner%','%hits_in%','%hits_out%');
		} else {
			$pattern = array('%id%','%pos%','%url%','%title%','%description%','%hits_in%','%hits_out%');
		}
		$posts = new WP_Query($args);
		if ($posts->have_posts()) {
			while($posts->have_posts()) {
				$posts->the_post();
				$postid = get_the_ID();
				$imageid = get_post_thumbnail_id($postid);
				$banner = wp_get_attachment_image_src($imageid,'full');
				if (isset($attr['banner']) && $attr['banner']==1) {
					$replace = array($postid,$rank,esc_url(get_post_meta($postid,'wptl-url',TRUE)),esc_attr(get_the_title($postid)),get_the_content($postid),$banner[0],get_post_meta($postid,'hits_in',TRUE),get_post_meta($postid,'hits_out',TRUE));
				} else {
					$replace = array($postid,$rank,esc_url(get_post_meta($postid,'wptl-url',TRUE)),esc_attr(get_the_title($postid)),get_the_content($postid),get_post_meta($postid,'hits_in',TRUE),get_post_meta($postid,'hits_out',TRUE));
				}
				$out .= str_replace($pattern,$replace,stripslashes($options['wptl-entry']));
				$rank++;
			}
		}
		$out = str_replace(array('%entry%'),array($out),stripslashes($options['wptl-tpl']));
		return $out;
	}
}
