<?php
/**
 * Load core
 */
require_once 'inc/starter/leaf-core.php';
require_once 'inc/option-tree-hook.php';
require_once 'inc/starter/functions-admin.php';

if (!defined('PARENT_THEME')) {
	define('PARENT_THEME', 'Apkafe');
}

/**
 * Registers the WordPress features
 */
function apkafe_setup()
{
	/*
	 * Makes theme available for translation.
	 */
	load_theme_textdomain('apkafe', get_template_directory() . '/languages');
	add_editor_style();
	add_theme_support('automatic-feed-links');
	add_theme_support('post-formats', array('gallery', 'video', 'audio'));
	add_theme_support('post-thumbnails');
	add_theme_support('title-tag');
	add_theme_support('woocommerce');
}

add_action('after_setup_theme', 'apkafe_setup');

function apkafe_get_option($options, $default = NULL)
{
	global $post;
	global $wp_query;
	if (is_singular()) {
		if (is_singular('tribe_events')) {
			global $wp_query;
			global $post;
			$post = $wp_query->post;
		}
		if (isset($post->ID)) {
			$meta = get_post_meta($post->ID, $options, true);
		}
		return $meta != '' ? $meta : ot_get_option($options, $default);
	}
	return ot_get_option($options, $default);
}

/**
 * Enqueues scripts and styles
 */
function apkafe_scripts_styles()
{
	/*
	 * Loads js.
	 */
	wp_enqueue_script('jquery');
	wp_enqueue_script('bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js', array(), '', true);
	wp_enqueue_script('template', get_template_directory_uri() . '/js/applay.js', array('jquery'), '', true);

	/*
	 * Loads css
	 */
	wp_enqueue_style('bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css');
	wp_enqueue_style('font-awesome', get_template_directory_uri() . '/css/fa/css/font-awesome.min.css');
	wp_enqueue_style('style', get_stylesheet_directory_uri() . '/style.css');
}

add_action('wp_enqueue_scripts', 'apkafe_scripts_styles');

/* Enqueues for Admin */
function apkafe_admin_scripts_styles()
{
	wp_enqueue_style('font-awesome', get_template_directory_uri() . '/css/fa/css/font-awesome.min.css');
	wp_enqueue_style('admin-style', get_template_directory_uri() . '/admin/style.css');
	wp_enqueue_style('wc-blocks-style');
	wp_enqueue_style('dashicons');
}
add_action('admin_enqueue_scripts', 'apkafe_admin_scripts_styles');

remove_action('shutdown', 'wp_ob_end_flush_all', 1);
