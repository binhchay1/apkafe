<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo_Vendor\Google\Auth\Credentials\UserRefreshCredentials;
/**
 * Extend existing class from Google library.
 * Uses internal, not-Google's API endpoint.
 * Used for tokens refreshes at new auth flow.
 */
class Google_Proxy_Refresh_Credentials extends UserRefreshCredentials {

    // phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- this is a copy of existing class.
	/**
	 * Create a new UserRefreshCredentials.
	 *
	 * @param string|array $scope the scope of the access request, expressed
	 *   either as an Array or as a space-delimited String.
	 * @param string|array $jsonKey JSON credential file path or JSON credentials
	 *   as an associative array.
	 */
	public function __construct( $scope, $jsonKey ) {
		parent::__construct( $scope, $jsonKey );
		if ( is_string( $jsonKey ) ) {
			$jsonKey = (array) json_decode( $jsonKey );
		}
		$this->auth = new \ahrefs\AhrefsSeo_Vendor\Google\Auth\OAuth2(
			[
				'clientId'           => $jsonKey['client_id'],
				'clientSecret'       => $jsonKey['client_secret'],
				'refresh_token'      => $jsonKey['refresh_token'],
				'scope'              => $scope,
				'tokenCredentialUri' => Google_Proxy_Client::OAUTH2_TOKEN_URI,
			]
		);
	}
}