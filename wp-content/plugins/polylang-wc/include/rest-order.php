<?php

/**
 * Expose language in the REST API
 *
 * @since 1.1
 */
class PLLWC_REST_Order extends PLL_REST_Translated_Object {

	/**
	 * Constructor
	 *
	 * @since 1.1
	 */
	public function __construct() {
		// FIXME Backward compatibility with Polylang < 2.6
		$instance = version_compare( POLYLANG_VERSION, '2.6-dev', '<' ) ? PLL()->model : PLL()->rest_api;

		parent::__construct( $instance, array( 'shop_order' => array( 'filters' => false, 'translations' => false ) ) );

		$this->data_store = PLLWC_Data_Store::load( 'order_language' );
		add_filter( 'woocommerce_rest_shop_order_object_query', array( $this, 'query' ), 10, 2 );
	}

	/**
	 * Returns the object language
	 *
	 * @since 1.1
	 *
	 * @param array $object Order array.
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
	 * @param object $object Instance of WC_Order.
	 * @return bool
	 */
	public function set_language( $lang, $object ) {
		if ( $object instanceof WC_Order ) {
			$this->data_store->set_language( $object->get_id(), $lang );
		}
		return true;
	}
}
