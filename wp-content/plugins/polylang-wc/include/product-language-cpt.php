<?php

/**
 * Setups the product languages and translations model when products are managed with a custom post type
 *
 * @since 1.0
 */
class PLLWC_Product_Language_CPT extends PLLWC_Translated_Object_Language_CPT {
	protected $permalinks; // WooCommerce permalinks option
	protected static $editing_term;

	/**
	 * Add filters, should be called only once
	 *
	 * @since 1.0
	 */
	public function init() {
		$this->permalinks = get_option( 'woocommerce_permalinks' );

		add_filter( 'pll_get_post_types', array( $this, 'translated_post_types' ), 10, 2 );
		add_filter( 'woocommerce_register_post_type_product', array( $this, 'woocommerce_register_post_type_product' ) );
		add_filter( 'woocommerce_variable_children_args', array( $this, 'variable_children_args' ) );

		// Synchronization
		add_filter( 'pll_copy_post_metas', array( $this, 'copy_post_metas' ), 5, 5 );
		add_filter( 'pll_translate_post_meta', array( $this, 'translate_post_meta' ), 5, 5 );
		add_action( 'woocommerce_product_object_updated_props', array( $this, 'updated_props' ), 10, 2 );

		// Attributes
		remove_action( 'edit_term', array( 'WC_Post_Data', 'edit_term' ), 10, 3 );
		remove_action( 'edited_term', array( 'WC_Post_Data', 'edited_term' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'edit_term' ), 10, 3 );
		add_action( 'edited_term', array( $this, 'edited_term' ), 10, 3 );
	}

	/**
	 * Add products and variations to translated post types
	 *
	 * @since 1.0
	 *
	 * @param array $types List of post type names for which Polylang manages language and translations.
	 * @param bool  $hide  True when displaying the list in Polylang settings.
	 * @return array List of post type names for which Polylang manages language and translations.
	 */
	public function translated_post_types( $types, $hide ) {
		$woo_types = array( 'product', 'product_variation' );
		return $hide ? array_diff( $types, $woo_types ) : array_merge( $types, $woo_types );
	}

	/**
	 * Disables the translation of the product slug handled by WooCommerce as it does not play nice in multilingual context
	 *
	 * @since 0.1
	 *
	 * @param array $args arguments used to register the post type.
	 * @return array
	 */
	public function woocommerce_register_post_type_product( $args ) {
		$product_permalink = empty( $this->permalinks['product_base'] ) ? 'product' : $this->permalinks['product_base'];
		$args['rewrite'] = $product_permalink ? array( 'slug' => untrailingslashit( $product_permalink ), 'with_front' => false, 'feeds' => true ) : false;
		return $args;
	}

