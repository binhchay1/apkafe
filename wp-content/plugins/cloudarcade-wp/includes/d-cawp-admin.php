<?php

function cawp_sync_category() {
    global $wpdb, $cloudarcade_db;  // access both databases
    Cloudarcade_Wp::setup_second_db();
    // if wp error
    if( $cloudarcade_db == null || is_wp_error( $cloudarcade_db->error ) ){
        return 'error';
    }

    $count_newly_added_categories = 0;
    $count_exist_categories = 0;
    $count_updated_categories = 0;
    $count_removed_categories = 0;

    // Get all categories from the second database
    $categories = $cloudarcade_db->get_results("SELECT * FROM categories");

    // Create an array to hold slugs of categories from the second database
    $cloudarcade_db_slugs = array();
    foreach ($categories as $category) {
        $cloudarcade_db_slugs[] = $category->slug;
    }

    // Loop through WordPress categories to remove those not in the second database
    $wp_terms = get_terms(array(
        'taxonomy' => 'game_category',
        'hide_empty' => false,
    ));

    foreach ($wp_terms as $wp_term) {
        if (!in_array($wp_term->slug, $cloudarcade_db_slugs)) {
            wp_delete_term($wp_term->term_id, 'game_category');
            $count_removed_categories++;
        }
    }

    // Loop through second database categories to add or update them in WordPress
    foreach ($categories as $category) {
        $category_slug = $category->slug;
        $term = term_exists($category->name, 'game_category');

        if ($term) {
            // Update term if it already exists
            $term_id = $term['term_id'];
            wp_update_term($term_id, 'game_category', array(
                'description' => $category->description,
            ));
            $count_updated_categories++;
        } else {
            // Insert term if it does not exist
            $new_term = wp_insert_term(
                $category->name,
                'game_category',
                array(
                    'slug' => $category_slug,
                    'description' => $category->description,
                )
            );
            if (!is_wp_error($new_term)) {
                $count_newly_added_categories++;
            }
        }
    }

    // Return a summary message
    return "{$count_newly_added_categories} categories added, {$count_updated_categories} categories updated, {$count_removed_categories} categories removed.";
}

function cawp_sync_games() {
    global $wpdb, $cloudarcade_db;  // access both databases
    Cloudarcade_Wp::setup_second_db();
    // if wp error
    if( $cloudarcade_db == null || is_wp_error( $cloudarcade_db->error ) ){
        return 'error';
    }
    
    $count_newly_added_games = 0;
    $count_exist_games = 0;
    $count_updated_games = 0;
    // Get all games from the second database
    $games = $cloudarcade_db->get_results("SELECT * FROM games");

    $game_categories = [];
    foreach ($cloudarcade_db->get_results("SELECT * FROM categories") as $cat) $game_categories[$cat->name] = $cat;

    // Loop through each game
    foreach($games as $game) {
        // Check if the game post already exists in WordPress based on some unique identifier (e.g., game ID)
        $existing_game = get_posts(array(
            'post_type'      => 'game',
            'meta_key'       => 'game_id',  // Adjust to the actual meta key for the unique identifier
            'meta_value'     => $game->id,  // Adjust to the actual value of the unique identifier
            'posts_per_page' => 1,
        ));

        if ($existing_game) {
            if ($existing_game[0]->post_modified != $game->last_modified && $game->last_modified != null) {
                $count_updated_games++;
                // Game post already exists, update the custom fields
                $game_post_id = $existing_game[0]->ID;

                update_post_meta($game_post_id, 'game_instructions', $game->instructions);
                update_post_meta($game_post_id, 'game_thumb1', $game->thumb_1);
                update_post_meta($game_post_id, 'game_thumb2', $game->thumb_2);
                update_post_meta($game_post_id, 'game_url', $game->url);
                update_post_meta($game_post_id, 'game_width', $game->width);
                update_post_meta($game_post_id, 'game_height', $game->height);
                // Update other custom fields as needed
                $wpdb->update( 
                    $wpdb->posts, 
                    array( 
                        'post_modified' => date('Y-m-d H:i:s', strtotime($game->last_modified)),  // string
                        'post_modified_gmt' => get_gmt_from_date( date('Y-m-d H:i:s', strtotime($game->last_modified)) )  // string
                    ), 
                    array( 'ID' => $game_post_id ), 
                    array( 
                        '%s',   // value1
                        '%s'    // value2
                    ), 
                    array( '%d' ) 
                );
                // Assign categories
                if (!empty($game->category)) {
                    $categories = explode(',', $game->category);
                    $term_ids = array();

                    foreach ($categories as $category) {
                        $category_slug = sanitize_title($category);
                        $term = term_exists($category, 'game_category');

                        if ($term) {
                            $term_ids[] = $term['term_id'];
                        } else {
                            $new_term = wp_insert_term($category, 'game_category', array('slug' => $category_slug, 'description' => $game_categories[$category]->description));
                            if (!is_wp_error($new_term) && isset($new_term['term_id'])) {
                                $term_ids[] = $new_term['term_id'];
                            }
                        }
                    }

                    wp_set_post_terms($game_post_id, $term_ids, 'game_category', false);
                }
            } else {
                $count_exist_games++;
            }
        } else {
            $count_newly_added_games++;
            // Game post doesn't exist, create a new game post and set the custom fields
            $new_game = array(
                'post_title'   => $game->title,
                'post_name'    => $game->slug,
                'post_content' => $game->description,
                'post_type'    => 'game',
                'post_status'  => 'publish',
                // Add other fields here
            );

            $new_game_id = wp_insert_post($new_game);

            if (!is_wp_error($new_game_id)) {
                // Set the custom fields for the new game post
                update_post_meta($new_game_id, 'game_instructions', $game->instructions);
                update_post_meta($new_game_id, 'game_thumb1', $game->thumb_1);
                update_post_meta($new_game_id, 'game_thumb2', $game->thumb_2);
                update_post_meta($new_game_id, 'game_url', $game->url);
                update_post_meta($new_game_id, 'game_width', $game->width);
                update_post_meta($new_game_id, 'game_height', $game->height);
                update_post_meta($new_game_id, 'game_id', $game->id);
                // Set other custom fields as needed

                // Assign categories
                if (!empty($game->category)) {
                    $categories = explode(',', $game->category);
                    $term_ids = array();

                    foreach($categories as $category) {
                        $category_slug = sanitize_title($category);
                        $term = term_exists($category, 'game_category');
                        if ($term) {
                            $term_ids[] = $term['term_id'];
                        } else {
                            $new_term = wp_insert_term($category, 'game_category', array('slug' => $category_slug, 'description' => $game_categories[$category]->description));
                            if (!is_wp_error($new_term) && isset($new_term['term_id'])) {
                                $term_ids[] = $new_term['term_id'];
                            }
                        }
                    }

                    wp_set_post_terms($new_game_id, $term_ids, 'game_category', false);
                }
            }
        }
    }
    $count_removed_games = cawp_remove_missing_games();
    // Return a success message
    cawp_delete_unused_categories();
    return $count_newly_added_games." games added!, ".$count_updated_games." games updated!, ".$count_exist_games." already exist!, ".$count_removed_games." games removed!";
}

