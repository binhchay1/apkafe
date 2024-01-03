<?php

/**
 * Allows to automatically install the translations of the WooCommerce default pages.
 * Manages the installation of the default product category.
 *
 * @since 0.1
 */
class PLLWC_Admin_WC_Install {

	/**
	 * Constructor
	 *
	 * @since 0.1
	 */
	public function __construct() {
		// Add post state for translations of the shop, cart, etc...
		add_filter( 'display_post_states', array( $this, 'display_post_states' ), 10, 2 );

		add_filter( 'woocommerce_debug_tools', array( $this, 'debug_tools' ) );

		// Make sure to load only on setup wizard and status page as initializing translated pages is expensive
		if ( isset( $_GET['page'] ) && ( 'wc-setup' === $_GET['page'] || 'wc-status' === $_GET['page'] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification
			$this->init_translated_pages();

			if ( ! empty( $this->pages ) ) {
				foreach ( array_keys( reset( $this->pages ) ) as $key ) {
					add_action( 'update_option_woocommerce_' . $key . '_page_id', array( $this, 'create_page' ) );
				}
			}
		}

		// Add default product category when adding a new language
		add_action( 'pll_add_language', array( $this, 'add_language' ) );
	}

	/**
	 * Add post states for the translations of the shop, cart, checkout, account and terms pages
	 *
	 * @since 0.9
	 *
	 * @param array  $post_states List of post states.
	 * @param object $post        Instance of WP_Post.
	 * @return array
	 */
	public function display_post_states( $post_states, $post ) {
		if ( in_array( $post->ID, pll_get_post_translations( wc_get_page_id( 'shop' ) ) ) ) {
			$post_states['wc_page_for_shop'] = __( 'Shop Page', 'woocommerce' );
		}

		if ( in_array( $post->ID, pll_get_post_translations( wc_get_page_id( 'cart' ) ) ) ) {
			$post_states['wc_page_for_cart'] = __( 'Cart Page', 'woocommerce' );
		}

		if ( in_array( $post->ID, pll_get_post_translations( wc_get_page_id( 'checkout' ) ) ) ) {
			$post_states['wc_page_for_checkout'] = __( 'Checkout Page', 'woocommerce' );
		}

		if ( in_array( $post->ID, pll_get_post_translations( wc_get_page_id( 'myaccount' ) ) ) ) {
			$post_states['wc_page_for_myaccount'] = __( 'My Account Page', 'woocommerce' );
		}

		if ( in_array( $post->ID, pll_get_post_translations( wc_get_page_id( 'terms' ) ) ) ) {
			$post_states['wc_page_for_terms'] = __( 'Terms and Conditions Page', 'woocommerce' );
		}

		return $post_states;
	}

	/**
	 * Replaces the Install WooCommerce Pages tool by our own to be able to create translations
	 *
	 * @since 0.1
	 *
	 * @param array $tools List of available tools.
	 * @return array
	 */
	public function debug_tools( $tools ) {
		$n = array_search( 'install_pages', array_keys( $tools ) );
		$end = array_slice( $tools, $n + 1 );
		$tools = array_slice( $tools, 0, $n );

		$tools['pll_install_pages'] = array(
			'name'     => __( 'Install WooCommerce pages', 'woocommerce' ),
			'button'   => __( 'Install pages', 'woocommerce' ),
			'desc'     => sprintf(
				'<strong class="red">%1$s</strong> %2$s',
				__( 'Note:', 'woocommerce' ),
				__( 'This tool will install all the missing WooCommerce pages. Pages already defined and set up will not be replaced.', 'woocommerce' )
			),
			'callback' => array( $this, 'install_pages' ),
		);

		return array_merge( $tools, $end );
	}

	/**
	 * Filters the locale for WooCommerce
	 *
	 * @since 0.1
	 *
	 * @param string $locale The plugin's current locale.
	 * @param string $domain Text domain.
	 */
	public function plugin_locale( $locale, $domain ) {
		return 'woocommerce' === $domain ? $this->locale : $locale;
	}

	/**
	 * Initializes the page names and content in all available languages
	 * Does not actually create the pages
	 *
	 * @since 0.1
	 */
	public function init_translated_pages() {
		remove_filter( 'load_textdomain_mofile', array( PLL_OLT_Manager::instance(), 'load_textdomain_mofile' ), 10, 2 ); // Polylang 2.0.4+
		add_filter( 'plugin_locale', array( $this, 'plugin_locale' ), 10, 2 );

		foreach ( pll_languages_list( array( 'fields' => '' ) ) as $language ) {
			// Load WooCommerce text domain in new language
			unload_textdomain( 'woocommerce' );
			$this->locale = $language->locale;
			WC()->load_plugin_textdomain();

			// Partly copy paste of WC_Install::create_pages
			// Can't use it directly as Woocommerce checks for the unicity of each page
			// Currently I prefer to rely on WooCommerce translations rather than creating new ones
			$this->pages[ $language->slug ] = apply_filters(
				'woocommerce_create_pages',
				array(
					'shop'      => array(
						'name'    => _x( 'shop', 'Page slug', 'woocommerce' ),
						'title'   => _x( 'Shop', 'Page title', 'woocommerce' ),
						'content' => '',
					),
					'cart'      => array(
						'name'    => _x( 'cart', 'Page slug', 'woocommerce' ),
						'title'   => _x( 'Cart', 'Page title', 'woocommerce' ),
						'content' => '[' . apply_filters( 'woocommerce_cart_shortcode_tag', 'woocommerce_cart' ) . ']',
					),
					'checkout'  => array(
						'name'    => _x( 'checkout', 'Page slug', 'woocommerce' ),
						'title'   => _x( 'Checkout', 'Page title', 'woocommerce' ),
						'content' => '[' . apply_filters( 'woocommerce_checkout_shortcode_tag', 'woocommerce_checkout' ) . ']',
					),
					'myaccount' => array(
						'name'    => _x( 'my-account', 'Page slug', 'woocommerce' ),
						'title'   => _x( 'My account', 'Page title', 'woocommerce' ),
						'content' => '[' . apply_filters( 'woocommerce_my_account_shortcode_tag', 'woocommerce_my_account' ) . ']',
					),
				)
			);
		}

		// Reload current text domain
		if ( ! did_action( 'pll_translate_labels' ) ) {
			add_filter( 'load_textdomain_mofile', array( PLL_OLT_Manager::instance(), 'load_textdomain_mofile' ), 10, 2 );
		}
		remove_filter( 'plugin_locale', array( $this, 'plugin_locale' ), 10, 2 );
		unload_textdomain( 'woocommerce' );
		WC()->load_plugin_textdomain();
	}

	/**
	 * Translate WooCommerce default pages when they are created by WooCommerce ( generally in setup wizard )
	 *
	 * @since 0.1
	 */
	public function create_page() {
		$key = substr( current_action(), 26, -8 );
		foreach ( pll_languages_list() as $lang ) {
			$this->translate_page( $key, $lang );
		}
	}

	/**
	 * Install pages from the WooCommerce status tools ( when using the install pages button )
	 *
	 * @since 0.1
	 */
	public function install_pages() {
		// Let WooCommerce create the pages in the default language
		WC_Install::create_pages();

		// In case pages were installed before Polylang, the pages may have no language. We must assign one.
		foreach ( array_keys( $this->pages[ pll_default_language() ] ) as $key ) {
			$post_id = wc_get_page_id( $key );
			if ( ! pll_get_post_language( $post_id ) ) {
				pll_set_post_language( $post_id, pll_default_language() );
			}
		}

		// Then translate them
		foreach ( pll_languages_list() as $lang ) {
			foreach ( array_keys( $this->pages[ $lang ] ) as $key ) {
				$this->translate_page( $key, $lang );
			}
		}

		$message = __( 'All missing WooCommerce pages successfully installed', 'woocommerce' );

		if ( version_compare( WC()->version, '3.1', '<' ) ) {
			// In WC 3.0 the message will be above the message 'Tool ran'. See https://github.com/woocommerce/woocommerce/pull/14576
			echo '<div class="updated"><p>' . esc_html( $message ) . '</p></div>';
		} else {
			return $message;
		}
	}

	/**
	 * Create a page translation
	 *
	 * @since 0.1
	 *
	 * @param string $id   WooCommerce page id.
	 * @param string $lang Language slug.
	 */
	public function translate_page( $id, $lang ) {
		$post_id = wc_get_page_id( $id );
		$translations = pll_get_post_translations( $post_id );

		// Create the translation only if it doesn't exist yet
		if ( empty( $translations[ $lang ] ) ) {
			$post = get_post( $post_id );
			$post->ID = null;
			// FIXME post parent
			$post->post_title = $this->pages[ $lang ][ $id ]['title'];
			$post->post_name = $this->pages[ $lang ][ $id ]['name'];
			$post->post_status = 'draft'; // Keep it draft before we set the language, for auto added pages to menu
			$tr_id = wp_insert_post( (array) $post );

			// Assign language and translations
			pll_set_post_language( $tr_id, $lang );
			$translations[ $lang ] = $tr_id;
			pll_save_post_translations( $translations );

			$tr_post = get_post( $tr_id );

			// We can now publish the page which will also add it to menus if auto add pages to menu is checked
			if ( class_exists( 'PLL_Share_Post_Slug', true ) && PLL()->options['force_lang'] && get_option( 'permalink_structure' ) && $tr_post->post_name !== $this->pages[ $lang ][ $id ]['name'] ) {
				// Attempt to share the slug if needed, to do after the language has been set
				$tr_post->post_name = $this->pages[ $lang ][ $id ]['name'];
				$tr_post->post_status = 'publish';
				wp_update_post( $tr_post );
			} else {
				wp_publish_post( $tr_post );
			}
		}
	}

	/**
	 * Creates a default product category for a language
	 *
	 * @since 0.9.3
	 *
	 * @param string $lang Language code.
	 */
	protected static function create_default_product_cat( $lang ) {
		$default = get_option( 'default_product_cat' );
		if ( $default && ! pll_get_term( $default, $lang ) ) {
			$name = _x( 'Uncategorized', 'Default category slug', 'woocommerce' );
			$slug = sanitize_title( $name . '-' . $lang );
			$cat = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );

			$cat = isset( $cat->error_data['term_exists'] ) ? $cat->error_data['term_exists'] : $cat['term_id'];

			// Set language
			pll_set_term_language( (int) $cat, $lang );
			$translations = pll_get_term_translations( $default );
			$translations[ $lang ] = $cat;
			pll_save_term_translations( $translations );
		}
	}

