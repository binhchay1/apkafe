<?php

/**
 * Lasso General - Ajax.
 *
 * @package Pages
 */

namespace Lasso\Pages;

use Lasso\Classes\Enum;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;
use Lasso\Classes\Keyword as Lasso_Keyword;
use Lasso\Classes\Link_Location as Lasso_Link_Location;
use Lasso\Classes\Post as Lasso_Post;
use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Classes\Setting_Enum as Lasso_Setting_Enum;

use Lasso\Models\Fields;
use Lasso\Models\Field_Mapping;
use Lasso\Models\Link_Locations as Model_Link_Locations;
use Lasso\Models\Model;

use Lasso\Pages\Hook as Lasso_Hook;

use Lasso_DB;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso_Affiliate_Link;
use Lasso_Amazon_Api;
use Lasso_Cron;
use Lasso_Init;
use Lasso_License;
use Lasso_Process_Add_Amazon;
use Lasso_Process_Import_All;
use Lasso_Process_Revert_All;
use Lasso_Process_Link_Database;
use Lasso_Process;
use Lasso_Process_Bulk_Add_Links;
use Lasso_Shortcode;

/**
 * Lasso General - Ajax.
 */
class Ajax
{
	/**
	 * Declare "Lasso ajax requests" to WordPress.
	 */
	public function register_hooks()
	{
		add_action('wp_ajax_lasso_add_a_new_link', array($this, 'lasso_add_a_new_link'));
		add_action('wp_ajax_lasso_report_urls', array($this, 'lasso_report_urls'));
		add_action('wp_ajax_lasso_popup_monetize_link', array($this, 'lasso_popup_monetize_link'));
		add_action('wp_ajax_lasso_unmonetized_link', array($this, 'lasso_unmonetized_link'));
		add_action('wp_ajax_monetize_amazon_link', array($this, 'monetize_amazon_link'));
		add_action('wp_ajax_lasso_search_attributes', array($this, 'lasso_search_attributes'));
		add_action('wp_ajax_lasso_search_attributes_group', array($this, 'lasso_search_attributes_group'));
		add_action('wp_ajax_lasso_dismiss_opportunity', array($this, 'lasso_dismiss_opportunity'));
		add_action('wp_ajax_lasso_get_link_quick_detail', array($this, 'lasso_get_link_quick_detail'));
		add_action('wp_ajax_lasso_save_link_quick_detail', array($this, 'lasso_save_link_quick_detail'));
		add_action('wp_ajax_lasso_get_shortcode_content', array($this, 'lasso_get_shortcode_content'));
		add_action('wp_ajax_lasso_get_status', array($this, 'lasso_get_status'));
		add_action('wp_ajax_lasso_get_display_html', array($this, 'lasso_get_display_html'));
		add_action('wp_ajax_lasso_dismiss_ga_tracking', array($this, 'lasso_dismiss_ga_tracking'));
		add_action('wp_ajax_lasso_hide_education_box', array($this, 'lasso_hide_education_box'));
		add_action('wp_ajax_lasso_get_notification_template', array($this, 'lasso_get_notification_template'));
		add_action('wp_ajax_lasso_get_response_from_prompt', array($this, 'lasso_get_response_from_prompt'));

		add_action('wp_ajax_lasso_check_crons', array($this, 'lasso_check_crons'));
		add_action('wp_ajax_lasso_cron_handle_manually', array($this, 'lasso_cron_handle_manually'));
		add_action('wp_ajax_lasso_trigger_cron', array($this, 'lasso_trigger_cron'));
		add_action('wp_ajax_lasso_review_snooze', array($this, 'lasso_review_snooze'));
		add_action('wp_ajax_lasso_disable_review', array($this, 'lasso_disable_review'));
		add_action('wp_ajax_lasso_heartbeat', array($this, 'lasso_heartbeat'));
		add_action('wp_ajax_lasso_get_gutenberg_schema_data', array($this, 'lasso_get_gutenberg_schema_data'));
		add_action('wp_ajax_lasso_bulk_remove_links', array($this, 'lasso_bulk_remove_links'));
	}

