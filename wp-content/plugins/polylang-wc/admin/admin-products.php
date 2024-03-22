<?php

/**
 * Manages the products on admin side (interface and synchronization of data)
 *
 * @since 0.1
 */
class PLLWC_Admin_Products {
	protected $data_store;

	/**
	 * Constructor
	 *
	 * @since 0.1
	 */
	public function __construct() {
		$this->data_store = PLLWC_Data_Store::load( 'product_language' );

		// Variations synchronization
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 5, 2 );
		add_action( 'woocommerce_update_product', array( $this, 'save_product' ) );
		add_action( 'woocommerce_new_product_variation', array( $this, 'save_variation' ) );
		add_action( 'woocommerce_update_product_variation', array( $this, 'save_variation' ) );

		add_action( 'pll_created_sync_post', array( $this, 'copy_variations' ), 5, 3 );

		// Variations deletion
		if ( version_compare( WC()->version, '3.4', '<' ) ) {
			add_action( 'before_delete_post', array( $this, 'delete_post' ) );
		} else {
			add_action( 'woocommerce_before_delete_product_variation', array( $this, 'delete_variation' ) );
		}

		// Ajax
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_product_lang_choice', array( $this, 'product_lang_choice' ) );

		// Ajax product ordering
		if ( in_array( 'menu_order', PLL()->options['sync'] ) ) {
			add_action( 'woocommerce_after_product_ordering', array( $this, 'product_ordering' ), 10, 2 );
		}

		// Autocomplete ajax products search
		add_filter( 'woocommerce_json_search_found_products', array( $this, 'search_found_products' ) );
		add_filter( 'woocommerce_json_search_found_grouped_products', array( $this, 'search_found_products' ) );

		// Search in Products list table
		add_filter( 'pll_filter_query_excluded_query_vars', array( $this, 'fix_products_search' ), 10, 2 ); // Since Polylang 2.3.5

		// Unique SKU
		add_filter( 'wc_product_has_unique_sku', array( $this, 'unique_sku' ), 10, 3 );

