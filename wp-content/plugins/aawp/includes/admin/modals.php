<?php
/**
 * Admin Modals
 *
 * @since       3.5.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Output the modal header html
 *
 * @param $id
 * @param $title
 */
function aawp_admin_the_modal_header( $id, $title ) {

    ?>

    <div id="aawp-modal-<?php echo $id; ?>" class="aawp-modal lity-hide">

        <div class="aawp-modal__header">
            <div class="aawp-modal__title"><?php echo $title; ?></div>
            <span class="aawp-modal__close" data-aawp-close-modal="true"><span class="dashicons dashicons-no"></span></span>
        </div>

        <div class="aawp-modal__content">
    <?php
}

/**
 * Output the modal footer html
 */
function aawp_admin_the_modal_footer() {

    ?>
        </div><!-- .aawp-modal__content -->
        <div class="aawp-modal__footer">
            <span class="aawp-brand-icon"></span>
            <span class="button aawp-modal__button" data-aawp-close-modal="true"><?php _e('Close', 'aawp' ); ?></span>
        </div>
    </div><!-- .aawp-modal -->
    <?php
}

/**
 * Output the modal opener link html
 *
 * @param $id
 * @param $text
 * @param string $classes
 */
function aawp_admin_the_modal_link( $id, $text, $classes = '' ) {

    ?>
    <a href="#aawp-modal-<?php echo $id; ?>" class="aawp-modal-link <?php echo $classes; ?>" data-aawp-modal="true"><?php echo $text; ?></a>
    <?php
}