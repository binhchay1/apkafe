<?php
/**
 * Declare class Extend_Product
 *
 * @package Extend_Product
 */

namespace Lasso\Classes;

use Lasso\Classes\Helper as Lasso_Helper;

use Lasso\Models\Model;

use Lasso_DB;

/**
 * Extend_Product
 */
class Extend_Product {
	const PRODUCT_TYPE_CHEWY                = 'chewy';
	const PRODUCT_TYPE_WAYFAIR              = 'wayfair';
	const PRODUCT_TYPE_WALMART              = 'walmart';
	const PRODUCT_TYPE_TARGET               = 'target';
	const PRODUCT_TYPE_HOMEDEPOT            = 'homedepot';
	const PRODUCT_TYPE_GROUPON              = 'groupon';
	const PRODUCT_TYPE_BESTBUY              = 'bestbuy';
	const PRODUCT_TYPE_BESTBUY_CA           = 'bestbuy-ca';
	const PRODUCT_TYPE_FANATICS             = 'fanatics';
	const PRODUCT_TYPE_FANATICS_INTL        = 'fanatics-intl';
	const PRODUCT_TYPE_FANATICS_INTL_STORE2 = 'fanatics-intl2';
	const PRODUCT_TYPE_FANATICS_INTL_STORE3 = 'fanatics-intl3';
	const PRODUCT_TYPE_FANATICS_CA          = 'fanatics-ca';
	const PRODUCT_TYPE_FANSEDGE             = 'fansedge';
	const PRODUCT_TYPE_LIDS                 = 'lids';
	const PRODUCT_TYPE_LIDS_CA              = 'lids-ca';
	const PRODUCT_TYPE_NFLSHOP              = 'nflshop';
	const PRODUCT_TYPE_NFLSHOP_CA           = 'nflshop-ca';
	const PRODUCT_TYPE_NFLSHOP_EU           = 'nflshop-eu';
	const PRODUCT_TYPE_NFLSHOP_EU2          = 'nflshop-eu2';
	const PRODUCT_TYPE_NHL                  = 'nhl';
	const PRODUCT_TYPE_NHL_CA               = 'nhl-ca';
	const PRODUCT_TYPE_NHL_INTL             = 'nhl-intl';
	const PRODUCT_TYPE_NHL_INTL2            = 'nhl-intl2';
	const PRODUCT_TYPE_NBA                  = 'nba';
	const PRODUCT_TYPE_NBA_2K               = 'nba-2k';
	const PRODUCT_TYPE_NBA_WNBA             = 'nba-wnba';
	const PRODUCT_TYPE_NBASTORE             = 'nbastore';
	const PRODUCT_TYPE_NBASTORE_AU          = 'nbastore-au';
	const PRODUCT_TYPE_NBASTORE_CA          = 'nbastore-ca';
	const PRODUCT_TYPE_NBASTORE_EU          = 'nbastore-eu';
	const PRODUCT_TYPE_NBASTORE_JP          = 'nbastore-jp';
	const PRODUCT_TYPE_NBASTORE_UK          = 'nbastore-uk';
	const PRODUCT_TYPE_MLSSTORE             = 'mlsstore';
	const PRODUCT_TYPE_MLSSTORE_CA          = 'mlsstore-ca';
	const PRODUCT_TYPE_MLBSHOP              = 'mlbshop';
	const PRODUCT_TYPE_MLBSHOP_CA           = 'mlbshop-ca';
	const PRODUCT_TYPE_MLBSHOP_EU           = 'mlbshop-eu';
	const PRODUCT_TYPE_MLBSHOP_ROW          = 'mlbshop-row';
	const PRODUCT_TYPE_MLBSHOP_UK           = 'mlbshop-uk';
	const PRODUCT_TYPE_WWE                  = 'wwe';
	const PRODUCT_TYPE_WWE_EU               = 'wwe-eu';
	const PRODUCT_TYPE_WWE_ROW              = 'wwe-row';
	const PRODUCT_TYPE_WWE_UK               = 'wwe-uk';