		// Don't apply German and Danish specific sanitization for product attributes titles
		$specific_locales = array( 'da_DK', 'de_DE', 'de_DE_formal', 'de_CH', 'de_CH_informal' );
		if ( array_intersect( PLL()->model->get_languages_list( array( 'fields' => 'locale' ) ), $specific_locales ) ) {
			add_action( 'wp_ajax_woocommerce_load_variations', array( $this, 'remove_sanitize_title' ), 5 );
			add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'remove_sanitize_title' ), 5 );
			add_action( 'woocommerce_product_data_panels', array( $this, 'add_sanitize_title' ), 5 );
		}
	}

	/**
	 * Copy (create) or synchronize a variation
	 *
	 * @since 1.0
	 *
	 * @param int    $id        Source variation product id.
	 * @param int    $tr_parent Target variable product id.
	 * @param string $lang      Target language.
	 */
	protected function copy_variation( $id, $tr_parent, $lang ) {
		static $avoid_recursion = false;

		if ( $avoid_recursion ) {
			return;
		}

		$tr_id = $this->data_store->get( $id, $lang );

		if ( $tr_id === $id ) {
			return;
		}

		if ( ! $tr_id ) {
			// If the product variation is untranslated, attempt to find a translation based on the attribute
			$tr_product = wc_get_product( $tr_parent );

			if ( is_a( $tr_product, 'WC_Product_Variable' ) ) {
				$tr_attributes = $tr_product->get_variation_attributes();

				if ( ! empty( $tr_attributes ) && $variation = wc_get_product( $id ) ) {
					// At least one translated variation was manually created
					$attributes = $variation->get_attributes();
					if ( ! in_array( '', $attributes ) ) {
						$attributes = $this->maybe_translate_attributes( $attributes, $lang );
						foreach ( $tr_product->get_children() as $_tr_id ) {
							$tr_variation = wc_get_product( $_tr_id );
							if ( $tr_variation && $attributes === $tr_variation->get_attributes() ) {
								$tr_id = $tr_variation->get_id();
								break;
							}
						}
					}
				}
			}

			if ( ! $tr_id ) {
				// Creates the translated product variation if it does not exist yet
				$avoid_recursion = true;

				$tr_variation = new WC_Product_Variation();
				$tr_variation->set_parent_id( $tr_parent );
				$tr_id = $tr_variation->save();

				$avoid_recursion = false;
			}

			$this->data_store->copy( $id, $tr_id, $lang );
			$this->data_store->set_language( $tr_id, $lang );
			$translations = $this->data_store->get_translations( $id );
			$translations[ $this->data_store->get_language( $id ) ] = $id; // In case this is the first translation created
			$translations[ $lang ] = $tr_id;
			$this->data_store->save_translations( $translations );
		} else {
			// Make sure the parent product is correct
			$tr_variation = new WC_Product_Variation( $tr_id );
			if ( $tr_variation->get_parent_id() !== $tr_parent ) {
				$avoid_recursion = true;

				$tr_variation->set_parent_id( $tr_parent );
				$tr_id = $tr_variation->save();

				$avoid_recursion = false;
			}

			// Synchronize
			$this->data_store->copy( $id, $tr_id, $lang, true );
		}
	}

	/**
	 * Copy or synchronize variations
	 *
	 * @since 0.1
	 *
	 * @param int    $from Product id from which we copy informations.
	 * @param int    $to   Product id to which we paste informations.
	 * @param string $lang Language code.
	 * @param bool   $sync True if it is synchronization, false if it is a copy, defaults to false.
	 */
	public function copy_variations( $from, $to, $lang, $sync = false ) {
		$product = wc_get_product( $from );

		if ( is_a( $product, 'WC_Product_Variable' ) ) {
			$language = $this->data_store->get_language( $from );
			$variations = $product->get_children(); // Note: it does not return disabled variations in WC < 3.3.

			remove_action( 'woocommerce_new_product_variation', array( $this, 'save_variation' ) ); // Avoid reverse sync.
			foreach ( $variations as $id ) {
				$this->data_store->set_language( $id, $language );
				$this->copy_variation( $id, $to, $lang );
			}
			add_action( 'woocommerce_new_product_variation', array( $this, 'save_variation' ) );
		}
	}

	/**
	 * Copy variations and metas when using "Add new" ( translation )
	 *
	 * @since 0.1
	 *
	 * @param string $post_type Unused.
	 * @param object $post      Current post object.
	 */
	public function add_meta_boxes( $post_type, $post ) {
		if ( 'post-new.php' === $GLOBALS['pagenow'] && isset( $_GET['from_post'], $_GET['new_lang'] ) && 'product' === $post_type ) {
			check_admin_referer( 'new-post-translation' );

			// Capability check already done in post-new.php
			$lang = PLL()->model->get_language( sanitize_key( $_GET['new_lang'] ) ); // Make sure we have a valid language.
			$this->copy_variations( (int) $_GET['from_post'], $post->ID, $lang->slug );

			/**
			 * Fires after metas and variations have been copied from a product to a translation
			 *
			 * @since 0.5
			 *
			 * @param int    $from Original product ID
			 * @param int    $to   Target product ID
			 * @param string $lang Language of the target product
			 * @param bool   $sync true when synchronizing products, empty when creating a new translation
			 */
			do_action( 'pllwc_copy_product', (int) $_GET['from_post'], $post->ID, $lang->slug );
		}
	}

	/**
	 * Fires an action that can be used to synchronize data when a product is saved
	 *
	 * @since 1.0
	 *
	 * @param int $id Product ID.
	 */
	public function save_product( $id ) {
		$translations = $this->data_store->get_translations( $id );
		foreach ( $translations as $lang => $tr_id ) {
			if ( $id !== $tr_id ) {
				// It's useless to copy variations if we already did it by saving variations before
				if ( ! did_action( 'woocommerce_update_product_variation' ) && ! did_action( 'woocommerce_new_product_variation' ) ) {
					$this->copy_variations( $id, $tr_id, $lang, true );
				}

				/** This action is documented in admin/admin-products.php */
				do_action( 'pllwc_copy_product', $id, $tr_id, $lang, true );
			}
		}
	}

	/**
	 * Sets the variation language and synchronizes it with its translations
	 *
	 * @since 1.0
	 *
	 * @param int $id Variation product id.
	 */
	public function save_variation( $id ) {
		static $avoid_recursion = false;

		if ( ! doing_action( 'woocommerce_product_duplicate' ) && ! doing_action( 'wp_ajax_woocommerce_do_ajax_product_import' ) && ! $avoid_recursion ) {
			$avoid_recursion = true;

			if ( $variation = wc_get_product( $id ) ) {
				$pid = $variation->get_parent_id();
				$language = $this->data_store->get_language( $pid );
				$this->data_store->set_language( $id, $language );

				foreach ( $this->data_store->get_translations( $pid ) as $lang => $tr_pid ) {
					if ( $tr_pid !== $pid ) {
						$tr_id = $this->copy_variation( $id, $tr_pid, $lang );
					}
				}
			}
		}
		$avoid_recursion = false;
	}

	/**
	 * Synchronizes variations deletion
	 * FIXME: Backward compatibility with WC < 3.4
	 *
	 * @since 0.1
	 *
	 * @param int $post_id Product ID.
	 */
	public function delete_post( $post_id ) {
		// This method still relies on post types due to the lack of a WC action before a product is deleted
		static $avoid_delete = array();
		static $avoid_parent = 0;

		$post_type = get_post_type( $post_id );

		// Avoid deleting translated variations when deleting a product
		if ( 'product' === $post_type ) {
			$avoid_parent = $post_id;
		}

		if ( 'product_variation' === $post_type && ! in_array( $post_id, $avoid_delete ) ) {
			$post = get_post( $post_id );
			if ( $post->post_parent !== $avoid_parent ) {
				$tr_ids = $this->data_store->get_translations( $post_id );
				$avoid_delete = array_merge( $avoid_delete, array_values( $tr_ids ) ); // To avoid deleting a post two times.
				foreach ( $tr_ids as $k => $tr_id ) {
					wp_delete_post( $tr_id );
				}
			}
		}
	}

	/**
	 * Synchronizes variations deletion
	 *
	 * @since 1.0
	 *
	 * @param int $id Variation product id.
	 */
	public function delete_variation( $id ) {
		static $avoid_delete = array();

		if ( ! in_array( $id, $avoid_delete ) ) {
			$tr_ids = $this->data_store->get_translations( $id );
			$avoid_delete = array_merge( $avoid_delete, array_values( $tr_ids ) ); // To avoid deleting a variation two times.
			foreach ( $tr_ids as $tr_id ) {
				if ( $variation = wc_get_product( $tr_id ) ) {
					$variation->delete( true );
				}
			}
		}
	}

	/**
	 * Checks whether two products are synchronized
	 * Backward compatibility with Polylang < 2.6
	 *
	 * @since 1.2
	 *
	 * @param int $id       ID of the first product to compare.
	 * @param int $other_id ID of the second product to compare.
	 * @return bool
	 */
	protected static function are_synchronized( $id, $other_id ) {
		if ( isset( PLL()->sync_post->sync_model ) ) {
			return PLL()->sync_post->sync_model->are_synchronized( $id, $other_id );
		} else {
			return PLL()->sync_post->are_synchronized( $id, $other_id );
		}
	}

	/**
	 * Determines whether texts should be copied depending on duplicate and synchronization options
	 *
	 * @since 1.0
	 *
	 * @param int  $from Product id from which we copy informations.
	 * @param int  $to   Product id which we paste informations.
	 * @param bool $sync True if it is synchronization, false if it is a copy.
	 */
	public static function should_copy_texts( $from, $to, $sync ) {
		if ( ! $sync ) {
			$duplicate_options = get_user_meta( get_current_user_id(), 'pll_duplicate_content', true );
			if ( ! empty( $duplicate_options ) && ! empty( $duplicate_options['product'] ) ) {
				return true;
			}
		}

		if ( isset( PLL()->sync_post ) ) {
			$from = wc_get_product( $from );
			$to   = wc_get_product( $to );

			if ( ! empty( $from ) && ! empty( $to ) ) {
				if ( 'variation' === $from->get_type() ) {
					return self::are_synchronized( $from->get_parent_id(), $to->get_parent_id() );
				} else {
					return self::are_synchronized( $from->get_id(), $to->get_id() );
				}
			}
		}

		return false;
	}

	/**
	 * Maybe translate a product property
	 *
	 * @since 1.0
	 *
	 * @param mixed  $value Property value.
	 * @param string $prop  Property name.
	 * @param string $lang  Language code.
	 * @return mixed Property value, possibly translated.
	 */
	public static function maybe_translate_property( $value, $prop, $lang ) {
		switch ( $prop ) {
			case 'image_id':
				$tr_value = ( $tr_value = pll_get_post( $value, $lang ) ) ? $tr_value : $value;
				break;

			case 'gallery_image_ids':
				$tr_value = array();
				foreach ( explode( ',', $value ) as $post_id ) {
					$tr_id = pll_get_post( $post_id, $lang );
					$tr_value[] = $tr_id ? $tr_id : $post_id;
				}
				$tr_value = implode( ',', $tr_value );
				break;

			case 'children':
			case 'upsell_ids':
			case 'cross_sell_ids':
				$data_store = PLLWC_Data_Store::load( 'product_language' );
				$tr_value = array();
				foreach ( $value as $id ) {
					if ( $tr_id = $data_store->get( $id, $lang ) ) {
						$tr_value[] = $tr_id;
					}
				}
				break;

			case 'default_attributes':
			case 'attributes':
				$tr_value = array();
				foreach ( $value as $k => $v ) {
					$tr_value[ $k ] = $v;

					switch ( gettype( $v ) ) {
						case 'string':
							if ( taxonomy_exists( $k ) ) {
								$terms = get_terms( array( 'taxonomy' => $k, 'slug' => $v, 'hide_empty' => false, 'lang' => '' ) ); // Don't use get_term_by filtered by language since WP 4.7
								if ( is_array( $terms ) && ( $term = reset( $terms ) ) && $tr_id = pll_get_term( $term->term_id, $lang ) ) {
									$term = get_term( $tr_id, $k );
									$tr_value[ $k ] = $term->slug;
								}
							}
							break;

						case 'object':
							if ( $v->is_taxonomy() && $terms = $v->get_terms() ) {
								$tr_ids = array();
								foreach ( $terms as $term ) {
									$tr_ids[] = pll_get_term( $term->term_id, $lang );
								}
								$v->set_options( $tr_ids );
							}
							break;
					}
				}
				break;

			default:
				$tr_value = $value;
				break;
		}

		/**
		 * Filter a property value before it is copied or synchronized
		 * but after it has been maybe translated
		 *
		 * @since 1.0
		 *
		 * @param mixed  $value Product property value.
		 * @param string $prop  Product property name.
		 * @param string $lang  Language code.
		 */
		return apply_filters( 'pllwc_translate_product_prop', $tr_value, $prop, $lang );
	}

	/**
	 * Translates taxonomy attributes
	 *
	 * @since 1.0
	 *
	 * @param array  $attributes Product attributes.
	 * @param string $lang       Language code.
	 * @return array
	 */
	protected function maybe_translate_attributes( $attributes, $lang ) {
		foreach ( $attributes as $tax => $value ) {
			if ( taxonomy_exists( $tax ) && $value ) {
				$terms = get_terms( $tax, array( 'slug' => $value, 'lang' => '' ) ); // Don't use get_term_by filtered by language since WP 4.7.
				if ( is_array( $terms ) && ( $term = reset( $terms ) ) && $tr_id = pll_get_term( $term->term_id, $lang ) ) {
					$term = get_term( $tr_id, $tax );
					$attributes[ $tax ] = $term->slug;
				}
			}
		}
		return $attributes;
	}

	/**
	 * Setups the js script (only on the products page)
	 *
	 * @since 0.1
	 */
	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( ! empty( $screen ) && 'post' === $screen->base && 'product' === $screen->post_type ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script( 'pllwc_product', plugins_url( '/js/product' . $suffix . '.js', PLLWC_FILE ), 0, PLLWC_VERSION, 1 );
		}
	}

	/**
	 * Ajax response for changing the language in the product language metabox
	 *
	 * @since 0.1
	 */
	public function product_lang_choice() {
		check_ajax_referer( 'pll_language', '_pll_nonce' );

		if ( isset( $_POST['post_id'], $_POST['lang'], $_POST['attributes'] ) ) {
			$post_id    = (int) $_POST['post_id'];
			$lang       = PLL()->model->get_language( sanitize_key( $_POST['lang'] ) );
			$attributes = array_map( 'sanitize_title', wp_unslash( $_POST['attributes'] ) );

			$x = new WP_Ajax_Response();

			// Attributes (taxonomies of select type)
			foreach ( wc_get_attribute_taxonomies() as $a ) {
				$taxonomy = wc_attribute_taxonomy_name( $a->attribute_name );
				if ( 'select' === $a->attribute_type && false !== $i = array_search( $taxonomy, $attributes ) ) {
					$out = '';
					$all_terms = get_terms( $taxonomy, array( 'orderby' => 'name', 'hide_empty' => 0, 'lang' => $lang->slug ) );

					if ( $all_terms ) {
						foreach ( $all_terms as $term ) {
							$out .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( has_term( absint( $term->term_id ), $taxonomy, $post_id ), true, false ) . '>' . $term->name . '</option>';
						}
					}

					$supplemental[ 'value-' . $i ] = $out;
				}
			}

			if ( ! empty( $supplemental ) ) {
				$x->Add( array( 'what' => 'attributes', 'supplemental' => $supplemental ) );
			}

			$x->send();
		}
	}

	/**
	 * Synchronize product ordering
	 *
	 * @since 1.0
	 *
	 * @param int   $id          Product id.
	 * @param array $menu_orders An array with product ids as key and menu_order as value.
	 */
	public function product_ordering( $id, $menu_orders ) {
		$language = $this->data_store->get_language( $id );
		foreach ( $menu_orders as $id => $order ) {
			if ( $this->data_store->get_language( $id ) === $language ) {
				foreach ( $this->data_store->get_translations( $id ) as $tr_id ) {
					if ( $id !== $tr_id ) {
						$this->data_store->save_product_ordering( $tr_id, $order );
					}
				}
			}
		}
	}

	/**
	 * Filter the products per language in autocomplete ajax searches
	 *
	 * @since 0.1
	 *
	 * @param array $products array with product ids as keys and names as values.
	 * @return array
	 */
	public function search_found_products( $products ) {
		// Either we are editing a product or an order
		if ( ! isset( $_REQUEST['pll_post_id'] ) || ! $lang = $this->data_store->get_language( (int) $_REQUEST['pll_post_id'] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification
			$lang = PLLWC_Admin::get_preferred_language();
		}

		foreach ( array_keys( $products ) as $id ) {
			if ( $this->data_store->get_language( $id ) !== $lang ) {
				unset( $products[ $id ] );
			}
		}

		return $products;
	}

	/**
	 * Fix search in Products list table
	 * Necessary since WC 3.3.1 as the query uses 'post__in'
	 * which is usually excluded from the language filter
	 *
	 * @since 1.0
	 *
	 * @param array  $excludes Query vars excluded from the language filter.
	 * @param object $query    WP Query object.
	 * @return array
	 */
	public function fix_products_search( $excludes, $query ) {
		if ( ! empty( $query->query['product_search'] ) ) {
			$excludes = array_diff( $excludes, array( 'post__in' ) );
		}
		return $excludes;
	}

	/**
	 * Filters wc_product_has_unique_sku
	 * Adds the language filter to the query from WC_Product_Data_Store_CPT::is_existing_sku()
	 * Code base: WC 3.0.5
	 *
	 * @since 0.7
	 *
	 * @param bool   $sku_found  True if the SKU is already associated to an existing product, false otherwise.
	 * @param int    $product_id Product ID.
	 * @param string $sku        Product SKU.
	 * @return bool
	 */
	public function unique_sku( $sku_found, $product_id, $sku ) {
		if ( $sku_found ) {
			$language = $this->data_store->get_language( $product_id );

			/**
			 * Filter the language used to filter wc_product_has_unique_sku
			 *
			 * @since 0.9
			 *
			 * @param object $language   Language.
			 * @param int    $product_id Product ID.
			 */
			$language = apply_filters( 'pllwc_language_for_unique_sku', $language, $product_id );

			if ( $language ) {
				return $this->data_store->is_existing_sku( $product_id, $sku, $language );
			}
		}
		return $sku_found;
	}

	/**
	 * Remove the German and Danish specific sanitization for titles
	 *
	 * @since 0.7.1
	 */
	public function remove_sanitize_title() {
		remove_filter( 'sanitize_title', array( PLL()->filters, 'sanitize_title' ), 10, 3 );
	}

	/**
	 * Add the German and Danish specific sanitization for titles
	 *
	 * @since 0.7.1
	 */
	public function add_sanitize_title() {
		add_filter( 'sanitize_title', array( PLL()->filters, 'sanitize_title' ), 10, 3 );
	}
}
