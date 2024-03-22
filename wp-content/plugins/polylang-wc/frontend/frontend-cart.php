<?php

/**
 * Manages the translation of the cart
 *
 * @since 1.0
 */
class PLLWC_Frontend_Cart {
	protected $data_store;

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->data_store = PLLWC_Data_Store::load( 'product_language' );

		if ( did_action( 'pll_language_defined' ) ) {
			$this->init();
		} else {
			add_action( 'pll_language_defined', array( $this, 'init' ), 1 );
		}

		add_filter( 'pll_set_language_from_query', array( $this, 'pll_set_language_from_query' ), 5 ); // Before Polylang

		// Hashes should be language independent
		// FIXME: Backward compatibility with WC < 3.6
		if ( version_compare( WC()->version, '3.6', '<' ) ) {
			add_filter( 'woocommerce_add_to_cart_hash', array( $this, 'cart_hash' ), 10, 2 );
		}
		add_filter( 'woocommerce_cart_hash', array( $this, 'cart_hash' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_data_to_validate', array( $this, 'cart_item_data_to_validate' ), 10, 2 ); // Since WC 3.4
	}

	/**
	 * Setups actions and filters once the language is defined
	 *
	 * @since 1.0
	 */
	public function init() {
		// Resets the cart when switching the language
		if ( isset( $_COOKIE[ PLL_COOKIE ] ) && pll_current_language() !== $_COOKIE[ PLL_COOKIE ] ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
			add_action( 'wp_head', array( $this, 'wp_head' ) );
		}

		// Translate products in cart
		add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'woocommerce_cart_loaded_from_session' ) );
	}

	/**
	 * Reload the cart when the language is set from the content
	 *
	 * @since 0.3.2
	 *
	 * @param bool|object $lang False or language object.
	 * @return bool|object
	 */
	public function pll_set_language_from_query( $lang ) {
		if ( ! PLL()->options['force_lang'] ) {
			if ( did_action( 'pll_language_defined' ) ) {
				// Specific case for the Site home (when the language code is hidden for the defualt language).
				// Done here and not in the 'pll_language_defined' action to avoid a notice with WooCommerce Dynamic pricing which calls is_shop()
				WC()->cart->get_cart_from_session();
			} else {
				add_action( 'pll_language_defined', array( WC()->cart, 'get_cart_from_session' ) );
			}
		}

		return $lang;
	}

	/**
	 * Enqueues jQuery
	 *
	 * @since 0.1
	 */
	public function wp_enqueue_scripts() {
		wp_enqueue_script( 'jquery' );
	}

	/**
	 * Reset cached data when switching the language
	 *
	 * @since 0.1
	 */
	public function wp_head() {
		// reset shipping methods (needed since WC 2.6)
		WC()->shipping->calculate_shipping( WC()->cart->get_shipping_packages() );

		if ( version_compare( WC()->version, '3.1', '<' ) ) {
			// FIXME backward compatibility with WC < 3.1
			$cart_hash_key = 'wc_cart_hash';
			$fragment_name = 'wc_fragments';
		} elseif ( version_compare( WC()->version, '3.4', '<' ) ) {
			// FIXME backward compatibility with WC < 3.4
			$cart_hash_key = 'wc_cart_hash';
			$fragment_name = apply_filters( 'woocommerce_cart_fragment_name', 'wc_fragments_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) ) );
		} else {
			$cart_hash_key = apply_filters( 'woocommerce_cart_hash_key', 'wc_cart_hash_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() ) );
			$fragment_name = apply_filters( 'woocommerce_cart_fragment_name', 'wc_fragments_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() ) );
		}

		// Add js to reset the cart
		printf(
			'<script type="text/javascript">
				jQuery( document ).ready( function( $ ){
					sessionStorage.removeItem( "%s" );
					sessionStorage.removeItem( "%s" );
				} );
			</script>',
			esc_js( $cart_hash_key ),
			esc_js( $fragment_name )
		);
	}

	/**
	 * Translates product attributes in cart
	 *
	 * @since 1.1
	 *
	 * @param array  $attributes Selected attributes.
	 * @param string $lang       Target language.
	 * @param string $orig_lang  Source language.
	 * @return array
	 */
	public function translate_attributes_in_cart( $attributes, $lang, $orig_lang ) {
		foreach ( $attributes as $name => $value ) {
			if ( '' === $value ) {
				continue;
			}

			$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );

			if ( taxonomy_exists( $taxonomy ) ) {
				// Don't use get_term_by( 'slug' ) which is filtered in the current language by Polylang Pro
				$terms = get_terms( $taxonomy, array( 'slug' => $value, 'lang' => $orig_lang ) );

				if ( ! empty( $terms ) && is_array( $terms ) ) {
					$term = reset( $terms );
					if ( $term_id = pll_get_term( $term->term_id, $lang ) ) {
						$term = get_term( $term_id, $taxonomy );
						$attributes[ $name ] = $term->slug;
					}
				}
			}
		}

		return $attributes;
	}

	/**
	 * Translates products in cart
	 *
	 * @since 0.3.5
	 *
	 * @param array  $item Cart item.
	 * @param string $lang Language code.
	 * @return array
	 */
	protected function translate_cart_item( $item, $lang ) {
		$orig_lang = $this->data_store->get_language( $item['product_id'] );

		$item['product_id'] = $this->data_store->get( $item['product_id'], $lang );

		// Variable product
		if ( $item['variation_id'] && $tr_id = $this->data_store->get( $item['variation_id'], $lang ) ) {
			$item['variation_id'] = $tr_id;
			if ( ! empty( $item['data'] ) ) {
				$item['data'] = wc_get_product( $item['variation_id'] );
			}

			// Variations attributes
			if ( ! empty( $item['variation'] ) ) {
				$item['variation'] = $this->translate_attributes_in_cart( $item['variation'], $lang, $orig_lang );
			}
		} elseif ( ! empty( $item['data'] ) ) {
			// Simple product
			$item['data'] = wc_get_product( $item['product_id'] );
		}

		/**
		 * Filters a cart item when it is translated
		 *
		 * @since 0.6
		 *
		 * @param array  $item Cart item.
		 * @param string $lang Language code.
		 */
		$item = apply_filters( 'pllwc_translate_cart_item', $item, $lang );

		/**
		 * Filters cart item data
		 * This filters aims to replace the filter 'woocommerce_add_cart_item_data'
		 * which can't be used here as it conflicts with WooCommerce Bookings
		 * which uses the filter to create new bookings and not only to filter the cart item data
		 *
		 * @since 0.7.4
		 *
		 * @param array $cart_item_data Cart item data.
		 * @param array $item           Cart item.
		 */
		$cart_item_data = (array) apply_filters( 'pllwc_add_cart_item_data', array(), $item );
		$item['key'] = WC()->cart->generate_cart_id( $item['product_id'], $item['variation_id'], $item['variation'], $cart_item_data );

		return $item;
	}

	/**
	 * Translates cart contents
	 *
	 * @since 0.3.5
	 *
	 * @param array  $contents Cart contents.
	 * @param string $lang     Language code.
	 * @return array
	 */
	protected function translate_cart_contents( $contents, $lang = '' ) {
		if ( empty( $lang ) ) {
			$lang = pll_current_language();
		}

		foreach ( $contents as $key => $item ) {
			if ( $item['product_id'] && ( $tr_id = $this->data_store->get( $item['product_id'], $lang ) ) && $tr_id !== $item['product_id'] ) {
				unset( $contents[ $key ] );
				$item = $this->translate_cart_item( $item, $lang );
				$contents[ $item['key'] ] = $item;

				/**
				 * Fires after a cart item has been translated
				 *
				 * @since 1.1
				 *
				 * @param array  $item Cart item.
				 * @param string $key  Previous cart item key. The new key can be found in $item['key'].
				 */
				do_action( 'pllwc_translated_cart_item', $item, $key );
			}
		}

		/**
		 * Filter cart contents after all cart items have been translated
		 *
		 * @since 1.1
		 *
		 * @param array  $contents Cart contents.
		 * @param string $lang     Language code.
		 */
		$contents = apply_filters( 'pllwc_translate_cart_contents', $contents, $lang );

		return $contents;
	}

	/**
	 * Translates the products and removed products in cart
	 *
	 * @since 0.3.5
	 */
	public function woocommerce_cart_loaded_from_session() {
		WC()->cart->cart_contents = $this->translate_cart_contents( WC()->cart->cart_contents );
		WC()->cart->removed_cart_contents = $this->translate_cart_contents( WC()->cart->removed_cart_contents );
	}

	/**
	 * Makes the cart hash language independent by relying on products in default language
	 *
	 * @since 0.9.4
	 *
	 * @param string $hash         Cart hash.
	 * @param array  $cart_session Cart session.
	 * @return string Modified cart hash.
	 */
	public function cart_hash( $hash, $cart_session ) {
		if ( ! empty( $cart_session ) ) {
			$cart_session = $this->translate_cart_contents( $cart_session, pll_default_language() );
			$hash = md5( wp_json_encode( $cart_session ) . WC()->cart->get_total( 'edit' ) );
		}
		return $hash;
	}

	/**
	 * Makes the cart item hash language independent by relying on attributes in default language
	 *
	 * @since 1.0
	 *
	 * @param array  $data    Data to validate in the hash.
	 * @param object $product Product in the cart item.
	 * @return array
	 */
	public function cart_item_data_to_validate( $data, $product ) {
		if ( ! empty( $data['attributes'] ) && $tr_product = $this->data_store->get( $product->get_id(), pll_default_language() ) ) {
			if ( $tr_product = wc_get_product( $tr_product ) ) {
				$data['attributes'] = $tr_product->get_variation_attributes();
			}
		}
		return $data;
	}
}
