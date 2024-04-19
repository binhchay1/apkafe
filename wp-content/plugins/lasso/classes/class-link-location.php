<?php
/**
 * Declare class Link_Location
 *
 * @package Link_Location
 */

namespace Lasso\Classes;

use Lasso_DB;
use Lasso_Amazon_Api;

use ReflectionClass;
use ReflectionProperty;

use Lasso\Classes\Helper;
use Lasso\Models\Model;

/**
 * Link_Location
 *
 * @method get_link_type()
 * @method get_post_id()
 */
class Link_Location {
	const LINK_TYPE_LASSO    = 'Lasso';
	const LINK_TYPE_INTERNAL = 'Internal';
	const LINK_TYPE_EXTERNAL = 'External';

	// ? lasso shortcodes
	const DISPLAY_TYPE_SINGLE  = 'Single';
	const DISPLAY_TYPE_BUTTON  = 'Button';
	const DISPLAY_TYPE_IMAGE   = 'Image';
	const DISPLAY_TYPE_GRID    = 'Grid';
	const DISPLAY_TYPE_LIST    = 'List';
	const DISPLAY_TYPE_GALLERY = 'Gallery';

	// ? <a> tags
	const DISPLAY_TYPE_TEXT       = 'Text';
	const DISPLAY_TYPE_IMAGE_ONLY = 'Image-only anchor text';
	const DISPLAY_TYPE_FIELD      = 'Field';
	const DISPLAY_TYPE_TABLE      = 'Table';

	// ? other plugin shortcodes
	const DISPLAY_TYPE_AAWP        = 'AAWP';
	const DISPLAY_TYPE_AMALINK     = 'AMALink';
	const DISPLAY_TYPE_EARNIST     = 'Earnist';
	const DISPLAY_TYPE_EASYAZON    = 'EasyAzon';
	const DISPLAY_TYPE_THIRSTYLINK = 'ThirstyLink';
	const DISPLAY_TYPE_SITE_STRIPE = 'SiteStripe';

	/**
	 * Data from DB
	 *
	 * @var array $data
	 */
	private $data = array();

	/**
	 * Lasso_Link_Location constructor.
	 *
	 * @param int     $id           Id from DB.
	 * @param boolean $is_use_cache Is use cache.
	 */
	public function __construct( $id = 0, $is_use_cache = false ) {
		global $wpdb;
		$lasso_db = new Lasso_DB();

		$this->wpdb     = $wpdb;
		$this->lasso_db = $lasso_db;

		$sql = '
			SELECT *
			FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . ' WHERE id = %d
		';

		// ? Enable cache on frontend to prevent duplicate queries issue
		if ( ! is_admin() ) {
			$is_use_cache = true;
		}

		$row = Model::get_row( Model::prepare( $sql, $id ), ARRAY_A, $is_use_cache ); // phpcs:ignore

		if ( ! empty( $row ) ) {
			$this->data = $row;
		}

		return $this;
	}

	/**
	 * Get property name by method
	 *
	 * @param string $method Method.
	 */
	private function get_property_name( $method ) {
		return substr_replace( $method, '', 0, 4 );
	}

	/**
	 * Call a method in this class
	 *
	 * @param string $method Method name.
	 * @param array  $args   Arguments.
	 */
	public function __call( $method, $args ) {
		$prefix = substr_replace( $method, '', 4 );

		switch ( $prefix ) {
			case 'get_':
				return $this->$method;

			case 'set_':
				$this->$method = $args[0] ?? null;
				break;
		}

		return null;
	}

	/**
	 * Set value for a property
	 *
	 * @param string $name  Function name (set_property_name).
	 * @param mix    $value Property value.
	 */
	public function __set( $name, $value ) {
		$method   = strtolower( $name );
		$property = $this->get_property_name( $method );

		// ? see if there exists a extra setter method: setName()
		if ( ! method_exists( $this, $method ) ) {
			// ? if there is no setter, receive all public/protected vars and set the correct one if found
			$this->data[ $property ] = $value;
		} else {
			$this->$method( $value ); // ? call the setter with the value
		}
	}

	/**
	 * Get value for a property
	 *
	 * @param string $name Function name (get_property_name).
	 */
	public function __get( $name ) {
		$method   = strtolower( $name );
		$property = $this->get_property_name( $method );

		// ? see if there is an extra getter method: get_name()
		if ( ! method_exists( $this, $method ) ) {
			// ? if there is no getter, receive all public/protected vars and return the correct one if found
			$data = $this->data;
			return $data[ $property ] ?? null;
		} else {
			return $this->$method(); // ? call the getter
		}

		return null;
	}

	/**
	 * Get all data of this class
	 *
	 * @param int $post_id Post id.
	 */
	public static function get_data_by_post( $post_id ) {
		if ( ! $post_id ) {
			return false;
		}

		$sql     = '
			SELECT *
			FROM ' . self::get_table_name() . '
			WHERE detection_id = %d
		';
		$prepare = Model::prepare( $sql, $post_id ); // phpcs:ignore

		return Model::get_results( $prepare );
	}

	/**
	 * Get all data of this class
	 */
	public function get_data() {
		if ( ! $this->data ) {
			$reflect    = new ReflectionClass( $this );
			$this->data = array();
			foreach ( $reflect->getProperties( ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED ) as $var ) {
				$this->data[] = $var->name;
			}
		}

		return $this->data;
	}

	/**
	 * Is dismiss or not
	 *
	 * @return bool
	 */
	public function get_is_dismiss() {
		return 1 === intval( $this->get_is_ignored() );
	}

	/**
	 * Get table name
	 *
	 * @return string
	 */
	private static function get_table_name() {
		return Model::get_wp_table_name( LASSO_LINK_LOCATION_DB );
	}

	/**
	 * Check whether a display type is a lasso shortcode
	 *
	 * @param string $display_type Display type in the link location table.
	 *
	 * @return bool
	 */
	public static function is_lasso_shortcode( $display_type ) {
		if ( ! $display_type ) {
			return false;
		}

		$shortcode_display = array(
			self::DISPLAY_TYPE_SINGLE,
			self::DISPLAY_TYPE_BUTTON,
			self::DISPLAY_TYPE_IMAGE,
			self::DISPLAY_TYPE_GRID,
			self::DISPLAY_TYPE_LIST,
			self::DISPLAY_TYPE_GALLERY,
		);

		return in_array( $display_type, $shortcode_display, true );
	}

	/**
	 * Update a record
	 *
	 * @return $this
	 */
	public function update() {
		$sql = '
			UPDATE ' . self::get_table_name() . ' SET 
				`is_ignored`     = %d,
				`detection_date` = %s
			WHERE `id` = %d;
		';
		$sql = Model::prepare( $sql, $this->get_is_ignored(), $this->get_detection_date(), $this->get_id() ); // phpcs:ignore
		Model::query( $sql ); // phpcs:ignore

		return $this;
	}

	/**
	 * Check whether is Site Stripe URL
	 *
	 * @param string $url URL.
	 *
	 * @return bool
	 */
	public static function is_site_stripe_url( $url ) {
		if ( ! $url || ! is_string( $url ) || ! Helper::validate_url( $url ) || ! Lasso_Amazon_Api::is_amazon_url( $url ) ) {
			return false;
		}

		return strpos( $url, 'amzn1.sym.' ) !== false && strpos( $url, 'amzn1.symc.' ) !== false;
	}
}
