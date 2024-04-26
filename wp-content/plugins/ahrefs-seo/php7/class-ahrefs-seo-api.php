<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsApiPhp\AhrefsAPI;
use ahrefs\AhrefsSeo\Data_Api\Data_Metrics_Extended;
use ahrefs\AhrefsSeo\Disconnect_Reason\Disconnect_Reason_Ahrefs;
use ahrefs\AhrefsSeo\Disconnect_Reason\Disconnect_Reason_Ahrefs_Free;
use ahrefs\AhrefsSeo\Messages\Message;
use Error;
use Exception;

/**
 * Work with Ahrefs API.
 */
class Ahrefs_Seo_Api extends Ahrefs_Seo_Abstract_Api {

	const OPTION_ACCOUNT_IS_FREE = 'ahrefs-seo-account-is-free';

	const OPTION_SUBSCRIPTION_INFO = 'ahrefs-seo-subscription-info';

	const OPTION_DOMAIN = 'ahrefs-seo-domain';

	/** Allow query visitors once per seconds. */
	const API_MIN_DELAY = 0.5;

	/** @var float Time when last query to Ahrefs API run. */
	private $last_query_time = 0;

	/** @var Ahrefs_Seo_Api */
	private static $instance = null;

	/**
	 * Token instance
	 *
	 * @var Ahrefs_Seo_Token
	 */
	protected $token;

	/**
	 * Last error or empty string
	 *
	 * @var string
	 */
	protected $last_error = '';

	/**
	 * Return the instance
	 *
	 * $return Ahrefs_Seo_Api
	 */
	public static function get() : Ahrefs_Seo_Api {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->token = Ahrefs_Seo_Token::get();
	}

	/**
	 * Create an AhrefsAPI class instance
	 *
	 * @return AhrefsAPI
	 * @throws Exception When valid token is missing.
	 */
	private function get_ahrefs_api() : AhrefsAPI {
		/**
		* Create an AhrefsAPI class instance
		*
		* @param string $token APItoken from https://ahrefs.com/api/
		* @param bool $debug Debug message
		*/
		$api = new AhrefsAPI( $this->token->token_get(), false );
		if ( method_exists( $api, 'useGuzzle' ) ) {
			$api->useGuzzle();
		}
		return $api;
	}

	/**
	 * Return last API error if any.
	 * Does not use option value.
	 *
	 * @return string Error message or empty string.
	 */
	public function get_last_error() : string {
		return $this->last_error;
	}

	/**
	 * Set last API error. Update option value too.
	 *
	 * @param string $message Error message or empty string.
	 * @param string $type 'error' or 'notice'.
	 * @return void
	 */
	protected function set_last_error( string $message, string $type ) : void {
		$this->last_error = $message;
		Ahrefs_Seo_Errors::save_message( 'ahrefs', $message, $type );
	}

