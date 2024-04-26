<?php

namespace ahrefs\AhrefsSeo\Third_Party;

/**
 * Result with canonical URL details
 *
 * @since 0.9.4
 */
class Result_Canonical extends Result {

	/** @var int|null */
	private $is_noncanonical = null;
	/**
	 * Set is item is non-canonical value
	 *
	 * @param int $is_noncanonical Is non-canonical value. 1: non-canonical, 0: canonical, -1: error.
	 * @return Result_Canonical
	 */
	public function set_is_noncanonical( $is_noncanonical ) {
		$this->is_noncanonical = $is_noncanonical;
		return $this;
	}
	/**
	 * Get is non-canonical
	 *
	 * @return int Value null: not checked, -1: error, 0: canonical, 1: non-canonical.
	 */
	public function get_is_noncanonical() {
		return $this->is_noncanonical;
	}
	/**
	 * Get all stored values as array
	 *
	 * @return array<string,mixed>
	 */
	public function as_array() {
		$data        = parent::as_array();
		$data['url'] = $this->get_post_tax()->get_url( true ); // original url.
		$data['c_url']    = $this->get_url_filtered();
		$data['is_not_c'] = $this->is_noncanonical;
		return $data;
	}
	/**
	 * Load fresh data using post tax value.
	 *
	 * @see self::get_is_redirected()
	 *
	 * @return int|null Result of detection, same as get_is_redirected() returns.
	 */
	public function check() {
		$this->is_noncanonical = parent::check();
		if ( is_null( $this->is_noncanonical ) ) {
			// fill themselves with fresh data.
			$result_canonical = $this->get_post_tax()->get_canonical_data();
			$this->set_url( $result_canonical->get_url() )->set_source_id( $result_canonical->get_source_id() );
			// both urls with applied filters.
			$original_canonical_url = $this->get_url_filtered();
			$original_url           = $this->get_post_tax()->get_url( true );
			$is_noncanonical        = false;
			if ( ! is_null( $original_canonical_url ) ) {
				// compare it with the current url.
				$is_noncanonical = ! $this->is_same_urls( $original_canonical_url, $original_url );
			}
			$this->set_is_noncanonical( $is_noncanonical ? 1 : 0 );
		}
		return $this->is_noncanonical;
	}
}