<?php
/**
 * List template
 *
 * @var AAWP_Template_Functions $this
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

?>

<div class="<?php echo $this->get_product_container_classes( 'aawp-product aawp-product--list' ); ?>" <?php $this->the_product_container(); ?>>

    <?php $this->the_product_ribbons(); ?>

    <div class="aawp-product__inner">
        <a class="aawp-product__image-link" href="<?php echo $this->get_product_image_link(); ?>" title="<?php echo $this->get_product_image_link_title(); ?>" rel="nofollow noopener sponsored" target="_blank">
            <img class="aawp-product__image" src="<?php echo $this->get_product_image(); ?>" alt="<?php echo $this->get_product_image_alt(); ?>" <?php $this->the_product_image_title(); ?> />
        </a>
        <div class="aawp-product__content">
            <a class="aawp-product__title" href="<?php echo $this->get_product_url(); ?>" title="<?php echo $this->get_product_link_title(); ?>" rel="nofollow noopener sponsored" target="_blank"><?php echo $this->get_product_title(); ?></a>
            <div class="aawp-product__teaser">
                <?php echo $this->get_product_teaser( $format = 'paragraph' ); ?>
            </div>
            <div class="aawp-product__meta">
                <?php if ( $this->get_product_rating() ) { ?>
                    <?php echo $this->get_product_star_rating(); ?>
                <?php } ?>

                <?php if ( $this->get_product_is_sale() ) { ?>
                    <?php if ( $this->sale_show_old_price() ) { ?>
                        <span class="aawp-product__price aawp-product__price--old"><?php echo $this->get_product_pricing('old'); ?></span>
                    <?php } ?>
                    <?php if ( $this->sale_show_price_reduction() ) { ?>
                        <span class="aawp-product__price aawp-product__price--saved"><?php echo $this->get_saved_text(); ?></span>
                    <?php } ?>
                <?php } ?>

                <?php if ( $this->show_advertised_price() ) { ?>
                    <span class="aawp-product__price aawp-product__price--current"><?php echo $this->get_product_pricing(); ?></span>
                <?php } ?>

                <?php $this->the_product_check_prime_logo(); ?>
            </div>
        </div>
    </div>

</div>
