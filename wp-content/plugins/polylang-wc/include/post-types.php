<?php

/**
 * Fixes how post types and taxonomies are registered
 * Setups languages and translations data stores
 *
 * @since 0.1
 */
class PLLWC_Post_Types {
	protected $permalinks; // WooCommerce permalinks option

	/**
	 * Constructor
	 *
	 * @since 0.1
	 */
	public function __construct() {
		$this->permalinks = get_option( 'woocommerce_permalinks' );

		add_filter( 'woocommerce_taxonomy_args_product_cat', array( $this, 'woocommerce_taxonomy_args_product_cat' ) );
		add_filter( 'woocommerce_taxonomy_args_product_tag', array( $this, 'woocommerce_taxonomy_args_product_tag' ) );
		add_filter( 'pll_get_taxonomies', array( $this, 'translate_taxonomies' ), 10, 2 );
		add_filter( 'pll_copy_taxonomies', array( $this, 'copy_taxonomies' ), 10, 2 );

		// Add filters from data stores
		$data_store = PLLWC_Data_Store::load( 'product_language' );
		$data_store->init();
		$data_store = PLLWC_Data_Store::load( 'order_language' );
		$data_store->init();
	}

	/**
	 * Disables the translation of the product category slug handled by WooCommerce as it does not play nice in multilingual context
	 *
	 * @since 0.1
	 *
	 * @param array $args Arguments used to register the taxonomy.
	 * @return array
	 */
	public function woocommerce_taxonomy_args_product_cat( $args ) {
		$args['rewrite']['slug'] = empty( $this->permalinks['category_base'] ) ? 'product-category' : $this->permalinks['category_base'];
		return $args;
	}

	/**
	 * Disables the translation of the product tag slug handled by WooCommerce as it does not play nice in multilingual context
	 *
	 * @since 0.1
	 *
	 * @param array $args Arguments used to register the taxonomy.
	 * @return array
	 */
	public function woocommerce_taxonomy_args_product_tag( $args ) {
		$args['rewrite']['slug'] = empty( $this->permalinks['tag_base'] ) ? 'product-tag' : $this->permalinks['tag_base'];
		return $args;
	}

	/**
	 * Get taxonomies for which Polylang manages language and translations
	 *
	 * @since 0.1
	 *
	 * @return array list of taxonomy names
	 */
	protected static function get_translated_taxonomies() {
		// Attribute taxonomies
		$woo_taxonomies = wp_list_pluck( wc_get_attribute_taxonomies(), 'attribute_name' );
		foreach ( $woo_taxonomies as $key => $tax ) {
			$woo_taxonomies[ $key ] = 'pa_' . $tax;
		}

		// Add woocommerce core taxonomies to translate
		return array_merge( array( 'product_cat', 'product_tag' ), $woo_taxonomies );
	}

	/**
	 * Language and translation management for custom taxonomies
	 * All are hidden from Polylang settings
	 *
	 * @since 0.1
	 *
	 * @param array $taxonomies List of taxonomy names for which Polylang manages language and translations.
	 * @param bool  $hide       True when displaying the list in Polylang settings.
	 * @return array List of taxonomy names for which Polylang manages language and translations.
	 */
	public function translate_taxonomies( $taxonomies, $hide ) {
		unset( $taxonomies['product_shipping_class'] ); // Untranslated but hidden from Polylang Settings
		return $hide ? array_diff( $taxonomies, self::get_translated_taxonomies() ) : array_merge( $taxonomies, self::get_translated_taxonomies() );
	}

	/**
	 * Adds taxonomies to the list of taxonomies to copy when creating a new translation
	 *
	 * @since 0.1
	 *
	 * @param array $taxonomies The list of taxonomies to copy or synchronize.
	 * @param bool  $sync       True for sync, false for copy.
	 * @return array The list of taxonomies to copy or synchronize.
	 */
	public function copy_taxonomies( $taxonomies, $sync ) {
		return array_merge( $taxonomies, array( 'product_type', 'product_shipping_class', 'product_visibility' ), self::get_translated_taxonomies() );
	}
}
