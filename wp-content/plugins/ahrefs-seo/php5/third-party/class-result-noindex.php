<?php

namespace ahrefs\AhrefsSeo\Third_Party;

/**
 * Result with noindex details
 *
 * @since 0.9.4
 */
class Result_Noindex extends Result {

	/** @var int|null */
	private $is_noindex = null;
	/**
	 * Set is item is noidex value
	 *
	 * @param int $is_noindex Is no index value. 1: noindex, 0: indexed, -1: error.
	 * @return Result_Noindex
	 */
	public function set_is_noindex( $is_noindex ) {
		$this->is_noindex = $is_noindex;
		return $this;
	}
	/**
	 * Get is noindex
	 *
	 * @return int Value null: not checked, -1: error, 0: index, 1: noindex.
	 */
	public function get_is_nonindex() {
		return $this->is_noindex;
	}
	/**
	 * Load fresh data using post tax value.
	 *
	 * @see self::get_is_redirected()
	 *
	 * @return int|null Result of detection, same as get_is_redirected() returns.
	 */
	public function check() {
		// fill themselves with fresh data.
		$this->is_noindex = parent::check();
		if ( is_null( $this->is_noindex ) ) {
			$is_noindex = null;
			$sources    = Sources::get()->get_noindex_sources();
			$post_tax   = $this->get_post_tax();
			// fill URL with source post URL if was empty.
			if ( is_null( $this->get_url() ) ) {
				$this->set_url( $post_tax->get_url() );
			}
			foreach ( $sources as $source ) {
				if ( $source->is_available() ) {
					$is_noindex = $source->is_noindex( $post_tax );
					if ( ! is_null( $is_noindex ) ) {
						$this->set_source_id( $source->get_source_id() );
						$this->set_is_noindex( $is_noindex ? 1 : 0 );
						break;
					}
				}
			}
			if ( is_null( $is_noindex ) ) {
				$this->set_source_id( null );
				$this->set_is_noindex( 0 ); // indexed by default.
			}
		}
		return $this->is_noindex ? 1 : 0;
	}
}