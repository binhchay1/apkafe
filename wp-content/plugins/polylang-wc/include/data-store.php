<?php

/**
 * As our language data stores don't implement the WC_Object_Data_Store_Interface
 * interface, it appears risky to use WC_Data_Store directly, so it has been thought
 * to be better to create our own class which can be used in a similar way.
 *
 * @since 1.0
 */
class PLLWC_Data_Store {

	private static $stores = array(
		'order_language'   => 'PLLWC_Order_Language_CPT',
		'product_language' => 'PLLWC_Product_Language_CPT',
	);

	/**
	 * Loads a data store
	 *
	 * @since 1.0
	 *
	 * @param string $object_type Identifier for the data store, typically 'order_language' or 'product_language'.
	 * @return object
	 */
	public static function load( $object_type ) {
		/**
		 * Filters the list of available data stores
		 *
		 * @since 1.0
		 *
		 * @param array $stores Available data stores.
		 */
		self::$stores = apply_filters( 'pllwc_data_stores', self::$stores );

		$store = self::$stores[ $object_type ];

		if ( class_exists( $store ) ) {
			return new $store();
		}
	}
}
