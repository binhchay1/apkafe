<?php

/**
 * Manages WooCommerce specific translations on frontend
 *
 * @since 0.1
 */
class PLLWC_Frontend {

	/**
	 * Constructor
	 *
	 * @since 0.1
	 */
	public function __construct() {
		if ( did_action( 'pll_language_defined' ) ) {
			$this->init();
		} else {
			add_action( 'pll_language_defined', array( $this, 'init' ), 1 );

			// Set the language early if a form has been posted with a language value
			if ( ! empty( $_REQUEST['lang'] ) && $lang = PLL()->model->get_language( sanitize_key( $_REQUEST['lang'] ) ) ) {  // phpcs:ignore WordPress.Security.NonceVerification
				PLL()->curlang = $lang;
				$GLOBALS['text_direction'] = $lang->is_rtl ? 'rtl' : 'ltr'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
				do_action( 'pll_language_defined', $lang->slug, $lang );
			}
		}
	}

	/**
	 * Setups actions filters once the language is defined
	 *
	 * @since 0.1
	 */
	public function init() {
		// Translates pages ids
		foreach ( array( 'myaccount', 'shop', 'cart', 'checkout', 'terms' ) as $page ) {
			// Don't use the filter 'woocommerce_get' . $page . '_page_id' as some themes (ex: Flatsome) are retrieving directly the option
			add_filter( 'option_woocommerce_' . $page . '_page_id', 'pll_get_post' );
		}

		// Filters the product search form
		add_filter( 'get_product_search_form', array( PLL()->filters_search, 'get_search_form' ), 99 );

		if ( ! PLL()->options['force_lang'] ) {
			if ( ! get_option( 'permalink_structure' ) ) {
				// Fix product page when using plain permalinks and the language is set from the content
				add_filter( 'pll_check_canonical_url', array( $this, 'pll_check_canonical_url' ) );
				add_filter( 'pll_translation_url', array( $this, 'pll_translation_url' ), 10, 2 );
			} else {
				// Fix shop link when using pretty permalinks and the language is set from the content
				add_filter( 'post_type_archive_link', array( $this, 'post_type_archive_link' ), 99, 2 ); // After Polylang
			}

			// Add language input field to forms to detect the language before wp_loaded is fired
			$actions = array(
				'woocommerce_login_form_start', // Login
				'woocommerce_before_cart_table', // Cart
				'woocommerce_before_add_to_cart_button', // Product
				'woocommerce_lostpassword_form', // Lost password
			);

			foreach ( $actions as $action ) {
				add_action( $action, array( $this, 'language_form_field' ) );
			}

			add_filter( 'woocommerce_get_remove_url', array( $this, 'add_lang_query_arg' ) );
		}

		// Translates home url in widgets
		add_filter( 'pll_home_url_white_list', array( $this, 'home_url_white_list' ) );

		// Layered nav chosen attributes
		add_filter( 'woocommerce_product_query_tax_query', array( $this, 'product_tax_query' ) );

		if ( PLL()->options['force_lang'] > 1 ) {
			add_filter( 'home_url', array( $this, 'fix_widget_price_filter' ), 10, 2 );
		}

		// Shortcodes
		add_filter( 'woocommerce_shortcode_products_query', array( $this, 'shortcode_products_query' ) ); // Since WC 3.0.2

		// Ajax endpoint
		add_filter( 'woocommerce_ajax_get_endpoint', array( $this, 'ajax_get_endpoint' ), 10, 2 );
	}

	/**
	 * Fix canonical redirection from shop page to product archive when using plain permalinks and the language is set from the content
	 *
	 * @since 0.3.2
	 *
	 * @param string $redirect_url Redirect url.
	 * @return string|false
	 */
	public function pll_check_canonical_url( $redirect_url ) {
		if ( is_post_type_archive( 'product' ) ) {
			return false;
		}
		return $redirect_url;
	}

	/**
	 * Fix translation url of shop page (product archive) when using plain permalinks and the language is set from the content
	 *
	 * @since 0.3.2
	 *
	 * @param string $url  Translation url.
	 * @param string $lang Language code.
	 * @return string
	 */
	public function pll_translation_url( $url, $lang ) {
		if ( is_post_type_archive( 'product' ) ) {
			$lang = PLL()->model->get_language( $lang );

			if ( PLL()->options['hide_default'] && 'page' === get_option( 'show_on_front' ) && PLL()->options['default_lang'] === $lang->slug ) {
				$pages = pll_languages_list( array( 'fields' => 'page_on_front' ) );
				if ( in_array( wc_get_page_id( 'shop' ), $pages ) ) {
					return $lang->home_url;
				}
			}

			$url = get_post_type_archive_link( 'product' );
			$url = PLL()->links_model->switch_language_in_link( $url, $lang );
			$url = PLL()->links_model->remove_paged_from_link( $url );
		}
		return $url;
	}

