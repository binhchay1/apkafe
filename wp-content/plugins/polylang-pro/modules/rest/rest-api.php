<?php

/**
 * Setup the REST API endpoints and filters
 *
 * @since 2.2
 */
class PLL_REST_API {
	public $model;

	/**
	 * Constructor
	 *
	 * @since 2.2
	 *
	 * @param object $model Instance of PLL_Model
	 */
	public function __construct( &$model ) {
		$this->model = &$model;
		add_action( 'rest_api_init', array( $this, 'init' ) );
	}

	/**
	 * Init filters and new endpoints
	 *
	 * @since 2.2
	 */
	public function init() {
		$post_types = array_fill_keys( $this->model->get_translated_post_types(), array() );

		/**
		 * Filter post types and their options passed to PLL_Rest_Post contructor
		 *
		 * @since 2.2.1
		 *
		 * @param array $post_types An array of arrays with post types as keys and options as values
		 */
		$post_types = apply_filters( 'pll_rest_api_post_types', $post_types );
		$this->post = new PLL_REST_Post( $this->model, $post_types );

		$taxonomies = array_fill_keys( $this->model->get_translated_taxonomies(), array() );

		/**
		 * Filter post types and their options passed to PLL_Rest_Term constructor
		 *
		 * @since 2.2.1
		 *
		 * @param array $taxonomies An array of arrays with taxonomies as keys and options as values
		 */
		$taxonomies = apply_filters( 'pll_rest_api_taxonomies', $taxonomies );
		$this->term = new PLL_REST_Term( $this->model, $taxonomies );

		register_rest_route(
			'pll/v1',
			'/languages',
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_languages' ),
			)
		);
	}

	/**
	 * Returns the list of languages
	 *
	 * @since 2.2
	 *
	 * @return array
	 */
	public function get_languages() {
		$languages = $this->model->get_languages_list();

		// Remove properties which don't make sense to be exposed
		foreach ( $languages as $k => $lang ) {
			foreach ( array( 'term_group', 'description', 'parent', 'flag', 'filter' ) as $prop ) {
				unset( $languages[ $k ]->$prop );
			}
		}

		return $languages;
	}
}
