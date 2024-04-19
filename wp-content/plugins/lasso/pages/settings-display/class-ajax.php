<?php
/**
 * Setting Display - Ajax.
 *
 * @package Pages
 */

namespace Lasso\Pages\Settings_Display;

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;
use Lasso\Classes\Setting as Lasso_Setting;

use Lasso_Affiliate_Link;
use Lasso_Amazon_Api;

/**
 * Setting Display - Ajax.
 */
class Ajax {
	/**
	 * Declare "Lasso ajax requests" to WordPress.
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_lasso_get_display_html_in_url_details', array( $this, 'lasso_get_display_html_in_url_details' ) );
	}

	/**
	 * Display shortcode html.
	 */
	public function lasso_get_display_html_in_url_details() {
		$data = wp_unslash( $_POST ); // phpcs:ignore
		if ( ! empty( $data['items'] ) && is_array( $data['items'] ) ) {
			// admin preview display type.
			if ( ! empty( $data['items'] ) ) {
				// re-structure the data array to map shortcode template.
				$shortcode                   = '';
				$results                     = array();
				$results['display_type']     = $data['display_type'] ?? Lasso_Setting::DISPLAY_TYPE_SINGLE;
				$results['width']            = $data['width'] ?? Lasso_Setting::W_750;
				$results['number_of_column'] = $data['number_of_column'] ?? 3;
				$results['custom_width']     = $data['custom_width'] ?? '';

				if ( count( $data['items'] ) > 1 ) {
					foreach ( $data['items'] as $item ) {
						$item['display_type'] = strtolower( $results['display_type'] );
						list( $item_html )    = $this->get_display_shortcode( $item );
						$results['items'][]   = $item_html;
					}
				} else {
					$data['items'][0]['display_type'] = strtolower( $results['display_type'] );
					list( $html, $shortcode )         = $this->get_display_shortcode( $data['items'][0] );
					$results['items'][]               = $html;
				}
				$html = Lasso_Html_Helper::render_view_by_display_type( $results );
			}
		} else {
			list( $html, $shortcode ) = $this->get_display_shortcode( $data );
		}

		$additional_info                      = array();
		$additional_info['is_amazon_product'] = false;
		$additional_info['is_amazon_page']    = false;
		$lasso_id                             = $data['lasso_id'] ?? 0;
		if ( $lasso_id > 0 ) {
			$lasso_url                            = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );
			$additional_info['is_amazon_product'] = LASSO_AMAZON_PRODUCT_TYPE === $lasso_url->link_type;
			$additional_info['is_amazon_page']    = Lasso_Amazon_Api::is_amazon_url( $lasso_url->public_link ) && ! Lasso_Amazon_Api::get_product_id_by_url( $lasso_url->public_link );
			$additional_info['image_src']         = $lasso_url->image_src;

			$additional_info['price']       = $lasso_url->price;
			$additional_info['name']        = str_replace( '"', '&quot;', $lasso_url->name );
			$additional_info['public_link'] = $lasso_url->public_link;

		}

		wp_send_json_success(
			array(
				'status'          => 1,
				'html'            => $html,
				'shortcode'       => $shortcode,
				'additional_info' => $additional_info,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Get html display of shortcode.
	 *
	 * @param array $data An array post meta data.
	 *
	 * @return array
	 */
	private function get_display_shortcode( $data ) {
		$lasso_id       = $data['lasso_id'] ?? 0;
		$theme          = $data['theme'] ?? '';
		$default_theme  = $data['default_theme'] ?? '';
		$title          = $data['title'] ?? '';
		$image_url      = $data['image_url'] ?? '';
		$price          = $data['price'] ?? '';
		$prime          = $data['prime'] ?? '';
		$badge          = $data['badge'] ?? '';
		$description    = $data['description'] ?? '';
		$type           = $data['display_type'] ?? '';
		$show_pros_cons = 'show_pros_cons="true"';
		$basis_price    = $data['basis_price'] ?? '';

		if ( '' !== $theme ) {
			$theme = 'theme="' . $theme . '"';
		} else {
			$theme = 'theme="' . $default_theme . '"';
		}

		if ( '' !== $title ) {
			$title = 'title="' . $title . '"';
		}

		if ( '' !== $image_url ) {
			$image_url = 'image_url="' . LASSO_PLUGIN_URL . $image_url . '"';
		}

		if ( '' !== $price ) {
			$price = 'price="' . $price . '"';
		}

		if ( '' !== $prime ) {
			$prime = 'prime="' . $prime . '"';
		}

		if ( '' !== $description ) {
			$description = 'description="' . $description . '"';
		}

		if ( '' !== $badge ) {
			$badge = 'badge="' . $badge . '"';
		}

		if ( '' !== $type ) {
			$type = 'type="' . $type . '"';
		}

		if ( '' !== $basis_price ) {
			$basis_price = 'basis_price="' . $basis_price . '"';
		}

		if ( 0 === $lasso_id ) {
			$shortcode = '[lasso ' . $theme . ' ' . $price . ' ' . $type . ' id="0" demo="true" ga="false"]';
		} elseif ( -1 == $lasso_id ) { // phpcs:ignore
			// Make a argument to hide Pros and Cons for Grid view demo.
			if ( Lasso_Helper::compare_string( $type, Lasso_Setting::DISPLAY_TYPE_GRID ) ) {
				$show_pros_cons = 'show_pros_cons="false"';
			}
			$shortcode = '[lasso ' . $theme . ' ' . $title . ' ' . $price . ' ' . $prime . ' ' . $description . ' ' . $image_url . ' ' . $badge . ' ' . $type . ' ' . $show_pros_cons . ' ' . $basis_price . ' secondary_url="https://getlasso.co" fields="demo" id="0" demo="true" ga="false"]';
		} else {
			$shortcode = '[lasso ' . $theme . ' ' . $price . ' id="' . $lasso_id . '" ga="false"]';
		}

		$html = do_shortcode( $shortcode );
		return array( $html, $shortcode );
	}
}
