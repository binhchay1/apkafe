<?php
/*
 *     Footer
 */
?>

<?php
$sql_top_game = "SELECT post_id FROM wp_top_games";
$top_game = $wpdb->get_results($sql_top_game);

if (empty($top_game)) {
    $top_game_list_id = [];
} else {
    foreach ($top_game as $game) {
        $top_game_list_id[] = $game->post_id;
    }
}
?>

<div id="bottom-sidebar">
    <div class="container">
        <div class="row normal-sidebar">
            <?php
            if (is_active_sidebar('bottom_sidebar')) :
                dynamic_sidebar('bottom_sidebar');
            endif;
            ?>
        </div>
    </div>
</div>
<div class="footer_new">
    <div class="footer_container">
        <div class="info" style="display: flex; justify-content: space-around;">
            <div class="item group">
                <img alt="Apkafe" src="https://apkafe.com/wp-content/uploads/2019/04/fav_apkafe-2.png" height="65" width="47" class="p_logo" style="height: 65px;">
                <p>ＧＳハイム日暮里 １１０１号室, ２丁目-３９-7 西日暮里 荒川区 東京都 116-0013</p>
            </div>
            <div class="item">
                <div class="title" style="text-align: center;">Follow US</div>
                <ul class="share-box">
                    <li>
                        <a class="facebook" rel="nofollow noopener" target="_blank" title="facebook" href="https://www.facebook.com/apkafedotcom/">
                            <i class="icon f_icon_facebook"></i>
                        </a>
                    </li>
                    <li>
                        <a class="youtube" rel="nofollow noopener" target="_blank" title="youtube" href="https://www.youtube.com/channel/UC6py-hSG9D0ryOGDIHoWz4Q">
                            <i class="icon f_icon_youtube"></i>
                        </a>
                    </li>
                    <li>
                        <a class="twitter" rel="nofollow noopener" target="_blank" title="twitter" href="https://twitter.com/Apkafeappstore">
                            <i class="icon f_icon_twitter"></i>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="item">
                <div class="title">Mainpage</div>
                <ul>
                    <li><a href="/about-us/">About Us</a></li>
                    <li><a href="/privacy-policy/">Privacy Policy</a></li>
                    <li><a href="/terms-of-use/">Term of use</a></li>
                </ul>
            </div>
            <div class="item">
                <div class="title">Top Game App</div>
                <ul>
                    <?php foreach ($top_game_list_id as $id) { ?>
                        <li><a href="<?php echo get_permalink($id) ?>"><?php echo get_the_title($id) ?></a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <div class="other">
            <div class="info">
                Copyright © 2023 Apkafe All rights reserved.
            </div>

        </div>
    </div>
</div>

<?php echo ot_get_option('google_analytics_code', ''); ?>
<?php wp_footer(); ?>

</body>

</html>