<?php
/**
 * Register easy accordion free custom post type.
 *
 * @link       https://shapedplugin.com/
 * @since      2.0.0
 *
 * @package    easy-accordion-free
 * @subpackage easy-accordion-free/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * The file that defines the carousel post type.
 *
 * A class the that defines the carousel post type and make the plugins' menu.
 */

/**
 * Custom post class to register the carousel.
 */
class Easy_Accordion_Free_Post_Type {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since 2.0.0
	 */
	private static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the base class object.
	 *
	 * @since 2.0.0
	 *
	 * @var object
	 */
	public $base;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since 1.0.0
	 * @static
	 * @return self Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Easy Accordion post type
	 */
	public function easy_accordion_post_type() {

		if ( post_type_exists( 'sp_easy_accordion' ) ) {
			return;
		}

		$capability      = apply_filters( 'sp_easy_accordion_ui_permission', 'manage_options' );
		$is_user_capable = current_user_can( $capability ) ? true : false;

		// Set the easy accordion post type labels.
		$labels = apply_filters(
			'sp_easy_accordion_post_type_labels',
			array(
				'name'               => esc_html_x( 'Accordion Groups', 'easy-accordion-free' ),
				'singular_name'      => esc_html_x( 'Accordion', 'easy-accordion-free' ),
				'add_new'            => esc_html__( 'Add New', 'easy-accordion-free' ),
				'add_new_item'       => esc_html__( 'Add Accordion Group', 'easy-accordion-free' ),
				'edit_item'          => esc_html__( 'Edit Accordion Group', 'easy-accordion-free' ),
				'new_item'           => esc_html__( 'New Accordion', 'easy-accordion-free' ),
				'view_item'          => esc_html__( 'View Accordion', 'easy-accordion-free' ),
				'search_items'       => esc_html__( 'Search Accordion Group', 'easy-accordion-free' ),
				'not_found'          => esc_html__( 'No WP Accordion found.', 'easy-accordion-free' ),
				'not_found_in_trash' => esc_html__( 'No WP Accordion found in trash.', 'easy-accordion-free' ),
				'parent_item_colon'  => esc_html__( 'Parent Item:', 'easy-accordion-free' ),
				'menu_name'          => esc_html__( 'Easy Accordion', 'easy-accordion-free' ),
				'all_items'          => esc_html__( 'Accordion Groups', 'easy-accordion-free' ),
			)
		);

		// Base 64 encoded SVG image.
		$icon      = 'PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDUxMiA1MTIiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDUxMiA1MTI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCiAgICAgICAgPHN0eWxlIHR5cGU9InRleHQvY3NzIj4NCiAgICAgICAgICAgIC5zdDB7ZmlsbDojQTBBNUFBO30NCiAgICAgICAgPC9zdHlsZT4NCiAgICAgICAgPGc+DQogICAgICAgICAgICA8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMCwxMjh2MjU2aDUxMlYxMjhIMHogTTQ4MCwzNTJIMzJWMjI0aDQ0OFYzNTJ6Ii8+DQogICAgICAgICAgICA8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMCwwaDUxMnY5NkgwVjB6Ii8+DQogICAgICAgICAgICA8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMCw0MTZoNTEydjk2SDBWNDE2eiIvPg0KICAgICAgICA8L2c+DQogICAgPC9zdmc+';
		$menu_icon = 'data:image/svg+xml;base64,' . $icon;

		// Set the easy accordion post type arguments.
		$args = apply_filters(
			'sp_easy_accordion_post_type_args',
			array(
				'labels'              => $labels,
				'public'              => false,
				'hierarchical'        => false,
				'exclude_from_search' => true,
				'show_ui'             => $is_user_capable,
				'show_in_admin_bar'   => false,
				'menu_position'       => apply_filters( 'sp_easy_accordion_menu_position', 116 ),
				'menu_icon'           => $menu_icon,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array(
					'title',
				),
			)
		);
		register_post_type( 'sp_easy_accordion', $args );
	}
}
