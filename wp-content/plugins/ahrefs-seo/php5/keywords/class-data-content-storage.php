<?php

namespace ahrefs\AhrefsSeo\Keywords;

/**
 * Data keyword content storage
 *
 * @since 0.8.2
 */
class Data_Content_Storage {

	/**
	 * Words divided by space, all tags and some punctuation replaced by '|'.
	 *
	 * @var string
	 */
	private $content = '';
	/** @var int */
	private $count = 0;
	/**
	 * List of keyword items, ordered by featured desc.
	 *
	 * @var Data_Keyword_Tfidf[]
	 */
	private $keywords = [];
	/**
	 * Get words count in content
	 *
	 * @return int
	 */
	public function get_count() {
		return $this->count;
	}
	/**
	 * Set raw keywords from array
	 *
	 * @param array<string|int, float> $keywords_raw Associative array [ keyword => featured ], ordered by featured desc.
	 * @return Data_Content_Storage
	 */
	public function set_raw_keywords( array $keywords_raw ) {
		$this->content  = '';
		$this->keywords = [];
		foreach ( $keywords_raw as $word => $featured ) {
			$this->keywords[] = new Data_Keyword_Tfidf( "{$word}", $featured );
		}
		return $this;
	}
	/**
	 * Get keywords
	 *
	 * @return Data_Keyword_Tfidf[] List of keyword items.
	 */
	public function get_keywords() {
		return $this->keywords;
	}
	/**
	 * Set content
	 *
	 * @param string $content Words divided by space, all tags and some punctuation replaced by '|'.
	 * @return Data_Content_Storage
	 */
	public function set_content( $content ) {
		$this->content = $content;
		$this->count   = strlen( $content ) ? substr_count( $content, ' ' ) + substr_count( $content, '|' ) + 1 : 0;
		return $this;
	}
	/**
	 * Get content
	 *
	 * @return string Words divided by space, all tags and some punctuation replaced by '|'.
	 */
	public function get_content() {
		return $this->content;
	}
}