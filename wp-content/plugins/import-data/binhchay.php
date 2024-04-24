<?php if (!defined('ABSPATH')) die;
/*
Plugin Name: Import Data
Description: Import data from product of woocommerce to lasso 
Author: binhchay
Version: 1.0
License: GPLv2 or later
*/

define('IMPORT_DATA_ADMIN_VERSION', '1.0.0');
define('IMPORT_DATA_ADMIN_DIR', 'import-data');

require plugin_dir_path(__FILE__) . 'admin-form.php';

function run_ct_wp_admin_form_import_data()
{
    $plugin = new Import_Data_Admin();
    $plugin->init();
}
run_ct_wp_admin_form_import_data();

remove_action('shutdown', 'wp_ob_end_flush_all', 1);
add_action('shutdown', function () {
    while (@ob_end_flush());
});


