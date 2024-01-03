<?php
/**
 * Table builder template
 *
 * Rendering our comparison tables
 *
 * @var AAWP_Template_Functions $this
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

global $aawp_table;

if ( ! isset( $aawp_table['id'] ) )
    return;

if ( ! isset( $aawp_table['rows'] ) || ! is_array( $aawp_table['rows'] ) )
    return;

if ( ! isset( $aawp_table['products'] ) || ! is_array( $aawp_table['products'] ) )
    return;
?>

<div class="aawp">

    <div id="aawp-tb-<?php echo $aawp_table['id']; ?>">

        <!-- Desktop -->
        <div class="aawp-tb aawp-tb--desktop aawp-tb--cols-<?php echo ( sizeof( $aawp_table['products'] ) + 1 ); ?><?php aawp_the_table_customization_classes( 'aawp-tb' ); ?>">

            <?php foreach ( $aawp_table['rows'] as $table_row_id => $table_row ) : ?>

                <?php if ( ! $table_row['status'] )
                    continue; ?>

                <div class="aawp-tb__row<?php if ( $table_row['highlight'] ) echo ' aawp-tb__row--highlight'; ?>">

                    <div class="aawp-tb__head">
                        <?php echo ( isset( $table_row['label'] ) ) ? $table_row['label'] : ''; ?>
                    </div>

                    <?php foreach ( $aawp_table['products'] as $table_product_id => $table_product ) : ?>

                        <?php if ( empty ( $table_product['asin'] ) )
                            continue; ?>

                        <?php if ( $table_product['rows'][$table_row_id] ) : ?>

                            <div class="aawp-tb-product-<?php echo $table_product_id; ?> <?php aawp_the_table_product_data_classes( 'aawp-tb__data', $table_row_id, $table_product_id ); ?><?php if ( $table_product['highlight'] ) echo ' aawp-tb__data--highlight'; ?>">
                                <?php aawp_the_table_product_highlight_ribbon( $table_product_id, $table_row_id ); ?>
                                <?php aawp_the_table_product_data( $table_row_id, $table_product_id ); ?>
                            </div>

                        <?php endif; ?>

                    <?php endforeach; ?>

                </div>

            <?php endforeach; ?>
        </div>

        <!-- Mobile -->
        <div class="aawp-tb aawp-tb--mobile<?php aawp_the_table_customization_classes( 'aawp-tb' ); ?>">

            <?php foreach ( $aawp_table['products'] as $table_product_id => $table_product ) : ?>

                <div class="aawp-tb__product aawp-tb-product-<?php echo $table_product_id; ?><?php if ( $table_product['highlight'] ) echo ' aawp-tb__product--highlight'; ?>">

                    <?php aawp_the_table_product_highlight_ribbon( $table_product_id ); ?>

                    <?php foreach ( $aawp_table['rows'] as $table_row_id => $table_row ) : ?>

                        <?php if ( ! $table_row['status'] )
                            continue; ?>

                        <div class="aawp-tb__row<?php if ( $table_row['highlight'] ) echo ' aawp-tb__row--highlight'; ?>">

                            <div class="aawp-tb__head">
                                <?php echo ( isset( $table_row['label'] ) ) ? $table_row['label'] : ''; ?>
                            </div>

                            <?php if ( $table_product['rows'][$table_row_id] ) : ?>

                                <div class="<?php aawp_the_table_product_data_classes( 'aawp-tb__data', $table_row_id, $table_product_id ); ?>">
                                    <?php aawp_the_table_product_data( $table_row_id, $table_product_id ); ?>
                                </div>

                            <?php endif; ?>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</div>

