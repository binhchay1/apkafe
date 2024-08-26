<?php
namespace Ht_Easy_Ga4;

use Ht_Easy_Ga4\Admin\Tabs\Standard_Reports;
use Ht_Easy_Ga4\Admin\Tabs\Ecommerce_Reports;
use Ht_Easy_Ga4\Admin\Tabs\Realtime_Reports;

class Base {
	use \Ht_Easy_Ga4\Helper_Trait; 
	use \Ht_Easy_Ga4\Rest_Request_Handler_Trait;

	/**
	 * [$_instance]
	 *
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * [instance] Initializes a singleton instance
	 *
	 * @return Base
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Standard_Reports instance.
	 *
	 * @return Standard_Reports
	 */
	public $standard_reports;

	/**
	 * Ecommerce_Reports instance.
	 *
	 * @return Ecommerce_Reports
	 */
	public $ecommerce_reports;

	/**
	 * Realtime_Reports instance.
	 *
	 * @return Realtime_Reports
	 */
	public $realtime_reports;

	public $response_message;

	public static $htga4_rest_base_url = '';

	public function __construct() {
		self::$htga4_rest_base_url = $this->get_config('redirect_uris') . '/index.php?rest_route=/htga4/';

		// Load text domain.
		add_action( 'init', array( $this, 'i18n' ) );

		// Include files.
		add_action( 'plugins_loaded', array( $this, 'includes' ) );

		// Add settings in plugin action.
		add_filter(
			'plugin_action_links_' . HT_EASY_GA4_BASE,
			function( $links ) {
				$link = sprintf( "<a href='%s'>%s</a>", esc_url( admin_url( 'admin.php?page=ht-easy-ga4-setting-page' ) ), __( 'Settings', 'ht-easy-ga4' ) );

				array_push( $links, $link );

				return $links;
			}
		);

		// Save the code for after login
		add_action('plugins_loaded', function(){
			$htga4_email = !empty( $_GET['email']) ? sanitize_email( $_GET['email']) : ''; // phpcs:ignore
			$htga4_sr_api_key = !empty( $_GET['key']) ? sanitize_text_field( $_GET['key']) : ''; // phpcs:ignore
			
			$nonce = !empty( $_GET['_wpnonce']) ? sanitize_text_field( $_GET['_wpnonce']) : '';  // phpcs:ignore
			$nonce_check_result = wp_verify_nonce($nonce, 'htga4_save_key_nonce');

			if( $nonce_check_result && $htga4_email && current_user_can('manage_options') ){
				update_option('htga4_email', $htga4_email);
				update_option('htga4_sr_api_key', $htga4_sr_api_key);

				$admin_url 		= admin_url('admin.php?page=ht-easy-ga4-setting-page');
				if( $this->is_ngrok_url() ){
					$admin_url = $this->get_ngrok_url() . '/wp-admin/admin.php?page=ht-easy-ga4-setting-page';
				}

				header("Location:$admin_url");
			}
		});

		// Generate access token after expired
		if ( get_option( 'htga4_email' ) && ! get_transient( 'htga4_access_token' ) && $this->get_data( 'page' ) == 'ht-easy-ga4-setting-page' ) {
			$this->generate_access_token( get_option( 'htga4_email' ) );
		}

		// Action when login & logout.
		add_action( 'admin_init', array( $this, 'login' ) );
		add_action( 'admin_init', array( $this, 'logout' ) );
	}

	public function i18n() {
		load_plugin_textdomain( 'ht-easy-ga4', false, dirname( plugin_basename( HT_EASY_GA4_ROOT ) ) . '/languages/' );
	}

