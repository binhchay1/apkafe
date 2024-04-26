<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Third_Party;

/**
 * Result with redirected URL details
 *
 * @since 0.9.4
 */
class Result_Redirected extends Result {
	/** @var int|null */
	private $is_redirected = null;

	/**
	 * Set is item redirected value
	 *
	 * @param int $is_redirected Is redirected value. 1: redirected, 0: not redirected, -1: error.
	 * @return Result_Redirected
	 */
	public function set_is_redirected( int $is_redirected ) : self {
		$this->is_redirected = $is_redirected;
		return $this;
	}
	/**
	 * Get is item redirected value
	 *
	 * @return int|null Is redirected value. 1: redirected, 0: not redirected, -1: error, null: unknown.
	 */
	public function get_is_redirected() : ?int {
		return $this->is_redirected;
	}
	/**
	 * Get all stored values as array
	 *
	 * @return array<string,mixed>
	 */
	public function as_array() : array {
		$data             = parent::as_array();
		$data['url']      = $this->get_post_tax()->get_url( true ); // original url.
		$data['r_url']    = $this->get_url_filtered(); // or not filtered?
		$data['is_redir'] = $this->is_redirected;
		return $data;
	}
	/**
	 * Load fresh data using post tax value.
	 *
	 * @see self::get_is_redirected()
	 *
	 * @return int|null Result of detection, same as get_is_redirected() returns.
	 */
	public function check() : ?int {
		$this->is_redirected = parent::check();
		if ( is_null( $this->is_redirected ) ) {
			// fill themselves with fresh data.
			$result_redirected = $this->get_post_tax()->get_redirected_data();
			$this->set_url( $result_redirected->get_url() )->set_source_id( $result_redirected->get_source_id() );

			// both urls with applied filters.
			$original_redirected_url = $result_redirected->get_url_filtered();
			$original_url            = $this->get_post_tax()->get_url( true );

			$is_redirected = false;
			if ( ! is_null( $original_redirected_url ) ) {
				// compare it with the current url.
				$is_redirected = ! $this->is_same_urls( $original_redirected_url, $original_url );
			}
			$this->set_is_redirected( $is_redirected ? 1 : 0 );
		}
		return $this->is_redirected;
	}
}
