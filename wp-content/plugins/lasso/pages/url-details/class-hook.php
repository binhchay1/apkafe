<?php
/**
 * Lasso Url detail - Hook.
 *
 * @package Pages
 */

namespace Lasso\Pages\Url_Details;

use Lasso_Affiliate_Link;

use Lasso\Classes\Helper as Lasso_Helper;


/**
 * Lasso Url detail - Hook.
 */
class Hook {
	/**
	 * Declare "Lasso register hook events" to WordPress.
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function register_hooks() {
		// ? change Edit URL in Dashboard
		add_filter( 'get_edit_post_link', array( $this, 'affiliate_link_edit_post_link' ), 10, 3 );

		add_filter( 'wp_insert_post_data', array( $this, 'filter_post_data' ), 100, 2 );
	}

	/**
	 * Change edit post link for Lasso post
	 *
	 * @param string $url     The edit link.
	 * @param int    $post_id Post ID.
	 * @param string $context The link context. If set to 'display' then ampersands are encoded.
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function affiliate_link_edit_post_link( $url, $post_id, $context ) {
		$post_type     = get_post_type( $post_id );
		$new_edit_link = '';

		if ( LASSO_POST_TYPE === $post_type ) {
			$new_edit_link = Lasso_Affiliate_Link::affiliate_edit_link( $post_id );
		}

		return '' !== $new_edit_link ? $new_edit_link : $url;
	}

	/**
	 * Change slug of lasso if the slug is existing already
	 *
	 * @param array $data    An array of slashed, sanitized, and processed post data.
	 * @param array $postarr An array of sanitized (and slashed) but otherwise unmodified post data.
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function filter_post_data( $data, $postarr ) {
		$post_id = $postarr['ID'] ?? 0;
		if ( LASSO_POST_TYPE === $data['post_type'] ) {
			$data['post_name'] = Lasso_Helper::lasso_unique_post_name( $post_id, $data['post_name'] );
			Lasso_Helper::update_post_name( $post_id, $data['post_name'] );
		}

		return $data;
	}
}
