<?php
function get_game_categories_str($post_id) {
    $terms = get_the_terms($post_id, 'game_category');
    if ($terms && !is_wp_error($terms)) {
        $term_names = array_map(function($term) {
            return $term->name;
        }, $terms);
        return join(', ', $term_names);
    }
    return '';
}

function ca_get_random_games(){
    $orderby_args = Cloudarcade_Wp_Tpl_Manipulations::get_ordering_attribute();

    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    // Query for your 'game' custom post type
    $args = array(
        'post_type' => 'game',
        'posts_per_page' => 4,
        'paged' => $paged,
        'orderby' => 'rand'  // Add this line to order games randomly
    ) + $orderby_args;

    return new WP_Query($args);
}

function ca_get_thumbnail($post_id, $size = null){
    $thumb_url = get_post_meta($post_id, 'game_thumb2', true);
    if($thumb_url[0] == '/'){
        $thumb_url = cloudarcade_get_setting('cloudarcade_domain') . $thumb_url;
    }
    return $thumb_url;
}

function ca_get_game_url($post_id){
    $game_url = get_post_meta($post_id, 'game_url', true);
    if($game_url[0] == '/'){
        $game_url = cloudarcade_get_setting('cloudarcade_domain') . $game_url;
    }
    return $game_url;
}