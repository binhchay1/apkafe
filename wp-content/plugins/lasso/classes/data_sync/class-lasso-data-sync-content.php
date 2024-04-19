<?php
/**
 * Declare class Lasso_Data_Sync_Content
 *
 * @package Lasso_Data_Sync_Content
 */

use Lasso\Models\Model;

/**
 * Lasso_Data_Sync_Content
 */
class Lasso_Data_Sync_Content extends Lasso_Data_Sync {
	/**
	 * Construction of Lasso_Data_Sync_Content
	 */
	public function __construct() {
		$this->table = Model::get_wp_table_name( LASSO_CONTENT_DB );

		parent::__construct( $this->table, LASSO_VERSION );
	}
}
