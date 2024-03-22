<?php
/**
 * Loading Google Analytics 4 scripts in header.
 */
class Ht_Easy_Ga4 {
	use \HtEasyGa4\Helper_Trait;

	/**
	 * [$_instance]
	 *
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * [instance] Initializes a singleton instance
	 *
	 * @return [Easy_Google_Analytics]
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

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

		if ( $this->get_measurement_id() ) {
			add_action( 'wp_head', array( $this, 'header_scirpt_render' ) );
		}

		// Save the code for after login
		add_action('plugins_loaded', function(){
			$htga4_email = !empty( $_GET['email']) ? sanitize_email( $_GET['email']) : '';
			$htga4_sr_api_key = !empty( $_GET['key']) ? sanitize_text_field( $_GET['key']) : '';

			if( $htga4_email && current_user_can('manage_options') ){
				update_option('htga4_email', $htga4_email);
				update_option('htga4_sr_api_key', $htga4_sr_api_key);

				$admin_url 		= admin_url('admin.php?page=ht-easy-ga4-setting-page');
				$forwarded_host = !empty($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : ''; // development mode

				if( $forwarded_host == 'dominant-fleet-swan.ngrok-free.app' ){
					$admin_url = 'https://dominant-fleet-swan.ngrok-free.app/wp-admin/admin.php?page=ht-easy-ga4-setting-page';
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
		require_once HT_EASY_GA4_PATH . 'admin/Recommended_Plugins.php';
		require_once HT_EASY_GA4_PATH . 'admin/plugin-recommendations.php';
		require_once HT_EASY_GA4_PATH . 'includes/class.manage-assets.php';
		require_once HT_EASY_GA4_PATH . 'admin/admin-init.php';

		require_once HT_EASY_GA4_PATH . 'includes/ajax-actions.php';
	}

	public function header_scirpt_render() {
		if( $this->check_header_script_render_status() == false ){
			return;
		}
		?>
		   <!-- Global site tag (gtag.js) - added by HT Easy Ga4 -->
		   <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js( $this->get_measurement_id() ); ?>"></script>
		   <script>
		   window.dataLayer = window.dataLayer || [];
		   function gtag(){dataLayer.push(arguments);}
		   gtag('js', new Date());
   
		   gtag('config', <?php echo "'" . esc_js( $this->get_measurement_id() ) . "'"; ?>);
		   </script>
		<?php
	}

	public function login() {
		$get_data = wp_unslash( $_GET );

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
		
		$get_data = wp_unslash( $_GET );

		// Return if there is no email, so no post request is sent.
		if ( ! get_option( 'htga4_email' ) ) {
			return;
		}

		if ( ! empty( $get_data['htga4_logout'] ) ) {
			$this->clear_data();

			// Delete access_token & email.
			delete_option( 'ht_easy_ga4_options' );

			$response = wp_remote_post(
				$this::$htga4_rest_base_url . 'v1/delete-data',
				array(
					'timeout'   => 20,
					'body'      => array(
						'email' => sanitize_email( get_option( 'htga4_email' ) ),
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

		delete_option( 'htga4_sr_api_key' );
	}

	/**
	 * Check if the header script should be rendered or not.
	 *
	 * @return bool
	 */
	public function check_header_script_render_status(){
		$return_value = true;

		// If the current user is of the excluded user roles return false.
		if( is_user_logged_in() ){
			$exclude_user_roles = $this->get_option('exclude_roles');

			$current_user_id    = get_current_user_id();
			$current_user       = get_userdata( $current_user_id );
			$current_user_roles = $current_user->roles;
			

			if( !empty($exclude_user_roles) && array_intersect($exclude_user_roles, $current_user_roles) ){
				$return_value = false;
			}
		}

		return $return_value;
	}
}

Ht_Easy_Ga4::instance();