	/**
	 * Get extend domains.
	 */
	public static function get_domains() {
		return array(
			self::PRODUCT_TYPE_CHEWY                => 'chewy.com',
			self::PRODUCT_TYPE_WAYFAIR              => 'wayfair.com',
			self::PRODUCT_TYPE_WALMART              => 'walmart.com',
			self::PRODUCT_TYPE_TARGET               => 'target.com',
			self::PRODUCT_TYPE_HOMEDEPOT            => 'homedepot.com',
			self::PRODUCT_TYPE_GROUPON              => 'groupon.com',
			self::PRODUCT_TYPE_BESTBUY              => 'bestbuy.com',
			self::PRODUCT_TYPE_BESTBUY_CA           => 'bestbuy.ca',
			self::PRODUCT_TYPE_FANATICS             => 'fanatics.com',
			self::PRODUCT_TYPE_FANATICS_INTL        => 'fanatics-intl.com',
			self::PRODUCT_TYPE_FANATICS_INTL_STORE2 => 'store2.fanatics-intl.com',
			self::PRODUCT_TYPE_FANATICS_INTL_STORE3 => 'store3.fanatics-intl.com',
			self::PRODUCT_TYPE_FANATICS_CA          => 'fanatics.ca',
			self::PRODUCT_TYPE_FANSEDGE             => 'fansedge.com',
			self::PRODUCT_TYPE_LIDS                 => 'lids.com',
			self::PRODUCT_TYPE_LIDS_CA              => 'lids.ca',
			self::PRODUCT_TYPE_NFLSHOP              => 'nflshop.com',
			self::PRODUCT_TYPE_NFLSHOP_CA           => 'nflshop.ca',
			self::PRODUCT_TYPE_NFLSHOP_EU           => 'europe.nflshop.com',
			self::PRODUCT_TYPE_NFLSHOP_EU2          => 'europe2.nflshop.com',
			self::PRODUCT_TYPE_NHL                  => 'shop.nhl.com',
			self::PRODUCT_TYPE_NHL_CA               => 'nhlshop.ca',
			self::PRODUCT_TYPE_NHL_INTL             => 'shop.international.nhl.com',
			self::PRODUCT_TYPE_NHL_INTL2            => 'shop2.international.nhl.com',
			self::PRODUCT_TYPE_NBA                  => 'store.nba.com',
			self::PRODUCT_TYPE_NBA_2K               => '2kleaguestore.nba.com',
			self::PRODUCT_TYPE_NBA_WNBA             => 'wnbastore.nba.com',
			self::PRODUCT_TYPE_NBASTORE             => 'global.nbastore.com',
			self::PRODUCT_TYPE_NBASTORE_AU          => 'nbastore.com.au',
			self::PRODUCT_TYPE_NBASTORE_CA          => 'nbastore.ca',
			self::PRODUCT_TYPE_NBASTORE_EU          => 'nbastore.eu',
			self::PRODUCT_TYPE_NBASTORE_JP          => 'nbastore.jp',
			self::PRODUCT_TYPE_NBASTORE_UK          => 'www2.nbastore.eu',
			self::PRODUCT_TYPE_MLSSTORE             => 'mlsstore.com',
			self::PRODUCT_TYPE_MLSSTORE_CA          => 'mlsstore.ca',
			self::PRODUCT_TYPE_MLBSHOP              => 'mlbshop.com',
			self::PRODUCT_TYPE_MLBSHOP_CA           => 'mlbshop.ca',
			self::PRODUCT_TYPE_MLBSHOP_EU           => 'mlbshopeurope.com',
			self::PRODUCT_TYPE_MLBSHOP_ROW          => 'www3.mlbshopeurope.com',
			self::PRODUCT_TYPE_MLBSHOP_UK           => 'www2.mlbshopeurope.com',
			self::PRODUCT_TYPE_WWE                  => 'shop.wwe.com',
			self::PRODUCT_TYPE_WWE_EU               => 'euroshop2.wwe.com',
			self::PRODUCT_TYPE_WWE_ROW              => 'euroshop3.wwe.com',
			self::PRODUCT_TYPE_WWE_UK               => 'euroshop.wwe.com',

		);
	}

	/**
	 * Get extend product type by url
	 *
	 * @param string  $url Link.
	 * @param boolean $skip_check_url_from_param Skip checking if the extend product link from parameter.
	 * @return string|bool
	 */
	public static function get_extend_product_type_from_url( $url, $skip_check_url_from_param = false ) {
		if ( empty( $url ) ) {
			return false;
		}

		$url     = Lasso_Helper::add_https( $url );
		$domains = self::get_domains();
		$domain  = Lasso_Helper::get_base_domain( $url );

		if ( in_array( $domain, $domains, true ) ) {
			return array_search( $domain, $domains, true );
		} elseif ( ! $skip_check_url_from_param ) {
			$final_url_from_url_param = Lasso_Helper::get_final_url_from_url_param( $url );
			if ( $final_url_from_url_param ) {
				return self::get_extend_product_type_from_url( $final_url_from_url_param, true );
			}
		}

		return false;
	}

