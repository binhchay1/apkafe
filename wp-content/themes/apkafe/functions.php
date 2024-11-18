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
	wp_enqueue_script('owl-carousel-js', get_template_directory_uri() . '/js/owl.carousel.min.js', [], '2.3.4');

	/*
	 * Loads css
	 */
	wp_enqueue_style('style', get_stylesheet_directory_uri() . '/style.css');
	wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css');
	wp_enqueue_style('owl-carousel', get_template_directory_uri() . '/css/owl.carousel.min.css', [], '2.3.4');
	wp_enqueue_style('owl-carousel-default', get_template_directory_uri() . '/css/owl.theme.default.min.css', [], '2.3.4');

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
	wp_enqueue_style('style-in', get_template_directory_uri() . '/admin/style.css', [], '6.5.5');
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
			$post = $wp_query->post;
		}
		if (isset($post->ID)) {
			$meta = get_post_meta($post->ID, $options, true);
		}
		return $meta != '' ? $meta : ot_get_option($options, $default);
	}

	return ot_get_option($options, $default);
}

add_filter('xmlrpc_enabled', '__return_false');
add_action('init', function ($search) {
	add_rewrite_rule('search/?$', 'index.php?s=' . $search, 'top');
});

add_filter('wp_schema_pro_role', 'add_role_schema_pro');
function add_role_schema_pro($roles)
{
	$new_roles = array('wpseo_editor', 'editor');
	$roles = array_merge($roles, $new_roles);
	return $roles;
}

function design__wpseo_canonical($url)
{
	$paths = explode('/', $_SERVER['REQUEST_URI']);
	$host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'];

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
				$urlCanonical = $host . '/' . $path . '/';
				$count++;
			} else {
				$urlCanonical .= $path . '/';
			}
		}

		return $urlCanonical;
	} else {
		return $url;
	}
}
add_filter('wpseo_canonical', 'design__wpseo_canonical');

function design__wpseo_next($url)
{
	$paths = explode('/', $_SERVER['REQUEST_URI']);
	$host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'];

	if (in_array('product-category', $paths)) {
		$count = 0;
		$urlNext = '';
		$urlPrev = '';
		$isPagination = false;

		foreach ($paths as $path) {
			if ($path == '') {
				continue;
			}

			if ($count == 0) {
				$urlNext = $host . '/' . $path . '/';
				$urlPrev = $host . '/' . $path . '/';
				$count++;
			} else {
				if (strpos($path, 'news_page') || strpos($path, 'popular_page') || strpos($path, 'hot_page')) {
					$isPagination = true;
					$explode = explode('=', $path);
					$currentPage = $explode[1];
					$nextPage = (int) $currentPage + 1;
					if ((int) $currentPage > 2) {
						$prevPage = (int) $currentPage - 1;
						$urlPrev .= $explode[0] . '=' . $prevPage . '/';
					}

					$urlNext .= $explode[0] . '=' . $nextPage . '/';
				} else {
					$urlNext .= $path . '/';
					$urlPrev .= $path . '/';
				}
			}
		}

		$link = '<link rel="next" href="' . $urlNext . '" />' . PHP_EOL;

		if ($isPagination) {
			$link .= '<link rel="prev" href="' . $urlPrev . '" />' . PHP_EOL;
		}

		return $link;
	} else {
		return $url;
	}
}
add_filter('wpseo_next_rel_link', 'design__wpseo_next');

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

function add_custom_option_types_youtube_embed($types)
{
	$types['youtube-embed'] = 'Youtube embed';

	return $types;
}
add_filter('ot_option_types_array', 'add_custom_option_types_youtube_embed');

