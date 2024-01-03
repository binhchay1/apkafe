<?php
/**
 * Comparison template
 *
 * @var AAWP_Template_Functions $this
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

?>

<div class="aawp">

    <table class="<?php echo $this->get_wrapper_classes('aawp-comparison-table'); ?>">
        <tbody>
            <!-- Thumbnail -->
            <tr class="<?php echo $this->get_product_container_classes('aawp-product'); ?>" <?php $this->the_product_container(); ?>>
                <th class="thumb" data-label="<?php _e('Preview', 'aawp'); ?>"><?php _e('Preview', 'aawp'); ?></th>

                <?php foreach ( $this->items as $i => $item ) : ?>
                    <?php $this->setup_item($i, $item); ?>

                    <td class="thumb" data-label="<?php _e('Preview', 'aawp'); ?>">
                        <a href="<?php echo $this->get_product_image_link(); ?>" title="<?php echo $this->get_product_image_link_title(); ?>" rel="nofollow noopener sponsored" target="_blank">
                            <img src="<?php echo $this->get_product_image(); ?>" alt="<?php echo $this->get_product_image_alt(); ?>" <?php $this->the_product_image_title(); ?> />
                        </a>
                    </td>
                <?php endforeach; ?>
            </tr>

            <!-- Product -->
            <tr class="<?php echo $this->get_product_container_classes('aawp-product'); ?>" <?php $this->the_product_container(); ?>>
                <th class="title" data-label="<?php _e('Product', 'aawp'); ?>"><?php _e('Product', 'aawp'); ?></th>

                <?php foreach ( $this->items as $i => $item ) : ?>
                    <?php $this->setup_item($i, $item); ?>

                    <td class="title" data-label="<?php _e('Product', 'aawp'); ?>">
                        <a href="<?php echo $this->get_product_url(); ?>" title="<?php echo $this->get_product_link_title(); ?>"
                           rel="nofollow noopener sponsored" target="_blank"><?php echo $this->truncate( $this->get_product_title(), 50 ); ?>
                        </a>
                    </td>
                <?php endforeach; ?>
            </tr>

            <!-- Customer Rating -->
            <?php if ( $this->show_star_rating() ) { ?>
                <tr class="<?php echo $this->get_product_container_classes('aawp-product'); ?>" <?php $this->the_product_container(); ?>>
                    <th class="rating" data-label="<?php _e('Customer Rating', 'aawp'); ?>"><?php _e('Customer Rating', 'aawp'); ?></th>

                    <?php foreach ( $this->items as $i => $item ) : ?>
                        <?php $this->setup_item($i, $item); ?>

                            <td class="rating" data-label="<?php _e('Customer Rating', 'aawp'); ?>">
                                <?php echo ( $this->get_product_rating() ) ? $this->get_product_star_rating() : __('No ratings yet', 'aawp'); ?>
                                <?php if ( $this->get_product_reviews() ) { ?>
                                    <span class="reviews"><?php echo $this->get_product_reviews(); ?></span>
                                <?php } ?>
                            </td>
                    <?php endforeach; ?>
                </tr>
            <?php } ?>

            <!-- Pricing -->
            <?php if ( $this->show_advertised_price() ) { ?>
                <tr class="<?php echo $this->get_product_container_classes('aawp-product'); ?>" <?php $this->the_product_container(); ?>>
                    <th class="pricing" data-label="<?php _e('Price', 'aawp'); ?>"><?php _e('Price', 'aawp'); ?></th>

                    <?php foreach ( $this->items as $i => $item ) : ?>
                        <?php $this->setup_item($i, $item); ?>

                        <td class="pricing" data-label="<?php _e('Price', 'aawp'); ?>">
                            <?php if ( $this->get_product_is_sale() && $this->sale_show_old_price() ) { ?>
                                <span class="price price--old">
                                    <?php echo $this->get_product_pricing('old'); ?>
                                </span>
                            <?php } ?>
                            <span class="price price--current"><?php echo $this->get_product_pricing(); ?></span>
                            <span class="price price--prime">
                                <?php $this->the_product_check_prime_logo(); ?>
                            </span>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php } ?>

            <!-- Links -->
            <tr class="<?php echo $this->get_product_container_classes('aawp-product'); ?>" <?php $this->the_product_container(); ?>>
                <th class="links" data-label="<?php _e('Link', 'aawp'); ?>">&nbsp;</th>

                <?php foreach ( $this->items as $i => $item ) : ?>
                    <?php $this->setup_item($i, $item); ?>

                    <td class="links" data-label="<?php _e('Link', 'aawp'); ?>">
                        <?php echo $this->get_button(); ?>
                    </td>
                <?php endforeach; ?>
            </tr>

        </tbody>
    </table>

</div>
