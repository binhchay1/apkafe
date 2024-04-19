<?php
/**
 * Declare class Lasso_Cron
 *
 * @package Lasso_Cron
 */

use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;
use Lasso\Classes\Deactivator as Lasso_Deactivator;
use Lasso\Classes\Elementor as Lasso_Elementor;
use Lasso\Classes\Encrypt;
use Lasso\Classes\Enum as Lasso_Enum;
use Lasso\Classes\Extend_Product as Lasso_Extend_Product;
use Lasso\Classes\Fix as Lasso_Fix;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Import as Lasso_Import;
use Lasso\Classes\Launch_Darkly;
use Lasso\Classes\Link_Location as Lasso_Link_Location;
use Lasso\Classes\Log as Lasso_Log;
use Lasso\Classes\Post as Lasso_Post;
use Lasso\Classes\Post_Content_History as Lasso_Post_Content_History;
use Lasso\Classes\Redirect as Lasso_Redirect;
use Lasso\Classes\Setting_Enum;
use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Classes\Verbiage as Lasso_Verbiage;

use Lasso\Models\Affiliate_Programs;
use Lasso\Models\Link_Locations;
use Lasso\Models\MetaData;
use Lasso\Models\Model;
use Lasso\Models\Revert;
use Lasso\Models\Table_Details;

/**
 * Lasso_Cron
 */
class Lasso_Cron {
	const SHORTCODE_LIST = array(
		'lasso',

		'earnist',
		'earnist_link',

		'easyazon_link',
		'easyazon-link',
		'simpleazon-link',

		'easyazon_image',
		'easyazon-image',
		'easyazon-image-link',
		'simpleazon-image',

		'easyazon_cta',
		'easyazon-cta',

		'easyazon_infoblock',
		'easyazon_block',
		'easyazon-block',

		'amalinkspro',

		'thirstylink',

		'amazon',
		'aawp',
	);

	const UNSUPPORT_BLOCKS = array(
		'tph/',
		'kadence/',
		'editor-blocks/',
		'uagb/',
		'rank-math/',
		'amalinkspro-legacy/',
	);

	const AMALINKSPRO_FIX_LINK_BLOCK = array(
		'amalinkspro-legacy/insert-imagelink-html',
		'amalinkspro-legacy/insert-textlink-html',
	);

	const CRONS = array(
		LASSO_CRON_MONTHLY_DATA_SYNC_CONTENT          => 'lasso_monthly',
		LASSO_CRON_MONTHLY_DATA_SYNC_LINK_LOCATIONS   => 'lasso_monthly',
		LASSO_CRON_MONTHLY_DATA_SYNC_LASSO_LINKS      => 'lasso_monthly',
		LASSO_CRON_MONTHLY_DATA_SYNC_AUTHORS          => 'lasso_monthly',

		LASSO_CRON_DAILY_CLEAN_LINK_LOCATIONS_HOOK    => 'daily',
		LASSO_CRON_DAILY_DATA_SYNC_CONTENT            => 'daily',
		LASSO_CRON_DAILY_DATA_SYNC_AFFILIATE_PROGRAMS => 'daily',
		LASSO_CRON_DAILY_DATA_SYNC_PLUGINS            => 'daily',
		/** LASSO_CRON_DAILY_DATA_GET_PRETTY_LINK_FINAL_URLS => 'daily', */
		LASSO_CRON_DAILY_DATA_SYNC_LINK_LOCATIONS     => 'daily',
		LASSO_CRON_DAILY_DATA_SYNC_LASSO_LINKS        => 'daily',
		LASSO_CRON_DAILY_DATA_SYNC_AUTHORS            => 'daily',
		LASSO_CRON_DAILY_UPDATE_LICENSE_STATUS        => 'daily',
		LASSO_CRON_SYNC_AMAZON_API                    => 'daily',
		LASSO_CRON_AUTO_MONETIZE                      => 'daily',

		LASSO_CRON_HOURLY_HOOK                        => 'hourly',
		LASSO_CRON_REMOVE_DUPLICATE_PROCESSES         => 'hourly',
		/** LASSO_CRON_REVERT_LINK_URL_FROM_HISTORY       => 'hourly', */
		/** LASSO_CRON_UPDATE_CATEGORY_FOR_IMPORTED_PRETTY_LINK => 'hourly', */
		LASSO_CRON_FORCE_SCAN_ALL_LINKS               => 'lasso_fifteen_minutes',

		LASSO_CRON_SCAN_ALL_LINKS                     => 'lasso_ten_minutes',

		LASSO_CRON_UPDATE_AMAZON                      => 'lasso_five_minutes',
		LASSO_CRON_ADD_AMAZON                         => 'lasso_five_minutes',
		LASSO_CRON_IMPORT_ALL                         => 'lasso_five_minutes',
		LASSO_CRON_REVERT_ALL                         => 'lasso_five_minutes',
		LASSO_CRON_CHECK_ISSUE                        => 'lasso_five_minutes',

		LASSO_CRON_CREATE_LASSO_WEBP_IMAGE            => 'hourly',
		LASSO_CRON_CREATE_LASSO_WEBP_IMAGE_TABLE      => 'hourly',
	);

	const CRONS_LEAN = array(
		LASSO_CRON_MONTHLY_DATA_SYNC_LASSO_LINKS => 'lasso_monthly',

		LASSO_CRON_DAILY_UPDATE_LICENSE_STATUS   => 'daily',
		LASSO_CRON_SYNC_AMAZON_API               => 'daily',

		LASSO_CRON_UPDATE_AMAZON                 => 'lasso_five_minutes',
	);

	const FILTER_FIX_CONTENT_BEFORE_SCANNING        = 'filter_fix_content_before_scanning';
	const FILTER_ATAG_AT_FIRST                      = 'filter_atag_at_first';
	const FILTER_ATAG_BEFORE_SET_IGNORE             = 'filter_atag_before_set_ignore';
	const FILTER_BLOCK_TO_UPDATE_POST_CONTENT       = 'filter_block_to_update_post_content';
	const FILTER_FIX_LASSO_BLOCK_IN_GUTENBERG       = 'filter_fix_lasso_block_in_gutenberg';
	const FILTER_FIX_LASSO_IMPORT_REVERT_POST_NAME  = 'filter_fix_lasso_import_revert_post_name';
	const FILTER_FIX_ATAG_IN_GENERATE_BLOCKS_PLUGIN = 'filter_fix_atag_in_generate_blocks_plugin';
	const SITE_STRIPE_DOMAIN                        = 'ws-na.amazon-adsystem.com/widgets/q';
	const SITE_STRIPE_EU_DOMAIN                     = 'ws-eu.amazon-adsystem.com/widgets/q';

	/**
	 * Construction of Lasso_Cron
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array( $this, 'add_lasso_cron' ) ); // phpcs:ignore

		add_filter( self::FILTER_ATAG_BEFORE_SET_IGNORE, array( $this, 'fix_kadence_block_links' ), 10, 3 );
		add_filter( self::FILTER_ATAG_BEFORE_SET_IGNORE, array( $this, 'fix_amalinkspro_block_links' ), 10, 3 );
		add_filter( self::FILTER_BLOCK_TO_UPDATE_POST_CONTENT, array( $this, 'fix_block_content_issues' ), 10, 2 );
		add_filter( self::FILTER_FIX_CONTENT_BEFORE_SCANNING, array( $this, 'fix_content_before_scanning' ), 10, 2 );
		add_filter( self::FILTER_FIX_CONTENT_BEFORE_SCANNING, array( $this, 'fix_sitestripe_content_before_scanning' ), 20, 2 );

		// ? add new cronjob
		$db_status = get_option( 'lasso_license_status', '' );
		if ( 1 === intval( $db_status ) ) {
			// ? fire when a post/page/custom post types is created or updated
			add_action( 'save_post', array( $this, 'check_all_links_in_post_page' ), 1000, 1 );
			add_action( 'deleted_post', array( $this, 'delete_post_associate_data' ), 100, 1 );

			// ? LASSO CRONS
			// ? only run lasso crons if the license is activated
			add_action( LASSO_CRON_UPDATE_AMAZON, array( $this, 'update_amazon_products' ) );
			add_action( LASSO_CRON_ADD_AMAZON, array( $this, 'amazon_auto_monetization' ) );
			add_action( LASSO_CRON_IMPORT_ALL, array( $this, 'lasso_import_all' ) );
			add_action( LASSO_CRON_REVERT_ALL, array( $this, 'lasso_revert_all' ) );
			add_action( LASSO_CRON_CHECK_ISSUE, array( $this, 'lasso_check_issues' ) );
			add_action( LASSO_CRON_SCAN_ALL_LINKS, array( $this, 'lasso_check_all_posts_pages' ) );
			add_action( LASSO_CRON_FORCE_SCAN_ALL_LINKS, array( $this, 'lasso_force_scan_all_links' ) );
			add_action( LASSO_CRON_AUTO_MONETIZE, array( $this, 'lasso_auto_monetize' ) );

			add_action( LASSO_CRON_HOURLY_HOOK, array( $this, 'hourly_process' ) );
			add_action( LASSO_CRON_DAILY_CLEAN_LINK_LOCATIONS_HOOK, array( $this, 'lasso_clean_link_locations' ) );
			add_action( LASSO_CRON_DAILY_DATA_SYNC_CONTENT, array( $this, 'lasso_data_sync_content' ) );
			add_action( LASSO_CRON_MONTHLY_DATA_SYNC_CONTENT, array( $this, 'lasso_data_sync_content_full' ) );
			add_action( LASSO_CRON_DAILY_DATA_SYNC_AFFILIATE_PROGRAMS, array( $this, 'lasso_data_sync_affiliate_programs' ) );
			add_action( LASSO_CRON_DAILY_DATA_SYNC_PLUGINS, array( $this, 'lasso_data_sync_plugins' ) );
			add_action( LASSO_CRON_MONTHLY_DATA_SYNC_LINK_LOCATIONS, array( $this, 'lasso_monthly_data_sync_link_locations' ) );
			add_action( LASSO_CRON_DAILY_DATA_SYNC_LINK_LOCATIONS, array( $this, 'lasso_daily_data_sync_link_locations' ) );
			add_action( LASSO_CRON_MONTHLY_DATA_SYNC_LASSO_LINKS, array( $this, 'lasso_monthly_data_sync_lasso_links' ) );
			add_action( LASSO_CRON_DAILY_DATA_SYNC_LASSO_LINKS, array( $this, 'lasso_daily_data_sync_lasso_links' ) );
			add_action( LASSO_CRON_MONTHLY_DATA_SYNC_AUTHORS, array( $this, 'lasso_monthly_data_sync_authors' ) );
			add_action( LASSO_CRON_DAILY_DATA_SYNC_AUTHORS, array( $this, 'lasso_daily_data_sync_authors' ) );
			add_action( LASSO_CRON_DAILY_UPDATE_LICENSE_STATUS, array( $this, 'lasso_update_license_status' ) );
			add_action( LASSO_CRON_SYNC_AMAZON_API, array( $this, 'lasso_sync_and_encrypt_amazon_api' ) );
			add_action( LASSO_CRON_CREATE_LASSO_WEBP_IMAGE, array( $this, 'lasso_create_webp_image' ) );
			add_action( LASSO_CRON_CREATE_LASSO_WEBP_IMAGE_TABLE, array( $this, 'lasso_create_webp_image_table' ) );
			add_action( LASSO_CRON_REMOVE_DUPLICATE_PROCESSES, array( $this, 'lasso_remove_duplicate_processes' ) );
			// ? LASSO CRONS - END

			$this->lasso_create_schedule_hook();
		} else {
			add_action( LASSO_CRON_SCAN_ALL_LINKS, array( $this, 'lasso_check_all_posts_pages' ) );

			$lasso_deactivator = new Lasso_Deactivator();
			$lasso_deactivator->init();
		}
	}

	/**
	 * Clean link_location
	 */
	public function lasso_clean_link_locations() {
		$lasso_db = new Lasso_DB();
		$lasso_db->clean_link_locations();

		// ? delete old logs
		$this->delete_old_logs();

		// ? Delete old Lasso post history
		$this->delete_post_content_history();
	}

	/**
	 * Delete associate data after a post has been deleted
	 *
	 * @param int $post_id Post ID.
	 */
	public function delete_post_associate_data( $post_id ) {
		if ( ! in_array( get_post_type( $post_id ), Lasso_Helper::get_cpt_support(), true ) ) {
			return;
		}

		$sql = '
			DELETE FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . '
			WHERE detection_id = ' . $post_id . ' 
		';
		Model::query( $sql );
	}

	/**
	 * Create hook for the new cron
	 */
	public function lasso_create_schedule_hook() {
		$crons = self::CRONS;

		// ? Disable most of the cron if Lasso Lean is enabled
		if ( Launch_Darkly::enable_lasso_lean() ) {
			$cron_lean  = self::CRONS_LEAN;
			$cron_names = array_keys( $cron_lean );

			// ? Delete all old crons
			foreach ( $crons as $cron_name => $interval ) {
				if ( ! in_array( $cron_name, $cron_names, true ) ) {
					wp_clear_scheduled_hook( $cron_name );
				}
			}
			$crons = $cron_lean;
		}

		// ? Remove unnecessary cron
		if ( ! $this->is_allow_update_category_for_imported_pretty_link() ) {
			unset( $crons[ LASSO_CRON_UPDATE_CATEGORY_FOR_IMPORTED_PRETTY_LINK ] );
		}

		if ( ! Lasso_Setting::lasso_get_setting( Lasso_Enum::OPTION_ENABLE_WEBP ) ) {
			unset( $crons[ LASSO_CRON_CREATE_LASSO_WEBP_IMAGE ] );
			unset( $crons[ LASSO_CRON_CREATE_LASSO_WEBP_IMAGE_TABLE ] );
		}

		$cron_interval = Lasso_Setting::lasso_get_setting( 'cron_time_interval', 0 );
		$crons_array   = _get_cron_array();
		$events        = array();

		if ( ! is_array( $crons_array ) ) {
			return;
		}

		foreach ( $crons_array as $time => $cron ) {
			foreach ( $cron as $hook => $dings ) {
				if ( strpos( $hook, 'lasso_' ) === false ) {
					continue;
				}

				foreach ( $dings as $sig => $data ) {
					$interval = $data['interval'] ?? HOUR_IN_SECONDS;

					// ? get the cron that is less than the existing one
					if ( isset( $events[ $hook ] ) && $interval >= $events[ $hook ]->interval ) {
						continue;
					}

					$events[ $hook ] = (object) array(
						'hook'     => $hook,
						'time'     => $time, // ? UTC
						'schedule' => $data['schedule'],
						'interval' => $interval,
					);

				}
			}
		}

		foreach ( $crons as $cron_name => $interval ) {
			if ( $cron_interval > 0 && $cron_interval <= 3 ) {
				$interval = 'lasso_' . $cron_interval . '_hour';

				// ? check and delete the existing cron if its interval is incorrect
				if ( isset( $events[ $cron_name ] ) && $events[ $cron_name ]->interval !== $cron_interval * HOUR_IN_SECONDS ) {
					wp_clear_scheduled_hook( $cron_name );
				}
			}

			if ( ! wp_next_scheduled( $cron_name ) ) {
				wp_schedule_event( time(), $interval, $cron_name );
			}
		}
	}

	/**
	 * Add a custom cron to WordPress
	 *
	 * @param array $schedules An array of non-default cron schedules. Default empty.
	 */
	public function add_lasso_cron( $schedules ) {
		$schedules['lasso_five_minutes'] = array(
			'interval' => 5 * MINUTE_IN_SECONDS, // ? 5 minutes in seconds
			'display'  => __( 'Lasso 5 minutes' ),
		);

		$schedules['lasso_ten_minutes'] = array(
			'interval' => 10 * MINUTE_IN_SECONDS, // ? 10 minutes in seconds
			'display'  => __( 'Lasso 10 minutes' ),
		);

		$schedules['lasso_fifteen_minutes'] = array(
			'interval' => 15 * MINUTE_IN_SECONDS, // ? 15 minutes in seconds
			'display'  => __( 'Lasso 15 minutes' ),
		);

		$schedules['lasso_half_hour'] = array(
			'interval' => 30 * MINUTE_IN_SECONDS, // ? 30 minutes in seconds
			'display'  => __( 'Lasso 30 minutes' ),
		);

		$schedules['lasso_monthly'] = array(
			'interval' => MONTH_IN_SECONDS, // ? 1 month
			'display'  => __( 'Lasso One month' ),
		);

		return $schedules;
	}

	/**
	 * Cron: check issues
	 */
	public function lasso_check_issues() {
		$bg = new Lasso_Process_Check_Issue();
		$bg->check_issue();
	}

