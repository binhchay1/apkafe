<?php
declare( strict_types=1 );

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Disconnect_Reason\Disconnect_Reason_GA;
use ahrefs\AhrefsSeo_Vendor\Google\Client as Google_Client;
use ahrefs\AhrefsSeo_Vendor\Google_Service_Exception;
use ahrefs\AhrefsSeo_Vendor\Google_Task_Runner as Runner;
use ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Client as GuzzleClient;
use ahrefs\AhrefsSeo_Vendor\GuzzleHttp\ClientInterface as GuzzleClientInterface;
use ahrefs\AhrefsSeo_Vendor\GuzzleHttp\RequestOptions as GuzzleRequestOptions;
use Composer\CaBundle\CaBundle;
use Error;
use Exception;


/**
 * Class for interacting with Google Analytics and Google Search Console API.
 */
class Analytics_Client {
	/**
	 * @var Data_Tokens_Storage
	 */
	public $data_tokens;
	/**
	 * @var null|\ahrefs\AhrefsSeo_Vendor\Psr\Log\AbstractLogger
	 */
	private $logger;
	/**
	 * @var Google_Client|Google_Proxy_Client|null
	 */
	private $client = null;
	/**
	 * @var string
	 */
	private $client_option_hash = '';
	/**
	 * @var string
	 */
	private $last_token = '';

	/**
	 * @var Ahrefs_Seo_Analytics
	 */
	private $analytics;

	/**
	 * Constructor
	 *
	 * @param Ahrefs_Seo_Analytics|null $analytics Existing Analytics instance.
	 */
	public function __construct( ?Ahrefs_Seo_Analytics $analytics = null ) {
		$this->analytics = $analytics ?? Ahrefs_Seo_Analytics::get();
	}

	/**
	 * Set Google client
	 *
	 * @param Google_Client $client Google client instance.
	 *
	 * @return void
	 * @since 0.8.4
	 */
	public function set_client( Google_Client $client ) : void {
		$this->client = $client;
	}

	/**
	 * Callback for tokens data set.
	 *
	 * @param array $token_raw Tokens data.
	 *
	 * @return void
	 * @throws Google_Service_Exception When API returned error instead of access token.
	 * @since 0.9.11
	 */
	public function token_callback_full( array $token_raw ) : void {
		Ahrefs_Seo::breadcrumbs( __METHOD__ . (string) wp_json_encode( func_get_args() ) );
		if ( ! is_null( $this->client ) ) {
			$token = $this->client->getAccessToken();
			if ( isset( $token_raw['access_token'] ) ) {
				$token['created']    = time();
				$token['expires_in'] = $token_raw['expires_in'] ?? 3600; // Google default.
				// similar as default handler, but do not overwrite refresh token.
				$token['access_token'] = (string) $token_raw['access_token'];
				if ( isset( $token_raw['id_token'] ) ) {
					$token['id_token'] = $token_raw['id_token'];
				}
				if ( isset( $token_raw['scope'] ) ) {
					$token['scope'] = $token_raw['scope'];
				}

				$this->client->setAccessToken( $token );
				$this->data_tokens->save_raw_token( $token );
			} else {
				$this->analytics->disconnect();
				if ( isset( $token_raw['error_description'] ) && is_string( $token_raw['error_description'] ) ) {
					$this->analytics->set_message( $token_raw['error_description'] );
				} elseif ( isset( $token_raw['error'] ) && is_string( $token_raw['error'] ) ) {
					$this->analytics->set_message( $token_raw['error'] );
				}
				( new Disconnect_Reason_GA() )->save_reason( __( 'Your Google account disconnected due to an invalid token. Please try to reconnect or contact support if this keeps happening.', 'ahrefs-seo' ) );
				if ( isset( $token_raw['error'] ) ) {
					throw new Google_Service_Exception( (string) wp_json_encode( $token_raw ) );
				}
			}
		}
	}

