<?php
/**
 * Declare class Affiliates
 *
 * @package Affiliates
 */

namespace Lasso\Classes;

use Lasso\Classes\Helper as Lasso_Helper;

use Lasso_License;

/**
 * Affiliates
 */
class Affiliates {
	const AFFILIATE_DATA_PATH                = LASSO_PLUGIN_PATH . '/files/affiliates.json';
	const IMPACT_DOMAINS_PATH                = LASSO_PLUGIN_PATH . '/files/impact-domains.json';
	const AMAZON_DOMAINS_PATH                = LASSO_PLUGIN_PATH . '/files/amazon-associates-domains.json';
	const DISALLOWED_CHANGE_SUBID_AFFILIATES = array( 'tradetracker' );

	/**
	 * Affiliates
	 *
	 * @var array $affiliates
	 */
	public $affiliates = array();

	/**
	 * Impact domains
	 *
	 * @var array $impact_domains
	 */
	public $impact_domains = array();

	/**
	 * Amazon domains
	 *
	 * @var array $amazon_domains
	 */
	public $amazon_domains = array();

	/**
	 * Construct
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Init
	 *
	 * @return void
	 */
	private function init() {
		$this->init_affiliates();
		$this->init_affiliates_domains();
	}

	/**
	 * Init affiliates data.
	 *
	 * @return array|mixed
	 */
	private function init_affiliates() {
		try {
			$affilates_data   = file_get_contents( self::AFFILIATE_DATA_PATH ); // phpcs:ignore
			$this->affiliates = json_decode( $affilates_data, true );
		} catch ( \Exception $e ) {
			return $this->affiliates;
		}

		return $this->affiliates;
	}

	/**
	 * Check if URL is an affiliate link
	 *
	 * @param string $url URL to check.
	 * @param bool   $is_included_amazon Whether to include Amazon Associates or not. Default to true.
	 * @param string $affiliate_slug Slug of affiliate.
	 *
	 * @return false|mixed|string
	 */
	public function is_affiliate_link( $url, $is_included_amazon = true, $affiliate_slug = false ) {
		foreach ( $this->affiliates as $affiliate ) {
			if ( ! $is_included_amazon && 'amazon_associates' === $affiliate['slug'] ) {
				continue;
			}

			if ( $affiliate_slug && $affiliate_slug !== $affiliate['slug'] ) {
				continue;
			}

			if ( isset( $affiliate['is_affiliate_function'] ) && method_exists( $this, $affiliate['is_affiliate_function'] ) && $this->{$affiliate['is_affiliate_function']}( $url ) ) { // ? Method validation
				return $affiliate['slug'];
			} elseif ( $affiliate['matches'] && preg_match( $affiliate['matches'], $url ) ) { // ? Regex validation
				return $affiliate['slug'];
			}
		}

		return false;
	}

