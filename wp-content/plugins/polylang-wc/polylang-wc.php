<?php

/**
Plugin name: Polylang for WooCommerce
Plugin URI: https://polylang.pro
Version: 1.2
Author: Frédéric Demarle
Author uri: https://polylang.pro
Description: Adds multilingual capability to WooCommerce
Text Domain: pllwc
Domain Path: /languages
WC requires at least: 3.0
WC tested up to: 3.6
 */

/**
 * Copyright 2016-2019 Frédéric Demarle
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * ( at your option ) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly
}

define( 'PLLWC_VERSION', '1.2' );
define( 'PLLWC_MIN_PLL_VERSION', '2.5.1' );

define( 'PLLWC_FILE', __FILE__ ); // This file
define( 'PLLWC_DIR', dirname( __FILE__ ) );

require_once PLLWC_DIR . '/include/functions.php';

/**
 * Plugin controller
 *
 * @since 0.1
 */
class Polylang_Woocommerce {
	public $post_types, $links, $stock, $emails, $strings;
	public $frontend;
	public $admin_taxonomies, $admin_products, $admin_orders, $admin_reports, $admin_wc_install, $admin_menus;
	protected static $instance;

	/**
	 * Constructor
	 *
	 * @since 0.1
	 */
	public function __construct() {
		spl_autoload_register( array( $this, 'autoload' ) ); // autoload classes

		$install = new PLLWC_Install( plugin_basename( __FILE__ ) );

		// Stopping here if we are going to deactivate the plugin ( avoids breaking rewrite rules )
		if ( $install->is_deactivation() ) {
			return;
		}

		// WC 3.3: Maybe update default product categories after WooCommerce did it
		$db_version = get_option( 'woocommerce_db_version' );
		if ( ! empty( $db_version ) && version_compare( $db_version, '3.3.0', '<' ) ) {
			add_action( 'add_option_woocommerce_db_version', array( 'PLLWC_Admin_WC_Install', 'update_330_wc_db_version' ), 10, 2 );
		}

		// Fix home url when using pretty permalinks and the shop is on front
		// Added here because the filters are fired before the action 'pll_init'
		add_filter( 'pll_languages_list', array( 'PLLWC_Links', 'set_home_urls' ), 7 ); // After Polylang
		add_filter( 'pll_after_languages_cache', array( 'PLLWC_Links', 'pll_after_languages_cache' ), 20 ); // After Polylang

		// The "ajax" request for feature product is indeed a direct link and thus does not include the pll_ajax_backend query var.
		if ( isset( $_GET['action'] ) && 'woocommerce_feature_product' === $_GET['action'] ) {  // phpcs:ignore WordPress.Security.NonceVerification
			define( 'PLL_ADMIN', true );
		}

		add_action( 'pll_init', array( $this, 'init' ) );
		PLLWC_Plugins_Compat::instance();
	}

	/**
	 * Polylang for WooCommerce instance
	 *
	 * @since 0.1
	 *
	 * @return object
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Autoload classes
	 *
	 * @since 0.1
	 *
	 * @param string $class Class name.
	 */
	public function autoload( $class ) {
		// Not a Polylang for WooCommerce class
		if ( 0 !== strncmp( 'PLLWC_', $class, 6 ) ) {
			return;
		}

		$class = str_replace( '_', '-', strtolower( substr( $class, 6 ) ) );

		$dirs = array(
			PLLWC_DIR . '/frontend',
			PLLWC_DIR . '/include',
			PLLWC_DIR . '/plugins',
			PLLWC_DIR . '/admin',
		);

		foreach ( $dirs as $dir ) {
			if ( file_exists( $file = "$dir/$class.php" ) ) {
				require_once $file;
				return;
			}
		}
	}

