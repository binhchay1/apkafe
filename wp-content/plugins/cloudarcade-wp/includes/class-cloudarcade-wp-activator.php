<?php

/**
 * Fired during plugin activation
 *
 * @link       https://cloudarcade.net
 * @since      1.0.0
 *
 * @package    Cloudarcade_Wp
 * @subpackage Cloudarcade_Wp/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cloudarcade_Wp
 * @subpackage Cloudarcade_Wp/includes
 * @author     CloudArcade <hello@redfoc.com>
 */
class Cloudarcade_Wp_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Flush rewrite rules
        flush_rewrite_rules();

        // Check if the 'games' page exists
        if (!get_page_by_path('games')) {
            // Create post object for your "games" page
            $my_post = array(
                'post_title'    => wp_strip_all_tags('Games'),
                'post_content'  => '',
                'post_status'   => 'publish',
                'post_author'   => 1,
                'post_type'     => 'page',
                'post_name'     => 'games'
            );

            // Insert the post into the database
            wp_insert_post($my_post);
        }
    }
}