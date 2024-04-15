<?php
/**
 * Framework helpers file.
 *
 * @link       https://shapedplugin.com/
 * @since      2.0.0
 *
 * @package    easy-accordion-free
 * @subpackage easy-accordion-free/framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.

if ( ! function_exists( 'eapro_array_search' ) ) {
	/**
	 * Array search key & value
	 *
	 * @param  mixed $array main array.
	 * @param  mixed $key key.
	 * @param  mixed $value val.
	 * @return array
	 */
	function eapro_array_search( $array, $key, $value ) {

		$results = array();

		if ( is_array( $array ) ) {
			if ( isset( $array[ $key ] ) && $array[ $key ] === $value ) {
				$results[] = $array;
			}

			foreach ( $array as $sub_array ) {
				$results = array_merge( $results, eapro_array_search( $sub_array, $key, $value ) );
			}
		}

		return $results;

	}
}


if ( ! function_exists( 'eapro_microtime' ) ) {
	/**
	 *
	 * Between Microtime
	 *
	 * @param  string $timenow now.
	 * @param  string $starttime start.
	 * @param  int    $timeout timeout.
	 * @return bool
	 */
	function eapro_timeout( $timenow, $starttime, $timeout = 30 ) {

		return ( ( $timenow - $starttime ) < $timeout ) ? true : false;
	}
}

if ( ! function_exists( 'eapro_wp_editor_api' ) ) {
	/**
	 *
	 * Check for wp editor api
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function eapro_wp_editor_api() {

		global $wp_version;
		return version_compare( $wp_version, '4.8', '>=' );
	}
}
