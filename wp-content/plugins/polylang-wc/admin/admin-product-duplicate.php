<?php

/**
 * Duplicates the product translations when duplicating a product
 *
 * @since 1.0
 */
class PLLWC_Admin_Product_Duplicate {
	protected $data_store;

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->data_store = PLLWC_Data_Store::load( 'product_language' );

		add_filter( 'woocommerce_duplicate_product_exclude_children', '__return_true' );
		add_action( 'admin_action_duplicate_product', array( $this, 'duplicate_product_action' ), 5 ); // Before WooCommerce
		add_action( 'woocommerce_product_duplicate', array( $this, 'product_duplicate' ), 10, 2 );
	}

	/**
	 * Remove taxonomy terms language check when duplicating products
	 * This is necessary because duplicate products are assigned the default language at creation.
	 *
	 * @since 0.9.3
	 */
	public function duplicate_product_action() {
		// FIXME Backward compatibility with Polylang < 2.4
		if ( method_exists( PLL()->filters_post, 'set_object_terms' ) ) {
			remove_action( 'set_object_terms', array( PLL()->filters_post, 'set_object_terms' ), 10, 4 );
		} else {
			remove_action( 'set_object_terms', array( PLL()->posts, 'set_object_terms' ), 10, 4 );
		}
	}

	/**
	 * Fires the duplication of duplicated product translations
	 * For WooCommerce 2.7+
	 * Obliged to copy the whole logic of WC_Admin_Duplicate_Product::product_duplicate()
	 * otherwise we can't avoid that WC creates a new sku before the language is assigned
	 * Code base: WC 3.0.5
	 * See also https://github.com/woocommerce/woocommerce/issues/13262
	 *
	 * @since 0.7
	 *
	 * @param object $duplicate Duplicated product.
	 * @param object $product   Original product.
	 */
	public function product_duplicate( $duplicate, $product ) {
		// Get the original translations
		$tr_ids = $this->data_store->get_translations( $product->get_id() );

		$meta_to_exclude = array_filter( apply_filters( 'woocommerce_duplicate_product_exclude_meta', array() ) );

		// First set the language of the product duplicated by WooCommerce
		$lang = $this->data_store->get_language( $product->get_id() );
		$new_tr_ids[ $lang ] = $duplicate->get_id();
		$this->data_store->set_language( $new_tr_ids[ $lang ], $lang );

		// Duplicate translations
		foreach ( $tr_ids as $lang => $tr_id ) {
			if ( $product->get_id() !== $tr_id && $tr_product = wc_get_product( $tr_id ) ) {
				$tr_duplicate = clone $tr_product;

				$tr_duplicate->set_id( 0 );
				/* translators: %s is a product name */
				$tr_duplicate->set_name( sprintf( __( '%s (Copy)', 'woocommerce' ), $tr_duplicate->get_name() ) );
				$tr_duplicate->set_total_sales( 0 );
				$tr_duplicate->set_status( 'draft' );
				$tr_duplicate->set_date_created( null );
				$tr_duplicate->set_slug( '' );
				$tr_duplicate->set_rating_counts( 0 );
				$tr_duplicate->set_average_rating( 0 );
				$tr_duplicate->set_review_count( 0 );

				foreach ( $meta_to_exclude as $meta_key ) {
					$tr_duplicate->delete_meta_data( $meta_key );
				}

				do_action( 'woocommerce_product_duplicate_before_save', $tr_duplicate, $tr_product );

				$tr_duplicate->save();
				$new_tr_ids[ $lang ] = $tr_duplicate->get_id();

				$this->data_store->set_language( $new_tr_ids[ $lang ], $lang );

				// Set SKU only now that the language is known
				if ( '' !== $duplicate->get_sku( 'edit' ) ) {
					$tr_duplicate->set_sku( $duplicate->get_sku( 'edit' ) );
					$tr_duplicate->save();
				}
			}
		}

		// Link duplicated translations together
		$this->data_store->save_translations( $new_tr_ids );

		// Variations
		if ( $product->is_type( 'variable' ) ) {
			foreach ( $product->get_children() as $child_id ) {
				$tr_ids = $this->data_store->get_translations( $child_id );

				if ( $tr_ids && $child = wc_get_product( $child_id ) ) {
					$new_child_tr_ids = array();

					$sku = wc_product_generate_unique_sku( 0, $child->get_sku( 'edit' ) );

					// 2 separate loops because we need to set all sku in the translation group before saving the variations to DB
					// Otherwise we get an Invalid or duplicated SKU exception
					// We use the fact that wc_product_has_unique_sku checks for existing sku in DB
					foreach ( $tr_ids as $lang => $tr_id ) {
						if ( $tr_child = wc_get_product( $tr_id ) ) {
							$tr_child_duplicate[ $lang ] = clone $tr_child;
							$tr_child_duplicate[ $lang ]->set_parent_id( $this->data_store->get( $duplicate->get_id(), $lang ) );
							$tr_child_duplicate[ $lang ]->set_id( 0 );

							if ( '' !== $child->get_sku( 'edit' ) ) {
								$tr_child_duplicate[ $lang ]->set_sku( $sku );
							}

							do_action( 'woocommerce_product_duplicate_before_save', $tr_child_duplicate[ $lang ], $tr_child );
						}
					}

					foreach ( $tr_ids as $lang => $tr_id ) {
						$tr_child_duplicate[ $lang ]->save();
						$new_child_tr_ids[ $lang ] = $tr_child_duplicate[ $lang ]->get_id();
						$this->data_store->set_language( $new_child_tr_ids[ $lang ], $lang );
					}

					$this->data_store->save_translations( $new_child_tr_ids );
				}
			}
		}
	}
}
