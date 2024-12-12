<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.1.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Global_About_Page' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.1.0
	 */
	class BSF_AIOSRS_Pro_Schema_Global_About_Page {

		/**
		 * Render Schema.
		 *
		 * @param  array<string, mixed> $post Current Post Array.
		 * @return array<string, mixed>
		 */
		public static function render( array $post ): array {
			$schema = array();

			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = 'AboutPage';

			// Ensure post ID is an integer
			$post_id                             = isset( $post['ID'] ) && is_int( $post['ID'] ) ? $post['ID'] : 0;
			$permalink                           = get_permalink( $post_id );
			$schema['mainEntityOfPage']['@type'] = 'WebPage';
			$schema['mainEntityOfPage']['@id']   = esc_url( $permalink ? $permalink : '' );

			// Ensure post title is a string
			$post_title         = isset( $post['post_title'] ) && is_string( $post['post_title'] ) ? $post['post_title'] : '';
			$schema['headline'] = wp_strip_all_tags( $post_title );

			// Ensure post content is a string
			$post_content          = isset( $post['post_content'] ) && is_string( $post['post_content'] ) ? $post['post_content'] : '';
			$schema['description'] = BSF_AIOSRS_Pro_Schema_Template::strip_markup( $post_content );

			// Ensure image ID is an integer
			$image_id = get_post_thumbnail_id( $post_id );
			if ( $image_id && is_int( $image_id ) ) {
				$thumb_image = wp_get_attachment_image_src( $image_id, 'full' );
				if ( $thumb_image ) {
					$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $thumb_image );
				}
			}

			$settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-general-settings'];
			if ( 'person' === $settings['site-represent'] || 'personblog' === $settings['site-represent'] ) {
				$settings['site-represent'] = 'person';
			}
			if ( 'organization' === $settings['site-represent'] || 'Webshop' === $settings['site-represent'] || 'Smallbusiness' === $settings['site-represent'] || 'Otherbusiness' === $settings['site-represent'] ) {
				$settings['site-represent'] = 'organization';
			}
			$schema['publisher']['@type'] = ( isset( $settings['site-represent'] ) && ! empty( $settings['site-represent'] ) ) ? $settings['site-represent'] : 'Organization';
			if ( 'organization' === $settings['site-represent'] ) {
				$site_name                   = isset( $settings['site-name'] ) && is_string( $settings['site-name'] ) ? $settings['site-name'] : get_bloginfo( 'name' );
				$schema['publisher']['name'] = wp_strip_all_tags( $site_name );
			} else {
				$person_name                 = isset( $settings['person-name'] ) && is_string( $settings['person-name'] ) ? $settings['person-name'] : get_bloginfo( 'name' );
				$schema['publisher']['name'] = wp_strip_all_tags( $person_name );
			}

			$schema['publisher']['url'] = wp_strip_all_tags( get_bloginfo( 'url' ) );

			if ( 'organization' === $settings['site-represent'] ) {
				// Ensure logo ID is an integer
				$logo_id = get_theme_mod( 'custom_logo' );
				if ( isset( $settings['site-logo'] ) && 'custom' === $settings['site-logo'] ) {
					$logo_id = $settings['site-logo-custom'] && is_int( $settings['site-logo-custom'] ) ? $settings['site-logo-custom'] : 0;
				}
				if ( $logo_id && is_int( $logo_id ) ) {
					// Add logo image size.
					add_filter( 'intermediate_image_sizes_advanced', 'BSF_AIOSRS_Pro_Schema_Template::logo_image_sizes', 10, 1 );
					$logo_image = wp_get_attachment_image_src( $logo_id, 'aiosrs-logo-size' );
					if ( $logo_image && $logo_image[3] !== 1 ) {
						BSF_AIOSRS_Pro_Schema_Template::generate_logo_by_width( $logo_id );
						$logo_image = wp_get_attachment_image_src( $logo_id, 'aiosrs-logo-size' );
					}
					// Remove logo image size.
					remove_filter( 'intermediate_image_sizes_advanced', 'BSF_AIOSRS_Pro_Schema_Template::logo_image_sizes', 10 );
					if ( $logo_image ) {
						$schema['publisher']['logo'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $logo_image, 'ImageObject' );
					}
				}
			}

			return apply_filters( 'wp_schema_pro_global_schema_about_page', $schema, $post );
		}

	}
}
