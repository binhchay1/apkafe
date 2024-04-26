<?php
declare( strict_types=1 );

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Admin_Notice\Google_Connection;
use ahrefs\AhrefsSeo\Disconnect_Reason\Disconnect_Reason_GA;
use ahrefs\AhrefsSeo\Disconnect_Reason\Disconnect_Reason_Google;
use ahrefs\AhrefsSeo\Disconnect_Reason\Disconnect_Reason_GSC;
use ahrefs\AhrefsSeo\Messages\Message;
use ahrefs\AhrefsSeo\Options\Temporary_Code;
use ahrefs\AhrefsSeo\Workers\Worker_Position;
use ahrefs\AhrefsSeo\Workers\Worker_Traffic;
use ahrefs\AhrefsSeo_Vendor\Google\Client as Google_Client;
use ahrefs\AhrefsSeo_Vendor\Google_Service_Exception;
use ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Exception\ClientException as GuzzleClientException;
use ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Exception\ConnectException as GuzzleConnectException;
use ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Error;
use Exception;
use InvalidArgumentException;
use LogicException;

/**
 * Class for interacting with Google Analytics and Google Search Console API.
 */
class Ahrefs_Seo_Analytics extends Ahrefs_Seo_Abstract_Api {
	use Analytics_Gsc, Analytics_Ga;

	private const OPTION_LAST_ERROR = 'ahrefs-seo-analytics-last-error';

	private const OPTION_HAS_ACCOUNT_GA     = 'ahrefs-seo-has-analytics-account'; // has GA or GA4 profiles to select from.
	private const OPTION_HAS_ACCOUNT_GA_RAW = 'ahrefs-seo-has-analytics-account-raw'; // has account, note: account may not have any profile.
	private const OPTION_HAS_ACCOUNT_GSC    = 'ahrefs-seo-has-gsc-account';
	private const OPTION_GSC_SITES          = 'ahrefs-seo-has-gsc-sites';

	public const NONCE_INTERNAL_REDIRECT = 'ahrefs-seo-google-nonce-1';

	/** Allow to send queries once per second. */
	const API_MIN_DELAY = 2.5;

	const SCOPE_ANALYTICS      = 'https://www.googleapis.com/auth/analytics.readonly';
	const SCOPE_SEARCH_CONSOLE = 'https://www.googleapis.com/auth/webmasters.readonly';
	const GSC_KEYWORDS_LIMIT   = 10;

	/**
	 * Load page size for traffic requests.
	 */
	private const QUERY_TRAFFIC_PER_PAGE = 20;
	/**
	 * Load first 100 results (pages) and search existing page slugs here.
	 */
	private const QUERY_DETECT_GA_LIMIT = 100;
	/**
	 * Load first 1000 results (search phrases) and search existing page slugs here.
	 */
	private const QUERY_DETECT_GSC_LIMIT = 1000;
	/**
	 * Page size for account details loading.
	 */
	private const QUERY_LIST_GA_ACCOUNTS_PAGE_SIZE = 100;
	/**
	 * How many items load per request if GA profile used.
	 */
	protected const REQUEST_SIZE_GA = 2;
	/**
	 * How many items load per request if GA4 profile used.
	 */
	protected const REQUEST_SIZE_GA4  = 20;
	public const TRAFFIC_TYPE_TOTAL   = 'total';
	public const TRAFFIC_TYPE_ORGANIC = 'Organic Search';


	/** @var Ahrefs_Seo_Analytics */
	private static $instance = null;
	/**
	 * Error message.
	 *
	 * @var string
	 */
	protected $message = '';
	/**
	 * @var array
	 */
	protected $service_error = [];
	/**
	 * User's account (profiles) list for GA is not empty.
	 * Null if unknown.
	 *
	 * @var null|bool
	 */
	protected $has_ga_account;
	/**
	 * User has at least single GA account. This is not mean, that user has any accessible profile.
	 * Null if unknown.
	 *
	 * @var null|bool
	 */
	protected $has_ga_account_raw;
	/**
	 * User's account (profiles) list for GSC is not empty.
	 * Null if unknown.
	 *
	 * @var null|bool
	 */
	protected $has_gsc_account;
	/**
	 * Cached accounts (profiles) list for GA.
	 * Used for choice at Google accounts page.
	 *
	 * @var array|null
	 */
	protected $accounts_ga;
	/**
	 * Cached accounts (profiles) list for GA4.
	 * Used for choice at Google accounts page.
	 *
	 * @var array|null
	 */
	protected $accounts_ga4;
	/**
	 * Cached accounts list for GA.
	 * Used for choice at Google accounts page.
	 *
	 * @var array|null
	 */
	protected $accounts_ga_raw;
	/**
	 * Cached accounts list for GA4.
	 * Used for choice at Google accounts page.
	 *
	 * @var array|null
	 */
	protected $accounts_ga4_raw;
	/**
	 * Cached accounts list for GSC.
	 *
	 * @var array|null
	 */
	protected $accounts_gsc;
	/** @var ?Analytics_Client */
	private $analytics_client;
	/** @var float[] Time when last visitors query to GA, GA4 or GSC run. */
	private $last_query_time = [];
	/**
	 * @var string
	 */
	private $api_user = '';
	/** @var bool Paused because last request returned rate error. */
	private $gsc_paused = false;

