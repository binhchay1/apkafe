<?php

/**
 * The Template for displaying all single products.
 *
 * Override this template by copying it to yourtheme/woocommerce/single-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if (!defined('ABSPATH')) exit;

$getCategory = get_the_terms(get_the_ID(), 'product_cat');
$product_cat_slug = [];
$slug = 'default';

foreach ($getCategory as $term) {
    $product_cat_slug[] = $term->slug;
}

if (in_array('review', $product_cat_slug)) {
    $slug = 'review';
}

get_header(); ?>
<?php
$content_padding = get_post_meta(get_the_ID(), 'product-contpadding', true);
$woo_layout = get_post_meta(get_the_ID(), 'product-sidebar', true);
if (function_exists('ot_get_option') && !$woo_layout) {
    $woo_layout =  ot_get_option('woocommerce_layout', 'left');
}
$disable_woo = get_post_meta(get_the_ID(), 'disable-woo', true) == 'on';


?>
<div class="container">
    <?php if ($content_padding != 'off') { ?>
        <div class="content-pad-4x">
        <?php } ?>
        <div class="row">
            <div id="content" class="<?php if ($woo_layout != 'full') { ?> col-md-9 <?php } else { ?>col-md-12 <?php }
                                                                                                            if ($woo_layout == 'left') { ?> revert-layout <?php } ?>">
                <?php while (have_posts()) : the_post(); ?>

                    <?php if ($disable_woo) {
                        the_content();
                    } else {
                        switch ($slug) {
                            case 'review':
                                get_template_part('templates/review', 'review');
                                break;
                            default:
                                wc_get_template_part('content', 'single-product');
                        }
                    } ?>

                <?php endwhile;
                ?>
            </div>
            <?php
            if ($woo_layout != 'full') {
                do_action('woocommerce_sidebar');
            }
            ?>
        </div>
        <?php if ($content_padding != 'off') { ?>
        </div>
    <?php } ?>
</div>
<?php get_footer('shop'); ?>