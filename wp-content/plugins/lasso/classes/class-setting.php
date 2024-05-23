<?php

/**
 * Declare class Lasso_Setting
 *
 * @package Lasso_Setting
 */

namespace Lasso\Classes;

use Lasso\Models\Model;

use Lasso_DB;
use Lasso_Process_Link_Database;
use Lasso_Process_Import_All;
use Lasso_Process_Revert_All;

use Exception;

/**
 * Lasso_Setting
 */
class Setting
{
	/**
	 * Install page
	 *
	 * @var string $install_page
	 */
	public $install_page = 'install';

	/**
	 * Uninstall page
	 *
	 * @var string $uninstall_page
	 */
	public $uninstall_page = 'uninstall';

	/**
	 * Dashboard page
	 *
	 * @var string $dashboard_page
	 */
	public $dashboard_page = 'dashboard';

	/**
	 * Domain opportunities page
	 *
	 * @var string $domain_opportunities
	 */
	public $domain_opportunities = 'domain-opportunities';

	/**
	 * Domain links page
	 *
	 * @var string $domain_links_page
	 */
	public $domain_links_page = 'domain-links';

	/**
	 * Content links page
	 *
	 * @var string $content_links_page
	 */
	public $content_links_page = 'content-links';

	/**
	 * Link opportunities page
	 *
	 * @var string $link_opportunities
	 */
	public $link_opportunities = 'link-opportunities';

	/**
	 * Keyword opportunities page
	 *
	 * @var string $keyword_opportunities
	 */
	public $keyword_opportunities = 'keyword-opportunities';

	/**
	 * Program opportunities page
	 *
	 * @var string $program_opportunities
	 */
	public $program_opportunities = 'program-opportunities';

	/**
	 * Content opportunities page
	 *
	 * @var string $content_opportunities
	 */
	public $content_opportunities = 'content-opportunities';

	/**
	 * Url details page
	 *
	 * @var string $url_details_page
	 */
	public $url_details_page = 'url-details';

	/**
	 * Url links page
	 *
	 * @var string $url_links_page
	 */
	public $url_links_page = 'url-links';

	/**
	 * Url opportunities page
	 *
	 * @var string $url_opportunities_page
	 */
	public $url_opportunities_page = 'url-opportunities';

	/**
	 * Groups page
	 *
	 * @var string $groups_page
	 */
	public $groups_page = 'groups';

	/**
	 * Group details page
	 *
	 * @var string $group_details_page
	 */
	public $group_details_page = 'group-details';

	/**
	 * Group urls page
	 *
	 * @var string $group_urls_page
	 */
	public $group_urls_page = 'group-urls';

	/**
	 * Fields page
	 *
	 * @var string $fields_page
	 */
	public $fields_page = 'fields';

	/**
	 * Fields details page
	 *
	 * @var string $field_details_page
	 */
	public $field_details_page = 'field-details';

	/**
	 * Field urls page
	 *
	 * @var string $field_urls_page
	 */
	public $field_urls_page = 'field-urls';

	/**
	 * Import page
	 *
	 * @var string $import_page
	 */
	public $import_page = 'import-urls';

	/**
	 * Settings general page
	 *
	 * @var string $settings_general_page
	 */
	public $settings_general_page = 'settings-general';

	/**
	 * Settings display page
	 *
	 * @var string $settings_display_page
	 */
	public $settings_display_page = 'settings-display';

	/**
	 * Settings amazon page
	 *
	 * @var string $settings_amazon_page
	 */
	public $settings_amazon_page = 'settings-amazon';

	/**
	 * Settings logs page
	 *
	 * @var string $settings_logs_page
	 */
	public $settings_logs_page = 'settings-logs';

	/**
	 * Settings DB status page
	 *
	 * @var string $settings_db_status_page
	 */
	public $settings_db_status_page = 'settings-db';

	/**
	 * Post content history page
	 *
	 * @var string $post_content_history_page
	 */
	public $post_content_history_page = 'post-content-history';

