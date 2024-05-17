<?php
get_header();
$category_id = get_query_var('cat');
$args = array('cat' => $category_id, 'orderby' => 'modified', 'order' => 'DESC', 'posts_per_page' => 24, 'post_status' => 'publish');
$argsLastUpdate = array('cat' => $category_id, 'orderby' => 'modified', 'order' => 'DESC', 'posts_per_page' => 1, 'post_status' => 'publish');
$get_post = new WP_Query($args);
$get_post_last_update = new WP_Query($argsLastUpdate);
?>

<div class="container">
    <div class="main_bar">
        <div class="widget">
            <div class="widget_head">
                <ul id="breadcrumbs" class="bread_crumb">
                    <li><a href="<?php echo home_url() ?>">Home</a></li>
                    <li class="breadcrumb-archive"> » </li>
                    <li><a class="active" href="<?php echo get_category_link($category_id) ?>"><?php echo get_cat_name($category_id) ?></a></li>
                </ul>
                <div class="clear"></div>
            </div>
            <div id="main_list_item" class="main_list_item">
                <?php foreach ($get_post->posts as $post) { ?>
                    <a class="side_list_item" href="<?php echo get_permalink($post->ID) ?>">
                        <img class="item_icon lazyloaded" width="80" height="80" src="<?php echo get_the_post_thumbnail($post->ID) ?>" alt="<?php echo get_the_title($post->ID) ?>">
                        <p class="title"><?php echo get_the_title($post->ID) ?></p>
                        <p class="category"><?php echo get_the_category($post->ID)[0]->name ?></p>
                    </a>
                <?php } ?>
            </div>
            <div class="clear mb20"></div>
        </div>
        <div class="clear mb15"></div>
    </div>
    <div class="side_bar">
        <div class="widget">
            <h2 class="widget_head">Latest Update</h2>
            <div class="list_item_wrap">
                <?php foreach ($get_post_last_update->posts as $post) { ?>
                    <a class="side_list_item" href="<?php echo get_permalink($post->ID) ?>">
                        <img class="item_icon lazyloaded" width="80" height="80" src="<?php echo get_the_post_thumbnail($post->ID) ?>" alt="<?php echo get_the_title($post->ID) ?>">
                        <p class="title"><?php echo get_the_title($post->ID) ?></p>
                        <p class="category"><?php echo get_the_category($post->ID)[0]->name ?></p>
                    </a>
                <?php } ?>
            </div>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
        <?php get_sidebar(); ?>
    </div>
    <div class="clear"></div>
</div>

<?php get_footer(); ?>