<?php

namespace ahrefs\AhrefsSeo\Options;

use ahrefs\AhrefsSeo\Ahrefs_Seo;
use ahrefs\AhrefsSeo\Helper_Content;
/**
 * Options class.
 *
 * @since 0.9.4
 */
class Option_Post extends Option {

	const OPTION_BASE = 'ahrefs-seo-options-p-';
	// from older versions.
	const OPTION_DISABLED_PAGES   = 'ahrefs-seo-content-disabled-pages';
	const OPTION_DISABLED_POSTS   = 'ahrefs-seo-content-disabled-posts';
	const OPTION_ENABLED_PRODUCTS = 'ahrefs-seo-content-enabled-products';
	const OPTION_CPT_LIST         = 'ahrefs-seo-content-cpt-list';
	const OPTION_POSTS_CAT        = 'ahrefs-seo-content-posts-cat';
	const OPTION_PAGES_CAT        = 'ahrefs-seo-content-pages-cat';
	const OPTION_PRODUCTS_CAT     = 'ahrefs-seo-content-products-cat';
	/** @var string */
	protected $post_type;
	/** @var string[]|null */
	protected $taxonomies = null;
	/** @var string|null */
	protected $current_tax = null;
	/** @var array<string, array> Taxonomy and taxonomy values of enabled items. */
	protected $tax_values;
	/** @var bool Include/exclude by individual page ID, not by taxonomy. */
	protected $use_individual_id_mode = false;
	/** @var int[] Included ID list, all others are excluded by default. */
	protected $id_values;
	/**
	 * Constructor
	 *
	 * @param string $post_type Post type.
	 */
	public function __construct( $post_type ) {
		$this->post_type              = $post_type;
		$this->use_individual_id_mode = 'page' === $post_type;
		$this->load_options();
	}
	/**
	 * This option make sense: post type exists.
	 *
	 * @param string $post_type Post type.
	 * @return bool
	 */
	public static function exists( $post_type ) {
		return post_type_exists( $post_type );
	}
	/**
	 * Render view with options
	 *
	 * @return void
	 */
	public function render_view() {
		$type = get_post_type_object( $this->post_type );
		if ( self::exists( $this->post_type ) && ! is_null( $type ) ) {
			$nonce_name = $this->get_var_name( 'nonce' );
			wp_nonce_field( $nonce_name, $nonce_name );
			$title    = $type->label;
			$template = $this->has_sub_options() ? 'options/scope/option-post-section' : 'options/scope/option-post-single';
			Ahrefs_Seo::get()->get_view()->show_part(
				$template,
				[
					'title'             => $title,
					'selected_taxonomy' => $this->current_tax,
					'is_enabled'        => $this->is_enabled,
					'var_enabled_name'  => $this->get_var_name( 'enabled' ),
					'var_name'          => $this->get_var_name(),
					'taxonomies_list'   => $this->get_taxonomies_list(),
					'tax_values'        => $this->tax_values,
					'id_mode'           => $this->use_individual_id_mode,
					'id_values'         => $this->id_values,
					'is_post'           => true,
				]
			);
		}
	}
	/**
	 * The view has a section with options vs single option only
	 *
	 * @return bool
	 */
	public function has_sub_options() {
		return ! empty( $this->current_tax ) || $this->use_individual_id_mode;
	}
	/**
	 * Get options hash if enabled.
	 *
	 * @return string
	 */
	public function get_options_hash() {
		return ! $this->is_enabled ? '' : sprintf( '%s-%s-%s*%s#%s', parent::get_options_hash(), $this->post_type, isset( $this->current_tax ) ? $this->current_tax : '', wp_json_encode( $this->tax_values ), wp_json_encode( $this->id_values ) );
	}
	/**
	 * Load options from request
	 *
	 * @return bool Success.
	 */
	public function load_options_from_request() {
		$nonce_name = $this->get_var_name( 'nonce' );
		if ( isset( $_POST[ $nonce_name ] ) && check_admin_referer( $nonce_name, $nonce_name ) ) {
			$var_enabled_name = $this->get_var_name( 'enabled' );
			$this->is_enabled = isset( $_POST[ $var_enabled_name ] ) && ! empty( $_POST[ $var_enabled_name ] );
			$this->tax_values = [];
			$this->id_values  = [];
			if ( $this->has_sub_options() ) {
				if ( $this->use_individual_id_mode ) {
					$tax_var = $this->get_var_name() . '___ID';
					$list    = isset( $_POST[ $tax_var ] ) && ! empty( $_POST[ $tax_var ] ) ? sanitize_text_field( wp_unslash( $_POST[ $tax_var ] ) ) : '';
					$values  = array_map( 'absint', explode( ' ', $list ) ); // only numeric values allowed.
					$this->id_values = $values;
				} else {
					$tax_var = $this->get_var_name() . '___' . $this->get_current_taxonomy();
					$list    = isset( $_POST[ $tax_var ] ) && ! empty( $_POST[ $tax_var ] ) ? sanitize_text_field( wp_unslash( $_POST[ $tax_var ] ) ) : '';
					$values  = array_map( 'absint', explode( ' ', $list ) ); // only numeric values allowed.
					$this->tax_values = [ $this->get_current_taxonomy() => $values ];
				}
			}
			$this->save_options();
			return true;
		}
		return false;
	}
	/**
	 * Import options from older plugin versions.
	 *
	 * @return void
	 */
	public function import_from_older_version() {
		$this->is_enabled = false; // off by default.
		$this->tax_values = [];
		switch ( $this->post_type ) {
			case 'post':
				$this->is_enabled  = empty( get_option( self::OPTION_DISABLED_POSTS ) );
				$this->current_tax = 'category';
				$taxes             = get_option( self::OPTION_POSTS_CAT, null );
				if ( is_null( $taxes ) ) { // fresh installation.
					$this->tax_values['category'] = Helper_Content::get()->get_all_term_ids( 'category' );
				} else {
					$this->tax_values['category'] = is_array( $taxes ) ? $taxes : [];
				}
				$this->save_options();
				delete_option( self::OPTION_DISABLED_POSTS );
				delete_option( self::OPTION_POSTS_CAT );
				break;
			case 'page':
				$disabled         = get_option( self::OPTION_DISABLED_PAGES, null );
				$pages            = get_option( self::OPTION_PAGES_CAT, null );
				$this->is_enabled = empty( $disabled );
				$this->id_values  = is_array( $pages ) ? array_map( 'absint', $pages ) : [];
				if ( is_null( $disabled ) && is_null( $pages ) ) { // fresh instal, check nothing by default.
					$this->is_enabled = false;
				}
				$this->save_options();
				delete_option( self::OPTION_DISABLED_PAGES );
				delete_option( self::OPTION_PAGES_CAT );
				break;
			case 'product':
				$value             = get_option( self::OPTION_ENABLED_PRODUCTS, null );
				$this->current_tax = 'product_cat';
				$this->is_enabled  = is_null( $value ) || ! empty( $value ); // turn on by default or respect existing value.
				$taxes                           = get_option( self::OPTION_PRODUCTS_CAT, null );
				$this->tax_values['product_cat'] = is_array( $taxes ) ? $taxes : [];
				$this->save_options();
				if ( $this->is_enabled && Option_Taxonomy::exists( 'product_cat' ) ) {
					( new Option_Taxonomy( 'product_cat', '' ) )->set_enabled();
				}
				delete_option( self::OPTION_ENABLED_PRODUCTS );
				delete_option( self::OPTION_PRODUCTS_CAT );
				break;
			default:
				$values = get_option( self::OPTION_CPT_LIST, null );
				if ( is_array( $values ) ) {
					$this->is_enabled = in_array( $this->post_type, $values, true );
				}
		}
	}
	/**
	 * Save options to DB
	 *
	 * @return self
	 */
	protected function save_options() {
		$options = [
			'current_tax' => $this->current_tax,
			'is_enabled'  => $this->is_enabled,
			'tax_values'  => $this->tax_values,
			'id_values'   => $this->id_values,
		];
		update_option( $this->get_option_name(), wp_json_encode( $options ) );
		return $this;
	}
	/**
	 * Load options from DB
	 *
	 * @return self
	 */
	public function load_options() {
		$value = get_option( $this->get_option_name(), null );
		if ( is_null( $value ) ) {
			$this->import_from_older_version();
		} else {
			$options          = json_decode( (string) $value, true );
			$options          = empty( $options ) ? [] : $options;
			$this->is_enabled = (bool) ( isset( $options['is_enabled'] ) ? $options['is_enabled'] : 'post' === $this->post_type );
			// enabled for 'post' by default.
			$this->tax_values = (array) ( isset( $options['tax_values'] ) ? $options['tax_values'] : [] );
			$this->id_values  = array_map( 'absint', (array) ( isset( $options['id_values'] ) ? $options['id_values'] : [] ) );
		}
		$this->current_tax = $this->get_default_tax();
		return $this;
	}
	/**
	 * Set enabled taxonomy values. Set post type enabled too.
	 *
	 * @param string         $taxonomy Taxonomy.
	 * @param string[]|int[] $values Tax values.
	 * @return self
	 */
	public function enable_tax_values( $taxonomy, array $values ) {
		$this->load_options();
		$this->tax_values[ $taxonomy ] = array_map( 'absint', $values );
		$this->save_options();
		return $this;
	}
	/**
	 * Get default (predefined) taxonomy
	 *
	 * @return string
	 */
	protected function get_default_tax() {
		switch ( $this->post_type ) {
			case 'post':
				return 'category';
			case 'product':
				return 'product_cat';
		}
		return '';
	}
	/**
	 * Get name for option save/load.
	 *
	 * @return string Option name.
	 */
	protected function get_option_name() {
		return sanitize_html_class( $this::OPTION_BASE . $this->post_type );
	}
	/**
	 * Return name of var for using in render and load from request option
	 *
	 * @param string $suffix Suffix for the variable name.
	 * @return string
	 */
	protected function get_var_name( $suffix = '' ) {
		return sanitize_file_name( 'ahpost_' . $this->post_type . '_' . $suffix );
	}
	/**
	 * Get taxonomies list for the current post type
	 *
	 * @return string[]
	 */
	public function get_taxonomies_list() {
		if ( ! is_array( $this->taxonomies ) ) {
			$this->taxonomies = [];
			/** @var \WP_Taxonomy[] $list */
			$list = get_object_taxonomies( $this->post_type, 'objects' );
			foreach ( $list as $item ) {
				if ( $item->public && $item->show_ui ) {
					$this->taxonomies[ $item->name ] = $item->label;
				}
			}
		}
		return $this->taxonomies;
	}
	/**
	 * Get current taxonomy for this post type
	 *
	 * @return string|null Taxonomy for filtering or null.
	 */
	public function get_current_taxonomy() {
		return $this->current_tax;
	}
	/**
	 * Get post type
	 *
	 * @return string
	 */
	public function get_post_type() {
		return $this->post_type;
	}
	/**
	 * Get selected taxonomy values
	 *
	 * @param string $taxonomy Taxonomy.
	 * @return int[]|string[]|null Selected values.
	 */
	public function get_tax_values( $taxonomy ) {
		return isset( $this->tax_values[ $taxonomy ] ) && is_array( $this->tax_values[ $taxonomy ] ) ? $this->tax_values[ $taxonomy ] : null;
	}
	/**
	 * Get selected id values
	 *
	 * @return int[] Selected ID values.
	 */
	public function get_id_values() {
		return $this->id_values;
	}
	/**
	 * Filter by individual page ID used, instead of filter by taxonomy.
	 *
	 * @return bool
	 */
	public function is_id_mode_used() {
		return $this->use_individual_id_mode;
	}
	/**
	 * Add individual item by ID.
	 *
	 * @param int[] $page_ids Page ID.
	 * @return Option_Post
	 */
	public function add_by_id( array $page_ids ) {
		if ( $this->use_individual_id_mode ) {
			$this->id_values = array_unique( array_merge( $this->id_values, $page_ids ) );
		}
		$this->save_options();
		return $this;
	}
	/**
	 * Remove individual item by ID.
	 *
	 * @param int[] $page_ids Page ID.
	 * @return Option_Post
	 */
	public function remove_by_id( array $page_ids ) {
		if ( $this->use_individual_id_mode ) {
			$this->id_values = array_diff( $this->id_values, $page_ids );
			$this->save_options();
		}
		return $this;
	}
}