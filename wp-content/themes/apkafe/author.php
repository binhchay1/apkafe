<?php
get_header();

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$args = array(
    'author__in' => array(get_the_author_meta('ID')),
    'orderby' => 'modified',
    'order' => 'DESC',
    'posts_per_page' => 16,
    'post_status' => 'publish',
    'paged' => $paged,
    'post_type' => 'post'
);

$get_post = new WP_Query($args);
$total_page_news = $get_post->max_num_pages;
$user_description = get_the_author_meta('user_description', $post->post_author);
$user_url = get_the_author_meta('url', $post->post_author);
$user_meta = get_user_meta($post->post_author);
?>
<style>
    .main_list_item .side_list_item {
        width: 49%;
    }

    .main_list_item .side_list_item img {
        width: 160px;
        height: 90px;
    }

    .side_list_item p.title {
        text-overflow: inherit;
        white-space: inherit;
    }
</style>

<div class="container">
    <div class="content-pad-4x">
        <div id="content">
            <div class="info-area-author-profile">
                <div class="avatar-author-in-profile">
                    <?php echo get_avatar(get_the_author_meta('user_email'), 200) ?>
                </div>
                <div class="right-side-author-in-profile">
                    <h1><?php echo get_the_author() ?></h1>
                    <div class="social-author-in-profile">
                        <a href="' . $user_url . '"><i class="fa fa-address-card"></i></a>
                        <?php if (isset($user_meta['facebook'])) { ?>
                            <a href="<?php echo $user_meta['facebook'][0] ?>"><i class="fa fa-facebook"></i></a>
                        <?php } ?>

                        <?php if (isset($user_meta['instagram'])) { ?>
                            <a href="<?php echo $user_meta['instagram'][0] ?>"><i class="fa fa-instagram"></i></a>
                        <?php } ?>

                        <?php if (isset($user_meta['linkedin'])) { ?>
                            <a href="<?php echo $user_meta['linkedin'][0] ?>"><i class="fa fa-linkedin"></i></a>
                        <?php } ?>

                        <?php if (isset($user_meta['pinterest'])) { ?>
                            <a href="<?php echo $user_meta['pinterest'][0] ?>"><i class="fa fa-pinterest"></i></a>
                        <?php } ?>

                    </div>
                    <div class="bio-author-in-profile">
                        <?php echo nl2br($user_description) ?>
                    </div>
                </div>
            </div>
            <div id="news" class="post-of-author">
                <div class="main_list_item">
                    <?php if ($get_post->have_posts()) { ?>
                        <?php foreach ($get_post->posts as $post) { ?>
                            <a class="side_list_item" href="<?php echo get_permalink($post->ID) ?>">
                                <?php echo get_the_post_thumbnail($post->ID) ?>
                                <p class="title"><?php echo get_the_title($post->ID) ?></p>
                                <p class="date"><?php echo get_the_date('F j, Y', $post->ID) ?></p>
                            </a>
                        <?php } ?>
                    <?php } ?>
                </div>

                <div class="d-flex justify-center margin-top-15">
                    <?php echo paginate_links(array(
                        'base' => get_pagenum_link(1) . '%_%',
                        'format' => '/page/%#%',
                        'current' => $paged,
                        'total' => $total_page_news,
                        'prev_text' => __('←'),
                        'next_text' => __('→'),
                        'type' => 'list',
                    )); ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    do_action('woocommerce_sidebar');
    ?>
</div>

<?php get_footer(); ?>