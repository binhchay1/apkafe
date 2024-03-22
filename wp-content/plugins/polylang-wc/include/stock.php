<?php

/**
 * Manages the synchronization of the stock between translations of the same product
 *
 * @since 0.1
 */
class PLLWC_Stock {
	protected $data_store;

	/**
	 * Constructor
	 *
	 * @since 0.1
	 */
	public function __construct() {
		$this->data_store = PLLWC_Data_Store::load( 'product_language' );

		// FIXME Backward compatibility with WC < 3.6
		if ( version_compare( WC()->version, '3.6', '<' ) ) {
			add_action( 'woocommerce_product_set_stock', array( $this, 'set_stock' ) );
			add_action( 'woocommerce_variation_set_stock', array( $this, 'set_stock' ) );
		}

		add_filter( 'woocommerce_update_product_stock_query', array( $this, 'update_product_stock_query' ), 10, 2 ); // Since WC 3.6.
		add_action( 'woocommerce_updated_product_stock', array( $this, 'updated_product_stock' ) ); // Since WC 3.6.

		add_action( 'woocommerce_product_set_stock_status', array( $this, 'set_stock_status' ), 10, 2 );
		add_action( 'woocommerce_variation_set_stock_status', array( $this, 'set_stock_status' ), 10, 2 );
	}

	/**
	 * Synchronize stock across product translations
	 *
	 * @since 0.9
	 *
	 * @param object $product An instance of WC_Product.
	 */
	public function set_stock( $product ) {
		static $avoid_recursion = array();

		$id  = $product->get_id();
		$qty = $product->get_stock_quantity();

		// To avoid recursion, we make sure that the couple product id + stock quantity is set only once.
		if ( empty( $avoid_recursion[ $id ][ $qty ] ) ) {
			$tr_ids = $this->data_store->get_translations( $id );

			foreach ( $tr_ids as $tr_id ) {
				if ( $tr_id !== $id ) {
					$avoid_recursion[ $id ][ $qty ] = true;
					wc_update_product_stock( $tr_id, $qty );
				}
			}
		}
	}

	/**
	 * Synchronize stock across product translations
	 *
	 * @since 1.2
	 *
	 * @param string $sql        SQL query used to update the product stock.
	 * @param int    $product_id Product id.
	 * @return Modified SQL query
	 */
	public function update_product_stock_query( $sql, $product_id ) {
		global $wpdb;

		$tr_ids = $this->data_store->get_translations( $product_id );

		return $sql = str_replace(
			$wpdb->prepare( 'post_id = %d', $product_id ),
			sprintf( 'post_id IN ( %s )', implode( ',', array_map( 'absint', $tr_ids ) ) ),
			$sql
		);
	}

	/**
	 * Delete the cache and update the stock status for all the translations
	 *
	 * @since 1.2
	 *
	 * @param int $id Product id.
	 */
	public function updated_product_stock( $id ) {
		foreach ( $this->data_store->get_translations( $id )  as $tr_id ) {
			if ( $tr_id !== $id ) {
				$product = wc_get_product( $tr_id );
				$product_id_with_stock = $product->get_stock_managed_by_id();

				// 1. Actions done in WC_Product_Data_Store_CPT::update_product_stock() for the source product
				wp_cache_delete( $product_id_with_stock, 'post_meta' );
				$this->data_store->update_lookup_table( $tr_id, 'wc_product_meta_lookup' );

				// 2. Actions done in wc_update_product_stock()
				// Some products (variations) can have their stock managed by their parent. Get the correct ID to reduce here.
				delete_transient( 'wc_product_children_' . ( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id() ) );
				wp_cache_delete( 'product-' . $product_id_with_stock, 'products' );
				// Re-read product data after updating stock, then have stock status calculated and saved.
				$product_with_stock = wc_get_product( $product_id_with_stock );
				$product_with_stock->set_stock_status();
				$product_with_stock->set_date_modified( current_time( 'timestamp', true ) );
				$product_with_stock->save();
				if ( $product_with_stock->is_type( 'variation' ) ) {
					do_action( 'woocommerce_variation_set_stock', $product_with_stock );
				} else {
					do_action( 'woocommerce_product_set_stock', $product_with_stock );
				}
			}
		}
	}

	/**
	 * Synchronize stock status across product translations
	 *
	 * @since 1.1
	 *
	 * @param int    $id     Product id.
	 * @param string $status Stock status.
	 */
	public function set_stock_status( $id, $status ) {
		static $avoid_recursion = array();

		// To avoid recursion, we make sure that the couple product id + stock status is set only once.
		if ( empty( $avoid_recursion[ $id ][ $status ] ) ) {
			$tr_ids = $this->data_store->get_translations( $id );

			foreach ( $tr_ids as $tr_id ) {
				if ( $tr_id !== $id ) {
					$avoid_recursion[ $id ][ $status ] = true;
					wc_update_product_stock_status( $tr_id, $status );
				}
			}
		}
	}
}
