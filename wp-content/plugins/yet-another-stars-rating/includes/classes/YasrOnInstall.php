<?php
/*

Copyright 2014 Dario Curvino (email : d.curvino@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>
*/

if (!defined('ABSPATH')) {
    exit('You\'re not allowed to see this page');
} // Exit if accessed directly

if (!current_user_can('manage_options') && !defined('WP_CLI') ) {
    /** @noinspection ForgottenDebugOutputInspection */
    wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'yet-another-stars-rating'));
}

/**
 * Class YasrOnInstall
 *
 * This class is run on installation
 *
 * @since 2.3.1
 * @author Dario Curvino <@dudo>
 *
 */
class YasrOnInstall {

    /**
     * YasrOnInstall constructor.
     *
     * @param $network_wide
     */
    public function __construct($network_wide) {
        global $wpdb;

        // Creating tables for all blogs in a WordPress Multisite installation
        if (is_multisite() && $network_wide) {
            // Get all blogs in the network and activate plugin on each one
            $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                self::createTables();
                restore_current_blog();
            }
        }
        //Not a multisite install
        else {
            self::createTables();
        }
        //default settings
        $this->defaultSettings();
    }

    /**
     * Create YASR tables
     *
     * @author Dario Curvino <@dudo>
     */
    public static function createTables () {
        global $wpdb; //Database WordPress object

        $prefix = $wpdb->prefix . 'yasr_';  //Table prefix

        $yasr_multi_set_table    = $prefix . 'multi_set';
        $yasr_multi_set_fields   = $prefix . 'multi_set_fields';
        $yasr_log_multi_set      = $prefix . 'log_multi_set';
        $yasr_log_table          = $prefix . 'log';

        //Do not use IF TABLE EXISTS here
        //see https://wordpress.stackexchange.com/a/302538/48442
        //since this function is called only on plugin activation AND if yasr-version is not found in
        //wp-option, there is no need to check if table exists, unless the user manually remove yasr-version option
        //but not the yasr tables.

        $sql_yasr_multi_set_table = "CREATE TABLE $yasr_multi_set_table (
            set_id int(2) NOT NULL AUTO_INCREMENT,
            set_name varchar(64) COLLATE utf8_unicode_ci NOT NULL,
            UNIQUE KEY set_id (set_id),
            UNIQUE KEY set_name (set_name)
        ) COLLATE 'utf8_unicode_ci';";

        $sql_yasr_multi_set_fields = "CREATE TABLE $yasr_multi_set_fields (
            id int(3) NOT NULL AUTO_INCREMENT,
            parent_set_id int(2) NOT NULL,
            field_name varchar(40) COLLATE utf8_unicode_ci NOT NULL,
            field_id int(2) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY id (id)
        ) COLLATE 'utf8_unicode_ci';";

        //log multiset
        $sql_yasr_log_multi_set_table = "CREATE TABLE $yasr_log_multi_set (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            field_id int(2) NOT NULL,
            set_type   int(2) NOT NULL,
            post_id    bigint(20) NOT NULL,
            comment_id bigint(20) NOT NULL,
            vote decimal(2,1) NOT NULL,
            user_id bigint(20) NOT NULL,
            date datetime NOT NULL,
            ip varchar(45) COLLATE 'utf8_unicode_ci' NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY id (id)
        ) COLLATE 'utf8_unicode_ci';";

        //Since version 2.0.9 user_id is bigint 20 and vote decimal 2,1
        //format DECIMAL(M, D) where M is the maximum number of digits (the precision) and D is the
        //number of digits to the right of the decimal point (the scale).
        $sql_yasr_log_table = "CREATE TABLE $yasr_log_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            vote decimal(2,1) NOT NULL,
            date datetime NOT NULL,
            ip varchar(45) COLLATE utf8_unicode_ci NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY id (id)
        ) COLLATE 'utf8_unicode_ci';";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql_yasr_multi_set_table);
        dbDelta($sql_yasr_multi_set_fields);
        dbDelta($sql_yasr_log_multi_set_table);
        dbDelta($sql_yasr_log_table);
    }

    /**
     * Save the default Settings
     *
     * @author Dario Curvino <@dudo>
     */
    private function defaultSettings() {
        $settings = new YasrSettingsValues();
        add_option('yasr_general_options',  $settings->getGeneralSettings());
        add_option('yasr_style_options',    $settings->getStyleSettings());
        add_option('yasr_multiset_options', $settings->getMultiSettings());
    }

}
