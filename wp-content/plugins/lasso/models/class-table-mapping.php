<?php
/**
 * Models
 *
 * @package Models
 */

namespace Lasso\Models;

use Lasso\Libraries\Lasso_URL;
use Lasso\Libraries\Table\Table_Field_Group_Detail;
use Lasso\Models\Table_Field_Group as Model_Table_Field_Group;
use Lasso\Models\Table_Field_Group_Detail as Model_Table_Field_Group_Detail;
use Lasso_Affiliate_Link;

/**
 * Model
 */
class Table_Mapping extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_table_mapping';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'id',
		'title',
		'table_id',
		'lasso_id',

		'order',
		'title_visible',
		'badge_text',
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
			table_id bigint(10) NOT NULL,
			lasso_id bigint(10) NULL DEFAULT NULL,
			`order` int(2) NULL DEFAULT NULL,
			title_visible tinyint(1) NULL DEFAULT 1,
			badge_text varchar(255) NULL DEFAULT NULL,
			PRIMARY KEY  (id),
			INDEX  idxTableIDLassoID (table_id, lasso_id)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Set order
	 *
	 * @param int $order Order.
	 */
	public function set_order( $order = null ) {
		if ( ! isset( $order ) ) {
			$order = self::get_max_order_by_table_id( $this->get_table_id() );
			$order ++;
		}
		$this->data['order'] = (int) $order;
		return $this;
	}

	/**
	 * Get order of field
	 *
	 * @param int $table_id Table ID.
	 *
	 * @return int|mixed
	 */
	public static function get_max_order_by_table_id( $table_id ) {
		$sql    = '
			SELECT MAX(`order`) AS `order` 
			FROM ' . ( new self() )->get_table_name() . ' 
			WHERE table_id = %d
		';
		$sql    = self::prepare( $sql, $table_id );
		$result = self::get_row( $sql, ARRAY_A );

		if ( ! empty( $result ) ) {
			return $result['order'];
		}
		return 0;
	}

	/**
	 * Get list object by table ID
	 *
	 * @param integer $table_id Table ID.
	 * @param int     $limit    Limit records. Default to false - No limit.
	 * @return $this[]
	 */
	public static function get_list_by_table_id( $table_id, $limit = false ) {
		$limit   = $limit ? 'LIMIT ' . intval( $limit ) : '';
		$sql     = '
			SELECT * 
			FROM ' . ( new self() )->get_table_name() . ' 
			WHERE table_id = %d 
			ORDER BY `order` ASC ' .
			$limit . '
		';
		$sql     = self::prepare( $sql, $table_id );
		$results = self::get_results( $sql );
		$list    = array();
		foreach ( $results as $row ) {
			$inst   = new self();
			$list[] = $inst->map_properties( $row );
		}
		return $list;
	}

	/**
	 * Get table product number.
	 *
	 * @param integer $table_id Table ID.
	 * @return int
	 */
	public static function get_count_by_table_id( $table_id ) {
		if ( ! $table_id ) {
			return 0;
		}

		$sql    = '
			SELECT COUNT(*) 
			FROM ' . ( new self() )->get_table_name() . ' 
			WHERE table_id = %d
		';
		$sql    = self::prepare( $sql, $table_id );
		$result = self::get_var( $sql );

		return intval( $result );
	}

	/**
	 * Add a product to table and also populate fields to table mapping.
	 *
	 * @param int   $table_id Table ID.
	 * @param int   $lasso_id Lasso ID.
	 * @param array $default_fields Default fields.
	 * @return Table_Mapping
	 */
	public static function add_product( $table_id, $lasso_id, $default_fields = array() ) {
		$table_mapping_products = self::get_list_by_table_id( $table_id );
		$lasso_url              = Lasso_URL::get_by_lasso_id( $lasso_id );
		if ( ! empty( $table_mapping_products ) ) {
			// ? populate fields from exist Product to new Product
			$table_mapping_product = array_shift( $table_mapping_products );

			$table_mapping_new_product = new Table_Mapping();
			$table_mapping_new_product->set_table_id( $table_id );
			$table_mapping_new_product->set_lasso_id( $lasso_id );
			$table_mapping_new_product->set_badge_text( $lasso_url->badge_text );
			$table_mapping_new_product->set_order();
			$table_mapping_new_product->insert();
			$list_field_exist = Table_Field_Group_Detail::get_list_by_table_id_lasso_id( $table_mapping_product->get_table_id(), $table_mapping_product->get_lasso_id() );
			foreach ( $list_field_exist as $item ) {
				// ? Because field group detail is existed, so we use the current field group order
				$field_group_order     = Model_Table_Field_Group::get_field_group_order( $table_id, $item->get_field_group_id() );
				$field_group_id        = $table_mapping_new_product->add_field( $item->get_field_id(), $item->get_field_group_id(), $field_group_order, $table_mapping_new_product->get_lasso_id() );
				$tb_field_group_detail = Model_Table_Field_Group_Detail::get_by_field_id_field_group_id_lasso_id( Fields::PRODUCT_NAME_FIELD_ID, $field_group_id, $lasso_id );
				if ( $tb_field_group_detail ) {
					$tb_field_group_detail->set_field_value( $lasso_url->name );
					$tb_field_group_detail->update();
				}
			}
		} else {
			$table_mapping_new_product = new Table_Mapping();
			$table_mapping_new_product->set_table_id( $table_id );
			$table_mapping_new_product->set_lasso_id( $lasso_id );
			$table_mapping_new_product->set_badge_text( $lasso_url->badge_text );
			$table_mapping_new_product->set_order();
			$table_mapping_new_product->insert();

			if ( ! empty( $default_fields ) ) {
				foreach ( $default_fields as $default_field ) {
					$table_mapping_new_product->add_field( $default_field, null );
				}
			} else {
				/*
				 * Adding the first product => Add some initial Fields:
				 * The first row/column:  Badget and Image
				 * The Second row/column: Product Name, Primary rating and Price
				 * The Third row/column:  Primary button
				 */
				// ? The first row/column: Badget and Image
				$table_mapping_new_product->add_field( Fields::IMAGE_FIELD_ID, null );

				// ? The Second row/column: Product Name, Primary rating and Price
				$the_second_field_group_id = $table_mapping_new_product->add_field( Fields::PRODUCT_NAME_FIELD_ID, null );
				$table_mapping_new_product->add_field( Fields::RATING_FIELD_ID, $the_second_field_group_id );
				$table_mapping_new_product->add_field( Fields::PRICE_ID, $the_second_field_group_id );

				// ? The Third row/column:  Primary button
				$table_mapping_new_product->add_field( Fields::PRIMARY_BTN_ID, null );
			}
		}
		return $table_mapping_new_product;
	}

	/**
	 * Add field to table
	 *
	 * @param int      $field_id       Field ID.
	 * @param null|int $field_group_id Field group id.
	 * @param null|int $order          Order.
	 * @param null|int $pre_lasso_id   Previous lasso id.
	 */
	public function add_field( $field_id, $field_group_id = null, $order = null, $pre_lasso_id = null ) {
		$table_field_group = Table_Field_Group::get_by_table_id_lasso_id_field_group_id( $this->get_table_id(), $this->get_lasso_id(), $field_group_id );
		if ( empty( $table_field_group ) ) {
			$table_field_group = new Table_Field_Group();
			$table_field_group->set_table_id( $this->get_table_id() );
			$table_field_group->set_lasso_id( $this->get_lasso_id() );
			$table_field_group->set_field_id( $field_id );
			$table_field_group->set_order( $order );
			$field_group_id = $field_group_id ?? Table_Field_Group::generate_field_group_id();
			$table_field_group->set_field_group_id( $field_group_id );
			$table_field_group->insert();
			if ( ! empty( $table_field_group->get_id() ) ) {
				$table_field_group_details = Table_Field_Group_Detail::get_list_by_lasso_id_field_group_id( $pre_lasso_id, $field_group_id );
				if ( empty( $table_field_group_details ) ) {
					self::link_field_group_detail( $field_group_id, $field_id, $this->get_lasso_id() );
				} else {
					foreach ( $table_field_group_details as $item ) {
						self::link_field_group_detail( $item->get_field_group_id(), $item->get_field_id(), $this->get_lasso_id(), $item->get_order() );
					}
				}
			}
		} else {
			if ( 0 === $table_field_group->get_field_id() ) {
				$table_field_group->set_field_id( $field_id );
				$table_field_group->update();
			}
			self::link_field_group_detail( $field_group_id, $field_id, $this->get_lasso_id() );
		}

		return $table_field_group->get_field_group_id();

	}

	/**
	 * Add field to cell
	 *
	 * @param int  $field_group_id Field group ID.
	 * @param int  $field_id       Field ID.
	 * @param int  $lasso_id       Lasso ID.
	 * @param null $order          Order.
	 */
	public static function link_field_group_detail( $field_group_id, $field_id, $lasso_id, $order = null ) {
		$table_field_group_details = Table_Field_Group_Detail::get_by_field_id_field_group_id_lasso_id( $field_id, $field_group_id, $lasso_id );
		$field_value               = '';
		$field_mapping             = Field_Mapping::get_by_lasso_id_field_id( $lasso_id, $field_id );
		if ( isset( $field_mapping ) ) {
			$field_value = $field_mapping->get_field_value();
		} elseif ( Fields::DESCRIPTION_FIELD_ID === intval( $field_id ) ) {
			$lasso_url   = Lasso_URL::get_by_lasso_id( $lasso_id ); // ? Set Lasso description as default when adding the Description field
			$field_value = $lasso_url->get_description();
		} elseif ( Fields::PRODUCT_NAME_FIELD_ID === intval( $field_id ) ) { // ? Set Lasso title as default when adding the Product Name field
			$field_value = get_the_title( $lasso_id );
		} elseif ( Fields::IMAGE_FIELD_ID === intval( $field_id ) ) {
			$lasso_url      = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );
			$webp_thumbnail = get_post_meta( $lasso_id, 'lasso_webp_thumbnail', true );
			$field_value    = $webp_thumbnail ? $webp_thumbnail : $lasso_url->image_src;
		} elseif ( Fields::PRICE_ID === intval( $field_id ) ) {
			$lasso_url   = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );
			$field_value = $lasso_url->price;
		} elseif ( in_array( intval( $field_id ), array( Fields::PRIMARY_BTN_ID, Fields::SECONDARY_BTN_ID ), true ) ) {
			$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );

			if ( Fields::PRIMARY_BTN_ID === intval( $field_id ) ) {
				$field_value = wp_json_encode(
					array(
						'button_text' => $lasso_url->display->primary_button_text,
						'url'         => $lasso_url->public_link,
					)
				);
			} else {
				$field_value = wp_json_encode(
					array(
						'button_text' => $lasso_url->display->secondary_button_text,
						'url'         => $lasso_url->display->secondary_url,
					)
				);
			}
		}

		if ( empty( $table_field_group_details ) ) {
			$table_field_group_detail = new Table_Field_Group_Detail();
			$table_field_group_detail->set_field_group_id( $field_group_id );
			$table_field_group_detail->set_lasso_id( $lasso_id );
			$table_field_group_detail->set_field_id( $field_id );
			$table_field_group_detail->set_order( $order );
			$table_field_group_detail->set_field_value( $field_value );
			$table_field_group_detail->insert();
		}
	}


	/**
	 * Get list field by table id and lasso id
	 *
	 * @param int $table_id Table ID.
	 * @param int $lasso_id lasso ID.
	 *
	 * @return Table_Mapping|null
	 */
	public static function get_by_table_id_lasso_id( $table_id, $lasso_id ) {
		$sql = '
			SELECT * FROM ' . ( new self() )->get_table_name() . ' 
				WHERE table_id = %d AND lasso_id = %d';

		$sql    = self::get_wpdb()->prepare( $sql, $table_id, $lasso_id ); // phpcs:ignore
		$result = self::get_row( $sql ); // phpcs:ignore

		if ( ! empty( $result ) ) {
			return ( new self() )->map_properties( $result );
		}
		return null;
	}

	/**
	 * Check a table mapping has at least a badge text
	 *
	 * @param Table_Mapping[] $table_products A list table products.
	 *
	 * @return bool
	 */
	public static function has_badge( $table_products ) {
		foreach ( $table_products as $table_product ) {
			if ( '' !== trim( $table_product->get_badge_text() ) ) {
				return true;
			}
		}
		return false;
	}

}
