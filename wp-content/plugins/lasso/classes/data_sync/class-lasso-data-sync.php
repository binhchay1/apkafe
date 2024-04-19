<?php
/**
 * Declare class Lasso_Data_Sync
 *
 * @package Lasso_Data_Sync
 */

use Lasso\Classes\Encrypt;
use Lasso\Classes\Helper as Lasso_Helper;

use Lasso\Models\Model;

/**
 * Lasso_Data_Sync
 */
abstract class Lasso_Data_Sync {
	/**
	 * Lasso table name
	 *
	 * @var string $table
	 */
	protected $table;

	/**
	 * Limit results per query
	 *
	 * @var string $limit
	 */
	protected $limit;

	/**
	 * Submission type
	 *
	 * @var string $submission_type
	 */
	protected $submission_type = '';

	/**
	 * Submission content
	 *
	 * @var string $submission_content
	 */
	protected $submission_content = '';

	/**
	 * Construction of Lasso_Data_Sync
	 *
	 * @param string $table Table name.
	 * @param string $version Lasso version.
	 */
	public function __construct( $table, $version = LASSO_VERSION ) {
		$this->table               = $table;
		$this->modified_date_field = 'last_modified';
		$this->lasso_db            = new Lasso_DB();

		$this->license = Lasso_License::get_license();
		$this->site_id = Lasso_License::get_site_id();
		$this->version = $version;
		$this->headers = array(
			'Content-Type' => 'application/json',
			'license'      => $this->license,
			'site_id'      => $this->site_id,
			'site_url'     => rawurlencode( site_url() ),
		);

		$this->limit = 2000;

		$submission_content       = str_replace( Model::get_prefix(), '', $this->table );
		$this->submission_content = str_replace( '_', '-', $submission_content );
	}

	/**
	 * Get full data
	 *
	 * @param array $schema Data schema.
	 */
	public function get_data_query( $schema = array() ) {
		if ( in_array( 'site_id', $schema, true ) ) {
			$schema = array_diff( $schema, array( 'site_id' ) );
		}

		$lasso_submission_date = get_option( 'lasso_submission_date_' . $this->table, '' );
		$select_list           = empty( $schema ) ? '*' : implode( ',', $schema );
		$query                 = '
            select ' . $select_list . '
            from ' . $this->table . '
		';

		if ( '' !== $lasso_submission_date && 'diff' === $this->submission_type ) {
			$query .= '
				where ' . $this->modified_date_field . ' > \'' . $lasso_submission_date . '\'
			';
		}

		return $query;
	}

	/**
	 * Get limited data
	 *
	 * @param int   $page Page. Default to 1.
	 * @param array $schema Data schema.
	 */
	public function get_data_query_limit( $page = 1, $schema = array() ) {
		$offset = ( $page - 1 ) * $this->limit;
		$query  = $this->get_data_query( $schema );
		$query .= '
			limit %d, %d
		';
		$query = Model::prepare( $query, $offset, $this->limit ); // phpcs:ignore

		return $query;
	}

	/**
	 * Get total records
	 */
	public function get_total_records() {
		$query       = $this->get_data_query();
		$query_count = '
			select count(*) as total
			from (
				' . $query . '
			) as total_tbl
		';
		$result      = Model::get_row( $query_count );

		return intval( $result->total ?? 0 );
	}

	/**
	 * Get limit
	 */
	public function get_limit() {
		return intval( $this->limit );
	}

	/**
	 * Get total pages
	 */
	public function get_total_pages() {
		$total_records = $this->get_total_records();
		if ( 0 === $total_records ) {
			return 0;
		}

		return intval( ceil( $total_records / $this->get_limit() ) );
	}

	/**
	 * Get table
	 */
	public function get_table() {
		return $this->table;
	}

	/**
	 * Get submission_type in the request
	 */
	public function get_submission_type() {
		$lasso_submission_date = get_option( 'lasso_submission_date_' . $this->table, '' );

		if ( 'full' === $this->submission_type ) {
			return $this->submission_type;
		}

		return '' === $lasso_submission_date ? 'full' : 'diff';
	}

	/**
	 * Set submission_type in the request
	 *
	 * @param string $type Submission type (full, diff). Default to 'full'.
	 */
	public function set_submission_type( $type = 'full' ) {
		$this->submission_type = 'full' === $type ? $type : 'diff';
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

		$query = 0 === $page ? $this->get_data_query( $schema ) : $this->get_data_query_limit( $page, $schema );
		$rows  = Model::get_results( $query, ARRAY_A );
		$rows  = array_map(
			function( $row ) use ( $site_id, &$submission_date ) {
				$row['site_id'] = $site_id;
				if ( '' === $submission_date ) {
					$submission_date = $row[ $this->modified_date_field ];
				}

				$last_modified   = new DateTime( $row[ $this->modified_date_field ] );
				$target          = new DateTime( $submission_date );
				$interval        = $last_modified->diff( $target );
				$submission_date = '+' === $interval->format( '%R' ) ? $submission_date : $row[ $this->modified_date_field ];

				return $row;
			},
			$rows
		);

		return array( $rows, $submission_date );
	}

	/**
	 * Get schema of the table from BLS
	 */
	public function get_schema() {
		$headers = $this->headers;
		$data    = array(
			'submission_content' => $this->submission_content,
			'plugin_version'     => $this->version,
		);

		$encrypted_base64 = Encrypt::encrypt_aes( $data, true );
		$api_link         = LASSO_LINK . '/data-sync/?' . $encrypted_base64;
		$res              = Lasso_Helper::send_request( 'get', $api_link, array(), $headers );

		if ( 200 === $res['status_code'] ) {
			$data = $res['response']->data ?? array();
			if ( $data && is_array( $data ) ) {
				return $data;
			}

			return $res['response'];
		}

		return array();
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
			update_option( 'lasso_submission_date_' . $this->table, $recent_date );
		}

		Lasso_Helper::write_log( 'Data sync table ' . $this->table . ' takes (seconds): ' . ( microtime( true ) - $start_time ), 'data_sync' );

		return $res;
	}

	/**
	 * Reset submission date
	 */
	public function reset_submission_date() {
		update_option( 'lasso_submission_date_' . $this->table, '' );
	}
}
