<?php

/**
 * Declare class Lasso_Affiliate_Link
 *
 * @package Lasso_Affiliate_Link
 */

use Lasso\Classes\Affiliates as Lasso_Affiliates;
use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;
use Lasso\Classes\Category_Order as Lasso_Category_Order;
use Lasso\Classes\Extend_Product as Lasso_Extend_Product;
use Lasso\Classes\Group as Lasso_Group;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Keyword as Lasso_Keyword;
use Lasso\Classes\Link_Location as Lasso_Link_Location;
use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Classes\Setting_Enum;

use Lasso\Models\Affiliate_Programs;
use Lasso\Models\Auto_Monetize;
use Lasso\Models\Field_Mapping;
use Lasso\Models\Model;
use Lasso\Models\Url_Details;

/**
 * Lasso_Affiliate_Link
 */
class Lasso_Affiliate_Link
{
	const DEFAULT_AMAZON_NAME          = 'Amazon';
	const DEFAULT_TITLE                = 'Add a Link Title';
	const ADD_NEW_LINK_RESPONSE_STATUS = 'lasso_add_new_link_response_status';

	/**
	 * Edit detail page
	 *
	 * @var string $edit_details_page
	 */
	public static $edit_details_page = 'url-details';

	/**
	 * Loaded lasso urls
	 *
	 * @var array $loaded_lasso_urls
	 */
	public static $loaded_lasso_urls = array();

	/**
	 * Get Lasso post detail
	 *
	 * @param int  $post_id   Lasso post id.
	 * @param bool $is_detail Is detail page. Default to false.
	 */
	public static function get_lasso_url($post_id, $is_detail = false)
	{
		$post_id = intval($post_id);
		$cache_obj = self::$loaded_lasso_urls[$is_detail ? 'detail' : 'no_detail'][$post_id] ?? null;
		if (Lasso_Cache_Per_Process::get_instance()->get_cache(Lasso_Shortcode::OBJECT_KEY) && $cache_obj) {
			return $cache_obj;
		}

		$lasso_post = Lasso_Cache_Per_Process::get_instance()->get_cache('wp_post_' . $post_id);
		if (false === $lasso_post) {
			$lasso_post = get_post($post_id);
			Lasso_Cache_Per_Process::get_instance()->set_cache('wp_post_' . $post_id, $lasso_post);
		}
		$post_type            = get_post_type($lasso_post);
		$post_status          = get_post_status($lasso_post);
		$post_meta            = get_post_meta($post_id);
		$settings             = Lasso_Setting::lasso_get_settings();
		$lasso_amazon_api     = new Lasso_Amazon_Api();
		$lasso_db             = new Lasso_DB();
		$lasso_extend_product = new Lasso_Extend_Product();

		$enable_amazon_prime          = $settings['enable_amazon_prime'];
		$show_amazon_discount_pricing = $settings['show_amazon_discount_pricing'];

		$lasso_id           = '';
		$edit_link          = '';
		$link_type          = '';
		$name               = $is_detail ? '' : 'The Link Title Goes Here';
		$description        = '';
		$custom_css         = '';
		$slug               = '';
		$guid               = '';
		$permalink          = '#';
		$public_link        = $permalink;
		$image_src          = $settings['default_thumbnail'];
		$image_src_default  = 1;
		$thumbnail_id       = '';
		$target_url         = '';
		$affiliate_homepage = '';
		$custom_theme       = '';
		$price              = $is_detail ? '' : '$199.99';
		$is_amazon_page     = false;
		$is_opportunity     = 1;
		$keyword            = array();
		$category           = array();
		$open_new_tab       = $settings['open_new_tab'];
		$enable_nofollow    = $settings['enable_nofollow'];
		$open_new_tab2      = $settings['open_new_tab2'];
		$enable_nofollow2   = $settings['enable_nofollow2'];
		$enable_sponsored   = $settings['enable_sponsored'];
		$link_cloaking      = true;
		$description        = $is_detail ? '' : '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi auctor suscipit magna pretium sodales. Vestibulum eu lorem vitae diam ullamcorper viverra in vitae nunc. Duis non risus urna.</p>';
		$currency           = 'USD';

		$display_primary_button_text_default   = $settings['primary_button_text'];
		$display_primary_button_text           = $display_primary_button_text_default;
		$display_secondary_button_text_default = $settings['secondary_button_text'];
		$display_secondary_button_text         = $display_secondary_button_text_default;

		$display_third_button_text_default = $settings['secondary_button_text'];
		$display_third_button_text         = $display_third_button_text_default;

		$display_fourth_button_text_default = $settings['secondary_button_text'];
		$display_fourth_button_text         = $display_fourth_button_text_default;

		$display_apple_button_text_default = 'Get on App Store';
		$display_apple_button_text         = $display_apple_button_text_default;

		$display_google_button_text_default = 'Get on CH Play';
		$display_google_button_text         = $display_google_button_text_default;

		$display_secondary_url                 = '';
		$display_disclosure_text               = $settings['disclosure_text'];
		$display_disclosure_text_default       = $settings['disclosure_text'];
		$display_badge_text                    = $settings['badge_text'];
		$display_show_price                    = $settings['show_price'];
		$display_show_disclosure               = self::get_toggle_value($post_meta, 'show_disclosure');
		$display_last_updated                  = Lasso_Helper::convert_datetime_format(gmdate('Y-m-d H:i:s'), true);
		$display_show_description              = get_post_meta($post_id, 'show_description', true);
		$display_show_description              = '' === $display_show_description ? 1 : $display_show_description; // ? post doesn't not show_description meta data value will show description by default

		$issue_out_of_stock = false;
		$issue_broken       = false;

		$amazon_product_id            = '';
		$amazon_default_product_name  = '';
		$amazon_latest_price          = '';
		$amazon_base_url              = '';
		$amazon_monetized_url         = '';
		$amazon_default_image         = '';
		$amazon_last_updated          = '';
		$amazon_discount_pricing_html = '';
		$amazon_rating                = 0;
		$amazon_reviews               = 0;
		$amazon_savings_basis         = '';
		$amazon_savings_amount        = '';
		$amazon_savings_percent       = '';

		$ext_product_type         = '';
		$ext_product_id           = '';
		$ext_default_product_name = '';
		$ext_latest_price         = '';
		$ext_base_url             = '';
		$ext_default_image        = '';
		$ext_last_updated         = '';

		$rating = get_post_meta($post_id, 'rating', true);
		$categories = get_post_meta($post_id, 'categories', true);
		$developer = get_post_meta($post_id, 'developer', true);
		$size = get_post_meta($post_id, 'size', true);
		$version = get_post_meta($post_id, 'version', true);
		$screen_shots = get_post_meta($post_id, 'screen_shots', true);
		$apple_btn_url = get_post_meta($post_id, 'apple_btn_url', true);
		$google_btn_url = get_post_meta($post_id, 'google_btn_url', true);
		$updated_on = get_post_meta($post_id, 'updated_on', true);

		$open_new_tab3 = get_post_meta($post_id, 'open_new_tab3', true);
		$open_new_tab3 = 1 === intval($open_new_tab3) ? true : false;

		$enable_nofollow3 = get_post_meta($post_id, 'enable_nofollow3', true);
		$enable_nofollow3 = 1 === intval($enable_nofollow2) ? true : false;

		$open_new_tab4 = get_post_meta($post_id, 'open_new_tab4', true);
		$open_new_tab4 = 1 === intval($open_new_tab4) ? true : false;

		$enable_nofollow4 = get_post_meta($post_id, 'enable_nofollow4', true);
		$enable_nofollow4 = 1 === intval($enable_nofollow2) ? true : false;

		$open_new_tab_google = get_post_meta($post_id, 'open_new_tab_google', true);
		$open_new_tab_google = 1 === intval($open_new_tab_google) ? true : false;

		$enable_nofollow_google = get_post_meta($post_id, 'enable_nofollow_google', true);
		$enable_nofollow_google = 1 === intval($enable_nofollow_google) ? true : false;

		$open_new_tab_apple = get_post_meta($post_id, 'open_new_tab_apple', true);
		$open_new_tab_apple = 1 === intval($open_new_tab_apple) ? true : false;

		$enable_nofollow_apple = get_post_meta($post_id, 'enable_nofollow_apple', true);
		$enable_nofollow_apple = 1 === intval($enable_nofollow_apple) ? true : false;

		$display_third_url = trim(get_post_meta($post_id, 'third_btn_url', true));
		$display_fourth_url = trim(get_post_meta($post_id, 'fourth_btn_url', true));

		if ($post_id > 0 && LASSO_POST_TYPE === $post_type && 'publish' === $post_status && $lasso_post) {
			$lasso_post_details = $lasso_db->get_url_details($post_id);

			$target_url     = $lasso_post_details->redirect_url ?? '';
			$is_opportunity = $lasso_post_details->is_opportunity ?? 1;
			$final_url      = get_post_meta($post_id, 'lasso_final_url', true);

			$link_type = (Lasso_Amazon_Api::is_amazon_url($target_url) && Lasso_Amazon_Api::get_product_id_by_url($target_url))
				|| ($final_url && Lasso_Amazon_Api::is_amazon_url($final_url) && Lasso_Amazon_Api::get_product_id_by_url($final_url))
				? LASSO_AMAZON_PRODUCT_TYPE : Lasso_Link_Location::LINK_TYPE_LASSO;

			$custom_theme                = get_post_meta($post_id, 'custom_theme', true);
			$display_primary_button_text = get_post_meta($post_id, 'buy_btn_text', true);
			$display_primary_button_text = '' === $display_primary_button_text ? $display_primary_button_text_default : $display_primary_button_text;

			$display_secondary_button_text = get_post_meta($post_id, 'second_btn_text', true);
			$display_secondary_button_text = '' === $display_secondary_button_text ? $display_secondary_button_text_default : $display_secondary_button_text;

			$display_third_button_text = get_post_meta($post_id, 'third_btn_text', true);
			$display_third_button_text = '' === $display_third_button_text ? $display_third_button_text_default : $display_third_button_text;

			$display_fourth_button_text = get_post_meta($post_id, 'fourth_btn_text', true);
			$display_fourth_button_text = '' === $display_fourth_button_text ? $display_fourth_button_text_default : $display_fourth_button_text;

			$display_google_button_text = get_post_meta($post_id, 'google_btn_text', true);
			$display_google_button_text = '' === $display_google_button_text ? $display_google_button_text_default : $display_google_button_text;

			$display_apple_button_text = get_post_meta($post_id, 'apple_btn_text', true);
			$display_apple_button_text = '' === $display_apple_button_text ? $display_apple_button_text_default : $display_apple_button_text;

			$display_secondary_url = trim(get_post_meta($post_id, 'second_btn_url', true));
			$display_third_url = trim(get_post_meta($post_id, 'third_btn_url', true));
			$display_fourth_url = trim(get_post_meta($post_id, 'fourth_btn_url', true));

			$display_disclosure_text = get_post_meta($post_id, 'disclosure_text', true);
			$display_disclosure_text = '' === trim($display_disclosure_text) ? $settings['disclosure_text'] : $display_disclosure_text;

			$display_badge_text = get_post_meta($post_id, 'badge_text', true);
			$display_badge_text = '' === $display_badge_text ? '' : $display_badge_text;

			$affiliate_homepage = $lasso_post_details->base_domain ?? '';
			$ud_product_id      = $lasso_post_details->product_id ?? '';
			$ud_product_type    = $lasso_post_details->product_type ?? '';
			$custom_css         = get_post_meta($post_id, 'custom_css', true);

			$is_amazon_page = Lasso_Amazon_Api::PRODUCT_TYPE === $ud_product_type && !$ud_product_id ? true : false;

			$open_new_tab = get_post_meta($post_id, 'open_new_tab', true);
			$open_new_tab = 1 === intval($open_new_tab) ? true : false;

			$enable_nofollow = get_post_meta($post_id, 'enable_nofollow', true);
			$enable_nofollow = 1 === intval($enable_nofollow) ? true : false;

			$enable_sponsored = get_post_meta($post_id, 'enable_sponsored', true);
			$enable_sponsored = 1 === intval($enable_sponsored) ? true : false;

			$rating = get_post_meta($post_id, 'rating', true);
			$categories = get_post_meta($post_id, 'categories', true);
			$developer = get_post_meta($post_id, 'developer', true);
			$size = get_post_meta($post_id, 'size', true);
			$version = get_post_meta($post_id, 'version', true);
			$screen_shots = get_post_meta($post_id, 'screen_shots', true);
			$apple_btn_url = get_post_meta($post_id, 'apple_btn_url', true);
			$google_btn_url = get_post_meta($post_id, 'google_btn_url', true);

			$open_new_tab2 = get_post_meta($post_id, 'open_new_tab2', true);
			$open_new_tab2 = 1 === intval($open_new_tab2) ? true : false;

			$enable_nofollow2 = get_post_meta($post_id, 'enable_nofollow2', true);
			$enable_nofollow2 = 1 === intval($enable_nofollow2) ? true : false;

			$open_new_tab3 = get_post_meta($post_id, 'open_new_tab3', true);
			$open_new_tab3 = 1 === intval($open_new_tab3) ? true : false;

			$enable_nofollow3 = get_post_meta($post_id, 'enable_nofollow3', true);
			$enable_nofollow3 = 1 === intval($enable_nofollow2) ? true : false;

			$open_new_tab4 = get_post_meta($post_id, 'open_new_tab4', true);
			$open_new_tab4 = 1 === intval($open_new_tab4) ? true : false;

			$enable_nofollow4 = get_post_meta($post_id, 'enable_nofollow4', true);
			$enable_nofollow4 = 1 === intval($enable_nofollow2) ? true : false;

			$open_new_tab_google = get_post_meta($post_id, 'open_new_tab_google', true);
			$open_new_tab_google = 1 === intval($open_new_tab_google) ? true : false;

			$enable_nofollow_google = get_post_meta($post_id, 'enable_nofollow_google', true);
			$enable_nofollow_google = 1 === intval($enable_nofollow_google) ? true : false;

			$open_new_tab_apple = get_post_meta($post_id, 'open_new_tab_apple', true);
			$open_new_tab_apple = 1 === intval($open_new_tab_apple) ? true : false;

			$enable_nofollow_apple = get_post_meta($post_id, 'enable_nofollow_apple', true);
			$enable_nofollow_apple = 1 === intval($enable_nofollow_apple) ? true : false;

			$link_cloaking = get_post_meta($post_id, 'link_cloaking', true);
			$link_cloaking = 1 === intval($link_cloaking) || '' === $link_cloaking ? true : false;

			$default_show_price = $settings['show_price'] ? 1 : 0;
			$display_show_price = get_post_meta($post_id, 'show_price', true);
			$display_show_price = '' === $display_show_price ? $default_show_price : $display_show_price;
			$display_show_price = 1 === intval($display_show_price) ? true : false;

			$description = get_post_meta($post_id, 'affiliate_desc', true);
			if (strlen($description) > 10) {
				$display_show_description = true;
			}

			$price                = get_post_meta($post_id, 'price', true);
			$display_last_updated = '';

			$amazon_product_id = $ud_product_id && Lasso_Amazon_Api::PRODUCT_TYPE === $ud_product_type ? $ud_product_id : '';
			$amazon_product    = $lasso_amazon_api->get_amazon_product_by_id($post_id, $amazon_product_id);

			if (LASSO_AMAZON_PRODUCT_TYPE === $link_type && $amazon_product) {
				$description = Lasso_Helper::is_description_empty($description) ? $amazon_product['description'] : $description;
				$description = $display_show_description ? $description : '';

				$target_url = Lasso_Amazon_Api::get_amazon_product_url($amazon_product['monetized_url']);
				$price      = $amazon_product['price'];

				$amazon_product_id            = $amazon_product['id'];
				$amazon_default_product_name  = $amazon_product['name'];
				$amazon_latest_price          = $amazon_product['price'];
				$amazon_base_url              = $amazon_product['url'];
				$amazon_monetized_url         = $amazon_product['monetized_url'];
				$amazon_default_image         = $amazon_product['image'];
				$amazon_last_updated          = $amazon_product['last_updated'];
				$display_last_updated         = gmdate('m/d/Y h:i a T', strtotime($amazon_product['last_updated']));
				$issue_out_of_stock           = 1 === intval($amazon_product['out_of_stock']);
				$amazon_discount_pricing_html = Lasso_Amazon_Api::build_discount_pricing_html($amazon_latest_price, $amazon_product['savings_basis'], $amazon_product['currency']);
				$amazon_rating                = $amazon_product['rating'];
				$amazon_reviews               = $amazon_product['reviews'];
				$amazon_savings_basis         = $amazon_product['savings_basis'] && $amazon_product['currency'] ? Lasso_Amazon_Api::format_price($amazon_product['savings_basis'], $amazon_product['currency']) : '';
				$amazon_savings_amount        = $amazon_product['savings_amount'] && $amazon_product['currency'] ? Lasso_Amazon_Api::format_price($amazon_product['savings_amount'], $amazon_product['currency']) : '';
				$amazon_savings_percent       = $amazon_product['savings_percent'] ? $amazon_product['savings_percent'] . '%' : '';
				$currency                     = $amazon_product['currency'];
			}

			$extend_product = $lasso_extend_product->get_extend_product_by_id($ud_product_type, $ud_product_id);
			if ($extend_product) {
				$price                    = empty($price) ? $extend_product['price'] : $price; // ? Use default price if price from post meta is empty
				$ext_product_type         = $extend_product['product_type'];
				$ext_product_id           = $extend_product['product_id'];
				$ext_default_product_name = $extend_product['name'];
				$ext_latest_price         = $extend_product['price'];
				$ext_base_url             = $extend_product['url'];
				$ext_default_image        = $extend_product['image'];
				$ext_last_updated         = $extend_product['last_updated'];
				$display_last_updated     = gmdate('m/d/Y h:i a T', strtotime($extend_product['last_updated']));
				$issue_out_of_stock       = 1 === intval($extend_product['out_of_stock']);
			}

			if (empty(get_post_meta($post_id, 'buy_btn_text', true)) && $lasso_db->is_imported_from_aawp($amazon_product_id, $post_id)) {
				$aawp_options = get_option('aawp_output');
				$button_text  = $aawp_options['button_text'] ?? '';
				$button_text  = $button_text ? $button_text : $display_primary_button_text;

				$display_primary_button_text = $button_text;
			}

			$url_issues = $lasso_db->get_url_issue_list($post_id);
			if ($url_issues) {
				foreach ($url_issues as $issue) {
					if ('000' === (string) $issue->issue_type) {
						$issue_out_of_stock = true;
					} else {
						$issue_broken = true;
					}
				}
			}

			$lasso_id          = $post_id;
			$edit_link         = self::affiliate_edit_link($lasso_id);
			$name              = Lasso_Helper::format_post_title($lasso_post->post_title);
			$slug              = $lasso_post->post_name;
			$guid              = $lasso_post->guid;
			$permalink         = get_post_permalink($lasso_id);
			$image_src         = self::get_lasso_thumbnail($lasso_id, $link_type, $amazon_product);
			$image_src_default = ($image_src === $settings['default_thumbnail']) ? 1 : 0;
			$thumbnail_id      = self::get_lasso_thumbnail_id($lasso_id, $link_type);
			$keyword           = Lasso_Keyword::get_keywords($lasso_id);
			$category          = wp_get_post_terms($lasso_id, LASSO_CATEGORY, array('fields' => 'ids'));
			$category          = is_array($category) ? $category : array();

			if (!empty($amazon_default_product_name) && 'Amazon' === $name) {
				$name = $amazon_default_product_name;
				wp_update_post(
					array(
						'ID'         => $post_id,
						'post_title' => $name,
					)
				);
			}
		}

		$description = str_replace('<p></p>', '', $description);

		$url_detail_checkbox_show_price       = $display_show_price ? 'checked' : '';
		$url_detail_checkbox_show_disclosure  = $display_show_disclosure ? 'checked' : '';
		$url_detail_checkbox_open_new_tab     = $open_new_tab ? 'checked' : '';
		$url_detail_checkbox_enable_nofollow  = $enable_nofollow ? 'checked' : '';
		$url_detail_checkbox_open_new_tab2    = $open_new_tab2 ? 'checked' : '';
		$url_detail_checkbox_enable_nofollow2 = $enable_nofollow2 ? 'checked' : '';

		$url_detail_checkbox_open_new_tab3    = $open_new_tab3 ? 'checked' : '';
		$url_detail_checkbox_enable_nofollow3 = $enable_nofollow3 ? 'checked' : '';

		$url_detail_checkbox_open_new_tab4    = $open_new_tab4 ? 'checked' : '';
		$url_detail_checkbox_enable_nofollow4 = $enable_nofollow4 ? 'checked' : '';

		$url_detail_checkbox_open_new_tab_google    = $open_new_tab_google ? 'checked' : '';
		$url_detail_checkbox_enable_nofollow_google = $enable_nofollow_google ? 'checked' : '';

		$url_detail_checkbox_open_new_tab_apple    = $open_new_tab_apple ? 'checked' : '';
		$url_detail_checkbox_enable_nofollow_apple = $enable_nofollow_apple ? 'checked' : '';

		$url_detail_checkbox_is_opportunity   = $is_opportunity ? 'checked' : '';
		$url_detail_checkbox_link_cloaking    = $link_cloaking ? 'checked' : '';
		$url_detail_checkbox_enable_sponsored = $enable_sponsored ? 'checked' : '';

		$rel = $enable_nofollow ? 'nofollow' : '';
		$rel = $open_new_tab ? trim($rel . ' noopener') : $rel;
		if (!empty($enable_sponsored)) {
			$rel .= !empty($rel) ? ' sponsored' : 'sponsored';
		}

		$rel2 = $enable_nofollow2 ? 'nofollow' : '';
		$rel2 = $open_new_tab2 ? trim($rel2 . ' noopener') : $rel2;
		if (!empty($enable_sponsored) && $enable_nofollow2) {
			$rel2 .= !empty($rel2) ? ' sponsored' : 'sponsored';
		}

		$html_attribute_rel     = 'rel="' . $rel . '"';
		$html_attribute_target  = $open_new_tab ? '_blank' : '_self';
		$html_attribute_rel2    = 'rel="' . $rel2 . '"';
		$html_attribute_target2 = $open_new_tab2 ? '_blank' : '_self';

		if (
			Lasso_Amazon_Api::is_amazon_url($target_url)
			|| (!Lasso_Amazon_Api::is_amazon_url($target_url) && !$link_cloaking)
		) {
			$public_link = $target_url;
		} else {
			$public_link = $permalink;
		}

		$lasso_custom_redirect = get_post_meta($lasso_id, 'lasso_custom_redirect', true);

		if (self::keep_affiliate_url($lasso_custom_redirect, $public_link)) {
			$link_type   = 'Affiliate URL';
			$target_url  = $lasso_custom_redirect;
			$public_link = $link_cloaking && !Lasso_Amazon_Api::is_amazon_url($public_link) ? $permalink : $lasso_custom_redirect;
		}

		if ('' !== $amazon_default_image && $image_src === $image_src_default) {
			$image_src = $amazon_default_image;
		}

		$fields = $lasso_db->get_fields_by_lasso_id($post_id);

		$primary_rating = false;
		$primary_key    = array_search('1', array_column($fields, 'field_id'), true);

		if (false !== $primary_key) {
			$primary_rating                  = (false !== $primary_key ? $fields[$primary_key] : null);
			$primary_rating->show_field_name = Field_Mapping::get_show_field_name($primary_rating->lasso_id, $primary_rating->field_id);
			unset($fields[$primary_key]);
		}

		$display_secondary_url = Lasso_Amazon_Api::get_amazon_product_url($display_secondary_url, true, false);

		$user_created = $fields;

		$lasso_url = (object) array(
			'lasso_id'            => intval($lasso_id),
			'edit_link'           => $edit_link,
			'link_type'           => $link_type,
			'name'                => trim($name),
			'description'         => $description,
			'custom_css'          => $custom_css,
			'slug'                => $slug,
			'guid'                => $guid,
			'permalink'           => $permalink,
			'image_src'           => trim($image_src),
			'image_src_default'   => $image_src_default,
			'thumbnail_id'        => $thumbnail_id,
			'target_url'          => $target_url,
			'affiliate_homepage'  => $affiliate_homepage,
			'price'               => Lasso_Amazon_Api::format_price($price),
			'is_amazon_page'      => $is_amazon_page,
			'keyword'             => $keyword,
			'category'            => $category,
			'open_new_tab'        => $open_new_tab,
			'enable_nofollow'     => $enable_nofollow,
			'open_new_tab2'       => $open_new_tab2,
			'enable_nofollow2'    => $enable_nofollow2,
			'enable_sponsored'    => $enable_sponsored,
			'link_cloaking'       => $link_cloaking,
			'public_link'         => Lasso_Amazon_Api::get_amazon_product_url($public_link),
			'currency'            => $currency,
			'rating'              => $rating,
			'categories'          => $categories,
			'developer'           => $developer,
			'version'             => $version,
			'size'           	  => $size,
			'updated_on'          => $updated_on,
			'screen_shots'        => $screen_shots,
			'apple_btn_url'       => $apple_btn_url,
			'google_btn_url'      => $google_btn_url,

			'display'             => (object) array(
				'theme'                         => $custom_theme,
				'primary_button_text'           => $display_primary_button_text,
				'primary_button_text_default'   => $display_primary_button_text_default,
				'secondary_button_text'         => $display_secondary_button_text,
				'secondary_button_text_default' => $display_secondary_button_text_default,

				'third_btn_text'         => $display_third_button_text,
				'third_btn_text_default' => $display_third_button_text_default,

				'fourth_btn_text'         => $display_fourth_button_text,
				'fourth_btn_text_default' => $display_fourth_button_text_default,

				'apple_btn_text'         => $display_apple_button_text,
				'apple_btn_text_default' => $display_apple_button_text_default,

				'google_btn_text'         => $display_google_button_text,
				'google_btn_text_default' => $display_google_button_text_default,

				'secondary_url'                 => $display_secondary_url,
				'disclosure_text'               => $display_disclosure_text,
				'disclosure_text_default'       => $display_disclosure_text_default,
				'badge_text'                    => $display_badge_text,
				'show_price'                    => $display_show_price,
				'show_disclosure'               => $display_show_disclosure,
				'show_description'              => $display_show_description,
				'last_updated'                  => $display_last_updated,
				'third_url'                  	=> $display_third_url,
				'fourth_url'                  => $display_fourth_url,
			),
			'amazon'              => (object) array(
				'amazon_id'             => $amazon_product_id,
				'default_product_name'  => $amazon_default_product_name,
				'latest_price'          => $amazon_latest_price,
				'base_url'              => $amazon_base_url,
				'monetized_url'         => $amazon_monetized_url,
				'default_image'         => trim($amazon_default_image),
				'last_updated'          => $amazon_last_updated,
				'is_prime'              => $enable_amazon_prime && '' !== $amazon_product_id,
				'show_discount_pricing' => $show_amazon_discount_pricing,
				'discount_pricing_html' => $amazon_discount_pricing_html,
				'rating'                => $amazon_rating,
				'reviews'               => $amazon_reviews,
				'savings_basis'         => $amazon_savings_basis,
				'savings_amount'        => $amazon_savings_amount,
				'savings_percent'       => $amazon_savings_percent,
			),
			'extend_product'      => (object) array(
				'product_type'         => $ext_product_type,
				'product_id'           => $ext_product_id,
				'default_product_name' => $ext_default_product_name,
				'latest_price'         => $ext_latest_price,
				'base_url'             => $ext_base_url,
				'default_image'        => trim($ext_default_image),
				'last_updated'         => $ext_last_updated,
			),
			'issue'               => (object) array(
				'out_of_stock' => $issue_out_of_stock,
				'broken'       => $issue_broken,
			),
			'url_detail_checkbox' => (object) array(
				'show_price'       => $url_detail_checkbox_show_price,
				'show_disclosure'  => $url_detail_checkbox_show_disclosure,
				'open_new_tab'     => $url_detail_checkbox_open_new_tab,
				'enable_nofollow'  => $url_detail_checkbox_enable_nofollow,
				'open_new_tab2'    => $url_detail_checkbox_open_new_tab2,
				'enable_nofollow2' => $url_detail_checkbox_enable_nofollow2,

				'open_new_tab3'    => $url_detail_checkbox_open_new_tab3,
				'enable_nofollow3' => $url_detail_checkbox_enable_nofollow3,
				'open_new_tab4'    => $url_detail_checkbox_open_new_tab4,
				'enable_nofollow4' => $url_detail_checkbox_enable_nofollow4,
				'open_new_tab_google'    => $url_detail_checkbox_open_new_tab_google,
				'enable_nofollow_google' => $url_detail_checkbox_enable_nofollow_google,
				'open_new_tab_apple'    => $url_detail_checkbox_open_new_tab_apple,
				'enable_nofollow_apple' => $url_detail_checkbox_enable_nofollow_apple,

				'is_opportunity'   => $url_detail_checkbox_is_opportunity,
				'link_cloaking'    => $url_detail_checkbox_link_cloaking,
				'enable_sponsored' => $url_detail_checkbox_enable_sponsored,
			),
			'html_attribute'      => (object) array(
				'rel'     => $html_attribute_rel,
				'target'  => $html_attribute_target,
				'rel2'    => $html_attribute_rel2,
				'target2' => $html_attribute_target2,
			),
			'fields'              => (object) array(
				'primary_rating' => $primary_rating,
				'user_created'   => $user_created,
			),
		);

		// ? Keep result on property $loaded_lasso_urls
		if (Lasso_Cache_Per_Process::get_instance()->get_cache(Lasso_Shortcode::OBJECT_KEY)) {
			self::$loaded_lasso_urls[$is_detail ? 'detail' : 'no_detail'][$post_id] = $lasso_url;
		}

		return $lasso_url;
	}