if (!function_exists('ot_type_youtube_embed')) {

	function ot_type_youtube_embed($args = array())
	{
		extract($args);
		$has_desc = $field_desc ? true : false;
		echo '<div class="format-setting type-post-checkbox type-checkbox ' . ($has_desc ? 'has-desc' : 'no-desc') . '">';
		if ($has_desc) {
			echo '<div class="description">' . wp_specialchars_decode($field_desc) . '</div>';
		}

		$explode = explode(',', $field_value);
		$link = '';
		$checked = 0;
		if (!empty($explode)) {
			$link = $explode[0];
			$checked = $explode[1];
		}

		echo '<div class="format-setting-inner" id="' . $field_id . '">';
		echo '<span><input value="' . $link . '" type="text" style="width: 50%" id="link-youtube-embed-' . $field_id . '" placeholder="Enter link youtube" onchange="handleChangeLinkYoutubeEmbed()"></span>';
		echo '<span style="display: flex; justify-content: center; align-items: center;">
		<input type="checkbox" id="check-box-youtube-embed-' . $field_id . '" onchange="handleChangeCheckboxYoutubeEmbed()" ' . ($checked == 1 ? 'checked' : '') . '>
		<input type="hidden" id="hidden-input-youtube-embed" name="' . esc_attr($field_name) . '">
		<p>Auto play</p>
		</span>';
		echo '</div></div>';

		echo '<script>

		function handleChangeLinkYoutubeEmbed() {
			let data_youtube_embed_hidden = "";
			let checked_youtube_embed = 0;
			let link_youtube_embed = jQuery("#link-youtube-embed-' . $field_id . '").val();
			if(jQuery("#check-box-youtube-embed-' . $field_id . '").is(":checked")) {
				checked_youtube_embed = 1;
			} else {
				checked_youtube_embed = 0;
			}

			data_youtube_embed_hidden = link_youtube_embed + "," + checked_youtube_embed;
			jQuery("#hidden-input-youtube-embed").val(data_youtube_embed_hidden);
		}

		function handleChangeCheckboxYoutubeEmbed() {
			let data_youtube_embed_hidden = "";
			let checked_youtube_embed = 0;
			let link_youtube_embed = jQuery("#link-youtube-embed-' . $field_id . '").val();
			if(jQuery("#check-box-youtube-embed-' . $field_id . '").is(":checked")) {
				checked_youtube_embed = 1;
			} else {
				checked_youtube_embed = 0;
			}

			data_youtube_embed_hidden = link_youtube_embed + "," + checked_youtube_embed;
			jQuery("#hidden-input-youtube-embed").val(data_youtube_embed_hidden);
		}
		
		</script>';
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
					get_template_part(404);
					exit();
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

function submit_review_handler()
{
	global $wpdb;
	$score = $_POST['score'];
	$user_name = $_POST['user_name'];
	$user_comment = trim($_POST['user_comment']);
	$post_id = $_POST['post_id'];
	$listCharacterBlackList = [
		'fuck',
		'shit',
		'bitch',
		'ass',
		'bastard',
		'damn',
		'hell',
		'whore',
		'dick',
		'pussy',
		'asshole',
		'cocksucker',
		'motherfucker',
		'fag',
		'cunt',
		'slut',
		'cock',
		'tits',
		'wanker',
		'crap',
		'bollocks',
		'prick',
		'dyke',
		'twat',
		'piss',
		'douche',
		'jerk',
		'screw',
		'slag',
		'turd',
		'son of a bitch',
		'goddamn',
		'gambling',
		'bet',
		'betting',
		'casino',
		'wager',
		'poker',
		'blackjack',
		'roulette',
		'slots',
		'bookie',
		'sportsbook',
		'odds',
		'jackpot',
		'bingo',
		'lottery',
		'lotto',
		'scam',
		'fraud',
		'con',
		'phishing',
		'swindle',
		'trick',
		'hoax',
		'deceive',
		'deceptive',
		'cheat',
		'cheating',
		'rip-off',
		'sham',
		'bogus',
		'counterfeit',
		'ponzi scheme',
		'pyramid scheme',
		'fake',
		'scammer',
		'fraudster',
	];

	foreach ($listCharacterBlackList as $character) {
		$checkCharacter = '/\b' . $character . '\b/i';
		if (preg_match($checkCharacter, $user_comment) == 1) {
			echo json_encode(array('success' => true, 'result' => 3, 'character' => $character));

			wp_die();
		}
	}

	$result = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT * FROM wp_user_review WHERE user_name = '%s' AND post_id = '%d'",
			$user_name,
			$post_id
		)
	);

	if ($result == '') {
		$resultComment = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT * FROM wp_user_review WHERE user_comment = '%s' AND post_id = '%d'",
				$user_comment,
				$post_id
			)
		);

		if ($resultComment != null) {
			echo json_encode(array('success' => true, 'result' => 4));
			wp_die();
		}

		$wpdb->insert('wp_user_review', array(
			'score' => $score,
			'user_name' => $user_name,
			'user_comment' => $user_comment,
			'post_id' => $post_id,
			'created_at' => date('Y-m-d H:i:s'),
		));

		$avg = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT AVG(score) FROM wp_user_review WHERE post_id = '%d'",
				$post_id
			)
		);

		$countRatingForSchema = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM wp_user_review WHERE post_id = '%d'",
				$post_id
			)
		);

		$ratingForSchema = number_format($avg, 1);
		if (metadata_exists('post', $post_id, 'software-application-4423-rating')) {
			update_post_meta($post_id, 'software-application-4423-rating', $ratingForSchema);
		} else {
			add_post_meta($post_id, 'software-application-4423-rating', $ratingForSchema);
		}

		if (metadata_exists('post', $post_id, 'software-application-4423-review-count')) {
			update_post_meta($post_id, 'software-application-4423-review-count', $countRatingForSchema);
		} else {
			add_post_meta($post_id, 'software-application-4423-review-count', $countRatingForSchema);
		}

		if (metadata_exists('post', $post_id, 'count-review')) {
			update_post_meta($post_id, 'count-review', $countRatingForSchema);
		} else {
			add_post_meta($post_id, 'count-review', $countRatingForSchema);
		}

		echo json_encode(array('success' => true, 'result' => 1));
	} else {
		echo json_encode(array('success' => true, 'result' => 0));
	}

	wp_die();
}

