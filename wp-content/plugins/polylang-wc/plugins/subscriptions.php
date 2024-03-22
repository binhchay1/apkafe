<?php

/**
 * Manages compatibility with WooCommerce subscriptions
 * Version tested: 2.2.19
 *
 * @since 0.4
 */
class PLLWC_Subscriptions {

	/**
	 * Constructor
	 *
	 * @since 0.4
	 */
	public function __construct() {
		add_filter( 'pllwc_copy_post_metas', array( $this, 'copy_post_metas' ) );

		// Add languages to subscriptions, similar to orders
		add_filter( 'pll_get_post_types', array( $this, 'translate_types' ), 10, 2 );
		add_filter( 'pll_bulk_translate_post_types', array( $this, 'bulk_translate_post_types' ) );

		// Renewal and Resubscribe
		add_filter( 'wcs_new_order_created', array( $this, 'new_order_created' ), 10, 2 );

		if ( PLL() instanceof PLL_admin ) {
			add_action( 'wp_loaded', array( $this, 'custom_columns' ), 20 );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 20 ); // FIXME or add a filter in PLLWC not to restrict to orders
		}

		// Emails
		$actions = array(
			// Cancelled subscription
			'cancelled_subscription_notification',
			// Customer completed order
			'woocommerce_order_status_completed_renewal_notification',
			// Customer Completed Switch Order
			'woocommerce_order_status_completed_switch_notification',
			// Customer renewal order
			'woocommerce_order_status_pending_to_processing_renewal_notification',
			'woocommerce_order_status_pending_to_on-hold_renewal_notification',
			// Customer renewal invoice
			'woocommerce_generated_manual_renewal_order_renewal_notification',
			'woocommerce_order_status_failed_renewal_notification',
			// Expired subscription
			'expired_subscription_notification', // Since WCS 2.1
			// New order (to the shop)
			'woocommerce_order_status_pending_to_processing_renewal_notification',
			'woocommerce_order_status_pending_to_completed_renewal_notification',
			'woocommerce_order_status_pending_to_on-hold_renewal_notification',
			'woocommerce_order_status_failed_to_processing_renewal_notification',
			'woocommerce_order_status_failed_to_completed_renewal_notification',
			'woocommerce_order_status_failed_to_on-hold_renewal_notification',
			// Switch order (to the shop)
			'woocommerce_order_status_pending_to_processing_switch_notification',
			'woocommerce_order_status_pending_to_completed_switch_notification',
			'woocommerce_order_status_pending_to_on-hold_switch_notification',
			'woocommerce_order_status_failed_to_processing_switch_notification',
			'woocommerce_order_status_failed_to_completed_switch_notification',
			'woocommerce_order_status_failed_to_on-hold_switch_notification',
			// Suspended Subscription
			'on-hold_subscription_notification', // Since WCS 2.1
		);

		foreach ( $actions as $action ) {
			add_action( $action, array( PLLWC()->emails, 'before_order_email' ), 1 ); // Switch the language for the email
			add_action( $action, array( PLLWC()->emails, 'after_email' ), 999 ); // Switch the language back after the email has been sent
		}

		add_action( 'change_locale', array( $this, 'change_locale' ) ); // Since WP 4.7

		// Strings translations
		$options = array(
			'add_to_cart_button_text' => __( 'Add to Cart Button Text', 'woocommerce-subscriptions' ),
			'order_button_text'       => __( 'Place Order Button Text', 'woocommerce-subscriptions' ),
			'switch_button_text'      => __( 'Switch Button Text', 'woocommerce-subscriptions' ),
		);

		add_filter( 'pll_sanitize_string_translation', array( $this, 'sanitize_strings' ), 10, 3 );

		foreach ( $options as $option => $name ) {
			if ( PLL() instanceof PLL_Settings && $string = get_option( 'woocommerce_subscriptions_' . $option ) ) {
				pll_register_string( $name, $string, 'WooCommerce Subscriptions' );
			} elseif ( PLL() instanceof PLL_Frontend ) {
				add_filter( 'option_woocommerce_subscriptions_' . $option, 'pll__' );
			}
		}

		if ( PLL() instanceof PLL_Frontend ) {
			add_action( 'parse_query', array( $this, 'parse_query' ), 3 ); // Before Polylang
		}

