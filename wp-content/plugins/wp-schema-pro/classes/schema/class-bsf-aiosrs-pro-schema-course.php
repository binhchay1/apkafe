<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Course' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema_Course {

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
			$schema['@type']    = 'Course';

			if ( isset( $data['name'] ) && ! empty( $data['name'] ) ) {
				$schema['name'] = esc_html( wp_strip_all_tags( $data['name'] ) );
			}

			if ( isset( $data['course-code'] ) && ! empty( $data['course-code'] ) ) {
				$schema['courseCode'] = esc_html( wp_strip_all_tags( $data['course-code'] ) );
			}

			if ( isset( $data['description'] ) && ! empty( $data['description'] ) ) {
				$schema['description'] = esc_html( wp_strip_all_tags( $data['description'] ) );
			}

			if ( isset( $data['course-instance'] ) && ! empty( $data['course-instance'] ) ) {

				foreach ( $data['course-instance'] as $key => $value ) {

					if ( isset( $value['name'] ) && ! empty( $value['name'] ) ||
						isset( $value['description'] ) && ! empty( $value['description'] ) ||
						isset( $value['start-date'] ) && ! empty( $value['start-date'] ) ||
						isset( $value['location-address'] ) && ! empty( $value['location-address'] ) ) {

						$schema['hasCourseInstance'][ $key ]['@type'] = 'CourseInstance';
						if ( isset( $value['name'] ) && ! empty( $value['name'] ) ) {
							$schema['hasCourseInstance'][ $key ]['name'] = esc_html( wp_strip_all_tags( $value['name'] ) );
						}

						if ( isset( $value['description'] ) && ! empty( $value['description'] ) ) {
							$schema['hasCourseInstance'][ $key ]['description'] = esc_html( wp_strip_all_tags( $value['description'] ) );
						}

						if ( isset( $value['course-mode'] ) && ! empty( $value['course-mode'] ) ) {
							$schema['hasCourseInstance'][ $key ]['courseMode'] = esc_html( wp_strip_all_tags( $value['course-mode'] ) );
						}

						if ( isset( $value['start-date'] ) && ! empty( $value['start-date'] ) ) {
							$schema['hasCourseInstance'][ $key ]['startDate'] = esc_html( wp_strip_all_tags( $value['start-date'] ) );
						}

						if ( isset( $value['end-date'] ) && ! empty( $value['end-date'] ) ) {
							$schema['hasCourseInstance'][ $key ]['endDate'] = esc_html( wp_strip_all_tags( $value['end-date'] ) );
						}

						if ( isset( $value['image'] ) && ! empty( $value['image'] ) ) {
							$schema['hasCourseInstance'][ $key ]['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $value['image'] );
						}

						if ( isset( $value['location-name'] ) && ! empty( $value['location-name'] ) ) {
							$schema['hasCourseInstance'][ $key ]['location']['@type'] = 'Place';
							$schema['hasCourseInstance'][ $key ]['location']['name']  = esc_html( wp_strip_all_tags( $value['location-name'] ) );
						}

						if ( isset( $value['location-address'] ) && ! empty( $value['location-address'] ) ) {
							$schema['hasCourseInstance'][ $key ]['location']['@type']   = 'Place';
							$schema['hasCourseInstance'][ $key ]['location']['address'] = esc_html( wp_strip_all_tags( $value['location-address'] ) );
						}

						$schema['hasCourseInstance'][ $key ]['offers']['@type'] = 'Offer';
						$schema['hasCourseInstance'][ $key ]['offers']['price'] = '0';

						if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) {
							$schema['hasCourseInstance'][ $key ]['offers']['price'] = esc_html( wp_strip_all_tags( $value['price'] ) );
						}

						if ( isset( $value['currency'] ) && ! empty( $value['currency'] ) ) {
							$schema['hasCourseInstance'][ $key ]['offers']['priceCurrency'] = esc_html( wp_strip_all_tags( $value['currency'] ) );
						}

						if ( isset( $value['url'] ) && ! empty( $value['url'] ) ) {
							$schema['hasCourseInstance'][ $key ]['offers']['url'] = esc_html( wp_strip_all_tags( $value['url'] ) );
						}

						if ( isset( $value['valid-from'] ) && ! empty( $value['valid-from'] ) ) {
							$schema['hasCourseInstance'][ $key ]['offers']['validFrom'] = esc_html( wp_strip_all_tags( $value['valid-from'] ) );
						}

						if ( isset( $value['avail'] ) && ! empty( $value['avail'] ) ) {
							$schema['hasCourseInstance'][ $key ]['offers']['availability'] = esc_html( wp_strip_all_tags( $value['avail'] ) );
						}

						if ( isset( $value['performer'] ) && ! empty( $value['performer'] ) ) {
							$schema['hasCourseInstance'][ $key ]['performer']['@type'] = 'Person';
							$schema['hasCourseInstance'][ $key ]['performer']['name']  = esc_html( wp_strip_all_tags( $value['performer'] ) );
						}
					}
				}
			}

			if ( ( isset( $data['orgnization-name'] ) && ! empty( $data['orgnization-name'] ) ) ||
				( isset( $data['same-as'] ) && ! empty( $data['same-as'] ) ) ) {

				$schema['provider']['@type'] = 'Organization';

				if ( isset( $data['orgnization-name'] ) && ! empty( $data['orgnization-name'] ) ) {
					$schema['provider']['name'] = esc_html( wp_strip_all_tags( $data['orgnization-name'] ) );
				}
				if ( isset( $data['same-as'] ) && ! empty( $data['same-as'] ) ) {
					$schema['provider']['sameAs'] = esc_url( $data['same-as'] );
				}
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

			return apply_filters( 'wp_schema_pro_schema_course', $schema, $data, $post );
		}

	}
}