	/**
	 * Check issues of links
	 *
	 * @param int        $check_lasso_id  Post id.
	 * @param string|int $status          Status. Default to empty.
	 * @param bool       $is_skip_request Is skip request to the link server. Default to false.
	 */
	public function check_issues( $check_lasso_id, $status = '', $is_skip_request = false ) {
		global $wpdb;

		$lasso_db   = new Lasso_DB();
		$amazon_api = new Lasso_Amazon_Api();
		$log_name   = 'url_issue_check';

		try {
			$query    = $lasso_db->get_query_link_location_and_issue();
			$prepare  = Model::prepare( $query, $check_lasso_id ); // phpcs:ignore
			$link_obj = Model::get_row( $prepare );

			Lasso_Helper::write_log( 'URL ID: ' . $check_lasso_id, $log_name );
			if ( $link_obj ) {
				// ? check link issue
				$lasso_id  = $link_obj->ID;
				$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );
				$link_slug = $lasso_url->target_url;
				$link_slug = Lasso_Amazon_Api::get_amazon_product_url( $link_slug );

				if ( ! $is_skip_request ) {
					if ( LASSO_AMAZON_PRODUCT_TYPE === $lasso_url->link_type ) {
						// ? amazon product
						$amazon_id   = Lasso_Amazon_Api::get_product_id_by_url( $link_slug );
						$amz_product = $amazon_api->fetch_product_info( $amazon_id, true, false, $link_slug );
						if ( 'NotFound' === $amz_product['error_code'] ) {
							$status = '404';
						} else {
							$amz_product = $amz_product['product'];
							$status      = intval( $amz_product['quantity'] ?? 0 ) > 0 ? '200' : '000';
						}
					} elseif ( $lasso_url->extend_product->product_type && $lasso_url->extend_product->product_id ) {
						// ? extend product
						$extend_product_obj = new Lasso_Extend_Product();

						// ? Check by final redirect link if this is affiliate / short link
						if ( ! Lasso_Extend_Product::get_extend_product_type_from_url( $lasso_url->target_url ) ) {
							$lasso_url->target_url = Lasso_Helper::get_redirect_final_target( $lasso_url->target_url );
						}

						$extend_product = $extend_product_obj->fetch_product_info( $lasso_url->target_url, true );

						if ( 'NotFound' === $extend_product['error_code'] ) {
							$status = '404';
						} else {
							$extend_product = $extend_product['product'];
							$status         = intval( $extend_product['quantity'] ?? 0 ) > 0 ? '200' : '000';
						}
					} elseif ( '' === $status ) {
						$status = Lasso_Helper::get_url_status_code( $link_slug );
					}
				}

				$now = gmdate( 'Y-m-d h:i:m' );
				Lasso_Helper::write_log( 'Status: ' . $status . ' | ' . $link_slug, $log_name );

				$issue_resolved    = 0;
				$issue_resolved_dt = $now;
				$is_ignored        = 0;

				if ( 200 === (int) $status ) {
					$issue_resolved = 1;
					$lasso_db->resolve_issue( $lasso_id );
					Lasso_Helper::write_log( 'No Issue Detected', $log_name );
				} else {
					do_action( Setting_Enum::HOOK_ISSUE_DETECTION, $lasso_id, $link_slug, $status );

					// ? issue_resolved null means lasso_url_don't have a entry
					$sql = $lasso_db->get_query_issue_insert();
					$sql = Model::prepare( // phpcs:ignore
						$sql, // phpcs:ignore
						// ? insert
						$lasso_id,
						$now,
						$status,
						$link_slug,
						$issue_resolved,
						$issue_resolved_dt,
						$is_ignored,
						// ? update
						$issue_resolved,
						$issue_resolved_dt,
						$link_slug,
						$status
					);
					$result = Model::query( $sql );

					Lasso_Helper::write_log( 'Result: ' . ( $result ? 'ok' : 'failed: ' . $wpdb->last_error ), $log_name );
				}
			}

			return true;
		} catch ( Exception $e ) {
			Lasso_Helper::write_log( 'Status: ' . $e->getMessage(), $log_name );
			return false;
		}
	}

	/**
	 * Update url everywhere
	 *
	 * @param string $search_url  Search url.
	 * @param string $replace_url Replace url.
	 */
	public function update_url_everywhere( $search_url, $replace_url ) {
		$lasso_db = new Lasso_DB();

		Lasso_Helper::write_log( 'Starting update_url_everywhere for "' . $search_url . '".', 'import_link_replace' );

		$sql       = $lasso_db->get_posts_and_links_with_url( $search_url );
		$all_posts = Model::get_results( $sql );

		foreach ( $all_posts as $single_post ) {
			$post_id          = $single_post->detection_id;
			$link_location_id = $single_post->id;
			$this->update_link_in_post( $post_id, $link_location_id, $replace_url );
			Lasso_Helper::write_log( 'Update Link in Post: ' . $post_id . ', ' . $link_location_id . ', ' . $replace_url, 'import_link_replace' );
		}
	}

	/**
	 * Cron: scan links in post/page
	 */
	public function lasso_check_all_posts_pages() {
		$bg = new Lasso_Process_Build_Link();
		// phpcs:ignore
		// $bg->diff = true;
		$bg->link_database_limit();
	}

	/**
	 * Check a block whether Lasso supports it or not
	 *
	 * @param string $block_name Block name.
	 *
	 * @return bool
	 */
	public static function is_block_supported( $block_name ) {
		// ? Fix the deprecated: preg_match(): Passing null to parameter #2
		if ( ! $block_name ) {
			return true;
		}

		$unsupport_block_pattern = '/' . str_replace( '/', '\/', implode( '|', self::UNSUPPORT_BLOCKS ) ) . '/';

		return 1 !== preg_match( $unsupport_block_pattern, $block_name );
	}

	/**
	 * Get all blocks and children blocks
	 *
	 * @param string $post_content Post content.
	 * @param array  $inner_blocks Inner blocks. Default to empty array.
	 * @param array  $results      Results. Default to empty array.
	 */
	public static function get_all_blocks( $post_content, $inner_blocks = array(), &$results = array() ) {
		// ? old WP version like 4.9 or older ones won't support blocks
		if ( ! function_exists( 'parse_blocks' ) ) {
			return $post_content;
		}

		$blocks = empty( $inner_blocks ) ? parse_blocks( $post_content ) : $inner_blocks;
		foreach ( $blocks as $block ) {
			$inner_block = $block['innerBlocks'];
			if ( $block['blockName'] ) {
				unset( $block['innerBlocks'] );
				$results[] = $block;
			}

			if ( isset( $inner_block ) && is_array( $inner_block ) && ! empty( $inner_block ) ) {
				self::get_all_blocks( $post_content, $inner_block, $results );
			}
		}
	}

	/**
	 * Remove lasso attributes in unsupport blocks (not default WP blocks).
	 * Add class 'lasso-scan-ignore' to a tags.
	 *
	 * @param string $post_content Post content contains blocks.
	 * @param array  $inner_blocks Inner blocks.
	 */
	public function not_scan_links_in_unsupport_blocks( $post_content, $inner_blocks = array() ) {
		// ? old WP version like 4.9 or older ones won't support blocks
		if ( ! function_exists( 'parse_blocks' ) ) {
			return $post_content;
		}

		$blocks = empty( $inner_blocks ) ? parse_blocks( $post_content ) : $inner_blocks;
		foreach ( $blocks as $block ) {
			if ( ! self::is_block_supported( $block['blockName'] ) ) {
				$html_str = $block['innerHTML'];

				$html = new simple_html_dom();
				$html->load( $html_str, true, false );

				$a_tags = $html->find( 'a' ); // ? Find a tags in the html
				foreach ( $a_tags as $index => $a ) {
					$a = apply_filters( self::FILTER_ATAG_BEFORE_SET_IGNORE, $a, $block, $index );

					$a->class = str_replace( 'lasso-scan-ignore', '', $a->class );
					$a->class = trim( $a->class . ' lasso-scan-ignore' );
					$a->class = Lasso_Helper::remove_multiple_spaces( $a->class );
					$a->setAttribute( 'data-lasso-id', null );
					$a->setAttribute( 'data-lasso-name', null );
					$a->setAttribute( 'data-old-href', null );
				}

				$ta_tags = $html->find( 'ta' ); // ? Find ta tags in the html
				foreach ( $ta_tags as $index => $ta ) {
					$ta->class = str_replace( 'lasso-scan-ignore', '', $ta->class );
					$ta->class = trim( $ta->class . ' lasso-scan-ignore' );
					$ta->class = Lasso_Helper::remove_multiple_spaces( $ta->class );
					$ta->setAttribute( 'data-lasso-id', null );
					$ta->setAttribute( 'data-lasso-name', null );
					$ta->setAttribute( 'data-old-href', null );
				}

				$img_tags = $html->find( 'img' ); // ? Find img tags in the html
				foreach ( $img_tags as $index => $img ) {
					$img->class = str_replace( 'lasso-scan-ignore', '', $img->class );
					$img->class = trim( $img->class . ' lasso-scan-ignore' );
					$img->class = Lasso_Helper::remove_multiple_spaces( $img->class );
					$img->setAttribute( 'data-lasso-id', null );
					$img->setAttribute( 'data-lasso-name', null );
					$img->setAttribute( 'data-old-href', null );
				}

				$iframe_tags = $html->find( 'iframe' ); // ? Find iframe tags in the html
				foreach ( $iframe_tags as $index => $iframe ) {
					$iframe->class = str_replace( 'lasso-scan-ignore', '', $iframe->class );
					$iframe->class = trim( $iframe->class . ' lasso-scan-ignore' );
					$iframe->class = Lasso_Helper::remove_multiple_spaces( $iframe->class );
					$iframe->setAttribute( 'data-lasso-id', null );
					$iframe->setAttribute( 'data-lasso-name', null );
					$iframe->setAttribute( 'data-old-href', null );
				}

				$new_post_content = str_replace( (string) $html_str, (string) $html, $post_content );
				$post_content     = '' !== $new_post_content ? $new_post_content : $post_content;
			}

			if ( isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) && ! empty( $block['innerBlocks'] ) ) {
				$post_content = $this->not_scan_links_in_unsupport_blocks( $post_content, $block['innerBlocks'] );
			}

			$post_content = apply_filters( self::FILTER_BLOCK_TO_UPDATE_POST_CONTENT, $post_content, $block );
		}

		return $post_content;
	}

	/**
	 * Scan all links in post/page
	 *
	 * @param array $posts          Array of posts.
	 */
	public function check_all_posts_pages( $posts ) {
		if ( ! is_array( $posts ) ) {
			return;
		}

		$time_start = microtime( true );
		$lasso_db   = new Lasso_DB();

		foreach ( $posts as $key => $post ) {
			if ( is_null( $post ) || ! in_array( $post->post_status, array( 'publish', 'draft' ), true ) ) {
				continue;
			}

			$post_id            = intval( $post->ID );
			$post_content       = $post->post_content;
			$lasso_location_ids = array();

			// ? fix issues of Thrive Architect plugin
			$post_content = Lasso_Helper::get_thrive_plugin_post_content( $post_id, $post_content );

			if ( ! $post_content || empty( $post_content ) ) {
				// ? remove all records in link location table
				$lasso_db->remove_all_link_location_data( $post_id );
				continue;
			}

			$post_content = $this->scan_post_content( $post, $post_content, $lasso_location_ids, true );

			// ? Update new content after has applied lasso settings
			if ( ! empty( $post_content ) && ! is_null( $post_content ) ) {
				$this->update_post_content( $post_id, $post_content );
			}

			$this->scan_acf_fields( $post, $lasso_location_ids );

			$this->delete_link_does_not_exist_in_post( $post_id, $lasso_location_ids );

			$this->delete_metadata_link_location_table_does_not_exist();

			// ? scan custom fields
			$this->scan_custom_fields( $post_id );
		}

		$time_end = microtime( true );
		// ? dividing with 60 will give the execution time in minutes otherwise seconds
		$execution_time = round( $time_end - $time_start, 2 );
		Lasso_Helper::write_log( 'Execution time (seconds): ' . $execution_time, 'scan_post_page' );
	}

	/**
	 * Scan post content
	 *
	 * @param object $post                   WP post.
	 * @param string $post_content           Content to scan.
	 * @param array  $lasso_location_ids     Lasso link location ids.
	 * @param bool   $is_scanning_main_posts Is scanning the main posts. If false, we don't need to scan the elementor post. Default to false.
	 */
	public function scan_post_content( $post, $post_content, &$lasso_location_ids = array(), $is_scanning_main_posts = false ) {
		global $wpdb;

		$lasso_amazon_api = new Lasso_Amazon_Api();
		$lasso_db         = new Lasso_DB();
		$lasso_helper     = new Lasso_Helper();

		$lasso_import_revert_db = Model::get_wp_table_name( LASSO_REVERT_DB );

		$current_date = gmdate( 'Y-m-d H:i:s' );
		$pattern      = get_shortcode_regex( self::SHORTCODE_LIST );
		$site_url     = site_url();
		$post_id      = intval( $post->ID );
		$post_type    = $post->post_type;

		$post_content = apply_filters( self::FILTER_FIX_CONTENT_BEFORE_SCANNING, $post_content );

		// ? ignore other blocks in Gutenberg editor
		$post_content = $this->not_scan_links_in_unsupport_blocks( $post_content );

		// ? Handle blocks
		$this->scan_site_stripe_blocks( $post_id, $post_content, $lasso_location_ids );

		// ? keep break line for "php simple html dom" library
		// ? https://stackoverflow.com/questions/4812691/preserve-line-breaks-simple-html-dom-parser
		$html = new simple_html_dom();
		$html->load( $post_content, true, false );

		$a_tags         = $html->find( 'a' ); // ? Find a tags in the html
		$ta_tags        = $html->find( 'ta' ); // ? Find ta tags in the html (ThirstyAffiliate links)
		$img_tags       = $html->find( 'img' ); // ? Find image tags in the html (SiteStripe images)
		$iframe_tags    = $html->find( 'iframe' ); // ? Find iframe tags in the html (SiteStripe images)
		$keyword_tags   = $html->find( 'keyword' ); // ? Find keyword tags in the html
		$post_permalink = 'post' === $post_type ? get_the_permalink( $post_id ) : $site_url . '/' . get_page_uri( $post_id );

		// ? fix: raw amazon link
		// ? convert raw amazon link to lasso shortcode if it is a Lasso post
		// ? this fix can be removed in the next (2-3) releases
		$div_tags = $html->find( 'div.wp-block-affiliate-plugin-lasso' );
		foreach ( $div_tags as $key => $div ) {
			$div_class   = $div->class ?? '';
			$anchor_text = $div->innertext;

			// ? Lasso blocks
			if ( 'wp-block-affiliate-plugin-lasso' === $div_class && Lasso_Amazon_Api::is_amazon_url( $anchor_text ) ) {
				$amz_id    = Lasso_Amazon_Api::get_product_id_by_url( $anchor_text );
				$lasso_id  = Lasso_Affiliate_Link::get_lasso_post_id_by_url( $anchor_text, 0 );
				$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );
				if ( $lasso_url->lasso_id > 0 && $amz_id === $lasso_url->amazon->amazon_id ) {
					$lasso_shortcode = '[lasso id="' . $lasso_url->lasso_id . '" ref="' . $lasso_url->slug . '"]';
					$div->innertext  = $lasso_shortcode;
				}
			}
		}

		// ? Handle keywords
		$this->scan_keyword_tags( $post_id, $keyword_tags );

		// ? Handle urls
		$this->scan_a_tags( $post_id, $is_scanning_main_posts, $a_tags, $lasso_location_ids );

		// ? Handle ta tags
		$this->scan_ta_tags( $post_id, $is_scanning_main_posts, $ta_tags, $lasso_location_ids );

		// ? Handle sitestripe data
		$this->scan_site_stripe_data_image( $post_id, $img_tags, $lasso_location_ids );
		$this->scan_site_stripe_data_iframe( $post_id, $iframe_tags, $lasso_location_ids );
		$this->scan_site_stripe_data_wp_recipe_maker( $post_id, $post_content, $lasso_location_ids );
		$this->scan_site_stripe_data_elementor( $post_id, $lasso_location_ids );
		$this->scan_site_stripe_data_beaver_builder( $post_id, $lasso_location_ids );

		if ( $is_scanning_main_posts ) {
			// ? Fix Lasso widget shortcode with Elementor plugin
			$elementor_data = Lasso_Elementor::get_elementor_data( $post_id );
			$elementor_data = $elementor_data ? $elementor_data : array();
		}

		// ? Handle Shortcodes
		// ? Wayy faster if we first check if there is even a bracket in the content
		$post_content          = $html;
		$post_content          = str_replace( '[lasso rel="', '[lasso ref="', $post_content );
		$post_content          = str_replace( 'data-sheets-value="{&quot;1&quot;:2,&quot;2&quot;:&quot;[lasso ref="', 'data-sheets-value=""', $post_content );
		$gutenberg_flag        = false;
		$elementor_ids_scanned = array();

		$post_content = self::switch_aawp_block_to_shortcode( $post_id, $post_content, $lasso_location_ids );

		if ( preg_match_all( '~' . $pattern . '~s', $post_content, $matches ) && array_key_exists( 2, $matches ) ) {
			// ? shortcode is being used
			foreach ( $matches[0] as $key => $code ) {
				$is_shortcode_encode     = strpos( $code, '&quot;' ) !== false;
				$is_shortcode_addslashes = strpos( $code, '=\u0022' ) !== false || strpos( $code, '=u0022' ) !== false;

				$display_type     = Lasso_Link_Location::DISPLAY_TYPE_SINGLE;
				$link_type        = Lasso_Link_Location::LINK_TYPE_EXTERNAL;
				$link_slug        = $is_shortcode_addslashes ? $code : stripcslashes( $code );
				$link_slug        = $is_shortcode_encode ? html_entity_decode( $link_slug ) : $link_slug;
				$detection_slug   = $post_permalink;
				$link_slug_domain = null;
				$anchor_text      = null;
				$tracking_id      = null;
				$product_id       = null;
				$no_follow        = 'true';
				$new_window       = 'false';

				$shortcode_type  = $matches[2][ $key ];
				$content_between = trim( $matches[5][ $key ] );

				$attributes = $lasso_helper->get_attributes( $link_slug, $is_shortcode_addslashes );
				// ? AAWP plugin
				if ( in_array( $shortcode_type, array( 'amazon', 'aawp' ), true ) && isset( $attributes['description'] ) ) {
					$regex_desc         = '/\s+description="(.*?)"\s?(?:\s+\w+=?|(?:\[\/aawp)?\])/m';
					$regex_replace_desc = '/(\s+description=").*?("\s?(?:\s+\w+=?|(?:\[\/aawp)?\]))/m';
					preg_match_all( $regex_desc, $link_slug, $matches_desc, PREG_SET_ORDER );
					// ? Get first group regex
					$desc_base64   = base64_encode( $matches_desc[0][1] ); // phpcs:ignore
					$new_link_slug = preg_replace( $regex_replace_desc, '$1' . $desc_base64 . '$2', $link_slug );
					$attributes    = $lasso_helper->get_attributes( $new_link_slug );
				}

				$attributes['ref']      = $attributes['ref'] ?? null;
				$attributes['category'] = $attributes['category'] ?? null;
				$attributes['type']     = $attributes['type'] ?? null;

				// ? Convert attribute fields="hide" value to field="hide" for stop conflict with AAWP.
				if ( isset( $attributes['fields'] ) && 'hide' === $attributes['fields'] ) {
					$attributes['field'] = 'hide';
					unset( $attributes['fields'] );
				}

				$link_id = intval( $attributes['link_id'] ?? 0 );
				$id      = intval( $attributes['id'] ?? 0 );

				$type            = trim( $attributes['type'] ?? '' );
				$sitestripe_scan = trim( $attributes['sitestripe_scan'] ?? '' );

				$lasso_link_location    = new Lasso_Link_Location( $link_id );
				$is_ll_id_duplicate     = in_array( $link_id, $lasso_location_ids ) && 'true' !== $sitestripe_scan; // phpcs:ignore
				$is_ll_id_exist_in_post = intval( $lasso_link_location->get_detection_id() ) === $post_id; // ? id belongs to this post id
				$is_ll_id_valid         = ( $link_id > 0 && ! $lasso_link_location->get_id() ) || $is_ll_id_exist_in_post;
				// ? check ll id exists in another post or not
				// ? check lasso-is is duplicated
				unset( $attributes['sitestripe_scan'] );
				if ( ! $is_ll_id_valid || $is_ll_id_duplicate ) {
					$link_id = 0;
				}

				if ( in_array( $shortcode_type, array( 'easyazon_link', 'easyazon-link', 'simpleazon-link' ), true ) ) {
					// ? EasyAzon plugin - Link
					$product_id  = $attributes['identifier'] ?? $attributes['asin'] ?? '';
					$product_url = Lasso_Amazon_Api::get_default_product_domain( $product_id );
					$tracking_id = $attributes['tag'] ?? '';

					$url_details = $lasso_db->get_url_details_by_product_id( $product_id, Lasso_Amazon_Api::PRODUCT_TYPE, false, $product_url );
					$id          = $url_details ? $url_details->lasso_id : $id;
					$anchor_text = $content_between;

					if ( isset( $attributes['keywords'] ) && ! $attributes['keywords'] && $content_between ) {
						$attributes['keywords'] = $content_between;
					}

					$link_type    = Lasso_Link_Location::LINK_TYPE_EXTERNAL;
					$display_type = Lasso_Link_Location::DISPLAY_TYPE_EASYAZON;

					$attributes['type'] = 'link';
				} elseif ( in_array( $shortcode_type, array( 'easyazon_image', 'easyazon-image', 'easyazon-image-link', 'simpleazon-image' ), true ) ) {
					// ? EasyAzon plugin - Image
					$product_id  = $attributes['identifier'] ?? $attributes['asin'] ?? '';
					$product_url = Lasso_Amazon_Api::get_default_product_domain( $product_id );
					$tracking_id = $attributes['tag'] ?? '';
					$url_details = $lasso_db->get_url_details_by_product_id( $product_id, Lasso_Amazon_Api::PRODUCT_TYPE, false, $product_url );
					$id          = $url_details ? $url_details->lasso_id : $id;
					$anchor_text = $content_between;

					$link_type    = Lasso_Link_Location::LINK_TYPE_EXTERNAL;
					$display_type = Lasso_Link_Location::DISPLAY_TYPE_EASYAZON;

					$attributes['type'] = 'image';
				} elseif ( in_array( $shortcode_type, array( 'easyazon_cta', 'easyazon-cta' ), true ) ) {
					// ? EasyAzon plugin - Button
					$product_id  = $attributes['identifier'] ?? $attributes['asin'] ?? '';
					$product_url = Lasso_Amazon_Api::get_default_product_domain( $product_id );
					$tracking_id = $attributes['tag'] ?? '';
					$url_details = $lasso_db->get_url_details_by_product_id( $product_id, Lasso_Amazon_Api::PRODUCT_TYPE, false, $product_url );
					$id          = $url_details ? $url_details->lasso_id : $id;
					$anchor_text = $content_between;

					$link_type    = Lasso_Link_Location::LINK_TYPE_EXTERNAL;
					$display_type = Lasso_Link_Location::DISPLAY_TYPE_EASYAZON;

					$attributes['type'] = 'button';
				} elseif ( in_array( $shortcode_type, array( 'easyazon_block', 'easyazon-block', 'easyazon_infoblock' ), true ) ) {
					// ? EasyAzon plugin - Display Box
					$product_id  = $attributes['identifier'] ?? $attributes['asin'] ?? '';
					$product_url = Lasso_Amazon_Api::get_default_product_domain( $product_id );
					$tracking_id = $attributes['tag'] ?? '';
					$url_details = $lasso_db->get_url_details_by_product_id( $product_id, Lasso_Amazon_Api::PRODUCT_TYPE, false, $product_url );
					$id          = $url_details ? $url_details->lasso_id : $id;
					$anchor_text = $content_between;

					$link_type    = Lasso_Link_Location::LINK_TYPE_EXTERNAL;
					$display_type = Lasso_Link_Location::DISPLAY_TYPE_EASYAZON;

					$attributes['type'] = 'single';
				}

				// ? AmaLinks Pro plugin
				if ( 'amalinkspro' === $shortcode_type ) {
					$asin        = $attributes['asin'] ?? '';
					$amazon_link = $attributes['apilink'] ?? '';
					$product_id  = Lasso_Amazon_Api::get_product_id_by_url( $amazon_link );
					$product_id  = empty( $product_id ) ? $asin : $product_id;
					$product_url = Lasso_Amazon_Api::get_default_product_domain( $product_id );
					$tracking_id = Lasso_Amazon_Api::get_amazon_tracking_id_by_url( $amazon_link );
					$url_details = $lasso_db->get_url_details_by_product_id( $product_id, Lasso_Amazon_Api::PRODUCT_TYPE, false, $product_url );
					$id          = $url_details->lasso_id ?? 0;
					$anchor_text = $content_between;

					$link_type    = Lasso_Link_Location::LINK_TYPE_EXTERNAL;
					$display_type = Lasso_Link_Location::DISPLAY_TYPE_AMALINK;

					// ? update amazon link in the shortcode
					$attributes['apilink'] = Lasso_Amazon_Api::get_amazon_product_url( $amazon_link );
					$ama_type              = $attributes['type'] ?? '';
					switch ( $ama_type ) {
						case 'text-link':
							$ama_type = 'link';
							break;
						case 'image-link':
							$ama_type = 'image';
							break;
						case 'cta-btn-css':
							$ama_type = 'button';
							break;
						case 'showcase':
							$ama_type = 'single';
							break;

						default:
							$ama_type = 'link';
							break;
					}
				}

				// ? Thirsty Affiliate plugin
				if ( 'thirstylink' === $shortcode_type ) {
					$ids    = $attributes['ids'] ?? '';
					$linkid = $attributes['linkid'] ?? '';

					// ? get the link ID
					if ( '' === $linkid ) {
						$ids        = isset( $attributes['ids'] ) ? array_map( 'intval', explode( ',', $ids ) ) : array();
						$count      = count( $ids );
						$key        = 1 === $count ? 0 : wp_rand( 0, $count - 1 );
						$thirsty_id = $ids[ $key ];
					} else {
						$thirsty_id = $linkid;
					}
					$thirsty_id = (int) $thirsty_id;
					$post_type  = get_post_type( $thirsty_id );
					if ( LASSO_POST_TYPE === $post_type ) {
						$id = $thirsty_id;
					} else {
						$redirect_url = get_post_meta( $thirsty_id, '_ta_destination_url', true );
						if ( Lasso_Amazon_Api::is_amazon_url( $redirect_url ) ) {
							$product_id        = Lasso_Amazon_Api::get_product_id_by_url( $redirect_url );
							$product_url       = Lasso_Amazon_Api::get_default_product_domain( $product_id );
							$tracking_id       = Lasso_Amazon_Api::get_amazon_tracking_id_by_url( $redirect_url );
							$amazon_product_db = $lasso_amazon_api->get_amazon_product_from_db( $product_id );
							$url_details       = $lasso_db->get_url_details_by_product_id( $product_id, Lasso_Amazon_Api::PRODUCT_TYPE, false, $product_url );
							$id                = $amazon_product_db && $url_details ? $url_details->lasso_id : $id;
						}
					}

					$link_type    = Lasso_Link_Location::LINK_TYPE_EXTERNAL;
					$display_type = Lasso_Link_Location::DISPLAY_TYPE_THIRSTYLINK;
				}

				$aawp_table_id          = 0;
				$aawp_table_revert_item = null;
				// ? AAWP plugin
				if ( in_array( $shortcode_type, array( 'amazon', 'aawp' ), true ) ) {
					// ? fix single quote in shortcode [amazon link='B07M6RS2LC']
					// ? repalce single quote by double quote [amazon link="B07M6RS2LC"]
					$attributes = array_map(
						function( $v ) {
							if ( $v ) {
								$v = ltrim( $v, "'" );
								$v = rtrim( $v, "'" );
							}
							return $v;
						},
						$attributes
					);

					// ? get Amazon product id
					$product_id  = $attributes['link'] ?? $attributes['box'] ?? $attributes['fields'] ?? $product_id;
					$product_url = Lasso_Amazon_Api::get_default_product_domain( $product_id );

					if ( isset( $attributes['box'] ) ) {
						$attributes['box'] = str_replace( '/span', '', $attributes['box'] );
					}

					// ? Fix case AAWP include description HTML format in shortcode [amazon box="ASIN" title="Title" description="<ul><li>Feature 1</li><li>Feature 2</li><li>Feature 3l</li></ul> Some additional text to describe the product"]
					if ( isset( $attributes['description'] ) ) {
						$attributes['description'] = base64_decode( $attributes['description'] ); // phpcs:ignore
					}

					$lasso_post_id = $lasso_db->get_lasso_id_by_product_id_and_type( $product_id, Lasso_Amazon_Api::PRODUCT_TYPE, $product_url );
					$id            = $lasso_post_id ? $lasso_post_id : $id;
					$tracking_id   = $attributes['tracking_id'] ?? '';
					$aawp_table_id = $attributes['table'] ?? 0;

					$link_type    = Lasso_Link_Location::LINK_TYPE_EXTERNAL;
					$display_type = Lasso_Link_Location::DISPLAY_TYPE_AAWP;
				}

				// ? Earnist plugin
				if ( in_array( $shortcode_type, array( 'earnist', 'earnist_link' ), true ) ) {
					$sql     = "SELECT lasso_id FROM `$lasso_import_revert_db` WHERE post_data = '%d'";
					$prepare = Model::prepare( $sql, $id ); // phpcs:ignore
					$result  = Model::get_results( $prepare );
					$id      = isset( $result[0] ) ? $result[0]->lasso_id : $id;

					$link_type    = Lasso_Link_Location::LINK_TYPE_EXTERNAL;
					$display_type = Lasso_Link_Location::DISPLAY_TYPE_EARNIST;
					$anchor_text  = 'earnist_link' === $shortcode_type ? $content_between : null;
				}

				// ? Lasso plugin
				if ( 'lasso' === $shortcode_type ) {
					// ? old shortcode is AAWP plugin shortcode
					$product_id = $attributes['link'] ?? $attributes['box'] ?? $attributes['fields'] ?? $product_id;
					if ( ! empty( $product_id ) && LASSO_POST_TYPE !== get_post_type( intval( $id ) ) ) {
						$product_url   = Lasso_Amazon_Api::get_default_product_domain( $product_id );
						$lasso_post_id = $lasso_db->get_lasso_id_by_product_id_and_type( $product_id, Lasso_Amazon_Api::PRODUCT_TYPE, $product_url );
						$id            = $lasso_post_id ? $lasso_post_id : $id;
						$tracking_id   = $attributes['tracking_id'] ?? '';
					}

					// ? old shortcode is AAWP table plugin shortcode and imported to Lasso
					if ( isset( $attributes['table'] ) && isset( $attributes['type'] ) && 'table' === $attributes['type'] && isset( $attributes['id'] ) ) {
						$aawp_table_id      = intval( $attributes['table'] );
						$lasso_tbl_model    = new Table_Details();
						$lasso_revert_model = new Revert();
						$lasso_tbl          = $lasso_tbl_model->get_one( $attributes['id'] );
						if ( ! $lasso_tbl->get_id() ) {
							$revert_obj       = $lasso_revert_model->get_one_by_col( 'post_data', $aawp_table_id );
							$tbl_id           = $revert_obj->get_lasso_id();
							$attributes['id'] = $tbl_id ? $tbl_id : $attributes['id'];
						}
					}

					// ? fix raw amazon url in post content, not in gutenberg editor
					$amz_url = trim( $attributes['amazon_url'] ?? '' );
					if ( 0 === $id && $amz_url ) {
						$product_id = Lasso_Amazon_Api::get_product_id_country_by_url( $amz_url );
						$id         = $lasso_db->get_lasso_id_by_product_id_and_type( $product_id, Lasso_Amazon_Api::PRODUCT_TYPE, $amz_url );
					}

					// ? fix raw geni url in post content, not in gutenberg editor
					$geni_url = trim( $attributes['geni_url'] ?? '' );
					if ( 0 === $id && $geni_url ) {
						$temp_lasso_id = $lasso_db->get_lasso_id_by_geni_url( $geni_url );
						$id            = $temp_lasso_id ? $temp_lasso_id : $id;
					}
				}

				$id                          = LASSO_POST_TYPE === get_post_type( intval( $id ) ) && ! $aawp_table_id ? $id : 0;
				$lasso_url                   = Lasso_Affiliate_Link::get_lasso_url( $id );
				$is_post_imported_into_lasso = $aawp_table_id ? Lasso_Import::is_post_imported_into_lasso( $aawp_table_id, 'aawp_table' )
					: Lasso_Import::is_post_imported_into_lasso( $lasso_url->lasso_id );
				if ( $lasso_url->lasso_id > 0 ) { // ? Lasso post id
					$product_id = $lasso_url->amazon->amazon_id;

					$attributes['id']  = $lasso_url->lasso_id;
					$attributes['ref'] = $lasso_url->slug;

					if ( $attributes['src'] ?? '' ) {
						$attributes['identifier'] = $lasso_url->amazon->amazon_id;
					}

					// ? AAWP attributes
					$aawp_product_id = explode( '_', $lasso_url->amazon->amazon_id )[0];
					if ( $lasso_url->amazon->amazon_id && ( $attributes['link'] ?? '' ) ) {
						$attributes['link'] = $aawp_product_id;
					} elseif ( $lasso_url->amazon->amazon_id && ( $attributes['box'] ?? '' ) ) {
						$attributes['box'] = $aawp_product_id;
					} elseif ( $lasso_url->amazon->amazon_id && ( $attributes['fields'] ?? '' ) ) {
						$attributes['fields'] = $aawp_product_id;
					}

					$link_type = Lasso_Link_Location::LINK_TYPE_LASSO;
				}

				// ? If AAWP table already imported => set the lasso table attributes
				if ( in_array( $shortcode_type, array( 'aawp', 'amazon' ), true ) && $aawp_table_id && $is_post_imported_into_lasso ) {
					$aawp_table_revert_item = ( new Revert() )->get_revert_data( $aawp_table_id, 'aawp', '[amazon table="' . $aawp_table_id . '"]' );
					$attributes['id']       = $aawp_table_revert_item->get_lasso_id(); // ? Lasso table attribute
					$attributes['type']     = 'table'; // ? Lasso table attribute

					$link_type    = Lasso_Link_Location::LINK_TYPE_LASSO;
					$display_type = Lasso_Link_Location::DISPLAY_TYPE_TABLE;
				}

				if ( 'lasso' === $shortcode_type && $type ) {
					switch ( $type ) {
						case 'button':
							$display_type = Lasso_Link_Location::DISPLAY_TYPE_BUTTON;
							break;
						case 'image':
							$display_type = Lasso_Link_Location::DISPLAY_TYPE_IMAGE;
							break;
						case 'grid':
							$display_type = Lasso_Link_Location::DISPLAY_TYPE_GRID;
							break;
						case 'list':
							$display_type = Lasso_Link_Location::DISPLAY_TYPE_LIST;
							break;
						case 'gallery':
							$display_type = Lasso_Link_Location::DISPLAY_TYPE_GALLERY;
							break;
						case 'table':
							$display_type = Lasso_Link_Location::DISPLAY_TYPE_TABLE;
							break;
						default:
							$display_type = Lasso_Link_Location::DISPLAY_TYPE_SINGLE;
							break;
					}

					$link_type = Lasso_Link_Location::LINK_TYPE_LASSO;
				}

				// ? Insert into link locations table
				if ( $gutenberg_flag ) {
					// ? fix the link_id in shortcode and gutenberg shortcode are different
					$link_location_id = $gutenberg_flag;
					$gutenberg_flag   = false;
				} else {
					$shortcode    = str_replace( '\u0022', '"', $link_slug );
					$shortcode    = str_replace( '\u0026', '&', $link_slug );
					$replace_with = $shortcode;

					$link_location_id = $this->write_link_locations(
						$link_type,
						$display_type,
						$anchor_text,
						$id,
						$shortcode,
						$link_slug_domain,
						$post_id,
						$detection_slug,
						$tracking_id,
						$product_id,
						$no_follow,
						$new_window,
						$current_date,
						$link_id
					);
				}

				if ( $link_location_id > 0 ) {
					$lasso_location_ids[] = $link_location_id;
					$lasso_link_location  = new Lasso_Link_Location( $link_location_id );
					$original_link        = $lasso_link_location->get_original_link_slug();
					$original_link        = trim( $original_link );

					// ? if original_link is not a shortcode, set it to empty
					// ? this won't replace a shortcode with a link and show a raw link in post content
					$original_link = strpos( $original_link, '[' ) !== false && strpos( $original_link, ']' ) ? $original_link : '';

					// ? add link location id to the shortcode
					$attributes['link_id'] = $link_location_id;
					$final_sc_content      = array_map(
						function( $key, $value ) {
							if ( null === $value ) {
								return '';
							}
							return $key . '="' . $value . '"';
						},
						array_keys( $attributes ),
						array_values( $attributes )
					);
					$final_sc_content      = array_filter(
						$final_sc_content,
						function( $value ) {
							return ! empty( $value );
						}
					);

					$closing_tag  = false !== strpos( $code, '/]' ) ? '/]' : ']';
					$replace_with = "[$shortcode_type " . implode( ' ', $final_sc_content ) . $closing_tag;

					// ? fix shortcode with content
					if ( ! empty( $content_between ) ) {
						$replace_with = "[$shortcode_type " . implode( ' ', $final_sc_content ) . ']' . $content_between . "[/$shortcode_type]";
					}

					$invalid_lasso_shortcode   = 0 === $lasso_url->lasso_id && 'lasso' === $shortcode_type;
					$is_lasso_single_shortcode = ! in_array( $type, array( 'grid', 'list', 'gallery', 'table' ), true );

					if ( $aawp_table_id ) {
						if ( $is_post_imported_into_lasso ) { // ? Lasso post id, convert other shortcode to lasso shortcode
							$shortcode_type = 'lasso';
							$replace_with   = "[$shortcode_type " . implode( ' ', $final_sc_content ) . ']';
						} elseif ( 'lasso' === $shortcode_type && ! $is_post_imported_into_lasso ) { // ? convert current shortcode to original shortcode if it is not imported to Lasso
							$custom_attributes = array(
								'link_id' => $link_location_id,
							);
							$old_shortcode     = $original_link ? $original_link : $replace_with;
							$replace_with      = Lasso_Shortcode::covert_lasso_shortcode_to_original_shortcode( $old_shortcode, $custom_attributes, $replace_with, $is_post_imported_into_lasso );
						}
					} elseif ( ( $lasso_url->lasso_id > 0 && $is_post_imported_into_lasso && 'lasso' !== $shortcode_type ) // ? Lasso post id, convert other shortcode to lasso shortcode
							|| ( ! $is_lasso_single_shortcode ) // ? fix lasso list/grid/gallery shortcode is replaced
					) {
						$shortcode_type = 'lasso';
						$replace_with   = "[$shortcode_type " . implode( ' ', $final_sc_content ) . ']';
					} elseif ( $is_lasso_single_shortcode
						&& (
							$invalid_lasso_shortcode
							|| ( $lasso_url->lasso_id > 0 && ! $is_post_imported_into_lasso )
						)
					) { // ? convert current shortcode to original shortcode if it is not imported to Lasso
						$custom_attributes = array(
							'link_id' => $link_location_id,
						);
						if ( $lasso_url->lasso_id > 0 ) {
							$custom_attributes['id'] = $lasso_url->lasso_id;
						}

						// ? fix easyazon_link keywords are empty
						if ( isset( $attributes['keywords'] ) ) {
							$custom_attributes['keywords'] = $content_between;
						}

						$old_shortcode = $original_link ? $original_link : $replace_with;

						$replace_with = Lasso_Shortcode::covert_lasso_shortcode_to_original_shortcode( $old_shortcode, $custom_attributes, $replace_with, $is_post_imported_into_lasso );
					}

					if ( $is_shortcode_addslashes ) {
						// ? shortcode in Gutenberg blocks
						$replace_with = "[$shortcode_type " . implode( ' ', $final_sc_content ) . ']';

						// ? fix shortcode with content
						if ( ! empty( $content_between ) ) {
							$replace_with = "[$shortcode_type " . implode( ' ', $final_sc_content ) . ']' . $content_between . "[/$shortcode_type]";
						}

						$replace_with = str_replace( '"', '\u0022', $replace_with );
						$replace_with = str_replace( '&', '\u0026', $replace_with );

						// phpcs:ignore $post_content = str_replace( $code, $replace_with, $post_content );
						$pos = strpos( $post_content, $code );
						if ( false !== $pos ) {
							$post_content = substr_replace( $post_content, $replace_with, $pos, strlen( $code ) );
						}

						$gutenberg_flag = $link_location_id;
						continue;
					} else {
						$post_content = $this->fix_shortcode_in_gutenberg( $link_id, $replace_with, $post_content );

						// ? make sure $replace_with is shortcode content. It is not Block content
						if ( '' !== $replace_with && strpos( $replace_with, '<!-- wp' ) === false ) {
							if ( 'lasso' === $shortcode_type ) {
								$replace_with = str_replace( '<', '&lt;', $replace_with );
								$replace_with = str_replace( '>', '&gt;', $replace_with );
							} else {
								$replace_with = str_replace( '&lt;', '<', $replace_with );
								$replace_with = str_replace( '&gt;', '>', $replace_with );
							}
						}
					}

					// ? Fix shortcode in TablePress plugin
					if ( 'tablepress_table' === $post_type ) {
						$replace_with = addslashes( $replace_with );
					}

					// ? Fix shortcode in span tag contains data-sheets-value attribute
					if ( $is_shortcode_encode ) {
						$replace_with = htmlentities( $replace_with );
						$replace_with = addslashes( $replace_with );
					}

					// phpcs:ignore $post_content = str_replace( $code, $replace_with, $post_content );
					$pos = strpos( $post_content, $code );
					if ( false !== $pos ) {
						$post_content = substr_replace( $post_content, $replace_with, $pos, strlen( $code ) );

						if ( $is_scanning_main_posts ) {
							// ? Fix shortcode with Elementor plugin
							Lasso_Elementor::fix_shortcode_elementor( $post_id, $code, $replace_with, $elementor_ids_scanned, $elementor_data );
						}
					}

					if ( Lasso_Link_Location::DISPLAY_TYPE_TABLE === $display_type && isset( $attributes['id'] ) ) {
						$ll = new Link_Locations();
						$ll->insert_meta_by_id( $link_location_id, Lasso_Link_Location::DISPLAY_TYPE_TABLE, $attributes['id'] );
					}
				}
			}

			if ( $is_scanning_main_posts ) {
				// ? Update elementor data
				Lasso_Elementor::update_elementor_data( $post_id, $elementor_data );
			}
		}

		// ? fix lasso block in gutenberg editor
		$post_content = $this->fix_lasso_block_in_gutenberg( $post_content );

		return $post_content;
	}

	/**
	 * Scan keyword tags
	 *
	 * @param int   $post_id                Post ID.
	 * @param array $keyword_tags           Keyword tags.
	 */
	public function scan_keyword_tags( $post_id, &$keyword_tags ) {
		global $wpdb;

		$lasso_tracked_keywords  = Model::get_wp_table_name( LASSO_TRACKED_KEYWORDS );
		$lasso_keyword_locations = Model::get_wp_table_name( LASSO_KEYWORD_LOCATIONS );

		foreach ( $keyword_tags as $key => $keyword ) {
			$keyword_id  = $keyword->getAttribute( 'data-keyword-id' ) ?? '';
			$anchor_text = $keyword->innertext ?? '';

			$query = "
					select $lasso_keyword_locations.lasso_id, count($lasso_keyword_locations.id) as count
					from $lasso_tracked_keywords
					left join $lasso_keyword_locations
					on $lasso_tracked_keywords.lasso_id = $lasso_keyword_locations.lasso_id 
						and $lasso_tracked_keywords.keyword = $lasso_keyword_locations.keyword
					where $lasso_keyword_locations.id > 0 
						and $lasso_keyword_locations.id = $keyword_id
						and $lasso_keyword_locations.keyword = '" . addslashes( $anchor_text ) . "'
						and $lasso_keyword_locations.detection_id = $post_id
					group by $lasso_keyword_locations.lasso_id
				";
			$row   = Model::get_row( $query );
			if ( ! $row ) {
				$keyword_row = Model::get_row( "select * from $lasso_tracked_keywords where keyword = '" . addslashes( $anchor_text ) . "'" );
				if ( $keyword_row ) {
					$wpdb->insert( // phpcs:ignore
						$lasso_keyword_locations,
						array(
							'lasso_id'     => "$keyword_row->lasso_id",
							'keyword'      => "$anchor_text",
							'detection_id' => "$post_id",
						)
					);
					$keyword_location_id = $wpdb->insert_id;
					$keyword->setAttribute( 'data-keyword-id', $keyword_location_id );
				} else {
					$keyword = $anchor_text;
				}
			}
		}
	}

	/**
	 * Scan ta tags
	 *
	 * @param int   $post_id                Post ID.
	 * @param bool  $is_scanning_main_posts Is scanning main posts.
	 * @param array $ta_tags                TA tags.
	 * @param array $lasso_location_ids     Lasso location ids.
	 */
	public function scan_ta_tags( $post_id, $is_scanning_main_posts, &$ta_tags, &$lasso_location_ids ) {
		$lasso_amazon_api = new Lasso_Amazon_Api();

		$is_ta_deactivated = ! isset( $GLOBALS['thirstyaffiliates'] ) || ! class_exists( 'ThirstyAffiliates' ) || ! function_exists( 'ThirstyAffiliates' );
		$site_url          = site_url();
		$current_date      = gmdate( 'Y-m-d H:i:s' );
		$post_type         = get_post_type( $post_id );
		$post_permalink    = 'post' === $post_type ? get_the_permalink( $post_id ) : $site_url . '/' . get_page_uri( $post_id );

		foreach ( $ta_tags as $key => $ta ) {
			// ? ignore ta tags have class 'lasso-scan-ignore' and remove this class name
			$ta_class = $ta->class ?? '';
			if ( strpos( $ta_class, 'lasso-scan-ignore' ) !== false ) {
				$new_class = trim( str_replace( 'lasso-scan-ignore', '', $ta_class ) );
				$ta->class = '' === $new_class ? null : $new_class;
				continue;
			}

			$tag_id   = $ta->linkid ?? '';
			$tag_href = $ta->href ?? '';

			$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $tag_id );
			$lasso_id  = $lasso_url->lasso_id;

			$href        = $ta->href ?? '';
			$nofollow    = $ta->rel; // ? Prevent search engines follow the url: nofollow
			$target      = $ta->target; // ? Open the url in a new tab: _blank
			$anchor_text = $ta->innertext;

			$a_lasso_id = $ta->getAttribute( 'data-lasso-id' );
			$a_lasso_id = intval( $a_lasso_id );
			$a_lasso_id = ( $a_lasso_id && $a_lasso_id > 0 ) ? $a_lasso_id : 0;

			$lasso_link_location    = new Lasso_Link_Location( $a_lasso_id );
			$is_ll_id_duplicate     = in_array( $a_lasso_id, $lasso_location_ids ); // phpcs:ignore
			$is_ll_id_exist_in_post = intval( $lasso_link_location->get_detection_id() ) === $post_id; // ? id belongs to this post id
			$is_ll_id_valid         = ( $a_lasso_id > 0 && ! $lasso_link_location->get_id() ) || $is_ll_id_exist_in_post;
			// ? check ll id exists in another post or not
			// ? check lasso-is is duplicated
			if ( ! $is_ll_id_valid || $is_ll_id_duplicate ) {
				$a_lasso_id = 0;
			}

			// ? Prepare to store data into db
			$link_type        = $this->get_link_type( $lasso_id, $href );
			$display_type     = 'link';
			$link_slug        = $href;
			$link_slug_domain = Lasso_Helper::get_base_domain( $link_slug );
			$detection_slug   = $post_permalink;
			$tracking_id      = $lasso_amazon_api->get_amazon_tracking_id_by_url( $href );
			$product_id       = Lasso_Amazon_Api::get_product_id_by_url( $href );
			$no_follow        = 'nofollow' === $nofollow ? 'true' : 'false';
			$new_window       = '_blank' === $target ? 'true' : 'false';

			// ? Store data into db
			$link_location_id = $this->write_link_locations(
				$link_type,
				$display_type,
				$anchor_text,
				$lasso_id,
				$link_slug,
				$link_slug_domain,
				$post_id,
				$detection_slug,
				$tracking_id,
				$product_id,
				$no_follow,
				$new_window,
				$current_date,
				$a_lasso_id
			);

			if ( $link_location_id > 0 ) {
				$lasso_location_ids[] = $link_location_id;
				$ta->setAttribute( 'data-lasso-id', $link_location_id );
			}

			// ? TA plugin is deactivated
			if ( $lasso_id > 0 ) {
				$rel_attr        = $lasso_url->enable_nofollow ? 'rel="nofollow"' : '';
				$target_attr     = 'target="' . $lasso_url->html_attribute->target . '"';
				$lasso_id_attr   = 'data-lasso-id="' . ( $link_location_id > 0 ? $link_location_id : $lasso_id ) . '"';
				$lasso_name_attr = 'data-lasso-name="' . $lasso_url->name . '"';
				$href_attr       = 'href="' . $lasso_url->public_link . '"';
				$ta->outertext   = '<a ' . $href_attr . ' ' . $lasso_id_attr . ' ' . $lasso_name_attr . ' ' . $target_attr . ' ' . $rel_attr . '>' . $ta->innertext . '</a>';
			} elseif ( $is_ta_deactivated ) {
				// ? not lasso post -> convert <ta> tags to <a> tags
				$lasso_id_attr = 'data-lasso-id="' . ( $link_location_id > 0 ? $link_location_id : $tag_id ) . '"';
				$target_attr   = 'target="_blank"';
				$href_attr     = 'href="' . $tag_href . '"';
				$ta->outertext = '<a ' . $href_attr . ' ' . $lasso_id_attr . ' ' . $target_attr . '>' . $ta->innertext . '</a>';
			}
		}
	}

	/**
	 * Scan a tags
	 *
	 * @param int   $post_id                Post ID.
	 * @param bool  $is_scanning_main_posts Is scanning main posts.
	 * @param array $a_tags                 A tags.
	 * @param array $lasso_location_ids     Lasso location ids.
	 */
	public function scan_a_tags( $post_id, $is_scanning_main_posts, &$a_tags, &$lasso_location_ids ) {
		$lasso_db         = new Lasso_DB();
		$lasso_amazon_api = new Lasso_Amazon_Api();

		$site_url          = site_url();
		$current_date      = gmdate( 'Y-m-d H:i:s' );
		$post_type         = get_post_type( $post_id );
		$post_permalink    = 'post' === $post_type ? get_the_permalink( $post_id ) : $site_url . '/' . get_page_uri( $post_id );
		$rewrite_slug      = Lasso_Helper::get_rewrite_slug();
		$rewrite_slug_preg = Lasso_Helper::get_rewrite_slug( true );

		// ? Handle urls
		foreach ( $a_tags as $key => $a ) {
			if ( ! $a->href ) {
				continue;
			}

			$a = apply_filters( self::FILTER_ATAG_AT_FIRST, $a, $site_url );

			// ? Fix links in TablePress plugin
			if ( 'tablepress_table' === $post_type ) {
				$all_attributes = $a->getAllAttributes();
				foreach ( $all_attributes as $name => $value ) {
					$new_name  = str_replace( '\"', '', $name );
					$new_value = str_replace( '\"', '', $value );
					$a->setAttribute( $new_name, $new_value );

					if ( strpos( $name, '\"' ) !== false ) {
						$a->setAttribute( $name, null );
					}
				}
			}

			// ? ignore a tags have class 'lasso-scan-ignore' and remove this class name
			$a_class = $a->class ?? '';
			if ( strpos( $a_class, 'lasso-scan-ignore' ) !== false ) {
				$new_class = trim( str_replace( 'lasso-scan-ignore', '', $a_class ) );
				$a->class  = '' === $new_class ? null : $new_class;
				continue;
			}

			$a_title       = $a->title ?? '';
			$a_class       = $a->class ?? '';
			$a->href       = Lasso_Helper::add_https( $a->href );
			$is_wp_post    = Lasso_Helper::is_wp_post( $a->href );
			$original_href = Lasso_Amazon_Api::get_amazon_product_url( $a->href );
			$href          = $a->href;
			$nofollow      = $a->rel; // ? Prevent search engines follow the url: nofollow
			$target        = $a->target; // ? Open the url in a new tab: _blank
			$anchor_text   = Lasso_Helper::is_existing_thrive_plugin()
				? Lasso_Helper::get_thrive_inner_text_simple_html_dom( $a )
				: $a->innertext;
			$a_lasso_name  = $a->getAttribute( 'data-lasso-name' );
			$a_lasso_id    = $a->getAttribute( 'data-lasso-id' );
			$a_lasso_id    = intval( $a_lasso_id );
			$a_lasso_id    = ( $a_lasso_id && $a_lasso_id > 0 ) ? $a_lasso_id : 0;
			$lasso_id      = 0;

			$a->setAttribute( 'data-old-href', null );

			$lasso_link_location    = new Lasso_Link_Location( $a_lasso_id );
			$is_ll_id_duplicate     = in_array( $a_lasso_id, $lasso_location_ids ); // phpcs:ignore
			$is_ll_id_exist_in_post = intval( $lasso_link_location->get_detection_id() ) === $post_id; // ? id belongs to this post id
			$is_ll_id_valid         = ( $a_lasso_id > 0 && ! $lasso_link_location->get_id() ) || $is_ll_id_exist_in_post;
			// ? check ll id exists in another post or not
			// ? check lasso-is is duplicated
			if ( ! $is_ll_id_valid || $is_ll_id_duplicate ) {
				$a_lasso_id = 0;
			}

			// ? fix: easyzaon link
			$a_keywords = $a->getAttribute( 'data-keywords' );
			$ea_locale  = $a->getAttribute( 'data-locale' );
			$ea_tag     = $a->getAttribute( 'data-tag' );
			if ( 'easyazon-link' === $a_class && $a_keywords && $ea_locale && $ea_tag && ! Lasso_Amazon_Api::is_amazon_url( $href ) ) {
				$amz_countries = Lasso_Amazon_Api::get_amazon_api_countries();
				$ea_domain     = $amz_countries[ strtolower( $ea_locale ) ]['amazon_domain'] ?? 'www.amazon.com';
				$ea_url        = 'https://' . $ea_domain . '/s/?field-keywords=' . rawurlencode( $a_keywords ) . '&tag=' . $ea_tag;
				$a->href       = $ea_url;
				$original_href = $ea_url;
			}

			// ? fix issues of ThirstyLink plugin
			if ( '' !== $a_title && 'thirstylink' === $a_class ) {
				$a_post = get_page_by_title( $a_title, OBJECT, LASSO_POST_TYPE );
				if ( $a_post ) {
					$a_lasso_post = Lasso_Affiliate_Link::get_lasso_url( $a_post->ID );
					$href         = $a_lasso_post->public_link;
					$a->href      = $a_lasso_post->public_link;
					$lasso_id     = $a_lasso_post->lasso_id;
				}
			}

			// ? fix text links of EasyAzon plugin
			if ( 'easyazon-link' === $a_class ) {
				$ea_amazon_id  = $a->getAttribute( 'data-identifier' );
				$ea_amazon_url = Lasso_Amazon_Api::get_default_product_domain( $ea_amazon_id );
				$lasso_post_id = $lasso_db->get_lasso_id_by_product_id_and_type( $ea_amazon_id, Lasso_Amazon_Api::PRODUCT_TYPE, $ea_amazon_url );
				$lasso_id      = $lasso_post_id ? $lasso_post_id : $lasso_id;

				if ( 0 === $lasso_id && class_exists( 'EasyAzon_Addition_Components_Cloaking' ) ) {
					$ae_att = array();
					foreach ( $a->attr as $k => $v ) {
						$k            = str_replace( 'data-', '', $k );
						$ae_att[ $k ] = $v;
					}
					$href    = EasyAzon_Addition_Components_Cloaking::get_url( $href, $ae_att );
					$a->href = $href;
				}
			}

			if ( count( $rewrite_slug ) > 0 && preg_match( $rewrite_slug_preg, $href ) ) {
				// ? fix old links - not apply for save_post_page hook
				$temp_href = preg_replace( $rewrite_slug_preg, '/', $href );
				$lasso_id  = Lasso_Affiliate_Link::get_lasso_post_id_by_url( $temp_href );
				if ( $lasso_id > 0 ) {
					$href    = $temp_href;
					$a->href = $href;
				}
			}

			// ? If the link is empty or not a url, it will be ignored
			if ( '' === $href || strpos( $href, 'mailto:' ) === 0 || strpos( $href, 'tel:' ) === 0 || substr( $href, 0, 1 ) === '#' ) {
				continue;
			}

			// ? fix issue try to cloaked external link but missing case lasso-name='' and there is a Lasso post with empty name as well.
			if ( $a_lasso_id && empty( $a_lasso_name ) && $lasso_link_location->get_id() && $lasso_link_location->get_post_id() ) {
				$temp_lasso_post = get_post( $lasso_link_location->get_post_id() );
				if ( $temp_lasso_post && ( LASSO_POST_TYPE === $temp_lasso_post->post_type ) && empty( $temp_lasso_post->post_title ) ) {
					$href    = $lasso_link_location->get_original_link_slug();
					$a->href = $href;
				}
			}

			// ? Get post id from url
			$lasso_id = Lasso_Affiliate_Link::get_lasso_post_id_by_url( $href, $lasso_id );

			// ? If this is a post, get the permalink
			$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );
			if ( $lasso_id > 0 && $lasso_url->lasso_id > 0 && '#' !== $lasso_url->public_link ) {
				if ( ! Lasso_Amazon_Api::is_amazon_url( $a->href ) && $lasso_url->link_cloaking && ( false !== strpos( $a->href, '?' ) ) ) { // ? Allow to add the custom parameters on the cloaked link
					$a->href = ( new Lasso_Redirect() )->pass_through_url_parameters( $lasso_url->public_link, $a->href, false );
				} elseif ( ! $is_wp_post ) {
					$a->href = $lasso_url->public_link;
				}
				// ? check whether the url is old or not
				$lasso_id = $this->get_current_id_of_old_post( $lasso_id, $href );
			} elseif ( strpos( $href, 'amazon.com' ) !== false && strpos( $href, 'tag=' . Lasso_Setting::lasso_get_setting( 'amazon_tracking_id' ) ) !== false ) {
				$link_location = $lasso_amazon_api->get_lasso_id_from_amazon_url( $href );
				$lasso_id      = $link_location['post_id'] ?? $lasso_id;
			}
			$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );

			if ( Lasso_Amazon_Api::is_amazon_url( $a->href ) && Lasso_Amazon_Api::get_product_id_by_url( $a->href ) ) {
				$href    = Lasso_Amazon_Api::get_amazon_product_url( $a->href );
				$a->href = $href;
			}

			$link_type = $this->get_link_type( $lasso_id, $href );
			if ( Lasso_Link_Location::LINK_TYPE_LASSO === $link_type || Lasso_Link_Location::LINK_TYPE_INTERNAL === $link_type ) {
				$link_slug_domain = null;
			} else {
				$link_slug_domain = Lasso_Helper::get_base_domain( $href );
			}

			// ? An existing amazon link can have multiple tracking ids, even it is added to Lasso.
			$lasso_settings              = Lasso_Setting::lasso_get_settings();
			$amazon_multiple_tracking_id = $lasso_settings['amazon_multiple_tracking_id'];
			$amazon_not_shortened_link   = Lasso_Amazon_Api::is_amazon_url( $original_href ) && ! Lasso_Amazon_Api::is_amazon_shortened_url( $original_href );
			if ( $amazon_not_shortened_link && $amazon_multiple_tracking_id ) {
				$href    = $original_href;
				$a->href = $href;
			}

			if ( Lasso_Link_Location::is_site_stripe_url( $href ) ) {
				$tracking_id = Lasso_Amazon_Api::get_amazon_tracking_id_by_url( $href );
				$href        = Lasso_Amazon_Api::get_amazon_product_url( $href, true, false, $tracking_id );
				$a->href     = $href;
			}

			// ? Prepare to store data into db
			$is_image       = preg_match( '/(<img[^>]+>)/i', $anchor_text );
			$display_type   = $is_image ? Lasso_Link_Location::DISPLAY_TYPE_IMAGE_ONLY : Lasso_Link_Location::DISPLAY_TYPE_TEXT;
			$link_slug      = $href;
			$detection_slug = $post_permalink;
			$tracking_id    = Lasso_Amazon_Api::get_amazon_tracking_id_by_url( $href );
			$product_id     = Lasso_Amazon_Api::get_product_id_by_url( $href );
			$no_follow      = 'nofollow' === $nofollow ? 'true' : 'false';
			$new_window     = '_blank' === $target ? 'true' : 'false';

			// ? classify Amazon URL is monetized or not
			if ( $lasso_id > 0 && get_post_type( $lasso_id ) === LASSO_POST_TYPE ) {
				// ? uncloaked non-amazon links
				if ( LASSO_AMAZON_PRODUCT_TYPE !== $lasso_url->link_type && ! $lasso_url->link_cloaking && ! $is_wp_post ) {
					$href      = $lasso_url->target_url;
					$a->href   = $lasso_url->target_url;
					$link_slug = $lasso_url->target_url;
				}
			} else {
				$lasso_id = 0; // ? not lasso post
			}

			// ? Store data into db
			$link_location_id = $this->write_link_locations(
				$link_type,
				$display_type,
				$anchor_text,
				$lasso_id,
				$link_slug,
				$link_slug_domain,
				$post_id,
				$detection_slug,
				$tracking_id,
				$product_id,
				$no_follow,
				$new_window,
				$current_date,
				$a_lasso_id
			);

			if ( $link_location_id > 0 ) {
				$lasso_location_ids[] = $link_location_id;
				$a->setAttribute( 'data-lasso-id', $link_location_id );
			}

			$a->rel = Lasso_Helper::trim( str_replace( array( '1' ), '', $a->rel ) );
			if ( $lasso_id > 0 ) {
				$rel = $lasso_url->enable_nofollow ? 'nofollow' : null;
				if ( ! empty( $lasso_url->enable_sponsored ) ) {
					$rel = str_replace( array( 'sponsored', '1' ), '', $rel );
					$rel = $rel . ' sponsored';
				}
				$rel       = apply_filters( self::FILTER_FIX_ATAG_IN_GENERATE_BLOCKS_PLUGIN, $rel, $a );
				$a->rel    = Lasso_Helper::trim( $rel );
				$a->target = $lasso_url->html_attribute->target;

				// Below line removes the wrong conversion of blank + dash ' - ' to en-dash.
				/**
				// $wptexturize = remove_filter( 'the_title', 'wptexturize' );
				// $title       = get_the_title( $lasso_id );
				// $a->setAttribute( 'data-lasso-name', $title );
				// if ( $wptexturize ) {
				//  add_filter( 'the_title', 'wptexturize' ); // add back the filter.
				// }
				 */

				$a->setAttribute( 'data-lasso-name', $lasso_url->name );
			} else {
				$a->setAttribute( 'data-lasso-name', null );
			}

			// ? fix tabnabbing issue
			if ( '_blank' === $a->target ) {
				$rel    = str_replace( array( 'noopener', 'noreferrer', '1' ), '', $a->rel );
				$rel    = $rel . ' noopener';
				$a->rel = Lasso_Helper::trim( $rel );
			}

			$a_href_empty = empty( $a->href );
			if ( $a_href_empty ) {
				$a->href = $original_href;
			}

			if ( ! $a->rel ) {
				$a->rel = null;
			}

			// ? Fix links in TablePress plugin
			if ( 'tablepress_table' === $post_type ) {
				$tablepress_pattern = '/(.*?)=([^"].*?)(\s|>)/i';

				$outer_text   = $a->outertext;
				$outer_text   = Lasso_Helper::convert_ansi_to_utf8( $outer_text );
				$outer_text   = str_replace( '\"', '"', $outer_text );
				$outer_text   = preg_replace( $tablepress_pattern, '$1="$2"$3', $outer_text );
				$outer_text   = addslashes( $outer_text );
				$outer_text   = str_replace( "\'", "'", $outer_text );
				$outer_text   = str_replace( '\\\\', '\\', $outer_text ); // phpcs:ignore
				$a->outertext = $outer_text;
			}

			$location_id = empty( $a_lasso_id ) ? $link_location_id : $a_lasso_id; // ? Make sure $location_id must have

			if ( $is_scanning_main_posts ) {
				Lasso_Post::update_content_to_plugin( $post_id, Lasso_Post::MODE_SAVE, $lasso_id, $location_id );
			}
		}
	}

	/**
	 * Scan site stripe data in image tags
	 *
	 * @param int   $post_id                Post ID.
	 * @param array $img_tags               Image tags.
	 * @param array $lasso_location_ids     Lasso location ids.
	 */
	public function scan_site_stripe_data_image( $post_id, &$img_tags, &$lasso_location_ids ) {
		$lasso_amazon_api = new Lasso_Amazon_Api();
		$site_url         = site_url();
		$current_date     = gmdate( 'Y-m-d H:i:s' );
		$post_type        = get_post_type( $post_id );
		$post_permalink   = 'post' === $post_type ? get_the_permalink( $post_id ) : $site_url . '/' . get_page_uri( $post_id );

		foreach ( $img_tags as $key => $img ) {
			// ? ignore img tags have class 'lasso-scan-ignore' and remove this class name
			$img_class      = $img->class ?? '';
			$support_blocks = true;
			if ( strpos( $img_class, 'lasso-scan-ignore' ) !== false ) {
				$new_class      = trim( str_replace( 'lasso-scan-ignore', '', $img_class ) );
				$img->class     = '' === $new_class ? null : $new_class;
				$support_blocks = false;
			}

			$img_src = $img->src ?? '';
			$url     = $img_src;

			if ( strpos( $img_src, self::SITE_STRIPE_DOMAIN ) === false && strpos( $img_src, self::SITE_STRIPE_EU_DOMAIN ) === false ) {
				$img->setAttribute( 'data-lasso-id', null );
				continue;
			}

			$lasso_id   = 0;
			$amazon_url = Lasso_Amazon_Api::get_site_stripe_url( $url );
			$amazon_id  = Lasso_Amazon_Api::get_product_id_by_url( $amazon_url );
			$amazon_tag = Lasso_Amazon_Api::get_amazon_tracking_id_by_url( $amazon_url );

			$sitestripe_format = Lasso_helper::get_argument_from_url( $url, 'format', true );

			$link_location = $lasso_amazon_api->get_lasso_id_from_amazon_url( $amazon_url );
			$lasso_id      = $link_location['post_id'] ?? $lasso_id;

			$a_lasso_id = $img->getAttribute( 'data-lasso-id' );
			$a_lasso_id = intval( $a_lasso_id );
			$a_lasso_id = ( $a_lasso_id && $a_lasso_id > 0 ) ? $a_lasso_id : 0;

			$lasso_link_location    = new Lasso_Link_Location( $a_lasso_id );
			$is_ll_id_duplicate     = in_array( $a_lasso_id, $lasso_location_ids ); // phpcs:ignore
			$is_ll_id_exist_in_post = intval( $lasso_link_location->get_detection_id() ) === $post_id; // ? id belongs to this post id
			$is_ll_id_valid         = ( $a_lasso_id > 0 && ! $lasso_link_location->get_id() ) || $is_ll_id_exist_in_post;
			// ? check ll id exists in another post or not
			// ? check lasso-is is duplicated
			if ( ! $is_ll_id_valid || $is_ll_id_duplicate ) {
				$a_lasso_id = 0;
			}

			// ? Prepare to store data into db
			$link_type        = Lasso_Link_Location::DISPLAY_TYPE_SITE_STRIPE;
			$display_type     = 'Image';
			$link_slug        = $img_src;
			$link_slug_domain = Lasso_Helper::get_base_domain( $link_slug );
			$detection_slug   = $post_permalink;
			$tracking_id      = $amazon_tag;
			$product_id       = $amazon_id;
			$no_follow        = 'false';
			$new_window       = 'false';

			// ? Store data into db
			$link_location_id = $this->write_link_locations(
				$link_type,
				$display_type,
				'',
				$lasso_id,
				$link_slug,
				$link_slug_domain,
				$post_id,
				$detection_slug,
				$tracking_id,
				$product_id,
				$no_follow,
				$new_window,
				$current_date,
				$a_lasso_id
			);

			if ( $link_location_id > 0 ) {
				$lasso_location_ids[] = $link_location_id;
			}

			if ( $lasso_id > 0 ) {
				$lasso_url        = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );
				$amazon_image_url = $lasso_url->amazon->default_image ?? $img_src;
				$amazon_image_url = $amazon_image_url ? $amazon_image_url : $img_src;
				$amazon_image_url = Lasso_Amazon_Api::replace_image_size( $amazon_image_url, $sitestripe_format );
				$img->src         = $amazon_image_url;
			}

			$img->setAttribute( 'data-lasso-id', null );
		}
	}

	/**
	 * Scan site stripe data in iframe tags
	 *
	 * @param int   $post_id                Post ID.
	 * @param array $iframe_tags            Iframe tags.
	 * @param array $lasso_location_ids     Lasso location ids.
	 */
	public function scan_site_stripe_data_iframe( $post_id, &$iframe_tags, &$lasso_location_ids ) {
		$lasso_amazon_api = new Lasso_Amazon_Api();
		$site_url         = site_url();
		$current_date     = gmdate( 'Y-m-d H:i:s' );
		$post_type        = get_post_type( $post_id );
		$post_permalink   = 'post' === $post_type ? get_the_permalink( $post_id ) : $site_url . '/' . get_page_uri( $post_id );

		foreach ( $iframe_tags as $key => $iframe ) {
			// ? ignore iframe tags have class 'lasso-scan-ignore' and remove this class name
			$iframe_class = $iframe->class ?? '';
			if ( strpos( $iframe_class, 'lasso-scan-ignore' ) !== false ) {
				$new_class     = trim( str_replace( 'lasso-scan-ignore', '', $iframe_class ) );
				$iframe->class = '' === $new_class ? null : $new_class;
				continue;
			}

			$iframe_src = $iframe->src ?? '';
			$url        = $iframe_src;

			if ( strpos( $iframe_src, self::SITE_STRIPE_DOMAIN ) === false && strpos( $iframe_src, self::SITE_STRIPE_EU_DOMAIN ) === false ) {
				$iframe->setAttribute( 'data-lasso-id', null );
				continue;
			}

			$lasso_id   = 0;
			$amazon_url = Lasso_Amazon_Api::get_site_stripe_url( $url );
			$amazon_id  = Lasso_Amazon_Api::get_product_id_by_url( $amazon_url );
			$amazon_tag = Lasso_Amazon_Api::get_amazon_tracking_id_by_url( $amazon_url );

			$link_location = $lasso_amazon_api->get_lasso_id_from_amazon_url( $amazon_url );
			$lasso_id      = $link_location['post_id'] ?? $lasso_id;

			$a_lasso_id = $iframe->getAttribute( 'data-lasso-id' );
			$a_lasso_id = intval( $a_lasso_id );
			$a_lasso_id = ( $a_lasso_id && $a_lasso_id > 0 ) ? $a_lasso_id : 0;

			$lasso_link_location    = new Lasso_Link_Location( $a_lasso_id );
			$is_ll_id_duplicate     = in_array( $a_lasso_id, $lasso_location_ids ); // phpcs:ignore
			$is_ll_id_exist_in_post = intval( $lasso_link_location->get_detection_id() ) === $post_id; // ? id belongs to this post id
			$is_ll_id_valid         = ( $a_lasso_id > 0 && ! $lasso_link_location->get_id() ) || $is_ll_id_exist_in_post;
			// ? check ll id exists in another post or not
			// ? check lasso-is is duplicated
			if ( ! $is_ll_id_valid || $is_ll_id_duplicate ) {
				$a_lasso_id = 0;
			}

			// ? Prepare to store data into db
			$link_type        = Lasso_Link_Location::DISPLAY_TYPE_SITE_STRIPE;
			$display_type     = 'Product Box';
			$link_slug        = $iframe_src;
			$link_slug_domain = Lasso_Helper::get_base_domain( $link_slug );
			$detection_slug   = $post_permalink;
			$tracking_id      = $amazon_tag;
			$product_id       = $amazon_id;
			$no_follow        = 'false';
			$new_window       = 'false';

			// ? Store data into db
			$link_location_id = $this->write_link_locations(
				$link_type,
				$display_type,
				'',
				$lasso_id,
				$link_slug,
				$link_slug_domain,
				$post_id,
				$detection_slug,
				$tracking_id,
				$product_id,
				$no_follow,
				$new_window,
				$current_date,
				$a_lasso_id
			);

			if ( $link_location_id > 0 ) {
				$lasso_location_ids[] = $link_location_id;
			}

			if ( $lasso_id > 0 ) {
				$lasso_url              = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );
				$lasso_single_shortcode = '[lasso sitestripe="true" sitestripe_scan="true" id="' . $lasso_url->lasso_id . '" ref="' . $lasso_url->slug . '" link_id="' . $link_location_id . '"]';
				$iframe->outertext      = $lasso_single_shortcode;
			}

			$iframe->setAttribute( 'data-lasso-id', null );
		}
	}

	/**
	 * Scan data in WP Recipe Maker plugin
	 *
	 * @param int    $post_id            Post ID.
	 * @param string $post_content       Post content.
	 * @param array  $lasso_location_ids Lasso location ids.
	 * @param array  $inner_blocks       Inner blocks.
	 */
	public function scan_site_stripe_data_wp_recipe_maker( $post_id, $post_content, &$lasso_location_ids, $inner_blocks = array() ) {
		if ( ! Lasso_Helper::is_wp_recipe_maker_plugin_active() ) {
			return;
		}

		// ? old WP version like 4.9 or older ones won't support blocks
		if ( ! function_exists( 'parse_blocks' ) ) {
			return $post_content;
		}

		$blocks = empty( $inner_blocks ) ? parse_blocks( $post_content ) : $inner_blocks;
		foreach ( $blocks as $block ) {
			if ( 'wp-recipe-maker/recipe-part' === $block['blockName'] ) {
				$attrs     = $block['attrs'];
				$recipe_id = $attrs['id'];
				if ( 'wprm_recipe' === get_post_type( $recipe_id ) ) {
					$this->scan_wp_recipe_maker_data_notes( $post_id, $lasso_location_ids, $recipe_id );
				}
			}
			if ( 'wp-recipe-maker/recipe' === $block['blockName'] ) {
				$attrs     = $block['attrs'];
				$recipe_id = $attrs['id'];
				if ( 'wprm_recipe' === get_post_type( $recipe_id ) ) {
					$this->scan_wp_recipe_maker_data_equipment( $post_id, $lasso_location_ids, $recipe_id );
				}
			}

			if ( isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) && ! empty( $block['innerBlocks'] ) ) {
				$post_content = $this->scan_site_stripe_data_wp_recipe_maker( $post_id, '', $lasso_location_ids, $block['innerBlocks'] );
			}
		}
	}

	/**
	 * Scan data in WP Recipe Maker plugin
	 *
	 * @param int   $post_id            Post ID.
	 * @param array $lasso_location_ids Lasso location ids.
	 * @param int   $recipe_id          Recipe ID.
	 */
	public function scan_wp_recipe_maker_data_notes( $post_id, &$lasso_location_ids, $recipe_id ) {
		if ( ! Lasso_Helper::is_wp_recipe_maker_plugin_active() ) {
			return;
		}

		$is_scanning_main_posts = false;

		$recipe_notes = get_post_meta( $recipe_id, 'wprm_notes', true );
		// ? keep break line for "php simple html dom" library
		// ? https://stackoverflow.com/questions/4812691/preserve-line-breaks-simple-html-dom-parser
		$html = new simple_html_dom();
		$html->load( $recipe_notes, true, false );

		$a_tags      = $html->find( 'a' ); // ? Find a tags in the html (SiteStripe images)
		$img_tags    = $html->find( 'img' ); // ? Find image tags in the html (SiteStripe images)
		$iframe_tags = $html->find( 'iframe' ); // ? Find iframe tags in the html (SiteStripe images)

		$this->scan_a_tags( $post_id, $is_scanning_main_posts, $a_tags, $lasso_location_ids );
		$this->scan_site_stripe_data_image( $post_id, $img_tags, $lasso_location_ids );
		$this->scan_site_stripe_data_iframe( $post_id, $iframe_tags, $lasso_location_ids );

		update_post_meta( $recipe_id, 'wprm_notes', (string) $html );
	}

	/**
	 * Scan data in WP Recipe Maker plugin
	 *
	 * @param int   $post_id            Post ID.
	 * @param array $lasso_location_ids Lasso location ids.
	 * @param int   $recipe_id          Recipe ID.
	 */
	public function scan_wp_recipe_maker_data_equipment( $post_id, &$lasso_location_ids, $recipe_id ) {
		if ( ! Lasso_Helper::is_wp_recipe_maker_plugin_active() ) {
			return;
		}

		$is_scanning_main_posts = false;

		$recipe_equipment = get_post_meta( $recipe_id, 'wprm_equipment', true );
		foreach ( $recipe_equipment as $equipment ) {
			$equipment_html = get_term_meta( $equipment['id'], 'wprmp_equipment_affiliate_html', true );

			// ? keep break line for "php simple html dom" library
			// ? https://stackoverflow.com/questions/4812691/preserve-line-breaks-simple-html-dom-parser
			$html = new simple_html_dom();
			$html->load( $equipment_html, true, false );

			$a_tags      = $html->find( 'a' ); // ? Find a tags in the html (SiteStripe images)
			$img_tags    = $html->find( 'img' ); // ? Find image tags in the html (SiteStripe images)
			$iframe_tags = $html->find( 'iframe' ); // ? Find iframe tags in the html (SiteStripe images)

			$this->scan_a_tags( $post_id, $is_scanning_main_posts, $a_tags, $lasso_location_ids );
			$this->scan_site_stripe_data_image( $post_id, $img_tags, $lasso_location_ids );
			$this->scan_site_stripe_data_iframe( $post_id, $iframe_tags, $lasso_location_ids );

			update_term_meta( $equipment['id'], 'wprmp_equipment_affiliate_html', (string) $html );
		}
	}

	/**
	 * Scan data in Elementor plugin
	 *
	 * @param int   $post_id            Post ID.
	 * @param array $lasso_location_ids Lasso location ids.
	 */
	public function scan_site_stripe_data_elementor( $post_id, &$lasso_location_ids ) {
		if ( class_exists( Lasso_Elementor::class ) && Lasso_Helper::is_wp_elementor_plugin_actived() && Lasso_Helper::is_built_with_elementor( $post_id ) ) {
			$mode            = Lasso_Post::MODE_SAVE;
			$lasso_elementor = new Lasso_Elementor( $post_id, 0, $mode );
			$editor_data     = $lasso_elementor->get_data();
			$lasso_elementor->scan_site_stripe_data( $editor_data, $mode, $lasso_location_ids );

			// ? We need the `wp_slash` in order to avoid the unslashing during the `update_post_meta`
			$json_value = wp_slash( wp_json_encode( $editor_data ) );
			update_metadata( 'post', $post_id, $lasso_elementor->get_data_key(), $json_value );
		}
	}

	/**
	 * Scan data in Beaver Builder plugin
	 *
	 * @param int   $post_id            Post ID.
	 * @param array $lasso_location_ids Lasso location ids.
	 */
	public function scan_site_stripe_data_beaver_builder( $post_id, &$lasso_location_ids ) {
		if ( ! Lasso_Helper::is_beaver_builder_plugin_active() ) {
			return;
		}

		$is_scanning_main_posts = false;

		$data = get_post_meta( $post_id, '_fl_builder_data', true );
		foreach ( $data as $index => $element ) {
			$settings = $element->settings ?? null;
			if ( ! $settings ) {
				continue;
			}

			$setting_html = $settings->html ?? null;
			$is_html      = ! is_null( $setting_html );
			if ( ! $is_html ) {
				$setting_html = $settings->text ?? null;
				$is_text      = ! is_null( $setting_html );
			}
			if ( ! $setting_html ) {
				continue;
			}

			// ? keep break line for "php simple html dom" library
			// ? https://stackoverflow.com/questions/4812691/preserve-line-breaks-simple-html-dom-parser
			$html = new simple_html_dom();
			$html->load( $setting_html, true, false );

			$a_tags      = $html->find( 'a' ); // ? Find a tags in the html (SiteStripe images)
			$img_tags    = $html->find( 'img' ); // ? Find image tags in the html (SiteStripe images)
			$iframe_tags = $html->find( 'iframe' ); // ? Find iframe tags in the html (SiteStripe images)

			$this->scan_a_tags( $post_id, $is_scanning_main_posts, $a_tags, $lasso_location_ids );
			$this->scan_site_stripe_data_image( $post_id, $img_tags, $lasso_location_ids );
			$this->scan_site_stripe_data_iframe( $post_id, $iframe_tags, $lasso_location_ids );

			if ( $is_text ) {
				$data[ $index ]->settings->text = (string) $html;
			} elseif ( $is_html ) {
				$data[ $index ]->settings->html = (string) $html;
			}
		}
		update_post_meta( $post_id, '_fl_builder_data', $data );
	}

	/**
	 * Fix blocks in Gutenberg editor
	 *
	 * @param int    $link_id      Id in link_location table.
	 * @param string $shortcode    Shortcode.
	 * @param string $post_content Post content.
	 */
	public function fix_shortcode_in_gutenberg( $link_id, $shortcode, $post_content ) {
		$link_id = intval( $link_id );
		if ( empty( $link_id ) || $link_id <= 0 ) {
			return $post_content;
		}

		$incorrect_tag_regex = '/\"short_code\"\:\"\s*(.*?)(\\\u0022' . $link_id . '\\\u0022)\s*(.*?)\"\,/i';
		preg_match_all( $incorrect_tag_regex, $post_content, $matches );

		$correct_shortcode = str_replace( '"', '\u0022', $shortcode );

		$incorrect_shortcode = '';
		if ( isset( $matches[1][0] ) && isset( $matches[2][0] ) && isset( $matches[3][0] ) ) {
			$incorrect_shortcode = $matches[1][0] . $matches[2][0] . $matches[3][0];
		}
		$new_post_content = str_replace( $incorrect_shortcode, $correct_shortcode, $post_content );

		return is_null( $new_post_content ) || empty( $new_post_content ) ? $post_content : $new_post_content;
	}

	/**
	 * Fix blocks in Gutenberg editor
	 *
	 * @param string $post_content Post content.
	 */
	public function fix_lasso_block_in_gutenberg( $post_content ) {
		$old_post_content = $post_content;

		// ? fix: lasso block in gutenberg editor (wrong html tag: <p> to <div>)
		$incorrect_tag_regex = '/<p class="wp-block-affiliate-plugin-lasso">(.*?)<\/p>/i';
		$correct_tag         = '<div class="wp-block-affiliate-plugin-lasso">$1</div>';
		$post_content        = preg_replace( $incorrect_tag_regex, $correct_tag, $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		// ? fix: lasso block in gutenberg editor (missing <div> tag)
		$incorrect_tag_regex = '/<\!\-\-\s*wp\:shortcode\s*\-\-\>\n\[lasso\s*ref="(.*?)"\s*id="(.*?)"\s*link_id="(.*?)"\]\n<\!\-\-\s*\/wp\:shortcode\s*\-\-\>/i';
		$correct_tag         = '
			<!-- wp:affiliate-plugin/lasso {"show_short_code":true,"short_code":"[lasso ref=\u0022$1\u0022 id=\u0022$2\u0022 link_id=\u0022$3\u0022]","button_text":"Select a New Display","button_update_text":"Update Display"} -->
			<div class="wp-block-affiliate-plugin-lasso">[lasso ref="$1" id="$2" link_id="$3"]</div>
			<!-- /wp:affiliate-plugin/lasso -->
		';
		$post_content        = preg_replace( $incorrect_tag_regex, $correct_tag, $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		// ? fix: image block in gutenberg editor
		$incorrect_tag_regex = '/<\!\-\-\s*wp\:image\s*(.*?)\s*\-\-\>\n<div\s*class="wp-block-image"><figure\s*class="(.*?)">(.*?)<\/figure><\/div>\n<\!\-\-\s*\/wp\:image\s*\-\-\>/i';
		$correct_tag         = '
			<!-- wp:image $1 -->
			<figure class="wp-block-image $2">$3</figure>
			<!-- /wp:image -->
		';
		$post_content        = preg_replace( $incorrect_tag_regex, $correct_tag, $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		// ? fix: text block in gutenberg editor
		$incorrect_tag_regex = '/<\!\-\-\s*wp\:paragraph\s*(.*?)\s*\-\-\>\n<p\s*(.*?)>(.*?)<a\s*(.*?)>\s*(.*?)<\/a><\/a>(.*?)<\/p>\n<\!\-\-\s*\/wp\:paragraph\s*\-\-\>/i';
		$correct_tag         = '
			<!-- wp:paragraph $1 -->
			<p $2">$3$5</a>$6</p>
			<!-- /wp:paragraph -->
		';
		$post_content        = preg_replace( $incorrect_tag_regex, $correct_tag, $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		// ? fix: lasso blocks are invalid in the editor
		$incorrect_tag_regex = '/<\!\-\-\s*wp\:affiliate\-plugin\/lasso\s*(.*?)(\"short_code\"\:\"\[)(.*?)(\]\")(.*?)\s*\-\-\>\n(.*?)\n(.*?)<\!\-\-\s*\/wp\:affiliate\-plugin\/lasso\s*\-\-\>/i';
		preg_match_all( $incorrect_tag_regex, $post_content, $matches );
		if ( isset( $matches[3] ) ) {
			foreach ( $matches[3] as $shortcode_content ) {
				$gu_lasso_content     = $shortcode_content;
				$gu_lasso_content_new = str_replace( '"', '\u0022', $gu_lasso_content );
				$gu_lasso_content_new = str_replace( '<', '\u003c', $gu_lasso_content_new );
				$gu_lasso_content_new = str_replace( '>', '\u003e', $gu_lasso_content_new );

				if ( '' !== $gu_lasso_content_new ) {
					$post_content = str_replace( $gu_lasso_content, $gu_lasso_content_new, $post_content );
					if ( is_null( $post_content ) || empty( $post_content ) ) {
						$post_content = $old_post_content;
					} else {
						$old_post_content = $post_content;
					}
				}
			}
		}
		$incorrect_tag_regex = '/<\!\-\-\s*wp\:affiliate\-plugin\/lasso\s*(.*?)\s*\-\-\>\n(.*?)(\<div .*?>)(.*?)(<\/div>)\n(.*?)<\!\-\-\s*\/wp\:affiliate\-plugin\/lasso\s*\-\-\>/i';
		preg_match_all( $incorrect_tag_regex, $post_content, $matches );
		if ( count( $matches ) > 5 ) {
			$shortcode_origin        = $matches[0];
			$shortcode               = $matches[4];
			$shortcode_wrapper_open  = $matches[3];
			$shortcode_wrapper_close = $matches[5];
			foreach ( $matches[1] as $key => $shortcode_attr ) {
				if ( '' === $shortcode_attr ) {
					$shortcode_content_attr = $shortcode[ $key ];
					$shortcode_content_attr = str_replace( '"', '\u0022', $shortcode_content_attr );
					$shortcode_attr         = '{"show_short_code":true,"short_code":"' . $shortcode_content_attr . '","button_text":"Select a New Display","button_update_text":"Update Display"}';
					$gu_content             = '
						<!-- wp:affiliate-plugin/lasso ' . $shortcode_attr . ' -->
							' . $shortcode_wrapper_open[ $key ] . '
							' . $shortcode[ $key ] . '
							' . $shortcode_wrapper_close[ $key ] . '
						<!-- /wp:affiliate-plugin/lasso -->
					';
					$post_content           = str_replace( $shortcode_origin[ $key ], $gu_content, $post_content );
					if ( is_null( $post_content ) || empty( $post_content ) ) {
						$post_content = $old_post_content;
					} else {
						$old_post_content = $post_content;
					}
				}
			}
		}

		// ? Fix: the regex find raw amazon link below effect to amazon links inside Lasso gutenberg json attribute. We should fix the wrong content back to the correct.
		$post_content = apply_filters( self::FILTER_FIX_LASSO_BLOCK_IN_GUTENBERG, $post_content );

		// ? fix: lasso shortcode is replaced by raw url (not in gutenberg editor)
		// ? amazon links
		// $incorrect_tag_regex = '/([^\"\\\u{0022}]|^\n)(https:\/\/www.amazon.([a-zA-z0-9\/\-\=\?\&\;\+]*))([^\"\\\u{0022}]|^\n)/i';
		// $correct_tag         = '$1[lasso amazon_url="$2"]$4';
		// $post_content        = preg_replace( $incorrect_tag_regex, $correct_tag, $post_content );
		// if ( is_null( $post_content ) || empty( $post_content ) ) {
		// $post_content = $old_post_content;
		// } else {
		// $old_post_content = $post_content;
		// }
		// ? geni.us links
		$incorrect_tag_regex = '/([^\"\\\u{0022}]|^\n)(https:\/\/geni.us\/([a-zA-z0-9\/\-\=\?\&\;]*))([^\"\\\u{0022}]|^\n)/i';
		$correct_tag         = '$1[lasso geni_url="$2"]$4';
		$post_content        = preg_replace( $incorrect_tag_regex, $correct_tag, $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		// ? fix: image block that is aligned
		$incorrect_tag_regex = '/<div class="(wp-block-image(\s+([a-zA-z\-\_0-9]*)))">(<figure class="([a-zA-z\-\_0-9]*)")>([\w\d\<\s\=\"\:\/\.\-\>]*)(<\/figure><\/div>)/i';
		$correct_tag         = '<figure class="$1 $5">$6</figure>';
		$post_content        = preg_replace( $incorrect_tag_regex, $correct_tag, $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		// ? Fix wrong Lasso gutenberg content
		$incorrect_tag_regex = '/(<\!\-\-\s*wp\:affiliate-plugin\/lasso\s*)(\{\".*\"\})(.*\n)(.*wp-block-affiliate-plugin-lasso\">)(.*)(\n\s*<\!\-\-\s*\/wp\:affiliate-plugin\/lasso)/i';
		$post_content        = preg_replace_callback( $incorrect_tag_regex, array( $this, 'replace_wrong_lasso_gutenberg_content' ), $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		// ? Fix wrong amalinkspro gutenberg image link content
		$incorrect_tag_regex = '/(<\!\-\-\s*wp\:common\/insert-amalinkspro-shortcode\s*\{\".*link_id\=\\\u0022)(.*)(\\\u0022\])(\\\u0022\}\s\-\-\>)(.*\n.*<div>\[amalinkspro\stype\=\\\u0022image-link\\\u0022.*link_id\=\\\u0022)(.*)(\\\u0022\])(.*)(\[\/amalinkspro\]<\/div>)(.*\n<\!\-\-\s*\/wp\:common\/insert-amalinkspro-shortcode\s\-\-\>)/i';
		$post_content        = preg_replace_callback( $incorrect_tag_regex, array( $this, 'replace_wrong_amalinkspro_image_link_gutenberg_content' ), $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		$incorrect_tag_regex = '/(<\!\-\-\s*wp\:common\/insert-amalinkspro-shortcode\s*\{\"shortcode\"\:\")(.*\stype\=\\\u0022image-link.*apilink\=\\\u0022http.*\\\u0022.*\slink_id\=\\\u0022.*\\\u0022\])(<\/div>\r*\n*<\!\-\-\s\/wp\:common\/insert-amalinkspro-shortcode\s\-\-\>)/i';
		$post_content        = preg_replace_callback( $incorrect_tag_regex, array( $this, 'replace_wrong_amalinkspro_image_link_gutenberg_content' ), $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		// ? Fix break content for amalinkspro gutenberg image link with amazon url that replace by [lasso amazon_url...] in json attribute
		$incorrect_tag_regex = '/(<\!\-\-\s*wp\:common\/insert-amalinkspro-shortcode\s*\{\"shortcode\"\:\")(.*\stype\=\\\u0022image-link.*apilink\=\\\u0022)(.*\[lasso\\\u0022\samazon_url\=\\\u0022)(.*)(\\\u0022\slink_id.*\\\u0022.*\])(.*\slink_id\=\\\u0022.*\\\u0022\])(<\/div>\r*\n*<\!\-\-\s\/wp\:common\/insert-amalinkspro-shortcode\s\-\-\>)/i';
		$post_content        = preg_replace_callback( $incorrect_tag_regex, array( $this, 'replace_wrong_amalinkspro_image_link_gutenberg_content_with_amazon_link' ), $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		// ? fix wrong link_id attribute of shortcode in blocks
		$incorrect_tag_regex = '/<\!\-\-\s*wp\:amalinkspro\-legacy\/(.*?)\s+{\"shortcode\"\:\"([\[\=\\\-\~\:\,\#\]\-\/\s\d\w]*)\"}\s+\-\-\>\n+<div>(.*?)<\/div>\n+<!--\s+\/wp\:amalinkspro\-legacy/i';
		$post_content        = preg_replace_callback( $incorrect_tag_regex, array( $this, 'replace_wrong_amalinkspro_link_id_gutenberg_content' ), $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		$incorrect_tag_regex = '/<\!\-\-\s*wp\:common\/insert\-amalinkspro\-shortcode(.*?)\s+{\"shortcode\"\:\"([\[\=\\\-\~\:\,\#\]\-\/\s\d\w\.\?\(\)]*)\"}\s+\-\-\>\n+<div>(.*?)<\/div>\n+<!--\s+\/wp\:common\/insert\-amalinkspro\-shortcode/i';
		$post_content        = preg_replace_callback( $incorrect_tag_regex, array( $this, 'replace_wrong_amalinkspro_link_id_gutenberg_content' ), $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		// ? Fix incorrect element attribute ' data-lasso-id=""'
		$post_content = str_replace( ' data-lasso-id=""', '', $post_content );

		$post_content = str_replace( ',"button_text":"Update Display"} -->', ',"button_text":"Select a New Display","button_update_text":"Update Display"} -->', $post_content );

		return $post_content;
	}

	/**
	 * Return correct lasso gutenberg content.
	 *
	 * @param  array $matches Matches parameters.
	 * @return string Correct lasso gutenberg content.
	 */
	public function replace_wrong_lasso_gutenberg_content( $matches ) {
		$lasso_gutenberg_attr = json_decode( $matches[2] );
		$shortcode            = $lasso_gutenberg_attr->short_code ?? trim( $matches[5], '</div>' );
		$shortcode            = trim( $shortcode );

		return $matches[1] . $matches[2] . $matches[3] . $matches[4] . $shortcode . '</div>' . $matches[6];
	}

	/**
	 * Return correct amalinkspro gutenberg image block.
	 *
	 * @param  array $matches Matches parameters.
	 * @return string Correct amalinkspro gutenberg image block.
	 */
	public function replace_wrong_amalinkspro_image_link_gutenberg_content( $matches ) {
		$image_link = $matches['8'] ?? '';

		if ( $image_link ) {
			$close_tag_in_attr = '[/amalinkspro]' . str_replace( '\u0022', '"', $matches[4] );
			$render_content    = $matches[5] . $matches[6] . $matches[7] . $matches[8] . $matches[9];
			$render_content    = str_replace( '\u0022', '"', $render_content );
			$render_content    = str_replace( '\u0026', '&', $render_content );
			$link_location_id  = $matches[6];
			return $matches[1] . $link_location_id . $matches[3] . $image_link . $close_tag_in_attr . $render_content . $matches[10];
		}

		return $matches[0];
	}

	/**
	 * Return correct content for amalinkspro gutenberg image link
	 *
	 * @param  array $matches Matches parameters.
	 * @return string Correct amalinkspro gutenberg image block.
	 */
	public function replace_wrong_amalinkspro_image_link_gutenberg_content_2( $matches ) {
		try {
			$shortcode_json        = $matches[2];
			$shortcode_json_format = str_replace( '\u0022', '"', $shortcode_json );
			$shortcode_json_format = str_replace( '\u0026', '&', $shortcode_json_format );

			$lasso_amazon_api = new Lasso_Amazon_Api();
			$attr_json        = Lasso_Helper::get_attributes( $shortcode_json_format );
			$asin             = $attr_json['asin'] ?? null;
			$produuct_url     = $attr_json['apilink'] ?? null;
			$product_id       = $asin ? $asin : Lasso_Amazon_Api::get_product_id_by_url( $produuct_url );

			// ? Because we lose the amazon image content, we have to get from DB or Lambda
			if ( $product_id && $produuct_url ) {
				$image_url     = LASSO_PLUGIN_URL . 'admin/assets/images/lasso-no-thumbnail.jpg';
				$product_in_db = $lasso_amazon_api->get_amazon_product_from_db( $product_id );

				if ( $product_in_db ) {
					$image_url = $product_in_db['default_image'];
				} else {
					$product = $lasso_amazon_api->fetch_product_info( $product_id, true, false, $produuct_url );

					if ( 'success' === $product['status'] ) {
						$image_url = $product['product']['image'];
					}
				}

				$final_sc_content = array_map(
					function( $key, $value ) {
						if ( null === $value ) {
							return '';
						}
						return $key . '="' . $value . '"';
					},
					array_keys( $attr_json ),
					array_values( $attr_json )
				);
				$final_sc_content = array_filter(
					$final_sc_content,
					function( $value ) {
						return ! empty( $value );
					}
				);

				$shortcode_content   = '[amalinkspro ' . implode( ' ', $final_sc_content ) . ']' . $image_url . '[/amalinkspro]';
				$full_shortcode_json = $shortcode_json . $image_url . '[/amalinkspro]';

				$matches[0] = $matches[1] . $full_shortcode_json . "\"} -->\n<div>" . $shortcode_content . $matches[3];
			}
		} catch ( \Exception $e ) {
			return $matches[0];
		}

		return $matches[0];
	}

	/**
	 * Return correct content for amalinkspro gutenberg image link with amazon url that replace by [lasso amazon_url...] in json attribute
	 *
	 * @param  array $matches Matches parameters.
	 * @return string Correct amalinkspro gutenberg image block.
	 */
	public function replace_wrong_amalinkspro_image_link_gutenberg_content_with_amazon_link( $matches ) {
		try {
			$api_link              = $matches[4];
			$api_link              = explode( '\u0022', $api_link );
			$api_link              = $api_link[0];
			$shortcode_json        = $matches[2] . $api_link . '\u0022 ' . $matches[6];
			$shortcode_json_format = str_replace( '\u0022', '"', $shortcode_json );
			$shortcode_json_format = str_replace( '\u0026', '&', $shortcode_json_format );

			$lasso_amazon_api = new Lasso_Amazon_Api();
			$attr_json        = Lasso_Helper::get_attributes( $shortcode_json_format );
			$asin             = $attr_json['asin'] ?? null;
			$produuct_url     = $attr_json['apilink'] ?? null;
			$product_id       = $asin ? $asin : Lasso_Amazon_Api::get_product_id_by_url( $produuct_url );

			// ? Because we lose the amazon image content, we have to get from DB or Lambda
			if ( $product_id && $produuct_url ) {
				$image_url     = LASSO_PLUGIN_URL . 'admin/assets/images/lasso-no-thumbnail.jpg';
				$product_in_db = $lasso_amazon_api->get_amazon_product_from_db( $product_id );

				if ( $product_in_db ) {
					$image_url = $product_in_db['default_image'];
				} else {
					$product = $lasso_amazon_api->fetch_product_info( $product_id, true, false, $produuct_url );

					if ( 'success' === $product['status'] ) {
						$image_url = $product['product']['image'];
					}
				}

				$final_sc_content = array_map(
					function( $key, $value ) {
						if ( null === $value ) {
							return '';
						}
						return $key . '="' . $value . '"';
					},
					array_keys( $attr_json ),
					array_values( $attr_json )
				);
				$final_sc_content = array_filter(
					$final_sc_content,
					function( $value ) {
						return ! empty( $value );
					}
				);

				$shortcode_content   = '[amalinkspro ' . implode( ' ', $final_sc_content ) . ']' . $image_url . '[/amalinkspro]';
				$full_shortcode_json = $shortcode_json . $image_url . '[/amalinkspro]';

				$matches[0] = $matches[1] . $full_shortcode_json . "\"} -->\n<div>" . $shortcode_content . $matches[7];
			}
		} catch ( \Exception $e ) {
			return $matches[0];
		}

		return $matches[0];
	}

	/**
	 * Return correct amalinkspro gutenberg block.
	 *
	 * @param  array $matches Matches parameters.
	 * @return string Correct amalinkspro gutenberg image block.
	 */
	public function replace_wrong_amalinkspro_link_id_gutenberg_content( $matches ) {
		$pattern           = get_shortcode_regex( self::SHORTCODE_LIST );
		$shortcode_json    = $matches[2] ?? '';
		$shortcode_content = $matches[3] ?? '';

		$attr_json = Lasso_Helper::get_attributes( $shortcode_json );
		$attr_json = str_replace( '\u0022', '"', $attr_json );
		$attr_json = str_replace( '\u0026', '&', $attr_json );

		if ( $shortcode_json && $shortcode_content ) {
			$attr      = Lasso_Helper::get_attributes( $shortcode_content );
			$attr_json = Lasso_Helper::get_attributes( $shortcode_json );
			$link_id   = $attr['link_id'] ?? '';
			if ( $link_id ) {
				$final_attr = array_merge( $attr_json, $attr );

				$final_sc_content = array_map(
					function( $key, $value ) {
						if ( null === $value ) {
							return '';
						}
						return $key . '="' . $value . '"';
					},
					array_keys( $final_attr ),
					array_values( $final_attr )
				);
				$final_sc_content = array_filter(
					$final_sc_content,
					function( $value ) {
						return ! empty( $value );
					}
				);
				preg_match_all( '~' . $pattern . '~s', $shortcode_content, $sc_matches );
				$shortcode_type  = $sc_matches[2][0];
				$content_between = trim( $sc_matches[5][0] );
				$replace_with    = "[$shortcode_type " . implode( ' ', $final_sc_content ) . ']';
				// ? fix shortcode with content
				if ( ! empty( $content_between ) ) {
					$replace_with = "[$shortcode_type " . implode( ' ', $final_sc_content ) . ']' . $content_between . "[/$shortcode_type]";
				}

				$pos = strpos( $matches[0], $shortcode_content );
				if ( false !== $pos ) {
					$matches[0] = substr_replace( $matches[0], $replace_with, $pos, strlen( $shortcode_content ) );
				}

				// ? shortcode in json
				$pos_json = strpos( $matches[0], $shortcode_json );
				if ( false !== $pos_json ) {
					$replace_with = str_replace( '"', '\u0022', $replace_with );
					$replace_with = str_replace( '&', '\u0026', $replace_with );
					$matches[0]   = substr_replace( $matches[0], $replace_with, $pos_json, strlen( $shortcode_json ) );
				}
			}
		}

		return $matches[0];
	}

	/**
	 * Update link in post/page
	 *
	 * @param int    $post_id         Post id.
	 * @param int    $lasso_id        Lasso post id.
	 * @param string $new_url         New url.
	 * @param string $new_anchor_text New anchor text. Default to empty.
	 */
	public function update_link_in_post( $post_id, $lasso_id, $new_url, $new_anchor_text = '' ) {
		$lasso_id = intval( $lasso_id );
		$post_id  = intval( $post_id );
		$post_old = get_post( $post_id );

		$query    = 'SELECT post_content, post_type, post_name FROM ' . Model::get_wp_table_name( 'posts' ) . ' WHERE ID=%d';
		$prepare  = Model::prepare( $query, $post_id ); // phpcs:ignore
		$post_old = Model::get_row( $prepare );

		if ( ! $post_old ) {
			return false;
		}

		$post_content = $post_old->post_content;
		// ? fix issues of Thrive Architect plugin
		$post_content = Lasso_Helper::get_thrive_plugin_post_content( $post_id, $post_content );
		if ( ! $post_content || empty( $post_content ) ) {
			return false;
		}

		// ? keep break line for "php simple html dom" library
		// ? https://stackoverflow.com/questions/4812691/preserve-line-breaks-simple-html-dom-parser
		$html = new simple_html_dom();
		$html->load( $post_content, true, false );

		$a_tags = $html->find( 'a' ); // ? Find a tags in the html

		// ? Handle urls
		foreach ( $a_tags as $a ) {
			$a_lasso_id = $a->getAttribute( 'data-lasso-id' );
			$a_lasso_id = intval( $a_lasso_id );
			$is_wp_post = url_to_postid( $a->href ) > 0;

			if ( $a_lasso_id === $lasso_id && ! $is_wp_post ) {
				$a->href = $new_url;
				if ( '' !== $new_anchor_text ) {
					$a->innertext = $new_anchor_text;
				}

				$a->target = Lasso_Affiliate_Link::open_new_tab( $lasso_id ) ? '_blank' : false;
				break;
			}
		}

		// ? Update new content after has applied lasso settings
		$this->update_post_content( $post_id, $html );

		// ? rescan post
		$query    = 'SELECT * FROM ' . Model::get_wp_table_name( 'posts' ) . ' WHERE ID = %d';
		$prepare  = Model::prepare( $query, $post_id ); // phpcs:ignore
		$post_new = Model::get_row( $prepare );

		$this->check_all_posts_pages( array( $post_new ) );

		return true;
	}


	/**
	 * Scan all lasso links in posts/pages so we can update target and rel attributes (enable_nofollow and open_new_tab)
	 *
	 * @param int   $lasso_id          Lasso post id.
	 * @param int   $post_id           Post id.
	 * @param array $link_location_ids Array of link location ids.
	 */
	public function scan_link_in_post( $lasso_id, $post_id, $link_location_ids ) {
		$lasso_db         = new Lasso_DB();
		$lasso_amazon_api = new Lasso_Amazon_Api();

		$post_id  = intval( $post_id );
		$post_old = get_post( $post_id );

		if ( is_null( $post_old ) ) {
			return false;
		}

		$post_content = $post_old->post_content;
		if ( ! $post_content || empty( $post_content ) ) {
			return false;
		}

		// ? keep break line for "php simple html dom" library
		// ? https://stackoverflow.com/questions/4812691/preserve-line-breaks-simple-html-dom-parser
		$html = new simple_html_dom();
		$html->load( $post_content, true, false );

		$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );

		$a_tags = $html->find( 'a' ); // ? Find a tags in the html

		$enable_open_new_tab = $lasso_url->open_new_tab;
		$enable_nofollow     = $lasso_url->enable_nofollow;

		$target_value = $enable_open_new_tab ? '_blank' : '_self';
		$rel_value    = $enable_nofollow ? 'nofollow' : null;
		// ? uncloaking amazon link
		$lasso_permalink = LASSO_AMAZON_PRODUCT_TYPE === $lasso_url->link_type ? $lasso_url->target_url : $lasso_url->permalink;

		// ? Handle urls
		foreach ( $a_tags as $a ) {
			$a_lasso_id = $a->getAttribute( 'data-lasso-id' );
			$a_lasso_id = intval( $a_lasso_id );

			// ? Skip replace a href if it's a WordPress post/page
			$is_wp_post = url_to_postid( $a->href ) > 0;
			if ( $is_wp_post ) {
				continue;
			}

			if ( in_array( $a_lasso_id, $link_location_ids ) ) { // phpcs:ignore
				$original_href = Lasso_Amazon_Api::get_amazon_product_url( $a->href );
				$a->target     = $target_value;
				$a->href       = $lasso_permalink;

				// ? Set href for amazon url
				if ( Lasso_Amazon_Api::is_amazon_url( $a->href ) && Lasso_Amazon_Api::get_product_id_by_url( $a->href ) ) {
					$original_href_tracking_id = $lasso_amazon_api->get_amazon_tracking_id_by_url( $original_href );
					$tracking_id               = $original_href_tracking_id ? $original_href_tracking_id : $lasso_amazon_api->get_amazon_tracking_id_by_url( $a->href );
					$href                      = empty( $tracking_id ) ? $original_href : Lasso_Amazon_Api::get_amazon_product_url( $a->href, true, false, $tracking_id );
					$a->href                   = $href;
				}
			}
		}

		global $wpdb;

		// ? Update new content after has applied lasso settings
		$this->update_post_content( $post_id, $html );

		// ? rescan post
		$query    = 'SELECT * FROM ' . Model::get_wp_table_name( 'posts' ) . ' WHERE ID=%d';
		$prepare  = Model::prepare( $query, $post_id ); // phpcs:ignore
		$post_new = Model::get_row( $prepare );

		$this->check_all_posts_pages( array( $post_new ) );

		return true;
	}

	/**
	 * Remove lasso attributes in link
	 *
	 * @param int $post_id Post id.
	 */
	public function remove_lasso_attributes_in_links( $post_id ) {
		$log_name = 'remove_lasso_attributes_in_links';
		Lasso_Helper::write_log( 'Post id: ' . $post_id, $log_name );

		$lasso_db = new Lasso_DB();

		$post_id  = intval( $post_id );
		$post_old = get_post( $post_id );

		if ( is_null( $post_old ) ) {
			return false;
		}

		$post_content = $post_old->post_content;
		if ( ! $post_content || empty( $post_content ) ) {
			return false;
		}

		// ? keep break line for "php simple html dom" library
		// ? https://stackoverflow.com/questions/4812691/preserve-line-breaks-simple-html-dom-parser
		$html = new simple_html_dom();
		$html->load( $post_content, true, false );

		// ? Handle urls
		$a_tags = $html->find( 'a' ); // ? Find a tags in the html
		foreach ( $a_tags as $a ) {
			Lasso_Helper::write_log( 'Link: ' . $a->outertext, $log_name );

			$a->removeAttribute( 'data-lasso-id' );
			$a->removeAttribute( 'data-lasso-name' );

			Lasso_Helper::write_log( 'New Link: ' . $a->outertext, $log_name );
		}
		$post_content = $html;

		// ? Update new content after has applied lasso settings
		$this->update_post_content( $post_id, $post_content );

		// ? remove data in link_location table
		$lasso_db->remove_all_link_location_data( $post_id );

		Lasso_Post::update_content_to_plugin( $post_id, Lasso_Post::MODE_REMOVE );

		Lasso_Helper::write_log( '===== ===== ===== End post id: ' . $post_id, $log_name );

		return true;
	}

	/**
	 * Get link_type before store data into Lasso_link_locations
	 *
	 * @param int    $lasso_id Lasso post id.
	 * @param string $href  Link.
	 *
	 * @return string
	 */
	public function get_link_type( $lasso_id, $href ) {
		$site_url = site_url();
		$lasso_id = intval( $lasso_id );

		if ( $lasso_id > 0 && get_post_type( $lasso_id ) === LASSO_POST_TYPE ) {
			$link_type = Lasso_Link_Location::LINK_TYPE_LASSO;
		} else { // ? Not any post type
			$link_type = strpos( $href, $site_url ) === 0 ? Lasso_Link_Location::LINK_TYPE_INTERNAL : Lasso_Link_Location::LINK_TYPE_EXTERNAL;
		}

		return $link_type;
	}

	/**
	 * Get all url in meta_key affiliate_homepage
	 */
	public function get_all_lasso_homepage() {
		$sql = '
			SELECT * 
			FROM ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . ' 
		';

		return Model::get_results( $sql );
	}

	/**
	 * Hourly process
	 */
	public function hourly_process() {
		$log_cron   = 'cron_hourly_process';
		$time_start = microtime( true );

		Lasso_Helper::write_log( '=== START AMAZON HOURLY ===', $log_cron );

		// 1. Make sure Amazon links use Tracking ID column where applicable
		$this->update_amazon_tracking_and_product_ids();

		// 2. Uncloak amazon link
		$this->uncloak_amazon_link();

		// 3. Match Amazon link with Lasso post
		$this->match_amazon_product();

		// ? Fix features of amazon product (imported from aawp).
		// ? Lasso_Fix::fix_amazon_features();

		// ? Fix empty details.
		// ? Lasso_Fix::fix_empty_details();

		$time_end       = microtime( true );
		$execution_time = $time_end - $time_start;
		Lasso_Helper::write_log( 'Execute time: ' . $execution_time, $log_cron );
		Lasso_Helper::write_log( '=== END AMAZON HOURLY ===', $log_cron );
	}

	/**
	 * Update Amazon product every ten minutes
	 */
	public function update_amazon_products() {
		$amazon_update_pricing_hourly = Lasso_Setting::lasso_get_setting( 'amazon_update_pricing_hourly', true );

		// ? Update Amazon Product DB Table (update pricing)
		if ( $amazon_update_pricing_hourly ) {
			$update_amazon = new Lasso_Process_Update_Amazon();
			$update_amazon->run();
		}
	}

	/**
	 * Auto monetize amazon link
	 */
	public function match_amazon_product() {
		$lasso_db         = new Lasso_DB();
		$lasso_amazon_api = new Lasso_Amazon_Api();

		$log_cron = 'cron_hourly_process';

		// ? start log
		Lasso_Helper::write_log( 'match_amazon_product', $log_cron );

		// ? updating post_id for amazon_product that are matching with lasso url
		$unlinked_links = $lasso_db->get_unlinked_amazon_product_lasso_url();
		if ( ! empty( $unlinked_links ) ) {
			foreach ( $unlinked_links as $link ) {
				$res = $lasso_amazon_api->get_monetized_link( $link->link_slug );
				if ( 'success' === $res['status'] ) {
					$m_link = $res['link'];

					// ? update posts
					Lasso_Helper::update_link_in_post( $link->detection_id, $link->link_location_id, $m_link );
				}
			}
		}
		$lasso_db->update_amazon_product_lasso_url();

		// ? end Log
		Lasso_Helper::write_log( 'match_amazon_product - end', $log_cron );
	}

	/**
	 * Auto monetize amazon link
	 */
	public function amazon_auto_monetization() {
		if ( Lasso_Setting::lasso_get_setting( 'auto_monetize_amazon', false ) ) {
			$lasso_add_amazon = new Lasso_Process_Add_Amazon();
			$lasso_add_amazon->import( 'asc' );
		}
	}

	/**
	 * Import all
	 */
	public function lasso_import_all() {
		$allow_import_all = get_option( Lasso_Process_Import_All::OPTION, '0' );
		if ( 1 === intval( $allow_import_all ) ) {
			$lasso_import_all = new Lasso_Process_Import_All();
			$lasso_import_all->import();
		}
	}

	/**
	 * Revert all
	 */
	public function lasso_revert_all() {
		$allow_revert_all = get_option( Lasso_Process_Revert_All::OPTION, '0' );
		if ( 1 === intval( $allow_revert_all ) ) {
			$lasso_revert_all = new Lasso_Process_Revert_All();
			$lasso_revert_all->revert();
		}
	}

	/**
	 * Uncloak amazon link
	 */
	public function uncloak_amazon_link() {
		$lasso_db = new Lasso_DB();

		$log_cron = 'cron_hourly_process';
		Lasso_Helper::write_log( 'uncloak_amazon_link', $log_cron );

		// ? Uncloak amazon short links in link location.
		// ? We should check if the client allow Lasso monetize their amazon links before process.
		if ( ! Lasso_Setting::lasso_get_setting( 'auto_monetize_amazon', false ) ) {
			return;
		}

		$links = $lasso_db->get_amazon_short_links_from_ll();
		foreach ( $links as $record ) {
			$original_link = Lasso_Helper::get_redirect_final_target( $record->link_slug );
			if ( $original_link !== $record->link_slug && Lasso_Amazon_Api::is_amazon_url( $original_link ) ) {
				// ? monetize link
				$original_link = Lasso_Amazon_Api::get_amazon_product_url( $original_link, true, false );

				// ? update post content
				Lasso_Helper::update_link_in_post( $record->detection_id, $record->id, $original_link );

				// ? update link location table
				$update_query = $lasso_db->update_amazon_links_ll_query( $record->id, $original_link );
				Model::query( $update_query );

				// ? update post meta
				if ( 0 !== (int) $record->post_id ) {
					// ? prepare data for saving Lasso post
					$post_id = $record->post_id;

					wp_update_post(
						array(
							'ID'         => $post_id,
							'post_title' => get_the_title( $post_id ),
							'meta_input' => array(
								'lasso_custom_redirect' => $original_link,
							),
						)
					);
				}

				Lasso_Helper::write_log( 'Uncloaked amazon short link for post_id: ' . $record->detection_id . ', cloaked link: ' . $record->link_slug . ', uncloaked link: ' . $original_link, $log_cron );
			}
		}

		Lasso_Helper::write_log( 'uncloak_amazon_link - end', $log_cron );
	}

	/**
	 * Update amazon tracking id and product id
	 */
	public function update_amazon_tracking_and_product_ids() {
		$lasso_db = new Lasso_DB();

		$log_cron = 'cron_hourly_process';
		$results  = $lasso_db->get_amazon_urls_in_ll_without_ids();

		foreach ( $results as $record ) {
			Lasso_Helper::write_log( 'update_amazon_tracking_and_product_ids: ' . $record->product_id . ' | ' . $record->link_slug, $log_cron );
			$tracking_id = Lasso_Amazon_Api::get_amazon_tracking_id_by_url( $record->link_slug );
			$product_id  = Lasso_Amazon_Api::get_product_id_by_url( $record->link_slug );
			if ( $record->tracking_id !== $tracking_id || $record->product_id !== $product_id ) {
				// ? Set tracking_id
				$link_id = $record->id;
				$lasso_db->update_ll_with_amazon_ids( $link_id, $tracking_id, $product_id );
			}
		}
	}

	/**
	 * Refresh Amazon products pricing
	 *
	 * @param string $amazon_id  Amazon product id.
	 * @param string $amazon_url Amazon url.
	 */
	public function update_amazon_pricing( $amazon_id, $amazon_url = '' ) {
		$time_start       = microtime( true );
		$lasso_amazon_api = new Lasso_Amazon_Api();

		if ( $amazon_url ) {
			$amazon_id = Lasso_Amazon_Api::get_product_id_country_by_url( $amazon_url );
		}

		$log_cron = 'update_amazon';
		Lasso_Helper::write_log( 'Update Amazon id: ' . $amazon_id, $log_cron );

		try {
			// ? if a product is checked very quick, we need to delay this in a short time
			// ? because amazon api will response an error when we request continuously
			sleep( 1 );

			$last_updated = gmdate( 'Y-m-d H:i:s', time() );
			$result       = $lasso_amazon_api->fetch_product_info( $amazon_id, true, $last_updated, $amazon_url );
			$quantity     = $result['product']['quantity'] ?? 200;
			$issue_status = 'NotFound' === $result['error_code'] ? '404' : ( intval( $quantity ) > 0 ? '200' : '000' );
			$lasso_db     = new Lasso_DB();
			$url_details  = $lasso_db->get_url_details_by_product_id( $amazon_id, 'amazon', true, $amazon_url ); // ? Allow duplicate link => Multiple affiliate/shorten links having the same amazon id
			Lasso_Helper::write_log( 'Update Amazon link: ' . $amazon_url, $log_cron );

			foreach ( $url_details as $url_detail ) {
				$this->check_issues( $url_detail->lasso_id, $issue_status, true );
			}
		} catch ( Exception $e ) {
			Lasso_Helper::write_log( 'Error: ' . $e->getMessage(), $log_cron );
		}

		$time_end       = microtime( true );
		$execution_time = round( $time_end - $time_start, 2 );
		Lasso_Helper::write_log( 'Execute time: ' . $execution_time, $log_cron );
	}

	/**
	 * Store info into table: lasso_link_location
	 *
	 * @param string $link_type        Link type. Default to empty.
	 * @param string $display_type     Display type. Default to 'link'.
	 * @param string $anchor_text      Anchor text. Default to empty.
	 * @param string $post_id          Post id. Default to empty.
	 * @param string $link_slug        Link slug. Default to empty.
	 * @param string $link_slug_domain Domnain. Default to empty.
	 * @param string $detection_id     Detection post id. Default to empty.
	 * @param string $detection_slug   Detection slug. Default to empty.
	 * @param string $tracking_id      Tracking id. Default to empty.
	 * @param string $product_id       Product id. Default to empty.
	 * @param string $no_follow        No follow. Default to 'false'.
	 * @param string $new_window       New window. Default to 'false'.
	 * @param string $detection_date   Detection date. Default to empty.
	 * @param int    $lasso_ll_id      Lasso link location id. Default to 0.
	 */
	private function write_link_locations(
		$link_type = '', $display_type = 'Text',
		$anchor_text = '', $post_id = '',
		$link_slug = '', $link_slug_domain = '',
		$detection_id = '', $detection_slug = '',
		$tracking_id = '', $product_id = '',
		$no_follow = 'false', $new_window = 'false',
		$detection_date = '', $lasso_ll_id = 0
	) {
		$detection_date     = trim( $detection_date ) ? $detection_date : gmdate( 'Y-m-d H:i:s', time() );
		$original_link_slug = $link_slug;

		$lasso_link_location = new Lasso_Link_Location( $lasso_ll_id );
		if ( $lasso_link_location->get_id() ) {
			$original_link_slug = $lasso_link_location->get_original_link_slug();

			// ? We need update detection_date to avoid delete data by delete_old_data function
			if ( $lasso_link_location->get_is_dismiss() ) {
				$lasso_link_location->set_detection_date( $detection_date );
				$lasso_link_location->update();

				return 0;
			}
		}

		global $wpdb;
		$lasso_db = new Lasso_DB();

		$original_link_slug = trim( $original_link_slug );

		$product_id = trim( $product_id ?? '' );
		$product_id = empty( $product_id ) ? null : $product_id;

		$tracking_id = trim( $tracking_id ?? '' );
		$tracking_id = empty( $tracking_id ) ? null : $tracking_id;

		$link_slug_domain = trim( $link_slug_domain ?? '' );
		$link_slug_domain = empty( $link_slug_domain ) ? null : $link_slug_domain;

		$anchor_text = trim( $anchor_text ?? '' );
		$anchor_text = empty( $anchor_text ) ? null : $anchor_text;

		$data        = array(
			'id'                 => $lasso_ll_id,
			'detection_date'     => $detection_date,
			'link_type'          => $link_type,
			'display_type'       => $display_type,
			'anchor_text'        => $anchor_text,

			'post_id'            => $post_id,
			'link_slug'          => $link_slug,
			'link_slug_domain'   => $link_slug_domain,
			'detection_id'       => $detection_id,
			'detection_slug'     => $detection_slug,

			'tracking_id'        => $tracking_id,
			'product_id'         => $product_id,
			'no_follow'          => $no_follow,
			'new_window'         => $new_window,
			'original_link_slug' => $original_link_slug,
		);
		$data_format = array(
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',

			'%d',
			'%s',
			'%s',
			'%d',
			'%s',

			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
		);

		Model::replace( // phpcs:ignore
			Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ),
			$data,
			$data_format
		);

		$link_location_id = $wpdb->insert_id;

		return $link_location_id;
	}

	/**
	 * Update content of the post
	 *
	 * @param int    $post_id      Post id.
	 * @param string $post_content Post content.
	 */
	public function update_post_content( $post_id, $post_content ) {
		global $wpdb;

		clean_post_cache( $post_id );

		// ? Update post content table wp_postmeta support Thrive plugin
		if ( Lasso_Helper::is_existing_thrive_plugin() ) {
			$thrive_active = get_post_meta( $post_id, 'tcb_editor_enabled', true );

			if ( ! empty( $thrive_active ) ) {
				$content_original = Lasso_Helper::get_thrive_plugin_post_content( $post_id, $post_content );
				$post_content     = (string) $post_content;
				$result           = Lasso_Helper::update_thrive_plugin_post_content( $post_id, $post_content );

				if ( $result ) {
					clean_post_cache( $post_id );
					Lasso_Post_Content_History::track_changes( $post_id, $content_original, $post_content );
					return $result;
				}
			}
		}

		$table_posts      = Model::get_wp_table_name( 'posts' );
		$content_original = get_post( $post_id )->post_content;
		$post_content     = (string) $post_content;
		$result           = $wpdb->update( // phpcs:ignore
			$table_posts,
			array( 'post_content' => $post_content ),
			array( 'ID' => (int) $post_id ),
			array( '%s' ),
			array( '%d' )
		);
		clean_post_cache( $post_id );
		Lasso_Post_Content_History::track_changes( $post_id, $content_original, $post_content );
		return $result;
	}

	/**
	 * Get current id of the url that was imported (id of old url)
	 *
	 * @param int    $lasso_id Lasso post id.
	 * @param string $url      URL.
	 */
	private function get_current_id_of_old_post( $lasso_id, $url ) {
		$trim_url = str_replace( array( 'http://', 'https://' ), '', $url );
		$trim_url = trim( $trim_url );
		$trim_url = trim( $trim_url, '/' ) . '/';
		$like     = '%' . Model::esc_like( $trim_url ) . '%';
		// @codingStandardsIgnoreStart
		$prepare  = Model::prepare(
			'
			SELECT `lasso_id`
			FROM ' . Model::get_wp_table_name( LASSO_REVERT_DB ) . '
			WHERE `old_uri` LIKE %s
			LIMIT 1
		',
			$like
		);
		// @codingStandardsIgnoreEnd
		$result = Model::get_col( $prepare );

		if ( count( $result ) > 0 ) {
			$id = $result[0];
		} else {
			$id = $lasso_id;

			$post_id = url_to_postid( $trim_url );

			$url  = explode( '/', $url ); // ? Split URL at "/"
			$url  = array_filter( $url ); // ? Remove empty array entries so get rid of last "/"
			$slug = array_pop( $url ); // ? Get last URL Segment

			$posts = get_posts(
				array(
					'meta_key'   => '_wp_old_slug', // phpcs:ignore
					'meta_value' => $slug, // phpcs:ignore
					'post_type'  => 'post',
				)
			);

			if ( count( $posts ) > 0 ) {
				$post_type = $posts[0]->post_type;

				if ( 'post' !== $post_type && 'page' !== $post_type ) {
					$post_id = $posts[0]->ID;
					$id      = $post_id;
				}
			}
		}

		$id = get_post_type( $id ) === LASSO_POST_TYPE ? $id : $lasso_id;

		return intval( $id );
	}

	/**
	 * When a post/page is created or updated,
	 * this function will be fired via a hook save_post
	 *
	 * @param int $post_id Post id.
	 */
	public function check_all_links_in_post_page( $post_id ) {
		// ? Start - Don't scan a post many times
		$post_ids_scanned_in_this_process = Lasso_Cache_Per_Process::get_instance()->get_cache( 'post_ids_scanned_in_save_post_hook', array() );

		if ( in_array( $post_id, $post_ids_scanned_in_this_process, true ) ) {
			return;
		}
		// ? End - Don't scan a post many times

		// ? Elementor: Skip scan post from the hook "save_post" then we will scan this post after elementor saving success
		if ( Lasso_Helper::is_built_with_elementor( $post_id ) ) {
			return;
		}

		if ( ! in_array( get_post_type( $post_id ), Lasso_Helper::get_cpt_support(), true )
			|| ! in_array( get_post_status( $post_id ), array( 'publish', 'draft' ), true ) ) {
			return;
		}

		$post_ids_scanned_in_this_process[] = $post_id;
		Lasso_Cache_Per_Process::get_instance()->set_cache( 'post_ids_scanned_in_save_post_hook', $post_ids_scanned_in_this_process );

		$bg = new Lasso_Process_Scan_Links_Post_Save();
		$bg->link_database( array( $post_id ) );
	}

	/**
	 * Delete links don't exist in post/page
	 *
	 * @param int   $post_id     Post id.
	 * @param array $exist_links Array of link location ids.
	 */
	private function delete_link_does_not_exist_in_post( $post_id, $exist_links ) {
		if ( 0 === count( $exist_links ) ) {
			$where = 'WHERE detection_id = %s';
		} elseif ( 1 === count( $exist_links ) ) {
			$where = 'WHERE id <> ' . $exist_links[0] . ' AND detection_id = %s';
		} else {
			$ids   = "'" . implode( "', '", array_filter( $exist_links ) ) . "'";
			$where = 'WHERE id NOT IN (' . $ids . ') AND detection_id = %s';
		}

		// ? Remove Link Locations meta data
		$sql_get = '
			DELETE 
			FROM ' . ( new MetaData() )->get_table_name() . '
			WHERE `object_id` NOT IN(
				SELECT `id` 
				FROM ' . ( new Link_Locations() )->get_table_name() . '
				)
			AND `type` = %s
			AND `meta_key` = %s
		';
		$sql_get = Model::prepare( $sql_get, Setting_Enum::META_LINK_LOCATION_NAME, Setting_Enum::DISPLAY_TYPE_TABLE );
		Model::query( $sql_get );

		$sql     = 'DELETE FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . ' ' . $where;
		$prepare = Model::prepare( $sql, $post_id );
		$result  = Model::query( $prepare );

		return $result;
	}

	/**
	 * Delete link location of Lasso table that does not exist.
	 *
	 * @return $this
	 */
	public function delete_metadata_link_location_table_does_not_exist() {
		$sql = '
			DELETE 
			FROM ' . Model::get_wp_table_name( 'lasso_metadata' ) . "
			WHERE 
				type='Link_Locations'
				AND meta_key='Table'
				AND object_id NOT IN(
					SELECT id
					FROM " . Model::get_wp_table_name( 'lasso_link_locations' ) . "
					WHERE display_type='Table'
						AND link_type='Lasso'
				)
		";
		Model::query( $sql );

		return $this;
	}

	/**
	 * Save post content into DB
	 *
	 * @param array  $posts    Array of post.
	 * @param string $log_name Log name.
	 */
	public function populate_lasso_content( $posts, $log_name ) {
		global $wpdb;

		$lasso_db     = new Lasso_DB();
		$lasso_helper = new Lasso_Helper();

		$lasso_displays_type = array(
			Lasso_Link_Location::DISPLAY_TYPE_SINGLE,
			Lasso_Link_Location::DISPLAY_TYPE_BUTTON,
			Lasso_Link_Location::DISPLAY_TYPE_IMAGE,
			Lasso_Link_Location::DISPLAY_TYPE_GALLERY,
		);

		$competitor_displays_type = array(
			Lasso_Link_Location::DISPLAY_TYPE_AAWP,
			Lasso_Link_Location::DISPLAY_TYPE_AMALINK,
			Lasso_Link_Location::DISPLAY_TYPE_EARNIST,
			Lasso_Link_Location::DISPLAY_TYPE_EASYAZON,
			Lasso_Link_Location::DISPLAY_TYPE_THIRSTYLINK,
		);

		$insert_data = array();

		foreach ( $posts as $post ) {
			$h2_count                   = substr_count( $post->post_content, '<h2>' );
			$img_count                  = substr_count( $post->post_content, '<img' );
			$words_count                = str_word_count( wp_strip_all_tags( $post->post_content ) );
			$link_locations_count       = $lasso_db->get_link_location_count( $post->ID );
			$incoming_links_count       = $lasso_db->get_link_location_count( $post->ID, true );
			$display_count              = Link_Locations::get_displays_type_count( $post->ID, Lasso_Link_Location::LINK_TYPE_LASSO, $lasso_displays_type );
			$grid_count                 = Link_Locations::get_displays_type_count( $post->ID, Lasso_Link_Location::LINK_TYPE_LASSO, array( Lasso_Link_Location::DISPLAY_TYPE_GRID ) );
			$list_count                 = Link_Locations::get_displays_type_count( $post->ID, Lasso_Link_Location::LINK_TYPE_LASSO, array( Lasso_Link_Location::DISPLAY_TYPE_LIST ) );
			$table_count                = Link_Locations::get_displays_type_count( $post->ID, Lasso_Link_Location::LINK_TYPE_LASSO, array( Lasso_Link_Location::DISPLAY_TYPE_TABLE ) );
			$competitor_shortcode_count = Link_Locations::get_displays_type_count( $post->ID, Lasso_Link_Location::LINK_TYPE_EXTERNAL, $competitor_displays_type );
			$lasso_link_count           = Link_Locations::get_displays_type_count( $post->ID, Lasso_Link_Location::LINK_TYPE_LASSO, array( Lasso_Link_Location::DISPLAY_TYPE_TEXT ) );
			$internal_link_count        = Link_Locations::get_displays_type_count( $post->ID, Lasso_Link_Location::LINK_TYPE_INTERNAL, array( Lasso_Link_Location::DISPLAY_TYPE_TEXT ) );
			$post_content               = $post->post_content;

			$html = new simple_html_dom();
			$html->load( $post_content, true, false );

			$insert_data['id']                           = $post->ID;
			$insert_data['post_type']                    = $post->post_type;
			$insert_data['title']                        = $post->post_title;
			$insert_data['permalink']                    = get_permalink( $post->ID );
			$insert_data['last_modified']                = $post->post_modified;
			$insert_data['author']                       = $post->post_author;
			$insert_data['words_count']                  = $words_count;
			$insert_data['h2_count']                     = $h2_count;
			$insert_data['image_count']                  = $img_count;
			$insert_data['total_link_count']             = $link_locations_count;
			$insert_data['monetized_count']              = $lasso_link_count;
			$insert_data['internal_link_count']          = $internal_link_count;
			$insert_data['incoming_internal_link_count'] = $incoming_links_count;
			$insert_data['created_at']                   = gmdate( 'Y-m-d h:i:m' );
			$insert_data['display_count']                = $display_count;
			$insert_data['grid_count']                   = $grid_count;
			$insert_data['list_count']                   = $list_count;
			$insert_data['table_count']                  = $table_count;
			$insert_data['competitor_shortcode_count']   = $competitor_shortcode_count;

			if ( Lasso_Helper::insert_lasso_content( $insert_data, $post->ID ) ) {
				Lasso_Helper::write_log( 'Lasso Content populated', $log_name );
			} else {
				Lasso_Helper::write_log( 'Record Not Inserted' . $wpdb->last_error, $log_name );
			}
		}
	}

	/**
	 * Delete old logs (older than 14 days)
	 */
	public function delete_old_logs() {
		// ? 14 days ago
		$fourteen_days_ago = gmdate( 'Y-m-d', strtotime( '-14 days', strtotime( gmdate( 'Y-m-d' ) ) ) );
		$date1             = \DateTime::createFromFormat( 'Y-m-d', $fourteen_days_ago );
		// ? get all log files
		$all_logs = glob( LASSO_PLUGIN_PATH . '/logs/*.log' );
		$dates    = array();
		foreach ( $all_logs as $logs ) {
			$dates[] = substr( basename( $logs ), 0, 10 );
		}

		$unique_dates = array_unique( $dates );
		$delete_dates = array_filter(
			$unique_dates,
			function( $date ) use ( $date1 ) {
				$date2 = \DateTime::createFromFormat( 'Y-m-d', gmdate( str_replace( '_', '-', $date ) ) );
				return $date1 > $date2;
			}
		);

		// ? delete old log files
		foreach ( $delete_dates as $date ) {
			array_map(
				function( $file ) {
					if ( file_exists( $file ) ) {
						unlink( $file );
					}
				},
				glob( LASSO_PLUGIN_PATH . "/logs/$date*.log" )
			);
		}
	}

	/**
	 * Run background process: sync content
	 */
	public function lasso_data_sync_content() {
		$lasso_sync_content = new Lasso_Process_Data_Sync_Content();
		$lasso_sync_content->sync_content( 'diff' );
	}

	/**
	 * Run background process: sync full content
	 */
	public function lasso_data_sync_content_full() {
		$lasso_sync_content = new Lasso_Process_Data_Sync_Content();
		$lasso_sync_content->sync_content( 'full' );
	}

	/**
	 * Sync affiliate programs data from DWH
	 */
	public function lasso_data_sync_affiliate_programs() {
		$lasso_db = new Lasso_DB();

		$option_name              = 'lasso_affiliate_programs_last_modified';
		$log_name                 = 'data_sync_affiliate_programs';
		$affiliate_programs_table = Model::get_wp_table_name( LASSO_AFFILIATE_PROGRAMS );

		try {
			// ? insert new ids
			$last_modified = get_option( $option_name, '' );
			$url           = LASSO_LINK . '/data-sync/data?post_modified=' . $last_modified;
			$headers       = Lasso_Helper::get_lasso_headers();
			$res           = Lasso_Helper::send_request( 'get', $url, array(), $headers );
			$status_code   = intval( $res['status_code'] ?? 500 );

			// ? invalid license
			if ( 200 !== $status_code ) {
				return false;
			}

			$data = $res['response'] ?? array();
			$data = is_object( $data ) && isset( $data->data ) ? $data->data : $data;

			list($result, $ids) = $lasso_db->wpdb_bulk_insert( $affiliate_programs_table, $data );

			$last_item          = $data[ count( $data ) - 1 ] ?? null;
			$last_post_modified = $last_item->post_modified ?? '';
			if ( $last_post_modified ) {
				update_option( $option_name, $last_post_modified );
			}

			Lasso_Helper::write_log( 'Result: ' . $result, $log_name );
			Lasso_Helper::write_log( 'Run items: ' . count( $ids ), $log_name );
			// ? insert new ids - end

			// ? delete old ids
			$index    = get_option( Affiliate_Programs::OPTION_INDEX, 1 );
			$url_data = array(
				'get_id' => $index,
			);

			$encrypted_base64 = Encrypt::encrypt_aes( $url_data, true );
			$url              = LASSO_LINK . '/data-sync/data?' . $encrypted_base64;
			$res              = Lasso_Helper::send_request( 'get', $url, array(), $headers );
			$status_code      = intval( $res['status_code'] ?? 500 );

			// ? invalid license
			if ( 200 !== $status_code ) {
				return false;
			}

			$data = $res['response'];
			Affiliate_Programs::delete_old_items( $data );
			// ? delete old ids - end

			return $result;
		} catch ( \Exception $e ) {
			Lasso_Helper::write_log( 'Error: ' . $e->getMessage(), $log_name );
		}
	}

	/**
	 *
	 * Sync plugins data to Lasso server
	 */
	public function lasso_data_sync_plugins() {
		// ? Send install data to lasso server
		Lasso_License::lasso_getinfo();

		// ? Report in
		$data    = array(
			'plugins' => Lasso_Helper::get_plugins_information(),
		);
		$data    = Encrypt::encrypt_aes( $data );
		$headers = Lasso_Helper::get_lasso_headers();

		Lasso_Helper::send_request( 'post', LASSO_LINK . '/server/update-plugins', $data, $headers );

		return true;
	}

	/**
	 * Check custom fields
	 *
	 * @param int $post_id Post id.
	 */
	public function scan_custom_fields( $post_id ) {
		$post_type = get_post_type( $post_id );

		// ? Only work with CPT that is supported
		if ( ! in_array( $post_type, Lasso_Helper::get_cpt_support(), true ) ) {
			return;
		}

		// ? Discount URL of WP Coupons plugin
		$wp_coupons_discount_url = get_post_meta( $post_id, 'wp_coupons_discount_url', true );

		if ( $wp_coupons_discount_url && Lasso_Helper::is_wp_coupons_plugin_actived() ) {
			$lasso_id  = $this->get_current_id_of_old_post( 0, $wp_coupons_discount_url );
			$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );
			if ( $lasso_url->lasso_id > 0 ) {
				update_post_meta( $post_id, 'wp_coupons_discount_url', $lasso_url->public_link );
			}
		}
	}

	/**
	 * Cron Get list final urls from pretty link
	 */
	public function lasso_get_pretty_link_final_urls() {
		$site_domain   = Lasso_Helper::get_base_domain( site_url() );
		$allow_domains = Lasso_Verbiage::SUPPORT_SITES['fix_link_changed_to_destination_url_issue_by_pretty_link_data'];
		if ( ! Lasso_Helper::is_pretty_link_plugin_active() || ! in_array( $site_domain, $allow_domains, true ) ) {
			return false;
		}

		$pretty_link_final_url_process = new Lasso_Process_Pretty_Link_Final_Url();
		$pretty_link_final_url_process->process();
	}

	/**
	 * Monthly sync existing link locations ids
	 */
	public function lasso_monthly_data_sync_link_locations() {
		$lasso_sync_link_locations = new Lasso_Data_Sync_Link_Location();

		// ? Sync existing link location id to delete link locations that are deleted in WP
		$lasso_sync_link_locations->sync_existing_link_locations();

		// ? Sync full monthly by reset lasso_submission_date to make sure we get all data
		$lasso_sync_link_locations->reset_submission_date();
	}

	/**
	 * Run background process: sync link locations
	 */
	public function lasso_daily_data_sync_link_locations() {
		$lasso_process_sync_link_locations = new Lasso_Process_Data_Sync_Link_Location();
		$lasso_process_sync_link_locations->sync_link_location();
	}

	/**
	 * Monthly sync existing lasso link ids
	 */
	public function lasso_monthly_data_sync_lasso_links() {
		$lasso_sync_lasso_links = new Lasso_Data_Sync_Lasso_Links();

		// ? Sync existing lasso link ids to delete lasso links that are deleted in WP
		$lasso_sync_lasso_links->sync_publishing_lasso_links();

		// ? Sync full monthly by reset lasso_submission_date to make sure we get all data
		$lasso_sync_lasso_links->reset_submission_date();
	}

	/**
	 * Run background process: sync lasso links
	 */
	public function lasso_daily_data_sync_lasso_links() {
		$lasso_process_sync_lasso_links = new Lasso_Process_Data_Sync_Lasso_Links();
		$lasso_process_sync_lasso_links->sync_lasso_links();
	}

	/**
	 * Monthly sync existing author ids
	 */
	public function lasso_monthly_data_sync_authors() {
		$lasso_sync_authors = new Lasso_Data_Sync_Authors();

		// ? Sync existing author ids to delete authors that are deleted in WP
		$lasso_sync_authors->sync_existing_authors();

		// ? Sync full monthly by reset lasso_submission_date to make sure we get all data
		$lasso_sync_authors->reset_submission_date();
	}

	/**
	 * Run background process: sync wp authors
	 */
	public function lasso_daily_data_sync_authors() {
		$lasso_process_sync_authors = new Lasso_Process_Data_Sync_Authors();
		$lasso_process_sync_authors->sync_authors();
	}

	/**
	 * Fix kadence's block links
	 *
	 * @param object $a     A tag object.
	 * @param object $block Block data.
	 * @param int    $index A tag index.
	 * @return object
	 */
	public function fix_kadence_block_links( $a, $block, $index ) {
		try {
			$block_name = $block['blockName'];
			if ( 'kadence/advancedbtn' === $block_name ) {
				$btn_attrs  = $block['attrs']['btns'][ $index ];
				$origin_url = $btn_attrs['link'] ? $btn_attrs['link'] : '#';
				$rel        = '';

				$btn_attrs['target']    = $btn_attrs['target'] ?? '_self';
				$btn_attrs['noFollow']  = $btn_attrs['noFollow'] ?? false;
				$btn_attrs['sponsored'] = $btn_attrs['sponsored'] ?? false;

				$a->setAttribute( 'href', $origin_url );

				// ? rel attribute order: noreferrer noopener nofollow sponsored
				if ( '_self' === $btn_attrs['target'] ) {
					$a->setAttribute( 'target', null );
				}

				if ( '_blank' === $btn_attrs['target'] ) {
					$rel = 'noreferrer noopener';
				}

				if ( true === $btn_attrs['noFollow'] ) {
					$rel .= ( $rel ? ' ' : '' ) . 'nofollow';
				}

				if ( true === $btn_attrs['sponsored'] ) {
					$rel .= ( $rel ? ' ' : '' ) . 'sponsored';
				}

				if ( $rel ) {
					$a->setAttribute( 'rel', $rel );
				}
			}

			if ( 'kadence/iconlist' === $block_name ) {
				$rel_attribute = $a->getAttribute( 'rel' );
				if ( false !== strpos( $rel_attribute, 'noopener' ) && false === strpos( $rel_attribute, 'noreferrer' ) ) {
					$a->setAttribute( 'rel', 'noreferrer ' . $rel_attribute );
				}
			}

			if ( in_array( $block_name, array( 'kadence/advancedgallery', 'kadence/infobox' ), true ) ) {
				$rel_attribute = $a->getAttribute( 'rel' );
				if ( ( false !== strpos( $rel_attribute, 'noopener' ) ) && ( false === strpos( $rel_attribute, 'noreferrer' ) ) ) {
					$rel_attribute = str_replace( 'noopener', 'noopener noreferrer', $rel_attribute );
					$a->setAttribute( 'rel', $rel_attribute );
				}
			}
		} catch ( \Exception $e ) {
			Lasso_Helper::write_log( 'Error: ' . $e->getMessage(), Lasso_Log::ERROR_LOG );
		}

		return $a;
	}

	/**
	 * Fix amalinkspro's block links
	 *
	 * @param object $a     A tag object.
	 * @param object $block Block data.
	 * @param int    $index A tag index.
	 * @return object
	 */
	public function fix_amalinkspro_block_links( $a, $block, $index ) {
		try {
			$block_name = $block['blockName'];
			if ( in_array( $block_name, self::AMALINKSPRO_FIX_LINK_BLOCK, true ) ) {
				$data_lasso_id = $a->getAttribute( 'data-lasso-id' );

				if ( $data_lasso_id ) {
					$origin_url  = $block['attrs']['href'];
					$origin_url  = htmlentities( $origin_url );
					$link_target = $block['attrs']['linkTarget'] ?? true;
					$no_follow   = $block['attrs']['noFollow'] ?? true;

					if ( true === $link_target ) {
						$link_target_string = '_blank';

						if ( true === $no_follow ) {
							$no_follow_string = 'nofollow noopener noreferrer';
						} else {
							$no_follow_string = 'noopener noreferrer';
						}
					} else {
						$link_target_string = '';

						if ( true === $no_follow ) {
							$no_follow_string = 'nofollow noopener noreferrer';
						} else {
							$no_follow_string = 'noopener noreferrer';
						}
					}

					$a->setAttribute( 'href', $origin_url );
					$a->setAttribute( 'target', $link_target_string );
					$a->setAttribute( 'rel', $no_follow_string );
				}
			}
		} catch ( \Exception $e ) {
			Lasso_Helper::write_log( 'Error: ' . $e->getMessage(), Lasso_Log::ERROR_LOG );
		}

		return $a;
	}

	/**
	 * Fix block content issues
	 *
	 * @param string $post_content Post content.
	 * @param object $block        Block data.
	 * @return string
	 */
	public function fix_block_content_issues( $post_content, $block ) {
		try {
			$block_name = $block['blockName'];

			// ? Fix "embed" block attempt issue with amazon link
			if ( 'core/embed' === $block_name ) {
				$html_str = $block['innerHTML'];

				if ( false !== strpos( $html_str, 'lasso amazon_url' ) ) {
					$url  = $block['attrs']['url'];
					$html = new simple_html_dom();
					$html->load( $html_str, true, false );
					$html->find( 'div.wp-block-embed__wrapper', 0 )->innertext = $url;

					$new_post_content = str_replace( (string) $html_str, (string) $html, $post_content );
					$post_content     = '' !== $new_post_content ? $new_post_content : $post_content;
				}
			}
		} catch ( \Exception $e ) {
			Lasso_Helper::write_log( 'Error: ' . $e->getMessage(), Lasso_Log::ERROR_LOG );
		}

		return $post_content;
	}

	/**
	 * Scan SiteStripe image URL
	 *
	 * @param string $img_url            Image URL.
	 * @param int    $post_id            Post ID.
	 * @param string $post_content       Post content.
	 * @param array  $lasso_location_ids Link location IDs.
	 */
	public function scan_site_stripe_image_url( $img_url, $post_id, &$post_content, &$lasso_location_ids ) {
		if ( ! $img_url ) {
			return $img_url;
		}

		$img_html = '<img src="' . $img_url . '" />';
		$html     = new simple_html_dom();
		$html->load( $img_html, true, false );

		$img_tags = $html->find( 'img' ); // ? Find image tags in the html (SiteStripe images)
		$this->scan_site_stripe_data_image( $post_id, $img_tags, $lasso_location_ids );
		preg_match( '/<img.*?src=["\'](.*?)["\'].*?>/i', (string) $html, $matches ); // ? get the url from the image tag
		$new_img_url = $matches[1] ?? $img_url;

		$new_post_content = str_replace( $img_url, $new_img_url, $post_content );
		$post_content     = '' !== $new_post_content ? $new_post_content : $post_content;

		$img_url_regex = str_replace( '&', '\u0026', $img_url );
		$img_url_regex = preg_quote( $img_url_regex, '/' );
		$regex         = '/(' . $img_url_regex . ')/';

		$new_post_content = preg_replace( $regex, $new_img_url, $post_content );
		$post_content     = '' !== $new_post_content ? $new_post_content : $post_content;

		return $new_img_url;
	}

	/**
	 * Fix block content issues
	 *
	 * @param int    $post_id            Post ID.
	 * @param string $post_content       Post content.
	 * @param array  $lasso_location_ids Link location IDs.
	 */
	public function scan_site_stripe_blocks( $post_id, &$post_content, &$lasso_location_ids ) {
		$all_blocks = array();
		self::get_all_blocks( $post_content, array(), $all_blocks );

		foreach ( $all_blocks as $b_key => $block ) {
			$block_name = $block['blockName'];

			if ( 'genesis-custom-blocks/amazon-right-aligned-image' === $block_name ) {
				$attrs   = $block['attrs'];
				$img_url = $attrs['image-url'] ?? '';
				$this->scan_site_stripe_image_url( $img_url, $post_id, $post_content, $lasso_location_ids );
			} elseif ( 'genesis-custom-blocks/comparison-table' === $block_name ) {
				$attrs = $block['attrs'];
				foreach ( $attrs as $a_key => $attr ) {
					if ( 'product-1-image' === $a_key || strpos( $a_key, 'product-image-row-' ) !== false ) {
						$img_url = $attr;
						$this->scan_site_stripe_image_url( $img_url, $post_id, $post_content, $lasso_location_ids );
					}
				}
			} elseif ( 'genesis-custom-blocks/buy-box' === $block_name ) {
				$attrs   = $block['attrs'];
				$img_url = $attrs['image'] ?? '';
				$this->scan_site_stripe_image_url( $img_url, $post_id, $post_content, $lasso_location_ids );
			} elseif ( 'core/image' === $block_name ) {
				self::fix_lasso_shortcode_in_image_blocks( $post_content, $block['innerHTML'] );
			}
		}
	}

	/**
	 * Fix lasso shortcode in image blocks
	 *
	 * @param string $post_content Post content.
	 * @param string $block_html   Block HTML.
	 */
	private static function fix_lasso_shortcode_in_image_blocks( &$post_content, $block_html ) {
		$regex = '~<figure class="wp-block-image(.*?)>(.*?)(\[lasso (.*?)\])(.*?)<\/figure>~';

		if ( ! preg_match( $regex, $block_html, $matches ) ) {
			return;
		}
		$lasso_shortcode = $matches[3] ?? '';

		$shortcode_pattern = get_shortcode_regex();
		if ( preg_match( "/$shortcode_pattern/", $lasso_shortcode, $matches ) ) {
			$shortcode = $matches[0];
			if ( 'lasso' === $matches[2] ) {
				$lasso_helper = new Lasso_Helper();
				$attrs        = $lasso_helper->get_attributes( $shortcode );
				$id           = $attrs['id'] ?? 0;
				if ( $id > 0 && LASSO_POST_TYPE === get_post_type( $id ) && 'publish' === get_post_status( $id ) ) {
					$lasso_url      = Lasso_Affiliate_Link::get_lasso_url( $id );
					$amazon_img_url = $lasso_url->amazon->default_image ?? '';
					if ( $amazon_img_url ) {
						$img_html         = '<img src="' . $amazon_img_url . '" alt="' . $lasso_url->name . '" />';
						$new_post_content = str_replace( $shortcode, $img_html, $post_content );
						$post_content     = '' !== $new_post_content ? $new_post_content : $post_content;
					}
				}
			}
		}
	}

	/**
	 * Fix content before scannning
	 *
	 * @param string $post_content Post content.
	 *
	 * @return string
	 */
	public function fix_content_before_scanning( $post_content ) {
		$new_content  = preg_replace( '/<iframe[^>]*>(?!<\/iframe>)/', '$0</iframe>', $post_content );
		$post_content = ! empty( $new_content ) ? $new_content : $post_content;

		$new_content  = str_replace( '<a href="https://[gift_item link=&quot;', '<a href="', $post_content );
		$post_content = ! empty( $new_content ) ? $new_content : $post_content;

		$new_content  = str_replace( '][/amazon" link_id="', '" link_id="', $post_content );
		$post_content = ! empty( $new_content ) ? $new_content : $post_content;

		$new_content  = str_replace( ']<meta charset="utf-8">[/amazon]', ']', $post_content );
		$post_content = ! empty( $new_content ) ? $new_content : $post_content;

		if ( function_exists( 'has_block' )
			&& ( has_block( 'paragraph', $post_content ) || has_block( 'heading', $post_content ) )
		) {
			$new_content  = str_replace( '<p><!-- wp:', '<!-- wp:', $post_content );
			$post_content = ! empty( $new_content ) ? $new_content : $post_content;

			$new_content  = str_replace( ' --></p>', ' -->', $post_content );
			$post_content = ! empty( $new_content ) ? $new_content : $post_content;

			$new_content  = str_replace( '<p><!-- /wp:', '<!-- /wp:', $post_content );
			$post_content = ! empty( $new_content ) ? $new_content : $post_content;
		}

		return $post_content;
	}

	/**
	 * Fix content before scannning
	 *
	 * @param string $post_content Post content.
	 *
	 * @return string
	 */
	public function fix_sitestripe_content_before_scanning( $post_content ) {
		$site_url    = site_url();
		$site_domain = Lasso_Helper::get_base_domain( $site_url );

		if ( 'giangiskitchen.com' === $site_domain ) {
			$separator        = 'Shop This Post';
			$splitted_content = explode( $separator, $post_content );
			$count            = count( $splitted_content );
			if ( $count > 1 ) {
				$last_element = $splitted_content[ $count - 1 ];

				$new_string   = preg_replace( '~\[lasso id="(.*?)~', '[lasso sitestripe="true" id="', $last_element );
				$last_element = $new_string ? $new_string : $last_element;

				$new_string   = preg_replace( '~\[lasso ref="(.*?)~', '[lasso sitestripe="true" ref="', $last_element );
				$last_element = $new_string ? $new_string : $last_element;

				// ? replacement for gutenberg editor
				$new_string   = preg_replace( '~\[lasso id=\\\u0022(.*?)~', '[lasso sitestripe=\\\u0022true\\u0022 id=\\\u0022', $last_element );
				$last_element = $new_string ? $new_string : $last_element;

				$new_string   = preg_replace( '~\[lasso ref=\\\u0022(.*?)~', '[lasso sitestripe=\\\u0022true\\\u0022 ref=\\\u0022', $last_element );
				$last_element = $new_string ? $new_string : $last_element;

				$splitted_content[ $count - 1 ] = $last_element;

				$post_content = implode( $separator, $splitted_content );
			}
		}

		return $post_content;
	}

	/**
	 * Update license status.
	 */
	public function lasso_update_license_status() {
		Lasso_License::check_user_license();
	}

	/**
	 * Check if this site allow to run "Update category for imported pretty link"
	 *
	 * @return bool
	 */
	public function is_allow_update_category_for_imported_pretty_link() {
		$site_domain = Lasso_Helper::get_base_domain( site_url() );
		if ( in_array( $site_domain, Lasso_Verbiage::SUPPORT_SITES['update_category_for_imported_pretty_link'], true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Run background process: Update category for imported pretty link
	 */
	public function lasso_update_category_for_imported_pretty_link() {
		$background_process = new Lasso_Process_Update_Category_For_Imported_Pretty_Links();
		$background_process->process();
	}

	/**
	 * Sync and encrypt Amazon API
	 */
	public function lasso_sync_and_encrypt_amazon_api() {
		$lasso_options = Lasso_Setting::lasso_get_settings();

		// ? won't send request if the keys are empty
		if ( ! $lasso_options['amazon_access_key_id'] || ! $lasso_options['amazon_secret_key']
			|| ! $lasso_options['amazon_tracking_id'] || ! $lasso_options['amazon_default_tracking_country']
		) {
			return false;
		}

		$data    = array(
			'access'      => $lasso_options['amazon_access_key_id'],
			'secret'      => $lasso_options['amazon_secret_key'],
			'tracking_id' => $lasso_options['amazon_tracking_id'],
			'country'     => $lasso_options['amazon_default_tracking_country'],
		);
		$headers = Lasso_Helper::get_lasso_headers();
		$url     = LASSO_LINK . '/encrypt/amazon';

		$body = Encrypt::encrypt_aes( $data );
		$res  = Lasso_Helper::send_request( 'post', $url, $body, $headers );

		return $res;
	}

	/**
	 * Cron: Create Lasso webp image
	 */
	public function lasso_create_webp_image() {
		$bp = new Lasso_Process_Create_Webp_Image();
		$bp->process();
	}

	/**
	 * Cron: Create Lasso webp image for table
	 */
	public function lasso_create_webp_image_table() {
		$bp = new Lasso_Process_Create_Webp_Image_Table();
		$bp->process();
	}

	/**
	 * Auto Monetize links
	 */
	public function lasso_auto_monetize() {
		if ( Lasso_Setting::lasso_get_setting( 'auto_monetize_affiliates' ) ) {
			$bp = new Lasso_Process_Auto_Monetize();
			$bp->run();
		}
	}

	/**
	 * Scan ACF field content.
	 *
	 * @param object $post               WP Post object.
	 * @param array  $lasso_location_ids Lasso link location ids.
	 * @return $this
	 */
	public function scan_acf_fields( $post, &$lasso_location_ids ) {
		$selected_custom_fields = Lasso_Setting::lasso_get_setting( 'custom_fields_support', array() );

		if ( ! empty( $selected_custom_fields ) ) {
			$acf_postmetas = Lasso_DB::get_acf_postmetas( $post->ID, $selected_custom_fields );

			if ( $acf_postmetas && ! empty( $acf_postmetas ) ) {
				foreach ( $acf_postmetas as $acf_postmeta ) {
					$new_acf_meta_value = $this->scan_post_content( $post, $acf_postmeta->meta_value, $lasso_location_ids, false );
					if ( $new_acf_meta_value && ! Lasso_Helper::compare_string( $new_acf_meta_value, $acf_postmeta->meta_value ) ) {
						update_post_meta( $post->ID, $acf_postmeta->meta_key, $new_acf_meta_value );
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Delete post content history (older than 14 days)
	 */
	public function delete_post_content_history() {
		$sql = '
			DELETE 
				FROM ' . Model::get_wp_table_name( LASSO_POST_CONTENT_HISTORY ) . '
				WHERE UNIX_TIMESTAMP(updated_date) < UNIX_TIMESTAMP(%s)
		';

		// ? 14 days ago
		$fourteen_days_ago = gmdate( 'Y-m-d', strtotime( '-14 days' ) ) . ' 23:59:59';
		$sql               = Model::prepare( $sql, $fourteen_days_ago );
		Model::query( $sql );
	}

	/**
	 * Switch AAWP block to shortcode
	 *
	 * @param int    $post_id            WP Post id.
	 * @param string $post_content       WP Post content.
	 * @param array  $lasso_location_ids Lasso link location ids.
	 *
	 * @return string
	 */
	public function switch_aawp_block_to_shortcode( $post_id, $post_content, &$lasso_location_ids ) {
		$lasso_db = new Lasso_Db();
		if ( function_exists( 'has_block' ) ) {
			$blocks = parse_blocks( $post_content );
			foreach ( $blocks as $block ) {
				if ( 'aawp/aawp-block' === $block['blockName'] ) {
					$aawp_pattern = '/<!--\s*wp:aawp\/aawp-block[^>]*>/';
					preg_match_all( $aawp_pattern, $post_content, $matches_aawp );
					if ( count( $matches_aawp ) > 0 ) {
						foreach ( $matches_aawp[0] as $code ) {
							$pattern_fix_json = '/\{.*?\}/';
							if ( preg_match( $pattern_fix_json, $code, $matches_json ) ) {
								// ? Extracted JSON data is in $matches[0]
								$json_string = $matches_json[0];
								$attributes  = json_decode( $json_string, true );

								$link_id = 0;
								if ( isset( $attributes['className'] ) && ! empty( $attributes['className'] ) ) {
									preg_match( '/' . Lasso_Enum::LASSO_LL_ATTR . '-(\d+)/', $attributes['className'], $matches );
									$link_id = intval( $matches[1] ?? 0 );
								}

								$shortcode = $code; // ? AAWP block content

								$link_type    = Lasso_Link_Location::LINK_TYPE_EXTERNAL;
								$display_type = Lasso_Link_Location::DISPLAY_TYPE_AAWP;
								$anchor_text  = null;
								$lasso_id     = 0;

								$aawp_table_id    = 0;
								$link_slug_domain = null;
								$detection_slug   = get_the_permalink( $post_id );
								$tracking_id      = $attributes['tracking_id'] ?? '';
								$product_id       = $attributes['asin'] ?? '';
								$no_follow        = 'true';
								$new_window       = 'false';
								$current_date     = gmdate( 'Y-m-d H:i:s' );
								$look             = $attributes['look'] ?? '';

								$is_post_imported_into_lasso = false;
								if ( in_array( $look, array( 'box', 'fields', 'link' ), true ) ) {
									$product_url = Lasso_Amazon_Api::get_default_product_domain( $product_id );
									$lasso_id    = $lasso_db->get_lasso_id_by_product_id_and_type( $product_id, Lasso_Amazon_Api::PRODUCT_TYPE, $product_url );
									if ( $lasso_id ) {
										$is_post_imported_into_lasso = Lasso_Import::is_post_imported_into_lasso( $lasso_id );
									}

									if ( in_array( $look, array( 'fields', 'link' ), true ) ) {
										if ( 'fields' === $look ) {
											$attributes['value'] = $attributes['value_attr'] ?? '';
										} elseif ( 'link' === $look ) {
											$attributes['value'] = $look;
										}

										$attributes['fields'] = $product_id;
										unset( $attributes['box'] );
									}
								} elseif ( 'table' === $attributes['look'] ) {
									$aawp_table_id               = $attributes['table']; // ? get table ID
									$is_post_imported_into_lasso = Lasso_Import::is_post_imported_into_lasso( $aawp_table_id, 'aawp_table' );
								}

								$link_location_id = $this->write_link_locations(
									$link_type,
									$display_type,
									$anchor_text,
									$lasso_id,
									$shortcode,
									$link_slug_domain,
									$post_id,
									$detection_slug,
									$tracking_id,
									$product_id,
									$no_follow,
									$new_window,
									$current_date,
									$link_id
								);

								// ? Keep the block and add Lasso class to know we scanned it.
								if ( $link_location_id > 0 && ! $is_post_imported_into_lasso ) {
									$lasso_location_ids[] = $link_location_id;

									if ( isset( $attributes['className'] ) && ! empty( $attributes['className'] ) ) {
										$classname = preg_replace( '/' . Lasso_Enum::LASSO_LL_ATTR . '-(\d+)/', '', $attributes['className'] );
										$classname = preg_replace( '/\s+/', ' ', $classname );

										$attributes['className'] = trim( $classname ) . ' ' . Lasso_Enum::LASSO_LL_ATTR . '-' . $link_location_id;
									} else {
										$attributes['className'] = Lasso_Enum::LASSO_LL_ATTR . '-' . $link_location_id;
									}

									$block_content_aawp = '<!-- wp:aawp/aawp-block ' . wp_json_encode( $attributes ) . ' /-->';
									$pos                = strpos( $post_content, $code );
									if ( false !== $pos ) {
										$post_content = substr_replace( $post_content, $block_content_aawp, $pos, strlen( $code ) );
									}
								} elseif ( $link_location_id > 0 && ( $post_id || $aawp_table_id ) && $is_post_imported_into_lasso ) {
									// ? Replace the block by shortcode
									if ( $aawp_table_id > 0 ) {
										// ? Table doesn't has asin
										$attributes['box'] = 'table';
									} else {
										$attributes['box'] = $product_id;
									}
									$attributes['link_id'] = $link_location_id;
									$shortcode             = '[aawp ';
									foreach ( $attributes as $key => $value ) {
										$shortcode .= $key . '="' . $value . '" ';
									}

									$shortcode = rtrim( $shortcode ) . ']';
									$pos       = strpos( $post_content, $code );
									if ( false !== $pos ) {
										$post_content = substr_replace( $post_content, $shortcode, $pos, strlen( $code ) );
									}
								}
							}
						}
					}
				}
			}
		}

		return $post_content;
	}

	/**
	 * Remove duplicate background processes
	 */
	public function lasso_remove_duplicate_processes() {
		$processes = Lasso_Verbiage::PROCESS_DESCRIPTION;

		foreach ( $processes as $process => $desc ) {
			$class = new $process();
			$class->remove_duplicated_processes();
		}

		$lasso_db = new Lasso_DB();
		$lasso_db->check_empty_process();
	}

	/**
	 * Force scan all links
	 */
	public function lasso_force_scan_all_links() {
		$force_scan = new Lasso_Process_Force_Scan_All_Posts();
		$force_scan->force_to_run_new_scan();
	}
}
