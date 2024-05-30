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

function create_user_review_table()
{
    global $wpdb;
    $db_table_name = $wpdb->prefix . 'user_review';
    $db_version = '1.0.0';
    $charset_collate = $wpdb->get_charset_collate();

    if ($wpdb->get_var("show tables like '$db_table_name'") != $db_table_name) {
        $sql = "CREATE TABLE $db_table_name (
                id int(11) NOT NULL auto_increment,
                score int(11),
                user_name varchar(100),
                user_comment text,
                post_id int(11),
                created_at datetime
                UNIQUE KEY id (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        add_option('my_db_version', $db_version);
        dbDelta($sql);
    }
}

register_activation_hook(__FILE__, 'create_user_review_table');