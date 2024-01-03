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

			if ( isset( $data['name'] ) && ! empty( $data['name'] ) ) {
				$schema['name'] = esc_html( wp_strip_all_tags( $data['name'] ) );
			}

			if ( isset( $data['operating-system'] ) && ! empty( $data['operating-system'] ) ) {
				$schema['operatingSystem'] = esc_html( wp_strip_all_tags( $data['operating-system'] ) );
			}

			if ( isset( $data['category'] ) && ! empty( $data['category'] ) ) {
				$schema['applicationCategory'] = esc_html( wp_strip_all_tags( $data['category'] ) );
			}

			if ( isset( $data['image'] ) && ! empty( $data['image'] ) ) {
				$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['image'] );
			}

			if ( ( isset( $data['rating'] ) && ! empty( $data['rating'] ) ) ||
				( isset( $data['review-count'] ) && ! empty( $data['review-count'] ) ) ) {

				$schema['aggregateRating']['@type'] = 'AggregateRating';

				if ( isset( $data['rating'] ) && ! empty( $data['rating'] ) ) {
					$schema['aggregateRating']['ratingValue'] = wp_strip_all_tags( $data['rating'] );
				}
				if ( isset( $data['review-count'] ) && ! empty( $data['review-count'] ) ) {
					$schema['aggregateRating']['reviewCount'] = wp_strip_all_tags( $data['review-count'] );
				}
			}

			$schema['offers']['@type'] = 'Offer';
			$schema['offers']['price'] = '0';

			if ( isset( $data['price'] ) && ! empty( $data['price'] ) ) {
				$schema['offers']['price'] = esc_html( wp_strip_all_tags( $data['price'] ) );
			}

			if ( isset( $data['currency'] ) && ! empty( $data['currency'] ) ) {
				$schema['offers']['priceCurrency'] = esc_html( wp_strip_all_tags( $data['currency'] ) );
			}

			return apply_filters( 'wp_schema_pro_schema_software_application', $schema, $data, $post );
		}

	}
}
