<?php
/**
 * Declare class Lasso_Amazon_Api
 *
 * @package Lasso_Amazon_Api
 */

use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Log as Lasso_Log;
use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Classes\Setting_Enum;
use Lasso\Classes\Verbiage as Lasso_Verbiage;

use Lasso\Models\Amazon_Products as Model_Amazon_Products;
use Lasso\Models\Url_Details as Model_Url_Details;
use Lasso\Models\Model;

/**
 * Lasso_Amazon_Api
 */
class Lasso_Amazon_Api {
	const OBJECT_KEY                                        = 'lasso_amazon_api';
	const FUNCTION_NAME_GET_LASSO_ID_BY_PRODUCT_ID_AND_TYPE = 'get_lasso_id_by_product_id_and_type';
	const PRODUCT_TYPE                                      = 'amazon';
	const SHORT_LINK_DOMAINS                                = array( 'amzn.com', 'amzn.to' );
	const FILTER_AMAZON_PRODUCT                             = 'filter_amazon_product';
	const TRACKING_ID_REGEX                                 = '^[a-zA-Z0-9-_.]+-\d{2,3}$';
	const CURRENCY_ISO                                      = array( 'USD', 'AUD', 'CAD', 'EUR', 'MXN', 'CNY', 'JPY', 'INR', 'SEK', 'BRL', 'TRY', 'GBP', 'PLN', 'EGP', 'SGD', 'AED' );
	const VARIATION_PAGE_LIMIT                              = 2;

	/**
	 * Amazon access key
	 *
	 * @var string $amazon_access_key_id
	 */
	private $amazon_access_key_id;

	/**
	 * Amazon secret key
	 *
	 * @var string $amazon_secret_key
	 */
	private $amazon_secret_key;

	/**
	 * Amazon tracking id
	 *
	 * @var string $amazon_tracking_id
	 */
	private $amazon_tracking_id;

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	private $log_name = 'amazon_api_error';

	/**
	 * Lasso_Amazon_Api constructor.
	 */
	public function __construct() {
		add_filter( self::FILTER_AMAZON_PRODUCT, array( $this, 'update_incorrect_monetized_url' ), 10, 1 );
	}

	/**
	 * Get ignore errors list
	 */
	public static function get_ignore_error_codes() {
		return array(
			'ItemNotAccessible',
			'InvalidParameterValue',
			'AccessDeniedException',
			'AccessDenied',
			'TooManyRequestsException',
			'TooManyRequests',
			'ThrottlingException',
			'RequestThrottled',
			'AWS.ThrottlingException',
			'AWS.RequestThrottled',
			'AWS.AccessDeniedException',
			'UnrecognizedClient',
		);
	}

	/**
	 * Get amazon domains
	 */
	public static function get_domains() {
		return array(
			'amazon.com',           // ? US
			'amazon.ca',            // ? Canada
			'amazon.co.uk',         // ? UK
			'amazon.com.au',        // ? Australia
			'amazon.com.br',        // ? Brazil
			'amazon.com.mx',        // ? Mexico
			'amazon.fr',            // ? France
			'amazon.de',            // ? Germany
			'amazon.it',            // ? Italy
			'amazon.in',            // ? India
			'amazon.es',            // ? Spain
			'amazon.cn',            // ? China
			'amazon.co.jp',         // ? Japan
			'amazon.nl',            // ? Netherlands
			'amazon.se',            // ? Sweden
			'amazon.sg',            // ? Singapore
			'amazon.com.tr',        // ? Turkey
			'amazon.ae',            // ? United Arab Emirates
			'amzn.com',             // ? Short URL
			'amzn.to',              // ? Short URL
			'amazon-adsystem.com',  // ? Amazon Embed
			'smile.amazon.com',      // ? Amazon Smile
		);
	}

	/**
	 * Get amazon API countries
	 */
	public static function get_amazon_api_countries() {
		return array(
			'us'  => array(
				'name'          => 'United States',
				'amazon_domain' => 'www.amazon.com',
				'pa_endpoint'   => 'webservices.amazon.com',
				'region'        => 'us-east-1',
			),
			'usa' => array(
				'name'          => 'United States',
				'amazon_domain' => 'www.amazon.com',
				'pa_endpoint'   => 'webservices.amazon.com',
				'region'        => 'us-east-1',
			),
			'au'  => array(
				'name'          => 'Australia',
				'amazon_domain' => 'www.amazon.com.au',
				'pa_endpoint'   => 'webservices.amazon.com.au',
				'region'        => 'us-west-2',
			),
			'aus' => array(
				'name'          => 'Australia',
				'amazon_domain' => 'www.amazon.com.au',
				'pa_endpoint'   => 'webservices.amazon.com.au',
				'region'        => 'us-west-2',
			),
			'br'  => array(
				'name'          => 'Brazil',
				'amazon_domain' => 'www.amazon.com.br',
				'pa_endpoint'   => 'webservices.amazon.com.br',
				'region'        => 'us-east-1',
			),
			'bra' => array(
				'name'          => 'Brazil',
				'amazon_domain' => 'www.amazon.com.br',
				'pa_endpoint'   => 'webservices.amazon.com.br',
				'region'        => 'us-east-1',
			),
			'ca'  => array(
				'name'          => 'Canada',
				'amazon_domain' => 'www.amazon.ca',
				'pa_endpoint'   => 'webservices.amazon.ca',
				'region'        => 'us-east-1',
			),
			'can' => array(
				'name'          => 'Canada',
				'amazon_domain' => 'www.amazon.ca',
				'pa_endpoint'   => 'webservices.amazon.ca',
				'region'        => 'us-east-1',
			),
			'cn'  => array(
				'name'          => 'China',
				'amazon_domain' => 'www.amazon.cn',
				'pa_endpoint'   => 'webservices.amazon.cn',
				'region'        => 'us-east-1',
			),
			'chn' => array(
				'name'          => 'China',
				'amazon_domain' => 'www.amazon.cn',
				'pa_endpoint'   => 'webservices.amazon.cn',
				'region'        => 'us-east-1',
			),
			'fr'  => array(
				'name'          => 'France',
				'amazon_domain' => 'www.amazon.fr',
				'pa_endpoint'   => 'webservices.amazon.fr',
				'region'        => 'eu-west-1',
			),
			'fra' => array(
				'name'          => 'France',
				'amazon_domain' => 'www.amazon.fr',
				'pa_endpoint'   => 'webservices.amazon.fr',
				'region'        => 'eu-west-1',
			),
			'de'  => array(
				'name'          => 'Germany',
				'amazon_domain' => 'www.amazon.de',
				'pa_endpoint'   => 'webservices.amazon.de',
				'region'        => 'eu-west-1',
			),
			'deu' => array(
				'name'          => 'Germany',
				'amazon_domain' => 'www.amazon.de',
				'pa_endpoint'   => 'webservices.amazon.de',
				'region'        => 'eu-west-1',
			),
			'in'  => array(
				'name'          => 'India',
				'amazon_domain' => 'www.amazon.in',
				'pa_endpoint'   => 'webservices.amazon.in',
				'region'        => 'eu-west-1',
			),
			'ind' => array(
				'name'          => 'India',
				'amazon_domain' => 'www.amazon.in',
				'pa_endpoint'   => 'webservices.amazon.in',
				'region'        => 'eu-west-1',
			),
			'it'  => array(
				'name'          => 'Italy',
				'amazon_domain' => 'www.amazon.it',
				'pa_endpoint'   => 'webservices.amazon.it',
				'region'        => 'eu-west-1',
			),
			'ita' => array(
				'name'          => 'Italy',
				'amazon_domain' => 'www.amazon.it',
				'pa_endpoint'   => 'webservices.amazon.it',
				'region'        => 'eu-west-1',
			),
			'jp'  => array(
				'name'          => 'Japan',
				'amazon_domain' => 'www.amazon.co.jp',
				'pa_endpoint'   => 'webservices.amazon.co.jp',
				'region'        => 'us-west-2',
			),
			'jpn' => array(
				'name'          => 'Japan',
				'amazon_domain' => 'www.amazon.co.jp',
				'pa_endpoint'   => 'webservices.amazon.co.jp',
				'region'        => 'us-west-2',
			),
			'mx'  => array(
				'name'          => 'Mexico',
				'amazon_domain' => 'www.amazon.com.mx',
				'pa_endpoint'   => 'webservices.amazon.com.mx',
				'region'        => 'us-east-1',
			),
			'mex' => array(
				'name'          => 'Mexico',
				'amazon_domain' => 'www.amazon.com.mx',
				'pa_endpoint'   => 'webservices.amazon.com.mx',
				'region'        => 'us-east-1',
			),
			'nl'  => array(
				'name'          => 'Netherlands',
				'amazon_domain' => 'www.amazon.nl',
				'pa_endpoint'   => 'webservices.amazon.nl',
				'region'        => 'eu-west-1',
			),
			'nld' => array(
				'name'          => 'Netherlands',
				'amazon_domain' => 'www.amazon.nl',
				'pa_endpoint'   => 'webservices.amazon.nl',
				'region'        => 'eu-west-1',
			),
			'se'  => array(
				'name'          => 'Sweden',
				'amazon_domain' => 'www.amazon.se',
				'pa_endpoint'   => 'webservices.amazon.se',
				'region'        => 'us-west-1',
			),
			'sek' => array(
				'name'          => 'Sweden',
				'amazon_domain' => 'www.amazon.se',
				'pa_endpoint'   => 'webservices.amazon.se',
				'region'        => 'us-west-1',
			),
			'sg'  => array(
				'name'          => 'Singapore',
				'amazon_domain' => 'www.amazon.sg',
				'pa_endpoint'   => 'webservices.amazon.sg',
				'region'        => 'us-west-2',
			),
			'sgp' => array(
				'name'          => 'Singapore',
				'amazon_domain' => 'www.amazon.sg',
				'pa_endpoint'   => 'webservices.amazon.sg',
				'region'        => 'us-west-2',
			),
			'es'  => array(
				'name'          => 'Spain',
				'amazon_domain' => 'www.amazon.es',
				'pa_endpoint'   => 'webservices.amazon.es',
				'region'        => 'eu-west-1',
			),
			'esp' => array(
				'name'          => 'Spain',
				'amazon_domain' => 'www.amazon.es',
				'pa_endpoint'   => 'webservices.amazon.es',
				'region'        => 'eu-west-1',
			),
			'tr'  => array(
				'name'          => 'Turkey',
				'amazon_domain' => 'www.amazon.com.tr',
				'pa_endpoint'   => 'webservices.amazon.com.tr',
				'region'        => 'eu-west-1',
			),
			'tur' => array(
				'name'          => 'Turkey',
				'amazon_domain' => 'www.amazon.com.tr',
				'pa_endpoint'   => 'webservices.amazon.com.tr',
				'region'        => 'eu-west-1',
			),
			'ae'  => array(
				'name'          => 'United Arab Emirates',
				'amazon_domain' => 'www.amazon.ae',
				'pa_endpoint'   => 'webservices.amazon.ae',
				'region'        => 'eu-west-1',
			),
			'are' => array(
				'name'          => 'United Arab Emirates',
				'amazon_domain' => 'www.amazon.ae',
				'pa_endpoint'   => 'webservices.amazon.ae',
				'region'        => 'eu-west-1',
			),
			'gb'  => array(
				'name'          => 'United Kingdom',
				'amazon_domain' => 'www.amazon.co.uk',
				'pa_endpoint'   => 'webservices.amazon.co.uk',
				'region'        => 'eu-west-1',
			),
			'gbr' => array(
				'name'          => 'United Kingdom',
				'amazon_domain' => 'www.amazon.co.uk',
				'pa_endpoint'   => 'webservices.amazon.co.uk',
				'region'        => 'eu-west-1',
			),
		);
	}

