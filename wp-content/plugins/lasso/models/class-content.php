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
class Content extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_content';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'id',
		'post_type',
		'title',
		'permalink',
		'last_modified',

		'author',
		'words_count',
		'h2_count',
		'image_count',
		'total_link_count',

		'monetized_count',
		'internal_link_count',
		'incoming_internal_link_count',
		'display_count',
		'grid_count',

		'list_count',
		'table_count',
		'competitor_shortcode_count',
		'created_at',
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
			id bigint(20) NOT NULL,
			post_type varchar(20) NOT NULL,
			title text NOT NULL,
			permalink varchar(500) NOT NULL,
			last_modified datetime NOT NULL,
			author bigint(20) NOT NULL,
			words_count int(11) NOT NULL,
			h2_count int(11) NOT NULL,
			image_count int(11) NOT NULL,
			total_link_count int(11) NOT NULL,
			monetized_count int(11) NOT NULL,
			internal_link_count int(11) NOT NULL,
			incoming_internal_link_count int(11) NOT NULL,
			display_count int(11) NOT NULL,
			grid_count int(11) NOT NULL,
			list_count int(11) NOT NULL,
			table_count int(11) NOT NULL,
			competitor_shortcode_count int(11) NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Update DB structure and data for v228
	 */
	public function update_for_v228() {
		// ? change datetime column to not null
		$query = '
			ALTER TABLE ' . $this->get_table_name() . ' 
				CHANGE `created_at` `created_at` DATETIME NOT NULL
		';
		self::query( $query );
	}
}
