<?php
/**
 * Utils.
 *
 * @package Schema Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class BSF_AIOSRS_Pro_Helper.
 */
class BSF_AIOSRS_Pro_Helper {


	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var settings
	 */
	public static $settings;

	/**
	 * Default static array
	 *
	 * @var default_options
	 */
	private static $default_options = array(
		// General Settings.
		'wp-schema-pro-general-settings'   => array(
			'organization'     => '',
			'site-represent'   => '',
			'site-name'        => '',
			'person-name'      => '',
			'site-logo'        => 'custom',
			'site-logo-custom' => '',
		),

		// Social Profiles.
		'wp-schema-pro-social-profiles'    => array(
			'facebook'    => '',
			'twitter'     => '',
			'google-plus' => '',
			'instagram'   => '',
			'youtube'     => '',
			'linkedin'    => '',
			'pinterest'   => '',
			'soundcloud'  => '',
			'tumblr'      => '',
			'wikipedia'   => '',
			'myspace'     => '',
			'other'       => array(),
		),

		// Global Schemas.
		'wp-schema-pro-global-schemas'     => array(
			'about-page'              => '',
			'contact-page'            => '',
			'site-navigation-element' => '',
			'breadcrumb'              => '1',
			'sitelink-search-box'     => '1',
		),

		// Advanced Settings.
		'aiosrs-pro-settings'              => array(
			'quick-test'          => '1',
			'menu-position'       => 'options-general.php',
			'schema-location'     => 'head',
			'yoast-compatibility' => '1',
			'schema-validation'   => '',
			'default_image'       => '',
			'delete-schema-data'  => '',

		),

		// Corporate Contact.
		'wp-schema-pro-corporate-contact'  => array(
			'contact-type'       => '',
			'telephone'          => '',
			'url'                => '',
			'email'              => '',
			'areaServed'         => '',
			'contact-hear'       => '',
			'contact-toll'       => '',
			'availableLanguage'  => '',
			'cp-schema-type'     => '',
			'areaserved-type'    => '',
			'country'            => array(),
			'place'              => '',
			'contact-page-id'    => '',
			'contact-type-other' => '',

		),

		// Branding Settings.
		'wp-schema-pro-branding-settings'  => array(
			'sp_plugin_name'        => '',
			'sp_plugin_sname'       => '',
			'sp_plugin_desc'        => '',
			'sp_plugin_author_name' => '',
			'sp_plugin_author_url'  => '',
			'sp_hide_label'         => 'disabled',
		),
		'wp-schema-pro-breadcrumb-setting' => array(
			'product'      => '',
			'product_cat'  => '',
			'product_tag'  => '',
			'enable_bread' => '1',
		),
	);


	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		self::prepare_schema_pro_options();
	}

	/**
	 * Prepare options from database.
	 */
	public static function prepare_schema_pro_options() {
		$options = array_keys( self::$default_options );
		foreach ( $options as $option ) {
			self::$settings[ $option ] = get_option( $option );
		}
	}

	/**
	 *  Enqueue the scripts at the backend.
	 */
	public static function bsf_schema_pro_enqueue_admin_script() {
		global $pagenow;
			return 'post-new.php' === $pagenow || 'post.php' === $pagenow;
	}

	/**
	 *  Set default options.
	 */
	public static function bsf_schema_pro_set_default_options() {

		foreach ( self::$default_options as $key => $default_option ) {
			$settings = get_option( $key );
			if ( ! get_option( $key ) ) {
				update_option( $key, $default_option );
			} else {
				foreach ( $default_option as $name => $setting ) {
					if ( ! isset( $settings[ $name ] ) ) {
						$settings[ $name ] = $default_option[ $name ];
					}
				}
				// Updated settings if new settings added.
				update_option( $key, $settings );
			}
		}

		// Delete decrypted cached structured option data.
		delete_option( BSF_AIOSRS_PRO_CACHE_KEY );
	}

	/**
	 *  Return the WP_debug.
	 */
	public static function bsf_schema_pro_is_wp_debug_enable() {
		return true === ( defined( 'WP_DEBUG' ) && WP_DEBUG );
	}
}


BSF_AIOSRS_Pro_Helper::get_instance();
