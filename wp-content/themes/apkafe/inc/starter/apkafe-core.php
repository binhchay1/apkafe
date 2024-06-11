<?php

/**
 * Option Tree integration ===========
 */

add_filter('ot_show_pages', '__return_true');
add_filter('ot_show_new_layout', '__return_false');
add_filter('ot_theme_mode', '__return_true');
load_template(trailingslashit(get_template_directory()) . '/inc/option-tree/ot-loader.php');
require_once locate_template('/inc/meta/meta-boxes.php');

