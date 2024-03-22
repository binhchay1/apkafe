<?php

/**
 * Manages the coupons
 *
 * @since 0.9
 */
class PLLWC_Coupons {
	protected $data_store;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct() {
		$this->data_store = PLLWC_Data_Store::load( 'product_language' );
		add_action( 'woocommerce_coupon_loaded', array( $this, 'coupon_loaded' ) );
	}

	/**
	 * Translates products and categories restrictions in coupons
	 *
	 * @since 0.3.6
	 *
	 * @param object $data Coupon properties.
	 */
	public function coupon_loaded( $data ) {
		// Test pll_current_language() not to break the Coupons admin page when the admin language filter shows all languages
		if ( pll_current_language() ) {

			$data->set_product_ids( array_map( array( $this, 'maybe_get_translated_product' ), $data->get_product_ids() ) );
			$data->set_excluded_product_ids( array_map( array( $this, 'maybe_get_translated_product' ), $data->get_excluded_product_ids() ) );
			$data->set_product_categories( array_map( array( $this, 'maybe_get_translated_term' ), $data->get_product_categories() ) );
			$data->set_excluded_product_categories( array_map( array( $this, 'maybe_get_translated_term' ), $data->get_excluded_product_categories() ) );
		}
	}

	/**
	 * Returns the translated product id or the current product id if it is not translated
	 *
	 * @since 1.0
	 *
	 * @param int $id Product id.
	 * @return int Translated product id.
	 */
	protected function maybe_get_translated_product( $id ) {
		$tr_id = $this->data_store->get( $id );
		return $tr_id ? $tr_id : $id;
	}

	/**
	 * Returns the translated term id or the current term id if it is not translated
	 *
	 * @since 1.0
	 *
	 * @param int $id Term id.
	 * @return int Translated term id.
	 */
	protected function maybe_get_translated_term( $id ) {
		$tr_id = pll_get_term( $id );
		return $tr_id ? $tr_id : $id;
	}
}
