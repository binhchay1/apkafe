<?php

require('wp-load.php');

$get = $_GET;
if (empty($get['id'])) {
    die;
}

global $wpdb;

$table = $wpdb->prefix . 'top_games';
$status = $wpdb->delete($table, array(
    'post_id' => $get['id'],
));