	/**
	 * Get Extend product id by url
	 *
	 * @param string $url Link.
	 * @return array|bool
	 */
	public static function get_extend_product_id_by_url( $url ) {
		$product_type = self::get_extend_product_type_from_url( $url );
		if ( ! $product_type ) {
			return false;
		}

		$final_url = Lasso_Helper::get_final_url_from_url_param( $url );
		$url       = $final_url ? $final_url : $url;
		$url       = Lasso_Helper::add_https( $url );
		$domain    = Lasso_Helper::get_base_domain( $url );
		$matches   = array();
		$reg       = '##';

		// ? Extend products
		switch ( $product_type ) {
			case self::PRODUCT_TYPE_CHEWY:
				$reg = '#' . $domain . '(?:/.*){0,1}(?:/dp/)([0-9]+)#';
				break;
			case self::PRODUCT_TYPE_WAYFAIR:
				$reg = '#' . $domain . '(?:/.*){0,1}(?:/pdp/)(?:[\\w-]+)-([\\w]+)(\.html)#';
				break;
			case self::PRODUCT_TYPE_WALMART:
				$reg = '#' . $domain . '(?:/ip)(?:/[\\w-]+){0,1}/([0-9]+)#';
				break;
			case self::PRODUCT_TYPE_TARGET:
				$reg = '#' . $domain . '(?:/p)(?:/[\\w-]+){0,1}/-/(\\w+-[0-9]+)#';
				break;
			case self::PRODUCT_TYPE_HOMEDEPOT:
				$reg = '#' . $domain . '(?:/p)(?:/[\\w-]+){0,1}/([0-9]+)#';
				break;
			case self::PRODUCT_TYPE_GROUPON:
				$reg = '#' . $domain . '(?:/deals/)([\\w-]+)#';
				break;
			case self::PRODUCT_TYPE_BESTBUY:
				$reg  = '#' . $domain;
				$reg .= false !== strpos( $url, $domain . '/site/combo/' ) ? '(?:/site/combo/)(?:[\\w-]+){0,1}/([\\w-]+)#' : '(?:/site)(?:/[\\w-]+){0,1}/([0-9]+)(?:\.p)#';
				break;
			case self::PRODUCT_TYPE_BESTBUY_CA:
				$reg = '#' . $domain . '(?:/en-ca/product)(?:/[\\w-]+){0,1}/([\\w-]+)#';
				break;
		}

		// ? Fanatics products
		if ( in_array(
			$product_type,
			array(
				self::PRODUCT_TYPE_NFLSHOP,
				self::PRODUCT_TYPE_NFLSHOP_CA,
				self::PRODUCT_TYPE_NFLSHOP_EU,
				self::PRODUCT_TYPE_NFLSHOP_EU2,
				self::PRODUCT_TYPE_NHL_INTL,
				self::PRODUCT_TYPE_NHL_INTL2,
				self::PRODUCT_TYPE_NHL,
				self::PRODUCT_TYPE_NHL_CA,
				self::PRODUCT_TYPE_NBA,
				self::PRODUCT_TYPE_NBASTORE,
				self::PRODUCT_TYPE_NBASTORE_AU,
				self::PRODUCT_TYPE_NBASTORE_CA,
				self::PRODUCT_TYPE_NBASTORE_EU,
				self::PRODUCT_TYPE_NBASTORE_JP,
				self::PRODUCT_TYPE_NBASTORE_UK,
				self::PRODUCT_TYPE_MLSSTORE,
				self::PRODUCT_TYPE_MLBSHOP,
				self::PRODUCT_TYPE_NBA_2K,
				self::PRODUCT_TYPE_NBA_WNBA,
				self::PRODUCT_TYPE_MLSSTORE_CA,
				self::PRODUCT_TYPE_MLBSHOP_CA,
				self::PRODUCT_TYPE_MLBSHOP_EU,
				self::PRODUCT_TYPE_MLBSHOP_ROW,
				self::PRODUCT_TYPE_MLBSHOP_UK,
			),
			true
		) ) {
			/**
			 * Example: https://www.nflshop.com/pittsburgh-steelers/mens-pittsburgh-steelers-black-scoreboard-pullover-hoodie/t-14376050+p-8202232265323+z-9-4032295482?_ref=p-TLP:m-DDEAME:pi-BELOW_SLEEPER_2:i-r0c0:po-0
			 * ID: t-14376050+p-8202232265323+z-9-4032295482
			 */
			$reg = '#' . $domain . '(?:/[\\w-]+)(?:/.+)+/(t-\\d+\+p-\\d+[\\w+-]+)#';
		} elseif ( in_array( $product_type, array( self::PRODUCT_TYPE_FANSEDGE, self::PRODUCT_TYPE_WWE, self::PRODUCT_TYPE_WWE_EU, self::PRODUCT_TYPE_WWE_ROW, self::PRODUCT_TYPE_WWE_UK ), true ) ) {
			/**
			 * Example: https://www.fansedge.com/en/nike-manny-machado-san-diego-padres-womens-white-brown-home-replica-player-jersey/p-25291683570657+z-9802-3391121255?_ref=p-GALP:m-GRID:i-r22c0:po-66
			 * ID: p-25291683570657+z-9802-3391121255
			 */
			$reg = '#' . $domain . '(?:/[\\w-]+)(?:/.+)+/(p-\\d+\+z-[\\w+-]+)#';
		} elseif ( in_array(
			$product_type,
			array(
				self::PRODUCT_TYPE_FANATICS,
				self::PRODUCT_TYPE_FANATICS_INTL,
				self::PRODUCT_TYPE_FANATICS_INTL_STORE2,
				self::PRODUCT_TYPE_FANATICS_INTL_STORE3,
				self::PRODUCT_TYPE_FANATICS_CA,
				self::PRODUCT_TYPE_LIDS,
				self::PRODUCT_TYPE_LIDS_CA,
			),
			true
		) ) {
			/**
			 * Example: https://www.fanatics.com/international-clubs/arsenal/arsenal-adidas-womens-2022/23-away-replica-custom-jersey-black/o-19323588+t-58077847+p-935492928+z-9-1295894817?_ref=p-TLP:m-GRID:i-r6c2:po-20
			 * ID: o-19323588+t-58077847+p-935492928+z-9-1295894817
			 */
			$reg = '#' . $domain . '(?:/[\\w-]+)(?:/.+)+/(o-\\d+\+t-\\d+\+p-[\\w+-]+)#';
		}

		preg_match( $reg, $url, $matches );
		$product_id = isset( $matches[1] ) && ! empty( $matches[1] ) ? $matches[1] : false;

		if ( ! $product_id && self::PRODUCT_TYPE_FANATICS === $product_type ) {
			/**
			 * Example: https://www.fanatics.com/Andre_Drummond_Philadelphia_76ers_Game-Used_Nike_Number_1_Jersey_vs._Memphis_Grizzlies_on_January_31_2022/p-5162925
			 * ID: p-5162925
			 */
			$reg = '#' . $domain . '\/(.*?)\/(p-\d+)#';
			preg_match( $reg, $url, $matches );
			$product_id = isset( $matches[2] ) && ! empty( $matches[2] ) ? $matches[2] : false;
		}

		$product_id = $product_id && ( self::PRODUCT_TYPE_BESTBUY_CA === $product_type ) ? 'ca-' . $product_id : $product_id;

		return $product_id;
	}

