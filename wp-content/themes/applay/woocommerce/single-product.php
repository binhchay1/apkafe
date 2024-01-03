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

if (!defined('ABSPATH')) exit; // Exit if accessed directly

get_header('shop'); ?>
<?php get_template_part('templates/header/header', 'heading');
$content_padding = get_post_meta(get_the_ID(), 'product-contpadding', true);
$woo_layout = get_post_meta(get_the_ID(), 'product-sidebar', true);
if (function_exists('ot_get_option') && !$woo_layout) {
    $woo_layout =  ot_get_option('woocommerce_layout', 'right');
}
$disable_woo = get_post_meta(get_the_ID(), 'disable-woo', true) == 'on';
?>
<div class="container">
    <?php if ($content_padding != 'off') { ?>
        <div class="content-pad-4x">
        <?php } ?>
        <div class="row">
            <?php
            /**
             * woocommerce_before_main_content hook
             *
             * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
             * @hooked woocommerce_breadcrumb - 20
             */
            //do_action( 'woocommerce_before_main_content' );
            ?>
            <div id="content" class="<?php if ($woo_layout != 'full') { ?> col-md-9 <?php } else { ?>col-md-12 <?php }
                                                                                                    if ($woo_layout == 'left') { ?> revert-layout <?php } ?>">
                <script>
                    jQuery(document).ready(function(e) {
                        jQuery("script[type='application/ld+json']").remove();
                    });
                </script>
                <?php while (have_posts()) : the_post(); ?>

                    <?php if ($disable_woo) {
                        the_content();
                    } else {
                        wc_get_template_part('content', 'single-product');
                    } ?>

                <?php endwhile; // end of the loop. 
                ?>
            </div>
            <?php
            /**
             * woocommerce_after_main_content hook
             *
             * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
             */
            // do_action( 'woocommerce_after_main_content' );
            ?>
            <?php
            /**
             * woocommerce_sidebar hook
             *
             * @hooked woocommerce_get_sidebar - 10
             */
            if ($woo_layout != 'full') {
                do_action('woocommerce_sidebar');
            }
            ?>
        </div>
        <?php if ($content_padding != 'off') { ?>
        </div><!--/content-pad-4x-->
    <?php } ?>
</div>
<?php get_footer('shop'); ?>