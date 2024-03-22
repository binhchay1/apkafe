<?php
/**
 * Widget "small" template
 *
 * @var AAWP_Template_Functions $this
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

?>

<div class="<?php echo $this->get_product_container_classes( 'aawp-product aawp-product--widget-small' ); ?>" <?php $this->the_product_container(); ?>>

    <?php $this->the_product_ribbons(); ?>

    <span class="aawp-product__inner">
        <a class="aawp-product__image-link" href="<?php echo $this->get_product_image_link(); ?>" title="<?php echo $this->get_product_image_link_title(); ?>" rel="nofollow noopener sponsored" target="_blank">
            <img class="aawp-product__image" src="<?php echo $this->get_product_image('medium'); ?>" alt="<?php echo $this->get_product_image_alt(); ?>" <?php $this->the_product_image_title(); ?> />
        </a>
        <span class="aawp-product__content">
            <a class="aawp-product__title" href="<?php echo $this->get_product_url(); ?>" title="<?php echo $this->get_product_link_title(); ?>" rel="nofollow noopener sponsored" target="_blank"><?php echo $this->truncate( $this->get_product_title(), 50 ); ?></a>
            <span class="aawp-product__meta">
                <?php if ( $this->get_product_rating() ) { ?>
                    <?php echo $this->get_product_star_rating(); ?>
                <?php } ?>

                <?php if ( $this->show_advertised_price() ) { ?>
                    <span class="aawp-product__price aawp-product__price--current"><?php echo $this->get_product_pricing(); ?></span>
                <?php } ?>
            </span>
        </span>
    </span>

</div>
