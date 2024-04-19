<?php

use Lasso\Libraries\Lasso_URL;
use Lasso\Classes\Helper as Lasso_Helper;

$lasso_url_obj = new Lasso_URL( $lasso_url );
?>
<li>
<div class="lasso-container lasso-list <?php echo $theme_name . ' lasso-url-' . $lasso_url->slug; ?> <?php echo strtolower($title_type); ?>">
    <!-- LASSO LIST BOX (https://getlasso.co) -->
	<?php if ( '' !== $lasso_url->name ) { ?>
		<div class="ls-list-title">
		<?php echo $title_type_start; ?>
		<?php if ( $lasso_url->link_from_display_title ): ?>
			<a class="lasso-title" <?php echo $lasso_url_obj->render_attributes( $lasso_url->title_url ) ?>>
				<?php echo html_entity_decode( $lasso_url->name ); ?>
			</a>
		<?php else: ?>
			<span class="lasso-title"><?php echo html_entity_decode( $lasso_url->name ); ?></span>
		<?php endif; ?>
		<?php echo $title_type_end; ?>
		</div>
	<?php } else { ?>
		<div class="lasso-title"></div>
	<?php } ?>

	<div class="lasso-list-content">
		<a class="lasso-image" <?php echo $lasso_url_obj->render_attributes() ?>>
			<img src="<?php echo $lasso_url->image_src; ?>" loading="lazy" height="500" width="500" alt="<?php echo $lasso_url->name; ?>">
		</a>

		<?php if( $is_show_fields && $lasso_url->fields->primary_rating && ($lasso_url->fields->primary_rating->field_value != '') ) { ?>
			<div class="lasso-stars" style="--rating: <?php echo $lasso_url->fields->primary_rating->field_value; ?>">
				<span class="lasso-stars-value">
					<?php echo Lasso_Helper::show_decimal_field_rate( $lasso_url->fields->primary_rating->field_value ); ?>
				</span>
			</div>
		<?php } ?>

		<?php if ( ( '' !== $lasso_url->price && $lasso_url->display->show_price ) || $lasso_url->amazon->is_prime ) { ?>
			<div class="lasso-price">
				<?php if ( '' !== $lasso_url->price && $lasso_url->display->show_price ) { ?>
					<?php $discount_price_html = $lasso_url->amazon->show_discount_pricing ? $lasso_url->amazon->discount_pricing_html : ''; ?>
					<div class="lasso-price-value">
						<span class="discount-price"><?php echo $discount_price_html; ?></span>
						<span class="latest-price"><?php echo $lasso_url->price; ?></span></div>
				<?php } ?>
				<?php if ( $lasso_url->amazon->is_prime ) { ?>
					<i class="lasso-amazon-prime"></i>
				<?php } ?>
			</div>
		<?php } ?>

		<?php if ( ! in_array( $lasso_url->description, array( '', '<p><br></p>' ), true ) ) { ?>
		<div class="lasso-description">
			<?php echo $lasso_url->description; ?>
		</div>
		<?php } else { ?>
			<div class="lasso-description"></div>
		<?php } ?>

		<?php if( $is_show_fields && ($lasso_url->fields->user_created) ) { ?>
			<div class="lasso-fields">
				<?php foreach ($lasso_url->fields->user_created as $field) { ?>
					<?php
					Lasso_Helper::include_with_variables(
						LASSO_PLUGIN_PATH . '/admin/views/displays/field-row.php',
						array(
							'field_data' => $field
						),
						false
					);
					?>
		
				<?php } ?>		
			</div>
		<?php } ?>

		<a class="lasso-button-1" <?php echo $lasso_url_obj->render_attributes() ?>>
			<?php echo $lasso_url->display->primary_button_text; ?>
		</a>

		<?php if ( '' !== $lasso_url->display->secondary_url ) { ?>
			<a class="lasso-button-2" <?php echo $lasso_url_obj->render_attributes_second() ?>>
				<?php echo $lasso_url->display->secondary_button_text; ?>
			</a>
		<?php
			}
		?>
		<div>
			<?php if ( $is_show_disclosure ) : ?>
				<div class="lasso-disclosure">
					<?php
					if ( $lasso_url->display->show_disclosure ) {
						echo "<p>" . $lasso_url->display->disclosure_text . "</p>";
					}
					?>
				</div>
			<?php endif; ?>
			<div class="lasso-date">
				<?php
				if ( $lasso_url->display->show_date && $lasso_url->display->show_price && '' !== $lasso_url->price ) {
					echo $lasso_url->display->last_updated . ' <i class="lasso-amazon-info" data-tooltip="Price and availability are accurate as of the date and time indicated and are subject to change."></i>';
				}
				?>
			</div>
		</div>
	</div>
</div>
</li>