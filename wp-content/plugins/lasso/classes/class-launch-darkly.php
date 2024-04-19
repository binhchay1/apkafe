<?php
/**
 * Declare class Launch_Darkly
 *
 * @package Launch_Darkly
 */

namespace Lasso\Classes;

use LassoVendor\LaunchDarkly\LDClient;
use LassoVendor\LaunchDarkly\LDUser;

/**
 * Launch_Darkly
 */
class Launch_Darkly {
	const DEFAULT_FLAG_VALUE = false;
	const CACHE_TIME_EXPIRED = 3600; // ? 1 hour.
	const CACHE_TRUE_VALUE   = 1;
	const CACHE_FALSE_VALUE  = 2;

	/**
	 * Get client
	 */
	private static function get_client() {
		$timeout = 10;
		$options = array(
			'event_publisher' => \LassoVendor\LaunchDarkly\Integrations\Guzzle::eventPublisher(),
			'timeout'         => $timeout,
			'connect_timeout' => $timeout,
		);

		try {
			return new LDClient( LAUNCH_DARKLY, $options );
		} catch ( \Exception $e ) {
			unset( $options['event_publisher'] );
			return new LDClient( LAUNCH_DARKLY, $options );
		}
	}

	/**
	 * Get user
	 */
	private static function get_user() {
		$user_email = get_option( 'admin_email' ); // phpcs:ignore
		$user_email = get_option( 'lasso_license_email', $user_email ); // phpcs:ignore

		return new LDUser( $user_email );
	}

	/**
	 * Get flag from LaunchDarkly
	 *
	 * @param string $flag_name Flag name.
	 *
	 * @return bool $result.
	 */
	private static function get_flag( $flag_name ) {
		$cache_key    = 'lasso_launch_darkly_' . $flag_name;
		$result       = self::DEFAULT_FLAG_VALUE;
		$cache_result = self::get_cache_result( $cache_key );

		if ( is_null( $cache_result ) ) {
			try {
				$client        = self::get_client();
				$user          = self::get_user();
				$request_valid = method_exists( $client, 'variation' );

				$result = $request_valid ? $client->variation( $flag_name, $user ) : self::DEFAULT_FLAG_VALUE;
			} catch ( \Exception $e ) {
				$result = self::DEFAULT_FLAG_VALUE;
			}

			self::set_cache_result( $cache_key, $result );
		} else {
			$result = $cache_result;
		}

		return $result;
	}

	/**
	 * Check whether Auto Monetize is enabled for Amazon
	 */
	public static function enable_auto_monetize() {
		$flag_name = 'auto_monetize';
		return self::get_flag( $flag_name );
	}

	/**
	 * Check whether Audit Log is enabled
	 */
	public static function enable_audit_log() {
		$flag_name = 'audit_log';
		return self::get_flag( $flag_name );
	}

	/**
	 * Check whether Google Analytics - Anonymize IP is enabled
	 */
	public static function enable_ga_anonymize_ip() {
		$flag_name = 'ga_anonymize_ip';
		return self::get_flag( $flag_name );
	}

	/**
	 * Check whether Google Analytics - Anonymize IP is enabled
	 */
	public static function enable_lasso_lean() {
		$flag_name = 'lasso-lean';
		return self::get_flag( $flag_name );
	}

	/**
	 * Check whether Google Analytics - Anonymize IP is enabled
	 */
	public static function enable_site_stripe() {
		$flag_name = 'site-stripe';
		return self::get_flag( $flag_name );
	}

	/**
	 * Get cache result. Return null if no cache or has expired ELSE return LD result in bool.
	 *
	 * @param string $cache_key Cache key.
	 * @return bool|null
	 */
	private static function get_cache_result( $cache_key ) {
		$cache_value = get_site_transient( $cache_key );

		if ( false === $cache_value ) {
			return null;
		}

		$result = self::CACHE_TRUE_VALUE === intval( $cache_value ) ? true : false;

		return $result;
	}

	/**
	 * Set cache result with expired time.
	 *
	 * @param string $cache_key Cache key.
	 * @param bool   $result    Launch Darkly result.
	 */
	private static function set_cache_result( $cache_key, $result ) {
		$cache_value = $result ? self::CACHE_TRUE_VALUE : self::CACHE_FALSE_VALUE;

		return set_site_transient( $cache_key, $cache_value, self::CACHE_TIME_EXPIRED );
	}
}
