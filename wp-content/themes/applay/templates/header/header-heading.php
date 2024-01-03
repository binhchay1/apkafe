<?php
global $page_title;
$ct_hd = get_post_meta(get_the_ID(), 'header_content', true);
if (function_exists('is_shop') && is_shop()) {
    $ct_hd = '';
    $id_ot = get_option('woocommerce_shop_page_id');
    if ($id_ot != '') {
        $ct_hd = get_post_meta($id_ot, 'header_content', true);
    }
}
if (is_home()) {
    $ct_hd = '';
    $id_ot = get_option('page_for_posts');
    if ($id_ot != '') {
        $ct_hd = get_post_meta($id_ot, 'header_content', true);
    }
}
if (!is_page_template('page-templates/front-page.php') && $ct_hd == '') {
    $heading_bg = leaf_get_option('heading_bg');
    if ($heading_bg) { ?>
        <style scoped>
            .page-heading {
                background-image: url(<?php echo esc_url($heading_bg['background-image']) ?>);
                background-color: <?php echo esc_attr($heading_bg['background-color']) ?>;
                background-position: <?php echo esc_attr($heading_bg['background-position']) ?>;
                background-repeat: <?php echo esc_attr($heading_bg['background-repeat']) ?>;
                background-size: <?php echo esc_attr($heading_bg['background-size']) ?>;
                background-attachment: <?php echo esc_attr($heading_bg['background-attachment']) ?>;
            }
        </style>
    <?php }
    if (is_singular('app_portfolio') || is_singular('product')) { ?>
        <?php
        if (function_exists('yoast_breadcrumb')) {
            yoast_breadcrumb('<div class="container"><p id="breadcrumbs">', '</p></div>');
        }
        ?>
        <div class="page-heading main-color-1-bg dark-div">
            <div class="container">

                <?php if (get_field('custom_heading', get_the_ID())) { ?>
                    <div class="row ducho custom-heading">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <h1><?php the_field('custom_heading', get_the_ID()); ?></h1>
                        </div>
                    </div>
                <?php } ?>

                <div class="row">
                    <?php if ($icon = get_post_meta(get_the_ID(), 'app-icon', true)) { ?>
                        <div class="col-md-2 col-sm-3 col-xs-12">
                            <img src="<?php echo esc_url($icon); ?>" alt="<?php the_title_attribute(); ?>" title="<?php the_title_attribute(); ?>" class="icon-appport" />
                        </div>
                    <?php } ?>
                    <div class="col-md-10 col-sm-9 col-xs-12" style="margin-top: 15px;">
                        <span class="sub-title" style="font-size: 30px !important; font-weight: bold;"><?php echo esc_attr($page_title) ?></span>
                        <div class="app-store-link">
                            <button class="btn btn-default btn-store btn-download-now" target="_blank" id="download-now">
                                <div class="btn-store-text">
                                    <span><?php _e("Download now", "leafcolor") ?></span><br />
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $heading_bg = get_post_meta(get_the_ID(), 'app-banner', true);
        $darkness = get_post_meta(get_the_ID(), 'banner-darkness', true);
        if ($heading_bg || $darkness) { ?>
            <style scoped>
                <?php if ($heading_bg) { ?>.page-heading {
                    background-image: url(<?php echo esc_url($heading_bg) ?>);
                    background-position: center center;
                    background-size: cover;
                    background-attachment: fixed;
                }

                <?php }
                if ($darkness) { ?>.page-heading:before {
                    background: rgba(0, 0, 0, <?php echo esc_attr($darkness / 100); ?>);
                }

                <?php } ?>
            </style>
        <?php }
    } else { ?>

<?php
    }
}
?>

<div class="top-sidebar">
    <div class="container">
        <div class="row">
            <?php
            if (is_active_sidebar('top_sidebar')) :
                dynamic_sidebar('top_sidebar');
            endif;
            ?>
        </div>
    </div>
</div>