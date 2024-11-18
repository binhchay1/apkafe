<?php
$category = get_the_terms($post->ID, 'product_cat');
$getMeta = get_post_meta($post->ID);
$related = get_posts(array(
    'numberposts' => 6,
    'post__not_in' => array($post->ID),
    'post_type' => 'product',
    'tax_query' => array(
        array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id',
            'terms' => array($category[0]->term_id)
        )
    )
));

$size = [];
$version = [];
$mod_infor = [];
$faq = [];
$sapo = [];
$h1_sapo = [];

if (array_key_exists('size', $getMeta)) {
    $size = $getMeta['size'];
}

if (array_key_exists('latest_version', $getMeta)) {
    $version = $getMeta['latest_version'];
}

if (array_key_exists('mod_infor', $getMeta)) {
    $mod_infor = $getMeta['mod_infor'];
}

if (array_key_exists('size', $getMeta)) {
    $size = $getMeta['size'];
}

if (array_key_exists('_faq', $getMeta)) {
    $faq = $getMeta['_faq'];
}

if (array_key_exists('sapo_default', $getMeta)) {
    $sapo = $getMeta['sapo_default'];
}

if (array_key_exists('h1_sapo', $getMeta)) {
    $h1_sapo = $getMeta['h1_sapo'];
}

?>
<style>
    tr:first-child {
        color: black;
    }

    #content {
        background: none;
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
                <li><a href="<?php echo get_category_link($category[0]->term_id) ?>"><?php echo $category[0]->name ?></a></li>
                <li class="btn-fa-angle"> » </li>
                <li><a class="active" href="<?php echo get_permalink(get_the_ID()) ?>"><?php echo the_title() ?></a></li>
            </ul>
            <div class="clear"></div>
        </div>
        <div class="pad10">
			<?php if(!empty($h1_sapo)) { ?>
            <h1 class="main_head ac"><?php print_r($h1_sapo[0]) ?></h1>
			<?php } ?>
            <?php if (!empty($sapo)) { ?>
                <div class="sapo-review-default">
                    <p><?php echo $sapo[0] ?></p>
                </div>
            <?php } ?>

            <?php echo the_content() ?>

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
                <?php $terms = wp_get_post_terms($post->ID, 'product_cat'); ?>
                <a class="side_list_item" href="<?php echo get_permalink($post->ID) ?>">
                    <?php echo get_the_post_thumbnail($post->ID) ?>
                    <p class="title"><?php echo get_the_title($post->ID) ?></p>
                    <p class="category"><?php echo $terms[0]->name ?></p>
                </a>
            <?php } ?>
        </div>
    </div>
    <div class="clear mb20"></div>

</div>