	/**
	 * Filter args used to get variable products children, to make sure that they are not filtered by the current language
	 *
	 * @since 0.7.3
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	public function variable_children_args( $args ) {
		$args['lang'] = '';
		return $args;
	}

	/**
	 * Returns legacy product metas mapped to product properties
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	protected function get_legacy_metas() {
		// Map metas to properties
		$metas = array(
			'_backorders'            => 'backorders',
			'_children'              => 'children',
			'_crosssell_ids'         => 'cross_sell_ids',
			'_default_attributes'    => 'default_attributes',
			'_download_expiry'       => 'download_expiry',
			'_download_limit'        => 'download_limit',
			'_downloadable'          => 'downloadable',
			'_downloadable_files'    => 'downloads',
			'_featured'              => 'featured',
			'_height'                => 'height',
			'_length'                => 'length',
			'_low_stock_amount'      => 'low_stock_amount',
			'_manage_stock'          => 'manage_stock',
			'_price'                 => 'price',
			'_product_attributes'    => 'attributes',
			'_product_image_gallery' => 'gallery_image_ids',
			'_regular_price'         => 'regular_price',
			'_sale_price'            => 'sale_price',
			'_sale_price_dates_from' => 'date_on_sale_from',
			'_sale_price_dates_to'   => 'date_on_sale_to',
			'_sku'                   => 'sku',
			'_sold_individually'     => 'sold_individually',
			'_stock'                 => 'stock_quantity',
			'_stock_status'          => 'stock_status',
			'_tax_class'             => 'tax_class',
			'_tax_status'            => 'tax_status',
			'_thumbnail_id'          => 'image_id',
			'_upsell_ids'            => 'upsell_ids',
			'_virtual'               => 'virtual',
			'_weight'                => 'weight',
			'_width'                 => 'width',
			'_button_text'           => 'button_text',
			'_product_url'           => 'product_url',
			'_purchase_note'         => 'purchase_note',
			'_variation_description' => 'description',
		);

		return $metas;
	}

	/**
	 * Get the custom fields to copy or synchronize
	 *
	 * @since 1.0
	 *
	 * @param array  $metas List of custom fields names.
	 * @param bool   $sync  True if it is synchronization, false if it is a copy.
	 * @param int    $from  Id of the product from which we copy informations.
	 * @param int    $to    Id of the product to which we copy informations.
	 * @param string $lang  Language code.
	 * @return array
	 */
	public function copy_post_metas( $metas, $sync, $from, $to, $lang ) {
		if ( in_array( get_post_type( $from ), array( 'product', 'product_variation' ) ) ) {

			$_to_copy = self::get_legacy_metas();
			$to_copy = array_keys( $_to_copy );

			// Add attributes in variations
			foreach ( array_keys( get_post_custom( $from ) ) as $key ) {
				if ( 0 === strpos( $key, 'attribute_' ) ) {
					$to_copy[] = $key;
				}
			}

			// Should we copy text ?
			if ( ! PLLWC_Admin_Products::should_copy_texts( $from, $to, $sync ) ) {
				$to_copy = array_diff(
					$to_copy,
					array(
						'_button_text',
						'_product_url',
						'_purchase_note',
						'_variation_description',
					)
				);
			}

			/**
			 * Filter the custom fields to copy or synchronize
			 *
			 * @since 0.2
			 *
			 * @param array  $to_copy List of custom fields names.
			 * @param bool   $sync    True if it is synchronization, false if it is a copy.
			 * @param int    $from    Id of the product from which we copy informations.
			 * @param int    $to      Id of the product to which we paste informations.
			 * @param string $lang    Language code.
			 */
			$to_copy = array_unique( apply_filters( 'pllwc_copy_post_metas', array_combine( $to_copy, $to_copy ), $sync, $from, $to, $lang ) );
			$metas = array_merge( $metas, $to_copy );
		}

		return $metas;
	}

	/**
	 * Get data to save to a lookup table.
	 * Exact copy of WC_Product_Data_Store_CPT::get_data_for_lookup_table()
	 * Code base: WC 3.6
	 *
	 * @since 1.2
	 *
	 * @param int    $id    ID of object to update.
	 * @param string $table Lookup table name.
	 * @return array
	 */
	protected function get_data_for_lookup_table( $id, $table ) {
		if ( 'wc_product_meta_lookup' === $table ) {
			$price_meta   = (array) get_post_meta( $id, '_price', false );
			$manage_stock = get_post_meta( $id, '_manage_stock', true );
			$stock        = 'yes' === $manage_stock ? wc_stock_amount( get_post_meta( $id, '_stock', true ) ) : null;
			$price        = wc_format_decimal( get_post_meta( $id, '_price', true ) );
			$sale_price   = wc_format_decimal( get_post_meta( $id, '_sale_price', true ) );
			return array(
				'product_id'     => absint( $id ),
				'sku'            => get_post_meta( $id, '_sku', true ),
				'virtual'        => 'yes' === get_post_meta( $id, '_virtual', true ) ? 1 : 0,
				'downloadable'   => 'yes' === get_post_meta( $id, '_downloadable', true ) ? 1 : 0,
				'min_price'      => reset( $price_meta ),
				'max_price'      => end( $price_meta ),
				'onsale'         => $sale_price && $price === $sale_price ? 1 : 0,
				'stock_quantity' => $stock,
				'stock_status'   => get_post_meta( $id, '_stock_status', true ),
				'rating_count'   => array_sum( (array) get_post_meta( $id, '_wc_rating_count', true ) ),
				'average_rating' => get_post_meta( $id, '_wc_average_rating', true ),
				'total_sales'    => get_post_meta( $id, 'total_sales', true ),
			);
		}
		return array();
	}

