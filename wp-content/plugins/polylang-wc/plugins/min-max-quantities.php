<?php

/**
 * Manages compatibility with WooCommerce Min/Max Quantities
 * Version tested: 2.4.3
 *
 * @since 1.1
 */
class PLLWC_Min_Max_Quantities {

	/**
	 * Constructor
	 * Setups filters
	 *
	 * @since 1.1
	 */
	public function __construct() {
		add_filter( 'pllwc_copy_post_metas', array( $this, 'copy_product_metas' ) );
		add_filter( 'pll_copy_term_metas', array( $this, 'copy_term_metas' ) );
	}

	/**
	 * Synchronize product metas
	 *
	 * @since 1.1
	 *
	 * @param array $metas List of custom fields names.
	 * @return array
	 */
	public function copy_product_metas( $metas ) {
		$to_sync = array(
			'min_max_rules',
			'allow_combination',
			'group_of_quantity',
			'maximum_allowed_quantity',
			'minimum_allowed_quantity',
			'minmax_cart_exclude',
			'minmax_category_group_of_exclude',
			'minmax_do_not_count',
			'variation_group_of_quantity',
			'variation_maximum_allowed_quantity',
			'variation_minimum_allowed_quantity',
			'variation_minmax_cart_exclude',
			'variation_minmax_category_group_of_exclude',
			'variation_minmax_do_not_count',
		);

		return array_merge( $metas, array_combine( $to_sync, $to_sync ) );
	}

	/**
	 * Synchronize term metas
	 *
	 * @since 1.1
	 *
	 * @param array $metas List of term metas names.
	 * @return array
	 */
	public function copy_term_metas( $metas ) {
		return array_merge( $metas, array( 'group_of_quantity' ) );
	}
}