	/**
	 * Post content history detail page
	 *
	 * @var string $post_content_history_detail_page
	 */
	public $post_content_history_detail_page = 'post-content-history-detail';

	/**
	 * Lasso permission level
	 *
	 * @var string $lasso_permission_level
	 */
	public $lasso_permission_level = 'manage_options';

	const DISPLAY_TYPE_SINGLE = 'Single';
	const DISPLAY_TYPE_GRID   = 'Grid';
	const DISPLAY_TYPE_LIST   = 'List';

	const W_1000   = '1000';
	const W_800    = '800';
	const W_750    = '750';
	const W_500    = '500';
	const W_650    = '650';
	const W_CUSTOM = 'Custom';

	const SSL_VERIFY = true;
	const TIME_OUT   = 30;

	/**
	 * Tables page
	 *
	 * @var string $tables_page
	 */
	public $tables_page = 'tables';

	/**
	 * Table details page
	 *
	 * @var string $tables_page
	 */
	public $table_details_page = 'table-details';


	/**
	 * List of plugins
	 *
	 * @var string $import_sources
	 */
	public static $import_sources = array(
		'simple-urls/plugin.php',
		'earnist/earnist.php',
		'thirstyaffiliates/thirstyaffiliates.php',
		'pretty-link/pretty-link.php',
		'affiliate-link-automation/affiliate_automation.php',
		'aawp/aawp.php',
		'easyazon/easyazon.php',
		'amalinkspro/amalinkspro.php',
		'easy-affiliate-links/easy-affiliate-links.php',
	);

	/**
	 * List of JS files are allowed in the Lasso pages
	 *
	 * @var string $js_allowed_in_lasso
	 */
	public static $js_allowed_in_lasso = array(
		'admin-bar',
		'utils',
		'quicktags',
		'common',
		'jquery',
		'jquery-effects-core',
		'jquery-ui-core',
		'jquery-ui-sortable',
		'jquery-ui-tooltip',
		'jquery-migrate',
		'media-editor',
		'media-audiovideo',
		'mce-view',
		'image-edit',
		'spectrum-js',
		'moment-js',
		'select2-js',
		'pagination-js',
		'popper-js',
		'bootstrap-js',
		'bootstrap-select-js',
		LASSO_POST_TYPE . '-js',
		LASSO_POST_TYPE . '-jq-auto-complete-js',
		'lasso-quill',
		'vue-js',
		'lasso-js',
		'edit-lasso',
		'lasso-popup-monetize',
		'lasso-import',
		'custom-slug',
		'lasso-page',
		'lasso-onboarding',
		'admin-theme-js',
		'uikit',
		'uikit-icons',
		'admin2020-utilities',
		'a2020-vue-build',
		'admin2020-update',
		'admin-bar-app',
		'group-urls',
		'lasso-helper',
		'google-diff_match_patch-js',
		'lasso-history-js',
		'lasso-icons',
		'lasso-icons-regular',
		'lasso-icons-brands',
		'lasso-tables',
		'lasso-table-product-link',
		'lasso-display-modal',
		'setting-general',
		'wp-pointer',
		'svg-painter',
		'wp-color-picker',
	);

	/**
	 * List of CSS files are allowed in the Lasso pages
	 *
	 * @var string $css_allowed_in_lasso
	 */
	public static $css_allowed_in_lasso = array(
		'admin-bar',
		'colors',
		'wp-auth-check',
		'spectrum',
		'media-views',
		'imgareaselect',
		'hestia-meta-radio-buttons-style',
		'bootstrap-css',
		'bootstrap-select-css',
		'simple-panigation-css',
		'select2-css',
		'lasso-dashboard',
		'lasso-live',
		'lasso-icons',
		'lasso-icons-regular',
		'lasso-icons-brands',
		'lasso-quill',
		'calypsoify_wpadminmods_css',
		'admin2020_admin_bar',
		'admin2020_admin_menu',
		'admin2020_admin_theme',
		'custom-google-icons',
		'admin2020_app',
		'admin-menu',
		'lasso-dashboard-grid',
		'lasso-tables',
		'lasso-table-frontend',
		'lasso-display-modal',
		'cp-admin-style',
		'seopress-admin',
		'seopress-admin-bar',
		'ct_admin_css',
		'wp-color-picker',
		'wp-mail-smtp-admin-bar',
	);