	/**
	 * Update a lookup table for an object.
	 * Exact copy of WC_Data_Store_WP::update_lookup_table()
	 * Code base: WC 3.6
	 *
	 * @since 1.2
	 *
	 * @param int    $id    ID of object to update.
	 * @param string $table Lookup table name.
	 */
	public function update_lookup_table( $id, $table ) {
		global $wpdb;

		$id    = absint( $id );
		$table = sanitize_key( $table );

		if ( empty( $id ) || empty( $table ) ) {
			return false;
		}

		$existing_data = wp_cache_get( 'lookup_table', 'object_' . $id );
		$update_data   = $this->get_data_for_lookup_table( $id, $table );

		if ( ! empty( $update_data ) && $update_data !== $existing_data ) {
			$wpdb->replace(
				$wpdb->$table,
				$update_data
			);
			wp_cache_set( 'lookup_table', $update_data, 'object_' . $id );
		}
	}

	/**
	 * Fires actions and update look tables of translated products after properties have been synchronized
	 *
	 * @since 1.2
	 *
	 * @param object $product       Product.
	 * @param array  $updated_props Product properties being updated.
	 */
	public function updated_props( $product, $updated_props ) {
		static $avoid_recursion = false;

		if ( $avoid_recursion ) {
			return;
		}

		$avoid_recursion = true;

		foreach ( $this->get_translations( $product->get_id() ) as $tr_id ) {
			if ( $product->get_id() !== $tr_id && $product = wc_get_product( $tr_id ) ) {

				if ( in_array( 'stock_quantity', $updated_props, true ) ) {
					if ( $product->is_type( 'variation' ) ) {
						do_action( 'woocommerce_variation_set_stock', $product );
					} else {
						do_action( 'woocommerce_product_set_stock', $product );
					}
				}

				if ( in_array( 'stock_status', $updated_props, true ) ) {
					if ( $product->is_type( 'variation' ) ) {
						do_action( 'woocommerce_variation_set_stock_status', $product->get_id(), $product->get_stock_status(), $product );
					} else {
						do_action( 'woocommerce_product_set_stock_status', $product->get_id(), $product->get_stock_status(), $product );
					}
				}

				if ( version_compare( WC()->version, '3.6', '>=' ) && array_intersect( $updated_props, array( 'sku', 'regular_price', 'sale_price', 'date_on_sale_from', 'date_on_sale_to', 'total_sales', 'average_rating', 'stock_quantity', 'stock_status', 'manage_stock', 'downloadable', 'virtual' ) ) ) {
					$this->update_lookup_table( $product->get_id(), 'wc_product_meta_lookup' );
				}

				// Trigger action so 3rd parties can deal with updated props.
				do_action( 'woocommerce_product_object_updated_props', $product, $updated_props );
			}
		}

		$avoid_recursion = false;
	}

	/**
	 * Translate a custom field before it is copied or synchronized
	 *
	 * @since 1.0
	 *
	 * @param mixed  $value Meta value.
	 * @param string $key   Meta key.
	 * @param string $lang  Language of target.
	 * @param int    $from  Id of the object from which we copy informations.
	 * @param int    $to    Id of the target.
	 * @return mixed
	 */
	public function translate_post_meta( $value, $key, $lang, $from, $to ) {
		if ( in_array( get_post_type( $from ), array( 'product', 'product_variation' ) ) ) {
			if ( 0 === strpos( $key, 'attribute_' ) ) {
				// Translate taxonomy attributes in variations
				$tax = substr( $key, 10 );
				if ( taxonomy_exists( $tax ) && $value ) {
					$terms = get_terms( $tax, array( 'slug' => $value, 'hide_empty' => false, 'lang' => '' ) ); // Don't use get_term_by filtered by language since WP 4.7
					if ( is_array( $terms ) && ( $term = reset( $terms ) ) && $tr_id = pll_get_term( $term->term_id, $lang ) ) {
						$term = get_term( $tr_id, $tax );
						$value  = $term->slug;
					}
				}
			} else {
				$props = self::get_legacy_metas();
				if ( isset( $props[ $key ] ) ) {
					$value = PLLWC_Admin_Products::maybe_translate_property( $value, $props[ $key ], $lang );
				}
			}

			/**
			 * Filter a meta value before is copied or synchronized
			 *
			 * @since 1.0
			 *
			 * @param mixed  $value Meta value.
			 * @param string $key   Meta key.
			 * @param string $lang  Language of target.
			 * @param int    $from  Id of the source.
			 * @param int    $to    Id of the target.
			 */
			$value = apply_filters( 'pllwc_translate_product_meta', $value, $key, $lang, $from, $to );
		}
		return $value;
	}

