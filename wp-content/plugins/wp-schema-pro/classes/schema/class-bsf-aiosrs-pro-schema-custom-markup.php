<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 2.5.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Custom_Markup' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 2.5.0
	 */
	class BSF_AIOSRS_Pro_Schema_Custom_Markup {

		/**
		 * Render Schema.
		 *
		 * @param  array<string, mixed> $data Meta Data.
		 * @param  array<string, mixed> $post Current Post Array.
		 * @return array<string, mixed>
		 */
		public static function render( array $data, array $post ): array {
			$schema = array();
			if ( isset( $data['custom-markup'] ) && ! empty( $data['custom-markup'] ) ) {
				$schema['custom-markup'] = $data['custom-markup'];
			}

			return apply_filters( 'wp_schema_pro_schema_article', $schema, $data, $post );
		}

	}
}
