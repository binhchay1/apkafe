<?php
/*
 * Box table template
 * ------------
 *
 * @package AAWP
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

?>
<p class="aawp-responsive-table-note"><?php _e('Please swipe over the table to view more.', 'aawp'); ?></p>
<div class="aawp aawp-responsive-table">
    <table class="aawp aawp-table">
        <thead>
            <tr>
                <th><?php _e('Preview', 'aawp'); ?></th>
                <th class="title"><?php _e('Product', 'aawp'); ?></th>
                <th><?php _e('Rating', 'aawp'); ?></th>
                <?php if ( $this->show_advertised_price() ) { ?>
                    <th><?php _e('Price', 'aawp'); ?></th>
                <?php } ?>
                <th></th>
            </tr>
        </thead>
        <tbody>

        <?php foreach ( $this->items as $i => $item ) : ?>
            <?php $this->setup_item($i, $item); ?>

            <tr <?php $this->the_product_container(); ?>>
                <td class="thumb">
                    <a href="<?php echo $this->get_product_image_link(); ?>" title="<?php echo $this->get_product_image_link_title(); ?>" rel="nofollow noopener sponsored" target="_blank">
                        <img src="<?php echo $this->get_product_image('small'); ?>" alt="<?php echo $this->get_product_image_alt(); ?>" <?php $this->the_product_image_title(); ?> />
                    </a>
                </td>
                <td class="title">
                    <a href="<?php echo $this->get_product_url(); ?>" title="<?php echo $this->get_product_link_title(); ?>"
                       rel="nofollow noopener sponsored" target="_blank"><?php echo $this->get_product_title(); ?>
                    </a>
                </td>
                <td class="rating"><?php echo ( $this->get_product_rating() ) ? $this->get_product_star_rating() : __('No ratings yet', 'aawp'); ?></td>
                <?php if ( $this->show_advertised_price() ) { ?>
                    <td class="price"><?php echo $this->get_product_pricing(); ?></td>
                <?php } ?>
                <td class="links">
                    <?php echo $this->get_button(); ?>
                </td>
            </tr>

        <?php endforeach; ?>
        </tbody>
    </table>
</div>