	/**
	 * Convert WP GET parameters to Lasso GET
	 *
	 * @var array|string $get
	 */
	public $get = array();

	/**
	 * Setting constructor.
	 */
	public function __construct()
	{
		$this->get = wp_unslash($_GET); // phpcs:ignore
	}

	/**
	 * Get report url by tab
	 */
	public function get_dashboard_page()
	{
		$dashboard_url = add_query_arg(
			array(
				'post_type' => LASSO_POST_TYPE,
				'page'      => $this->dashboard_page,
			),
			admin_url('edit.php')
		);

		return $dashboard_url;
	}

	/**
	 * Get report url by tab
	 *
	 * @param string $page_slug Page slug.
	 */
	public static function get_lasso_page_url($page_slug)
	{
		$dashboard_url = add_query_arg(
			array(
				'post_type' => LASSO_POST_TYPE,
				'page'      => $page_slug,
			),
			admin_url('edit.php')
		);

		return $dashboard_url;
	}

	/**
	 * Check whether current page is Lasso dashboard page
	 */
	public function is_lasso_dashboard_page()
	{
		$get       = wp_unslash($_GET); // phpcs:ignore
		$page      = $get['page'] ?? '';
		$post_type = $get['post_type'] ?? '';

		return LASSO_POST_TYPE === $post_type && in_array(
			$page,
			array(
				$this->dashboard_page,
				$this->url_details_page,
				$this->url_links_page,
				$this->url_opportunities_page,
				$this->domain_opportunities,
				$this->link_opportunities,
				$this->keyword_opportunities,
				$this->program_opportunities,
				$this->content_opportunities,
				$this->domain_links_page,
				$this->content_links_page,
				$this->install_page,
				$this->groups_page,
				$this->group_details_page,
				$this->group_urls_page,
				$this->fields_page,
				$this->field_details_page,
				$this->field_urls_page,
				$this->import_page,
				$this->settings_general_page,
				$this->settings_display_page,
				$this->settings_amazon_page,
				$this->settings_logs_page,
				$this->settings_db_status_page,
				$this->post_content_history_page,
				$this->post_content_history_detail_page,
				$this->tables_page,
				$this->table_details_page,
			),
			true
		);
	}

	/**
	 * Check whether current page is WP post page
	 */
	public function is_wordpress_post()
	{
		global $pagenow;

		$get          = wp_unslash($_GET); // phpcs:ignore
		$action       = $get['action'] ?? '';
		$add_new_page = 'post-new.php' === $pagenow;
		$edit_page    = 'post.php' === $pagenow && 'edit' === $action;
		$post_type    = $get['post_type'] ?? '';

		if (('edit.php' === $pagenow || $add_new_page) && '' === $post_type) {
			$post_type = 'post';
		} elseif ($add_new_page) {
			$post_type = $get['post_type'] ?? $post_type;
		} elseif ($edit_page) {
			$post_id   = intval($get['post'] ?? 0);
			$post_type = $post_id > 0 ? get_post_type($post_id) : $post_type;
		}

		if ('term.php' === $pagenow) {
			$post_type = '';
		}

		return 'post' === $post_type || 'page' === $post_type;
	}

	/**
	 * Check whether current page is custom post page
	 */
	public function is_custom_post()
	{
		global $pagenow;

		$get          = wp_unslash($_GET); // phpcs:ignore
		$action       = $get['action'] ?? '';
		$add_new_page = 'post-new.php' === $pagenow;
		$edit_page    = 'post.php' === $pagenow && 'edit' === $action;
		$post_type    = $get['post_type'] ?? '';

		if (('edit.php' === $pagenow || $add_new_page) && '' === $post_type) {
			$post_type = 'post';
		} elseif ($add_new_page) {
			$post_type = $get['post_type'] ?? $post_type;
		} elseif ($edit_page) {
			$post_id   = intval($get['post'] ?? 0);
			$post_type = $post_id > 0 ? get_post_type($post_id) : $post_type;
		}

		if ('term.php' === $pagenow) {
			$post_type = '';
		}

		return '' !== $post_type && 'post' !== $post_type && 'page' !== $post_type;
	}

