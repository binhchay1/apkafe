<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.1.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Global_Site_Navigation_Element' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.1.0
	 */
	class BSF_AIOSRS_Pro_Schema_Global_Site_Navigation_Element {

		/**
		 * Render Schema.
		 *
		 * @param  array $post Current Post Array.
		 * @return array
		 */
		public static function render( $post ) {
			$schema = array();

			$names = array();
			$urls  = array();

			$settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-global-schemas'];
			if ( isset( $settings['site-navigation-element'] ) && ! empty( $settings['site-navigation-element'] ) ) {
				$navigation_links = wp_get_nav_menu_items( $settings['site-navigation-element'] );

				if ( $navigation_links ) {
					foreach ( $navigation_links as $link ) {
						$names[] = wp_strip_all_tags( $link->title );
						$urls[]  = esc_url( $link->url );
					}
				}
			}

			/**
			* Convert normal array into associative array
			*/
			$combine_array = array_combine( $names, $urls );
			$new_arr[]     = array();
			$j             = 0;
			foreach ( $combine_array as $key => $value ) {
				$new_arr[ $j ]['name'] = $key;
				$new_arr[ $j ]['url']  = $value;
				$j++;
			}

				$schema['@context'] = 'https://schema.org';

			if ( isset( $new_arr ) && ! empty( $new_arr ) ) {
				foreach ( $new_arr as $key2 => $value2 ) {
					$schema['@graph'][ $key2 ]['@context'] = 'https://schema.org';
					$schema['@graph'][ $key2 ]['@type']    = 'SiteNavigationElement';
					$schema['@graph'][ $key2 ]['id']       = 'site-navigation';
					$schema['@graph'][ $key2 ]['name']     = isset( $value2['name'] ) ? $value2['name'] : '';
					$schema['@graph'][ $key2 ]['url']      = isset( $value2['url'] ) ? $value2['url'] : '';
				}
			}

			return apply_filters( 'wp_schema_pro_global_schema_site_navigation_element', $schema, $post );
		}

	}
}
