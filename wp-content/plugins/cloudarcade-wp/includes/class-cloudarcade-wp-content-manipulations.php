<?php

class Cloudarcade_Wp_Content_Manipulations {

 
	public function __construct() {
	
		add_filter( 'init', [ $this, 'patch_popularity_filed' ] );
		add_filter( 'the_content', [ $this, 'cloudarcade_content_after_page' ] );
		add_filter( 'the_content', [ $this, 'append_single_game_content' ] );
		add_action( 'the_content', [ $this, 'calculate_game_popularity' ] );
		add_action( 'pre_get_posts', [ $this, 'cloudarcade_ordering' ] );
	}
 
	/**
	 * preinit popularity field
	 */
	function patch_popularity_filed(){
		$all_games = get_posts([
			'post_type' => 'game',
			'showposts' => -1,
			'fields' => 'ids',
			'meta_query' => [
				[
					'key' => 'popularity',
					'value' => '',
					'compare' => 'NOT EXISTS'
				]
			]
		]);
		foreach( $all_games as $s_game_id ){
			update_post_meta( $s_game_id, 'popularity', 0 );
		}
	}

	/**
	 * calculate and increase popularity
	 */
	function calculate_game_popularity( $content ){
		global $post, $wpdb;
		if( is_single() && get_post_type( $post->ID ) == 'game' ){		
			if( get_post_meta( $post->ID, 'popularity', true ) == '' ){
				update_post_meta( $post->ID, 'popularity', 0 );
			}			
			$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = meta_value + 1 WHERE post_id = %d AND meta_key = 'popularity' ", $post->ID ) );
		}
		return $content;
	}

	
	/**
	 * output for games page - list of all games used as shortcode
	 */
	function cloudarcade_content_after_page($content) {
		// Check if we're inside the main loop in a single page.
		$game_list_page = cloudarcade_get_setting('games_display_page');
		if ( is_page($game_list_page) && in_the_loop() && is_main_query() ) {
			remove_filter( 'the_content', 'cloudarcade_content_after_page' );
			ob_start(); // start output buffering
			include CLOUDARCADE_WP_ROOT. 'templates/archive-game.php';
			$output = ob_get_clean(); // end output buffering and capture the output
			add_filter( 'the_content', 'cloudarcade_content_after_page' );
			$content .= $output; // append the output to the existing content
		}
		return $content;
	}
	

	/**
	 * single game content output
	 */
	function append_single_game_content( $content ) {
		// Check if we're inside the main loop in a single post page.
		if ( is_single() && in_the_loop() && is_main_query() ) {
			if ( get_post_type() === 'game' ) {
				remove_filter( 'the_content', 'cloudarcade_content_game' );
				ob_start(); // start output buffering
				include CLOUDARCADE_WP_ROOT. 'templates/content-single-game.php';
				$output = ob_get_clean(); // end output buffering and capture the output
				add_filter( 'the_content', 'cloudarcade_content_game' );
				$content = $output; // append the output to the existing content
				// Your custom content
				// $custom_content = "<p>This is my custom content.</p>";
				// $content = $custom_content;
			}
		}
		return $content;
	}
	

	function cloudarcade_ordering( $query ) {
		// Only modify the main query on the frontend for the custom post type archive
		if ( is_admin() || ! $query->is_main_query() || ! is_post_type_archive('game') ) {
			return;
		}

		// Get the orderby value from the query string
		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : '';

		// Modify the query based on the orderby value
		switch ( $orderby ) {
			case 'popularity':
				$query->set( 'meta_key', 'popularity' );
				$query->set( 'orderby', 'meta_value_num' );
				break;
			case 'rating':
				$query->set( 'meta_key', 'rating' );
				$query->set( 'orderby', 'meta_value_num' );
				break;
			case 'date':
				$query->set( 'orderby', 'date' );
				break;
		}
	}
	

}
