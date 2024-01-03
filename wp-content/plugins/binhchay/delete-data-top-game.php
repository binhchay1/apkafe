<?php

$root = preg_replace('/wp-content.*$/', '', __DIR__);
require($root . 'wp-load.php');

$get = $_GET;
if (empty($get['id'])) {
    die;
}

global $wpdb;

$table = $wpdb->prefix . 'top_games';
$status = $wpdb->delete($table, array(
    'post_id' => $get['id'],
));
