<?php
/**
 * Models
 *
 * @package Models
 */

namespace Lasso\Models;

use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;
use Lasso\Classes\Enum;
use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Model
 */
class Table_Field_Group_Detail extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_table_field_group_detail';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'id',
		'lasso_id',
		'field_id',
		'field_group_id',
		'order',

		'field_value',
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
			lasso_id bigint(10) NULL DEFAULT NULL,
			field_id bigint(10) NOT NULL,
			field_group_id varchar(30) NOT NULL,
			`order` int(2) NULL DEFAULT NULL,
			field_value text NULL DEFAULT NULL,
			PRIMARY KEY  (id),
			INDEX  idxLassoIDFieldGroupIDFieldID (field_id, field_group_id, lasso_id)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Set order.
	 *
	 * @param mixed $order Order.
	 */
	public function set_order( $order = null ) {
		if ( empty( $order ) ) {
			$order = self::get_max_order_by_field_group_id_lasso_id( $this->get_field_group_id(), $this->get_lasso_id() );
			$order ++;
		}
		$this->data['order'] = (int) $order;
		return $this;
	}

	/**
	 * Get list field group id.
	 *
	 * @param int  $field_group_id Field group id.
	 * @param bool $is_group       Is group.
	 * @return $this[]
	 */
	public static function get_list_by_field_group_id( $field_group_id, $is_group = true ) {
		$group = $is_group ? ' GROUP BY lasso_id ' : '';
		$sql   = '
			SELECT * 
			FROM ' . ( new self() )->get_table_name() . ' 
			WHERE field_group_id = %s 
			' . $group . ' 
			ORDER BY `order` ASC
		';

		$sql     = self::prepare( $sql, $field_group_id );
		$results = self::get_results( $sql );
		$list    = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$inst   = new self();
				$list[] = $inst->map_properties( $result );
			}
		}
		return $list;
	}

	/**
	 * Get list by field group id and field id.
	 *
	 * @param int $field_group_id Field group id.
	 * @param int $field_id       Field id.
	 *
	 * @return $this[]
	 */
	public static function get_list_by_field_group_id_field_id( $field_group_id, $field_id ) {
		$sql = '
			SELECT * 
			FROM ' . ( new self() )->get_table_name() . ' 
			WHERE field_group_id = %s 
				AND field_id = %d 
			ORDER BY `order` ASC
		';

		$sql     = self::prepare( $sql, $field_group_id, $field_id ); // phpcs:ignore
		$results = self::get_results( $sql ); // phpcs:ignore

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = ( new self() )->map_properties( $result );
			}
		}
		return $list;
	}

	/**
	 * Get a record
	 *
	 * @param int $field_id       Field ID.
	 * @param int $field_group_id Field group ID.
	 * @param int $lasso_id       Lasso ID.
	 *
	 * @return $this|null
	 */
	public static function get_by_field_id_field_group_id_lasso_id( $field_id, $field_group_id, $lasso_id ) {
		$sql    = '
			SELECT * 
			FROM ' . ( new self() )->get_table_name() . ' 
			WHERE lasso_id= %d 
				AND field_id = %d 
				AND field_group_id = %s
		';
		$sql    = self::prepare( $sql, $lasso_id, $field_id, $field_group_id ); // phpcs:ignore
		$result = self::get_row( $sql ); // phpcs:ignore

		if ( ! empty( $result ) ) {
			return ( new self() )->map_properties( $result );
		}
		return null;
	}

	/**
	 * Get list by table id and lasso id.
	 *
	 * @param int $table_id Table id.
	 * @param int $lasso_id Lasso id.
	 * @param int $field_id Field id.
	 *
	 * @return $this|null
	 */
	public static function get_by_table_id_lasso_id_field_id( $table_id, $lasso_id, $field_id ) {
		$table_name     = ( new self() )->get_table_name();
		$cache_key      = Lasso_Cache_Per_Process::determine_cache_key( array( $table_name, $table_id, $lasso_id, $field_id ) );
		$cache_instance = Lasso_Cache_Per_Process::get_instance();
		$cache_value    = $cache_instance->get_cache( $cache_key );
		if ( $cache_value ) {
			return $cache_value;
		}

		$sql = '
			SELECT b.* 
			FROM ' . ( new Table_Field_Group() )->get_table_name() . ' a 
			INNER JOIN ' . $table_name . ' b
				ON a.lasso_id = b.lasso_id
			WHERE a.table_id = %d 
				AND a.lasso_id = %d 
				AND b.field_id = %d
		';

		$sql    = self::prepare( $sql, $table_id, $lasso_id, $field_id ); // phpcs:ignore
		$result = self::get_row( $sql ); // phpcs:ignore

		if ( ! empty( $result ) ) {
			$inst = ( new self() )->map_properties( $result );
			$cache_instance->set_cache( $cache_key, $inst );
			return $inst;
		}
		return null;
	}

	/**
	 * Get order of field
	 *
	 * @param string $field_group_id Field group ID.
	 * @param int    $lasso_id       Lasso id.
	 * @return int
	 */
	public static function get_max_order_by_field_group_id_lasso_id( $field_group_id, $lasso_id ) {
		$sql    = '
			SELECT max(`order`) AS `order` 
			FROM ' . ( new self() )->get_table_name() . ' 
			WHERE field_group_id = %s 
				AND lasso_id = %d
		';
		$sql    = self::prepare( $sql, $field_group_id, $lasso_id ); // phpcs:ignore
		$result = self::get_row( $sql, ARRAY_A ); // phpcs:ignore

		if ( ! empty( $result['order'] ) ) {
			return $result['order'];
		}
		return 0;
	}

	/**
	 * Get list fields in the table
	 *
	 * @param int $table_id Table ID.
	 *
	 * @return $this|array
	 */
	public static function get_list_distinct_field_by_table_id( $table_id ) {
		$sql = '
			SELECT b.field_id 
			FROM ' . ( new Table_Field_Group() )->get_table_name() . ' a 
			INNER JOIN ' . ( new self() )->get_table_name() . ' b
				ON a.field_group_id = b.field_group_id
			WHERE table_id = %d 
			GROUP BY b.field_id
			ORDER BY b.field_group_id, b.`order` ASC
		';

		$sql     = self::prepare( $sql, $table_id ); // phpcs:ignore
		$results = self::get_results( $sql, ARRAY_A ); // phpcs:ignore

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = (int) $result['field_id'];
			}
		}
		return $list;
	}

	/**
	 * Get list by table id and lasso id.
	 *
	 * @param int $table_id Table id.
	 * @param int $lasso_id Lasso id.
	 *
	 * @return $this[]
	 */
	public static function get_list_by_table_id_lasso_id( $table_id, $lasso_id ) {
		$table_name     = ( new self() )->get_table_name();
		$cache_key      = Lasso_Cache_Per_Process::determine_cache_key( array( $table_name, $table_id, $lasso_id ) );
		$cache_instance = Lasso_Cache_Per_Process::get_instance();
		$cache_value    = $cache_instance->get_cache( $cache_key );
		if ( $cache_value ) {
			return $cache_value;
		}

		$sql = '
			SELECT b.* 
			FROM ' . ( new Table_Field_Group() )->get_table_name() . ' a 
			INNER JOIN ' . $table_name . ' b
				ON a.field_group_id = b.field_group_id
			WHERE a.table_id = %d 
				AND b.lasso_id = %d 
			GROUP BY field_id 
			ORDER BY a.order, b.order ASC
		';

		$sql     = self::prepare( $sql, $table_id, $lasso_id ); // phpcs:ignore
		$results = self::get_results( $sql ); // phpcs:ignore

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = ( new self() )->map_properties( $result );
			}
		}
		$cache_instance->set_cache( $cache_key, $list );
		return $list;
	}


	/**
	 * Get list field group id by table id.
	 *
	 * @param int $table_id Table id.
	 *
	 * @return array
	 */
	public static function get_list_field_group_id_by_table_id( $table_id ) {
		$sql = '
			SELECT b.field_group_id 
			FROM ' . ( new Table_Field_Group() )->get_table_name() . ' a 
			INNER JOIN ' . ( new self() )->get_table_name() . ' b
				ON a.lasso_id = b.lasso_id
			WHERE a.table_id = %d 
			GROUP BY b.field_group_id
			ORDER BY b.field_group_id, b.`order` ASC
		';

		$sql     = self::prepare( $sql, $table_id ); // phpcs:ignore
		$results = self::get_results( $sql, ARRAY_A ); // phpcs:ignore

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = $result['field_group_id'];
			}
		}
		return $list;
	}


	/**
	 * Get list by lasso id and field group id.
	 *
	 * @param int $lasso_id       Lasso id.
	 * @param int $field_group_id Field group id.
	 *
	 * @return $this[]
	 */
	public static function get_list_by_lasso_id_field_group_id( $lasso_id, $field_group_id ) {
		$table_name     = ( new self() )->get_table_name();
		$cache_key      = Lasso_Cache_Per_Process::determine_cache_key( array( $table_name, $lasso_id, $field_group_id ) );
		$cache_instance = Lasso_Cache_Per_Process::get_instance();
		$cache_value    = $cache_instance->get_cache( $cache_key );
		if ( $cache_value ) {
			return $cache_value;
		}

		$sql = '
			SELECT * 
			FROM ' . $table_name . ' 
			WHERE lasso_id = %d 
				AND field_group_id = %s 
			ORDER BY `order` ASC
		';

		$sql     = self::prepare( $sql, $lasso_id, $field_group_id ); // phpcs:ignore
		$results = self::get_results( $sql ); // phpcs:ignore

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = ( new self() )->map_properties( $result );
			}
		}
		if ( ! empty( $list ) ) {
			$cache_instance->set_cache( $cache_key, $list );
		}
		return $list;
	}

	/**
	 * Get list by field ids
	 *
	 * @param array $field_ids Field IDs in array.
	 * @return array
	 */
	public function get_list_by_field_ids( $field_ids ) {
		if ( empty( $field_ids ) ) {
			return array();
		}

		$field_ids = implode( ', ', $field_ids );

		$sql = '
			SELECT * 
			FROM ' . $this->get_table_name() . ' 
			WHERE field_id IN(' . $field_ids . ')
		';

		$results = self::get_results( $sql );

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = ( new Table_Field_Group_Detail() )->map_properties( $result );
			}
		}

		return $list;
	}

	/**
	 * Get optimize image.
	 *
	 * @return mixed
	 */
	public function get_image() {
		$image_path = $this->get_field_value();

		// ? Priority webp image
		if ( '.webp' === strtolower( substr( $image_path, -5 ) ) ) {
			if ( Lasso_Helper::is_uploaded_file_existing( $image_path ) ) {
				return $image_path;
			} else { // ? If webp image was removed, we use the Lasso Post's image.
				$custom_thumbnail = get_post_meta( $this->get_lasso_id(), 'lasso_custom_thumbnail', true );
				$webp_thumbnail   = get_post_meta( $this->get_lasso_id(), Enum::LASSO_WEBP_THUMBNAIL, true );
				if ( ! empty( $webp_thumbnail ) && Lasso_Helper::is_uploaded_file_existing( $webp_thumbnail ) ) {
					return $webp_thumbnail;
				} elseif ( ! empty( $custom_thumbnail ) ) {
					return $custom_thumbnail;
				} else {
					return LASSO_DEFAULT_THUMBNAIL;
				}
			}
		}

		// ? Get webp image from Lasso metadata table
		$sql    = '
			SELECT base.meta_value
			FROM ' . Model::get_wp_table_name( 'lasso_metadata' ) . ' AS base
			WHERE base.object_id = %d
				AND base.type = %s
				AND base.meta_key = %s';
		$sql    = Model::get_wpdb()->prepare( $sql, $this->get_id(), 'Table_Field_Group_Detail', Enum::LASSO_WEBP_THUMBNAIL ); // phpcs:ignore
		$result = Model::get_row( $sql );

		if ( ! empty( $result ) && Lasso_Helper::is_uploaded_file_existing( $result->meta_value ) ) {
			return $result->meta_value;
		}

		return $image_path;
	}
}
