<?php
/*
 * Bestseller Widget template
 * ------------
 *
 * @package AAWP
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

?>

<?php foreach ( $this->items as $i => $item ) : ?>
    <?php $this->setup_item($i, $item); ?>

    <div class="<?php echo $this->get_classes('box', $widget = true); ?> box--widget" <?php $this->the_product_container(); ?>>

        <span class="aawp-box__ribbon aawp-box__bestseller">
            <?php echo $this->get_bestseller_text(); ?> <span class="aawp-box__bestseller-count"><?php echo $this->get_bestseller_position(); ?></span>
        </span>

        <div class="aawp-box__thumb">
            <a class="aawp-box__image-link"
               href="<?php echo $this->get_product_url(); ?>" title="<?php echo $this->get_product_image_link_title(); ?>" rel="nofollow noopener sponsored" target="_blank">
                <img class="aawp-box__image" src="<?php echo $this->get_product_image(); ?>" alt="<?php echo $this->get_product_image_alt(); ?>" />
            </a>

            <?php if ( $this->get_product_rating() ) { ?>
                <div class="aawp-box__rating">
                    <?php echo $this->get_product_star_rating(); ?>
                </div>
            <?php } ?>
        </div>

        <div class="aawp-box__content">
            <a class="aawp-box__title" href="<?php echo $this->get_product_url(); ?>" title="<?php echo $this->get_product_link_title(); ?>" rel="nofollow noopener sponsored" target="_blank">
                <?php echo $this->get_product_title(); ?>
            </a>
        </div>

        <div class="aawp-box__footer">
            <?php if ( $this->get_product_is_sale() ) { ?>
                <span class="aawp-box__ribbon aawp-box__sale">
                    <?php if ( $this->show_sale_discount() && $this->is_sale_discount_position('ribbon') ) { ?>
                        <?php echo $this->get_saved_text(); ?>
                    <?php } else { ?>
                        <?php echo $this->get_sale_text(); ?>
                    <?php } ?>
                </span>
            <?php } ?>

            <div class="aawp-box__pricing">
                <?php if ( $this->get_product_is_sale() && $this->show_sale_discount() ) { ?>
                    <span class="aawp-box__price aawp-box__price--old"><?php echo $this->get_product_pricing('old'); ?></span>
                    <?php if ( $this->is_sale_discount_position('standard') ) { ?>
                        <span class="aawp-box__price aawp-box__price--saved"><?php echo $this->get_saved_text(); ?></span>
                    <?php } ?>
                <?php } ?>

                <?php if ( $this->show_advertised_price() ) { ?>
                    <span class="aawp-box__price aawp-box__price--current"><?php echo $this->get_product_pricing(); ?></span>
                <?php } ?>

                <?php $this->the_product_check_prime_logo(); ?>
            </div>

            <?php echo $this->get_button(); ?>

            <?php if ( $this->get_inline_info() ) { ?>
                <span class="aawp-box__info"><?php echo $this->get_inline_info_text(); ?></span>
            <?php } ?>
        </div>
    </div>

<?php endforeach; ?>