	/**
	 * Check whether current page is configuring page
	 */
	public function is_lasso_configured_page()
	{
		$get  = wp_unslash($_GET); // phpcs:ignore
		$page = $get['page'] ?? '';

		return $page === $this->install_page;
	}

	/**
	 * Check whether current page is configuring page
	 */
	public function is_lasso_table_page()
	{
		$get  = wp_unslash($_GET); // phpcs:ignore
		$page = $get['page'] ?? '';

		return $page === $this->table_details_page;
	}

	/**
	 * Check whether current page is configuring page
	 */
	public function is_lasso_uninstall_page()
	{
		$get  = wp_unslash($_GET); // phpcs:ignore
		$page = $get['page'] ?? '';

		return $page === $this->uninstall_page;
	}

	/**
	 * Check whether current page is Opportunities content page
	 */
	public function is_lasso_opportunities_content_page()
	{
		$get  = wp_unslash($_GET); // phpcs:ignore
		$page = $get['page'] ?? '';

		return $page === $this->content_links_page;
	}

	/**
	 * Check whether current page is Opportunities keyword page
	 */
	public function is_lasso_opportunities_keyword_page()
	{
		$get  = wp_unslash($_GET); // phpcs:ignore
		$page = $get['page'] ?? '';

		return $page === $this->keyword_opportunities;
	}

	/**
	 * Check whether current page is setting page
	 */
	public function is_lasso_setting_page()
	{
		$get  = wp_unslash($_GET); // phpcs:ignore
		$page = $get['page'] ?? '';

		$is_setting_page = in_array(
			$page,
			array(
				$this->settings_general_page,
				$this->settings_display_page,
				$this->settings_amazon_page,
				$this->settings_logs_page,
			),
			true
		);

		return $is_setting_page
			&& isset($get['post_type']) && LASSO_POST_TYPE === $get['post_type'];
	}

	/**
	 * Check whether current page is setting page
	 */
	public function is_general_setting_page()
	{
		return $this->get_page_name() === $this->settings_general_page
			&& LASSO_POST_TYPE === $this->get_post_type();
	}

	/**
	 * Check whether current page is importing Lasso post page
	 */
	public function is_lasso_import_page()
	{
		$get  = wp_unslash($_GET); // phpcs:ignore
		$page = $get['page'] ?? '';

		return $page === $this->import_page
			&& isset($get['post_type']) && LASSO_POST_TYPE === $get['post_type'];
	}

	/**
	 * Check whether current page is editing Lasso post page
	 */
	public function is_edit_lasso_post_page()
	{
		global $pagenow;

		$get = wp_unslash($_GET); // phpcs:ignore

		return 'edit.php' === $pagenow && isset($get['post_type'])
			&& LASSO_POST_TYPE === $get['post_type']
			&& isset($get['post_id']) && $get['post_id'] > 0;
	}

	/**
	 * Check whether current page is adding Lasso post page
	 */
	public function is_add_lasso_post_page()
	{
		global $pagenow;

		$get = wp_unslash($_GET); // phpcs:ignore

		return 'edit.php' === $pagenow && isset($get['post_type'])
			&& LASSO_POST_TYPE === $get['post_type']
			&& 'url-details' === ($get['page'] ?? '')
			&& !(isset($get['post_id']));
	}

	/**
	 * Check whether current page is Post Content History pages
	 */
	public function is_lasso_post_content_history_pages()
	{
		$get  = wp_unslash($_GET); // phpcs:ignore
		$page = $get['page'] ?? '';

		return ($page === $this->post_content_history_detail_page || $page === $this->post_content_history_page)
			&& isset($get['post_type']) && LASSO_POST_TYPE === $get['post_type'];
	}

