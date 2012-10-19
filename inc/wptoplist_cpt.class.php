<?php
/**
 * the Custom-Post-Type Class for WP Toplist
 *
 * @author		WPler <plugins@wpler.com>
 * @version		1.0
 * @copyright	2012 WPler <http://www.wpler.com>
 */
class wptoplist_cpt
{
	/**
	 * The Class object
	 *
	 * @staticvar object
	 * @access private
	 */
	static private $_object = NULL;
	/**
	 * The constructor of the class
	 *
	 * @access public
	 * @since 1.0
	 * @uses add_filter,
	 * @return void
	 */
	public function __construct() {
		add_filter('init', array($this,'register_cpt'));
		add_filter('save_post', array($this,'save_post'));
		add_filter('manage_edit-toplist_columns',array($this,'cpt_columns'));
		add_action('manage_toplist_posts_custom_column', array($this,'add_cpt_column_content'), 10, 2);
		add_filter('manage_edit-toplist_sortable_columns',array($this,'sortable_columns'));
		add_action('load-edit.php',array($this,'add_sortable_filter'));
	}
	/**
	 * Instantiate this object
	 *
	 * @access public
	 * @static
	 * @since 1.0
	 * @return object
	 */
	static public function get_object() {
		if (is_null(self::$_object))
			self::$_object = new self();
		return self::$_object;
	}
	/**
	 * Register the new CPT for toplist-entries
	 *
	 * @access public
	 * @since 1.0
	 * @uses __(), register_post_type,
	 * @return void
	 */
	public function register_cpt() {
		$labels = array(
			'name'					=> __('Toplist', wptoplist::textdomain()),
			'singular_name'			=> __('Toplist', wptoplist::textdomain()),
			'add_new'				=> __('New Entry', wptoplist::textdomain()),
			'add_new_item'			=> __('Add new entry', wptoplist::textdomain()),
			'edit_item'				=> __('Edit entry', wptoplist::textdomain()),
			'new_item'				=> __('New entry', wptoplist::textdomain()),
			'view_item'				=> __('Show entry', wptoplist::textdomain()),
			'search_items'			=> __('Search entry', wptoplist::textdomain()),
			'not_found'				=> __('No entry found', wptoplist::textdomain()),
			'not_found_in_trash'	=> __('No entry found into the trash', wptoplist::textdomain()),
			'all_items'				=> __('All entries', wptoplist::textdomain())
		);
		$supports = array(
			'title',
			'editor',
			'author',
			'thumbnail',
			'custom-fields'
		);
		register_post_type('toplist',array(
			'register_meta_box_cb' 	=> array($this,'meta_boxes'),
			'labels' 				=> $labels,
			'supports'				=> $supports,
			'public'				=> TRUE,
			'description'			=> __('This CPT are for the toplist-entries only and listed all Toplist Entries into your blog.', wptoplist::textdomain()),
			'rewrites'				=> array(
				'slug'				=> 'toplist'
			)
		));
	}
	/**
	 * Register the new metaboxes for the CPT
	 *
	 * @access public
	 * @since 1.0
	 * @uses add_meta_box,
	 * @return void
	 */
	public function meta_boxes() {
		add_meta_box('wptl-url', __('The URL of the Domain', wptoplist::textdomain()), array($this,'wptl_url'),'toplist','normal','high');
	}
	/**
	 * Metabox URL
	 *
	 * @param object $page
	 * @access public
	 * @since 1.0
	 * @uses
	 * @return void
	 */
	public function wptl_url($page) {
		$url = get_post_meta($page->ID, 'wptl-url', TRUE);
		?>
		<table class="form-table">
			<tr>
				<th class="row-title"><label for="wptl-url"><?php _e('The Domain-URL for this Entry', wptoplist::textdomain()); ?></label></th>
				<td><input type="text" name="wptl-url" id="wptl-url" tabindex="2" value="<?php echo esc_url($url); ?>" autocomplete="off" style="width:100%;" /></td>
			</tr>
		</table>
		<?php
	}
	/**
	 * save the custom-metabox values for this CPT
	 *
	 * @param int $postid
	 * @access public
	 * @since 1.0
	 * @uses
	 * @return void
	 */
	public function save_post($postid) {
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		update_post_meta($postid, 'hits_in', 0);
		update_post_meta($postid, 'hits_out', 0);
	}
	/**
	 * Showing the hits-in, hits-out and url into the listings on backend
	 *
	 * @param array $columns
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function cpt_columns($columns) {
		$columns = array(
			'cb' 		=> '<input type="checkbox" />',
			'date'		=> __('Date', wptoplist::textdomain()),
			'title'		=> __('Entry Title', wptoplist::textdomain()),
			'url'		=> __('URL', wptoplist::textdomain()),
			'hits_in'	=> __('Hits IN', wptoplist::textdomain()),
			'hits_out'	=> __('Hist Out', wptoplist::textdomain()),
			'user'		=> __('Username', wptoplist::textdomain())
		);
		return $columns;
	}
	/**
	 * Add the content into the defined columns
	 *
	 * @param string $column
	 * @param int $postid
	 * @access public
	 * @since 1.0
	 * @uses get_post_meta,
	 * @return void
	 */
	public function add_cpt_column_content($column, $postid) {
		switch($column) {
			case 'url':
				$url = get_post_meta($postid, 'wptl-url', TRUE);
				if (empty($url)) echo __('Unknown', wptoplist::textdomain());
				else printf(__('<a href="%1$s" target="_blank">%1$s</a>',wptoplist::textdomain()),$url);
				break;
			case 'hits_in':
				$hitsin = get_post_meta($postid,'hits_in', TRUE);
				echo $hitsin;
				break;
			case 'hits_out':
				$hitsout = get_post_meta($postid,'hits_out',TRUE);
				echo $hitsout;
				break;
			case 'user':
				$post = get_post($postid);
				if (!empty($post->post_author))
					echo get_the_author_meta('user_nicename',absint($post->post_author));
				else
					echo __('Unknown',wptoplist::textdomain());
				break;
			default: break;
		}
	}
	/**
	 * Make the Columns sortable
	 *
	 * @param array $columns
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function sortable_columns($columns) {
		return $columns=array_merge($columns,array(
			'hits_in' => 'hits_in',
			'hits_out'=> 'hits_out'
		));
	}
	/**
	 * Sortable method for sorting the listing
	 *
	 * @param array $vars
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function sort_by($vars) {
		if (isset($vars['post_type']) && 'toplist'==$vars['post_type']) {
			if (isset($vars['orderby']) && 'hits_in'==$vars['orderby']) {
				$vars = array_merge($vars,array('meta_key'=>'hits_in','orderby'=>'meta_value_num'));
			} elseif (isset($vars['orderby']) && 'hits_out'==$vars['orderby']) {
				$vars = array_merge($vars,array('meta_key'=>'hits_out','orderby'=>'meta_value_num'));
			}
		}
		return $vars;
	}
	/**
	 * Add the sortable filter
	 *
	 * @access public
	 * @since 1.0
	 * @uses add_filter
	 * @return void
	 */
	public function add_sortable_filter() {
		add_filter('request', array($this,'sort_by'));
	}
}
