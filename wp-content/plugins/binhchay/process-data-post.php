<?php

require('wp-load.php');

$posts = $_POST;
if (empty($posts)) {
    die;
}

global $wpdb;

foreach ($posts as $key => $post) {
    $table = $wpdb->prefix . 'binhchay';
    $sql = "UPDATE " . $table . " SET post_id='" . $post . "' WHERE key_post='" . $key . "'";
    $results = $wpdb->get_results($sql);
}

wp_redirect(admin_url('/admin.php?page=apkafe-seo&status=saved'));
