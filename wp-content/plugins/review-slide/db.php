<?php

function create_top_game_category_table()
{
    global $wpdb;
    $db_table_name = $wpdb->prefix . 'top_game_category';
    $db_version = '1.0.0';
    $charset_collate = $wpdb->get_charset_collate();

    if ($wpdb->get_var("show tables like '$db_table_name'") != $db_table_name) {
        $sql = "CREATE TABLE $db_table_name (
                id int(11) NOT NULL auto_increment,
                category_id varchar(15) NOT NULL,
                game text NOT NULL,
                UNIQUE KEY id (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        add_option('my_db_version', $db_version);
        dbDelta($sql);
    }
}

register_activation_hook(__FILE__, 'create_top_game_category_table');
