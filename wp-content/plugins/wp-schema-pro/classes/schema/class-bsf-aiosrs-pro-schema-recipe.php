<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Recipe' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */

	class BSF_AIOSRS_Pro_Schema_Recipe {
		
		/**
		 * Render Schema.
		 *
		 * @param  array<string, mixed> $data Meta Data.
		 * @param  array<string, mixed> $post Current Post Array.
		 * @return array<string, mixed>
		 */
		public static function render( array $data, array $post ): array {
			// Get timezone string from WordPress settings.
			$timezone_string = get_option( 'timezone_string' );

			// Validate timezone string.
			
			if ( is_string( $timezone_string ) && ! empty( $timezone_string ) && in_array( $timezone_string, timezone_identifiers_list(), true ) ) {
				// WordPress calculates offsets from UTC.
				// phpcs:ignore WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set
				date_default_timezone_set( $timezone_string );
			} else {
				// If the timezone string is empty or invalid, set a fallback timezone.
				date_default_timezone_set( 'UTC' );
				$timezone_string = 'UTC'; // Ensure $timezone_string is set to a valid timezone for later use.
			}

			$schema = array();

			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = 'Recipe';

			$schema['name'] = isset( $data['name'] ) && is_string( $data['name'] ) ? wp_strip_all_tags( $data['name'] ) : null;

			if ( isset( $data['image'] ) && is_array( $data['image'] ) ) {
				$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['image'] );
			}

			if ( isset( $data['reviewer-type'] ) && is_string( $data['reviewer-type'] ) ) {
				$schema['author']['@type'] = wp_strip_all_tags( $data['reviewer-type'] );
			} else {
				$schema['author']['@type'] = 'Person';
			}

			$schema['author']['name'] = isset( $data['author'] ) && is_string( $data['author'] ) ? wp_strip_all_tags( $data['author'] ) : null;

			$schema['description'] = isset( $data['description'] ) && is_string( $data['description'] ) ? wp_strip_all_tags( $data['description'] ) : null;

			$schema['prepTime'] = isset( $data['preperation-time'] ) && is_string( $data['preperation-time'] ) ? wp_strip_all_tags( $data['preperation-time'] ) : null;

			$schema['cookTime'] = isset( $data['cook-time'] ) && is_string( $data['cook-time'] ) ? wp_strip_all_tags( $data['cook-time'] ) : null;

			if ( isset( $data['recipe-yield'] ) && is_string( $data['recipe-yield'] ) ) {
				$schema['recipeYield'] = esc_html( $data['recipe-yield'] );
			}

			$schema['keywords'] = isset( $data['recipe-keywords'] ) && is_string( $data['recipe-keywords'] ) ? wp_strip_all_tags( $data['recipe-keywords'] ) : null;

			$schema['recipeCategory'] = isset( $data['recipe-category'] ) && is_string( $data['recipe-category'] ) ? wp_strip_all_tags( $data['recipe-category'] ) : null;

			$schema['recipeCuisine'] = isset( $data['recipe-cuisine'] ) && is_string( $data['recipe-cuisine'] ) ? wp_strip_all_tags( $data['recipe-cuisine'] ) : null;

			if ( ( isset( $data['rating'] ) && is_string( $data['rating'] ) ) ||
				( isset( $data['review-count'] ) && is_string( $data['review-count'] ) ) ) {

				$schema['aggregateRating']['@type'] = 'AggregateRating';

				$schema['aggregateRating']['ratingValue'] = isset( $data['rating'] ) && is_string( $data['rating'] ) ? wp_strip_all_tags( $data['rating'] ) : null;
				$schema['aggregateRating']['reviewCount'] = isset( $data['review-count'] ) && is_string( $data['review-count'] ) ? wp_strip_all_tags( $data['review-count'] ) : null;
			}

			if ( isset( $data['nutrition'] ) && is_string( $data['nutrition'] ) ) {
				$schema['nutrition']['@type']    = 'NutritionInformation';
				$schema['nutrition']['calories'] = wp_strip_all_tags( $data['nutrition'] );
			}

			if ( isset( $data['ingredients'] ) && is_string( $data['ingredients'] ) ) {
				$recipe_ingredients = explode( ',', $data['ingredients'] );
				foreach ( $recipe_ingredients as $key => $value ) {
					$schema['recipeIngredient'][ $key ] = wp_strip_all_tags( $value );
				}
			}

			if ( isset( $data['recipe-instructions'] ) && is_array( $data['recipe-instructions'] ) ) {
				foreach ( $data['recipe-instructions'] as $key => $value ) {
					if ( isset( $value['steps'] ) && is_string( $value['steps'] ) ) {
						$schema['recipeInstructions'][ $key ]['@type'] = 'HowToStep';
						$schema['recipeInstructions'][ $key ]['text']  = wp_strip_all_tags( $value['steps'] );
						$schema['recipeInstructions'][ $key ]['name']  = isset( $value['name'] ) && is_string( $value['name'] ) ? wp_strip_all_tags( $value['name'] ) : null;
						$schema['recipeInstructions'][ $key ]['url']   = isset( $value['url'] ) && is_string( $value['url'] ) ? wp_strip_all_tags( $value['url'] ) : null;
						if ( isset( $value['image'] ) && is_array( $value['image'] ) ) {
							$schema['recipeInstructions'][ $key ]['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $value['image'], 'URL' );
						}
					}
				}
			}

			if ( isset( $data['recipe-video'] ) && is_array( $data['recipe-video'] ) ) {
				foreach ( $data['recipe-video'] as $key => $value ) {
					if ( isset( $value['video-name'] ) && is_string( $value['video-name'] ) ) {
						$schema['video'][ $key ]['@type']       = 'VideoObject';
						$schema['video'][ $key ]['name']        = wp_strip_all_tags( $value['video-name'] );
						$schema['video'][ $key ]['description'] = isset( $value['video-desc'] ) && is_string( $value['video-desc'] ) ? wp_strip_all_tags( $value['video-desc'] ) : null;
						if ( isset( $value['video-image'] ) && is_array( $value['video-image'] ) ) {
							$schema['video'][ $key ]['thumbnailUrl'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $value['video-image'], 'URL' );
						}
						if ( isset( $value['recipe-video-content-url'] ) && is_string( $value['recipe-video-content-url'] ) ) {
							$schema['video'][ $key ]['contentUrl'] = esc_url( $value['recipe-video-content-url'] );
						}
						if ( isset( $value['recipe-video-embed-url'] ) && is_string( $value['recipe-video-embed-url'] ) ) {
							$schema['video'][ $key ]['embedUrl'] = esc_url( $value['recipe-video-embed-url'] );
						}
						$schema['video'][ $key ]['duration'] = isset( $value['recipe-video-duration'] ) && is_string( $value['recipe-video-duration'] ) ? wp_strip_all_tags( $value['recipe-video-duration'] ) : null;

						// Convert uploadDate to DateTime object and set the timezone.
						if ( isset( $value['recipe-video-upload-date'] ) && is_string( $value['recipe-video-upload-date'] ) ) {
							$upload_date = new DateTime( $value['recipe-video-upload-date'] );
							$upload_date->setTimezone( new DateTimeZone( is_string( $timezone_string ) ? $timezone_string : 'UTC' ) );
							$schema['video'][ $key ]['uploadDate'] = $upload_date->format( 'c' );
						}

						// Use DateTime to handle timezone for 'expires'
						if ( isset( $value['recipe-video-expires-date'] ) && is_string( $value['recipe-video-expires-date'] ) ) {
							$expires_date = new DateTime( $value['recipe-video-expires-date'] );
							$expires_date->setTimezone( new DateTimeZone( is_string( $timezone_string ) ? $timezone_string : 'UTC' ) );
							$schema['video'][ $key ]['expires'] = $expires_date->format( 'c' );
						}

						$schema['video'][ $key ]['interactionCount'] = isset( $value['recipe-video-interaction-count'] ) && is_string( $value['recipe-video-interaction-count'] ) ? wp_strip_all_tags( $value['recipe-video-interaction-count'] ) : null;
					}
				}
			}

			return apply_filters( 'wp_schema_pro_schema_recipe', $schema, $data, $post );
		}
	}
}
