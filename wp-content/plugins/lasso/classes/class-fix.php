<?php
/**
 * Declare class Fix
 *
 * @package Fix
 */

namespace Lasso\Classes;

use Lasso_Amazon_Api;
use Lasso_DB;

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Keyword as Lasso_Keyword;
use Lasso\Classes\Verbiage as Lasso_Verbiage;

use Lasso\Models\Model;

/**
 * Fix
 * This class is created for fixing the issues in the cron.
 */
class Fix {

	/**
	 * Bring back the origin href before delete data-old-href attribute
	 * We want to fix issue the origin affiliate link has been changed to destination url but destination is not amazon link.
	 * This function should be remove after customer fixed the data.
	 *
	 * @param object $a       A tag object.
	 * @param string $site_url Site url.
	 * @return object
	 */
	public function fix_origin_url_has_been_changed_to_destination_url_issue( $a, $site_url ) {
		// ? Apply only for site: www.besserklettern.com
		$site_domain = Lasso_Helper::get_base_domain( $site_url );
		if ( ! in_array( $site_domain, Lasso_Verbiage::SUPPORT_SITES['fix_origin_url_has_been_changed_to_destination_url_issue'], true ) ) {
			return $a;
		}

		$href          = $a->href ?? '';
		$data_old_href = $a->getAttribute( 'data-old-href' );

		if ( $href && $data_old_href && ( $href !== $data_old_href ) && ! Lasso_Amazon_Api::is_amazon_url( $href ) ) {
			$a->href = $data_old_href;
		}

		// ? convert https://www.bergzeit.de to https://www.awin1.com/cread.php
		$href = $a->href ?? '';
		if ( $href && strpos( $href, 'https://www.bergzeit.' ) === 0 ) {
			$awin_href  = urldecode( $href );
			$awin_href  = htmlspecialchars_decode( $awin_href );
			$awin_href  = str_replace( ' ', '+', $awin_href );
			$utm_term   = Lasso_Helper::get_argument_from_url( $awin_href, 'utm_term' );
			$utm_source = Lasso_Helper::get_argument_from_url( $awin_href, 'utm_source' );

			$awin_href_without_query = explode( '?', $awin_href )[0] ?? '';
			$awin_href               = rawurlencode( $awin_href_without_query );
			$awin_url                = 'https://www.awin1.com/cread.php?awinmid=12557&amp;awinaffid=601639&amp;clickref=' . $utm_term . '&amp;ued=' . $awin_href;

			// ? only update href if href contains utm_term
			if ( $utm_source && 'awin' === $utm_source && $awin_href_without_query && $awin_href ) {
				$a->href = $awin_url;
			}
		}

		return $a;
	}

	/**
	 * Fix the multi ASINs issue
	 */
	public static function fix_multi_asins_issue() {
		$lasso_db         = new Lasso_DB();
		$lasso_amazon_api = new Lasso_Amazon_Api();

		$wrong_links = $lasso_db->get_wrong_amazon_links();
		if ( ! empty( $wrong_links ) ) {
			foreach ( $wrong_links as $link ) {
				$url    = $lasso_amazon_api->get_amazon_link_by_product_id( $link->amazon_id, $link->base_url );
				$url    = Lasso_Amazon_Api::get_amazon_product_url( $url, false );
				$m_link = Lasso_Amazon_Api::get_amazon_product_url( $url );

				// ? only update data if the url is amazon url and not is shortened url and can get product id
				if ( Lasso_Amazon_Api::is_amazon_url( $url )
					&& ! Lasso_Amazon_Api::is_amazon_shortened_url( $url )
					&& Lasso_Amazon_Api::get_product_id_by_url( $url )
				) {
					$link->product_id  = $link->amazon_id;
					$link->default_url = $url;
					$link->url         = $m_link;
					$link->title       = $link->default_product_name;
					$link->price       = $link->latest_price;
					$link->image       = $link->default_image;
					$link->quantity    = 0 === intval( $link->out_of_stock ) ? 200 : 0;
					$lasso_amazon_api->update_amazon_product_in_db( (array) $link );
				}
			}
		}
	}

	/**
	 * Fix empty details
	 */
	public static function fix_empty_details() {
		$lasso_db         = new Lasso_DB();
		$lasso_amazon_api = new Lasso_Amazon_Api();

		$wrong_links = $lasso_db->get_empty_details_links();
		if ( ! empty( $wrong_links ) ) {
			foreach ( $wrong_links as $link ) {
				$amazon_id      = get_post_meta( $link->ID, 'amazon_product_id', true );
				$amazon_url     = Lasso_Amazon_Api::get_default_product_domain( $amazon_id );
				$amazon_product = $lasso_amazon_api->get_amazon_product_from_db( $amazon_id, $amazon_url );
				if ( $amazon_product ) {
					$lasso_db->update_url_details(
						$link->ID,
						$amazon_product['monetized_url'],
						Lasso_Helper::get_base_domain( $amazon_product['base_url'] ),
						0,
						$amazon_id,
						Lasso_Amazon_Api::PRODUCT_TYPE
					);
				}
			}
		}
	}

