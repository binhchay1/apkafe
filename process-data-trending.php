<?php

require('wp-load.php');

$get = $_GET;
if (empty($get['title']) || empty($get['url'])) {
    die;
}

global $wpdb;

$table = $wpdb->prefix . 'trending_search';
$wpdb->insert($table, array(
    'title' => $get['title'],
    'url' => $get['url'],
));
