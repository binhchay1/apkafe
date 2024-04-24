<?php
/**
 * Declare class Lasso_Helper
 *
 * @package Lasso_Helper
 */

namespace Lasso\Classes;

use Lasso\Classes\Affiliates as Lasso_Affiliates;
use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;
use Lasso\Classes\Encrypt;
use Lasso\Classes\Enum;
use Lasso\Classes\Extend_Product;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Import;
use Lasso\Classes\Link_Location;
use Lasso\Classes\Log as Lasso_Log;
use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Classes\Setting_Enum;

use Lasso\Models\Fields as Model_Field;
use Lasso\Models\Model;
use Lasso\Models\Link_Locations as Model_Link_Locations;

use Lasso\Pages\Hook as Page_Hook;

use Lasso_Affiliate_Link;
use Lasso_Amazon_Api;
use Lasso_Cron;
use Lasso_DB;
use Lasso_License;

use simple_html_dom;
use Exception;

use WP_Error;
use WP_Filesystem_Direct;

/**
 * Lasso_Helper
 */
class Helper {
	/**
	 * User agent
	 *
	 * @var string $user_agent
	 */
	public static $user_agent = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0';

	/**
	 * Post types are allowed to be scanned by Lasso process.
	 *
	 * @var string $post_types_are_allowed_scanning
	 */
	public static $post_types_are_allowed_scanning = array(
		'post', // ? WP
		'page', // ? WP
		'wp_block', // ? Reusable block
	);

	/**
	 * Limit number of request sent to 404 page
	 *
	 * @var string $limit_404_request
	 */
	private static $limit_404_request = 3;

	/**
	 * Post types are not shown in the list of Custom post type support.
	 *
	 * @var string $post_types_are_excluded
	 */
	public static $post_types_are_excluded = array(
		LASSO_POST_TYPE,
		'pretty-link',
		'affiliate_url',
		'attachment',
		'revision',
		'nav_menu_item',
		'custom_css',
		'customize_changeset',
		'oembed_cache',
		'user_request',
		'wp_block',
		'acf-field-group',
		'acf-field',
		'wpadc',
		'wpcf7_contact_form',
		'elementor_snippet',
		'elementor_font',
		'elementor_icons',
	);

	/**
	 * Get post types that are supported by Lasso
	 */
	public static function get_cpt_support() {
		$default_cpt = self::$post_types_are_allowed_scanning;

		$cpt = Lasso_Setting::lasso_get_setting( 'cpt_support', $default_cpt );
		$cpt = is_array( $cpt ) ? $cpt : $default_cpt;
		$cpt = array_merge( $cpt, $default_cpt );

		if ( is_null( $cpt ) ) {
			$cpt = $default_cpt;
		}

		return array_unique( $cpt );
	}

	/**
	 * Format URL before sending request
	 *
	 * @param string $url    URL.
	 * @param bool   $encode Encode url or not. Default to false.
	 */
	public static function format_url_before_requesting( $url, $encode = false ) {
		$url = trim( $url );
		$url = $encode ? rawurlencode( $url ) : $url;

		return $url;
	}