	/**
	 * Fix empty features in amazon products table
	 */
	public static function fix_amazon_features() {
		$lasso_db         = new Lasso_DB();
		$lasso_amazon_api = new Lasso_Amazon_Api();

		$wrong_links = $lasso_db->get_amazon_empty_features();
		if ( ! empty( $wrong_links ) ) {
			foreach ( $wrong_links as $link ) {
				$product       = $lasso_amazon_api->get_amazon_product_from_db( $link->asin );
				$aawp_features = maybe_unserialize( $link->aawp_features );

				$product_data = array(
					'product_id'      => $product['amazon_id'],
					'title'           => $product['default_product_name'],
					'price'           => $product['latest_price'],
					'default_url'     => $product['base_url'],
					'url'             => $product['monetized_url'],
					'image'           => $product['default_image'],
					'quantity'        => '0' === $product['out_of_stock'] ? 200 : 0,
					'is_prime'        => $product['is_prime'],
					'currency'        => $product['currency'],
					'savings_amount'  => $product['savings_amount'],
					'savings_percent' => $product['savings_percent'],
					'savings_basis'   => $product['savings_basis'],
					'features'        => $aawp_features,
					'is_manual'       => 1,
				);

				$lasso_amazon_api->update_amazon_product_in_db( $product_data, $product['last_updated'] );
			}
		}

		// ? fix amazon product that has incorrect format of features in the amazon table
		$products = $lasso_db->get_amazon_incorrect_format_features();
		if ( ! empty( $products ) ) {
			foreach ( $products as $product ) {
				$product = (array) $product;

				$product_data = array(
					'product_id'      => $product['amazon_id'],
					'title'           => $product['default_product_name'],
					'price'           => $product['latest_price'],
					'default_url'     => $product['base_url'],
					'url'             => $product['monetized_url'],
					'image'           => $product['default_image'],
					'quantity'        => '0' === $product['out_of_stock'] ? 200 : 0,
					'is_prime'        => $product['is_prime'],
					'currency'        => $product['currency'],
					'savings_amount'  => $product['savings_amount'],
					'savings_percent' => $product['savings_percent'],
					'savings_basis'   => $product['savings_basis'],
					'features'        => maybe_unserialize( $product['features'] ),
					'is_manual'       => 1,
				);

				$lasso_amazon_api->update_amazon_product_in_db( $product_data, $product['last_updated'] );
			}
		}
	}

	/**
	 * Fix invalid keyword and delete from "lasso keyword location" table
	 * For example:
	 * Invalid keyword content: <span>The bi<keyword data-keyword-id="12345">cycle</keyword> shop</span>
	 * Fix to:                  <span>The bicycle shop</span>
	 *
	 * @param string $post_content Post content.
	 * @return string
	 */
	public function fix_invalid_keyword_in_post_content( $post_content ) {
		$old_post_content    = $post_content;
		$incorrect_tag_regex = '/([a-zA-Z0-9])(<keyword\sdata-keyword-id=\")(\d*)(\">)([^><\[\]]+)(<\/keyword>)/i';
		$post_content        = preg_replace_callback( $incorrect_tag_regex, array( $this, 'replace_invalid_keyword_and_delete_location_keyword' ), $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		}

		return $post_content;
	}

	/**
	 * Return the fix content for invalid keywords and delete invalid keyword location id from database.
	 *
	 * @param  array $matches Matches parameters.
	 * @return string Correct fix content.
	 */
	private function replace_invalid_keyword_and_delete_location_keyword( $matches ) {
		$keyword_location_id = $matches[3];
		Lasso_Keyword::delete_keyword_location( $keyword_location_id );

		return $matches[1] . $matches[5];
	}

