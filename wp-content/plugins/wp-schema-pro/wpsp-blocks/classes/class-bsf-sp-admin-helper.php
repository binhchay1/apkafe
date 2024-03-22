<?php
/**
 * Schema Pro Admin Helper.
 *
 * @package Schema Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BSF_SP_Admin_Helper' ) ) {

	/**
	 * Class BSF_SP_Admin_Helper.
	 */
	final class BSF_SP_Admin_Helper {

		/**
		 * Member Variable
		 *
		 * @since 0.0.1
		 * @var instance
		 */
		private static $instance;

		/**
		 *  Initiator
		 *
		 * @since 0.0.1
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Returns an option from the database for
		 * the admin settings page.
		 *
		 * @param  string  $key     The option key.
		 * @param  mixed   $default Option default value if option is not available.
		 * @param  boolean $network_override Whether to allow the network admin setting to be overridden on subsites.
		 * @return string           Return the option value
		 * @since 0.0.1
		 */
		public static function get_admin_settings_option( $key, $default = false, $network_override = false ) {

			// Get the site-wide option if we're in the network admin.
			if ( $network_override && is_multisite() ) {
				$value = get_site_option( $key, $default );
			} else {
				$value = get_option( $key, $default );
			}

			return $value;
		}

		/**
		 * Provide Widget settings.
		 *
		 * @return array()
		 * @since 0.0.1
		 */
		public static function get_block_options() {

			$blocks       = BSF_SP_Helper::$block_list;
			$saved_blocks = self::get_admin_settings_option( '_wpsp_blocks' );
			if ( is_array( $blocks ) ) {
				foreach ( $blocks as $slug => $data ) {
					$_slug = str_replace( 'wpsp/', '', $slug );

					if ( isset( $saved_blocks[ $_slug ] ) ) {
						if ( 'disabled' === $saved_blocks[ $_slug ] ) {
							$blocks[ $slug ]['is_activate'] = false;
						} else {
							$blocks[ $slug ]['is_activate'] = true;
						}
					} else {
						$blocks[ $slug ]['is_activate'] = ( isset( $data['default'] ) ) ? $data['default'] : false;
					}
				}
			}

			BSF_SP_Helper::$block_list = $blocks;

			return apply_filters( 'wpsp_enabled_blocks', BSF_SP_Helper::$block_list );
		}

		/**
		 * Updates an option from the admin settings page.
		 *
		 * @param string $key       The option key.
		 * @param mixed  $value     The value to update.
		 * @param bool   $network   Whether to allow the network admin setting to be overridden on subsites.
		 * @return mixed
		 * @since 0.0.1
		 */
		public static function update_admin_settings_option( $key, $value, $network = false ) {

			// Update the site-wide option since we're in the network admin.
			if ( $network && is_multisite() ) {
				update_site_option( $key, $value );
			} else {
				update_option( $key, $value );
			}
		}

		/**
		 *  Get Specific Stylesheet
		 *
		 * @since 1.13.4
		 */
		public static function create_specific_stylesheet() {

			$saved_blocks   = self::get_admin_settings_option( '_wpsp_blocks' );
			$combined       = array();
			$is_already_faq = false;

			foreach ( BSF_SP_Config::$block_attributes as $key => $block ) {

				$block_name = str_replace( 'wpsp/', '', $key );

				if ( isset( $saved_blocks[ $block_name ] ) && 'disabled' === $saved_blocks[ $block_name ] ) {
					continue;
				}

				switch ( $block_name ) {
					case 'faq-child':
					case 'faq':
						if ( ! $is_already_faq ) {
							$combined[]     = 'faq';
							$combined[]     = 'faq-child';
							$is_already_faq = true;
						}
						break;

					default:
						$combined[] = $block_name;
						break;
				}
			}

			$combined_path = plugin_dir_path( BSF_AIOSRS_PRO_FILE ) . 'dist/style-blocks.css';
			wp_delete_file( $combined_path );

			$style = '';

			$wp_filesystem = BSF_SP_Helper::get_instance()->get_filesystem();

			foreach ( $combined as $key => $c_block ) {
				$style .= $wp_filesystem->get_contents( plugin_dir_path( BSF_AIOSRS_PRO_FILE ) . 'wpsp-blocks/assets/css/blocks/' . $c_block . '.css' );

			}
			$wp_filesystem->put_contents( $combined_path, $style, FS_CHMOD_FILE );
		}

	}

	/**
	 *  Prepare if class 'WPSP_Admin_Helper' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	BSF_SP_Admin_Helper::get_instance();
}

