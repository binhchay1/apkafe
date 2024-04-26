<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Messages\Message;

/**
 * Abstract class for Screen.
 */
abstract class Ahrefs_Seo_Screen {

	/**
	 * View class instance.
	 *
	 * @var Ahrefs_Seo_View
	 */
	protected $view;

	/**
	 * Ahrefs token instance.
	 *
	 * @var Ahrefs_Seo_Token
	 */
	protected $token;

	/**
	 * Current (WordPress's) screen id.
	 *
	 * @var string
	 */
	protected $screen_id;

	/**
	 * Constructor
	 *
	 * @param Ahrefs_Seo_View $view View instance.
	 */
	public function __construct( Ahrefs_Seo_View $view ) {
		$this->view  = $view;
		$this->token = Ahrefs_Seo_Token::get();

		$this->register_ajax_handlers(); // called during "init" action.

		if ( ! defined( 'DOING_AJAX' ) || ( defined( 'DOING_AJAX' ) && ! DOING_AJAX ) ) {
			add_filter( 'admin_footer_text', [ $this, 'admin_footer_text' ], 100 );
			add_filter( 'update_footer', [ $this, 'update_footer' ], 100 );
		}
	}

	/**
	 * Return action name of nonce for a page.
	 * Result based on actual children class name.
	 *
	 * @return string
	 */
	public function get_nonce_name() : string {
		$class = strtolower( get_called_class() );
		$pos   = strrpos( $class, '\\' );
		if ( $pos ) {
			$class = substr( $class, $pos + 1 );
		}
		return 'ahrefs_' . $class;
	}

	/**
	 * Return name of nonce for a page.
	 * Static method.
	 *
	 * @return string
	 */
	public static function get_nonce_name_static() : string {
		$class = strtolower( get_called_class() );
		$pos   = strrpos( $class, '\\' );
		if ( $pos ) {
			$class = substr( $class, $pos + 1 );
		}
		return 'ahrefs_' . $class;
	}

	/**
	 * Set screen id of admin page for this screen.
	 * Register 'process_post_data' method as action.
	 *
	 * @param string $screen_id Screen id.
	 */
	public function set_screen_id( string $screen_id ) : void {
		$this->screen_id = $screen_id;
		add_action( 'ahrefs_seo_process_data_' . $screen_id, [ $this, 'process_post_data' ] );
	}