	/**
	 * Check whether current page is Post Content History detail page
	 */
	public function is_lasso_post_content_history_detail_page()
	{
		$get  = wp_unslash($_GET); // phpcs:ignore
		$page = $get['page'] ?? '';

		return $page === $this->post_content_history_detail_page
			&& isset($get['post_type']) && LASSO_POST_TYPE === $get['post_type'];
	}

	/**
	 * Check whether current page is Lasso page or not.
	 */
	public function is_lasso_page()
	{
		global $pagenow;

		$get = wp_unslash($_GET); // phpcs:ignore

		return 'edit.php' === $pagenow && isset($get['post_type']) && LASSO_POST_TYPE === $get['post_type'];
	}

	/**
	 * Update lasso settings to db
	 *
	 * @param array $options New options.
	 */
	public static function lasso_set_settings($options)
	{
		if (!is_array($options)) {
			return false;
		}

		$defaults = self::lasso_get_settings();
		$options  = array_merge($defaults, $options);

		return update_option(LASSO_SETTINGS, $options);
	}

	/**
	 * Update lasso setting to db
	 *
	 * @param string $option_name Option name.
	 * @param string $option_value Option value.
	 */
	public static function lasso_set_setting($option_name, $option_value)
	{
		if (!is_string($option_name)) {
			return false;
		}

		$options                 = self::lasso_get_settings();
		$options[$option_name] = $option_value;

		return update_option(LASSO_SETTINGS, $options);
	}

	/**
	 * Update lasso setting to db
	 *
	 * @param string $option_name Option name.
	 * @param string $option_default Option default. Default to null.
	 */
	public static function lasso_get_setting($option_name, $option_default = null)
	{
		if (!is_string($option_name)) {
			return $option_default;
		}

		$options = self::lasso_get_settings();

		return $options[$option_name] ?? $option_default;
	}

