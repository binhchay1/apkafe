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

			$schema             = array();
			$general_settings   = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-general-settings'];
			$social_profiles    = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-social-profiles'];
			$contact_type       = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-corporate-contact'];
			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = 'Person';
			$schema['name']     = ( isset( $general_settings['person-name'] ) && ! empty( $general_settings['person-name'] ) ) ? $general_settings['person-name'] : wp_strip_all_tags( get_bloginfo( 'name' ) );
			$schema['url']      = wp_strip_all_tags( get_bloginfo( 'url' ) );
			if ( isset( $contact_type['contact-type'] ) && ! empty( $contact_type['contact-type'] ) || isset( $contact_type['telephone'] ) && ! empty( $contact_type['telephone'] ) || isset( $contact_type['contact-page-id'] ) && ! empty( $contact_type['contact-page-id'] ) ) {
				$schema['ContactPoint']['@type'] = 'ContactPoint';
				if ( isset( $contact_type['contact-type'] ) && ! empty( $contact_type['contact-type'] ) ) {
					if ( 'other' === $contact_type['contact-type'] ) {
						$schema ['ContactPoint']['contactType'] = $contact_type['contact-type-other'];
					} else {
						$schema ['ContactPoint']['contactType'] = $contact_type['contact-type'];
					}
				}
				if ( isset( $contact_type['telephone'] ) && ! empty( $contact_type['telephone'] ) ) {
					$schema ['ContactPoint']['telephone'] = $contact_type['telephone'];
				}
				if ( isset( $contact_type['contact-page-id'] ) && ! empty( $contact_type['contact-page-id'] ) ) {
					$page_url                       = get_permalink( $contact_type['contact-page-id'] );
					$schema ['ContactPoint']['url'] = $page_url;
				} else {
					$schema ['ContactPoint']['url'] = $contact_type['contact-page-id'];
				}
			}
			foreach ( $social_profiles as $type => $social_link ) {
				if ( 'other' === $type ) {
					foreach ( $social_link as $dynamic_social_link ) {
						if ( ! empty( $dynamic_social_link ) ) {
							$schema['sameAs'][] = $dynamic_social_link;
						}
					}
				} else {
					if ( ! empty( $social_link ) && ( ! is_array( $social_link ) ) ) {
						$schema['sameAs'][] = $social_link;
					}
				}
			}

			return apply_filters( 'wp_schema_pro_global_schema_person', $schema, $post );
		}

	}
}
