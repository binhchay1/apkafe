<?php
/*
 * Applay functions
 */

/* Define list of recommended and required plugins */

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

if (!defined('PARENT_THEME')) {
	define('PARENT_THEME', 'Applay');
}

/**
 * Registers the WordPress features
 */
function leafcolor_setup()
{
	/*
	 * Makes theme available for translation.
	 */
	load_theme_textdomain('leafcolor', get_template_directory() . '/languages');

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// Post formats.
	add_theme_support('post-formats', array('gallery', 'video', 'audio'));

	// Register menus.
	register_nav_menu('primary-menus', __('Primary Menus', 'leafcolor'));
	register_nav_menu('off-canvas-menus', __('Off Canvas Menus', 'leafcolor'));

	// Featured images.
	add_theme_support('post-thumbnails');
	add_theme_support('title-tag');

	// Supports woocommerce.
	add_theme_support('woocommerce');
}
add_action('after_setup_theme', 'leafcolor_setup');

/**
 * Enqueues scripts and styles
 */
function leafcolor_scripts_styles()
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

add_action('wp_enqueue_scripts', 'leafcolor_scripts_styles');

/* Enqueues for Admin */
function leafcolor_admin_scripts_styles()
{
	wp_enqueue_style('font-awesome', get_template_directory_uri() . '/css/fa/css/font-awesome.min.css');
	wp_enqueue_style('wc-blocks-style');
	wp_enqueue_style('dashicons');
}
add_action('admin_enqueue_scripts', 'leafcolor_admin_scripts_styles');

remove_action('shutdown', 'wp_ob_end_flush_all', 1);

/* Display Icon Links to some social networks */
if (!function_exists('leafcolor_social_share')) {
	function leafcolor_social_share($id = false)
	{
		if (!$id) {
			$id = get_the_ID();
		}
?>
		<?php if (ot_get_option('share_facebook', 'on') != 'off') { ?>
			<li><a class="btn btn-default btn-lighter social-icon" title="<?php _e('Share on Facebook', 'leafcolor'); ?>" href="#" target="_blank" rel="nofollow" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u='+'<?php echo urlencode(get_permalink($id)); ?>','facebook-share-dialog','width=626,height=436');return false;"><i class="fa fa-facebook"></i></a></li>
		<?php } ?>
		<?php if (ot_get_option('share_twitter', 'on') != 'off') { ?>
			<li><a class="btn btn-default btn-lighter social-icon" href="#" title="<?php _e('Share on Twitter', 'leafcolor'); ?>" rel="nofollow" target="_blank" onclick="window.open('http://twitter.com/share?text=<?php echo urlencode(get_the_title($id)); ?>&url=<?php echo urlencode(get_permalink($id)); ?>','twitter-share-dialog','width=626,height=436');return false;"><i class="fa fa-twitter"></i></a></li>
		<?php } ?>
		<?php if (ot_get_option('share_linkedin', 'on') != 'off') { ?>
			<li><a class="btn btn-default btn-lighter social-icon" href="#" title="<?php _e('Share on LinkedIn', 'leafcolor'); ?>" rel="nofollow" target="_blank" onclick="window.open('http://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(get_permalink($id)); ?>&title=<?php echo urlencode(get_the_title($id)); ?>&source=<?php echo urlencode(get_bloginfo('name')); ?>','linkedin-share-dialog','width=626,height=436');return false;"><i class="fa fa-linkedin"></i></a></li>
		<?php } ?>
		<?php if (ot_get_option('share_tumblr', 'on') != 'off') { ?>
			<li><a class="btn btn-default btn-lighter social-icon" href="#" title="<?php _e('Share on Tumblr', 'leafcolor'); ?>" rel="nofollow" target="_blank" onclick="window.open('http://www.tumblr.com/share/link?url=<?php echo urlencode(get_permalink($id)); ?>&name=<?php echo urlencode(get_the_title($id)); ?>','tumblr-share-dialog','width=626,height=436');return false;"><i class="fa fa-tumblr"></i></a></li>
		<?php } ?>
		<?php if (ot_get_option('share_google_plus', 'on') != 'off') { ?>
			<li><a class="btn btn-default btn-lighter social-icon" href="#" title="<?php _e('Share on Google Plus', 'leafcolor'); ?>" rel="nofollow" target="_blank" onclick="window.open('https://plus.google.com/share?url=<?php echo urlencode(get_permalink($id)); ?>','googleplus-share-dialog','width=626,height=436');return false;"><i class="fa fa-google-plus"></i></a></li>
		<?php } ?>
		<?php if (ot_get_option('share_pinterest', 'on') != 'off') { ?>
			<li><a class="btn btn-default btn-lighter social-icon" href="#" title="<?php _e('Pin this', 'leafcolor'); ?>" rel="nofollow" target="_blank" onclick="window.open('//pinterest.com/pin/create/button/?url=<?php echo urlencode(get_permalink($id)) ?>&media=<?php echo urlencode(wp_get_attachment_url(get_post_thumbnail_id($id))); ?>&description=<?php echo urlencode(get_the_title($id)) ?>','pin-share-dialog','width=626,height=436');return false;"><i class="fa fa-pinterest"></i></a></li>
		<?php } ?>
		<?php if (ot_get_option('share_email', 'on') != 'off') { ?>
			<li><a class="btn btn-default btn-lighter social-icon" href="mailto:?subject=<?php echo esc_attr(get_the_title($id)) ?>&body=<?php echo urlencode(get_permalink($id)) ?>" title="<?php _e('Email this', 'leafcolor'); ?>"><i class="fa fa-envelope"></i></a></li>
		<?php } ?>
<?php }
}
