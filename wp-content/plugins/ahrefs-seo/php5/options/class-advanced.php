<?php

namespace ahrefs\AhrefsSeo\Options;

/**
 * Google Advanced options class.
 *
 * @since 0.9.11
 */
class Advanced {

	const OPTION_ADVANCED = 'ahrefs-seo-analytics-advanced';
	/**
	 * Set advanced options
	 *
	 * @param bool $gsc_uses_uppercase My GSC uses uppercase URL encoded characters.
	 * @param bool $ga_not_urlencoded My GA does not use URL encoding.
	 * @param bool $ga_uses_full_url My GA reports full page URLs that include the domain name.
	 *
	 * @return void
	 * @since 0.9.4
	 */
	public function set_adv_options( $gsc_uses_uppercase, $ga_not_urlencoded, $ga_uses_full_url ) { // phpcs:ignore:Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- false positive.
		update_option( self::OPTION_ADVANCED, (string) wp_json_encode( compact( 'gsc_uses_uppercase', 'ga_not_urlencoded', 'ga_uses_full_url' ) ) );
	}
	/**
	 * Get all advanced options as associative array.
	 *
	 * @return array{gsc_uses_uppercase?: bool, ga_not_urlencoded?:bool, ga_uses_full_url?:bool}
	 * @since 0.9.4
	 */
	public function get_adv_options_raw() {
		$values = json_decode( (string) get_option( self::OPTION_ADVANCED ), true );
		return is_array( $values ) ? $values : [];
	}
	/**
	 * Get "My GSC uses uppercase URL encoded characters" advanced option value.
	 *
	 * @return bool
	 * @since 0.9.4
	 */
	public function get_adv_gsc_uses_uppercase() {
		return (bool) ( isset( $this->get_adv_options_raw()['gsc_uses_uppercase'] ) ? $this->get_adv_options_raw()['gsc_uses_uppercase'] : false );
	}
	/**
	 * Get "My GA does not use URL encoding" advanced option value.
	 *
	 * @return bool
	 * @since 0.9.4
	 */
	public function get_adv_ga_not_urlencoded() {
		return (bool) ( isset( $this->get_adv_options_raw()['ga_not_urlencoded'] ) ? $this->get_adv_options_raw()['ga_not_urlencoded'] : false );
	}
	/**
	 * Get "My GA uses full URL (with site domain)" advanced option value.
	 *
	 * @return bool
	 * @since 0.9.8
	 */
	public function get_adv_ga_uses_full_url() {
		return (bool) ( isset( $this->get_adv_options_raw()['ga_uses_full_url'] ) ? $this->get_adv_options_raw()['ga_uses_full_url'] : false );
	}
}