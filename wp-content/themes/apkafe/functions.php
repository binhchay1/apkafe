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
	wp_enqueue_script('js', get_template_directory_uri() . '/js/apkafe.js');

	/*
	 * Loads css
	 */
	wp_enqueue_style('font-awesome', get_template_directory_uri() . '/css/fa/css/font-awesome.min.css');
	wp_enqueue_style('style', get_stylesheet_directory_uri() . '/style.css');

	if (is_single()) {
		wp_enqueue_style('single', get_stylesheet_directory_uri() . '/css/single.css');
	}

	if (is_product()) {
		$getCategory = get_the_terms(get_the_ID(), 'product_cat');

		foreach ($getCategory as $term) {
			if ($term->slug == 'review') {
				wp_enqueue_style('review', get_stylesheet_directory_uri() . '/css/review.css');
			}
		}
	}

	if (in_category('top-list')) {
		wp_enqueue_style('review', get_stylesheet_directory_uri() . '/css/review.css');
	}
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

add_action('init', function ($search) {
	add_rewrite_rule('search/?$', 'index.php?s=' . $search, 'top');
	add_rewrite_rule('search/ja/?$', 'index.php?s=' . $search, 'top');
	add_rewrite_rule('search/th/?$', 'index.php?s=' . $search, 'top');
});

add_action('wp_head', function () {
	$paths = explode('/', $_SERVER['REQUEST_URI']);
	if (is_front_page()) {
		$urlFontPage = 'https://apkafe.com/';
		if (in_array('th', $paths)) {
			$urlFontPage = 'https://apkafe.com/th/';
		}

		if (in_array('ja', $paths)) {
			$urlFontPage = 'https://apkafe.com/ja/';
		}

		echo '<link rel="alternate" href="' . $urlFontPage . '" hreflang="x-default" />';
	}

	if (in_array('product-category', $paths)) {
		$host = 'https://apkafe.com';
		$urlProductCategory = $host . $_SERVER['REQUEST_URI'];
		echo '<link rel="alternate" href="' . $urlProductCategory . '" hreflang="x-default" />';
	}

	if (is_admin() || is_user_logged_in()) {
		$style = '<style type="text/css">
			#main-nav {
					margin-top: 30px !important;
			}
			</style>';

		echo $style;
	}
}, PHP_INT_MAX);

function add_custom_option_types($types)
{
	$types['post-list'] = 'Post list';

	return $types;
}
add_filter('ot_option_types_array', 'add_custom_option_types');

if (!function_exists('ot_type_post_list')) {

	function ot_type_post_list($args = array())
	{
		wp_enqueue_style('single', get_stylesheet_directory_uri() . '/inc/option-tree/assets/css/custom-type.css');
		extract($args);
		$has_desc = $field_desc ? true : false;
		echo '<div class="format-setting type-post-checkbox type-checkbox ' . ($has_desc ? 'has-desc' : 'no-desc') . '">';
		if ($has_desc) {
			echo '<div class="description">' . wp_specialchars_decode($field_desc) . '</div>';
		}

		echo '<div class="format-setting-inner">';
		echo '<input type="text" style="margin-bottom: 20px" aria-label="Search list" placeholder="Enter post title" id="search-post-list">';
		$my_posts = get_posts(apply_filters('ot_type_post_checkbox_query', array('posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC', 'post_status' => 'any'), $field_id));
		if (is_array($my_posts) && !empty($my_posts)) {
			foreach ($my_posts as $my_post) {
				$post_url = get_permalink($my_post->ID);
				echo '<p class="item-post">';
				echo '<input type="checkbox" name="' . esc_attr($field_name) . '[' . esc_attr($my_post->ID) . ']" id="' . esc_attr($field_id) . '-' . esc_attr($my_post->ID) . '" value="' . esc_attr($my_post->ID) . '" ' . (isset($field_value[$my_post->ID]) ? checked($field_value[$my_post->ID], $my_post->ID, false) : '') . ' class="option-tree-ui-checkbox ' . esc_attr($field_class) . '" />';
				echo '<label for="' . esc_attr($field_id) . '-' . esc_attr($my_post->ID) . '">' . $post_url . '</label>';
				echo '</p>';
			}
		} else {
			echo '<p>' . __('No Posts Found', 'option-tree') . '</p>';
		}

		echo '</div>';
		echo '</div>';
		echo "<script>
		var input = document.getElementById('search-post-list');
		var lis = document.getElementsByClassName('item-post');

		input.onkeyup = function () {
            var filter = input.value.toUpperCase();

            for (var i = 0; i < lis.length; i++) {
                var text = lis[i].getElementsByTagName('label')[0].innerHTML;
                if (text.toUpperCase().indexOf(filter) == 0) 
                    lis[i].style.display = 'block';
                else
                    lis[i].style.display = 'none';
            }
        }
		</script>";
	}
}

function handle_content()
{
	global $wp_query;
	$default = get_the_content();

	if ($wp_query->is_404 === false) {
		$getList = generate_navigation($default);

		$content = '<div class="explore_box"><strong class="explode_head">Table of Contents</strong><div class="explore_box_inner">';
		$content .= $getList . '</div></div>';
		$content .= '<div class="clear"></div><div class="cnt_box">';
		$content .= $default;
		$content .= '</div>';

		$content .= '
		<script>
		let listH2 = jQuery("h2");
		for(let i = 0; i < listH2.length; i++) {
			let result = listH2[i].textContent.replaceAll(" ", "_");
			listH2[i].setAttribute("id", result);
		}
		</script>';

		return $content;
	} else {
		return $default;
	}
}

