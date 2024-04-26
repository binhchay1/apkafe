<?php

namespace ahrefs\AhrefsSeo\Options;

/**
 * Temporary code class.
 *
 * @since 0.9.11
 */
class Temporary_Code {

	const OPTION_BASE = 'ahrefs-seo-options-temp-';
	/** @var string */
	private $prefix;
	/**
	 * Constructor
	 *
	 * @param string $prefix Unique code prefix.
	 */
	public function __construct( $prefix ) {
		$this->prefix = $prefix;
	}
	/**
	 * Create temporary state code
	 *
	 * @param bool $short_time 15 or 30 minutes long.
	 * @return string
	 */
	public function create_state_code( $short_time = true ) {
		$nonce       = wp_generate_password( 24, false, false ) . (string) time();
		$valid_until = time() + ( $short_time ? 15 : 30 ) * MINUTE_IN_SECONDS;
		update_option( $this->get_option_name(), [ $nonce, $valid_until ] );
		return $nonce;
	}
	/**
	 * Get state code
	 *
	 * @return ?string Code value if exists and not expired.
	 */
	public function get_state_code() {
		$value = get_option( $this->get_option_name() );
		if ( is_array( $value ) && 2 === count( $value ) ) {
			list($value, $valid_until) = $value;
			if ( is_int( $valid_until ) && $valid_until >= time() && is_string( $value ) ) {
				return $value;
			} else {
				delete_option( $this->get_option_name() );
			}
		}
		return null;
	}
	/**
	 * Verify state code
	 *
	 * @param string $nonce Code to check.
	 * @return bool
	 */
	public function verify_code( $nonce ) {
		return $this->get_state_code() === $nonce;
	}
	/**
	 * @return string
	 */
	protected function get_option_name() {
		return self::OPTION_BASE . $this->prefix;
	}
}