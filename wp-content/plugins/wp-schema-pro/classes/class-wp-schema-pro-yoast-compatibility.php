<?php
/**
 * Schema Pro Yoast Compatibilty
 *
 * @since 1.1.0
 * @package Schema Pro
 */

if ( ! class_exists( 'WP_Schema_Pro_Yoast_Compatibility' ) ) {

	/**
	 * WP_Schema_Pro_Yoast_Compatibility initial setup
	 *
	 * @since 1.1.0
	 */
	class WP_Schema_Pro_Yoast_Compatibility {

		/**
		 * Class instance.
		 *
		 * @access private
		 * @var $instance Class instance.
		 */
		private static $instance;

		/**
		 * Yoast SEO is activated or not.
		 *
		 * @access private
		 * @var $activated Yoast SEO is activated or not.
		 */
		public static $activated = false;

		/**
		 * Yoast SEO Options.
		 *
		 * @access private
		 * @var $wpseo Yoast SEO options.
		 */
		private static $wpseo = array();

		/**
		 * Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor function.
		 */
		public function __construct() {

			if ( defined( 'WPSEO_VERSION' ) && class_exists( 'WPSEO_Options' ) ) {
				self::$activated = true;
			}
		}

		/**
		 * Get Yoast Options
		 *
		 * @param  string $key Yoast Option key.
		 * @return array Get yoast compatibility options.
		 */
		public static function get_option( $key = '' ) {

			$settings = get_option( 'aiosrs-pro-settings', array() );
			if ( ! self::$activated || ( isset( $settings['yoast-compatibility'] ) && '1' !== $settings['yoast-compatibility'] ) ) {
				return false;
			}

			if ( 'wp_schema_pro_yoast_enabled' === $key ) {
				return true;
			}
			if ( empty( self::$wpseo ) && method_exists( 'WPSEO_Options', 'get_options' ) ) {
				self::$wpseo = WPSEO_Options::get_options( array( 'wpseo', 'wpseo_social', 'wpseo_internallinks', 'wpseo_titles' ) );
			}

			return isset( self::$wpseo[ $key ] ) ? self::$wpseo[ $key ] : false;
		}

	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
WP_Schema_Pro_Yoast_Compatibility::get_instance();
