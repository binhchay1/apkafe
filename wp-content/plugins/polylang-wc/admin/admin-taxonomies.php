<?php

/**
 * Manages Woocommerce taxonomies
 *
 * @since 0.1
 */
class PLLWC_Admin_Taxonomies {

	/**
	 * Constructor
	 *
	 * @since 0.1
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 11 ); // After Woocommerce
	}

	/**
	 * Setups actions and filters
	 *
	 * @since 0.1
	 */
	public function init() {
		add_filter( 'pll_copy_term_metas', array( $this, 'get_metas_to_copy' ), 10, 5 );

		if ( PLL()->options['media_support'] ) {
			add_filter( 'pll_translate_term_meta', array( $this, 'translate_meta' ), 10, 3 );

			// WooCommerce (2.5.5) inconsistently uses created_term and edit_term so we can't use pll_save_term
			add_action( 'created_product_cat', array( $this, 'saved_product_cat' ), 999 );
			add_action( 'edited_product_cat', array( $this, 'saved_product_cat' ), 999 );
		}

		// Attributes
		add_action( 'create_term', array( $this, 'create_attribute_term' ), 10, 3 );

		// FIXME would be better if Woocomerce would give access to its object
		// FIXME would be even better if filters could allow to pre-populate term meta the same way 'taxonomy_parent_dropdown_args' does
		pll_remove_anonymous_object_filter( 'product_cat_add_form_fields', array( 'WC_Admin_Taxonomies', 'add_category_fields' ) );
		add_action( 'product_cat_add_form_fields', array( $this, 'add_category_fields' ) );

		add_action( 'admin_print_footer_scripts', array( $this, 'admin_print_footer_scripts' ) );
	}

	/**
	 * Adds metas to copy or synchronize
	 *
	 * @since 1.0
	 *
	 * @param array  $to_copy List of term metas names.
	 * @param bool   $sync    True if it is synchronization, false if it is a copy.
	 * @param int    $from    Id of the term from which we copy informations.
	 * @param int    $to      Id of the term to which we paste informations.
	 * @param string $lang    Language slug.
	 */
	public function get_metas_to_copy( $to_copy, $sync, $from, $to, $lang ) {
		$term = get_term( $from );

		// Product categories
		if ( 'product_cat' === $term->taxonomy ) {
			$_to_copy = array(
				'display_type',
				'thumbnail_id',
			);

			if ( ! $sync ) {
				$_to_copy[] = 'order';
			}

			$to_copy = array_merge( $to_copy, $_to_copy );
		}

		// Add attributes order
		if ( ! $sync && 0 === strpos( $term->taxonomy, 'pa_' ) ) {
			$metas = get_term_meta( $from );

			if ( ! empty( $metas ) ) {
				foreach ( array_keys( $metas ) as $key ) {
					if ( 0 === strpos( $key, 'order_' ) ) {
						$to_copy[] = $key;
					}
				}
			}
		}

		/**
		 * Filter the custom fields to copy or synchronize
		 *
		 * @since 0.7
		 *
		 * @param array  $to_copy list of custom fields names.
		 * @param bool   $sync    true if it is synchronization, false if it is a copy.
		 * @param int    $from    id of the term from which we copy informations.
		 * @param int    $to      id of the term to which we paste informations.
		 * @param string $lang    language slug.
		 */
		return apply_filters( 'pllwc_copy_term_metas', $to_copy, $sync, $from, $to, $lang );
	}

	/**
	 * Translates the thumbnail id
	 *
	 * @since 1.0
	 *
	 * @param mixed  $value Meta value.
	 * @param string $key   Meta key.
	 * @param string $lang  Language of target.
	 */
	public function translate_meta( $value, $key, $lang ) {
		if ( 'thumbnail_id' === $key ) {
			$value = ( $tr_value = pll_get_post( $value, $lang ) ) ? $tr_value : $value;
		}
		return $value;
	}

	/**
	 * Maybe fix the language of the product cat image
	 * Needed because if the image was just uploaded,
	 * it is assigned the preferred language instead of the current language
	 *
	 * @since 0.1
	 *
	 * @param int $term_id Term id.
	 */
	public function saved_product_cat( $term_id ) {
		$thumbnail_id = get_term_meta( $term_id, 'thumbnail_id', true );

		$lang = pll_get_term_language( $term_id );

		if ( $thumbnail_id && pll_get_post_language( $thumbnail_id ) !== $lang ) {
			$translations = pll_get_post_translations( $thumbnail_id );

			if ( ! empty( $translations[ $lang ] ) ) {
				update_term_meta( $term_id, 'thumbnail_id', $translations[ $lang ] ); // Take the translation in the right language
			} else {
				pll_set_post_language( $thumbnail_id, $lang ); // Or fix the language
			}
		}
	}

	/**
	 * Saves the language of an attribute term when created from the product metabox.
	 *
	 * @since 1.0
	 *
	 * @param int    $term_id  Term id.
	 * @param int    $tt_id    Term taxonomy id.
	 * @param string $taxonomy Taxonomy name.
	 */
	public function create_attribute_term( $term_id, $tt_id, $taxonomy ) {
		if ( doing_action( 'wp_ajax_woocommerce_add_new_attribute' ) && ! empty( $_POST['pll_post_id'] ) && 0 === strpos( $taxonomy, 'pa_' ) ) {
			check_ajax_referer( 'add-attribute', 'security' );
			$data_store = PLLWC_Data_Store::load( 'product_language' );

			$lang = $data_store->get_language( (int) $_POST['pll_post_id'] );
			pll_set_term_language( $term_id, $lang );
		}
	}

	/**
	 * Rewrites WC_Admin_Taxonomies::add_category_fields to populate metas when creating a new translation
	 *
	 * @since 0.1
	 */
	public function add_category_fields() {
		$wc_admin_tax = pll_get_anonymous_object_from_filter( 'product_cat_edit_form_fields', array( 'WC_Admin_Taxonomies', 'edit_category_fields' ), 10 );

		if ( isset( $_GET['taxonomy'], $_GET['from_tag'], $_GET['new_lang'] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification
			$term = get_term( (int) $_GET['from_tag'], 'product_cat' );  // phpcs:ignore WordPress.Security.NonceVerification
		}

		if ( ! empty( $term ) ) {
			$wc_admin_tax->edit_category_fields( $term );
		} else {
			$wc_admin_tax->add_category_fields();
		}
	}

	/**
	 * Filter the media list when adding an image to a product category
	 *
	 * @since 0.2
	 */
	public function admin_print_footer_scripts() {
		$screen = get_current_screen();
		if ( ! empty( $screen ) && in_array( $screen->base, array( 'edit-tags', 'term' ) ) && 'product_cat' === $screen->taxonomy ) {
			?>
			<script type="text/javascript">
				if (typeof jQuery != 'undefined') {
					(function( $ ){
						$.ajaxPrefilter(function ( options, originalOptions, jqXHR ) {
							if ( options.data.indexOf( 'action=query-attachments' ) > 0 ) {
								options.data = 'lang=' + $( '#term_lang_choice' ).val() + '&' + options.data;
							}
						});
					})( jQuery )
				}
			</script>
			<?php
		}
	}
}