		// Endpoints
		add_filter( 'pll_translation_url', array( $this, 'pll_translation_url' ), 10, 2 );
		add_filter( 'pllwc_endpoints_query_vars', array( $this, 'pllwc_endpoints_query_vars' ) );

		// Check if a user has a subscription
		add_filter( 'wcs_user_has_subscription', array( $this, 'user_has_subscription' ), 10, 4 );
		add_filter( 'woocommerce_get_subscriptions_query_args', array( $this, 'get_subscriptions_query_args' ), 10, 2 );
	}

	/**
	 * Copy or synchronize metas
	 *
	 * @since 0.4
	 *
	 * @param array $keys List of custom fields names.
	 * @return array
	 */
	public function copy_post_metas( $keys ) {
		$wcs_keys = array(
			'_subscription_payment_sync_date',
			'_subscription_length',
			'_subscription_limit',
			'_subscription_period',
			'_subscription_period_interval',
			'_subscription_price',
			'_subscription_sign_up_fee',
			'_subscription_trial_length',
			'_subscription_trial_period',
		);
		return array_merge( $keys, $wcs_keys );
	}

	/**
	 * Language and translation management for subscriptions post type
	 *
	 * @since 0.4
	 *
	 * @param array $types List of post type names for which Polylang manages language and translations.
	 * @param bool  $hide  True when displaying the list in Polylang settings.
	 * @return array List of post type names for which Polylang manages language and translations.
	 */
	public function translate_types( $types, $hide ) {
		$wcs_types = array( 'shop_subscription' );
		return $hide ? array_diff( $types, $wcs_types ) : array_merge( $types, $wcs_types );
	}

	/**
	 * Remove subscriptions post type from bulk translate
	 *
	 * @since 1.2
	 *
	 * @param array $types List of post type names for which Polylang manages the bulk translation.
	 * @return array
	 */
	public function bulk_translate_post_types( $types ) {
		return array_diff( $types, array( 'shop_subscription' ) );
	}

	/**
	 * Sets the order language when created from a subscription
	 *
	 * @since 0.4.4
	 *
	 * @param object $new_order    New order.
	 * @param object $subscription Parent subscription.
	 * @return object Unmodified order
	 */
	public function new_order_created( $new_order, $subscription ) {
		if ( $lang = pll_get_post_language( $subscription->get_id() ) ) {
			$data_store = PLLWC_Data_Store::load( 'order_language' );
			$data_store->set_language( $new_order->get_id(), $lang );
		}
		return $new_order;
	}

	/**
	 * Removes the standard languages columns for subscriptions
	 * and replace them with one unique column as for orders
	 *
	 * @since 0.4
	 */
	public function custom_columns() {
		remove_filter( 'manage_edit-shop_subscription_columns', array( PLL()->filters_columns, 'add_post_column' ), 100 );
		remove_action( 'manage_shop_subscription_posts_custom_column', array( PLL()->filters_columns, 'post_column' ), 10, 2 );

		add_filter( 'manage_edit-shop_subscription_columns', array( PLLWC()->admin_orders, 'add_order_column' ), 100 );
		add_action( 'manage_shop_subscription_posts_custom_column', array( PLLWC()->admin_orders, 'order_column' ), 10, 2 );
	}

	/**
	 * Removes the language metabox for subscriptions
	 *
	 * @since 0.4
	 *
	 * @param string $post_type Post type.
	 */
	public function add_meta_boxes( $post_type ) {
		if ( 'shop_subscription' === $post_type ) {
			remove_meta_box( 'ml_box', $post_type, 'side' ); // Remove Polylang metabox
			add_meta_box( 'pllwc_box', __( 'Language', 'polylang' ), array( PLLWC()->admin_orders, 'order_language' ), $post_type, 'side', 'high' );
		}
	}

	/**
	 * Reload Subscription translations in emails
	 *
	 * @since 1.0
	 */
	public function change_locale() {
		WC_Subscriptions::load_plugin_textdomain();
	}

	/**
	 * Translated strings must be sanitized the same way WooCommerce does before they are saved
	 *
	 * @since 0.4
	 *
	 * @param string $translation A string translation.
	 * @param string $name        The string name.
	 * @param string $context     The group the string belongs to.
	 * @return string Sanitized translation
	 */
	public function sanitize_strings( $translation, $name, $context ) {
		if ( 'WooCommerce Subscriptions' === $context ) {
			$translation = wp_kses_post( trim( stripslashes( $translation ) ) );
		}
		return $translation;
	}

	/**
	 * Disables the languages filter for a customer to see all subscriptions whatever the languages
	 *
	 * @since 0.4
	 *
	 * @param object $query WP_Query object.
	 */
	public function parse_query( $query ) {
		$qvars = $query->query_vars;

		// Customers should see all their orders whatever the language
		if ( isset( $qvars['post_type'] ) && ( 'shop_subscription' === $qvars['post_type'] || ( is_array( $qvars['post_type'] ) && in_array( 'shop_subscription', $qvars['post_type'] ) ) ) ) {
			$query->set( 'lang', 0 );
		}
	}

	/**
	 * Returns the translation of the current url
	 *
	 * @since 0.4
	 *
	 * @param string $url  URL of the translation, to modify.
	 * @param string $lang Language slug.
	 * @return string
	 */
	public function pll_translation_url( $url, $lang ) {
		global $wp;

		$wcs_query = pll_get_anonymous_object_from_filter( 'the_title', array( 'WCS_Query', 'change_endpoint_title' ), 11 );

		if ( $endpoint = $wcs_query->get_current_endpoint() ) {
			if ( version_compare( WC_Subscriptions::$version, '2.3', '<' ) ) {
				$value = wc_edit_address_i18n( $wp->query_vars[ $endpoint ], true );
				$url   = wc_get_endpoint_url( $endpoint, $value, $url );
			}

			if ( defined( 'POLYLANG_PRO' ) && POLYLANG_PRO && get_option( 'permalink_structure' ) ) {
				$language = PLL()->model->get_language( $lang );
				$url      = PLL()->translate_slugs->slugs_model->switch_translated_slug( $url, $language, 'wc_' . $wcs_query->query_vars[ $endpoint ] );
			}
		}

		return $url;
	}

	/**
	 * Adds Subscriptions endpoints to the list of endpoints to translate
	 *
	 * @since 0.4
	 *
	 * @param array $slugs Endpoints slugs.
	 * @return array
	 */
	public function pllwc_endpoints_query_vars( $slugs ) {
		$wcs_query = pll_get_anonymous_object_from_filter( 'the_title', array( 'WCS_Query', 'change_endpoint_title' ), 11 );
		return empty( $wcs_query ) ? $slugs : array_merge( $slugs, $wcs_query->get_query_vars() );
	}

	/**
	 * Check if a user has a subscription to a translated product.
	 *
	 * @since 0.9.2
	 *
	 * @param bool  $has_subscription Whether WooCommerce Subscriptions found a subscription.
	 * @param int   $user_id          The ID of a user in the store.
	 * @param int   $product_id       The ID of a product in the store.
	 * @param mixed $status           Subscription status.
	 * @return bool
	 */
	public function user_has_subscription( $has_subscription, $user_id, $product_id, $status ) {
		if ( false === $has_subscription && ! empty( $product_id ) ) {
			$data_store = PLLWC_Data_Store::load( 'product_language' );
			foreach ( wcs_get_users_subscriptions( $user_id ) as $subscription ) {
				if ( empty( $status ) || 'any' === $status || $subscription->has_status( $status ) ) {
					foreach ( $data_store->get_translations( $product_id ) as $tr_id ) {
						if ( $subscription->has_product( $tr_id ) ) {
							$has_subscription = true;
							break 2;
						}
					}
				}
			}
		}
		return $has_subscription;
	}

	/**
	 * When querying subscriptions and no subscriptions have been found for the current product,
	 * check if there are subscriptions for the translated products.
	 *
	 * @since 1.2
	 *
	 * @param array $query_args WP_Query() arguments.
	 * @param array $args       Arguments of wcs_get_subscriptions().
	 * @return array
	 */
	public function get_subscriptions_query_args( $query_args, $args ) {
		if ( isset( $query_args['post__in'] ) && array( 0 ) === $query_args['post__in'] ) {
			$data_store = PLLWC_Data_Store::load( 'product_language' );
			$query_args['post__in'] = wcs_get_subscriptions_for_product(
				array_merge(
					$data_store->get_translations( $args['product_id'] ),
					$data_store->get_translations( $args['variation_id'] )
				)
			);
		}
		return $query_args;
	}
}
