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

function run_ct_wp_admin_form()
{
    $plugin = new Review_Slide_Admin();
    $plugin->init();
}
run_ct_wp_admin_form();

function create_review_slide_table()
{
    global $wpdb;
    $db_table_name = $wpdb->prefix . 'review_slide';
    $db_version = '1.0.0';
    $charset_collate = $wpdb->get_charset_collate();

    if ($wpdb->get_var("show tables like '$db_table_name'") != $db_table_name) {
        $sql = "CREATE TABLE $db_table_name (
                id int(11) NOT NULL auto_increment,
                title varchar(250) NOT NULL,
                images text NOT NULL,
                short_code varchar(100) NOT NULL,
                UNIQUE KEY id (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        add_option('my_db_version', $db_version);
        dbDelta($sql);
    }

    $listColumnsUpdate = ['description'];

    foreach ($listColumnsUpdate as $column) {
        $row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = " . $db_table_name . " AND column_name = '" . $column . "'");

        if (empty($row)) {
            if ($column == 'description') {
                $wpdb->query("ALTER TABLE " . $db_table_name . " ADD " . $column . " TEXT NULL");
            }
        }
    }
}

register_activation_hook(__FILE__, 'create_review_slide_table');

remove_action('shutdown', 'wp_ob_end_flush_all', 1);
add_action('shutdown', function () {
    while (@ob_end_flush());
});

global $wpdb;
$result = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "review_slide");
$content = '';
foreach ($result as $shortcode) {
    add_shortcode('review-slide-shortcode-' . $shortcode->short_code, function () use ($shortcode, $content) {

        $listImg = explode(',', $shortcode->images);
        $countImg = count($listImg);
        $width = $countImg * 7;
        $setIdKeyFrames = str_replace(' ', '-', strtolower($shortcode->title));

        $classes = 'img-ticker-reverse';

        $content .= '<style>@keyframes ticker-kf-' . $setIdKeyFrames . ' {
                    0% {
                        transform: translate3d(0, 0, 0);
                    }
        
                    100% {
                        transform: translate3d(-' . $width .  'rem, 0, 0);
                    }
                }
        
                .img-ticker {
                    animation: ticker-kf-' . $setIdKeyFrames . ' 75s linear infinite;
                }
        
                .img-ticker-reverse {
                    animation: ticker-kf-' . $setIdKeyFrames . ' 75s linear infinite;
                    animation-direction: reverse;
                }</style>';
        $content .= '<h2 class="text-2xl font-bold text-center mb-4">' . $shortcode->title . '</h2>';
        $content .= '<p class="text-center mb-4">' . $shortcode->description . '</p>';
        $content .= '<div class="overflow-hidden w-full relative">';
        $content .= '<div class="w-10 md:w-40 h-full left-0 top-0 absolute z-20 bg-gradient-to-r from-white to-transparent"></div>';
        $content .= '<div class="flex ' . $classes . ' -mx-4">';

        foreach ($listImg as $id) {
            $url = wp_get_attachment_image_url($id, array(150, 150));
            $content .= '<div class="bg-white p-5 rounded-md border border-gray-300 mx-4 self-start flex-none">';
            $content .= '<img src="' . $url . '" class="item-img-short-review-slide">';
            $content .= '</div>';
        }

        foreach ($listImg as $id) {
            $url = wp_get_attachment_image_url($id, array(150, 150));
            $content .= '<div class="bg-white p-5 rounded-md border border-gray-300 mx-4 self-start flex-none">';
            $content .= '<img src="' . $url . '" class="item-img-short-review-slide">';
            $content .= '</div>';
        }

        $content .= '</div>';
        $content .= '<div class="w-10 md:w-40 h-full right-0 top-0 absolute z-20 bg-gradient-to-r from-transparent to-white"></div>';
        $content .= '</div>';
        $content .= '</section>';

        return $content;
    });
}

function review_slide_shortcode_style()
{
    if (is_single() || is_product()) {
        wp_enqueue_style('review-slide-css', plugins_url('review-slide/asset/css/alpine.css'));
    }
}
add_action('wp_enqueue_scripts', 'review_slide_shortcode_style');
