<?php

namespace ahrefs\AhrefsSeo\Keywords;

/**
 * Store details about many keywords in kw_imported (and kw_pos?) column.
 *
 * @since 0.8.8
 */
class Data_Keywords {

	const VERSION = 1;
	/**
	 * @var Data_Keyword[] The keywords list.
	 */
	public $list = [];
	/**
	 * Constructor
	 *
	 * @param Data_Keyword[]|null $keywords_list List of Data_Keyword items.
	 */
	public function __construct( array $keywords_list = [] ) {
		$this->list = ! is_null( $keywords_list ) ? $this::filter_empty_values( $keywords_list ) : [];
	}
	/**
	 * Return Data_Keyword instance using array with data.
	 *
	 * @param array $data Array previously saved from as_array() call.
	 * @return self
	 */
	public static function from_array( array $data ) {
		if ( isset( $data['v'] ) && 1 === $data['v'] && is_array( $data['l'] ) ) {
			// check version.
			$keywords_list = self::filter_empty_values(
				array_map(
					function ( $row ) {
						// do not specify parameter type, because we must check it before using.
						return is_array( $row ) ? Data_Keyword::from_array( $row ) : null;
					},
					$data['l']
				)
			);
			return new self( $keywords_list );
		}
		return new self( null );
	}
	/**
	 * Return content as associative array
	 *
	 * @return array<string, int|array>
	 */
	public function as_array() {
		return [
			'v' => $this::VERSION,
			'l' => array_filter( // If no callback is supplied, all empty entries of array will be removed.
				array_map(
					function ( Data_Keyword $keyword = null ) {
						return ! is_null( $keyword ) ? $keyword->as_array() : null;
					},
					$this->list
				)
			),
		];
	}
	/**
	 * Remove empty and incorrect values
	 *
	 * @param array<Data_Keyword|null> $keywords_and_nulls Values to filter.
	 * @return Data_Keyword[] List of Data_Keyword instances only.
	 */
	private static function filter_empty_values( array $keywords_and_nulls ) {
		return array_filter(
			$keywords_and_nulls,
			function ( $item ) {
				return ! is_null( $item ) && $item instanceof Data_Keyword;
			}
		);
	}
	/**
	 * Get keywords
	 *
	 * @return Data_Keyword[] List of Data_Keyword.
	 */
	public function get_keywords() {
		return $this->list;
	}
	/**
	 * Set keywords from new list
	 *
	 * @param array<Data_Keyword|null>|null $keywords_list List of Data_Keyword.
	 * @return void
	 */
	public function set_keywords_info( array $keywords_list = null ) {
		$this->list = ! is_null( $keywords_list ) ? $this::filter_empty_values( $keywords_list ) : [];
	}
	/**
	 * Append keywords from new list to current list
	 *
	 * @param Data_Keywords $data_keywords Data_Keywords instance to append items from.
	 * @return void
	 */
	public function append_keywords( Data_Keywords $data_keywords ) {
		$additional_keywords = $data_keywords->get_keywords();
		if ( count( $additional_keywords ) ) {
			$this->list = $this->list + $this::filter_empty_values( $additional_keywords );
			$this->list = array_values( array_unique( $this->list ) );
		}
	}
}