<?php

class Cloudarcade_Wp_Shortcodes {

	public function init() {
		add_shortcode('game_list', array($this, 'game_list_shortcode'));
		add_shortcode('cawp', array($this, 'cawp_shortcode'));
	}

	function game_shortcode($atts) {
	    global $cloudarcade_db;

		// if wp error
		if( is_wp_error( $cloudarcade_db->error ) ){
			return '';
		}

	    // Shortcode attributes for 'id'
	    $a = shortcode_atts( array(
	        'id' => '0',
	    ), $atts );

	    $id = intval($a['id']);  // Make sure the ID is a valid integer

	    // Get the game data from your second database
	    $game = $cloudarcade_db->get_row("SELECT * FROM your_table WHERE id = $id");

	    // Return the iframe code with game URL
	    return '<iframe src="' . esc_url($game->url) . '" width="100%" height="600"></iframe>';
	}
	public function game_list_shortcode($atts) {
		global $cloudarcade_db;

		// if wp error
		if( is_wp_error( $cloudarcade_db->error ) ){
			return '';
		}

		$games = $cloudarcade_db->get_results("SELECT * FROM games LIMIT 3");

		$output = '<div class="game-list">';
		foreach($games as $game) {
			$output .= '<div class="game">';
			$output .= '<h2>' . $game->title . '</h2>';
			$output .= '<iframe src="' . $game->url . '"></iframe>';
			$output .= '</div>';
		}
		$output .= '</div>';

		return $output;
	}
	function cawp_shortcode($atts) {
		$atts = shortcode_atts( array(
	        'amount' => '10',
	        'category' => false,
	        'sortby' => false,
	    ), $atts );
 
		// Query for your 'game' custom post type
		$args = array(
			'post_type' => 'game',
			'posts_per_page' => $atts['amount']
		);
		if( $atts['category'] ){
			$args['tax_query'] = [
				[
					'taxonomy' => 'game_category',
					'field' => 'slug',
					'terms' => $atts['category']
				]
			];
		}
		if( $atts['sortby'] ){
			switch ( $atts['sortby'] ) {
				case 'popularity':
					$orderby_args = array(
						'meta_key' => 'popularity',
						'orderby'  => 'meta_value_num'
					);
					break;
				case 'rating':
					$orderby_args = array(
						'meta_key' => 'rating',
						'orderby'  => 'meta_value_num'
					);
					break;
				case 'date':
					$orderby_args = array(
						'orderby'  => 'date',
						'order'    => 'DESC'
					);
					break;
			}
			$args = $args + $orderby_args;
		}
		
		$game_query = new WP_Query($args);
	
		 if ($game_query->have_posts()) :  

			$output = '
			<div class="cloudarcade archive-game">
				<ul class="games">';
					 while ($game_query->have_posts()) : $game_query->the_post(); 
						ob_start(); // start output buffering
						include(CLOUDARCADE_WP_ROOT.'/templates/content-game.php');  
						$output .= ob_get_clean(); // end output buffering and capture the output
					  endwhile; 
			$output .= '
				</ul>					
			</div>';
 

		else:  
			$output .= '<p>'.esc_html_e('No games found.', 'text-domain').'</p>';
		endif;
	 
		return $output;
	}

}


?>