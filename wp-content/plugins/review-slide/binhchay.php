<?php if (!defined('ABSPATH')) die;
/*
Plugin Name: Review slide
Description: Create review slide with image for post
Author: binhchay
Version: 1.0
License: GPLv2 or later
*/

define('REVIEW_SLIDE_ADMIN_VERSION', '1.0.0');
define('REVIEW_SLIDE_ADMIN_DIR', 'review-slide');

require plugin_dir_path(__FILE__) . 'admin-form.php';
require plugin_dir_path(__FILE__) . 'function.php';
require plugin_dir_path(__FILE__) . 'db.php';

function run_ct_wp_admin_form()
{
    $plugin = new Admin_Form();
    $plugin->init();
}
run_ct_wp_admin_form();
