<?php
/**
 * The template for displaying the game archive page.
 */

$orderby_args = Cloudarcade_Wp_Tpl_Manipulations::get_ordering_attribute();

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Query for your 'game' custom post type
$args = array(
    'post_type' => 'game',
    'posts_per_page' => cloudarcade_get_setting('games_per_page'), // Number of games to show per page
    'paged' => $paged,
) + $orderby_args;

$game_query = new WP_Query($args);

?>

<?php if ($game_query->have_posts()) :
    do_action( 'cloudarcade_wp_before_game_loop' );
    ?>
    <header class="page-header">
        <h1 class="page-title cloudarcade-page-title"><?php post_type_archive_title(); ?></h1>
    </header>

    <?php

    $total = $game_query->found_posts;

    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $posts_per_page = cloudarcade_get_setting('games_per_page');
    $from = $posts_per_page * ($paged - 1) + 1;
    $to = ($total > $paged * $posts_per_page) ? $paged * $posts_per_page : $total;

    echo '<p class="cloudarcade-result-count">Showing ' . $from . '–' . $to . ' of ' . $total . ' results</p>';

    ?>

    <?php echo Cloudarcade_Wp_Tpl_Manipulations::get_archive_ordering(); ?>

    <div class="cloudarcade archive-game">
        <ul class="games">
            <?php while ($game_query->have_posts()) : $game_query->the_post(); ?>
                <?php include('content-game.php'); ?>
            <?php endwhile; ?>
        </ul>
    </div>

    <?php
    do_action( 'cloudarcade_wp_after_game_loop' );

    ?>
     <?php echo Cloudarcade_Wp_Tpl_Manipulations::get_archive_pagination( $game_query ); ?>

<?php else : ?>
    <p><?php esc_html_e('No games found.', 'text-domain'); ?></p>
<?php endif; ?>

<?php wp_reset_postdata(); // Always reset postdata after a custom query ?>