<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Third_Party;

use ahrefs\AhrefsSeo\Post_Tax;
/**
 * Generic result class with URL and other result details
 *
 * @since 0.9.4
 */
class Result implements \JsonSerializable {
	/** @var Post_Tax */
	private $post_tax;
	/** @var string|null */
	private $url;
	/** @var string|null */
	private $source_id;

	/**
	 * Constructor
	 *
	 * @param Post_Tax    $post_tax Post or taxonomy item to store data for.
	 * @param string|null $url URL to store.
	 * @param string|null $source_id Source ID to store.
	 */
	public function __construct( Post_Tax $post_tax, ?string $url, ?string $source_id ) {
		$this->post_tax  = $post_tax;
		$this->url       = $url;
		$this->source_id = $source_id;
	}
	/**
	 * Create instance from DB field.
	 *
	 * @param Post_Tax $post_tax Post tax.
	 * @param string   $data_string One of 'canonical_data', 'noindex_data', 'redirected_data'.
	 * @return Result|Result_Canonical|Result_Noindex|Result_Redirected
	 */
	public static function create( Post_Tax $post_tax, string $data_string ) : self {
		switch ( $data_string ) {
			case 'canonical_data':
				return new Result_Canonical( $post_tax, null, null );
			case 'noindex_data':
				return new Result_Noindex( $post_tax, null, null );
			case 'redirected_data':
				return new Result_Redirected( $post_tax, null, null );
		}
		return new self( $post_tax, null, null );
	}
	/**
	 * Get URL.
	 *
	 * @return string|null Stored URL value.
	 */
	public function get_url() : ?string {
		return $this->url;
	}
	/**
	 * Get URL filters.
	 *
	 * @return string|null Stored URL value after filters applied.
	 */
	public function get_url_filtered() : ?string {
		return is_null( $this->url ) ? null : (string) apply_filters( 'ahrefs_seo_original_url', $this->url );
	}
	/**
	 * Get Source ID.
	 *
	 * @return string|null Stored Source ID value.
	 */
	public function get_source_id() : ?string {
		return $this->source_id;
	}
	/**
	 * Get Post tax.
	 *
	 * @return Post_Tax Stored post tax value.
	 */
	public function get_post_tax() : Post_Tax {
		return $this->post_tax;
	}
	/**
	 * Get all stored values as array
	 *
	 * @return array<string,mixed>
	 */
	public function as_array() : array {
		return [
			'source_id' => $this->source_id,
		];
	}
	/**
	 * Load fresh data using post tax value.
	 *
	 * @return int|null Result of detection. null: this post tax item exists and has a view URL; -1: error, not exists.
	 */
	public function check() : ?int {
		return ! $this->post_tax->exists() || ( '' === $this->post_tax->get_url() ) ? -1 : null; // error if post or category did not exist.
	}

	/**
	 * Compare two urls.
	 *
	 * @param string $url_a First URL.
	 * @param string $url_b Second URL.
	 * @return bool True: the same, false: different.
	 */
	protected function is_same_urls( string $url_a, string $url_b ) : bool {
		return urldecode( $url_a ) === urldecode( $url_b );
	}
	/**
	 * Set URL
	 *
	 * @param string|null $url URL.
	 * @return Result
	 */
	protected function set_url( ?string $url ) : self {
		$this->url = $url;
		return $this;
	}
	/**
	 * Set source ID.
	 *
	 * @param string|null $source_id Source ID or null.
	 * @return Result
	 */
	protected function set_source_id( ?string $source_id ) : self {
		$this->source_id = $source_id;
		return $this;
	}
	/**
	 * Specify data which should be serialized to JSON
	 *
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->as_array();
	}
}
