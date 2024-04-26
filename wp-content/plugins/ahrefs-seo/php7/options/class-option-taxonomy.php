<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Options;

use ahrefs\AhrefsSeo\Ahrefs_Seo;

/**
 * Options class for taxonomies.
 *
 * @since 0.9.4
 */
class Option_Taxonomy extends Option {
	protected const OPTION_BASE = 'ahrefs-seo-options-t-';
	/** @var string */
	protected $taxonomy;
	/** @var string */
	protected $title;

	/**
	 * Constructor
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param string $title Taxonomy title.
	 */
	public function __construct( string $taxonomy, string $title ) {
		$this->taxonomy = $taxonomy;
		$this->title    = $title;
		$this->load_options();
	}

	/**
	 * This option make sense: taxonomy exists.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @return bool
	 */
	public static function exists( string $taxonomy ) : bool {
		return taxonomy_exists( $taxonomy );
	}
	/**
	 * Render view with options
	 *
	 * @return void
	 */
	public function render_view() : void {
		if ( self::exists( $this->taxonomy ) ) {
			$nonce_name = $this->get_var_name( 'nonce' );
			wp_nonce_field( $nonce_name, $nonce_name );

			$title = $this->title;

			Ahrefs_Seo::get()->get_view()->show_part(
				'options/scope/option-post-single',
				[
					'title'            => $title,
					'is_enabled'       => $this->is_enabled,
					'var_enabled_name' => $this->get_var_name( 'enabled' ),
					'is_post'          => false,
				]
			);
		}
	}
	/**
	 * The view has a section with options vs single option only
	 *
	 * @return bool
	 */
	public function has_sub_options() : bool {
		return false;
	}
	/**
	 * Get options hash if enabled.
	 *
	 * @return string
	 */
	public function get_options_hash() : string {
		return ! $this->is_enabled ? '' : sprintf( '%s-%s', parent::get_options_hash(), $this->taxonomy );
	}
	/**
	 * Load options from request
	 *
	 * @return bool Success.
	 */
	public function load_options_from_request() : bool {
		if ( taxonomy_exists( $this->taxonomy ) ) {
			$nonce_name = $this->get_var_name( 'nonce' );
			if ( isset( $_POST[ $nonce_name ] ) && check_admin_referer( $nonce_name, $nonce_name ) ) {
				$var_enabled_name = $this->get_var_name( 'enabled' );
				$this->is_enabled = isset( $_POST[ $var_enabled_name ] ) && ! empty( $_POST[ $var_enabled_name ] );
				$this->save_options();
				return true;
			}
		}
		return false;
	}
	/**
	 * Import options from older plugin versions.
	 *
	 * @return void
	 */
	public function import_from_older_version() : void {
		$this->is_enabled = ( 'category' === $this->taxonomy ) || ( 'product_category' === $this->taxonomy );
		$this->save_options();
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
			$this->is_enabled = (bool) ( $options['is_enabled'] ?? ( ( 'category' === $this->taxonomy ) || ( 'product_category' === $this->taxonomy ) ) ); // enabled for Category and Product category by default.
		}
		return $this;
	}
	/**
	 * Get name for option save/load.
	 *
	 * @return string Option name.
	 */
	protected function get_option_name() : string {
		return sanitize_html_class( $this::OPTION_BASE . $this->taxonomy );
	}
	/**
	 * Return name of var for using in render and load from request option
	 *
	 * @param string $suffix Suffix for the variable name.
	 * @return string
	 */
	protected function get_var_name( string $suffix = '' ) : string {
		return sanitize_file_name( 'ahtax_' . $this->taxonomy . '_' . $suffix );
	}

}
