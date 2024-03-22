<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.1.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Global_Sitelink_Search_Box' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.1.0
	 */
	class BSF_AIOSRS_Pro_Schema_Global_Sitelink_Search_Box {

		/**
		 * Render Schema.
		 *
		 * @param  array $post Current Post Array.
		 * @return array
		 */
		public static function render( $post ) {
			$schema = array();

			$general_settings   = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-general-settings'];
			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = 'WebSite';
			$schema['name']     = ( isset( $general_settings['site-name'] ) && ! empty( $general_settings['site-name'] ) ) ? $general_settings['site-name'] : wp_strip_all_tags( get_bloginfo( 'name' ) );
			$schema['url']      = home_url();

			$potential_action = array();

			$template_urls = apply_filters( 'wp_schema_pro_sitelink_srearch_box_template_urls', array( site_url( '?s=' ) ) );
			foreach ( $template_urls as $template_url ) {
				$potential_action[] = array(
					'@type'       => 'SearchAction',
					'target'      => esc_url( $template_url ) . '{search_term_string}',
					'query-input' => 'required name=search_term_string',
				);
			}

			$schema['potentialAction'] = $potential_action;

			return apply_filters( 'wp_schema_pro_global_schema_sitelink_search_box', $schema, $post );
		}

	}
}
