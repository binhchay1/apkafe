<?php
/**
 * Lasso Post Content History Detail - Ajax.
 *
 * @package Pages
 */

namespace Lasso\Pages\Post_Content_History_Detail;

use Lasso\Classes\Post_Content_History as Lasso_Post_Content_History;

/**
 * Lasso Post Content History Detail - Ajax.
 */
class Ajax {
	/**
	 * Declare "Lasso ajax requests" to WordPress.
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_lasso_revert_post_content', array( $this, 'lasso_revert_post_content' ) );
	}

	/**
	 * Revert post content
	 */
	public function lasso_revert_post_content() {
		$response = array(
			'status' => true,
			'msg'    => '',
		);

		try {
			$data_post  = wp_unslash( $_POST ); // phpcs:ignore
			$history_id = $data_post['history_id'] ?? 0;

			if ( $history_id ) {
				Lasso_Post_Content_History::revert( $history_id );
			} else {
				$response['status'] = false;
				$response['msg']    = "History ID: $history_id does not exist.";
			}
		} catch ( \Exception $e ) {
			// @codeCoverageIgnoreStart
			$response['status'] = false;
			$response['msg']    = "Revert post content error: {$e->getMessage()}";
			// @codeCoverageIgnoreEnd
		}

		wp_send_json_success( $response );
	} // @codeCoverageIgnore
}