	/**
	 * Copies properties (taxonomies and metas) from one product to another product
	 *
	 * @since 1.0
	 *
	 * @param int    $from  Id of the product from which we copy informations.
	 * @param int    $to    Id of the product to which we copy informations.
	 * @param string $lang  Language code.
	 * @param bool   $sync  Optional, defaults to false. True if it is synchronization, false if it is a copy.
	 */
	public function copy( $from, $to, $lang, $sync = false ) {
		global $wpdb;

		// Synchronize the status for the variations
		$post = get_post( $from );

		if ( 'product_variation' === $post->post_type ) {
			$wpdb->update( $wpdb->posts, array( 'post_status' => $post->post_status ), array( 'ID' => $to ) );
		}

		if ( ! $sync ) {
			PLL()->sync->taxonomies->copy( $from, $to, $lang );
		}

		PLL()->sync->post_metas->copy( $from, $to, $lang, $sync );
	}

	/**
	 * Synchronize product ordering
	 *
	 * @since 1.0
	 *
	 * @param int   $id    Product id.
	 * @param array $order Product order.
	 */
	public function save_product_ordering( $id, $order ) {
		global $wpdb;
		$wpdb->update( $wpdb->posts, array( 'menu_order' => $order ), array( 'ID' => $id ) );
	}

	/**
	 * Returns the translation group name of a product (if exists)
	 * This is the name of the associated taxonomy term
	 *
	 * @since 1.0
	 *
	 * @param int $id Product id.
	 * @return string
	 */
	public function get_translation_group_name( $id ) {
		$term = PLL()->model->post->get_object_term( $id, 'post_translations' );
		return empty( $term ) ? '' : $term->name;
	}

