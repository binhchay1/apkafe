<?php

require('wp-load.php');

$get = $_GET;
if (empty($get['id'])) {
    die;
}

global $wpdb;

$table = $wpdb->prefix . 'trending_search';
$status = $wpdb->delete($table, array(
    'id' => $get['id'],
));
