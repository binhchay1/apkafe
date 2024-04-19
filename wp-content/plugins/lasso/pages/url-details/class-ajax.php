<?php
/**
 * Lasso Url detail - Ajax.
 *
 * @package Pages
 */

namespace Lasso\Pages\Url_Details;

use Lasso_Affiliate_Link;
use Lasso_Amazon_Api;

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting as Lasso_Setting;

use Lasso\Models\Link_Locations as Model_Link_Locations;

/**
 * Lasso Url detail - Ajax.
 */
class Ajax {
	/**
	 * Declare "Lasso ajax requests" to WordPress.
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_lasso_save_lasso_url', array( $this, 'save_lasso_url' ) );
		add_action( 'wp_ajax_upload_thumbnail', array( $this, 'upload_thumbnail' ) );
		add_action( 'wp_ajax_lasso_delete_post', array( $this, 'lasso_delete_post' ) );
		add_action( 'wp_ajax_lasso_save_amazon_tracking_id', array( $this, 'lasso_save_amazon_tracking_id' ) );

		// ? Get locations count of specific lasso url id via ajax
		add_action( 'wp_ajax_get_lasso_url_location_count', array( $this, 'get_lasso_url_location_count' ) );
	}

	/**
	 * Save Lasso data into DB
	 */
	public function save_lasso_url() {
		$lasso_affiliate_link = new Lasso_Affiliate_Link();
		return $lasso_affiliate_link->save_lasso_url();
	}

	/**
	 * Upload thumbnail
	 *
	 * @param string $image_url Image url.
	 */
	public function upload_thumbnail( $image_url = '' ) {
		$data           = wp_unslash( $_POST ); // phpcs:ignore
		$lasso_id       = intval( $data['lasso_id'] ?? 0 );
		$product_url    = $data['product_url'] ?? '';
		$image_url      = $data['image_url'] ?? $image_url;
		$product_name   = '';
		$is_product_url = $data['is_product_url'] ?? false;
		$amazon_product = false;

		$product_id = Lasso_Amazon_Api::get_product_id_by_url( $product_url );

		if ( 0 === $lasso_id ) {
			$this->lasso_ajax_error( 'Lasso ID (' . $lasso_id . ') is invalid.' );
		}

		if ( '' === $product_id ) {
			$this->lasso_ajax_error( 'Product ID (' . $product_id . ') is invalid.' );
		}

		// ? send request to broken link service
		$lasso_amazon_api = new Lasso_Amazon_Api();
		if ( $is_product_url ) {
			$amazon_product = $lasso_amazon_api->fetch_product_info( $product_id, true, false, $product_url );
			$amazon_product = $amazon_product['product'];
			if ( isset( $amazon_product['status_code'] ) && 200 === $amazon_product['status_code'] ) {
				$image_url    = $amazon_product['image'] ?? $image_url;
				$product_name = $amazon_product['title'] ?? '';
			} else {
				$this->lasso_ajax_error( 'Fetch status was not 200.' );
			}
		} else {
			$this->lasso_ajax_error( "Don't run BLS, not an Amazon Product." );
		}

		// We have an Amazon Image, let's hook it up.
		if ( ! empty( $image_url ) && LASSO_DEFAULT_THUMBNAIL !== $image_url ) {
			delete_post_thumbnail( $lasso_id );
			update_post_meta( $lasso_id, 'lasso_custom_thumbnail', $image_url );
			Lasso_Helper::create_lasso_webp_image( $lasso_id );
		}

		// ? Set Amazon additional data
		if ( ! empty( $amazon_product ) && isset( $amazon_product['price'] ) && isset( $amazon_product['savings_basis'] ) && isset( $amazon_product['currency'] ) ) {
			$amazon_product['show_discount_pricing'] = Lasso_Setting::lasso_get_setting( 'show_amazon_discount_pricing' );
			$amazon_product['discount_pricing_html'] = Lasso_Amazon_Api::build_discount_pricing_html( $amazon_product['price'], $amazon_product['savings_basis'], $amazon_product['currency'] );
		}

		if ( isset( $image_url ) ) {
			wp_send_json_success(
				array(
					'status'         => 1,
					'amazon_product' => $amazon_product,
					'thumbnail'      => $image_url,
					'thumbnail_id'   => 0,
					'product_name'   => $product_name,
				)
			);
		} else {
			$this->lasso_ajax_error( "For some reason the image_url isn't set, weird issue." );
		}
	} // @codeCoverageIgnore

	/**
	 * Send error via ajax request
	 *
	 * @param string $error_message Error message.
	 */
	private function lasso_ajax_error( $error_message ) {
		wp_send_json_success(
			array(
				'status' => 0,
				'error'  => $error_message,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Delete a lasso post
	 */
	public function lasso_delete_post() {
		$post    = wp_unslash( $_POST ); // phpcs:ignore
		$post_id = $post['post_id'] ?? 0;

		if ( LASSO_POST_TYPE === get_post_type( $post_id ) ) {
			wp_delete_post( $post_id );
		}

		wp_send_json_success(
			array(
				'data' => 1,
				'post' => $post,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Get Lasso URL's location count
	 *
	 * @param int $lasso_id Lasso post id.
	 */
	public function get_lasso_url_location_count( $lasso_id = '' ) {
		$response = array(
			'status'         => 1,
			'location_count' => 0,
			'error'          => '',
		);

		$lasso_id = $lasso_id ? $lasso_id : '';
		$post     = wp_unslash( $_POST ); // phpcs:ignore
		$lasso_id = wp_doing_ajax() ? ( $post['lasso_post_id'] ?? '' ) : $lasso_id;

		if ( ! $lasso_id ) {
			$response['status'] = 0;
			$response['error']  = 'Lasso Post ID was not found.';
		} else {
			$response['location_count'] = Model_Link_Locations::total_locations_by_lasso_id( $lasso_id );
		}

		return wp_doing_ajax() ? wp_send_json_success( $response ) : $response;
	} // @codeCoverageIgnore

	/**
	 * Save Amazon tracking id
	 */
	public function lasso_save_amazon_tracking_id() {
		$post = wp_unslash( $_POST ); // phpcs:ignore

		$options                       = array();
		$options['amazon_tracking_id'] = $post['amazon_tracking_id'];

		Lasso_Setting::lasso_set_settings( $options );

		wp_send_json_success(
			array(
				'data' => 1,
				'post' => $post,
			)
		);
	} // @codeCoverageIgnore
}