	/**
	 * Get lasso settings from db
	 */
	public static function lasso_get_settings()
	{
		$options = get_option(LASSO_SETTINGS, array());
		if (!is_array($options)) {
			$options = array();
		}

		$defaults = unserialize(SETTINGS_DEFAULT); // phpcs:ignore

		$options = wp_parse_args($options, $defaults);

		// ? force to int
		$options['cpu_threshold']                     = abs((int) $options['cpu_threshold']);
		$options['cron_time_interval']                = abs((int) $options['cron_time_interval']);
		$options['manually_background_process_limit'] = abs((int) $options['manually_background_process_limit']);

		// ? force to boolean
		$options['general_disable_notification']          = (bool) $options['general_disable_notification'];
		$options['general_disable_amazon_notifications']  = (bool) $options['general_disable_amazon_notifications'];
		$options['analytics_enable_click_tracking']       = (bool) $options['analytics_enable_click_tracking'];
		$options['analytics_enable_ip_anonymization']     = (bool) $options['analytics_enable_ip_anonymization'];
		$options['general_disable_tooltip']               = (bool) $options['general_disable_tooltip'];
		$options['show_price']                            = (bool) $options['show_price'];
		$options['show_disclosure']                       = (bool) $options['show_disclosure'];
		$options['show_disclosure_grid']                  = (bool) $options['show_disclosure_grid'];
		$options['keep_site_stripe_ui']                   = (bool) $options['keep_site_stripe_ui'];
		$options['enable_amazon_prime']                   = (bool) $options['enable_amazon_prime'];
		$options['amazon_add_tracking_id_to_attribution'] = (bool) $options['amazon_add_tracking_id_to_attribution'];
		$options['auto_monetize_amazon']                  = (bool) $options['auto_monetize_amazon'];
		$options['auto_monetize_affiliates']              = (bool) $options['auto_monetize_affiliates'];
		$options['enable_logs']                           = (bool) $options['enable_logs'];
		$options['enable_brag_mode']                      = (bool) $options['enable_brag_mode'];
		$options['enable_nofollow']                       = (bool) $options['enable_nofollow'];
		$options['enable_nofollow2']                      = (bool) $options['enable_nofollow2'];
		$options['open_new_tab']                          = (bool) $options['open_new_tab'];
		$options['open_new_tab2']                         = (bool) $options['open_new_tab2'];
		$options['enable_sponsored']                      = (bool) $options['enable_sponsored'];
		$options['show_amazon_discount_pricing']          = (bool) $options['show_amazon_discount_pricing'];
		$options['amazon_multiple_tracking_id']           = (bool) $options['amazon_multiple_tracking_id'];
		$options['restrict_prefix']                       = (bool) $options['restrict_prefix'];
		$options[Setting_Enum::SEGMENT_ANALYTICS]       = (bool) $options[Setting_Enum::SEGMENT_ANALYTICS];
		$options[Setting_Enum::PROCESSES_EXECUTE_LIMIT] = (bool) $options[Setting_Enum::PROCESSES_EXECUTE_LIMIT];
		$options['fontawesome_js_svg']                    = (bool) $options['fontawesome_js_svg'];

		$options['open_new_tab3']                         = (bool) $options['open_new_tab3'];
		$options['enable_nofollow3']                      = (bool) $options['enable_nofollow3'];
		$options['open_new_tab4']                         = (bool) $options['open_new_tab4'];
		$options['enable_nofollow4']                      = (bool) $options['enable_nofollow4'];
		$options['open_new_tab_google']                   = (bool) $options['open_new_tab_google'];
		$options['enable_nofollow_google']                = (bool) $options['enable_nofollow_google'];
		$options['open_new_tab_apple']                    = (bool) $options['open_new_tab_apple'];
		$options['enable_nofollow_apple']                 = (bool) $options['enable_nofollow_apple'];

		// ? check empty and set default
		$options['primary_button_text']             = empty($options['primary_button_text']) ? $defaults['primary_button_text'] : $options['primary_button_text'];
		$options['secondary_button_text']           = empty($options['secondary_button_text']) ? $defaults['secondary_button_text'] : $options['secondary_button_text'];
		$options['disclosure_text']                 = empty($options['disclosure_text']) ? $defaults['disclosure_text'] : $options['disclosure_text'];
		$options['amazon_default_tracking_country'] = empty($options['amazon_default_tracking_country']) ? $defaults['amazon_default_tracking_country'] : $options['amazon_default_tracking_country'];
		$options['lasso_affiliate_URL']             = empty($options['lasso_affiliate_URL']) ? $defaults['lasso_affiliate_URL'] : $options['lasso_affiliate_URL'];

		// phpcs:ignore: check empty and array
		$options['cpt_support']           = is_null($options['cpt_support']) || empty($options['cpt_support']) || !is_array($options['cpt_support'])
			? $defaults['cpt_support'] : $options['cpt_support'];
		$options['custom_fields_support'] = is_null($options['custom_fields_support']) || empty($options['custom_fields_support']) || !is_array($options['custom_fields_support'])
			? $defaults['custom_fields_support'] : $options['custom_fields_support'];

		// ? remove empty element in array
		$options['amazon_tracking_id_whitelist'] = is_array($options['amazon_tracking_id_whitelist']) ? $options['amazon_tracking_id_whitelist'] : array();
		$options['amazon_tracking_id_whitelist'] = array_filter(
			$options['amazon_tracking_id_whitelist'],
			function ($value) {
				return !is_null($value) && '' !== $value;
			}
		);

		return $options;
	}

