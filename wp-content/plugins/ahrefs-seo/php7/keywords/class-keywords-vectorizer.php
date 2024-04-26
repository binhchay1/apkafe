<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Keywords;

/**
 * Vectorizer implementation for TF_IDF keywords search.
 */
class Keywords_Vectorizer {

	/**
	* Max words count in keyword phrase.
	*/
	const WORDS_COUNT_MAX = 5;
	/**
	* Min words count in keyword phrase.
	*/
	const WORDS_COUNT_MIN = 2;
	/**
	* Allow phrases with less that WORDS_COUNT_MIN if nothing found.
	*/
	const FALLBACK_TO_SHORT_PHRASE = true;
	/**
	* Decrease importance of phrase if it has stop words.
	*/
	const DECREASE_IF_HAS_STOP_WORD = true;

	/**
	 * @var Keywords_Tokenizer
	 */
	protected $tokenizer;

	/**
	 * How many top results save
	 *
	 * @var int
	 */
	private $top_words_count;

	/**
	 * Constructor
	 *
	 * @param Keywords_Tokenizer $tokenizer Tokenizer instance.
	 * @param int                $top_words_count How many results (top words) save.
	 */
	public function __construct( Keywords_Tokenizer $tokenizer, int $top_words_count = 50 ) {
		$this->tokenizer       = $tokenizer;
		$this->top_words_count = $top_words_count;
	}

	/**
	 * Get list of texts and replace it with list of recommended keywords.
	 *
	 * @param Data_Content_Storage $text Text for transforming.
	 * @return void
	 */
	public function transform( Data_Content_Storage &$text ) : void {
		$tokens = $this->tokenizer->tokenize( $text->get_content(), self::WORDS_COUNT_MAX, true );
		$text->set_raw_keywords( $this->transform_counts( $tokens ) );
	}

	/**
	 * Default fast method.
	 * Get counts of phrases and return recommended keywords using limit.
	 * Use stop words from keyword tokenizer.
	 *
	 * @param array<int, array> $counts Words and phrases counts data.
	 *
	 * @return array<string, float> Associative array [ keyword => featured ], ordered by featured desc.
	 */
	protected function transform_counts( array &$counts ) : array {
		$sample = []; // reset sample values.

		if ( self::DECREASE_IF_HAS_STOP_WORD ) { // @phpstan-ignore-line -- constant can have any value in the future.
			$stop_words = $this->tokenizer->get_stop_words();

			foreach ( $counts as $words_len => &$data ) {
				if ( (int) $words_len > 1 ) {
					foreach ( $data as $phrase => &$count ) {
						$words            = explode( ' ', $phrase );
						$stop_words_count = array_filter(
							$words,
							function( $word ) use ( $stop_words ) {
								return isset( $stop_words[ $word ] );
							}
						);
						if ( count( $stop_words_count ) ) {
							// move phrase to lower words count. This will decrease its importance.
							$new_len                       = (int) $words_len - count( $stop_words_count );
							$counts[ $new_len ][ $phrase ] = $count;
							unset( $counts[ $words_len ][ $phrase ] );
						}
					}
				}
			}
		}
		$backup_words = [];
		if ( self::FALLBACK_TO_SHORT_PHRASE ) { // @phpstan-ignore-line -- constant can have any value in the future.
			for ( $i = self::WORDS_COUNT_MIN; $i > 0; $i-- ) {
				if ( ! count( $backup_words ) && isset( $counts[ $i ] ) && count( $counts[ $i ] ) ) {
					$backup_words = $counts[ $i ];
				}
			}
		}
		// filter by minimal words count.
		for ( $i = -self::WORDS_COUNT_MAX; $i < self::WORDS_COUNT_MIN; $i++ ) {
			unset( $counts[ $i ] );
		}
		// remove empty items.
		$counts = array_filter(
			$counts,
			function( $values ) {
				return count( $values ) > 0;
			}
		);

		if ( ! empty( $counts ) ) {
			/**
			* @var int[] $sums
			*/
			$sums       = array_map( 'array_sum', $counts );
			$count_sums = count( $sums ); // int.

			$samples = [];
			// search the best keywords here.
			foreach ( $counts as $word_count => &$data ) {
				$samples[ $word_count ] = [];
				$featured               = log( $sums[ $word_count ] / $count_sums + 1, max( $sums ) / $count_sums );

				foreach ( $data as $word => &$count ) {
					$samples[ $word_count ][ $word ] = $count / $featured;
				}

				arsort( $samples[ $word_count ] ); // sort many smaller arrays is better that sort one big array.
				$samples[ $word_count ] = array_slice( $samples[ $word_count ], 0, $this->top_words_count );
				$sample                 = array_merge( $sample, $samples[ $word_count ] );
			}
		}
		if ( ! count( $sample ) && self::FALLBACK_TO_SHORT_PHRASE ) { // @phpstan-ignore-line -- constant can have any value in the future.
			arsort( $backup_words );
			foreach ( $backup_words as $word => &$count ) {
				$sample[ $word ] = $count;
			}
		}
		arsort( $sample );

		return array_slice( $sample, 0, $this->top_words_count );
	}

}
