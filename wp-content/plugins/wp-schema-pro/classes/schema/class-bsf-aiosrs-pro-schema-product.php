<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Product' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema_Product {

		/**
		 * Render Schema.
		 *
		 * @param  array $data Meta Data.
		 * @param  array $post Current Post Array.
		 * @return array
		 */
		public static function render( $data, $post ) {
			$schema             = array();
			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = 'Product';
			$schema['name']     = ! empty( $data['name'] ) ? wp_strip_all_tags( (string) $data['name'] ) : null;

			if ( isset( $data['image'] ) && ! empty( $data['image'] ) ) {
				$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['image'] );
			}

			$schema['description'] = ! empty( $data['description'] ) ? wp_strip_all_tags( (string) $data['description'] ) : null;

			$schema['sku'] = ! empty( $data['sku'] ) ? wp_strip_all_tags( (string) $data['sku'] ) : null;
			$schema['mpn'] = ! empty( $data['mpn'] ) ? wp_strip_all_tags( (string) $data['mpn'] ) : null;
			if ( ! empty( $data['brand-name'] ) ) {
				$schema['brand']['@type'] = 'Brand';
				$schema['brand']['name']  = wp_strip_all_tags( (string) $data['brand-name'] );
			}

			if ( ( isset( $data['rating'] ) && ! empty( $data['rating'] ) ) ||
				( isset( $data['review-count'] ) && ! empty( $data['review-count'] ) ) ) {

				$schema['aggregateRating']['@type'] = 'AggregateRating';

				$schema['aggregateRating']['ratingValue'] = ! empty( $data['rating'] ) ? wp_strip_all_tags( (string) $data['rating'] ) : null;
				$schema['aggregateRating']['reviewCount'] = ! empty( $data['review-count'] ) ? wp_strip_all_tags( (string) $data['review-count'] ) : null;
			}
			if ( apply_filters( 'wp_schema_pro_remove_product_offers', true ) ) {
				$schema['offers']['@type']           = 'Offer';
				$schema['offers']['price']           = '0';
				$schema['offers']['price']           = ! empty( $data['price'] ) ? wp_strip_all_tags( (string) $data['price'] ) : null;
				$schema['offers']['priceValidUntil'] = ! empty( $data['price-valid-until'] ) ? wp_strip_all_tags( (string) $data['price-valid-until'] ) : null;

				if ( isset( $data['url'] ) && ! empty( $data['url'] ) ) {
					$schema['offers']['url'] = esc_url( $data['url'] );
				}

				if ( ( isset( $data['currency'] ) && ! empty( $data['currency'] ) ) ||
					( isset( $data['avail'] ) && ! empty( $data['avail'] ) ) ) {

					$schema['offers']['priceCurrency'] = ! empty( $data['currency'] ) ? wp_strip_all_tags( (string) $data['currency'] ) : null;
					$schema['offers']['availability']  = ! empty( $data['avail'] ) ? wp_strip_all_tags( (string) $data['avail'] ) : null;
				}
			}

			if ( apply_filters( 'wp_schema_pro_remove_product_reviews', true ) && isset( $data['product-review'] ) && ! empty( $data['product-review'] ) ) {
				foreach ( $data['product-review'] as $key => $value ) {
					if ( ( isset( $value['reviewer-name'] ) && ! empty( $value['reviewer-name'] ) ) && ( isset( $value['product-rating'] ) && ! empty( $value['product-rating'] ) ) ) {
						$schema['review'][ $key ]['@type']          = 'Review';
						$schema['review'][ $key ]['author']['name'] = wp_strip_all_tags( (string) $value['reviewer-name'] );
						if ( isset( $value['reviewer-type'] ) && ! empty( $value['reviewer-type'] ) ) {
							$schema['review'][ $key ]['author']['@type'] = wp_strip_all_tags( (string) $value['reviewer-type'] );
						} else {
							$schema['review'][ $key ]['author']['@type'] = 'Person';
						}

						if ( isset( $value['product-rating'] ) && ! empty( $value['product-rating'] ) ) {
							$schema['review'][ $key ]['reviewRating']['@type']       = 'Rating';
							$schema['review'][ $key ]['reviewRating']['ratingValue'] = wp_strip_all_tags( (string) $value['product-rating'] );
						}

						$schema['review'][ $key ]['reviewBody'] = ! empty( $value['review-body'] ) ? wp_strip_all_tags( (string) $value['review-body'] ) : null;
					}
				}
			}

			// Fetch woocommerce review.
			if ( defined( 'WC_VERSION' ) && apply_filters( 'wp_schema_pro_add_woocommerce_review', false ) ) {
					$comments = get_comments(
						array(
							'number'      => 5,
							'post_id'     => $post['ID'],
							'status'      => 'approve',
							'post_status' => 'publish',
							'post_type'   => 'product',
							'parent'      => 0,
							'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
								array(
									'key'     => 'rating',
									'type'    => 'NUMERIC',
									'compare' => '>',
									'value'   => 0,
								),
							),
						)
					);

				if ( $comments ) {
					foreach ( $comments as $key => $comment ) {
						$schema['review'][ $key ]['@type']                           = 'Review';
							$schema['review'][ $key ]['reviewRating']['@type']       = 'Rating';
							$schema['review'][ $key ]['reviewRating']['ratingValue'] = get_comment_meta( $comment->comment_ID, 'rating', true );
							$schema['review'][ $key ]['author']['@type']             = 'Person';
							$schema['review'][ $key ]['author']['name']              = get_comment_author( $comment );
							$schema['review'][ $key ]['reviewBody']                  = get_comment_text( $comment );
					}
				}
			}

			return apply_filters( 'wp_schema_pro_schema_product', $schema, $data, $post );
		}
	}
}
