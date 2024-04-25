<?php
/**
 * Loop Rating
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product;
?>
<?php 
$type = $product->get_type();
if($type=='variable'){
	$price_html = $product->get_price(); ?>
		<span class="price"><?php _e('From  ','leafcolor') ?><?php  echo get_woocommerce_currency_symbol(); echo wp_kses_post($price_html); ?></span>
	<?php 
}else{
	if ( $price_html = $product->get_price_html() ) : ?>
		<span class="price"><?php echo wp_kses_post($price_html); ?></span>
	<?php endif; 	
}
?>

<?php
if ( get_option( 'woocommerce_enable_review_rating' ) === 'no' )
	return;
?>

<?php 
if(function_exists('wc_get_rating_html')){
	$rating_html = wc_get_rating_html($product->get_average_rating());
}else{
	$rating_html = $product->get_rating_html();
}

if ( $rating_html ) : ?>
	<?php echo wp_kses_post($rating_html); ?>
<?php endif; ?>