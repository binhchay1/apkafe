<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Keywords\Data_Content_Storage;
use ahrefs\AhrefsSeo\Keywords\Data_Keyword_Tfidf;
use ahrefs\AhrefsSeo\Keywords\Data_Keyword;
use ahrefs\AhrefsSeo\Keywords\Data_Keywords;

/**
 * Work with current keywords and keyword from GSC/TF-IDF.
 *
 * @since 0.8.0
 */
class Post_Tax_With_Keywords extends Post_Tax {
	/*
	An article must contain at least ... words, otherwise an empty list of keywords will be returned.
	*/
	private const MIN_WORDS_LIMIT = 300;
	/**
	 * Current keyword
	 *
	 * @var string|null
	 */
	protected $current_keyword = null;
	/**
	 * Keyword from manual input field
	 *
	 * @var string|null
	 */
	protected $manual_keyword = null;
	/**
	 * Position of current keyword
	 *
	 * @var float|null
	 */
	protected $position = null;
	/**
	 * Keywords from GSC
	 *
	 * @var array{total_clicks:int, total_impr:int, result?:array<array{query:string, clicks:int, pos:float, impr:int, error?:\Exception}>}|null
	 */
	protected $keywords = null;
	/**
	 * Keywords from TF-IDF
	 *
	 * @var Data_Keyword_Tfidf[]|null
	 */
	protected $keywords2 = null;
	/**
	 * Keywords from TF-IDF
	 *
	 * @var Data_Keywords|null
	 */
	protected $keywords_imported = null;
	/**
	 * Article words count is too low.
	 *
	 * @var bool|null
	 */
	protected $keywords2_low_len = null;
	/**
	 * Cached position of current keyword and imported keywords, same format as $keywords used.
	 *
	 * @var array<array{query:string, clicks:int, pos:float, impr:int}>|null
	 */
	protected $keywords_pos = null;
	/**
	 * Source of currently selected keyword (target keyword).
	 *
	 * @var string|null
	 */
	protected $keyword_source = null;
	/**
	 * Is keyword approved.
	 *
	 * @var bool|null
	 */
	protected $is_keyword_approved = null;
	/**
	 * Error message if any
	 *
	 * @var string|null
	 */
	protected $error_message = null;

	/**
	 * Create instance of class using parent class instance.
	 *
	 * @see load_from_db() For load values, otherwise instance fields are filled by null.
	 *
	 * @param Post_Tax|Post_Tax_With_Keywords $post_tax Source Post Tax instance.
	 * @return Post_Tax_With_Keywords
	 */
	public static function create_from( Post_Tax $post_tax ) : self {
		if ( $post_tax instanceof Post_Tax_With_Keywords ) {
			return $post_tax;
		}
		return new Post_Tax_With_Keywords( $post_tax->get_post_id(), $post_tax->get_taxonomy(), $post_tax->get_snapshot_id() );
	}

	/**
	 * Set current keyword for post/term
	 *
	 * @param string $current_keyword Current Keyword.
	 * @return Post_Tax_With_Keywords
	 */
	public function set_keyword_current( string $current_keyword ) : self {
		$this->current_keyword = $current_keyword;
		return $this;
	}

	/**
	 * Set position of current keyword
	 *
	 * @param float|null $position Position of current keyword.
	 * @return Post_Tax_With_Keywords
	 */
	public function set_position( ?float $position ) : self {
		$this->position = $position;
		return $this;
	}

	/**
	 * Set recommended keywords lists for post/term
	 *
	 * @param array{total_clicks:int, total_impr:int, result:array<array{query:string, clicks:int, pos:float, impr:int}>, error?:\Exception}|null $keywords Keywords from GSC.
	 * @return Post_Tax_With_Keywords
	 */
	public function set_keywords( ?array $keywords ) : self {
		$this->keywords = $keywords;
		return $this;
	}

	/**
	 * Set recommended keywords lists for post/term
	 *
	 * @param Data_Content_Storage|null $keywords2 Keywords from TF-IDF.
	 * @return Post_Tax_With_Keywords
	 */
	public function set_keywords2( ?Data_Content_Storage $keywords2 ) : self {
		if ( ! is_null( $keywords2 ) ) {
			if ( $keywords2->get_count() >= self::MIN_WORDS_LIMIT ) {
				$this->keywords2         = $keywords2->get_keywords();
				$this->keywords2_low_len = false;
			} else {
				$this->keywords2_low_len = true;
				$this->keywords2         = [];
			}
		} else {
			$this->keywords2         = null;
			$this->keywords2_low_len = null;
		}
		return $this;
	}

	/**
	 * Set recommended keywords lists for post/term
	 *
	 * @param Data_Keywords|null $data_keywords Imported keywords.
	 * @return Post_Tax_With_Keywords
	 */
	public function set_keywords_imported( ?Data_Keywords $data_keywords ) : self {
		$this->keywords_imported = $data_keywords;
		return $this;
	}

	/**
	 * Set recommended keywords lists for post/term
	 *
	 * @param Data_Keyword[]|array{query:string, clicks:int, pos:float, impr:int, error?:\Exception}[] $keywords_pos Row with keyword, position, clicks, etc. for current keyword.
	 * @return Post_Tax_With_Keywords
	 */
	public function set_keywords_pos( array $keywords_pos ) : self {
		$this->keywords_pos = [];
		foreach ( $keywords_pos as $row ) {
			if ( is_object( $row ) && ( $row instanceof Data_Keyword ) ) {
				$this->keywords_pos[] = $row->as_gsc_array();
			} elseif ( is_array( $row ) ) {
				$this->keywords_pos[] = $row;
			}
		}
		return $this;
	}

