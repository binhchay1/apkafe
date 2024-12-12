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
		 * @param  array<mixed> $data Meta Data.
		 * @param  array<mixed> $post Current Post Array.
		 * @return array<mixed>
		 */
		public static function render( array $data, array $post ): array {
			$schema = array();

			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = 'SoftwareApplication';

			$schema['name'] = isset( $data['name'] ) ? wp_strip_all_tags( (string) $data['name'] ) : null;

			$schema['operatingSystem'] = isset( $data['operating-system'] ) ? wp_strip_all_tags( (string) $data['operating-system'] ) : null;

			$schema['applicationCategory'] = isset( $data['category'] ) ? wp_strip_all_tags( (string) $data['category'] ) : null;

			if ( isset( $data['image'] ) && is_array( $data['image'] ) && ! empty( $data['image'] ) ) {
				$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['image'] );
			}

			if ( ( isset( $data['rating'] ) && is_numeric( $data['rating'] ) ) ||
				( isset( $data['review-count'] ) && is_numeric( $data['review-count'] ) ) ) {

				$schema['aggregateRating']['@type'] = 'AggregateRating';

				$schema['aggregateRating']['ratingValue'] = isset( $data['rating'] ) ? wp_strip_all_tags( (string) $data['rating'] ) : null;
				$schema['aggregateRating']['reviewCount'] = isset( $data['review-count'] ) ? wp_strip_all_tags( (string) $data['review-count'] ) : null;
			}

			$schema['offers']['@type'] = 'Offer';
			$schema['offers']['price'] = '0';

			$schema['offers']['price'] = isset( $data['price'] ) ? wp_strip_all_tags( (string) $data['price'] ) : null;

			$schema['offers']['priceCurrency'] = isset( $data['currency'] ) ? wp_strip_all_tags( (string) $data['currency'] ) : null;

			return apply_filters( 'wp_schema_pro_schema_software_application', $schema, $data, $post );
		}

	}
}
