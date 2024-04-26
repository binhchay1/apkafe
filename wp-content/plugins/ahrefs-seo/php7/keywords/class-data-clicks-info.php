<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Keywords;

/**
 * Store details about clicks, position, impressions.
 *
 * @since 0.8.8
 */
class Data_Clicks_Info {
	/**
	 * @var int|null Number of clicks.
	 */
	public $clicks = null;
	/**
	 * @var float|null Position in GSC.
	 */
	public $position = null;
	/**
	 * @var int|null Impressions.
	 */
	public $impressions = null;

	/**
	 * Constructor
	 *
	 * @param int|null   $clicks Number of clicks from GSC.
	 * @param float|null $position Average position from GSC.
	 * @param int|null   $impressions Impressions from GSC.
	 */
	public function __construct( ?int $clicks, ?float $position, ?int $impressions ) {
		$this->clicks      = $clicks;
		$this->position    = $position;
		$this->impressions = $impressions;
	}

	/**
	 * Return Data_Clicks_Info instance using array with data.
	 *
	 * @param array|null $data Data from DB, same format as as_array() used.
	 * @return self
	 */
	public static function from_array( ?array $data ) : self {
		$clicks      = null;
		$position    = null;
		$impressions = null;
		if ( is_array( $data ) && isset( $data['k'] ) && isset( $data['p'] ) && isset( $data['i'] ) ) {
			$clicks      = (int) $data['k'];
			$position    = (float) $data['p'];
			$impressions = (int) $data['i'];
		} elseif ( is_array( $data ) && isset( $data[0] ) && isset( $data[1] ) && isset( $data[2] ) ) {
			$clicks      = (int) $data[0];
			$position    = (float) $data[1];
			$impressions = (int) $data[2];
		}
		return new self( $clicks, $position, $impressions );
	}

	/**
	 * Return content as associative array.
	 *
	 * @see from_array() method.
	 *
	 * @return array<string,int|float|null>
	 */
	public function as_array() : array {
		return [
			'k' => $this->clicks,
			'p' => $this->position,
			'i' => $this->impressions,
		];
	}

	/**
	 * Get Clicks value
	 *
	 * @return int|null
	 */
	public function get_clicks() : ?int {
		return $this->clicks;
	}

	/**
	 * Get Position value
	 *
	 * @return float|null
	 */
	public function get_position() : ?float {
		return $this->position;
	}

	/**
	 * Get Impressions value
	 *
	 * @return int|null
	 */
	public function get_impressions() : ?int {
		return $this->impressions;
	}


}
