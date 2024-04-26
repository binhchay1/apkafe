<?php


declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Options;

use ahrefs\AhrefsSeo\Ahrefs_Seo;

/**
 * Section with single options class.
 *
 * @since 0.9.4
 */
class Option_Section extends Option {
	protected const OPTION_NAME = 'section_all_singles';
	// from older versions.
	private const OPTION_ENABLED_ALL_CPT = 'ahrefs-seo-content-enabled-all-cpt';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->load_options();
	}

	/**
	 * Render view start part
	 *
	 * @return void
	 */
	public function render_view() : void {
		$nonce_name = $this->get_var_name( 'nonce' );
		wp_nonce_field( $nonce_name, $nonce_name );
		Ahrefs_Seo::get()->get_view()->show_part(
			'options/scope/section-open',
			[
				'title'            => __( 'Other', 'ahrefs-seo' ),
				'is_enabled'       => $this->is_enabled,
				'var_enabled_name' => $this->get_var_name( 'enabled' ),
			]
		);
	}
	/**
	 * Render view final part
	 *
	 * @return void
	 */
	public function render_view_close() : void {
		Ahrefs_Seo::get()->get_view()->show_part(
			'options/scope/section-close',
			[]
		);
	}

	/**
	 * Load options from request
	 *
	 * @return bool Success.
	 */
	public function load_options_from_request() : bool {
		$nonce_name = $this->get_var_name( 'nonce' );
		if ( isset( $_POST[ $nonce_name ] ) && check_admin_referer( $nonce_name, $nonce_name ) ) {
			$var_enabled_name = $this->get_var_name( 'enabled' );
			$this->is_enabled = ! empty( $_POST[ $var_enabled_name ] );
			$this->save_options();
			return true; // always.
		}
		return false;
	}
	/**
	 * Import options from older plugin versions.
	 *
	 * @return void
	 */
	public function import_from_older_version() : void {
		$this->is_enabled = ! empty( get_option( self::OPTION_ENABLED_ALL_CPT ) );
	}
	/**
	 * Save options to DB
	 *
	 * @return self
	 */
	protected function save_options() : Option {
		$options = [
			'is_enabled' => $this->is_enabled,
		];
		update_option( $this->get_option_name(), wp_json_encode( $options ) );
		return $this;
	}
	/**
	 * Load options from DB
	 *
	 * @return self
	 */
	protected function load_options() : Option {
		$value = get_option( $this->get_option_name(), null );
		if ( is_null( $value ) ) {
			$this->import_from_older_version();
		} else {
			$options          = json_decode( (string) $value, true );
			$options          = empty( $options ) ? [] : $options;
			$this->is_enabled = (bool) ( $options['is_enabled'] ?? false );
		}
		return $this;
	}
	/**
	 * Get name for option save/load.
	 *
	 * @return string Option name.
	 */
	protected function get_option_name() : string {
		return sanitize_html_class( $this::OPTION_BASE . $this::OPTION_NAME );
	}
	/**
	 * Return name of var for using in render and load from request option
	 *
	 * @param string $suffix Suffix for the variable name.
	 * @return string
	 */
	protected function get_var_name( string $suffix = '' ) : string {
		return sanitize_file_name( 'ah' . $this::OPTION_NAME . '_' . $suffix );
	}

}
