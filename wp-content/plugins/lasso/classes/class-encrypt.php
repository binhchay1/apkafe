<?php
/**
 * Declare class Encrypt
 *
 * @package Encrypt*/

namespace Lasso\Classes;

use LassoVendor\phpseclib3\Crypt\PublicKeyLoader;
use LassoVendor\phpseclib3\Crypt\AES;

/**
 * Lasso Encrypt
 */
class Encrypt {
	const PUBLIC_PEM_PATH = LASSO_PLUGIN_PATH . '/files/public.pem';

	/**
	 * Encrypt data
	 *
	 * @param string|array $data            Data to be encrypted.
	 * @return string      $encryptedBase64 Base64 string.
	 */
	public static function encrypt( $data ) {
		$data = is_array( $data ) ? wp_json_encode( $data ) : $data;

		// ? Load the public key
		$pem_file = file_get_contents( self::PUBLIC_PEM_PATH ); // phpcs:ignore
		// ? Your provided public key
		$public_key_object = PublicKeyLoader::load( $pem_file );

		// ? Encrypt the data
		$encrypted_data = $public_key_object->encrypt( $data );

		// ? Encode the encrypted data (binary) to base64 for safe transmission
		$encrypted_base64 = base64_encode( $encrypted_data );	// phpcs:ignore

		return $encrypted_base64;
	}

	/**
	 * Encrypt data using AES for the long data, then encrypt the AES key using RSA
	 * This way we can send the AES key along with the encrypted data
	 *
	 * @param string|array $data Data to be encrypted.
	 * @param bool         $build_query  Whether to build the query string or not.
	 */
	public static function encrypt_aes( $data, $build_query = false ) {
		$data = is_array( $data ) ? wp_json_encode( $data ) : $data;

		// ? Initialize the RSA and AES objects
		$cipher        = new AES( 'cbc' );
		$symmetric_key = random_bytes( 32 ); // 32 bytes for AES-256, adjust as needed

		$cipher->setKey( $symmetric_key );
		$cipher->setIV( str_repeat( "\x00", 16 ) ); // ? 16 bytes for AES-256 in CBC mode

		// ? Encrypt the plaintext message with the AES key
		$encrypted_message = $cipher->encrypt( $data );

		$results = array(
			'encrypted_symmetric_key_base64' => self::encrypt( $symmetric_key ), // phpcs:ignore
			'encrypted_base64'               => base64_encode( $encrypted_message ), // phpcs:ignore
		);

		// ? Build the query string if needed
		if ( $build_query ) {
			$results = http_build_query( $results );
		}

		return $results;
	}
}