	/** @var bool */
	private $account_just_disconnected = false;
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->api_user           = substr(
			'w' . implode(
				'-',
				[
					get_current_user_id(),
					get_current_blog_id(),
					wp_parse_url( get_home_url(), PHP_URL_HOST ) ?? '',
				]
			),
			0,
			40
		);
		$this->has_ga_account     = get_option( self::OPTION_HAS_ACCOUNT_GA, null );
		$this->has_ga_account_raw = get_option( self::OPTION_HAS_ACCOUNT_GA_RAW, null );
		$this->has_gsc_account    = get_option( self::OPTION_HAS_ACCOUNT_GSC, null );
		$this->get_data_tokens()->tokens_load(); // this will create and initialize data_tokens property.
	}

	/**
	 * @return Data_Tokens_Storage
	 */
	public function get_data_tokens() : Data_Tokens_Storage {
		return $this->get_analytics_client()->get_data_tokens();
	}

	/**
	 * @return Analytics_Client
	 */
	public function get_analytics_client() : Analytics_Client {
		if ( is_null( $this->analytics_client ) ) {
			$this->analytics_client = new Analytics_Client( $this );
		}

		return $this->analytics_client;
	}

	/**
	 * Maybe disconnect Google using 'disconnect' link.
	 * Static function.
	 *
	 * @param Ahrefs_Seo_Screen $screen Screen instance.
	 *
	 * @return void
	 */
	public static function maybe_disconnect( Ahrefs_Seo_Screen $screen ) : void {
		if ( isset( $_GET['disconnect-analytics'] ) && check_admin_referer( $screen->get_nonce_name(), 'disconnect-analytics' ) && current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_SAVE ) ) {
			// disconnect Analytics.
			self::get()->disconnect();
			// show notice if any of Analytics settings changed.
			$params = [
				'page' => isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : Ahrefs_Seo::SLUG,
				'tab'  => isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : null,
				'step' => isset( $_GET['step'] ) ? sanitize_text_field( wp_unslash( $_GET['step'] ) ) : null,
			];
			Helper_Content::wp_redirect( remove_query_arg( [ 'disconnect-analytics' ], add_query_arg( $params, admin_url( 'admin.php' ) ) ) );
			die();
		}
	}

	/**
	 * Remove existing token.
	 */
	public function disconnect() : void {
		Ahrefs_Seo::breadcrumbs( sprintf( '%s', __METHOD__ ) );

		$this->get_analytics_client()->client_disconnect();
		delete_option( self::OPTION_HAS_ACCOUNT_GA );
		delete_option( self::OPTION_HAS_ACCOUNT_GA_RAW );
		delete_option( self::OPTION_HAS_ACCOUNT_GSC );
		delete_option( self::OPTION_GSC_SITES );
		( new Google_Connection() )->reset();
		Ahrefs_Seo::get()->initialized_set( null, false );
		wp_cache_flush();
		$this->accounts_ga_raw    = null;
		$this->accounts_ga        = null;
		$this->accounts_ga4       = null;
		$this->accounts_ga4_raw   = null;
		$this->accounts_gsc       = null;
		$this->has_ga_account     = null;
		$this->has_ga_account_raw = null;
		$this->has_gsc_account    = null;
	}

	/**
	 * Return the instance
	 *
	 * @return Ahrefs_Seo_Analytics
	 */
	public static function get() : Ahrefs_Seo_Analytics {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * User has at least single GA account.
	 * Cached result.
	 *
	 * @return bool
	 * @since 0.7.1
	 */
	public function is_analytics_has_accounts() : bool {
		return ! empty( $this->has_ga_account_raw );
	}

	/**
	 * Return url for OAuth2, where user will see a code
	 *
	 * @return string
	 */
	public function get_oauth2_url() : string {
		try {
			$client = $this->create_client();
			$client->setState( ( new Temporary_Code( 'oauth2' ) )->create_state_code() ); // get new state code.

			return $client->createAuthUrl();
		} catch ( Error $e ) {
			$message = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );
			$this->set_message( $message );
		}

		return '#error-happened';
	}

	/**
	 * @return Google_Client
	 */
	public function create_client() : Google_Client {
		return $this->get_analytics_client()->create_client();
	}

	/**
	 * Return url for OAuth2, where user will see a code
	 *
	 * @return string
	 */
	public function get_sites_url() : string {
		try {
			if ( ! $this->get_data_tokens()->is_using_direct_connection() ) {
				$client = $this->create_client();
				$client->setState( ( new Temporary_Code( 'oauth2' ) )->create_state_code( false ) ); // get new state code.
				if ( $client instanceof Google_Proxy_Client ) {
					return $client->load_sites_or_register_url_from_proxy( true );
				}
			}
		} catch ( Error $e ) {
			$message = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );
			$this->set_message( $message );
		}

		return '#error-happened';
	}

	/**
	 * Check received code.
	 * Update options if it is ok.
	 *
	 * @param string      $code Code.
	 * @param string|null $new_client_id New client ID.
	 * @return bool
	 */
	public function check_token( string $code, ?string $new_client_id = null ) : bool {
		Ahrefs_Seo::breadcrumbs( sprintf( '%s (%s, %s)', __METHOD__, (string) wp_json_encode( $code ), (string) wp_json_encode( $new_client_id ) ) );
		try {
			$client = $this->create_client();
			if ( $this->get_data_tokens()->is_token_set() ) {
				// another token exists? Disconnect it.
				$this->disconnect();
				$this->set_message( '' );
				// recreate client.
				$client = $this->create_client();
			}
			if ( is_string( $new_client_id ) ) {
				$client->setClientId( $new_client_id );
			}

			$prev_client_secret = $client->getClientSecret();
			$client_id          = $client->getClientId();
			if ( empty( $client_id ) ) {
				Ahrefs_Seo::breadcrumbs( sprintf( '%s: client: %s', __METHOD__, (string) wp_json_encode( (array) $client ) ), true );
				Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( 'Auth: empty client_id' ) );
			}
			if ( empty( $prev_client_secret ) ) {
				$client->setClientSecret( 'temp' );
			}
			try {
				$result = $client->fetchAccessTokenWithAuthCode( $code );
				$token  = $client->getAccessToken();
			} finally {
				$client->setClientSecret( $prev_client_secret );
			}
			if ( ! empty( $token ) ) {
				Ahrefs_Seo::breadcrumbs( sprintf( '%s: (%s)', __METHOD__, (string) wp_json_encode( $token ) ) );
				$this->set_message( '' );
				( new Disconnect_Reason_Google() )->clean_reason();
				$this->get_data_tokens()->save_raw_token( $token );
			} else { // no error, but code was wrong.
				if ( is_array( $result ) && isset( $result['error_description'] ) && is_string( $result['error_description'] ) ) {
					$this->set_message( $result['error_description'] );
				}
				return false;
			}
		} catch ( Error $e ) {
			$message = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );
			$this->set_message( $message );
		} catch ( InvalidArgumentException $e ) {
			$this->set_message( $this->extract_message( $e ), $e );

			return false;
		} catch ( Exception $e ) {
			$this->set_message( $this->extract_message( $e ), $e );

			return false;
		}

		return true;
	}

	/**
	 * Extract human-readable message from exception
	 *
	 * @param Exception   $e Exception.
	 * @param string|null $default_message Default message, translated.
	 * @param bool        $skip_disconnected_message Do not add disconnected account message, if account is disconnecting due an error.
	 *
	 * @return string|null
	 * @since 0.7.4
	 */
	protected function extract_message( Exception $e, ?string $default_message = null, bool $skip_disconnected_message = true ) : ?string {
		$result = $default_message ?? $e->getMessage();
		if ( $e instanceof Google_Service_Exception ) {
			/** @var array $errors */
			$errors = $e->getErrors();
			if ( is_array( $errors ) && count( $errors ) && isset( $errors[0]['message'] ) && isset( $errors[0]['reason'] ) ) {
				if ( $skip_disconnected_message && in_array(
					$errors[0]['reason'],
					[
						'userRateLimitExceeded',
						'rateLimitExceeded',
						'quotaExceeded',
						'internalError',
						'forbidden',
					],
					true
				) ) {
					/** @see Worker::on_rate_error() */
					$result = null; // no need to save and show this error, because other tip displayed.
				} else {
					$reason = preg_replace( '/(?<! )[A-Z]/', ' $0', $errors[0]['reason'] ); // camel case to words with a space as separator.
					$result = sprintf( '%s. %s', ucfirst( $reason ), $errors[0]['message'] );
				}
			} else {
				if ( false !== stripos( $e->getMessage(), 'The server encountered a temporary error' ) || false !== stripos( $e->getMessage(), 'Error 404' ) ) {
					$result = null; // no need to save and show this error, because other tip displayed.
				} else {
					$json = json_decode( $e->getMessage(), true );
					if ( is_array( $json ) && isset( $json['error_description'] ) && is_string( $json['error_description'] ) ) {
						$result = $json['error_description'];
					}
				}
			}
		} elseif ( $e instanceof GuzzleConnectException ) {
			$error = $e->getMessage();
			if ( false !== stripos( $error, 'could not resolve' ) ) { // "cURL error 6: Could not resolve host: www.googleapis.com (see https://curl.haxx.se/libcurl/c/libcurl-errors.html)".
				$result = sprintf( '%s. %s', __( 'Connection error', 'ahrefs-seo' ), __( 'Could not resolve host.', 'ahrefs-seo' ) );
			} elseif ( false !== stripos( $error, 'connection timed out' ) ) { // "cURL error 7: Failed to connect to analyticsreporting.googleapis.com port 443: Connection timed out (see https://curl.haxx.se/libcurl/c/libcurl-errors.html)".
				$result = sprintf( '%s. %s', __( 'Connection error', 'ahrefs-seo' ), __( 'Connection timed out.', 'ahrefs-seo' ) );
			} elseif ( false !== stripos( $error, 'operation timed out' ) ) { // "cURL error 28: Operation timed out after 120001 milliseconds with 0 bytes received (see https://curl.haxx.se/libcurl/c/libcurl-errors.html)".
				$result = sprintf( '%s. %s', __( 'Connection error', 'ahrefs-seo' ), __( 'Operation timed out.', 'ahrefs-seo' ) );
			} elseif ( false !== stripos( $error, 'Failed to connect' ) ) { // "cURL error 28: Operation timed out after 120001 milliseconds with 0 bytes received (see https://curl.haxx.se/libcurl/c/libcurl-errors.html)".
				$result = sprintf( '%s. %s', __( 'Connection error', 'ahrefs-seo' ), __( 'Failed to connect.', 'ahrefs-seo' ) );
			}
		} elseif ( $e instanceof GuzzleRequestException ) {
			$result = sprintf( '%s. %s', __( 'Request error', 'ahrefs-seo' ), $e->getMessage() );
		}

		return $result;
	}

	/**
	 * Access to GA enabled and account set in plugin options.
	 *
	 * @return bool
	 */
	public function is_ua_set() : bool {
		return '' !== $this->get_data_tokens()->get_ua_id() && $this->is_analytics_enabled();
	}

	/**
	 * Access to Google Analytics allowed and accounts (profiles) list is not empty
	 *
	 * @param bool $force_detection Force account detection.
	 *
	 * @return bool
	 */
	public function is_analytics_enabled( bool $force_detection = false ) : bool {
		if ( ( $force_detection || is_null( $this->has_ga_account ) ) && false !== strpos( $this->get_data_tokens()->get_token_scope_as_string(), self::SCOPE_ANALYTICS ) ) {
			$accounts_ga_all          = $this->load_accounts_list();
			$this->has_ga_account     = ! empty( $accounts_ga_all );
			$this->has_ga_account_raw = ! empty( $this->accounts_ga_raw ) || ! empty( $this->accounts_ga4_raw );
			update_option( self::OPTION_HAS_ACCOUNT_GA, $this->has_ga_account );
			update_option( self::OPTION_HAS_ACCOUNT_GA_RAW, $this->has_ga_account_raw );
		}

		return false !== strpos( $this->get_data_tokens()->get_token_scope_as_string(), self::SCOPE_ANALYTICS ) && $this->has_ga_account || defined( 'AHREFS_SEO_NO_GA' ) && AHREFS_SEO_NO_GA;
	}

	/**
	 * Get API user string for request to API
	 *
	 * @return string
	 */
	public function get_api_user() : string {
		return (string) $this->api_user;
	}

	/**
	 * Return service error or empty array
	 *
	 * @return array<array>
	 */
	public function get_service_error() : array {
		return (array) $this->service_error;
	}

	/**
	 * Access to GSC enabled and site set in plugin options.
	 *
	 * @return bool
	 */
	public function is_gsc_set() : bool {
		if ( '' !== $this->get_data_tokens()->get_gsc_site() && $this->is_gsc_enabled() ) {
			return true;
		}

		return false;
	}

	/**
	 * Access to Google Search Console allowed and accounts list is not empty
	 *
	 * @param bool $force_detection Force account detection.
	 *
	 * @return bool
	 */
	public function is_gsc_enabled( bool $force_detection = false ) : bool {
		if ( ( $force_detection || is_null( $this->has_gsc_account ) ) && false !== strpos( $this->get_data_tokens()->get_token_scope_as_string(), self::SCOPE_SEARCH_CONSOLE ) ) {
			if ( is_null( $this->accounts_gsc ) ) { // no existing value from another service call.
				$this->accounts_gsc = $this->load_gsc_accounts_list();
			}
			$this->has_gsc_account = ! empty( $this->accounts_gsc );
			update_option( self::OPTION_HAS_ACCOUNT_GSC, $this->has_gsc_account );
		}

		return false !== strpos( $this->get_data_tokens()->get_token_scope_as_string(), self::SCOPE_SEARCH_CONSOLE ) && $this->has_gsc_account;
	}

	/**
	 * Is request to GSC API paused?
	 *
	 * @return bool
	 * @since 0.7.4
	 */
	public function is_gsc_paused() : bool {
		return $this->gsc_paused;
	}

	/**
	 * Requests to GSC API are paused
	 *
	 * @param bool $is_paused Is audit paused.
	 *
	 * @return void
	 * @since 0.7.4
	 */
	public function set_gsc_paused( bool $is_paused ) : void {
		$this->gsc_paused = $is_paused;
	}

	/**
	 * Set client ID
	 *
	 * @param ?string $client_id Client ID.
	 *
	 * @return void
	 */
	public function set_client_id( ?string $client_id ) : void {
		$this->get_data_tokens()->set_client_id( $client_id );
	}

	/**
	 * Set client secret
	 *
	 * @param ?string $client_secret Client secret.
	 *
	 * @return void
	 */
	public function set_client_secret( ?string $client_secret ) : void {
		$this->get_data_tokens()->set_client_secret( $client_secret );
	}

	/**
	 * Do a minimal delay between requests.
	 * Used to prevent API rate errors.
	 *
	 * @param string $what_api What API used: 'ga', 'ga4' or 'gsc'.
	 * @param bool   $request_just_finished Do not pause, just set request time.
	 *
	 * @return void
	 */
	protected function maybe_do_a_pause( string $what_api, bool $request_just_finished = false ) : void {
		if ( ! $request_just_finished ) {
			$time_since = microtime( true ) - ( $this->last_query_time[ $what_api ] ?? 0 );
			if ( $time_since < self::API_MIN_DELAY && ! defined( 'AHREFS_SEO_IGNORE_DELAY' ) ) {
				$pause = intval( ceil( ( self::API_MIN_DELAY - $time_since ) * 1000000 ) );
				Ahrefs_Seo::breadcrumbs( sprintf( '%s(%s): %d', __METHOD__, $what_api, $pause ) );
				Ahrefs_Seo::usleep( $pause );
			}
		}
		$this->last_query_time[ $what_api ] = microtime( true );
	}

	/**
	 * Return lowercase domain name without 'www.'.
	 *
	 * @param null|string $url If null - return domain of current site.
	 *        Examples: http://www.example.com/ (for a URL-prefix property) or sc-domain:example.com (for a Domain property).
	 *
	 * @return string
	 */
	private function get_clean_domain( ?string $url = null ) : string {
		if ( is_null( $url ) ) {
			$result = strtolower( Ahrefs_Seo::get_current_domain() );
		} else {
			$result = 0 !== strpos( $url, 'sc-domain:' ) ? wp_parse_url( $url, PHP_URL_HOST ) : substr( $url, strlen( 'sc-domain:' ) ); // url or string "sc-domain:".
			$result = is_string( $result ) ? strtolower( $result ) : ''; // wp_parse_url may return null.
		}
		if ( 0 === strpos( $result, 'www.' ) ) {
			$result = substr( $result, 4 );
		}

		return $result;
	}

	/**
	 * Handle exception, set error message, maybe refresh token or disconnect on invalid token
	 *
	 * @param Exception $e Exception.
	 * @param bool      $set_service_error Set internal variable with error message.
	 * @param bool      $is_gsc Is exception coming from GSC.
	 * @param bool      $save_message Save message.
	 *
	 * @return void
	 */
	private function handle_exception( Exception $e, bool $set_service_error = false, bool $is_gsc = false, bool $save_message = true ) : void {
		Ahrefs_Seo::breadcrumbs( __METHOD__ . (string) wp_json_encode( [ (string) $e, $set_service_error, $is_gsc ] ) );
		if ( $e instanceof Google_Service_Exception ) {
			$no_report = false;
			if ( $set_service_error ) {
				$this->service_error = is_array( $e->getErrors() ) ? $e->getErrors() : [];
			}

			$error = json_decode( $e->getMessage(), true );
			if ( is_array( $error ) && isset( $error['error'] ) && in_array(
				$error['error'],
				[
					'invalid_grant',
					'unauthorized_client',
				],
				true
			) ) {
				// tokens are invalid.
				do_action( 'ahrefs_seo_analytics_token_disconnect' );
				$this->disconnect();
				$this->set_message( null ); // clean possible error message.
				( new Disconnect_Reason_Google() )->save_reason( __( 'Your Google account has been disconnected because token has been expired or revoked. Please try to reconnect or contact support if this keeps happening.', 'ahrefs-seo' ) );
			} elseif ( 403 === $e->getCode() ) {
				/** @var array $errors */
				$errors = $e->getErrors();

				if ( is_array( $errors ) && ( 0 < count( $errors ) ) && isset( $errors[0]['reason'] ) ) {
					$reason = $errors[0]['reason'];
					if ( 'forbidden' === $reason ) {
						if ( $is_gsc && $this->get_data_tokens()->get_gsc_site() ) { // if was not disconnected before.
							$site    = preg_match( "/site '([^']+)'/", $errors[0]['message'] ?? $e->getMessage(), $m ) ? $m[1] : $this->get_data_tokens()->get_gsc_site(); // get site from message.
							$message = ! empty( $site ) ?
								/* Translators: %s: site url */
								sprintf( __( 'Your Google account has been disconnected because you don’t have the required permission for %s site.', 'ahrefs-seo' ), $site )
								:
								__( 'Your Google account has been disconnected because you don’t have the required permission for this site.', 'ahrefs-seo' );

							$this->set_gsc_disconnect_reason( $message );

							$this->set_message( $message, $e ); // this will submit error.
							$no_report = true;
						} else {
							$ga      = $this->get_data_tokens()->get_ua_id();
							$message = ! empty( $ga ) ?
								/* Translators: %s: profile name */
								sprintf( __( 'Your Google account has been disconnected because you don’t have the sufficient permissions for %s profile.', 'ahrefs-seo' ), $ga )
								:
								__( 'Your Google account has been disconnected because you don’t have the sufficient permissions for this profile.', 'ahrefs-seo' );

							$this->set_ga_disconnect_reason( $message );
						}
					} elseif ( 'insufficientPermissions' === $reason && isset( $errors[0]['message'] ) && 'User does not have any Google Analytics account.' === $errors[0]['message'] ) { // do not translate 'User does not....'.
						$message = __( 'Google Search Console account does not exists.', 'ahrefs-seo' );
						$this->set_message( $message );
						$no_report = true;
					}
				}
			} elseif ( 401 === $e->getCode() ) {
				$this->try_to_refresh_token();
			} elseif ( 400 === $e->getCode() ) {
				/** @var array $errors */
				$errors = $e->getErrors();
				if ( is_array( $errors ) && ( 0 < count( $errors ) ) && isset( $errors[0]['reason'] ) ) {
					$err = $errors[0];
					if ( ( 'invalidParameter' === $err['reason'] ) && ( 'siteUrl' === ( $err['location'] ?? '' ) ) ) {
						/* Translators: %s: original error message */
						$message = sprintf( __( 'Your Google account has been disconnected because of error: %s', 'ahrefs-seo' ), $err['message'] ?? $err['reason'] );
						$this->set_gsc_disconnect_reason( $message );
						$this->set_message( $message, $e ); // this will submit error.
						$no_report = true;
					}
				}
			}

			if ( ! $no_report ) {
				$this->set_message( $this->extract_message( $e ), $e );
			}
		} elseif ( $e instanceof GuzzleRequestException ) { // GuzzleConnectException and GuzzleClientException.
			if ( strpos( $e->getMessage(), '"error"' ) && ( strpos( $e->getMessage(), '"invalid_grant"' ) || strpos( $e->getMessage(), '"invalid_token"' ) ) ) {
				do_action( 'ahrefs_seo_analytics_token_disconnect' );
				$this->disconnect();
				$this->set_gsc_disconnect_reason( '' );
				( new Disconnect_Reason_Google() )->save_reason( 'Your Google account has been disconnected because token has been expired or revoked.' );
			}
			Ahrefs_Seo::notify( $e );
		} elseif ( $e instanceof Ahrefs_Seo_Compatibility_Exception ) {
			Content_Audit::audit_stop( [ Message::google_api_error( $e->getMessage() ) ] );
			Ahrefs_Seo::notify( $e );
		} else { // \Exception.
			Ahrefs_Seo::breadcrumbs( 'Events ' . (string) wp_json_encode( $this->get_analytics_client()->get_logged_events() ) );
			if ( $save_message ) {
				$this->set_message( $this->extract_message( $e ), $e );
			}
			Ahrefs_Seo::notify( $e );
		}
	}

	/**
	 * Set disconnect reason for GCS if any.
	 *
	 * @param string|null $string Null if not disconnected.
	 * @param bool        $reset_gsc_account Reset GSC account.
	 *
	 * @return void
	 */
	public function set_gsc_disconnect_reason( ?string $string = null, bool $reset_gsc_account = true ) : void {
		if ( $reset_gsc_account && ! is_null( $string ) ) {
			$this->set_ua( $this->get_data_tokens()->get_ua_id(), $this->get_data_tokens()->get_ua_name(), $this->get_data_tokens()->get_ua_url() );
		}
		( new Disconnect_Reason_GSC() )->save_reason( $string );
	}

	/**
	 * Set GA and GSC accounts
	 *
	 * @param string $ua_id UA id.
	 * @param string $ua_name UA name.
	 * @param string $ua_url UA url.
	 * @param string $gsc_site GSC site.
	 *
	 * @return void
	 */
	public function set_ua( string $ua_id, string $ua_name, string $ua_url, string $gsc_site = '' ) : void {
		Ahrefs_Seo::breadcrumbs( sprintf( '%s (%s) (%s) (%s) (%s)', __METHOD__, $ua_id, $ua_name, $ua_url, $gsc_site ) );
		$is_gsc_updated = $this->get_data_tokens()->get_gsc_site() !== $gsc_site;
		$is_ga_updated  = $this->get_data_tokens()->get_ua_id() !== $ua_id;
		$this->reset_pause( $is_ga_updated, $is_gsc_updated );

		$this->get_data_tokens()->save_values( $ua_id, $ua_name, $ua_url, $gsc_site );
		if ( $is_gsc_updated ) {
			$this->set_gsc_disconnect_reason(); // reset any error.
			if ( '' !== $gsc_site ) { // do not check if site is empty.
				if ( ! $this->gsc_check_domain() ) { // set error if domain is incorrect.
					$gsc_site = ''; // ... and reset gsc account.
					$this->get_data_tokens()->save_values( $ua_id, $ua_name, $ua_url, $gsc_site );
				}
			}
		}
		if ( $is_ga_updated ) {
			$this->set_ga_disconnect_reason(); // reset any error.
		}
	}

	/**
	 * Reset pause for GA or GSC.
	 *
	 * @param bool $reset_ga Reset GA pause.
	 * @param bool $reset_gsc Reset GSC pause.
	 *
	 * @return void
	 * @since 0.8.4
	 */
	public function reset_pause( bool $reset_ga, bool $reset_gsc ) : void {
		if ( $reset_ga ) {
			( new Worker_Traffic() )->reset_pause();
		}
		if ( $reset_gsc ) {
			( new Worker_Position() )->reset_pause();
		}
	}

	/**
	 * Set disconnect reason for GA if any.
	 *
	 * @param string|null  $string Null if not disconnected.
	 * @param Message|null $message Message instance.
	 *
	 * @return void
	 * @since 0.7.5
	 */
	public function set_ga_disconnect_reason( ?string $string = null, ?Message $message = null ) : void {
		if ( ! is_null( $string ) || ! is_null( $message ) ) {
			$this->set_ua( '', '', '', $this->get_data_tokens()->get_gsc_site() );
		}
		( new Disconnect_Reason_GA() )->save_reason( $string );
	}

	/**
	 * Try to refresh current access token using refresh token.
	 * Disconnect Analytics on invalid_grant error or if no refresh token exists.
	 *
	 * @return bool Was the token updated
	 */
	private function try_to_refresh_token() : bool {
		// try to update current token.
		try {
			$client = $this->create_client();
			Ahrefs_Seo::breadcrumbs( sprintf( '%s: %s', __METHOD__, (string) wp_json_encode( $client->getAccessToken() ) ) );
			$refresh = $client->getRefreshToken();
			if ( $refresh ) {
				$_token           = $client->getAccessToken();
				$created_time_old = $_token['created'] ?? 0;

				$result = $client->fetchAccessTokenWithRefreshToken( $refresh );

				$_token           = $client->getAccessToken();
				$created_time_new = $_token['created'] ?? 0;

				if ( $created_time_new === $created_time_old ) {
					$this->disconnect();
					$this->notice_account_is_disconnected();
					$message = ( is_array( $result ) && isset( $result['error_description'] ) ? $result['error_description'] . ' ' : '' ) . __( 'Your Google account disconnected due to an invalid token. Please try to reconnect or contact support if this keeps happening.', 'ahrefs-seo' );
					$this->set_message( $message );
					( new Disconnect_Reason_Google() )->save_reason( $message );
				} else { // save token using callback.
					$this->get_analytics_client()->token_callback_full( $_token );

					return true;
				}
			} else {
				$this->disconnect();
			}
		} catch ( LogicException $e ) {
			$this->disconnect();
			self::save_message( $e->getMessage(), Message::TYPE_NOTICE );
			Ahrefs_Seo::notify( $e, 'token refresh' );
		} catch ( GuzzleClientException $e ) {
			$this->disconnect();
			self::save_message( $e->getMessage(), Message::TYPE_NOTICE );
			Ahrefs_Seo::notify( $e, 'token refresh' );
		} catch ( Google_Service_Exception $e ) {
			/** @var array $errors */
			$errors = $e->getErrors();
			$this->disconnect();
			if ( is_array( $errors ) && count( $errors ) && ( 401 === $e->getCode() && isset( $errors[0]['reason'] ) && 'authError' === $errors[0]['reason'] ) ) {
				$this->notice_account_is_disconnected();
				$message = __( 'Your Google account disconnected due to an invalid token. Please try to reconnect or contact support if this keeps happening.', 'ahrefs-seo' );
				$this->set_message( $message, $e );
			} else {
				/* Translators: 1: error code, 2: error message */
				$message = sprintf( __( 'There was an additional Google Auth error while refresh token %1$d: %2$s', 'ahrefs-seo' ), $e->getCode(), $e->getMessage() );
				$this->set_message( $this->get_message() . ' ' . $message, $e );
			}
		} catch ( Error $e ) {
			$message = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );
			$this->set_message( $message );
		} catch ( Exception $e ) {
			$this->disconnect();
			/* Translators: 1: error code, 2: error message */
			$message = sprintf( __( 'There was an additional error while refresh token %1$d: %2$s', 'ahrefs-seo' ), $e->getCode(), $e->getMessage() );
			$this->set_message( $this->get_message() . ' ' . $message, $e );
		}

		return false;
	}

	/**
	 * Get last error message from Analytics API.
	 *
	 * @param bool $return_and_clear_saved_message true - return and clear message from option, false - return current message.
	 *
	 * @return string Error message or empty string.
	 */
	public function get_message( bool $return_and_clear_saved_message = false ) : string {
		if ( $return_and_clear_saved_message ) {
			$error = '' . get_option( self::OPTION_LAST_ERROR, '' );
			if ( '' !== $error ) {
				$this->set_message( '' );
			}

			return $error;
		}

		return $this->message;
	}

	/**
	 * Set error message. Submit report if Exception parameter is set.
	 * Save 'google notice' message.
	 *
	 * @param string|null    $message Message, null if no need to save.
	 * @param Exception|null $e Exception.
	 * @param string|null    $request Request string, saved to breadcrumbs.
	 * @param string         $type 'notice', 'error', 'error-single'.
	 *
	 * @return void
	 */
	public function set_message( ?string $message, ?Exception $e = null, ?string $request = null, string $type = 'error' ) : void {
		if ( $this->account_just_disconnected ) {
			return;
		}
		if ( ! is_null( $message ) ) {
			if ( '' !== $message ) {
				self::save_message( $message, $type );
			} else { // clean messages.
				Ahrefs_Seo_Errors::clean_messages( 'google' );
			}

			$this->message = $message;
			update_option( self::OPTION_LAST_ERROR, $message );
		}
		if ( ! is_null( $e ) ) {
			Ahrefs_Seo::breadcrumbs( 'Events ' . (string) wp_json_encode( $this->get_analytics_client()->get_logged_events() ) . ( ! empty( $request ) ? "\nRequest: " . $request : '' ) );
			Ahrefs_Seo::notify( $e );
		}
	}

	/**
	 * @return void
	 * @since 0.9.11
	 */
	protected function notice_account_is_disconnected() : void {
		$this->set_message( '' ); // clean all Google errors history.
		$this->account_just_disconnected = true;
	}

	/**
	 * @param string|null $message Message text.
	 * @param string|null $type Type.
	 *
	 * @return void
	 * @since 0.9.11
	 */
	public static function save_message( ?string $message, ?string $type = null ) : void {
		if ( ! self::get()->account_just_disconnected ) {
			Ahrefs_Seo_Errors::save_message( 'google', $message, $type );
		}
	}
}