	/**
	 * Get amazon link and flag
	 */
	public static function get_aff_link_and_flag() {
		return array(
			'www.amazon.com'    => array(
				'flag'     => '🇺🇸',
				'code'     => 'us',
				'aff_link' => 'https://affiliate-program.amazon.com/',
			),
			'www.amazon.ca'     => array(
				'flag'     => '🇨🇦',
				'code'     => 'ca',
				'aff_link' => 'https://associates.amazon.ca/',
			),
			'www.amazon.com.br' => array(
				'flag'     => '🇧🇷',
				'code'     => 'br',
				'aff_link' => 'https://associados.amazon.com.br/',
			),
			'www.amazon.com.mx' => array(
				'flag'     => '🇲🇽',
				'code'     => 'mx',
				'aff_link' => 'https://afiliados.amazon.com.mx/',
			),
			'www.amazon.fr'     => array(
				'flag'     => '🇫🇷',
				'code'     => 'fr',
				'aff_link' => 'https://partenaires.amazon.fr/',
			),
			'www.amazon.de'     => array(
				'flag'     => '🇩🇪',
				'code'     => 'de',
				'aff_link' => 'https://partnernet.amazon.de/',
			),
			'www.amazon.it'     => array(
				'flag'     => '🇮🇹',
				'code'     => 'it',
				'aff_link' => 'https://programma-affiliazione.amazon.it/',
			),
			'www.amazon.es'     => array(
				'flag'     => '🇪🇸',
				'code'     => 'es',
				'aff_link' => 'https://afiliados.amazon.es/',
			),
			'www.amazon.co.uk'  => array(
				'flag'     => '🇬🇧',
				'code'     => 'gb',
				'aff_link' => 'https://affiliate-program.amazon.co.uk/',
			),
			'www.amazon.cn'     => array(
				'flag'     => '🇨🇳',
				'code'     => 'cn',
				'aff_link' => 'https://associates.amazon.cn/',
			),
			'www.amazon.co.jp'  => array(
				'flag'     => '🇯🇵',
				'code'     => 'jp',
				'aff_link' => 'https://affiliate.amazon.co.jp/',
			),
			'www.amazon.in'     => array(
				'flag'     => '🇮🇳',
				'code'     => 'in',
				'aff_link' => 'https://affiliate-program.amazon.in/',
			),
			'www.amazon.com.au' => array(
				'flag'     => '🇦🇺',
				'code'     => 'au',
				'aff_link' => 'https://affiliate-program.amazon.com.au/',
			),
		);
	}

