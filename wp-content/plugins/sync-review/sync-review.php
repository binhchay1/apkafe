<?php if (!defined('ABSPATH')) die;
/*
Plugin Name: Sync Review
Description: Sync review of user
Author: binhchay
Version: 1.0
License: GPLv2 or later
*/

define('SYNC_REVIEW_ADMIN_VERSION', '1.0.0');
define('SYNC_REVIEW_ADMIN_DIR', 'sync-review');

require plugin_dir_path(__FILE__) . 'admin-form.php';

function run_ct_wp_admin_form_sync_review()
{
    $plugin = new SYNC_REVIEW_ADMIN();
    $plugin->init();
}

run_ct_wp_admin_form_sync_review();
