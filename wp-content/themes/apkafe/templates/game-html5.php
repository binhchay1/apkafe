<?php
$getMeta = get_post_meta(get_the_ID());
$category = get_the_category(get_the_ID());
$related = get_posts(array('category__in' => wp_get_post_categories($post->ID), 'numberposts' => 6, 'post__not_in' => array($post->ID)));

$sapo = [];
$h1_sapo = [];

$sapo = substr(get_post_meta($post->ID, '_yoast_wpseo_metadesc', true), 0, 100);

if (array_key_exists('h1_sapo', $getMeta)) {
    $h1_sapo = $getMeta['h1_sapo'];
}

?>

<style>
    .lang_box a {
        display: flex;
        align-items: center;
    }

    .side_cat_item {
        display: flex;
    }
</style>
<div class="main_bar">
    <div id="article" class="widget">
        <div class="widget_head">
            <ul id="breadcrumbs" class="bread_crumb">
                <li><a href="<?php echo home_url() ?>">Home</a></li>
                <li class="btn-fa-angle"> » </li>
                <li><a class="active" href="<?php echo get_permalink(get_the_ID()) ?>"><?php echo the_title() ?></a></li>
            </ul>
            <div class="clear"></div>
        </div>
        <div class="pad10">
            <?php if (!empty($h1_sapo[0])) { ?>
                <h1 class="main_head ac"><?php echo $h1_sapo[0] ?></h1>
            <?php } else { ?>
                <h1 class="the_title_post"><?php echo the_title() ?></h1>
            <?php } ?>

            <?php if (!empty($sapo[0])) { ?>
                <div class="sapo-review-default">
                    <p><?php echo $sapo ?></p>
                </div>
            <?php } else {
                preg_match('/^(.*)\s/', get_the_content(), $matches);

                $sapo_def = $matches[0];
            ?>
                <div class="sapo-review-default">
                    <p><?php echo $sapo_def ?></p>
                </div>
            <?php } ?>

            <div class="post-author-and-date">
                <p class="the_title_author">by <?php the_author_posts_link(); ?></p>
                <p class="the_title_modified_date">Updated on <?php echo get_the_modified_date(); ?></p>
                <p class="the_title_published_date">Published on <?php echo get_the_date(); ?></p>
            </div>

            <div class="clear mb20"></div>

            <iframe src="<?php echo get_post_meta(get_the_ID(), 'link', true) ?>" width="100%" height="100%" frameborder="0" style="height: 477px; z-index: 99999; "></iframe>

            <div class="clear mb20"></div>

            <div class="social_sharer">
                <a id="share_facebook" onclick="share_this('share_facebook')" class="facebook" data-url="<?php echo get_permalink(get_the_ID()) ?>" data-title="Line Apk 13.21.0 Download For Android Latest Version" href="javascript:void(0)"><i class="fa fa-facebook"></i> <span>Facebook</span></a>
                <a id="share_twitter" onclick="share_this('share_twitter')" class="twitter" data-url="<?php echo get_permalink(get_the_ID()) ?>" data-title="Line Apk 13.21.0 Download For Android Latest Version" href="javascript:void(0)"><i class="fa fa-twitter"></i><span>Twitter</span></a>
                <a id="share_reddit" onclick="share_this('share_reddit')" class="reddit" data-url="<?php echo get_permalink(get_the_ID()) ?>" data-title="Line Apk 13.21.0 Download For Android Latest Version" href="javascript:void(0)"><i class="fa fa-reddit"></i><span>Reddit</span></a>
                <a id="share_pinterest" onclick="share_this('share_pinterest')" class="pinterest" data-url="<?php echo get_permalink(get_the_ID()) ?>" data-title="Line Apk 13.21.0 Download For Android Latest Version" href="javascript:void(0)"><i class="fa fa-pinterest"></i><span>Pinterest</span></a>
            </div>

            <div class="clear mb20"></div>

            <?php get_template_part('templates/rating', 'rating'); ?>
        </div>
    </div>

    <div class="clear mb20"></div>
    <div class="widget">
        <h2 class="widget_head">Recommended for you</h2>
        <div class="main_list_item">
            <?php foreach ($related as $post) { ?>
                <a class="side_list_item" href="<?php echo get_permalink($post->ID) ?>">
                    <?php echo get_the_post_thumbnail($post->ID) ?>
                    <p class="title"><?php echo get_the_title($post->ID) ?></p>
                    <p class="category"><?php echo get_the_category($post->ID)[0]->name ?></p>
                </a>
            <?php } ?>
        </div>
    </div>
    <div class="clear"></div>

</div>