	/**
	 * Clone lasso_url object and sub properties
	 *
	 * @param object $lasso_url Lasso url object.
	 */
	public static function clone_lasso_url_obj($lasso_url)
	{
		$lasso_url          = clone $lasso_url;
		$lasso_url->display = clone $lasso_url->display;
		$lasso_url->fields  = clone $lasso_url->fields;
		$lasso_url->amazon  = clone $lasso_url->amazon;

		return $lasso_url;
	}

	/**
	 * Get edit url of post
	 *
	 * @param int $post_id The post id.
	 *
	 * @return string $url Edit url of the post id.
	 */
	public static function affiliate_edit_link($post_id = 0)
	{
		$query = array(
			'post_type' => LASSO_POST_TYPE,
			'page'      => self::$edit_details_page,
			'post_id'   => $post_id,
		);

		$affiliate_link_url = add_query_arg(
			$query,
			admin_url('edit.php')
		);
		$url                = $affiliate_link_url;

		return $url;
	}

	/**
	 * Get lasso_id by URL and check whether setting allows URL is duplicated
	 *
	 * @param string $url           URL.
	 * @param string $get_final_url Final URL.
	 */
	public static function is_lasso_url_exist($url, $get_final_url)
	{
		$enable_duplicate_link = Lasso_Setting::lasso_get_setting('check_duplicate_link', false);
		$final_url_base_domain = Lasso_Helper::get_base_domain($get_final_url);
		$is_amazon_search_page = Lasso_Amazon_Api::is_amazon_search_page($get_final_url);
		$is_amazon_non_product = Lasso_Amazon_Api::is_amazon_url($get_final_url) && !Lasso_Amazon_Api::get_product_id_by_url($get_final_url);
		$extend_product_type   = Lasso_Extend_Product::get_extend_product_type_from_url($get_final_url);
		$extend_product_id     = Lasso_Extend_Product::get_extend_product_id_by_url($get_final_url);
		$final_url_to_check    = $is_amazon_search_page || ($extend_product_type && !$extend_product_id)
			|| $is_amazon_non_product || in_array($final_url_base_domain, Setting_Enum::DOMAIN_ALLOW_KEEP_FINAL_URL_PARAMS, true)
			? $get_final_url
			: explode('?', $get_final_url)[0];
		$check_url             = $get_final_url && !$enable_duplicate_link ? $final_url_to_check : $url;
		$lasso_post_id         = self::get_lasso_post_id_by_url($check_url);

		if ($is_amazon_non_product && 0 === $lasso_post_id) {
			$check_url     = Lasso_Amazon_Api::get_amazon_product_url($check_url, true, false);
			$lasso_post_id = self::get_lasso_post_id_by_url($check_url);
		}

		if ($lasso_post_id <= 0 && !$enable_duplicate_link) {
			$url_final_without_arguments = Lasso_Helper::format_url_for_checking_duplication($get_final_url, $url);
			$url_detail                  = Url_Details::get_by_url_without_arguments($url_final_without_arguments);
			if (!is_null($url_detail)) {
				$lasso_post_id = $url_detail->get_lasso_id();
			}

			if ($lasso_post_id <= 0) {
				$lasso_post_id = self::get_lasso_post_id_by_url($url);
			}
		}

		$lasso_post_id = intval($lasso_post_id);

		if (LASSO_POST_TYPE !== get_post_type($lasso_post_id)) {
			return 0;
		}

		return $lasso_post_id;
	}