	/**
	 * Fix amazon link get issue cause replacing raw amazon link to Lasso amazon_url shortcode
	 *
	 * @param string $post_content Post content.
	 * @return string
	 */
	public function fix_shortcode_lasso_amazon_url_causing_amazon_link_issues( $post_content ) {
		// ? Fix: the regex find raw amazon link below effect to amazon links inside Lasso gutenberg json attribute. We should fix the wrong content back to the correct.
		// Case: Scan Gutenberg wrong content from second time and the next time, the content would change like:
		// =\u002[lasso amazon_url="  => =\u002[lasso amazon_url=\u0022
		// \u0022"]                   => Change to: \u0022\u0022]
		$old_post_content = $post_content;
		$post_content     = str_replace( '[lasso amazon_url=\u0022', '[lasso amazon_url="', $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		$post_content = str_replace( '\u0022\u0022]', '\u0022"]', $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		$incorrect_tag_regex = '/(\slink_id\=\\\u0022([0-9]*)\\\u0022\])(link_id\=\\\u0022([0-9]*)\\\u0022\])+/i';
		$correct_tag         = '"]';
		$post_content        = preg_replace( $incorrect_tag_regex, $correct_tag, $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		$incorrect_tag_regex = '/(\=\\\u002\[lasso\samazon_url\=\")(https:\/\/www.amazon.([a-zA-z0-9\/\-\=\?\&\;]*))(\\\u0022\slink_id\=\\\u0022([0-9]*)\\\u0022\])/i';
		$correct_tag         = '=\u0022$2\u0022 ';
		$post_content        = preg_replace( $incorrect_tag_regex, $correct_tag, $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		$incorrect_tag_regex = '/(\=\\\u002\[lasso\samazon_url\=\")(https:\/\/www.amazon.([a-zA-z0-9\/\-\=\?\&\;]*))(\\\u0022\"\])/i';
		$correct_tag         = '=\u0022$2\u0022 ';
		$post_content        = preg_replace( $incorrect_tag_regex, $correct_tag, $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		/*
		 * Fix for the site "theadultman.com" in the issue "Changing a Lasso link removes blocks from Rank Math and Block Lab #2343"
		 * Return the correct json format if having the lasso amazon_url shortcode.
		 *
		 * + Example:
		 *  <!-- wp:block-lab/standout {"standout-description":"If ... href=\u002[lasso amazon_url="https://www.amazon.com/dp/0801886562?tag=theadultman01-20\u0026linkCode=ogi\u0026th=1\u0026psc=1\u0022 id=\u002235836\u0022 ref=\u0022chimpanzee-politics-power-and-sex-among-apes\u0022 link_id=\u0022229724\u0022]id=\u002235836\u0022 ref=\u0022chimpanzee-politics-power-and-sex-among-apes\u0022 link_id=\u0022229584\u0022]target=\u0022_blank\u0022...
		 * + Return to:
		 * <!-- wp:block-lab/standout {"standout-description":"If ... href=\u0022https://www.amazon.com/dp/0801886562?tag=theadultman01-20\u0026linkCode=ogi\u0026th=1\u0026psc=1\u0022 target=\u0022_blank\u0022...
		*/
		$incorrect_tag_regex = '/(href\=)(\\\u002\[lasso\samazon_url\=\")([^\s]*)(\\\u0022\s)((?!\shref\=\\\u002).)*(\\\u0022\]target\=)/i';
		$post_content        = preg_replace_callback( $incorrect_tag_regex, array( $this, 'replace_lasso_amazon_inside_shortcode_json' ), $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		/**
		 * Fix rank math faq block
		 * From: \u003ca href=\u002[lasso amazon_url="https://www.amazon.com/dp/B000QYKK3Wcharismatic/a" link_id="235269"]
		 * To: \u003ca href=\u0022https://www.amazon.com/dp/B000QYKK3Wcharismatic\u0022\u003echarismatic\u003c/a\u003e
		 */
		$incorrect_tag_regex = '/(\\\u003ca href=\\\u002\[lasso amazon_url\=\")([^\s]*)(\/a")(.*)("])/i';
		$post_content        = preg_replace_callback( $incorrect_tag_regex, array( $this, 'fix_rank_math_faq_block' ), $post_content );
		if ( is_null( $post_content ) || empty( $post_content ) ) {
			$post_content = $old_post_content;
		} else {
			$old_post_content = $post_content;
		}

		return $post_content;
	}

	/**
	 * Return the correct json format if having the lasso amazon_url shortcode.
	 *
	 * @param  array $matches Matches parameters.
	 * @return string
	 */
	private function replace_lasso_amazon_inside_shortcode_json( $matches ) {
		$amazon_link = $matches[3];
		return 'href=\u0022' . $amazon_link . '\u0022 target=';
	}

	/**
	 * Return the correct json format if having the lasso amazon_url shortcode.
	 *
	 * @param  array $matches Matches parameters.
	 * @return string
	 */
	private function fix_rank_math_faq_block( $matches ) {
		$amazon_link = $matches[2];
		$attrs       = str_replace( '"', '\u0022', $matches[4] ) . '\u0022';
		$tmp         = preg_replace( '/\/dp\/([A-Z0-9]{10})/', 'lasso_split$2', $amazon_link );
		$anchor_text = explode( 'lasso_split', $tmp )[1] ?? '';

		return '\u003ca href=\u0022' . $amazon_link . '\u0022' . $attrs . ' \u003e' . $anchor_text . '\u003c/a\u003e';
	}
}