	/**
	 * Check if current domain name changed.
	 *
	 * @param string $domain Domain name.
	 * @return void
	 */
	protected function check_domain_updated( string $domain ) : void {
		$current_domain = (string) get_option( self::OPTION_DOMAIN, '' );
		if ( $current_domain !== $domain ) {
			// call reset backlinks data on domain change action.
			do_action( Ahrefs_Seo::ACTION_DOMAIN_CHANGED, $domain, $current_domain ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- constant used plugin prefix.
			update_option( self::OPTION_DOMAIN, $domain );
		}
	}

	/**
	 * Check is token valid, set error message if any.
	 * Call API for result.
	 *
	 * @return bool true if token is valid.
	 */
	public function token_check() : bool {
		$result = $this->get_subscription_info();
		return ! is_null( $result );
	}

	/**
	 * Return array with details or null.
	 * Also set last API error.
	 *
	 * @param bool $use_cached_info if true - return cached info, if exists.
	 * @return null|array
	 */
	public function get_subscription_info( bool $use_cached_info = false ) : ?array {
		if ( $use_cached_info ) { // try to return cached info first.
			$value = get_option( self::OPTION_SUBSCRIPTION_INFO );
			if ( is_array( $value ) ) {
				return $value;
			}
		}

		$error       = __( 'Ahrefs API get subscription info failed.', 'ahrefs-seo' );
		$token_value = $this->token->token_get();
		if ( empty( $token_value ) ) {
			$this->set_last_error( __( 'Ahrefs token is required.', 'ahrefs-seo' ), 'error' );
			return null;
		}
		Ahrefs_Seo_Errors::clean_messages( 'ahrefs' );
		try {
			// Create an AhrefsAPI class instance.
			$ahrefs = $this->get_ahrefs_api();
			$this->maybe_do_a_pause();
			$info = $ahrefs->get_subscription_info();
			do_action_ref_array( 'ahrefs_seo_api_subscription_info', [ &$info ] );

			if ( '' === $info ) {
				$error        = __( 'Please try again later', 'ahrefs-seo' );
				$raw          = $ahrefs->getCurlInfo();
				$guzzle_error = $ahrefs->getlastGuzzleError();
				$code         = ! is_null( $guzzle_error ) ? (int) $guzzle_error->getCode() : 0;
				if ( is_array( $raw ) && is_array( $raw[0] ) && isset( $raw[0]['total_time'] ) && isset( $raw[0]['size_download'] ) && 0.0 === floatval( $raw[0]['size_download'] ) && isset( $raw[0]['http_code'] ) && 0 === $raw[0]['http_code'] ) {
					$error = __( 'Connection error', 'ahrefs-seo' );
				}
				Ahrefs_Seo::notify(
					new Ahrefs_Seo_Exception(
						'Ahrefs API get_subscription_info() returned empty result. ' .
						(string) wp_json_encode(
							[
								'token' => $token_value,
								'info'  => $info,
								'raw'   => $raw,
							]
						),
						$code,
						$guzzle_error
					),
					'Ahrefs API get_subscription_info empty.'
				);
			} else {
				$data = json_decode( $info, true );
				if ( ! empty( $data ) && is_array( $data ) ) {
					if ( isset( $data['info'] ) ) {
						update_option( self::OPTION_SUBSCRIPTION_INFO, $data['info'] );
						return $data['info'];
					} elseif ( isset( $data['error'] ) ) {
						$error = $data['error'];
					} else {
						$error = 'API error, get_subscription_info, response [' . $info . ']';
					}
				}
			}
		} catch ( Error $e ) {
			$error = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__, __( 'Ahrefs API get subscription info failed.', 'ahrefs-seo' ) );
		} catch ( Exception $e ) {
			$error = $e->getMessage();
			Ahrefs_Seo::notify( $e, 'Ahrefs API get_subscription_info unexpected.' );
		}

		if ( 'invalid token' === $error ) { // replace error message.
			if ( $this->token->token_get() ) {
				$token              = $this->token->token_get();
				$was_a_free_account = $this->is_free_account( true );
				// save disconnect reason.
				if ( $was_a_free_account ) {
					( new Disconnect_Reason_Ahrefs_Free() )->save_reason( $token );
				} else {
					( new Disconnect_Reason_Ahrefs() )->save_reason( $token );
				}
				Ahrefs_Seo_Errors::clean_messages( 'ahrefs' );
				$this->last_error = '';
				Ahrefs_Seo_Token::get()->disconnect();
				Ahrefs_Seo_Errors::save_message( 'ahrefs', __( 'Ahrefs account disconnected due to invalid token.', 'ahrefs-seo' ), Message::TYPE_NOTICE );
				$error = '';
			} else {
				$error = __( 'The code is invalid', 'ahrefs-seo' );
			}
		}
		if ( $error ) {
			$this->set_last_error( $error, 'error' );
		}
		return null;
	}