	/**
	 * Get IP address of user
	 */
	public static function get_user_ip_address() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = self::get_server_param( 'HTTP_CLIENT_IP' );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = self::get_server_param( 'HTTP_X_FORWARDED_FOR' );
		} else {
			$ip = self::get_server_param( 'REMOTE_ADDR' );
		}

		return apply_filters( 'lasso_get_user_ip_address', $ip );
	}

	/**
	 * Validate URL
	 *
	 * @param string $url URL.
	 */
	public static function validate_url( $url ) {
		if ( ! is_string( $url ) ) {
			return false;
		}

		$url = str_replace( ' ', '%20', $url );
		$url = preg_replace( '/[^\00-\255]+/u', '', $url );

		return ( ( strpos( $url, 'http://' ) === 0 || strpos( $url, 'https://' ) === 0 ) &&
			filter_var( $url, FILTER_VALIDATE_URL ) !== false );
	}

	/**
	 * Get url without parameters
	 *
	 * @param string $url URL.
	 * @return string
	 */
	public static function get_url_without_parameters( $url ) {
		if ( ! self::validate_url( $url ) ) {
			return $url;
		}

		$tmp_url = explode( '?', $url );

		return $tmp_url[0] ?? $url;
	}

	/**
	 * Get the status code of the URL
	 *
	 * @param string $url          URL.
	 * @param bool   $get_response Get response or not. Default to false.
	 */
	public static function get_url_status_code( $url, $get_response = false ) {
		$use_bls = apply_filters( 'get_final_url_domain_bls', false, $url );
		if ( $use_bls ) {
			self::get_redirect_final_target( $url, false, false );
			return Lasso_Cache_Per_Process::get_instance()->get_cache( Lasso_Affiliate_Link::ADD_NEW_LINK_RESPONSE_STATUS . md5( $url ), 200 );
		}

		$browser = self::get_server_param( 'HTTP_USER_AGENT' );
		$browser = '' !== $browser ? $browser : self::$user_agent;
		$url     = Lasso_Amazon_Api::get_amazon_product_url( $url, false );
		$url     = self::format_url_before_requesting( $url );

		$amazon_product_id = Lasso_Amazon_Api::get_product_id_by_url( $url );
		if ( Lasso_Amazon_Api::is_amazon_url( $url ) && $amazon_product_id ) {
			$status = Lasso_Amazon_Api::check_amazon_link_status( $url );
			return $status;
		}

		// ? Call to BLS if $url is amazon link or extends support links
		if ( ! Lasso_Amazon_Api::is_amazon_url( $url ) && ! Extend_Product::get_extend_product_type_from_url( $url ) ) {
			$res    = self::send_request( 'get', $url );
			$status = $res['status_code'];

			if ( 404 === $status ) {
				return $get_response ? $res : $status;
			}
		} else {
			$status = 403; // ? amazon links are blocked by amazon, we need to use BLS
		}

		self::write_log( 'Link: ' . $url, 'get_url_status_code' );
		self::write_log( 'Before BLS Status: ' . $status, 'get_url_status_code' );
		if ( $status >= 301 || $status >= 302 || ( $status >= 400 && $status <= 599 ) ) {
			self::write_log( 'BLS: Yes', 'get_url_status_code' );

			$res    = self::get_url_status_code_by_broken_link_service( $url, true );
			$status = intval( $res['response']->status ?? $status );
			self::write_log( 'BLS Status: ' . $status, 'get_url_status_code' );
		}

		return $get_response ? $res : $status;
	}

	/**
	 * Get status from BLS
	 *
	 * @param string $url     URL.
	 * @param bool   $get_res Get response or not. Default to false.
	 * @param bool   $is_lasso_save Is Lasso save data action. Default to false.
	 */
	public static function get_url_status_code_by_broken_link_service( $url, $get_res = false, $is_lasso_save = false ) {
		$status   = 200;
		$log_name = 'get_url_status_code_by_broken_link_service';
		$url      = Lasso_Amazon_Api::get_amazon_product_url( $url, false );
		$url      = self::format_url_before_requesting( $url );

		$lasso_db      = new Lasso_DB();
		$total_request = $lasso_db->get_total_404_request_to_bls( $url );
		if ( ! $is_lasso_save && $total_request >= self::$limit_404_request ) {
			return $get_res ? array(
				'status_code' => 404,
				'response'    => array(),
			) : $status;
		}

		self::write_log( 'Link: ' . $url, $log_name );
		self::write_log( 'Total request: ' . $total_request, $log_name );
		self::write_log( 'Before BLS Status: ' . $status, $log_name );
		self::write_log( 'BLS: Yes', $log_name );

		$headers = self::get_lasso_headers();
		$data    = array(
			'url' => $url,
		);

		$encrypted_base64 = Encrypt::encrypt_aes( $data, true );
		$res              = self::send_request( 'get', LASSO_LINK . '/link/status/?' . $encrypted_base64, array(), $headers );
		self::write_log( 'BLS RESPONSE: ' . wp_json_encode( $res ), $log_name );
		$status_temp = intval( $res['response']->status ?? $status );

		if ( 404 === $status_temp ) {
			if ( $is_lasso_save ) {
				$lasso_db->set_total_404_request_to_bls( $url, 0 );
			} elseif ( $total_request < self::$limit_404_request ) {
				$total_request++;
				$lasso_db->set_total_404_request_to_bls( $url, $total_request );
			}
		}

		return $get_res ? $res : $status_temp;
	}

	/**
	 * Get page title from HTML
	 *
	 * @param string $html HTML string.
	 */
	public static function get_page_title( $html ) {
		$temp = explode( '<title', $html )[1] ?? '';
		$html = $temp ? '<title' . $temp : $html;
		$temp = explode( '</title>', $html )[0] ?? '';
		$html = $temp ? $temp . '</title>' : $html;
		$res  = preg_match( '/<title\s*(.*?)>(.*?)<\/title>/siU', $html, $title_matches );
		if ( ! $res ) {
			return '';
		}

		// ? Clean up title: remove EOL's and excessive whitespace.
		$title = preg_replace( '/\s+/', ' ', $title_matches[2] ?? '' );
		// ? String – to UTF8 is \xe2\x80\x93, replace by -
		$title = str_replace( '–', '-', $title );
		// ? Remove all non-US-ASCII (i.e. outside 0x0-0x7F) characters
		$title = preg_replace( '/[^\x00-\x7F]/', '', $title );
		$title = trim( $title );
		$title = self::format_post_title( $title );

		return $title;
	}

	/**
	 * FOLLOW ALL REDIRECTS
	 * This makes multiple requests, following each redirect until it reaches the final destination.
	 *
	 * @param string $url            URL.
	 * @param bool   $is_lasso_save  Is Lasso save data action. Default to false.
	 * @param bool   $get_page_title Get page title or not. Default to false.
	 */
	public static function get_redirect_final_target( $url, $is_lasso_save = false, $get_page_title = false ) {
		// ? Get final url for amazon shortlink from cache
		$amazon_shortlink_final_url_cached = Lasso_Amazon_Api::get_shortlink_final_url_cached( $url );
		if ( $amazon_shortlink_final_url_cached ) {
			return $get_page_title ? array( $amazon_shortlink_final_url_cached, get_option( Lasso_Amazon_Api::build_shortlink_cache_key( $url ) . '_page_title' ) ) : $amazon_shortlink_final_url_cached;
		}

		$origin_url  = $url;
		$url         = Lasso_Amazon_Api::get_amazon_product_url( $url, $is_lasso_save ? true : false );
		$url         = self::format_url_before_requesting( $url );
		$base_domain = self::get_base_domain( $url );
		$browser     = self::get_server_param( 'HTTP_USER_AGENT' );
		$browser     = '' !== $browser ? $browser : self::$user_agent;
		$use_bls     = apply_filters( 'get_final_url_domain_bls', false, $url );

		$cache_prefix = 'get_final_url_';
		$page_title   = '';

		// ? check result in cache first, it may be use before in the same request.
		$final_url_cache = Lasso_Cache_Per_Process::get_instance()->get_cache( $cache_prefix . md5( $url ) . $get_page_title );
		if ( $final_url_cache ) {
			return $final_url_cache;
		}

		$tmp_url = self::get_final_url_from_url_param( $url );
		if ( ! $use_bls && $tmp_url && ! in_array( $base_domain, Setting_Enum::DOMAIN_NOT_GET_URL_PARAMS, true ) ) {
			$page_title = self::get_title_by_url( $tmp_url );
			// ? cache result
			$result = $get_page_title ? array( $tmp_url, $page_title ) : $tmp_url;
			Lasso_Cache_Per_Process::get_instance()->set_cache( $cache_prefix . md5( $url ) . $get_page_title, $result );
			return $result;
		}

		if ( ( Lasso_Amazon_Api::is_amazon_url( $url ) && Lasso_Amazon_Api::get_product_id_by_url( $url ) )
			|| Extend_Product::get_extend_product_type_from_url( $url )
		) {
			return $get_page_title ? array( $url, $page_title ) : $url;
		}

		$lasso_db      = new Lasso_DB();
		$total_request = $lasso_db->get_total_404_request_to_bls( $url );
		if ( ! $is_lasso_save && $total_request >= self::$limit_404_request ) {
			// ? cache result
			$result = $get_page_title ? array( $url, $page_title ) : $url;
			Lasso_Cache_Per_Process::get_instance()->set_cache( $cache_prefix . md5( $url ) . $get_page_title, $result );
			return $result;
		}

		self::write_log( 'Send request to URL: ' . $url, 'send_request' );
		$res = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'user-agent' => $browser,
				),
			)
		);

		$is_amazon_shortened_url = Lasso_Amazon_Api::is_amazon_shortened_url( $url );

		$status_code = is_wp_error( $res ) ? 500 : $res['response']['code'] ?? '';
		if ( 200 === $status_code ) {
			$new_url         = $res['http_response']->get_response_object()->url;
			$use_bls         = apply_filters( 'get_final_url_domain_bls', false, $new_url );
			$new_base_domain = Lasso_Helper::get_base_domain( $new_url );
			if ( in_array( $new_base_domain, Verbiage::SUPPORT_SITES['get_final_url_domain_bls'], true ) ) {
				$status_code = 500;
			}
		}

		if ( is_wp_error( $res ) || ( $use_bls && 200 !== $status_code ) || ( 403 === $status_code ) ) {
			$headers          = self::get_lasso_headers();
			$data             = array(
				'url' => $url,
			);
			$encrypted_base64 = Encrypt::encrypt_aes( $data, true );
			$res              = self::send_request( 'get', LASSO_LINK . '/link/final-url/?' . $encrypted_base64, array(), $headers );

			$final_url  = $res['response']->finalUrl ?? $url;
			$page_title = $res['response']->pageTitle ?? '';

			// ? Set the response status code for add new link process
			Lasso_Cache_Per_Process::get_instance()->set_cache( Lasso_Affiliate_Link::ADD_NEW_LINK_RESPONSE_STATUS . md5( $origin_url ), $res['response']->status ?? 200 );

			$tmp_url = self::get_final_url_from_url_param( $final_url );
			if ( $tmp_url ) {
				$page_title = self::get_title_by_url( $tmp_url );
				$final_url  = $tmp_url;
			}

			// ? cache result
			$result = $get_page_title ? array( $final_url, $page_title ) : $final_url;
			Lasso_Cache_Per_Process::get_instance()->set_cache( $cache_prefix . md5( $url ) . $get_page_title, $result );

			// ? Cache the final url of amazon shortlink
			if ( $is_amazon_shortened_url ) {
				$shortlink_cache_key = Lasso_Amazon_Api::build_shortlink_cache_key( $url );
				update_option( $shortlink_cache_key, $final_url );
				// ? Cache the page title of amazon shortlink
				update_option( $shortlink_cache_key . '_page_title', $page_title );
			}

			return $result;
		}

		$http_response = $res['http_response']->get_response_object();
		$status        = wp_remote_retrieve_response_code( $res );

		// ? Set the response status code for add new link process
		Lasso_Cache_Per_Process::get_instance()->set_cache( Lasso_Affiliate_Link::ADD_NEW_LINK_RESPONSE_STATUS . md5( $origin_url ), $status );

		if ( 404 === $status ) {
			if ( $is_lasso_save ) {
				$lasso_db->set_total_404_request_to_bls( $url, 0 );
			} elseif ( $total_request < self::$limit_404_request ) {
				$total_request++;
				$lasso_db->set_total_404_request_to_bls( $url, $total_request );
			}
		}

		$final_url  = $http_response->url;
		$page_title = self::get_page_title( $http_response->body );
		if ( strpos( $page_title, 'Please Wait...' ) !== false
			|| strpos( $page_title, 'Cloudflare' ) !== false
			|| strpos( $page_title, 'Access Denied' ) !== false
			|| strpos( $page_title, 'Just a moment...' ) !== false
		) {
			$page_title = self::get_title_by_url( $final_url );
		}

		$tmp_url = self::get_final_url_from_url_param( $final_url );
		if ( $tmp_url ) {
			$page_title = self::get_title_by_url( $tmp_url );
			$final_url  = $tmp_url;
		}

		// ? Cache the final url of amazon shortlink
		if ( $is_amazon_shortened_url ) {
			$shortlink_cache_key = Lasso_Amazon_Api::build_shortlink_cache_key( $url );
			update_option( $shortlink_cache_key, $final_url );
			// ? Cache the page title of amazon shortlink
			update_option( $shortlink_cache_key . '_page_title', $page_title );
		}

		if ( ! $page_title || Lasso_Affiliate_Link::DEFAULT_TITLE === $page_title ) {
			$page_title = self::get_title_by_url( $final_url );
		}

		// ? auto monetize
		$html = trim( $http_response->body );
		self::get_final_url_shareasale( $final_url, $page_title, $html );

		// ? cache result
		$result = $get_page_title ? array( $final_url, $page_title ) : $final_url;
		Lasso_Cache_Per_Process::get_instance()->set_cache( $cache_prefix . md5( $url ) . $get_page_title, $result );

		

		return $result;
	}

	/**
	 * Get final URL of Share A Sale links
	 *
	 * @param string $final_url   Final URL.
	 * @param string $page_title  Page title.
	 * @param string $html_string HTML.
	 */
	public static function get_final_url_shareasale( &$final_url, &$page_title, $html_string ) {
		try {
			if ( strpos( $final_url, 'https://shareasale-analytics.com/r.cfm' ) === false || ! $html_string ) {
				return $final_url;
			}

			$html = new simple_html_dom();
			$html->load( $html_string, true, false );

			$scripts = $html->find( 'script' );
			$script  = trim( $scripts[0]->innertext ?? '' );
			$regex   = '~window.location.replace\(\'(.*?)\'\)~';
			preg_match( $regex, $script, $matches );
			$tmp_url = $matches[1] ?? '';
			$tmp_url = str_replace( '\\', '', $tmp_url );

			$final_url = $tmp_url ? $tmp_url : $final_url;
		} catch ( \Exception $e ) {
			$final_url = $final_url;
		}

		$page_title = self::get_title_by_url( $final_url );
	}

	/**
	 * Check whether the URL is home page or not
	 *
	 * @param  string $url URL.
	 * @return bool
	 */
	public static function is_home_page( $url ) {
		$url   = trim( $url, '/' );
		$parse = wp_parse_url( $url );
		$path  = trim( $parse['path'] ?? '', '/' );

		return $path ? false : true;
	}

	/**
	 * Get base domain
	 * Ex: "http://domain.com" would return "domain.com"
	 *
	 * @param string $domain Domain. It must be passed WITH protocol. Default to empty.
	 * @param bool   $include_subdomain Include subdomain or not. Default to true.
	 */
	public static function get_base_domain( $domain = '', $include_subdomain = true ) {
		$domain = self::add_https( $domain );
		if ( ! self::validate_url( $domain ) ) {
			return '';
		}

		$url  = @wp_parse_url( $domain ); // phpcs:ignore
		$host = $url['host'] ?? '';
		$host = str_replace( 'www.', '', $host );
		$host = trim( $host );

		if ( ! $include_subdomain ) {
			$host_parts = explode( '.', $host );
			if ( count( $host_parts ) > 2 ) {
				array_shift( $host_parts );
				$host = implode( '.', $host_parts );
			}
		}

		return $host ? $host : '';
	}

	/**
	 * Get domain with scheme
	 * Result is http://yourdomain or https://yourdomain
	 *
	 * @return string
	 */
	public static function get_domain_with_scheme() {
		$url  = @wp_parse_url( self::get_server_current_url() ); // phpcs:ignore
		return $url['scheme'] . '://' . $url['host'];
	}

	/**
	 * Count all posts/pages contain <a> tag
	 */
	public static function count_all_pages_posts() {
		$lasso_db = new Lasso_DB();

		$sql = '
            SELECT COUNT(`ID`) AS `count`
            FROM ' . Model::get_wp_table_name( 'posts' ) . "
            WHERE (`post_type` = 'post' OR `post_type` = 'page')
                AND `post_status` = 'publish'
                AND `post_content` LIKE '%<a %'
        ";

		$count = Model::get_results( $sql );
		$count = count( $count ) > 0 ? $count[0]->count : 0;

		return $count;
	}

	/**
	 * Get argument from url
	 *
	 * @param string $link     Amazon link.
	 * @param string $argument URL argument.
	 * @param bool   $is_lowercase_arguments Is lowercase arguments. Default to false.
	 * @param bool   $remove_wrong_format_for_ampersand Remove wrong format for ampersand. Default to false.
	 * @return string
	 */
	public static function get_argument_from_url( $link, $argument, $is_lowercase_arguments = false, $remove_wrong_format_for_ampersand = false ) {
		if ( ! $argument ) {
			return '';
		}

		$link    = str_replace( '&amp;', '&', $link );
		$parse   = wp_parse_url( $link );
		$queries = array();
		parse_str( $parse['query'] ?? '', $queries );

		if ( $is_lowercase_arguments && ! empty( $queries ) && is_array( $queries ) ) {
			$queries = array_change_key_case( $queries, CASE_LOWER );
		}

		$result = $queries[ $argument ] ?? '';

		if ( $remove_wrong_format_for_ampersand ) {
			// ? Fix issue the value include wrong format for & character. Ex: 16364u0026awinaffid%3D1119465u0026ue we will remove u0026 and the string after this character. Finally the result is 16364
			$result = $result ? explode( 'u0026', $result )[0] : '';
		}

		return $result;
	}

	/**
	 * Convert parse_url() to a url
	 *
	 * @param array $parse Parse data from a URL.
	 * @param bool  $host Is it host. Default to false.
	 */
	public static function get_url_from_parse( $parse, $host = false ) {
		$parse['host']     = $parse['host'] ?? '';
		$host              = false !== $host ? $host : $parse['host'];
		$parse['host']     = '://' . $host;
		$parse['query']    = ( $parse['query'] ?? '' ) ? '?' . $parse['query'] : '';
		$parse['fragment'] = ( $parse['fragment'] ?? '' ) ? '#' . $parse['fragment'] : '';

		return implode( '', $parse );
	}

	/**
	 * Convert query array to a string (in a url)
	 *
	 * @param array $query Query.
	 */
	public static function get_query_from_array( $query ) {
		$result = array();
		foreach ( $query as $key => $value ) {
			$result[] = $key . '=' . rawurlencode( $value );
		}
		$result = implode( '&', $result );
		return $result;
	}

	/**
	 * Add a param to URL
	 *
	 * @param string $url   URL.
	 * @param string $param Param.
	 * @param string $value Value.
	 */
	public static function add_param_to_url( $url, $param, $value ) {
		// ? parse url
		$parse = wp_parse_url( $url );
		parse_str( $parse['query'] ?? '', $query );

		$query[ $param ] = $value;
		$query           = self::get_query_from_array( $query );
		$parse['query']  = $query;

		return self::get_url_from_parse( $parse );
	}

	/**
	 * Add params to URL
	 *
	 * @param string $url    URL.
	 * @param array  $params Params.
	 */
	public static function add_params_to_url( $url, $params ) {
		// ? parse url
		$parse = wp_parse_url( $url );
		parse_str( $parse['query'] ?? '', $query );

		$query          = array_merge( $query, $params );
		$query          = self::get_query_from_array( $query );
		$parse['query'] = $query;

		return self::get_url_from_parse( $parse );
	}

	/**
	 * Write log
	 *
	 * @param string      $message     Message.
	 * @param string      $log_file    Log file. Default to test.
	 * @param string|bool $custom_date Custom date. Default to false.
	 * @param string|bool $force Force write log and skip enable_logs setting, this option helpful when we
	 * want write important logs then client can provide log files for dev to investigate issues. Default to false.
	 */
	public static function write_log( $message, $log_file = 'test', $custom_date = false, $force = false ) {
		if ( false === Lasso_Setting::lasso_get_setting( 'enable_logs' ) && ! $force ) {
			return false;
		}

		try {
			$current_date = ! $custom_date ? gmdate( 'Y_m_d' ) : $custom_date;
			$log_path     = '/logs/' . $current_date . '_' . $log_file . '.log';

			// ? Logging class initialization
			$log = new Lasso_Log();

			// ? set path and name of log file (optional)
			$log->lfile( LASSO_PLUGIN_PATH . $log_path );

			$type = gettype( $message );
			if ( in_array( $type, array( 'array', 'object' ), true ) ) {
				$message = print_r( $message, true ); // phpcs:ignore
			} elseif ( self::compare_string( 'boolean', $type ) ) {
				$message = var_export( $message, true ); // phpcs:ignore
			}

			// ? write message to the log file
			$log->lwrite( $message );

			// ? close log file
			$log->lclose();
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Write log. This function is used for debugging locally
	 *
	 * @param string $message  Message.
	 * @param string $caption  Caption.
	 */
	public static function write_log_local_debug( $message, $caption = null ) {
		if ( ! is_null( $caption ) ) {
			$pieces  = "\n============>> " . $caption . " <<============\n";
			$message = $pieces . $message . "\n";
		}
		self::write_log( $message, 'local_debug', false, true );
	}

	/**
	 * Build headers for Lasso API
	 *
	 * @param string $license_id License ID. Default to null.
	 * @return array
	 */
	public static function get_lasso_headers( $license_id = null ) {
		$headers = array(
			'Content-Type' => 'application/json',
			'license'      => $license_id ? $license_id : Lasso_License::get_license(),
			'site_id'      => Lasso_License::get_site_id(),
			'site_url'     => rawurlencode( site_url() ),
		);

		return $headers;
	}

	/**
	 * Send request
	 *
	 * @param string $method Method (get or post). Default to get.
	 * @param string $url    URL. Default to empty.
	 * @param array  $data   Post data. Default to empty array.
	 * @param array  $headers Headers. Default to empty array.
	 * @param bool   $is_lasso_save Is Lasso save data action. Default to false.
	 */
	public static function send_request( $method = 'get', $url = '', $data = array(), $headers = array(), $is_lasso_save = false ) {
		$method = strtolower( $method );
		$url    = Lasso_Amazon_Api::get_amazon_product_url( $url, false );

		self::write_log( 'Send request to URL: ' . $url, 'send_request' );
		$request_options = array(
			'headers'   => $headers,
			'timeout'   => Lasso_Setting::TIME_OUT,
			'sslverify' => Lasso_Setting::SSL_VERIFY,
		);
		$body            = wp_json_encode( $data );
		$headers_expect  = ! empty( $body ) && strlen( $body ) > 1048576 ? '100-Continue' : '';
		if ( 'get' === $method ) {
			$res = wp_remote_get( $url, $request_options );
		} elseif ( 'post' === $method ) {
			$request_options['headers']['expect'] = $headers_expect;
			$request_options['body']              = $body;
			$res                                  = wp_remote_post( $url, $request_options );
		} elseif ( 'put' === $method ) {
			$request_options['headers']['expect'] = $headers_expect;
			$request_options['body']              = $body;
			$request_options['method']            = 'PUT';
			$res                                  = wp_remote_request( $url, $request_options );
		}

		if ( is_wp_error( $res ) ) {
			self::write_log( $res->get_error_message(), 'request_error' );
			return array(
				'status_code' => 500,
				'response'    => array(),
				'message'     => $res->get_error_message(),
			);
		}

		$body   = wp_remote_retrieve_body( $res );
		$status = wp_remote_retrieve_response_code( $res );

		if ( 'get' === $method && strpos( $url, LASSO_LINK . '/link' ) === 0 ) {
			$res    = json_decode( $body );
			$status = $res->status ?? $status;
		}

		if ( is_null( json_decode( $body ) ) ) {
			$body = wp_json_encode( self::get_url_information( $url, $body ) );
		}

		return array(
			'status_code' => $status,
			'response'    => json_decode( $body ),
		);
	}

	/**
	 * Get automation stats
	 */
	public static function get_automation_stats() {
		$tbl_posts               = Model::get_wp_table_name( 'posts' );
		$tbl_postmeta            = Model::get_wp_table_name( 'postmeta' );
		$wp_lasso_link_locations = Model::get_wp_table_name( LASSO_LINK_LOCATION_DB );

		$sql = "
            SELECT $tbl_posts.`post_type`, COUNT($tbl_posts.`post_type`) AS `total`
            FROM $wp_lasso_link_locations 
            LEFT JOIN $tbl_posts
            ON $wp_lasso_link_locations.`detection_id` = $tbl_posts.`ID`
            GROUP BY $tbl_posts.`post_type`
        ";

		$total = Model::get_results( $sql );

		$sql = "
            SELECT `detection_date`
            FROM $wp_lasso_link_locations
            ORDER BY `detection_date` DESC
            LIMIT 1
        ";

		$latest_date = Model::get_results( $sql );
		$latest_date = $latest_date[0]->detection_date ?? '';
		$latest_date = '' === $latest_date ? '' : self::convert_datetime_format( $latest_date );

		$sql             = "
            SELECT `meta_value`
            FROM $tbl_postmeta
            WHERE `meta_key` = 'lasso_checked'
            ORDER BY `meta_value` DESC
            LIMIT 1
        ";
		$amz_latest_date = Model::get_results( $sql );

		if ( count( $amz_latest_date ) > 0 ) {
			$amz_latest_date = $amz_latest_date[0]->meta_value;
			$amz_latest_date = self::convert_datetime_format( $amz_latest_date );

			$latest_date = ( strtotime( $latest_date ) > strtotime( $amz_latest_date ) ) ? $latest_date : $amz_latest_date;
		}

		return array(
			'total'       => $total,
			'latest_date' => $latest_date,
		);
	}

	/**
	 * Convert date time to WordPress format
	 *
	 * @param string $datetime Date time. Format must be 'Y-m-d H:i:s' (example: 2018-09-14 10:34:54).
	 * @param bool   $time     Is it time. Default to true.
	 */
	public static function convert_datetime_format( $datetime, $time = true ) {
		if ( ! $datetime ) {
			return $datetime;
		}

		$date_format     = get_option( 'date_format' );
		$time_format     = 'g:i a T';
		$datetime_format = $date_format . ' ' . $time_format;
		$format          = ( $time ) ? $datetime_format : $date_format;

		try {
			$result = date_create_from_format( 'Y-m-d H:i:s', $datetime );
			$result = $result->format( $format );
		} catch ( \Throwable $th ) {
			$result = $datetime;
		}

		return $result;
	}

	/**
	 * Get name of a domain
	 * Ex: https://google.com/abc > google
	 *
	 * @param string $url URL.
	 */
	public static function get_name_of_domain( $url ) {
		$result = wp_parse_url( $url, PHP_URL_HOST );
		$result = str_replace( 'www.', '', $result );
		$result = explode( '.', $result )[0];

		return $result;
	}

	/**
	 * Input a domain and find matches to potential Lasso Links
	 *
	 * @param string $domain Domain.
	 */
	public static function get_suggested_affiliate( $domain ) {
		$sql     = '
            SELECT *
            FROM ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . ' as d
                LEFT JOIN ' . Model::get_wp_table_name( 'posts' ) . ' as p
				ON d.lasso_id = p.ID
            WHERE base_domain = %s
            LIMIT 1
        ';
		$prepare = Model::prepare( $sql, $domain ); // phpcs:ignore
		$post    = Model::get_row( $prepare );

		return $post;
	}

	/**
	 * Get the rewrite slugs of the plugins in the revert table
	 *
	 * @param bool $get_preg Get preg or not. Default to false.
	 */
	public static function get_rewrite_slug( $get_preg = false ) {
		$lasso_revert_db = Model::get_wp_table_name( LASSO_REVERT_DB );
		$result          = array();
		$preg            = '';

		$sql = "
            SELECT $lasso_revert_db.`plugin`, $lasso_revert_db.`old_uri`
            FROM (
                SELECT `plugin`
                FROM $lasso_revert_db
                GROUP BY `plugin`
            ) AS `tbl_plugin`
				LEFT JOIN $lasso_revert_db
				ON tbl_plugin.`plugin` = $lasso_revert_db.`plugin`
            WHERE (`old_uri` like 'http%' or `old_uri` like '%www.%' or `old_uri` like '%/%') and `old_uri`= (
                SELECT `old_uri`
                FROM $lasso_revert_db
                WHERE `plugin` = tbl_plugin.`plugin`
                LIMIT 1
            )
        ";

		$plugins = Model::get_results( $sql );

		// ? check slug of each plugin
		if ( count( $plugins ) > 0 ) {
			foreach ( $plugins as $plugin ) {
				$link = $plugin->old_uri;
				$link = trim( $link, '/' );
				$slug = self::get_rewrite_slug_in_url( $link );

				if ( $slug ) {
					$result[] = "/$slug/";
					$or_char  = '' === $preg ? '' : '|';
					$preg    .= $or_char . "\/$slug\/";
				}
			}
		}

		$preg = "/$preg/i";

		return $get_preg ? $preg : $result;
	}

	/**
	 * Get rewrite slug in url
	 *
	 * @param string $url URL.
	 */
	private static function get_rewrite_slug_in_url( $url ) {
		$parse = wp_parse_url( $url );

		if ( isset( $parse['path'] ) ) {
			$path = $parse['path'];
			$path = trim( $path, '/' );

			$explode = explode( '/', $path );

			if ( count( $explode ) > 1 ) {
				return $explode[0];
			}
		}

		return false;
	}

	/**
	 * Check if Classic Editor plugin is active.
	 *
	 * @return bool
	 */
	public static function is_classic_editor_plugin_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return self::get_is_plugin_active( 'classic-editor/classic-editor.php' );
	}

	/**
	 * Check if Disable Gutenberg plugin is active.
	 *
	 * @return bool
	 */
	public static function is_disable_gutenberg_plugin_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return self::get_is_plugin_active( 'disable-gutenberg/disable-gutenberg.php' );
	}

	/**
	 * Check if AdSense Integration WP QUADS plugin is active.
	 *
	 * @return bool
	 */
	public static function is_adsense_integration_wp_quads_plugin_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return self::get_is_plugin_active( 'quick-adsense-reloaded/quick-adsense-reloaded.php' );
	}

	/**
	 * Check if Beaver Builder Plugin plugin is active.
	 *
	 * @return bool
	 */
	public static function is_beaver_builder_plugin_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return self::get_is_plugin_active( 'bb-plugin/fl-builder.php' );
	}

	/**
	 * Check if WP Recipe Maker plugin is active.
	 *
	 * @return bool
	 */
	public static function is_wp_recipe_maker_plugin_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return self::get_is_plugin_active( 'wp-recipe-maker/wp-recipe-maker.php' );
	}

	/**
	 * Check if WP RSS Aggregator - Feed to Post plugin is active.
	 *
	 * @return bool
	 */
	public static function is_wp_rss_feed_to_post_plugin_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return self::get_is_plugin_active( 'wp-rss-feed-to-post/wp-rss-feed-to-post.php' );
	}

	/**
	 * Check whether a slug is a Lasso post
	 *
	 * @param string $post_name Post name.
	 */
	public static function is_lasso_post_by_post_name( $post_name ) {
		$lasso_db = new Lasso_DB();

		// ? Remove sub-directory from $post_name
		$post_name = self::remove_subdirectory_from_post_name( $post_name );

		// ? priority tag or category first
		$post_name = trim( $post_name, '/' );
		$explode   = explode( '/', $post_name );
		if ( 2 === count( $explode ) ) {
			if ( 'tag' === $explode[0] && ! empty( term_exists( $explode[1], 'post_tag' ) ) ) {
				return false;
			}

			if ( 'category' === $explode[0] && ! empty( term_exists( $explode[1], 'category' ) ) ) {
				return false;
			}

			$rewrite_slug = Lasso_Setting::lasso_get_setting( 'rewrite_slug' );
			if ( ( $rewrite_slug && $rewrite_slug !== $explode[0] ) || ( ! $rewrite_slug ) ) {
				return false;
			}
		}

		$row = $lasso_db->get_lasso_by_uri( $post_name );

		return ! is_null( $row );
	}

	/**
	 * Check whether slug exists or not
	 *
	 * @param string $post_name Post name.
	 * @param int    $post_id   Post id. Default to 0.
	 */
	public static function the_slug_exists( $post_name, $post_id = 0 ) {
		$posts_tbl = Model::get_wp_table_name( 'posts' );
		$sql       = "
			SELECT ID, post_name, post_type 
			FROM $posts_tbl 
			WHERE post_name = %s AND ID != %d AND post_status <> 'lasso_delete'
			LIMIT 1
		";
		$prepare   = Model::prepare( $sql, $post_name, $post_id ); // phpcs:ignore
		$row       = Model::get_row( $prepare, 'ARRAY_A' );

		if ( $row ) {
			self::write_log( '===== ===== ===== ===== =====', 'duplicated_slug' );
			self::write_log( 'Lasso id: ' . $post_id, 'duplicated_slug' );
			self::write_log( 'Slug: ' . $post_name, 'duplicated_slug' );
			self::write_log( 'Post id: ' . $row['ID'], 'duplicated_slug' );
			self::write_log( 'Post type: ' . $row['post_type'], 'duplicated_slug' );

			// ? ignore post type: attachment
			$row = 'attachment' === $row['post_type'] ? false : $row;
		}

		return $row ? $row : false;
	}

	/**
	 * Check whether content is populated or not
	 *
	 * @param int $post_id Post id.
	 */
	public static function is_content_populated( $post_id ) {
		if ( isset( $post_id ) ) {
			$query = 'SELECT count(*) FROM ' . Model::get_wp_table_name( LASSO_CONTENT_DB ) . ' where id = ' . $post_id;
			$count = Model::get_var( $query );

			return $count > 0 ? true : false;
		} else {
			return false;
		}
	}

	/**
	 * Insert lass content data into DB
	 *
	 * @param array $data    Data.
	 * @param int   $post_id Post id. Default to 0.
	 */
	public static function insert_lasso_content( $data, $post_id = 0 ) {
		global $wpdb;

		$lasso_content_db = Model::get_wp_table_name( LASSO_CONTENT_DB );

		if ( self::is_content_populated( $post_id ) ) {
			return $wpdb->update( $lasso_content_db, $data, array( 'id' => $post_id ) ); // phpcs:ignore
		} else {
			return $wpdb->insert( $lasso_content_db, $data ); // phpcs:ignore
		}
	}

	/**
	 * Include variables
	 *
	 * @param string $file_path File path.
	 * @param array  $variables List of variables.
	 * @param bool   $output_ajax_html Output a ajax html string or not.
	 */
	public static function include_with_variables( $file_path, $variables = array(), $output_ajax_html = true ) {
		$output = null;
		if ( file_exists( $file_path ) ) {
			extract( $variables ); // phpcs:ignore
			if ( $output_ajax_html ) {
				ob_start();
				include $file_path;
				$output = ob_get_clean();
				return $output;
			} else {
				require $file_path;
			}
		}

		return $output;
	}

	/**
	 * Get countries of Amazon
	 *
	 * @param array  $countries        Countries list.
	 * @param string $selected_country Country code.
	 */
	public static function get_countries_dd( $countries, $selected_country ) {
		$countries = Lasso_Amazon_Api::get_amazon_api_countries();

		$countries_dd = '<select id="amazon_default_tracking_country" name="amazon_default_tracking_country" class="form-control">';
		foreach ( $countries as $key => $country ) {
			if ( strlen( $key ) !== 2 ) {
				continue;
			}

			$selected = '';
			if ( $selected_country === $key ) {
				$selected = 'selected';
			}
			$countries_dd .= '<option value="' . $key . '" ' . $selected . ' >' . $country['name'] . '</option>';
		}
		$countries_dd .= '</select>';

		return $countries_dd;
	}

	/**
	 * Get tracking id field
	 *
	 * @param array  $amazon_tracking_ids         Amazon tracking ids list.
	 * @param string $default_amazon_tracking_id Default Amazon tracking id.
	 */
	public static function get_tracking_id_fields( $amazon_tracking_ids, $default_amazon_tracking_id ) {
		$aff_link_flags        = Lasso_Amazon_Api::get_aff_link_and_flag();
		$amazon_affiliate_html = '';

		foreach ( $amazon_tracking_ids as $amazon_tracking_info ) {
			if ( 'United States' === $amazon_tracking_info['country_name'] ) {
				$tracking_id = ! empty( $amazon_tracking_info['tracking_id'] )
					? $amazon_tracking_info['tracking_id']
					: $default_amazon_tracking_id;
			} else {
				$tracking_id = $amazon_tracking_info['tracking_id'];
			}

			$amazon_affiliate_html .= '
                <div class="form-group mb-4">
                    <label>' . $aff_link_flags[ $amazon_tracking_info['amazon_domain'] ]['flag'] . '
                        <a href="' . $aff_link_flags[ $amazon_tracking_info['amazon_domain'] ]['aff_link'] . '" target="_blank" class="underline">'
						. $amazon_tracking_info['country_name'] .
						'</a> Tracking ID
                    </label>
                    <input type="text" name="amazon_tracking_' . $amazon_tracking_info['id'] . '" data-id="' . $amazon_tracking_info['id'] . '" id="" class="form-control" value="' . $tracking_id . '">
                </div>';
		}

		return $amazon_affiliate_html;
	}

	/**
	 * Get paragraph contains link or keyword
	 *
	 * @param int  $post_id          Post id.
	 * @param int  $link_location_id Id in link location table.
	 * @param bool $keyword          Is it keyword. Default to false.
	 */
	public static function get_paragraph_of_link( $post_id, $link_location_id = '', $keyword = false ) {
		$post = get_post( $post_id );
		if ( ! empty( $post ) ) {
			$post_content = $post->post_content ?? '';
			// ? Get post content support Thrive plugin if existing
			$post_content = self::get_thrive_plugin_post_content( $post_id, $post_content );

			// ? keep break line for "php simple html dom" library
			// ? https://stackoverflow.com/questions/4812691/preserve-line-breaks-simple-html-dom-parser
			$html = new simple_html_dom();
			$html->load( $post_content, true, false );

			if ( ! $keyword ) {
				$a_tags = $html->find( 'a' ); // ? Find a tags in the html
				foreach ( $a_tags as $key => $a ) {
					$matches    = array();
					$a_lasso_id = $a->getAttribute( 'data-lasso-id' );
					if ( (int) $a_lasso_id !== (int) $link_location_id && ! $keyword ) {
						continue;
					}

					// ? Handle a tag Thrive Architect editor plugin
					if ( self::is_existing_thrive_plugin() ) {
						$a_tag = preg_replace( '/<div.*<\/div>/imUs', '', $a->outertext() );
						preg_match_all( '/<a.*?data-lasso-id="' . $link_location_id . '".*?<\/a>/imUs', $a_tag, $matches );
					} else {
						preg_match_all( '/^.*?data-lasso-id="' . $link_location_id . '".*?$/imU', $post_content, $matches );
					}
					return $matches[0][0] ?? '';
				}
			} else {
				$a_tags = $html->find( 'keyword' ); // ? Find a tags in the html
				foreach ( $a_tags as $a ) {
					$matches    = array();
					$a_lasso_id = $a->getAttribute( 'data-keyword-id' );
					if ( (int) $a_lasso_id !== (int) $link_location_id ) {
						continue;
					}

					// ? Handle a tag Thrive Architect editor plugin
					if ( self::is_existing_thrive_plugin() ) {
						$a_tag = preg_replace( '/<div.*<\/div>/imUs', '', $a->outertext() );
						preg_match_all( '/<keyword.*?data-keyword-id="' . $link_location_id . '".*?<\/keyword>/imUs', $a_tag, $matches );
					} else {
						preg_match_all( '/^.*?data-keyword-id="' . $link_location_id . '".*?$/imU', $post_content, $matches );
					}

					return $matches[0][0] ?? '';
				}
			}
		}
	}

	/**
	 * Update link in post
	 *
	 * @param int    $post_id          Post id.
	 * @param int    $link_location_id Id in link location table.
	 * @param string $new_link         New link.
	 */
	public static function update_link_in_post( $post_id, $link_location_id, $new_link ) {
		$post_obj = get_post( $post_id );

		$lasso_cron   = new Lasso_Cron();
		$post_content = $post_obj->post_content ?? '';

		$html = new simple_html_dom();
		$html->load( $post_content, true, false );

		$a_tags = $html->find( 'a' ); // ? Find a tags in the html

		$is_changed = false;
		foreach ( $a_tags as $a ) {
			$a_lasso_id = $a->getAttribute( 'data-lasso-id' );

			if ( (int) $a_lasso_id === (int) $link_location_id ) {
				$a->href    = $new_link;
				$is_changed = true;
				break;
			}
		}

		// ? Update new content after has updated the link
		if ( $is_changed ) {
			$lasso_cron->update_post_content( $post_id, $html );
		}

		return true;
	}

	/**
	 * Get attributes of shortcode
	 *
	 * @param string $tags_data               Shortcode string.
	 * @param string $is_shortcode_addslashes Shortcode string in Gutenberg editor.
	 */
	public static function get_attributes( $tags_data, $is_shortcode_addslashes = false ) {
		// ? fix shortcode in gutenberg editor: [shortcode attr=\u0022attr-1\u0022]
		if ( strpos( $tags_data, '\\u0022' ) !== false ) {
			$tags_data = str_replace( '\\u0022', '"', $tags_data );
		}

		// ? fix shortcode contains content: [shortcode]content[/shortcode]
		preg_match_all(
			'/' . get_shortcode_regex() . '/',
			$tags_data,
			$matches,
			PREG_SET_ORDER
		);
		$content_between = $matches[0][5] ?? '';
		$shortcode_name  = $matches[0][2] ?? '';
		if ( ! empty( $shortcode_name ) ) {
			// ? remove content between content/anchor text
			$tags_data = str_replace( ']' . $content_between . '[', '][', $tags_data );
			// ? remove the end shortcode
			$tags_data = str_replace( '[/' . $shortcode_name . ']', '', $tags_data );
		}
		$tags_data = str_replace( array( '"]', '" ]' ), '" /]', $tags_data );

		$attributes = array();
		$parse      = shortcode_parse_atts( $tags_data );

		$temp_key = 'temp';
		foreach ( $parse as $key => $value ) {
			if ( is_integer( $key ) ) {
				continue;
			}
			$attributes[ $key ] = self::remove_special_character_in_attributes( $value, $is_shortcode_addslashes );
		}
		unset( $attributes['temp'] );

		return $attributes;
	}

	/**
	 * Remove special character in attributes
	 *
	 * @param string $text                    Text string.
	 * @param string $is_shortcode_addslashes Shortcode string in Gutenberg editor.
	 */
	public static function remove_special_character_in_attributes( $text, $is_shortcode_addslashes = false ) {
		$text = str_replace( 'u0026', '&', $text );
		$text = str_replace( array( '\\', 'u0022', '&quot;' ), '', $text );
		if ( $is_shortcode_addslashes ) {
			$text = str_replace( 'u003c', '\u003c', $text );
			$text = str_replace( 'u003e', '\u003e', $text );
		} else {
			$text = str_replace( '<', '&lt;', $text );
			$text = str_replace( '>', '&gt;', $text );
		}
		return htmlspecialchars_decode( trim( $text, ']' ), ENT_QUOTES );
	}

	/**
	 * Get server load linux data
	 */
	private static function get_server_load_linux_data() {
		if ( @is_readable( '/proc/stat' ) ) { // phpcs:ignore
			$stats = @file_get_contents( '/proc/stat' ); // phpcs:ignore

			if ( false !== $stats ) {
				// ? Remove double spaces to make it easier to extract values with explode()
				$stats = preg_replace( '/[[:blank:]]+/', ' ', $stats );

				// ? Separate lines
				$stats = str_replace( array( "\r\n", "\n\r", "\r" ), "\n", $stats );
				$stats = explode( "\n", $stats );

				// ? Separate values and find line for main CPU load
				foreach ( $stats as $stat_line ) {
					$stat_line_data = explode( ' ', trim( $stat_line ) );

					// ? Found
					if ( count( $stat_line_data ) >= 5 && 'cpu' === $stat_line_data[0] ) {
						return array(
							$stat_line_data[1],
							$stat_line_data[2],
							$stat_line_data[3],
							$stat_line_data[4],
						);
					}
				}
			}
		}

		return null;
	}

	/**
	 * Get CPU load of server/hosting
	 */
	public static function get_cpu_load() {
		$load = 0;

		if ( stristr( PHP_OS, 'win' ) ) {
			$cmd = 'wmic cpu get loadpercentage /all';
			@exec( $cmd, $output ); // phpcs:ignore

			if ( $output ) {
				foreach ( $output as $line ) {
					if ( $line && preg_match( '/^[0-9]+$/', $line ) ) {
						$load = $line;
						break;
					}
				}
			}
		} else {
			try {
				if ( @is_readable( '/proc/stat' ) ) { // phpcs:ignore
					$cached_cpu_load = Lasso_Cache_Per_Process::get_instance()->get_cache( 'cpu_load', null );
					if ( $cached_cpu_load ) {
						$stat_data1 = $cached_cpu_load;
					} else {
						$stat_data1 = self::get_server_load_linux_data();
					}

					// ? Collect 2 samples - each with 1 second period
					// ? See: https://de.wikipedia.org/wiki/Load#Der_Load_Average_auf_Unix-Systemen
					sleep( 1 );

					$stat_data2 = self::get_server_load_linux_data();
					Lasso_Cache_Per_Process::get_instance()->set_cache( 'cpu_load', $stat_data2 );

					if ( ( ! is_null( $stat_data1 ) ) && ( ! is_null( $stat_data2 ) ) ) {
						// ? Get difference
						$stat_data2[0] -= $stat_data1[0];
						$stat_data2[1] -= $stat_data1[1];
						$stat_data2[2] -= $stat_data1[2];
						$stat_data2[3] -= $stat_data1[3];

						// ? Sum up the 4 values for User, Nice, System and Idle and calculate
						// ? the percentage of idle time (which is part of the 4 values!)
						$cpu_time = $stat_data2[0] + $stat_data2[1] + $stat_data2[2] + $stat_data2[3];

						// ? Invert percentage to get CPU time, not idle time
						$load = 100 - ( $stat_data2[3] * 100 / max( $cpu_time, 1 ) );
					}
				}
			} catch ( \Exception $e ) {
				$load = 0; // Just run because we can't detect CPU load.
			}
		}

		return round( $load, 2 );
	}

	/**
	 * Get title by url
	 *
	 * @param string $url URL.
	 */
	public static function get_title_by_url( $url ) {
		$url   = self::add_https( $url );
		$parse = wp_parse_url( $url );
		$host  = $parse['host'] ?? '';
		$host  = str_replace( 'www.', '', $host );
		$host  = explode( '.', $host );
		$host  = $host[ count( $host ) - 2 ] ?? '';
		$host  = str_replace( '-', ' ', $host );
		$host  = ucwords( $host );

		return $host;
	}

	/**
	 * Get param of $_REQUEST
	 *
	 * @param string $name Name of param.
	 */
	public static function get_request_param( $name ) {
		return wp_unslash( $_REQUEST[ $name ] ?? '' ); // phpcs:ignore
	}

	/**
	 * Get param of $_SERVER
	 *
	 * @param string $name Name of param.
	 */
	public static function get_server_param( $name ) {
		return wp_unslash( $_SERVER[ $name ] ?? '' ); // phpcs:ignore
	}

	/**
	 * Get request URI
	 */
	public static function get_server_uri() {
		$request_uri = self::get_server_param( 'REQUEST_URI' );
		if ( empty( $request_uri ) ) {
			$request_uri = self::get_server_param( 'REDIRECT_URL' );
		}

		// ? REQUEST_URI will exist on most hosts, REDIRECT_URL is only on Apache/IIS
		$temp        = wp_parse_url( $request_uri );
		$request_uri = $temp['path'] ?? $request_uri;

		return $request_uri;
	}

	/**
	 * Get current URL
	 */
	public static function get_server_current_url() {
		$request_uri  = self::get_server_uri();
		$https        = 'on' === self::get_server_param( 'HTTPS' ) ? 'https' : 'http';
		$http_host    = self::get_server_param( 'HTTP_HOST' );
		$current_link = $https . '://' . $http_host . $request_uri;

		return $current_link;
	}

	/**
	 * Add https to the url
	 *
	 * @param string $url URL.
	 */
	public static function add_https( $url ) {
		$invalid_url = array(
			'https://%20https:/',
			'https://xhttps://',
			'http:/https://',
			'http://https://',
			'https://https://',
			'https://hhttps://',
			'https://]https://',
			'https://&quot;https://',
			'[gift_item link=&quot;https://',
			']https://',
			'https:///',
		);
		$url         = trim( $url );
		$url         = str_replace( $invalid_url, 'https://', $url );

		// ? fix mailto in <a> href
		if ( strpos( $url, 'mailto:' ) !== false || filter_var( $url, FILTER_VALIDATE_EMAIL ) ) {
			$email = explode( 'mailto:', $url )[1] ?? '';
			if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				$url = 'mailto:' . $email;
			}

			return $url;
		}

		if ( '' === $url || is_null( $url ) || strpos( $url, '[' ) === 0 ) {
			return $url;
		}

		if ( strpos( $url, 'http://' ) !== 0 && strpos( $url, 'https://' ) !== 0 && strpos( $url, '.' ) !== false && '#' !== $url ) {
			$url = ltrim( $url, '/' );
			$url = 'https://' . $url;
		}

		$parse = wp_parse_url( $url );
		$host  = $parse['host'] ?? '';
		if ( strpos( $host, '.html' ) !== false ) {
			return '/' . $host;
		}

		return $url;
	}

	/**
	 * Check whether aawp list id is imported to Lasso or not
	 *
	 * @param int $id Aawp list id.
	 */
	public static function check_aawp_list_is_imported( $id ) {
		$lasso_db = new Lasso_DB();

		$sql        = '
			SELECT lud.product_id 
			FROM ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . ' AS lud
				LEFT JOIN ' . Model::get_wp_table_name( 'posts' ) . ' AS p
				ON lud.lasso_id = p.ID
			WHERE lud.product_id != \'\' 
				AND p.ID is not null 
				AND lud.product_type = \'' . Lasso_Amazon_Api::PRODUCT_TYPE . '\'
		';
		$row        = Model::get_col( $sql );
		$amazon_ids = $row ? $row : array();

		$aawp_list       = $lasso_db->get_aawp_list( $id );
		$aawp_amazon_ids = $aawp_list->product_asins ?? '';
		$aawp_amazon_ids = '' !== $aawp_amazon_ids ? explode( ',', $aawp_amazon_ids ) : array();

		$same_elements = array_intersect( $aawp_amazon_ids, $amazon_ids );

		return $aawp_amazon_ids === $same_elements;
	}

	/**
	 * Dump content in pretty format
	 *
	 * @param any $content Content.
	 */
	public static function dump( $content ) {
		if ( class_exists( '\LassoVendor\Symfony\Component\VarDumper\VarDumper' ) ) {
			return \LassoVendor\Symfony\Component\VarDumper\VarDumper::dump( $content );
		}

		return var_dump( $content ); // phpcs:ignore
	}

	/**
	 * Gets the status of WP-Cron functionality on the site by performing a test spawn if necessary. Cached for one hour when all is well.
	 *
	 * @param bool $cache Whether to use the cached result from previous calls.
	 * @return true|WP_Error Boolean true if the cron spawner is working as expected, or a `WP_Error` object if not.
	 */
	public static function test_cron_spawn( $cache = true ) {
		global $wp_version;

		$cron_runner_plugins = array(
			'\HM\Cavalcade\Plugin\Job'    => 'Cavalcade',
			'\Automattic\WP\Cron_Control' => 'Cron Control',
		);

		foreach ( $cron_runner_plugins as $class => $plugin ) {
			if ( class_exists( $class ) ) {
				return new WP_Error(
					'crontrol_info',
					sprintf(
					/* translators: 1: The name of the plugin that controls the running of cron events. */
						__( 'WP-Cron spawning is being managed by the %s plugin.', 'wp-crontrol' ),
						$plugin
					)
				);
			}
		}

		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			return new WP_Error(
				'crontrol_info',
				sprintf(
				/* translators: 1: The name of the PHP constant that is set. */
					__( 'The %s constant is set to true. WP-Cron spawning is disabled.', 'wp-crontrol' ),
					'DISABLE_WP_CRON'
				)
			);
		}

		if ( defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON ) {
			return new WP_Error(
				'crontrol_info',
				sprintf(
				/* translators: 1: The name of the PHP constant that is set. */
					__( 'The %s constant is set to true.', 'wp-crontrol' ),
					'ALTERNATE_WP_CRON'
				)
			);
		}

		$cached_status = get_transient( 'crontrol-cron-test-ok' );

		if ( $cache && $cached_status ) {
			return true;
		}

		$sslverify     = version_compare( $wp_version, 4.0, '<' );
		$doing_wp_cron = sprintf( '%.22F', microtime( true ) );

		$cron_request = apply_filters(
			'cron_request',
			array(
				'url'  => add_query_arg( 'doing_wp_cron', $doing_wp_cron, site_url( 'wp-cron.php' ) ),
				'key'  => $doing_wp_cron,
				'args' => array(
					'timeout'   => 3,
					'blocking'  => true,
					'sslverify' => apply_filters( 'https_local_ssl_verify', $sslverify ),
				),
			)
		);

		$cron_request['args']['blocking'] = true;

		$result = wp_remote_post( $cron_request['url'], $cron_request['args'] );

		if ( is_wp_error( $result ) ) {
			return $result;
		} elseif ( wp_remote_retrieve_response_code( $result ) >= 300 ) {
			return new WP_Error(
				'unexpected_http_response_code',
				sprintf(
				/* translators: 1: The HTTP response code. */
					__( 'Unexpected HTTP response code: %s', 'wp-crontrol' ),
					intval( wp_remote_retrieve_response_code( $result ) )
				)
			);
		} else {
			set_transient( 'crontrol-cron-test-ok', 1, 3600 );
			return true;
		}
	}

	/**
	 * Check whether the cron has issues
	 *
	 * @param bool $cache Whether to use the cached result from previous calls.
	 */
	public static function is_cron_getting_issues( $cache = true ) {
		$status = self::test_cron_spawn();

		return is_wp_error( $status );
	}

	/**
	 * Convert stdclass object to array
	 *
	 * @param bool $obj StdClass object.
	 */
	public static function convert_stdclass_to_array( $obj ) {
		$array = json_decode( wp_json_encode( $obj ), true );

		return $array;
	}

	/**
	 * Format "post title" to escape html and apply limit length.
	 *
	 * @param string $title lasso urls title.
	 * @param int    $limit_length limit length of title.
	 */
	public static function format_post_title( $title, $limit_length = 200 ) {
		$title = esc_html( $title );

		if ( strlen( $title ) > $limit_length ) {
			$title = substr( $title, 0, $limit_length ) . '...';
		}

		return $title;
	}

	/**
	 * Get shortcode key error in attrs
	 *
	 * @param [] $attrs Whether to use as input of shortcode attributes.
	 * @return array
	 */
	public static function get_shortcode_key_invalid( $attrs ) {
		$shortcode_key   = null;
		$shortcode_value = null;
		if ( ! is_array( $attrs ) ) {
			$shortcode_key = null;
		} else {
			foreach ( $attrs as $key => $value ) {
				if ( is_integer( $key ) ) {
					$shortcode_key   = $key;
					$shortcode_value = $value;
					break;
				}
			}
		}

		return array( $shortcode_key, $shortcode_value );
	}

	/**
	 * Check if shortcode attrs is invalid
	 *
	 * @param [] $attrs Whether to use as input of shortcode attributes.
	 * @return bool
	 */
	public static function is_shortcode_attrs_invalid( $attrs ) {
		list( $shortcode_key_error ) = self::get_shortcode_key_invalid( $attrs );

		return false !== $shortcode_key_error ? true : false;
	}

	/**
	 * Repair the attrs if attrs is invalid.
	 *
	 * @param [] $attrs Whether to use as input of shortcode attributes.
	 * @return mixed
	 */
	public static function repair_shortcode_attr( $attrs ) {
		list( $shortcode_key_error, $shortcode_value ) = self::get_shortcode_key_invalid( $attrs );
		if ( ! empty( $shortcode_value ) ) {
			$attr_keys = array_keys( $attrs );
			$error_key = null;
			foreach ( $attr_keys as $index => $value ) {
				if ( $value === $shortcode_key_error ) {
					$error_key = $index;
					break;
				}
			}
			if ( isset( $error_key ) ) {
				$shortcode_value = trim( $shortcode_value );
				if ( false !== strpos( $shortcode_value, '"' ) ) {
					$shortcode_value_explode = explode( '=', $shortcode_value );
					if ( count( $shortcode_value_explode ) > 1 ) {
						$shortcode_value_key           = $shortcode_value_explode[0];
						$shortcode_value               = $shortcode_value_explode[ count( $shortcode_value_explode ) - 1 ];
						$shortcode_value               = trim( str_replace( '"', '', $shortcode_value ) );
						$attrs[ $shortcode_value_key ] = $shortcode_value;
					}

					unset( $attrs[ $shortcode_key_error ] );
				}
			}
		}

		return $attrs;
	}

	/**
	 * Format importable data before showing/importing/reverting
	 *
	 * @param object $p Importable post.
	 */
	public static function format_importable_data( $p ) {
		$lasso_db         = new Lasso_DB();
		$lasso_helper     = new Lasso_Helper();
		$lasso_amazon_api = new Lasso_Amazon_Api();

		$home_url            = home_url();
		$p->import_permalink = get_permalink( $p->id );

		if ( 'Pretty Links' === $p->import_source ) {
			$pretty_link_data = $lasso_db->get_pretty_link_by_id( $p->id );
			$defaul_permalink = $home_url . '/' . ( $pretty_link_data->slug ?? '' ) . '/';

			// @codingStandardsIgnoreStart
			// $prlipro             = get_option( 'prlipro_options', array() );
			// $prlipro             = is_array( $prlipro ) ? $prlipro : array();
			// $base_slug_prefix    = $prlipro['base_slug_prefix'] ?? '';
			// $p->import_permalink = '' !== $base_slug_prefix && strpos( $p->post_name, $base_slug_prefix ) === false && intval( $p->id ) !== intval( $p->post_name )
			// 	? $home_url . '/' . $base_slug_prefix . '/' . $p->post_name . '/'
			// 	: $defaul_permalink;
			// @codingStandardsIgnoreEnd

			$p->import_permalink = $defaul_permalink;
		} elseif ( 'AAWP' === $p->import_source ) {
			$aawp_row            = $lasso_db->get_aawp_product( $p->id );
			$p->import_permalink = $aawp_row->url ?? '';
			$shortcode           = '[amazon link="' . $p->post_name . '"]';
			$p->shortcode        = $shortcode;

			// ? AAWP list
			if ( 'aawp_list' === $p->post_type ) {
				$p->import_permalink = 'https://amazon.com/s?k=' . $p->post_title;
				$p->check_status     = self::check_aawp_list_is_imported( $p->id ) ? 'checked' : '';

				$cat             = term_exists( $p->post_title, LASSO_CATEGORY );
				$p->check_status = $cat ? 'checked' : $p->check_status;

				$aawp_list = $lasso_db->get_aawp_list( $p->id );
				if ( $aawp_list ) {
					$items_count  = $aawp_list->items_count ?? 0;
					$attr_type    = 'bestseller' === $aawp_list->type ? 'bestseller' : 'link';
					$attr_type    = 'new_releases' === $aawp_list->type ? 'new' : $attr_type;
					$shortcode    = '[amazon ' . $attr_type . '="' . $aawp_list->keywords . '" items="' . $items_count . '"]';
					$p->shortcode = $shortcode;
				}
			} elseif ( 'aawp_table' === $p->post_type ) {
				$p->check_status = Import::is_post_imported_into_lasso( $p->id, $p->post_type ) ? 'checked' : '';
				$p->shortcode    = '[amazon table="' . $p->id . '"]';
			}
		} elseif ( 'EasyAzon' === $p->import_source ) {
			$product             = Lasso_DB::get_easyazon_option( $p->post_title );
			$p->post_title       = $product['title'];
			$p->id               = $product['identifier'];
			$p->import_permalink = $product['url'];
			$p->post_name        = strtolower( $product['identifier'] );
			$shortcode           = '[easyazon_link identifier="' . $p->id . '"]' . $p->post_title . '[/easyazon_link]';
			$p->shortcode        = $shortcode;

			$revert = $lasso_db->is_easyazon_product_imported( $product['identifier'] );
			if ( $revert ) {
				$p->id = $revert->lasso_id;
			}
		} elseif ( 'AmaLinks Pro' === $p->import_source ) {
			$shortcode           = $p->post_name;
			$attributes          = $lasso_helper->get_attributes( $shortcode );
			$p->shortcode        = $shortcode;
			$p->import_permalink = $attributes['apilink'] ?? '';
			if ( empty( $p->id ) ) {
				$p->id = $attributes['asin'] ?? $p->id;

				$url_details     = $lasso_db->get_url_details_by_product_id( $p->id, Lasso_Amazon_Api::PRODUCT_TYPE, false, $p->import_permalink );
				$lasso_id        = $url_details->lasso_id ?? 0;
				$p->check_status = $lasso_id > 0 ? 'checked' : '';

			}
			if ( empty( $p->import_permalink ) ) {
				$p->import_permalink = $lasso_amazon_api->get_amazon_link_by_product_id( $p->id, $p->import_permalink );
			}
		} elseif ( Link_Location::DISPLAY_TYPE_SITE_STRIPE === $p->import_source ) {
			$p->import_permalink = Lasso_Amazon_Api::get_amazon_product_url( $p->post_name, false, false );

			$format     = Lasso_Helper::get_argument_from_url( $p->post_name, 'format', true );
			$id         = Lasso_Helper::get_argument_from_url( $p->post_name, 'id', true );
			$amazon_url = Lasso_Amazon_Api::get_site_stripe_url( $p->post_name );
			$amazon_id  = Lasso_Amazon_Api::get_product_id_by_url( $amazon_url );

			if ( 0 === strpos( $format, '_SL' ) && 'AsinImage' === $id ) {
				$p->post_title = $amazon_id . ' Image';
			} else {
				$p->post_title = $amazon_id . ' Product Box';
			}
		}

		// ? Set location count.
		if ( 'checked' === $p->check_status && 'aawp_list' !== $p->post_type && is_numeric( $p->id ) ) {
			$p->locations = Model_Link_Locations::total_locations_by_lasso_id( $p->id );
		} else {
			$p->locations = 0;
		}

		return $p;
	}

	/**
	 * Check if mysql error relative to Lasso's table does not exist
	 *
	 * @param string $mysql_error mysql error.
	 * @return boolean
	 */
	public static function is_lasso_tables_does_not_exist_error( $mysql_error ) {
		return (bool) preg_match( "/(\.)(.)*lasso_(.)*doesn\'t(\s)exist/", $mysql_error );
	}

	/**
	 * Update post_name of the post
	 *
	 * @param int    $post_id   Post id.
	 * @param string $post_name Post name.
	 */
	public static function update_post_name( $post_id, $post_name ) {
		$sql    = '
			update ' . Model::get_wp_table_name( 'posts' ) . '
			set post_name = "' . $post_name . '"
			where ID = ' . $post_id . '
		';
		$result = Model::query( $sql );

		return $result;
	}

	/**
	 * Get unique post name of lasso post
	 *
	 * @param int    $post_id   Post id.
	 * @param string $post_name Post name.
	 */
	public static function lasso_unique_post_name( $post_id, $post_name ) {
		if ( intval( $post_id ) > 0 && ! empty( $post_name ) && self::the_slug_exists( $post_name, $post_id ) ) {
			$post_name = rtrim( $post_name, '-link' ); // ? Fix the issue adding multiple "-link" string to the end.
			$post_name = wp_unique_post_slug( $post_name, $post_id, 'publish', LASSO_POST_TYPE, 0 );
		}

		return $post_name;
	}

	/**
	 * Compare string
	 *
	 * @param string $str1 The first string.
	 * @param string $str2 The second string.
	 *
	 * @return bool
	 */
	public static function compare_string( $str1, $str2 ) {
		return strcmp( strtolower( $str1 ), strtolower( $str2 ) ) === 0;
	}

	/**
	 * Cast the value to boolean
	 *
	 * @param bool|string $value A string boolean like "true" or "false".
	 *
	 * @return bool
	 */
	public static function cast_to_boolean( $value ) {
		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Show decimal number star
	 *
	 * @param string $stars A number star.
	 *
	 * @return string
	 */
	public static function show_decimal_field_rate( $stars ) {
		return number_format( (float) $stars, 1, '.', '' );
	}

	/**
	 * Show convert ansi to utf8
	 *
	 * @param string $text A text ansi.
	 *
	 * @return string
	 */
	public static function convert_ansi_to_utf8( $text ) {
		$str          = str_replace( '\u', 'u', $text );
		$str_replaced = preg_replace( '/u([\da-fA-F]{4})/', '&#x\1;', $str );
		return html_entity_decode( $str_replaced );
	}

	/**
	 * Get url information from body html content.
	 *
	 * @param string $url  Request URL.
	 * @param string $body URL body html.
	 * @return array Information result.
	 */
	public static function get_url_information( $url, $body ) {
		self::write_log( 'Get url information: ' . $url, 'send_request' );
		$result = array(
			'pageTitle' => '',
		);

		// ? Get page title for all links
		if ( preg_match( '/<title(.*)>(.*)<\/title>/iU', $body, $t ) ) {
			$result['pageTitle'] = esc_html( trim( $t[2] ?? $result['pageTitle'] ) );
		}

		self::write_log( wp_json_encode( $result ), 'send_request' );

		return $result;
	}

	/**
	 * Trim space at the begin/end of string. Replace multiple spaces with one space.
	 *
	 * @param string $input String.
	 */
	public static function trim( $input ) {
		$output = preg_replace( '!\s+!', ' ', $input );
		$output = trim( $output );

		return $output;
	}

	/**
	 * Format Post content history row data
	 *
	 * @param object $p Post content data.
	 */
	public static function format_post_content_history_data( $p ) {
		// ? Edit post link
		$p->edit_url = get_edit_post_link( $p->post_id );

		// ? View post link
		$p->view_url = get_permalink( $p->post_id );

		// ? Format post type label
		$post_type    = get_post_type_object( $p->post_type );
		$p->post_type = $post_type->label;

		// ? Format updated_date
		$p->updated_date = date_i18n( 'd M, Y<\b\r>H:i:s', strtotime( $p->updated_date ) );

		return $p;
	}

	/**
	 * Remove specific parameter from url
	 *
	 * @param string $url                   Url.
	 * @param string $parameter             Url parameter.
	 * @param bool   $is_remove_all_parameter Remove all parameter from URL.
	 * @return string
	 */
	public static function remove_parameter_from_url( $url, $parameter = '', $is_remove_all_parameter = false ) {
		if ( ! self::validate_url( $url ) ) {
			return $url;
		}

		if ( $is_remove_all_parameter ) {
			return strtok( $url, '?' );
		}

		$parse = wp_parse_url( $url );
		parse_str( $parse['query'] ?? '', $query );
		unset( $query[ $parameter ] );
		$parse['query'] = self::get_query_from_array( $query );
		$result         = self::get_url_from_parse( $parse );
		$result         = trim( $result );
		$result         = trim( $result, '?' );

		return $result;
	}

	/**
	 * Get URL parameters
	 *
	 * @param string $url URL.
	 *
	 * @return array
	 */
	public static function get_url_params( $url ) {
		$parse          = wp_parse_url( $url );
		$parse['query'] = $parse['query'] ?? '';
		parse_str( $parse['query'], $params );

		return $params;
	}

	/**
	 * Generate random string
	 *
	 * @param int $length Random string's length.
	 * @return string
	 */
	public static function generate_random_string( $length = 10 ) {
		$characters        = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$characters_length = strlen( $characters );
		$random_string     = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$random_string .= $characters[ wp_rand( 0, $characters_length - 1 ) ];
		}
		return $random_string;
	}

	/**
	 * Check plugin AMP (Accelerated Mobile Pages) is loaded
	 *
	 * @return bool
	 */
	public static function is_amp_plugin_loaded() {
		global $wp;
		$params = $wp->query_vars ?? array();
		if ( class_exists( 'Ampforwp_Init' ) && isset( $params['amp'] ) && $params['amp'] ) {
			return true;
		} else {
			// ? Check exist AMP plugin https://wordpress.org/plugins/amp/
			$pieces  = explode( '/', $wp->request );
			$results = array_filter(
				$pieces,
				function ( $item ) {
					if ( self::compare_string( $item, 'amp' ) ) {
						return $item;
					}
					return false;
				}
			);
			if ( self::get_is_plugin_active( 'amp/amp.php' ) && (
				isset( $params['amp'] ) && $params['amp'] ) || ! empty( $results ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 *
	 * Check plugin Jetpack is loaded https://jetpack.com/.
	 *
	 * @return bool
	 */
	public static function is_jetpack_plugin_loaded() {
		return self::get_is_plugin_active( 'jetpack/jetpack.php' );
	}

	/**
	 *
	 * Check plugin Woody Code Snippets is loaded https://wordpress.org/plugins/insert-php/.
	 *
	 * @return bool
	 */
	public static function is_woody_code_plugin_loaded() {
		return self::get_is_plugin_active( 'insert-php/insert_php.php' );
	}

	/**
	 * Render css inline since some plugin does not include lasso css from file.
	 */
	public static function render_css_inline() {
		if ( ! Lasso_Cache_Per_Process::get_instance()->get_cache( 'lasso-css-inline' ) ) {
			$settings = Lasso_Setting::lasso_get_settings();
			// ? Lasso branding color
			// @codingStandardsIgnoreStart
			echo '
			:root{
				--lasso-main: ' . $settings['display_color_main'] . ';
				--lasso-title: ' . $settings['display_color_title'] . ';
				--lasso-button: ' . $settings['display_color_button'] . ';
				--lasso-secondary-button: ' . $settings['display_color_secondary_button'] . ';
				--lasso-button-text: ' . $settings['display_color_button_text'] . ';
				--lasso-background: ' . $settings['display_color_background'] . ';
				--lasso-pros: ' . $settings['display_color_pros'] . ';
				--lasso-cons: ' . $settings['display_color_cons'] . ';
			}
			' . $settings['custom_css_default'];

			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
			$wp_filesystem = new WP_Filesystem_Direct( null );
			$css_content   = '';
			$css_files     = array( 'lasso-amp.css' );
			foreach ( $css_files as $css_file ) {
				$css_content .= $wp_filesystem->get_contents( LASSO_PLUGIN_PATH . '/admin/assets/css/' . $css_file );
			}
			Lasso_Cache_Per_Process::get_instance()->set_cache( 'lasso-css-inline', 1 );
			$css_content = str_replace('url(\'..', 'url(\''.LASSO_PLUGIN_URL . 'admin/assets', $css_content );
			echo $css_content;
			// @codingStandardsIgnoreEnd
		}
	}

	/**
	 * Render Lasso GA js since some plugin does not include lasso js files.
	 */
	public static function render_lasso_ga_js() {
		if ( ! Lasso_Cache_Per_Process::get_instance()->get_cache( 'lasso_hook_js_head' ) ) {
			Lasso_Cache_Per_Process::get_instance()->set_cache( 'lasso_hook_js_head', 1 );

			$page_hook = new Page_Hook();
			$page_hook->lasso_event_tracking();
		}
	}

	/**
	 * Check if Lasso URL description is empty.
	 *
	 * @param string $description Lasso URL description.
	 * @return bool
	 */
	public static function is_description_empty( $description ) {
		if ( empty( $description ) || '<p><br></p>' === $description ) {
			return true;
		}

		return false;
	}

	/**
	 * Get price value from price text including currency symbol.
	 *
	 * @param string $price_text   Price text.
	 * @param string $price_symbol Price symbol.
	 * @return mixed|string
	 */
	public static function get_price_value_from_price_text( $price_text, $price_symbol = '' ) {
		if ( preg_match( '/[€]|R\$|TL|kr|zł/', $price_text ) || in_array( $price_symbol, array( '€', 'R$', 'TL', 'kr', 'zł' ), true ) ) {
			// ? For price use , as decimal separator and . as thousands separator.
			$replace_character = '.';
			$reg_pattern       = '/\d+,?\d*/';
		} else {
			// ? For price use . as decimal separator and , as thousands separator.
			$replace_character = ',';
			$reg_pattern       = '/\d+\.?\d*/';
		}

		$price_without_thousands_separator = str_replace( $replace_character, '', $price_text );
		preg_match( $reg_pattern, $price_without_thousands_separator, $matches );

		// ? Final format to general float number by replace ',' to '.'.
		$price = isset( $matches[0] ) ? str_replace( ',', '.', $matches[0] ) : '';
		$price = floatval( $price );

		return $price;
	}

	/**
	 * Get currency symbol from ISO currency code
	 *
	 * @param string $iso Iso currency code.
	 * @return string
	 */
	public static function get_currency_symbol_from_iso_code( $iso ) {
		$iso    = strtoupper( $iso );
		$result = '$';

		$currencies = array(
			'USD' => '$',
			'AUD' => '$',
			'CAD' => '$',
			'EUR' => '€',
			'MXN' => '$',
			'CNY' => '¥',
			'JPY' => '¥',
			'INR' => '₹',
			'SEK' => 'kr',
			'BRL' => 'R$',
			'TRY' => 'TL',
			'GBP' => '£',
			'PLN' => 'zł',
			'EGP' => 'E£',
			'SGD' => 'S$',
			'AED' => 'AED',
		);

		return isset( $currencies[ $iso ] ) ? $currencies[ $iso ] : $result;
	}

	/**
	 * Get plugins information.
	 *
	 * @return array Plugins.
	 */
	public static function get_plugins_information() {
		$result = array();

		try {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$plugins = get_plugins();

			foreach ( $plugins as $plugin_path => $plugin ) {
				$description = wp_strip_all_tags( $plugin['Description'] );
				$description = substr( $description, 0, 100 ) . '...'; // ? get first 100 characters
				$result[]    = array(
					'name'        => $plugin['Name'],
					'version'     => $plugin['Version'],
					'status'      => intval( self::get_is_plugin_active( $plugin_path ) ),
					'description' => $description,
					'url'         => $plugin['PluginURI'],
				);
			}
		} catch ( \Exception $exception ) {
			self::write_log( Lasso_Log::ERROR_LOG, "Get plugins information error: {$exception->getMessage()}", false, true );
		}

		return $result;
	}

	/**
	 * Get amazon product from DB by image url
	 *
	 * @param string $image_url Amazon image url.
	 */
	public static function get_easyazon_product_by_image_url( $image_url ) {
		preg_match( '~\/([a-zA-Z0-9\-\_]*)(\.[a-zA-Z0-9\-\_]+)?\.(jpg|png)~s', $image_url, $image_matches );
		$image_id = $image_matches[1] ?? '';

		if ( ! $image_url || ! $image_id ) {
			return false;
		}

		$sql = '
			SELECT * 
			FROM ' . Model::get_wp_table_name( 'options' ) . ' 
			WHERE option_name LIKE "easyazon_item_%" AND option_value LIKE "%' . $image_id . '%"
		';
		$row = Model::get_row( $sql );

		if ( $row ) {
			$row = maybe_unserialize( $row->option_value );
		}

		return $row;
	}

	/**
	 * Get all custom fields in ACF plugin
	 */
	public static function get_all_acf_fields() {
		$options = array();

		$fields = get_posts(
			array(
				'posts_per_page'         => -1,
				'post_type'              => 'acf-field',
				'orderby'                => 'menu_order',
				'order'                  => 'ASC',
				'suppress_filters'       => true, // ? DO NOT allow WPML to modify the query
				'post_status'            => 'any',
				'update_post_meta_cache' => false,
			)
		);
		foreach ( $fields as $field ) {
			$options[ $field->post_excerpt ] = $field->post_title;
		}

		return $options;
	}

	/**
	 * Get Lasso current page name
	 *
	 * @return bool|mixed
	 */
	public static function get_page_name() {
		$get = wp_unslash( $_GET ); // phpcs:ignore
		return $get['page'] ?? false;
	}

	/**
	 * Escape string for use as MYSQL identifier names
	 *
	 * @param string $identifier Identifier.
	 *
	 * @return string
	 */
	public static function escape_mysql_identifier( $identifier ) {
		$identifier = str_replace( '`', '``', $identifier );
		return "`$identifier`";
	}

	/**
	 * Check if Gravity Perks plugin is active.
	 *
	 * @return bool
	 */
	public static function is_gravity_perks_plugin_active() {
		return self::get_is_plugin_active( 'gravityperks/gravityperks.php' );
	}

	/**
	 * Check if Ezoic plugin is active.
	 *
	 * @return bool
	 */
	public static function is_ezoic_plugin_active() {
		return self::get_is_plugin_active( 'ezoic-integration/ezoic-integration.php' );
	}

	/**
	 * Check if "Pretty Link" plugin is active.
	 *
	 * @return bool
	 */
	public static function is_pretty_link_plugin_active() {
		return self::get_is_plugin_active( 'pretty-link/pretty-link.php' );
	}

	/**
	 * Check if WP Coupons plugin is active.
	 *
	 * @return bool
	 */
	public static function is_wp_coupons_plugin_actived() {
		return is_plugin_active( 'wp-coupons/wp-coupons.php' );
	}

	/**
	 * Check if WP Elementor plugin is active.
	 *
	 * @return bool
	 */
	public static function is_wp_elementor_plugin_actived() {
		return is_plugin_active( 'elementor/elementor.php' );
	}

	/**
	 * Check if Forminator plugin is active.
	 *
	 * @return bool
	 */
	public static function is_forminator_plugin_actived() {
		return is_plugin_active( 'forminator/forminator.php' );
	}

	/**
	 * Check if Lasso Lite/Simple URLs plugin is active.
	 *
	 * @return bool
	 */
	public static function is_lasso_lite_plugin_actived() {
		return is_plugin_active( 'simple-urls/plugin.php' );
	}

	/**
	 * Check if Sitemap Generator plugin is active.
	 *
	 * @return bool
	 */
	public static function is_sitemap_generator_plugin_actived() {
		return is_plugin_active( 'google-sitemap-generator/sitemap.php' );
	}

	/**
	 * Get plugin status result
	 *
	 * @param string $plugin Plugin key.
	 * @return bool
	 */
	public static function get_is_plugin_active( $plugin ) {
		$cache_result = Lasso_Cache_Per_Process::get_instance()->get_cache( 'is_plugin_active_' . md5( $plugin ), null );
		if ( null !== $cache_result ) {
			return $cache_result;
		}

		$result = is_plugin_active( $plugin );
		Lasso_Cache_Per_Process::get_instance()->set_cache( 'is_plugin_active_' . md5( $plugin ), $result );
		return $result;
	}

	/**
	 * Update whiltelist amazon tracking ids to DB
	 *
	 * @param array $tracking_id_collection New tracking ids. Default to empty array.
	 */
	public static function update_amazon_whitelist_ids( $tracking_id_collection = array() ) {
		if ( ! is_array( $tracking_id_collection ) || empty( $tracking_id_collection ) ) {
			return;
		}

		$lasso_tracking_whitelist = Lasso_Setting::lasso_get_setting( 'amazon_tracking_id_whitelist', array() );
		$lasso_tracking_whitelist = array_merge( $lasso_tracking_whitelist, $tracking_id_collection );
		sort( $lasso_tracking_whitelist );
		$lasso_tracking_whitelist = array_unique( $lasso_tracking_whitelist );
		Lasso_Setting::lasso_set_setting( 'amazon_tracking_id_whitelist', $lasso_tracking_whitelist );
		self::update_amazon_blacklist_ids( $tracking_id_collection );
	}

	/**
	 * Update blacklist amazon tracking ids to DB
	 *
	 * @param array $tracking_id_collection New tracking ids. Default to empty array.
	 */
	public static function update_amazon_blacklist_ids( $tracking_id_collection = array() ) {
		if ( ! is_array( $tracking_id_collection ) || empty( $tracking_id_collection ) ) {
			return;
		}

		$lasso_tracking_blacklist = self::get_option( 'amazon_tracking_id_blacklist', array() );
		$lasso_tracking_blacklist = array_merge( $lasso_tracking_blacklist, $tracking_id_collection );
		sort( $lasso_tracking_blacklist );
		$lasso_tracking_whitelist = array_unique( $lasso_tracking_blacklist );
		self::update_option( 'amazon_tracking_id_blacklist', $lasso_tracking_blacklist );
	}

	/**
	 * Build url parameter string
	 * Parameter example: array( 'rel_=abc', 'maas=def' );
	 *
	 * @param array $parameters Parameter key=value array.
	 * @return string|mixed
	 */
	public static function build_url_parameter_string( $parameters = array() ) {
		$result = implode( '&', $parameters );
		$result = preg_replace( '!\&+!', '&', $result );
		$result = trim( $result, '&' );

		return $result;
	}

	/**
	 *  Enqueue a Lasso script.
	 *
	 * @param string $handle    Name of the script. Should be unique.
	 * @param string $file_name Lasso script file name.
	 * @param array  $deps      Optional. An array of registered script handles this script depends on. Default empty array.
	 * @param bool   $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
	 */
	public static function enqueue_script( $handle, $file_name, $deps = array(), $in_footer = false ) {
		$file_path = LASSO_PLUGIN_PATH . '/admin/assets/js/' . $file_name;

		if ( file_exists( $file_path ) ) {
			$src = LASSO_PLUGIN_URL . 'admin/assets/js/' . $file_name;
			$ver = strval( @filemtime( $file_path ) ); // phpcs:ignore

			wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
		}
	}

	/**
	 *  Enqueue a Lasso CSS stylesheet.
	 *
	 * @param string $handle    Name of the stylesheet. Should be unique.
	 * @param string $file_name Lasso stylesheet file name.
	 * @param array  $deps      Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
	 * @param string $media     Optional. The media for which this stylesheet has been defined.
	 *                          Default 'all'. Accepts media types like 'all', 'print' and 'screen', or media queries like
	 *                          '(orientation: portrait)' and '(max-width: 640px)'.
	 */
	public static function enqueue_style( $handle, $file_name, $deps = array(), $media = 'all' ) {
		$file_path = LASSO_PLUGIN_PATH . '/admin/assets/css/' . $file_name;

		if ( file_exists( $file_path ) ) {
			$src = LASSO_PLUGIN_URL . 'admin/assets/css/' . $file_name;
			$ver = strval( @filemtime( $file_path ) ); // phpcs:ignore

			wp_enqueue_style( $handle, $src, $deps, $ver, $media );
		}
	}

	/**
	 * Paginate items by a sql query
	 * Reset page number if results is empty.
	 *
	 * @param string $sql   Sql query.
	 * @param int    $page  Number of page.
	 * @param int    $limit Number of results. Default to 10.
	 */
	public static function paginate( $sql, &$page, $limit = 10 ) {
		$start_index    = ( $page - 1 ) * $limit;
		$pagination_sql = $sql . ' LIMIT ' . $start_index . ', ' . $limit;

		if ( $page > 1 ) {
			$result = Model::get_row( $pagination_sql );

			if ( ! $result ) {
				$page           = 1;
				$pagination_sql = $sql . ' LIMIT 0, ' . $limit;
			}
		}

		return $pagination_sql;
	}

	/**
	 * Fix post content issue can't detection link on Thrive Architect plugin.
	 *
	 * @param int    $post_id      Post ID.
	 * @param string $post_content Post content.
	 *
	 * @return string
	 */
	public static function get_thrive_plugin_post_content( $post_id, $post_content ) {
		if ( self::is_existing_thrive_plugin() ) {
			$thrive_active   = get_post_meta( $post_id, 'tcb_editor_enabled', true );
			$thrive_template = get_post_meta( $post_id, 'tve_landing_page', true );
			$thrive_page     = get_post_meta( $post_id, 'page', true );

			if ( ! empty( $thrive_active ) ) {
				$thrive_content = get_post_meta( $post_id, 'tve_updated_post', true );

				if ( $thrive_content ) {
					$post_content = htmlspecialchars_decode( $thrive_content, ENT_NOQUOTES );
				}
			} elseif ( empty( trim( $post_content ) ) && $thrive_page ) {
				$post_content = $thrive_page;
			}

			if ( get_post_meta( $post_id, 'tve_landing_set', true ) && $thrive_template ) {
				$post_content = get_post_meta( $post_id, 'tve_updated_post_' . $thrive_template, true );
			}
		}

		return $post_content;
	}

	/**
	 * Update post content to table wp_postmeta support Thrive Architect plugin.
	 *
	 * @param int    $post_id      Post ID.
	 * @param string $post_content Post content.
	 *
	 * @return string
	 */
	public static function update_thrive_plugin_post_content( $post_id, $post_content ) {
		if ( self::is_existing_thrive_plugin() ) {
			$thrive_active   = get_post_meta( $post_id, 'tcb_editor_enabled', true );
			$thrive_template = get_post_meta( $post_id, 'tve_landing_page', true );
			if ( ! empty( $thrive_active ) ) {
				$thrive_content = get_post_meta( $post_id, 'tve_updated_post', true );
				if ( $thrive_content ) {
					return update_post_meta( $post_id, 'tve_updated_post', $post_content );
				}
			}

			if ( get_post_meta( $post_id, 'tve_landing_set', true ) && $thrive_template ) {
				return update_post_meta( $post_id, 'tve_updated_post' . $thrive_template, $post_content );
			}
		}

		return false;
	}

	/**
	 * Check Thrive Plugin existing.
	 *
	 * @return bool
	 */
	public static function is_existing_thrive_plugin() {
		$theme      = wp_get_theme();
		$theme_name = $theme->template;

		return defined( 'TVE_PLUGIN_FILE' ) || defined( 'TVE_EDITOR_URL' ) || ( 'thrive-theme' === $theme_name );
	}

	/**
	 * Get inner-text first child in a simple_html_dom.
	 *
	 * @param simple_html_dom_node $dom Object simple_html_dom_node.
	 *
	 * @return mixed
	 */
	public static function get_thrive_inner_text_simple_html_dom( $dom ) {
		$dom = empty( $dom->find( '.tcb-button-texts' ) )
			? $dom
			: $dom->find( '.tcb-button-texts' ) [0];

		if ( $dom->first_child() ) {
			return self::get_thrive_inner_text_simple_html_dom( $dom->first_child() );
		} else {
			$inner_text = html_entity_decode( $dom->innertext() );

			if ( empty( $inner_text ) ) {
				$inner_text = $dom->parentNode()->innertext();
			}

			return $inner_text;
		}
	}

	/**
	 * Check is Thrive shortcode pattern match
	 *
	 * @param string $shortcode Shortcode.
	 *
	 * @return boolean
	 */
	public static function is_thrive_shortcode_pattern_match( $shortcode ) {
		if ( ! self::is_existing_thrive_plugin() ) {
			return false;
		}

		$thrive_content_allowed_shortcodes = apply_filters( 'tcb_content_allowed_shortcodes', array() );
		if ( ! empty( $thrive_content_allowed_shortcodes ) ) {
			$pattern = get_shortcode_regex( $thrive_content_allowed_shortcodes );
			return boolval( preg_match( "/$pattern/", $shortcode ) );
		}

		return false;
	}

	/**
	 * Get WP Rocket settings
	 */
	public static function get_wp_rocket_settings() {
		return get_option( 'wp_rocket_settings', array() );
	}

	/**
	 * Check if WP Rocket - Lazyload is enabled.
	 *
	 * @return bool
	 */
	public static function is_wp_rocket_lazyload_image_enabled() {
		if ( self::is_wp_rocket_plugin_loaded() ) {
			// ? Check if layzyload enabled
			$wp_rocket_settings = self::get_wp_rocket_settings();
			if ( $wp_rocket_settings['lazyload'] ?? false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get url without scheme and arguments.
	 * Example: https://www.abc.com/test-1?first_name=Test => Result is: abc.com/test-1
	 *
	 * @param string $link Link.
	 *
	 * @return string
	 */
	public static function get_url_without_scheme_and_arguments( $link ) {
		$link      = self::add_https( $link );
		$result    = $link;
		$url_parse = wp_parse_url( $link );
		$path      = $url_parse['path'] ?? '';
		if ( ! empty( $path ) ) {
			$base_domain = self::get_base_domain( $link );
			$result      = $base_domain . '' . $path;
		}
		$result = trim( $result, '/' );

		return $result;
	}

	/**
	 * Check URL is static url
	 *
	 * @param string $url URL.
	 *
	 * @return bool
	 */
	public static function is_static_url( $url ) {
		$static_urls = array(
			'/favicon.ico',
			'/admin',
		);
		foreach ( $static_urls as $static_url ) {
			if ( strpos( $url, $static_url ) !== false ) {
				return true;
			}
		}
		return false;
	}

	/**
	 *
	 * Check plugin wp rocket is loaded https://wp-rocket.me/.
	 *
	 * @return bool
	 */
	public static function is_wp_rocket_plugin_loaded() {
		return self::get_is_plugin_active( 'wp-rocket/wp-rocket.php' );
	}

	/**
	 *
	 * Check plugin earnist is loaded https://www.getearnist.com.
	 *
	 * @return bool
	 */
	public static function is_earnist_plugin_loaded() {
		return self::get_is_plugin_active( 'earnist/earnist.php' );
	}

	/**
	 *
	 * Check plugin Shortcode Star Rating is loaded https://github.com/modshrink/shortcode-star-rating.
	 *
	 * @return bool
	 */
	public static function is_shortcode_start_rating_plugin_loaded() {
		return self::get_is_plugin_active( 'shortcode-star-rating/shortcode-star-rating.php' );
	}

	/**
	 * Check plugin "Easy Table of Contents" is activated
	 *
	 * @return bool
	 */
	public static function is_plugin_easy_table_of_contents_activated() {
		return self::get_is_plugin_active( 'easy-table-of-contents/easy-table-of-contents.php' );
	}

	/**
	 * Remove a action out of WordPress hook
	 *
	 * Example 1: Lasso_Helper::remove_action('admin_print_footer_scripts', 'register_tinymce_quicktags'); $callback is a function name.
	 * Example 2: Lasso_Helper::remove_action('admin_print_footer_scripts', array('EarnistProductPicker', 'register_tinymce_quicktags')); $callback is array.
	 *
	 * @param string       $hook_name Hook name.
	 * @param array|string $callback  Callback function. $callback[0] is a class name, $callback[1] is a function name.
	 * @param int          $priority  Priority.
	 */
	public static function remove_action( $hook_name, $callback, $priority = 10 ) {
		global $wp_filter;

		if ( ! isset( $wp_filter[ $hook_name ]->callbacks[ $priority ] ) ) {
			return;
		}

		foreach ( $wp_filter[ $hook_name ]->callbacks[ $priority ] as $key_function_name_wp => $data ) {
			$should_remove    = false;
			$obj_name_wp      = null;
			$function_name_wp = is_array( $data['function'] ) ? $data['function'][1] : $data['function'];
			if ( ! is_array( $callback ) ) {
				$function_name = $callback;
				if ( $function_name_wp === $function_name ) {
					$should_remove = true;
				}
			} else {
				list( $object_name, $function_name ) = $callback;
				if ( gettype( $object_name ) === 'object' ) {
					$object_name = get_class( $object_name );
				}

				if ( ! $data['function'] instanceof \Closure ) {
					$obj_name_wp = $data['function'][0];
					if ( gettype( $obj_name_wp ) === 'object' ) {
						$obj_name_wp = get_class( $obj_name_wp );
						if ( $obj_name_wp === $object_name && $function_name_wp === $function_name ) {
							$should_remove = true;
						}
					}
				}
			}

			if ( $should_remove ) {
				unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $key_function_name_wp ] );
				break;
			}
		}
	}

	/**
	 * Check whether url is shareasale domain or not
	 *
	 * @param string $url URL.
	 * @return bool
	 */
	public static function is_shareasale_url( $url ) {
		$allow_domains = array( 'shareasale.com', 'shareasale-analytics.com' );
		$domain        = self::get_base_domain( $url );

		return in_array( $domain, $allow_domains, true );
	}

	/**
	 * Check whether url is youtube domain or not
	 *
	 * @param string $url URL.
	 * @return bool
	 */
	public static function is_youtube_url( $url ) {
		$allow_domains = array( 'youtube.com', 'youtu.be' );
		$domain        = self::get_base_domain( $url );

		return in_array( $domain, $allow_domains, true );
	}

	/**
	 * Format domain for saving data in DB
	 *
	 * @param string $url          URL.
	 * @param string $original_url Original URL. Default to false.
	 */
	public static function format_url_for_checking_duplication( $url, $original_url = false ) {
		$db_url          = $url;
		$domain          = self::get_base_domain( $url );
		$original_domain = self::get_base_domain( $original_url );

		if ( in_array( $original_domain, array( 'sovrn.co', 'ebay.us', 'fashionphile.pxf.io' ), true ) ) {
			$parse  = wp_parse_url( $original_url );
			$path   = trim( $parse['path'] ?? '', '/' );
			$db_url = $original_domain . '_' . $path;
		} elseif ( preg_match( '/skillshare\.[a-z]+\.net/i', $original_domain ) ) {
			/**
			 * Skillshare affiliate links
			 *
			 * + Link include final url examples:
			 * https://skillshare.eqcm.net/c/141234/300217/4650?u=https%3A%2F%2Fwww.skillshare.com%2Flists%2FBest-of-Web-Development%2F643124
			 * https://skillshare.eqcm.net/c/141234/476210/4650?u=https%3A%2F%2Fwww.skillshare.com%2Fclasses%2FQuickBooks-Online-Start-to-Finish%2F2000067588
			 *
			 * + Normal affiliate links examples:
			 * https://skillshare.eqcm.net/DV70Aa
			 * https://skillshare.evyy.net/c/141234/298081/4650
			 */
			$final_url_in_parameter = self::get_argument_from_url( $original_url, 'u' );
			if ( $final_url_in_parameter && self::validate_url( $final_url_in_parameter ) ) {
				$db_url = $final_url_in_parameter;
			} else {
				$db_url = $original_url;
			}
		} elseif ( self::is_shareasale_url( $url ) ) {
			$merchantid = self::get_argument_from_url( $url, 'merchantid', true );
			$userid     = self::get_argument_from_url( $url, 'userid', true );
			$productid  = self::get_argument_from_url( $url, 'productid', true );

			$b = self::get_argument_from_url( $url, 'b', true );
			$u = self::get_argument_from_url( $url, 'u', true );
			$m = self::get_argument_from_url( $url, 'm', true );
			$d = self::get_argument_from_url( $url, 'd', true );

			$db_url = $domain . '_' . $merchantid . '_' . $userid . '_' . $productid . '_' . $b . '_' . $u . '_' . $m . '_' . $d;
		} elseif ( self::is_youtube_url( $url ) ) {
			$v      = self::get_argument_from_url( $url, 'v' );
			$db_url = $domain . '_' . $v;
		} elseif ( 'titan.fitness' === $domain ) {
			$pid    = self::get_argument_from_url( $url, 'pid' );
			$db_url = $domain . '_' . $pid;
			if ( empty( $pid ) ) {
				$db_url = explode( '?', $url )[0];
			}
		} elseif ( 'mechanicalkeyboards.com' === $domain ) {
			$p      = self::get_argument_from_url( $url, 'p' );
			$db_url = $domain . '_' . $p;
		} elseif ( 'musicnotes.com' === $domain ) {
			$ppn    = self::get_argument_from_url( $url, 'ppn' );
			$db_url = $domain . '_' . $ppn;
		} elseif ( 'play.google.com' === $domain ) {
			$id     = self::get_argument_from_url( $url, 'id' );
			$db_url = $domain . '_' . $id;
		} elseif ( in_array( $domain, array( 'brooksbrothers.com', 'swimoutlet.com', 'eatpropergood.com' ), true ) ) {
			$parse   = wp_parse_url( $url );
			$queries = array();
			parse_str( $parse['query'] ?? '', $queries );
			if ( ! empty( $queries ) ) {
				$path = implode( '_', $queries );
				$path = rawurlencode( $path );

				$url_without_params = self::get_url_without_scheme_and_arguments( $url );
				$db_url             = $url_without_params . '_' . $path;
			}
		} elseif ( $original_domain && ( new Lasso_Affiliates() )->is_affiliate_link( $original_url, false ) ) {
			$parse   = wp_parse_url( $original_url );
			$path    = trim( $parse['path'] ?? '', '/' );
			$queries = array();
			$suffix  = '';

			parse_str( $parse['query'] ?? '', $queries );
			if ( ! empty( $queries ) ) {
				$suffix = implode( '_', $queries );
				$suffix = '_' . rawurlencode( $suffix );
			}

			$db_url = $original_domain . '_' . $path . $suffix;
		}

		$db_url = trim( $db_url, '/' );
		$db_url = self::get_url_without_scheme_and_arguments( $db_url );

		return $db_url;
	}

	/**
	 * Get final url in the url
	 * Example: https://affiliate.com/redirect?url=https://getlasso.co
	 *
	 * @param string $url URL.
	 */
	public static function get_final_url_from_url_param( $url ) {
		if ( ! self::validate_url( $url ) ) {
			return false;
		}

		$final_url   = false;
		$base_domain = self::get_base_domain( $url );

		if ( self::is_shareasale_url( $url ) ) {
			$final_url = self::get_argument_from_url( $url, 'urllink' );
		} elseif ( 'pntra.com' === $base_domain ) {
			$final_url = self::get_argument_from_url( $url, 'url' );
		} elseif ( 'wordseed.com' === $base_domain ) {
			$final_url = self::get_argument_from_url( $url, 'url' );
		} elseif ( 'titan.fitness' === $base_domain ) {
			$titan_fitness_redirect = self::get_argument_from_url( $url, 'redirect' );
			$final_url              = $titan_fitness_redirect ? 'https://www.titan.fitness' . $titan_fitness_redirect : false;
		} elseif ( 'prf.hn' === $base_domain ) {
			$parse = wp_parse_url( $url );
			$path  = $parse['path'] ?? '';

			if ( $path ) {
				$path_items = explode( '/', $path );

				foreach ( $path_items as $path_item ) {
					if ( strpos( $path_item, 'destination:' ) !== false ) {
						$url_from_path = str_replace( 'destination:', '', $path_item );
						$url_from_path = urldecode( $url_from_path );
						$final_url     = self::validate_url( $url_from_path ) ? $url_from_path : $final_url;
					}
				}
			}
		} else { // ? other urls
			$parse   = wp_parse_url( $url );
			$queries = array();
			parse_str( $parse['query'] ?? '', $queries );
			foreach ( $queries as $key => $param ) {
				if ( 'referrer' === $key || ! is_string( $param ) ) {
					continue;
				}

				$param = str_replace( ' ', '%20', $param );
				if ( self::validate_url( $param ) ) {
					$final_url = $param;
					break;
				}
			}
		}
		$final_url = self::add_https( $final_url );

		if ( empty( $final_url ) || ! self::validate_url( $final_url ) ) {
			$final_url = false;
		} else {
			$final_url = trim( $final_url, '/' );
		}

		return $final_url;
	}

	/**
	 * Remove unexpected character from post title
	 *
	 * @param string $post_title Post title.
	 * @return string
	 */
	public static function remove_unexpected_characters_from_post_title( $post_title ) {
		// ? Remove unexpected character.
		$post_title = preg_replace( "/[^A-Za-z0-9\s`~!@#$%^&:;\/\?\"\'\+\=\.\,\-\_\*\(\)\|\[\]\<\>\{\}\\\]/", ' ', $post_title );
		// ? Remove duplicated space.
		$post_title = preg_replace( '/\s\s+/', ' ', $post_title );

		return $post_title;
	}

	/**
	 * Format CSS before saving to DB
	 *
	 * @param string $text Text.
	 */
	public static function format_css( $text ) {
		$text = preg_replace( '~[^a-zA-Z0-9\-\_\{\}\@\#\!\;\:\.\,\s\(\)\%\[\]=*"\'\~\|\^\$]~', '', $text );
		$text = trim( $text );

		return $text;
	}

	/**
	 * Get Lasso Lite to import.
	 *
	 * @param  int $import_id post id.
	 * @return object.
	 */
	public static function get_lasso_lite_url_to_import( $import_id ) {
		$settings      = Lasso_Setting::lasso_get_settings();
		$settings_lite = self::lasso_lite_get_setting();

		// ? Lite fields meta data
		$meta_buy_btn_text_lite     = get_post_meta( $import_id, '_buy_btn_text', true );
		$meta_show_disclosure_lite  = get_post_meta( $import_id, '_show_disclosure', true );
		$meta_badge_text_lite       = get_post_meta( $import_id, '_badge_text', true );
		$meta_show_price_lite       = get_post_meta( $import_id, '_show_price', true );
		$meta_open_new_tab_lite     = get_post_meta( $import_id, '_open_new_tab', true );
		$meta_enable_nofollow_lite  = get_post_meta( $import_id, '_enable_nofollow', true );
		$meta_enable_sponsored_lite = get_post_meta( $import_id, '_enable_sponsored', true );

		// ? Lasso Lite does not support Secondary Button(SB), we should get Lasso Pro SB data case import back from Lite
		$meta_second_btn_url   = get_post_meta( $import_id, 'second_btn_url', true );
		$meta_second_btn_text  = get_post_meta( $import_id, 'second_btn_text', true );
		$meta_open_new_tab2    = get_post_meta( $import_id, 'open_new_tab2', true );
		$meta_enable_nofollow2 = get_post_meta( $import_id, 'enable_nofollow2', true );

		// ? Setting default fields
		$setting_buy_btn_text_lite = $settings_lite['primary_button_text'] ?? $settings['primary_button_text'];
		$setting_show_disclosure   = boolval( $settings_lite['show_disclosure'] ?? $settings['show_disclosure'] );
		$setting_badge_text        = $settings_lite['badge_text'] ?? $settings['badge_text'];
		$setting_show_price        = boolval( $settings_lite['show_price'] ?? $settings['show_price'] );
		$setting_open_new_tab      = boolval( $settings_lite['open_new_tab'] ?? $settings['open_new_tab'] );
		$setting_enable_nofollow   = boolval( $settings_lite['enable_nofollow'] ?? $settings['enable_nofollow'] );
		$setting_enable_sponsored  = boolval( $settings_lite['enable_sponsored'] ?? $settings['enable_sponsored'] );
		$settings_open_new_tab2    = $settings['open_new_tab2'];
		$settings_nofollow2        = $settings['enable_nofollow2'];

		$primary_button_text = '' !== $meta_buy_btn_text_lite ? $meta_buy_btn_text_lite : $setting_buy_btn_text_lite;
		$show_disclosure     = '' !== $meta_show_disclosure_lite ? (bool) $meta_show_disclosure_lite : $setting_show_disclosure;
		$badge_text          = '' !== $meta_badge_text_lite ? $meta_badge_text_lite : $setting_badge_text;
		$show_price          = '' !== $meta_show_price_lite ? (bool) $meta_show_price_lite : $setting_show_price;
		$open_new_tab        = '' !== $meta_open_new_tab_lite ? (bool) $meta_open_new_tab_lite : $setting_open_new_tab;
		$enable_nofollow     = '' !== $meta_enable_nofollow_lite ? (bool) $meta_enable_nofollow_lite : $setting_enable_nofollow;
		$enable_sponsored    = '' !== $meta_enable_sponsored_lite ? (bool) $meta_enable_sponsored_lite : $setting_enable_sponsored;
		$open_new_tab2       = '' !== $meta_open_new_tab2 ? (bool) $meta_open_new_tab2 : $settings_open_new_tab2;
		$enable_nofollow2    = '' !== $meta_enable_nofollow2 ? (bool) $meta_enable_nofollow2 : $settings_nofollow2;

		$lasso_lite_url = (object) array(
			'show_disclosure'  => $show_disclosure,
			'description'      => get_post_meta( $import_id, '_description', true ),
			'price'            => get_post_meta( $import_id, '_price', true ),
			'custom_thumbnail' => get_post_meta( $import_id, '_lasso_lite_custom_thumbnail', true ),
			'second_btn_url'   => $meta_second_btn_url,
			'second_btn_text'  => $meta_second_btn_text,
			'open_new_tab'     => $open_new_tab,
			'enable_nofollow'  => $enable_nofollow,
			'open_new_tab2'    => $open_new_tab2,
			'enable_nofollow2' => $enable_nofollow2,
			'enable_sponsored' => $enable_sponsored,
			'display'          => (object) array(
				'primary_button_text' => $primary_button_text,
				'show_price'          => $show_price,
				'badge_text'          => $badge_text,
			),
		);

		return $lasso_lite_url;
	}

	/**
	 * Get Lasso Lite settings from db
	 */
	public static function lasso_lite_get_setting() {
		$options = get_option( 'lassolite_settings', array() );
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		return $options;
	}

	/**
	 * Check whether import page should display
	 *
	 * @return bool
	 */
	public static function should_show_import_page() {
		return ! empty( ( new Lasso_DB() )->get_import_plugins( true ) ) ? true : false;
	}

	/**
	 * Is built with Elementor.
	 *
	 * Check whether the post was built with Elementor.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return bool Whether the post was built with Elementor.
	 */
	public static function is_built_with_elementor( $post_id ) {
		return ! ! get_post_meta( $post_id, '_elementor_edit_mode', true );
	}

	/**
	 * Get elementor json meta
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key Meta key.
	 *
	 * @return array
	 */
	public static function get_elementor_json_meta( $post_id, $key ) {
		$meta = get_post_meta( $post_id, $key, true );

		if ( is_string( $meta ) && ! empty( $meta ) ) {
			$meta = json_decode( $meta, true );
		}

		if ( empty( $meta ) ) {
			$meta = array();
		}

		return $meta;
	}

	/**
	 * Get Ajax URL
	 */
	public static function get_ajax_url() {
		return admin_url( 'admin-ajax.php' );
	}

	/**
	 * Get Lasso - WP option
	 *
	 * @param string $option_name Option name.
	 * @param mixed  $default     Default value.
	 * @return mixed|void
	 */
	public static function get_option( $option_name, $default = false ) {
		return get_option( Enum::SETTING_PREFIX . $option_name, $default );
	}

	/**
	 * Update Lasso - WP option
	 *
	 * @param string $option_name  Option name.
	 * @param mixed  $option_value Option value.
	 * @param bool   $autoload     Autoload.
	 * @return bool
	 */
	public static function update_option( $option_name, $option_value, $autoload = null ) {
		return update_option( Enum::SETTING_PREFIX . $option_name, $option_value, $autoload );
	}

	/**
	 * Whether show Request Review at the top of the page
	 */
	public static function show_request_review() {
		$lasso_db   = new Lasso_DB();
		$link_count = $lasso_db->get_dashboard_link_count();

		$lasso_review_allow      = self::cast_to_boolean( self::get_option( Enum::OPTION_REVIEW_ALLOW, '1' ) );
		$lasso_review_snooze     = self::cast_to_boolean( self::get_option( Enum::OPTION_REVIEW_SNOOZE, '0' ) );
		$lasso_review_link_count = intval( self::get_option( Enum::OPTION_REVIEW_LINK_COUNT, $link_count ) );
		$first_install_date      = get_option( Enum::OPTION_FIRST_INSTALL_DATE, false );

		if ( ! $first_install_date ) {
			$first_install_date = time();
			update_option( Enum::OPTION_FIRST_INSTALL_DATE, $first_install_date );
		}

		$show            = ! $lasso_review_snooze && $link_count >= 20;
		$snooze_but_show = $lasso_review_snooze && $link_count - $lasso_review_link_count >= 20;

		if ( ! $lasso_review_allow ) {
			return false;
		}

		$delay_days   = 30;
		$delay_time   = $delay_days * DAY_IN_SECONDS;
		$should_delay = intval( $first_install_date ) + $delay_time >= time();
		if ( ! $should_delay && ( $show || $snooze_but_show ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Create lasso webp image file
	 *
	 * @param int $post_id Lasso Post ID.
	 * @return string|null
	 */
	public static function create_lasso_webp_image( $post_id ) {
		$result      = '';
		$enable_webp = Lasso_Setting::lasso_get_setting( Enum::OPTION_ENABLE_WEBP );
		if ( ! $enable_webp ) {
			return $result;
		}

		try {
			$custom_thumbnail = get_post_meta( $post_id, 'lasso_custom_thumbnail', true );

			if ( ! $custom_thumbnail ) {
				$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $post_id );
				// ? Get amazon image url if existed
				if ( $lasso_url->amazon->amazon_id && strpos( $lasso_url->amazon->default_image, 'https://m.media-amazon.com/images/I/' ) !== false ) {
					$custom_thumbnail = $lasso_url->amazon->default_image;
					update_post_meta( $post_id, 'lasso_custom_thumbnail', $custom_thumbnail );
				} else {
					return $result;
				}
			}

			// ? Skip if current image is webp type || ? Skip if same as default webp thumbnail
			if ( '.webp' === strtolower( substr( $custom_thumbnail, -5 ) ) || LASSO_DEFAULT_THUMBNAIL === $custom_thumbnail ) {
				update_post_meta( $post_id, Enum::LASSO_WEBP_THUMBNAIL, $custom_thumbnail );
				return $result;
			}

			// ? Change old default image jpg to webp
			if ( strpos( $custom_thumbnail, 'lasso-no-thumbnail.jpg' ) !== false ) {
				update_post_meta( $post_id, 'lasso_custom_thumbnail', LASSO_DEFAULT_THUMBNAIL );
				return $result;
			}

			$custom_thumbnail_file_name = self::format_file_name( pathinfo( $custom_thumbnail, PATHINFO_FILENAME ) );
			$webp_thumbnail             = get_post_meta( $post_id, Enum::LASSO_WEBP_THUMBNAIL, true );
			$webp_thumbnail_file_name   = $webp_thumbnail ? self::format_file_name( pathinfo( $webp_thumbnail, PATHINFO_FILENAME ) ) : '';
			$is_update_new_webp         = $custom_thumbnail_file_name !== $webp_thumbnail_file_name;

			if ( ! $webp_thumbnail || $is_update_new_webp ) {
				delete_post_meta( $post_id, Enum::LASSO_WEBP_THUMBNAIL );
				$result = self::create_webp_image_from_url( $custom_thumbnail );
				if ( $result ) {
					update_post_meta( $post_id, Enum::LASSO_WEBP_THUMBNAIL, $result );
				}
			}
		} catch ( \Exception $e ) {
			self::write_log( 'Error: ' . $e->getMessage(), Lasso_Log::ERROR_LOG );
		}

		return $result;
	}

	/**
	 * Create webp image url from image url.
	 *
	 * @param string $url Image url.
	 * @return string|null
	 */
	public static function create_webp_image_from_url( $url ) {
		$result      = '';
		$enable_webp = Lasso_Setting::lasso_get_setting( Enum::OPTION_ENABLE_WEBP );
		if ( ! $enable_webp ) {
			return $result;
		}

		try {
			$upload_dir                       = wp_upload_dir();
			$upload_base_dir                  = $upload_dir['basedir'] ?? ''; // ? Ex: /var/wwww/wordpress/wp-content/uploads
			$upload_base_url                  = $upload_dir['baseurl'] ?? ''; // ? Ex: https://example.com/wp-content/uploads
			$upload_base_url_without_protocol = str_replace( array( 'http://', 'https://' ), '', $upload_base_url ); // ? Ex: example.com/wp-content/uploads
			$relative_upload_dir              = substr( $upload_base_url_without_protocol, strpos( $upload_base_url_without_protocol, '/' ) + 1 ); // ? Ex: wp-content/uploads

			if ( $upload_base_dir && is_dir( $upload_base_dir ) ) {
				$is_manually_upload_image = strpos( $url, $upload_base_url_without_protocol ) === false ? false : true;

				if ( $is_manually_upload_image ) { // ? Create webp image from existed file.
					$file_relative_path = explode( $relative_upload_dir, $url )[1]; // ? Ex: /2022/09/iphone.png
					$file_full_path     = $upload_dir['basedir'] . $file_relative_path; // ? Ex: /var/wwww/wordpress/wp-content/uploads/2022/09/iphone.png
					$result             = self::create_webp_image( $file_full_path ); // ? Ex: /var/wwww/wordpress/wp-content/uploads/2022/09/iphone.webp
				} else { // ? Download image file then create webp image and delete downloaded file.
					// ? Resize amazon image to suitable size
					if ( strpos( $url, 'https://m.media-amazon.com/images/I/' ) !== false ) {
						$url = self::build_amazon_image_size( $url );
					}

					// ? Get the downloaded image path
					$temp_image_path = self::upload_image_from_url( $url );

					// ? Create webp image from downloaded file then delete downloaded file
					if ( $temp_image_path && file_exists( $temp_image_path ) ) {
						$result = self::create_webp_image( $temp_image_path, true ); // ? Ex: /var/wwww/wordpress/wp-content/uploads/2022/09/iphone.webp
					}
				}

				// ? Build the webp image url
				if ( $result && file_exists( $result ) ) {
					$result = $upload_dir['baseurl'] . '/' . self::get_relative_uploads_path_from_wp_path( $result ); // ? Ex: https://example.com/wp-content/uploads/2022/09/iphone.webp
				}
			}
		} catch ( \Exception $e ) {
			self::write_log( 'Error: ' . $e->getMessage(), Lasso_Log::ERROR_LOG );
		}
		$result = str_replace( '\\', '\/', $result );
		return $result;
	}

	/**
	 * Create webp image from the image path
	 *
	 * @param string $source     Image path.
	 * @param bool   $remove_old Is remove the image path. Default to false.
	 * @param int    $quality    Webp image quantity 1-100. After compare the webp with the original image, the default 50 is good value.
	 * @return string|null
	 */
	public static function create_webp_image( $source, $remove_old = false, $quality = 50 ) {
		$destination = null;

		if ( ! function_exists( 'imagewebp' ) || ! file_exists( $source ) ) {
			return '';
		}

		try {
			$dir         = pathinfo( $source, PATHINFO_DIRNAME );
			$name        = pathinfo( $source, PATHINFO_FILENAME );
			$destination = $dir . DIRECTORY_SEPARATOR . $name . '.webp';
			$info        = getimagesize( $source );
			$image_mime  = $info['mime'] ?? '';

			if ( $image_mime ) {
				$is_alpha = in_array( $image_mime, array( 'image/png', 'image/gif' ), true );

				if ( 'image/jpeg' === $image_mime ) {
					$image = @imagecreatefromjpeg( $source ); // phpcs:ignore
				} elseif ( 'image/png' === $image_mime ) {
					$image = @imagecreatefrompng( $source ); // phpcs:ignore
				} elseif ( 'image/gif' === $image_mime ) {
					$image = @imagecreatefromgif( $source ); // phpcs:ignore
				}

				if ( isset( $image ) && ! is_bool( $image ) ) {
					if ( $is_alpha ) {
						imagepalettetotruecolor( $image );
						imagealphablending( $image, true );
						imagesavealpha( $image, true );
					}

					imagewebp( $image, $destination, $quality );
					imagedestroy( $image );
				}

				if ( $remove_old && file_exists( $source ) ) {
					unlink( $source );
				}
			}
		} catch ( \Exception $e ) {
			self::write_log( 'Error: ' . $e->getMessage(), Lasso_Log::ERROR_LOG );
			$destination = null;
		}

		$destination = $destination && file_exists( $destination ) ? $destination : null;

		return $destination;
	}

	/**
	 * Upload image from url to WordPress uploads directory
	 *
	 * @param string $image_url Image url.
	 * @return string|null
	 */
	public static function upload_image_from_url( $image_url ) {
		$destination = null;
		$success     = false;

		try {
			$file_name  = basename( wp_parse_url( $image_url, PHP_URL_PATH ) );
			$upload_dir = wp_upload_dir();

			if ( $file_name && ! empty( $upload_dir['basedir'] ) ) {
				$image_content = self::get_content_from_url( $image_url );
				if ( $image_content ) {
					WP_Filesystem();
					require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
					require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
					$wp_filesystem = new WP_Filesystem_Direct( null );
					$file_name     = self::format_file_name( $file_name );
					$destination   = $upload_dir['path'] . DIRECTORY_SEPARATOR . $file_name;

					$wp_filesystem->put_contents( $destination, $image_content );
					$success = true;
				}
			}
		} catch ( \Exception $e ) {
			self::write_log( 'Error: ' . $e->getMessage(), Lasso_Log::ERROR_LOG );
		}

		$destination = $success && $destination && file_exists( $destination ) ? $destination : null;

		return $destination;
	}

	/**
	 * Get content from url
	 *
	 * @param string $url Image url.
	 * @return false|string
	 */
	public static function get_content_from_url( $url ) {
		$result = '';

		try {
			$res = wp_remote_get( $url );

			if ( ! is_wp_error( $res ) ) {
				$body   = wp_remote_retrieve_body( $res );
				$status = wp_remote_retrieve_response_code( $res );

				if ( 200 === $status && $body ) {
					$result = $body;
				}
			}
		} catch ( \Exception $e ) {
			self::write_log( 'Error: ' . $e->getMessage(), Lasso_Log::ERROR_LOG );
			$result = '';
		}

		return $result;
	}

	/**
	 * Build the amazon image url with the custom size
	 *
	 * @param string $image_url Amazon image url.
	 * @param int    $size      Custom size.
	 * @return string
	 */
	public static function build_amazon_image_size( $image_url, $size = 500 ) {
		$result = $image_url;
		try {
			$image_url_split = explode( 'https://m.media-amazon.com/images/I/', $image_url );
			$image_id        = substr( $image_url_split[1], 0, strpos( $image_url_split[1], '.' ) );
			$result          = 'https://m.media-amazon.com/images/I/' . $image_id . '._SL' . $size . '_.jpg';
		} catch ( \Exception $e ) {
			self::write_log( 'Error: ' . $e->getMessage(), Lasso_Log::ERROR_LOG );
		}

		return $result;
	}

	/**
	 * Format file name to remove unexpect character
	 *
	 * @param string $file_name File name.
	 * @return string|string[]|null
	 */
	public static function format_file_name( $file_name ) {
		return preg_replace( '/[^A-Za-z0-9\.\-\_]/', '', $file_name );
	}

	/**
	 * Get webp url image by lasso_id, fix webp file does not exist.
	 *
	 * @param int $lasso_id lasso_id.
	 *
	 * @return string
	 */
	public static function get_webp_url( $lasso_id ) {
		$webp_url = get_post_meta( $lasso_id, Enum::LASSO_WEBP_THUMBNAIL, true );

		if ( $webp_url ) {
			$webp_url_domain = self::get_base_domain( $webp_url );
			$site_domain     = self::get_base_domain( site_url() );

			// ? Compare domain image url with site domain, if difference replace by site domain
			if ( $webp_url_domain !== $site_domain ) {
				$webp_url = str_replace( $webp_url_domain, $site_domain, $webp_url );
			}

			$webp_path      = self::get_wp_file_path_from_url( $webp_url );
			$webp_file_name = self::format_file_name( pathinfo( $webp_url, PATHINFO_BASENAME ) );

			if ( ! file_exists( $webp_path ) || '.webp' !== strtolower( substr( $webp_file_name, -5 ) ) ) {
				delete_post_meta( $lasso_id, Enum::LASSO_WEBP_THUMBNAIL );
				$webp_url = '';
			}
		}

		return $webp_url;
	}

	/**
	 * Get WordPress file path from url
	 *
	 * @param string $url URL.
	 * @return mixed|string
	 */
	public static function get_wp_file_path_from_url( $url ) {
		$file_path = '';
		$parse     = explode( site_url(), $url );

		if ( is_array( $parse ) && count( $parse ) > 1 ) {
			$path      = $parse[1];
			$file_path = ABSPATH . $path;
			$file_path = str_replace( '//', '/', $file_path );
		}

		return $file_path;
	}

	/**
	 * Check if file url is updated by WordPress and existing.
	 *
	 * @param string $file_url File url.
	 * @return bool
	 */
	public static function is_uploaded_file_existing( $file_url ) {
		$file_path = self::get_wp_file_path_from_url( $file_url );

		if ( file_exists( $file_path ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get relative path from WordPress path
	 * Ex: /var/wwww/wordpress/wp-content/uploads/2022/09/iphone.webp
	 *   => 2022/09/iphone.webp
	 *
	 * @param string $wp_path WordPress path, this can absolute path or base url path.
	 *
	 * @return string
	 */
	public static function get_relative_uploads_path_from_wp_path( $wp_path ) {
		$pos_uploads = strpos( $wp_path, 'uploads' );
		return substr( $wp_path, ( $pos_uploads + 8 ), strlen( $wp_path ) );
	}

	/**
	 * Check to show brag icon
	 *
	 * @return bool
	 */
	public static function is_show_brag_icon() {
		$lasso_settings = Lasso_Setting::lasso_get_settings();

		$enable_brag_mode = $lasso_settings['enable_brag_mode'] ?? false;
		$lasso_url        = $lasso_settings['lasso_affiliate_URL'] ?? false;

		$show_brag_after   = 48; // ? hours
		$current_timestamp = time();
		$lasso_end_date    = intval( get_option( 'lasso_end_date', $current_timestamp ) );
		$always_show_brag  = $current_timestamp > $lasso_end_date + ( $show_brag_after * HOUR_IN_SECONDS );

		if ( ( ! $lasso_url || ! $enable_brag_mode ) && ! $always_show_brag ) {
			return false;
		}

		return true;
	}

	/**
	 * Check using WP classic editor
	 *
	 * @return bool
	 */
	public static function is_classic_editor() {
		return self::is_classic_editor_plugin_active() || self::is_disable_gutenberg_plugin_active();
	}

	/**
	 * Get Pros Cons items.
	 *
	 * @param array $schema_values Pros or Cons string values.
	 *
	 * @return array
	 */
	public static function get_pros_cons_items( $schema_values ) {
		$item_list_element = array();
		$position          = 0;

		foreach ( $schema_values as $value ) {
			$position++;
			$item_list_element[] = array(
				'@context' => 'https://schema.org',
				'@type'    => 'ListItem',
				'position' => $position,
				'name'     => $value,
			);
		}

		return $item_list_element;
	}

	/**
	 * Get root Pros and Cons value
	 *
	 * @param object $fields   Custom fields.
	 * @param int    $field_id Field ID.
	 * @param string $separator Separator.
	 *
	 * @return false|string
	 */
	public static function get_root_pros_cons_value( $fields, $field_id, $separator = '|||' ) {
		// ? Get root Pros and Cons value
		$field_idx   = array_search( (string) $field_id, array_column( $fields, 'field_id' ), true );
		$field       = ( false !== $field_idx ? $fields[ $field_idx ] : false );
		$field_value = '';

		if ( $field ) {
			$field_value = implode( $separator, explode( "\n", $field->field_value ) );
		}

		return $field_value;
	}

	/**
	 * Get schema info by Lasso atts
	 *
	 * @param array $atts $atts.
	 *
	 * @return array
	 */
	public static function get_schema_info_by_lasso_atts( $atts ) {
		$post_id   = $atts['id'] ?? '';
		$price     = $atts['price'] ?? '';
		$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $post_id );

		// ? Get fields
		$lasso_db = new Lasso_DB();
		$fields   = $lasso_db->get_fields_by_lasso_id( $post_id );

		// ? Get root Pros and Cons value
		$pros_field_value            = self::get_root_pros_cons_value( $fields, Model_Field::PROS_FIELD_ID, '|' );
		$cons_field_value            = self::get_root_pros_cons_value( $fields, Model_Field::CONS_FIELD_ID, '|' );
		$is_display_pros_cons_toggle = ! ( empty( $pros_field_value ) || empty( $cons_field_value ) );

		// ? Get Primary Rating value
		$primary_rating = $lasso_url->fields->primary_rating ? $lasso_url->fields->primary_rating->field_value : '';

		if ( '' !== $price ) {
			$lasso_url->price = $price;
		}

		$price = $lasso_url->price;
		$price = (float) str_replace( '$', '', $price );

		return array(
			'lasso_id'                    => $lasso_url->lasso_id,
			'price'                       => $price,
			'currency'                    => $lasso_url->currency,
			'pros'                        => $pros_field_value,
			'cons'                        => $cons_field_value,
			'is_display_pros_cons_toggle' => $is_display_pros_cons_toggle,
			'primary_rating'              => $primary_rating,
		);
	}

	/**
	 * Repair table
	 *
	 * @param string $table Table name.
	 * @return mixed
	 */
	public static function repair_table( $table ) {
		// @codeCoverageIgnoreStart
		$repair         = Model::get_row( "REPAIR TABLE $table" );
		$repair_msg_txt = $repair->Msg_text ?? ''; // phpcs:ignore
		$result         = 'OK' === $repair_msg_txt
			? "Successfully repaired the $table table."
			: "Failed to repair the $table table. Error: $repair->Msg_text";
		// @codeCoverageIgnoreEnd

		return $result;
	}

	/**
	 * Remove sub-directory from Post name
	 *
	 * @param string $post_name Post name.
	 * @return string
	 */
	public static function remove_subdirectory_from_post_name( $post_name ) {
		$post_name    = trim( $post_name, '/' );
		$site_url     = site_url();
		$parse        = wp_parse_url( $site_url );
		$subdirectory = isset( $parse['path'] ) ? trim( $parse['path'], '/' ) : '';

		if ( $subdirectory && false !== strpos( $post_name, "$subdirectory/" ) ) {
			$post_name = str_replace( "$subdirectory/", '', $post_name );
		}

		return $post_name;
	}

	/**
	 * Check a url is internal link
	 *
	 * @param string $url URL.
	 *
	 * @return bool
	 */
	public static function is_internal_full_link( $url ) {
		$site_url    = get_site_url();
		$url_domain  = self::get_base_domain( $url );
		$site_domain = self::get_base_domain( $site_url );

		return $url_domain === $site_domain;
	}

	/**
	 * Remove multiple spaces by a space
	 *
	 * @param string $text Text.
	 *
	 * @return string
	 */
	public static function remove_multiple_spaces( $text ) {
		return preg_replace( '/\s+/', ' ', $text );
	}

	/**
	 * Build lsid
	 *
	 * @return false|string
	 */
	public static function build_lsid() {
		$lsid = session_create_id( 'ls-' );
		if ( ! $lsid ) {
			$lsid = 'ls-' . md5( uniqid( wp_rand(), true ) );
		}

		return $lsid;
	}

	/**
	 * Check URL is WP post
	 *
	 * @param string $url URL.
	 *
	 * @return false|string
	 */
	public static function is_wp_post( $url ) {
		$wp_post_id = url_to_postid( $url );

		if ( ! $wp_post_id ) {
			return false;
		}

		$post_type = get_post_type( $wp_post_id );

		return in_array( $post_type, array( 'post', 'page' ), true );
	}

	/**
	 * Get current GMT datetime string
	 *
	 * @param string $format Format date.
	 * @return mixed
	 */
	public static function get_gmt_datetime( $format = 'Y-m-d H:i:s' ) {
		$datetime = new \DateTime( 'now', new \DateTimeZone( 'UTC' ) );
		return $datetime->format( $format );
	}

	/**
	 * Convert seconds to time
	 *
	 * @param int $seconds Seconds.
	 */
	public static function seconds_to_time( $seconds ) {
		$seconds = intval( $seconds );
		$hours   = floor( $seconds / HOUR_IN_SECONDS );
		$minutes = floor( ( $seconds % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS );
		$seconds = $seconds % MINUTE_IN_SECONDS;

		return sprintf( "%02d:%02d:%02d", $hours, $minutes, $seconds ); // phpcs:ignore
	}
}