	/**
	 * Add a new Lasso link
	 *
	 * @param string $link    Link. Default to empty.
	 * @param array  $options Option values. Default to empty.
	 */
	public function lasso_add_a_new_link($link = '', $options = array())
	{
		Lasso_Helper::write_log('Add a Lasso post', 'lasso_save_post');
		$time_start = microtime(true);

		$link = trim($link ?? '');
		$url  = trim($link != '' ? $link : ($_POST['link'] ?? ''));

		$is_ajax_request = wp_doing_ajax() && '' === $link;
		$is_ajax_request = $is_ajax_request || false;

		$lasso_amazon_api = new Lasso_Amazon_Api();

		if ('' === $url) {
			if ($is_ajax_request) {
				wp_send_json_error('No data to save.');
			} else {
				return 'No data to save.';
			}
		}

		$url                                = Lasso_Helper::add_https($url);
		$url                                = Lasso_Helper::format_url_before_requesting($url);
		$is_amazon_link                     = Lasso_Amazon_Api::is_amazon_url($url);
		list($get_final_url, $page_title) = Lasso_Helper::get_redirect_final_target($url, true, true);
		$res['status_code']                 = Lasso_Cache_Per_Process::get_instance()->get_cache(self::ADD_NEW_LINK_RESPONSE_STATUS . md5($url), 200);
		$url                                = Lasso_Amazon_Api::is_amazon_shortened_url($url) ? $get_final_url : $url; // ? Set url as final url if this is amazon shortlink
		$title                              = $is_amazon_link ? self::DEFAULT_AMAZON_NAME : self::DEFAULT_TITLE;
		$default_title                      = $title;
		$image                              = '';
		$permalink                          = '';
		$status                             = 200;
		$amz_product                        = false;
		$extend_product                     = false;

		$url           = Lasso_Amazon_Api::format_amazon_url($url);
		$get_final_url = Lasso_Amazon_Api::format_amazon_url($get_final_url);

		if ($page_title) {
			$title = $page_title;
		}
		$amazon_search_title = Lasso_Amazon_Api::get_search_page_title($get_final_url);

		$parse_url = wp_parse_url($get_final_url);
		$is_keyword = false;
		$apiCheckSearch = 'https://serpapi.com/account?api_key=';
		$primaryKeySearch = '';

		$arrKey = [
			'f15db785c3495c2f77ab53a7ecfa45e2d23406642a2a7631342f1134425901cc',
			'2e012c4331346280042ad2946814f792ff852975d49043c5e70fbe89ae6b9922',
			'bfb476bc8440f060661a2ed7121d9868dd008f6e809d2be38e6051a6201baf65',
			'39c82642d685e32f50722334c08147bf9c4a5449e38234e31423d6c360a409c2',
			'b438bb4ba7fb8e292e79fae8a5f56777786cc2bddf92eafb650ea1d8aad16f4a',
			'9ce009863ee89b61d77e7ec47b47881de1c019e5b305b9eb908e0bfa7f1bc471'
		];

		foreach ($arrKey as $keySearch) {
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_URL => $apiCheckSearch . $keySearch,
				CURLOPT_SSL_VERIFYPEER => false
			));

			$respCheckSearch = curl_exec($curl);
			$respCheckSearch = json_decode($respCheckSearch, true);
			if ($respCheckSearch['plan_searches_left'] == 0) {
				continue;
			} else {
				$primaryKeySearch = $respCheckSearch['api_key'];
				break;
			}

