<?php

class Cloudarcade_Wp_Taxonomies {

	public function init() {
		add_action('init', array($this, 'create_taxonomy'));
	}

	public function create_taxonomy() {
		$labels = array(
			'name'                       => 'Game Categories',
			'singular_name'              => 'Game Category',
			'menu_name'                  => 'Game Categories',
			'all_items'                  => 'All Categories',
			'edit_item'                  => 'Edit Category',
			'view_item'                  => 'View Category',
			'update_item'                => 'Update Category',
			'add_new_item'               => 'Add New Category',
			'new_item_name'              => 'New Category Name',
			'parent_item'                => 'Parent Category',
			'parent_item_colon'          => 'Parent Category:',
			'search_items'               => 'Search Categories',
			'popular_items'              => 'Popular Categories',
			'separate_items_with_commas' => 'Separate categories with commas',
			'add_or_remove_items'        => 'Add or remove categories',
			'choose_from_most_used'      => 'Choose from the most used categories',
			'not_found'                  => 'No categories found',
		);
	
		$args = array(
			'labels'            => $labels,
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug' => 'game-category',  // Customize the category slug as needed
			),
		);
	
		register_taxonomy('game_category', 'game', $args);
	}
}

?>