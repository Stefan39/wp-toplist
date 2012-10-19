<?php
/**
 * Description goes here.
 *
 * @author         Wpler <info@wpler.com>
 * @date           29.08.12
 * @version        1.0
 * @copyright      2012 WPler <http://www.wpler.com>
 */
class wptoplist_tracking
{
	/**
	 * Track an hit out over ajax
	 *
	 * @access public
	 * @static
	 * @since 1.0
	 * @uses wp_verify_nonce, absint, get_post_meta, update_post_meta, wp_die,
	 * @return int
	 */
	static public function wptl_hit_out() {
		if (wp_verify_nonce($_REQUEST['nonce'])) wp_die('Busted! Wrong nonce key');
		$postid = absint($_REQUEST['id']);
		$hits = get_post_meta($postid, 'hits_out',TRUE);
		update_post_meta($postid,'hits_out',$hits+1);
		return $hits;
	}
	/**
	 * Track a hit in by toplist-entry
	 *
	 * @access public
	 * @static
	 * @since 1.0
	 * @uses absint, get_post_meta, update_post_meta, get_query_var,
	 * @return void
	 */
	static public function wptl_hit_in() {
		if (($id=get_query_var('toplist'))) {
				$hits = get_post_meta(absint($id), 'hits_in', TRUE);
				if (isset($hits)) {
					update_post_meta(absint($id), 'hits_in', $hits+1);
				}
		}
	}
}
