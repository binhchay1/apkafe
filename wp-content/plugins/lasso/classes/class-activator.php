<?php
/**
 * Declare class Activator
 *
 * @package Activator
 */

namespace Lasso\Classes;

use Lasso\Classes\Setting;

use Lasso_Init;
use Lasso_DB_Script;

/**
 * Require WordPress core file
 */
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once LASSO_PLUGIN_PATH . '/classes/class-lasso-init.php';

/**
 * Activator
 */
class Activator {
	/**
	 * Run when Lasso is activated
	 */
	public function init() {
		$this->update_version();

		// ? create tables
		self::create_lasso_table();

		// ? create default data for Lasso tables
		self::add_default_data();

		// ? setup for new active site
		self::setup_for_new_active_site();

		// ? allow other processes are triggered after removing attribute
		// update_option( 'lasso_disable_processes', 0, false );

		Lasso_Init::force_to_run_new_scan();
	}

	/**
	 * Set version and save it to the option table
	 */
	public function update_version() {
		update_option( 'lasso_version', LASSO_VERSION );
	}

	/**
	 * Create Lasso tables
	 */
	public static function create_lasso_table() {
		$db_script = new Lasso_DB_Script();

		$db_script->create_revert_table();
		$db_script->create_url_issue_def_table();
		$db_script->create_url_issue_table();
		$db_script->create_link_locations_table();
		$db_script->create_tracked_keyword_table();
		$db_script->create_keyword_locations_table();
		$db_script->create_amazon_product_table();
		$db_script->create_category_order_table();
		$db_script->create_content_table();
		$db_script->create_amazon_tracking_table();
		$db_script->create_url_details_table();
		$db_script->create_fields_table();
		$db_script->create_field_mapping_table();
		$db_script->create_affiliate_programs_table();
		$db_script->create_lasso_post_content_history_table();
		$db_script->create_extend_product_table();
		$db_script->create_table_details();
		$db_script->create_table_mapping();
		$db_script->create_table_field_group();
		$db_script->create_table_field_group_detail();
		$db_script->create_table_metadata();
		$db_script->create_amazon_shortened_url_table();
		$db_script->create_auto_monetize_table();
	}

	/**
	 * Add default data for Lasso tables
	 */
	public static function add_default_data() {
		$db_script = new Lasso_DB_Script();

		$db_script->url_issue_definitions();
		$db_script->amazon_tracking_default_data();
		$db_script->create_default_fields();
	}

	/**
	 * Setup for new active site
	 */
	public static function setup_for_new_active_site() {
		Setting::lasso_set_setting( 'auto_monetize_amazon', true );
	}
}