	/**
	 * Links Report page: Ajax handle and response to client
	 */
	public function lasso_report_urls()
	{
		global $wpdb;

		$post = wp_unslash($_POST); // phpcs:ignore

		$lasso_db = new Lasso_DB();

		$page        = $post['pageNumber'] ?? 1;
		$search      = $post['search'] ?? '';
		$search      = str_replace(' ', '%', $search);
		$search_body = '%' . str_replace(' ', '%', $search) . '%';
		$post_id     = $post['post_id'] ?? '';
		$keyword     = $post['keyword'] ?? '';
		$filter      = $post['filter'] ?? '';
		$link_type   = $post['link_type'] ?? 'dashboard';

		$where     = $post_id ? $wpdb->prepare('p.ID=%d', $post_id) : '1=1';
		$total     = array();
		$posts     = array();
		$posts_sql = '';

		if (strtolower('dashboard') === $link_type) {
			$order_by   = $post['order_by'] ?? 'post_modified';
			$order_type = $post['order_type'] ?? 'desc';

			$search_term_string = '';
			if ('' !== $search) {
				$search_term_string = $wpdb->prepare('AND ( p.post_title LIKE %s OR ud.redirect_url LIKE %s OR p.post_name LIKE %s )', $search_body, $search_body, $search_body);
			}

			$sql       = $lasso_db->get_dashboard_query($search_term_string, $where, $filter);
			$posts_sql = $lasso_db->set_order($sql, $order_by, $order_type);
			$posts_sql = Lasso_Helper::paginate($posts_sql, $page);
			$posts     = Model::get_results($posts_sql);
			$count     = '' !== $search ? Model::get_count($sql) : $lasso_db->get_dashboard_link_count($search_term_string);

			$total['total']              = $count;
			$total['display_count']      = $lasso_db->get_total_display_count();
			$total['opportunities']      = $lasso_db->get_dashboard_opportunities_count($search_body);
			$total['broken_link_count']  = $lasso_db->get_url_broken_link_count($search_term_string);
			$total['out_of_stock_count'] = $lasso_db->get_url_out_of_stock_link_count($search_term_string);

			switch ($filter) {
				case 'opportunities':
					$total['total'] = $total['opportunities'];
					break;
				case 'broken-links':
					$total['total'] = $total['broken_link_count'];
					break;
				case 'out-of-stock':
					$total['total'] = $total['out_of_stock_count'];
					break;
				default:
					$total['total'] = $total['total'];
					break;
			}
		} elseif ('url-links' === $link_type) {
			$order_by   = $post['order_by'] ?? 'post_modified';
			$order_type = $post['order_type'] ?? 'desc';

			$search_term_string = '' !== $search ? "AND post_title LIKE '" . $search_body . "'" : '';
			$sql                = Model_Link_Locations::get_url_links_query($post_id);
			$posts_sql          = $lasso_db->set_order($sql, $order_by, $order_type);
			$posts_sql          = Lasso_Helper::paginate($posts_sql, $page);
			$posts              = Model::get_results($posts_sql);
			$total['total']     = Model::get_count($sql);
		} elseif (in_array($link_type, array(Lasso_Setting_Enum::PAGE_URL_DETAILS, Lasso_Setting_Enum::PAGE_TABLE_DETAILS), true) && '' === $filter) {
			$order_by     = $post['order_by'] ?? '';
			$order_type   = $post['order_type'] ?? '';
			$limit        = $post['pageSize'] ?? 6;
			$no_field_ids = $post['no_field_ids'] ?? array();

			$search_term_string = '' !== $search ? " AND field_name LIKE '" . $search_body . "'" : '';
			if (Lasso_Helper::compare_string($link_type, Lasso_Setting_Enum::PAGE_URL_DETAILS)) {
				$list_field_id = Fields::get_built_in_field_page_table_details();
				$append_where  = ' AND lf.id NOT IN ( %s ) ';
				$append_where  = sprintf($append_where, implode(',', $list_field_id));

				$search_term_string .= $append_where;
			}

			// ? don't show fields that are added to the table detail
			if (Lasso_Helper::compare_string($link_type, Lasso_Setting_Enum::PAGE_TABLE_DETAILS) && count($no_field_ids) > 0) {
				$append_where        = ' AND lf.id NOT IN ( %s ) ';
				$search_term_string .= sprintf($append_where, implode(',', $no_field_ids));
			}

			$sql = $lasso_db->get_fields_query($search_term_string);

			if (Lasso_Helper::compare_string($link_type, Lasso_Setting_Enum::PAGE_TABLE_DETAILS)) {
				$sql .= ' ORDER BY lf.order, lf.id ASC ';
			}

			$posts_sql      = $lasso_db->set_order($sql, $order_by, $order_type);
			$posts_sql      = Lasso_Helper::paginate($posts_sql, $page, $limit);
			$posts          = Model::get_results($posts_sql);
			$total['total'] = Model::get_count($sql);
		} elseif ('url-details' === $link_type && 'url-details' === $filter) { // ? Get custom fields of lasso url
			$order_by   = $post['order_by'] ?? '';
			$order_type = $post['order_type'] ?? '';

			$sql            = Fields::get_fields_for_product_query($post_id, $search_body);
			$posts_sql      = $lasso_db->set_order($sql, $order_by, $order_type);
			$posts          = Model::get_results($posts_sql);
			$total['total'] = Model::get_count($sql);
		} elseif ('link-opportunities' === $link_type || 'url-opportunities' === $link_type) {
			$order_by   = $post['order_by'] ?? '';
			$order_type = $post['order_type'] ?? '';

			$sql            = $lasso_db->get_link_opportunities_query($search_body, $post_id);
			$posts_sql      = $lasso_db->set_order($sql, $order_by, $order_type);
			$posts_sql      = Lasso_Helper::paginate($posts_sql, $page);
			$posts          = Model::get_results($posts_sql);
			$total['total'] = Model::get_count($sql);
		} elseif ('keyword-opportunities' === $link_type) {
			$order_by   = $post['order_by'] ?? 'post_modified';
			$order_type = $post['order_type'] ?? 'desc';

			$sql            = $lasso_db->get_keyword_opportunities_query($search_body, $keyword);
			$posts_sql      = $lasso_db->set_order($sql, $order_by, $order_type);
			$posts_sql      = Lasso_Helper::paginate($posts_sql, $page);
			$posts          = Model::get_results($posts_sql);
			$total['total'] = Model::get_count($sql);
		} elseif ('domain-opportunities' === $link_type) {
			$order_by   = $post['order_by'] ?? 'count';
			$order_type = $post['order_type'] ?? 'desc';

			$search_term_string = '' !== $search
				? $wpdb->prepare('AND link_slug_domain LIKE %s ', $search_body)
				: '';
			$sql                = strpos($search, 'Opportunities') !== false
				? $lasso_db->get_opportunities_only_query($search_term_string, $where)
				: $lasso_db->get_opportunities_query($search_term_string, $where);

			$posts_sql      = $lasso_db->set_order($sql, $order_by, $order_type);
			$posts_sql      = Lasso_Helper::paginate($posts_sql, $page);
			$posts          = Model::get_results($posts_sql);
			$total['total'] = Model::get_count($sql);
		} elseif ('program-opportunities' === $link_type) {
			$order_by   = $post['order_by'] ?? 'count';
			$order_type = $post['order_type'] ?? 'desc';

			$search_term_string = '' !== $search
				? $wpdb->prepare('AND link_slug_domain LIKE %s OR post_title like %s ', $search_body, $search_body)
				: '';
			$sql                = $lasso_db->get_program_opportunities_query($search_term_string, $where);

			$posts_sql      = $lasso_db->set_order($sql, $order_by, $order_type);
			$posts_sql      = Lasso_Helper::paginate($posts_sql, $page);
			$posts          = Model::get_results($posts_sql);
			$total['total'] = Model::get_count($sql);
		} elseif ('content-opportunities' === $link_type) {
			$order_by   = $post['order_by'] ?? 'post_modified';
			$order_type = $post['order_type'] ?? 'desc';

			$search_term_string = '' !== $search
				? $wpdb->prepare('AND base.detection_title LIKE %s ', $search_body)
				: '';

			$sql            = $lasso_db->get_content_query($search_term_string, $where);
			$posts_sql      = $lasso_db->set_order($sql, $order_by, $order_type);
			$posts_sql      = Lasso_Helper::paginate($posts_sql, $page);
			$posts          = Model::get_results($posts_sql);
			$total['total'] = Model::get_count($sql);
		} elseif ('content-links' === $link_type) {
			$order_by   = $post['order_by'] ?? 'll.link_type desc, ll.ID asc';
			$order_type = $post['order_type'] ?? '';

			$prepare_string     = "
				AND (
					ll.link_slug LIKE %s  
					OR (ll.anchor_text LIKE %s AND ll.anchor_text != 'DISPLAY BOX')
					OR (ll.link_slug LIKE %s AND ll.anchor_text = 'DISPLAY BOX')
				)
			";
			$search_term_string = '' !== $search
				? $wpdb->prepare($prepare_string, $search_body, $search_body, $search_body) // phpcs:ignore
				: '';

			$sql            = $lasso_db->get_content_link_query($search_term_string, $where);
			$posts_sql      = $lasso_db->set_order($sql, $order_by, $order_type);
			$posts_sql      = Lasso_Helper::paginate($posts_sql, $page);
			$posts          = Model::get_results($posts_sql);
			$total['total'] = Model::get_count($sql);
		} elseif ('domain-links' === $link_type) {
			$order_by   = $post['order_by'] ?? 'll.ID';
			$order_type = $post['order_type'] ?? 'asc';
			$where      = $filter ? 'link_slug_domain = \'' . $filter . '\'' : '1=1';

			$sql            = $lasso_db->get_domain_link_query($search_body, $where);
			$posts_sql      = $lasso_db->set_order($sql, $order_by, $order_type);
			$posts_sql      = Lasso_Helper::paginate($posts_sql, $page);
			$posts          = Model::get_results($posts_sql);
			$total['total'] = Model::get_count($sql);
		} elseif ('groups' === $link_type) {
			$order_by   = $post['order_by'] ?? 't.term_id';
			$order_type = $post['order_type'] ?? 'desc';

			$search_term_string = '' !== $search ? "AND t.name LIKE '$search_body'" : '';
			$sql                = $lasso_db->get_groups_query($search_term_string, $where);
			$posts_sql          = $lasso_db->set_order($sql, $order_by, $order_type);
			$posts_sql          = Lasso_Helper::paginate($posts_sql, $page);
			$posts              = Model::get_results($posts_sql);
			$total['total']     = Model::get_count($sql);
		} elseif ('group-urls' === $link_type) {
			$order_by   = $post['order_by'] ?? 'o.term_order';
			$order_type = $post['order_type'] ?? 'asc';

			$where          = $post_id ? $wpdb->prepare('t.term_id = %d', $post_id) : '1 = 1';
			$sql            = $lasso_db->get_urls_in_group('', $where);
			$posts_sql      = $lasso_db->set_order($sql, $order_by, $order_type);
			$posts          = Model::get_results($posts_sql);
			$total['total'] = Model::get_count($sql);
		} elseif ('import-urls' === $link_type) {
			$order_by   = $post['order_by'] ?? 'import_source';
			$order_type = $post['order_type'] ?? 'asc';

			$total['plugins']   = $this->get_import_plugins(); // ? All plugin sources
			$search_term_string = '' !== $search ? "AND ( post_title LIKE '$search_body' OR post_name LIKE '$search_body' )" : '';
			$search_term_string = '' !== $filter ? $search_term_string . " AND BASE.import_source LIKE '$filter' " : $search_term_string;
			$sql                = $lasso_db->get_importable_urls_query(true, $search_term_string);
			$posts_sql          = Lasso_Helper::paginate($sql, $page);
			$posts              = Model::get_results($posts_sql);
			$sql                = $lasso_db->get_importable_urls_query(true, $search_term_string, '', null, false, true);
			$total['total']     = Model::get_count($sql);
		} elseif ('fields' === $link_type) {
			$order_by   = $post['order_by'] ?? '';
			$order_type = $post['order_type'] ?? '';

			$search_term_string = '' !== $search ? "AND field_name LIKE '" . $search_body . "'" : '';
			$sql                = $lasso_db->get_fields_query($search_term_string);
			$posts_sql          = $lasso_db->set_order($sql, $order_by, $order_type);
			$posts_sql          = Lasso_Helper::paginate($posts_sql, $page);
			$posts              = Model::get_results($posts_sql);
			$total['total']     = Model::get_count($sql);
		} elseif ('field-urls' === $link_type) {
			$sql            = $lasso_db->get_urls_in_field($post_id);
			$posts_sql      = Lasso_Helper::paginate($sql, $page);
			$posts          = Model::get_results($posts_sql);
			$total['total'] = Model::get_count($sql);
		} elseif ('post-content-history' === $link_type) {
			$order_by   = $post['order_by'] ?? 'h.updated_date';
			$order_type = $post['order_type'] ?? 'desc';

			$search_term_string = '' !== $search ? "AND p.post_title LIKE '$search_body'" : '';
			$sql                = $lasso_db->get_post_content_history_query($search_term_string, $where);
			$posts_sql          = $lasso_db->set_order($sql, $order_by, $order_type, ', h.id ' . $order_type);
			$posts_sql          = Lasso_Helper::paginate($posts_sql, $page);
			$posts              = Model::get_results($posts_sql);
			$total['total']     = Model::get_count($sql);
		}

		// ? Since Model::get_results may be return a null value
		$posts = is_array($posts) ? $posts : array();

		// ? Add Lasso Link count
		$total['link_count'] = $lasso_db->get_dashboard_link_count();

		foreach ($posts as $key => $p) {
			$post_id          = $p->post_id ?? $p->ID ?? 0;
			$link_slug_domain = $p->link_slug_domain ?? '';
			$p->post_title    = Lasso_Helper::format_post_title($p->post_title ?? '');
			$p->shortcode     = '';
			$p_link_type      = $p->link_type ?? '';
			$p_display_type   = $p->display_type ?? '';
			$p_display_box    = $p->display_box ?? '';
			$p_lasso_id       = intval($p->lasso_id ?? 0);
			$p_anchor_text    = $p->anchor_text ?? null;
			$link_location_id = $p->link_id ?? $p->link_location_id ?? 0;

			$is_lasso_shortcode = Lasso_Link_Location::is_lasso_shortcode($p_display_type);

			// ? Get import target permalinks
			if ('import-urls' === $link_type && isset($p->import_source)) {
				$p = Lasso_Helper::format_importable_data($p);
				continue;
			} elseif ('post-content-history' === $link_type) {
				$p = Lasso_Helper::format_post_content_history_data($p);
				continue;
			} elseif (in_array($link_type, array('keyword-opportunities', 'link-opportunities', 'url-opportunities', 'domain-links'), true)) {
				$p->permalink = get_permalink($p->detection_id);
			} elseif ('url-details' === $link_type) {
				$p->show_field_name = Field_Mapping::get_show_field_name($p->lasso_id, $p->field_id);
			}

			// ? Show Shortcode Affiliate URL
			if (Lasso_Link_Location::LINK_TYPE_LASSO === ($p_link_type) && $is_lasso_shortcode && $p_lasso_id > 0) {
				$p->link_slug_original = get_the_permalink($p->lasso_id);
			}

			// ? Link Icon + Copy + Link Color
			list($p->link_report_type, $p->link_report_tooltip, $p->link_report_color) = Lasso_Html_Helper::get_link_location_displays($p_link_type, $p_display_type, $p_anchor_text, $p_lasso_id);

			$lasso_url = Lasso_Affiliate_Link::get_lasso_url($post_id);
			if ($post_id > 0) {
				$p->type = 'Internal Link';

				// ? BUILD GROUP LINKS
				$groups     = array();
				$group_list = wp_get_post_terms($post_id, LASSO_CATEGORY, array('fields' => 'all'));
				foreach ($group_list as $group) {
					$groups[] = '<a href="edit.php?post_type=lasso-urls&page=group-urls&post_id=' . $group->term_id . '" 
						class="black hover-purple-text">' . $group->name . '</a>';
				}
				$p->categories = $groups;

				if ($lasso_url) {
					$p->type           = LASSO_AMAZON_PRODUCT_TYPE === $lasso_url->link_type ? LASSO_AMAZON_PRODUCT_TYPE : LASSO_BASIC_LINK_TYPE;
					$p->link_slug      = $lasso_url->permalink ?? $p->link_slug ?? '';
					$p->lasso_edit_url = $lasso_url->edit_link ?? '';
					$p->post_edit_url  = $lasso_url->edit_link ?? '';
					$p->suggestion_url = $lasso_url->edit_link ?? '';
					$p->thumbnail      = $lasso_url->image_src ?? '';
					$p->out_of_stock   = $lasso_url->issue->out_of_stock ? '1' : '0';
				} else {
					$p->link_slug      = get_post_field('post_name', $post_id);
					$p->lasso_edit_url = get_edit_post_link($post_id);
					$p->post_edit_url  = get_edit_post_link($post_id);
					$p->suggestion_url = get_edit_post_link($post_id);
					$p->thumbnail      = get_the_post_thumbnail_url($post_id, 'thumbnail');
				}
			} else {
				$amazon               = Lasso_Amazon_Api::get_domains();
				$lasso_link_suggested = Lasso_Helper::get_suggested_affiliate($link_slug_domain);

				$p->monetize_with = !empty($lasso_link_suggested) && is_object($lasso_link_suggested)
					? $lasso_link_suggested->post_title : ($p->monetize_with ?? '');
				$p->monetize_with = in_array($link_slug_domain, $amazon, true)
					? 'Amazon Associates' : $p->monetize_with;

				$p->lasso_edit_url = $link_slug_domain && isset($lasso_link_suggested->ID) && $lasso_link_suggested->post_title
					? Lasso_Affiliate_Link::affiliate_edit_link($lasso_link_suggested->ID) : '';

				$p->type           = 'Basic Link';
				$p->status         = 'Basic';
				$p->suggestion_url = '#';
				$p->thumbnail      = $lasso_url->image_src ?? '';
				$p->categories     = array();
				$p->affiliate_url  = $p->post_title ?? '';
			}

			if (!Lasso_License::get_license_status()) {
				$p->count = 0;
			}

			if (in_array($link_type, array('content-links', 'link-opportunities', 'url-opportunities', 'keyword-opportunities', 'url-links', 'domain-links'), true)) {
				if ('a single link display' === $p->link_report_tooltip) {
					// ? display preview popup for the single link
					$p_display_box                  = str_replace('\\u0022', '"', $p_display_box);
					$p->display_box                 = $p_display_box;
					$posts[$key]->display_preview = do_shortcode($p_display_box);
				} elseif ('a text link' === $p->link_report_tooltip) {
					// ? anchor text preview popup for the text link
					$detection_id                   = $p->detection_id ?? url_to_postid($p->detection_slug);
					$detection_id                   = 'post' === get_post_type($post_id) ? $post_id : $detection_id;
					$paragraph                      = Lasso_Helper::get_paragraph_of_link($detection_id, $link_location_id);
					$posts[$key]->display_preview = $paragraph;
				} elseif ('an image link' === $p->link_report_tooltip) {
					$detection_id                   = $p->detection_id ?? url_to_postid($p->detection_slug);
					$detection_id                   = 'post' === get_post_type($post_id) ? $post_id : $detection_id;
					$paragraph                      = Lasso_Helper::get_paragraph_of_link($detection_id, $link_location_id);
					$posts[$key]->display_preview = $paragraph;
				} elseif ('a keyword mention' === $p->link_report_tooltip) {
					// ? anchor text preview popup for the keyword link
					$paragraph                      = Lasso_Helper::get_paragraph_of_link($post_id, $link_location_id, $p_anchor_text);
					$posts[$key]->display_preview = $paragraph;
				}
			}

			if ('dashboard' === $link_type) {
				$p->link_slug    = Lasso_Amazon_Api::get_amazon_product_url($lasso_url->public_link);
				$p->out_of_stock = $lasso_url->issue->out_of_stock ? '1' : '0';
			}

			if (isset($p->detection_id)) {
				$posts[$key]->post_edit_url = get_edit_post_link($p->detection_id);
			}
		}

		wp_send_json_success(
			array(
				'status' => 1,
				'data'   => $posts,
				'search' => $search,
				'filter' => $filter,
				'total'  => $total,
				'page'   => $page,
				'post'   => $post,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Get import plugin options to filter
	 *
	 * @return array
	 */
	public function get_import_plugins()
	{
		$lasso_db       = new Lasso_DB();
		$sql            = $lasso_db->get_importable_urls_query(true, '', 'import_source', null, true);
		$import_results = Model::get_results($sql, 'OBJECT', true);

		$result = array();

		foreach ($import_results as $import_result) {
			$result[] = $import_result->import_source;
		}

		return $result;
	}

	/**
	 * Add a new Lasso link
	 */
	public function lasso_add_a_new_link()
	{

		if (is_array($_POST['link'])) { // phpcs:ignore
			$bg    = new Lasso_Process_Bulk_Add_Links();
			$links = $_POST['link']; // phpcs:ignore
			$links = array_filter(
				$links,
				function ($item) {
					return '' !== trim($item);
				}
			);

			if (10 < count($links)) {
				$links = array_slice($links, 0, 10); // ? Only allow 10 links
			}
			$bg->process($links);
		} else {
			$lasso_affiliate_link = new Lasso_Affiliate_Link();
			return $lasso_affiliate_link->lasso_add_a_new_link();
		}
	}

	/**
	 * Monetize link in the popup
	 */
	public function lasso_popup_monetize_link()
	{
		$post                  = wp_unslash($_POST); // phpcs:ignore
		$link_type             = $post['link_type'];
		$post['post_id_count'] = $post['post_id_count'] ?? 0;
		$post['post_id']       = $post['post_id'] ?? 0;
		$post['lasso_id']      = $post['lasso_id'] ?? 0;
		$post['new_url']       = $post['new_url'] ?? '';

		$cron   = new Lasso_Cron();
		$status = true;
		$result = true;

		if ($post['post_id_count'] > 0 && $post['post_id'] > 0 && $post['lasso_id'] > 0 && '' !== $post['new_url']) {
			if ('keyword' !== $link_type) {
				$result = $cron->update_link_in_post($post['post_id'], $post['lasso_id'], $post['new_url']);
				// ? $post['lasso_id'] is link location id => Not sure, need confirm again
				$location_id = $post['lasso_id'];
				Lasso_Post::update_content_to_plugin($post['post_id'], Lasso_Post::MODE_MONETIZE, null, $location_id, $post['new_url']);
			} else {
				$result = Lasso_Keyword::replace_keyword_tag_by_link($post['lasso_id'], $post['post_id'], $post['new_url']);
				Lasso_Keyword::scan_keywords_in_post_page($post['post_id']);
			}
		} else {
			$status = false;
		}

		wp_send_json_success(
			array(
				'status'      => $status,
				'post'        => $post,
				'location_id' => $result,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Unmonetized a link
	 */
	public function lasso_unmonetized_link()
	{
		$post      = wp_unslash($_POST); // phpcs:ignore
		$link_type = $post['link_type'] ?? '';
		$cron      = new Lasso_Cron();

		if ('keyword' !== $link_type) {
			$lasso_link_location = new Lasso_Link_Location($post['link_location_id']);
			$original_link_slug  = $lasso_link_location->get_original_link_slug() ? $lasso_link_location->get_original_link_slug() : '';
			$result              = $cron->update_link_in_post($post['post_id'], $post['link_location_id'], $original_link_slug);
		} else {
			$original_link_slug = $post['keyword'];
			$result             = Lasso_Keyword::replace_link_by_keyword($post['link_location_id'], $post['post_id']);
		}

		wp_send_json_success(
			array(
				'status'             => $result,
				'original_link_slug' => $original_link_slug,
				'post'               => $post,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Monetize an amazon link
	 */
	public function monetize_amazon_link()
	{
		$post             = wp_unslash($_POST); // phpcs:ignore
		$link             = trim($post['link'] ?? '');
		$post_id          = trim($post['post_id'] ?? '');
		$link_location_id = trim($post['link_location_id'] ?? '');

		if (strpos($link, 'amzn.') !== false) {
			$full_amazon_url = Lasso_Helper::get_redirect_final_target($link);
			$link            = $full_amazon_url ? $full_amazon_url : $link;
		}

		$amazon_api = new Lasso_Amazon_Api();
		$cron       = new Lasso_Cron();
		$product_id = Lasso_Amazon_Api::get_product_id_by_url($link);

		// ? the link is invalid
		if (!$product_id) {
			wp_send_json_success(
				array(
					'status'  => 0,
					'message' => 'Product id is invalid',
				)
			);
		} // @codeCoverageIgnore

		// ? check whether product is exist
		$lasso_id = Lasso_Affiliate_Link::get_lasso_post_id_by_url($link);

		// ? Lasso is not existing
		if (0 === $lasso_id) {
			$lasso_affiliate_link = new Lasso_Affiliate_Link();
			$lasso_id             = $lasso_affiliate_link->lasso_add_a_new_link($link);
		}

		wp_update_post(array('ID' => $lasso_id));
		$amazon_product_id = get_post_meta($lasso_id, 'amazon_product_id', true);
		$permalink         = $amazon_api->get_amazon_monetized_url($amazon_product_id, $link)->monetized_url ?? '';

		if ('' !== $link_location_id && '' !== $permalink) {
			$cron->update_link_in_post($post_id, $link_location_id, $permalink);
		}

		wp_send_json_success(
			array(
				'status'            => 1,
				'permalink'         => $permalink,
				'post_id'           => $post_id,
				'lasso_id'          => $lasso_id,
				'amazon_product_id' => $amazon_product_id,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Get lasso posts in the popup (in post/page editor)
	 */
	public function lasso_search_attributes()
	{
		$post = wp_unslash($_POST); // phpcs:ignore

		$search      = $post['search_key'] ?? '';
		$search      = addslashes($search);
		$search      = str_replace(' ', '%', $search);
		$search_body = '%' . str_replace(' ', '%', $search) . '%';
		$limit       = $post['limit'] ?? 5;
		$page        = $post['page'] ?? 1;
		$order_by    = $post['order_by'] ?? 'post_modified';
		$order_type  = $post['order_type'] ?? 'desc';

		$lasso_db = new Lasso_DB();

		// ? from class-lasso-setting.php > lasso_report_urls()
		$search_term_string = '' !== $search ? "AND (p.post_title LIKE '" . $search_body . "')" : '';
		$sql                = $lasso_db->get_monetize_modal_query($search_term_string);
		$posts_sql          = $lasso_db->set_order($sql, $order_by, $order_type);
		$posts_sql          = Lasso_Helper::paginate($posts_sql, $page, $limit);
		$lasso_amazon       = Model::get_results($posts_sql);
		$count              = Model::get_count($sql) ?? 0;

		$data = array();
		if (is_array($lasso_amazon) || is_object($lasso_amazon)) {
			foreach ($lasso_amazon as $post) {
				$post_id   = $post->ID;
				$lasso_url = Lasso_Affiliate_Link::get_lasso_url($post_id);

				$amazon_product['post_id']  = $post_id;
				$amazon_product['edit_url'] = $lasso_url->edit_link;
				$amazon_product['name']     = $lasso_url->name;

				$amazon_product['thumbnail'] = $lasso_url->image_src;
				$amazon_product['permalink'] = $lasso_url->permalink;
				$amazon_product['slug']      = $lasso_url->slug;

				if (LASSO_AMAZON_PRODUCT_TYPE === $lasso_url->link_type) {
					$amazon_product['permalink'] = $lasso_url->amazon->monetized_url;
				}

				$data[] = $amazon_product;
			}
		}

		wp_send_json_success(
			array(
				'post'          => $post,
				'count'         => $count,
				'page'          => $page,
				'default_image' => LASSO_DEFAULT_THUMBNAIL,
				'data'          => $data,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Get lasso posts in the popup (in post/page editor)
	 */
	public function lasso_search_attributes_group()
	{
		$post = wp_unslash($_POST); // phpcs:ignore

		$search      = $post['search_key'] ?? '';
		$search      = addslashes($search);
		$search      = str_replace(' ', '%', $search);
		$search_body = '%' . str_replace(' ', '%', $search) . '%';
		$limit       = $post['limit'] ?? 6;
		$page        = $post['page'] ?? 1;
		$order_by    = $post['order_by'] ?? 't.term_id';
		$order_type  = $post['order_type'] ?? 'desc';

		$lasso_db = new Lasso_DB();

		$search_term_string = '' !== $search ? "AND t.name LIKE '$search_body'" : '';
		$sql                = $lasso_db->get_groups_query($search_term_string, '1=1');
		$posts_sql          = $lasso_db->set_order($sql, $order_by, $order_type);
		$posts_sql          = Lasso_Helper::paginate($posts_sql, $page, $limit);
		$groups             = Model::get_results($posts_sql);
		$count              = Model::get_count($sql);

		$data = array();
		if (is_array($groups) || is_object($groups)) {
			foreach ($groups as $group) {
				$group->name      = $group->post_title;
				$group->permalink = $group->count . ' link(s)';
				$data[]           = $group;
			}
		}

		wp_send_json_success(
			array(
				'post'          => $post,
				'count'         => $count,
				'page'          => $page,
				'default_image' => LASSO_DEFAULT_THUMBNAIL,
				'data'          => $data,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Get stats of the background process: opportunity
	 */
	public function lasso_dismiss_opportunity()
	{
		$post      = wp_unslash($_POST); // phpcs:ignore
		$link_type = $post['link_type'];
		$link_id   = $post['link_id'];

		$lasso_db = new Lasso_DB();

		$status = $lasso_db->process_dismiss_opportunity($link_type, $link_id);

		wp_send_json_success(
			array(
				'status'    => $status,
				'link_type' => $link_type,
				'link_id'   => $link_id,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Add a new Lasso link
	 */
	public function lasso_get_link_quick_detail()
	{
		$lasso_id = $_POST['lasso_id'] ?? null; // phpcs:ignore
		if (!empty($lasso_id)) {
			$lasso_url = Lasso_Affiliate_Link::get_lasso_url($lasso_id, true);
			$html      = Lasso_Html_Helper::render_template(
				LASSO_PLUGIN_PATH . '/admin/views/components/url-quick-detail.php',
				array(
					'lasso_url' => $lasso_url,
				)
			);
			wp_send_json_success(
				array(
					'success' => true,
					'html'    => $html,
				)
			);
		} else {
			wp_send_json_error('No affiliate link to get.');
		}
	} // @codeCoverageIgnore

	/**
	 * Save a lasso link with basic data
	 */
	public function lasso_save_link_quick_detail()
	{
		$data       = wp_unslash($_POST); // phpcs:ignore
		$lasso_id   = $data['lasso_id'] ?? null; // phpcs:ignore
		$lasso_post = get_post($lasso_id);
		if ($lasso_post) {
			$lasso_post_data = array(
				'ID'          => $lasso_post->ID,
				'post_title'  => trim($data['affiliate_name']),
				'post_type'   => LASSO_POST_TYPE,
				'post_status' => 'publish',
				'meta_input'  => array(
					'affiliate_desc'         => trim($data['description']) ?? '',
					'lasso_custom_thumbnail' => $data['thumbnail_image_url'] ?? '',
					'buy_btn_text'           => trim($data['buy_btn_text']) ?? '',
					'badge_text'             => trim($data['badge_text']) ?? '',
				),
			);
			Lasso_Affiliate_Link::lasso_insert_post($lasso_post_data, true);
			// ? Create webp image
			Lasso_Helper::create_lasso_webp_image($lasso_id);
			clean_post_cache($lasso_id); // ? clean post cache
			wp_send_json_success(
				array(
					'success' => true,
				)
			);
		} else {
			wp_send_json_success(
				array(
					'success' => false,
					'msg'     => 'No affiliate link existed.',
				)
			);
		}
	} // @codeCoverageIgnore

	/**
	 * Get display html
	 */
	public function lasso_get_shortcode_content()
	{
		$shortcode = stripslashes($_GET['shortcode'] ?? ''); // phpcs:ignore
		$html      = '';
		$schema    = array();

		if ('' !== $shortcode) {
			$html = do_shortcode($shortcode);

			preg_match_all(
				'/' . get_shortcode_regex(array('lasso')) . '/s',
				$shortcode,
				$matches,
				PREG_SET_ORDER
			);

			$match  = reset($matches);
			$atts   = shortcode_parse_atts($match[3]);
			$schema = Lasso_Helper::get_schema_info_by_lasso_atts($atts);
		}

		wp_send_json_success(
			array(
				'shortcode' => $shortcode,
				'html'      => $html,
				'schema'    => $schema,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Get background process data
	 */
	public function lasso_get_status()
	{
		// phpcs:ignore
		$get  = $_GET;
		$json = array();

		if ('import' === $get['action_type']) {
			// ? Import Tracker
			$bg   = new Lasso_Process_Import_All();
			$json = array(
				'all_items'   => $bg->get_total(),
				'completed'   => $bg->get_total_completed(),
				'status_type' => 'import',
				'bg_running'  => $bg->is_process_running(),
			);
		}
		if ('revert' === $get['action_type']) {
			// ? Revert Tracker
			$bg   = new Lasso_Process_Revert_All();
			$json = array(
				'all_items'   => $bg->get_total(),
				'completed'   => $bg->get_total_completed(),
				'status_type' => 'import',
				'bg_running'  => $bg->is_process_running(),
			);
		} else {
			// ? DB Build
			$bg   = new Lasso_Process_Link_Database();
			$json = array(
				'all_items'   => $bg->get_total(),
				'completed'   => $bg->get_total_completed(),
				'status_type' => 'link_build',
				'bg_running'  => $bg->is_process_running(),
			);
		}

		wp_send_json_success($json);
	} // @codeCoverageIgnore

	/**
	 * Check cron and trigger it if it can't work on hosting/server
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function lasso_check_crons()
	{
		global $wp_filter;

		$results      = array();
		$crons        = Lasso_Cron::CRONS;
		$crons        = apply_filters('lasso_all_crons', $crons);
		$cron_names   = array_keys($crons);
		$current_time = microtime(true);
		$schedules    = wp_get_schedules();

		$is_cron_working         = Lasso_Helper::test_cron_spawn();
		$failed_time_option_name = 'lasso_cron_is_failed_time';
		if (!is_wp_error($is_cron_working)) {
			$lasso_cron_is_failed_time = get_option($failed_time_option_name, $current_time);
			$reset_restart_attempt     = 24 * HOUR_IN_SECONDS;
			// ? if cron is working back after 24h, we will reset the restart_attempt option
			if ($lasso_cron_is_failed_time + $reset_restart_attempt < $current_time) {
				delete_option(Lasso_Process::OPTION_RESTART_ATTEMPTED);
			}

			$result = array(
				'messages' => 'Cron is working well.',
				'status'   => true,
			);
			wp_send_json_success($result);
		} else {
			update_option($failed_time_option_name, $current_time);
		}

		$manually_background_process_limit = Lasso_Setting::lasso_get_setting('manually_background_process_limit');
		foreach ($wp_filter as $cron_name => $callbacks) {
			if (in_array($cron_name, $cron_names, true)) {
				// ? Break lasso cron to stop background process init when total running process reachs to the "manually cron limitation".
				$total_background_process_running = (new Lasso_Process_Add_Amazon())->get_total_running_processes();
				if ($total_background_process_running >= $manually_background_process_limit) {
					break;
				}

				$callback = $callbacks->callbacks[10];
				$callback = array_reverse($callback); // ? Fix PHP Notice:  Only variables should be passed by reference.
				$callback = array_pop($callback);

				$class_obj = $callback['function'][0];
				$callback  = $callback['function'][1];

				$next_schedule = wp_next_scheduled($cron_name);
				$interval      = $schedules[$crons[$cron_name]]['interval'];
				$next_interval = time() + $interval;

				$results[$cron_name]['current_time']  = time();
				$results[$cron_name]['next_schedule'] = $next_schedule;
				$results[$cron_name]['next_interval'] = $next_interval;
				$results[$cron_name]['callback']      = $callback;
				$results[$cron_name]['recurrence']    = $crons[$cron_name];

				if ($current_time < $next_schedule) {
					continue;
				}

				$results[$cron_name]['run'] = true;
				wp_clear_scheduled_hook($cron_name);
				wp_schedule_event($next_interval, $crons[$cron_name], $cron_name);

				if (class_exists('Lasso_Affiliate_Program_Class\\Lasso_Cron') && strpos($cron_name, 'lasso_ap_') !== false) {
					$lasso_ap = new \Lasso_Affiliate_Program_Class\Lasso_Cron();
					$lasso_ap->$callback();
				} else {
					$class_obj->$callback();
				}
			}
		}

		$result = array(
			'messages' => 'WP cron is disabled',
			'status'   => false,
		);
		wp_send_json_success($result);
	} // @codeCoverageIgnore

	/**
	 * Handle cron and call task() function manually
	 */
	public function lasso_cron_handle_manually()
	{
		$post = $_POST; // phpcs:ignore

		$process_class = $post['class_name'] ?? '';
		$process_class = str_replace('\\\\', '\\', $process_class);
		if (!class_exists($process_class)) {
			wp_send_json_error(
				array(
					'message' => 'Class does not exist',
					'class'   => $process_class,
				)
			);
		}

		$process_class_obj                 = new $process_class();
		$restart_attempted                 = intval(get_option(Lasso_Process::OPTION_RESTART_ATTEMPTED, 0));
		$trigger_handle                    = false;
		$manually_background_process_limit = Lasso_Setting::lasso_get_setting('manually_background_process_limit');
		$total_background_process_running  = $process_class_obj->get_total_running_processes();

		// @codeCoverageIgnoreStart
		if (
			$restart_attempted >= Lasso_Process::RESTART_ATTEMPTED_LIMIT
			&& !$process_class_obj->is_queue_empty()
			&& !get_site_transient($process_class_obj->get_key() . '_process_lock')
			&& $total_background_process_running < $manually_background_process_limit
		) {
			$process_class_obj->handle_manually();
			$trigger_handle = true;
		}
		// @codeCoverageIgnoreEnd

		wp_send_json_success(
			array(
				'message'        => 'Success.',
				'class'          => $process_class,
				'trigger_handle' => $trigger_handle,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Build db - background process
	 */
	public function lasso_trigger_cron()
	{
		Lasso_Init::force_to_run_new_scan();

		wp_send_json_success(
			array(
				'status' => 1,
				'result' => true,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Get display html
	 */
	public function lasso_get_display_html()
	{
		ob_start();
		include_once LASSO_PLUGIN_PATH . '/admin/views/modals/display-add.php';
		$html                        = ob_get_clean();
		$file_path                   = LASSO_PLUGIN_PATH . '/admin/views/modals/url-add.php';
		$url_add_modal_html          = Lasso_Html_Helper::render_template(
			$file_path,
			array(
				'is_from_editor' => true,
			)
		);
		$url_quick_detail_modal_html = Lasso_Html_Helper::render_template(LASSO_PLUGIN_PATH . '/admin/views/modals/url-quick-detail.php', array());
		wp_send_json_success(
			array(
				'html' => $html . $url_add_modal_html . $url_quick_detail_modal_html,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Update option lasso GA tracking
	 */
	public function lasso_dismiss_ga_tracking()
	{
		update_option('lasso_ga_tracking', 0);
	}

	/**
	 * Save the hiding of an education box
	 */
	public function lasso_hide_education_box()
	{
		$post                               = wp_unslash($_POST); // phpcs:ignore
		$options[$post['education_page']] = 'hide';

		// ? Update settings
		Lasso_Setting::lasso_set_settings($options);

		wp_send_json_success(
			array(
				'status'         => 1,
				'education_page' => $post['education_page'],
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Get template for notification
	 */
	public function lasso_get_notification_template()
	{
		$get = wp_unslash($_GET); // phpcs:ignore

		$template  = $get['template'] ?? 'default-template';
		$variables = array(
			'message'  => $get['message'] ?? '',
			'name'     => $get['name'] ?? '',
			'alert_id' => $get['alert_id'] ?? '',
			'alert_bg' => $get['alert_bg'] ?? '',
		);

		$file_path = LASSO_PLUGIN_PATH . '/admin/views/notifications/' . $template . '.php';
		$html      = Lasso_Helper::include_with_variables($file_path, $variables, true);

		wp_send_json_success(
			array(
				'status' => 1,
				'html'   => $html,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Do save support to open intercom chat
	 */
	public function lasso_review_snooze()
	{
		$lasso_db   = new Lasso_DB();
		$link_count = $lasso_db->get_dashboard_link_count();

		Lasso_Helper::update_option(Enum::OPTION_REVIEW_SNOOZE, '1');
		Lasso_Helper::update_option(Enum::OPTION_REVIEW_LINK_COUNT, $link_count);

		wp_send_json_success(
			array(
				'status' => 1,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Disable Review notification
	 */
	public function lasso_disable_review()
	{
		Lasso_Helper::update_option(Enum::OPTION_REVIEW_ALLOW, '0');

		wp_send_json_success(
			array(
				'status' => 1,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Heartbeat to check background process
	 */
	public function lasso_heartbeat()
	{
		$post = wp_unslash($_POST); // phpcs:ignore
		$type = $post['type'];
		if ('bulk_add_links' === $type) {
			wp_send_json_success(
				array(
					'percent' => Lasso_Process_Bulk_Add_Links::get_progress(),
					'status'  => 1,
				)
			);
		}
	} // @codeCoverageIgnore

	/**
	 * Get Lasso url detail schema data
	 */
	public function lasso_get_gutenberg_schema_data()
	{
		$get     = wp_unslash($_GET); // phpcs:ignore
		$post_id = $get['post_id'] ?? 0;
		$post    = get_post($post_id);

		wp_send_json_success(
			array(
				'lassoUrlDetailsSchemaData' => Lasso_Hook::get_lasso_urls_schema_data($post),
			)
		);
	} // @codeCoverageIgnore


	/**
	 * Get Lasso url detail schema data
	 */
	public function lasso_bulk_remove_links()
	{

		$response = array(
			'status'         => 1,
			'location_count' => 0,
			'error'          => '',
		);

		$post     = wp_unslash($_POST); // phpcs:ignore
		$post_ids = $post['post_ids'] ?? array();
		if (empty($post_ids)) {
			$response['status'] = 0;
			$response['error']  = 'Invalid Lasso Post ID!';
		} else {
			$response['location_count'] = Model_Link_Locations::total_locations_by_lasso_id($post_ids);
		}

		if (0 !== $response['location_count']) {
			$response['error'] = 'Invalid Lasso Post ID!';
		} else {
			foreach ($post_ids as $id) {
				wp_update_post(
					array(
						'ID'          => $id,
						'post_status' => 'trash',
					)
				);
			}
		}

		return wp_send_json_success($response);
	} // @codeCoverageIgnore

	/**
	 * Get response from AI
	 *
	 * @return void
	 */
	public function lasso_get_response_from_prompt()
	{
		$post = wp_unslash($_POST); // phpcs:ignore

		$prompt           = $post['prompt'] ?? '';
		$result           = $post['result'] ?? '';
		$is_from_modal    = $post['is_from_modal'] ?? false;
		$is_add_shortcode = $post['is_add_shortcode'] ?? false;

		$prompt        = trim($prompt);
		$optional_data = array(
			'response_modified' => trim($result),
			'is_from_modal'     => $is_from_modal,
			'is_add_shortcode'  => $is_add_shortcode,
		);
		$response      = '';
		if ('' !== $prompt) {
			$response = Lasso_Shortcode::get_chatgpt_response($prompt, 'html', 0, $optional_data);
		}
		wp_send_json_success(
			array(
				'response' => $response,
			)
		);
	} // @codeCoverageIgnore


}
