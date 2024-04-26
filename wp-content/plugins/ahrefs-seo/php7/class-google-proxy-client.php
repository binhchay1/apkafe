<?php
declare( strict_types=1 );

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Options\Temporary_Code;
use ahrefs\AhrefsSeo_Vendor\Google\Auth\HttpHandler\HttpHandlerFactory;
use ahrefs\AhrefsSeo_Vendor\Google\Auth\OAuth2;
use ahrefs\AhrefsSeo_Vendor\Google\Client as Google_Client;
use ahrefs\AhrefsSeo_Vendor\GuzzleHttp\ClientInterface;
use ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Psr7\Request;
use ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Psr7\Utils;
use Exception;

if ( ! defined( 'AHREFS_SEO_PROXY_ROOT' ) ) {
	define( 'AHREFS_SEO_PROXY_ROOT', 'https://wpauth.ahrefs.com' );
}
// phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase

/**
 * Class for interacting with Google API using custom endpoints for auth.
 * Required because of previously used "out-of-band" auth method (code copy-paste) deprecation.
 *
 * Handle with custom endpoints:
 * - get "auth" endpoint URL for initial auth;
 * - exchange code to tokens;
 * - token refreshes;
 * - get URL for connected sites list;
 * - token disconnect;
 *
 * Some methods duplicated or are the slightly modified copy of Google Client library code.
 * Extends \Google\Client class.
 *
 * @since 0.9.11
 */
class Google_Proxy_Client extends Google_Client {
	// define URLs for custom API endpoints.
	const OAUTH2_AUTH_URL   = AHREFS_SEO_PROXY_ROOT . '/o/oauth2/auth';
	const OAUTH2_REVOKE_URI = AHREFS_SEO_PROXY_ROOT . '/o/oauth2/revoke';
	const OAUTH2_SITE_ADD   = AHREFS_SEO_PROXY_ROOT . '/o/oauth2/site';
	const OAUTH2_TOKEN_URI  = AHREFS_SEO_PROXY_ROOT . '/o/oauth2/token';
	// define additional scopes list.
	const SCOPE_PROFILE = 'https://www.googleapis.com/auth/userinfo.profile';

	/**
	 * @var string|null
	 */
	private $state = null;

	/**
	 * Get auth root of current proxy service
	 *
	 * @return string
	 */
	public static function get_auth_root() : string {
		return (string) AHREFS_SEO_PROXY_ROOT;
	}

