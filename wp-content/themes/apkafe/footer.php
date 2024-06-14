<?php
/*
 *     Footer
 */
?>

<footer>
    <div class="container">
        <div class="ftr_link_box">
            <?php if (!empty(ot_get_option('menu_footer'))) {  ?>
                <?php foreach (ot_get_option('menu_footer') as $menu) { ?>
                    <a href="<?php echo $menu['link'] ?>"><?php echo $menu['title'] ?></a>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
    <div class="clear mb15"></div>
    <div class="footer_main">
        <div class="container">
            <div class="ftr_social_box">
                <?php if (ot_get_option('share_facebook') != '') { ?>
                    <a class="fsb_facebook" target="_blank" href="<?php echo ot_get_option('share_facebook') ?>"><i class="fa fa-facebook"></i></a>
                <?php } ?>
                <?php if (ot_get_option('share_twitter') != '') { ?>
                    <a class="fsb_twitter" target="_blank" href="<?php echo ot_get_option('share_twitter') ?>"><i class="fa fa-twitter"></i></a>
                <?php } ?>
                <?php if (ot_get_option('share_linkedin') != '') { ?>
                    <a class="fsb_linkedin" target="_blank" href="<?php echo ot_get_option('share_linkedin') ?>"><i class="fa fa-linkedin"></i></a>
                <?php } ?>
                <?php if (ot_get_option('share_youtube') != '') { ?>
                    <a class="fsb_youtube" target="_blank" href="<?php echo ot_get_option('share_youtube') ?>"><i class="fa fa-youtube"></i></a>
                <?php } ?>
                <?php if (!empty(ot_get_option('custom_share'))) { ?>
                    <?php foreach (ot_get_option('custom_share') as $link) { ?>
                        <a class="fsb_custom" target="_blank" href="<?php echo $link['link_share'] ?>"><img src="<?php echo $link['icon'] ?>" width="30" height="30"></i></a>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
    <div class="footer_bottom">
        <div class="container" style="display: flex;">
            <div><img src="<?php echo get_template_directory_uri() . '/images/logo-modobom-resize.png' ?>" /></div>
            <div class="ac"><?php echo ot_get_option('copyright') ?></div>
        </div>
    </div>
    <div class="clear"></div>
</footer>

<?php if (ot_get_option('custom_script_footer') != '') {
    echo ot_get_option('custom_script_footer');
} ?>

<?php wp_footer() ?>
</body>

</html>