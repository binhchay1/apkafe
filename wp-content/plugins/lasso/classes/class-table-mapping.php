<?php
/**
 * Declare class Table_Mapping
 *
 * @package Table_Mapping
 */

namespace Lasso\Classes;

use Lasso\Libraries\Field\Field_Mapping;
use Lasso\Libraries\Lasso_URL;
use Lasso\Libraries\Table\Table_Field_Group;
use Lasso\Libraries\Table\Table_Field_Group_Detail;

use Lasso\Models\Model as Lasso_Model;
use Lasso\Models\Fields;
use Lasso\Models\Model;
use Lasso\Models\Table_Field_Group as Model_Table_Field_Group;
use Lasso\Models\Table_Field_Group_Detail as Model_Table_Field_Group_Detail;

use Lasso_Affiliate_Link;

/**
 * Table_Mapping
 */
class Table_Mapping {

	/**
	 * ID
	 *
	 * @var int $id ID of table.
	 */
	private $id;

	/**
	 * Table ID
	 *
	 * @var int $table_id Table id.
	 */
	private $table_id;

	/**
	 * Lasso ID
	 *
	 * @var int $lasso_id Lasso id.
	 */
	private $lasso_id;

	/**
	 * Order
	 *
	 * @var int $order order.
	 */
	private $order;

	/**
	 * Title
	 *
	 * @var string $title Title.
	 */
	private $title;

	const TABLE = 'lasso_table_mapping';

	/**
	 * Title visible
	 *
	 * @var int $title_visible Title visible.
	 */
	private $title_visible;

	/**
	 * Table_Mapping constructor.
	 *
	 * @param array $data An array data from DB.
	 */
	public function __construct( $data = array() ) {
		if ( isset( $data['id'] ) ) {
			$this->set_id( $data['id'] );
		}

		if ( isset( $data['table_id'] ) ) {
			$this->set_table_id( $data['table_id'] );
		}

		if ( isset( $data['lasso_id'] ) ) {
			$this->set_lasso_id( $data['lasso_id'] );
		}

		if ( isset( $data['order'] ) ) {
			$this->set_order( $data['order'] );
		}

		if ( isset( $data['title_visible'] ) ) {
			$this->set_title_visible( $data['title_visible'] );
		} else {
			$this->set_title_visible( 1 );
		}

		if ( isset( $data['title'] ) ) {
			$this->set_title( $data['title'] );
		}

		return $this;
	}

	/**
	 * Get ID
	 *
	 * @return mixed
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set ID
	 *
	 * @param mixed $id ID.
	 */
	private function set_id( $id ) {
		$this->id = (int) $id;
	}

	/**
	 * Get table ID
	 *
	 * @return mixed
	 */
	public function get_table_id() {
		return $this->table_id;
	}

	/**
	 * Set table ID
	 *
	 * @param mixed $table_id Should match a value in the lasso_table_details table.
	 */
	public function set_table_id( $table_id ) {
		$this->table_id = (int) $table_id;
	}


	/**
	 * Get lasso id
	 *
	 * @return mixed
	 */
	public function get_lasso_id() {
		return (int) $this->lasso_id;
	}

	/**
	 * Set lasso id
	 *
	 * @param mixed $lasso_id Should match an existing Lasso Link ID.
	 */
	public function set_lasso_id( $lasso_id ) {
		$this->lasso_id = (int) $lasso_id;
	}


	/**
	 * Get lasso order
	 *
	 * @return int
	 */
	public function get_order() {
		return (int) $this->order;
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
		$this->order = (int) $order;
	}

	/**
	 * Get title visible
	 *
	 * @return int
	 */
	public function get_title_visible() {
		return $this->title_visible;
	}

