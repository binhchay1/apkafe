<?php if (!defined('ABSPATH')) die;
/*
Plugin Name: Group post
Description: Create a new group for post
Author: binhchay
Version: 1.0
License: GPLv2 or later
*/

define('GROUP_POST_ADMIN_VERSION', '1.0.0');
define('GROUP_POST_ADMIN_DIR', 'review-slide');

require plugin_dir_path(__FILE__) . 'admin-form.php';

function run_ct_wp_admin_form()
{
    $plugin = new Group_Post_Admin();
    $plugin->init();
}
run_ct_wp_admin_form();

function create_group_post_table()
{
    global $wpdb;
    $db_table_name = $wpdb->prefix . 'group_post';
    $db_version = '1.0.0';
    $charset_collate = $wpdb->get_charset_collate();

    if ($wpdb->get_var("show tables like '$db_table_name'") != $db_table_name) {
        $sql = "CREATE TABLE $db_table_name (
                id int(11) NOT NULL auto_increment,
                title varchar(250) NOT NULL,
                post_id text NOT NULL,
                short_code varchar(100) NOT NULL,
                UNIQUE KEY id (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        add_option('my_db_version', $db_version);
        dbDelta($sql);
    }

    $listColumnsUpdate = ['description', 'category'];

    foreach ($listColumnsUpdate as $column) {
        $row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = " . $db_table_name . " AND column_name = '" . $column . "'");

        if (empty($row)) {
            if ($column == 'description') {
                $wpdb->query("ALTER TABLE " . $db_table_name . " ADD " . $column . " TEXT NULL");
            }

            if ($column == 'category') {
                $wpdb->query("ALTER TABLE " . $db_table_name . " ADD " . $column . " INT NOT NULL");
            }
        }
    }
}

register_activation_hook(__FILE__, 'create_group_post_table');

global $wpdb;
$result = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "group_post");
$content = '';
foreach ($result as $shortcode) {
    add_shortcode('group-post-shortcode-' . $shortcode->short_code, function () use ($shortcode, $content) {

        $listID = explode(',', $shortcode->post_id);
        foreach ($listID as $id) {
            $title = get_the_title($id);
            $thumb = get_post_thumbnail_id($id);
            $description = get_post_meta($id, '_yoast_wpseo_metadesc', true);

            $content .= '<div id="short-code-group-post"><div class="lasso-container">';
            $content .= '<div class="lasso-display lasso-money">';
            $content .= '<div class="lasso-box-1">
            <a class="lasso-title" target="_blank" 
            href="' . get_permalink($id) . '"
            title="' . $title . '">' . $title . '</a>
            <span>' . $description . '</span>
            </div>';
            $content .= '<div class="lasso-box-2">
            <a class="lasso-image" target="_blank" href="' . get_permalink($id) . '" 
            title="' . $title . '"><img loading="lazy" decoding="async" src="' . $thumb . '" height="120" width="120" 
            alt="' . $title . '" style="width: 200px !important; height: inherit !important">
            </a>
            </div>';
            $content .= '</div>';
            $content .= '</div>';
            $content .= '</div>';
        }

        $content .= '<div style="display: flex; justify-content: center;"><div id="button-load-more-group-post" class="btn-show-and-load"><button>Load more <i class="fa fa-angle-down"></i></button></div>';
        $content .= '<div id="button-show-less-group-post" class="btn-show-and-load"><button>Show less <i class="fa fa-angle-up"></i></button></div></div>';
        $content .= '<script>
            jQuery(function(){
                jQuery("#button-load-more-group-post").show();
                jQuery("#short-code-group-post .lasso-container").slice(0, 3).show();

                jQuery("#button-load-more-group-post").click(function(e){
                    e.preventDefault();
                    let listPost = jQuery("#short-code-group-post .lasso-container");
                    let count = 0;
                    for(let i = 0; i < listPost.length; i++) {
                        if(listPost[i].style.display == "") {
                            if(count <= 3) {
                                count++;
                                listPost[i].style.display = "block";
                            }
                        }
                    }

                    if(listPost[listPost.length - 1].style.display == "block") {
                        jQuery("#button-load-more-group-post").hide();
                    }

                    jQuery("#button-show-less-group-post").show();
                });

                jQuery("#button-show-less-group-post").click(function(e){
                    e.preventDefault();
                    let listPost = jQuery("#short-code-group-post .lasso-container");

                    for(let i = 0; i < listPost.length; i++) {
                        if(listPost[i].style.display == "block") {
                            listPost[i].style.display = "none";
                        }
                    }

                    jQuery("#short-code-group-post .lasso-container").slice(0, 3).show();
                    jQuery("#button-load-more-group-post").show();
                    jQuery("#button-show-less-group-post").hide();
                });
            });
            </script>';

        return $content;
    });
}

function group_post_shortcode_style()
{
    if (is_single() || is_product()) {
        wp_enqueue_style('group-post-css', plugins_url('group-post/asset/css/short-code.css'));
    }
}
add_action('wp_enqueue_scripts', 'group_post_shortcode_style');
