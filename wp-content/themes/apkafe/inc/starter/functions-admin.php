<?php
/*load admin js & css*/
function leafcolor_admin_styles()
{
	wp_enqueue_style('style', get_template_directory_uri() . '/admin/style.css');
}
if (is_admin()) {
	add_action('admin_print_styles', 'leafcolor_admin_styles');
	add_filter('manage_edit-post_columns', 'ia_add_posts_columns');
	add_filter('manage_edit-page_columns', 'ia_add_pages_columns');
	add_filter('manage_edit-category_columns', 'ia_add_pages_columns');

	function ia_add_posts_columns($columns)
	{
		$cols = array_merge(array('id' => __('ID', 'leafcolor')), $columns);
		$cols = array_merge($cols, array('thumbnail' => __('Thumbnail', 'leafcolor')));
		return $cols;
	}

	function ia_add_pages_columns($columns)
	{
		$cols = array_merge(array('id' => __('ID', 'leafcolor')), $columns);

		return $cols;
	}
	add_action('manage_pages_custom_column', 'ia_set_posts_columns_value', 10, 2);
	add_action('manage_posts_custom_column', 'ia_set_posts_columns_value', 10, 2);
	add_filter('manage_category_custom_column', 'ia_set_cats_columns_value', 10, 3);
	function ia_set_posts_columns_value($column, $post_id)
	{
		if ($column == 'id') {
			echo esc_attr($post_id);
		} else if ($column == 'thumbnail') {
			echo esc_url(get_the_post_thumbnail($post_id, 'thumbnail'));
		} else if ($column == 'startdate') {
			// for event
			$date_str = get_post_meta($post_id, 'start_day', true);
			if ($date_str != '') {
				$date = date_create_from_format('m/d/Y H:i', $date_str);
				echo esc_attr($date->format(get_option('date_format')));
			}
		}
	}

	function ia_set_cats_columns_value($value, $name, $cat_id)
	{
		if ('id' == $name)
			echo esc_attr($cat_id);
	}

	function ia_image_custom_sizes($sizes)
	{
		global $_wp_additional_image_sizes;
		foreach ($_wp_additional_image_sizes as $key => $value) {
			$custom[$key] = ucwords(str_replace('-', ' ', $key));
		}

		return array_merge($sizes, $custom);
	}
	add_filter('image_size_names_choose', 'ia_image_custom_sizes');

	function ia_addUploadMimes($mimes)
	{
		$mimes = array_merge($mimes, array(
			'eot' => 'application/octet-stream',
			'svg' => 'image/svg+xml',
			'ttf' => 'application/octet-stream',
			'otf' => 'application/octet-stream',
			'woff' => 'application/octet-stream',
		));
		return $mimes;
	}
	add_filter('upload_mimes', 'ia_addUploadMimes');
	add_action('admin_head', 'custom_admin_styling');
	function custom_admin_styling()
	{
		echo '<style type="text/css">th#id{width:40px;}</style>';
	}
}

add_action('login_enqueue_scripts', 'ia_login_logo');
function ia_login_logo()
{
	if ($img = ot_get_option('login_logo')) {
?>
		<style type="text/css">
			body.login div#login h1 a {
				background-image: url(<?php echo esc_url($img) ?>);
				width: 320px;
				height: 120px;
				background-size: auto;
				background-position: center;
			}
		</style>
<?php }
}
