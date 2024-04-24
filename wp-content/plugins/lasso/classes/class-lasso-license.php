<?php
/**
 * Declare class Lasso_License
 *
 * @package Lasso_License
 */

use Lasso\Classes\Encrypt;
use Lasso\Classes\Enum;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting as Lasso_Setting;

/**
 * Lasso_License
 */
class Lasso_License {
	/**
	 * Construction of Lasso_License
	 */
	public function __construct() {
		add_filter( 'plugins_api', array( $this, 'lasso_details' ), 10, 3 );
	}

	/**
	 * Get license of user
	 */
	public static function get_license() {
		return Lasso_Setting::lasso_get_setting( 'license_serial', '' );
	}

	/**
	 * Get site id of user
	 */
	public static function get_site_id() {
		return Lasso_Setting::lasso_get_setting( 'site_id', '' );
	}

	/**
	 * Save site id of user
	 *
	 * @param string $site_id Site id of website.
	 */
	public static function save_site_id( $site_id ) {
		return Lasso_Setting::lasso_set_setting( 'site_id', $site_id );
	}

	/**
	 * Check license of user
	 *
	 * @param string  $license_id License of user.
	 * @param boolean $update_db  Whether licens status is updated in DB. Default to true.
	 */
	public static function check_license( $license_id, $update_db = true ) {
		$headers     = Lasso_Helper::get_lasso_headers( $license_id );
		$request_url = LASSO_LINK . '/license/status';

		$res = Lasso_Helper::send_request( 'get', $request_url, array(), $headers );

		$status_code = $res['status_code'];
		$response    = $res['response'];

		$error_code    = 'other';
		$error_message = 'Error!';
		$status        = false;

		if ( 200 === $status_code ) {
			// ? store user email
			if ( isset( $response->email ) && '' !== $response->email ) {
				update_option( 'lasso_license_email', $response->email );
			}
			if ( isset( $response->end_date ) && $response->end_date > 0 ) {
				update_option( 'lasso_end_date', $response->end_date );
			}
			if ( isset( $response->install_count ) ) {
				update_option( Enum::LASSO_INSTALL_COUNT, $response->install_count );
			}

			if ( isset( $response->is_startup_plan ) ) {
				update_option( Enum::LASSO_IS_STARTUP_PLAN, $response->is_startup_plan );
			}

			$status = true;
		} elseif ( 401 === $status_code ) {
			$error_code    = $response->error_code ?? $error_code;
			$error_message = $response->message ?? $error_message;
			$status        = false;
		} else {
			$update_db     = false; // ? Don't update DB if the status is not 200, 401
			$error_message = $res['message'] ?? $error_message;
		}

		// ? store user hash
		if ( isset( $response->hash ) && '' !== $response->hash ) {
			update_option( 'lasso_license_hash', $response->hash );
		}

		// ? update license status in DB
		if ( $update_db ) {
			$status = $status ? 1 : 0;
			$status = 1;
			update_option( 'lasso_license_status', $status, true );
		}

		return array( Lasso_Helper::cast_to_boolean( $status ), $error_code, $error_message );
	}

	/**
	 * Check license in setting
	 */
	public static function check_user_license() {
		$license = self::get_license();
		list($license_status, $error_code, $error_message) = self::check_license( $license );

		return $license_status;
	}

	/**
	 * Get license status in DB
	 */
	public static function get_license_status() {
		$db_status      = get_option( 'lasso_license_status', '' );
		$active_license = Lasso_Helper::cast_to_boolean( $db_status );

		// ? re-activate again if option `lasso_license_status` is not existing
		if ( '' === $db_status ) {
			$active_license = Lasso_Helper::cast_to_boolean( self::check_user_license() );
		}

		return $active_license;
	}

	/**
	 * Get license status in DB
	 */
	public static function get_plugin_update_url() {
		$license  = self::get_license();
		$site_id  = self::get_site_id();
		$site_url = rawurlencode( site_url() );
		$data     = array(
			'license'  => $license,
			'site_id'  => $site_id,
			'site_url' => $site_url,
		);

		$encrypted_base64 = Encrypt::encrypt_aes( $data, true );

		$lasso_link = LASSO_LINK . '/server/update?' . $encrypted_base64;

		return $lasso_link;
	}

	/**
	 * Send install data to Lasso server
	 */
	public static function lasso_getinfo() {
		global $wp_version;
		global $wpdb;

		// ? Report in
		$data = array(
			'installed_version' => LASSO_VERSION,
			'datetime'          => gmdate( 'Y-m-d H:i:s' ),
			'site_id'           => self::get_site_id(),
			'install_url'       => site_url(),
			'license_key'       => self::get_license(),
			'wordpress_version' => $wp_version,
			'php_version'       => phpversion(),
			'mysql_version'     => $wpdb->db_version(),
			'is_classic_editor' => Lasso_Helper::is_classic_editor() ? 1 : 0,
		);

		$body     = Encrypt::encrypt_aes( $data );
		$response = Lasso_Helper::send_request( 'post', LASSO_LINK . '/server/getinfo', $body );

		$site_id = $response['response']->site_id ?? '';

		if ( $site_id ) {
			self::save_site_id( $site_id ); // ? save site_id from DB
		}

		return $response;
	}

	/**
	 * Get Lasso details information (changelog)
	 *
	 * @param false|object|array $result The result object or array. Default false.
	 * @param string             $action The type of information being requested from the Plugin Installation API.
	 * @param object             $args Plugin API arguments.
	 */
	public function lasso_details( $result, $action, $args ) {
		$lasso_main_file_name = 'affiliate-plugin';
		$lasso_folder_name    = 'lasso';
		$plugin_slug          = $args->slug ?? '';
		if ( $lasso_main_file_name === $plugin_slug || $lasso_folder_name === $plugin_slug ) {
			$start_time = microtime( true );
			$lasso_link = self::get_plugin_update_url();
			new Lasso_Puc_v4p4_Plugin_UpdateChecker( $lasso_link, LASSO_PLUGIN_MAIN_FILE, $lasso_folder_name );

			$time = microtime( true ) - $start_time;
			Lasso_Helper::write_log( 'Time for checking plugin update data (seconds): ' . $time, 'lasso_update_checker' );
		}

		return $result;
	}

	/**
	 * Check client is a Startup or Essential plan
	 */
	public static function is_startup_plan() {
		$is_startup_plan = get_option( Enum::LASSO_IS_STARTUP_PLAN, '' );
		return Lasso_Helper::cast_to_boolean( $is_startup_plan );
	}
}