	/**
	 * Check if URL is an Impact link
	 *
	 * @param string $url URL to check.
	 *
	 * @return bool
	 */
	public function is_impact( $url ) {
		$base_domain                    = Lasso_Helper::get_base_domain( $url );
		$base_domain_without_sub_domain = Lasso_Helper::get_base_domain( $url, false );

		if ( in_array( $base_domain, $this->impact_domains, true ) || in_array( $base_domain_without_sub_domain, $this->impact_domains, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if URL is an Amazon link
	 *
	 * @param string $url URL to check.
	 *
	 * @return bool
	 */
	public function is_amazon( $url ) {
		$base_domain = Lasso_Helper::get_base_domain( $url );

		if ( in_array( $base_domain, $this->amazon_domains, true ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Check if URL is a Post Affiliate Pro link
	 *
	 * @param string $url URL to check.
	 *
	 * @return bool
	 */
	public function is_post_affiliate_pro( $url ) {
		$url = parse_url( $url ); // phpcs:ignore

		// Perform matching logic.
		$hash         = $url['fragment'] ?? '';
		$host         = $url['host'] ?? '';
		$path         = $url['path'] ?? '';
		$query        = $url['query'] ?? '';
		$hash_match   = preg_match( '/(a_aid=.+)|(tr_aid=.+)/', $hash );
		$origin_match = strpos( $host, 'repfitness.com' ) !== false || strpos( $host, '.ivacy.com' ) !== false;
		$href_match   = preg_match( '/((\.postaffiliatepro)|(affiliates\.xeroshoes)).com\/scripts\//', $path );
		$search_match = preg_match( '/a_aid|tr_aid|a_bid/', $query );

		// Check if any of the conditions are true.
		if ( $hash_match || $origin_match || $href_match || $search_match ) {
			return true;
		}

		return false;
	}

	/**
	 * Init affiliates domains
	 *
	 * @return void
	 */
	public function init_affiliates_domains() {
		$this->impact_domains = $this->init_domains( self::IMPACT_DOMAINS_PATH );
		$this->amazon_domains = $this->init_domains( self::AMAZON_DOMAINS_PATH );
	}

	/**
	 * Send tracking click request to server.
	 *
	 * @param string $url       Tracking click url.
	 * @param object $lasso_url Lasso url object.
	 * @return void
	 */
	public function track_click( $url, $lasso_url ) {
		try {
			$request_uri = Lasso_Helper::get_server_param( 'REQUEST_URI' );
			$payload     = array(
				'd' => Lasso_Helper::get_base_domain( site_url() ),
				'h' => 0,
				'n' => 'click',
				'r' => Lasso_Helper::get_server_param( 'HTTP_REFERER' ),
				'u' => $request_uri ? $request_uri : Lasso_Helper::get_server_param( 'REDIRECT_URL' ),
				'w' => 0,
				'p' => array(
					'lasso_href'  => $url,
					'class'       => null,
					'd'           => Lasso_Helper::get_base_domain( site_url() ),
					'hierarchy'   => '',
					'ipa'         => '',
					'lasso_lid'   => $lasso_url->lasso_id,
					'lasso_text'  => '',
					'lasso_title' => $lasso_url->name,
					'lsid'        => Lasso_Helper::build_lsid(),
					'lssid'       => Lasso_License::get_site_id(),
					'pid'         => 2, // ? 2 = Default WordPress Page ID
					'pt'          => $lasso_url->name,
					'tag'         => 'a',
					'type'        => 'External Click',
				),
			);

			$api_link = LASSO_LINK . '/events/tracking';
			$res      = Lasso_Helper::send_request( 'post', $api_link, $payload );

			return;
		} catch ( \Exception $e ) {
			return;
		}
	}

	/**
	 * Get domains list.
	 *
	 * @param string $domain_file_path Domain file path.
	 * @return array|int[]|string[]
	 */
	private function init_domains( $domain_file_path ) {
		try {
			$domains = file_get_contents( $domain_file_path ); // phpcs:ignore
			$domains = json_decode( $domains, true );

			return array_keys( $domains );
		} catch ( \Exception $e ) {
			return array();
		}
	}

	/**
	 * Get subid
	 *
	 * @param string $affiliate Affiliate slug.
	 * @param string $url       URL to check.
	 */
	private function get_subid( $affiliate, $url ) {
		$subid_key = $this->affiliates[ $affiliate ]['sub_ids'];
		$url_parts = wp_parse_url( $url );
		parse_str( $url_parts['query'] ?? '', $url_params );

		$subid = array();
		foreach ( $subid_key as $key => $value ) {
			$subid[ $value ] = $url_params[ $value ] ?? '';
		}

		return $subid;
	}

	/**
	 * Add lcid to subid
	 *
	 * @param string $href URL to check.
	 * @param string $lcid Lasso click id.
	 */
	public function add_lcid_to_subid( $href, $lcid ) {
		$affiliate_slug = $this->is_affiliate_link( $href );
		$subid_params   = $this->get_subid( $affiliate_slug, $href );
		$subid_keys     = array_keys( $subid_params );
		$last_key       = array_key_last( $subid_keys );

		if ( 1 === count( $subid_keys ) ) {
			$key                  = $subid_keys[0];
			$subid_params[ $key ] = '' !== $subid_params[ $key ] ? $subid_params[ $key ] . '__' . $lcid : $lcid;
		} else {
			foreach ( $subid_params as $key => $value ) {
				if ( '' === $subid_params[ $key ] ) {
					$subid_params[ $key ] = $lcid;
					break;
				} elseif ( $last_key === $key ) {
					$subid_params[ $last_key ] = $subid_params[ $key ] . '__' . $lcid;
					break;
				}
			}
		}

		// ? Set the Lasso subid to the lassoHref
		foreach ( $subid_params as $key => $value ) {
			if ( $value ) {
				$href = Lasso_Helper::add_param_to_url( $href, $key, $value );
			}
		}

		return $href;
	}
}
