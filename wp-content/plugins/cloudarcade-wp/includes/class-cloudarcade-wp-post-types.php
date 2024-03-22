<?php

class Cloudarcade_Wp_Post_Types {

	public function init() {
		add_action('init', array($this, 'create_game_post_type'));

		/** hide add new items for Game */
		add_action('admin_menu', array($this, 'disable_new_posts' ) );
	}

	public function create_game_post_type() {
		$args = array(
			'public' => true,
			'label'  => 'Games',
			'show_in_menu' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => array('slug' => cloudarcade_get_setting('game_slug')),
			'query_var' => true,
			'menu_icon' => 'dashicons-games',
			'has_archive' => false,
			'show_in_menu' => 'cloudarcade-wp-games',
			'supports' => array(
				'title',
				'editor',
				'excerpt',
				'trackbacks',
				'custom-fields',
				'comments',
				'revisions',
				'thumbnail',
				'author',
				'page-attributes'
			),
		 
		);
		register_post_type('game', $args);
		flush_rewrite_rules();
	}


	function disable_new_posts() {
		// Hide sidebar link
		global $submenu;
		unset($submenu['edit.php?post_type=game'][10]);
	
		$show_hider  = false;
		if ( ( isset($_GET['post_type']) && $_GET['post_type'] == 'game' )  ) {
			$show_hider = true;
		}
		if ( isset( $_GET['post']  )  ) {
			if( get_post_type( $_GET['post']  ) == 'game' ){
				$show_hider = true;
			}
		}
		// Hide link on listing page
		if ( $show_hider  ) {
			echo '<style type="text/css">
			#favorite-actions, .add-new-h2, .tablenav, .admin-bar.post-type-game .page-title-action{ display:none; }
			</style>';
		}
	}
	

}

?>