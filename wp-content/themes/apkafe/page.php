<?php
$custom_section = ot_get_option('custom_section');
$get_post = new WP_Query(array(
    'posts_per_page' => 24,
    'orderby'     => 'modified',
    'order'       => 'DESC',
));

get_header();
?>

<div class="container">
    <div class="main_bar">
        <div class="cnt_box pad10">
            <h1><?php echo ot_get_option('homepage_title_short_description') ?></h1>
            <p><?php echo ot_get_option('homepage_short_description') ?></p>
        </div>
        <div class="widget">
            <h2 class="widget_head">Latest Update</h2>
            <div id="main_list_item" class="main_list_item">
                <?php foreach ($get_post->posts as $post) { ?>
                    <a class="side_list_item" href="<?php echo get_permalink($post->ID) ?>">
                        <?php echo get_the_post_thumbnail($post->ID) ?>
                        <p class="title"><?php echo get_the_title($post->ID) ?></p>
                        <p class="category"><?php echo get_the_category($post->ID)[0]->name ?></p>
                    </a>
                <?php } ?>
            </div>
            <div class="clear mb10"></div>
        </div>
        <div class="clear mb10"></div>

        <?php if ($custom_section != '') { ?>
            <?php foreach ($custom_section as $section) { ?>
                <div class="widget">
                    <h2 class="widget_head"><?php echo $section['title'] ?></h2>
                    <div class="main_list_item">
                        <?php foreach ($section['post_select'] as $post_id) { ?>
                            <a class="side_list_item" href="<?php echo get_permalink($post_id) ?>">
                                <?php echo get_the_post_thumbnail($post_id) ?>
                                <p class="title"><?php echo get_the_title($post_id) ?></p>
                                <p class="category"><?php echo get_the_category($post_id)[0]->name ?></p>
                            </a>
                            <div class="clear mb10"></div>
                        <?php } ?>
                    </div>
                </div>
                <div class="clear mb10"></div>
            <?php } ?>
        <?php } ?>

        <div class="cnt_box pad10">
            <h2><strong><span class="s4"><?php echo ot_get_option('homepage_title_description') ?></span></strong></h2>
            <?php echo ot_get_option('homepage_description') ?>
        </div>
        <div class="clear"></div>
    </div>
    <?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>