<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Review' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema_Review {

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
			$schema['@type']    = 'Review';

			/* start Book schema fields */

			$data['schema-type'] = isset( $data['schema-type'] ) ? $data['schema-type'] : '';

			switch ( $data['schema-type'] ) {
				case 'bsf-aiosrs-book':
					$schema['itemReviewed']['@type'] = 'Book';
					$schema['itemReviewed']['name']  = ! empty( $data['bsf-aiosrs-book-name'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-book-name'] ) : null;
					if ( isset( $data['bsf-aiosrs-book-author'] ) && ! empty( $data['bsf-aiosrs-book-author'] ) ) {
						$schema['itemReviewed']['author']['@type']  = 'Person';
						$schema['itemReviewed']['author']['name']   = wp_strip_all_tags( (string) $data['bsf-aiosrs-book-author'] );
						$schema['itemReviewed']['author']['sameAs'] = wp_strip_all_tags( (string) $data['bsf-aiosrs-book-same-As'] );

					}
					$schema['itemReviewed']['isbn'] = ! empty( $data['bsf-aiosrs-book-serial-number'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-book-serial-number'] ) : null;
					$schema['description']          = ! empty( $data['bsf-aiosrs-book-description'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-book-description'] ) : null;
					$book_url                       = get_permalink( $post['ID'] );
					if ( isset( $book_url ) && ! empty( $book_url ) ) {
						$schema['url'] = esc_url( $book_url );
					}
					break;
				case 'bsf-aiosrs-course':
					$schema['itemReviewed']['@type']       = 'Course';
					$schema['itemReviewed']['name']        = ! empty( $data['bsf-aiosrs-course-name'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-course-name'] ) : null;
					$schema['itemReviewed']['description'] = ! empty( $data['bsf-aiosrs-course-description'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-course-description'] ) : null;

					if ( isset( $data['bsf-aiosrs-course-orgnization-name'] ) && ! empty( $data['bsf-aiosrs-course-orgnization-name'] ) ) {
						$schema['itemReviewed']['provider']['@type'] = 'Organization';
						$schema['itemReviewed']['provider']['name']  = wp_strip_all_tags( (string) $data['bsf-aiosrs-course-orgnization-name'] );
					}
					//phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase.
					// Initialize 'hasCourseInstance' array.
					$hasCourseInstance = array(); 
					$schema['itemReviewed']['hasCourseInstance']['course-instance'] = ! empty( $data['bsf-aiosrs-course-course-instance'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-course-course-instance'] ) : null;
					$schema['itemReviewed']['hasCourseInstance']['courseMode']      = ! empty( $data['bsf-aiosrs-course-courseMode'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-course-courseMode'] ) : null;
					$schema['itemReviewed']['hasCourseInstance']['courseWorkload']  = ! empty( $data['bsf-aiosrs-course-course-workload'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-course-course-workload'] ) : null;

					// Initialize 'offers' array.
					$offers                                       = array();
					$schema['itemReviewed']['offers']['price']    = ! empty( $data['bsf-aiosrs-course-price'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-course-price'] ) : null;
					$schema['itemReviewed']['offers']['currency'] = ! empty( $data['bsf-aiosrs-course-currency'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-course-currency'] ) : null;
					$schema['itemReviewed']['offers']['category'] = ! empty( $data['bsf-aiosrs-course-category'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-course-category'] ) : null;
					break;
										
				case 'bsf-aiosrs-event':
					$schema['itemReviewed']['@type']       = 'event';
					$schema['itemReviewed']['name']        = ! empty( $data['bsf-aiosrs-event-name'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-event-name'] ) : null;
					$schema['itemReviewed']['description'] = ! empty( $data['bsf-aiosrs-event-description'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-event-description'] ) : null;
					if ( isset( $data['bsf-aiosrs-event-image'] ) && ! empty( $data['bsf-aiosrs-event-image'] ) ) {
						$schema['itemReviewed']['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['bsf-aiosrs-event-image'] );
					}
					if ( isset( $data['bsf-aiosrs-event-start-date'] ) && ! empty( $data['bsf-aiosrs-event-start-date'] ) ) {
						if ( 'OfflineEventAttendanceMode' !== $data['bsf-aiosrs-event-event-attendance-mode'] ) {
							$start_date                          = gmdate( DATE_ISO8601, strtotime( $data['bsf-aiosrs-event-start-date'] ) );
							$schema['itemReviewed']['startDate'] = wp_strip_all_tags( (string) $start_date );
						} else {
							$schema['itemReviewed']['startDate'] = wp_strip_all_tags( (string) $data['bsf-aiosrs-event-start-date'] );
						}
					}
					$schema['itemReviewed']['endDate']     = ! empty( $data['bsf-aiosrs-event-end-date'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-event-end-date'] ) : null;
					$schema['itemReviewed']['eventStatus'] = ! empty( $data['bsf-aiosrs-event-event-status'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-event-event-status'] ) : null;

					$schema['itemReviewed']['eventAttendanceMode'] = ! empty( $data['bsf-aiosrs-event-event-attendance-mode'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-event-event-attendance-mode'] ) : null;

					$schema['itemReviewed']['previousStartDate'] = ! empty( $data['bsf-aiosrs-event-previous-date'] ) && 'EventRescheduled' === $data['bsf-aiosrs-event-event-status'] ? wp_strip_all_tags( (string) $data['bsf-aiosrs-event-previous-date'] ) : null;
					if ( isset( $data['bsf-aiosrs-event-online-location'] ) && ! empty( $data['bsf-aiosrs-event-online-location'] ) &&
					( 'OfflineEventAttendanceMode' !== $data['bsf-aiosrs-event-event-attendance-mode'] ) ||
					( 'MixedEventAttendanceMode' === $data['bsf-aiosrs-event-event-attendance-mode'] ) ) {
						$schema['itemReviewed']['location']['@type'] = 'VirtualLocation';
						$schema['itemReviewed']['location']['url']   = esc_url( $data['bsf-aiosrs-event-online-location'] );
					}
					if ( isset( $data['bsf-aiosrs-event-performer'] ) && ! empty( $data['bsf-aiosrs-event-performer'] ) ) {
						$schema['itemReviewed']['performer']['@type'] = 'Person';
						$schema['itemReviewed']['performer']['name']  = wp_strip_all_tags( (string) $data['bsf-aiosrs-event-performer'] );
					}
					if ( isset( $data['bsf-aiosrs-event-location'] ) && ! empty( $data['bsf-aiosrs-event-location'] ) && 'OnlineEventAttendanceMode' !== $data['bsf-aiosrs-event-event-attendance-mode'] ) {
						$schema['itemReviewed']['location']['@type'] = 'Place';
						$schema['itemReviewed']['location']['name']  = wp_strip_all_tags( (string) $data['bsf-aiosrs-event-location'] );
					}
					if ( ( ( isset( $data['bsf-aiosrs-event-location-street'] ) && ! empty( $data['bsf-aiosrs-event-location-street'] ) ) ||
					( isset( $data['bsf-aiosrs-event-location-locality'] ) && ! empty( $data['bsf-aiosrs-event-location-locality'] ) ) ||
					( isset( $data['bsf-aiosrs-event-location-postal'] ) && ! empty( $data['bsf-aiosrs-event-location-postal'] ) ) ||
					( isset( $data['bsf-aiosrs-event-location-region'] ) && ! empty( $data['bsf-aiosrs-event-location-region'] ) ) ||
					( isset( $data['bsf-aiosrs-event-location-country'] ) && ! empty( $data['bsf-aiosrs-event-location-country'] ) ) ) && ( 'OnlineEventAttendanceMode' !== $data['bsf-aiosrs-event-event-attendance-mode'] ) ) {
						$schema['itemReviewed']['location']['address']['@type'] = 'PostalAddress';

						$schema['itemReviewed']['location']['address']['streetAddress']   = ! empty( $data['bsf-aiosrs-event-location-street'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-event-location-street'] ) : null;
						$schema['itemReviewed']['location']['address']['addressLocality'] = ! empty( $data['bsf-aiosrs-event-location-locality'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-event-location-locality'] ) : null;
						$schema['itemReviewed']['location']['address']['postalCode']      = ! empty( $data['bsf-aiosrs-event-location-postal'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-event-location-postal'] ) : null;
						$schema['itemReviewed']['location']['address']['addressRegion']   = ! empty( $data['bsf-aiosrs-event-location-region'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-event-location-region'] ) : null;
						$schema['itemReviewed']['location']['address']['addressCountry']  = ! empty( $data['bsf-aiosrs-event-location-country'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-event-location-country'] ) : null;
					}
					$schema['itemReviewed']['offers']['@type'] = 'Offer';

					if ( ( isset( $data['bsf-aiosrs-event-avail'] ) && ! empty( $data['bsf-aiosrs-event-avail'] ) ) ||
						( isset( $data['bsf-aiosrs-event-currency'] ) && ! empty( $data['bsf-aiosrs-event-currency'] ) ) ||
						( isset( $data['bsf-aiosrs-event-valid-from'] ) && ! empty( $data['bsf-aiosrs-event-valid-from'] ) ) ||
						( isset( $data['bsf-aiosrs-event-ticket-buy-url'] ) && ! empty( $data['bsf-aiosrs-event-ticket-buy-url'] ) ) ) {
						if ( isset( $data['bsf-aiosrs-event-ticket-buy-url'] ) && ! empty( $data['bsf-aiosrs-event-ticket-buy-url'] ) ) {
							$schema['itemReviewed']['offers']['url'] = esc_url( $data['bsf-aiosrs-event-ticket-buy-url'] );
						}
						$schema['itemReviewed']['offers']['price']         = ! empty( $data['bsf-aiosrs-event-price'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-event-price'] ) : null;
						$schema['itemReviewed']['offers']['availability']  = ! empty( $data['bsf-aiosrs-event-avail'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-event-avail'] ) : null;
						$schema['itemReviewed']['offers']['priceCurrency'] = ! empty( $data['bsf-aiosrs-event-currency'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-event-currency'] ) : null;
						$schema['itemReviewed']['offers']['validFrom']     = ! empty( $data['bsf-aiosrs-event-valid-from'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-event-valid-from'] ) : null;
					}
					if ( ( isset( $data['bsf-aiosrs-event-event-organizer-name'] ) && ! empty( $data['bsf-aiosrs-event-event-organizer-name'] ) ) ||
						( isset( $data['bsf-aiosrs-event-event-organizer-url'] ) && ! empty( $data['bsf-aiosrs-event-event-organizer-url'] ) ) ) {

						$schema['itemReviewed']['organizer']['@type'] = 'Organization';

						$schema['itemReviewed']['organizer']['name'] = ! empty( $data['bsf-aiosrs-event-event-organizer-name'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-event-event-organizer-name'] ) : null;
						if ( isset( $data['bsf-aiosrs-event-event-organizer-url'] ) && ! empty( $data['bsf-aiosrs-event-event-organizer-url'] ) ) {
							$schema['itemReviewed']['organizer']['url'] = esc_url( $data['bsf-aiosrs-event-event-organizer-url'] );
						}
					}
					break;
				case 'bsf-aiosrs-local-business':
					$schema['itemReviewed']['@type'] = 'LocalBusiness';
					$schema['itemReviewed']['name']  = ! empty( $data['bsf-aiosrs-local-business-name'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-local-business-name'] ) : null;
					if ( isset( $data['bsf-aiosrs-local-business-image'] ) && ! empty( $data['bsf-aiosrs-local-business-image'] ) ) {

						$schema['itemReviewed']['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['bsf-aiosrs-local-business-image'] );
					}
					$schema['itemReviewed']['telephone'] = ! empty( $data['bsf-aiosrs-local-business-telephone'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-local-business-telephone'] ) : null;
					if ( ( isset( $data['bsf-aiosrs-local-business-location-street'] ) && ! empty( $data['bsf-aiosrs-local-business-location-street'] ) ) ||
						( isset( $data['bsf-aiosrs-local-business-location-locality'] ) && ! empty( $data['bsf-aiosrs-local-business-location-locality'] ) ) ||
						( isset( $data['bsf-aiosrs-local-business-location-postal'] ) && ! empty( $data['bsf-aiosrs-local-business-location-postal'] ) ) ||
						( isset( $data['bsf-aiosrs-local-business-location-region'] ) && ! empty( $data['bsf-aiosrs-local-business-location-region'] ) ) ||
						( isset( $data['bsf-aiosrs-local-business-location-country'] ) && ! empty( $data['bsf-aiosrs-local-business-location-country'] ) ) ) {

						$schema['itemReviewed']['address']['@type'] = 'PostalAddress';

						$schema['itemReviewed']['address']['streetAddress']   = ! empty( $data['bsf-aiosrs-local-business-location-street'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-local-business-location-street'] ) : null;
						$schema['itemReviewed']['address']['addressLocality'] = ! empty( $data['bsf-aiosrs-local-business-location-locality'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-local-business-location-locality'] ) : null;
						$schema['itemReviewed']['address']['postalCode']      = ! empty( $data['bsf-aiosrs-local-business-location-postal'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-local-business-location-postal'] ) : null;
						$schema['itemReviewed']['address']['addressRegion']   = ! empty( $data['bsf-aiosrs-local-business-location-region'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-local-business-location-region'] ) : null;
						$schema['itemReviewed']['address']['addressCountry']  = ! empty( $data['bsf-aiosrs-local-business-location-country'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-local-business-location-country'] ) : null;
					}
					$schema['itemReviewed']['priceRange'] = ! empty( $data['bsf-aiosrs-local-business-price-range'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-local-business-price-range'] ) : null;
					break;
				case 'bsf-aiosrs-recipe':
					$schema['itemReviewed']['@type'] = 'Recipe';
					$schema['itemReviewed']['name']  = ! empty( $data['bsf-aiosrs-recipe-name'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-recipe-name'] ) : null;
					if ( isset( $data['bsf-aiosrs-recipe-image'] ) && ! empty( $data['bsf-aiosrs-recipe-image'] ) ) {
						$schema['itemReviewed']['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['bsf-aiosrs-recipe-image'] );
					}
					if ( isset( $data['bsf-aiosrs-recipe-author'] ) && ! empty( $data['bsf-aiosrs-recipe-author'] ) ) {
						$schema['itemReviewed']['author']['@type'] = 'Person';
						$schema['itemReviewed']['author']['name']  = wp_strip_all_tags( (string) $data['bsf-aiosrs-recipe-author'] );
					}
					$schema['itemReviewed']['description']    = ! empty( $data['bsf-aiosrs-recipe-description'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-recipe-description'] ) : null;
					$schema['itemReviewed']['prepTime']       = ! empty( $data['bsf-aiosrs-recipe-preperation-time'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-recipe-preperation-time'] ) : null;
					$schema['itemReviewed']['cookTime']       = ! empty( $data['bsf-aiosrs-recipe-cook-time'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-recipe-cook-time'] ) : null;
					$schema['itemReviewed']['keywords']       = ! empty( $data['bsf-aiosrs-recipe-recipe-keywords'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-recipe-recipe-keywords'] ) : null;
					$schema['itemReviewed']['recipeCategory'] = ! empty( $data['bsf-aiosrs-recipe-recipe-category'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-recipe-recipe-category'] ) : null;
					$schema['itemReviewed']['recipeCuisine']  = ! empty( $data['bsf-aiosrs-recipe-recipe-cuisine'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-recipe-recipe-cuisine'] ) : null;
					if ( ( isset( $data['bsf-aiosrs-recipe-rating'] ) && ! empty( $data['bsf-aiosrs-recipe-rating'] ) ) ||
					( isset( $data['review-count'] ) && ! empty( $data['review-count'] ) ) ) {
						$schema['itemReviewed']['aggregateRating']['@type']       = 'AggregateRating';
						$schema['itemReviewed']['aggregateRating']['ratingValue'] = ! empty( $data['bsf-aiosrs-recipe-rating'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-recipe-rating'] ) : null;
						$schema['itemReviewed']['aggregateRating']['reviewCount'] = ! empty( $data['bsf-aiosrs-recipe-review-count'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-recipe-review-count'] ) : null;
					}
					if ( isset( $data['bsf-aiosrs-recipe-nutrition'] ) && ! empty( $data['bsf-aiosrs-recipe-nutrition'] ) ) {
						$schema['itemReviewed']['nutrition']['@type']    = 'NutritionInformation';
						$schema['itemReviewed']['nutrition']['calories'] = wp_strip_all_tags( (string) $data['bsf-aiosrs-recipe-nutrition'] );
					}
					if ( isset( $data['bsf-aiosrs-recipe-ingredients'] ) && ! empty( $data['bsf-aiosrs-recipe-ingredients'] ) ) {
						$recipe_ingredients = explode( ',', $data['bsf-aiosrs-recipe-ingredients'] );
						foreach ( $recipe_ingredients as $key => $value ) {
							$schema['itemReviewed']['recipeIngredient'][ $key ] = wp_strip_all_tags( (string) $value );
						}
					}
					if ( isset( $data['bsf-aiosrs-recipe-recipe-instructions'] ) && ! empty( $data['bsf-aiosrs-recipe-recipe-instructions'] ) ) {
								$recipe_instructions = explode( ',', $data['bsf-aiosrs-recipe-recipe-instructions'] );
						foreach ( $recipe_instructions as $key => $value ) {
							if ( isset( $value ) && ! empty( $value ) ) {
								$schema['itemReviewed']['recipeInstructions'][ $key ]['@type'] = 'HowToStep';
								$schema['itemReviewed']['recipeInstructions'][ $key ]['text']  = wp_strip_all_tags( (string) $value );
							}
						}
					}

					if ( isset( $data['bsf-aiosrs-recipe-video-name'] ) && ! empty( $data['bsf-aiosrs-recipe-video-name'] ) ) {
								$schema['itemReviewed']['video']['@type'] = 'VideoObject';
								$schema['itemReviewed']['video']['name']  = wp_strip_all_tags( (string) $data['bsf-aiosrs-recipe-video-name'] );
						$schema['itemReviewed']['video']['description']   = ! empty( $data['bsf-aiosrs-recipe-video-desc'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-recipe-video-desc'] ) : null;
						if ( isset( $data['bsf-aiosrs-recipe-video-image'] ) && ! empty( $data['bsf-aiosrs-recipe-video-image'] ) ) {
							$schema['itemReviewed']['video']['thumbnailUrl'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['bsf-aiosrs-recipe-video-image'] );
						}
						if ( isset( $data['bsf-aiosrs-recipe-recipe-video-content-url'] ) && ! empty( $data['bsf-aiosrs-recipe-recipe-video-content-url'] ) ) {
							$schema['itemReviewed']['video']['contentUrl'] = esc_url( $data['bsf-aiosrs-recipe-recipe-video-content-url'] );
						}
						if ( isset( $data['bsf-aiosrs-recipe-recipe-video-embed-url'] ) && ! empty( $data['bsf-aiosrs-recipe-recipe-video-embed-url'] ) ) {
							$schema['itemReviewed']['video']['embedUrl'] = esc_url( $data['bsf-aiosrs-recipe-recipe-video-embed-url'] );
						}
						$schema['itemReviewed']['video']['duration']         = ! empty( $data['bsf-aiosrs-recipe-recipe-video-duration'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-recipe-recipe-video-duration'] ) : null;
						$schema['itemReviewed']['video']['uploadDate']       = ! empty( $data['bsf-aiosrs-recipe-recipe-video-upload-date'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-recipe-recipe-video-upload-date'] ) : null;
						$schema['itemReviewed']['video']['interactionCount'] = ! empty( $data['bsf-aiosrs-recipe-recipe-video-interaction-count'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-recipe-recipe-video-interaction-count'] ) : null;
						$schema['itemReviewed']['video']['expires']          = ! empty( $data['bsf-aiosrs-recipe-recipe-video-expires-date'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-recipe-recipe-video-expires-date'] ) : null;
					}
					break;
				case 'bsf-aiosrs-software-application':
					$schema['itemReviewed']['@type'] = 'SoftwareApplication';
					$schema['itemReviewed']['name']  = ! empty( $data['bsf-aiosrs-software-application-name'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-software-application-name'] ) : null;

					$schema['itemReviewed']['operatingSystem'] = ! empty( $data['bsf-aiosrs-software-application-operating-system'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-software-application-operating-system'] ) : null;

					$schema['itemReviewed']['applicationCategory'] = ! empty( $data['bsf-aiosrs-software-application-category'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-software-application-category'] ) : null;

					if ( ( isset( $data['bsf-aiosrs-software-application-rating'] ) && ! empty( $data['bsf-aiosrs-software-application-rating'] ) ) ||
						( isset( $data['bsf-aiosrs-software-application-review-count'] ) && ! empty( $data['bsf-aiosrs-software-application-review-count'] ) ) ) {

						$schema['itemReviewed']['aggregateRating']['@type'] = 'AggregateRating';

						$schema['itemReviewed']['aggregateRating']['ratingValue'] = ! empty( $data['bsf-aiosrs-software-application-rating'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-software-application-rating'] ) : null;
						$schema['itemReviewed']['aggregateRating']['reviewCount'] = ! empty( $data['bsf-aiosrs-software-application-review-count'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-software-application-review-count'] ) : null;
					}
					if ( true === apply_filters( 'wp_schema_pro_remove_software_application_offers_review_type', true ) ) {
						$schema['itemReviewed']['offers']['@type'] = 'Offer';
						$schema['itemReviewed']['offers']['price'] = '0';

						$schema['itemReviewed']['offers']['price'] = ! empty( $data['bsf-aiosrs-software-application-price'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-software-application-price'] ) : null;

						$schema['itemReviewed']['offers']['priceCurrency'] = ! empty( $data['bsf-aiosrs-software-application-currency'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-software-application-currency'] ) : null;
					}
					break;
				case 'bsf-aiosrs-product':
					$schema['itemReviewed']['@type'] = 'product';
					$schema['itemReviewed']['name']  = ! empty( $data['bsf-aiosrs-product-name'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-product-name'] ) : null;
					if ( isset( $data['bsf-aiosrs-product-image'] ) && ! empty( $data['bsf-aiosrs-product-image'] ) ) {
						$schema['itemReviewed']['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['bsf-aiosrs-product-image'] );
					}

					$schema['itemReviewed']['description'] = ! empty( $data['bsf-aiosrs-product-description'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-product-description'] ) : null;

					$schema['itemReviewed']['sku'] = ! empty( $data['bsf-aiosrs-product-sku'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-product-sku'] ) : null;
					$schema['itemReviewed']['mpn'] = ! empty( $data['bsf-aiosrs-product-mpn'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-product-mpn'] ) : null;
					if ( isset( $data['bsf-aiosrs-product-brand-name'] ) && ! empty( $data['bsf-aiosrs-product-brand-name'] ) ) {
						$schema['itemReviewed']['brand']['@type'] = 'Organization';
						$schema['itemReviewed']['brand']['name']  = wp_strip_all_tags( (string) $data['bsf-aiosrs-product-brand-name'] );
					}

					if ( ( isset( $data['bsf-aiosrs-product-rating'] ) && ! empty( $data['bsf-aiosrs-product-rating'] ) ) ||
						( isset( $data['bsf-aiosrs-product-review-count'] ) && ! empty( $data['bsf-aiosrs-product-review-count'] ) ) ) {

						$schema['itemReviewed']['aggregateRating']['@type'] = 'AggregateRating';

						$schema['itemReviewed']['aggregateRating']['ratingValue'] = ! empty( $data['bsf-aiosrs-product-rating'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-product-rating'] ) : null;
						$schema['itemReviewed']['aggregateRating']['reviewCount'] = ! empty( $data['bsf-aiosrs-product-review-count'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-product-review-count'] ) : null;
					}
					if ( apply_filters( 'wp_schema_pro_remove_product_offers', true ) ) {
						$schema['itemReviewed']['offers']['@type']           = 'Offer';
						$schema['itemReviewed']['offers']['price']           = '0';
						$schema['itemReviewed']['offers']['price']           = ! empty( $data['bsf-aiosrs-product-price'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-product-price'] ) : null;
						$schema['itemReviewed']['offers']['priceValidUntil'] = ! empty( $data['bsf-aiosrs-product-price-valid-until'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-product-price-valid-until'] ) : null;

							$schema['itemReviewed']['offers']['url'] = get_permalink( $post['ID'] );

						if ( ( isset( $data['bsf-aiosrs-product-currency'] ) && ! empty( $data['bsf-aiosrs-product-currency'] ) ) ||
							( isset( $data['bsf-aiosrs-product-avail'] ) && ! empty( $data['bsf-aiosrs-product-avail'] ) ) ) {

							$schema['itemReviewed']['offers']['priceCurrency'] = ! empty( $data['bsf-aiosrs-product-currency'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-product-currency'] ) : null;
							$schema['itemReviewed']['offers']['availability']  = ! empty( $data['bsf-aiosrs-product-avail'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-product-avail'] ) : null;
						}
					}

					break;
				case 'bsf-aiosrs-movie':
					$schema['itemReviewed']['@type']  = 'Movie';
					$schema['itemReviewed']['name']   = ! empty( $data['bsf-aiosrs-movie-name'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-movie-name'] ) : null;
					$schema['itemReviewed']['sameAs'] = ! empty( $data['bsf-aiosrs-movie-same-As'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-movie-same-As'] ) : null;
					if ( isset( $data['bsf-aiosrs-movie-image'] ) && ! empty( $data['bsf-aiosrs-movie-image'] ) ) {
						$schema['itemReviewed']['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['bsf-aiosrs-movie-image'] );
					}
					$schema['itemReviewed']['dateCreated'] = ! empty( $data['bsf-aiosrs-movie-dateCreated'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-movie-dateCreated'] ) : null;
					if ( isset( $data['bsf-aiosrs-movie-director-name'] ) && ! empty( $data['bsf-aiosrs-movie-director-name'] ) ) {
						$schema['itemReviewed']['director']['@type'] = 'Person';
						$schema['itemReviewed']['director']['name']  = wp_strip_all_tags( (string) $data['bsf-aiosrs-movie-director-name'] );
					}
					$schema['description'] = ! empty( $data['bsf-aiosrs-movie-description'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-movie-description'] ) : null;

					break;
				case 'bsf-aiosrs-organization':
					$schema['itemReviewed']['@type'] = 'Organization';
					$schema['itemReviewed']['name']  = ! empty( $data['bsf-aiosrs-organization-name'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-organization-name'] ) : null;
					if ( ( isset( $data['bsf-aiosrs-organization-location-street'] ) && ! empty( $data['bsf-aiosrs-organization-location-street'] ) ) ||
						( isset( $data['bsf-aiosrs-organization-location-locality'] ) && ! empty( $data['bsf-aiosrs-organization-location-locality'] ) ) ||
						( isset( $data['bsf-aiosrs-organization-location-postal'] ) && ! empty( $data['bsf-aiosrs-organization-location-postal'] ) ) ||
						( isset( $data['bsf-aiosrs-organization-location-region'] ) && ! empty( $data['bsf-aiosrs-organization-location-region'] ) ) ||
						( isset( $data['bsf-aiosrs-organization-location-country'] ) && ! empty( $data['bsf-aiosrs-organization-location-country'] ) ) ) {

						$schema['itemReviewed']['address']['@type'] = 'PostalAddress';

						$schema['itemReviewed']['address']['streetAddress']   = ! empty( $data['bsf-aiosrs-organization-location-street'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-organization-location-street'] ) : null;
						$schema['itemReviewed']['address']['addressLocality'] = ! empty( $data['bsf-aiosrs-organization-location-locality'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-organization-location-locality'] ) : null;
						$schema['itemReviewed']['address']['postalCode']      = ! empty( $data['bsf-aiosrs-organization-location-postal'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-organization-location-postal'] ) : null;
						$schema['itemReviewed']['address']['addressRegion']   = ! empty( $data['bsf-aiosrs-organization-location-region'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-organization-location-region'] ) : null;
						$schema['itemReviewed']['address']['addressCountry']  = ! empty( $data['bsf-aiosrs-organization-location-country'] ) ? wp_strip_all_tags( (string) $data['bsf-aiosrs-organization-location-country'] ) : null;
					}
					break;
				default:
					break;
			}
			/*Review schema fields*/

			if ( isset( $data['rating'] ) && ! empty( $data['rating'] ) ) {
				$schema['reviewRating']['@type']       = 'Rating';
				$schema['reviewRating']['ratingValue'] = wp_strip_all_tags( (string) $data['rating'] );
			}
			$schema['reviewBody']      = ! empty( $data['review-body'] ) ? wp_strip_all_tags( (string) $data['review-body'] ) : null;
			$schema['datePublished']   = ! empty( $data['date'] ) ? wp_strip_all_tags( (string) $data['date'] ) : null;
			$schema['author']['@type'] = ! empty( $data['reviewer-type'] ) ? wp_strip_all_tags( (string) $data['reviewer-type'] ) : 'Person';
			if ( isset( $data['reviewer-name'] ) && ! empty( $data['reviewer-name'] ) ) {
				$schema['author']['name']   = wp_strip_all_tags( (string) $data['reviewer-name'] );
				$author_data                = get_userdata( $post['post_author'] );
					$author_name            = ( isset( $author_data->user_nicename ) ) ? $author_data->user_nicename : '';
					$author_url             = get_author_posts_url( $post['ID'] );
					$final_url              = $author_url . '' . $author_name;
					$is_available           = true;
				$schema['author']['sameAs'] = esc_url( $final_url );
			}
			if ( isset( $data['publisher-name'] ) && ! empty( $data['publisher-name'] ) ) {
				$schema['publisher']['@type']  = 'Organization';
				$schema['publisher']['name']   = wp_strip_all_tags( (string) $data['publisher-name'] );
				$prg_url_value                 = get_bloginfo( 'url' );
				$schema['publisher']['sameAs'] = esc_url( $prg_url_value );
			}

			return apply_filters( 'wp_schema_pro_schema_review', $schema, $data, $post );
		}

	}
}
