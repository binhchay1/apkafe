<?php

/**
 * The template for displaying product content within loops.
 *
 * Override this template by copying it to yourtheme/woocommerce/content-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.6.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $product;
?>
<li <?php post_class(); ?>>

	<?php do_action('woocommerce_before_shop_loop_item'); ?>

	<?php

	$icon = get_post_meta(get_the_ID(), 'app-icon', true);
	?>
	<div class="item-content <?php if ($icon) { ?> has-icon <?php } ?>">
		<?php if ($icon) {
			if ($icon_id = ia_get_attachment_id_from_url($icon)) {
				$thumbnail = wp_get_attachment_image_src($icon_id, 'thumbnail', true);
				$icon = isset($thumbnail[0]) ? $thumbnail[0] : $icon;
			}
		?>
			<div class="app-icon">
				<a href="<?php the_permalink(get_the_ID()) ?>" title="<?php the_title_attribute() ?>">
					<img src="<?php echo esc_url($icon); ?>" alt="<?php the_title_attribute(); ?>" width="60" height="60" />
				</a>
			</div>
		<?php } ?>
		<p class="product-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute() ?>" class="main-color-1-hover"><?php the_title(); ?></a></p>
		<?php

		do_action('woocommerce_after_shop_loop_item_title');
		?>
	</div>

</li>