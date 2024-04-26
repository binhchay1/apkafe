<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Admin_Notice\Google_Connection;
use ahrefs\AhrefsSeo\Options\Advanced;
/**
 * Data tokens for Google Analytics storage
 *
 * @since 0.8.4
 */
class Data_Tokens_Storage {

	const OPTION_TOKENS = 'ahrefs-seo-oauth2-tokens';
	/**
	 * @var null|string
	 */
	protected $token;
	/**
	 * @var string
	 */
	protected $ua_id = '';
	/**
	 * @var string
	 */
	protected $ua_name = '';
	/**
	 * @var string
	 */
	protected $ua_url = '';
	/**
	 * @var string
	 */
	protected $gsc_site = '';
	/**
	 * @var string|null
	 */
	protected $client_id = null;
	/**
	 * @var string|null
	 */
	protected $client_secret = null;
	/**
	 * @var array
	 */
	private $default_config = [ // live config.
		// OAuth2 Settings, you can get these keys at https://code.google.com/apis/console .
		'oauth2_client_id'     => '616074445976-gce92a0p1ptkrgj6rl0jdpk7povts56a.apps.googleusercontent.com',
		'oauth2_client_secret' => 'JpBej-3XMNqXhGdRpgpSc7Y4',
	];
	/**
	 * Token is correct.
	 * Analytics and/or Search console enabled by scope credentials and user.
	 *
	 * @return bool
	 */
	public function is_token_set() {
		return ! empty( $this->token );
	}
	/**
	 * Return GA selected ID
	 *
	 * @return string
	 */
	public function get_ua_id() {
		return $this->ua_id;
	}
	/**
	 * Return GSC selected site
	 *
	 * @return string
	 */
	public function get_gsc_site() {
		return $this->gsc_site;
	}
	/**
	 * Return GA selected name
	 *
	 * @return string
	 */
	public function get_ua_name() {
		return $this->ua_name;
	}
	/**
	 * Return GA selected url
	 *
	 * @return string
	 */
	public function get_ua_url() {
		return $this->ua_url;
	}
	/**
	 * Return Client ID
	 *
	 * @return string|null
	 */
	public function get_client_id() {
		return $this->client_id;
	}
	/**
	 * Set and save Client ID
	 *
	 * @param string|null $client_id Client ID.
	 */
	public function set_client_id( $client_id = null ) {
		$this->client_id = $client_id;
		$this->save_raw_token( $this->get_raw_token() );
	}
	/**
	 * Set and save Client Secret
	 *
	 * @param string|null $client_secret Client secret.
	 */
	public function set_client_secret( $client_secret = null ) {
		$this->client_secret = $client_secret;
		$this->save_raw_token( $this->get_raw_token() );
	}
	/**
	 * Return Client Secret
	 *
	 * @return string|null
	 */
	public function get_client_secret() {
		return $this->client_secret;
	}
	/**
	 * Direct connection used if token already set and credentials of default App used.
	 *
	 * @return bool
	 */
	public function is_using_direct_connection() {
		return ! empty( $this->token ) && ( is_null( $this->client_id ) || $this->client_id === $this->get_default_config_client() ) && ( is_null( $this->client_secret ) || $this->client_secret === $this->get_default_config_secret() );
	}
	/**
	 * Get client ID from default config
	 *
	 * @return string
	 * @since 0.9.11
	 */
	public function get_default_config_client() {
		return (string) apply_filters( 'ahrefs_seo_oauth2_client', $this->default_config['oauth2_client_id'] );
	}
	/**
	 * Get client secret from default config
	 *
	 * @return string
	 * @since 0.9.11
	 */
	public function get_default_config_secret() {
		return (string) apply_filters( 'ahrefs_seo_oauth2_secret', $this->default_config['oauth2_client_secret'] );
	}
	/**
	 * Set GA and GSC profile values.
	 *
	 * @param string $ua_id UA id.
	 * @param string $ua_name UA name.
	 * @param string $ua_url UA url.
	 * @param string $gsc_site GSC site.
	 * @return void
	 */
	public function save_values( $ua_id, $ua_name, $ua_url, $gsc_site = '' ) {
	 //phpcs:ignore: Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- false positive.
		$token         = $this->token ?: null;
		$client_id     = $this->client_id !== $this->get_default_config_client() ? $this->client_id : null;
		$client_secret = $this->client_secret !== $this->get_default_config_secret() ? $this->client_secret : null;
		update_option( self::OPTION_TOKENS, compact( 'token', 'ua_id', 'ua_name', 'ua_url', 'gsc_site', 'client_id', 'client_secret' ) );
		$this->tokens_load(); // reload and fill properties with new values.
	}
	/**
	 * Save Google token.
	 *
	 * @param string|array $token Google token.
	 * @return void
	 */
	public function save_raw_token( $token ) {
		// note: do not use parameter type, may be string or array.
		if ( is_array( $token ) ) { // support for tokens from Google API client v2.
			$token = (string) wp_json_encode( $token );
		}
		$ua_id         = $this->ua_id ?: '';
		$ua_name       = $this->ua_name ?: '';
		$ua_url        = $this->ua_url ?: '';
		$gsc_site      = empty( $this->gsc_site ) ? '' : $this->gsc_site;
		$client_id     = $this->client_id !== $this->get_default_config_client() ? $this->client_id : null;
		$client_secret = $this->client_secret !== $this->get_default_config_secret() ? $this->client_secret : null;
		$this->extract_client_id_and_code( $token, $client_id, $client_secret );
		Ahrefs_Seo::breadcrumbs( sprintf( '%s (%s) [(%s) (%s) (%s) (%s)]', __METHOD__, $token, $ua_id, $ua_name, $ua_url, $gsc_site ) );
		update_option( self::OPTION_TOKENS, compact( 'token', 'ua_id', 'ua_name', 'ua_url', 'gsc_site', 'client_id', 'client_secret' ) );
		$this->tokens_load();
		( new Google_Connection() )->reset();
	}
	/**
	 * Extract client id and secret from the proxy token. Update all parameters.
	 *
	 * @since 0.9.11
	 *
	 * @param string  $token Raw token.
	 * @param ?string $client_id Client ID.
	 * @param ?string $client_secret Client secret.
	 *
	 * @return void
	 */
	private function extract_client_id_and_code( &$token, &$client_id = null, &$client_secret = null ) {
		if ( strlen( $token ) ) {
			$data = json_decode( $token, true );
			if ( is_array( $data ) ) {
				if ( isset( $data['client_id'] ) && isset( $data['client_secret'] ) ) {
					$client_id     = $data['client_id'];
					$client_secret = $data['client_secret'];
					unset( $data['client_id'] );
					unset( $data['client_secret'] );
					$token = wp_json_encode( $data );
				}
			}
		}
	}
	/**
	 * Get raw token data as string
	 *
	 * @return string
	 */
	public function get_raw_token() {
		return is_string( $this->token ) ? $this->token : '';
	}
	/**
	 * Load tokens values from DB option.
	 *
	 * @return void
	 */
	public function tokens_load() {
		static $prev_value = null;
		$data              = get_option( self::OPTION_TOKENS, [] );
		if ( $prev_value !== $data ) {
			if ( isset( $data['token'] ) && is_array( $data['token'] ) ) { // support for tokens from Google API client v2.
				$data['token'] = (string) wp_json_encode( $this->token );
			}
			$this->token    = (string) ( isset( $data['token'] ) ? $data['token'] : '' );
			$this->ua_id    = isset( $data['ua_id'] ) ? $data['ua_id'] : '';
			$this->ua_name  = isset( $data['ua_name'] ) ? $data['ua_name'] : '';
			$this->ua_url   = isset( $data['ua_url'] ) ? $data['ua_url'] : '';
			$this->gsc_site = isset( $data['gsc_site'] ) ? $data['gsc_site'] : '';
			$client_id      = isset( $data['client_id'] ) ? $data['client_id'] : null;
			$client_secret  = isset( $data['client_secret'] ) ? $data['client_secret'] : null;
			if ( $this->token ) { // maybe set default values if token already exists.
				if ( $client_id !== $this->get_default_config_client() ) {
					$this->client_id = $client_id;
				}
				if ( $client_secret !== $this->get_default_config_secret() ) {
					$this->client_secret = $client_secret;
				}
			} else {
				$this->client_id     = $client_id;
				$this->client_secret = $client_secret;
			}
			$prev_value = $data;
		}
	}
	/**
	 * Remove existing token.
	 */
	public function disconnect() {
		delete_option( self::OPTION_TOKENS );
		wp_cache_flush();
		$this->token    = null;
		$this->ua_id    = '';
		$this->ua_name  = '';
		$this->ua_url   = '';
		$this->gsc_site = '';
		( new Google_Connection() )->reset();
	}
	/**
	 * Get token scope as string
	 *
	 * @return string What is allowed for the token.
	 */
	public function get_token_scope_as_string() {
		if ( ! empty( $this->token ) ) {
			$token_data = is_string( $this->token ) ? json_decode( $this->token, true ) : $this->token; // accept both string and array.
			if ( is_array( $token_data ) && isset( $token_data['scope'] ) && is_string( $token_data['scope'] ) ) {
				return $token_data['scope'];
			}
		}
		return '';
	}
	/**
	 * Get google configuration
	 */
	public function get_config() {
		$site_url          = get_site_url();
		$home_url          = get_home_url();
		$direct_connection = $this->is_using_direct_connection();
		$auth_root         = Google_Proxy_Client::get_auth_root();
		$result            = wp_json_encode(
			[
				'ver'      => AHREFS_SEO_VERSION,
				'urls'     => [
					'site'      => $site_url,
					'home'      => $home_url,
					'domain'    => Ahrefs_Seo::get()->get_current_domain(),
					'traffic'   => apply_filters( 'ahrefs_seo_search_traffic_url', $home_url ),
					'backlinks' => apply_filters( 'ahrefs_seo_post_url', $home_url ),
				],
				'filters'  => [
					'domain'    => has_filter( 'ahrefs_seo_domain' ) ? 1 : 0,
					'traffic'   => has_filter( 'ahrefs_seo_search_traffic_url' ) ? 1 : 0,
					'backlinks' => has_filter( 'ahrefs_seo_post_url' ) ? 1 : 0,
				],
				'config'   => get_option( self::OPTION_TOKENS, [] ),
				'advanced' => get_option( Advanced::OPTION_ADVANCED, '' ),
				'direct'   => $direct_connection ? 'Y' : 'N',
				'auth'     => $auth_root,
			]
		);
		return is_string( $result ) ? $result : '';
	}
}