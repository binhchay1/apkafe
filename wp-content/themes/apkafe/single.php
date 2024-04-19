<?php
get_header();
$getCategory = get_the_category(get_the_ID());
?>

<div class="container">
	<?php
	get_template_part('templates/default', 'default');
	get_sidebar(); ?>
</div>
<?php get_footer(); ?>