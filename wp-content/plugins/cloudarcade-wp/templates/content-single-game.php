<?php
defined('ABSPATH') || exit;

do_action('cloudarcade_before_single_game');
?>

<div class="cloudarcade">
	<div class="single-game">
		<?php 
		do_action('cloudarcade_before_game_iframe');
		$game_id = get_the_ID();
		$game_url = ca_get_game_url($game_id);
		$game_width = get_post_meta($game_id, 'game_width', true);
		$game_height = get_post_meta($game_id, 'game_height', true);
		?>
		<div class="game-iframe-container">
			<iframe class="game-iframe" src="<?php echo esc_url($game_url); ?>" width="<?php echo esc_attr($game_width); ?>" height="<?php echo esc_attr($game_height); ?>" scrolling="no" frameborder="0" allowfullscreen></iframe>
		</div>
		<?php do_action('cloudarcade_after_game_iframe'); ?>
		
		<?php do_action('cloudarcade_before_game_info'); ?>
		<div class="game-info">
			<div class="game-description">
				<h2>Description</h2>
				<?php echo get_the_content(); ?>
			</div>
			<div class="game-instructions">
				<h2>Instructions</h2>
				<?php echo get_post_meta($game_id, 'game_instructions', true); ?>
			</div>
		</div>
		<?php do_action('cloudarcade_after_game_info'); ?>
		
		<h2>Categories</h2>
		<?php
		$categories = get_the_terms($game_id, 'game_category');
		if ($categories):
		?>
			<div class="game-categories">
				<ul>
					<?php
					foreach ($categories as $category):
						$category_link = get_term_link($category);
						if (is_wp_error($category_link)) continue;
					?>
						<li><a href="<?php echo esc_url($category_link); ?>"><?php echo esc_html($category->name); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
		<h2>You may like</h2>
		<?php
		$random_games = ca_get_random_games();
		?>
		<div class="cloudarcade archive-game random-games">
	        <ul class="games">
	            <?php while ($random_games->have_posts()) : $random_games->the_post(); ?>
	                <?php include('content-game.php'); ?>
	            <?php endwhile; ?>
	        </ul>
	    </div>
	</div>
</div>

<?php do_action('cloudarcade_after_single_game'); ?>
