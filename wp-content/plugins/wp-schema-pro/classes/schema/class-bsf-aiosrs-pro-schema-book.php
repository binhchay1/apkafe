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
		 * @param  array $data Meta Data.
		 * @param  array $post Current Post Array.
		 * @return array
		 */
		public static function render( $data, $post ) {
			$schema = array();

			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = 'Book';

			if ( isset( $data['name'] ) && ! empty( $data['name'] ) ) {
				$schema['name'] = esc_html( wp_strip_all_tags( $data['name'] ) );
			}
			if ( isset( $data['image'] ) && ! empty( $data['image'] ) ) {
				$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['image'] );
			}

			if ( isset( $data['author'] ) && ! empty( $data['author'] ) ) {
				$schema['author']['@type'] = 'Person';
				$schema['author']['name']  = esc_html( wp_strip_all_tags( $data['author'] ) );
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
					if ( isset( $value['serial-number'] ) && ! empty( $value['serial-number'] ) ) {
						$schema['workExample'][ $key ]['isbn'] = esc_html( wp_strip_all_tags( $value['serial-number'] ) );
					}

					if ( isset( $value['book-edition'] ) && ! empty( $value['book-edition'] ) ) {
						$schema['workExample'][ $key ]['bookEdition'] = esc_html( wp_strip_all_tags( $value['book-edition'] ) );
					}

					if ( isset( $value['book-format'] ) && ! empty( $value['book-format'] ) ) {
						$schema['workExample'][ $key ]['bookFormat'] = 'https://schema.org/' . esc_html( wp_strip_all_tags( $value['book-format'] ) );
					}

					$schema['workExample'][ $key ]['potentialAction']['@type']           = 'ReadAction';
					$schema['workExample'][ $key ]['potentialAction']['target']['@type'] = 'EntryPoint';
					$action_platform = explode( ',', $value['action-platform'] );
					$action_platform = array_map( 'trim', $action_platform );
					$schema['workExample'][ $key ]['potentialAction']['target']['urlTemplate']    = $value['url-template'];
					$schema['workExample'][ $key ]['potentialAction']['target']['actionPlatform'] = $action_platform;

					$schema['workExample'][ $key ]['potentialAction']['expectsAcceptanceOf']['@type'] = 'Offer';
					$schema['workExample'][ $key ]['potentialAction']['expectsAcceptanceOf']['price'] = '0';
					if ( isset( $value['price'] ) && ! empty( $value['price'] ) ) {
						$schema['workExample'][ $key ]['potentialAction']['expectsAcceptanceOf']['price'] = esc_html( wp_strip_all_tags( $value['price'] ) );
					}

					if ( ( isset( $value['currency'] ) && ! empty( $value['currency'] ) ) ||
						( isset( $value['avail'] ) && ! empty( $value['avail'] ) ) ) {

						if ( isset( $value['currency'] ) && ! empty( $value['currency'] ) ) {
							$schema['workExample'][ $key ]['potentialAction']['expectsAcceptanceOf']['priceCurrency'] = esc_html( wp_strip_all_tags( $value['currency'] ) );
						}
						if ( isset( $value['avail'] ) && ! empty( $value['avail'] ) ) {
							$schema['workExample'][ $key ]['potentialAction']['expectsAcceptanceOf']['availability'] = esc_html( wp_strip_all_tags( $value['avail'] ) );
						}
					}

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
