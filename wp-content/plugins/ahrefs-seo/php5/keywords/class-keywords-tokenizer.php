<?php

namespace ahrefs\AhrefsSeo\Keywords;

/**
 * Tokenizer implementation for TF_IDF keywords search.
 */
class Keywords_Tokenizer {

	const ADD_SINGLE_TIME_WORDS = false;
	/**
	 * Associative array of found phrases, when it found first time.
	 * key is phrase, value is [$a, $b] - pair of indexes in $content_blocks[$a][$b] array, where this phrase started.
	 * Length of phrase is $word_len of current iterations.
	 *
	 * @var array
	 */
	private $first_indexes = [];
	/**
	 * Associative array of used phrases.
	 * key is phrase, value is [$a, $b] - pair of indexes in $content_blocks[$a][$b] array, where this phrase started.
	 * Length of phrase is $word_len of current iterations.
	 *
	 * @var array
	 */
	private $used_indexes = [];
	/**
	 * @var array
	 */
	private $counters = [];
	/**
	 * Built in stop words list for English.
	 *
	 * @var array Stop words.
	 */
	protected $stop_words_list = [
		'about',
		'above',
		'after',
		'again',
		'against',
		'all',
		'am',
		'an',
		'and',
		'any',
		'are',
		'aren\'t',
		'as',
		'at',
		'be',
		'because',
		'been',
		'before',
		'being',
		'below',
		'between',
		'both',
		'but',
		'by',
		'can\'t',
		'cannot',
		'could',
		'couldn\'t',
		'did',
		'didn\'t',
		'do',
		'does',
		'doesn\'t',
		'doing',
		'don\'t',
		'down',
		'during',
		'each',
		'few',
		'for',
		'from',
		'further',
		'had',
		'hadn\'t',
		'has',
		'hasn\'t',
		'have',
		'haven\'t',
		'having',
		'he',
		'he\'d',
		'he\'ll',
		'he\'s',
		'her',
		'here',
		'here\'s',
		'hers',
		'herself',
		'him',
		'himself',
		'his',
		'how',
		'how\'s',
		'i\'d',
		'i\'ll',
		'i\'m',
		'i\'ve',
		'if',
		'in',
		'into',
		'is',
		'isn\'t',
		'it',
		'it\'s',
		'its',
		'itself',
		'let\'s',
		'me',
		'more',
		'most',
		'mustn\'t',
		'my',
		'myself',
		'no',
		'nor',
		'not',
		'of',
		'off',
		'on',
		'once',
		'only',
		'or',
		'other',
		'ought',
		'our',
		'oursourselves',
		'out',
		'over',
		'own',
		'per', // .
		'same',
		'shan\'t',
		'she',
		'she\'d',
		'she\'ll',
		'she\'s',
		'should',
		'shouldn\'t',
		'so',
		'some',
		'such',
		'than',
		'that',
		'that\'s',
		'the',
		'their',
		'theirs',
		'them',
		'themselves',
		'then',
		'there',
		'there\'s',
		'these',
		'they',
		'they\'d',
		'they\'ll',
		'they\'re',
		'they\'ve',
		'this',
		'those',
		'through',
		'to',
		'too',
		'under',
		'until',
		'up',
		'very',
		'was',
		'wasn\'t',
		'we',
		'we\'d',
		'we\'ll',
		'we\'re',
		'we\'ve',
		'were',
		'weren\'t',
		'what',
		'what\'s',
		'when',
		'when\'s',
		'where',
		'where\'s',
		'which',
		'while',
		'who',
		'who\'s',
		'whom',
		'why',
		'why\'s',
		'will', // .
		'with',
		'won\'t',
		'would',
		'wouldn\'t',
		'you',
		'you\'d',
		'you\'ll',
		'you\'re',
		'you\'ve',
		'your',
		'yours',
		'yourself',
		'yourselves',
	];
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->stop_words_list = array_flip( $this->stop_words_list );
	}
	/**
	 * Split string to words and phrases.
	 *
	 * @param string $text Text with '|' as formerly tags.
	 * @param int    $words_count How many words can have a phrase.
	 * @param bool   $return_array Return as array with frequencies instead of array of tokens.
	 *
	 * @return array
	 */
	public function tokenize( $text, $words_count = 5, $return_array = false ) {
		$result    = []; // result as array of tokens.
		$result123 = array_fill( 1, $words_count, [] ); // result as array with tokens and count of each token.
		$this->counters              = [ 1 => [] ];
		$parts                       = explode( '|', $text );
		$content_blocks              = [];
		$this->used_indexes          = [];
		$this->first_indexes         = [];
		$word_len                    = 2;
		$this->counters[ $word_len ] = [];
		foreach ( $parts as $part ) {
			$part = trim( $part );
			if ( '' !== $part ) {
				$substrings       = explode( ' ', $part );
				$a                = count( $content_blocks );
				$content_blocks[] = $substrings;
				$queue            = []; // reset queue, so we do not include words from different blocks to phrase.
				$queue_len        = 0;
				foreach ( $substrings as $b => $word ) {
					$len = function_exists( 'mb_strlen' ) ? mb_strlen( $word ) : strlen( $word );
					if ( ! $this->is_stop_word( $word ) && ! is_numeric( $word ) && $len > 1 ) { // skip any stop words or numbers or too short words.
						if ( self::ADD_SINGLE_TIME_WORDS ) { // @phpstan-ignore-line -- constant can have any value.
							$result[] = $word; // add word.
						}
						if ( $return_array ) {
							if ( isset( $this->counters[1][ $word ] ) ) {
								$result[] = $word; // add word that found more that once only.
								if ( 1 === $this->counters[1][ $word ]++ ) {
									$result[] = $word; // add word twice.
								}
							} else {
								$this->counters[1][ $word ] = 1;
							}
						}
					}
					// but allow phrases with stop words.
					$queue[] = $word; // add to queue.
					++$queue_len;
					if ( $queue_len > $word_len ) { // remove first item from queue.
						array_shift( $queue );
						--$queue_len;
					}
					if ( $queue_len === $word_len ) { // add phrases from queue to result.
						$index = "{$queue[0]} {$queue[1]}";
						$this->add_phrase( $result, $word_len, $index, $a, $b - 1 ); // b-1 because of queue length = 2, and we need index of starting word.
					}
				}
			}
		}
		if ( $return_array ) {
			$result123[1] = array_filter(
				$this->counters[1],
				function ( $value ) {
					return $value > 1;
				}
			); // [ string word => int count].
		}
		if ( $words_count < 3 ) {
			return $return_array ? $result123 : $result;
		}
		$this->first_indexes = [];
		/*
		 * === phrase length 3, 4...
		 * Build longer phrases using already found phrases from previous iteration.
		 * Save new phrases if they used more than once.
		 * $this->used_indexes - is a pointer to begin of each existing phrase.
		 * $word_len - is a desired phrase length.
		 */
		for ( $word_len = 3; $word_len <= $words_count; $word_len++ ) {
			// === Remove all phrases with count < 2.
			$word_len_1                    = $word_len - 1;
			$this->counters[ $word_len_1 ] = array_filter(
				$this->counters[ $word_len_1 ],
				function ( $v ) {
					return $v[0] > 1;
				}
			);
			if ( $return_array && is_array( $this->counters[ $word_len_1 ] ) ) {
				foreach ( $this->counters[ $word_len_1 ] as $word => list($count, $indexes) ) {
					$result123[ $word_len_1 ][ $word ] = $count;
				}
			}
			$this->counters[ $word_len ] = [];
			$existing_phrases            = $this->used_indexes;
			// source array is unique because we check indexes before add.
			if ( count( $existing_phrases ) ) {
				$this->used_indexes  = [];
				$this->first_indexes = [];
				foreach ( $existing_phrases as &$pointer ) {
					$a         =& $pointer[0];
					$b_current =& $pointer[1];
					// can we add a word before the current phrase to new phrase?
					if ( $b_current > 0 ) {
						$b     = $b_current - 1;
						$index = implode( ' ', array_slice( $content_blocks[ $a ], $b, $word_len ) );
						$this->add_phrase( $result, $word_len, $index, $a, $b );
					}
					// can we add a word after the current phrase to new phrase?
					if ( $b_current + $word_len < count( $content_blocks[ $a ] ) - 1 ) {
						$b     = $b_current; // start from same index, but 1 word longer.
						$index = implode( ' ', array_slice( $content_blocks[ $a ], $b, $word_len ) );
						$this->add_phrase( $result, $word_len, $index, $a, $b );
					}
				}
			}
		}
		// clean memory.
		$this->used_indexes  = [];
		$this->first_indexes = [];
		if ( $return_array ) {
			foreach ( $this->counters[ $words_count ] as $word => list($count, $indexes) ) {
				if ( $count > 1 ) {
					$result123[ $words_count ][ $word ] = $count;
				}
			}
			$result = [];
		}
		$this->counters = [];
		return $return_array ? $result123 : $result;
	}
	/**
	 * Add phrase to index and update counters
	 *
	 * @param array  $result Result array, where to add.
	 * @param int    $word_len Word length.
	 * @param string $index Phrase to add.
	 * @param int    $a [$a, $b] - pair of indexes in $content_blocks[$a][$b] array, where this phrase started.
	 * @param int    $b [$a, $b] - pair of indexes in $content_blocks[$a][$b] array, where this phrase started.
	 * @return void
	 */
	private function add_phrase( array &$result, $word_len, &$index, $a, $b ) {
		if ( isset( $this->counters[ $word_len ][ $index ] ) ) {
			// check is $a $b pair unique?
			if ( in_array( [ $a, $b ], $this->counters[ $word_len ][ $index ][1] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				return; // this indexes already added.
			}
			$result[] = $index; // save to results - for current match.
			++$this->counters[ $word_len ][ $index ][0];
			$this->counters[ $word_len ][ $index ][1][] = [ $a, $b ];
			if ( isset( $this->first_indexes[ $index ] ) ) {
				$result[]             = $index; // save to results - for first match.
				$this->used_indexes[] = $this->first_indexes[ $index ];
				unset( $this->first_indexes[ $index ] );
			}
			$this->used_indexes[] = [ $a, $b ];
		} else {
			$this->counters[ $word_len ][ $index ] = [ 1, [ [ $a, $b ] ] ];
			$this->first_indexes[ $index ]         = [ $a, $b ];
		}
	}
	/**
	 * Is the word a stop word?
	 *
	 * @param string $token Token (Char or word or string).
	 * @return bool
	 */
	private function is_stop_word( &$token ) {
		return isset( $this->stop_words_list[ $token ] );
	}
	/**
	 * Return all stop words.
	 *
	 * @return array<string, int> Associative array where word is index.
	 */
	public function get_stop_words() {
		return $this->stop_words_list;
	}
}