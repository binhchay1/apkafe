<?php
/**
 * Declare class Lasso_Data_Sync_Authors
 *
 * @package Lasso_Data_Sync_Authors
 */

use Lasso\Classes\Encrypt;
use Lasso\Classes\Helper as Lasso_Helper;

use Lasso\Models\Model;

/**
 * Lasso_Data_Sync_Authors
 */
class Lasso_Data_Sync_Authors extends Lasso_Data_Sync {
	const DWH_TABLE_NAME = 'lasso_authors';

	/**
	 * Construction of Lasso_Data_Sync_Authors
	 */
	public function __construct() {
		$this->table = Model::get_wp_table_name( 'users' );

		parent::__construct( $this->table, LASSO_VERSION );

		$this->submission_content  = self::DWH_TABLE_NAME;
		$this->modified_date_field = 'user_registered';
		$this->limit               = 200;
	}

	/**
	 * Get full data
	 *
	 * @param array $schema Data schema.
	 */
	public function get_data_query( $schema = array() ) {
		$lasso_submission_date = get_option( 'lasso_submission_date_' . self::DWH_TABLE_NAME, '' );
		$query                 = '
            SELECT ID as author_id, display_name as author_name, user_registered
            FROM ' . $this->table;

		if ( '' !== $lasso_submission_date && 'diff' === $this->submission_type ) {
			$query .= '
				WHERE ' . $this->modified_date_field . ' > \'' . $lasso_submission_date . '\'
			';
		}

		$query .= '
			ORDER BY ' . $this->modified_date_field . ' ASC, ID ASC
		';

		return $query;
	}

	/**
	 * Get data in the table
	 *
	 * @param int   $page Page number.
	 * @param array $schema Data schema.
	 */
	public function get_data( $page = 0, $schema = array() ) {
		$site_id         = $this->site_id;
		$submission_date = '';
		$updated_dt      = Lasso_Helper::get_gmt_datetime();

		$query = 0 === $page ? $this->get_data_query( $schema ) : $this->get_data_query_limit( $page, $schema );
		$rows  = Model::get_results( $query, ARRAY_A );
		$time  = ( new DateTime() )->format( 'Y-m-d H:i:s' );
		$rows  = array_map(
			function ( $row ) use ( $site_id, &$submission_date, $schema, $updated_dt, $time ) {
				if ( false !== strpos( $row[ $this->modified_date_field ], '0000' ) ) {
					$row[ $this->modified_date_field ] = $time;
				}
				$row['site_id']       = $site_id;
				$row['last_modified'] = $row[ $this->modified_date_field ];
				$row['updated_dt']    = $updated_dt;
				if ( '' === $submission_date ) {
					$submission_date = $row[ $this->modified_date_field ];
				}

				$last_modified   = new DateTime( $row[ $this->modified_date_field ] );
				$target          = new DateTime( $submission_date );
				$interval        = $last_modified->diff( $target );
				$submission_date = '+' === $interval->format( '%R' ) ? $submission_date : $row[ $this->modified_date_field ];
				unset( $row[ $this->modified_date_field ] );

				return $row;
			},
			$rows
		);

		// ? Remove empty item from $rows
		$rows = array_filter( $rows );

		return array( $rows, $submission_date );
	}

	/**
	 * Send data to BLS
	 *
	 * @param int   $page Page number.
	 * @param array $schema Data schema.
	 */
	public function send_data( $page = 0, $schema = array() ) {
		$start_time = microtime( true );
		$headers    = $this->headers;
		$api_link   = LASSO_LINK . '/data-sync';

		list($rows, $submission_date) = $this->get_data( $page, $schema );
		if ( 0 === count( $rows ) ) {
			return false;
		}

		$get_submission_type = $this->get_submission_type();
		$total               = $this->get_total_records();
		$batch_total_count   = intval( floor( $total / $this->limit ) + 1 );
		$data                = array(
			'plugin_version'     => $this->version,
			'batch_number'       => 0 === $page ? 1 : $page,
			'batch_total_count'  => $batch_total_count,
			'submission_type'    => $get_submission_type,
			'submission_date'    => $submission_date,
			'submission_content' => $this->submission_content,
			'records'            => $rows,
			'license'            => $headers['license'],
			'site_id'            => $headers['site_id'],
		);
		$data                = Encrypt::encrypt_aes( $data );

		$res     = Lasso_Helper::send_request( 'post', $api_link, $data, $headers );
		$status  = $res['response']->status ?? false;
		$message = $res['response']->message ?? '';

		if ( true === $status ) {
			$recent_date = $message;
			update_option( 'lasso_submission_date_' . self::DWH_TABLE_NAME, $recent_date );
		}

		Lasso_Helper::write_log( 'Data sync author takes (seconds): ' . ( microtime( true ) - $start_time ), 'data_sync' );

		return $res;
	}

	/**
	 * Get all ids so we can delete unused ids on Lambda DB
	 */
	public function get_all_ids() {
		$query = '
			SELECT ID as id
			FROM ' . $this->table;

		return Model::get_col( $query );
	}

	/**
	 * Sync existing authors in WP
	 */
	public function sync_existing_authors() {
		$headers  = $this->headers;
		$api_link = LASSO_LINK . '/data-sync/data';
		$ids      = $this->get_all_ids();

		$data = array(
			'submission_content' => $this->submission_content,
			'post_ids'           => $ids,
			'license'            => \Lasso_License::get_license(),
			'site_id'            => \Lasso_License::get_site_id(),
		);

		$data = Encrypt::encrypt_aes( $data );
		$res  = Lasso_Helper::send_request( 'put', $api_link, $data, $headers );

		return $res;
	}

	/**
	 * Reset submission date
	 */
	public function reset_submission_date() {
		update_option( 'lasso_submission_date_' . self::DWH_TABLE_NAME, '' );
	}
}
