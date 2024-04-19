<?php
/**
 * Declare class Lasso_Data_Sync_Lasso_Links
 *
 * @package Lasso_Data_Sync_Lasso_Links
 */

use Lasso\Classes\Encrypt;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting as Lasso_Setting;

use Lasso\Models\Model;

/**
 * Lasso_Data_Sync_Lasso_Links
 */
class Lasso_Data_Sync_Lasso_Links extends Lasso_Data_Sync {
	const DWH_TABLE_NAME    = 'lasso_links';
	const TEXT_LENGTH_LIMIT = 100;

	/**
	 * Default theme setting.
	 *
	 * @var string
	 */
	public $default_theme;

	/**
	 * Construction of Lasso_Data_Sync_Lasso_Links
	 */
	public function __construct() {
		$this->table = Model::get_wp_table_name( 'posts' );

		parent::__construct( $this->table, LASSO_VERSION );

		$this->submission_content  = self::DWH_TABLE_NAME;
		$this->modified_date_field = 'post_modified';
		$this->limit               = 200;
		$this->default_theme       = Lasso_Setting::lasso_get_setting( 'theme_name' );
	}

	/**
	 * Get full data
	 *
	 * @param array $schema Data schema.
	 */
	public function get_data_query( $schema = array() ) {
		$lasso_submission_date = get_option( 'lasso_submission_date_' . self::DWH_TABLE_NAME, '' );
		$query                 = '
            SELECT p.ID as id, p.post_modified, lu.product_type, lu.product_id, lu.base_domain, lu.is_opportunity as detect_oppotunities
            FROM ' . $this->table . ' as p
            INNER JOIN ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . " as lu 
				ON p.ID = lu.lasso_id
            WHERE 
				p.post_type = '" . LASSO_POST_TYPE . "'
				AND p.post_status = 'publish'
		";

		if ( '' !== $lasso_submission_date && 'diff' === $this->submission_type ) {
			$query .= '
				AND p.' . $this->modified_date_field . ' > \'' . $lasso_submission_date . '\'
			';
		}

		$query .= '
			ORDER BY p.' . $this->modified_date_field . ' ASC, p.ID ASC
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

		$query = 0 === $page ? $this->get_data_query( $schema ) : $this->get_data_query_limit( $page, $schema );
		$rows  = Model::get_results( $query, ARRAY_A );
		$rows  = array_map(
			function( $row ) use ( $site_id, &$submission_date, $schema ) {
				$row['site_id'] = $site_id;
				if ( '' === $submission_date ) {
					$submission_date = $row[ $this->modified_date_field ];
				}

				$last_modified   = new DateTime( $row[ $this->modified_date_field ] );
				$target          = new DateTime( $submission_date );
				$interval        = $last_modified->diff( $target );
				$submission_date = '+' === $interval->format( '%R' ) ? $submission_date : $row[ $this->modified_date_field ];

				$row = $this->build_lasso_link_data( $row );

				// ? Validate schema with row keys
				$row_keys = array_keys( $row );
				sort( $row_keys );
				$diff = array_diff( $row_keys, $schema );

				// ? Set null if the row keys not match to the schema
				if ( ! empty( $diff ) ) {
					$row = null;
				}

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

		Lasso_Helper::write_log( 'Data sync lasso links takes (seconds): ' . ( microtime( true ) - $start_time ), 'data_sync' );

		return $res;
	}

	/**
	 * Get all ids so we can delete unused ids on Lambda DB
	 */
	public function get_all_ids() {
		$query = '
            SELECT ID as id
            FROM ' . $this->table . "
            WHERE 
				post_type = '" . LASSO_POST_TYPE . "'
				AND post_status = 'publish'
		";

		return Model::get_col( $query );
	}

	/**
	 * Sync Lasso links that are publishing in WP
	 */
	public function sync_publishing_lasso_links() {
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
	 * Build lasso link data
	 *
	 * @param array $row Row data.
	 * @return array
	 */
	public function build_lasso_link_data( $row ) {
		$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $row['id'], true );

		// ? Category: eparated by double pipe and sort ASC. Ex: Apple||Google||Samsung
		$category = $lasso_url->category;
		if ( ! empty( $category ) ) {
			$category = get_terms(
				array(
					'taxonomy'         => LASSO_CATEGORY,
					'term_taxonomy_id' => $category,
					'fields'           => 'names',
				)
			);
		}

		$row['link_type']                 = $lasso_url->link_type;
		$row['name']                      = $lasso_url->name;
		$row['slug']                      = $lasso_url->slug;
		$row['primary_url']               = $lasso_url->target_url;
		$row['permalink']                 = $lasso_url->permalink;
		$row['public_link']               = $lasso_url->public_link;
		$row['product_type']              = $row['product_type'] ? $row['product_type'] : 'Other';
		$row['product_id']                = $row['product_id'] ? $row['product_id'] : '';
		$row['image_src']                 = $lasso_url->image_src;
		$row['price']                     = strlen( $lasso_url->price ) > self::TEXT_LENGTH_LIMIT ? substr( $lasso_url->price, 0, self::TEXT_LENGTH_LIMIT ) . '...' : $lasso_url->price;
		$row['description']               = strlen( $lasso_url->description ) > self::TEXT_LENGTH_LIMIT ? substr( $lasso_url->description, 0, self::TEXT_LENGTH_LIMIT ) . '...' : $lasso_url->description;
		$row['categories']                = implode( '||', $category );
		$row['fields']                    = $this->build_lasso_url_fields( $lasso_url->fields );
		$row['theme']                     = $lasso_url->display->theme ? $lasso_url->display->theme : $this->default_theme;
		$row['badge']                     = strlen( $lasso_url->display->badge_text ) > self::TEXT_LENGTH_LIMIT ? substr( $lasso_url->display->badge_text, 0, self::TEXT_LENGTH_LIMIT ) . '...' : $lasso_url->display->badge_text;
		$row['primary_url_button_text']   = $lasso_url->display->primary_button_text;
		$row['secondary_url']             = $lasso_url->display->secondary_url;
		$row['secondary_url_button_text'] = $lasso_url->display->secondary_button_text;
		$row['disclosure']                = strlen( $lasso_url->display->disclosure_text ) > self::TEXT_LENGTH_LIMIT ? substr( $lasso_url->display->disclosure_text, 0, self::TEXT_LENGTH_LIMIT ) . '...' : $lasso_url->display->disclosure_text;
		$row['show_disclosure']           = intval( $lasso_url->display->show_disclosure );
		$row['detect_oppotunities']       = intval( $row['detect_oppotunities'] );
		$row['link_cloaking']             = intval( $lasso_url->link_cloaking );
		$row['open_new_tab']              = intval( $lasso_url->open_new_tab );
		$row['enable_nofollow']           = intval( $lasso_url->enable_nofollow );
		$row['enable_sponsored']          = intval( $lasso_url->enable_sponsored );
		$row['open_new_tab2']             = intval( $lasso_url->open_new_tab2 );
		$row['enable_nofollow2']          = intval( $lasso_url->enable_nofollow2 );
		return $row;
	}

	/**
	 * Build Lasso link fields string. Separated by double pipe and sort ASC. Ex: Cons||Primary Rating||Pros
	 *
	 * @param object $fields Lasso post fields.
	 * @return string
	 */
	public function build_lasso_url_fields( $fields ) {
		$field_names = array();

		if ( $fields->primary_rating ) {
			$field_names[] = $fields->primary_rating->field_name;
		}

		foreach ( $fields->user_created as $field ) {
			$field_names[] = $field->field_name;
		}

		sort( $field_names );
		$field_names = implode( '||', $field_names );

		return $field_names;
	}

	/**
	 * Reset submission date
	 */
	public function reset_submission_date() {
		update_option( 'lasso_submission_date_' . self::DWH_TABLE_NAME, '' );
	}
}
