<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Image_License' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 2.7.0
	 */
	class BSF_AIOSRS_Pro_Schema_Image_License {

		/**
		 * Render Schema.
		 *
		 * @param  array $data Meta Data.
		 * @param  array $post Current Post Array.
		 * @return array
		 */
		public static function render( $data, $post ) {
			$schema = array();

			if ( isset( $data['image-license'] ) && ! empty( $data['image-license'] ) ) {
				foreach ( $data['image-license'] as $key => $value ) {
					$schema[ $key ]['@context'] = 'https://schema.org';
					$schema[ $key ]['@type']    = 'ImageObject';
					if ( isset( $value['content-url'] ) && ! empty( $value['content-url'] ) ) {
						$schema[ $key ]['contentUrl'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $value['content-url'], 'URL' );
					}
					if ( isset( $value['license'] ) && ! empty( $value['license'] ) ) {
						$schema[ $key ]['license'] = esc_url( $value['license'] );
					}
					if ( isset( $value['acquire-license-Page'] ) && ! empty( $value['acquire-license-Page'] ) ) {
						$schema[ $key ]['acquireLicensePage'] = esc_url( $value['acquire-license-Page'] );
					}
					if ( isset( $value['credit-text'] ) && ! empty( $value['credit-text'] ) ) {
						$schema[ $key ]['creditText'] = esc_url( $value['credit-text'] );
					}
					if ( ! empty( $data['creator-type'] ) ) {
						$schema['creator']['@type'] = wp_strip_all_tags( (string) $data['creator-type'] );
					} else {
						$schema[ $key ]['creator']['@type'] = 'Person';
					}
					if ( isset( $value['creator'] ) && ! empty( $value['creator'] ) ) {
						$schema[ $key ]['creator']['name'] = esc_url( $value['creator'] );
					}
					if ( isset( $value['copy-right-notice'] ) && ! empty( $value['copy-right-notice'] ) ) {
						$schema[ $key ]['copyrightNotice'] = esc_url( $value['copy-right-notice'] );
					}
				}
			}

			return apply_filters( 'wp_schema_pro_schema_image_license', $schema, $data, $post );
		}

	}
}
