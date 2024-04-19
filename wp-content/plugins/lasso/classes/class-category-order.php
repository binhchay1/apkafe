<?php
/**
 * Declare class Category_Order
 *
 * @package Category_Order
 */

namespace Lasso\Classes;

use Lasso\Models\Model;

/**
 * Category_Order
 */
class Category_Order {

	/**
	 * Item Id
	 *
	 * @var int $item_id item_id.
	 */
	private $item_id;

	/**
	 * Parent Slug
	 *
	 * @var string $parent_slug parent_slug
	 */
	private $parent_slug;

	/**
	 * Term Order
	 *
	 * @var int $term_order term_order
	 */
	private $term_order;

	/**
	 * Category_Order constructor.
	 *
	 * @param array $data An array of DB.
	 */
	public function __construct( $data = array() ) {

		if ( isset( $data['item_id'] ) ) {
			$this->set_item_id( $data['item_id'] );
		}

		if ( isset( $data['parent_slug'] ) ) {
			$this->set_parent_slug( $data['parent_slug'] );
		}

		if ( isset( $data['term_order'] ) ) {
			$this->set_term_order( $data['term_order'] );
		}

		return $this;
	}

	/**
	 * Get Item ID
	 *
	 * @return mixed
	 */
	public function get_item_id() {
		return $this->item_id;
	}

	/**
	 * Set ItemID
	 *
	 * @param mixed $item_id ItemID.
	 */
	public function set_item_id( $item_id ): void {
		$this->item_id = $item_id;
	}

	/**
	 * Get ParentSlug
	 *
	 * @return mixed
	 */
	public function get_parent_slug() {
		return $this->parent_slug;
	}

	/**
	 * Set ParentSlug
	 *
	 * @param mixed $parent_slug ParentSlug.
	 */
	public function set_parent_slug( $parent_slug ): void {
		$this->parent_slug = $parent_slug;
	}

	/**
	 * Get TermOrder
	 *
	 * @return mixed
	 */
	public function get_term_order() {
		return $this->term_order;
	}

	/**
	 * Set TermOrder
	 *
	 * @param mixed $term_order TermOrder.
	 */
	public function set_term_order( $term_order ): void {
		$this->term_order = $term_order;
	}

	/**
	 * Get table name
	 *
	 * @return string
	 */
	public static function get_table_name() {
		return Model::get_prefix() . LASSO_CATEGORY_ORDER_DB;
	}

	/**
	 * Get max order by slug
	 *
	 * @param string $parent_slug ParentSlug.
	 *
	 * @return int
	 */
	public static function get_max_order_by_slug( $parent_slug ) {
		$sql     = '
			SELECT max(term_order) as `term_order` FROM ' . self::get_table_name() . ' WHERE `parent_slug` = %s
		';
		$results = Model::get_results( Model::prepare( $sql, $parent_slug ) ); // phpcs:ignore
		return ( isset( $results[0] ) && ( null !== $results[0]->term_order ) ) ? intval( $results[0]->term_order ) + 1 : 0;
	}

	/**
	 * Get by item
	 *
	 * @param int $item_id ItemId.
	 *
	 * @return array|null
	 */
	public static function get_by_item( $item_id ) {
		$sql     = '
			SELECT * FROM ' . self::get_table_name() . ' WHERE `item_id` = %d
		';
		$results = Model::get_results( Model::prepare( $sql, $item_id ) ); // phpcs:ignore
		return $results;
	}

	/**
	 * Delete Category Order
	 *
	 * @param int   $item_id ItemId.
	 * @param array $slugs ParentSlug.
	 *
	 * @return boolean
	 */
	public static function delete_category_order( $item_id, $slugs ) {
		$slugs      = array_values( $slugs );
		$in_str_arr = array_fill( 0, count( $slugs ), '%s' );
		$in_str     = implode( ',', $in_str_arr );

		$sql = 'DELETE FROM ' . self::get_table_name() . ' WHERE `item_id` = %d';
		$sql = Model::prepare( $sql, $item_id ); // phpcs:ignore
		$sql = $sql . " AND `parent_slug` IN($in_str)";
		$sql = Model::prepare( $sql, $slugs ); // phpcs:ignore

		return Model::query( $sql ); // phpcs:ignore
	}

	/**
	 * Insert new record
	 *
	 * @return boolean
	 */
	public function insert() {
		$sql = '
			INSERT INTO ' . self::get_table_name() . ' VALUES (%d, %s, %d);
		';
		$sql = Model::prepare( $sql, $this->get_item_id(), $this->get_parent_slug(), $this->get_term_order() ); // phpcs:ignore
		return Model::query( $sql ); // phpcs:ignore
	}

}
