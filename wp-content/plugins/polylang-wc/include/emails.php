<?php

/**
 * Manages the customer email languages
 * Associates a language to the user and to orders
 *
 * @since 0.1
 */
class PLLWC_Emails {
	protected $data_store, $switched_locale, $saved_curlang;

	/**
	 * Constructor
	 * Setups actions
	 *
	 * @since 0.1
	 */
	public function __construct() {
		$this->data_store = PLLWC_Data_Store::load( 'order_language' );

		// Deactivate the email locale switch from WooCommerce
		add_filter( 'woocommerce_email_setup_locale', '__return_false' );
		add_filter( 'woocommerce_email_restore_locale', '__return_false' );

		// Define the customer preferred language
		add_action( 'woocommerce_created_customer', array( $this, 'created_customer' ), 5 ); // Before WC sends the notification
		add_action( 'woocommerce_new_order', array( $this, 'new_order' ) );

		// Automatic user emails
		$actions = array(
			'woocommerce_created_customer_notification', // Customer new account
			'woocommerce_reset_password_notification', // Reset password
		);

		add_action( 'change_locale', array( $this, 'change_locale' ), 1 ); // Soon to load the plugin_locale filter

		foreach ( $actions as $action ) {
			add_action( $action, array( $this, 'before_user_email' ), 1 ); // Switch the language for the email
			add_action( $action, array( $this, 'after_email' ), 999 ); // Switch the language back after the email has been sent
		}

		// FIXME new order and cancelled order are sent to the shop. Should I really change the language?
		// Automatic order emails
		$actions = array(
			// Cancelled order
			'woocommerce_order_status_pending_to_cancelled_notification', // Backward compatibility with WC < 3.1
			'woocommerce_order_status_processing_to_cancelled_notification', // Since WC 3.1
			'woocommerce_order_status_on-hold_to_cancelled_notification',
			// Completed order
			'woocommerce_order_status_completed_notification',
			// Customer note
			'woocommerce_new_customer_note_notification',
			// On hold
			'woocommerce_order_status_failed_to_on-hold_notification', // + new order
			'woocommerce_order_status_pending_to_on-hold_notification', // + new order
			// Processing
			'woocommerce_order_status_on-hold_to_processing_notification',
			'woocommerce_order_status_pending_to_processing_notification', // + new order
			// Refunded order
			'woocommerce_order_fully_refunded_notification',
			'woocommerce_order_partially_refunded_notification',
			// Failed order
			'woocommerce_order_status_pending_to_failed_notification',
			'woocommerce_order_status_on-hold_to_failed_notification',
			// New order
			'woocommerce_order_status_pending_to_completed_notification',
			'woocommerce_order_status_failed_to_processing_notification',
			'woocommerce_order_status_failed_to_completed_notification',
		);

		foreach ( $actions as $action ) {
			add_action( $action, array( $this, 'before_order_email' ), 1 ); // Switch the language for the email
			add_action( $action, array( $this, 'after_email' ), 999 ); // Switch the language back after the email has been sent
		}

		// Manually sent order emails (incl. Customer Invoice )
		add_action( 'woocommerce_before_resend_order_emails', array( $this, 'before_order_email' ) );
		add_action( 'woocommerce_after_resend_order_email', array( $this, 'after_email' ) );

		// Translate site title
		add_filter( 'woocommerce_email_format_string_replace', array( $this, 'format_string_replace' ), 10, 2 );
	}

	/**
	 * Set the preferred customer language at customer creation
	 *
	 * @since 0.1
	 *
	 * @param int $user_id User ID.
	 */
	public function created_customer( $user_id ) {
		update_user_meta( $user_id, 'locale', get_locale() );
	}

	/**
	 * May be change the customer language when he places a new order
	 * The chosen language is the currently browsed language
	 *
	 * @since 1.0
	 *
	 * @param int $order_id Order ID.
	 */
	public function new_order( $order_id ) {
		if ( PLL() instanceof PLL_Frontend ) {
			$order = wc_get_order( $order_id );
			if ( ( $user_id = $order->get_user_id() ) ) {
				$order_locale = $this->data_store->get_language( $order_id, 'locale' );
				$user_locale  = get_user_meta( $user_id, 'locale', true );
				if ( ! empty( $order_locale ) && $order_locale !== $user_locale ) {
					update_user_meta( $user_id, 'locale', $order_locale );
				}
			}
		}
	}