	/**
	 * Set keyword source
	 *
	 * @since 0.8.8
	 *
	 * @param string|null $source_id Source ID, one of Sources::SOURCE_*.
	 * @return Post_Tax_With_Keywords
	 */
	public function set_keyword_source( ?string $source_id ) : self {
		$this->keyword_source = $source_id;
		return $this;
	}

	/**
	 * Set error message
	 *
	 * @param string|null $error_message Error message.
	 * @return Post_Tax_With_Keywords
	 */
	public function set_error_message( ?string $error_message ) : self {
		$this->error_message = $error_message;
		return $this;
	}

	/**
	 * Get current keyword of post/term.
	 *
	 * @return string|null
	 */
	public function get_keyword_current() : ?string {
		return $this->current_keyword;
	}

	/**
	 * Get keyword manual input field of post/term.
	 *
	 * @return string|null
	 */
	public function get_keyword_manual() : ?string {
		return $this->manual_keyword;
	}

	/**
	 * Get position of current keyword
	 *
	 * @return float|null
	 */
	public function get_position() : ?float {
		return $this->position;
	}

	/**
	 * Get recommended by GSC keywords
	 *
	 * @return array{total_clicks:int, total_impr:int, result?:array<array{query:string, clicks:int, pos:float, impr:int}>, error?:\Exception}|null
	 */
	public function get_keywords() : ?array {
		return $this->keywords;
	}

	/**
	 * Get recommended by TF-IDF keywords
	 *
	 * @return Data_Keyword_Tfidf[]|null
	 */
	public function get_keywords2() : ?array {
		return $this->keywords2;
	}

	/**
	 * Get imported from third-party SEO plugins keywords
	 *
	 * @since 0.8.8
	 *
	 * @return Data_Keywords
	 */
	public function get_keywords_imported() : Data_Keywords {
		return $this->keywords_imported ?? new Data_Keywords();
	}

	/**
	 * Get is article have low words count.
	 *
	 * @since 0.8.2
	 *
	 * @return bool|null
	 */
	public function get_keywords2_low_len() : ?bool {
		return $this->keywords2_low_len;
	}

	/**
	 * Get row with position for current keyword.
	 *
	 * @return array<array{query:string, clicks:int, pos:float, impr:int}>|null
	 */
	public function get_keywords_pos() : ?array {
		return $this->keywords_pos;
	}

	/**
	 * Get keyword source
	 *
	 * @since 0.8.8
	 *
	 * @return string|null
	 */
	public function get_keyword_source() : ?string {
		return $this->keyword_source;
	}

	/**
	 * Get error message if any happened
	 *
	 * @return string|null
	 */
	public function get_error_message() : ?string {
		return $this->error_message;
	}

	/**
	 * Is keyword approved?
	 *
	 * @return bool|null
	 */
	public function get_is_keyword_approved() : ?bool {
		return $this->is_keyword_approved;
	}


	/**
	 * Load all values from DB.
	 *
	 * @return Post_Tax_With_Keywords
	 */
	public function load_from_db() : self {
		global $wpdb;
		$fields = $wpdb->get_row( $wpdb->prepare( "SELECT keyword, keyword_manual, position, kw_gsc, kw_idf, kw_imported, kw_pos, kw_low, kw_source, is_approved_keyword FROM {$wpdb->ahrefs_content} WHERE post_id = %d AND snapshot_id = %d AND taxonomy = %s", $this->post_id, $this->snapshot_id, $this->taxonomy ), ARRAY_A );
		if ( is_array( $fields ) ) {
			$this->current_keyword     = is_string( $fields['keyword'] ) ? $fields['keyword'] : null;
			$this->manual_keyword      = is_string( $fields['keyword_manual'] ) ? $fields['keyword_manual'] : null;
			$this->position            = is_string( $fields['position'] ) ? floatval( $fields['position'] ) : null;
			$this->keywords            = is_string( $fields['kw_gsc'] ) ? (array) json_decode( $fields['kw_gsc'], true ) : null; // @phpstan-ignore-line -- this array already should contain previously saved keywords data.
			$this->keywords2           = [];
			$this->keywords2_low_len   = is_string( $fields['kw_low'] ) ? (bool) $fields['kw_low'] : null; // article length is too low.
			$this->keywords_pos        = is_string( $fields['kw_pos'] ) ? (array) json_decode( $fields['kw_pos'], true ) : null;
			$this->keyword_source      = is_string( $fields['kw_source'] ) ? $fields['kw_source'] : null;
			$this->is_keyword_approved = is_string( $fields['is_approved_keyword'] ) ? (bool) $fields['is_approved_keyword'] : null;
			if ( $this->keywords2_low_len ) {
				$this->keywords2 = [];
			}
			$keywords2 = is_string( $fields['kw_idf'] ) ? (array) json_decode( $fields['kw_idf'], true ) : null;
			if ( is_array( $keywords2 ) ) {
				foreach ( $keywords2 as $item ) {
					if ( $item instanceof Data_Keyword_Tfidf ) {
						$this->keywords2[] = $item;
					} elseif ( is_array( $item ) && isset( $item['q'] ) && isset( $item['f'] ) ) {
						$this->keywords2[] = new Data_Keyword_Tfidf( (string) $item['q'], floatval( $item['f'] ) );
					}
				}
			}
			$this->keywords_imported = null;
			if ( is_string( $fields['kw_imported'] ) ) {
				$this->keywords_imported = Data_Keywords::from_array( (array) json_decode( $fields['kw_imported'], true ) );
			}
		}
		return $this;
	}
}