add_action('wp_ajax_submit_review_handler', 'submit_review_handler');
add_action('wp_ajax_nopriv_submit_review_handler', 'submit_review_handler');

function filter_review_handler()
{
	global $wpdb;
	$option_star = $_POST['option_star'];
	$option_newsest = $_POST['option_newsest'];
	$post_id = $_POST['post_id'];
	$string_query = "SELECT * FROM wp_user_review WHERE post_id = '%d'";

	if ($option_star == '0') {
		$string_query = $string_query . " AND score = '0'";
	}

	if ($option_star == '1') {
		$string_query = $string_query . " AND score = '1'";
	}

	if ($option_star == '2') {
		$string_query = $string_query . " AND score = '2'";
	}

	if ($option_star == '3') {
		$string_query = $string_query . " AND score = '3'";
	}

	if ($option_star == '4') {
		$string_query = $string_query . " AND score = '4'";
	}

	if ($option_star == '5') {
		$string_query = $string_query . " AND score = '5'";
	}

	if ($option_newsest == 'newest') {
		$string_query = $string_query . " ORDER BY created_at";
	}

	if ($option_newsest == 'rating') {
		$string_query = $string_query . " ORDER BY score";
	}

	$result = $wpdb->get_results(
		$wpdb->prepare(
			$string_query,
			$post_id
		)
	);

	echo json_encode(array('success' => true, 'result' => $result));

	wp_die();
}

add_action('wp_ajax_filter_review_handler', 'filter_review_handler');
add_action('wp_ajax_nopriv_filter_review_handler', 'filter_review_handler');

