<?php
get_header();
$category = get_category(get_query_var('cat'));
$cat_id = $category->cat_ID;

$get_post = new WP_Query(array(
    'posts_per_page' => 24,
    'orderby'     => 'modified',
    'order'       => 'DESC',
));
?>

<div class="container">
    <div class="main_bar">
        <div class="widget">
            <div class="widget_head">
                <ul id="breadcrumbs" class="bread_crumb">
                    <li><a href="<?php echo home_url() ?>">Home</a></li>
                    <li class="breadcrumb-archive"> » </li>
                    <li><a class="active" href="<?php echo get_category_link($cat_id) ?>"><?php echo get_cat_name($cat_id) ?></a></li>
                </ul>
                <div class="clear"></div>
            </div>
            <div id="main_list_item" class="main_list_item">
                <a class="side_list_item" href="https://apkmodget.com/games/fnaf-2-apk-3/">
                    <img class="item_icon lazyloaded" width="80" height="80" src="https://apkmodget.com/media/2023/10/_1/80x80/fnaf-2-apk_1c29e.jpg" data-src="https://apkmodget.com/media/2023/10/_1/80x80/fnaf-2-apk_1c29e.jpg" alt="FnaF 2 Apk 2.0 Download Full Version For Android">
                    <p class="title">FnaF 2 Apk 2.0 Download Full Version For Android</p>
                    <p class="category">v2.0 + MOD: Unlocked</p>
                </a>
            </div>
            <div class="clear mb20"></div>
            <div class="ac" id="main_list_item_next"><a onclick="get_more_cat_items();" class="more_link" href="javascript:void(0);">Load More Action Updates <i class="fa fa-angle-double-down"></i></a></div>
        </div>
        <div class="clear mb15"></div>
    </div>
    <div class="side_bar">
        <div class="widget">
            <h2 class="widget_head">Latest Update</h2>
            <div class="list_item_wrap">
                <a class="side_list_item" href="https://apkmodget.com/apps/mytelkomsel-apk-download-1/">
                    <img class="item_icon lazyloaded" width="80" height="80" src="https://apkmodget.com/media/2023/10/_1/80x80/mytelkomsel-apk_f9f39.jpg" data-src="https://apkmodget.com/media/2023/10/_1/80x80/mytelkomsel-apk_f9f39.jpg" alt="MyTelkomsel Apk V7.3.0 Download For Android">
                    <p class="title">MyTelkomsel Apk v7.3.0 Download For Android</p>
                    <p class="category">v7.3.0 + MOD: For Android</p>
                </a>
            </div>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
        <?php get_sidebar(); ?>
    </div>
    <div class="clear"></div>
</div>

<?php get_footer(); ?>