	/**
	 * Assign the default language to default product category.
	 *
	 * @since 1.0
	 */
	public static function maybe_set_default_category_language() {
		if ( $default = get_option( 'default_product_cat' ) ) {
			if ( ! pll_get_term_language( $default ) ) {
				pll_set_term_language( (int) $default, pll_default_language() );
			}
		}
	}

	/**
	 * Creates a default product category when adding a language
	 *
	 * @since 0.9.3
	 *
	 * @param array $args New language arguments.
	 */
	public function add_language( $args ) {
		if ( $default = get_option( 'default_product_cat' ) ) {
			$default_cat_lang = pll_get_term_language( $default );

			// Assign a default language to default product category.
			if ( ! $default_cat_lang ) {
				pll_set_term_language( (int) $default, pll_default_language() );
			} else {
				self::create_default_product_cat( $args['slug'] );
			}
		}
	}

	/**
	 * Assigns the default language to the default product category
	 * and creates translated default categories
	 *
	 * @since 0.9.3
	 */
	public static function create_default_product_cats() {
		if ( $default = get_option( 'default_product_cat' ) ) {
			$default_cat_lang = pll_get_term_language( $default );

			// Assign a default language to default product category.
			if ( ! $default_cat_lang ) {
				pll_set_term_language( (int) $default, pll_default_language() );
			}

			foreach ( pll_languages_list() as $language ) {
				if ( $language !== $default_cat_lang && ! pll_get_term( $default, $language ) ) {
					self::create_default_product_cat( $language );
				}
			}
		}
	}

