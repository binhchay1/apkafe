<?php

/**
 * Expose language and translations in the REST API
 *
 * @since 1.1
 */
class PLLWC_REST_Product extends PLL_REST_Translated_Object {

	/**
	 * Constructor
	 *
	 * @since 1.1
	 */
	public function __construct() {
		// FIXME Backward compatibility with Polylang < 2.6
		$instance = version_compare( POLYLANG_VERSION, '2.6-dev', '<' ) ? PLL()->model : PLL()->rest_api;

		parent::__construct( $instance, array( 'product' => array( 'filters' => false ) ) );

		$this->type = 'post';
		$this->id   = 'ID';

		$this->data_store = PLLWC_Data_Store::load( 'product_language' );
		add_filter( 'woocommerce_rest_product_object_query', array( $this, 'query' ), 10, 2 );
		add_filter( 'get_terms_args', array( $this, 'get_terms_args' ) ); // Before Auto translate
	}

	/**
	 * Returns the object language
	 *
	 * @since 1.1
	 *
	 * @param array $object Product array.
	 * @return string
	 */
	public function get_language( $object ) {
		return $this->data_store->get_language( $object['id'] );
	}

	/**
	 * Sets the object language
	 *
	 * @since 1.1
	 *
	 * @param string $lang   Language code.
	 * @param object $object Instance of WC_Product.
	 * @return bool
	 */
	public function set_language( $lang, $object ) {
		if ( $object instanceof WC_Product ) {
			$this->data_store->set_language( $object->get_id(), $lang );
		} else {
			parent::set_language( $lang, $object );
		}
		return true;
	}

	/**
	 * Returns the object translations
	 *
	 * @since 1.1
	 *
	 * @param array $object Product array.
	 * @return array
	 */
	public function get_translations( $object ) {
		return $this->data_store->get_translations( $object['id'] );
	}

	/**
	 * Save translations
	 *
	 * @since 1.1
	 *
	 * @param array  $translations Array of translations with language codes as keys and object ids as values.
	 * @param object $object       Instance of WC_Product.
	 * @return bool
	 */
	public function save_translations( $translations, $object ) {
		if ( $object instanceof WC_Product ) {
			$translations[ $this->data_store->get_language( $object->get_id() ) ] = $object->get_id();
			$this->data_store->save_translations( $translations );
		} else {
			parent::save_translations( $translations, $object );
		}
		return true;
	}

	/**
	 * Deactivate Auto translate to allow queries of attribute terms in the right language
	 *
	 * @since 1.1
	 *
	 * @param array $args WP_Term_Query arguments.
	 * @return array
	 */
	public function get_terms_args( $args ) {
		if ( ! empty( $args['include'] ) ) {
			$args['lang'] = '';
		}
		return $args;
	}
}
