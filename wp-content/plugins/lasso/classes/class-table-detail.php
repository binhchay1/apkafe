<?php
/**
 * Declare class Table_Detail
 *
 * @package Table_Detail
 */

namespace Lasso\Classes;

use Lasso\Libraries\Table\Table_Field_Group_Detail;

use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;
use Lasso\Classes\Helper as Lasso_Helper;

use Lasso\Models\Model;

/**
 * Table_Detail
 */
class Table_Detail {
	/**
	 * ID
	 *
	 * @var int $id ID.
	 */
	private $id;

	/**
	 * Title
	 *
	 * @var string $title Title
	 */
	private $title;

	/**
	 * Style Row or Column
	 *
	 * @var string $style Style
	 */
	private $style;

	/**
	 * Theme like Cactus, Money etc
	 *
	 * @var string $theme Theme.
	 */
	private $theme;

	/**
	 * Show title
	 *
	 * @var int $show_title Show title.
	 */
	private $show_title;

	const TABLE                       = 'lasso_table_details';
	const MODE_DESIGN                 = 'Design';
	const MODE_DISPLAY                = 'Display';
	const VERTICAL_DISPLAY_ITEM_LIMIT = 4;

	/**
	 * Table_Detail constructor.
	 *
	 * @param array $data An array of DB.
	 */
	public function __construct( $data = array() ) {
		if ( isset( $data['id'] ) ) {
			$this->set_id( $data['id'] );
		}

		if ( isset( $data['title'] ) ) {
			$this->set_title( $data['title'] );
		}

		if ( isset( $data['style'] ) ) {
			$this->set_style( $data['style'] );
		}

		if ( isset( $data['theme'] ) ) {
			$this->set_theme( $data['theme'] );
		}

		if ( isset( $data['show_title'] ) ) {
			$this->set_show_title( $data['show_title'] );
		}

		return $this;
	}

	/**
	 * Get ID
	 *
	 * @return mixed
	 */
	public function get_id() {
		return (int) $this->id;
	}

	/**
	 * Set ID
	 *
	 * @param mixed $id ID.
	 */
	private function set_id( $id ): void {
		$this->id = $id;
	}

	/**
	 * Get title
	 *
	 * @return mixed
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Set title
	 *
	 * @param mixed $title Title.
	 */
	public function set_title( $title ): void {
		$this->title = $title;
	}

	/**
	 * Get style
	 *
	 * @return mixed
	 */
	public function get_style() {
		return $this->style;
	}

	/**
	 * Get table style friendly name
	 *
	 * @return mixed
	 */
	public function get_style_friendly_name() {
		if ( Lasso_Helper::compare_string( $this->style, 'Row' ) ) {
			return 'Horizontal';
		} elseif ( Lasso_Helper::compare_string( $this->style, 'Column' ) ) {
			return 'Vertical';
		}
		return '';
	}

	/**
	 * Set style
	 *
	 * @param mixed $style Style.
	 */
	public function set_style( $style ): void {
		$this->style = $style;
	}

	/**
	 * Get theme
	 *
	 * @return mixed
	 */
	public function get_theme() {
		return $this->theme;
	}

	/**
	 * Set theme
	 *
	 * @param mixed $theme Theme.
	 */
	public function set_theme( $theme ): void {
		$this->theme = $theme;
	}

	/**
	 * Get show title
	 *
	 * @return mixed
	 */
	public function get_show_title() {
		return $this->show_title;
	}

	/**
	 * Set show title
	 *
	 * @param mixed $show_title show title.
	 */
	public function set_show_title( $show_title ): void {
		$this->show_title = $show_title;
	}

	/**
	 * Get table name
	 *
	 * @return string
	 */
	public static function get_table_name() {
		return Model::get_wp_table_name( self::TABLE );
	}

