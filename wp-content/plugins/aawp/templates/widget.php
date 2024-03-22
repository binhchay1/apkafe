<?php
/**
 * Standard Widget Loop template
 *
 * Including single product templates inside the loop
 *
 * @var AAWP_Template_Functions $this
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

?>

<div class="aawp aawp-widget">

    <?php foreach ( $this->items as $i => $item ) : ?>
        <?php $this->setup_item($i, $item); ?>

            <?php $this->the_product_template(); ?>

    <?php endforeach; ?>

</div>
