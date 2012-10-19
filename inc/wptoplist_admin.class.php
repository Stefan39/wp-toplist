<?php
/**
 * The Admin-Class for the plugin
 * Shows and instantiate into the backend only
 *
 * @author		WPler <plugins@wpler.com>
 * @version		1.0
 * @copyright 	2012 WPler <http://www.wpler.com>
 */
class wptoplist_admin
{
	/**
	 * The static object of instantiate class
	 * @staticvar object
	 * @access private
	 */
	static private $_object = NULL;
	/**
	 * Default Settings
	 * @var array
	 * @access private
	 */
	private $_defaults = array(
		'wptl-tpl' 	=> "<table class='wptl-list'>\n<thead><tr>\n<th class='pos'>RANK</th>\n<th class='entry'>ENTRY</th>\n<th class='hits in'>HITS IN</th><th class='hits out'>HITS OUT</th>\n</tr>\n</thead>\n<tbody>%entry%</tbody>\n</table>",
		'wptl-entry'=> "<tr>\n<td class=\"pos\">%pos%</td>\n<td class=\"entry\">\n<h3><a href='%url%' target='_blank' class='wptl_trackit'>%title%</a></h3>\n<p class='description'>%description%</p>\n</td>\n<td class='hits in'>%hits_in%</td>\n<td class='hits out'>%hits_out%</td>\n</tr>",
		'tl_active'	=> 1,
		'wptl-open' => 1,
		'listform'	=> 'text',
		'showbanner'=> 10,
		'bannerwidth'=>468,
		'bannerheight'=>60,
		'reset'		=> 31,
		'ipblockin'	=> 60,
		'ipblockout'=> 10,
		'proxyin'	=> 1,
		'proxyout'	=> 0,
		'refin'		=> 1,
		'refout'	=> 1
	);
	/**
	 * Instantiation of the class
	 *
	 * @access public
	 * @static
	 * @since 1.0
	 * @return void
	 */
	static public function get_object() {
		if (is_null(self::$_object))
			self::$_object = new self();
		return self::$_object;
	}
	/**
	 * The constructor of the class
	 * Will doing some actions for the backend
	 *
	 * @access public
	 * @since 1.0
	 * @uses add_filter, add_action, get_option,
	 * @return void
	 */
	public function __construct() {
		$options = unserialize(get_option('wptl-options'));
		if (empty($options['tl_active']))
			add_action('admin_notices', array($this,'wptl_notice'));
		add_action('admin_init', array($this,'add_scripts'));
	}
	/**
	 * A notice for the admin, if the plugin are not configured
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function wptl_notice() {
		echo "<div id='notice' class='updated fade' style='padding:10px;'>".__('WP Toplist Plugin is not configured yet. Please <a href="/wp-admin/admin.php?page=wp-toplist/wp-toplist.php">configure it</a> now.', wptoplist::textdomain())."</div>";
	}
	/**
	 * Add the Backend Javascripts
	 *
	 * @access public
	 * @since 1.0
	 * @uses wp_enqueue_script,
	 * @return void
	 */
	public function add_scripts() {
		wp_enqueue_script('wptl', WPTOPLIST_URL.'js/wptl-admin.js',array('jquery'));
	}
	/**
	 * Add a toplist menu into the backend
	 *
	 * @access public
	 * @since 1.0
	 * @uses add_menu_page, add_submenu_page, add_action,
	 * @return void
	 */
	public function add_menu() {
		$pagehook = add_menu_page(__('WP Toplist Administration'), __('WP Toplist'), 'manage_options', WPTOPLIST_SLUG, array($this,'page_properties'),WPTOPLIST_URL.'/img/icon.png');
		add_submenu_page(WPTOPLIST_SLUG, __('WP Toplist Administration', wptoplist::textdomain()), __('Properties', wptoplist::textdomain()), 'manage_options', WPTOPLIST_SLUG, array($this,'page_properties'));
		add_submenu_page(WPTOPLIST_SLUG, __('WP Toplist Template', wptoplist::textdomain()), __('Template', wptoplist::textdomain()), 'manage_options', 'wptl-template', array($this,'page_template'));
		add_action('load-'.$pagehook, array($this,'load_hook'));
	}
	/**
	 * By loading first time the plugin options, remove the admin notice
	 *
	 * @access public
	 * @since 1.0
	 * @uses remove_action,
	 * @return void
	 */
	public function load_hook() {
		remove_action('admin_notices', array($this,'wptl_notice'));
	}
	/**
	 * Prints the property page into the backend
	 *
	 * @access public
	 * @since 1.0
	 * @uses current_user_can, get_option, esc_attr, absint, add_option,
	 * @return void
	 */
	public function page_properties() {
		if (!current_user_can('manage_options'))
			wp_die(__('You do not have the permission to access this page', wptoplist::textdomain()));
		$options = unserialize(get_option('wptl-options'));
		$options = wp_parse_args($options, $this->_defaults);
		if (isset($_POST['save'])) {
			$options['tl_active'] 	= !empty($_POST['tl_active']) && is_numeric($_POST['tl_active']) ? 1 : 0;
			$options['listform'] 	= !empty($_POST['listform']) && (esc_attr($_POST['listform']) == 'banner') ? 'banner' : 'text';
			$options['showbanner'] 	= !empty($_POST['showbanner']) && is_numeric($_POST['showbanner']) ? absint($_POST['showbanner']) : 10;
			$options['bannerwidth']	= !empty($_POST['bannerwidth']) && is_numeric($_POST['bannerwidth']) ? absint($_POST['bannerwidth']) : 468;
			$options['bannerheight']= !empty($_POST['bannerheight']) && is_numeric($_POST['bannerheight']) ? absint($_POST['bannerheight']) : 60;
			$options['reset']		= !empty($_POST['reset']) && is_numeric($_POST['reset']) ? absint($_POST['reset']) : 31;
			$options['ipblockin']	= !empty($_POST['ipblockin']) && is_numeric($_POST['ipblockin']) ? absint($_POST['ipblockin']) : 60;
			$options['ipblockout']	= !empty($_POST['ipblockout']) && is_numeric($_POST['ipblockout']) ? absint($_POST['ipblockout']) : 10;
			$options['proxyin']		= !empty($_POST['proxyin']) && $_POST['proxyin']==1 ? 1 : 0;
			$options['proxyout']	= !empty($_POST['proxyout']) && $_POST['proxyout']==1 ? 1 : 0;
			$options['refin']		= !empty($_POST['refin']) && $_POST['refin']==1 ? 1 : 0;
			$options['refout']		= !empty($_POST['refout']) && $_POST['refout']==1 ? 1 : 0;
			update_option('wptl-options', serialize($options));
		}
		?>
		<div class="wrap">
			<h2><?php _e('WP Toplist Properties', wptoplist::textdomain()); ?></h2>
			<form action="" method="post" id="wptlform">
				<table class="form-table">
					<tr>
						<th><label for="tl_active"><?php _e('WP Toplist active?', wptoplist::textdomain()); ?></label></th>
						<td>
							<input type="checkbox" name="tl_active" id="tl_active" value="1" <?php checked(1,$options['tl_active']); ?> />
							<span class="description"><?php _e('You can deactivate your toplist here. If you are deactivate the toplist, the shortcode will not be parsed.', wptoplist::textdomain()); ?></span>
						</td>
					</tr>
					<tr>
						<th><label for="tl_open"><?php _e('Toplist open for register', wptoplist::textdomain()); ?></label></th>
						<td>
							<input type="checkbox" name="wptl-open" id="tl_open" value="1" <?php checked(1,$options['wptl-open']); ?> />
							<span class="description"><?php _e('New users can register into the toplist and add an new entry.', wptoplist::textdomain()); ?></span>
						</td>
					</tr>
					<tr>
						<th><label for="listform"><?php _e('Form of list', wptoplist::textdomain()); ?></label></th>
						<td><select name="listform" id="listform" size="1">
							<option value="text" <?php selected('text', $options['listform']); ?>><?php _e('Text', wptoplist::textdomain()); ?></option>
							<option value="banner" <?php selected('banner', $options['listform']); ?>><?php _e('Banner', wptoplist::textdomain()); ?></option>
						</select>
						<span class="description"><?php _e('If you use "banner", you can control the max output of banner by entries into the shortcode. The text-only entries will be showing after your max amount of banner entries.', wptoplist::textdomain()); ?></span>
						</td>
					</tr>
					<tr class="showbanner">
						<th><label for="showbanner"><?php _e('How much banner should be show?', wptoplist::textdomain()); ?></label></th>
						<td><input type="text" name="showbanner" id="showbanner" value="<?php echo !empty($options['showbanner']) ? $options['showbanner'] : '10'; ?>" class="small-text" />
						<span class="description"><?php _e('How much entries should be shown with image-banner on your toplist? The rest will showing only the title and description without banner-image.', wptoplist::textdomain()); ?></span></td>
					</tr>
					<tr class="showbanner">
						<th><label for="width"><?php _e('Banner-Width', wptoplist::textdomain()); ?></label></th>
						<td><input type="text" name="bannerwidth" id="width" value="<?php echo !empty($options['bannerwidth']) ? absint($options['bannerwidth']) : 468; ?>" class="small-text" />
						<span class="description"><?php _e('Pixel', wptoplist::textdomain()); ?></span></td>
					</tr>
					<tr class="showbanner">
						<th><label for="height"><?php _e('Banner-Height', wptoplist::textdomain()); ?></label></th>
						<td><input type="text" name="bannerheight" id="height" value="<?php echo !empty($options['bannerheight']) ? absint($options['bannerheight']) : 60; ?>" class="small-text" />
						<span class="description"><?php _e('Pixel', wptoplist::textdomain()); ?></span></td>
					</tr>
					<tr>
						<th><label for="tlreset"><?php _e('Toplist Reset', wptoplist::textdomain()); ?></label></th>
						<td><input type="text" name="reset" id="tlreset" value="<?php echo !empty($options['reset']) ? absint($options['reset']) : 31; ?>" class="small-text" />
						<span class="description"><?php _e('In how much days the toplist should be reset?', wptoplist::textdomain()); ?></span></td>
					</tr>
					<tr>
						<th><label for="ip-in"><?php _e('Block IP incoming Hit', wptoplist::textdomain()); ?></label></th>
						<td><input type="text" name="ipblockin" id="ip-in" value="<?php echo !empty($options['ipblockin']) ? absint($options['ipblockin']) : 60; ?>" class="small-text" />
						<span class="description"><?php _e(sprintf('Block the IP by incoming hit for <span>%d</span> seconds',$options['ipblockin']), wptoplist::textdomain()); ?></span></td>
					</tr>
					<tr>
						<th><label for="ip-out"><?php _e('Block IP outgoing Hit', wptoplist::textdomain()); ?></label></th>
						<td><input type="text" name="ipblockout" id="ip-out" value="<?php echo !empty($options['ipblockout']) ? absint($options['ipblockout']) : 10; ?>" class="small-text" />
						<span class="description"><?php _e(sprintf('Block the IP by outgoing hit for <span>%d</span> seconds',$options['ipblockout']), wptoplist::textdomain()); ?></span></td>
					</tr>
					<tr>
						<th><?php _e('Block Proxy Hit in', wptoplist::textdomain()); ?></th>
						<td>
							<input type="radio" name="proxyin" id="proxy-in" value="1" <?php checked(1,absint($options['proxyin'])); ?> />
							<label for="proxy-in"><?php _e('Yes', wptoplist::textdomain()); ?></label> |
							<input type="radio" name="proxyin" id="proxy-in-no" value="0" <?php checked(0,absint($options['proxyin'])); ?> />
							<label for="proxy-in-no"><?php _e('No',wptoplist::textdomain()); ?></label>
							<span class="description"><?php _e('Checking the hits of Forwarder and block it, if one (mostly proxies)',wptoplist::textdomain()); ?></span>
						</td>
					</tr>
					<tr>
						<th><?php _e('Block Proxy Hit out', wptoplist::textdomain()); ?></th>
						<td>
							<input type="radio" name="proxyout" id="proxy-out" value="1" <?php checked(1,absint($options['proxyout'])); ?> />
							<label for="proxy-out"><?php _e('Yes', wptoplist::textdomain()); ?></label> |
							<input type="radio" name="proxyout" id="proxy-out-no" value="0" <?php checked(0,absint($options['proxyout'])); ?> />
							<label for="proxy-out-no"><?php _e('No', wptoplist::textdomain()); ?></label>
							<span class="description"><?php _e('Checking the hits of Forwarder and block it, if one (mostly proxies - only neccessary, if use sorting by outgoing hits on the toplist)', wptoplist::textdomain()); ?></span>
						</td>
					</tr>
					<tr>
						<th><?php _e('Referer Check Hit in', wptoplist::textdomain()); ?></th>
						<td>
							<input type="radio" name="refin" id="refin" value="1" <?php checked(1,absint($options['refin'])); ?> />
							<label for="refin"><?php _e('Yes', wptoplist::textdomain()); ?></label> |
							<input type="radio" name="refin" id="refin-no" value="0" <?php checked(0,absint($options['refin'])); ?> />
							<label for="refin-no"><?php _e('No', wptoplist::textdomain()); ?></label>
							<span class="description"><?php _e('Count hits only if the referer are the same as incoming siteurl', wptoplist::textdomain()); ?></span>
						</td>
					</tr>
					<tr>
						<th><?php _e('Referer Check Hit out', wptoplist::textdomain()); ?></th>
						<td>
							<input type="radio" name="refout" id="refout" value="1" <?php checked(1,absint($options['refout'])); ?> />
							<label for="refout"><?php _e('Yes', wptoplist::textdomain()); ?></label> |
							<input type="radio" name="refout" id="refout-no" value="0" <?php checked(0,absint($options['refout'])); ?> />
							<label for="refout-no"><?php _e('No', wptoplist::textdomain()); ?></label>
							<span class="description"><?php _e('Count hits only if the referer are the same as toplist url', wptoplist::textdomain()); ?></span>
						</td>
					</tr>
				</table>
				<p class="submit"><input type="submit" name="save" value="<?php _e('Save properties', wptoplist::textdomain()); ?>" class="button-primary" /></p>
			</form>
		</div>
		<?php
	}
	/**
	 * Prints the settings-page for the template
	 *
	 * @access public
	 * @since 1.0
	 * @uses get_option, update_option,
	 * @return void
	 */
	public function page_template() {
		$options = unserialize(get_option('wptl-options'));
		$options = wp_parse_args($options, $this->_defaults);
		if (isset($_POST['save'])) {
			if (!empty($_POST['wptl-tpl']) && ($_POST['wptl-tpl'] != $options['wptl-tpl']))
				$options['wptl-tpl'] = $_POST['wptl-tpl'];
			if (!empty($_POST['wptl-entry']) && ($_POST['wptl-entry']!=$options['wptl-entry']))
				$options['wptl-entry'] = $_POST['wptl-entry'];
			update_option('wptl-options', serialize($options));
		}
		?>
		<div class="wrap">
			<h2><?php _e('WP Toplist Template', wptoplist::textdomain()); ?></h2>
			<form action="" method="post">
				<table class="form-table">
					<tr>
						<th><label for="wptl-tpl"><?php _e('How look like the Output of the listings?', wptoplist::textdomain()); ?></label></th>
						<td>
							<textarea name="wptl-tpl" id="wptl-tpl" rows="10" cols="40" class="large-text"><?php echo !empty($options['wptl-tpl']) ? esc_html(stripslashes($options['wptl-tpl'])) : ''; ?></textarea>
							<span class="description"><?php _e('You can use follow Replace-Strings:<ul><li><b>%entry%</b> - Replace the Entry-Template for every entry into the toplist</li></ul>', wptoplist::textdomain()); ?></span>
						</td>
					</tr>
					<tr>
						<th><label for="wptl-entry"><?php _e('How looks like the output of an entry?', wptoplist::textdomain()); ?></label></th>
						<td>
							<textarea name="wptl-entry" id="wptl-entry" rows="10" cols="40" class="large-text"><?php echo !empty($options['wptl-entry']) ? esc_html(stripslashes($options['wptl-entry'])) : ''; ?></textarea>
							<span class="description"><?php _e('You can use follow Replace-Strings:<ul><li><b>%id%</b> - The ID of this Toplist-Post</li><li><b>%pos%</b> - The Ranking-Position</li><li><b>%url%</b> - The URL of the entry</li><li><b>%title%</b> - The title of the entry</li><li><b>%description%</b> - The Text-Description of the entry</li><li><b>%banner%</b> - The Banner-URL of the entry</li><li><b>%hits_in%</b> - The amount of hits in</li><li><b>%hits_out%</b> - The amount of hits out</li></ul><b>Be aware,</b> use the css-class "wptl_trackit" into the A-Tag of the entry to track the outgoing clicks!', wptoplist::textdomain()); ?></span>
						</td>
					</tr>
				</table>
				<p class="submit"><input type="submit" name="save" id="save" value="<?php _e('Save template', wptoplist::textdomain()); ?>" /></p>
			</form>
			<h3><?php _e('How you use it', wptoplist::textdomain()); ?></h3>
			<p><?php _e('This template settings will be used for output the entries by the shortcode "[wptl-listing show="10" paging="1"] into your choosed page.', wptoplist::textdomain()); ?></p>
			<p><?php _e('As first, the template for "listing" will be the outer template (by default the defined table tag). As second every entry will be showing like you defined into the output of the entry (by default defined tr rows).', wptoplist::textdomain()); ?></p>
			<p><?php _e('The shortcode wich you can use is [wptl-listing] and you can defined followed parameters:', wptoplist::textdomain()); ?></p>
			<ul>
				<li><?php _e('<b>show="X"</b> - like [wptl-listings show="10"] will showing 10 entries into your list', wptoplist::textdomain()); ?></li>
				<li><?php _e('<b>paging="1"</b> - like [wptl-listings paging="1"] will activate the paging between your entries. If you have 100 entries and showing (by default) 10 entries on one page, your page will paging with 10 pages with a limit of 10 entries.', wptoplist::textdomain()); ?></li>
				<li><?php _e('<b>banner="1"</b> - like [wptl-listings banner="10"] will show on the first 10 entries with banner output, all others with title and description only.', wptoplist::textdomain()); ?></li>
			</ul>
			<p><?php _e('You can combine this parameters, like: [wptl-listings show="20" paging="1" banner="10"] - will show all entries with paging with 20 entries on every page and the first 10 entries with banner, all other with title and description only.', wptoplist::textdomain()); ?></p>
			<p><?php _e('<strong>!!!Important:</strong> Into the Outlink of an entry put the CSS <u>class="wptl_trackit"</u> and the Attribte <u>data-id="%id%"</u> for tracking outgoing hits of this entry. Without this both none outgoing click will be tracked.', wptoplist::textdomain()); ?></p>
		</div>
		<?php
	}
	/**
	 * Returns the default-options
	 *
	 * @access public
	 * @since 0.1
	 * @static
	 * @return array
	 */
	static public function get_defaults() {
		return self::get_object()->_defaults;
	}
}