	/**
	 * Check whether a url is amazon link or not
	 *
	 * @param string $url Url.
	 */
	public static function is_amazon_url( $url ) {
		if ( empty( $url ) ) {
			return false;
		}

		$domains = self::get_domains();
		$url     = Lasso_Helper::add_https( $url );

		if ( ! Lasso_Helper::validate_url( $url ) ) {
			return false;
		}

		$parse_url = wp_parse_url( $url );
		if ( ! isset( $parse_url['host'] ) ) {
			return false;
		}

		$domain = ltrim( $parse_url['host'], 'www.' );

		if ( in_array( $domain, $domains, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check whether a url is amazon link or not
	 *
	 * @param string $url Url.
	 */
	public static function is_amazon_shortened_url( $url ) {
		$is_amazon_url    = self::is_amazon_url( $url );
		$is_shortened_url = strpos( $url, 'amzn.to' ) || strpos( $url, 'amzn.com' );

		return $is_amazon_url && $is_shortened_url;
	}

	/**
	 * Search amazon product
	 *
	 * @param string $keyword Keyword: product name,...
	 */
	public function search_product( $keyword ) {
		$result = $this->get_product_by_keyword_v5( $keyword, 'All' );
		if ( isset( $result->SearchResult->Items ) ) { // phpcs:ignore
			$items    = $result->SearchResult->Items; // phpcs:ignore
			$products = array();

			foreach ( $items as $item ) {
				$product = $this->extract_search_result_v5( $item );
				array_push( $products, $product );
			}

			return $products;

		} elseif ( isset( $result->Errors ) ) { // phpcs:ignore
			return array(
				'error' => $result->Errors, // phpcs:ignore
			);
		}
	}

	/**
	 * Check whether product url has the same domain with Amazon settings
	 *
	 * @param string $product_url Amazon link.
	 */
	public function is_same_domain( $product_url ) {
		if ( '' === $product_url ) {
			return false;
		}

		$amazon_default_tracking_country = Lasso_Setting::lasso_get_setting( 'amazon_default_tracking_country', 'usa' );
		$all_countries                   = self::get_amazon_api_countries();
		$domain                          = $all_countries[ $amazon_default_tracking_country ]['amazon_domain'] ?? 'www.amazon.com';
		$domain                          = str_replace( 'www.', '', $domain );

		return strpos( $product_url, $domain ) !== false;
	}

	/**
	 * Fetch amazon product from Amazon API v5
	 *
	 * @param string      $product_id    Amazon product id.
	 * @param bool        $store_product Store product into DB or not. Default to false.
	 * @param bool|string $updated_at    Set date time or not. Default to false.
	 * @param string      $amz_link      Amazon link. Default to empty.
	 */
	public function fetch_product_info( $product_id, $store_product = false, $updated_at = false, $amz_link = '' ) {
		$product_id           = explode( '_', $product_id )[0]; // ? remove country code in product id
		$lasso_settings       = Lasso_Setting::lasso_get_settings();
		$is_amazon_configured = $lasso_settings['amazon_access_key_id'] && $lasso_settings['amazon_secret_key'] && $lasso_settings['amazon_tracking_id'];
		$result               = $is_amazon_configured && $this->is_same_domain( $amz_link ) ? $this->get_product_by_id_v5( $product_id ) : false;
		// phpcs:ignore
		if ( isset( $result->ItemsResult->Items[0] ) ) { // phpcs:ignore
			$item        = $result->ItemsResult->Items[0]; // phpcs:ignore
			$product     = $this->extract_search_result_v5( $item, true );
			$product_url = $amz_link ? $amz_link : $product['url'];
			$status      = 200;

			// ? If $item->Offers is missing, we try to get the quantity from variation data
			if ( ! isset( $item->Offers ) ) { // phpcs:ignore
				sleep( 1 ); // ? Delay for a while before call the next request
				$variation_product = $this->get_product_variation( $product_id, $product_url );
				if ( $variation_product && isset( $variation_product['quantity'] ) && $variation_product['quantity'] ) {
					$product = $variation_product;
				}
			}

			$product['status_code'] = 200;
			$product['url']         = self::get_amazon_product_url( $product_url );
			$should_fetch_bls       = 0 === intval( $product['quantity'] ) || 0 >= $product['amount'];

			if ( $should_fetch_bls ) {
				list( $product_bls, $status ) = $this->fetch_product_from_bls( $product_id, $store_product, $updated_at, $amz_link );
				$product['quantity']          = $product_bls['quantity'] ?? $product['quantity'];
				$product['price']             = $product_bls['price'] ?? $product['price'];
			} else {
				do_action( Setting_Enum::HOOK_FETCH_AMAZON_PRODUCT_API, $product );
			}

			// ? Get Lasso ID
			$query               = '
                SELECT post_id 
                FROM ' . Model::get_wp_table_name( 'postmeta' ) . '
                WHERE meta_key = %s 
					AND meta_value = %s
            ';
			$query               = Model::prepare( $query, 'amazon_product_id', $product['product_id'] );
			$lasso_id            = Model::get_var( $query );
			$product['lasso_id'] = isset( $lasso_id ) ? $lasso_id : 0;

			if ( $store_product ) {
				$amazon_tracking_id     = Lasso_Setting::lasso_get_setting( 'amazon_tracking_id', '' );
				$product['default_url'] = '' === $amazon_tracking_id ? $amz_link : $product['default_url'];
				$this->update_amazon_product_in_db( $product, $updated_at );
			}

			return array(
				'product'    => $product,
				'api'        => 'yes',
				'full_item'  => $item,
				'status'     => 200 === $status ? 'success' : 'fail',
				'error_code' => 404 === $status ? 'NotFound' : '',
			);
		} else {
			list( $product, $status ) = $this->fetch_product_from_bls( $product_id, $store_product, $updated_at, $amz_link );

			return array(
				'product'    => $product,
				'api'        => 'no',
				'full_item'  => array(),
				'status'     => 200 === $status ? 'success' : 'fail',
				'error_code' => 404 === $status ? 'NotFound' : '',
			);
		}
	}

	/**
	 * Fetch amazon product from BLS (Lambda)
	 *
	 * @param string      $product_id    Amazon product id.
	 * @param bool        $store_product Store product into DB or not. Default to false.
	 * @param bool|string $updated_at    Set date time or not. Default to false.
	 * @param string      $amz_link      Amazon link. Default to empty.
	 */
	public function fetch_product_from_bls( $product_id, $store_product = false, $updated_at = false, $amz_link = '' ) {
		$url    = strpos( $amz_link, 'amazon.' ) !== false ? $amz_link : $this->get_amazon_link_by_product_id( $product_id, $amz_link );
		$m_link = self::get_amazon_product_url( $url );
		$url    = self::get_amazon_product_url( $url, false );

		$amazon_product = array(
			'title'           => '',
			'image'           => '',
			'url'             => $m_link,
			'price'           => '',
			'currency'        => '',
			'savings_amount'  => 0,
			'savings_percent' => 0,
			'savings_basis'   => 0,
		);

		$res = Lasso_Helper::get_url_status_code_by_broken_link_service( $url, true );
		if ( 200 === $res['status_code'] && 200 === $res['response']->status ) {
			$img_url      = $res['response']->imgUrl ?? '';
			$img_url      = '' !== $img_url ? $img_url : '';
			$product_name = $res['response']->productName ?? '';
			$product_name = '' === $product_name ? ( $res['response']->pageTitle ?? '' ) : $product_name;
			$quantity     = $res['response']->quantity ?? 200;
			$price        = $res['response']->price ?? '';
			$product_id   = self::get_product_id_by_url( $url );

			$temp_url = $res['response']->finalUrl ?? $url;
			$url      = '' !== $temp_url ? $temp_url : $url;
			$m_link   = self::get_amazon_product_url( $amz_link ? $amz_link : $url );

			if ( strpos( $product_name, 'Amazon.com:' ) === 0 ) {
				$product_name = str_replace( 'Amazon.com:', '', $product_name );
				$product_name = trim( $product_name );
			}

			if ( $product_id && $store_product ) {
				$store_data = array(
					'product_id'  => $product_id,
					'title'       => $product_name,
					'price'       => $price,
					'default_url' => $url,
					'url'         => $m_link,
					'image'       => trim( $img_url ),
					'quantity'    => intval( $quantity ),  // Manual checks won't show out of stock for now. TODO: Add BLS to out of stock checks.
					'is_manual'   => 1,
				);

				// ? Set additional data for amazon product
				if ( isset( $res['response']->additionalData ) && ! empty( $res['response']->additionalData ) ) {
					$basis_price    = $res['response']->additionalData->basis_price ?? '';
					$basis_price    = Lasso_Helper::get_price_value_from_price_text( $basis_price );
					$savings_amount = $res['response']->additionalData->saving_amount ?? '';
					$savings_amount = Lasso_Helper::get_price_value_from_price_text( $savings_amount );

					$store_data['currency']        = $res['response']->additionalData->currency_name ?? '';
					$store_data['savings_basis']   = $basis_price;
					$store_data['savings_amount']  = $savings_amount;
					$store_data['savings_percent'] = $res['response']->additionalData->saving_amount_percent ?? '';

					$amazon_product['currency']        = $store_data['currency'];
					$amazon_product['savings_basis']   = $store_data['savings_basis'];
					$amazon_product['savings_amount']  = $store_data['savings_amount'];
					$amazon_product['savings_percent'] = $store_data['savings_percent'];
				}

				$this->update_amazon_product_in_db( $store_data, $updated_at, true );
			}

			$amazon_product['title']       = $product_name;
			$amazon_product['image']       = $img_url;
			$amazon_product['url']         = $m_link;
			$amazon_product['price']       = $price;
			$amazon_product['quantity']    = $quantity;
			$amazon_product['status_code'] = $res['response']->status;
		}
		if ( 404 === $res['status_code'] || ( 200 === $res['status_code'] && 404 === $res['response']->status ) ) {
			$lasso_db     = new Lasso_DB();
			$last_updated = gmdate( 'Y-m-d H:i:s', time() );
			$lasso_db->update_amazon_field( $product_id, 'last_updated', $last_updated );
			$lasso_db->update_amazon_field( $product_id, 'out_of_stock', 0 );
		}

		$status = $res['response']->status ?? 200;

		return array( $amazon_product, intval( $status ) );
	}

	/**
	 * Insert or Update Amazon Product Data
	 *
	 * @param array       $product     Amazon product.
	 * @param bool|string $updated_at  Set update date time. Default to false.
	 * @param bool        $is_from_bls Is update product in bls request. Default to false.
	 */
	public function update_amazon_product_in_db( $product, $updated_at = false, $is_from_bls = false ) {
		$lasso_db = new Lasso_DB();

		$amazon_id            = $product['product_id'] ?? '';
		$default_product_name = $product['title'] ?? '';
		$latest_price         = $product['price'] ?? '';
		$latest_price         = '0' === $latest_price || ( is_int( $latest_price ) && 0 === $latest_price ) ? '' : $latest_price;
		$base_url             = self::get_amazon_product_url( $product['default_url'] ?? '', false, false );
		$monetized_url        = self::get_amazon_product_url( $product['url'] ?? '', true, false );
		$default_image        = trim( $product['image'] ?? '' );
		$last_updated         = gmdate( 'Y-m-d H:i:s', time() );
		$last_updated         = $updated_at ? $updated_at : $last_updated;
		$is_prime             = $product['is_prime'] ?? '';
		$currency             = $product['currency'] ?? '';
		$features             = wp_json_encode( $product['features'] ?? array() );
		$savings_amount       = $product['savings_amount'] ?? '';
		$savings_percent      = $product['savings_percent'] ?? '';
		$savings_basis        = $product['savings_basis'] ?? '';
		$is_manual            = $product['is_manual'] ?? 0;
		$quantity             = intval( $product['quantity'] ?? 200 );
		$out_of_stock         = 0 === $quantity ? 1 : 0;

		$amazon_id = $base_url ? self::get_product_id_country_by_url( $base_url ) : $base_url;

		// ? Gift product out of stock, return out of stock is 1, empty product name and  empty image url
		if ( $is_from_bls && 1 === $out_of_stock && $amazon_id && '' === $default_product_name && '' === $default_image ) {
			$amzon_product_model = new Model_Amazon_Products();
			$amzon_product       = $amzon_product_model->get_one( $amazon_id );

			// ? Check if existing product
			if ( $amzon_product->get_amazon_id() ) {
				$sql = '
					UPDATE ' . $amzon_product_model->get_table_name() . '
					SET out_of_stock = 1
					WHERE amazon_id = %s
				';
				$sql = Model_Amazon_Products::prepare( $sql, $amazon_id );

				Model_Amazon_Products::query( $sql );
				return;
			}
		}

		if ( '' === $amazon_id || '' === $default_product_name || '' === $default_image
			|| ( '' !== $default_image && Lasso_Helper::validate_url( $default_image ) === false && strpos( $default_image, 'data:image' ) !== 0 )
		) {
			return false;
		}

		$lasso_db->resolve_issue_by_url( $base_url, '404' );
		if ( 0 === $out_of_stock ) {
			$lasso_db->resolve_product_out_of_stock( $amazon_id, self::PRODUCT_TYPE );
		}

		$base_url      = trim( $base_url );
		$monetized_url = trim( $monetized_url );

		$query   = '
            INSERT INTO ' . Model::get_wp_table_name( LASSO_AMAZON_PRODUCTS_DB ) . "
                (
                    amazon_id, default_product_name, latest_price, base_url, 
                    monetized_url, default_image, last_updated, is_prime, 
                    currency, features, savings_amount, savings_percent, 
                    savings_basis, is_manual, out_of_stock
                )
            VALUES
                (
                    %s, %s, %s, %s, 
                    %s, %s, %s, %d, 
                    %s, %s, %s, %d,
                    %s, %d, %d
                )
            ON DUPLICATE KEY UPDATE
                amazon_id = %s,
                default_product_name = %s,
                latest_price = %s,
                base_url = %s,
                monetized_url = %s,
                default_image = (CASE WHEN %s='' or %s IS NULL THEN `default_image` ELSE %s END),
                last_updated = %s,
                is_prime = %d,
                currency  = %s,
                features = %s,
                savings_amount = %s,
                savings_percent = %d,
                savings_basis = %s,
                is_manual = %d,
                out_of_stock = %d
            ;
		";
		$prepare = Model::prepare(
			// phpcs:ignore
			$query,
			// ? First for insert
			$amazon_id,
			$default_product_name,
			$latest_price,
			$base_url,
			$monetized_url,
			$default_image,
			$last_updated,
			$is_prime,
			$currency,
			$features,
			$savings_amount,
			$savings_percent,
			$savings_basis,
			$is_manual,
			$out_of_stock,
			// ? Second for update
			$amazon_id,
			$default_product_name,
			$latest_price,
			$base_url,
			$monetized_url,
			$default_image,
			$default_image,
			$default_image,
			$last_updated,
			$is_prime,
			$currency,
			$features,
			$savings_amount,
			$savings_percent,
			$savings_basis,
			$is_manual,
			$out_of_stock
		);

		Model::query( $prepare );

		return true;
	}

	/**
	 * Get monetized link
	 *
	 * @param string $link Amazon link.
	 */
	public function get_monetized_link( $link ) {
		if ( empty( $link ) ) {
			return array( 'status' => 'failed' );
		}

		$base_domain = Lasso_Helper::get_base_domain( $link );
		if ( 'amzn.to' === $base_domain || 'amzn.com' === $base_domain ) {
			$link = Lasso_Helper::get_redirect_final_target( $link );
		}

		$amazon_product_id = self::get_product_id_by_url( $link );
		if ( $amazon_product_id ) {
			$amazon_product = $this->fetch_product_info( $amazon_product_id, true, false, $link );

			if ( 'success' === $amazon_product['status'] && isset( $amazon_product['product']['url'] ) ) {
				$link    = $amazon_product['product']['url'];
				$m_title = $amazon_product['product']['title'];
			} else {
				$m_title = '';  // ? TODO: Figure out a better default
			}

			return array(
				'status' => 'success',
				'link'   => self::get_amazon_product_url( $link ),
				'title'  => $m_title,
			);
		}

		return array( 'status' => 'failed' );
	}

	/**
	 * Get amazon product from DB
	 *
	 * @param string $product_id  Amazon Product id.
	 * @param string $product_url Amazon Product url.
	 */
	public function get_amazon_product_from_db( $product_id, $product_url = '' ) {
		if ( empty( $product_id ) ) {
			return false;
		}

		if ( $product_url ) {
			$product_id = self::get_product_id_country_by_url( $product_url );
		}

		$sql = '
			SELECT * 
			FROM ' . Model::get_wp_table_name( LASSO_AMAZON_PRODUCTS_DB ) . ' 
			WHERE amazon_id = %s
		';

		$prepare = Model::prepare( $sql, $product_id ); // phpcs:ignore
		$result  = Model::get_row( $prepare, ARRAY_A );

		if ( $result ) {
			$result                  = apply_filters( self::FILTER_AMAZON_PRODUCT, $result );
			$result['monetized_url'] = self::get_amazon_product_url( $result['monetized_url'] );
			$result['base_url']      = self::get_amazon_product_url( $result['base_url'], false );
			$result['features']      = json_decode( $result['features'] );
		}

		return $result;
	}

	/**
	 * Extract result to an array
	 *
	 * @param object $response    Data from Amazon.
	 * @param bool   $large_image Get large image size. Default to false.
	 * @param string $product_id  Product Id. Default to empty.
	 * @param string $product_url Product url. Default to empty.
	 *
	 * @return array
	 */
	private function extract_search_result_v5( $response, $large_image = false, $product_id = '', $product_url = '' ) {

		$image = '';
		if ( isset( $response->Images->Primary ) ) { // phpcs:ignore
			$image = $large_image ? $response->Images->Primary->Large->URL : $response->Images->Primary->Small->URL; // phpcs:ignore
		}

		$cat_binding       = $response->ItemInfo->Classifications->Binding->DisplayValue ?? false; // phpcs:ignore
		$cat_product_group = $response->ItemInfo->Classifications->ProductGroup->DisplayValue ?? false; // phpcs:ignore
		$categories        = array( $cat_binding, $cat_product_group );
		$categories        = array_filter( $categories );

		// @codingStandardsIgnoreStart
		$result = array(
			'product_id'      => $product_id ? $product_id : ( $response->ASIN ?? 0 ),
			'title'           => $response->ItemInfo->Title->DisplayValue ?? '',
			'url'             => $product_url ? $product_url : ( $response->DetailPageURL ?? '' ),
			'default_url'     => $response->DetailPageURL ?? '',
			'image'           => $image,
			'quantity'        => $response->Offers->Summaries[0]->OfferCount ?? 0,
			'is_prime'        => $response->Offers->Listings[0]->DeliveryInfo->IsPrimeEligible ?? false,
			'price'           => $response->Offers->Listings[0]->Price->DisplayAmount ?? 0,
			'amount'          => $response->Offers->Listings[0]->Price->Amount ?? 0,
			'currency'        => $response->Offers->Listings[0]->Price->Currency ?? '',
			'features'        => $response->ItemInfo->Features->DisplayValues ?? array(),
			'categories'      => $categories,
			'savings_amount'  => $response->Offers->Listings[0]->Price->Savings->Amount ?? 0.0,
			'savings_percent' => $response->Offers->Listings[0]->Price->Savings->Percentage ?? 0,
			'savings_basis'   => $response->Offers->Listings[0]->SavingBasis->Amount ?? 0.0,
		);
		// @codingStandardsIgnoreEnd

		return $result;
	}

	/**
	 * Query amazon v5
	 *
	 * @param array   $parameters      Amazon API params.
	 * @param boolean $lasso_settings Lasso settings. Default to false.
	 *
	 * @return array
	 */
	public function query_amazon_v5( $parameters, $lasso_settings = false ) {
		try {
			if ( ! $lasso_settings ) {
				$lasso_settings = get_option( LASSO_SETTINGS, array() );
			}

			$this->amazon_access_key_id = $lasso_settings['amazon_access_key_id'] ?? '';
			$this->amazon_secret_key    = $lasso_settings['amazon_secret_key'] ?? '';
			$this->amazon_tracking_id   = $lasso_settings['amazon_tracking_id'] ?? '';

			$result = $this->aws_signed_request_v5( $parameters, $this->amazon_access_key_id, $this->amazon_secret_key, $this->amazon_tracking_id );

			if ( isset( $result->Errors ) ) { // phpcs:ignore
				$error = $result->Errors[0]; // phpcs:ignore
				Lasso_Helper::write_log( $error->Code . ' - ' . $error->Message, $this->log_name ); // phpcs:ignore
			}

			return $result;
		} catch ( Exception $e ) {
			Lasso_Helper::write_log( $e->getMessage(), $this->log_name );
			return array();
		}
	}

	/**
	 * Get Amazon product by product id
	 *
	 * @param string $product_id Amazon product id.
	 *
	 * @return object
	 */
	public function get_product_by_id_v5( $product_id ) {
		$parameters = array(
			'Operation' => 'GetItems',
			'ItemIds'   => array( $product_id ),
			'Resources' => array(
				'Images.Primary.Small',
				'Images.Primary.Large',
				'ItemInfo.Title',
				'ItemInfo.ContentRating',
				'ItemInfo.Features',
				'ItemInfo.ProductInfo',
				'ItemInfo.TechnicalInfo',
				'ItemInfo.Classifications',
				'ItemInfo.Features',
				'Offers.Listings.Price',
				'Offers.Listings.SavingBasis',
				'Offers.Listings.MerchantInfo',
				'Offers.Listings.DeliveryInfo.IsPrimeEligible',
				'Offers.Summaries.OfferCount',
			),
		);

		$json_response = $this->query_amazon_v5( $parameters );

		return $json_response;
	}

	/**
	 * Get product from Amazon by product name
	 *
	 * @param string $keyword      Keyword.
	 * @param string $product_type Product type.
	 *
	 * @return object
	 */
	public function get_product_by_keyword_v5( $keyword, $product_type ) {
		$parameters = array(
			'Operation'   => 'SearchItems',
			'Keywords'    => $keyword,
			'SearchIndex' => $product_type,
			'Resources'   => array(
				'Images.Primary.Small',
				'Images.Primary.Large',
				'ItemInfo.Title',
				'ItemInfo.ContentRating',
				'ItemInfo.Features',
				'ItemInfo.ProductInfo',
				'ItemInfo.TechnicalInfo',
				'Offers.Listings.Price',
				'Offers.Listings.SavingBasis',
				'Offers.Summaries.OfferCount',
				'Offers.Listings.DeliveryInfo.IsPrimeEligible',
			),
		);

		$json_response = $this->query_amazon_v5( $parameters );

		return $json_response;
	}

	/**
	 * Sign request v5
	 *
	 * @param array  $params               Amazon params.
	 * @param string $amazon_access_key_id Amazon access key.
	 * @param string $amazon_secret_key    Amazon secret key.
	 * @param string $amazon_tracking_id   Amazon tracking id.
	 *
	 * @return object|bool
	 */
	private function aws_signed_request_v5( $params, $amazon_access_key_id, $amazon_secret_key, $amazon_tracking_id ) {
		// phpcs:ignore
		// $amazon_domain = 'www.amazon.com';
		// $pa_endpoint = 'webservices.amazon.com';

		$country       = Lasso_Setting::lasso_get_setting( 'amazon_default_tracking_country', 'usa' );
		$countries     = self::get_amazon_api_countries();
		$amazon_domain = $countries[ $country ]['amazon_domain'];
		$pa_endpoint   = $countries[ $country ]['pa_endpoint'];
		$amazon_region = $countries[ $country ]['region'];

		$params['Marketplace'] = $amazon_domain;
		$params['PartnerType'] = 'Associates';
		$params['PartnerTag']  = $amazon_tracking_id;
		$post_fields           = wp_json_encode( $params );

		$aws_v5 = new LassoAwsV5( $amazon_access_key_id, $amazon_secret_key );
		$aws_v5->setHost( $pa_endpoint );
		$aws_v5->setRegionName( $amazon_region );
		$aws_v5->setPayload( $post_fields );
		$aws_v5->addHeader( 'x-amz-target', 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.' . $params['Operation'] );
		$headers = $aws_v5->getHeaders( true );
		$url     = "https://$pa_endpoint/paapi5/searchitems";

		// @codingStandardsIgnoreStart
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_fields );
		curl_setopt( $ch, CURLOPT_POST, 1 );

		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

		$result = curl_exec( $ch );
		if ( curl_errno( $ch ) ) {
			// phpcs:ignore
			// $error = curl_error( $ch );
			return false;
		}
		curl_close( $ch );
		// @codingStandardsIgnoreEnd

		return json_decode( $result );
	}

	/**
	 * Get Amazon product id country by url
	 *
	 * @param string $url Amazon link.
	 * @return string|bool
	 */
	public static function get_product_id_country_by_url( $url ) {
		$product_id = self::get_product_id_by_url( $url );
		if ( ! $product_id ) {
			return false;
		}

		$base_domain   = Lasso_Helper::get_base_domain( $url );
		$countries     = self::get_aff_link_and_flag();
		$country_code  = $countries[ 'www.' . $base_domain ]['code'] ?? 'us';
		$product_id_db = $product_id . '_' . $country_code;

		return $product_id_db;
	}

	/**
	 * Get Amazon product id by url
	 *
	 * @param string $url Amazon link.
	 * @return string|bool
	 */
	public static function get_product_id_by_url( $url ) {
		$url = Lasso_Helper::add_https( $url );

		if ( ! self::is_amazon_url( $url ) || strpos( $url, '.' ) === false ) {
			return false;
		}

		$parse_url           = wp_parse_url( $url );
		$amazon_domain       = trim( $parse_url['host'] ?? '', 'www.' );
		$amazon_domain_regex = str_replace( '.', '\.', str_replace( 'www.', '', $amazon_domain ) );

		$reg     = '#(?:https?://(?:www\.){0,1}' . $amazon_domain_regex . '(?:/.*){0,1}(?:/gp/aw/d/|/d/[a-zA-Z0-9\-]*/[a-zA-Z0-9\-]*/|/dp/|/gp/product/|/ASIN/|/gp/video/detail/))([a-zA-Z0-9]*)(.*?)(?:/.*|$)#';
		$matches = array();
		preg_match( $reg, $url, $matches );

		return isset( $matches[1] ) && ! empty( $matches[1] ) ? $matches[1] : false;
	}

	/**
	 * Get Amazon link by product id
	 *
	 * @param string $product_id Amazon product id.
	 * @param string $amz_link   Amazon link. Default to empty.
	 */
	public function get_amazon_link_by_product_id( $product_id, $amz_link = '' ) {
		if ( ! $product_id ) {
			return $amz_link;
		}

		$product_id = explode( '_', $product_id )[0];

		if ( '' !== $amz_link ) {
			$parse = wp_parse_url( $amz_link );
			$host  = $parse['host'] ?? '';
			if ( '' !== $host ) {
				return 'https://' . $host . '/dp/' . $product_id;
			}
		}

		$country       = Lasso_Setting::lasso_get_setting( 'amazon_default_tracking_country', 'usa' );
		$countries     = self::get_amazon_api_countries();
		$amazon_domain = $countries[ $country ]['amazon_domain'];

		return 'https://' . $amazon_domain . '/dp/' . $product_id;
	}

	/**
	 * Get amazon tracking id by url
	 * This function will be deprecated in the future. Please use Lasso\Classes\Lasso_Helper::get_argument_from_url() instead
	 *
	 * @param  string $link Amazon link.
	 * @return string
	 */
	public static function get_amazon_tracking_id_by_url( $link ) {
		$search  = '/([?|&|&amp;|\/]{1})tag=([a-zA-Z0-9\-\_]*)/i';
		$matches = array();
		preg_match( $search, $link, $matches );

		return $matches[2] ?? '';
	}

	/**
	 * Replace image size
	 *
	 * @param  string $link Amazon link.
	 * @param  string $size Image size. Example: _SL250_.
	 * @return string
	 */
	public static function replace_image_size( $link, $size ) {
		if ( ! $size || strpos( $size, '_SL' ) === false ) {
			return $link;
		}
		preg_match( '/_SL([0-9]+)_/', $size, $size_match );
		$size_number = $size_match[1] ?? false;

		if ( ! $size_number ) {
			return $link;
		}

		$link = preg_replace( '/\._SL([0-9]+)_\./', '._SL' . $size_number . '_.', $link );

		return $link;
	}

	/**
	 * Get amazon monetize url
	 *
	 * @param string $amazon_product_id  Amazon product id.
	 * @param string $amazon_product_url Amazon product url.
	 */
	public function get_amazon_monetized_url( $amazon_product_id, $amazon_product_url = '' ) {
		if ( ! $amazon_product_id ) {
			return false;
		}

		if ( $amazon_product_url ) {
			$amazon_product_id = self::get_product_id_country_by_url( $amazon_product_url );
		}

		// @codingStandardsIgnoreStart
		$prepare = Model::prepare(
			'
			SELECT monetized_url
			FROM ' . Model::get_wp_table_name( LASSO_AMAZON_PRODUCTS_DB ) . '
			WHERE `amazon_id` = %s
		',
			$amazon_product_id
		);
		// @codingStandardsIgnoreEnd

		return Model::get_row( $prepare );
	}

	/**
	 * Keep URL arguments for Amazon URL
	 *
	 * @param string $product_url Amazon product url.
	 *
	 * @return array $results
	 */
	private static function keep_args( $product_url ) {
		// ? amazon link but it is not product url
		if ( ! self::is_amazon_url( $product_url ) || self::is_amazon_shortened_url( $product_url ) ) {
			return array();
		}

		$results    = array();
		$url_params = array(
			'maas',
			'ref_',
			's',
			'aa_campaignid',
			'aa_creativeid',
			'aa_adgroupid',
			'campaignId',
			'linkCode',
			'linkId',
		);

		foreach ( $url_params as $param_name ) {
			$param_value = Lasso_Helper::get_argument_from_url( $product_url, $param_name );
			if ( $param_value ) {
				$results[] = $param_name . '=' . $param_value;
			}
		}

		return $results;
	}

	/**
	 * Make product url to shorten url if this setting is enabled
	 *
	 * @param string $product_url        Amazon product url.
	 * @param bool   $monetize           Monetize link or not. Default to true.
	 * @param bool   $check_lasso_post   Check Lasso post or ignore. Default to true.
	 * @param string $custom_tracking_id Allow to use custom tracking id.
	 */
	public static function get_amazon_product_url( $product_url, $monetize = true, $check_lasso_post = true, $custom_tracking_id = '' ) {
		// ? amazon link but it is not product url
		if ( ! self::is_amazon_url( $product_url ) || self::is_amazon_shortened_url( $product_url ) ) {
			return $product_url;
		}

		$product_id = self::get_product_id_by_url( $product_url );

		// ? remove all url queries, just keep needed args
		$url_without_params = explode( '?', $product_url )[0];
		if ( $product_id && ! $monetize ) {
			$keep_args = self::keep_args( $product_url );
			$args      = Lasso_Helper::build_url_parameter_string( $keep_args );

			return $args ? $url_without_params . '?' . $args : $url_without_params;
		}

		$lasso_settings                        = Lasso_Setting::lasso_get_settings();
		$amazon_multiple_tracking_id           = $lasso_settings['amazon_multiple_tracking_id'] ?? true;
		$amazon_tracking_id_whitelist          = $lasso_settings['amazon_tracking_id_whitelist'] ?? array();
		$amazon_tracking_id                    = trim( $lasso_settings['amazon_tracking_id'] ?? '' );
		$amazon_add_tracking_id_to_attribution = $lasso_settings['amazon_add_tracking_id_to_attribution'] ?? true;

		if ( ! $amazon_multiple_tracking_id ) {
			$amazon_tracking_id_whitelist = array();
		}

		$lasso_db      = new Lasso_DB();
		$amz_cache_key = self::OBJECT_KEY . '_' . self::FUNCTION_NAME_GET_LASSO_ID_BY_PRODUCT_ID_AND_TYPE . '_' . $product_id . '_' . self::PRODUCT_TYPE;
		$lasso_id      = Lasso_Cache_Per_Process::get_instance()->get_cache( $amz_cache_key, null );
		if ( null === $lasso_id ) {
			$lasso_id = $lasso_db->get_lasso_id_by_product_id_and_type( $product_id, self::PRODUCT_TYPE, $product_url );
			Lasso_Cache_Per_Process::get_instance()->set_cache( $amz_cache_key, $lasso_id );
		}

		if ( $check_lasso_post && ! $lasso_id ) {
			return $product_url;
		}

		$tag = Lasso_Helper::get_argument_from_url( $product_url, 'tag' );
		$tag = $custom_tracking_id ? $custom_tracking_id : $tag;
		$tag = $tag ? $tag : '';
		$tag = ! empty( $amazon_tracking_id ) && ! in_array( $tag, $amazon_tracking_id_whitelist, true )
			? $amazon_tracking_id : $tag;

		// ? Return the remove all url queries for product url
		if ( $product_id ) {
			$tag_args       = $tag ? 'tag=' . $tag : '';
			$keep_args      = self::keep_args( $product_url );
			$maas           = Lasso_Helper::get_argument_from_url( $product_url, 'maas' );
			$add_tag_to_url = ( $maas && $amazon_add_tracking_id_to_attribution ) || ! $maas;

			if ( $add_tag_to_url ) {
				array_unshift( $keep_args, $tag_args );
			}

			$args        = Lasso_Helper::build_url_parameter_string( $keep_args );
			$product_url = $args ? $url_without_params . '?' . $args : $url_without_params;
		} else {
			$product_url = str_replace( '&amp;', '&', $product_url );
			$parse       = wp_parse_url( $product_url );
			parse_str( $parse['query'] ?? '', $query );

			// ? set tag id (tracking id) at the end of the url
			if ( $tag ) {
				$query['tag'] = $tag;
			} elseif ( ! empty( $amazon_tracking_id ) ) {
				$query['tag'] = $amazon_tracking_id;
			}

			if ( ! $monetize ) {
				unset( $query['tag'] );
			}

			$parse['query'] = Lasso_Helper::get_query_from_array( $query );
			$product_url    = Lasso_Helper::get_url_from_parse( $parse );
			$product_url    = trim( $product_url );
			$product_url    = trim( $product_url, '?' );
		}

		return $product_url;
	}

	/**
	 * Validate Amazon configurations
	 */
	public function validate_amazon_settings() {
		$lasso_settings       = Lasso_Setting::lasso_get_settings();
		$is_amazon_configured = $lasso_settings['amazon_access_key_id'] && $lasso_settings['amazon_secret_key'] && $lasso_settings['amazon_tracking_id'];

		$result = $is_amazon_configured ? $this->get_product_by_keyword_v5( 'test', 'All' ) : false;

		if ( ! $result ) {
			update_option( 'lasso_amazon_valid', false );
			return false;
			// phpcs:ignore
		} elseif ( isset( $result->Errors ) ) {
			update_option( 'lasso_amazon_valid', false );
			// phpcs:ignore
			return $result->Errors;
		} else {
			update_option( 'lasso_amazon_valid', true );
			return true;
		}
	}

	/**
	 * Get Amazon product info from url accept url or post_id
	 *
	 * @param string|int $url_or_post_id URL or post id.
	 */
	public function get_amazon_product( $url_or_post_id ) {

		if ( is_numeric( $url_or_post_id ) ) {
			// ? get amazon product using post_id
			$post_id   = $url_or_post_id;
			$amazon_id = Lasso_Affiliate_Link::get_amazon_id( $post_id );
			$product   = $this->get_amazon_product_from_db( $amazon_id );
			return $product;
		} else {
			$url = $url_or_post_id;
		}

		// ? get amazon prodcut using url
		if ( empty( $url ) ) {
			return '';
		}

		$url            = trim( $url, '/' );
		$product_id     = self::get_product_id_by_url( $url );
		$product        = $this->fetch_product_info( $product_id, true ); // ? Let's save all Amazon details as well
		$amazon_product = '';

		// ? re-check whether the url is a short url or not
		if ( 'success' !== $product['status'] ) {
			$url        = Lasso_Helper::get_redirect_final_target( $url );
			$product_id = self::get_product_id_by_url( $url );
			$product    = $this->fetch_product_info( $product_id );
		}

		if ( 'success' === $product['status'] ) {
			$product        = $product['product'];
			$shortened_url  = self::get_amazon_product_url( $product['url'] );
			$amazon_product = array(
				'id'          => $product['product_id'],
				'name'        => $product['title'],
				'price'       => $product['price'],
				'url'         => $shortened_url,
				'image'       => $product['image'],
				'description' => '',
			);
		}

		return $amazon_product;
	}

	/**
	 * Check whether Amazon configurations are configured or not
	 */
	public static function is_configured() {
		$lasso_settings = Lasso_Setting::lasso_get_settings();

		return '' !== $lasso_settings['amazon_access_key_id'] && '' !== $lasso_settings['amazon_secret_key'] && '' !== $lasso_settings['amazon_tracking_id'];
	}

	/**
	 * Check amazon link status
	 *
	 * @param string $url URL.
	 */
	public static function check_amazon_link_status( $url ) {
		// ? skip checking amazon link if the settings is not configured
		if ( ! self::is_configured() ) {
			return 200;
		}

		$status            = 500;
		$lasso_amazon_api  = new Lasso_Amazon_Api();
		$amazon_product_id = self::get_product_id_by_url( $url );
		$result            = $lasso_amazon_api->fetch_product_info( $amazon_product_id );
		$amz_status        = $result['status'] ?? '';
		if ( 'success' === $amz_status ) {
			// ? out of stock product.
			$status = isset( $result['product']['quantity'] ) && 0 === $result['product']['quantity'] ? '000' : 200;
		} elseif ( 'fail' === $amz_status ) {
			if ( in_array( $result['error_code'], self::get_ignore_error_codes(), true ) ) {
				return 200;
			} else {
				$status = 404;
			}
		} else {
			$status = 404;
		}

		return $status;
	}

	/**
	 * Get Amazon product by Lasso post id
	 *
	 * @param int    $lasso_id Lasso post id.
	 * @param string $amazon_product_id Amazon product id. Default to empty.
	 *
	 * @return array
	 */
	public function get_amazon_product_by_id( $lasso_id, $amazon_product_id = '' ) {
		$amazon_product = $this->get_amazon_product_from_db( $amazon_product_id );

		if ( empty( $amazon_product ) || ! $amazon_product ) {
			return false;
		}

		$default_image = LASSO_PLUGIN_URL . 'admin/assets/images/lasso-no-thumbnail.jpg';
		$data          = get_post( $lasso_id );
		$image         = ! empty( $amazon_product['default_image'] ) ? $amazon_product['default_image'] : $default_image;

		// ? get description
		$description = $data->post_content ?? '';
		if ( empty( $description ) ) {
			$description = get_post_meta( $lasso_id, 'affiliate_desc', true );
		}
		if ( Lasso_Helper::is_description_empty( $description ) && $amazon_product['features'] ) {
			$description        = '';
			$aawp_options       = get_option( 'aawp_output' );
			$description_items  = $aawp_options['description_items'] ?? 999;
			$description_length = $aawp_options['description_length'] ?? 999;

			$lasso_db              = new Lasso_DB();
			$is_imported_from_aawp = $lasso_db->is_imported_from_aawp( $amazon_product_id, $lasso_id );

			$count    = 0;
			$is_admin = is_admin();

			if ( ! is_array( $amazon_product['features'] ) ) {
				$amazon_product['features'] = array();
			}

			$description .= '<ul class="lasso-aawp-desc">';
			foreach ( $amazon_product['features'] as $feature ) {
				if ( $count >= $description_items || ! $is_imported_from_aawp ) {
					break;
				}
				$title_tag = $feature;

				if ( $is_imported_from_aawp && ! $is_admin && strlen( $feature ) > $description_length ) {
					$feature = preg_replace( '/\s+?(\S+)?$/', '', substr( $feature, 0, $description_length + 1 ) ) . '...';
				}
				$description .= '<li title="' . $title_tag . '">' . $feature . '</li>';
				$count++;
			}
			$description .= '</ul>';

			if ( ! $is_imported_from_aawp ) {
				$description = '';
			}
		}

		$product = array(
			'id'              => $amazon_product_id,
			'name'            => $amazon_product['default_product_name'],
			'price'           => $amazon_product['latest_price'] ?? 0,
			'url'             => trim( $amazon_product['base_url'] ),
			'description'     => $description,
			'image'           => $image,
			'last_updated'    => $amazon_product['last_updated'],
			'monetized_url'   => trim( $amazon_product['monetized_url'] ),
			'is_prime'        => $amazon_product['is_prime'],
			'out_of_stock'    => $amazon_product['out_of_stock'],
			'currency'        => $amazon_product['currency'],
			'savings_basis'   => $amazon_product['savings_basis'],
			'rating'          => $amazon_product['rating'] ?? 0,
			'reviews'         => $amazon_product['reviews'] ?? 0,
			'savings_amount'  => $amazon_product['savings_amount'] ?? 0,
			'savings_percent' => $amazon_product['savings_percent'] ?? '',
		);

		return $product;
	}

	/**
	 * Get lasso id from amazon product url
	 *
	 * @param string $amazon_url Amazon url.
	 *
	 * @return array|boolean
	 */
	public function get_lasso_id_from_amazon_url( $amazon_url ) {
		$product_id         = self::get_product_id_by_url( $amazon_url );
		$product_id_country = self::get_product_id_country_by_url( $amazon_url );
		if ( empty( $product_id ) ) {
			return false;
		}

		$query   = '
			SELECT
				tbl_meta.*, ap.base_url, ap.monetized_url
			FROM (
				SELECT post_id, CONVERT(meta_value USING utf8) AS amazon_id
				FROM ' . Model::get_wp_table_name( 'postmeta' ) . ' pm
				INNER JOIN ' . Model::get_wp_table_name( 'posts' ) . " p
					ON pm.post_id = p.ID
					AND p.post_status = 'publish'
					AND p.post_type = %s
				WHERE 
					pm.meta_key='amazon_product_id' 
					AND (pm.meta_value = %s OR pm.meta_value = %s)
			) AS tbl_meta
			LEFT JOIN " . Model::get_wp_table_name( LASSO_AMAZON_PRODUCTS_DB ) . ' ap
			ON CONVERT(ap.amazon_id USING utf8) = CONVERT(tbl_meta.amazon_id USING utf8)
		';
		$prepare = Model::prepare( $query, LASSO_POST_TYPE, $product_id, $product_id_country ); // phpcs:ignore
		$data    = Model::get_row( $prepare, ARRAY_A );

		return is_array( $data ) ? $data : false;
	}

	/**
	 * Update wrong amazon id in DB
	 *
	 * @param array $product Amazon product.
	 */
	public function update_wrong_amazon_product_id( $product ) {
		global $wpdb;

		$amazon_id            = $product['amazon_id'];
		$default_product_name = $product['default_product_name'];
		$latest_price         = $product['latest_price'];
		$base_url             = $product['base_url'];
		$monetized_url        = $product['monetized_url'];
		$default_image        = $product['default_image'];
		$last_updated         = $product['last_updated'];

		// ? check whether row is wrong amazon id
		// @codingStandardsIgnoreStart
		$prepare = Model::prepare(
			'
            SELECT * 
            FROM ' . Model::get_wp_table_name( LASSO_AMAZON_PRODUCTS_DB ) . ' 
            WHERE default_product_name = %s AND 
                latest_price = %s AND
                base_url = %s AND 
                monetized_url = %s AND 
                default_image = %s 
        ',
			$default_product_name,
			$latest_price,
			$base_url,
			$monetized_url,
			$default_image
		);
		// @codingStandardsIgnoreEnd
		$result = Model::get_results( $prepare );

		if ( count( $result ) > 1 ) {
			// ? delete duplicate rows if that amazon exists
			// @codingStandardsIgnoreStart
			$prepare = Model::prepare(
				'
                DELETE FROM ' . Model::get_wp_table_name( LASSO_AMAZON_PRODUCTS_DB ) . '
                WHERE default_product_name = %s AND 
                    latest_price = %s AND 
                    base_url = %s AND 
                    monetized_url = %s AND 
                    default_image = %s AND 
                    amazon_id <> %s
            ',
				$default_product_name,
				$latest_price,
				$base_url,
				$monetized_url,
				$default_image,
				$amazon_id
			);
			// @codingStandardsIgnoreEnd
			$result = Model::query( $prepare );
		}

		// ? update wrong amazon id rows
		// phpcs:ignore
		$result = $wpdb->update(
			Model::get_wp_table_name( LASSO_AMAZON_PRODUCTS_DB ),
			array(
				'amazon_id' => $amazon_id,
			),
			array(
				'default_product_name' => $default_product_name,
				'latest_price'         => $latest_price,
				'base_url'             => $base_url,
				'monetized_url'        => $monetized_url,
				'default_image'        => $default_image,
				'last_updated'         => $last_updated,
			),
			array( '%s' ),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);

		return $result;
	}

	/**
	 * Build discount pricing html.
	 *
	 * @param string $latest_price Latest price.
	 * @param mixed  $basis_price  Basis price value.
	 * @param string $currency     Currency ISO.
	 */
	public static function build_discount_pricing_html( $latest_price, $basis_price, $currency ) {
		$result = '';

		try {
			$latest_price_value = Lasso_Helper::get_price_value_from_price_text( $latest_price );
			$basis_price_value  = Lasso_Helper::get_price_value_from_price_text( $basis_price );

			if ( $basis_price_value && ( round( $latest_price_value, 2 ) < round( $basis_price_value, 2 ) ) ) {
				$currency_symbol    = Lasso_Helper::get_currency_symbol_from_iso_code( $currency );
				$currency_position  = preg_match( '/[€]|R\$|TL|kr|zł/', $latest_price ) ? 'end' : 'begin';
				$basis_price_format = preg_match( '/[€]|R\$|TL|kr|zł/', $latest_price ) ? number_format( $basis_price_value, 2, ',', '.' ) : number_format( $basis_price_value, 2, '.', ',' );
				$format_price       = 'begin' === $currency_position ? $currency_symbol . $basis_price_format : $basis_price_format . ' ' . $currency_symbol;

				$result = "<strike>$format_price</strike>";
			}
		} catch ( \Exception $e ) {
			Lasso_Helper::write_log( 'Build discount pricing html Error: ' . $e->getMessage(), Lasso_Log::ERROR_LOG, null, true );
		}

		return $result;
	}

	/**
	 * Format price
	 * Ex: convert 19.89USD to $19.89
	 *
	 * @param string $price        Price.
	 * @param string $currency_iso Currency ISO.
	 * @return string
	 */
	public static function format_price( $price, $currency_iso = null ) {
		$currency_iso = $currency_iso ? $currency_iso : self::get_currency_iso_from_price_text( $price );

		if ( $price && $currency_iso ) {
			return self::build_price_with_currency_iso( $price, $currency_iso );
		}

		return $price;
	}

	/**
	 * Get Currency ISO from price text
	 *
	 * @param string $price Price text.
	 * @return mixed|string
	 */
	public static function get_currency_iso_from_price_text( $price ) {
		$result = '';

		foreach ( self::CURRENCY_ISO as $currency_iso ) {
			if ( strpos( $price, $currency_iso ) !== false ) {
				return $currency_iso;
			}
		}

		return $result;
	}

	/**
	 * Build price final format base on the currency ISO
	 *
	 * @param string $price_value  Price value.
	 * @param string $currency_iso Currency ISO.
	 * @return string
	 */
	public static function build_price_with_currency_iso( $price_value, $currency_iso ) {
		$currency_symbol   = Lasso_Helper::get_currency_symbol_from_iso_code( $currency_iso );
		$price_without_iso = str_replace( $currency_iso, '', $price_value );
		$price_value       = Lasso_Helper::get_price_value_from_price_text( $price_without_iso, $currency_symbol );
		$currency_position = preg_match( '/[€]|R\$|TL|kr|zł/', $currency_symbol ) ? 'end' : 'begin';
		$price_format      = preg_match( '/[€]|R\$|TL|kr|zł/', $currency_symbol ) ? number_format( $price_value, 2, ',', '.' ) : number_format( $price_value, 2, '.', ',' );

		return 'begin' === $currency_position ? $currency_symbol . $price_format : $price_format . $currency_symbol;
	}

	/**
	 * Update incorrect monetized url from amazon product database.
	 *
	 * @param array $amazon_product Amazon product result.
	 * @return array Amazon product result.
	 */
	public function update_incorrect_monetized_url( $amazon_product ) {
		$base_domain = Lasso_Helper::get_base_domain( site_url() );
		if ( ! in_array( $base_domain, Lasso_Verbiage::SUPPORT_SITES['update_incorrect_monetized_url'], true ) ) {
			return $amazon_product;
		}

		$url_details = Model_Url_Details::get_by_product_id_and_type( $amazon_product['amazon_id'] );

		if ( $url_details && ! self::is_amazon_shortened_url( $url_details->get_redirect_url() ) ) {
			$target_url             = $url_details->get_redirect_url();
			$target_url_tracking_id = self::get_amazon_tracking_id_by_url( $target_url );

			if ( $target_url_tracking_id ) {
				$amazon_product_monetized_url_tracking_id = self::get_amazon_tracking_id_by_url( $amazon_product['monetized_url'] ?? '' );

				if ( $target_url_tracking_id !== $amazon_product_monetized_url_tracking_id ) {
					$amazon_product['monetized_url'] = self::get_amazon_product_url( $url_details->get_redirect_url(), true, true );
				}
			}
		}

		return $amazon_product;
	}

	/**
	 * Check whether a URL is amazon search page
	 *
	 * @param string $url URL.
	 *
	 * @return bool|string
	 */
	public static function is_amazon_search_page( $url ) {
		$amazon_id      = self::get_product_id_by_url( $url );
		$parse          = wp_parse_url( $url );
		$path           = $parse['path'] ?? '';
		$path           = rtrim( $path, '/' );
		$keywords       = Lasso_Helper::get_argument_from_url( $url, 'keywords' );
		$field_keywords = Lasso_Helper::get_argument_from_url( $url, 'field-keywords' );
		$k              = Lasso_Helper::get_argument_from_url( $url, 'k' );
		$k              = $k ? $k : $field_keywords;
		$k              = $k ? $k : $keywords;

		if ( ! $amazon_id && ( '/s' === substr( $path, -2 ) || strpos( $path, '/s/' ) !== false || strpos( $path, '/s?' ) !== false ) && $k ) {
			return $k;
		}

		return false;
	}

	/**
	 * Check whether a URL is amazon search page
	 *
	 * @param string $url URL.
	 *
	 * @return bool|string
	 */
	public static function get_search_page_title( $url ) {
		$new_title = 'Amazon';
		$k         = self::is_amazon_search_page( $url );
		if ( $k ) {
			$new_title = 'Amazon: ' . $k;
		}

		return $new_title;
	}

	/**
	 * Validate Amazon tracking id
	 *
	 * @param string $tracking_id Amazon tracking id.
	 * @return boolean
	 */
	public static function validate_tracking_id( $tracking_id ) {
		return (bool) preg_match( '/' . self::TRACKING_ID_REGEX . '/i', $tracking_id );
	}

	/**
	 * Get final url of the amazon shortlink from cache
	 *
	 * @param string $shortlink Amazon shortlink.
	 * @return string|null
	 */
	public static function get_shortlink_final_url_cached( $shortlink ) {
		if ( ! self::is_amazon_shortened_url( $shortlink ) ) {
			return null;
		}
		$final_url_cache = get_option( self::build_shortlink_cache_key( $shortlink ) );

		return $final_url_cache ? $final_url_cache : null;
	}

	/**
	 * Build amazon shortlink cache key
	 *
	 * @param string $shortlink Amazon shortlink.
	 * @return string|null
	 */
	public static function build_shortlink_cache_key( $shortlink ) {
		if ( ! self::is_amazon_shortened_url( $shortlink ) ) {
			return null;
		}

		$parse = wp_parse_url( $shortlink );
		$host  = str_replace( '.', '_', $parse['host'] );
		$id    = trim( $parse['path'] ?? '', '/' );

		return $host . '_' . $id;
	}

	/**
	 * Get product review url.
	 *
	 * @param string $url Amazon product url.
	 * @return string
	 */
	public static function get_product_review_url( $url ) {
		$result     = '';
		$product_id = self::get_product_id_by_url( $url );

		if ( $product_id ) {
			$base_domain = Lasso_Helper::get_base_domain( $url );
			$result      = 'https://www.' . $base_domain . "/product-reviews/$product_id";
		}

		return $result;
	}

	/**
	 * Format Amazon URLs
	 *
	 * @param string $url Amazon product url.
	 * @return string URL.
	 */
	public static function format_amazon_url( $url ) {
		$is_amazon_link = self::is_amazon_url( $url );
		$product_id     = self::get_product_id_by_url( $url );

		if ( $is_amazon_link && $product_id && strpos( $url, 'smile.amazon.' ) !== false ) {
			$url = str_replace( 'smile.amazon.', 'amazon.', $url );
		}

		return $url;
	}

	/**
	 * Get the variation item in stock
	 *
	 * @param string $product_id     Product id.
	 * @param string $product_url    Product url.
	 * @param int    $variation_page Variation page.
	 * @return array
	 */
	public function get_product_variation( $product_id, $product_url, $variation_page = 1 ) {
		$result = $this->get_product_variations_by_id_v5( $product_id, $variation_page );
		$items  = $result->VariationsResult->Items ?? array(); // phpcs:ignore

		if ( ! empty( $items ) ) {
			$items              = $result->VariationsResult->Items; // phpcs:ignore
			$product_variations = array();

			// ? Get product variation list
			foreach ( $items as $item ) {
				$product_variations[] = $this->extract_search_result_v5( $item, true, $product_id, $product_url );
			}

			// ? Sort price from lowest to highest
			usort(
				$product_variations,
				function( $a, $b ) {
					return strcmp( $a['amount'], $b['amount'] );
				}
			);

			// ? Get the in-stock product
			foreach ( $product_variations as $product_variation ) {
				if ( $product_variation['quantity'] && $product_variation['price'] ) {
					return $product_variation;
				}
			}

			// ? If all variation products in this page unavailable, we request to the next page
			$page_count = $result->VariationsResult->VariationSummary->PageCount ?? 1; // phpcs:ignore
			if ( $variation_page < self::VARIATION_PAGE_LIMIT && $variation_page < $page_count ) {
				sleep( 1 ); // ? Delay for a while before call the next request
				return $this->get_product_variation( $product_id, $product_url, $variation_page + 1 );
			}
		}

		return array();
	}

	/**
	 * Get Amazon product variations by product id
	 *
	 * @param string $product_id     Amazon product id.
	 * @param int    $variation_page Variation page.
	 *
	 * @return object
	 */
	public function get_product_variations_by_id_v5( $product_id, $variation_page = 1 ) {
		$parameters = array(
			'Operation'     => 'GetVariations',
			'ASIN'          => $product_id,
			'Condition'     => 'New',
			'VariationPage' => $variation_page,
			'Resources'     => array(
				'Images.Primary.Small',
				'Images.Primary.Large',
				'ItemInfo.Title',
				'ItemInfo.ContentRating',
				'ItemInfo.Features',
				'ItemInfo.ProductInfo',
				'ItemInfo.TechnicalInfo',
				'Offers.Listings.Price',
				'Offers.Listings.SavingBasis',
				'Offers.Summaries.OfferCount',
				'Offers.Listings.DeliveryInfo.IsPrimeEligible',
			),
		);

		$json_response = $this->query_amazon_v5( $parameters );

		return $json_response;
	}

	/**
	 * Get Amazon product URL fom Site Stripe URL
	 *
	 * @param string $url URL.
	 */
	public static function get_site_stripe_url( $url ) {
		$is_site_stripe_url    = strpos( $url, Lasso_Cron::SITE_STRIPE_DOMAIN ) !== false;
		$is_site_stripe_eu_url = strpos( $url, Lasso_Cron::SITE_STRIPE_EU_DOMAIN ) !== false;
		$site_stripe_domain    = $is_site_stripe_eu_url ? Lasso_Cron::SITE_STRIPE_EU_DOMAIN : Lasso_Cron::SITE_STRIPE_DOMAIN;
		if ( ! $is_site_stripe_url && ! $is_site_stripe_eu_url ) {
			return $url;
		}

		$temp     = explode( $site_stripe_domain, $url );
		$temp_url = 'https://' . $site_stripe_domain . $temp[1];

		$param_asin = Lasso_Helper::get_argument_from_url( $temp_url, 'asins', true ); // ? iframe blocks
		$param_asin = $param_asin ? $param_asin : Lasso_Helper::get_argument_from_url( $temp_url, 'asin', true ); // ? img blocks

		$param_tag = Lasso_Helper::get_argument_from_url( $temp_url, 'tracking_id', true ); // ? iframe blocks
		$param_tag = $param_tag ? $param_tag : Lasso_Helper::get_argument_from_url( $temp_url, 'tag', true ); // ? img blocks

		$marketplace = Lasso_Helper::get_argument_from_url( $temp_url, 'marketplace', true );
		$region      = Lasso_Helper::get_argument_from_url( $temp_url, 'region', true );
		$region      = $region ? $region : $marketplace;
		$region      = $region ? $region : 'us';
		$region      = strtolower( $region );

		$amazon_data   = self::get_amazon_api_countries();
		$amazon_domain = $amazon_data[ $region ]['amazon_domain'] ?? 'www.amazon.com';

		$amazon_url = 'https://' . $amazon_domain . '/dp/' . $param_asin . '/?tag=' . $param_tag;

		$url = self::get_amazon_product_url( $amazon_url, true, false, $param_tag );

		return $url;
	}

	/**
	 * Get Amazon default product url by product id
	 *
	 * @param string $product_id Product id.
	 */
	public static function get_default_product_domain( $product_id ) {
		$amazon_default_country = Lasso_Setting::lasso_get_setting( 'amazon_default_tracking_country', 'us' );
		$amazon_countries       = self::get_amazon_api_countries();
		$amazon_default_domain  = $amazon_countries[ $amazon_default_country ]['amazon_domain'];
		$amazon_product_domain  = 'https://' . $amazon_default_domain . '/dp/';

		return $amazon_product_domain . $product_id;
	}
}
