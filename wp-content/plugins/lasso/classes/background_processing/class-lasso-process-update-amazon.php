<?php
/**
 * Declare class Lasso_Process_Update_Amazon
 *
 * @package Lasso_Process_Update_Amazon
 */

use Lasso\Classes\Helper;

use Lasso\Models\Amazon_Products;
use Lasso\Models\Url_Details;

/**
 * Lasso_Process_Update_Amazon
 */
class Lasso_Process_Update_Amazon extends Lasso_Process {
	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_update_amazon_process';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'update_amazon';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $base_url Queue item to iterate over.
	 *
	 * @return mixed
	 */
	public function task( $base_url ) {
		if ( empty( $base_url ) ) {
			return false;
		}

		$product_id = Lasso_Amazon_Api::get_product_id_by_url( $base_url );

		$cron = new Lasso_Cron();
		$cron->update_amazon_pricing( $product_id, $base_url );
		self::improve_amazon_product_id( $base_url );

		return false;
	}

	/**
	 * Prepare data for process
	 */
	public function run() {
		// ? check whether process is age out and make it can work on Lasso UI via ajax requests
		$this->is_process_age_out();

		if ( $this->is_process_running_reach_the_limit() ) {
			$this->push_to_lasso_processes_queue( __CLASS__, __FUNCTION__, func_get_args() );
			return false;
		}

		if ( $this->is_process_running() ) {
			return false;
		}

		$lasso_db         = new Lasso_DB();
		$lasso_amazon_api = new Lasso_Amazon_Api();

		$total_amz = $lasso_db->count_amazon_product_in_db();
		// $limit              = intval( ceil( $total_amz / ( 24 * 12 ) ) ); // 24 hours, 12 times/hour - every 5 minutes
		$limit              = intval( ceil( $total_amz / ( 20 * 10 ) ) );
		$limit              = $limit < 50 ? 50 : $limit;
		$amazon_product_ids = $lasso_db->get_amazon_product_in_db( $limit );
		$count              = count( $amazon_product_ids ) ?? 0;

		if ( $count <= 0 || Lasso_Process::are_all_processes_disabled() ) {
			return false;
		}

		foreach ( $amazon_product_ids as $product ) {
			$id                   = Lasso_Amazon_Api::get_product_id_by_url( $product['base_url'] );
			$product['amazon_id'] = '' === $product['amazon_id'] ? $id : $product['amazon_id'];
			$original_id          = explode( '_', $product['amazon_id'] )[0];

			// ? fix wrong product id
			// phpcs:disable
			// ? Terminal comment, this should be remove in the future.
			/*
			if ( intval( $id ) !== intval( $product['amazon_id'] ) ) {
				$product['amazon_id'] = $id;
				$update_result        = $lasso_amazon_api->update_wrong_amazon_product_id( $product );
				if ( ! $update_result ) {
					$product['amazon_id'] = $original_id;
				}
			}
			*/
			// phpcs:enable

			$base_id      = Lasso_Amazon_Api::get_product_id_by_url( $product['base_url'] );
			$monetized_id = Lasso_Amazon_Api::get_product_id_by_url( $product['monetized_url'] );
			if ( $id !== $product['amazon_id']
				|| $product['amazon_id'] !== $base_id
				|| $product['amazon_id'] !== $monetized_id
				|| strpos( $product['monetized_url'], $product['base_url'] ) === false
			) {
				$product['amazon_id']     = $original_id;
				$url                      = $lasso_amazon_api->get_amazon_link_by_product_id( $product['amazon_id'], $product['base_url'] );
				$product['base_url']      = Lasso_Amazon_Api::get_amazon_product_url( $url, false );
				$product['monetized_url'] = Lasso_Amazon_Api::get_amazon_product_url( $url, true );

				// ? fix wrong base_url or monetized_url
				$model_amazon_products = new Amazon_Products();
				$db_product            = $model_amazon_products->get_one( $original_id );
				if ( $db_product && $db_product->get_amazon_id() ) {
					$amazon_product_id_country = Lasso_Amazon_Api::get_product_id_country_by_url( $product['base_url'] );

					$db_product->set_amazon_id( $amazon_product_id_country );
					$db_product->set_base_url( $product['base_url'] );
					$db_product->set_monetized_url( $product['monetized_url'] );
					$db_product->update();
				}
			}

			$country = explode( '_', $product['amazon_id'] )[1] ?? false;
			if ( ! $country ) {
				$amazon_product_id_country = Lasso_Amazon_Api::get_product_id_country_by_url( $product['base_url'] );

				$sql     = '
					UPDATE ' . Amazon_Products::get_wp_table_name( 'lasso_amazon_products' ) . '
					SET amazon_id = %s
					WHERE amazon_id = %s
						AND base_url = %s
				';
				$prepare = Amazon_Products::prepare( $sql, $amazon_product_id_country, $product['amazon_id'], $product['base_url'] );
				Amazon_Products::query( $prepare );
			}

			$amazon_url = explode( '?', $product['amazon_url'] )[0];
			$amazon_id  = Lasso_Amazon_Api::get_product_id_by_url( $amazon_url );
			if ( $amazon_id ) {
				$this->push_to_queue( $amazon_url );
			}
		}

		$this->set_total( $count );
		$this->set_log_file_name( $this->log_name );
		$this->task_start_log();

		$this->save()->dispatch();

		return true;
	}

	/**
	 * Improve Amazon Product ID
	 *
	 * @param string $base_url Base URL.
	 */
	private static function improve_amazon_product_id( $base_url ) {
		$amazon_product_id = Lasso_Amazon_Api::get_product_id_by_url( $base_url );
		$base_domain       = Helper::get_base_domain( $base_url );

		$url_details_sql = '
			SELECT base_domain, product_id, lasso_id
			FROM ' . Url_Details::get_wp_table_name( 'lasso_url_details' ) . '
			WHERE base_domain = %s
				AND product_id = %s
				AND product_type = %s
		';
		$prepare         = Url_Details::prepare( $url_details_sql, $base_domain, $amazon_product_id, 'amazon' );
		$row             = Url_Details::get_row( $prepare );

		if ( $row ) {
			$amazon_product_id_country = Lasso_Amazon_Api::get_product_id_country_by_url( $base_url );

			$improve_id_sql = '
				UPDATE ' . Url_Details::get_wp_table_name( 'lasso_url_details' ) . '
				SET product_id = %s
				WHERE base_domain = %s
					AND product_id = %s
					AND product_type = %s
			';
			$prepare        = Url_Details::prepare( $improve_id_sql, $amazon_product_id_country, $base_domain, $amazon_product_id, 'amazon' );
			Url_Details::query( $prepare );

			// ? Update postmeta with "amazon_product_id" key as well
			update_post_meta( $row->lasso_id, 'amazon_product_id', $amazon_product_id_country );
		}
	}
}
new Lasso_Process_Update_Amazon();