	/**
	 * Replaces the Uncategorized product cat in default language by the correct translation
	 *
	 * @since 0.9.3
	 */
	public static function replace_default_product_cats() {
		global $wpdb;

		if ( $default = get_option( 'default_product_cat' ) ) {
			$default_category = get_term( $default, 'product_cat' );

			if ( $default_category instanceof WP_Term ) {
				foreach ( PLL()->model->get_languages_list() as $language ) {
					if ( pll_default_language() !== $language->slug ) {
						$tr_cat = pll_get_term( $default_category->term_id, $language->slug );
						if ( $tr_cat ) {
							$tr_cat = get_term( $tr_cat, 'product_cat' );

							$wpdb->query(
								$wpdb->prepare(
									"UPDATE {$wpdb->term_relationships} as tr1
									JOIN {$wpdb->term_relationships} as tr2 ON tr1.object_id = tr2.object_id
									AND tr2.term_taxonomy_id = %d
									SET tr1.term_taxonomy_id = %d
									WHERE tr1.term_taxonomy_id = %d",
									$language->term_taxonomy_id,
									$tr_cat->term_taxonomy_id,
									$default_category->term_taxonomy_id
								)
							);
						}
					}
				}

				wp_cache_flush();
				delete_transient( 'wc_term_counts' );
				wp_update_term_count_now( pll_get_term_translations( $default_category->term_id ), 'product_cat' );
			}
		}
	}

	/**
	 * Update default product categories after update to WooCommerce 3.3
	 *
	 * @since 0.9.3
	 *
	 * @param string $option Option name.
	 * @param string $value  WooCommerce DB version.
	 */
	public static function update_330_wc_db_version( $option, $value ) {
		if ( version_compare( $value, '3.3.0', '>=' ) ) {
			self::replace_default_product_cats();
		}
	}
}
