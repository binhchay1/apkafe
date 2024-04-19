<?php
/**
 * Declare class Cache_Per_Process
 * For cache data to Lasso Cache class's attribute and only exist on per php process
 * Destruct function will clear all attribute data before php close
 * Use to cache data by key then use on next step.
 *
 * @package Cache_Per_Process
 */

namespace Lasso\Classes;

/**
 * Cache_Per_Process
 */
class Cache_Per_Process {
	/**
	 * Class instance
	 *
	 * @var null class instance
	 */
	private static $instance = null;

	/**
	 * Cache data
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Construction of Cache_Per_Process
	 */
	private function __construct() {
	}

	/**
	 * Singleton design pattern - Only get object of class one time
	 *
	 * @return Cache_Per_Process
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get cache by key
	 *
	 * @param Cache key $key Cache key.
	 * @param Cache key $default Default value if cache is not exist. Default to false.
	 * @return bool|mixed
	 */
	public function get_cache( $key, $default = false ) {
		if ( array_key_exists( $key, $this->data ) ) {
			return $this->data[ $key ];
		}

		return $default;
	}

	/**
	 * Set cache key and value
	 *
	 * @param Cache key   $key cache key.
	 * @param Cache value $value cache value.
	 */
	public function set_cache( $key, $value ) {
		if ( isset( $this->data[ $key ] ) ) {
			return;
		}

		$this->data[ $key ] = $value;
	}

	/**
	 * Unset cache by key
	 *
	 * @param string key $key cache key.
	 */
	public function un_set( $key ) {
		if ( isset( $this->data[ $key ] ) ) {
			unset( $this->data[ $key ] );
		}
	}

	/**
	 * Destruct cache items
	 */
	public function __destruct() {
		$keys = array_keys( $this->data );
		array_walk( $keys, array( $this, 'un_set' ) );
	}

	/**
	 * Determine cache key.
	 *
	 * @param array|string $data Determine data.
	 *
	 * @return string
	 */
	public static function determine_cache_key( $data ) {
		return is_array( $data ) ? implode( '-', $data ) : (string) $data;
	}

	/**
	 * Clear all cache key
	 */
	public function clear_cache() {
		$this->data = array();
	}
}