	/**
	 * Set title visible
	 *
	 * @param int $title_visible Title visible.
	 */
	public function set_title_visible( $title_visible ) {
		$this->title_visible = (int) $title_visible;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Set title
	 *
	 * @param string $title Title.
	 */
	public function set_title( $title ) {
		$this->title = $title;
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

		$sql = '
			INSERT INTO ' . self::get_table_name() . '
				(`table_id`, `lasso_id`, `order`, `title_visible`, `title`) 
			VALUES (%d, %d, %d, %d, %s);
		';
		$sql = Model::prepare(
			$sql, // phpcs:ignore
			$this->get_table_id(),
			$this->get_lasso_id(),
			$this->get_order(),
			$this->get_title_visible(),
			$this->get_title()
		); // phpcs:ignore

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
				`table_id` = %d,
				`lasso_id` = %d,
				`order` = %d,
				`title_visible` = %d,
				`title` = %s
			WHERE `id` = %d;
		';
		$sql = Model::prepare(
			$sql, // phpcs:ignore
			$this->get_table_id(),
			$this->get_lasso_id(),
			$this->get_order(),
			$this->get_title_visible(),
			$this->get_title(),
			$this->get_id()
		); // phpcs:ignore

		Model::query( $sql ); // phpcs:ignore

		return $this;
	}

	/**
	 * Get by id
	 *
	 * @param int $id ID.
	 *
	 * @return Table_Mapping|null
	 */
	public static function get_by_id( $id ) {
		$sql    = '
			SELECT * 
			FROM ' . self::get_table_name() . ' 
			WHERE id = %d
		';
		$sql    = Model::prepare( $sql, $id ); // phpcs:ignore
		$result = Model::get_row( $sql, ARRAY_A ); // phpcs:ignore

		if ( ! empty( $result ) ) {
			return new self( $result );
		}
		return null;
	}

	/**
	 * Get list lasso url by table ID
	 *
	 * @param int $table_id Table ID.
	 * @param int $limit    Limit records. Default to false - No limit.
	 *
	 * @return Table_Mapping[]
	 */
	public static function get_list_by_table_id( $table_id, $limit = false ) {
		$limit = $limit ? 'LIMIT ' . intval( $limit ) : '';
		$sql   = '
			SELECT * 
			FROM ' . self::get_table_name() . ' 
			WHERE table_id = %d 
			ORDER BY `order` ASC ' .
			$limit;

		$sql     = Model::prepare( $sql, $table_id ); // phpcs:ignore
		$results = Model::get_results( $sql, ARRAY_A ); // phpcs:ignore

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = new self( $result );
			}
		}
		return $list;
	}

	/**
	 * Get list field by table id and lasso id
	 *
	 * @param int  $table_id Table ID.
	 * @param int  $lasso_id lasso ID.
	 * @param bool $use_serial_key Define array key by product ID.
	 * @return Table_Mapping[]
	 */
	public static function get_list_by_table_id_lasso_id( $table_id, $lasso_id, $use_serial_key = true ) {
		$sql = '
			SELECT * 
			FROM ' . self::get_table_name() . ' 
			WHERE table_id = %d 
				AND lasso_id = %d 
			ORDER BY `order` ASC
		';

		$sql     = Model::prepare( $sql, $table_id, $lasso_id ); // phpcs:ignore
		$results = Model::get_results( $sql, ARRAY_A ); // phpcs:ignore

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$row = new self( $result );
				if ( $use_serial_key ) {
					$list[] = $row;
				} else {
					$list[ $row->get_id() ] = $row;
				}
			}
		}
		return $list;
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
			SELECT * 
			FROM ' . self::get_table_name() . ' 
			WHERE table_id = %d 
				AND lasso_id = %d
		';

		$sql    = Model::prepare( $sql, $table_id, $lasso_id ); // phpcs:ignore
		$result = Model::get_row( $sql, ARRAY_A ); // phpcs:ignore

		if ( ! empty( $result ) ) {
			return new self( $result );
		}
		return null;
	}

	/**
	 * Delete a row
	 */
	public function delete() {
		$sql = '
			DELETE FROM ' . self::get_table_name() . ' 
			WHERE id = %d
		';
		$sql = Model::prepare( $sql, $this->get_id() ); // phpcs:ignore
		Model::query( $sql ); // phpcs:ignore
	}

	/**
	 * Add a product to table and also populate fields to table mapping.
	 *
	 * @param int $table_id Table ID.
	 * @param int $lasso_id Lasso ID.
	 * @return Table_Mapping
	 */
	public static function add_product( $table_id, $lasso_id ) {

		$table_mapping_products = self::get_list_by_table_id( $table_id );
		$lasso_url              = Lasso_URL::get_by_lasso_id( $lasso_id );
		if ( ! empty( $table_mapping_products ) ) {
			// ? populate fields from exist Product to new Product
			$table_mapping_product = array_shift( $table_mapping_products );

			$table_mapping_new_product = new Table_Mapping();
			$table_mapping_new_product->set_table_id( $table_id );
			$table_mapping_new_product->set_lasso_id( $lasso_id );
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
			$table_mapping_new_product->set_order();
			$table_mapping_new_product->insert();

			// ? We auto add title and image when customer add the first product to comparison table
			// 1. Add title field
			$field_group_id = $table_mapping_new_product->add_field( Fields::PRODUCT_NAME_FIELD_ID, null );
			// 2. Clone title from url detail to table
			$tb_field_group_detail = Model_Table_Field_Group_Detail::get_by_field_id_field_group_id_lasso_id( Fields::PRODUCT_NAME_FIELD_ID, $field_group_id, $lasso_id );
			if ( $tb_field_group_detail ) {
				$tb_field_group_detail->set_field_value( $lasso_url->name );
				$tb_field_group_detail->update();
			}

			$table_mapping_new_product->add_field( Fields::IMAGE_FIELD_ID, $field_group_id );
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
		if ( ! isset( $table_field_group ) ) {
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
			self::link_field_group_detail( $field_group_id, $field_id, $this->get_lasso_id(), $order );
		}

		$table_mapping_products = self::get_list_by_table_id( $this->get_table_id() );
		foreach ( $table_mapping_products as $table_mapping_product ) {
			if ( $table_mapping_product->get_lasso_id() === $this->get_lasso_id() ) {
				continue;
			}
			$order_clone       = $table_field_group->get_order();
			$table_field_group = Table_Field_Group::get_by_table_id_lasso_id_field_group_id( $table_mapping_product->get_table_id(), $table_mapping_product->get_lasso_id(), $field_group_id );
			if ( ! isset( $table_field_group ) ) {
				$table_field_group = new Table_Field_Group();
				$table_field_group->set_table_id( $table_mapping_product->get_table_id() );
				$table_field_group->set_lasso_id( $table_mapping_product->get_lasso_id() );
				$table_field_group->set_field_id( $field_id );
				$table_field_group->set_field_group_id( $field_group_id );
				$table_field_group->set_order( $order_clone );
				$table_field_group->insert();
			}
			if ( 0 === $table_field_group->get_field_id() ) {
				$table_field_group->set_field_id( $field_id );
				$table_field_group->update();
			}
			self::link_field_group_detail( $field_group_id, $field_id, $table_mapping_product->get_lasso_id(), $order );
		}

		return $table_field_group->get_field_group_id();

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
			FROM ' . self::get_table_name() . ' 
			WHERE table_id = %d
		';
		$sql    = Model::prepare( $sql, $table_id ); // phpcs:ignore
		$result = Model::get_row( $sql, ARRAY_A ); // phpcs:ignore

		if ( ! empty( $result ) ) {
			return $result['order'];
		}
		return 0;
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
	 * Product is show title or not
	 *
	 * @return bool
	 */
	public function is_show_title() {
		return 1 === $this->get_title_visible();
	}

	/**
	 * Get the final primary url of table product.
	 */
	public function get_final_primary_url() {
		// ? Get primary url field if exist.
		$sql = '
			SELECT field_value 
			FROM `' . ( new Model_Table_Field_Group_Detail() )->get_table_name() . '`
			WHERE lasso_id = %d
				AND field_id = %d
				AND field_group_id IN 
					(
						SELECT field_group_id 
						FROM `' . ( new Model_Table_Field_Group() )->get_table_name() . '` 
						WHERE table_id = %d 
							AND lasso_id = %d
					)
		';
		$sql = Lasso_Model::prepare( $sql, $this->get_lasso_id(), Fields::PRIMARY_BTN_ID, $this->get_table_id(), $this->get_lasso_id() );

		$primary_url_field_value = Lasso_Model::get_var( $sql, true );

		if ( ! empty( $primary_url_field_value ) ) {
			$field_value_decode_json = json_decode( $primary_url_field_value );
			return $field_value_decode_json->url;
		} else {
			$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $this->get_lasso_id() );
			return $lasso_url->public_link;
		}
	}
}
