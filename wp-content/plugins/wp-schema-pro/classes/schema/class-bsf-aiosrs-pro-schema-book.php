<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Book' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema_Book {

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
			$schema['@type']    = 'Book';

			$schema['name'] = ! empty( $data['name'] ) ? wp_strip_all_tags( (string) $data['name'] ) : null;
			if ( isset( $data['image'] ) && ! empty( $data['image'] ) ) {
				$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['image'] );
			}

			if ( ! empty( $data['author'] ) ) {
				$schema['author']['@type'] = 'Person';
				$schema['author']['name']  = wp_strip_all_tags( (string) $data['author'] );
			}

			if ( isset( $data['url'] ) && ! empty( $data['url'] ) ) {
				$schema['url'] = esc_url( $data['url'] );
			}

			if ( isset( $data['same-as'] ) && ! empty( $data['same-as'] ) ) {
				$schema['sameAs'] = esc_url( $data['same-as'] );
			}

			if ( isset( $data['work-example'] ) && ! empty( $data['work-example'] ) ) {
				foreach ( $data['work-example'] as $key => $value ) {

					$schema['workExample'][ $key ]['@type'] = 'Book';
					$schema['workExample'][ $key ]['isbn']  = ! empty( $value['serial-number'] ) ? wp_strip_all_tags( (string) $value['serial-number'] ) : null;

					$schema['workExample'][ $key ]['bookEdition'] = ! empty( $value['book-edition'] ) ? wp_strip_all_tags( (string) $value['book-edition'] ) : null;

					$schema['workExample'][ $key ]['bookFormat'] = ! empty( $value['book-format'] ) ? 'https://schema.org/' . wp_strip_all_tags( (string) $value['book-format'] ) : null;

					$schema['workExample'][ $key ]['potentialAction']['@type']           = 'ReadAction';
					$schema['workExample'][ $key ]['potentialAction']['target']['@type'] = 'EntryPoint';
					$action_platform = explode( ',', $value['action-platform'] );
					$action_platform = array_map( 'trim', $action_platform );
					$schema['workExample'][ $key ]['potentialAction']['target']['urlTemplate']    = $value['url-template'];
					$schema['workExample'][ $key ]['potentialAction']['target']['actionPlatform'] = $action_platform;

					$schema['workExample'][ $key ]['potentialAction']['expectsAcceptanceOf']['@type'] = 'Offer';
					$schema['workExample'][ $key ]['potentialAction']['expectsAcceptanceOf']['price'] = '0';
					$schema['workExample'][ $key ]['potentialAction']['expectsAcceptanceOf']['price'] = ! empty( $value['price'] ) ? wp_strip_all_tags( (string) $value['price'] ) : null;

					$schema['workExample'][ $key ]['potentialAction']['expectsAcceptanceOf']['priceCurrency'] = ! empty( $value['currency'] ) ? wp_strip_all_tags( (string) $value['currency'] ) : null;
					$schema['workExample'][ $key ]['potentialAction']['expectsAcceptanceOf']['availability']  = ! empty( $value['avail'] ) ? wp_strip_all_tags( (string) $value['avail'] ) : null;

					if ( isset( $value['country'] ) && ! empty( $value['country'] ) ) {
						$expects_acceptance = explode( ',', $value['country'] );
						$expects_acceptance = array_map( 'trim', $expects_acceptance );

						$expects_acceptances = array();
						foreach ( $expects_acceptance as $index => $country_name ) {
							$expects_acceptances[ $index ]['@type'] = 'Country';
							$expects_acceptances[ $index ]['name']  = $country_name;
						}
						$schema['workExample'][ $key ]['potentialAction']['expectsAcceptanceOf']['eligibleRegion'] = $expects_acceptances;
					}
				}
			}

			return apply_filters( 'wp_schema_pro_schema_book', $schema, $data, $post );
		}

	}
}
