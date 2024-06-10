<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.1.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Global_Contact_Page' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.1.0
	 */
	class BSF_AIOSRS_Pro_Schema_Global_Contact_Page {

		/**
		 * Render Schema.
		 *
		 * @param  array $post Current Post Array.
		 * @return array
		 */
		public static function render( $post ) {
			$schema = array();

			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = 'ContactPage';

			$schema['mainEntityOfPage']['@type'] = 'WebPage';
			$schema['mainEntityOfPage']['@id']   = esc_url( get_permalink( $post['ID'] ) );

			$schema['headline']    = wp_strip_all_tags( $post['post_title'] );
			$schema['description'] = BSF_AIOSRS_Pro_Schema_Template::strip_markup( $post['post_content'] );

			$image_id = get_post_thumbnail_id( $post['ID'] );
			if ( ! empty( $image_id ) ) {
				$thumb_image     = wp_get_attachment_image_src( $image_id, 'full' );
				$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $thumb_image );
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
				$schema['publisher']['name'] = ( isset( $settings['site-name'] ) && ! empty( $settings['site-name'] ) ) ? $settings['site-name'] : wp_strip_all_tags( get_bloginfo( 'name' ) );
			} else {
				$schema['publisher']['name'] = ( isset( $settings['person-name'] ) && ! empty( $settings['person-name'] ) ) ? $settings['person-name'] : wp_strip_all_tags( get_bloginfo( 'name' ) );
			}

			$schema['publisher']['url'] = wp_strip_all_tags( get_bloginfo( 'url' ) );

			if ( 'organization' === $settings['site-represent'] ) {
				$logo_id = get_theme_mod( 'custom_logo' );
				if ( isset( $settings['site-logo'] ) && 'custom' === $settings['site-logo'] ) {
					$logo_id = isset( $settings['site-logo-custom'] ) ? $settings['site-logo-custom'] : '';
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
					$schema['publisher']['logo'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $logo_image, 'ImageObject' );
				}
			}

			return apply_filters( 'wp_schema_pro_global_schema_contact_page', $schema, $post );
		}

	}
}
