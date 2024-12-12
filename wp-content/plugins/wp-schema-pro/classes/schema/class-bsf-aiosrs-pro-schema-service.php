<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Service' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema_Service {

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
			$schema['@type']    = 'Service';

			$schema['name'] = ! empty( $data['name'] ) && is_string( $data['name'] ) ? wp_strip_all_tags( $data['name'] ) : null;

			$schema['serviceType'] = ! empty( $data['type'] ) && is_string( $data['type'] ) ? wp_strip_all_tags( $data['type'] ) : null;

			if ( isset( $data['image'] ) && is_array( $data['image'] ) ) {
				$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['image'] );
			}

			if ( ( isset( $data['provider'] ) && is_string( $data['provider'] ) ) ||
				( isset( $data['location-image'] ) && is_array( $data['location-image'] ) ) ||
				( isset( $data['telephone'] ) && is_string( $data['telephone'] ) ) ||
				( isset( $data['price-range'] ) && is_string( $data['price-range'] ) ) ) {

				$schema['provider']['@type'] = 'LocalBusiness';

				$schema['provider']['name'] = ! empty( $data['provider'] ) && is_string( $data['provider'] ) ? wp_strip_all_tags( $data['provider'] ) : null;
				if ( isset( $data['location-image'] ) && is_array( $data['location-image'] ) ) {
					$schema['provider']['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['location-image'] );
				}
				$schema['provider']['telephone']  = ! empty( $data['telephone'] ) && is_string( $data['telephone'] ) ? wp_strip_all_tags( $data['telephone'] ) : null;
				$schema['provider']['priceRange'] = ! empty( $data['price-range'] ) && is_string( $data['price-range'] ) ? wp_strip_all_tags( $data['price-range'] ) : null;
			}

			if ( ( isset( $data['location-locality'] ) && ! empty( $data['location-locality'] ) ) ||
			( isset( $data['location-region'] ) && ! empty( $data['location-region'] ) ) ||
			( isset( $data['location-street'] ) && ! empty( $data['location-street'] ) ) ||
			( isset( $data['addressCountry'] ) && ! empty( $data['addressCountry'] ) ) ||
			( isset( $data['postalCode'] ) && ! empty( $data['postalCode'] ) ) ) {

				$schema['provider']['@type']            = 'LocalBusiness';
				$schema['provider']['address']['@type'] = 'PostalAddress';

				$schema['provider']['address']['addressLocality'] = ! empty( $data['location-locality'] ) ? wp_strip_all_tags( (string) $data['location-locality'] ) : null;
				$schema['provider']['address']['addressRegion']   = ! empty( $data['location-region'] ) ? wp_strip_all_tags( (string) $data['location-region'] ) : null;
				$schema['provider']['address']['streetAddress']   = ! empty( $data['location-street'] ) ? wp_strip_all_tags( (string) $data['location-street'] ) : null;
				$schema['provider']['address']['addressCountry']  = ! empty( $data['addressCountry'] ) ? wp_strip_all_tags( (string) $data['addressCountry'] ) : null;
				$schema['provider']['address']['postalCode']      = ! empty( $data['postalCode'] ) ? wp_strip_all_tags( (string) $data['postalCode'] ) : null;
			}

			if ( isset( $data['area'] ) && is_string( $data['area'] ) ) {
				$schema['areaServed']['@type'] = 'State';
				$schema['areaServed']['name']  = wp_strip_all_tags( $data['area'] );
			}

			$schema['description'] = ! empty( $data['description'] ) && is_string( $data['description'] ) ? wp_strip_all_tags( $data['description'] ) : null;

			return apply_filters( 'wp_schema_pro_schema_service', $schema, $data, $post );
		}

	}
}
