<?php

namespace ahrefs\AhrefsSeo\Keywords;

/**
 * Data storage class for TF IDF results.
 *
 * @since 0.8.2
 */
class Data_Keyword_Tfidf {

	/**
	 * Keyword.
	 *
	 * @var string
	 */
	public $q = '';
	/**
	 * Feature index, some float, bigger is better.
	 *
	 * @var float
	 */
	public $f = 0;
	/**
	 * Constructor
	 *
	 * @param string $keyword Keyword.
	 * @param float  $featured Featured score.
	 */
	public function __construct( $keyword, $featured ) {
		$this->q = $keyword;
		$this->f = $featured;
	}
	/**
	 * Get keyword
	 *
	 * @return string
	 */
	public function get_keyword() {
		return $this->q;
	}
}