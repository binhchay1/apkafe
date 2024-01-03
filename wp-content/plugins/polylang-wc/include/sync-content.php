<?php

/**
 * Smart copy of WooCommerce blocks
 *
 * @since 1.2
 */
class PLLWC_Sync_Content {

	/**
	 * Constructor
	 * Setup filters
	 *
	 * @since 1.2
	 */
	public function __construct() {
		add_filter( 'pll_translate_blocks', array( $this, 'translate_blocks' ), 10, 2 );
	}

	/**
	 * Translate blocks
	 *
	 * @since 1.2
	 *
	 * @param array  $blocks An array of blocks arrays.
	 * @param string $lang   Target language.
	 * @return array
	 */
	public function translate_blocks( $blocks, $lang ) {
		foreach ( $blocks as $k => $block ) {
			switch ( $block['blockName'] ) {
				case 'woocommerce/handpicked-products':
					$data_store = PLLWC_Data_Store::load( 'product_language' );
					$products = array();

					foreach ( $block['attrs']['products'] as $id ) {
						$products[] = $data_store->get( $id, $lang );
					}

					$blocks[ $k ]['attrs']['products'] = $products;
					$blocks[ $k ]['innerContent'][0] = $blocks[ $k ]['innerHTML'] = preg_replace( '#ids="\d+(,\d+)*"#', 'ids="' . implode( ',', $products ) . '"', $block['innerHTML'] );
					break;

				case 'woocommerce/product-category':
				case 'woocommerce/product-best-sellers':
				case 'woocommerce/product-new':
				case 'woocommerce/product-top-rated':
				case 'woocommerce/product-on-sale':
					if ( ! empty( $block['attrs']['categories'] ) ) {
						$categories = array();

						foreach ( $block['attrs']['categories'] as $id ) {
							$categories[] = pll_get_term( $id, $lang );
						}

						$blocks[ $k ]['attrs']['categories'] = $categories;
						$blocks[ $k ]['innerContent'][0] = $blocks[ $k ]['innerHTML'] = preg_replace( '#category="\d+(,\d+)*"#', 'category="' . implode( ',', $categories ) . '"', $block['innerHTML'] );
					}
					break;

				case 'woocommerce/products-by-attribute':
					$terms = array();

					foreach ( $block['attrs']['attributes'] as $n => $attributes ) {
						$tr_id = pll_get_term( $attributes['id'], $lang );
						$blocks[ $k ]['attrs']['attributes'][ $n ]['id'] = $tr_id;
						$terms[] = $tr_id;
					}

					$blocks[ $k ]['innerContent'][0] = $blocks[ $k ]['innerHTML'] = preg_replace( '#terms="\d+(,\d+)*"#', 'terms="' . implode( ',', $terms ) . '"', $block['innerHTML'] );
					break;

				case 'woocommerce/featured-product':
					$data_store = PLLWC_Data_Store::load( 'product_language' );

					$tr_id = $data_store->get( $block['attrs']['productId'], $lang );

					// Extract the URL in the button
					$dom = new DOMDocument();
					$dom->loadHTML( $block['innerBlocks'][0]['innerHTML'] );
					$tags = $dom->getElementsByTagName( 'a' );
					$href = $tags[0]->getAttribute( 'href' );

					$blocks[ $k ]['attrs']['productId'] = $tr_id;
					$blocks[ $k ]['innerBlocks'][0]['innerContent'][0] = $blocks[ $k ]['innerBlocks'][0]['innerHTML'] = str_replace( $href, get_permalink( $tr_id ), $block['innerBlocks'][0]['innerHTML'] );
					break;
			}
		}

		return $blocks;
	}
}
