<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Article' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema_Article {

		/**
		 * Render Schema.
		 *
		 * @param  array<string, mixed> $data Meta Data.
		 * @param  array<string, mixed> $post Current Post Array.
		 * @return array<string, mixed>
		 */
		public static function render( array $data, array $post ): array {
			$schema           = array();
			$general_settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-general-settings'];

			$schema['@context'] = 'https://schema.org';
			if ( isset( $data['schema-type'] ) && ! empty( $data['schema-type'] ) ) {
				$schema['@type'] = $data['schema-type'];
			}

			if ( isset( $data['main-entity'] ) && ! empty( $data['main-entity'] ) ) {
				$schema['mainEntityOfPage']['@type'] = 'WebPage';
				$schema['mainEntityOfPage']['@id']   = esc_url( $data['main-entity'] );
			}

			$schema['headline'] = ! empty( $data['name'] ) ? wp_strip_all_tags( (string) $data['name'] ) : null;

			if ( isset( $data['image'] ) && ! empty( $data['image'] ) && is_array( $data['image'] ) ) {
				$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['image'] );
			}

			$schema['datePublished'] = ! empty( $data['published-date'] ) ? wp_strip_all_tags( (string) $data['published-date'] ) : null;

			$schema['dateModified'] = ! empty( $data['modified-date'] ) ? wp_strip_all_tags( (string) $data['modified-date'] ) : null;

			if ( ! empty( $data['author'] ) ) {
				$schema['author']['@type'] = 'Person';
				$schema['author']['name']  = wp_strip_all_tags( (string) $data['author'] );
				$schema['author']['url']   = ! empty( $data['author-url'] ) ? wp_strip_all_tags( (string) $data['author-url'] ) : null;
			}

			if ( ! empty( $data['orgnization-name'] ) ) {
				$schema['publisher']['@type'] = 'Organization';
				$schema['publisher']['name']  = wp_strip_all_tags( (string) $data['orgnization-name'] );
			}

			if ( isset( $data['site-logo'] ) && ! empty( $data['site-logo'] ) ) {
				$schema['publisher']['@type'] = 'Organization';
				$schema['publisher']['logo']  = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( (array) $data['site-logo'], 'ImageObject2' );
			} else {
				$logo_id = get_post_thumbnail_id( $post['ID'] );
				if ( isset( $general_settings['site-logo'] ) && 'custom' === $general_settings['site-logo'] ) {
					$logo_id = isset( $general_settings['site-logo-custom'] ) ? $general_settings['site-logo-custom'] : '';
				}
				if ( $logo_id ) {
					// Add logo image size.
					add_filter( 'intermediate_image_sizes_advanced', 'BSF_AIOSRS_Pro_Schema_Template::logo_image_sizes' );
					$logo_image = wp_get_attachment_image_src( (int) $logo_id, 'aiosrs-logo-size' );
					if ( isset( $logo_image[3] ) && 1 !== $logo_image[3] ) {
						BSF_AIOSRS_Pro_Schema_Template::generate_logo_by_width( (int) $logo_id );
						$logo_image = wp_get_attachment_image_src( (int) $logo_id, 'aiosrs-logo-size' );
					}
					// Remove logo image size.
					remove_filter( 'intermediate_image_sizes_advanced', 'BSF_AIOSRS_Pro_Schema_Template::logo_image_sizes' );
					$schema['publisher']['@type'] = 'Organization';
					$schema['publisher']['logo']  = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( (array) $logo_image, 'ImageObject' );
				}
			}

			$schema['description'] = ! empty( $data['description'] ) ? wp_strip_all_tags( (string) $data['description'] ) : null;

			return apply_filters( 'wp_schema_pro_schema_article', $schema, $data, $post );
		}

	}
}
