<?php

/**
 * Setups a translatable object language model when managed with a custom post type
 *
 * @since 1.0
 */
abstract class PLLWC_Translated_Object_Language_CPT extends PLLWC_Object_Language_CPT {

	/**
	 * Get the translations group taxonomy name
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_tax_translations() {
		return 'post_translations';
	}

	/**
	 * Save product translations
	 *
	 * @since 1.0
	 *
	 * @param array $arr An associative array of translations with language code as key and product id as value.
	 */
	public function save_translations( $arr ) {
		pll_save_post_translations( $arr );
	}

	/**
	 * Returns an array of translations of a product
	 *
	 * @since 1.0
	 *
	 * @param int $id Product id.
	 * @return array An associative array of translations with language code as key and translation product id as value.
	 */
	public function get_translations( $id ) {
		return pll_get_post_translations( $id );
	}

	/**
	 * Among the product and its translations, returns the id of the product which is in the language represented by $lang
	 *
	 * @since 1.0
	 *
	 * @param int    $id   Product id.
	 * @param string $lang Optional language code, defaults to the current language.
	 * @return int|false|null Product id of the translation if exists, false otherwise, null if the current language is not defined yet.
	 */
	public function get( $id, $lang = '' ) {
		return pll_get_post( $id, $lang );
	}
}
