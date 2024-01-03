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
		 * @param  array $data Meta Data.
		 * @param  array $post Current Post Array.
		 * @return array
		 */
		public static function render( $data, $post ) {
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

			if ( isset( $data['name'] ) && ! empty( $data['name'] ) ) {
				$schema['headline'] = esc_html( wp_strip_all_tags( $data['name'] ) );
			}

			if ( isset( $data['image'] ) && ! empty( $data['image'] ) ) {
				$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['image'] );
			}

			if ( isset( $data['published-date'] ) && ! empty( $data['published-date'] ) ) {
				$schema['datePublished'] = esc_html( wp_strip_all_tags( $data['published-date'] ) );
			}

			if ( isset( $data['modified-date'] ) && ! empty( $data['modified-date'] ) ) {
				$schema['dateModified'] = esc_html( wp_strip_all_tags( $data['modified-date'] ) );
			}

			if ( isset( $data['author'] ) && ! empty( $data['author'] ) ) {
				$schema['author']['@type'] = 'Person';
				$schema['author']['name']  = esc_html( wp_strip_all_tags( $data['author'] ) );
			}

			if ( isset( $data['orgnization-name'] ) && ! empty( $data['orgnization-name'] ) ) {
				$schema['publisher']['@type'] = 'Organization';
				$schema['publisher']['name']  = esc_html( wp_strip_all_tags( $data['orgnization-name'] ) );
			}

			if ( isset( $data['site-logo'] ) && ! empty( $data['site-logo'] ) ) {
				$schema['publisher']['@type'] = 'Organization';
				$schema['publisher']['logo']  = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['site-logo'], 'ImageObject2' );
			} else {
				$logo_id = get_post_thumbnail_id( $post['ID'] );
				if ( isset( $general_settings['site-logo'] ) && 'custom' === $general_settings['site-logo'] ) {
					$logo_id = isset( $general_settings['site-logo-custom'] ) ? $general_settings['site-logo-custom'] : '';
				}
				if ( $logo_id ) {
					// Add logo image size.
					add_filter( 'intermediate_image_sizes_advanced', 'BSF_AIOSRS_Pro_Schema_Template::logo_image_sizes', 10, 2 );
					$logo_image = wp_get_attachment_image_src( $logo_id, 'aiosrs-logo-size' );
					if ( isset( $logo_image[3] ) && 1 !== $logo_image[3] ) {
						BSF_AIOSRS_Pro_Schema_Template::generate_logo_by_width( $logo_id );
						$logo_image = wp_get_attachment_image_src( $logo_id, 'aiosrs-logo-size' );
					}
					// Remove logo image size.
					remove_filter( 'intermediate_image_sizes_advanced', 'BSF_AIOSRS_Pro_Schema_Template::logo_image_sizes', 10, 2 );
					$schema['publisher']['@type'] = 'Organization';
					$schema['publisher']['logo']  = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $logo_image, 'ImageObject' );
				}
			}

			if ( isset( $data['description'] ) && ! empty( $data['description'] ) ) {
				$schema['description'] = esc_html( wp_strip_all_tags( $data['description'] ) );
			}

			return apply_filters( 'wp_schema_pro_schema_article', $schema, $data, $post );
		}

	}
}
