<?php
get_header();
$getCategory = get_the_category(get_the_ID());
$slug = 'default';
foreach ($getCategory as $category) {
	if ($category->slug == 'top-list') {
		$slug = $category->slug;
	}
}

?>

<div class="container">
	<?php
	switch ($slug) {
		case 'top-list':
			get_template_part('templates/top-list', 'top-list');
			break;
		default:
			get_template_part('templates/default', 'default');
	} ?>
	<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>