	/**
	 * Check whether or not the plugins are activated
	 * Example for plugin: Lasso URLs: affiliate-plugin/affiliate-plugin.php
	 *
	 * @param bool $include_deactivate Include deactivate plugins or not. Default to false.
	 * @param bool $all                All plugins. Default to false.
	 */
	public static function get_import_sources($include_deactivate = false, $all = false)
	{
		$plugin_list = self::$import_sources;

		$result          = array();
		$plugin_path_abs = dirname(LASSO_PLUGIN_PATH);

		try {
			$db          = new Lasso_DB();
			$query       = $db->get_importable_urls_query(false, '', 'import_source', null, true);
			$import_data = Model::get_results($query, 'OBJECT', true);

			foreach ($plugin_list as $plugin) {
				$full_plugin_path = $plugin_path_abs . '/' . $plugin;
				if (!file_exists($full_plugin_path)) {
					continue;
				}

				// ? Check plugin is activate or deactivate
				$condition = ($include_deactivate) ? $include_deactivate : is_plugin_active($plugin);
				if ($condition) {
					$plugin_name = self::get_plugin_name($plugin);
					$plugin_name = 'Simple URLs' === $plugin_name ? 'Lasso Lite / Simple URLs' : $plugin_name;

					if (!$all) {
						$plugin_data = array_filter(
							$import_data,
							function ($p) use ($plugin_name) {
								return $p->import_source === $plugin_name;
							}
						);

						if (count($plugin_data) === 0) {
							continue;
						}
					}

					$key            = $plugin;
					$result[$key] = $plugin_name;
				}
			}
		} catch (Exception $e) {
			return false;
		}

		return $result;
	}

	/**
	 * Get plugin name
	 *
	 * @param string $plugin Plugin file path.
	 */
	public static function get_plugin_name($plugin)
	{
		$plugin_name = 'The plugin ' . $plugin . ' has been deleted.';
		$plugin_path = WP_PLUGIN_DIR . '/' . $plugin;
		if (file_exists($plugin_path)) {
			$plugin_data = get_plugin_data($plugin_path);
			$plugin_name = $plugin_data['Name'];
		}

		return $plugin_name;
	}

	/**
	 * Get stats in report pages
	 */
	public function lasso_get_stats_in_report_page()
	{
		$stats = array(
			'build'        => -1,
			'import'       => -1,
			'amazon'       => false,
			'refresh_page' => false,
		);

		// ? build data process
		$build_bg            = new Lasso_Process_Link_Database();
		$build_bg_is_running = $build_bg->is_process_running();
		if ($build_bg_is_running) {
			$count_all_pages_posts = $build_bg->get_total();
			$completed             = $build_bg->get_total_completed();
			$completed             = 0 !== $completed ? $completed : 1;
			$percentage            = 0 === $count_all_pages_posts ? 0 : $completed * 100 / $count_all_pages_posts;
			$stats['build']        = round($percentage, 2);
		}

		// ? import process
		$import_all = new Lasso_Process_Import_All();
		if ($import_all->is_process_running()) {
			$stats['import'] = 1;
		}
		// ? revert process
		$revert_all = new Lasso_Process_Revert_All();
		if ($revert_all->is_process_running()) {
			$stats['revert'] = 1;
		}

		// ? amazon configurations
		$amazon_valid    = (bool) get_option('lasso_amazon_valid', false);
		$stats['amazon'] = $amazon_valid;

		$stats['refresh_page'] = ($build_bg->get_total_completed() === $build_bg->get_total())
			&& ($revert_all->get_total_completed() === $revert_all->get_total())
			&& ($import_all->get_total_completed() === $import_all->get_total());

		return $stats;
	}

	/**
	 * Get log content
	 *
	 * @param string $file_postfix Prefix of log name file.
	 */
	public static function get_logs_content($file_postfix)
	{
		$log_data    = '';
		$filename    = '';
		$files_found = false;

		if (!empty($file_postfix)) {
			$filebase = LASSO_PLUGIN_PATH . '/logs/';

			// 2019_04_10_<type>.log
			$file_array = glob($filebase . '*_' . $file_postfix . '.log');

			if (is_array($file_array)) {
				rsort($file_array);
				$filename = isset($file_array[0]) ? $file_array[0] : '';
				if ('' !== $filename) {
					$files_found = true;
				}
			}

			if ($files_found) {
				$log_data = file_get_contents($filename, true);
			}
		}

		return $log_data;
	}

