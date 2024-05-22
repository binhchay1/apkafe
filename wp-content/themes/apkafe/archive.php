<?php
get_header();
$category_id = get_query_var('cat');
$args = array('cat' => $category_id, 'orderby' => 'modified', 'order' => 'DESC', 'posts_per_page' => 24, 'post_status' => 'publish');
$get_post = new WP_Query($args);
$getH1 = get_term_meta($category_id, 'h1_category', true);
$description = category_description($category_id);
?>

<div class="container">
    <?php
    function get_all_terms($ter, $breadcrumb, $listTermShift)
    {
        $terms_parent = get_term($ter->parent, $ter->taxonomy);
        $listTermShift[] = $terms_parent;
        if ($terms_parent->parent == 0) {
            $listTermShift = array_reverse($listTermShift);
            foreach ($listTermShift as $key => $term) {
                if ($key == (count($listTermShift) - 1)) {
                    $breadcrumb .= '<span class="breadcrumb_last" aria-current="page">' . $term->name . '</span></span></span></p>';
                } else {
                    $breadcrumb .= '<a href="' . get_term_link($term->slug, $term->taxonomy) . '" rel="tag">' . $term->name . '</a> » ';
                }
            }

            echo $breadcrumb;
        } else {
            get_all_categories($cate_parent, $breadcrumb, $listCategoryShift);
        }
    }

    $term = get_queried_object();
    $breadcrumb = '<p id="breadcrumbs"><span><span><a href="/">Home</a> » ';

    if ($term->parent == 0) {
        $breadcrumb .= '<span class="breadcrumb_last" aria-current="page">' . $term->name . '</span></span></span></p>';

        echo $breadcrumb;
    } else {
        $listTermShift[] = $term;
        get_all_terms($term, $breadcrumb, $listTermShift);
    }
    ?>
    <div class="content-pad-4x">
        <div class="row">
            <div id="content" class="<?php if ($layout != 'full' && $layout != 'true-full') { ?> col-md-9 <?php } else { ?>col-md-12 <?php }
                                                                                                                                    if ($layout == 'left') { ?> revert-layout <?php } ?>">
                <?php
                if (class_exists('WCV_Vendor_Shop')) {
                    WCV_Vendor_Shop::shop_description();
                } ?>

                <div>
                    <?php if ($getH1 != '') { ?>
                        <h1><?php echo $getH1 ?></h1>
                    <?php } ?>
                </div>

                <div>
                    <?php if ($description != '') { ?>
                        <?php echo $description ?>
                    <?php } ?>
                </div>

                <ul class="sort-controls" id="section-tab-filter">
                    <li class="active" style="cursor: pointer;" onclick="handleTabCategory(jQuery(this), 'news')">
                        <a>News</a>
                    </li>
                    <li style="cursor: pointer;" onclick="handleTabCategory(jQuery(this), 'hot')">
                        <a>Hot</a>
                    </li>
                    <li style="cursor: pointer;" onclick="handleTabCategory(jQuery(this), 'popular')">
                        <a>Popular</a>
                    </li>
                </ul>

                <div id="news">
                    <ul class="ul-list-in-archive">
                        <?php if ($get_post->have_posts()) { ?>
                            <?php foreach ($get_post->posts as $post) { ?>
                                <li <?php post_class(); ?>>
                                    <?php
                                    $icon = get_the_post_thumbnail_url($post->ID);
                                    ?>
                                    <div class="item-content <?php if ($icon) { ?> has-icon <?php } ?>">
                                        <div class="app-icon">
                                            <a href="<?php the_permalink($post->ID) ?>" title="<?php echo $post->post_title ?>">
                                                <img src="<?php echo esc_url($icon); ?>" alt="<?php echo $post->post_title ?>" />
                                            </a>
                                        </div>
                                        <p class="product-title"><a href="<?php the_permalink($post->ID) ?>" title="<?php echo $post->post_title ?>" class="main-color-1-hover"><?php echo $post->post_title ?></a></p>
                                    </div>
                                </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </div>

                <div id="hot">
                    <?php $listPostHot = []; ?>
                    <?php $getOptionHot = ot_get_option('customize_hot') ?>
                    <?php if ($getOptionHot != '') { ?>
                        <?php foreach ($getOptionHot as $option) { ?>
                            <?php if ($option['title'] == $term->name) { ?>
                                <?php foreach ($option['post_select'] as $postSelectHot) { ?>
                                    <?php $listPostHot[] = $postSelectHot ?>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                        <?php if (!empty($listPostHot)) { ?>
                            <?php
                            $paged = max(1, get_query_var('page'));
                            $args = array(
                                'post__in' => $listPostHot,
                                'posts_per_page' => 16,
                                'paged' => $paged,
                                'post_status' => 'published',
                            );
                            $res =  new WP_Query($args); ?>
                            <ul class="ul-list-in-archive">
                                <?php if ($res->have_posts()) { ?>
                                    <?php foreach ($res->posts as $post) { ?>
                                        <li <?php post_class(); ?>>
                                            <?php
                                            $icon = get_the_post_thumbnail_url($post->ID);
                                            ?>
                                            <div class="item-content <?php if ($icon) { ?> has-icon <?php } ?>">
                                                <div class="app-icon">
                                                    <a href="<?php the_permalink($post->ID) ?>" title="<?php echo $post->post_title ?>">
                                                        <img src="<?php echo esc_url($icon); ?>" alt="<?php echo $post->post_title ?>" />
                                                    </a>
                                                </div>
                                                <p class="product-title"><a href="<?php the_permalink($post->ID) ?>" title="<?php echo $post->post_title ?>" class="main-color-1-hover"><?php echo $post->post_title ?></a></p>
                                            </div>
                                        </li>
                                    <?php } ?>
                                <?php } ?>
                            </ul>
                        <?php } ?>
                    <?php } ?>
                </div>

                <div id="popular">
                    <?php $listPostPopular = []; ?>
                    <?php $getOptionPopular = ot_get_option('customize_popular') ?>
                    <?php if ($getOptionPopular != '') { ?>
                        <?php foreach ($getOptionPopular as $optionPopular) { ?>
                            <?php if ($optionPopular['title'] == $term->name) { ?>
                                <?php foreach ($optionPopular['post_select'] as $postSelectPopular) { ?>
                                    <?php $listPostPopular[] = $postSelectPopular ?>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                        <?php
                        $paged = max(1, get_query_var('page'));
                        $args = array(
                            'post__in' => $listPostPopular,
                            'posts_per_page' => 12,
                            'paged' => $paged,
                            'post_status' => 'published',
                            'post_type' => 'any',
                        );
                        $res =  new WP_Query($args); ?>
                        <ul class="ul-list-in-archive">
                            <?php if ($res->have_posts()) { ?>
                                <?php foreach ($res->posts as $post) { ?>
                                    <li <?php post_class(); ?>>
                                        <?php
                                        $icon = get_the_post_thumbnail_url($post->ID);
                                        ?>
                                        <div class="item-content <?php if ($icon) { ?> has-icon <?php } ?>">
                                            <div class="app-icon">
                                                <a href="<?php the_permalink($post->ID) ?>" title="<?php echo $post->post_title ?>">
                                                    <img src="<?php echo esc_url($icon); ?>" alt="<?php echo $post->post_title ?>" />
                                                </a>
                                            </div>
                                            <p class="product-title"><a href="<?php the_permalink($post->ID) ?>" title="<?php echo $post->post_title ?>" class="main-color-1-hover"><?php echo $post->post_title ?></a></p>
                                        </div>
                                    </li>
                                <?php } ?>
                            <?php } ?>
                        </ul>
                    <?php  } ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    do_action('woocommerce_sidebar');
    ?>
</div>
<script>
    function handleTabCategory(btn, cate) {
        let listLi = jQuery('#section-tab-filter li');
        let idSection = '#' + cate;
        for (let i = 0; i < listLi.length; i++) {
            if (listLi[i].classList.contains('active')) {
                listLi[i].classList.remove("active");
            }
        }

        btn.addClass('active');
        jQuery('#news').hasClass('')

        if (cate == 'news') {
            jQuery('#hot').hide();
            jQuery('#popular').hide();
        }

        if (cate == 'hot') {
            jQuery('#hot').hide();
            jQuery('#news').hide();
        }

        if (cate == 'popular') {
            jQuery('#hot').hide();
            jQuery('#news').hide();
        }

        jQuery(idSection).show();
    }

    jQuery(document).ready(function() {
        jQuery('#hot').hide();
        jQuery('#popular').hide();
    });
</script>
<?php get_footer(); ?>