function cawp_remove_missing_games(){
    global $wpdb, $cloudarcade_db;
    Cloudarcade_Wp::setup_second_db();
    $count = 0;
    // First, collect all IDs from second_db into an array
    $cloudarcade_db_ids = $cloudarcade_db->get_col("SELECT id FROM games");
    $cloudarcade_db_ids = array_map('intval', $cloudarcade_db_ids);  // Convert all IDs to integers

    // Then, collect all IDs from WordPress database into another array
    $all_wp_game_posts = get_posts(array(
        'post_type'      => 'game',
        'meta_key'       => 'game_id',  // This should match the actual meta key for your game posts
        'posts_per_page' => -1,         // Retrieve all posts
        'fields'         => 'ids',      // Only get post IDs to improve performance
    ));

    $wp_game_ids = array();
    foreach($all_wp_game_posts as $post_id) {
        $game_id = get_post_meta($post_id, 'game_id', true);
        $wp_game_ids[] = intval($game_id);  // Convert to integer
    }

    // Find missing IDs
    $missing_ids = array_diff($wp_game_ids, $cloudarcade_db_ids);

    // Delete missing posts
    foreach($missing_ids as $missing_id) {
        // Find the WP post ID by the game_id meta value
        $posts_to_delete = get_posts(array(
            'post_type'      => 'game',
            'meta_key'       => 'game_id',
            'meta_value'     => $missing_id,
            'posts_per_page' => 1,
            'fields'         => 'ids',  // Only get post IDs to improve performance
        ));

        if(!empty($posts_to_delete)) {
            $count++;
            wp_delete_post($posts_to_delete[0], true);  // Delete post by ID. The second parameter indicates whether to bypass the trash (true means the post will be deleted permanently)
        }
    }
    return $count;
}

function cawp_delete_all_game_posts() {
    // Query all game posts
    $args = array(
        'post_type'      => 'game',
        'posts_per_page' => -1, // Get all posts
    );

    $game_posts = get_posts($args);

    // Delete each game post
    foreach ($game_posts as $game_post) {
        wp_delete_post($game_post->ID, true);
    }

    // Return a success message
    return count($game_posts) . ' games removed.';
}

function cawp_delete_unused_categories () {
    $terms = get_terms( [
        'taxonomy'               => 'game_category',
        'hide_empty'             => false,
    ] );

    foreach ( $terms as $t ) {
        if ( 0 === $t->count ) {
            wp_delete_term( $t->term_id, 'game_category' );
        }
    }
}

add_filter('pre_set_site_transient_update_plugins', 'cawp_check_for_update');

function cawp_check_for_update($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }

    $api_url = 'https://api.cloudarcade.net/wp-plugin/cloudarcade-wp/update.php';
    $plugin_path = 'cloudarcade-wp/cloudarcade-wp.php'; // Path to the main plugin file

    // Send request to your server to check for updates
    $response = wp_remote_get($api_url);
    
    if (is_wp_error($response)) {
        return $transient;
    }
    
    $response_body = wp_remote_retrieve_body($response);
    $update_data = json_decode($response_body);
    if (!empty($update_data) && is_object($update_data)) {
        $icons_array = json_decode(json_encode($update_data->icons), true);
        $update_data->icons = $icons_array;
        // Ensure the object has all the necessary properties
        if (isset($update_data->new_version, $update_data->slug, $update_data->url, $update_data->package)) {
            // If there is a new version, modify the transient to reflect an update is available
            if (version_compare($transient->checked[$plugin_path], $update_data->new_version, '<')) {
                // Cast the update data to an array as WordPress expects an array
                $transient->response[$plugin_path] = $update_data;
            }
        }
    }
    
    return $transient;
}
