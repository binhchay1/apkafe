<?php

/**
 * Load core
 */
require_once 'inc/starter/apkafe-core.php';
require_once 'inc/option-tree-hook.php';
require_once 'inc/starter/functions-admin.php';
require_once 'inc/starter/category-image.php';

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
	wp_enqueue_style('style', get_stylesheet_directory_uri() . '/style.css');
	wp_enqueue_style('font-awesome', get_template_directory_uri() . '/css/fa/css/font-awesome.min.css');

	if (is_single()) {
		wp_enqueue_style('single', get_stylesheet_directory_uri() . '/css/single.css');
	}
}

add_action('wp_enqueue_scripts', 'apkafe_scripts_styles');
remove_action('shutdown', 'wp_ob_end_flush_all', 1);

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
	$host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'];
	if (is_front_page()) {
		$urlFontPage = $_SERVER['HTTP_REFERER'];

		echo '<link rel="alternate" href="' . $urlFontPage . '" hreflang="x-default" />';
	}

	if (in_array('product-category', $paths)) {
		$countProduct = 0;
		$urlCanonical = '';
		foreach ($paths as $path) {
			if ($path == '') {
				continue;
			}

			if ($path == 'page') {
				break;
			}

			if ($countProduct == 0) {
				$urlCanonical = $host . '/' . $path;
				$countProduct++;
			} else {
				$urlCanonical = $urlCanonical . '/' . $path;
			}
		}

		$urlProductCategory = $host . $_SERVER['REQUEST_URI'];
		echo '<link rel="alternate" href="' . $urlProductCategory . '" hreflang="x-default" />';
		echo '<link rel="canonical" href="' . $urlCanonical . '" />';
	}

	if (in_array('category', $paths)) {
		$count = 0;
		$urlCanonical = '';
		foreach ($paths as $path) {
			if ($path == '') {
				continue;
			}

			if ($path == 'page') {
				break;
			}

			if ($count == 0) {
				$urlCanonical = $host . '/' . $path;
			} else {
				$urlCanonical = $urlCanonical . '/' . $path;
			}
		}

		echo '<link rel="canonical" href="' . $urlCanonical . '" />';
	}
}, PHP_INT_MAX);

function add_custom_option_types_post_list($types)
{
	$types['post-list'] = 'Post list';

	return $types;
}
add_filter('ot_option_types_array', 'add_custom_option_types_post_list');