	/**
	 * Initializes the plugin
	 *
	 * @since 0.1
	 */
	public function init() {
		// Silently disable the plugin if Polylang or WooCommerce are not active
		if ( ! defined( 'POLYLANG_VERSION' ) || ! defined( 'WOOCOMMERCE_VERSION' ) ) {
			return;
		}

		// Version of Polylang is too old
		if ( version_compare( POLYLANG_VERSION, PLLWC_MIN_PLL_VERSION, '<' ) ) {
			add_action( 'all_admin_notices', array( $this, 'admin_notices' ) );
			return;
		}

		if ( PLL() instanceof PLL_Admin_Base ) {
			new PLL_License( __FILE__, 'Polylang for WooCommerce', PLLWC_VERSION, 'Frédéric Demarle' );
		}

		// Bail early if no language has been defined yet.
		if ( ! pll_languages_list() ) {
			return;
		}

		add_action( 'admin_init', array( $this, 'maybe_upgrade' ) );

		// Maybe assign the default language to the default category
		PLLWC_Admin_WC_Install::maybe_set_default_category_language();

		// Install default categories at WooCommerce install
		add_action( 'add_option_default_product_cat', array( 'PLLWC_Admin_WC_Install', 'create_default_product_cats' ) );

		// FIXME backward compatibility with WC < 3.1
		if ( version_compare( WC()->version, '3.1.0', '<' ) ) {
			add_action( 'before_woocommerce_init', array( $this, 'prevent_caching' ), 1 ); // Before WooCommerce
		}

		add_action( 'woocommerce_delete_product_transients', array( $this, 'delete_product_transients' ) );

		$this->post_types = new PLLWC_Post_Types();
		$this->links      = defined( 'POLYLANG_PRO' ) && POLYLANG_PRO && get_option( 'permalink_structure' ) ? new PLLWC_Links_Pro() : new PLLWC_Links();
		$this->stock      = new PLLWC_Stock();
		$this->emails     = new PLLWC_Emails();
		$this->strings    = new PLLWC_Strings();
		$this->data       = new PLLWC_Xdata();
		$this->export     = new PLLWC_Export();
		$this->import     = new PLLWC_Import();

		if ( defined( 'POLYLANG_PRO' ) && POLYLANG_PRO ) {
			$this->rest_api     = new PLLWC_REST_API();
			$this->sync_content = new PLLWC_Sync_Content();
		}

		// Frontend only
		if ( PLL() instanceof PLL_Frontend ) {
			$this->frontend   = new PLLWC_Frontend();
			$this->cart       = new PLLWC_Frontend_Cart();
			$this->my_account = new PLLWC_Frontend_Account();
			$this->coupons    = new PLLWC_Coupons();

			// WC pages on front
			if ( 'page' === get_option( 'show_on_front' ) ) {
				$this->wc_pages = new PLLWC_Frontend_WC_Pages();
			}
		} else {
			$this->admin_wc_install = new PLLWC_Admin_WC_Install();

			// Admin only ( but not useful on Polylang settings pages )
			if ( PLL() instanceof PLL_admin ) {
				$this->admin_taxonomies        = new PLLWC_Admin_Taxonomies();
				$this->admin_products          = new PLLWC_Admin_Products();
				$this->admin_product_duplicate = new PLLWC_Admin_Product_Duplicate();
				$this->admin_orders            = new PLLWC_Admin_Orders();
				$this->admin_reports           = new PLLWC_Admin_Reports();
				$this->admin_menus             = new PLLWC_Admin_Menus();
				$this->coupons                 = new PLLWC_Admin_Coupons();

				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
				add_action( 'woocommerce_system_status_report', array( $this, 'status_report' ) );
				load_plugin_textdomain( 'pllwc', false, basename( PLLWC_DIR ) . '/languages' );
			}
		}

		/**
		 * Fires after the Polylang for WooCommerce object is initialized
		 *
		 * @since 0.3.2
		 *
		 * @param object &$this The Polylang for WooCommerce object
		 */
		do_action_ref_array( 'pllwc_init', array( &$this ) );
	}

