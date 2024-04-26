<?php

namespace ahrefs\AhrefsSeo\Keywords;

use ahrefs\AhrefsSeo\Post_Tax;
/**
 * Keywords search implementation using TF_IDF.
 */
class Keywords_Search {

	/**
	 * Keywords for current search if found.
	 *
	 * @var Data_Content_Storage|null
	 */
	private $keywords = null;
	/**
	 * @var null|Keywords_Dataset
	 */
	private $dataset = null;
	/**
	 * @var null|Keywords_Tokenizer
	 */
	private $tokenizer = null;
	/**
	 * @var null|Keywords_Vectorizer
	 */
	private $vectorizer = null;
	/**
	 * @var int
	 */
	private $keywords_limit;
	/**
	 * Constructor
	 *
	 * @param Post_Tax $posts_tax Post Tax where to search keywords.
	 * @param int      $keywords_limit How many keywords return.
	 */
	public function __construct( Post_Tax $posts_tax, $keywords_limit = 10 ) {
		$this->keywords_limit = $keywords_limit;
		$this->run_fast_method( $posts_tax );
		// free everything, save keywords only.
		unset( $this->dataset );
		unset( $this->vectorizer );
	}
	/**
	 * Run faster method for Post Tax item.
	 *
	 * @param Post_Tax $posts_tax Post Tax.
	 * @return void
	 */
	private function run_fast_method( Post_Tax $posts_tax ) {
		$this->dataset    = new Keywords_Dataset( $posts_tax );
		$this->tokenizer  = new Keywords_Tokenizer();
		$this->vectorizer = new Keywords_Vectorizer( $this->tokenizer, $this->keywords_limit );
		// posts content without html tags.
		$item = $this->dataset->get_target();
		if ( ! is_null( $item ) ) {
			// return best keywords for each post.
			$this->vectorizer->transform( $item );
		}
		$this->keywords = $item;
	}
	/**
	 * Get all keywords
	 *
	 * @return Data_Content_Storage|null Data_Content_Storage instance with keywords and article length.
	 */
	public function get_all_keywords() {
		return $this->keywords;
	}
}