	/**
	 * Called on token update. Callback.
	 * Update the in-memory access token and save it.
	 *
	 * @param string $cache_key Unused parameter.
	 * @param string $access_token Google access token (without refresh token).
	 *
	 * @return void
	 * @since 0.7.2
	 */
	public function token_callback( $cache_key, $access_token ) : void {
		// Note: callback, do not use parameter types.
		Ahrefs_Seo::breadcrumbs( __METHOD__ . (string) wp_json_encode( func_get_args() ) );

		if ( ! is_null( $this->client ) ) {
			$token = $this->client->getAccessToken();
			// similar as default handler, but do not overwrite refresh token and scope.
			$token['access_token'] = (string) $access_token;
			$token['expires_in']   = 3600; // Google default.
			$token['created']      = time();
			$this->client->setAccessToken( $token );
			$this->data_tokens->save_raw_token( $token );
		}
	}

	/**
	 * Set Data token
	 *
	 * @param Data_Tokens_Storage $data_tokens Data tokens instance.
	 *
	 * @since 0.8.4
	 */
	public function set_data_token( Data_Tokens_Storage $data_tokens ) : void {
		$this->data_tokens = $data_tokens;
	}

	/**
	 * Get Data token
	 *
	 * @return Data_Tokens_Storage
	 * @since 0.8.4
	 */
	public function get_data_tokens() : Data_Tokens_Storage {
		if ( is_null( $this->data_tokens ) ) { // @phpstan-ignore-line
			$this->data_tokens = new Data_Tokens_Storage();
		}

		return $this->data_tokens;
	}

	/**
	 * Disconnect Google account.
	 *
	 * @return void
	 */
	public function client_disconnect() : void {
		$this->data_tokens->tokens_load();
		if ( ! empty( $this->data_tokens->get_raw_token() ) && ! defined( 'AHREFS_SEO_PRESERVE_TOKEN' ) ) {
			try {
				$client = $this->create_client();
				$client->revokeToken( $client->getAccessToken() );
			} catch ( Error $e ) {
				$this->analytics->set_message( Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ ) );
			} catch ( Exception $e ) {
				Ahrefs_Seo::breadcrumbs( 'Events ' . (string) wp_json_encode( $this->get_logged_events() ) );
				Ahrefs_Seo_Errors::save_message( 'google', $e->getMessage() );
			}
			$this->analytics->set_message( __( 'Google account disconnected.', 'ahrefs-seo' ) );
		}

