<?php
/**
 * White labeling for Schema Pro
 *
 * @package Schema Pro
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Branding' ) ) {

	/**
	 * BSF_AIOSRS_Pro_Branding initial setup
	 *
	 * @since 1.3.0
	 */
	/**
	 * This class initializes Schema Branding
	 *
	 * @class BSF_AIOSRS_Pro_Branding
	 */
	final class BSF_AIOSRS_Pro_Branding {

		/**
		 * Function that initializes necessary filters
		 *
		 * @return void
		 */
		public static function init() {
			add_filter( 'all_plugins', __CLASS__ . '::plugins_page' );
		}

		/**
		 * Branding on the plugins page.
		 *
		 * @since 1.3.0
		 * @param array $plugins An array data for each plugin.
		 * @return array
		 */
		public static function plugins_page( $plugins ) {

			if ( is_multisite() ) {
				$branding = get_site_option( 'wp-schema-pro-branding-settings' );

			} else {
				$branding = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
			}

			$basename = plugin_basename( BSF_AIOSRS_PRO_DIR . 'wp-schema-pro.php' );

			if ( isset( $plugins[ $basename ] ) && is_array( $branding ) ) {

				$sp_plugin_name = ( array_key_exists( 'sp_plugin_name', $branding ) ) ? $branding['sp_plugin_name'] : '';
				$sp_plugin_desc = ( array_key_exists( 'sp_plugin_desc', $branding ) ) ? $branding['sp_plugin_desc'] : '';
				$sp_author_name = ( array_key_exists( 'sp_plugin_author_name', $branding ) ) ? $branding['sp_plugin_author_name'] : '';
				$sp_author_url  = ( array_key_exists( 'sp_plugin_author_url', $branding ) ) ? $branding['sp_plugin_author_url'] : '';

				if ( '' !== $sp_plugin_name ) {
					$plugins[ $basename ]['Name']  = $sp_plugin_name;
					$plugins[ $basename ]['Title'] = $sp_plugin_name;
				}

				if ( '' !== $sp_plugin_desc ) {
					$plugins[ $basename ]['Description'] = $sp_plugin_desc;
				}

				if ( '' !== $sp_author_name ) {
					$plugins[ $basename ]['Author']     = $sp_author_name;
					$plugins[ $basename ]['AuthorName'] = $sp_author_name;
				}

				if ( '' !== $sp_author_url ) {
					$plugins[ $basename ]['AuthorURI'] = $sp_author_url;
					$plugins[ $basename ]['PluginURI'] = $sp_author_url;
				}
			}
			return $plugins;
		}
	}
}

BSF_AIOSRS_Pro_Branding::init();
