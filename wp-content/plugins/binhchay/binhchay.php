<?php if (!defined('ABSPATH')) die;
/*
Plugin Name: Config for only Apkafe
Description: All config SEO for Apkafe
Author: binhchay
Version: 1.0
License: GPLv2 or later
*/

define('BINHCHAY_ADMIN_VERSION', '1.0.0');
define('BINHCHAY_ADMIN_DIR', 'binhchay');

require plugin_dir_path(__FILE__) . 'admin-form.php';
function run_ct_wp_admin_form_trending_search()
{
	$plugin = new Apkafe_Admin_Form();
	$plugin->init();
}
run_ct_wp_admin_form_trending_search();