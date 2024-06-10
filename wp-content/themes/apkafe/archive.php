<?php
get_header();
$category_id = get_query_var('cat');
$category = get_the_category_by_ID($category_id);
$getH1 = get_term_meta($category_id, 'h1_category', true);
$description = category_description($category_id);
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$args = array('cat' => $category_id, 'orderby' => 'modified', 'order' => 'DESC', 'posts_per_page' => 16, 'post_status' => 'publish', 'paged' => $paged, 'post_type' => 'post');
$get_post = new WP_Query($args);
$total_page_news = $get_post->max_num_pages;
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

            <div id="news">
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