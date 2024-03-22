<?php
/**
 * Table template
 *
 * @var AAWP_Template_Functions $this
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

?>

<div class="aawp">

    <table class="<?php echo $this->get_wrapper_classes('aawp-table'); ?>">
        <thead>
            <tr>
                <?php if ( $this->show_product_numbering() ) { ?>
                    <th class="aawp-table__th-position">#</th>
                <?php } ?>
                <th class="aawp-table__th-thumb"><?php _e('Preview', 'aawp'); ?></th>
                <th class="aawp-table__th-title"><?php _e('Product', 'aawp'); ?></th>
                <?php if ( $this->show_star_rating() ) { ?>
                    <th class="aawp-table__th-rating"><?php _e('Rating', 'aawp'); ?></th>
                <?php } ?>
                <?php if ( $this->show_advertised_price() ) { ?>
                    <th class="aawp-table__th-pricing"><?php _e('Price', 'aawp'); ?></th>
                <?php } ?>
                <th class="aawp-table__th-links"></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $this->items as $i => $item ) : ?>
            <?php $this->setup_item($i, $item); ?>

            <tr class="<?php echo $this->get_product_container_classes('aawp-product'); ?>" <?php $this->the_product_container(); ?>>
                <?php if ( $this->show_product_numbering() ) { ?>
                    <td class="aawp-table__td-position" data-label="<?php echo $this->get_product_numbering_label(); ?>"><?php echo $this->get_product_numbering(); ?></td>
                <?php } ?>
                <td class="aawp-table__td-thumb" data-label="<?php _e('Preview', 'aawp'); ?>">
                    <a href="<?php echo $this->get_product_image_link(); ?>" title="<?php echo $this->get_product_image_link_title(); ?>" rel="nofollow noopener sponsored" target="_blank">
                        <img class="aawp-product__img" src="<?php echo $this->get_product_image(); ?>" alt="<?php echo $this->get_product_image_alt(); ?>" <?php $this->the_product_image_title(); ?> />
                    </a>
                </td>
                <td class="aawp-table__td-title" data-label="<?php _e('Product', 'aawp'); ?>">
                    <a class="aawp-product__title" href="<?php echo $this->get_product_url(); ?>" title="<?php echo $this->get_product_link_title(); ?>"
                       rel="nofollow noopener sponsored" target="_blank"><?php echo $this->truncate( $this->get_product_title(), 100 ); ?>
                    </a>
                </td>
                <?php if ( $this->show_star_rating() ) { ?>
                    <td class="aawp-table__td-rating" data-label="<?php _e('Rating', 'aawp'); ?>">
                        <?php if ( $this->get_product_rating() ) { ?>
                            <span class="aawp-product__rating"><?php echo $this->get_product_star_rating(); ?></span>
                        <?php } else { ?>
                            <span class="aawp-product__no-rating"><?php _e('No ratings yet', 'aawp'); ?></span>
                        <?php } ?>
                        <?php if ( $this->get_product_reviews() ) { ?>
                            <span class="aawp-product__reviews"><?php echo $this->get_product_reviews(); ?></span>
                        <?php } ?>
                    </td>
                <?php } ?>
                <?php if ( $this->show_advertised_price() ) { ?>
                    <td class="aawp-table__td-pricing" data-label="<?php _e('Price', 'aawp'); ?>">
                        <?php if ( $this->product_is_on_sale() ) { ?>
                            <?php if ( $this->sale_show_old_price() ) { ?>
                                <span class="aawp-product__price aawp-product__price--old"><?php echo $this->get_product_pricing('old'); ?></span>
                            <?php } ?>
                        <?php } ?>
                        <span class="aawp-product__price"><?php echo $this->get_product_pricing(); ?></span><?php $this->the_product_check_prime_logo(); ?>
                    </td>
                <?php } ?>
                <td class="aawp-table__td-links" data-label="<?php _e('Link', 'aawp'); ?>">
                    <?php echo $this->get_button(); ?>
                </td>
            </tr>

        <?php endforeach; ?>
        </tbody>
    </table>

</div>
