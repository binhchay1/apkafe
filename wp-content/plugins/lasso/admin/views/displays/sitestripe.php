<?php
/** @var bool $is_show_description */

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;

/** @var bool $is_show_disclosure */
/** @var bool $is_show_fields */
/** @var string $type */

?>
<div <?php echo $anchor_id_html ?> class="lasso-container sitestripe">
    <!-- LASSO DISPLAY BOX (https://getlasso.co) -->
	<div class="lasso-display sitestripe <?php echo 'lasso-url-' . $lasso_url->slug; ?>" style="<?php echo $sitestripe_style; ?>">
		<!-- BUTTONS -->
		<div class="lasso-box-2">
			<a class="lasso-image" <?php echo $lasso_url_obj->render_attributes($lasso_url->title_url) ?>>
				<img src="<?php echo $lasso_url->image_src; ?>" height="500" width="500" <?php echo Lasso_Html_Helper::build_img_lazyload_attributes() ?> alt="<?php echo $image_alt; ?>">
			</a>

			<?php if ( $lasso_post->is_show_title() ) : ?>
				<?php echo $title_type_start; ?>
				<?php if ( $lasso_url->link_from_display_title ): ?>
					<a class="lasso-title" <?php echo $lasso_url_obj->render_attributes( $lasso_url->title_url ) ?>>
						<?php echo html_entity_decode( $lasso_url->name ); ?>
					</a>
				<?php else: ?>
					<span class="lasso-title"><?php echo html_entity_decode( $lasso_url->name ); ?></span>
				<?php endif; ?>
				<?php echo $title_type_end; ?>
			<?php endif; ?>

			<?php if ( ( '' !== $lasso_url->price && $lasso_url->display->show_price ) || $lasso_url->amazon->is_prime ) { ?>
				<div class="lasso-price">
					<div class="lasso-price-value">
					<?php if ( '' !== $lasso_url->price && $lasso_url->display->show_price ) { ?>
						<?php $discount_price_html = $lasso_url->amazon->show_discount_pricing ? $lasso_url->amazon->discount_pricing_html : ''; ?>
							<span class="discount-price" title="<?php echo $discount_price_html; ?>"><?php echo $discount_price_html; ?></span>
							<span class="latest-price" title="<?php echo $lasso_url->price; ?>"><?php echo $lasso_url->price; ?></span>
						<?php } ?>
					</div>
				</div>
            <?php } ?>
            <?php if ( $lasso_url->amazon->is_prime ) { ?>
                <span><i class="lasso-amazon-prime"></i></span>
            <?php } ?>
			<div class="clear"></div>

			<a class="lasso-button-1" <?php echo $lasso_url_obj->render_attributes() ?>>
				<?php echo $lasso_url->display->primary_button_text; ?>
			</a>		
		</div>
	</div>
</div>
