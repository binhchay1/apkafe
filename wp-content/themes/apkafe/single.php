<?php
get_header();
$getCategory = get_the_category(get_the_ID());
$slug = $getCategory[0]->slug;
?>

<div class="container">
	<?php
	switch ($slug) {
		case 'top-list':
			get_template_part('templates/top-list', 'top-list');
			break;
		case 'review':
			get_template_part('templates/review', 'review');
			break;
		default:
			get_template_part('templates/default', 'default');
	} ?>
	<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>