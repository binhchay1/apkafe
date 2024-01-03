<?php

/**
 * Manages compatibility with WooCommerce Table Rate Shipping
 * Version tested: 3.0.2
 *
 * @since 0.5
 */
class PLLWC_Table_Rate_Shipping {

	/**
	 * Constructor
	 *
	 * @since 0.5
	 */
	public function __construct() {
		if ( PLL() instanceof PLL_Frontend ) {
			add_filter( 'woocommerce_table_rate_query_rates', array( $this, 'table_rate_query_rates' ) );
		} else {
			add_filter( 'pll_sanitize_string_translation', array( $this, 'sanitize_strings' ), 10, 3 );
			$this->register_strings();
		}
	}

	/**
	 * Registers all labels in strings translations
	 *
	 * @since 0.5
	 */
	public function register_strings() {
		global $wpdb;

		$labels = $wpdb->get_col( "SELECT rate_label FROM {$wpdb->prefix}woocommerce_shipping_table_rates" );
		$labels = array_filter( $labels ); // Remove empty labels.

		foreach ( $labels as $label ) {
			pll_register_string( __( 'Label', 'woocommerce-table-rate-shipping' ), $label, 'WooCommerce Table Rate Shipping' );
		}
	}

	/**
	 * Translated strings must be sanitized the same way WooCommerce Table Rate Shipping does before they are saved
	 *
	 * @since 0.5
	 *
	 * @param string $translation A string translation.
	 * @param string $name        The string name.
	 * @param string $context     The group the string belongs to.
	 * @return string Sanitized translation
	 */
	public function sanitize_strings( $translation, $name, $context ) {
		if ( 'WooCommerce Table Rate Shipping' === $context ) {
			$translation = wc_clean( $translation );
		}
		return $translation;
	}

	/**
	 * Translates the label on frontend
	 *
	 * @since 0.5
	 *
	 * @param object $rates Table rate shipping method.
	 * @return object
	 */
	public function table_rate_query_rates( $rates ) {
		foreach ( $rates as $k => $rate ) {
			$rates[ $k ]->rate_label = pll__( $rate->rate_label );
		}
		return $rates;
	}
}
