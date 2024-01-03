<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.1.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Global_Person' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.1.0
	 */
	class BSF_AIOSRS_Pro_Schema_Global_Person {

		/**
		 * Render Schema.
		 *
		 * @param  array $post Current Post Array.
		 * @return array
		 */
		public static function render( $post ) {

			$schema           = array();
			$general_settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-general-settings'];
			$social_profiles  = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-social-profiles'];

			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = 'Person';
			$schema['name']     = ( isset( $general_settings['person-name'] ) && ! empty( $general_settings['person-name'] ) ) ? $general_settings['person-name'] : wp_strip_all_tags( get_bloginfo( 'name' ) );
			$schema['url']      = wp_strip_all_tags( get_bloginfo( 'url' ) );

			foreach ( $social_profiles as $social_link ) {
				if ( ! empty( $social_link ) ) {
					$schema['sameAs'][] = $social_link;
				}
			}

			return apply_filters( 'wp_schema_pro_global_schema_person', $schema, $post );
		}

	}
}
