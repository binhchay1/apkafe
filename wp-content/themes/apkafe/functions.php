<?php

/**
 * Load core
 */
require_once 'inc/starter/leaf-core.php';
require_once 'inc/option-tree-hook.php';
require_once 'inc/starter/functions-admin.php';
require_once 'inc/starter/category-image.php';

remove_action('shutdown', 'wp_ob_end_flush_all', 1);

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

/**
 * Enqueues scripts and styles
 */
function apkafe_scripts_styles()
{
	/*
	 * Loads js.
	 */
	wp_enqueue_script('jquery');
	wp_enqueue_script('template', get_template_directory_uri() . '/js/apkafe.js', array('jquery'), '', true);

	/*
	 * Loads css
	 */
	wp_enqueue_style('font-awesome', get_template_directory_uri() . '/css/fa/css/font-awesome.min.css');
	wp_enqueue_style('style', get_stylesheet_directory_uri() . '/style.css');
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

function addTitleFieldToCat()
{
	$cat_title = get_term_meta($_POST['tag_ID'], '_pagetitle', true);
?>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="cat_page_title"><?php _e('Category Page Title'); ?></label></th>
		<td>
			<input type="text" name="cat_title" id="cat_title" value="<?php echo $cat_title ?>"><br />
			<span class="description"><?php _e('Title for the Category '); ?></span>
		</td>
	</tr>
<?php

}
add_action('edit_category_form_fields', 'addTitleFieldToCat');

function saveCategoryFields()
{
	if (isset($_POST['cat_title'])) {
		update_term_meta($_POST['tag_ID'], '_pagetitle', $_POST['cat_title']);
	}
}
add_action('edited_category', 'saveCategoryFields');