	/**
	 * Fixes the shop link when using pretty permalinks and the language is set from the content
	 * This fixes the widget layered nav which calls get_post_type_archive_link( 'product' )
	 *
	 * @since 0.4.6
	 *
	 * @param string $link      Post type archive link.
	 * @param string $post_type Post type name.
	 * @return string Modified link.
	 */
	public function post_type_archive_link( $link, $post_type ) {
		return 'product' === $post_type ? wc_get_page_permalink( 'shop' ) : $link;
	}

	/**
	 * Outputs the hidden language input field
	 *
	 * @since 0.3.5
	 */
	public function language_form_field() {
		printf( '<input type="hidden" name="lang" value="%s" />', esc_attr( pll_current_language() ) );
	}

	/**
	 * Add a lang query arg to the url
	 *
	 * @since 0.5
	 *
	 * @param string $url URL to modify.
	 * @return string
	 */
	public function add_lang_query_arg( $url ) {
		return add_query_arg( 'lang', pll_current_language(), $url );
	}

	/**
	 * Fixes home url in widgets
	 *
	 * @since 0.5
	 *
	 * @param array $arr List of files and functions to whitelist for the home_url filter.
	 * @return array
	 */
	public function home_url_white_list( $arr ) {
		// FIXME Backward compatibility with WC < 3.3
		if ( version_compare( WC()->version, '3.3', '<' ) ) {
			$arr = array_merge(
				$arr,
				array(
					array( 'file' => 'class-wc-widget-layered-nav.php' ),
					array( 'file' => 'class-wc-widget-layered-nav-filters.php' ),
					array( 'file' => 'class-wc-widget-rating-filter.php' ),
				)
			);
		} else {
			$arr = array_merge(
				$arr,
				array( array( 'file' => 'abstract-wc-widget.php' ) )
			);
		}

		// Avoid a redirect when the language is set from the content
		if ( PLL()->options['force_lang'] > 0 ) {
			$arr = array_merge(
				$arr,
				array( array( 'file' => 'class-wc-widget-product-categories.php' ) )
			);
		}

		if ( PLL()->options['force_lang'] > 1 ) {
			$arr = array_merge(
				$arr,
				array( array( 'file' => 'class-wc-widget-price-filter.php' ) )
			);
		}

		return $arr;
	}

	/**
	 * Fixes the layered nav chosen attributes when shared slugs are in query
	 * Otherwise the query would look for products in all attributes in all languages which always return an empty result
	 *
	 * @since 0.5
	 *
	 * @param array $tax_query Tax query parameter in WP_Query.
	 * @return array
	 */
	public function product_tax_query( $tax_query ) {
		foreach ( $tax_query as $k => $q ) {
			if ( is_array( $q ) && 'slug' === $q['field'] ) {
				$terms = get_terms( $q['taxonomy'], array( 'slug' => $q['terms'] ) );
				$tax_query[ $k ]['terms'] = wp_list_pluck( $terms, 'term_taxonomy_id' );
				$tax_query[ $k ]['field'] = 'term_taxonomy_id';
			}
		}
		return $tax_query;
	}

	/**
	 * Filters the form action url of the widget price filter for subdomains and multiple domains
	 *
	 * @since 0.5
	 *
	 * @param string $url  Form action url.
	 * @param string $path Path.
	 * @return string
	 */
	public function fix_widget_price_filter( $url, $path ) {
		global $wp;

		if ( ! empty( $wp->request ) && trailingslashit( $wp->request ) === $path ) {
			$url = PLL()->links_model->switch_language_in_link( $url, PLL()->curlang );
		}

		return $url;
	}

	/**
	 * Adds language to shortcodes query args to get one cache key per language
	 * Needed for WC 3.0, Requires WC 3.0.2+
	 *
	 * @since 0.7.4
	 *
	 * @param array $args WP_Query arguments.
	 * @return array
	 */
	public function shortcode_products_query( $args ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'language',
			'field'    => 'term_taxonomy_id',
			'terms'    => PLL()->curlang->term_taxonomy_id,
			'operator' => 'IN',
		);

		return $args;
	}

	/**
	 * Make sure the ajax endpoint is in the right language. Needed since WC 3.2.
	 *
	 * @since 0.9.1
	 *
	 * @param string $url     Ajax endpoint.
	 * @param string $request Ajax endpoint request.
	 * @return string
	 */
	public function ajax_get_endpoint( $url, $request ) {
		// Remove wc-ajax to avoid the value %%endpoint%% to be encoded by add_query_arg (used in plain permalinks)
		$url = remove_query_arg( 'wc-ajax', $url );
		$url = PLL()->links_model->switch_language_in_link( $url, PLL()->curlang );
		return add_query_arg( 'wc-ajax', $request, $url );
	}
}
