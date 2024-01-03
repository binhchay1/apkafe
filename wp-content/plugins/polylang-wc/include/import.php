<?php

/**
 * A class to import languages and translations of products from CSV files
 *
 * @since 0.8
 */
class PLLWC_Import {
	protected $data_store;

	/**
	 * Constructor
	 *
	 * @since 0.8
	 */
	public function __construct() {
		$this->data_store = PLLWC_Data_Store::load( 'product_language' );

		add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( $this, 'default_columns' ) );
		add_filter( 'woocommerce_csv_product_import_mapping_options', array( $this, 'mapping_options' ), 1 );
		add_action( 'woocommerce_product_import_inserted_product_object', array( $this, 'inserted_product_object' ), 10, 2 );

		add_action( 'woocommerce_product_importer_before_set_parsed_data', array( $this, 'before_set_parsed_data' ), 10, 2 );
		add_action( 'woocommerce_product_import_before_import', array( $this, 'set_language' ) );
		add_action( 'woocommerce_product_import_before_process_item', array( $this, 'set_language' ) );

		add_filter( 'woocommerce_product_importer_formatting_callbacks', array( $this, 'formatting_callbacks' ), 10, 2 );
	}

	/**
	 * Add language and translation group to default columns
	 *
	 * @since 0.8
	 *
	 * @param array $mappings Importer columns mappings.
	 * @return array
	 */
	public function default_columns( $mappings ) {
		return array_merge(
			$mappings,
			array(
				__( 'Language', 'polylang' )             => 'language',
				__( 'Translation group', 'polylang-wc' ) => 'translations',
			)
		);
	}

	/**
	 * Add language and translation group to mapping options
	 * Between "Description" and "Date sale price starts"
	 *
	 * @since 0.8
	 *
	 * @param array $options Mapping options.
	 * @return array
	 */
	public function mapping_options( $options ) {
		if ( $n = array_search( 'price', array_keys( $options ) ) ) {
			$end     = array_slice( $options, $n );
			$options = array_slice( $options, 0, $n );
		}

		$options = array_merge(
			$options,
			array(
				'language'     => __( 'Language', 'polylang' ),
				'translations' => __( 'Translation group', 'polylang-wc' ),
			)
		);

		return isset( $end ) ? array_merge( $options, $end ) : $options;
	}

	/**
	 * Import language and translation group
	 *
	 * @since 0.8
	 *
	 * @param object $object Product object.
	 * @param array  $data   Data in a row of the CSV file.
	 */
	public function inserted_product_object( $object, $data ) {
		$id = $object->get_id();

		if ( isset( $data['language'] ) && $language = PLL()->model->get_language( $data['language'] ) ) {
			if ( isset( $data['translations'] ) ) {
				$this->set_translation_group( $id, $data );
			}

			// Shared slug
			if ( ! empty( $data['name'] ) ) {
				$object->set_slug( $data['name'] ); // WooCommerce keeps the slug empty in the product object.
				$object->save();
			}
		}
	}

	/**
	 * Assigns translations group
	 *
	 * @since 0.8
	 *
	 * @param int   $id   Product id.
	 * @param array $data Data in a row of the CSV file.
	 */
	public function set_translation_group( $id, $data ) {
		$taxonomy = 'post_translations';
		$group = $data['translations'];
		$term = get_term_by( 'name', $group, $taxonomy );

		if ( empty( $term ) ) {
			$translations[ $data['language'] ] = $id;
			$term = wp_insert_term( $group, $taxonomy, array( 'description' => serialize( $translations ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
			if ( ! is_wp_error( $term ) ) {
				wp_set_object_terms( $id, $term['term_id'], $taxonomy );
			}
		} else {
			$translations = unserialize( $term->description ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
			$translations[ $data['language'] ] = $id;
			$this->data_store->save_translations( $translations );
		}
	}

	/**
	 * Setups filters for the import ( first action used during the import )
	 * Sets the preferred language when parsing data for terms to be created in the right language
	 *
	 * @since 0.8
	 *
	 * @param array $row         Row values.
	 * @param array $mapped_keys Mapped keys.
	 */
	public function before_set_parsed_data( $row, $mapped_keys ) {
		// Add filters which must be used only during the import
		add_filter( 'get_terms_args', array( $this, 'get_terms_args' ), 5 ); // Before Polylang.
		add_filter( 'woocommerce_get_product_id_by_sku', array( $this, 'get_product_id_by_sku' ), 10, 2 );
		add_filter( 'pllwc_language_for_unique_sku', array( $this, 'language_for_unique_sku' ) );

		add_filter( 'pllwc_copy_post_metas', '__return_empty_array', 999 ); // Avoids _children, _crosssell_ids, etc.. to be wrongly overwritten.

		// Preferred language for terms
		$col = array_search( 'language', $mapped_keys );
		if ( ! empty( $col ) && ! empty( $row[ $col ] ) && $language = PLL()->model->get_language( $row[ $col ] ) ) {
			PLL()->pref_lang = $language;
		}
	}

	/**
	 * Saves the language of the current item being imported for future use
	 *
	 * @since 0.8
	 *
	 * @param array $data Data in a row of the CSV file.
	 */
	public function set_language( $data ) {
		if ( isset( $data['language'] ) && $language = PLL()->model->get_language( $data['language'] ) ) {
			PLL()->pref_lang = $language;
		}
	}

	/**
	 * Filters get_terms according to the language of the current item
	 * This allows get_term_by (slug or name) to return the term in the correct language.
	 *
	 * @since 0.8
	 *
	 * @param array $args WP_Term_Query arguments.
	 * @return array
	 */
	public function get_terms_args( $args ) {
		if ( ! empty( PLL()->pref_lang ) ) {
			$args['lang'] = PLL()->pref_lang->slug;
		}
		return $args;
	}

	/**
	 * When searching a product id by sku, returns the product id in the current language
	 *
	 * @since 0.9
	 *
	 * @param int    $product_id Product id found by WooCommerce.
	 * @param string $sku        Product SKU.
	 * @return int
	 */
	public function get_product_id_by_sku( $product_id, $sku ) {
		global $wpdb;

		if ( $sku && ! empty( PLL()->pref_lang ) ) {
			$product_id = $this->data_store->get_product_id_by_sku( $sku, PLL()->pref_lang->slug );
		}

		return $product_id;
	}

	/**
	 * Returns the language to use when searching if a sku is unique
	 *
	 * @since 0.9
	 *
	 * @return object PLL_Language object.
	 */
	public function language_for_unique_sku() {
		return PLL()->pref_lang;
	}

	/**
	 * Replace the categories and tags parsing callback by our own callbacks
	 *
	 * @since 1.0.3
	 *
	 * @param array  $callbacks Array of parsing callbacks.
	 * @param object $importer  WC_Product_CSV_Importer object.
	 * @return array
	 */
	public function formatting_callbacks( $callbacks, $importer ) {
		if ( false !== $key = array_search( 'category_ids', $importer->get_mapped_keys() ) ) {
			$callbacks[ $key ] = array( $this, 'parse_categories_field' );
		}

		if ( false !== $key = array_search( 'tag_ids', $importer->get_mapped_keys() ) ) {
			$callbacks[ $key ] = array( $this, 'parse_tags_field' );
		}

		return $callbacks;
	}

	/**
	 * Parse a category field from a CSV.
	 * Categories are separated by commas and subcategories are "parent > subcategory".
	 * Mainly a copy of WC_Product_CSV_Importer::parse_categories_field() (WC 3.4.4)
	 * using our own term_exists method.
	 *
	 * @since 1.0.3
	 *
	 * @param string $value Field value.
	 * @return array of arrays with "parent" and "name" keys.
	 */
	public function parse_categories_field( $value ) {
		if ( empty( $value ) ) {
			return array();
		}

		$row_terms  = $this->explode_values( $value );
		$categories = array();

		foreach ( $row_terms as $row_term ) {
			$parent = null;
			$_terms = array_map( 'trim', explode( '>', $row_term ) );
			$total  = count( $_terms );

			foreach ( $_terms as $index => $_term ) {
				// Check if category exists. Parent must be empty string or null if doesn't exists.
				$term_id = PLL()->model->term_exists( $_term, 'product_cat', $parent, PLL()->pref_lang );

				if ( empty( $term_id ) ) {
					// Worst hack ever, for shared slug
					$_POST['term_lang_choice'] = PLL()->pref_lang->slug;
					$_REQUEST['_pll_nonce'] = wp_create_nonce( 'pll_language' );

					$term = wp_insert_term( $_term, 'product_cat', array( 'parent' => intval( $parent ) ) );

					if ( is_wp_error( $term ) ) {
						break; // We cannot continue if the term cannot be inserted.
					}

					$term_id = $term['term_id'];
				}

				// Only requires assign the last category.
				if ( ( 1 + $index ) === $total ) {
					$categories[] = $term_id;
				} else {
					// Store parent to be able to insert or query categories based in parent ID.
					$parent = $term_id;
				}
			}
		}

		return $categories;
	}

	/**
	 * Parse a tag field from a CSV.
	 * Mainly a copy of WC_Product_CSV_Importer::parse_tags_field() (WC 3.4.4)
	 * using a hack to share the slug
	 *
	 * @since 1.0.3
	 *
	 * @param  string $value Field value.
	 * @return array
	 */
	public function parse_tags_field( $value ) {
		if ( empty( $value ) ) {
			return array();
		}
		$names = $this->explode_values( $value );
		$tags  = array();
		foreach ( $names as $name ) {
			$term = get_term_by( 'name', $name, 'product_tag' );
			if ( ! $term || is_wp_error( $term ) ) {
				// Worst hack ever, for shared slug
				$_POST['term_lang_choice'] = PLL()->pref_lang->slug;
				$_REQUEST['_pll_nonce'] = wp_create_nonce( 'pll_language' );

				$term = (object) wp_insert_term( $name, 'product_tag' );
			}
			if ( ! is_wp_error( $term ) ) {
				$tags[] = $term->term_id;
			}
		}
		return $tags;
	}

	/**
	 * Explode CSV cell values using commas by default, and handling escaped separators.
	 * Exact copy of WC_Product_Importer::explode_values (WC 3.4.4)
	 *
	 * @since  1.0.3
	 *
	 * @param  string $value Value to explode.
	 * @return array
	 */
	protected function explode_values( $value ) {
		$value  = str_replace( '\\,', '::separator::', $value );
		$values = explode( ',', $value );
		$values = array_map( array( $this, 'explode_values_formatter' ), $values );

		return $values;
	}

	/**
	 * Remove formatting and trim each value.
	 * Exact copy of WC_Product_Importer::explode_values_formatter (WC 3.4.4)
	 *
	 * @since  1.0.3
	 *
	 * @param  string $value Value to format.
	 * @return string
	 */
	protected function explode_values_formatter( $value ) {
		return trim( str_replace( '::separator::', ',', $value ) );
	}
}
