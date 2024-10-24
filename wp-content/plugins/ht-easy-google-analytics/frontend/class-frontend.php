<?php
namespace Ht_Easy_Ga4\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

class Frontend {
	use \Ht_Easy_Ga4\Helper_Trait;

	/**
	 * [$_instance]
	 *
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * [instance] Initializes a singleton instance
	 *
	 * @return Frontend
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		if ( $this->get_measurement_id2() ) {
			add_action( 'wp_head', array( $this, 'header_scirpt_render' ) );

			// Compatibility with WooCommerce redirect to cart after added to cart feature.
			if ( htga4()->get_option( 'add_to_cart_event' ) ) {
				// works both ajax non ajax.
				add_action( 'woocommerce_add_to_cart', array( $this, 'woocommerce_add_to_cart_cb' ), 10, 6 );

				// Detect added to cart proudct after redirect in the cart page.
				add_action( 'template_redirect', array( $this, 'detect_added_to_cart_after_redirect_in_cart_page' ), 10, 1 );
			}
		}
	}

	public function header_scirpt_render() {
		if ( $this->check_header_script_render_status() == false ) {
			return;
		}
		?>
			<!-- Global site tag (gtag.js) - added by HT Easy Ga4 -->
			<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js( $this->get_measurement_id2() ); ?>"></script>
			<script>
				window.dataLayer = window.dataLayer || [];
				function gtag(){dataLayer.push(arguments);}
				gtag('js', new Date());

				gtag('config', <?php echo "'" . esc_js( $this->get_measurement_id2() ) . "'"; ?>);
			</script>
		<?php
	}

	/**
	 * Check if the header script should be rendered or not.
	 *
	 * @return bool
	 */
	public function check_header_script_render_status() {
		$return_value = true;

		// If the current user is of the excluded user roles return false.
		if ( is_user_logged_in() ) {
			$exclude_user_roles = $this->get_option( 'exclude_roles' );

			if( !is_array($exclude_user_roles) ){
				$exclude_user_roles = [];
			}

			$current_user_id    = get_current_user_id();
			$current_user       = get_userdata( $current_user_id );
			$current_user_roles = $current_user->roles;

			if ( ! empty( $exclude_user_roles ) && array_intersect( $exclude_user_roles, $current_user_roles ) ) {
				$return_value = false;
			}
		}

		return $return_value;
	}

	/**
	 * Add to cart action
	 */
	public function woocommerce_add_to_cart_cb( $cart_id, $product_id, $request_quantity, $variation_id, $variation, $cart_item_data ) {
		$item_id  = $variation_id ? $variation_id : $product_id;
		$item_arr = array(
			'item_id'  => $item_id,
			'quantity' => $request_quantity,
		);

		// redirect to cart should be enabled.
		if ( 'yes' == get_option( 'woocommerce_cart_redirect_after_add' ) ) {
			WC()->session->set( 'whols_last_added_item', $item_arr );
		}
	}

	/**
	 * Detect added to cart proudct after redirect in the cart page
	 */
	public function detect_added_to_cart_after_redirect_in_cart_page() {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		// redirect to cart should be enabled.
		if ( 'yes' != get_option( 'woocommerce_cart_redirect_after_add' ) ) {
			return;
		}

		// Notices are somehow removed from the session from the inner hooks, so get the notices using init hook first.
		$last_added_item = WC()->session->get( 'whols_last_added_item' );

		// Last added item is not in the session.
		if ( ! $last_added_item ) {
			return;
		}

		add_action(
			'wp_footer',
			function() use ( $last_added_item ) {
				// should be the cart page.
				if ( is_cart() ) {
					$item_id  = $last_added_item['item_id'];
					$quantity = $last_added_item['quantity'];
					?>
					<script>
						;( function ( $ ) {
							$.ajax({
								url: htga4_params.ajax_url,
								type: 'POST',
								data: {
									'action': 'htga4_add_to_cart_event_ajax_action',
									'p_id': <?php echo esc_html( $item_id ); ?>,
									'quantity': <?php echo esc_html( $quantity ); ?>,
									'nonce' : htga4_params.nonce
								},

								success:function(response) {
									if( response.success && typeof gtag === 'function' ){
										gtag("event", "add_to_cart", response.data);
									}
								},

								error: function(errorThrown){
									alert(errorThrown);
								}
							});
						} )( jQuery );
					   
					</script>
					<?php

					WC()->session->__unset( 'whols_last_added_item' ); // Our job is done, clear the session data.
				}
			}
		);
	}
}

Frontend::instance();
