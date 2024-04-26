<?php

namespace ahrefs\AhrefsSeo\Data_Api;

/**
 * Store info received from Ahrefs API.
 */
class Data_Metrics_Extended {

	/** @var int|null Number of external backlinks found on the referring pages that link to the target. */
	private $backlinks;
	/** @var int|null Number of domains containing at least one backlink that links to the target. */
	private $ref_domains;
	/** @var bool Was there an error during request. */
	private $error;
	/**
	 * Constructor
	 *
	 * @param int|null $backlinks Backlinks number.
	 * @param int|null $ref_domains Number of referring domains.
	 * @param bool     $error Was there an error during request.
	 */
	private function __construct( $backlinks = null, $ref_domains = null, $error = false ) {
		$this->backlinks   = $backlinks;
		$this->ref_domains = $ref_domains;
		$this->error       = $error;
	}
	/**
	 * Create instance with data
	 *
	 * @param int|null $backlinks Backlinks number.
	 * @param int|null $ref_domains Number of referring domains.
	 */
	public static function data( $backlinks = null, $ref_domains = null ) {
		return new self( $backlinks, $ref_domains );
	}
	/**
	 * Create instance with error
	 *
	 * @return Data_Metrics_Extended
	 */
	public static function error() {
		return new self( null, null, true );
	}
	/**
	 * Get backlinks number
	 *
	 * @return int|null
	 */
	public function get_backlinks() {
		return $this->backlinks;
	}
	/**
	 * Get referring domains number
	 *
	 * @return int|null
	 */
	public function get_ref_domains() {
		return $this->ref_domains;
	}
	/**
	 * Is there an error?
	 *
	 * @return bool
	 */
	public function is_error() {
		return $this->error;
	}
}