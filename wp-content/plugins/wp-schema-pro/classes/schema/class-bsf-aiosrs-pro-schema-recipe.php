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
		 * @param  array $data Meta Data.
		 * @param  array $post Current Post Array.
		 * @return array
		 */
		public static function render( $data, $post ) {
			$schema = array();

			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = 'Recipe';

			$schema['name'] = ! empty( $data['name'] ) ? wp_strip_all_tags( (string) $data['name'] ) : null;

			if ( isset( $data['image'] ) && ! empty( $data['image'] ) ) {
				$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['image'] );
			}
			if ( isset( $data['reviewer-type'] ) && ! empty( $data['reviewer-type'] ) ) {
				$schema['author']['@type'] = wp_strip_all_tags( (string) $data['reviewer-type'] );
			} else {
				$schema['author']['@type'] = 'Person';
			}
			$schema['author']['name'] = ! empty( $data['author'] ) ? wp_strip_all_tags( (string) $data['author'] ) : null;

			$schema['description'] = ! empty( $data['description'] ) ? wp_strip_all_tags( (string) $data['description'] ) : null;

			$schema['prepTime'] = ! empty( $data['preperation-time'] ) ? wp_strip_all_tags( (string) $data['preperation-time'] ) : null;

			$schema['cookTime'] = ! empty( $data['cook-time'] ) ? wp_strip_all_tags( (string) $data['cook-time'] ) : null;
			if ( isset( $data['recipe-yield'] ) && ! empty( $data['recipe-yield'] ) ) {
				$schema['recipeYield'] = esc_html( $data['recipe-yield'] );
			}
			$schema['keywords'] = ! empty( $data['recipe-keywords'] ) ? wp_strip_all_tags( (string) $data['recipe-keywords'] ) : null;

			$schema['recipeCategory'] = ! empty( $data['recipe-category'] ) ? wp_strip_all_tags( (string) $data['recipe-category'] ) : null;

			$schema['recipeCuisine'] = ! empty( $data['recipe-cuisine'] ) ? wp_strip_all_tags( (string) $data['recipe-cuisine'] ) : null;

			if ( ( isset( $data['rating'] ) && ! empty( $data['rating'] ) ) ||
				( isset( $data['review-count'] ) && ! empty( $data['review-count'] ) ) ) {

				$schema['aggregateRating']['@type'] = 'AggregateRating';

				$schema['aggregateRating']['ratingValue'] = ! empty( $data['rating'] ) ? wp_strip_all_tags( (string) $data['rating'] ) : null;
				$schema['aggregateRating']['reviewCount'] = ! empty( $data['review-count'] ) ? wp_strip_all_tags( (string) $data['review-count'] ) : null;
			}

			if ( isset( $data['nutrition'] ) && ! empty( $data['nutrition'] ) ) {
				$schema['nutrition']['@type']    = 'NutritionInformation';
				$schema['nutrition']['calories'] = wp_strip_all_tags( (string) $data['nutrition'] );
			}

			if ( isset( $data['ingredients'] ) && ! empty( $data['ingredients'] ) ) {
				$recipe_ingredients = explode( ',', $data['ingredients'] );
				foreach ( $recipe_ingredients as $key => $value ) {
					$schema['recipeIngredient'][ $key ] = wp_strip_all_tags( (string) $value );
				}
			}

			if ( isset( $data['recipe-instructions'] ) && ! empty( $data['recipe-instructions'] ) ) {
				foreach ( $data['recipe-instructions'] as $key => $value ) {

					if ( isset( $value['steps'] ) && ! empty( $value['steps'] ) ) {

						$schema['recipeInstructions'][ $key ]['@type'] = 'HowToStep';
						$schema['recipeInstructions'][ $key ]['text']  = wp_strip_all_tags( (string) $value['steps'] );
						$schema['recipeInstructions'][ $key ]['name']  = ! empty( $value['name'] ) ? wp_strip_all_tags( (string) $value['name'] ) : null;
						$schema['recipeInstructions'][ $key ]['url']   = ! empty( $value['url'] ) ? wp_strip_all_tags( (string) $value['url'] ) : null;
						if ( isset( $value['image'] ) && ! empty( $value['image'] ) ) {
							$schema['recipeInstructions'][ $key ]['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $value['image'], 'URL' );
						}
					}
				}
			}

			if ( isset( $data['recipe-video'] ) && ! empty( $data['recipe-video'] ) ) {

				foreach ( $data['recipe-video'] as $key => $value ) {
					if ( isset( $value['video-name'] ) && ! empty( $value['video-name'] ) ) {
						$schema['video'][ $key ]['@type']       = 'VideoObject';
						$schema['video'][ $key ]['name']        = wp_strip_all_tags( (string) $value['video-name'] );
						$schema['video'][ $key ]['description'] = ! empty( $value['video-desc'] ) ? wp_strip_all_tags( (string) $value['video-desc'] ) : null;
						if ( isset( $value['video-image'] ) && ! empty( $value['video-image'] ) ) {
							$schema['video'][ $key ]['thumbnailUrl'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $value['video-image'], 'URL' );
						}
						if ( isset( $value['recipe-video-content-url'] ) && ! empty( $value['recipe-video-content-url'] ) ) {
							$schema['video'][ $key ]['contentUrl'] = esc_url( $value['recipe-video-content-url'] );
						}
						if ( isset( $value['recipe-video-embed-url'] ) && ! empty( $value['recipe-video-embed-url'] ) ) {
							$schema['video'][ $key ]['embedUrl'] = esc_url( $value['recipe-video-embed-url'] );
						}
						$schema['video'][ $key ]['duration']         = ! empty( $value['recipe-video-duration'] ) ? wp_strip_all_tags( (string) $value['recipe-video-duration'] ) : null;
						$schema['video'][ $key ]['uploadDate']       = ! empty( $value['recipe-video-upload-date'] ) ? wp_strip_all_tags( (string) $value['recipe-video-upload-date'] ) : null;
						$schema['video'][ $key ]['interactionCount'] = ! empty( $value['recipe-video-interaction-count'] ) ? wp_strip_all_tags( (string) $value['recipe-video-interaction-count'] ) : null;
						if ( isset( $value['recipe-video-expires-date'] ) && ! empty( $value['recipe-video-expires-date'] ) && is_string( $value['recipe-video-expires-date'] ) ) {
							$schema['video'][ $key ]['expires'] = wp_strip_all_tags( $value['recipe-video-expires-date'] );
						}
					}
				}
			}

			return apply_filters( 'wp_schema_pro_schema_recipe', $schema, $data, $post );
		}

	}
}