			curl_close($curl);
		}

		if ($primaryKeySearch == '') {
			$error_message = "All keys run out of searches.";
			if ($is_ajax_request) {
				wp_send_json_error($error_message);
			} else {
				return $error_message;
			}
		}

		$apiKeySerp = $primaryKeySearch;
		if (!array_key_exists('host', $parse_url)) {
			$is_keyword = true;
			$apiSearchGoogle = 'https://serpapi.com/search.json?engine=google_play&q=';
			$keySearchGoogle = $parse_url['path'];
			$curlUrl = $apiSearchGoogle . urlencode($keySearchGoogle) . '&api_key=' . $apiKeySerp;
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_URL => $curlUrl,
				CURLOPT_SSL_VERIFYPEER => false
			));

			$respGoogle = curl_exec($curl);
			curl_close($curl);
			$respGoogle = json_decode($respGoogle);
			$idAppPlay = '';

			if (isset($respGoogle->app_highlight)) {
				if (strpos($respGoogle->app_highlight->title, $parse_url['path']) !== false) {
					$idAppPlay = $respGoogle->app_highlight->product_id;
				}
			} else {
				foreach ($respGoogle->organic_results as $resultSearchAppPlay) {
					if (strpos($resultSearchAppPlay->items[0]->title, $parse_url['path']) !== false) {
						$idAppPlay = $resultSearchAppPlay->items[0]->product_id;

						break;
					}
				}
			}

			$apiSearchApple = 'https://serpapi.com/search.json?country=us&engine=apple_app_store&term=';
			$keySearchApple = $parse_url['path'];
			$curlUrl = $apiSearchApple . urlencode($keySearchApple) . '&api_key=' . $apiKeySerp;
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_URL => $curlUrl,
				CURLOPT_SSL_VERIFYPEER => false
			));

			$respApple = curl_exec($curl);
			curl_close($curl);
			$respApple = json_decode($respApple);
			$idAppStore = '';

			if (isset($respApple->app_highlight)) {
				if (strpos($respApple->app_highlight->title, $parse_url['path']) !== false) {
					$idAppStore = $respApple->app_highlight->id;
				}
			} else {
				foreach ($respApple->organic_results as $resultSearchAppStore) {
					if (strpos($resultSearchAppStore->title, $parse_url['path']) !== false) {
						$idAppStore = $resultSearchAppStore->id;

						break;
					}
				}
			}
		}

		if (!$is_keyword) {
			if ($parse_url['host'] == 'play.google.com') {
				$query_url = $parse_url['query'];
				$idAppPlay = '';
				$hlAppPlay = '';
				$glAppPlay = '';
				$explodeQuery = explode('&', $query_url);
				foreach ($explodeQuery as $explode) {
					$explodeInExplode = explode('=', $explode);
					if ($explodeInExplode[0] == 'id') {
						$idAppPlay = $explodeInExplode[1];
					}

					if ($explodeInExplode[0] == 'hl') {
						$hlAppPlay = $explodeInExplode[1];
					}

					if ($explodeInExplode[0] == 'gl') {
						$glAppPlay = $explodeInExplode[1];
					}
				}

				if ($idAppPlay == '') {
					$productAppGoogle = [
						'title' => '',
						'rating' => '',
						'price' => '',
						'developer' => '',
						'categories' => '',
						'thumbnail' => '',
						'screen_shots' => '',
					];
				} else {
					$apiGooglePlay = 'https://serpapi.com/search?engine=google_play_product&store=apps&gl=GB&hl=en&product_id=';
					$curlUrl = $apiGooglePlay . $idAppPlay . '&api_key=' . $apiKeySerp;

					if ($hlAppPlay != '') {
						$curlUrl .= '&hl=' . $hlAppPlay;
					}

					if ($glAppPlay != '') {
						$curlUrl .= '&gl=' . $glAppPlay;
					}

					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_URL => $curlUrl,
						CURLOPT_SSL_VERIFYPEER => false
					));

					$resp = curl_exec($curl);
					curl_close($curl);

					$dump = json_decode($resp, true);

					if (array_key_exists('error', $dump)) {
						$error_message = $dump['error'];
						if ($is_ajax_request) {
							wp_send_json_error($error_message);
						} else {
							return $error_message;
						}
					}

					if ($resp) {
						$resp = json_decode($resp);
						$productInfo = $resp->product_info;

						$productAppGoogle['title'] = $productInfo->title;
						$productAppGoogle['rating'] = $productInfo->rating;
						$productAppGoogle['price'] = $productInfo->offers[0]->price;
						$productAppGoogle['developer'] = $productInfo->authors[0]->name;
						$productAppGoogle['categories'] = $resp->categories[0]->name;

						if ($resp->media->video != null) {
							$productAppGoogle['thumbnail'] = $resp->media->video->thumbnail;
						} else {
							$productAppGoogle['thumbnail'] = $productInfo->thumbnail;
						}

						$productAppGoogle['screen_shots'] = json_encode($resp->media->images);
					}
				}
			}

			if ($parse_url['host'] == 'apps.apple.com') {
				$path = $parse_url['path'];
				$idAppStore = '';
				$explodePath = explode('/', $path);
				$regex = '/[i][d][0-9]/';
				foreach ($explodePath as $itemPath) {
					if (preg_match($regex, $itemPath)) {
						$explodePreg = explode('id', $itemPath);
						$idAppStore = $explodePreg[1];
					}
				}

				if ($idAppStore == '') {
					$productAppStore = [
						'title' => '',
						'rating' => '',
						'price' => '',
						'developer' => '',
						'categories' => '',
						'thumbnail' => '',
						'size' => '',
						'version' => '',
						'screen_shots' => '',
					];
				} else {
					$apiAppStore = 'https://serpapi.com/search.json?engine=apple_product&product_id=';
					$curlUrl = $apiAppStore . $idAppStore . '&api_key=' . $apiKeySerp;
					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_URL => $curlUrl,
						CURLOPT_SSL_VERIFYPEER => false
					));

					$resp = curl_exec($curl);
					curl_close($curl);

					if ($resp) {
						$productAppStore = [];
						$resp = json_decode($resp);
						$productAppStore['title'] = $resp->title;
						$productAppStore['rating'] = $resp->rating;
						$productAppStore['price'] = $resp->price;
						$productAppStore['developer'] = $resp->developer->name;
						$productAppStore['categories'] = $resp->information->categories[0];
						$productAppStore['thumbnail'] = $resp->logo;
						$productAppStore['size'] = $resp->information->size;
						$productAppStore['version'] = $resp->version_history[0]->release_version;
						if (isset($resp->iphone_screenshots)) {
							$productAppStore['screen_shots'] = json_encode($resp->iphone_screenshots);
						}

						if (isset($resp->ipad_screenshots)) {
							$productAppStore['screen_shots'] = json_encode($resp->ipad_screenshots);
						}
					}
				}
			}
		} else {
			if ($idAppPlay == '') {
				$productAppGoogle = [
					'title' => '',
					'rating' => '',
					'price' => '',
					'developer' => '',
					'categories' => '',
					'thumbnail' => '',
					'screen_shots' => '',
					'base_url' => '',
				];
			} else {
				$apiGooglePlay = 'https://serpapi.com/search.json?engine=google_play_product&product_id=';
				$curlUrl = $apiGooglePlay . $idAppPlay . '&store=apps&api_key=' . $apiKeySerp;
				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_URL => $curlUrl,
					CURLOPT_SSL_VERIFYPEER => false
				));

				$resp = curl_exec($curl);
				curl_close($curl);

				if ($resp) {
					$productAppGoogle = [];
					$resp = json_decode($resp);
					$productInfo = $resp->product_info;
					$productAppGoogle['title'] = $productInfo->title;
					$productAppGoogle['rating'] = $productInfo->rating;
					$productAppGoogle['price'] = $productInfo->offers[0]->price;
					$productAppGoogle['developer'] = $productInfo->authors[0]->name;
					$productAppGoogle['categories'] = $resp->categories[0]->name;
					$productAppGoogle['thumbnail'] = $resp->media->video->thumbnail;
					$productAppGoogle['screen_shots'] = json_encode($resp->media->images);
					$productAppGoogle['base_url'] = $resp->search_metadata->google_play_product_url;
				}
			}

			if ($idAppStore == '') {
				$productAppStore = [
					'title' => '',
					'rating' => '',
					'price' => '',
					'developer' => '',
					'categories' => '',
					'thumbnail' => '',
					'size' => '',
					'version' => '',
					'screen_shots' => '',
					'base_url' => '',
				];
			} else {
				$apiAppStore = 'https://serpapi.com/search.json?engine=apple_product&product_id=';
				$curlUrl = $apiAppStore . $idAppStore . '&api_key=' . $apiKeySerp;
				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_URL => $curlUrl,
					CURLOPT_SSL_VERIFYPEER => false
				));

				$resp = curl_exec($curl);
				curl_close($curl);

				if ($resp) {
					$productAppStore = [];
					$resp = json_decode($resp);
					$productAppStore['title'] = $resp->title;
					$productAppStore['rating'] = $resp->rating;
					$productAppStore['price'] = $resp->price;
					$productAppStore['developer'] = $resp->developer->name;
					$productAppStore['categories'] = $resp->information->categories[0];
					$productAppStore['thumbnail'] = $resp->logo;
					$productAppStore['size'] = $resp->information->size;
					$productAppStore['version'] = $resp->version_history[0]->release_version;
					$productAppStore['base_url'] = $resp->search_metadata->apple_product_url;
					if (isset($resp->iphone_screenshots)) {
						$productAppStore['screen_shots'] = json_encode($resp->iphone_screenshots);
					}

					if (isset($resp->ipad_screenshots)) {
						$productAppStore['screen_shots'] = json_encode($resp->ipad_screenshots);
					}
				}
			}
		}

		if ($lasso_post_id > 0) {
			$post_title    = get_the_title($lasso_post_id);
			$optional_data = 'publish' === get_post_status($lasso_post_id) ? array('is_duplicate' => true) : array();

			if ($is_amazon_link && strpos($post_title, 'Sorry!') !== false || strpos($post_title, ' wrong!') !== false) {
				$post_title = $amazon_search_title;
			}

			wp_update_post(
				array(
					'ID'          => $lasso_post_id,
					'post_title'  => $post_title,
					'post_status' => 'publish',
				)
			);

			update_post_meta($lasso_post_id, 'rating', $productAppGoogle['rating']);
			update_post_meta($lasso_post_id, 'price', $productAppGoogle['price']);
			update_post_meta($lasso_post_id, 'size', $productAppGoogle['size']);
			update_post_meta($lasso_post_id, 'developer', $productAppGoogle['developer']);
			update_post_meta($lasso_post_id, 'categories', $productAppGoogle['categories']);
			update_post_meta($lasso_post_id, 'thumbnail', $productAppGoogle['thumbnail']);
			update_post_meta($lasso_post_id, 'screen_shots', $productAppGoogle['screen_shots']);
			update_post_meta($lasso_post_id, 'apple_btn_url', $productAppStore['base_url']);
			update_post_meta($lasso_post_id, 'google_btn_url', $productAppGoogle['base_url']);
			update_post_meta($lasso_post_id, 'updated_on', date('Y-m-d'));

			if ($is_ajax_request) {
				$this->check_error_and_response_ajax($lasso_post_id, '', '', $optional_data);
			} else {
				return $lasso_post_id;
			}
		}

		$url        = Lasso_Amazon_Api::get_amazon_product_url($url, true, false);
		$product_id = Lasso_Amazon_Api::get_product_id_country_by_url($get_final_url);

		$extend_product_url  = Lasso_Extend_Product::url_to_get_product_id($url, $get_final_url);
		$extend_product_type = Lasso_Extend_Product::get_extend_product_type_from_url($extend_product_url);
		$extend_product_id   = Lasso_Extend_Product::get_extend_product_id_by_url($extend_product_url);

		if ($is_amazon_link && $product_id) {
			$product = $lasso_amazon_api->get_amazon_product_from_db($product_id, $get_final_url);

			if ($product) {
				$lasso_amazon_api->update_amazon_product_in_db(
					array(
						'product_id'      => $product['amazon_id'],
						'title'           => $product['default_product_name'],
						'price'           => $product['latest_price'],
						'default_url'     => $product['base_url'],
						'url'             => $url,
						'image'           => $product['default_image'],
						'quantity'        => '0' === $product['out_of_stock'] ? 200 : 0,
						'is_manual'       => $product['is_manual'],
						'is_prime'        => $product['is_prime'],
						'features'        => $product['features'],
						'currency'        => $product['currency'],
						'savings_amount'  => $product['savings_amount'],
						'savings_percent' => $product['savings_percent'],
						'savings_basis'   => $product['savings_basis'],
					)
				);
			}

			if (!$product) {
				$product_info = $lasso_amazon_api->fetch_product_info($product_id, true, false, $get_final_url);
				$product      = $product_info['product'];

				if ('NotFound' === $product_info['error_code']) {
					$res['status_code']              = 404;
					$product['default_product_name'] = self::DEFAULT_AMAZON_NAME;
					$product['default_image']        = LASSO_DEFAULT_THUMBNAIL;
					$product['monetized_url']        = $url;
				} else {
					$product['default_product_name'] = $product['title'];
					$product['default_image']        = $product['image'];
					$product['monetized_url']        = $product['url'];
				}
			}

			$amz_product = $product;

			$url = Lasso_Amazon_Api::get_amazon_product_url($get_final_url, true, false);
		} elseif ($extend_product_type && $extend_product_id) {
			list($extend_product, $is_status_404) = $this->get_extend_product($extend_product_type, $extend_product_id, $extend_product_url);

			if ($is_status_404) {
				$res['status_code'] = 404;
			}

			$title = $extend_product['default_product_name'];
			$image = $extend_product['default_image'];
		} elseif ($default_title === $title || $is_amazon_link) { // ? Amazon search/category and other urls.
			// phpcs:ignore
			// ? $res = Lasso_Helper::get_url_status_code_by_broken_link_service( $url, true, true );
			$res = Lasso_Helper::get_url_status_code($url, true);
			if (200 === (int) $res['status_code']) {
				$title  = $res['response']->title ?? $title;
				$title  = $res['response']->productName ?? $title;
				$title  = $res['response']->pageTitle ?? $title;
				$image  = $res['response']->imgUrl ?? $image;
				$status = $res['response']->status ?? $status;
				// ? Apply tracking id to amazon link
				$url = Lasso_Amazon_Api::get_amazon_product_url($url);

				if ('Robot Check' === $title) {
					$title = $default_title;
				}
			}

			if ('' === $title && Lasso_Amazon_Api::is_amazon_search_page($get_final_url)) {
				$title = $amazon_search_title;
			}

			$permalink = $title ? sanitize_title($title) : Lasso_Helper::get_title_by_url($url);
		}

		$google_product = false;
		$apple_product = false;

		if (isset($productAppGoogle) && !isset($productAppStore)) {
			$title       = $productAppGoogle['title'];
			$image       = $productAppGoogle['thumbnail'];
			$google_product = $productAppGoogle;
		} elseif (isset($productAppStore) && !isset($productAppGoogle)) {
			$title       = $productAppStore['title'];
			$image       = $productAppStore['thumbnail'];
			$apple_product = $productAppStore;
		} elseif (isset($productAppStore) && isset($productAppGoogle)) {
			$title       = !empty($productAppGoogle['title']) ? $productAppGoogle['title'] : $productAppStore['title'];
			$image       = !empty($productAppGoogle['thumbnail']) ? $productAppGoogle['thumbnail'] : $productAppStore['thumbnail'];
			$url       = !empty($productAppGoogle['base_url']) ? $productAppGoogle['base_url'] : $productAppStore['base_url'];
			$google_product = $productAppGoogle;
			$apple_product = $productAppStore;
			$permalink = $this->create_slug($parse_url['path']);
		} else {
			$title       = $product['default_product_name'];
			$image       = $product['default_image'];
		}

		if (!$google_product && !$apple_product) {
			if (!$is_amazon_link && ('' === $title || $title === $default_title)) {
				$title = $permalink;
			} elseif ($is_amazon_link && '' === $title) {
				$title = $default_title;
			}
			if (!$title) {
				$title = Lasso_Helper::get_title_by_url($url);
			}
		}

		$affiliate_link = array(
			'is_amazon'       => $is_amazon_link,
			'affiliate_name'  => $title,
			'affiliate_url'   => trim($url),
			'affiliate_desc'  => '',
			'permalink'       => strtolower($permalink),
			'is_opportunity'  => 1,
			'buy_btn_text'    => '',
			'second_btn_text' => '',
			'price'           => '',
			'badge_text'      => '',
			'second_btn_url'  => '',
			'thumbnail'       => $image,
			'show_disclosure' => 0,
			'link_cloaking' => 0,
		);

		if (!empty($options)) {
			$affiliate_link = array_merge($affiliate_link, $options);
		}

		$time_end = microtime(true);
		$execution_time = round($time_end - $time_start, 2);
		Lasso_Helper::write_log($url, 'lasso_save_post');
		Lasso_Helper::write_log('Add takes ' . $execution_time, 'lasso_save_post');
		Lasso_Helper::write_log('Add a Lasso post - End', 'lasso_save_post');

		$data['settings'] = $affiliate_link;

		$post_id = $this->save_lasso_url($data, $is_ajax_request, $res, $amz_product, $extend_product, $google_product, $apple_product);

		if ('' !== $link) {
			return $post_id;
		}

		wp_send_json_success(
			array(
				'success' => true,
				'url'     => $url,
				'link'    => $affiliate_link,
				'title'   => $title,
				'image'   => $image,
				'post_id' => $post_id,
				'status'  => $status,
			)
		);
	}

	/**
	 * Save Lasso data into DB
	 *
	 * @param array $data           Lasso data. Default to null.
	 * @param bool  $is_ajax        Is request ajax. Default to false.
	 * @param bool  $res            Response of request. Default to false.
	 * @param bool  $amz_product    Amazon product. Default to false.
	 * @param bool  $extend_product Extend product. Default to false.
	 */
	public function save_lasso_url($data = null, $is_ajax = false, $res = false, $amz_product = false, $extend_product = false, $google_product = false, $apple_product = false)
	{
		Lasso_Helper::write_log('Save Lasso post', 'lasso_save_post');
		$time_start = microtime(true);

		global $wpdb;

		$lasso_amazon_api     = new Lasso_Amazon_Api();
		$lasso_db             = new Lasso_DB();
		$lasso_cat_order      = new Lasso_Category_Order();
		$cron                 = new Lasso_Cron();
		$lasso_extend_product = new Lasso_Extend_Product();

		$is_ajax_request = wp_doing_ajax() && ('' === $data || null === $data);
		$is_ajax_request = $is_ajax_request || $is_ajax;
		$post            = is_array($data) ? $data : $_POST; // phpcs:ignore

		$post      = wp_unslash($post); // phpcs:ignore
		$post_id   = intval($post['post_id'] ?? 0);
		$is_update = $post_id > 0;
		$is_new    = !$is_update;

		$thumbnail_id = $post['thumbnail_id'] ?? 0;
		$post_data    = $post['settings'] ?? array();

		if (empty($post_data) || !is_array($post_data)) {
			$error_message = 'No data to save.';
			if ($is_ajax_request) {
				wp_send_json_error($error_message);
			} else {
				return $error_message;
			}
		}

		if (empty(trim($post_data['affiliate_url'] ?? '')) || empty(trim($post_data['affiliate_name'] ?? ''))) {
			$error_message = 'Name and Target URL are required.';

			if (!empty(trim($post_data['affiliate_url'] ?? ''))) {
				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_URL => $post_data['affiliate_url'],
					CURLOPT_SSL_VERIFYPEER => false
				));

				$respGoogle = curl_exec($curl);
				curl_close($curl);
				preg_match("/\<title.*\>(.*)\<\/title\>/isU", $respGoogle, $matches);
				$title = $matches[1];
				$explode = explode(" - ", $title);
				$post_data['affiliate_name'] = $explode[0];
			} else {
				if ($is_ajax_request) {
					wp_send_json_error($error_message);
				} else {
					return $error_message;
				}
			}
		}

		$post_data['affiliate_url'] = trim($post_data['affiliate_url'] ?? '');

		$post_data['second_btn_url'] = trim($post_data['second_btn_url'] ?? '');
		$post_data['second_btn_url'] = Lasso_Helper::add_https($post_data['second_btn_url']);
		$post_data['second_btn_url'] = Lasso_Amazon_Api::get_amazon_product_url($post_data['second_btn_url'], true, false);

		$post_data['permalink'] = $post_data['permalink'] ?? '';
		$post_data['keywords']  = $post_data['keywords'] ?? array();
		$post_data['keywords']  = is_array($post_data['keywords']) ? $post_data['keywords'] : array();

		$enable_nofollow  = 1 === intval($post_data['enable_nofollow'] ?? '') ? true : false;
		$open_new_tab     = 1 === intval($post_data['open_new_tab'] ?? '') ? true : false;
		$enable_nofollow2 = 1 === intval($post_data['enable_nofollow2'] ?? '') ? true : false;
		$open_new_tab2    = 1 === intval($post_data['open_new_tab2'] ?? '') ? true : false;

		$link_cloaking    = 1 === intval($post_data['link_cloaking'] ?? '') ? true : false;
		$enable_sponsored = 1 === intval($post_data['enable_sponsored'] ?? '') ? true : false;
		$is_opportunity   = 1 === intval($post_data['is_opportunity'] ?? '') ? 1 : 0;
		$term             = isset($post_data['categories']) && is_array($post_data['categories']) ? $post_data['categories'] : array();
		$term             = array_map(
			function ($val) {
				$term_id = is_numeric($val) ? intval($val) : 0;
				$term_id = get_term_by('name', $val, LASSO_CATEGORY)->term_id ?? $term_id;
				// ? Support category name is number and different existed term ids.
				$term_id = $term_id && term_exists($term_id) ? $term_id : 0;

				if (0 === $term_id && !empty($val)) { // ? add new category
					$result  = wp_insert_term($val, LASSO_CATEGORY);
					$term_id = (!is_wp_error($result)) ? $result['term_id'] : 0;
				}

				return $term_id;
			},
			$term
		);

		$lasso_url = self::get_lasso_url($post_id);

		foreach ($post_data as $key => $value) {
			if ('true' === $value) {
				$post_data[$key] = true;
			} elseif ('false' === $value) {
				$post_data[$key] = false;
			} else {
				$post_data[$key] = $value;
			}
		}

		$url                = $post_data['affiliate_url'];
		$url                = Lasso_Helper::add_https($url);
		$url                = Lasso_Amazon_Api::get_amazon_product_url($url, true, $is_update);
		$original_url       = $url;
		$get_final_url      = Lasso_Helper::get_redirect_final_target($url, true, $is_new ? true : false); // ? If adding the new link, we set param "get_page_title" is true to get result from cache.
		$get_final_url      = is_array($get_final_url) ? $get_final_url[0] : $get_final_url;
		$affiliate_homepage = Lasso_Helper::get_base_domain($get_final_url);

		$url           = Lasso_Amazon_Api::format_amazon_url($url);
		$get_final_url = Lasso_Amazon_Api::format_amazon_url($get_final_url);

		// ? check whether product is exist
		// $lasso_post_id = self::is_lasso_url_exist($url, $get_final_url);

		if ($lasso_post_id > 0 && (!$is_update || LASSO_POST_TYPE !== get_post_type($post_id))) {
			wp_update_post(
				array(
					'ID'          => $lasso_post_id,
					'post_status' => 'publish',
				)
			);

			if ($is_ajax_request) {
				$this->check_error_and_response_ajax($lasso_post_id);
			} else {
				return $lasso_post_id;
			}
		}

		$format       = 'Y-m-d H:i:s';
		$updated_date = get_post_modified_time($format, true, $post_id);
		$now          = gmdate($format);
		$now          = date_create_from_format($format, $now);
		$lastest_hour = date_sub($now, date_interval_create_from_date_string('1 hour')); // ? an hour ago
		$lastest_hour = $lastest_hour->format($format);

		$check_link_status = true;
		if ($is_new || ($is_update && $updated_date > $lastest_hour && $lasso_url->target_url === $url)) {
			$check_link_status = false;
		}

		$warning                   = '';
		$error                     = '';
		$status                    = 200;
		$force_update_issue_status = false;
		if ($res || $amz_product || $extend_product) {
			$status = $res['status_code'] ?? $status;
			$status = $amz_product['status_code'] ?? $status;
			$status = $extend_product['status_code'] ?? $status;
		}

		if (is_null($data) && $check_link_status) {
			$status = '' !== $status ? $status : Lasso_Helper::get_url_status_code($url);
			if (!Lasso_Helper::validate_url($url) || 200 !== $status) {
				$error = 'Saved but could not resolve the URL.';
			}
		}

		if (in_array($get_final_url, Setting_Enum::ERROR_URL, true)) {
			$status = 500;
		}

		if ($google_product && $apple_product) {
			$status = 200;
		}

		$amazon_product_id   = Lasso_Amazon_Api::get_product_id_country_by_url($get_final_url);
		$extend_product_url  = Lasso_Extend_Product::url_to_get_product_id($url, $get_final_url);
		$extend_product_type = Lasso_Extend_Product::get_extend_product_type_from_url($extend_product_url);
		$extend_product_id   = Lasso_Extend_Product::get_extend_product_id_by_url($extend_product_url);

		$custom_thumbnail = $post_data['thumbnail'] ?? '';
		$custom_thumbnail = '' !== $custom_thumbnail && LASSO_DEFAULT_THUMBNAIL !== $custom_thumbnail ? $custom_thumbnail : '';

		$post_name = $post_data['permalink'] ?? $lasso_url->slug ?? '';
		if ('' === $post_name && isset($post_data['post_name']) && '' !== $post_data['post_name']) {
			$post_name = $post_data['post_name'];
		}

		$show_description = !empty($post_data['affiliate_desc']) ? 1 : 0;
		$show_description = (0 === $post_id) ? 1 : $show_description;
		$description      = $post_data['affiliate_desc'] ?? $lasso_url->description;
		$description = $show_description ? $description : '';
		$description = wp_encode_emoji($description);

		$lasso_post = array(
			'post_title'   => $post_data['affiliate_name'] ?? $lasso_url->name,
			'post_type'    => LASSO_POST_TYPE,
			'post_name'    => $post_name,
			'post_content' => '',
			'post_status'  => 'publish',
			'meta_input'   => array(
				'lasso_custom_redirect'  => $url,
				'lasso_final_url'        => $get_final_url,

				'affiliate_desc'         => $description,
				'price'                  => $post_data['price'] ?? $lasso_url->price ?? '',
				'lasso_custom_thumbnail' => $custom_thumbnail,

				'rating' => $post_data['rating'],
				'developer' => $post_data['developer'],
				'categories' => $post_data['categories_app'],
				'version' => $post_data['version'],
				'size' => $post_data['size'],
				'apple_btn_url' => $post_data['apple_btn_url'],
				'google_btn_url' => $post_data['google_btn_url'],

				'enable_nofollow'        => $post_data['enable_nofollow'] ?? $lasso_url->enable_nofollow,
				'open_new_tab'           => $post_data['open_new_tab'] ?? $lasso_url->open_new_tab,
				'enable_nofollow2'       => $post_data['enable_nofollow2'] ?? $lasso_url->enable_nofollow2,
				'open_new_tab2'          => $post_data['open_new_tab2'] ?? $lasso_url->open_new_tab2,

				'enable_nofollow3'       => $post_data['enable_nofollow3'] ?? true,
				'open_new_tab3'          => $post_data['open_new_tab3'] ?? true,
				'enable_nofollow4'       => $post_data['enable_nofollow4'] ?? true,
				'open_new_tab4'          => $post_data['open_new_tab4'] ?? true,
				'enable_nofollow_google' => $post_data['enable_nofollow_google'] ?? true,
				'open_new_tab_google'    => $post_data['open_new_tab_google'] ?? true,
				'enable_nofollow_apple'  => $post_data['enable_nofollow_apple'] ?? true,
				'open_new_tab_apple'     => $post_data['open_new_tab_apple'] ?? true,

				'link_cloaking'          => $post_data['link_cloaking'] ?? $lasso_url->link_cloaking,

				'custom_theme'           => $post_data['theme_name'] ?? $lasso_url->display->theme,
				'disclosure_text'        => trim($post_data['disclosure_text'] ?? $lasso_url->display->disclosure_text),
				'badge_text'             => $post_data['badge_text'] ?? $lasso_url->display->badge_text,
				'buy_btn_text'           => $post_data['buy_btn_text'] ?? $lasso_url->display->primary_button_text,
				'second_btn_url'         => $post_data['second_btn_url'] ?? $lasso_url->display->secondary_url,
				'second_btn_text'        => $post_data['second_btn_text'] ?? $lasso_url->display->secondary_button_text,

				'third_btn_url'        	 => $post_data['third_btn_url'] ?? '',
				'third_btn_text'         => $post_data['third_btn_text'] ?? '',
				'fourth_btn_url'         => $post_data['fourth_btn_url'] ?? '',
				'fourth_btn_text'        => $post_data['fourth_btn_text'] ?? '',

				'show_price'             => $post_data['show_price'] ?? $lasso_url->display->show_price,
				'show_disclosure'        => $post_data['show_disclosure'] ?? $lasso_url->display->show_disclosure,
				'show_description'       => $show_description,
				'enable_sponsored'       => $post_data['enable_sponsored'] ?? $lasso_url->enable_sponsored,
				'updated_on'	=> $post_data['updated_on'] ?? date('Y-m-d')
			),
		);

		if ($google_product) {
			$lasso_post = array(
				'post_title'   => $google_product['title'],
				'post_type'    => LASSO_POST_TYPE,
				'post_name'    => $google_product['title'],
				'post_content' => '',
				'post_status'  => 'publish',
				'meta_input'   => array(
					'lasso_custom_redirect'  => $url,
					'lasso_final_url'        => $get_final_url,

					'rating' => $google_product['rating'],
					'developer' => $google_product['developer'],
					'categories' => $google_product['categories'],
					'screen_shots' => $google_product['screen_shots'],
					'google_btn_url' => $get_final_url,

					'affiliate_desc'         => $description,
					'price'                  => $google_product['price'],
					'lasso_custom_thumbnail' => $google_product['thumbnail'],

					'enable_nofollow'        => $post_data['enable_nofollow'] ?? $lasso_url->enable_nofollow,
					'open_new_tab'           => $post_data['open_new_tab'] ?? $lasso_url->open_new_tab,
					'enable_nofollow2'       => $post_data['enable_nofollow2'] ?? $lasso_url->enable_nofollow2,
					'open_new_tab2'          => $post_data['open_new_tab2'] ?? $lasso_url->open_new_tab2,

					'enable_nofollow3'       => $post_data['enable_nofollow3'] ?? true,
					'open_new_tab3'          => $post_data['open_new_tab3'] ?? true,
					'enable_nofollow4'       => $post_data['enable_nofollow4'] ?? true,
					'open_new_tab4'          => $post_data['open_new_tab4'] ?? true,
					'enable_nofollow_google' => $post_data['enable_nofollow_google'] ?? true,
					'open_new_tab_google'    => $post_data['open_new_tab_google'] ?? true,
					'enable_nofollow_apple'  => $post_data['enable_nofollow_apple'] ?? true,
					'open_new_tab_apple'     => $post_data['open_new_tab_apple'] ?? true,

					'link_cloaking'          => $post_data['link_cloaking'] ?? $lasso_url->link_cloaking,

					'custom_theme'           => $post_data['theme_name'] ?? $lasso_url->display->theme,
					'disclosure_text'        => trim($post_data['disclosure_text'] ?? $lasso_url->display->disclosure_text),
					'badge_text'             => $post_data['badge_text'] ?? $lasso_url->display->badge_text,
					'buy_btn_text'           => $post_data['buy_btn_text'] ?? $lasso_url->display->primary_button_text,
					'second_btn_url'         => $post_data['second_btn_url'] ?? $lasso_url->display->secondary_url,
					'second_btn_text'        => $post_data['second_btn_text'] ?? $lasso_url->display->secondary_button_text,

					'third_btn_url'        	 => $post_data['third_btn_url'] ?? '',
					'third_btn_text'         => $post_data['third_btn_text'] ?? '',
					'fourth_btn_url'         => $post_data['fourth_btn_url'] ?? '',
					'fourth_btn_text'        => $post_data['fourth_btn_text'] ?? '',

					'show_price'             => $post_data['show_price'] ?? $lasso_url->display->show_price,
					'show_disclosure'        => $post_data['show_disclosure'] ?? $lasso_url->display->show_disclosure,
					'show_description'       => $show_description,
					'enable_sponsored'       => $post_data['enable_sponsored'] ?? $lasso_url->enable_sponsored,

					'updated_on'	=> $post_data['updated_on'] ?? date('Y-m-d')
				),
			);
		}

		if ($apple_product) {
			$lasso_post = array(
				'post_title'   => $apple_product['title'],
				'post_type'    => LASSO_POST_TYPE,
				'post_name'    => $apple_product['title'],
				'post_content' => '',
				'post_status'  => 'publish',
				'meta_input'   => array(
					'lasso_custom_redirect'  => $apple_product['base_url'],
					'lasso_final_url'        => $apple_product['base_url'],

					'rating' => $apple_product['rating'],
					'developer' => $apple_product['developer'],
					'categories' => $apple_product['categories'],
					'version' => $apple_product['version'],
					'size' => $apple_product['size'],
					'screen_shots' => $apple_product['screen_shots'],
					'apple_btn_url' => $apple_product['base_url'],

					'affiliate_desc'         => $description,
					'price'                  => $apple_product['price'],
					'lasso_custom_thumbnail' => $apple_product['thumbnail'],

					'enable_nofollow'        => $post_data['enable_nofollow'] ?? $lasso_url->enable_nofollow,
					'open_new_tab'           => $post_data['open_new_tab'] ?? $lasso_url->open_new_tab,
					'enable_nofollow2'       => $post_data['enable_nofollow2'] ?? $lasso_url->enable_nofollow2,
					'open_new_tab2'          => $post_data['open_new_tab2'] ?? $lasso_url->open_new_tab2,

					'enable_nofollow3'       => $post_data['enable_nofollow3'] ?? true,
					'open_new_tab3'          => $post_data['open_new_tab3'] ?? true,
					'enable_nofollow4'       => $post_data['enable_nofollow4'] ?? true,
					'open_new_tab4'          => $post_data['open_new_tab4'] ?? true,
					'enable_nofollow_google' => $post_data['enable_nofollow_google'] ?? true,
					'open_new_tab_google'    => $post_data['open_new_tab_google'] ?? true,
					'enable_nofollow_apple'  => $post_data['enable_nofollow_apple'] ?? true,
					'open_new_tab_apple'     => $post_data['open_new_tab_apple'] ?? true,

					'link_cloaking'          => $post_data['link_cloaking'] ?? $lasso_url->link_cloaking,

					'custom_theme'           => $post_data['theme_name'] ?? $lasso_url->display->theme,
					'disclosure_text'        => trim($post_data['disclosure_text'] ?? $lasso_url->display->disclosure_text),
					'badge_text'             => $post_data['badge_text'] ?? $lasso_url->display->badge_text,
					'buy_btn_text'           => $post_data['buy_btn_text'] ?? $lasso_url->display->primary_button_text,
					'second_btn_url'         => $post_data['second_btn_url'] ?? $lasso_url->display->secondary_url,
					'second_btn_text'        => $post_data['second_btn_text'] ?? $lasso_url->display->secondary_button_text,

					'third_btn_url'        	 => $post_data['third_btn_url'] ?? '',
					'third_btn_text'         => $post_data['third_btn_text'] ?? '',
					'fourth_btn_url'         => $post_data['fourth_btn_url'] ?? '',
					'fourth_btn_text'        => $post_data['fourth_btn_text'] ?? '',

					'show_price'             => $post_data['show_price'] ?? $lasso_url->display->show_price,
					'show_disclosure'        => $post_data['show_disclosure'] ?? $lasso_url->display->show_disclosure,
					'show_description'       => $show_description,
					'enable_sponsored'       => $post_data['enable_sponsored'] ?? $lasso_url->enable_sponsored,

					'updated_on'	=> $post_data['updated_on'] ?? date('Y-m-d')
				),
			);
		}

		if ($google_product && $apple_product) {
			$lasso_post = array(
				'post_title'   => $apple_product['title'],
				'post_type'    => LASSO_POST_TYPE,
				'post_name'    => $apple_product['title'],
				'post_content' => '',
				'post_status'  => 'publish',
				'meta_input'   => array(
					'lasso_custom_redirect'  => $url,
					'lasso_final_url'        => $get_final_url,

					'rating' => !empty($google_product['rating']) ? $google_product['rating'] : $apple_product['rating'],
					'developer' => !empty($google_product['developer']) ? $google_product['developer'] : $apple_product['developer'],
					'categories' => $google_product['categories'],
					'version' => $apple_product['version'],
					'size' => $apple_product['size'],
					'screen_shots' => $google_product['screen_shots'],
					'apple_btn_url' => $apple_product['base_url'],
					'google_btn_url' => $get_final_url,

					'affiliate_desc'         => $description,
					'price'                  => $apple_product['price'],
					'lasso_custom_thumbnail' => $apple_product['thumbnail'],

					'enable_nofollow'        => $post_data['enable_nofollow'] ?? $lasso_url->enable_nofollow,
					'open_new_tab'           => $post_data['open_new_tab'] ?? $lasso_url->open_new_tab,
					'enable_nofollow2'       => $post_data['enable_nofollow2'] ?? $lasso_url->enable_nofollow2,
					'open_new_tab2'          => $post_data['open_new_tab2'] ?? $lasso_url->open_new_tab2,

					'enable_nofollow3'       => $post_data['enable_nofollow3'] ?? true,
					'open_new_tab3'          => $post_data['open_new_tab3'] ?? true,
					'enable_nofollow4'       => $post_data['enable_nofollow4'] ?? true,
					'open_new_tab4'          => $post_data['open_new_tab4'] ?? true,
					'enable_nofollow_google' => $post_data['enable_nofollow_google'] ?? true,
					'open_new_tab_google'    => $post_data['open_new_tab_google'] ?? true,
					'enable_nofollow_apple'  => $post_data['enable_nofollow_apple'] ?? true,
					'open_new_tab_apple'     => $post_data['open_new_tab_apple'] ?? true,

					'link_cloaking'          => $post_data['link_cloaking'] ?? $lasso_url->link_cloaking,

					'custom_theme'           => $post_data['theme_name'] ?? $lasso_url->display->theme,
					'disclosure_text'        => trim($post_data['disclosure_text'] ?? $lasso_url->display->disclosure_text),
					'badge_text'             => $post_data['badge_text'] ?? $lasso_url->display->badge_text,
					'buy_btn_text'           => $post_data['buy_btn_text'] ?? $lasso_url->display->primary_button_text,
					'second_btn_url'         => $post_data['second_btn_url'] ?? $lasso_url->display->secondary_url,
					'second_btn_text'        => $post_data['second_btn_text'] ?? $lasso_url->display->secondary_button_text,

					'third_btn_url'        	 => $post_data['third_btn_url'] ?? '',
					'third_btn_text'         => $post_data['third_btn_text'] ?? '',
					'fourth_btn_url'         => $post_data['fourth_btn_url'] ?? '',
					'fourth_btn_text'        => $post_data['fourth_btn_text'] ?? '',

					'show_price'             => $post_data['show_price'] ?? $lasso_url->display->show_price,
					'show_disclosure'        => $post_data['show_disclosure'] ?? $lasso_url->display->show_disclosure,
					'show_description'       => $show_description,
					'enable_sponsored'       => $post_data['enable_sponsored'] ?? $lasso_url->enable_sponsored,

					'updated_on'	=> $post_data['updated_on'] ?? date('Y-m-d')
				),
			);
		}

		if ($lasso_url->lasso_id > 0 && strpos($lasso_url->guid, site_url()) === false) {
			$query = "update {$wpdb->posts} set guid = '' where ID = {$lasso_url->lasso_id}";
			Model::query($query);
		}

		if ($is_update) {
			$lasso_post['ID'] = $post_id;
			clean_post_cache($post_id);
		}

		if ($amazon_product_id) {
			$product = $lasso_amazon_api->get_amazon_product_from_db($amazon_product_id, $get_final_url);
			$url     = Lasso_Amazon_Api::get_amazon_product_url($get_final_url, true, $is_update);

			if (!$product || is_null($product)) {
				if (!$amz_product) {
					$amz_product     = $lasso_amazon_api->fetch_product_info($amazon_product_id, true, false, $get_final_url);
					$amz_product_qty = intval($amz_product['product']['quantity'] ?? 0);

					if ('NotFound' === $amz_product['error_code']) {
						$status                    = 404;
						$force_update_issue_status = true;
					} elseif (0 === $amz_product_qty) {
						$status                    = '000';
						$force_update_issue_status = true;
					}

					$amz_product = $amz_product['product'];
				}

				$product['default_product_name'] = $amz_product['title'] ?? $lasso_post['post_title'];
				$product['default_image']        = $amz_product['image'] ?? LASSO_DEFAULT_THUMBNAIL;

				$product_data = $product;
			} elseif ($product) {
				$status            = 200;
				$check_link_status = false;

				if ('0' !== $product['out_of_stock']) {
					$status                    = '000';
					$force_update_issue_status = true;
				}

				$product_data = array(
					'product_id'      => $product['amazon_id'],
					'title'           => $product['default_product_name'],
					'price'           => $product['latest_price'],
					'default_url'     => $product['base_url'],
					'url'             => $url,
					'image'           => $product['default_image'],
					'quantity'        => '0' === $product['out_of_stock'] ? 200 : 0,
					'is_prime'        => $product['is_prime'],
					'currency'        => $product['currency'],
					'savings_amount'  => $product['savings_amount'],
					'savings_percent' => $product['savings_percent'],
					'savings_basis'   => $product['savings_basis'],
					'features'        => $product['features'],
					'is_manual'       => 1,
				);
			}

			$lasso_amazon_api->update_amazon_product_in_db($product_data);

			$lasso_amazon_url = $lasso_url->amazon->monetized_url ?? $lasso_url->target_url;
			if (($is_update && $lasso_amazon_url !== $url
					&& Lasso_Amazon_Api::get_product_id_by_url($url) !== Lasso_Amazon_Api::get_product_id_by_url($lasso_amazon_url)
				)
				|| $is_new
			) {
				$lasso_post['post_title']                           = !empty($product['default_product_name'] ?? '') && !($data['use_defined_affiliate_name'] ?? false)
					? $product['default_product_name'] : $lasso_post['post_title'];
				$lasso_post['meta_input']['lasso_custom_thumbnail'] = $product['default_image'];
			}

			$lasso_post['meta_input']['amazon_product_id']           = $amazon_product_id;
			$lasso_post['meta_input']['amazon_product_last_updated'] = current_time(gmdate('Y-m-d H:i:s'));
			$lasso_post['meta_input']['affiliate_link_type']         = LASSO_AMAZON_PRODUCT_TYPE;
		} elseif ($extend_product_type && $extend_product_id) {
			$product = Lasso_Extend_Product::get_extend_product_from_db($extend_product_type, $extend_product_id);

			if ($product) {
				$status            = 200;
				$check_link_status = false;

				if ('0' !== $product['out_of_stock']) {
					$status                    = '000';
					$force_update_issue_status = true;
				}

				$lasso_extend_product->update_extend_product_in_db(
					array(
						'id'           => $product['product_id'],
						'product_type' => $product['product_type'],
						'product_id'   => $product['product_id'],
						'title'        => $product['default_product_name'],
						'price'        => $product['latest_price'],
						'default_url'  => $product['base_url'],
						'url'          => $url,
						'image'        => $product['default_image'],
						'quantity'     => '0' === $product['out_of_stock'] ? 200 : 0,
						'is_manual'    => $product['is_manual'],
					)
				);
			} elseif (!$product || is_null($product)) {
				if (!$extend_product) {
					$extend_product     = $lasso_extend_product->fetch_product_info($extend_product_url, true);
					$extend_product_qty = intval($extend_product['product']['quantity'] ?? 0);

					if ('NotFound' === $extend_product['error_code']) {
						$status                    = 404;
						$force_update_issue_status = true;
					} elseif (0 === $extend_product_qty) {
						$status                    = '000';
						$force_update_issue_status = true;
					}

					$extend_product = $extend_product['product'];
				}

				$product['default_product_name'] = $extend_product['title'] ?? $lasso_post['title'];
				$product['default_image']        = $extend_product['image'] ?? $lasso_post['img_src'];
			}

			if ($is_update && $lasso_url->target_url !== $url || $is_new) {
				$lasso_post['post_title']                           = !empty($product['default_product_name'] ?? '') && !($data['use_defined_affiliate_name'] ?? false)
					? $product['default_product_name'] : $lasso_post['post_title'];
				$lasso_post['meta_input']['lasso_custom_thumbnail'] = $product['default_image'];
			}

			$lasso_post['meta_input']['extend_product_type']         = $extend_product_type;
			$lasso_post['meta_input']['extend_product_id']           = $extend_product_id;
			$lasso_post['meta_input']['extend_product_last_updated'] = current_time(gmdate('Y-m-d H:i:s'));
			$lasso_post['meta_input']['affiliate_link_type']         = LASSO_BASIC_LINK_TYPE;
			delete_post_meta($post_id, 'amazon_product_id');
		} else {
			$lasso_post['meta_input']['affiliate_link_type'] = LASSO_BASIC_LINK_TYPE;
			delete_post_meta($post_id, 'amazon_product_id');
		}

		if (self::keep_affiliate_url($original_url, $get_final_url)) {
			$lasso_post['meta_input']['affiliate_link_type']   = LASSO_BASIC_LINK_TYPE;
			$lasso_post['meta_input']['lasso_custom_redirect'] = $original_url;

			$is_final_url_amazon_link = Lasso_Amazon_Api::is_amazon_url($get_final_url);
			$lasso_amazon_url         = $lasso_url->amazon->monetized_url ?? $lasso_url->target_url;
			if ($is_final_url_amazon_link && Lasso_Amazon_Api::get_product_id_by_url($lasso_amazon_url) !== Lasso_Amazon_Api::get_product_id_by_url($get_final_url)) {
				$post_data['affiliate_name'] = !empty($product['default_product_name'] ?? '') ? $product['default_product_name'] : $lasso_post['post_title'];
			}
			$lasso_post['post_title'] = $post_data['affiliate_name'] ?? $lasso_post['post_title'];
		}

		if ('' === $lasso_post['meta_input']['lasso_custom_thumbnail']) {
			$domain_affiliate_programs_row_result = Affiliate_Programs::get_row_by_domain($get_final_url);
			$logo_affiliate_programs              = $domain_affiliate_programs_row_result->image_url ?? '';

			$lasso_post['meta_input']['lasso_custom_thumbnail'] = $logo_affiliate_programs
				? $logo_affiliate_programs
				: $lasso_post['meta_input']['lasso_custom_thumbnail'];
		}

		if (!Lasso_Amazon_Api::is_amazon_url($get_final_url) && $is_update && $duplicate_post = Lasso_Helper::the_slug_exists($lasso_post['post_name'], $post_id)) {
			$warning = 'Permalink <a href="' . get_edit_post_link($duplicate_post['ID']) . '" class="white underline" target="_blank"><strong>' . $duplicate_post['post_name'] . '</strong></a> is being used by <strong>' . $duplicate_post['post_type'] . '</strong>. We updated the permalink to avoid a conflict.';
		}

		$name_category = $lasso_post['meta_input']['categories'];
		$getCategories = get_term_by('name', $name_category, 'product_cat');
		$post_id = self::lasso_insert_post($lasso_post, true);

		if (!$getCategories) {
			wp_insert_term(
				$name_category,
				'product_cat'
			);
		}

		if (!is_wp_error($post_id) && $post_id > 0) {
			$product_id_col   = $amazon_product_id
				? $amazon_product_id
				: ($extend_product_id ? $extend_product_id : '');
			$product_type_col = Lasso_Amazon_Api::is_amazon_url($get_final_url)
				? Lasso_Amazon_Api::PRODUCT_TYPE
				: ($extend_product_type ? $extend_product_type : '');

			$url_detail_redirect_url = $url;
			if (self::keep_affiliate_url($url, $get_final_url)) {
				$url_detail_redirect_url = $lasso_post['meta_input']['lasso_custom_redirect'];
			}
			$lasso_db->update_url_details($post_id, $url_detail_redirect_url, $affiliate_homepage, $is_opportunity, $product_id_col, $product_type_col);

			$url_final_without_arguments = Lasso_Helper::format_url_for_checking_duplication($get_final_url, $url);
			$enable_duplicate_link       = Lasso_Setting::lasso_get_setting('check_duplicate_link', false);
			if (
				!empty($url_final_without_arguments)
				&& is_string($url_final_without_arguments)
				&& !Lasso_Amazon_Api::is_amazon_url($get_final_url)
				&& !Lasso_Extend_Product::get_extend_product_type_from_url($get_final_url)
				&& !$enable_duplicate_link
			) {
				update_post_meta($post_id, Url_Details::META_KEY_URL_WITHOUT_ARGUMENTS, $url_final_without_arguments);
			}

			wp_set_object_terms($post_id, $term, LASSO_CATEGORY);

			$all_cat_item = Lasso_Category_Order::get_by_item($post_id);
			$slugs        = wp_list_pluck($all_cat_item, 'parent_slug');
			if (1 <= count($term)) {
				foreach ($term as $term_id) {
					$lasso_group = Lasso_Group::get_by_id($term_id);
					if ($lasso_group) {
						$slug = $lasso_group->get_slug();

						$lasso_term      = Lasso_Category_Order::get_by_item($post_id);
						$lasso_term_slug = array_filter(
							$lasso_term,
							function ($v) use ($slug) {
								return $v->parent_slug === $slug;
							}
						);

						if (0 === count($lasso_term_slug) && !in_array($slug, $slugs, true)) {
							$order_max = Lasso_Category_Order::get_max_order_by_slug($slug);
							$lasso_cat_order->set_item_id($post_id);
							$lasso_cat_order->set_parent_slug($lasso_group->get_slug());
							$lasso_cat_order->set_term_order(intval($order_max));
							$lasso_cat_order->insert();
						} else {
							$key = array_search($slug, $slugs, true);
							if (false !== $key) {
								unset($slugs[$key]);
							}
						}
					}
				}
			}

			if (0 < count($slugs)) {
				Lasso_Category_Order::delete_category_order($post_id, $slugs);
			}

			if ($thumbnail_id > 0) {
				set_post_thumbnail($post_id, $thumbnail_id);
				$image_url = wp_get_attachment_url($thumbnail_id);
				update_post_meta($post_id, 'lasso_custom_thumbnail', $image_url);
			} else {
				delete_post_thumbnail($post_id);
			}

			$is_slug_changed = LASSO_AMAZON_PRODUCT_TYPE !== $lasso_url->link_type && $lasso_url->slug !== $post_data['permalink'];
			if (
				$lasso_url->enable_nofollow !== $enable_nofollow
				|| $lasso_url->open_new_tab !== $open_new_tab
				|| $lasso_url->enable_nofollow2 !== $enable_nofollow2
				|| $lasso_url->open_new_tab2 !== $open_new_tab2
				|| $lasso_url->link_cloaking !== $link_cloaking
				|| $lasso_url->target_url !== $post_data['affiliate_url']
				|| $lasso_url->enable_sponsored !== $enable_sponsored
				|| $is_slug_changed
			) {

				$scan = new Lasso_Process_Scan_Link();
				$scan->scan_post_page($post_id);
			}

			if ($is_slug_changed) {
				update_post_meta($post_id, 'lasso_old_slug', $lasso_url->slug);
			}

			// ? Handle URL issues on save
			Lasso_Helper::write_log('Check link issue ' . ($check_link_status ? 'true' : 'false'), 'lasso_save_post');
			if ($check_link_status) {
				Lasso_Helper::write_log('Check status issue ' . $status, 'lasso_save_post');
				$cron->check_issues($post_id, $status);
			} elseif ($force_update_issue_status || ($is_new && '' !== $status && 200 !== intval($status))) {
				Lasso_Helper::write_log('Force update issue for new link ' . $status, 'lasso_save_post');
				$cron->check_issues($post_id, $status, true);
			}

			// ? Create webp image
			Lasso_Helper::create_lasso_webp_image($post_id);
		}

		$time_end = microtime(true);
		// ? dividing with 60 will give the execution time in minutes otherwise seconds
		$execution_time = round($time_end - $time_start, 2);
		Lasso_Helper::write_log($post_id, 'lasso_save_post');
		Lasso_Helper::write_log('Save takes ' . $execution_time, 'lasso_save_post');
		Lasso_Helper::write_log('Save Lasso post - End', 'lasso_save_post');

		if ($is_ajax_request) {
			if (!is_wp_error($post_id)) {
				Lasso_Cache_Per_Process::get_instance()->un_set('wp_post_' . $post_id);
			}
			$this->check_error_and_response_ajax($post_id, $error, $warning);
		}

		return $post_id;
	}

	/**
	 * Check whether we should keep the affiliate URL, do not use final URL
	 *
	 * @param string $url       URL.
	 * @param string $final_url Final URL.
	 */
	public static function keep_affiliate_url($url, $final_url)
	{
		$base_domain       = Lasso_Helper::get_base_domain($url);
		$keep_original_url = Lasso_Setting::lasso_get_setting('keep_original_url');

		$domains = array_merge(Setting_Enum::DOMAIN_ALLOW_ORIGINAL_URL_IN_LASSO_DETAIL, $keep_original_url);
		$domains = array_unique($domains);

		$use_original_url     = in_array($base_domain, $domains, true);
		$affiliate            = new Lasso_Affiliates();
		$affiliate_slug       = $affiliate->is_affiliate_link($url, false);
		$is_auto_monetize_url = boolval($affiliate_slug);

		return $use_original_url || $is_auto_monetize_url;
	}

	/**
	 * GET new lasso link
	 */
	public function get_new_affiliate_link_url()
	{
		$affiliate_link_url = add_query_arg(
			array(
				'post_type' => LASSO_POST_TYPE,
				'page'      => 'url-details',
			),
			admin_url('edit.php')
		);

		return $affiliate_link_url;
	}

	/**
	 * Check post id to see whether it gets an error or not, then send it to user via ajax
	 *
	 * @param int|object $post_id       Post id.
	 * @param string     $error         Error message.
	 * @param string     $warning       Warning message.
	 * @param array      $optional_data Optional data.
	 */
	private function check_error_and_response_ajax($post_id, $error = '', $warning = '', $optional_data = array())
	{
		if (!is_wp_error($post_id) && $post_id > 0) {
			// ? The post is valid
			$lasso_settings = new Lasso_Setting();
			$lasso_db       = new Lasso_DB();

			clean_post_cache($post_id);
			$lasso_url = self::get_lasso_url($post_id);

			// ? Tab Counts
			$sql             = $lasso_db->get_lasso_link_counts($post_id);
			$location_counts = Model::get_row($sql);

			$opportunity_sql   = $lasso_db->get_link_opportunities_query('', $post_id);
			$opportunity_count = Model::get_count($opportunity_sql);

			if (!is_object($location_counts)) {
				$location_counts = new stdClass();
			}

			$location_counts->opportunities = $opportunity_count;
			$data_response                  = array(
				'success'     => 1,
				'post'        => $lasso_url,
				'redirect_to' => $lasso_settings->get_dashboard_page(),
				'count'       => $location_counts,
				'error'       => $error,
				'warning'     => $warning,
			);
			if (!empty($optional_data)) {
				$data_response = array_merge($data_response, $optional_data);
			}
			wp_send_json_success($data_response);
		} else {
			$e     = $post_id->get_error_messages();
			$error = '' === $error ? ($e[0] ?? '') : $error;
			wp_send_json_error($error);
		}
	}

	/**
	 * Search attributes
	 *
	 * @param int    $post_id Post id. Default to 0.
	 * @param string $search_key Search text. Default to empty.
	 * @param int    $limit Number of items each request. Default to 1.
	 * @param int    $page Number of page. Default to 1.
	 */
	public function search_attributes($post_id = 0, $search_key = '', $limit = 1, $page = 1)
	{
		$post_id     = intval($post_id);
		$postmeta    = Model::get_wp_table_name('postmeta');
		$posts       = Model::get_wp_table_name('posts');
		$search      = '%' . Model::esc_like($search_key) . '%';
		$search_atts = 'lasso_attributes_%';

		if (0 === $post_id) {
			$start_index = $limit * ($page - 1);
			// @codingStandardsIgnoreStart
			$prepare     = Model::prepare(
				"
				SELECT $postmeta.`post_id`, $posts.`post_title`, $postmeta.`meta_value`
				FROM $postmeta
				LEFT JOIN $posts
				ON $postmeta.post_id = $posts.ID
				WHERE $postmeta.`meta_key` LIKE %s 
					AND ($postmeta.`meta_value` LIKE %s OR $posts.`post_title` LIKE %s)
				LIMIT %d, %d
			",
				$search_atts,
				$search,
				$search,
				$start_index,
				$limit
			);
			// @codingStandardsIgnoreEnd
		} else {
			// @codingStandardsIgnoreStart
			$prepare = Model::prepare(
				"
				SELECT `post_id`, `meta_value`
				FROM $postmeta
				WHERE `meta_key` LIKE %s AND `meta_value` LIKE %s
					AND `post_id` = %d
			",
				$search_atts,
				$search,
				$post_id
			);
			// @codingStandardsIgnoreEnd
		}
		$result = Model::get_results($prepare, ARRAY_A); // phpcs:ignore

		return $result;
	}

	/**
	 * Check whether the post id id Lasso post or not.
	 *
	 * @param int $post_id Post id.
	 */
	public static function is_lasso_post($post_id)
	{
		return LASSO_POST_TYPE === get_post_type($post_id);
	}

	/**
	 * Basic Link or Amazon Product
	 *
	 * @param int $post_id Post id.
	 */
	public static function get_lasso_type($post_id)
	{
		$lasso_db = new Lasso_DB();

		if (!self::is_lasso_post($post_id)) {
			return false;
		}

		$lasso_url_details = $lasso_db->get_url_details($post_id);
		$target_url        = $lasso_url_details->redirect_url ?? '';
		$lasso_type        = Lasso_Amazon_Api::is_amazon_url($target_url) && Lasso_Amazon_Api::get_product_id_by_url($target_url)
			? LASSO_AMAZON_PRODUCT_TYPE : LASSO_BASIC_LINK_TYPE;

		return $lasso_type;
	}

	/**
	 * Get Lasso post id by url
	 *
	 * @param string $url URL.
	 * @param int    $default_id Default id. Default to 0.
	 */
	public static function get_lasso_post_id_by_url($url, $default_id = 0)
	{
		// ? Get final url for amazon shortlink from cache
		$amazon_shortlink_final_url_cached = Lasso_Amazon_Api::get_shortlink_final_url_cached($url);
		$url                               = $amazon_shortlink_final_url_cached ? $amazon_shortlink_final_url_cached : $url;

		$lasso_db = new Lasso_DB();

		// ? Get post id from url
		$lasso_id = $default_id;
		$url      = trim($url, '/');
		$url      = str_replace('&amp;', '&', $url);
		$parse    = wp_parse_url($url);
		$path     = '';

		if (strpos($url, home_url()) !== false && isset($parse['path'])) {
			$path = $parse['path'];
			$path = trim($path, '/');

			$explode = explode('/', $path);
			$slug    = end($explode);
			$lasso   = get_page_by_path($slug, OBJECT, LASSO_POST_TYPE);

			$rewrite_slug = Lasso_Setting::lasso_get_setting('rewrite_slug');
			if (($rewrite_slug && $rewrite_slug !== $explode[0]) || (!$rewrite_slug && count($explode) === 2)) {
				$lasso = false;
			}

			if ($lasso) {
				$lasso_id = LASSO_POST_TYPE === get_post_type($lasso->ID) ? $lasso->ID : $lasso_id;
			}
		}

		if (0 === $lasso_id) {
			$lasso_post = $lasso_db->get_lasso_by_uri($path); // ? by redirect url
			$lasso_id   = $lasso_post->ID ?? $lasso_id;
		}

		if (0 === $lasso_id) {
			$detail   = $lasso_db->get_url_details_by_url($url); // ? by redirect url
			$lasso_id = $detail->lasso_id ?? $lasso_id;
		}

		// @codingStandardsIgnoreStart
		// if ( 0 === $lasso_id ) {
		// 	$temp_id  = $lasso_db->get_post_id_by_original_url( $url ); // ? by redirect url
		// 	$lasso_id = $temp_id ?? $lasso_id;
		// }
		// @codingStandardsIgnoreEnd

		if (0 === $lasso_id) {
			$obj     = (new Auto_Monetize())->get_one_by_col('url', $url);
			$temp_id = $obj ? $obj->get_lasso_id() : $lasso_id;
			$temp_id = intval($temp_id);

			$lasso_id = $temp_id > 0 && LASSO_POST_TYPE === get_post_type($temp_id) ? $temp_id : $lasso_id;
		}

		if (0 === $lasso_id && !Lasso_Helper::is_internal_full_link($url)) {
			$rewrite_slug = Lasso_Setting::lasso_get_setting('rewrite_slug');
			if ($rewrite_slug) {
				$url_parts = explode('/', $url);
				if ($rewrite_slug === $url_parts[0]) {
					$position = strpos($url, '/');
					$url      = substr($url, $position, strlen($url) - $position);
				}
			}
			$obj      = get_page_by_path($url, OBJECT, LASSO_POST_TYPE);
			$lasso_id = $obj ? $obj->ID : $lasso_id;
		}

		if (0 === $lasso_id && Lasso_Amazon_Api::is_amazon_url($url)) {
			$amazon_product_id = Lasso_Amazon_Api::get_product_id_country_by_url($url);
			$lasso_post_id     = $lasso_db->get_lasso_id_by_product_id_and_type($amazon_product_id, Lasso_Amazon_Api::PRODUCT_TYPE, $url);
			$lasso_id          = $lasso_post_id ? $lasso_post_id : $default_id;
		}

		if (0 === $lasso_id) {
			$extend_product_type = Lasso_Extend_Product::get_extend_product_type_from_url($url);
			$extend_product_id   = Lasso_Extend_Product::get_extend_product_id_by_url($url);

			if ($extend_product_type && $extend_product_id) {
				$lasso_post_id = $lasso_db->get_lasso_id_by_product_id_and_type($extend_product_id, $extend_product_type);
				$lasso_id      = $lasso_post_id ? $lasso_post_id : $default_id;
			}
		}

		$tmp_url = Lasso_Helper::get_final_url_from_url_param($url);
		$tmp_url = $tmp_url ? $tmp_url : $url;
		$k       = Lasso_Amazon_Api::is_amazon_search_page($tmp_url);
		if (0 === $lasso_id && $k) {
			$lasso_id = self::get_lasso_id_by_amazon_search_text($k, $lasso_id);
		}

		return intval($lasso_id);
	}

	/**
	 * Get lasso id by Amazon search text
	 *
	 * @param string $search_text Search text.
	 * @param int    $lasso_id    Lasso id.
	 */
	public static function get_lasso_id_by_amazon_search_text($search_text, $lasso_id)
	{
		$lasso_db = new Lasso_DB();

		$k_query = str_replace(' ', '+', $search_text);
		$detail  = $lasso_db->get_url_details_by_url('%/s?k=' . $k_query . '&%'); // ? by redirect url
		if (!$detail) {
			$detail = $lasso_db->get_url_details_by_url('%/s?k=' . $k_query . '%'); // ? by redirect url
		}

		if (!$detail) {
			$k_query = str_replace(' ', '%20', $search_text);
			$detail  = $lasso_db->get_url_details_by_url('%/s?k=' . str_replace('%', '\%', $k_query) . '&%'); // ? by redirect url
			if (!$detail) {
				$detail = $lasso_db->get_url_details_by_url('%/s?k=' . str_replace('%', '\%', $k_query) . '%'); // ? by redirect url
			}
		}

		$db_k     = Lasso_Amazon_Api::is_amazon_search_page($detail->redirect_url ?? '');
		$db_k     = $db_k ? rawurlencode($db_k) : $db_k;
		$lasso_id = $detail && $k_query === $db_k ? $detail->lasso_id : $lasso_id;

		return $lasso_id;
	}

	/**
	 * Whether the link is enabled nofollow or not
	 *
	 * @param int $lasso_id Lasso post id.
	 */
	public static function enable_nofollow_noindex($lasso_id)
	{
		$enable_nofollow_noindex = get_post_meta($lasso_id, 'enable_nofollow', true);

		return 1 === intval($enable_nofollow_noindex);
	}

	/**
	 * Whether the link is opened in a new tab or not
	 *
	 * @param int $lasso_id Lasso post id.
	 */
	public static function open_new_tab($lasso_id)
	{
		$open_new_tab = get_post_meta($lasso_id, 'open_new_tab', true);

		return 1 === intval($open_new_tab);
	}

	/**
	 * Get amazon product if by Lasso post id
	 *
	 * @param int $lasso_id Lasso post id.
	 */
	public static function get_amazon_id($lasso_id)
	{
		$lasso_db           = new Lasso_DB();
		$lasso_post_details = $lasso_db->get_url_details($lasso_id);
		$details_product_id = $lasso_post_details->product_id ?? '';

		$amazon_product_id = get_post_meta($lasso_id, 'amazon_product_id', true);

		return '' === $details_product_id ? $amazon_product_id : $details_product_id;
	}

	/**
	 * Get Lasso link from old link
	 *
	 * @param string $current_url Current url.
	 */
	public static function get_lasso_link_from_old_link($current_url)
	{
		$lasso_import_revert_db = Model::get_wp_table_name(LASSO_REVERT_DB);
		$posts_tbl              = Model::get_wp_table_name('posts');

		$parse_url   = wp_parse_url($current_url);
		$host        = isset($parse_url['host']) ? $parse_url['host'] : '';
		$path        = isset($parse_url['path']) ? $parse_url['path'] : '';
		$current_url = $host . $path;
		$current_url = trim($current_url, '/');

		$query   = "
			SELECT lasso_id
			FROM $lasso_import_revert_db
			LEFT JOIN $posts_tbl
			ON $lasso_import_revert_db.lasso_id = $posts_tbl.ID
			WHERE TRIM('https://' 
				FROM TRIM('http://' 
					FROM TRIM('www.' 
						FROM TRIM('/' FROM old_uri)
					)
				)
			) = %s AND $posts_tbl.post_type = %s
			ORDER BY revert_dt DESC
		";
		$prepare = Model::prepare($query, $current_url, LASSO_POST_TYPE); // phpcs:ignore
		$result  = Model::get_row($prepare, ARRAY_A, true); // phpcs:ignore

		if (null !== $result) {
			$lasso_id = $result['lasso_id'];
			return get_post_type($lasso_id) === LASSO_POST_TYPE ? get_the_permalink($lasso_id) : false;
		}

		return false;
	}

	/**
	 * Get thumbnail of Lasso post
	 *
	 * @param int    $lasso_id Lasso post id.
	 * @param string $lasso_type Lasso post type.
	 * @param array  $amazon_product Amazon product.
	 */
	public static function get_lasso_thumbnail($lasso_id, $lasso_type, $amazon_product)
	{
		$has_post_thumbnail = has_post_thumbnail($lasso_id);
		$custom_thumbnail   = get_post_meta($lasso_id, 'lasso_custom_thumbnail', true);
		$default_thumbnail  = LASSO_DEFAULT_THUMBNAIL;
		$thumbnail          = $default_thumbnail;

		if ('' !== $custom_thumbnail) { // ? Lasso custom thumbnail (default Lasso post)
			$thumbnail = $custom_thumbnail;
		} elseif ($has_post_thumbnail) { // ? WP thumbnail
			$thumbnail = get_the_post_thumbnail_url($lasso_id, 'large');
		} else { // ? Lasso thumbnail from imported posts
			$lasso_image_url    = get_post_meta($lasso_id, 'lasso_thumbnail_url', true);
			$lasso_thumbnail_id = get_post_meta($lasso_id, 'lasso_thumbnail_id', true);
			$image_url          = wp_get_attachment_url($lasso_thumbnail_id);
			if ('' !== $lasso_image_url) {
				$thumbnail = $lasso_image_url;
			} elseif ('' !== $lasso_thumbnail_id && $image_url) {
				$thumbnail = $image_url;
			}

			// ? thumbnail of Amazon product
			if (LASSO_AMAZON_PRODUCT_TYPE === $lasso_type) {
				$thumbnail = isset($amazon_product['image']) && $amazon_product['image'] !== $default_thumbnail
					? $amazon_product['image'] : $default_thumbnail;
			}
		}

		$thumbnail = trim($thumbnail);

		/*
		 * The image name upload by customer can be contains special characters like: –(en dash), —(em dash) and need to go use
		 * filter_var($thumbnail, FILTER_SANITIZE_URL) to remove all illegal characters from a thumbnail url
		 * before validate by filter_var( $url, FILTER_VALIDATE_URL ) inside Lasso_Helper::validate_url
		 * That make sure validate_url function does not misunderstood this is invalid url
		 */
		$thumbnail_to_validate = filter_var($thumbnail, FILTER_SANITIZE_URL);

		if (!Lasso_Helper::validate_url($thumbnail_to_validate) && strpos($thumbnail, 'data:image') !== 0) {
			$thumbnail = $default_thumbnail;
		}

		// ? fix https issue, the old link may be stored as http protocol, we should update to the current protocol
		if (Lasso_Helper::get_base_domain($thumbnail) === Lasso_Helper::get_base_domain(site_url()) && false !== strpos(site_url(), 'https://')) {
			$thumbnail = str_replace('http://', 'https://', $thumbnail);
		}

		return $thumbnail;
	}

	/**
	 * Get thumbnail id of a Lasso post
	 *
	 * @param int    $lasso_id Lasso post id.
	 * @param string $lasso_type Lasso post type.
	 */
	public static function get_lasso_thumbnail_id($lasso_id, $lasso_type)
	{
		$has_post_thumbnail = has_post_thumbnail($lasso_id);
		$thumbnail_id       = '';

		if ($has_post_thumbnail && LASSO_AMAZON_PRODUCT_TYPE !== $lasso_type) { // ? WP thumbnail
			$thumbnail_id = get_post_thumbnail_id($lasso_id);
		}

		return $thumbnail_id;
	}

	/**
	 * Send error via ajax request
	 *
	 * @param string $error_message Error message.
	 */
	private function lasso_ajax_error($error_message)
	{
		wp_send_json_success(
			array(
				'status' => 0,
				'error'  => $error_message,
			)
		);
	}

	/**
	 * Insert or update a Lasso post.
	 *
	 * If the $postarr parameter has 'ID' set to a value, then post will be updated.
	 *
	 * You can set the post date manually, by setting the values for 'post_date'
	 * and 'post_date_gmt' keys. You can close the comments or open the comments by
	 * setting the value for 'comment_status' key.
	 *
	 * @since 1.0.0
	 * @since 4.2.0 Support was added for encoding emoji in the post title, content, and excerpt.
	 * @since 4.4.0 A 'meta_input' array can now be passed to `$postarr` to add post meta data.
	 *
	 * @see sanitize_post()
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param array $postarr {
	 *     An array of elements that make up a post to update or insert.
	 *
	 *     @type int    $ID                    The post ID. If equal to something other than 0,
	 *                                         the post with that ID will be updated. Default 0.
	 *     @type int    $post_author           The ID of the user who added the post. Default is
	 *                                         the current user ID.
	 *     @type string $post_date             The date of the post. Default is the current time.
	 *     @type string $post_date_gmt         The date of the post in the GMT timezone. Default is
	 *                                         the value of `$post_date`.
	 *     @type mixed  $post_content          The post content. Default empty.
	 *     @type string $post_content_filtered The filtered post content. Default empty.
	 *     @type string $post_title            The post title. Default empty.
	 *     @type string $post_excerpt          The post excerpt. Default empty.
	 *     @type string $post_status           The post status. Default 'draft'.
	 *     @type string $post_type             The post type. Default 'post'.
	 *     @type string $comment_status        Whether the post can accept comments. Accepts 'open' or 'closed'.
	 *                                         Default is the value of 'default_comment_status' option.
	 *     @type string $ping_status           Whether the post can accept pings. Accepts 'open' or 'closed'.
	 *                                         Default is the value of 'default_ping_status' option.
	 *     @type string $post_password         The password to access the post. Default empty.
	 *     @type string $post_name             The post name. Default is the sanitized post title
	 *                                         when creating a new post.
	 *     @type string $to_ping               Space or carriage return-separated list of URLs to ping.
	 *                                         Default empty.
	 *     @type string $pinged                Space or carriage return-separated list of URLs that have
	 *                                         been pinged. Default empty.
	 *     @type string $post_modified         The date when the post was last modified. Default is
	 *                                         the current time.
	 *     @type string $post_modified_gmt     The date when the post was last modified in the GMT
	 *                                         timezone. Default is the current time.
	 *     @type int    $post_parent           Set this for the post it belongs to, if any. Default 0.
	 *     @type int    $menu_order            The order the post should be displayed in. Default 0.
	 *     @type string $post_mime_type        The mime type of the post. Default empty.
	 *     @type string $guid                  Global Unique ID for referencing the post. Default empty.
	 *     @type array  $post_category         Array of category IDs.
	 *                           lasso_insert_post              Defaults to value of the 'default_category' option.
	 *     @type array  $tags_input            Array of tag names, slugs, or IDs. Default empty.
	 *     @type array  $tax_input             Array of taxonomy terms keyed by their taxonomy name. Default empty.
	 *     @type array  $meta_input            Array of post meta values keyed by their post meta key. Default empty.
	 * }
	 * @param bool  $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 * @return int|WP_Error The post ID on success. The value 0 or WP_Error on failure.
	 */
	public static function lasso_insert_post($postarr, $wp_error = false)
	{
		global $wpdb;

		// Capture original pre-sanitized array for passing into filters.
		$unsanitized_postarr = $postarr;

		$user_id = get_current_user_id();

		$defaults = array(
			'post_author'           => $user_id,
			'post_content'          => '',
			'post_content_filtered' => '',
			'post_title'            => '',
			'post_excerpt'          => '',
			'post_status'           => 'draft',
			'post_type'             => 'post',
			'comment_status'        => '',
			'ping_status'           => '',
			'post_password'         => '',
			'to_ping'               => '',
			'pinged'                => '',
			'post_parent'           => 0,
			'menu_order'            => 0,
			'guid'                  => '',
			'import_id'             => 0,
			'context'               => '',
		);

		$postarr = wp_parse_args($postarr, $defaults);

		unset($postarr['filter']);

		$postarr = sanitize_post($postarr, 'db');

		// Are we updating or creating?
		$post_ID = 0;
		$update  = false;
		$guid    = $postarr['guid'];

		if (!empty($postarr['ID'])) {
			$update = true;

			// Get the post ID and GUID.
			$post_ID     = $postarr['ID'];
			$post_before = get_post($post_ID);

			if (is_null($post_before)) {
				if ($wp_error) {
					return new WP_Error('invalid_post', __('Invalid post ID.'));
				}
				return 0;
			}

			$guid            = get_post_field('guid', $post_ID);
			$previous_status = get_post_field('post_status', $post_ID);
		} else {
			$previous_status = 'new';
		}

		$post_type = empty($postarr['post_type']) ? 'post' : $postarr['post_type'];

		$post_title   = $postarr['post_title'];
		$post_content = $postarr['post_content'];
		$post_excerpt = $postarr['post_excerpt'];

		if (isset($postarr['post_name'])) {
			$post_name = $postarr['post_name'];
		} elseif ($update) {
			// For an update, don't modify the post_name if it wasn't supplied as an argument.
			$post_name = $post_before->post_name;
		}

		$post_status = empty($postarr['post_status']) ? 'draft' : $postarr['post_status'];

		/*
		* Don't allow contributors to set the post slug for pending review posts.
		*
		* For new posts check the primitive capability, for updates check the meta capability.
		*/
		$post_type_object = get_post_type_object($post_type);

		/*
		* Create a valid post name. Drafts and pending posts are allowed to have
		* an empty post name.
		*/
		if (empty($post_name)) {
			if (!in_array($post_status, array('draft', 'pending', 'auto-draft'), true)) {
				$post_name = sanitize_title($post_title);
			} else {
				$post_name = '';
			}
		} else {
			// On updates, we need to check to see if it's using the old, fixed sanitization context.
			$check_name = sanitize_title($post_name, '', 'old-save');

			// phpcs:ignore
			if ($update && strtolower(urlencode($post_name)) == $check_name && get_post_field('post_name', $post_ID) == $check_name) {
				$post_name = $check_name;
			} else { // new post, or slug has changed.
				$post_name = sanitize_title($post_name);
			}
		}

		/*
		* If the post date is empty (due to having been new or a draft) and status
		* is not 'draft' or 'pending', set date to now.
		*/
		if (empty($postarr['post_date']) || '0000-00-00 00:00:00' === $postarr['post_date']) {
			if (empty($postarr['post_date_gmt']) || '0000-00-00 00:00:00' === $postarr['post_date_gmt']) {
				$post_date = current_time('mysql');
			} else {
				$post_date = get_date_from_gmt($postarr['post_date_gmt']);
			}
		} else {
			$post_date = $postarr['post_date'];
		}

		// Validate the date.
		$mm         = substr($post_date, 5, 2);
		$jj         = substr($post_date, 8, 2);
		$aa         = substr($post_date, 0, 4);
		$valid_date = wp_checkdate($mm, $jj, $aa, $post_date);
		if (!$valid_date) {
			if ($wp_error) {
				return new WP_Error('invalid_date', __('Invalid date.'));
			} else {
				return 0;
			}
		}

		if (empty($postarr['post_date_gmt']) || '0000-00-00 00:00:00' === $postarr['post_date_gmt']) {
			if (!in_array($post_status, get_post_stati(array('date_floating' => true)), true)) {
				$post_date_gmt = get_gmt_from_date($post_date);
			} else {
				$post_date_gmt = '0000-00-00 00:00:00';
			}
		} else {
			$post_date_gmt = $postarr['post_date_gmt'];
		}

		if ($update || '0000-00-00 00:00:00' === $post_date) {
			$post_modified     = current_time('mysql');
			$post_modified_gmt = current_time('mysql', 1);
		} else {
			$post_modified     = $post_date;
			$post_modified_gmt = $post_date_gmt;
		}

		$comment_status = 'closed';

		// These variables are needed by compact() later.
		$post_content_filtered = $postarr['post_content_filtered'];
		$post_author           = isset($postarr['post_author']) ? $postarr['post_author'] : $user_id;
		$ping_status           = empty($postarr['ping_status']) ? get_default_comment_status($post_type, 'pingback') : $postarr['ping_status'];
		$to_ping               = isset($postarr['to_ping']) ? sanitize_trackback_urls($postarr['to_ping']) : '';
		$pinged                = isset($postarr['pinged']) ? $postarr['pinged'] : '';
		$import_id             = isset($postarr['import_id']) ? $postarr['import_id'] : 0;

		$menu_order    = 0;
		$post_password = '';
		$post_parent   = 0;

		$new_postarr = array_merge(
			array(
				'ID' => $post_ID,
			),
			compact(array_diff(array_keys($defaults), array('context', 'filter')))
		);

		/*
		* If the post is being untrashed and it has a desired slug stored in post meta,
		* reassign it.
		*/
		if ('trash' === $previous_status && 'trash' !== $post_status) {
			$desired_post_slug = get_post_meta($post_ID, '_wp_desired_post_slug', true);

			if ($desired_post_slug) {
				delete_post_meta($post_ID, '_wp_desired_post_slug');
				$post_name = $desired_post_slug;
			}
		}

		// If a trashed post has the desired slug, change it and let this post have it.
		if ('trash' !== $post_status && $post_name) {
			/**
			 * Filters whether or not to add a `__trashed` suffix to trashed posts that match the name of the updated post.
			 *
			 * @since 5.4.0
			 *
			 * @param bool   $add_trashed_suffix Whether to attempt to add the suffix.
			 * @param string $post_name          The name of the post being updated.
			 * @param int    $post_ID            Post ID.
			 */
			$add_trashed_suffix = apply_filters('add_trashed_suffix_to_trashed_posts', true, $post_name, $post_ID);

			if ($add_trashed_suffix) {
				wp_add_trashed_suffix_to_post_name_for_trashed_posts($post_name, $post_ID);
			}
		}

		// ? When trashing an existing post, change its slug to allow non-trashed posts to use it.
		if ('trash' === $post_status && 'trash' !== $previous_status && 'new' !== $previous_status) {
			$post_name = wp_add_trashed_suffix_to_post_name_for_post($post_ID);
		}

		$get_final_url = $postarr['meta_input']['lasso_final_url'] ?? '';
		$post_name     = self::add_prefix_to_lasso_amazon_permalink($get_final_url, $post_name);
		$post_name     = wp_unique_post_slug($post_name, $post_ID, $post_status, $post_type, $post_parent);

		// ? Don't unslash.
		$post_mime_type = isset($postarr['post_mime_type']) ? $postarr['post_mime_type'] : '';

		// ? Expected slashed (everything!).
		$data = compact('post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_content_filtered', 'post_title', 'post_excerpt', 'post_status', 'post_type', 'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_parent', 'menu_order', 'post_mime_type', 'guid');

		$emoji_fields = array('post_title', 'post_content', 'post_excerpt');

		foreach ($emoji_fields as $emoji_field) {
			if (isset($data[$emoji_field])) {
				$charset = $wpdb->get_col_charset($wpdb->posts, $emoji_field);

				if ('utf8' === $charset) {
					$data[$emoji_field] = wp_encode_emoji($data[$emoji_field]);
				}
			}
		}

		/**
		 * Filters slashed post data just before it is inserted into the database.
		 *
		 * @since 2.7.0
		 * @since 5.4.1 `$unsanitized_postarr` argument added.
		 *
		 * @param array $data                An array of slashed, sanitized, and processed post data.
		 * @param array $postarr             An array of sanitized (and slashed) but otherwise unmodified post data.
		 * @param array $unsanitized_postarr An array of slashed yet *unsanitized* and unprocessed post data as
		 *                                   originally passed to wp_insert_post().
		 */
		$data = apply_filters('wp_insert_post_data', $data, $postarr, $unsanitized_postarr, $update);

		$data  = wp_unslash($data);
		$where = array('ID' => $post_ID);

		if ($update) {
			/**
			 * Fires immediately before an existing post is updated in the database.
			 *
			 * @since 2.5.0
			 *
			 * @param int   $post_ID Post ID.
			 * @param array $data    Array of unslashed post data.
			 */
			do_action('pre_post_update', $post_ID, $data);

			if (false === $wpdb->update($wpdb->posts, $data, $where)) { // phpcs:ignore
				if ($wp_error) {
					if ('attachment' === $post_type) {
						$message = __('Could not update attachment in the database.');
					} else {
						$message = __('Could not update post in the database.');
					}

					return new WP_Error('db_update_error', $message, $wpdb->last_error);
				} else {
					return 0;
				}
			}
		} else {
			// If there is a suggested ID, use it if not already present.
			if (!empty($import_id)) {
				$import_id = (int) $import_id;

				if (!Model::get_var(Model::prepare("SELECT ID FROM $wpdb->posts WHERE ID = %d", $import_id))) { // phpcs:ignore
					$data['ID'] = $import_id;
				}
			}

			if (false === $wpdb->insert($wpdb->posts, $data)) { // phpcs:ignore
				if ($wp_error) {
					if ('attachment' === $post_type) {
						$message = __('Could not insert attachment into the database.');
					} else {
						$message = __('Could not insert post into the database.');
					}

					return new WP_Error('db_insert_error', $message, $wpdb->last_error);
				} else {
					return 0;
				}
			}

			$post_ID = (int) $wpdb->insert_id;

			// Use the newly generated $post_ID.
			$where = array('ID' => $post_ID);
		}

		if (empty($data['post_name']) && !in_array($data['post_status'], array('draft', 'pending', 'auto-draft'), true)) {
			$data['post_name'] = wp_unique_post_slug(sanitize_title($data['post_title'], $post_ID), $post_ID, $data['post_status'], $post_type, $post_parent);

			$wpdb->update($wpdb->posts, array('post_name' => $data['post_name']), $where); // phpcs:ignore
			clean_post_cache($post_ID);
		}

		if (!empty($postarr['meta_input'])) {
			foreach ($postarr['meta_input'] as $field => $value) {
				update_post_meta($post_ID, $field, $value);
			}
		}

		$current_guid = get_post_field('guid', $post_ID);

		// Set GUID.
		if (!$update && '' === $current_guid) {
			$wpdb->update($wpdb->posts, array('guid' => get_permalink($post_ID)), $where); // phpcs:ignore
		}

		if ('attachment' === $postarr['post_type']) {
			if (!empty($postarr['file'])) {
				update_attached_file($post_ID, $postarr['file']);
			}

			if (!empty($postarr['context'])) {
				add_post_meta($post_ID, '_wp_attachment_context', $postarr['context'], true);
			}
		}

		return $post_ID;
	}

	/**
	 * Get post_meta by name
	 *
	 * @param array  $post_meta    An array post meta data.
	 * @param string $setting_name A Meta data name.
	 *
	 * @return int|mixed
	 */
	public static function get_post_meta_value_by_name($post_meta, $setting_name)
	{
		$default_setting = Lasso_Setting::lasso_get_settings();
		$default_value   = $default_setting[$setting_name] ?? null;
		$meta_value      = $default_value;

		$post_meta_value = $post_meta[$setting_name] ?? null;
		if (!empty($post_meta_value) && is_array($post_meta_value)) {
			$meta_value = $post_meta_value[0];
		}
		return $meta_value;
	}

	/**
	 * Cast post meta data value to integer
	 *
	 * @param array  $post_meta    An array post meta data.
	 * @param string $setting_name A Meta data name.
	 *
	 * @return int
	 */
	public static function get_toggle_value($post_meta, $setting_name)
	{
		$toggle_value = self::get_post_meta_value_by_name($post_meta, $setting_name);
		return intval($toggle_value);
	}

	/**
	 * Get Extend Product.
	 *
	 * @param string $extend_product_type Extend product type.
	 * @param string $extend_product_id   Extend product id.
	 * @param string $url                 URL.
	 * @return array
	 */
	public function get_extend_product($extend_product_type, $extend_product_id, $url)
	{
		$lasso_extend_product = new Lasso_Extend_Product();
		$product              = Lasso_Extend_Product::get_extend_product_from_db($extend_product_type, $extend_product_id);
		$is_status_404        = false;

		if ($product) {
			$lasso_extend_product->update_extend_product_in_db(
				array(
					'product_type' => $product['product_type'],
					'product_id'   => $product['product_id'],
					'title'        => $product['default_product_name'],
					'price'        => $product['latest_price'],
					'default_url'  => $product['base_url'],
					'url'          => $url,
					'image'        => $product['default_image'],
					'quantity'     => '0' === $product['out_of_stock'] ? 200 : 0,  // ? Manual checks won't show out of stock for now. TODO: Add BLS to out of stock checks.
					'is_manual'    => $product['is_manual'],
				)
			);
		}

		if (!$product) {
			$product_info = $lasso_extend_product->fetch_product_info($url, true);
			$product      = $product_info['product'];

			if ('NotFound' === $product_info['error_code']) {
				$is_status_404                   = true;
				$product['default_product_name'] = self::DEFAULT_TITLE;
				$product['default_image']        = LASSO_DEFAULT_THUMBNAIL;
			} else {
				$product['default_product_name'] = $product['title'];
				$product['default_image']        = $product['image'];
			}
		}

		return array($product, $is_status_404);
	}

	/**
	 * Get lasso id by post title
	 *
	 * @param string $title Post title.
	 */
	public static function get_lasso_id_by_title($title)
	{
		if (!$title || strtolower($title) === 'amazon') {
			return 0;
		}

		$sql     = '
			SELECT ID
			FROM ' . Model::get_wp_table_name('posts') . '
			WHERE post_title = %s and post_type = %s and post_status = %s
		';
		$prepare = Model::prepare($sql, $title, LASSO_POST_TYPE, 'publish'); // phpcs:ignore

		$lasso_id = intval(Model::get_var($prepare, true));

		return $lasso_id;
	}

	/**
	 * Add prefix to Lasso Amazon permalink
	 *
	 * @param string $get_final_url The final URL.
	 * @param string $post_name     The post name.
	 *
	 * @return string
	 */
	public static function add_prefix_to_lasso_amazon_permalink($get_final_url, $post_name)
	{
		// ? Add prefix 'amzn-' to the post_name for the Lasso Amazon post type.
		$prefix        = 'amzn-';
		$prefix_length = strlen($prefix);
		if ($get_final_url && $post_name && Lasso_Amazon_Api::is_amazon_url($get_final_url) && substr($post_name, 0, $prefix_length) !== $prefix) {
			$post_name_length = Model::get_column_length('posts', 'post_name');
			if (strlen($post_name) >= $post_name_length - $prefix_length) {
				$post_name = $prefix . substr($post_name, $prefix_length);
			} else {
				$post_name = $prefix . $post_name;
			}
		}

		return $post_name;
	}

	public function create_slug($string)
	{
		$search = array(
			'#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#',
			'#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#',
			'#(ì|í|ị|ỉ|ĩ)#',
			'#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#',
			'#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#',
			'#(ỳ|ý|ỵ|ỷ|ỹ)#',
			'#(đ)#',
			'#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#',
			'#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#',
			'#(Ì|Í|Ị|Ỉ|Ĩ)#',
			'#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#',
			'#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#',
			'#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#',
			'#(Đ)#',
			"/[^a-zA-Z0-9\-\_]/",
		);
		$replace = array(
			'a',
			'e',
			'i',
			'o',
			'u',
			'y',
			'd',
			'A',
			'E',
			'I',
			'O',
			'U',
			'Y',
			'D',
			'-',
		);
		$string = preg_replace($search, $replace, $string);
		$string = preg_replace('/(-)+/', '-', $string);
		$string = strtolower($string);
		return $string;
	}
}
