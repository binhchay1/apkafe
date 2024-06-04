<?php
get_header();
$category_id = get_query_var('cat');
$category = get_the_category_by_ID($category_id);
$getH1 = get_term_meta($category_id, 'h1_category', true);
$description = category_description($category_id);
$isHotTab = false;
$isPopularTab = false;
$isNewsTab = false;

if (!empty($_GET)) {
    if (array_key_exists('hot_page', $_GET)) {
        $getPaginationHot = $_GET['hot_page'];
        $isHotTab = true;
    }

    if (array_key_exists('popular_page', $_GET)) {
        $getPaginationPopular = $_GET['popular_page'];
        $isPopularTab = true;
    }

    if (array_key_exists('news_page', $_GET)) {
        $getPaginationNews = $_GET['news_page'];
        $isNewsTab = true;
    }
}

if (isset($getPaginationNews)) {
    $current_page_news = $getPaginationNews;
} else {
    $current_page_news = 1;
}

$args = array('cat' => $category_id, 'orderby' => 'modified', 'order' => 'DESC', 'posts_per_page' => 15, 'post_status' => 'publish', 'paged' => $current_page_news, 'post_type' => 'post');
$get_post = new WP_Query($args);
$total_page_news = $get_post->max_num_pages;
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
    $breadcrumb = '<p id="breadcrumbs" style="padding: 10px"><span><span><a href="/">Home</a> » ';

    if ($term->parent == 0) {
        $breadcrumb .= '<span class="breadcrumb_last" aria-current="page">' . $term->name . '</span></span></span></p>';

        echo $breadcrumb;
    } else {
        $listTermShift[] = $term;
        get_all_terms($term, $breadcrumb, $listTermShift);
    }
    ?>
    <div class="content-pad-4x">
        <div id="content">
            <?php if ($getH1 != '') { ?>
                <div class="padding-20">
                    <h1><?php echo $getH1 ?></h1>
                </div>
            <?php } ?>

            <?php if ($description != '') { ?>
                <div class="padding-20">
                    <?php echo $description ?>
                </div>
            <?php } ?>


            <ul class="sort-controls" id="section-tab-filter">
                <li id="tab-filter-news" class="active" style="cursor: pointer;" onclick="handleTabCategory(jQuery(this), 'news')">
                    <a><i class="fa fa-refresh" style="margin-right: 10px;"></i>News</a>
                </li>
                <li id="tab-filter-hot" style="cursor: pointer;" onclick="handleTabCategory(jQuery(this), 'hot')">
                    <a><i class="fa fa-heartbeat" style="margin-right: 10px;"></i>Hot</a>
                </li>
                <li id="tab-filter-popular" style="cursor: pointer;" onclick="handleTabCategory(jQuery(this), 'popular')">
                    <a><i class="fa fa-line-chart" style="margin-right: 10px;"></i>Popular</a>
                </li>
            </ul>

            <div id="news">
                <div class="main_list_item">
                    <?php if ($get_post->have_posts()) { ?>
                        <?php foreach ($get_post->posts as $post) { ?>
                            <a class="side_list_item" href="<?php echo get_permalink($post->ID) ?>">
                                <?php echo get_the_post_thumbnail($post->ID) ?>
                                <p class="title"><?php echo get_the_title($post->ID) ?></p>
                            </a>
                        <?php } ?>
                    <?php } ?>
                </div>

                <div class="d-flex justify-center margin-top-15">
                    <?php echo paginate_links(array(
                        'format' => '?news_page=%#%',
                        'current' => $current_page_news,
                        'total' => $total_page_news,
                        'prev_text' => __('←'),
                        'next_text' => __('→'),
                        'type' => 'list',
                    )); ?>
                </div>
            </div>

            <div id="hot">
                <?php $listPostHot = []; ?>
                <?php $getOptionHot = ot_get_option('customize_hot') ?>
                <?php if ($getOptionHot != '') { ?>
                    <?php foreach ($getOptionHot as $option) { ?>
                        <?php if ($option['title'] == $category) { ?>
                            <?php foreach ($option['post_select'] as $postSelectHot) { ?>
                                <?php $listPostHot[] = $postSelectHot ?>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>

                    <?php if (!empty($listPostHot)) { ?>
                        <?php
                        if (isset($getPaginationHot)) {
                            $current_page_hot = $getPaginationHot;
                        } else {
                            $current_page_hot = 1;
                        }

                        $args = array(
                            'post__in' => $listPostHot,
                            'posts_per_page' => 15,
                            'paged' => $current_page_hot,
                            'post_status' => 'published',
                            'post_type' => 'post',
                        );
                        $res =  new WP_Query($args);
                        $total_page_hot = $res->max_num_pages; ?>
                        <div class="main_list_item">
                            <?php if ($res->have_posts()) { ?>
                                <?php foreach ($res->posts as $post) { ?>
                                    <a class="side_list_item" href="<?php echo get_permalink($post->ID) ?>">
                                        <?php echo get_the_post_thumbnail($post->ID) ?>
                                        <p class="title"><?php echo get_the_title($post->ID) ?></p>
                                    </a>
                                <?php } ?>
                            <?php } ?>
                        </div>

                        <div class="d-flex justify-center margin-top-15">
                            <?php echo paginate_links(array(
                                'format' => '?hot_page=%#%',
                                'current' => $current_page_hot,
                                'total' => $total_page_hot,
                                'type' => 'list',
                                'prev_text'    => __('←'),
                                'next_text'    => __('→'),
                            )); ?>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>

            <div id="popular">
                <?php $listPostPopular = []; ?>
                <?php $getOptionPopular = ot_get_option('customize_popular') ?>
                <?php if ($getOptionPopular != '') { ?>
                    <?php foreach ($getOptionPopular as $optionPopular) { ?>
                        <?php if ($optionPopular['title'] == $category) { ?>
                            <?php foreach ($optionPopular['post_select'] as $postSelectPopular) { ?>
                                <?php $listPostPopular[] = $postSelectPopular ?>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                    <?php
                    if (isset($getPaginationPopular)) {
                        $current_page_popular = $getPaginationPopular;
                    } else {
                        $current_page_popular = 1;
                    }

                    $args = array(
                        'post__in' => $listPostPopular,
                        'posts_per_page' => 15,
                        'paged' => $current_page_popular,
                        'post_status' => 'published',
                        'post_type' => 'post',
                    );
                    $res =  new WP_Query($args);
                    $total_page_popular = $res->max_num_pages; ?>
                    <div class="main_list_item">
                        <?php if ($res->have_posts()) { ?>
                            <?php foreach ($res->posts as $post) { ?>
                                <a class="side_list_item" href="<?php echo get_permalink($post->ID) ?>">
                                    <?php echo get_the_post_thumbnail($post->ID) ?>
                                    <p class="title"><?php echo get_the_title($post->ID) ?></p>
                                </a>
                            <?php } ?>
                        <?php } ?>
                    </div>
                    <div class="d-flex justify-center margin-top-15">
                        <?php echo paginate_links(array(
                            'format' => '?popular_page=%#%',
                            'current' => $current_page_popular,
                            'total' => $total_page_popular,
                            'type' => 'list',
                            'prev_text' => __('←'),
                            'next_text' => __('→'),
                        )); ?>
                    </div>
                <?php  } ?>
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
        let listLi = jQuery('#section-tab-filter li');
        <?php if ($isHotTab) { ?>
            jQuery('#news').hide();
            jQuery('#popular').hide();
            jQuery('#hot').show();
            for (let i = 0; i < listLi.length; i++) {
                if (listLi[i].classList.contains('active')) {
                    listLi[i].classList.remove("active");
                }
            }

            jQuery('#tab-filter-hot').addClass('active');
        <?php } elseif ($isPopularTab) { ?>
            jQuery('#news').hide();
            jQuery('#hot').hide();
            jQuery('#popular').show();
            for (let i = 0; i < listLi.length; i++) {
                if (listLi[i].classList.contains('active')) {
                    listLi[i].classList.remove("active");
                }
            }

            jQuery('#tab-filter-popular').addClass('active');
        <?php } else { ?>
            jQuery('#hot').hide();
            jQuery('#popular').hide();
        <?php } ?>

    });
</script>
<?php get_footer(); ?>