	/**
	 * Revoke access for the current site using client id.
	 * Revoke an OAuth2 access token or refresh token. This method will revoke the current access
	 * token, if a token isn't provided.
	 *
	 * @param string|array|null $token The token (access token or a refresh token) that should be revoked.
	 *
	 * @return boolean Returns True if the revocation was successful, otherwise False.
	 */
	public function revokeToken( $token = null ) {
		if ( ! $token ) {
			$token = $this->getAccessToken();
		}

		if ( is_array( $token ) ) {
			$token = $token['refresh_token'] ?? $token['access_token'];
		}
		$body    = Utils::streamFor(
			http_build_query(
				[
					'token'     => $token,
					'client_id' => $this->getClientId(),
				]
			)
		);
		$request = new Request(
			'POST',
			static::OAUTH2_REVOKE_URI,
			[
				'Cache-Control' => 'no-store',
				'Content-Type'  => 'application/x-www-form-urlencoded',
			],
			$body
		);
		try {
			$httpHandler = HttpHandlerFactory::build( $this->getHttpClient() );
			$response    = $httpHandler( $request );

			return 200 === (int) $response->getStatusCode();
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Set the scopes to be requested. Must be called before createAuthUrl().
	 * Will remove any previously configured scopes.
	 *
	 * @param string|array $scope_or_scopes , ie:
	 *    array(
	 *        'https://www.googleapis.com/auth/plus.login',
	 *        'https://www.googleapis.com/auth/moderator'
	 *    );
	 * @return void
	 */
	public function setScopes( $scope_or_scopes ) {
		if ( ! is_array( $scope_or_scopes ) ) {
			$scope_or_scopes = [ $scope_or_scopes ];
		}
		$scope_or_scopes[] = self::SCOPE_PROFILE; // require a user profile info.

		parent::setScopes( $scope_or_scopes );
	}

	/**
	 * Set OAuth 2.0 "state" parameter to achieve per-request customization.
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-22#section-3.1.2.2
	 *
	 * @param string $state State.
	 * @return void
	 */
	public function setState( $state ) {
		$this->state = $state;
		parent::setState( $state );
	}

	/**
	 * Create a URL to obtain user authorization.
	 * The authorization endpoint allows the user to first
	 * authenticate, and then grant/deny the access request.
	 *
	 * @param string|array $scope The scope is expressed as an array or list of space-delimited strings.
	 * @param array        $queryParams Querystring params to add to the authorization URL.
	 *
	 * @return string
	 */
	public function createAuthUrl( $scope = null, array $queryParams = [] ) {
		// load url from proxy service.
		return $this->load_sites_or_register_url_from_proxy( false, is_string( $scope ) ? explode( ' ', $scope ) : $scope );
	}

	/**
	 * Call external endpoint and return URL for redirect.
	 * On error will return to dashboard.
	 *
	 * @param bool       $sites_list Is sites list URL requested? Otherwise new site registration URL returned.
	 * @param array|null $scopes Scopes list.
	 *
	 * @return string
	 */
	public function load_sites_or_register_url_from_proxy( bool $sites_list = false, ?array $scopes = null ) : string {
		$error = null;
		$body  = array_merge(
			[

				'client_id'     => $this->getClientId(),
				'client_secret' => $this->getClientSecret(),
				'state'         => $this->state,
				'nonce'         => ( new Temporary_Code( 'verify' ) )->create_state_code( false ),
				'_'             => time(),
			],
			$this->get_site_details(),
			$this->get_user_details()
		);
		if ( $sites_list ) {
			$body['action'] = 'sites-list';
		}
		if ( ! $sites_list || ( '' === $this->getClientId() ) || ( '' === $this->getClientSecret() ) ) { // add scope for non sites list request or unauthorized request.
			$scopes = $scopes ?? $this->getScopes();
			sort( $scopes );
			$body['scope'] = implode( ' ', $scopes );
		}
		$headers = [ 'Content-Type' => 'application/json' ];
		$token   = $this->getRefreshToken();
		if ( $token ) {
			$headers['Authorization'] = "Bearer {$token}"; // Note: the refresh token used for auth purposes here.
		}
		$response = wp_remote_post(
			self::OAUTH2_SITE_ADD,
			[
				'headers' => $headers,
				'body'    => (string) wp_json_encode( $body ),
				'timeout' => 5,
			]
		);

		$redirect_url = wp_remote_retrieve_header( $response, 'Redirect-To' );
		if ( is_array( $redirect_url ) ) {
			$redirect_url = (string) array_pop( $redirect_url );
		}
		if ( empty( $redirect_url ) ) {
			$request_id = wp_remote_retrieve_header( $response, 'x-request-id' );
			/* Translators: %s: service URL */
			$error = sprintf( __( 'Can not connect to proxy service (%s). Please try again or contact support if this keeps happening.', 'ahrefs-seo' ), AHREFS_SEO_PROXY_ROOT );
			$json  = wp_remote_retrieve_body( $response );
			$data  = json_decode( $json, true );
			if ( is_array( $data ) && isset( $data['error_description'] ) ) {
				$error = (string) $data['error_description'];
				if ( strrpos( $error, '.' ) !== strlen( rtrim( $error ) ) - 1 ) {
					$error .= '.';
				}
			}
			if ( ! empty( $request_id ) ) {
				$error .= ' Request id: ' . ( ! is_array( $request_id ) ? $request_id : (string) array_pop( $request_id ) );
			}
		}

		return $redirect_url ?: add_query_arg(
			[
				'page'  => sanitize_text_field( wp_unslash( $_GET['page'] ?? '' ) ),
				'step'  => ! empty( $_GET['step'] ) ? sanitize_text_field( wp_unslash( $_GET['step'] ) ) : null,
				'tab'   => ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : null,
				// todo: show more info.
				'error' => is_wp_error( $response ) ? $response->get_error_message() : $error,
			],
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Get site details
	 *
	 * @return array
	 */
	protected function get_site_details() : array {
		return [
			'site_name' => mb_substr( wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), 0, 255 ),
			'site_url'  => \trailingslashit( site_url() ),
			'admin_url' => admin_url( 'admin.php' ),
			'ajax_url'  => admin_url( 'admin-ajax.php' ),
		];
	}

	/**
	 * Get plugin details and highest user role for a multisite.
	 *
	 * @return array
	 */
	protected function get_user_details() : array {
		$roles = wp_get_current_user()->roles;
		if ( is_multisite() ) {
			$roles[] = current_user_can( 'manage_network' ) ? 'network_administrator' : 'not_network_administrator';
		}
		$roles = array_unique( $roles );
		sort( $roles );

		return [
			'hl'     => get_user_locale(),
			'ver'    => AHREFS_SEO_VERSION,
			'client' => $this::LIBVER,
			'roles'  => implode( ',', $roles ),
		];
	}

	/**
	 * Adds auth listeners to the HTTP client based on the credentials
	 * set in the Google API Client object
	 *
	 * @param ClientInterface|null $http the http client object.
	 *
	 * @return ClientInterface the http client object
	 * @throws Exception On authorization error.
	 */
	public function authorize( ClientInterface $http = null ) {
		$token = $this->getAccessToken();
		// prevent use of default authorize handler with original endpoint.
		if ( isset( $token['refresh_token'] ) && $this->isAccessTokenExpired() ) {
			$callback = $this->getConfig( 'token_callback' );
			try {
				$http        = $http ?: $this->getHttpClient();
				$authHandler = $this->getAuthHandler();
				$scopes      = (string) $this->prepareScopes();
				$credentials = $this->createUserRefreshCredentials( $scopes, $token['refresh_token'] );

				return $authHandler->attachCredentials( $http, $credentials, $callback );
			} catch ( Exception $e ) {
				// todo: handle some auth errors.
				Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Auth error %s: %s', get_class( $e ), $e->getMessage() ), 0, $e ) );
				throw $e;
			}
		}

		return parent::authorize( $http );
	}

	/**
	 * Recreate similar private method from the Google/Client class.
	 * But use our own implementation of UserRefreshCredentials class with our url for token auth.
	 *
	 * @param string $scope Scopes list.
	 * @param string $refreshToken Refresh token.
	 *
	 * @return Google_Proxy_Refresh_Credentials
	 */
	protected function createUserRefreshCredentials( $scope, $refreshToken ) {
		$creds = \array_filter(
			[
				'client_id'     => $this->getClientId(),
				'client_secret' => $this->getClientSecret(),
				'refresh_token' => $refreshToken,
			]
		);

		return new Google_Proxy_Refresh_Credentials( $scope, $creds );
	}

	/**
	 * Create a default google auth object.
	 *
	 * @return \ahrefs\AhrefsSeo_Vendor\Google\Auth\OAuth2
	 */
	protected function createOAuth2Service() {
		return new OAuth2(
			[
				'clientId'           => $this->getClientId(),
				'clientSecret'       => $this->getClientSecret(),
				'authorizationUri'   => static::OAUTH2_AUTH_URL,
				'tokenCredentialUri' => static::OAUTH2_TOKEN_URI,
				'redirectUri'        => $this->getRedirectUri(),
				'issuer'             => $this->getClientId(),
				'signingKey'         => null,
				'signingAlgorithm'   => null,
			]
		);
	}
}
