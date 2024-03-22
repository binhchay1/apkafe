<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Local_Business' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema_Local_Business {

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

			if ( isset( $data['schema-type'] ) && ! empty( $data['schema-type'] ) && 'ProfessionalService' !== $data['schema-type'] ) {
				$schema['@type'] = $data['schema-type'];
			} else {
				$schema['@type'] = 'LocalBusiness';
			}

			$schema['name'] = ! empty( $data['name'] ) ? wp_strip_all_tags( (string) $data['name'] ) : null;

			if ( isset( $data['image'] ) && ! empty( $data['image'] ) ) {
				$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['image'] );
			}

			$schema['telephone'] = ! empty( $data['telephone'] ) ? wp_strip_all_tags( (string) $data['telephone'] ) : null;

			$schema['url'] = ! empty( $data['url'] ) ? wp_strip_all_tags( (string) $data['url'] ) : null;

			if ( ( isset( $data['location-street'] ) && ! empty( $data['location-street'] ) ) ||
				( isset( $data['location-locality'] ) && ! empty( $data['location-locality'] ) ) ||
				( isset( $data['location-postal'] ) && ! empty( $data['location-postal'] ) ) ||
				( isset( $data['location-region'] ) && ! empty( $data['location-region'] ) ) ||
				( isset( $data['location-country'] ) && ! empty( $data['location-country'] ) ) ) {

				$schema['address']['@type'] = 'PostalAddress';

				$schema['address']['streetAddress']   = ! empty( $data['location-street'] ) ? wp_strip_all_tags( (string) $data['location-street'] ) : null;
				$schema['address']['addressLocality'] = ! empty( $data['location-locality'] ) ? wp_strip_all_tags( (string) $data['location-locality'] ) : null;
				$schema['address']['postalCode']      = ! empty( $data['location-postal'] ) ? wp_strip_all_tags( (string) $data['location-postal'] ) : null;
				$schema['address']['addressRegion']   = ! empty( $data['location-region'] ) ? wp_strip_all_tags( (string) $data['location-region'] ) : null;
				$schema['address']['addressCountry']  = ! empty( $data['location-country'] ) ? wp_strip_all_tags( (string) $data['location-country'] ) : null;
			}

			if ( ! empty( $data['rating'] ) && ! empty( $data['review-count'] ) && 'none' !== $data['rating'] && 'none' !== $data['review-count'] ) {

				$schema['aggregateRating']['@type'] = 'AggregateRating';

				$schema['aggregateRating']['ratingValue'] = ! empty( $data['rating'] ) ? wp_strip_all_tags( (string) $data['rating'] ) : null;
				$schema['aggregateRating']['reviewCount'] = ! empty( $data['review-count'] ) ? wp_strip_all_tags( (string) $data['review-count'] ) : null;
			}

			$schema['priceRange'] = ! empty( $data['price-range'] ) ? wp_strip_all_tags( (string) $data['price-range'] ) : null;

			if ( isset( $data['hours-specification'] ) && ! empty( $data['hours-specification'] ) ) {
				foreach ( $data['hours-specification'] as $key => $value ) {
					$schema['openingHoursSpecification'][ $key ]['@type'] = 'OpeningHoursSpecification';
					$days = explode( ',', $value['days'] );
					$days = array_map( 'trim', $days );
					$schema['openingHoursSpecification'][ $key ]['dayOfWeek'] = $days;
					$schema['openingHoursSpecification'][ $key ]['opens']     = $value['opens'];
					$schema['openingHoursSpecification'][ $key ]['closes']    = $value['closes'];
				}
			}
			if ( isset( $data['geo-latitude'] ) && isset( $data['geo-longitude'] ) ) {
				$schema['geo']['@type']     = 'GeoCoordinates';
				$schema['geo']['latitude']  = wp_strip_all_tags( (string) $data['geo-latitude'] );
				$schema['geo']['longitude'] = wp_strip_all_tags( (string) $data['geo-longitude'] );

			}
			$contact_type       = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-corporate-contact'];
			$contact_hear       = isset( $contact_type['contact-hear'] ) ? $contact_type['contact-hear'] : '';
			$contact_toll       = isset( $contact_type['contact-toll'] ) ? $contact_type['contact-toll'] : '';
			$contact_point_type = $contact_hear . ' ' . $contact_toll;
			$contact_point_type = explode( ' ', $contact_point_type );
			if ( '1' === $contact_type['cp-schema-type'] && true === apply_filters( 'wp_schema_pro_contactpoint_local_business_schema_enabled', true ) && isset( $contact_type['contact-type'] ) && ! empty( $contact_type['contact-type'] ) ) {
						$schema['ContactPoint']['@type'] = 'ContactPoint';

				$schema ['ContactPoint']['contactType'] = ! empty( $contact_type['contact-type'] ) ? wp_strip_all_tags( (string) $contact_type['contact-type'] ) : null;
				$schema ['ContactPoint']['telephone']   = ! empty( $contact_type['telephone'] ) ? wp_strip_all_tags( (string) $contact_type['telephone'] ) : null;
				if ( isset( $contact_type['url'] ) && ! empty( $contact_type['url'] ) ) {
					$schema ['ContactPoint']['url'] = esc_url( $contact_type['url'] );
				}
				$schema ['ContactPoint']['email'] = ! empty( $contact_type['email'] ) ? wp_strip_all_tags( (string) $contact_type['email'] ) : null;
				if ( isset( $contact_type['areaServed'] ) && ! empty( $contact_type['areaServed'] ) ) {
					$language = explode( ',', $contact_type['areaServed'] );
					foreach ( $language as $key => $value ) {
						$schema ['ContactPoint']['areaServed'][ $key ] = wp_strip_all_tags( (string) $value );
					}
				}
				foreach ( $contact_point_type  as $key => $value ) {
					$schema ['ContactPoint']['contactOption'][ $key ] = wp_strip_all_tags( (string) $value );
				}
				$schema ['ContactPoint']['availableLanguage'] = ! empty( $contact_type['availableLanguage'] ) ? wp_strip_all_tags( (string) $contact_type['availableLanguage'] ) : null;
			}

			return apply_filters( 'wp_schema_pro_schema_local_business', $schema, $data, $post );
		}

	}
}