	/**
	 * Load WC text domain
	 *
	 * @since 1.0.2
	 */
	public function change_locale() {
		if ( is_locale_switched() ) {
			remove_filter( 'locale', array( PLL()->filters, 'get_locale' ) );
			add_filter( 'get_user_metadata', array( $this, 'filter_user_locale' ), 10, 3 );
		} else {
			if ( PLL() instanceof PLL_Frontend && isset( PLL()->filters ) ) {
				add_filter( 'locale', array( PLL()->filters, 'get_locale' ) );
			}
			remove_filter( 'get_user_metadata', array( $this, 'filter_user_locale' ), 10, 3 );
		}
		WC()->load_plugin_textdomain();
	}

	/**
	 * Set the email language
	 *
	 * @since 0.1
	 *
	 * @param object $language An instance of PLL_Language.
	 */
	public function set_email_language( $language ) {
		$this->switched_locale = switch_to_locale( $language->locale );

		// Set current language
		$this->saved_curlang = empty( PLL()->curlang ) ? null : PLL()->curlang;
		PLL()->curlang = $language;

		// Translates pages ids (to translate urls if any)
		foreach ( array( 'myaccount', 'shop', 'cart', 'checkout', 'terms' ) as $page ) {
			add_filter( 'option_woocommerce_' . $page . '_page_id', 'pll_get_post' );
		}

		/**
		 * Fires just after the language of the email has been set
		 *
		 * @since 0.1
		 */
		do_action( 'pllwc_email_language' );
	}

	/**
	 * Set the email language depending on the order language
	 *
	 * @since  0.1
	 *
	 * @param int|array|object $order Order or order ID.
	 */
	public function before_order_email( $order ) {
		if ( is_numeric( $order ) ) {
			$order_id = $order;
		} elseif ( is_array( $order ) ) {
			$order_id = $order['order_id'];
		} elseif ( is_object( $order ) ) {
			$order_id = $order->get_id();
		}

		if ( ! empty( $order_id ) ) {
			$lang = $this->data_store->get_language( $order_id );
			$language = PLL()->model->get_language( $lang );
			$this->set_email_language( $language );
		}
	}

	/**
	 * Set the email language depending on the user language
	 *
	 * @since 0.1
	 *
	 * @param int|string $user User ID or user login.
	 */
	public function before_user_email( $user ) {
		if ( is_numeric( $user ) ) {
			$user_id = $user;
		} else {
			$user = get_user_by( 'login', $user );
			$user_id = $user->ID;
		}

		$lang = get_user_meta( $user_id, 'locale', true );
		$lang = empty( $lang ) ? get_locale() : $lang;
		$language = PLL()->model->get_language( $lang );
		$this->set_email_language( $language );
	}

	/**
	 * Set the language back after the email has been sent
	 *
	 * @since 0.1
	 */
	public function after_email() {
		if ( ! empty( $this->switched_locale ) ) {
			unset( $this->switched_locale );
			restore_previous_locale();
		}

		// Set back the current language
		PLL()->curlang = $this->saved_curlang;

		foreach ( array( 'myaccount', 'shop', 'cart', 'checkout', 'terms' ) as $page ) {
			remove_filter( 'option_woocommerce_' . $page . '_page_id', array( $this, 'translate_wc_page_id' ) );
		}
	}

	/**
	 * Translate the site title which is filled before the email is triggered
	 *
	 * @since 0.5
	 *
	 * @param array  $replace Array of strings to replace placeholders in emails.
	 * @param object $email   Instance of WC_Email.
	 * @return array
	 */
	public function format_string_replace( $replace, $email ) {
		$replace['blogname']   = $email->get_blogname();
		$replace['site-title'] = $email->get_blogname();
		return $replace;
	}

	/**
	 * Filter the user locale, needed when sending the email from admin
	 *
	 * @since 1.0.3
	 *
	 * @param null|array|string $value    The value get_metadata() should return.
	 * @param int               $user_id  User ID.
	 * @param string            $meta_key Meta key.
	 * @return null|array|string The meta value.
	 */
	public function filter_user_locale( $value, $user_id, $meta_key ) {
		return 'locale' === $meta_key ? get_locale() : $value;
	}
}