	public function includes() {
		require_once HT_EASY_GA4_PATH . 'includes/class-manage-assets.php';
		require_once HT_EASY_GA4_PATH . 'includes/class-ajax-actions.php';

		require_once HT_EASY_GA4_PATH . 'admin/class-admin.php';
		require_once HT_EASY_GA4_PATH . 'admin/tabs/class-general-options.php';
		require_once HT_EASY_GA4_PATH . 'admin/tabs/class-events-tracking.php';
		require_once HT_EASY_GA4_PATH . 'admin/tabs/class-standard-reports.php';
		$this->standard_reports = Standard_Reports::instance();
		
		require_once HT_EASY_GA4_PATH . 'admin/tabs/class-ecommerce-reports.php';
		$this->ecommerce_reports = Ecommerce_Reports::instance();

		require_once HT_EASY_GA4_PATH . 'admin/tabs/class-realtime-reports.php';
		$this->realtime_reports = Realtime_Reports::instance();

		require_once HT_EASY_GA4_PATH . 'admin/class-recommended-plugins.php';
		require_once HT_EASY_GA4_PATH . 'admin/class-recommended-plugins-init.php';

		if( is_admin() ){
			require_once ( HT_EASY_GA4_PATH .'admin/class-trial.php' );
			require_once ( HT_EASY_GA4_PATH .'admin/class-diagnostic-data.php' );
		}

		require_once HT_EASY_GA4_PATH . 'frontend/class-frontend.php';
	}

	public function login() {
		$get_data = wp_unslash( $_GET ); // phpcs:ignore

		if (  current_user_can('manage_options') && ! empty( $get_data['access_token'] ) && ! empty( $get_data['email'] ) ) {
			set_transient( 'htga4_access_token', sanitize_text_field( $get_data['access_token'] ), ( MINUTE_IN_SECONDS * 58 ) );
			update_option( 'htga4_email', sanitize_email( $get_data['email'] ) );
		}
	}

	public function logout() {
		// Previllage check.
		if( !current_user_can('manage_options') ){
			return;
		}
		
		$get_data = wp_unslash( $_GET ); // phpcs:ignore

		// Return if there is no email, so no post request is sent.
		if ( ! get_option( 'htga4_email' ) ) {
			return;
		}

		$mail = get_option( 'htga4_email' );

		if ( ! empty( $get_data['htga4_logout'] ) ) {
			$this->clear_data();

			// Delete access_token & email.
			delete_option( 'ht_easy_ga4_options' );

			$response = wp_remote_post(
				self::$htga4_rest_base_url . 'v1/delete-data',
				array(
					'timeout'   => 20,
					'body'      => array(
						'email' => sanitize_email( $mail ),
					),
					'sslverify' => false,
				)
			);

			// If the request is success.
			if ( ! is_wp_error( $response ) ) {

				$response_body = json_decode( $response['body'], true );
				if ( ! empty( $response_body['success'] ) ) {
					delete_option( 'htga4_email' );

					wp_safe_redirect( $this->get_current_admin_url() );
					return;
				}
			}

			// if the response is WP_Error.
			if ( is_wp_error( $response ) ) {
				$this->response_message = $response->get_error_message();

				add_action(
					'admin_notices',
					function() {
						?>
					<div class="notice notice-error is-dismissible">
						<p><?php echo esc_html__( 'Something went wrong: ', 'ht-easy-ga4' ) . wp_kses_post( $this->response_message ); ?></p>
					</div>
						<?php
					}
				);

				return;
			}

			$response_body = json_decode( $response['body'], true );

			// Record not found.
			if ( ! empty( $response_body['message'] ) ) {
				$this->response_message = $response_body['message'];

				add_action(
					'admin_notices',
					function() {
						?>
					<div class="notice notice-error is-dismissible">
						<p><?php echo esc_html__( 'Something went wrong: ', 'ht-easy-ga4' ) . wp_kses_post( $this->response_message ); ?></p>
					</div>
						<?php
					}
				);

				return;
			}
		}
	}

	public function clear_data() {
		delete_transient( 'htga4_access_token' );
		delete_transient( 'htga4_accounts' );
		delete_transient( 'htga4_properties' );
		delete_transient( 'htga4_data_streams' );

		delete_option( 'htga4_email' );
		delete_option( 'htga4_sr_api_key' );
	}
}