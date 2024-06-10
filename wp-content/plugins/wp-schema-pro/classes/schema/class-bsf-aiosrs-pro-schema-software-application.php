<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Software_Application' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema_Software_Application {

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
			$schema['@type']    = 'SoftwareApplication';

			$schema['name'] = ! empty( $data['name'] ) ? wp_strip_all_tags( (string) $data['name'] ) : null;

			$schema['operatingSystem'] = ! empty( $data['operating-system'] ) ? wp_strip_all_tags( (string) $data['operating-system'] ) : null;

			$schema['applicationCategory'] = ! empty( $data['category'] ) ? wp_strip_all_tags( (string) $data['category'] ) : null;

			if ( isset( $data['image'] ) && ! empty( $data['image'] ) ) {
				$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['image'] );
			}

			if ( ( isset( $data['rating'] ) && ! empty( $data['rating'] ) ) ||
				( isset( $data['review-count'] ) && ! empty( $data['review-count'] ) ) ) {

				$schema['aggregateRating']['@type'] = 'AggregateRating';

				$schema['aggregateRating']['ratingValue'] = ! empty( $data['rating'] ) ? wp_strip_all_tags( (string) $data['rating'] ) : null;
				$schema['aggregateRating']['reviewCount'] = ! empty( $data['review-count'] ) ? wp_strip_all_tags( (string) $data['review-count'] ) : null;
			}

			$schema['offers']['@type'] = 'Offer';
			$schema['offers']['price'] = '0';

			$schema['offers']['price'] = ! empty( $data['price'] ) ? wp_strip_all_tags( (string) $data['price'] ) : null;

			$schema['offers']['priceCurrency'] = ! empty( $data['currency'] ) ? wp_strip_all_tags( (string) $data['currency'] ) : null;

			return apply_filters( 'wp_schema_pro_schema_software_application', $schema, $data, $post );
		}

	}
}
