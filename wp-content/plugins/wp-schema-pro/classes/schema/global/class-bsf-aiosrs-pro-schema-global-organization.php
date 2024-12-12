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
		 * @param  array<string, mixed> $post Current Post Array.
		 * @return array<string, mixed>
		 */
		public static function render( array $post ): array {
			$schema           = array();
			$general_settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-general-settings'];
			$social_profiles  = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-social-profiles'];
			$contact_type     = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-corporate-contact'];
			$contact_hear     = $contact_type['contact-hear'] ?? '';
			$contact_toll     = $contact_type['contact-toll'] ?? '';
			$contactpoint     = array( $contact_hear, $contact_toll );

			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = $general_settings['organization'] ?? 'organization';
			$schema['name']     = $general_settings['site-name'] ?? wp_strip_all_tags( get_bloginfo( 'name' ) );
			$schema['url']      = wp_strip_all_tags( get_bloginfo( 'url' ) );
			if ( ! empty( $contact_type['contact-type'] ) || ! empty( $contact_type['telephone'] ) || ! empty( $contact_type['contact-page-id'] ) ) {
				$schema['ContactPoint']['@type'] = 'ContactPoint';
				if ( ! empty( $contact_type['contact-type'] ) ) {
					$schema['ContactPoint']['contactType'] = $contact_type['contact-type'] === 'other'
						? $contact_type['contact-type-other']
						: $contact_type['contact-type'];
				}
				if ( ! empty( $contact_type['telephone'] ) ) {
					$schema['ContactPoint']['telephone'] = $contact_type['telephone'];
				}
				if ( ! empty( $contact_type['contact-page-id'] ) ) {
					$page_id = $contact_type['contact-page-id'];
					if ( is_int( $page_id ) || $page_id instanceof WP_Post ) {
						$schema['ContactPoint']['url'] = get_permalink( $page_id );
					} else {
						$schema['ContactPoint']['url'] = $page_id;
					}
				}
				if ( ! empty( $contact_type['email'] ) ) {
					$schema['ContactPoint']['email'] = $contact_type['email'];
				}
				$schema['ContactPoint']['contactOption'] = $contactpoint;
				
				if ( ! empty( $contact_type['areaServed'] ) && is_string( $contact_type['areaServed'] ) ) {
					$language = explode( ',', $contact_type['areaServed'] );
					foreach ( $language as $key => $value ) {
						$schema['ContactPoint']['areaServed'][ $key ] = wp_strip_all_tags( $value );
					}
				}
				if ( ! empty( $contact_type['availableLanguage'] ) && is_string( $contact_type['availableLanguage'] ) ) {
					$language = explode( ',', $contact_type['availableLanguage'] );
					foreach ( $language as $key => $value ) {
						$schema['ContactPoint']['availableLanguage'][ $key ] = wp_strip_all_tags( $value );
					}
				} else {
					$schema['ContactPoint']['availableLanguage'] = 'English';
				}
			}

			$post_id = $post['ID'] ?? null;
			if ( $post_id && ( is_int( $post_id ) || is_string( $post_id ) ) ) {
				$logo_id = get_post_thumbnail_id( (int) $post_id );
			}
			if ( isset( $general_settings['site-logo-custom'] ) ) {
				$logo_id = $general_settings['site-logo-custom'];
			} elseif ( isset( $general_settings['site-logo'] ) && $general_settings['site-logo'] === 'customizer-logo' ) {
				if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
					$logo_id = get_theme_mod( 'custom_logo' );
				}
			}
			if ( $logo_id ) {
				$key        = 'site-logo';
				$logo_image = BSF_AIOSRS_Pro_Schema_Template::get_image_object( $logo_id, $key );
				if ( $logo_image !== false ) {
					$schema['logo'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $logo_image, 'ImageObject' );
				}
			}

			if ( is_array( $social_profiles ) ) {
				foreach ( $social_profiles as $type => $social_link ) {
					if ( $type === 'other' ) {
						foreach ( $social_link as $dynamic_social_link ) {
							if ( ! empty( $dynamic_social_link ) ) {
								$schema['sameAs'][] = $dynamic_social_link;
							}
						}
					}
					if ( ! empty( $social_link ) && ! is_array( $social_link ) ) {
						$schema['sameAs'][] = $social_link;
					}
				}
			}

			foreach ( $social_profiles as $type => $social_link ) {
				if ( 'other' === $type && is_array( $social_link ) ) {
					foreach ( $social_link as $dynamic_social_link ) {
						if ( ! empty( $dynamic_social_link ) ) {
							$schema['sameAs'][] = $dynamic_social_link;
						}
					}
				}
				if ( ! empty( $social_link ) && ! is_array( $social_link ) ) {

					$schema['sameAs'][] = $social_link;
				}
			}

			return apply_filters( 'wp_schema_pro_global_schema_organization', $schema, $post );
		}

	}
}

