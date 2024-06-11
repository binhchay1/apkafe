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

			// Initialize 'shippingDetails' array
			$shipping_details = array();

			// Initialize 'deliveryTime' array
			$delivery_time = array();

			// Modify the section where 'shippingDetails' are added to the schema
			if ( ! empty( $data['shippingDetails'] ) ) {
				// Iterate over each set of shipping details
				foreach ( $data['shippingDetails'] as $shipping_detail ) {
					// Initialize shipping detail array for this set
					$shipping = array();

					// Add handlingTimeMinValue if available
					if ( ! empty( $shipping_detail['handlingTimeMinValue'] ) && $shipping_detail['handlingTimeMinValue'] !== 'none' ) {
						// Initialize 'deliveryTime' array
						$delivery_time = array();

						// Add handlingTimeMinValue to 'deliveryTime' array
						$delivery_time['@type']                    = 'ShippingDeliveryTime';
						$delivery_time['handlingTime']['minValue'] = wp_strip_all_tags( (string) $shipping_detail['handlingTimeMinValue'] );


						// Push the 'deliveryTime' array to 'shipping' array
						$shipping['deliveryTime'] = $delivery_time;
					}

					if ( ! empty( $shipping_detail['unitCode'] ) && $shipping_detail['unitCode'] !== 'none' ) {
						$delivery_time['handlingTime']['unitCode'] = wp_strip_all_tags( (string) $shipping_detail['unitCode'] );

						$delivery_time['transitTime']['unitCode'] = wp_strip_all_tags( (string) $shipping_detail['unitCode'] );
					}                    

						// Add shipping destination
					if ( ! empty( $shipping_detail['shippingDestination'] ) && $shipping_detail['shippingDestination'] !== 'none' ) {
						// Initialize shipping destination array
						$shipping_destination = array();
	
						// Add address country to shipping destination
						$shipping_destination['@type']          = 'DefinedRegion';
						$shipping_destination['addressCountry'] = $shipping_detail['shippingDestination']; // Assign selected country directly
	
						$shipping['shippingDestination'] = $shipping_destination;
					}

						// Add shipping rate
					if ( ! empty( $shipping_detail['shippingRate'] ) ) {
						// Initialize shipping rate array
						$shipping_rate = array();

						// Add value and currency to shipping rate
						$shipping_rate['@type']    = 'MonetaryAmount';
						$shipping_rate['value']    = $shipping_detail['shippingRate'];
						$shipping_rate['currency'] = ! empty( $shipping_detail['shippingCurrency'] ) ? $shipping_detail['shippingCurrency'] : 'USD'; // Default to USD if currency not provided

						$shipping['shippingRate'] = $shipping_rate;
					}

					// Add handlingTimeMaxValue if available
					if ( ! empty( $shipping_detail['handlingTimeMaxValue'] ) && $shipping_detail['handlingTimeMaxValue'] !== 'none' ) {
						// Initialize 'deliveryTime' array if not already initialized
						if ( empty( $delivery_time ) ) {
							$delivery_time = array();
						}

						// Add handlingTimeMaxValue to 'deliveryTime' array
						$delivery_time['handlingTime']['maxValue'] = wp_strip_all_tags( (string) $shipping_detail['handlingTimeMaxValue'] );

						// Push the 'deliveryTime' array to 'shipping' array
						$shipping['deliveryTime'] = $delivery_time;
					}

					// Add transitTimeMinValue if available
					if ( ! empty( $shipping_detail['transitTimeMinValue'] ) && $shipping_detail['transitTimeMinValue'] !== 'none' ) {
						// Initialize 'deliveryTime' array if not already initialized
						if ( empty( $delivery_time ) ) {
							$delivery_time = array();
						}

						// Add transitTimeMinValue to 'deliveryTime' array
						$delivery_time['transitTime']['minValue'] = wp_strip_all_tags( (string) $shipping_detail['transitTimeMinValue'] );
						

						// Push the 'deliveryTime' array to 'shipping' array
						$shipping['deliveryTime'] = $delivery_time;
					}

					// Add transitTimeMaxValue if available
					if ( ! empty( $shipping_detail['transitTimeMaxValue'] ) && $shipping_detail['transitTimeMaxValue'] !== 'none' ) {
						// Initialize 'deliveryTime' array if not already initialized
						if ( empty( $delivery_time ) ) {
							$delivery_time = array();
						}

						// Add transitTimeMaxValue to 'deliveryTime' array
						$delivery_time['transitTime']['maxValue'] = wp_strip_all_tags( (string) $shipping_detail['transitTimeMaxValue'] );

						// Push the 'deliveryTime' array to 'shipping' array
						$shipping['deliveryTime'] = $delivery_time;
					}

					// Push the shipping detail to 'shippingDetails' array
					$shipping_details[] = $shipping;
				}

				// Assign 'shippingDetails' array to 'offers'
				$schema['offers']['shippingDetails'] = $shipping_details;
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

			if ( isset($data['merchant-return-policy']) && !empty($data['merchant-return-policy'] ) ) {
				// Initialize hasMerchantReturnPolicy array
				$schema['offers']['hasMerchantReturnPolicy'] = array();
			
				foreach ($data['merchant-return-policy'] as $policy) {
					// Add each policy to hasMerchantReturnPolicy
					$return_policy = array();
			
					$return_policy['@type'] = 'MerchantReturnPolicy';
					$return_policy['applicableCountry'] = isset($policy['applicableCountry']) ? wp_strip_all_tags((string)$policy['applicableCountry']) : null;
					// Validate length of applicableCountry
					if ($return_policy['applicableCountry'] && strlen($return_policy['applicableCountry']) > 2) {
						// Truncate or handle the string appropriately if it exceeds the maximum length
						$return_policy['applicableCountry'] = substr($return_policy['applicableCountry'], 0, 2);
					}
					$return_policy['returnPolicyCategory'] = isset($policy['returnPolicyCategory']) ? esc_url($policy['returnPolicyCategory']) : null;
					$return_policy['merchantReturnDays'] = isset($policy['merchantReturnDays']) ? intval($policy['merchantReturnDays']) : null;
					$return_policy['returnFees'] = isset($policy['returnFees']) ? esc_url($policy['returnFees']) : null;
					$return_policy['returnMethod'] = isset($policy['returnMethod']) ? esc_url($policy['returnMethod']) : null;
			
					// Add returnShippingFeesAmount if returnFees is set to ReturnShippingFees
					if (
						isset($policy['returnFees']) &&
						$policy['returnFees'] === 'https://schema.org/ReturnShippingFees' &&
						isset($policy['returnShippingFeesAmount']) &&
						!empty($policy['returnShippingFeesAmount'])
					) {
						// Initialize returnShippingFeesAmount array
						$return_shipping_fees_amount = array();
			
						// Add value and currency to returnShippingFeesAmount
						$return_shipping_fees_amount['@type'] = 'MonetaryAmount';
						$return_shipping_fees_amount['value'] = floatval($policy['returnShippingFeesAmount']);
						$return_shipping_fees_amount['currency'] = !empty($policy['merchantCurrency']) ? $policy['merchantCurrency'] : null; // Default to USD if currency not provided
			
						$return_policy['returnShippingFeesAmount'] = $return_shipping_fees_amount;
					}
			
					// Push the policy to hasMerchantReturnPolicy array
					$schema['offers']['hasMerchantReturnPolicy'][] = $return_policy;
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
						$schema['review'][ $key ]['@type']                       = 'Review';
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
