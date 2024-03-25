<?php
/*
 * Apkafe functions
 */

/* Define list of recommended and required plugins */

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

if (!defined('PARENT_THEME')) {
	define('PARENT_THEME', 'Apkafe');
}

/**
 * Registers the WordPress features
 */
function apkafe_init()
{
	/*
	 * Makes theme available for translation.
	 */
	load_theme_textdomain('apkafe', get_template_directory() . '/languages');

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();
}

add_action('after_setup_theme', 'apkafe_init');

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
	wp_enqueue_script('template', get_template_directory_uri() . '/js/apkafe.js', array('jquery'), '', true);

	/*
	 * Loads css
	 */
	wp_enqueue_style('bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css');
	wp_enqueue_style('font-awesome', get_template_directory_uri() . '/css/fa/css/font-awesome.min.css');
	wp_enqueue_style('lightbox2', get_template_directory_uri() . '/js/colorbox/colorbox.css');
	wp_enqueue_style('style', get_stylesheet_directory_uri() . '/style.css');

	if (is_singular()) wp_enqueue_script('comment-reply');
}

add_action('wp_enqueue_scripts', 'apkafe_scripts_styles');

/* Enqueues for Admin */
function apkafe_admin_scripts_styles()
{
	wp_enqueue_style('font-awesome', get_template_directory_uri() . '/css/fa/css/font-awesome.min.css');
	wp_enqueue_style('wc-blocks-style');
	wp_enqueue_style('dashicons');
}
add_action('admin_enqueue_scripts', 'apkafe_admin_scripts_styles');

remove_action('shutdown', 'wp_ob_end_flush_all', 1);
