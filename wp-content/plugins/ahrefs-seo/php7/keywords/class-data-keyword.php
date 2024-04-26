<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Keywords;

use ahrefs\AhrefsSeo\Ahrefs_Seo_Data_Content;
/**
 * Store details about single keyword in kw_imported (and kw_pos?) column.
 *
 * @since 0.8.8
 */
class Data_Keyword implements \JsonSerializable {
	/**
	 * @var string|null The keyword.
	 */
	protected $keyword = null;
	/**
	 * @var string|null The source ID.
	 */
	protected $source_id = null;
	/**
	 * @var string|null URL of the article.
	 */
	protected $url = null;
	/**
	 * @var string|null Error message.
	 */
	protected $error = null;
	/**
	 * @var Data_Clicks_Info|null Clicks info.
	 */
	protected $clicks_info = null;
	/**
	 * @var string Country code for these keywords.
	 */
	protected $country_code = '';

	/**
	 * Constructor
	 *
	 * @param string|null           $keyword Keyword.
	 * @param string|null           $source_id Source id or empty string (instead of null).
	 * @param Data_Clicks_Info|null $clicks_info Data clicks info or null.
	 * @param string|null           $url URL.
	 * @param string                $country_code Country code or empty string for All countries.
	 */
	public function __construct( ?string $keyword, ?string $source_id = null, ?Data_Clicks_Info $clicks_info = null, ?string $url = null, string $country_code = '' ) {
		$this->keyword      = $keyword;
		$this->source_id    = $source_id;
		$this->clicks_info  = $clicks_info;
		$this->url          = $url;
		$this->country_code = $country_code;
	}

	/**
	 * Return Data_Keyword instance using array with data.
	 *
	 * @param array $data Array with fields values, same format as as_array() used.
	 * @return self
	 */
	public static function from_array( array $data ) : self {
		$keyword      = isset( $data['k'] ) && ! is_null( $data['k'] ) ? (string) $data['k'] : null;
		$source_id    = isset( $data['s'] ) && ! is_null( $data['s'] ) ? (string) $data['s'] : null;
		$clicks_info  = isset( $data['c'] ) && is_array( $data['c'] ) ? Data_Clicks_Info::from_array( $data['c'] ) : null;
		$error        = isset( $data['e'] ) && ! is_null( $data['e'] ) ? (string) $data['e'] : null;
		$url          = isset( $data['u'] ) && ! is_null( $data['u'] ) ? (string) $data['u'] : null;
		$country_code = isset( $data['cc'] ) && is_string( $data['cc'] ) ? $data['cc'] : '';
		return ( new self( $keyword, $source_id, $clicks_info, $url, $country_code ) )->set_error( $error );
	}

	/**
	 * Return content as associative array.
	 * Skip source_id and clicks info if it is missing (is null).
	 *
	 * @return array<string,string|float|Data_Clicks_Info|null>
	 */
	public function as_array() : array {
		$result = [
			'k' => $this->keyword,
		];
		if ( ! is_null( $this->source_id ) ) {
			$result['s'] = $this->source_id;
		}
		if ( '' !== $this->country_code ) {
			$result['cc'] = $this->country_code;
		}
		if ( ! is_null( $this->url ) ) {
			$result['u'] = $this->url;
		}
		if ( ! is_null( $this->error ) ) {
			$result['e'] = $this->error;
		}
		if ( ! is_null( $this->clicks_info ) ) {
			$result['c'] = $this->clicks_info;
		}
		return $result;
	}

	/**
	 * Return result as it stored from GSC
	 *
	 * @return array{query:string, clicks:int, pos:float, impr:int}
	 * }
	 */
	public function as_gsc_array() : array {
		$clicks = $this->clicks_info ?? new Data_Clicks_Info( null, null, null );
		return [
			'query'  => $this->keyword ?? '',
			'clicks' => $clicks->get_clicks() ?? 0,
			'pos'    => $clicks->get_position() ?? Ahrefs_Seo_Data_Content::POSITION_MAX,
			'impr'   => $clicks->get_impressions() ?? 0,
		];
	}
	/**
	 * Get keyword
	 *
	 * @return string|null Keyword.
	 */
	public function get_keyword() : ?string {
		return $this->keyword;
	}

	/**
	 * Get source ID
	 *
	 * @return string|null
	 */
	public function get_source_id() : ?string {
		return $this->source_id;
	}

	/**
	 * Get clicks info
	 *
	 * @return Data_Clicks_Info|null
	 */
	public function get_clicks_info() : ?Data_Clicks_Info {
		return $this->clicks_info;
	}

	/**
	 * Get URL
	 *
	 * @return string|null
	 */
	public function get_url() : ?string {
		return $this->url;
	}

	/**
	 * Get Error message
	 *
	 * @return string|null
	 */
	public function get_error() : ?string {
		return $this->error;
	}

	/**
	 * Get county code
	 *
	 * @since 0.9.6
	 *
	 * @return string
	 */
	public function get_country_code() : string {
		return $this->country_code;
	}

	/**
	 * Set clicks info
	 *
	 * @param Data_Clicks_Info|null $clicks_info Clicks info.
	 * @return Data_Keyword
	 */
	public function set_clicks_info( ?Data_Clicks_Info $clicks_info ) : self {
		$this->clicks_info = $clicks_info;
		return $this;
	}

	/**
	 * Set URL
	 *
	 * @param string|null $url URL.
	 * @return Data_Keyword
	 */
	public function set_url( ?string $url ) : self {
		$this->url = $url;
		return $this;
	}

	/**
	 * Set Error message
	 *
	 * @param string|null $error Error message.
	 * @return Data_Keyword
	 */
	public function set_error( ?string $error ) : self {
		$this->error = $error;
		return $this;
	}

	/**
	 * Return as string
	 *
	 * @return string JSON encoded array of properties.
	 */
	public function __toString() : string {
		return (string) wp_json_encode( $this->as_array() );
	}

	/**
	 * Specify data which should be serialized to JSON
	 *
	 * @since 0.9.6
	 *
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->as_array();
	}

	/**
	 * Is same keyword?
	 *
	 * @param string $keyword Keyword to compare.
	 * @return bool
	 */
	public function is_same_keyword( string $keyword ) : bool {
		if ( is_null( $this->keyword ) ) {
			return false;
		}
		if ( function_exists( 'mb_strtolower' ) ) {
			return trim( mb_strtolower( $keyword ) ) === trim( mb_strtolower( $this->keyword ) );
		} else {
			return 0 === strcasecmp( $keyword, $this->keyword );
		}
	}
}