	/**
	 * Insert new record
	 *
	 * @return $this
	 */
	public function insert() {
		global $wpdb;

		$sql    = '
			INSERT INTO ' . self::get_table_name() . '
			(`title`, `style`, `theme`, `show_title`) VALUES 
			(%s, %s, %s, %s);
		';
		$sql    = Model::prepare( $sql, $this->get_title(), $this->get_style(), $this->get_theme(), $this->get_show_title() ); // phpcs:ignore
		$result = Model::query( $sql ); // phpcs:ignore
		if ( $result ) {
			$this->set_id( $wpdb->insert_id );
		}
		return $this;
	}

	/**
	 * Update a record
	 *
	 * @return $this
	 */
	public function update() {
		$sql = '
			UPDATE ' . self::get_table_name() . ' 
			SET 
				`title` = %s,
				`style` = %s,
				`theme` = %s,
				`show_title` = %s
			WHERE `id` = %d
		';
		$sql = Model::prepare( $sql, $this->get_title(), $this->get_style(), $this->get_theme(), $this->get_show_title(), $this->get_id() ); // phpcs:ignore
		Model::query( $sql ); // phpcs:ignore

		return $this;
	}

	/**
	 * Get by id
	 *
	 * @param int $id ID.
	 *
	 * @return Table_Detail|null
	 */
	public static function get_by_id( $id ) {
		$cache_key      = Lasso_Cache_Per_Process::determine_cache_key( array( self::get_table_name(), $id ) );
		$cache_instance = Lasso_Cache_Per_Process::get_instance();
		$cache_value    = $cache_instance->get_cache( $cache_key );
		if ( $cache_value ) {
			return $cache_value;
		}

		$sql    = '
			SELECT * FROM ' . self::get_table_name() . ' WHERE id = %d
		';
		$result = Model::get_row( Model::prepare( $sql, $id ), ARRAY_A ); // phpcs:ignore
		if ( ! empty( $result ) ) {
			$row = new self( $result );
			$cache_instance->set_cache( $cache_key, $row );
			return $row;
		}
		return null;
	}

	/**
	 * Get list table
	 *
	 * @param int  $page_number Page number.
	 * @param int  $limit       Limit.
	 * @param bool $fetch_all   Is fetch all.
	 *
	 * @return Table_Detail[]
	 */
	public static function get_list( $page_number = 1, $limit = 10, $fetch_all = false ) {
		if ( $fetch_all ) {
			$sql     = '
				SELECT * FROM ' . self::get_table_name() . '
			';
			$results = Model::get_results( $sql, ARRAY_A ); // phpcs:ignore
		} else {
			if ( 1 === $page_number ) {
				$page_number = 0;
			} else {
				$page_number = ( ( $page_number * $limit ) - $limit );
			}
			$sql     = '
				SELECT * FROM ' . self::get_table_name() . ' LIMIT %d, %d
			';
			$results = Model::get_results( Model::prepare( $sql, $page_number, $limit ), ARRAY_A ); // phpcs:ignore
		}
		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = new self( $result );
			}
		}
		return $list;
	}

	/**
	 * Check field is exist in the table
	 *
	 * @param int $field_id Field ID.
	 *
	 * @return bool
	 */
	public function is_exist_field_id( $field_id ) {
		$table_field_group_details = Table_Field_Group_Detail::get_list_by_table_id_field_id( $this->get_id(), $field_id );
		$is_exist                  = ! empty( $table_field_group_details ) ? true : false;

		return $is_exist;
	}

	/**
	 * Generate table title
	 *
	 * @param int $index Index.
	 *
	 * @return string
	 */
	public static function generate_table_title( &$index = 0 ) {
		$title = 'Untitled Table';
		$sql   = '
			SELECT * FROM ' . self::get_table_name() . ' WHERE title = %s
		';
		if ( ! empty( $index ) ) {
			$title = $title . ' ' . $index;
		}
		$sql     = Model::prepare( $sql, $title ); // phpcs:ignore
		$results = Model::get_results( $sql, ARRAY_A ); // phpcs:ignore
		if ( ! empty( $results ) ) {
			$index++;
			return self::generate_table_title( $index );
		}
		return $title;
	}

	/**
	 * Get link detail of table
	 *
	 * @return string
	 */
	public function get_link_detail() {
		return 'edit.php?post_type=lasso-urls&page=table-details&id=' . $this->get_id();
	}

}