if (!function_exists('ot_type_post_list')) {

	function ot_type_post_list($args = array())
	{
		extract($args);
		$has_desc = $field_desc ? true : false;
		echo '<div class="format-setting type-post-checkbox type-checkbox ' . ($has_desc ? 'has-desc' : 'no-desc') . '">';
		if ($has_desc) {
			echo '<div class="description">' . wp_specialchars_decode($field_desc) . '</div>';
		}

		echo '<div class="format-setting-inner" id="' . $field_id . '">';
		echo '<input type="text" style="margin-bottom: 20px" aria-label="Search list" placeholder="Enter post title" id="search-post-list-' . esc_attr($field_id) . '">';
		echo '<p style="margin-bottom: 10px; font-weight: bold;">Total post selected: <span id="total-count-area"></span></p>';
		$my_posts = get_posts(apply_filters('ot_type_post_checkbox_query', array('posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC', 'post_status' => 'any', 'post_type' => array('post', 'product')), $field_id));
		if (is_array($my_posts) && !empty($my_posts)) {
			foreach ($my_posts as $my_post) {
				$post_url = get_permalink($my_post->ID);
				echo '<p class="item-post item-in-lis-' . esc_attr($field_id) . '">';
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
		var input_" . esc_attr($field_id) . " = document.getElementById('search-post-list-" . esc_attr($field_id) . "');
		var lis_" . esc_attr($field_id) . " = document.getElementsByClassName('item-in-lis-" . esc_attr($field_id) . "');
		var count = 0;
		if(jQuery('#" . $field_id . " input:checkbox:checked').length > 0) {
			count = jQuery('#" . $field_id . " input:checkbox:checked').length;
		}
		jQuery('#" . $field_id . " #total-count-area').html(count);

		input_" . esc_attr($field_id) . ".onkeyup = function () {
            var filter = input_" . esc_attr($field_id) . ".value.toUpperCase();
            for (var i = 0; i < lis_" . esc_attr($field_id) . ".length; i++) {
                var text = lis_" . esc_attr($field_id) . "[i].getElementsByTagName('label')[0].innerHTML;
                if (text.toUpperCase().indexOf(filter) == 0) 
				lis_" . esc_attr($field_id) . "[i].style.display = 'block';
                else
				lis_" . esc_attr($field_id) . "[i].style.display = 'none';
            }
        }
		</script>";
	}
}

function add_custom_option_types_post_list_section_customize($types)
{
	$types['post-list-section-customize'] = 'Post list section customize';

	return $types;
}
add_filter('ot_option_types_array', 'add_custom_option_types_post_list_section_customize');

if (!function_exists('ot_type_post_list_section_customize')) {

	function ot_type_post_list_section_customize($args = array())
	{
		extract($args);
		$has_desc = $field_desc ? true : false;
		echo '<div class="format-setting type-post-checkbox type-checkbox ' . ($has_desc ? 'has-desc' : 'no-desc') . '">';
		if ($has_desc) {
			echo '<div class="description">' . wp_specialchars_decode($field_desc) . '</div>';
		}

		$explode = explode(',', $field_value);

		echo '<div class="format-setting-inner" id=" ' . $field_id . '" style="margin-top: 20px">';
		echo '<input type="text" style="margin: 0" aria-label="Search list" placeholder="Enter post link" id="search-post-list-' . esc_attr($field_id) . '">';
		if ($field_value == '') {
			echo '<input type="hidden" name="' . esc_attr($field_name) . '" id="hidden-for-section-' . $field_id . '">';
		} else {
			echo '<input type="hidden" name="' . esc_attr($field_name) . '" id="hidden-for-section-' . $field_id . '" value="' . $field_value . '">';
		}
		echo '<a href="javascript:void(0)" id="click-to-add-list-' . esc_attr($field_id) . '" style="margin-left: 20px; text-decoration: none; padding: 10px 20px; border: 1px solid black" onclick="addToList' . $field_id . '()">Add post</a>';
		echo '<ul id="list-for-section-customize-' . $field_id . '" style="margin-top: 30px">';
		foreach ($explode as $ex) {
			if ($ex == '') {
				continue;
			}
			echo '<li style="margin-top: 10px;">' . $ex . '<span onclick="deleteToList' . $field_id . '(jQuery(this))" data-link="' . $ex . '" style="margin-left: 15px; font-weight: bold; font-size: 18px">x</span></li>';
		}
		echo '</ul>';
		echo '</div>';
		echo '</div>';
		echo '<script>';
		if ($field_value == '') {
			echo 'var listForAdd' . $field_id . ' = [];';
		} else {
			echo '
			let strExplode' . $field_id . ' = "' . $field_value . '";
			let split' . $field_id . ' = strExplode' . $field_id . '.split(",");
			var listForAdd' . $field_id . ' = split' . $field_id . ';
			';
		}

		echo 'jQuery(document).ready(function() {
			let length = jQuery("#list-for-section-customize-' . $field_id . ' li").length;
			if(length >= 6) {
				jQuery("#click-to-add-list-' . esc_attr($field_id) . '").hide();
			}
		});

		function addToList' . $field_id . '() {
			let link = jQuery("#search-post-list-' . esc_attr($field_id) . '").val();
			listForAdd' . $field_id . '.push(link);
			let strAppend = `<li style="margin-top: 10px;">` + link + `<span onclick="deleteToList' . $field_id . '(jQuery(this))" data-link="` + link + `" style="margin-left: 15px; font-weight: bold; font-size: 18px">x</span></li>`;
			jQuery("#list-for-section-customize-' . $field_id . '").append(strAppend);
			let length = jQuery("#list-for-section-customize-' . $field_id . ' li").length;
			if(length >= 6) {
				jQuery("#click-to-add-list-' . esc_attr($field_id) . '").hide();
			}

			let toString = listForAdd' . $field_id . '.toString();
			jQuery("#hidden-for-section-' . $field_id . '").val(toString);
		}

		function deleteToList' . $field_id . '(button) {
			let parent = button.parent();
			let link = button.attr("data-link");
			let index = listForAdd' . $field_id . '.indexOf(link);
			
			if (index !== -1) {
				listForAdd' . $field_id . '.splice(index, 1);
			}

			parent.remove();
			let length = jQuery("#list-for-section-customize-' . $field_id . ' li").length;
			if(length < 6) {
				jQuery("#click-to-add-list-' . esc_attr($field_id) . '").show();
			}

			let toString = listForAdd' . $field_id . '.toString();
			jQuery("#hidden-for-section-' . $field_id . '").val(toString);
		}
		</script>';
	}
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

add_filter('get_search_form', function ($form) {
	$form = '<form role="search" method="post" id="searchform" class="searchform" action="' . home_url('/search/') . '" >
	  <div class="custom-form"><label class="screen-reader-text" for="s">' . __('Search:') . '</label>
	  <input type="text" value="' . get_search_query() . '" name="s" id="s" />
	  <input type="submit" id="searchsubmit" value="' . esc_attr__('Search') . '" />
	</div>
	</form>';

	return $form;
}, 40);

function create_meta_boxes_faq()
{
	add_meta_box('faq-meta-boxes', 'FAQ', 'faq_meta_boxes_callback', 'post');
}
add_action('add_meta_boxes', 'create_meta_boxes_faq');

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
	if (isset($_POST['faq_meta_nonce'])) {
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
}
add_action('save_post', 'faq_meta_boxes_save');