	/**
	 * Displays an admin notice if Polylang is not at the right version
	 *
	 * @since 0.1
	 */
	public function admin_notices() {
		load_plugin_textdomain( 'pllwc', false, basename( PLLWC_DIR ) . '/languages' );
		printf(
			'<div class="error"><p>%s</p><p>%s</p></div>',
			esc_html__( 'Polylang for WooCommerce has been deactivated because you are using an old version of Polylang.', 'pllwc' ),
			esc_html(
				sprintf(
					/* translators: %1$s and %2$s are Polylang version numbers */
					__( 'You are using Polylang %1$s. Polylang for WooCommerce requires at least Polylang %2$s.', 'pllwc' ),
					POLYLANG_VERSION,
					PLLWC_MIN_PLL_VERSION
				)
			)
		);
	}

	/**
	 * Manages updates of the plugin
	 *
	 * @since 0.9.3
	 */
	public function maybe_upgrade() {
		$options = get_option( 'polylang-wc' );

		// New install
		if ( empty( $options ) ) {
			$options['version'] = PLLWC_VERSION;
			update_option( 'polylang-wc', $options );
		}

		if ( version_compare( $options['version'], PLLWC_VERSION, '<' ) ) {
			// Version 0.4.3
			if ( version_compare( $options['version'], '0.4.3', '<' ) ) {
				delete_transient( 'woocommerce_cache_excluded_uris' );
			}

			// Version 0.4.6
			if ( version_compare( $options['version'], '0.4.6', '<' ) ) {
				// Same as Polylang 2.0.8, for WP 4.7
				global $wpdb;
				$wpdb->update( $wpdb->usermeta, array( 'meta_key' => 'locale' ), array( 'meta_key' => 'user_lang' ) );
			}

			// Version 0.9.3, if already updated to WC 3.3
			if ( version_compare( $options['version'], '0.9.3', '<' ) ) {
				if ( version_compare( WC()->version, '3.3.0', '>=' ) ) {
					PLLWC_Admin_WC_Install::create_default_product_cats();
					PLLWC_Admin_WC_Install::replace_default_product_cats();
				}
			}

			$options['previous_version'] = $options['version']; // Remember the previous version
			$options['version'] = PLLWC_VERSION;
			update_option( 'polylang-wc', $options );
		}
	}

	/**
	 * Prevents caching the cart, checkout and account pages, including translations
	 * Backward compatibility with WC < 3.1
	 *
	 * @since 0.4.3
	 */
	public static function prevent_caching() {
		if ( false === ( $wc_page_uris = get_transient( 'woocommerce_cache_excluded_uris' ) ) ) {
			$wc_page_uris = array();

			foreach ( array( 'cart', 'checkout', 'myaccount' ) as $wc_page ) {
				foreach ( pll_get_post_translations( wc_get_page_id( $wc_page ) ) as $page_id ) {
					if ( $page_id && $page_id > 0 && ( $page = get_post( $page_id ) ) ) {
						$wc_page_uris[] = 'p=' . $page_id;
						$wc_page_uris[] = '/' . $page->post_name . '/';
					}
				}
			}
			set_transient( 'woocommerce_cache_excluded_uris', $wc_page_uris );
		}
	}

	/**
	 * Clear all transients cache for translations when WC clears a product transient
	 *
	 * @since 0.4.5
	 *
	 * @param int $product_id Product ID.
	 */
	public function delete_product_transients( $product_id ) {
		static $ids;
		$ids[] = $product_id;

		$data_store = PLLWC_Data_Store::load( 'product_language' );
		foreach ( $data_store->get_translations( $product_id ) as $tr_id ) {
			if ( ! in_array( $tr_id, $ids ) ) {
				wc_delete_product_transients( $tr_id );
			}
		}
	}

	/**
	 * Enqueues the stylesheet
	 *
	 * @since 0.1
	 */
	public function admin_enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style( 'pll_wc_admin', plugins_url( '/css/admin' . $suffix . '.css', PLLWC_FILE ), array(), PLLWC_VERSION );
	}

	/**
	 * Loads the status report for the translations of the default pages
	 *
	 * @since 0.1
	 */
	public function status_report() {
		include PLLWC_DIR . '/admin/view-status-report.php';
	}
}

PLLWC();
