<?php
/**
 * Models
 *
 * @package Models
 */

namespace Lasso\Models;

/**
 * Model
 */
class Post_Content_History extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_post_content_history';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'id',
		'object_id',
		'old_value',
		'new_value',
		'updated_date',
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
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			object_id bigint(20) UNSIGNED NOT NULL,
			old_value longtext NOT NULL,
			new_value longtext NOT NULL,
			updated_date datetime NOT NULL,
			PRIMARY KEY  (`id`)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Update DB structure and data for v277
	 */
	public function update_for_v277() {
		$query = '
			SELECT id 
			FROM ' . $this->get_table_name() . ' 
			GROUP BY object_id 
			ORDER BY updated_date ASC
		';
		// ? The record ids that is the first one of each post on table lasso_post_content_history
		$first_one_ids = self::get_col( $query );

		// ? Delete other records that are not the first one of each post
		if ( ! empty( $first_one_ids ) ) {
			$query = '
				DELETE FROM ' . $this->get_table_name() . ' 
				WHERE id NOT IN(' . implode( ',', $first_one_ids ) . ')
			';
			self::query( $query );
		}
	}
}
