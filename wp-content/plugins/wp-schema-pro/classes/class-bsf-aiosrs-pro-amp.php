<?php
/**
 * AMP for Schema Pro
 *
 * @package Schema Pro
 */

/**
 * Exit if accessed directly.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
	/**
	 * BSF_AIOSRS_Pro_Amp initial setup
	 *
	 * @since 1.5.0
	 */
	/**
	 * This class initializes Schema for AMP
	 *
	 * @class BSF_AIOSRS_Pro_Amp
	 */
final class BSF_AIOSRS_Pro_Amp {
	/**
	 * Class instance.
	 *
	 * @access private
	 * @var $instance Class instance.
	 */
	private static $instance;
	/**
	 * Initiator
	 *
	 * @access public
	 * @var $amp_activated set default to false.
	 */
	public static $amp_activated = false;

	/**
	 * AMP Options.
	 *
	 * @access private
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
		add_action( 'init', array( $this, 'amp_schema_markup' ) );
	}
	/**
	 * Rendering the Schema markup for AMP Template.
	 */
	public static function amp_schema_markup() {

		if ( apply_filters( 'wp_schema_pro_remove_amp_schema_markup', true ) ) {
			$settings      = BSF_AIOSRS_Pro_Helper::$settings['aiosrs-pro-settings'];
			$schema_markup = BSF_AIOSRS_Pro_Markup::get_instance();

			if ( isset( $settings['schema-location'] ) ) {

				switch ( $settings['schema-location'] ) {
					case 'head':
						add_action( 'amp_post_template_head', array( $schema_markup, 'schema_markup' ) );
						add_action( 'amp_post_template_head', array( $schema_markup, 'global_schemas_markup' ) );
						break;

					case 'footer':
						add_action( 'amp_post_template_footer', array( $schema_markup, 'schema_markup' ) );
						add_action( 'amp_post_template_footer', array( $schema_markup, 'global_schemas_markup' ) );
						break;
					default:
						break;
				}
			}
		}
	}
}
	BSF_AIOSRS_Pro_Amp::get_instance();

