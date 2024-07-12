<?php
get_header();
$getCategory = get_the_category(get_the_ID());
$getPostType = get_post_type(get_the_ID());
?>

<?php if ($getPostType == 'games-html5') { ?>
	<div class="container">
		<?php
		get_template_part('templates/game-html5', 'game-html5');
		get_sidebar(); ?>
	</div>
<?php } else { ?>
	<div class="container">
		<?php
		get_template_part('templates/default', 'default');
		get_sidebar(); ?>
	</div>
<?php } ?>

<?php get_footer(); ?>