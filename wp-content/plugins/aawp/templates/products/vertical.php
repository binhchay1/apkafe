<?php
/**
 * Vertical template
 *
 * @var AAWP_Template_Functions $this
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

?>

<div class="<?php echo $this->get_product_container_classes('aawp-product aawp-product--vertical'); ?>" <?php $this->the_product_container(); ?>>

    <?php $this->the_product_ribbons(); ?>

    <a class="aawp-product__image--link aawp-product__image"
       href="<?php echo $this->get_product_image_link(); ?>" title="<?php echo $this->get_product_image_link_title(); ?>" rel="nofollow noopener sponsored" target="_blank">
        <img class="aawp-product__image" src="<?php echo $this->get_product_image(); ?>" alt="<?php echo $this->get_product_image_alt(); ?>" <?php $this->the_product_image_title(); ?> />
    </a>

    <div class="aawp-product__content">
        <a class="aawp-product__title" href="<?php echo $this->get_product_url(); ?>" title="<?php echo $this->get_product_link_title(); ?>" rel="nofollow noopener sponsored" target="_blank">
            <?php echo $this->truncate( $this->get_product_title(), 50 ); ?>
        </a>

        <div class="aawp-product__meta">
            <?php if ( $this->get_product_rating() ) { ?>
                <?php echo $this->get_product_star_rating( array( 'size' => 'small' ) ); ?>
                <?php if ( $this->get_product_reviews() ) { ?>
                    <span class="aawp-product__reviews">(<?php echo $this->get_product_reviews( $label = false ); ?>)</span>
                <?php } ?>
            <?php } ?>

            <?php $this->the_product_check_prime_logo(); ?>
        </div>
    </div>

    <div class="aawp-product__footer">

        <div class="aawp-product__pricing">

            <?php if ( $this->get_product_is_sale() && $this->sale_show_old_price() ) { ?>
                <span class="aawp-product__price aawp-product__price--old"><?php echo $this->get_product_pricing('old'); ?></span>
            <?php } ?>

            <?php if ( $this->show_advertised_price() ) { ?>
                <span class="aawp-product__price aawp-product__price--current"><?php echo $this->get_product_pricing(); ?></span>
            <?php } ?>
        </div>

        <?php echo $this->get_button('detail'); ?>
        <?php echo $this->get_button(); ?>

        <?php if ( $this->get_inline_info() ) { ?>
            <span class="aawp-product__info"><?php echo $this->get_inline_info_text(); ?></span>
        <?php } ?>
    </div>
</div>