		$this->data_tokens->disconnect();
	}

	/**
	 * Return Google Client static instance, until token is same.
	 *
	 * @return Google_Client|Google_Proxy_Client
	 */
	public function create_client() : Google_Client {

		// load fresh tokens.
		$this->data_tokens->tokens_load();
		$client_option_hash = $this->data_tokens->get_client_id() . $this->data_tokens->get_client_secret();

		if ( is_null( $this->client ) || ( $client_option_hash !== $this->client_option_hash ) || ( $this->last_token !== $this->data_tokens->get_raw_token() ) ) {
			$this->client_option_hash = $client_option_hash;
			$client_id                = $this->data_tokens->get_client_id();
			$client_secret            = $this->data_tokens->get_client_secret();
			$redirect_uri             = $this->get_url_for_code_callback();
			$scopes                   = [
				Ahrefs_Seo_Analytics::SCOPE_ANALYTICS,
				Ahrefs_Seo_Analytics::SCOPE_SEARCH_CONSOLE,
			];
			$config                   = [
				'retry'               => [
					'retries'       => 3,
					'initial_delay' => 0,
				],
				'retry_map'           => array(
					'500'                   => Runner::TASK_RETRY_ALWAYS,
					'503'                   => Runner::TASK_RETRY_ALWAYS,
					'rateLimitExceeded'     => Runner::TASK_RETRY_ALWAYS,
					'userRateLimitExceeded' => Runner::TASK_RETRY_ALWAYS,
					6                       => Runner::TASK_RETRY_ALWAYS,  // CURLE_COULDNT_RESOLVE_HOST.
					7                       => Runner::TASK_RETRY_ALWAYS,  // CURLE_COULDNT_CONNECT.
					28                      => Runner::TASK_RETRY_ALWAYS,  // CURLE_OPERATION_TIMEOUTED.
					35                      => Runner::TASK_RETRY_ALWAYS,  // CURLE_SSL_CONNECT_ERROR.
					52                      => Runner::TASK_RETRY_ALWAYS,  // CURLE_GOT_NOTHING.
					'quotaExceeded'         => Runner::TASK_RETRY_NEVER,
					'internalServerError'   => Runner::TASK_RETRY_NEVER,
					'backendError'          => Runner::TASK_RETRY_NEVER,
				),
				'token_callback_full' => [ $this, 'token_callback_full' ], // for google proxy client.
			];

			if ( $this->data_tokens->is_using_direct_connection() ) {
				$this->client  = new Google_Client( $config );
				$redirect_uri  = 'urn:ietf:wg:oauth:2.0:oob';
				$client_id     = $this->data_tokens->get_default_config_client();
				$client_secret = $this->data_tokens->get_default_config_secret();
			} else {
				$this->client = new Google_Proxy_Client( $config );
			}
			// request offline access token.
			$this->client->setAccessType( 'offline' );
			$this->client->setClientSecret( $client_secret ?? '' );
			$this->client->setScopes( $scopes );
			$this->client->setRedirectUri( $redirect_uri );
			$this->client->setClientId( $client_id ?? '' );
			$this->client->setTokenCallback( [ $this, 'token_callback' ] );
			$this->client->setApplicationName( 'ahrefs-seo/' . AHREFS_SEO_VERSION . '-' . AHREFS_SEO_RELEASE );
			$this->client->setIncludeGrantedScopes( true );

			$path = $this::get_cert_path();
			if ( ! empty( $path ) ) { // recreate http client with updated verify path in config.
				$http_client                              = $this->client->getHttpClient();
				$options                                  = $http_client->getConfig();
				$options[ GuzzleRequestOptions::VERIFY ]  = $path;
				$options[ GuzzleRequestOptions::TIMEOUT ] = 120;
				$options[ GuzzleRequestOptions::CONNECT_TIMEOUT ] = 15;
				$this->client->setHttpClient( $this->get_http_client( $options ) );
			}

			$raw_token = $this->data_tokens->get_raw_token();
			Ahrefs_Seo::breadcrumbs( sprintf( '%s Google %s version: %s, client_id: %s, client_secret: %s, current token: %s', __METHOD__, substr( strrchr( get_class( $this->client ), '\\' ) ?: '', 1 ), $this->client->getLibraryVersion(), (string) wp_json_encode( $client_id ), (string) wp_json_encode( $client_secret ), (string) wp_json_encode( $raw_token ) ) );
			if ( ! empty( $raw_token ) ) {
				$this->client->setAccessToken( $raw_token );
			}
			$this->last_token = $raw_token;
		}
		// clean old logged data each time when new client required. Add logger for Google api client v2.
		$this->logger = new Logger();
		$this->client->setLogger( $this->logger );

		return $this->client;
	}

	/**
	 * Get redirect URL. Really are not used, but must exist.
	 *
	 * @return string
	 */
	protected function get_url_for_code_callback() : string {
		return admin_url( 'admin.php' );
	}

	/**
	 * Get the ca bundle path if one exists.
	 *
	 * @return string|null
	 * @since 0.7.2
	 */
	public static function get_cert_path() : ?string {
		if ( version_compare( PHP_VERSION, '5.3.2' ) < 0 ) {
			return null;
		}

		return realpath( CaBundle::getSystemCaRootBundlePath() ) ?: null;
	}

	/**
	 * Return new Guzzle HTTP client instance
	 *
	 * @param array<string, mixed> $options Guzzle client options.
	 *
	 * @return GuzzleClientInterface
	 * @since 0.7.3
	 */
	protected function get_http_client( array $options ) : GuzzleClientInterface {
		return new GuzzleClient( $options );
	}

	/**
	 * Get logged events from API requests.
	 *
	 * @return array<array>|null Null if no logging method available.
	 * @since 0.7.1
	 */
	public function get_logged_events() : ?array {
		return ! is_null( $this->logger ) && ( $this->logger instanceof Logger ) ? $this->logger->get_events() : null;
	}

}
