<?php
/**
 * Grid template
 *
 * Including single product templates inside the loop
 *
 * @var AAWP_Template_Functions $this
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

?>

<div class="aawp">
    <div class="<?php echo $this->get_wrapper_classes('aawp-grid'); ?>">

    <?php foreach ( $this->items as $i => $item ) : ?>
        <?php $this->setup_item($i, $item); ?>

        <div class="aawp-grid__item">
            <?php $this->the_product_template(); ?>
        </div>

    <?php endforeach; ?>

    </div>
</div>
