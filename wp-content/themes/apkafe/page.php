<?php
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
                <a class="side_list_item" href="https://apkmodget.com/apps/mytelkomsel-apk-download-1/">
                    <img class="item_icon lazyloaded" width="80" height="80" src="https://apkmodget.com/media/2023/10/_1/80x80/mytelkomsel-apk_f9f39.jpg" data-src="https://apkmodget.com/media/2023/10/_1/80x80/mytelkomsel-apk_f9f39.jpg" alt="MyTelkomsel Apk V7.3.0 Download For Android">
                    <p class="title">MyTelkomsel Apk v7.3.0 Download For Android</p>
                    <p class="category">v7.3.0 + MOD: For Android</p>
                </a>
            </div>
            <div class="clear mb10"></div>
            <div class="ac" id="main_list_item_next"><a onclick="get_more_latest_items();" class="more_link" href="javascript:void(0);">Load More Latest Updates </a></div>
            <div class="clear mb30"></div>
        </div>
        <div class="clear mb10"></div>
        
        <div class="widget">
            <h2 class="widget_head">Top Games</h2>
            <div class="main_list_item">
                <a class="side_list_item" href="https://apkmodget.com/games/minecraft-apk-4/">
                    <img class="item_icon lazyloaded" width="80" height="80" src="https://apkmodget.com/media/2021/11/_1/80x80/Minecraft-Apk.png" data-src="https://apkmodget.com/media/2021/11/_1/80x80/Minecraft-Apk.png" alt="Minecraft Apk Download V1.20.80.23 Free Softonic Android">
                    <p class="title">Minecraft Apk Download v1.20.80.23 Free Softonic Android</p>
                    <p class="category">v1.20.80.23 + MOD: Free</p>
                </a>
                <div class="clear mb10"></div>
                <a href="https://apkmodget.com/games/" class="more_link">Get More Games </a>
            </div>
        </div>
        <div class="clear mb10"></div>

        <div class="cnt_box pad10">
            <h2><strong><span class="s4"><?php echo ot_get_option('homepage_title_description') ?></span></strong></h2>
            <?php echo ot_get_option('homepage_description') ?>
        </div>
        <div class="clear"></div>
    </div>
    <?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>