	/**
	 * Check if product sku is found for any other product IDs in a language
	 * Modified version WC_Product_Data_Store_CPT::is_existing_sku()
	 * Code base: WC 3.6
	 *
	 * @since 1.0
	 *
	 * @param int    $product_id Product ID.
	 * @param string $sku        SKU Will be slashed to work around https://core.trac.wordpress.org/ticket/27421.
	 * @param string $lang       Language code.
	 * @return bool
	 */
	public function is_existing_sku( $product_id, $sku, $lang ) {
		global $wpdb;

		$lang = PLL()->model->get_language( $lang );

		// FIXME Backward compatibility with WC < 3.6
		if ( version_compare( WC()->version, '3.6', '<' ) ) {
			return $wpdb->get_var(
				$wpdb->prepare(
					"SELECT $wpdb->posts.ID
					FROM $wpdb->posts
					LEFT JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
					INNER JOIN $wpdb->term_relationships AS pll_tr ON pll_tr.object_id = $wpdb->posts.ID
					WHERE $wpdb->posts.post_type IN ( 'product', 'product_variation' )
						AND $wpdb->posts.post_status != 'trash'
						AND $wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value = %s
						AND $wpdb->postmeta.post_id <> %d
						AND pll_tr.term_taxonomy_id = %d
					LIMIT 1",
					wp_slash( $sku ),
					$product_id,
					$lang->term_taxonomy_id
				)
			);
		}

		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT posts.ID
				FROM {$wpdb->posts} AS posts
				INNER JOIN {$wpdb->wc_product_meta_lookup} AS lookup ON posts.ID = lookup.product_id
				INNER JOIN {$wpdb->term_relationships} AS pll_tr ON pll_tr.object_id = posts.ID
				WHERE posts.post_type IN ( 'product', 'product_variation' )
					AND posts.post_status != 'trash'
					AND lookup.sku = %s
					AND lookup.product_id <> %d
					AND pll_tr.term_taxonomy_id = %d
				LIMIT 1",
				wp_slash( $sku ),
				$product_id,
				$lang->term_taxonomy_id
			)
		);

	}

	/**
	 * Returns product id based on sku and language
	 * Modified version WC_Product_Data_Store_CPT::get_product_id_by_sku()
	 * Code base: WC 3.6
	 *
	 * @since 1.0
	 *
	 * @param string $sku  SKU.
	 * @param string $lang Language code.
	 * @return int Product id.
	 */
	public function get_product_id_by_sku( $sku, $lang ) {
		global $wpdb;

		$lang = PLL()->model->get_language( $lang );

		// FIXME Backward compatibility with WC < 3.6
		if ( version_compare( WC()->version, '3.6', '<' ) ) {
			return $wpdb->get_var(
				$wpdb->prepare(
					"SELECT posts.ID
					FROM $wpdb->posts AS posts
					LEFT JOIN $wpdb->postmeta AS postmeta ON ( posts.ID = postmeta.post_id )
					INNER JOIN $wpdb->term_relationships AS pll_tr ON pll_tr.object_id = posts.ID
					WHERE posts.post_type IN ( 'product', 'product_variation' )
						AND posts.post_status != 'trash'
						AND postmeta.meta_key = '_sku'
						AND postmeta.meta_value = %s
						AND pll_tr.term_taxonomy_id = %d
					LIMIT 1",
					$sku,
					$lang->term_taxonomy_id
				)
			);
		}

		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT posts.ID
				FROM {$wpdb->posts} as posts
				INNER JOIN {$wpdb->wc_product_meta_lookup} AS lookup ON posts.ID = lookup.product_id
				INNER JOIN {$wpdb->term_relationships} AS pll_tr ON pll_tr.object_id = posts.ID
				WHERE posts.post_type IN ( 'product', 'product_variation' )
					AND posts.post_status != 'trash'
					AND lookup.sku = %s
					AND pll_tr.term_taxonomy_id = %d
				LIMIT 1",
				$sku,
				$lang->term_taxonomy_id
			)
		);
	}

	/**
	 * When editing a term, check for product attributes.
	 * This is a exact copy of WC_Post_Data::edit_term()
	 * Code base: WC 3.6
	 * A copy is needed because of the private property $editing_term
	 *
	 * @since 1.2
	 *
	 * @param  int    $term_id  Term ID.
	 * @param  int    $tt_id    Term taxonomy ID.
	 * @param  string $taxonomy Taxonomy slug.
	 */
	public static function edit_term( $term_id, $tt_id, $taxonomy ) {
		if ( strpos( $taxonomy, 'pa_' ) === 0 ) {
			self::$editing_term = get_term_by( 'id', $term_id, $taxonomy );
		} else {
			self::$editing_term = null;
		}
	}

	/**
	 * When a term is edited, check for product attributes and update variations.
	 * This is a modified version of WC_Post_Data::edited_term()
	 * Code base: WC 3.6
	 * The language is added to the query to take into account updates of attributes sharing slugs
	 *
	 * @since 1.2
	 *
	 * @param  int    $term_id  Term ID.
	 * @param  int    $tt_id    Term taxonomy ID.
	 * @param  string $taxonomy Taxonomy slug.
	 */
	public function edited_term( $term_id, $tt_id, $taxonomy ) {
		global $wpdb;

		if ( ! is_null( self::$editing_term ) && strpos( $taxonomy, 'pa_' ) === 0 ) {
			$edited_term = get_term_by( 'id', $term_id, $taxonomy );

			if ( $edited_term->slug !== self::$editing_term->slug ) {
				$language = pll_get_term_language( $term_id );

				$sql  = "UPDATE {$wpdb->postmeta} AS pm";
				$sql .= " INNER JOIN {$wpdb->term_relationships} AS pll_tr ON pll_tr.object_id = pm.post_id"; // FIXME Can't use $this->join_clause due to wrong column name
				$sql .= $wpdb->prepare( ' SET meta_value = %s', $edited_term->slug );
				$sql .= $wpdb->prepare( ' WHERE meta_key = %s AND meta_value = %s', 'attribute_' . sanitize_title( $taxonomy ), self::$editing_term->slug );
				$sql .= $this->where_clause( $language );
				$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

				$sql  = "UPDATE {$wpdb->postmeta} AS pm";
				$sql .= " INNER JOIN {$wpdb->term_relationships} AS pll_tr ON pll_tr.object_id = pm.post_id"; // FIXME Can't use $this->join_clause due to wrong column name
				$sql .= $wpdb->prepare(
					' SET meta_value = REPLACE( meta_value, %s, %s )',
					serialize( self::$editing_term->taxonomy ) . serialize( self::$editing_term->slug ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
					serialize( $edited_term->taxonomy ) . serialize( $edited_term->slug ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
				);
				$sql .= " WHERE meta_key = '_default_attributes'";
				$sql .= $this->where_clause( $language );
				$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}
		} else {
			self::$editing_term = null;
		}
	}
}
