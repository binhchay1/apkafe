<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.1.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Global_Organization' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.1.0
	 */
	class BSF_AIOSRS_Pro_Schema_Global_Organization {

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
			$contact_type     = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-corporate-contact'];
			$contact_hear     = isset( $contact_type['contact-hear'] ) ? $contact_type['contact-hear'] : '';
			$contact_toll     = isset( $contact_type['contact-toll'] ) ? $contact_type['contact-toll'] : '';
			$contactpoint     = array( $contact_hear, $contact_toll );

			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = ( isset( $general_settings['organization'] ) && ! empty( $general_settings['organization'] ) ) ? $general_settings['organization'] : 'organization';
			$schema['name']     = ( isset( $general_settings['site-name'] ) && ! empty( $general_settings['site-name'] ) ) ? $general_settings['site-name'] : wp_strip_all_tags( get_bloginfo( 'name' ) );
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
				if ( isset( $contact_type['email'] ) && ! empty( $contact_type['email'] ) ) {
					$schema ['ContactPoint']['email'] = $contact_type['email'];
				}
				if ( isset( $contactpoint ) && ! empty( $contactpoint ) ) {

					$schema ['ContactPoint']['contactOption'] = $contactpoint;
				}
				if ( isset( $contact_type['areaServed'] ) && ! empty( $contact_type['areaServed'] ) ) {
					$language = explode( ',', $contact_type['areaServed'] );
					foreach ( $language as $key => $value ) {
						$schema ['ContactPoint']['areaServed'][ $key ] = wp_strip_all_tags( $value );
					}
				}
				if ( isset( $contact_type['availableLanguage'] ) && ! empty( $contact_type['availableLanguage'] ) ) {
					$language = explode( ',', $contact_type['availableLanguage'] );
					foreach ( $language as $key => $value ) {
						$schema ['ContactPoint']['availableLanguage'][ $key ] = wp_strip_all_tags( $value );
					}
				} else {
					$schema ['ContactPoint']['availableLanguage'] = 'English';
				}
			}

			$logo_id = get_post_thumbnail_id( $post['ID'] );
			if ( isset( $general_settings['site-logo-custom'] ) ) {
				$logo_id = isset( $general_settings['site-logo-custom'] ) ? $general_settings['site-logo-custom'] : '';
			} elseif ( isset( $general_settings['site-logo'] ) && 'customizer-logo' === $general_settings['site-logo'] ) {
				if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
						$logo_id = get_theme_mod( 'custom_logo' );
				}
			}
			if ( $logo_id ) {
				$key            = 'site-logo';
				$logo_image     = BSF_AIOSRS_Pro_Schema_Template::get_image_object( $logo_id, $key );
				$schema['logo'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $logo_image, 'ImageObject' );
			}

			foreach ( $social_profiles as $type => $social_link ) {
				if ( 'other' === $type ) {
					foreach ( $social_link as $dynamic_social_link ) {
						if ( ! empty( $dynamic_social_link ) ) {
							$schema['sameAs'][] = $dynamic_social_link;
						}
					}
				}
				if ( ! empty( $social_link ) && ( ! is_array( $social_link ) ) ) {

					$schema['sameAs'][] = $social_link;
				}
			}

			return apply_filters( 'wp_schema_pro_global_schema_organization', $schema, $post );
		}

	}
}
