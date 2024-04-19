<?php
/**
 * Declare class Lasso_Process_Add_Amazon
 *
 * @package Lasso_Process_Add_Amazon
 */

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Import as Lasso_Import;
use Lasso\Classes\Link_Location as Lasso_Link_Location;
use Lasso\Classes\Setting as Lasso_Setting;

use Lasso\Models\Amazon_Shortened_Url;
use Lasso\Models\Model;

/**
 * Lasso_Process_Add_Amazon
 */
class Lasso_Process_Add_Amazon extends Lasso_Process {
	const LIMIT = 100;

	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_add_amazon_link';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'add_amazon_link';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $data Queue item to iterate over.
	 *
	 * @return mixed
	 */
	public function task( $data ) {
		$start_time = microtime( true );

		$lasso_affiliate_link = new Lasso_Affiliate_Link();
		$lasso_amazon_api     = new Lasso_Amazon_Api();
		$lasso_import         = new Lasso_Import();

		Lasso_Helper::write_log( 'START Import process for: ' . strval( $data ), $this->log_name );

		// ? Amazon shortened link: Adding tracking id from final url if existed
		$is_amazon_shortened_url = Lasso_Amazon_Api::is_amazon_shortened_url( $data );
		$original_url            = $data;
		if ( $is_amazon_shortened_url ) {
			$final_url   = Lasso_Helper::get_redirect_final_target( $data );
			$tracking_id = Lasso_Amazon_Api::get_amazon_tracking_id_by_url( $final_url );
			$data        = $final_url;

			if ( $tracking_id ) {
				// Check and filter compare with blacklist tracking ids.
				$amazon_blacklist_tracking_ids = Lasso_Helper::get_option( 'amazon_tracking_id_whitelist', array() );

				if ( ! in_array( $tracking_id, $amazon_blacklist_tracking_ids, true ) ) {
					Lasso_Helper::update_amazon_whitelist_ids( array( $tracking_id ) );
				}
			}
		}

		$amazon_id = Lasso_Amazon_Api::get_product_id_by_url( $data );
		if ( Lasso_Amazon_Api::is_amazon_url( $data ) && $amazon_id ) {
			$product_data = false;
			if ( Model::table_exists( Model::get_wp_table_name( 'aawp_products' ) ) ) {
				$product_data = $lasso_import->import_aawp_amazon_product_into_lasso( $amazon_id );
			}
			if ( empty( $product_data ) ) {
				$lasso_amazon_api->fetch_product_info( $amazon_id, true, false, $data );
			}
		}

		// ? check whether product is exist
		$lasso_post_id = Lasso_Affiliate_Link::is_lasso_url_exist( $data, $data );
		if ( $lasso_post_id > 0 ) {
			sleep( 1 );
			return false;
		}

		$lasso_id    = $lasso_affiliate_link->lasso_add_a_new_link( $data );
		$log_message = 'Failed to create lasso url for amazon product';

		if ( is_integer( $lasso_id ) && $lasso_id > 0 && LASSO_POST_TYPE === get_post_type( $lasso_id ) ) {
			$amazon_id = Lasso_Amazon_Api::get_product_id_by_url( $data );

			// ? save shortened url and final url
			if ( $is_amazon_shortened_url ) {
				Amazon_Shortened_Url::upsert( $original_url, $data, $lasso_id );
			}

			// ? fix link location is zero
			if ( $amazon_id ) {
				$sql    = '
					UPDATE ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . ' 
					SET post_id = %d, link_type = %s
					WHERE product_id = %s';
				$sql    = Model::prepare( $sql, $lasso_id, Lasso_Link_Location::LINK_TYPE_LASSO, $amazon_id );
				$result = Model::query( $sql );

				$log_message = 'Successfully created Lasso url for amazon product: ' . $amazon_id . ' - Lasso id: ' . $lasso_id . ' - Row effected: ' . $result;
			} else {
				// ? Update link_locations.post_id for the processed url
				$sql    = '
					UPDATE ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . ' 
					SET post_id = %d, link_type = %s
					WHERE link_slug = %s';
				$sql    = Model::prepare( $sql, $lasso_id, Lasso_Link_Location::LINK_TYPE_LASSO, $data );
				$result = Model::query( $sql );

				$log_message = 'Successfully created Lasso url for amazon url: ' . strval( $data ) . ' - Lasso id: ' . $lasso_id . ' - Row effected: ' . $result;
			}

			$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );
			$new_title = '';

			// ? fix wrong post title
			if ( 'Amazon' === $lasso_url->name && ! empty( $lasso_url->amazon->default_product_name ) ) {
				$new_title = $lasso_url->amazon->default_product_name;
			}

			// ? update title for amazon search page
			if ( Lasso_Amazon_Api::is_amazon_search_page( $data ) ) {
				$new_title = Lasso_Amazon_Api::get_search_page_title( $data );
			}

			if ( $new_title ) {
				wp_update_post(
					array(
						'ID'         => $lasso_id,
						'post_title' => $new_title,
					)
				);
			}

			// ? fix duplicate issue
			if ( 0 === $lasso_url->lasso_id ) {
				wp_delete_post( $lasso_id );
			}
		}

		Lasso_Helper::write_log( $log_message, $this->log_name );

		$time_end       = microtime( true );
		$execution_time = round( $time_end - $start_time, 2 );
		Lasso_Helper::write_log( 'Completed Import process - End, total process time ' . $execution_time, $this->log_name );

