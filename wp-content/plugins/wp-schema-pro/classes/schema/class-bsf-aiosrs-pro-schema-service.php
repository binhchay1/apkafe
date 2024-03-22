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
		 * @param  array $data Meta Data.
		 * @param  array $post Current Post Array.
		 * @return array
		 */
		public static function render( $data, $post ) {
			$schema = array();

			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = 'Service';

			$schema['name'] = ! empty( $data['name'] ) ? wp_strip_all_tags( (string) $data['name'] ) : null;

			$schema['serviceType'] = ! empty( $data['type'] ) ? wp_strip_all_tags( (string) $data['type'] ) : null;

			if ( isset( $data['image'] ) && ! empty( $data['image'] ) ) {
				$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['image'] );
			}

			if ( ( isset( $data['provider'] ) && ! empty( $data['provider'] ) ) ||
				( isset( $data['location-image'] ) && ! empty( $data['location-image'] ) ) ||
				( isset( $data['telephone'] ) && ! empty( $data['telephone'] ) ) ||
				( isset( $data['price-range'] ) && ! empty( $data['price-range'] ) ) ) {

				$schema['provider']['@type'] = 'LocalBusiness';

				$schema['provider']['name'] = ! empty( $data['provider'] ) ? wp_strip_all_tags( (string) $data['provider'] ) : null;
				if ( isset( $data['location-image'] ) && ! empty( $data['location-image'] ) ) {
					$schema['provider']['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['location-image'] );
				}
				$schema['provider']['telephone']  = ! empty( $data['telephone'] ) ? wp_strip_all_tags( (string) $data['telephone'] ) : null;
				$schema['provider']['priceRange'] = ! empty( $data['price-range'] ) ? wp_strip_all_tags( (string) $data['price-range'] ) : null;
			}

			if ( ( isset( $data['location-locality'] ) && ! empty( $data['location-locality'] ) ) ||
				( isset( $data['location-region'] ) && ! empty( $data['location-region'] ) ) ||
				( isset( $data['location-street'] ) && ! empty( $data['location-street'] ) ) ) {

				$schema['provider']['@type']            = 'LocalBusiness';
				$schema['provider']['address']['@type'] = 'PostalAddress';

				$schema['provider']['address']['addressLocality'] = ! empty( $data['location-locality'] ) ? wp_strip_all_tags( (string) $data['location-locality'] ) : null;
				$schema['provider']['address']['addressRegion']   = ! empty( $data['location-region'] ) ? wp_strip_all_tags( (string) $data['location-region'] ) : null;
				$schema['provider']['address']['streetAddress']   = ! empty( $data['location-street'] ) ? wp_strip_all_tags( (string) $data['location-street'] ) : null;
			}

			if ( isset( $data['area'] ) && ! empty( $data['area'] ) ) {
				$schema['areaServed']['@type'] = 'State';
				$schema['areaServed']['name']  = wp_strip_all_tags( (string) $data['area'] );
			}

			$schema['description'] = ! empty( $data['description'] ) ? wp_strip_all_tags( (string) $data['description'] ) : null;

			return apply_filters( 'wp_schema_pro_schema_service', $schema, $data, $post );
		}

	}
}