add_action('the_content', 'handle_content');

function generate_navigation($HTML)
{
	$DOM = new DOMDocument();
	$DOM->loadHTML($HTML);

	$navigation = '<ol>';

	$h2IteratorStatus = 0;
	$h3IteratorStatus = 0;
	foreach ($DOM->getElementsByTagName('*') as $element) {
		if ($element->tagName == 'h2') {

			if ($h3IteratorStatus) {
				$navigation .= '</ul>';
				$h3IteratorStatus = 0;
			}

			if ($h2IteratorStatus) {
				$navigation .= '</li>';
				$h2IteratorStatus = 0;
			}

			$h2IteratorStatus = 1;
			if ($element->textContent != '') {
				$idElement = str_replace(' ', '_', $element->textContent);
				$navigation .= '<li><a onclick="scrollToc(`' . $idElement . '`)" href="#' . $idElement . '">' . $element->textContent . '</a>';
			}
		} else if ($element->tagName == 'h3') {

			if (!$h3IteratorStatus) {
				$navigation .= '<ul>';
				$h3IteratorStatus = 1;
			}

			if ($element->textContent != '') {
				$idElement = str_replace(' ', '_', $element->textContent);
				$navigation .= '<li><a onclick="scrollToc(`' . $idElement . '`)" href="#' . $idElement . '">' . $element->textContent . '</a></li>';
			}
		}
	}

	if ($h3IteratorStatus) {
		$navigation .= '</ul>';
	}

	if ($h2IteratorStatus) {
		$navigation .= '</li>';
	}

	return $navigation . `</ol>`;
}

add_action('template_redirect', function () {

	if ((defined('DOING_CRON') && DOING_CRON) || (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) || (defined('DOING_AJAX') && DOING_AJAX)) return;

	if (is_admin()) return;

	global $wp_query;
	if ($wp_query->is_404 === false) {
		$paths = explode('/', $_SERVER['REQUEST_URI']);
		foreach ($paths as $path) {
			if ($path == '404') {
				if (end($paths) == '') {
					status_header(200);
					$wp_query->is_404  = false;
					return;
				}
			}
		}
	} else {
		$paths = explode('/', $_SERVER['REQUEST_URI']);
		foreach ($paths as $path) {
			if ($path == '404') {
				if (end($paths) == '') {
					status_header(200);
					$wp_query->is_404  = false;
					return;
				}
			}
		}
	}
}, PHP_INT_MAX);

function ia_get_attachment_id_from_url($attachment_url = '')
{
	global $wpdb;
	$attachment_id = false;

	if ('' == $attachment_url)
		return;
	$upload_dir_paths = wp_upload_dir();
	if (false !== strpos($attachment_url, $upload_dir_paths['baseurl'])) {
		$attachment_url = preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url);
		$attachment_url = str_replace($upload_dir_paths['baseurl'] . '/', '', $attachment_url);
		$attachment_id = $wpdb->get_var($wpdb->prepare("SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url));
	}
	return $attachment_id;
}

function create_meta_boxes()
{
	add_meta_box('faq-meta-boxes', 'FAQ', 'faq_meta_boxes_callback', 'post');
}
add_action('add_meta_boxes', 'create_meta_boxes');

function faq_meta_boxes_callback($post)
{
	$get_post_meta = get_post_meta($post->ID, '_faq');
	wp_nonce_field('faq_meta_boxes_save', 'faq_meta_nonce');
	echo
	'<style type="text/css">
		#table-faq input {
			width: 600px;
		}
	</style>';
	echo
	'
	<button type="button" id="add-faq">Thêm câu hỏi</button>
	<table id="table-faq">
		<tr>
	  		<th>Question</th>
	  		<th>Answer</th>
		</tr>';
	if (empty($get_post_meta)) {
		echo
		'<tr>
			<td><input type="text" name="question[]" placeholder="Nhập câu hỏi" /></td>
			<td><input type="text" name="answer[]" placeholder="Nhập trả lời" /></td>
	  	</tr>';
	} else {
		$get_post_meta = json_decode($get_post_meta[0], true);
		foreach ($get_post_meta as $key => $value) {
			echo
			'<tr>
				<td><input type="text" name="question[]" placeholder="Nhập câu hỏi" value="' . $key . '"/></td>
				<td><input type="text" name="answer[]" placeholder="Nhập trả lời" value="' . $value . '"/></td>
	  		</tr>';
		}
	}

	echo '</table>';

	echo '
	<script type="text/javascript">
		jQuery("#add-faq").click(function(){
			jQuery("#table-faq").append(`<tr><td><input type="text" name="question[]" placeholder="Nhập câu hỏi" /></td><td><input type="text" name="answer[]" placeholder="Nhập trả lời" /></td></tr>`);
		});
	</script>';
}

function faq_meta_boxes_save($post_id)
{
	$_nonce = $_POST['faq_meta_nonce'];
	if (!isset($_nonce)) {
		return;
	}

	if (!wp_verify_nonce($_nonce, 'faq_meta_boxes_save')) {
		return;
	}

	$question = $_POST['question'];
	$answer = $_POST['answer'];
	$arrInput = [];


	foreach ($question as $key => $value) {
		$arrInput[$value] = $answer[$key];
	}

	$faq = json_encode($arrInput);
	update_post_meta($post_id, '_faq', $faq);
}
add_action('save_post', 'faq_meta_boxes_save');