		sleep( 1 );

		return false;
	}

	/**
	 * Prepare data for process
	 *
	 * @param string $order_by Order by. Default to asc.
	 */
	public function import( $order_by = 'asc' ) {
		// ? check whether process is age out and make it can work on Lasso UI via ajax requests
		$this->is_process_age_out();

		if ( $this->is_process_running_reach_the_limit() ) {
			$this->push_to_lasso_processes_queue( __CLASS__, __FUNCTION__, func_get_args() );
			return false;
		}

		if ( $this->is_process_running() ) {
			return false;
		}

		$lasso_db = new Lasso_DB();

		if ( ! Lasso_Setting::lasso_get_setting( 'auto_monetize_amazon', false ) ) {
			return false;
		}

		// ? get all amazon_product that are not matching with lasso url
		$non_lasso_aps = $lasso_db->get_non_lasso_amazon_product( self::LIMIT, $order_by );
		$count         = count( $non_lasso_aps );

		if ( $count <= 0 ) {
			return false;
		}

		$tracking_ids = array();
		foreach ( $non_lasso_aps as $product ) {
			$product_id  = Lasso_Amazon_Api::get_product_id_by_url( $product->link_slug_domain );
			$tracking_id = Lasso_Amazon_Api::get_amazon_tracking_id_by_url( $product->link_slug_domain );
			if ( ! $product->product_id && ! $product->tracking_id && $product_id ) {
				$product->tracking_id      = $tracking_id;
				$product->product_id       = $product_id;
				$product->link_slug_domain = Lasso_Helper::get_base_domain( $product->link_slug_domain );
			}

			$tracking_id = $product->tracking_id;
			$product_id  = $product->product_id;

			if ( strpos( $product->link_slug, Lasso_Cron::SITE_STRIPE_DOMAIN ) !== false || strpos( $product->link_slug, Lasso_Cron::SITE_STRIPE_EU_DOMAIN ) !== false ) {
				$amazon_url = Lasso_Amazon_Api::get_site_stripe_url( $product->link_slug );
			} elseif ( $product_id && $product->link_slug_domain ) {
				$amazon_url = 'www.' . $product->link_slug_domain . '/dp/' . $product->product_id;
				$amazon_url = Lasso_Helper::add_https( $amazon_url );
				$amazon_url = ! empty( $tracking_id ) ? $amazon_url . '?tag=' . $tracking_id : $amazon_url;
			} else {
				$amazon_url = $product->link_slug;
				$tmp_url    = Lasso_Helper::get_final_url_from_url_param( $amazon_url );

				$amazon_url       = $tmp_url ? $tmp_url : $amazon_url;
				$term_tracking_id = Lasso_Amazon_Api::get_amazon_tracking_id_by_url( $amazon_url );
				$tracking_id      = $term_tracking_id ? $term_tracking_id : $tracking_id;
			}

			// ? ignore links that are not Amazon product links
			if ( ! Lasso_Amazon_Api::is_amazon_url( $amazon_url ) ) {
				$this->get_amazon_url_from_shortcode( $amazon_url );

				continue;
			}

			if ( $tracking_id ) {
				$tracking_ids[] = $tracking_id;
			}

			$this->push_to_queue( $amazon_url );
		}

		$tracking_ids = array_unique( $tracking_ids );

		// Check and filter compare with blacklist tracking ids.
		$amazon_blacklist_tracking_ids = Lasso_Helper::get_option( 'amazon_tracking_id_blacklist', array() );

		$tracking_ids = array_filter(
			$tracking_ids,
			function( $tracking_id ) use ( $amazon_blacklist_tracking_ids ) {
				return ! in_array( $tracking_id, $amazon_blacklist_tracking_ids, true );
			}
		);

		Lasso_Helper::update_amazon_whitelist_ids( $tracking_ids );

		$this->set_total( $count );
		$this->set_log_file_name( $this->log_name );
		$this->task_start_log();
		// ? save queue
		$this->save()->dispatch();

		return true;
	}

	/**
	 * Get all URLs from AAWP shortcode
	 *
	 * @param string $shortcode AAWP shortcode.
	 * @return array $urls      URLs.
	 */
	public function get_amazon_url_from_shortcode( $shortcode ) {
		$shortcode = trim( $shortcode );
		$urls      = array();

		if ( has_shortcode( $shortcode, 'amazon' ) || has_shortcode( $shortcode, 'aawp' ) ) {
			$shortcode    = str_replace( '"]', '" ]', $shortcode ); // ? Format before run shortcode_parse_atts to get the correct attributes
			$shortcode    = str_replace( '"/]', '" /]', $shortcode ); // ? Format before run shortcode_parse_atts to get the correct attributes
			$aawp_options = get_option( 'aawp_api' );
			$country      = $aawp_options['country'] ?? 'com';
			$attrs        = shortcode_parse_atts( $shortcode );
			$amazon_ids   = $attrs['box'] ?? $attrs['link'] ?? $attrs['fields'] ?? '';
			$ids          = explode( ',', $amazon_ids );

			if ( ! $amazon_ids || count( $ids ) === 0 ) {
				return $urls;
			}

			$urls = array_map(
				function( $amazon_id ) use ( $country ) {
					$url = 'https://amazon.' . $country . '/dp/' . $amazon_id;
					$this->push_to_queue( $url );
					return $url;
				},
				$ids
			);
		}

		return $urls;
	}
}

new Lasso_Process_Add_Amazon();
