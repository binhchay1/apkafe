<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Person' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema_Person {

		/**
		 * Render Schema.
		 *
		 * @param  array<string, mixed> $data Meta Data.
		 * @param  array<string, mixed> $post Current Post Array.
		 * @return array<string, mixed>
		 */
		public static function render( array $data, array $post ): array {
			$schema = array();

			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = 'Person';

			$schema['name'] = ! empty( $data['name'] ) ? wp_strip_all_tags( (string) $data['name'] ) : null;

			if ( ( isset( $data['street'] ) && ! empty( $data['street'] ) ) ||
				( isset( $data['locality'] ) && ! empty( $data['locality'] ) ) ||
				( isset( $data['postal'] ) && ! empty( $data['postal'] ) ) ||
				( isset( $data['region'] ) && ! empty( $data['region'] ) ) ) {

				$schema['address']['@type'] = 'PostalAddress';

				$schema['address']['addressLocality'] = ! empty( $data['locality'] ) ? wp_strip_all_tags( (string) $data['locality'] ) : null;

				$schema['address']['addressRegion'] = ! empty( $data['region'] ) ? wp_strip_all_tags( (string) $data['region'] ) : null;

				$schema['address']['postalCode'] = ! empty( $data['postal'] ) ? wp_strip_all_tags( (string) $data['postal'] ) : null;

				$schema['address']['streetAddress'] = ! empty( $data['street'] ) ? wp_strip_all_tags( (string) $data['street'] ) : null;
			}

			$schema['email'] = ! empty( $data['email'] ) ? wp_strip_all_tags( (string) $data['email'] ) : null;

			$schema['gender'] = ! empty( $data['gender'] ) ? wp_strip_all_tags( (string) $data['gender'] ) : null;

			if ( isset( $data['dob'] ) && ! empty( $data['dob'] ) ) {
				$timestamp = strtotime( (string) $data['dob'] );
				if ( $timestamp !== false ) {
					$date_informat       = gmdate( 'Y.m.d', $timestamp );
					$schema['birthDate'] = wp_strip_all_tags( $date_informat );
				}
			}

			$schema['memberOf'] = ! empty( $data['member'] ) ? wp_strip_all_tags( (string) $data['member'] ) : null;

			$schema['nationality'] = ! empty( $data['nationality'] ) ? wp_strip_all_tags( (string) $data['nationality'] ) : null;

			if ( isset( $data['image'] ) && is_array( $data['image'] ) ) {
				$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['image'] );
			}

			$schema['jobTitle'] = ! empty( $data['job-title'] ) ? wp_strip_all_tags( (string) $data['job-title'] ) : null;

			$schema['telephone'] = ! empty( $data['telephone'] ) ? wp_strip_all_tags( (string) $data['telephone'] ) : null;

			if ( isset( $data['homepage-url'] ) && ! empty( $data['homepage-url'] ) ) {
				$schema['url'] = esc_url( (string) $data['homepage-url'] );
			}

			if ( isset( $data['add-url'] ) && is_array( $data['add-url'] ) ) {
				foreach ( $data['add-url'] as $key => $value ) {
					if ( isset( $value['same-as'] ) && ! empty( $value['same-as'] ) ) {
						$schema['sameAs'][ $key ] = esc_url( (string) $value['same-as'] );
					}
				}
			}

			$contact_type       = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-corporate-contact'] ?? array();
			$contact_hear       = isset( $contact_type['contact-hear'] ) ? (string) $contact_type['contact-hear'] : '';
			$contact_toll       = isset( $contact_type['contact-toll'] ) ? (string) $contact_type['contact-toll'] : '';
			$contact_point_type = $contact_hear . ' ' . $contact_toll;
			$contact_point_type = explode( ' ', $contact_point_type );

			if ( isset( $contact_type['cp-schema-type'] ) && '1' === $contact_type['cp-schema-type'] && true === apply_filters( 'wp_schema_pro_contactpoint_person_schema_enabled', true ) && isset( $contact_type['contact-type'] ) && ! empty( $contact_type['contact-type'] ) ) {
				$schema['ContactPoint']['@type'] = 'ContactPoint';

				$schema['ContactPoint']['contactType'] = wp_strip_all_tags( (string) $contact_type['contact-type'] );
				$schema['ContactPoint']['telephone']   = wp_strip_all_tags( (string) $contact_type['telephone'] );

				if ( isset( $contact_type['url'] ) && ! empty( $contact_type['url'] ) ) {
					$schema['ContactPoint']['url'] = esc_url( (string) $contact_type['url'] );
				}

				$schema['ContactPoint']['email'] = ! empty( $contact_type['email'] ) ? wp_strip_all_tags( (string) $contact_type['email'] ) : null;

				if ( isset( $contact_type['areaServed'] ) && ! empty( $contact_type['areaServed'] ) ) {
					$language = explode( ',', (string) $contact_type['areaServed'] );
					if ( is_array( $language ) ) {
						foreach ( $language as $key => $value ) {
							$schema['ContactPoint']['areaServed'][ $key ] = wp_strip_all_tags( (string) $value );
						}
					}
				}

				if ( is_array( $contact_point_type ) ) {
					foreach ( $contact_point_type as $key => $value ) {
						$schema['ContactPoint']['contactOption'][ $key ] = wp_strip_all_tags( (string) $value );
					}
				}

				$schema['ContactPoint']['availableLanguage'] = ! empty( $contact_type['availableLanguage'] ) ? wp_strip_all_tags( (string) $contact_type['availableLanguage'] ) : null;
			}

			return apply_filters( 'wp_schema_pro_schema_person', $schema, $data, $post );
		}

	}
}