	/**
	 * Get results by url.
	 * - Number of external backlinks found on the referring pages that link to the target.
	 * - Number of domains containing at least one backlink that links to the target.
	 *
	 * @param string $url Source url.
	 * @return Data_Metrics_Extended Number of dofollow backlinks and ref domains for given url.
	 */
	public function get_count_by_url( string $url ) : Data_Metrics_Extended {
		if ( $this->is_disconnected() ) {
			$this->set_last_error( __( 'Ahrefs token is required.', 'ahrefs-seo' ), 'error' );
		} elseif ( $this->is_free_account() || ! $this->is_limited_account( true ) ) { // first check is for free account, because it is limited by default.
			$error = '';
			$url   = apply_filters( 'ahrefs_seo_post_url', $url );

			$domain = wp_parse_url( home_url(), PHP_URL_HOST );
			if ( is_string( $domain ) ) {
				$this->check_domain_updated( $domain );
			}
			// remove scheme.
			$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
			if ( $scheme ) {
				$url = substr( $url, strlen( $scheme ) + 3 );
			}
			try {
				$ahrefs = $this->get_ahrefs_api();
				$ahrefs->set_target( $url )->mode_exact()->select( 'backlinks,refdomains' )->set_output( 'json' ); // @phpstan-ignore-line -- methods are not defined, but __call used.

				$this->maybe_do_a_pause();
				$info = $ahrefs->get_metrics_extended();
				do_action_ref_array( 'ahrefs_seo_api_metrics_extended', [ &$info ] );

				if ( '' === $info ) {
					$error        = __( 'Empty answer', 'ahrefs-seo' );
					$guzzle_error = $ahrefs->getlastGuzzleError();
					$code         = ! is_null( $guzzle_error ) ? (int) $guzzle_error->getCode() : 0;
					$raw          = $ahrefs->getCurlInfo();
					if ( is_array( $raw ) && is_array( $raw[0] ) && isset( $raw[0]['total_time'] ) && isset( $raw[0]['size_download'] ) && 0.0 === floatval( $raw[0]['size_download'] ) && isset( $raw[0]['http_code'] ) && 0 === $raw[0]['http_code'] ) {
						$error = __( 'Connection error', 'ahrefs-seo' );
					}
					$e = new Ahrefs_Seo_Exception(
						'Ahrefs API get_metrics_extended() returned empty result. ' .
							(string) wp_json_encode(
								[
									'info' => $info,
									'raw'  => $raw,
								]
							),
						$code,
						$guzzle_error
					);
					Ahrefs_Seo::notify( $e, 'Ahrefs API get_metrics_extended empty.' );
					$this->on_error_received( $e, [ $url ] );
				} else {
					$data = json_decode( $info, true );

					if ( is_array( $data ) && isset( $data['metrics'] ) && is_array( $data['metrics'] ) && isset( $data['metrics']['backlinks'] ) && isset( $data['metrics']['refdomains'] ) ) {
						return Data_Metrics_Extended::data( intval( $data['metrics']['backlinks'] ), intval( $data['metrics']['refdomains'] ) );
					} elseif ( is_array( $data ) && isset( $data['error'] ) ) {
						if ( is_string( $data['error'] ) ) {
							$error = $data['error'];
							$this->check_is_limited_error( $error );
						}
					} else {
						$raw   = $ahrefs->getCurlInfo();
						$error = 'Incorrect service answer: [' . $info . '] for page [ ' . $url . ' ]';
						$e     = new Ahrefs_Seo_Exception(
							'Ahrefs API get_metrics_extended() returned incorrect result. ' .
								(string) wp_json_encode(
									[
										'info' => $info,
										'raw'  => $raw,
									]
								)
						);
						Ahrefs_Seo::notify( $e, 'Ahrefs API get_metrics_extended incorrect.' );
						$this->on_error_received( $e, [ $url ] );
					}
				}
			} catch ( Error $e ) {
				$error = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__, __( 'Ahrefs API get metrics extended failed.', 'ahrefs-seo' ) );
				$this->on_error_received( $e, [ $url ] );
			} catch ( Exception $e ) {
				$error = $e->getMessage();
				Ahrefs_Seo::notify( $e, 'Ahrefs API get_metrics_extended unexpected.' );
				$this->on_error_received( $e, [ $url ] );
			}
			if ( $error ) {
				$this->set_last_error( $error, 'error' );
			}
		} else {
			$this->set_last_error( __( 'Limited Ahrefs account detected.', 'ahrefs-seo' ), 'error' );
		}
		return Data_Metrics_Extended::error();
	}

	/**
	 * Clear internal cached data
	 */
	public function clear_cache() : void {
		delete_option( self::OPTION_SUBSCRIPTION_INFO );
	}

	/**
	 * Check error message against limited account error and expired account error.
	 *
	 * @param string $error Error message.
	 * @return bool
	 */
	protected function check_is_limited_error( string $error ) : bool {
		if ( 'invalid token' === $error ) {
			// was it a free token?
			if ( ! $this->is_disconnected() ) {
				// next line will load fresh details and maybe will disconnect an account.
				$this->is_free_account( false );
			}
		} elseif ( 'Integration limit is exceeded' === $error ) {
			return ! $this->is_free_account( false ) && $this->is_limited_account( true ); // is_limited_account() uses cached values from uncached is_free_account() call.
		}
		return false;
	}

	/**
	 * There is no active Ahrefs account
	 *
	 * @phpstan-impure
	 * @return bool
	 */
	public function is_disconnected() : bool {
		return ! $this->token->token_state_ok();
	}

	/**
	 * Current paid account has no rows left.
	 * Cached result uses latest values saved from get_subscription_info() call.
	 *
	 * @phpstan-impure
	 * @param bool $use_cached_subscription_info if true - use cached info, if exists, otherwise do an API request.
	 * @return bool
	 */
	public function is_limited_account( bool $use_cached_subscription_info = false ) : bool {
		$info = $this->get_subscription_info( $use_cached_subscription_info ); // get cached or uncached subscription info.
		return ( is_array( $info ) && isset( $info['rows_left'] ) && ( 0 === $info['rows_left'] ) && ( 'No Subscription' !== $info['subscription'] ) );
	}

	/**
	 * Current account is free
	 * Cached value updated on uncached function call only.
	 * Uncached call will call get_subscription_info() - and this may update is_limited_account() value.
	 *
	 * @param bool $use_cached_info true - return cached info (from option), false - query API, check, save to option and return result.
	 * @return bool true - account is free.
	 */
	public function is_free_account( bool $use_cached_info = true ) : bool {
		static $result = null;
		if ( $use_cached_info ) {
			$value = get_option( self::OPTION_ACCOUNT_IS_FREE );
			return ! empty( $value );
		}
		if ( is_null( $result ) ) {
			$info   = $this->get_subscription_info(); // get fresh subscription info.
			$result = is_array( $info ) && isset( $info['subscription'] ) && ( 'no subscription' === strtolower( $info['subscription'] ) );
			// save result to cache.
			update_option( self::OPTION_ACCOUNT_IS_FREE, $result );
		}
		return $result;
	}

	/**
	 * Maybe disconnect Ahrefs using 'disconnect' link
	 *
	 * @param Ahrefs_Seo_Screen $screen Current screen instance.
	 */
	public static function maybe_disconnect( Ahrefs_Seo_Screen $screen ) : void {
		if ( isset( $_GET['disconnect-ahrefs'] ) && check_admin_referer( $screen->get_nonce_name(), 'disconnect-ahrefs' ) && current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_SAVE ) ) {
			// disconnect Ahrefs.
			Ahrefs_Seo_Token::get()->disconnect();
			Ahrefs_Seo::get()->initialized_set( null );
			Ahrefs_Seo_Errors::clean_messages( 'ahrefs' );
			$params = [
				'page' => isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : Ahrefs_Seo::SLUG,
				'tab'  => isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : null,
				'step' => isset( $_GET['step'] ) ? sanitize_text_field( wp_unslash( $_GET['step'] ) ) : null,
			];

			Helper_Content::wp_redirect( remove_query_arg( [ 'disconnect-ahrefs' ], add_query_arg( $params, admin_url( 'admin.php' ) ) ) );
			die();
		}
	}

	/**
	 * Do a minimal delay between requests.
	 * Used to prevent API rate errors.
	 */
	private function maybe_do_a_pause() : void {
		$time_since = microtime( true ) - $this->last_query_time;
		if ( $time_since < self::API_MIN_DELAY && ! defined( 'AHREFS_SEO_IGNORE_DELAY' ) ) {
			Ahrefs_Seo::usleep( intval( ceil( self::API_MIN_DELAY - $time_since ) * 1000000 ) );
		}
		$this->last_query_time = microtime( true );
	}
}
