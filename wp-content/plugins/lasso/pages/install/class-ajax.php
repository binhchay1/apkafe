<?php
/**
 * Lasso Install - Ajax.
 *
 * @package Pages
 */

namespace Lasso\Pages\Install;

use Lasso\Classes\Activator as Lasso_Activator;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting as Lasso_Setting;

use Lasso_License;
use Lasso_Process_Link_Database;

/**
 * Lasso Install - Ajax.
 */
class Ajax {
	const ACTIVATE_FIRST_TIME_KEY = 'lasso_activate_first_time';

	/**
	 * Declare "Lasso ajax requests" to WordPress.
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_lasso_activate_license', array( $this, 'lasso_activate_license' ) );
	}

	/**
	 * Activate lasso plugin
	 */
	public function lasso_activate_license() {
		$license       = wp_unslash( $_POST['license'] ?? '' ); // phpcs:ignore
		$onboarding    = wp_unslash( $_POST['onboarding'] ?? false ); // phpcs:ignore
		$onboarding    = Lasso_Helper::cast_to_boolean( $onboarding );
		$lasso_setting = new Lasso_Setting();

		Lasso_Setting::lasso_set_setting( 'license_serial', $license );
		Lasso_License::lasso_getinfo();
		Lasso_Activator::add_default_data();

		list($license_status, $error_code, $error_message) = Lasso_License::check_license( $license );
		if ( $license_status ) {
			// ? rebuild data automatically
			$bg = new Lasso_Process_Link_Database();
			if ( $onboarding ) {
				$bg->link_database();
			} else {
				$bg->link_database_limit();
			}

			// ? Set flag activate first time
			update_option( self::ACTIVATE_FIRST_TIME_KEY, 1 );
		}

		wp_send_json_success(
			array(
				'status'        => $license_status,
				'error_code'    => $error_code,
				'error_message' => $error_message,
				'redirect_url'  => $lasso_setting->get_dashboard_page(),
				'hash'          => get_option( 'lasso_license_hash', '' ),
			)
		);
	} // @codeCoverageIgnore
}