function wpb_author_info_box($content)
{
	global $post;

	if (is_single() && isset($post->post_author)) {
		$display_name = get_the_author_meta('display_name', $post->post_author);
		if (empty($display_name)) {
			$display_name = get_the_author_meta('nickname', $post->post_author);
		}
		$user_description = get_the_author_meta('user_description', $post->post_author);
		$user_url = get_the_author_meta('url', $post->post_author);
		$user_meta = get_user_meta($post->post_author);

		$author_details = '<div class="area-infor-author">';
		if (!empty($user_description)) {
			$author_details .= '<div class="author_avatar">' . get_avatar(get_the_author_meta('user_email'), 90) . '</div>';
		}

		if (!empty($display_name)) {
			$author_details .= '<div class="author_name"><p>' . $display_name . '</p><div class="icon-social-author">';
		}

		if (!empty($display_name)) {
			$author_details .= '<a href="' . $user_url . '"><i class="fa fa-address-card"></i></a>';
		}

		if (isset($user_meta['facebook'])) {
			$author_details .= '<a href="' . $user_meta['facebook'][0] . '"><i class="fa fa-facebook"></i></a>';
		}

		if (isset($user_meta['instagram'])) {
			$author_details .= '<a href="' . $user_meta['instagram'][0] . '"><i class="fa fa-instagram"></i></a>';
		}

		if (isset($user_meta['linkedin'])) {
			$author_details .= '<a href="' . $user_meta['linkedin'][0] . '"><i class="fa fa-linkedin"></i></a>';
		}

		if (isset($user_meta['pinterest'])) {
			$author_details .= '<a href="' . $user_meta['pinterest'][0] . '"><i class="fa fa-pinterest"></i></a>';
		}

		$author_details .= '</div></div></div><div class="author_bio">' . nl2br($user_description) . '</div>';
		$content = $content . '<div class="author_bio_section" >' . $author_details . '</div>';
	}
	return $content;
}

add_action('the_content', 'wpb_author_info_box');
remove_filter('pre_user_description', 'wp_filter_kses');

add_action('added_post_meta', 'sync_on_product_with_schema', 10, 4);
add_action('updated_post_meta', 'sync_on_product_with_schema', 10, 4);
function sync_on_product_with_schema($meta_id, $post_id, $meta_key, $meta_value)
{
	if ($meta_key == '_edit_lock') {
		if (get_post_type($post_id) == 'product') {
			$product_featured_image_id = get_post_meta($post_id, '_thumbnail_id', true);
			update_post_meta($post_id, 'software-application-4423-image', $product_featured_image_id);
		}
	}
}

add_action('admin_init', 'display_review');

function display_review()
{
	$current_post_type = get_current_post_type();

	if ($current_post_type == 'product' or $current_post_type == 'post') {
		add_filter('manage_' . $current_post_type . '_posts_columns', 'column_heading', 10, 1);
		add_action('manage_' . $current_post_type . '_posts_custom_column', 'column_content', 10, 2);
		add_filter('manage_edit-' . $current_post_type . '_sortable_columns', 'column_sort', 10, 2);
		add_action('pre_get_posts', 'my_sort_custom_column_query');
	}
}

function get_current_post_type()
{
	if (isset($_GET['post_type']) && is_string($_GET['post_type'])) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reason: We are not processing form information.
		$post_type = sanitize_text_field(wp_unslash($_GET['post_type']));
		if (! empty($post_type)) {
			return $post_type;
		}
	}
	return 'post';

	return null;
}

function column_heading($columns)
{
	$added_columns = [];

	$added_columns['count-review'] = __('Review', 'count-review');

	return array_merge($columns, $added_columns);
}

function column_content($column_name, $post_id)
{
	switch ($column_name) {
		case 'count-review':
			echo parse_column_score($post_id);

			return;
	}
}

function column_sort($columns)
{
	$columns['count-review'] = 'count-review';

	return $columns;
}

function parse_column_score($post_id)
{
	global $wpdb;

	$table = $wpdb->prefix . 'user_review';
	$result = $wpdb->get_results(
		$wpdb->prepare("SELECT * FROM $table WHERE post_id = %d", $post_id)
	);

	$count = count($result);

	return $count;
}

function my_sort_custom_column_query($query)
{
	$orderby = $query->get('orderby');

	if ('count-review' == $orderby) {
		$query->set('meta_key', 'count-review');
		$query->set('orderby', 'meta_value_num');
	}
}
