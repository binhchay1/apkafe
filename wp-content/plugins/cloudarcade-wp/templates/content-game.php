<?php
/**
 * Single game block output for archives
 */
?>
<li id="post-<?php the_ID(); ?>" <?php post_class('game-item-wrapper'); ?>>
    <div class="game-item-inner">
        <a href="<?php the_permalink(); ?>" class="archive-game-link">
            <div class="game-thumbnail-wrapper">
                <img src="<?php echo esc_url(ca_get_thumbnail(get_the_ID())); ?>" alt="<?php the_title_attribute(); ?>">
            </div>
            <div class="game-title-wrapper">
                <h2 class="loop-game-title"><?php the_title(); ?></h2>
                <div class="loop-game-category">
                    <?php echo get_game_categories_str(get_the_ID()); ?>
                </div>
            </div>
        </a>
    </div>
</li>