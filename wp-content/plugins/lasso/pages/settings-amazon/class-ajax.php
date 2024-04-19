<?php
/**
 * Setting Amazon - Ajax.
 *
 * @package Pages
 */

namespace Lasso\Pages\Settings_Amazon;

/**
 * Setting Amazon - Ajax.
 */
class Ajax {
	/**
	 * Declare "Lasso ajax requests" to WordPress.
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_lasso_disable_auto_amazon_notification', array( $this, 'lasso_disable_auto_amazon_notification' ) );
	}

	/**
	 * Disable Auto Amazon notification.
	 */
	public function lasso_disable_auto_amazon_notification() {
		update_option( 'lasso_enable_auto_amazon_notification', 0 );

		wp_send_json_success(
			array(
				'status' => 1,
			)
		);
	} // @codeCoverageIgnore
}
