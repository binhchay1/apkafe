<?php

require('wp-load.php');

$get = $_GET;
if (empty($get['id'])) {
    die;
}

global $wpdb;

$table = $wpdb->prefix . 'top_games';
$wpdb->insert($table, array(
    'post_id' => $get['id'],
));
