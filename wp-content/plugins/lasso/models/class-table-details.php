<?php
/**
 * Models
 *
 * @package Models
 */

namespace Lasso\Models;

use Lasso\Classes\Link_Location;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting_Enum as Lasso_Setting_Enum;

use Lasso\Models\MetaData;

/**
 * Model
 */
class Table_Details extends Model {

	const ENABLE_SHOW_HEADERS_HORIZONTAL = 1;

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_table_details';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'id',
		'title',
		'style',
		'theme',
		'show_title',
		'show_headers_horizontal',
	);

	/**
	 * Primary key of the table
	 *
	 * @var string
	 */
	protected $primary_key = 'id';

	/**
	 * Create table
	 */
	public function create_table() {
		$columns_sql = '
			id bigint(10) NOT NULL AUTO_INCREMENT,
			title varchar(255) NULL DEFAULT NULL,
			style varchar(32) NULL DEFAULT NULL,
			theme varchar(32) NULL DEFAULT NULL,
			show_title tinyint(1) NULL DEFAULT NULL,
			show_headers_horizontal tinyint(1) NULL DEFAULT 1,
			PRIMARY KEY  (id)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Get tables query
	 *
	 * @param string $search Search text.
	 * @param string $where  Where statement. Default to '1=1'.
	 */
	public function get_tables_query( $search, $where = '1=1' ) {
		$sql = '
			SELECT
				id, title
			FROM
				' . $this->get_table_name() . '
			WHERE
				' . $where . '
				' . $search;

		return $sql;
	}

	/**
	 * Get object by ID
	 *
	 * @param integer $id ID.
	 *
	 * @return $this
	 */
	public static function get_by_id( $id ) {
		$inst = new self();
		return $inst->get_one( $id );
	}

	/**
	 * Insert
	 */
	public function insert() {
		unset( $this->data['id'] );
		parent::insert();
	}

	/**
	 * Clone the table
	 */
	public function clone_table() {
		$table_id = $this->get_id();

		$field_groups_mapping = array();
		$field_groups         = Table_Field_Group::get_list_field_group_id_by_table_id( $table_id );
		foreach ( $field_groups as $field_group ) {
			$field_groups_mapping[ $field_group ] = ( new Table_Field_Group() )->generate_field_group_id();
		}

		$table_clone = clone $this;
		$title       = $table_clone->get_title() . ' - Copy';
		$table_clone->set_title( $title );
		$table_clone->insert();
		$table_clone_id = $table_clone->get_id();
		if ( $table_clone_id ) {
			// Clone.
			$table_mappings = Table_Mapping::get_list_by_table_id( $table_id );
			foreach ( $table_mappings as $table_mapping ) {
				$table_mapping_clone = clone $table_mapping;
				$table_mapping_clone->set_id( null );
				$table_mapping_clone->set_table_id( $table_clone_id );
				$table_mapping_clone->insert();
			}

			// Clone.
			$table_field_groups = Table_Field_Group::get_list_by_table_id( $table_id );
			foreach ( $table_field_groups as $table_field_group ) {
				$table_field_group_clone = clone $table_field_group;
				$table_field_group_clone->set_id( null );
				$table_field_group_clone->set_table_id( $table_clone_id );

				$current_field_group_id = $table_field_group->get_field_group_id();
				$field_group_clone      = $field_groups_mapping[ $current_field_group_id ];
				$table_field_group_clone->set_field_group_id( $field_group_clone );
				$table_field_group_clone->insert();
			}

			// Clone.
			foreach ( $field_groups_mapping as $field_group => $field_group_clone ) {
				$fields = Table_Field_Group_Detail::get_list_by_field_group_id( $field_group, false );
				foreach ( $fields as $field ) {
					$field_clone = clone $field;
					$field_clone->set_id( null );
					$field_clone->set_field_id( $field->get_field_id() );
					$field_clone->set_field_group_id( $field_group_clone );
					$field_clone->insert();
				}
			}
		}

	}

	/**
	 * Get link detail of table
	 *
	 * @return string
	 */
	public function get_link_detail() {
		return 'edit.php?post_type=lasso-urls&page=table-details&id=' . $this->get_id();
	}

	/**
	 * Init instance
	 *
	 * @param object $object Object.
	 *
	 * @return Table_Details|void
	 */
	public static function get_inst( $object ) {
		return ( new self() )->map_properties( $object );
	}

	/**
	 * Search List
	 *
	 * @param int    $page_number Page number.
	 * @param int    $limit       Limit.
	 * @param string $search_term Search Team.
	 * @param string $where       Where condition.
	 * @return array
	 */
	public function get_search_list( &$page_number = 1, $limit = 10, $search_term = '', $where = '1=1' ) {
		$sql     = '
				SELECT * 
				FROM  ' . self::get_table_name() . ' 
				WHERE ' . $where . ' ' . $search_term;
		$sql     = Lasso_Helper::paginate( $sql, $page_number, $limit );
		$results = $this->get_results( $sql );
		$list    = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = new self( $result );
			}
		}
		return $list;
	}


	/**
	 * Count total of table shortcode are using
	 *
	 * @return int
	 */
	public function get_total_locations() {
		$sql = '
			SELECT count(*) as total FROM ' . self::get_prefix() . 'posts  p 
				INNER JOIN ' . ( new Link_Locations() )->get_table_name() . ' ll 
					ON p.ID = ll.detection_id
				INNER JOIN ' . ( new MetaData() )->get_table_name() . ' mt
					ON mt.object_id = ll.id
				WHERE mt.type = %s AND mt.meta_key = %s AND mt.meta_value = %d';

		$sql    = self::prepare( $sql, 'Link_Locations', Link_Location::DISPLAY_TYPE_TABLE, $this->get_id() );
		$result = self::get_row( $sql );
		$total  = 0;
		if ( ! is_null( $result ) ) {
			$total = $result->total;
		}
		return (int) $total;
	}

	/**
	 * Get list table locations query
	 *
	 * @param array $select Select columns in array.
	 *
	 * @return string
	 */
	public function get_locations_query( $select = array() ) {
		if ( empty( $select ) ) {
			$select_query = 'p.*';
		} else {
			$select_query = implode( ',', $select );
		}

		$sql = '
			SELECT ' . $select_query . ' 
			FROM ' . self::get_wp_table_name( 'posts' ) . ' p 
				INNER JOIN ' . ( new Link_Locations() )->get_table_name() . ' ll 
					ON p.ID = ll.detection_id
				INNER JOIN ' . ( new MetaData() )->get_table_name() . ' mt
					ON mt.object_id = ll.id
				WHERE mt.type = %s AND mt.meta_key = %s AND mt.meta_value = %d';

		$sql = self::prepare( $sql, Lasso_Setting_Enum::META_LINK_LOCATION_NAME, Link_Location::DISPLAY_TYPE_TABLE, $this->get_id() );
		return $sql;
	}

	/**
	 * Get list table
	 *
	 * @param int  $page_number Page number.
	 * @param int  $limit       Limit.
	 * @param bool $fetch_all   Is fetch all.
	 *
	 * @return $this[]
	 */
	public static function get_list( $page_number = 1, $limit = 10, $fetch_all = false ) {
		$table_name = ( new self() )->get_table_name();
		if ( $fetch_all ) {
			$sql     = '
				SELECT * FROM ' . $table_name . '
			';
			$results = self::get_results( $sql ); // phpcs:ignore
		} else {
			if ( 1 === $page_number ) {
				$page_number = 0;
			} else {
				$page_number = ( ( $page_number * $limit ) - $limit );
			}
			$sql     = '
				SELECT * FROM ' . $table_name . ' LIMIT %d, %d
			';
			$results = self::get_results( self::prepare( $sql, $page_number, $limit ) ); // phpcs:ignore
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
	 * Get table style friendly name
	 *
	 * @return mixed
	 */
	public function get_style_friendly_name() {
		if ( Lasso_Helper::compare_string( $this->get_style(), 'Row' ) ) {
			return 'Horizontal';
		} elseif ( Lasso_Helper::compare_string( $this->get_style(), 'Column' ) ) {
			return 'Vertical';
		}
		return '';
	}

	/**
	 * Force property to boolean value.
	 */
	public function get_show_headers_horizontal() {
		return boolval( $this->data['show_headers_horizontal'] ?? '' );
	}

	/**
	 * Check should render field name or not in Comparison Table
	 *
	 * @return bool
	 */
	public function is_show_field_name_comparison_table() {
		$is_show       = false;
		$is_horizontal = Lasso_Setting_Enum::TABLE_STYLE_ROW === $this->get_style();

		if ( ( $is_horizontal && $this->get_show_headers_horizontal() ) || $this->get_show_field_name() ) {
			$is_show = true;
		}

		return $is_show;
	}

	/**
	 * Check a field exist in table
	 *
	 * @param integer $field_id Field ID.
	 *
	 * @return bool
	 */
	public function has_field( $field_id ) {
		$sql   = '
			SELECT t1.field_group_id 
			FROM ' . ( new Table_Field_Group() )->get_table_name() . ' t1 
				INNER JOIN ' . ( new Table_Field_Group_Detail() )->get_table_name() . ' t2
					ON t1.field_group_id = t2.field_group_id 
			WHERE t1.table_id = %d AND t2.field_id = %d 
				GROUP BY t1.field_group_id
		';
		$sql   = self::prepare( $sql, $this->get_id(), $field_id );
		$count = self::get_count( $sql );

		return $count > 0;
	}

	/**
	 * Get show field name value
	 *
	 * @return bool
	 */
	public function get_show_field_name() {
		$default_show_field_name = true;
		$meta_value              = MetaData::get_metadata( 'Table_Details', $this->get_id(), 'show_field_name', $default_show_field_name );
		return boolval( intval( $meta_value ) );
	}

	/**
	 * Set show field name value
	 *
	 * @param string $value Value.
	 *
	 * @return string
	 */
	public function set_show_field_name( $value ) {
		return MetaData::update_metadata( 'Table_Details', $this->get_id(), 'show_field_name', $value );
	}
}