	/**
	 * Get extend product from DB
	 *
	 * @param string  $product_type Extend product type.
	 * @param string  $product_id   Extend product id.
	 * @param string  $select       Select column query.
	 * @param boolean $is_use_cache Is use cache.
	 */
	public static function get_extend_product_from_db( $product_type, $product_id, $select = '*', $is_use_cache = false ) {
		if ( ! $product_type || ! $product_id ) {
			return false;
		}

		$sql = "
			SELECT $select 
			FROM " . Model::get_wp_table_name( LASSO_EXTEND_PRODUCTS ) . ' 
			WHERE product_type = %s
				AND product_id = %s
		';

		// ? Enable cache on frontend to prevent duplicate queries issue
		if ( ! is_admin() ) {
			$is_use_cache = true;
		}

		$prepare = Model::prepare( $sql, $product_type, $product_id ); // phpcs:ignore
		$result  = Model::get_row( $prepare, ARRAY_A, $is_use_cache );

		return $result;
	}

	/**
	 * Insert or Update Extend Product Data
	 *
	 * @param array       $product    Product.
	 * @param bool|string $updated_at Set update date time. Default to false.
	 */
	public function update_extend_product_in_db( $product, $updated_at = false ) {
		global $wpdb;

		$lasso_db = new Lasso_DB();

		$product_id           = $product['product_id'] ?? '';
		$product_type         = $product['product_type'] ?? '';
		$default_product_name = $product['title'] ?? '';
		$latest_price         = $product['price'] ?? '';
		$latest_price         = '0' === $latest_price || ( is_int( $latest_price ) && 0 === $latest_price ) ? '' : $latest_price;
		$base_url             = $product['default_url'] ?? '';
		$default_image        = trim( $product['image'] ?? '' );
		$last_updated         = gmdate( 'Y-m-d H:i:s', time() );
		$last_updated         = $updated_at ? $updated_at : $last_updated;
		$is_manual            = $product['is_manual'] ?? 0;
		$quantity             = intval( $product['quantity'] ?? 200 );
		$out_of_stock         = 0 === $quantity ? 1 : 0;

		if ( ! $product_type || ! $product_id || '' === $default_product_name || '' === $default_image
			|| ( '' !== $default_image && Lasso_Helper::validate_url( $default_image ) === false && strpos( $default_image, 'data:image' ) !== 0 )
		) {
			return false;
		}

		$lasso_db->resolve_issue_by_url( $base_url, '404' );
		if ( 0 === $out_of_stock ) {
			$lasso_db->resolve_product_out_of_stock( $product_id, $product_type );
		}

		$base_url = trim( $base_url );
		$query    = '
            INSERT INTO ' . Model::get_wp_table_name( LASSO_EXTEND_PRODUCTS ) . "
                (
                    product_id, product_type, default_product_name, latest_price, base_url, 
                    default_image, last_updated, is_manual, out_of_stock
                )
            VALUES
                (
                    %s, %s, %s, %s, %s, 
                    %s, %s, %d, %d
                )
            ON DUPLICATE KEY UPDATE
                product_id = %s,
                product_type = %s,
                default_product_name = %s,
                latest_price = %s,
                base_url = %s,
                default_image = (CASE WHEN %s='' or %s IS NULL THEN `default_image` ELSE %s END),
                last_updated = %s,
                is_manual = %d,
                out_of_stock = %d
            ;
		";
		$prepare  = $wpdb->prepare(
			// phpcs:ignore
			$query,
			// ? First for insert
			$product_id,
			$product_type,
			$default_product_name,
			$latest_price,
			$base_url,
			$default_image,
			$last_updated,
			$is_manual,
			$out_of_stock,
			// ? Second for update
			$product_id,
			$product_type,
			$default_product_name,
			$latest_price,
			$base_url,
			$default_image,
			$default_image,
			$default_image,
			$last_updated,
			$is_manual,
			$out_of_stock
		);

		Model::query( $prepare );

		return true;
	}

