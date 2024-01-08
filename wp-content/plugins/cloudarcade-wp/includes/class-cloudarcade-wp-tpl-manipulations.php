<?php

class Cloudarcade_Wp_Tpl_Manipulations {

 
	public function __construct() {
	
		//add_action('init', [ $this, 'cawp_update_game_permalink_structure'] );
		//add_action('init', [ $this, 'cawp_register_game_archive_rewrite_rule'] );
		add_action('template_redirect', [ $this, 'cawp_redirect_game_archive_template'] );
		add_filter('template_include', [ $this, 'cawp_my_plugin_category_template' ] );
		//add_filter('single_template', 'cawp_load_custom_single_game_template');
	}

	
	function cawp_update_game_permalink_structure() {
		global $wp_rewrite;
		$game_permalink_structure = '/game/%game%';
		$wp_rewrite->add_rewrite_tag('%game%', '([^/]+)', 'game=');
		$wp_rewrite->add_permastruct('game', $game_permalink_structure, false);
	}


	function cawp_register_game_archive_rewrite_rule() {
		add_rewrite_rule('^games$', 'index.php?post_type=game', 'top' );
	}


	function cawp_redirect_game_archive_template() {
		if (is_post_type_archive('game')) {
			include( CLOUDARCADE_WP_ROOT . 'templates/archive-game.php' );
			exit;
		}
	}

	function cawp_load_custom_single_game_template($template) {
		if (is_singular('game')) {
			include( CLOUDARCADE_WP_ROOT . 'templates/single-game.php' );
		}
	}

	

	function cawp_my_plugin_category_template($template) {
		if (is_tax('game_category')) {
			// Use the template provided by the plugin
			$template = CLOUDARCADE_WP_ROOT. 'templates/taxonomy-game-category.php';
		}

		return $template;
	}

	public static function get_archive_ordering(){
		$out = '
		<form class="cloudarcade-archive-ordering" method="get">
		<select name="orderby" class="orderby" aria-label="Shop order" onchange="this.form.submit()">
						<option value="menu_order" selected="selected">Default sorting</option>
						<option value="popularity">Sort by popularity</option>
						<option value="date">Sort by latest</option>
				</select>
		<input type="hidden" name="paged" value="1">
		</form>
		';
		return $out;
	}
	public static function get_archive_pagination( $game_query ){
		 
		$big = 999999999; // need an unlikely integer
		$out =  paginate_links(array(
        'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format' => '?paged=%#%',
        'current' => max(1, get_query_var('paged')),
        'total' => $game_query->max_num_pages
    	));
		$out = "<div class='pagination cloudarcade-pagination-container'><div class='nav-links cloudarcade-pagination'>{$out}</div></div>";
		return $out;
	}
	public static function get_ordering_attribute(){
		 
		// Get the orderby value from the query string
		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : '';

		// Create an array to store your orderby arguments
		$orderby_args = array();

		// Modify the orderby arguments based on the orderby value
		switch ( $orderby ) {
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
		return $orderby_args;
	}


}