	/**
	 * Process post request from a page if any
	 */
	public function process_post_data() : void {
		// request for the Google auth.
		if ( isset( $_POST['google_auth'] ) && check_admin_referer( Ahrefs_Seo_Analytics::NONCE_INTERNAL_REDIRECT ) ) {
			// disconnect any existing account.
			Ahrefs_Seo_Analytics::get()->disconnect();
			// redirect to external oauth URL or to the dashboard page with error message.
			wp_redirect( Ahrefs_Seo_Analytics::get()->get_oauth2_url() ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			die();
		}
		if ( isset( $_GET['google_sites_list'] ) && check_admin_referer( Ahrefs_Seo_Analytics::NONCE_INTERNAL_REDIRECT, 'google_sites_list' ) ) {
			// redirect to external sites list URL or to the dashboard page with error message.
			wp_redirect( Ahrefs_Seo_Analytics::get()->get_sites_url() ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			die();
		}

		// Google oAuth callback with code or error.
		if ( isset( $_GET['action'] ) && ( 'google_oauth' === $_GET['action'] ) ) {
			$error = null;
			if ( ! empty( $_GET['error'] ) && empty( $_GET['code'] ) ) {
				$error = sanitize_text_field( wp_unslash( $_GET['error'] ) );
				switch ( strtolower( $error ) ) { // translate to human-readable message.
					case 'admin_policy_enforced':
						$error = __( 'The Google Account is unable to authorize one or more scopes requested due to the policies of their Google Workspace administrator.', 'ahrefs-seo' );
						break;
					case 'disallowed_useragent':
						$error = __( "'The authorization endpoint is displayed inside an embedded user-agent disallowed by Google's OAuth 2.0 Policies.", 'ahrefs-seo' );
						break;
					case 'org_internal':
						$error = __( 'The OAuth client ID in the request is part of a project limiting access to Google Accounts in a specific Google Cloud Organization.', 'ahrefs-seo' );
						break;
					case 'redirect_uri_mismatch':
						$error = __( 'The redirect_uri passed in the authorization request does not match an authorized redirect URI for the OAuth client ID.', 'ahrefs-seo' );
						break;
					case 'expired':
						$error = __( 'Authorization session expired. The session expires in 10 minutes, please try again.', 'ahrefs-seo' );
						break;
					case 'cancelled':
						$error = __( 'The setup was cancelled', 'ahrefs-seo' );
						break;
					case 'access_denied':
						$error = __( 'Access denied', 'ahrefs-seo' );
						break;
					case 'incomplete-scope':
						$error = __( 'The setup was interrupted because you did not grant the necessary permissions. You’ll need to redo the Google account setup – make sure to approve all permissions at the authentication stage.', 'ahrefs-seo' );
						break;
					case 'revoked':
						$error = ( new Settings_Google() )->revoke_access( $this, isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : null );
						break;
					default:
						$error = str_replace( '-', ' ', $error );
						break;
				}
			} elseif ( ! empty( $_GET['code'] ) ) { // code received? Apply it!
				if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_MENU ) ) {
					$error = ( new Settings_Google() )->apply_options( $this );
				} else {
					$error = Message::action_not_allowed();
				}
			}
			$url = Ahrefs_Seo::get()->initialized_get() ? Links::settings( Ahrefs_Seo_Screen_Settings::TAB_ANALYTICS ) : Links::wizard_step( 2 );
			if ( ! is_null( $error ) ) {
				$url = add_query_arg( [ 'error' => $error ], $url );
			}

			header( 'Location: ' . $url );
			exit();
		}
	}

	/**
	 * Register ajax handlers if any
	 */
	abstract public function register_ajax_handlers() : void;

	/**
	 * Show a page
	 */
	abstract public function show() : void;

	/**
	 * Add our footer template to footer.
	 *
	 * @param null|string $text Default content.
	 * @return null|string Final content.
	 */
	public function admin_footer_text( $text = '' ) {
		$screen = get_current_screen();
		if ( ! is_null( $screen ) && ( $screen->id === $this->screen_id ) ) {
			ob_start();
			$this->view->show_part( 'footer-text' );
			$text = (string) ob_get_clean();
		}
		return $text;
	}

	/**
	 * Remove text from footer on plugin's admin pages.
	 *
	 * @param null|string $text Default text.
	 * @return null|string Final text.
	 */
	public function update_footer( $text = '' ) {
		$screen = get_current_screen();
		if ( ! is_null( $screen ) && ( $screen->id === $this->screen_id ) ) {
			$text = '';
		}
		return $text;
	}

	/**
	 * Get template variables for view call
	 *
	 * @return array<string, mixed>
	 */
	public function get_template_vars() : array {
		return [];
	}

	/**
	 * Get classes for header block based on current user restrictions
	 *
	 * @since 0.9.5
	 *
	 * @param string[] $classes Predefined classes list.
	 * @return string[]
	 */
	public function get_header_classes( array $classes ) : array {
		if ( ! current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_RUN ) ) {
			$classes[] = 'uiroles-hidden-run-audit';
		}
		if ( ! current_user_can( Ahrefs_Seo::CAP_SETTINGS_AUDIT_VIEW ) ) {
			$classes[] = 'uiroles-hidden-settings-scope';
		}
		if ( ! current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_VIEW ) ) {
			$classes[] = 'uiroles-hidden-settings-account';
		}
		if ( ! current_user_can( Ahrefs_Seo::CAP_SETTINGS_SCHEDULE_VIEW ) ) {
			$classes[] = 'uiroles-hidden-settings-schedule';
		}
		return $classes;
	}
}