	/**
	 * Fetch extend product
	 *
	 * @param string      $link          Link.
	 * @param bool        $store_product Store product into DB or not. Default to false.
	 * @param bool|string $updated_at    Set date time or not. Default to false.
	 */
	public function fetch_product_info( $link, $store_product = false, $updated_at = false ) {
		list( $product, $status ) = $this->fetch_product_from_bls( $link, $store_product, $updated_at );
		return array(
			'product'    => $product,
			'api'        => 'no',
			'full_item'  => array(),
			'status'     => 200 === $status ? 'success' : 'fail',
			'error_code' => 404 === $status ? 'NotFound' : '',
		);
	}

	/**
	 * Fetch extend product from BLS (Lambda)
	 *
	 * @param string      $link          Link.
	 * @param bool        $store_product Store product into DB or not. Default to false.
	 * @param bool|string $updated_at    Set date time or not. Default to false.
	 */
	public function fetch_product_from_bls( $link, $store_product = false, $updated_at = false ) {
		$product_id   = self::get_extend_product_id_by_url( $link );
		$product_type = self::get_extend_product_type_from_url( $link );

		$product = array(
			'title' => '',
			'image' => '',
			'url'   => $link,
			'price' => '',
		);

		if ( ! $product_id || ! $product_type ) {
			return array( $product, 404 );
		}

		$res = Lasso_Helper::get_url_status_code_by_broken_link_service( $link, true );
		if ( 200 === $res['status_code'] && 200 === $res['response']->status ) {
			$img_url      = $res['response']->imgUrl ?? '';
			$img_url      = '' !== $img_url ? $img_url : '';
			$product_name = $res['response']->productName ?? '';
			$product_name = '' === $product_name ? ( $res['response']->pageTitle ?? '' ) : $product_name;
			$quantity     = $res['response']->quantity ?? 200;
			$price        = $res['response']->price ?? '';
			$temp_url     = $res['response']->finalUrl ?? $link;
			$url          = '' !== $temp_url ? $temp_url : $link;

			if ( $store_product ) {
				$store_data = array(
					'product_type' => $product_type,
					'product_id'   => $product_id,
					'title'        => $product_name,
					'price'        => $price,
					'default_url'  => $url,
					'url'          => $url,
					'image'        => trim( $img_url ),
					'quantity'     => intval( $quantity ),  // Manual checks won't show out of stock for now. TODO: Add BLS to out of stock checks.
					'is_manual'    => 1,
				);
				$this->update_extend_product_in_db( $store_data, $updated_at );
			}

			$product['title']       = $product_name;
			$product['image']       = $img_url;
			$product['url']         = $url;
			$product['price']       = $price;
			$product['quantity']    = $quantity;
			$product['status_code'] = $res['response']->status;
		}

		if ( 404 === $res['status_code'] || ( 200 === $res['status_code'] && 404 === $res['response']->status ) ) {
			$last_updated = gmdate( 'Y-m-d H:i:s', time() );
			$this->update_extend_product_field( $product_type, $product_id, 'last_updated', $last_updated );
			$this->update_extend_product_field( $product_type, $product_id, 'out_of_stock', 0 );
		}

		$status = $res['response']->status ?? 200;

		return array( $product, intval( $status ) );
	}

