<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.5.0
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * This class initializes Schema for AMP
 *
 * @class BSF_AIOSRS_Pro_Schema_Global_Uninstall
 */
class BSF_AIOSRS_Pro_Schema_Global_Uninstall {

	/**
	 * Class instance.
	 *
	 * @access private
	 * @var $instance Class instance.
	 */
	private static $instance;

	/**
	 * Constructor function.
	 */
	public function __construct() {

		$this->delete_all_plugin_data();
	}

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
	 * Delete function.
	 */
	public function delete_queries() {
		global $wpdb;
		$delete_keys                 = BSF_AIOSRS_Pro_Helper::$settings;
		$delete_keys_options         = array_keys( $delete_keys );
		$delete_options_placeholders = implode( ', ', array_fill( 0, count( $delete_keys_options ), '%s' ) );
		$wpdb->query( $wpdb->prepare( " DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", '%' . $wpdb->esc_like( 'bsf-aiosrs-' ) . '%' ) );
		$wpdb->query( $wpdb->prepare( " DELETE FROM {$wpdb->posts} WHERE post_type LIKE %s", '%' . $wpdb->esc_like( 'aiosrs-schema' ) . '%' ) );
		$query = "DELETE FROM {$wpdb->options} WHERE option_name IN ($delete_options_placeholders)";
		// @codingStandardsIgnoreStart
		$wpdb->query( $wpdb->prepare( $query, $delete_keys_options ) );
		 wp_cache_delete($delete_options_placeholders,'options');
		// @codingStandardsIgnoreEnd
	}
	/**
	 * Delete Schema from single site or multisite.
	 */
	public function delete_all_plugin_data() {

		if ( ! is_multisite() ) {

			$option_schema = BSF_AIOSRS_Pro_Helper::$settings['aiosrs-pro-settings'];
			if ( '1' === isset( $option_schema['delete-schema-data'] ) ) {
				self::delete_queries();
			}
		} else {
			global $wpdb;
			$blog_ids         = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			$original_blog_id = get_current_blog_id();
			$option_schema    = BSF_AIOSRS_Pro_Helper::$settings['aiosrs-pro-settings'];
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( '1' === isset( $option_schema['delete-schema-data'] ) ) {
					self::delete_queries();
				}
			}
				switch_to_blog( $original_blog_id );
		}
	}
}

BSF_AIOSRS_Pro_Schema_Global_Uninstall::get_instance();
