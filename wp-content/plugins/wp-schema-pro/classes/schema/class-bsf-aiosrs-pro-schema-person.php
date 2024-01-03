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
		 * @param  array $data Meta Data.
		 * @param  array $post Current Post Array.
		 * @return array
		 */
		public static function render( $data, $post ) {
			$schema = array();

			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = 'Person';

			if ( isset( $data['name'] ) && ! empty( $data['name'] ) ) {
				$schema['name'] = esc_html( wp_strip_all_tags( $data['name'] ) );
			}

			if ( ( isset( $data['street'] ) && ! empty( $data['street'] ) ) ||
				( isset( $data['locality'] ) && ! empty( $data['locality'] ) ) ||
				( isset( $data['postal'] ) && ! empty( $data['postal'] ) ) ||
				( isset( $data['region'] ) && ! empty( $data['region'] ) ) ) {

				$schema['address']['@type'] = 'PostalAddress';

				if ( isset( $data['locality'] ) && ! empty( $data['locality'] ) ) {
					$schema['address']['addressLocality'] = esc_html( wp_strip_all_tags( $data['locality'] ) );
				}

				if ( isset( $data['region'] ) && ! empty( $data['region'] ) ) {
					$schema['address']['addressRegion'] = esc_html( wp_strip_all_tags( $data['region'] ) );
				}

				if ( isset( $data['postal'] ) && ! empty( $data['postal'] ) ) {
					$schema['address']['postalCode'] = esc_html( wp_strip_all_tags( $data['postal'] ) );
				}

				if ( isset( $data['street'] ) && ! empty( $data['street'] ) ) {
					$schema['address']['streetAddress'] = esc_html( wp_strip_all_tags( $data['street'] ) );
				}
			}

			if ( isset( $data['email'] ) && ! empty( $data['email'] ) ) {
				$schema['email'] = esc_html( wp_strip_all_tags( $data['email'] ) );
			}

			if ( isset( $data['gender'] ) && ! empty( $data['gender'] ) ) {
				$schema['gender'] = esc_html( wp_strip_all_tags( $data['gender'] ) );
			}

			if ( isset( $data['dob'] ) && ! empty( $data['dob'] ) ) {
				$date_informat       = gmdate( 'Y.m.d', strtotime( $data['dob'] ) );
				$schema['birthDate'] = esc_html( wp_strip_all_tags( $date_informat ) );
			}

			if ( isset( $data['member'] ) && ! empty( $data['member'] ) ) {
				$schema['memberOf'] = esc_html( wp_strip_all_tags( $data['member'] ) );
			}

			if ( isset( $data['nationality'] ) && ! empty( $data['nationality'] ) ) {
				$schema['nationality'] = esc_html( wp_strip_all_tags( $data['nationality'] ) );
			}

			if ( isset( $data['image'] ) && ! empty( $data['image'] ) ) {
				$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['image'] );
			}

			if ( isset( $data['job-title'] ) && ! empty( $data['job-title'] ) ) {
				$schema['jobTitle'] = esc_html( wp_strip_all_tags( $data['job-title'] ) );
			}

			if ( isset( $data['telephone'] ) && ! empty( $data['telephone'] ) ) {
				$schema['telephone'] = esc_html( wp_strip_all_tags( $data['telephone'] ) );
			}

			if ( isset( $data['homepage-url'] ) && ! empty( $data['homepage-url'] ) ) {
				$schema['url'] = esc_url( $data['homepage-url'] );
			}

			if ( isset( $data['add-url'] ) && ! empty( $data['add-url'] ) ) {
				foreach ( $data['add-url'] as $key => $value ) {
					if ( isset( $value['same-as'] ) && ! empty( $value['same-as'] ) ) {
						$schema['sameAs'][ $key ] = esc_url( $value['same-as'] );
					}
				}
			}
			$contact_type = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-corporate-contact'];
			$contact_hear = isset( $contact_type['contact-hear'] ) ? $contact_type['contact-hear'] : '';
			$contact_toll = isset( $contact_type['contact-toll'] ) ? $contact_type['contact-toll'] : '';
			$contactpoint = array( $contact_hear, $contact_toll );
			if ( '1' === isset( $contact_type['cp-schema-type'] ) && true === apply_filters( 'wp_schema_pro_contactpoint_person_schema_enabled', true ) ) {
				if ( isset( $contact_type['contact-type'] ) && ! empty( $contact_type['contact-type'] ) ) {
						$schema['ContactPoint']['@type'] = 'ContactPoint';

					if ( isset( $contact_type['contact-type'] ) && ! empty( $contact_type['contact-type'] ) ) {
						$schema ['ContactPoint']['contactType'] = esc_html( wp_strip_all_tags( $contact_type['contact-type'] ) );
					}
					if ( isset( $contact_type['telephone'] ) && ! empty( $contact_type['telephone'] ) ) {
						$schema ['ContactPoint']['telephone'] = esc_html( wp_strip_all_tags( $contact_type['telephone'] ) );
					}
					if ( isset( $contact_type['url'] ) && ! empty( $contact_type['url'] ) ) {
						$schema ['ContactPoint']['url'] = esc_url( $contact_type['url'] );
					}
					if ( isset( $contact_type['email'] ) && ! empty( $contact_type['email'] ) ) {
						$schema ['ContactPoint']['email'] = esc_html( wp_strip_all_tags( $contact_type['email'] ) );
					}
					if ( isset( $contact_type['areaServed'] ) && ! empty( $contact_type['areaServed'] ) ) {
						$schema ['ContactPoint']['areaServed'] = esc_html( wp_strip_all_tags( $contact_type['areaServed'] ) );
					}
					if ( isset( $contactpoint ) && ! empty( $contactpoint ) ) {

						$schema ['ContactPoint']['contactOption'] = esc_html( wp_strip_all_tags( $contactpoint ) );

					}

					if ( isset( $contact_type['availableLanguage'] ) && ! empty( $contact_type['availableLanguage'] ) ) {
						$schema ['ContactPoint']['availableLanguage'] = esc_html( wp_strip_all_tags( $contact_type['availableLanguage'] ) );
					}
				}
			}

			return apply_filters( 'wp_schema_pro_schema_person', $schema, $data, $post );
		}

	}
}
