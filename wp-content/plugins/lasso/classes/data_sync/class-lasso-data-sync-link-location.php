<?php
/**
 * Declare class Lasso_Data_Sync_Link_Location
 *
 * @package Lasso_Data_Sync_Link_Location
 */

use Lasso\Classes\Helper as Lasso_Helper;

use Lasso\Classes\Encrypt;
use Lasso\Models\Model;

/**
 * Lasso_Data_Sync_Link_Location
 */
class Lasso_Data_Sync_Link_Location extends Lasso_Data_Sync {
	/**
	 * Construction of Lasso_Data_Sync_Link_Location
	 */
	public function __construct() {
		$this->table = Model::get_wp_table_name( LASSO_LINK_LOCATION_DB );

		parent::__construct( $this->table, LASSO_VERSION );

		$this->modified_date_field = 'detection_date';
		$this->limit               = 500;
	}

	/**
	 * Get full data
	 *
	 * @param array $schema Data schema.
	 */
	public function get_data_query( $schema = array() ) {
		$query = parent::get_data_query( $schema );

		$query .= '
			ORDER BY ' . $this->modified_date_field . ' ASC, id ASC
		';

		return $query;
	}

	/**
	 * Get all ids so we can delete unused ids on Lambda DB
	 */
	public function get_all_ids() {
		$query = '
			SELECT id
			FROM ' . $this->table . '
		';

		return Model::get_col( $query );
	}

	/**
	 * Sync link locations that are existing in WP
	 */
	public function sync_existing_link_locations() {
		$headers            = $this->headers;
		$api_link           = LASSO_LINK . '/data-sync/data';
		$ids                = $this->get_all_ids();
		$submission_content = str_replace( Model::get_prefix(), '', $this->table );

		$data = array(
			'submission_content' => $submission_content,
			'post_ids'           => $ids,
		);
		$data = Encrypt::encrypt_aes( $data );

		$res = Lasso_Helper::send_request( 'put', $api_link, $data, $headers );

		return $res;
	}
}
