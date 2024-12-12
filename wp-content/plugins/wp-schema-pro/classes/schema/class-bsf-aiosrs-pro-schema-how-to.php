<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 2.1.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_How_To' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 2.1.0
	 */
	class BSF_AIOSRS_Pro_Schema_How_To {

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
			$schema['@type']    = 'HowTo';

			$schema['name'] = ! empty( $data['name'] ) ? wp_strip_all_tags( (string) $data['name'] ) : null;

			$schema['description'] = ! empty( $data['description'] ) ? wp_strip_all_tags( (string) $data['description'] ) : null;

			$schema['totalTime'] = ! empty( $data['total-time'] ) ? wp_strip_all_tags( (string) $data['total-time'] ) : null;

			if ( isset( $data['supply'] ) && is_array( $data['supply'] ) ) {

				foreach ( $data['supply'] as $key => $value ) {

					if ( isset( $value['name'] ) && ! empty( $value['name'] ) ) {

						$schema['supply'][ $key ]['@type'] = 'HowToSupply';

						$schema['supply'][ $key ]['name'] = ! empty( $value['name'] ) ? wp_strip_all_tags( (string) $value['name'] ) : null;
					}
				}
			}

			if ( isset( $data['tool'] ) && is_array( $data['tool'] ) ) {

				foreach ( $data['tool'] as $key => $value ) {

					if ( isset( $value['name'] ) && ! empty( $value['name'] ) ) {

						$schema['tool'][ $key ]['@type'] = 'HowToTool';

						$schema['tool'][ $key ]['name'] = ! empty( $value['name'] ) ? wp_strip_all_tags( (string) $value['name'] ) : null;
					}
				}
			}

			if ( isset( $data['steps'] ) && is_array( $data['steps'] ) ) {
				foreach ( $data['steps'] as $key => $value ) {
					$schema['step'][ $key ]['@type'] = 'HowToStep';
					if ( isset( $value['name'] ) && ! empty( $value['name'] ) ) {
						$schema['step'][ $key ]['name'] = $value['name'];
					}
					if ( isset( $value['url'] ) && ! empty( $value['url'] ) ) {
						$schema['step'][ $key ]['url'] = $value['url'];
					}
					if ( isset( $value['description'] ) && ! empty( $value['description'] ) ) {
						$schema['step'][ $key ]['itemListElement']['@type'] = 'HowToDirection';
						$schema['step'][ $key ]['itemListElement']['text']  = $value['description'];
					}
					$step_image = wp_get_attachment_image_src( $value['image'], 'full' );
					if ( isset( $value['image'] ) && ! empty( $value['image'] ) && false !== $step_image ) {
						$schema['step'][ $key ]['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $step_image, 'ImageObject' );
					}
				}
			}

			return apply_filters( 'wp_schema_pro_schema_how_to', $schema, $data, $post );
		}

	}
}
