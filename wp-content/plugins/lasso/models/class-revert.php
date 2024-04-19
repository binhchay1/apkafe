<?php
/**
 * Models
 *
 * @package Models
 */

namespace Lasso\Models;

use Lasso\Classes\Setting_Enum;

/**
 * Model
 */
class Revert extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_revert';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'id',
		'lasso_id',
		'post_data',
		'old_uri',
		'plugin',

		'revert_dt',
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
			lasso_id bigint(20) NOT NULL,
			post_data text NULL,
			old_uri varchar(500) NOT NULL,
			plugin varchar(200) NOT NULL,
			revert_dt datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY  ix_revert_lasso_id (lasso_id),
			KEY  ix_revert_old_uri (old_uri),
			KEY  ix_revert_plugin (plugin)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Get revert data
	 *
	 * @param int         $id      Lasso id.
	 * @param string|bool $plugin  Post type. Default to false.
	 * @param string|bool $old_uri Filter by old uri. Default to false.
	 */
	public function get_revert_data( $id, $plugin = false, $old_uri = false ) {
		$query = '
			SELECT *
			FROM ' . $this->get_table_name() . ' 
			WHERE (
				post_data = %d 
				OR lasso_id = %d
			)
		';
		$query = self::prepare( $query, $id, $id );

		if ( $plugin ) {
			$query .= '
				AND plugin = %s
			';
			$query  = self::prepare( $query, $plugin );
		}

		if ( $old_uri ) {
			$query .= '
				AND old_uri = %s
			';
			$query  = self::prepare( $query, $old_uri );
		}

		$results = self::get_results( $query );

		if ( ! $results ) {
			return $results;
		}

		foreach ( $results as $result ) {
			$revert = new self();
			$row    = $revert->map_properties( $result );
			if ( intval( $row->get_post_data() ) > 0 ) { // ? Get id in post_data column
				return $row;
			}
		}

		return $row;
	}

	/**
	 * Update DB structure and data for v228
	 */
	public function update_for_v228() {
		// ? change datetime column to not null
		$query = '
			ALTER TABLE ' . $this->get_table_name() . ' 
				CHANGE `revert_dt` `revert_dt` DATETIME NOT NULL
		';
		self::query( $query );
	}

	/**
	 * Update DB structure and data for v316
	 */
	public function update_for_v316() {
		$query = '
			UPDATE ' . $this->get_table_name() . ' 
			SET `plugin` = %s
			WHERE plugin = %s
		';
		$query = self::prepare( $query, Setting_Enum::SURL_SLUG, 'simple-urls/plugin.php' );

		return self::query( $query );
	}

	/**
	 * Insert AAWP table record.
	 * Note:
	 *  + lasso_id: Lasso table id.
	 *  + post_data: AAWP table id.
	 *  + old_uri: [amazon table="{aawp_table_id}"]
	 *  + plugin: aawp
	 *
	 * @param int $aawp_table_id  AAWP table id.
	 * @param int $lasso_table_id Lasso table id.
	 * @return bool|int
	 */
	public function process_import_aawp_table( $aawp_table_id, $lasso_table_id ) {
		// ? Log what we imported for potential reverts
		$insert_sql = '
			INSERT INTO ' . $this->get_table_name() . ' (lasso_id, post_data, old_uri, plugin, revert_dt)
			VALUES (%d, %d, %s, %s, NOW());
		';

		$old_uri    = '[amazon table="' . $aawp_table_id . '"]';
		$insert_sql = Model::prepare( $insert_sql, $lasso_table_id, $aawp_table_id, $old_uri, 'aawp' );
		$result     = Model::query( $insert_sql );

		return $result;
	}
}
