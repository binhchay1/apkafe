<?php
/**
 * Admin search results
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

if ( ! isset( $products ) || ! is_array( $products ) )
    return;

?>
<div class="aawp-ajax-search-items">
    <?php foreach( $products as $Product ) { ?>

        <?php
        /** @var Flowdee\AmazonPAAPI5WP\Item $Product The product class from our Amazon API vendor */

        $product_asin = $Product->getASIN();
        $product_title = $Product->getTitle();
        $product_image = $Product->getImage( 'primary', 'small' );

        if ( empty( $product_asin ) || ( empty( $product_title )) || empty( $product_image['url'] ) )
            continue;

        $product_price = $Product->getPrice( 'display' );
        $product_is_prime = $Product->isPrime();
        ?>

        <div class="aawp-ajax-search-item" data-aawp-ajax-search-item="<?php echo $product_asin; ?>">
            <span class="aawp-ajax-search-item__thumb">
                <img src="<?php echo $product_image['url']; ?>" alt="thumbnail" />
            </span>
            <span class="aawp-ajax-search-item__content">
                <span class="aawp-ajax-search-item__title">
                <?php echo aawp_truncate_string( $product_title, 40 ); ?>
            </span>
            <?php if ( ! empty( $product_price ) ) { ?>
                <span class="aawp-ajax-search-item__price">
                    <?php echo $product_price; ?>
                    <?php if ( $product_is_prime ) { ?>
                        <span class="aawp-check-prime"></span>
                    <?php } ?>
                </span>
            <?php } ?>
        </div>
    <?php } ?>
</div>


