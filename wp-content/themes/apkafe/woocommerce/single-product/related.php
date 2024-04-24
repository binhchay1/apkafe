<?php

/**
 * Related Products
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.9.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>

<div class="app-store-link" id="app-store-link">
	<?php if ($apple = get_post_meta(get_the_ID(), 'store-link-apple', true)) { ?>
		<a class="btn btn-default btn-store btn-store-apple col-mid-12" href="<?php echo esc_url($apple) ?>" target="_blank">
			<i class="fa fa-apple"></i>
			<div class="btn-store-text">
				<span><?php _e("Download from", "leafcolor") ?></span><br />
				<?php _e("APP STORE", "leafcolor") ?>
			</div>
		</a>
	<?php } //if apple 
	?>
	<?php if ($google = get_post_meta(get_the_ID(), 'store-link-google', true)) { ?>
		<a class="btn btn-default btn-store btn-store-google" href="<?php echo esc_url($google) ?>" target="_blank">
			<i class="fa fa-google"></i>
			<div class="btn-store-text">
				<span><?php _e("Download from", "leafcolor") ?></span><br />
				<?php _e("PLAY STORE", "leafcolor") ?>
			</div>
		</a>
	<?php } //if google 
	?>
	<?php if ($windows = get_post_meta(get_the_ID(), 'store-link-windows', true)) { ?>
		<a class="btn btn-default btn-store btn-store-windows" href="<?php echo esc_url($windows) ?>" target="_blank">
			<i class="fa fa-windows"></i>
			<div class="btn-store-text">
				<span><?php _e("Download from", "leafcolor") ?></span><br />
				<?php _e("WINDOWS STORE", "leafcolor") ?>
			</div>
		</a>
	<?php } //if windows 
	?>
	<?php if (get_post_meta(get_the_ID(), 'app-port-file', true) && is_singular('app_portfolio')) { ?>
		<a class="btn btn-default btn-store btn-store-file" href="<?php echo esc_url(get_post_meta(get_the_ID(), 'app-port-file', true)) ?>" target="_blank">
			<i class="fa fa-download"></i>
			<div class="btn-store-text">
				<span><?php _e("Download", "leafcolor") ?></span><br />
				<?php _e("INSTALLATION FILE", "leafcolor") ?>
			</div>
		</a>
	<?php } //if file 
	?>

	<?php if ($links = get_post_meta(get_the_ID(), 'app-custom-link', true)) {
		foreach ($links as $link) { ?>
			<a class="btn btn-default btn-store btn-store-link" href="<?php echo esc_url($link['url']) ?>" target="_blank">
				<i class="fa <?php echo esc_attr($link['icon']) ?>"></i>
				<div class="btn-store-text">
					<span><?php echo esc_attr($link['download_text']) ?></span><br />
					<?php echo esc_attr($link['title']) ?>
				</div>
			</a>
	<?php }/*foreach*/
	} //if links 
	?>

</div>

<?php
global $product, $woocommerce_loop;

if (function_exists('wc_get_related_products')) {
	$related = wc_get_related_products($product->get_id(), 3);
} else {
	$related = $product->get_related($posts_per_page);
} //print_r($related);exit;
if (!$related) {
	return;
}


$args = apply_filters('woocommerce_related_products_args', array(
	'post_type'            => 'product',
	'ignore_sticky_posts'  => 1,
	'no_found_rows'        => 1,
	'posts_per_page'       => $posts_per_page,
	'orderby'              => $orderby,
	'post__in'             => $related,
	'post__not_in'         => array($product->get_id())
));

$products = new WP_Query($args);

$woocommerce_loop['columns'] = $columns;

if ($products->have_posts()) : ?>

	<div class="related-product">

		<h3><?php _e('Related Products', 'woocommerce'); ?></h3>
		<div class="ev-content">
			<?php woocommerce_product_loop_start();
			$i = 0;
			?>

			<?php while ($products->have_posts()) : $products->the_post();
				$i++;
			?>
				<li class="col-sm-4 related-item">
					<?php if (has_post_thumbnail(get_the_ID())) { ?>
						<div class="thumb"><a href="<?php echo esc_url(get_permalink(get_the_ID())) ?>"><?php echo get_the_post_thumbnail(get_the_ID(), 'thumb_80x80'); ?></a></div>
					<?php } ?>
					<div class="item-content"> <a class="main-color-1-hover" href="<?php echo esc_url(get_permalink(get_the_ID())) ?>"><?php echo get_the_title(get_the_ID()); ?></a></div><br />
					<div><?php do_action('woocommerce_after_shop_loop_item_title'); ?></div>
					<div class="clear"></div>
				</li>
			<?php endwhile; // end of the loop. 
			?>

			<?php woocommerce_product_loop_end(); ?>
		</div>
	</div>

<?php endif;

wp_reset_postdata();
