<?php
/**
 * Simple product add to cart
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     7.0.1
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

echo wc_get_stock_html( $product ); // WPCS: XSS ok.

$woo_listing_mode = get_post_meta(get_the_ID(),'product-mode',true);
if($woo_listing_mode==''){
	$woo_listing_mode = ot_get_option('woo_listing_mode');
}

if ( $product->is_in_stock() ) : ?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<?php if($woo_listing_mode!='on'){ ?>
			<?php do_action( 'woocommerce_before_add_to_cart_quantity' );

			woocommerce_quantity_input( array(
				'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
				'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
				'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
			) );

			do_action( 'woocommerce_after_add_to_cart_quantity' ); ?>

		 	<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt">
		 		<i class="fa fa-shopping-cart"></i> <?php echo esc_html( $product->single_add_to_cart_text() ); ?>
		 	</button>

		<?php }else{
			$app_link = get_post_meta(get_the_ID(),'store-link-apple',true);
			$gg_link = get_post_meta(get_the_ID(),'store-link-google',true);
			$win_link = get_post_meta(get_the_ID(),'store-link-windows',true);
			if(($app_link =='' && $gg_link =='')){
				$link_dl = $win_link;
			}else if(($app_link =='' && $win_link =='')){
				$link_dl = $gg_link;
			}else if(($gg_link =='' && $win_link =='')){
				$link_dl = $app_link;
			}else{
				$link_dl = '#top';
			}
		?>
        	<a class="single_add_to_cart_button btn btn-primary btn-lg btn-block" href="<?php echo esc_url($link_dl);?>" <?php if($link_dl != '#top'){?>  target="_blank" <?php }?>><i class="fa fa-download"></i> <?php _e('Download now','leafcolor'); ?></a>
        <?php } ?>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>