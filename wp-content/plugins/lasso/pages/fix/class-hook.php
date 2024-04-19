<?php
/**
 * Lasso Fix - Hook.
 *
 * @package Pages
 */

namespace Lasso\Pages\Fix;

use Lasso\Classes\Affiliates as Lasso_Affiliates;
use Lasso\Classes\Auto_Monetize\Auto_Monetize;
use Lasso\Classes\Fix as Lasso_Fix;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Classes\Setting_Enum;
use Lasso\Classes\Verbiage as Lasso_Verbiage;

use Lasso\Models\Model;
use Lasso\Models\Revert as Lasso_Revert;
use Lasso\Models\Link_Locations;

use Lasso_Affiliate_Link;
use Lasso_Amazon_Api;
use Lasso_Cron;
use Lasso_DB;
use Lasso_DB_Script;
use Lasso_Process_Pretty_Link_Final_Url;

/**
 * Lasso Fix - Hook.
 */
class Hook {
	/**
	 * List mapping pretty link final url and origin url.
	 *
	 * @var array $pretty_link_final_urls
	 */
	private $pretty_link_final_urls = array();

	/**
	 * Declare "Lasso register hook events" to WordPress.
	 */
	public function register_hooks() {
		// @codingStandardsIgnoreStart
		add_filter( Lasso_Cron::FILTER_ATAG_AT_FIRST, array( $this, 'fix_href_issues' ), 20, 1 );
		add_filter( Lasso_Cron::FILTER_ATAG_AT_FIRST, array( $this, 'fix_home_url_is_replaced' ), 40, 1 );

		// add_filter( Lasso_Cron::FILTER_ATAG_AT_FIRST, array( $this, 'fix_href_change_to_final_url_that_existed_in_pretty_link' ), 10, 2 );
		// add_filter( Lasso_Cron::FILTER_ATAG_AT_FIRST, array( $this, 'fix_import_revert_href' ), 30, 1 );
		// add_filter( Lasso_Cron::FILTER_ATAG_AT_FIRST, array( $this, 'fix_revert_internal_link_was_changed_to_lasso_link_having_the_same_permalink' ), 50, 1 );
		add_filter( Lasso_Cron::FILTER_ATAG_AT_FIRST, array( $this, 'fix_amz_prefix_in_url' ), 50, 1 );

		// ? Auto detect ShareASale, Impact, CJ,... links
		if ( Lasso_Setting::lasso_get_setting( 'auto_monetize_affiliates' ) ) {
			add_filter( Lasso_Cron::FILTER_ATAG_AT_FIRST, array( $this, 'auto_detection_affiliate_link' ), 120, 1 );
		}

		// add_filter( Lasso_Cron::FILTER_FIX_LASSO_BLOCK_IN_GUTENBERG, array( $this, 'fix_shortcode_lasso_amazon_url_causing_amazon_link_issues' ), 10, 1 );
		add_action( Lasso_Cron::FILTER_FIX_LASSO_IMPORT_REVERT_POST_NAME, array( $this, 'fix_import_revert_post_name' ), 10, 2 );
		add_action( Lasso_Cron::FILTER_FIX_ATAG_IN_GENERATE_BLOCKS_PLUGIN, array( $this, 'fix_atag_in_generate_blocks_plugin' ), 10, 2 );

		add_filter( 'get_final_url_domain_bls', array( $this, 'get_final_url_domain_bls' ), 10, 2 );

		if ( Lasso_Helper::is_forminator_plugin_actived() ) {
			add_filter( 'forminator_field_stripe_markup', array( $this, 'fix_forminator_stripe_field_wrong_html' ), 10, 1 );
		}
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Fix href issues
	 *
	 * @param object $a A tag object.
	 * @return object
	 */
	public function fix_href_issues( $a ) {
		$href = $a->href ?? '';

		if ( $href ) {
			// ? wp:paragraph allow '&amp;' instead of '&amp%3B'
			$href = str_replace( '&amp%3B', '&amp;', $href );
			$href = str_replace( ' ', '%20', $href ); // ? Fix link including the space character
			$href = str_replace( 'https:////', 'https://', $href );

			// @codeCoverageIgnoreStart
			// ? Fix conflict with Thrive Editor
			if ( Lasso_Helper::is_thrive_shortcode_pattern_match( $href ) ) {
				$href = str_replace( '%20', ' ', $href );
			}
			// @codeCoverageIgnoreEnd

			// ? Fix link including the space character
			$a->href = $href;

			// ? Fix amazon shortened links -> get final link from cache
			$amazon_shortlink_final_url_cached = Lasso_Amazon_Api::get_shortlink_final_url_cached( $href );
			$a->href                           = $amazon_shortlink_final_url_cached ? $amazon_shortlink_final_url_cached : $a->href;
		}

		// ? Remove no-meaning rel="" out of link.
		$rel = $a->rel ?? '';
		if ( empty( $rel ) ) {
			$a->setAttribute( 'rel', null );
		}

		// ? Remove no-meaning target="" out of link.
		$target = $a->target ?? '';
		if ( empty( $target ) ) {
			$a->setAttribute( 'target', null );
		}

		return $a;
	}

	/**
	 * Fix un-amazon links that replace by the final url and existing in "Pretty Links" plugin
	 *
	 * @param object $a        A tag object.
	 * @param string $site_url Site url.
	 * @return object
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function fix_href_change_to_final_url_that_existed_in_pretty_link( $a, $site_url ) {
		$site_domain   = Lasso_Helper::get_base_domain( $site_url );
		$allow_domains = Lasso_Verbiage::SUPPORT_SITES['fix_link_changed_to_destination_url_issue_by_pretty_link_data'];
		if ( ! Lasso_Helper::is_pretty_link_plugin_active() || ! in_array( $site_domain, $allow_domains, true ) ) {
			return $a;
		}

		$href = $a->href ?? '';

		if ( $href ) {
			$this->get_pretty_link_final_urls();
			$href_without_param = Lasso_Helper::get_url_without_parameters( $href );
			$mapping_key        = Lasso_Process_Pretty_Link_Final_Url::PREFIX_KEY . '_' . md5( $href_without_param );
			if ( isset( $this->pretty_link_final_urls[ $mapping_key ] ) && $this->pretty_link_final_urls[ $mapping_key ] ) {
				$a->href = $this->pretty_link_final_urls[ $mapping_key ];
			}
		}

		return $a;
	}

	/**
	 * Get list final urls from pretty link
	 *
	 * @return array
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function get_pretty_link_final_urls() {
		if ( ! empty( $this->pretty_link_final_urls ) ) {
			return $this->pretty_link_final_urls;
		}

		$sql     = 'SELECT option_name, option_value FROM ' . Model::get_wp_table_name( 'options' ) . " WHERE option_name LIKE '" . Lasso_Process_Pretty_Link_Final_Url::PREFIX_KEY . "_%'";
		$results = Model::get_results( $sql );

		foreach ( $results as $result ) {
			$this->pretty_link_final_urls[ $result->option_name ] = $result->option_value;
		}

		return $this->pretty_link_final_urls;
	}

	/**
	 * Apply fix shorcode Lasso amazon_url causing amazon link issue if current is supported.
	 *
	 * @param string $post_content Post content.
	 * @return string
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function fix_shortcode_lasso_amazon_url_causing_amazon_link_issues( $post_content ) {
		$site_url      = site_url();
		$site_domain   = Lasso_Helper::get_base_domain( $site_url );
		$allow_domains = Lasso_Verbiage::SUPPORT_SITES['fix_shortcode_lasso_amazon_url_causing_amazon_link_issues'];
		if ( ! in_array( $site_domain, $allow_domains, true ) ) {
			return $post_content;
		}

		$post_content = ( new Lasso_Fix() )->fix_shortcode_lasso_amazon_url_causing_amazon_link_issues( $post_content );

		return $post_content;
	}

	/**
	 * Allow URLs of domains send request to BLS
	 *
	 * @param bool   $use_bls Whether use BLS or not.
	 * @param string $url     URL.
	 *
	 * @return bool
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function get_final_url_domain_bls( $use_bls, $url ) {
		$base_domain   = Lasso_Helper::get_base_domain( $url );
		$allow_domains = Lasso_Verbiage::SUPPORT_SITES['get_final_url_domain_bls'];
		if ( in_array( $base_domain, $allow_domains, true ) ) {
			$use_bls = true;
		}

		$affiliate      = new Lasso_Affiliates();
		$affiliate_slug = $affiliate->is_affiliate_link( $url, false );
		if ( $affiliate_slug ) {
			return true;
		}

		return $use_bls;
	}

	/**
	 * Plugin forminator: Fix stripe field wrong html
	 * File path: wp-content/plugins/forminator/library/fields/stripe.php
	 * Code: $html .= sprintf( '<div id="card-element-%s" %s" class="forminator-stripe-element"></div>', $uniqid, $attributes );
	 *
	 * @param string $html Html code.
	 * @return string
	 */
	public function fix_forminator_stripe_field_wrong_html( $html ) {
		$html = str_replace( '"" class="forminator-stripe-element"', '" class="forminator-stripe-element"', $html );

		return $html;
	}

	/**
	 * Fix un-amazon links that replace by the final url and existing in "Pretty Links" plugin
	 *
	 * @param object $a A tag object.
	 * @return object
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function fix_import_revert_href( $a ) {
		$site_url    = site_url();
		$href        = $a->href ?? '';
		$domain      = Lasso_Helper::get_base_domain( $href );
		$site_domain = Lasso_Helper::get_base_domain( $site_url );

		$allow_domains = Lasso_Verbiage::SUPPORT_SITES['fix_import_revert_href'];
		if ( ! in_array( $site_domain, $allow_domains, true ) ) {
			return $a;
		}

		if ( ! $href || ! Lasso_Helper::validate_url( $href ) || $domain !== $site_domain ) {
			return $a;
		}

		$parse         = wp_parse_url( $href );
		$path          = trim( $parse['path'] ?? '', '/' );
		$post_name_tmp = explode( '/', $path );
		$post_name     = end( $post_name_tmp );

		$post = Lasso_DB_Script::get_post_by_post_name( $post_name );

		if ( ! $post ) {
			$post = Lasso_DB_Script::get_post_by_old_slug( $post_name );
		}

		if ( ! $post ) {
			return $a;
		}

		$correct_href = '';

		if ( LASSO_POST_TYPE === $post->post_type ) {
			$lasso_url    = Lasso_Affiliate_Link::get_lasso_url( $post->ID );
			$correct_href = $lasso_url->public_link ?? '';
		} elseif ( Setting_Enum::THIRSTYLINK_SLUG === $post->post_type && class_exists( 'ThirstyAffiliates' ) ) {
			$permalink    = get_permalink( $post->ID );
			$correct_href = $permalink;
		}

		$a->href = '#' !== $correct_href && $correct_href ? $correct_href : $href;

		return $a;
	}

	/**
	 * Fix home url is replaced by another url
	 *
	 * @param object $a A tag object.
	 * @return object
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function fix_home_url_is_replaced( $a ) {
		$site_url         = site_url();
		$href             = $a->href ?? '';
		$is_amazon_link   = Lasso_Amazon_Api::is_amazon_url( $href );
		$link_location_id = $a->getAttribute( 'data-lasso-id' );

		if ( ! $href || ! Lasso_Helper::validate_url( $href ) || ! $is_amazon_link || ! $link_location_id || intval( $link_location_id ) <= 0 ) {
			return $a;
		}

		$link_location = ( new Link_Locations() )->get_one( $link_location_id );
		if ( ! $link_location || ! $link_location->get_id() ) {
			return $a;
		}

		$original_url_raw = $link_location->get_original_link_slug();
		$original_url     = explode( '?', $original_url_raw )[0]; // ? fix home url with URL params
		$original_url     = trim( $original_url, '/' );
		$site_url         = trim( $site_url, '/' );

		if ( $is_amazon_link && $original_url === $site_url ) {
			$a->href = $original_url_raw;
		}

		return $a;
	}

	/**
	 * Revert post_name for wp_posts table
	 *
	 * @param int    $post_id Post ID.
	 * @param string $revert_plugin Revert plugin.
	 */
	public function fix_import_revert_post_name( $post_id, $revert_plugin ) {
		$site_url             = site_url();
		$allow_revert_plugins = array( Setting_Enum::THIRSTYLINK_SLUG );
		if ( $this->is_allow_fix( 'fix_import_revert_post_name', $site_url ) && in_array( $revert_plugin, $allow_revert_plugins, true ) ) {
			$sql          = '
			SELECT *
			FROM ' . ( new Lasso_Revert() )->get_table_name() . '
			WHERE lasso_id = %d AND plugin = %s
		';
			$sql          = Lasso_Revert::prepare( $sql, $post_id, $revert_plugin );
			$lasso_revert = Lasso_Revert::get_row( $sql );
			if ( ! is_null( $lasso_revert ) ) {
				$post_name = null;
				$pieces    = explode( '/', $lasso_revert->old_uri );
				if ( ! empty( $pieces ) ) {
					$pieces    = array_filter(
						$pieces,
						function ( $item ) {
							return '' !== $item;
						}
					);
					$pieces    = array_values( $pieces );
					$post_name = $pieces[ count( $pieces ) - 1 ];
				}
				if ( ! is_null( $post_name ) ) {
					$posts_tbl  = Model::get_wp_table_name( 'posts' );
					$update_sql = '
						UPDATE ' . $posts_tbl . '
						SET post_name = %s
						WHERE id = %d
					';
					$update_sql = Model::prepare( $update_sql, $post_name, $post_id );
					Model::query( $update_sql );
				}
			}
		}
	}

	/**
	 * Check to allow run fix code by domain
	 *
	 * @param string $fct_name Function name.
	 * @param string $url             URL.
	 *
	 * @return bool
	 */
	public function is_allow_fix( $fct_name, $url ) {
		$base_domain   = Lasso_Helper::get_base_domain( $url );
		$allow_domains = Lasso_Verbiage::SUPPORT_SITES[ $fct_name ];
		if ( in_array( $base_domain, $allow_domains, true ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Auto detection affiliate link
	 *
	 * @param string $a The atag object.
	 * @return mixed
	 */
	public function auto_detection_affiliate_link( $a ) {
		$url = $a->href ?? '';
		if ( ! $url ) {
			return $a;
		}

		foreach ( Auto_Monetize::AFFILIATES_CLASSES as $affiliate_class ) {
			$affiliate_class = 'Lasso\\Classes\\Auto_Monetize\\' . $affiliate_class;
			if ( ! class_exists( $affiliate_class ) ) {
				continue;
			}
			$affiliate_object = new $affiliate_class( $url );
			$affiliate_object->map_data();
		}

		return $a;
	}

	/**
	 * Fix a-tag in Generate Blocks plugin.
	 *
	 * @param string $rel Rel attribute value.
	 * @param mixed  $a   The atag object.
	 * @return mixed|string
	 */
	public function fix_atag_in_generate_blocks_plugin( $rel, $a ) {
		$class = $a->class ?? '';

		// ? If generate button have no rel attribute value, we set the default value 'noopener noreferrer' to fix the "Attempt Block Recovery" issue.
		if ( ! $rel && $class && false !== strpos( $class, 'gb-button' ) ) {
			$rel = 'noopener noreferrer';
		}

		return $rel;
	}

	/**
	 * Revert internal link was changed to lasso link having the same permalink.
	 *
	 * @param mixed $a The atag object.
	 * @return mixed
	 */
	public function fix_revert_internal_link_was_changed_to_lasso_link_having_the_same_permalink( $a ) {
		$site_url      = site_url();
		$href          = $a->href ?? '';
		$domain        = Lasso_Helper::get_base_domain( $href );
		$site_domain   = Lasso_Helper::get_base_domain( $site_url );
		$allow_domains = Lasso_Verbiage::SUPPORT_SITES['fix_revert_internal_link_was_changed_to_lasso_link_having_the_same_permalink'];

		if ( ! in_array( $site_domain, $allow_domains, true ) || ! $href || ! Lasso_Helper::validate_url( $href ) || $domain !== $site_domain ) {
			return $a;
		}

		$a_lasso_name = $a->getAttribute( 'data-lasso-name' );
		$a_lasso_id   = $a->getAttribute( 'data-lasso-id' );
		$temp_href    = explode( '?', $href )[0];
		$temp_href    = trim( $temp_href, '/' );
		$parse        = wp_parse_url( $temp_href );

		// ? If the lasso name or lasso id is empty or the href is not a valid url, we return the a tag.
		if ( ! $a_lasso_name || ! $a_lasso_id || ! isset( $parse['path'] ) ) {
			return $a;
		}

		$path    = $parse['path'];
		$path    = trim( $path, '/' );
		$explode = explode( '/', $path );
		$slug    = end( $explode );

		// ? This is the special case, the customer changed the permalink of the lasso post from "ring-deurbel" to "ring-deurbe" => we need to change it back to "ring-deurbel".
		if ( 'ring-deurbe' === $slug && Lasso_DB::get_wp_post_id_by_slug( 'ring-deurbel' ) ) {
			$a->href = $site_url . '/ring-deurbel/';
			return $a;
		}

		// ? We should cache the query results because there are many links in a process.
		$sql = '
			SELECT post_name
			FROM ' . Model::get_wp_table_name( 'posts' ) . "
			WHERE post_type IN (%s, 'page', 'post')
				AND post_status IN ('publish', 'draft')
				AND post_name <> ''
			GROUP BY post_name
			HAVING COUNT(post_name) > 1
		";

		$prepare                = Model::prepare( $sql, LASSO_POST_TYPE );
		$permalinks_was_changed = Model::get_col( $prepare, true );

		if ( in_array( $slug, $permalinks_was_changed, true ) && Lasso_DB::get_wp_post_id_by_slug( $slug ) ) {
			$a->href = "$site_url/$slug/";
		}

		return $a;
	}

	/**
	 * Fix amz- prefix in the URL.
	 *
	 * @param mixed $a The atag object.
	 * @return mixed
	 */
	public function fix_amz_prefix_in_url( $a ) {
		$site_url      = site_url();
		$href          = $a->href ?? '';
		$domain        = Lasso_Helper::get_base_domain( $href );
		$site_domain   = Lasso_Helper::get_base_domain( $site_url );
		$allow_domains = Lasso_Verbiage::SUPPORT_SITES['fix_amz_prefix_incorrect'];

		if ( ! in_array( $site_domain, $allow_domains, true ) || ! $href || ! Lasso_Helper::validate_url( $href ) || $domain !== $site_domain ) {
			return $a;
		}

		$temp_href = explode( '?', $href )[0];
		$temp_href = trim( $temp_href, '/' );
		$parse     = wp_parse_url( $temp_href );

		// ? If the lasso name or lasso id is empty or the href is not a valid url, we return the a tag.
		if ( ! isset( $parse['path'] ) ) {
			return $a;
		}

		$path    = $parse['path'];
		$path    = trim( $path, '/' );
		$explode = explode( '/', $path );
		$slug    = end( $explode );

		// ? We should cache the query results because there are many links in a process.
		$sql = '
			SELECT ID, post_name
			FROM ' . Model::get_wp_table_name( 'posts' ) . "
			WHERE post_type = %s
				AND post_status = 'publish'
				AND post_name = %s
		";

		$prepare = Model::prepare( $sql, LASSO_POST_TYPE, 'amzn-' . $slug );
		$result  = Model::get_row( $prepare, 'OBJECT', true );

		if ( $result ) {
			$post_name = $result->post_name;
			$a->href   = str_replace( $slug, $post_name, $href );
		}

		return $a;
	}
}