	/**
	 * Check plugins for importing and deactivating
	 */
	public function check_plugins_for_import_and_deactivate()
	{
		// ? import plugin check
		$plugins_for_import     = $this->get_import_sources();
		$setting_page_link      = '';
		$plugins_for_import_txt = '';

		if (!empty($plugins_for_import)) {
			$verb                   = count($plugins_for_import) > 1 ? 'are' : 'is';
			$plugins_for_import_txt = implode(', ', $plugins_for_import) . ' ' . $verb;

			$setting_page_link = add_query_arg(
				array(
					'post_type' => 'lasso-urls',
					'page'      => $this->import_page,
				),
				self_admin_url('edit.php')
			);
		}

		// ? deactivate plugin check
		$plugin_list           = self::$import_sources;
		$all_plugins           = array();
		$plugin_for_deactivate = array();
		$plugin_path_abs       = dirname(LASSO_PLUGIN_PATH);

		foreach ($plugin_list as $plugin) {
			$full_plugin_path = $plugin_path_abs . '/' . $plugin;
			if (!file_exists($full_plugin_path)) {
				continue;
			}

			if (is_plugin_active($plugin)) {
				$all_plugins[self::get_plugin_name($plugin)] = $plugin;
			}
		}

		$imported_plugins = array_diff(array_keys($all_plugins), $plugins_for_import);

		foreach ($imported_plugins as $plugin) {
			$deactivate_link = wp_nonce_url(
				self_admin_url('plugins.php?action=deactivate&plugin=') . $all_plugins[$plugin] . '&plugin_status=all&paged=1&s',
				'deactivate-plugin_' . $all_plugins[$plugin]
			);

			$plugin_for_deactivate[$plugin] = $deactivate_link;
		}

		return array(
			'plugins_for_import'    => $plugins_for_import_txt,
			'setting_page_link'     => $setting_page_link,
			'plugin_for_deactivate' => $plugin_for_deactivate,
		);
	}

	/**
	 * Generate update link
	 */
	public function generate_update_link()
	{
		return wp_nonce_url(
			self_admin_url('update.php?action=upgrade-plugin&plugin=') . LASSO_PLUGIN_BASE_NAME,
			'upgrade-plugin_' . LASSO_PLUGIN_BASE_NAME
		);
	}

	/**
	 * Get available display type
	 *
	 * @return array
	 */
	public static function get_display_type()
	{
		return array(
			self::DISPLAY_TYPE_SINGLE => self::DISPLAY_TYPE_SINGLE,
			self::DISPLAY_TYPE_GRID   => self::DISPLAY_TYPE_GRID,
			self::DISPLAY_TYPE_LIST   => self::DISPLAY_TYPE_LIST,
		);
	}

	/**
	 * Get available width options
	 *
	 * @return array
	 */
	public static function get_width_options()
	{
		return array(
			self::W_1000   => self::W_1000 . 'px',
			self::W_800    => self::W_800 . 'px',
			self::W_750    => self::W_750 . 'px',
			self::W_500    => self::W_500 . 'px',
			self::W_CUSTOM => self::W_CUSTOM,
		);
	}

	/**
	 * Get current page name
	 *
	 * @return mixed|string
	 */
	private function get_page_name()
	{
		return $this->get['page'] ?? '';
	}

	/**
	 * Get current post type
	 *
	 * @return mixed|string
	 */
	private function get_post_type()
	{
		return $this->get['post_type'] ?? '';
	}

	/**
	 * Get all GA tracking ids
	 */
	public static function get_ga_tracking_ids()
	{
		$ids             = array();
		$ga_tracking_ids = self::lasso_get_setting('analytics_google_tracking_id');
		if ($ga_tracking_ids) {
			$ga_tracking_ids = trim($ga_tracking_ids);
			$explode         = explode(',', $ga_tracking_ids);
			$explode         = array_map(
				function ($v) {
					return trim($v);
				},
				$explode
			);
			$ids             = $explode;
		}

		return $ids;
	}
}