	/**
	 * Update a field for an Extend product
	 *
	 * @param string $product_type Product type.
	 * @param string $product_id   Product id.
	 * @param string $field_name   Field name.
	 * @param string $field_value  Field value.
	 */
	public function update_extend_product_field( $product_type, $product_id, $field_name, $field_value ) {
		$sql     = '
			UPDATE ' . Model::get_wp_table_name( LASSO_EXTEND_PRODUCTS ) . '
			SET `' . $field_name . '` = %s
			WHERE product_type = %s
				AND product_id = %s		
		';
		$prepare = Model::prepare( $sql, $field_value, $product_type, $product_id ); // phpcs:ignore

		return Model::query( $prepare ); // phpcs:ignore
	}

	/**
	 * Get Extend product by Lasso post id and extend product's infos
	 *
	 * @param string $product_type Extend product type. Default to empty.
	 * @param string $product_id   Extend product id. Default to empty.
	 *
	 * @return array
	 */
	public function get_extend_product_by_id( $product_type = '', $product_id = '' ) {
		$product = self::get_extend_product_from_db( $product_type, $product_id );

		if ( empty( $product ) ) {
			return false;
		}

		$default_image = LASSO_DEFAULT_THUMBNAIL;
		$image         = ! empty( $product['default_image'] ) ? $product['default_image'] : $default_image;

		$product = array(
			'product_type' => $product['product_type'],
			'product_id'   => $product['product_id'],
			'name'         => $product['default_product_name'],
			'price'        => $product['latest_price'] ?? 0,
			'url'          => trim( $product['base_url'] ),
			'image'        => $image,
			'last_updated' => $product['last_updated'],
			'out_of_stock' => $product['out_of_stock'],
		);

		return $product;
	}

	/**
	 * Return the correct url to get product id if exist. If the main url is not extend product url then we check the final url.
	 *
	 * @param string $url           Main url.
	 * @param string $get_final_url The redirect final url.
	 * @return string
	 */
	public static function url_to_get_product_id( $url, $get_final_url = false ) {
		// ? Return the primary url if this is extend product domain
		if ( self::get_extend_product_type_from_url( $url, true ) && self::get_extend_product_id_by_url( $url ) ) {
			return $url;
		}

		// ? Return the final url if this is extend product domain
		if ( $get_final_url && self::get_extend_product_type_from_url( $get_final_url ) && self::get_extend_product_id_by_url( $get_final_url ) ) {
			return $get_final_url;
		}

		return $url;
	}
}
