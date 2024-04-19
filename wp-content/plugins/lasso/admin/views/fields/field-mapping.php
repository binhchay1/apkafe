<?php

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;
use Lasso\Classes\Setting_Enum as Lasso_Setting_Enum;

use Lasso\Libraries\Lasso_URL;
use Lasso\Libraries\Table\Table_Field_Group_Detail;

use Lasso\Models\Fields;
use Lasso\Models\Fields as Model_Field;

/** @var Table_Field_Group_Detail $table_field_mapping */
/** @var bool   $is_show_title */
/** @var bool   $is_show_rating_score */
/** @var bool   $is_show_field_name */
/** @var Lasso\Models\Table_Details $table */
/** @var string $final_primary_url */
/** @var integer $link_id */

if ( ! isset( $is_show_rating_score ) ) {
	$is_show_rating_score = true;
}
if ( ! isset( $is_show_field_name ) ) {
	$is_show_field_name = true;
}

if ( ! $table_field_mapping ) {
	return;
}

$field 				              = Fields::get_by_id( $table_field_mapping->get_field_id() );
$lasso_url 			              = Lasso_URL::get_by_lasso_id( $table_field_mapping->get_lasso_id() );
$is_horizontal                    = Lasso_Setting_Enum::TABLE_STYLE_ROW === $table->get_style();
$is_show_field_name_in_horizontal = $table->is_show_field_name_comparison_table();
$is_primary_rating                = $field->is_type_rating() && ( $field->get_field_name() === 'Primary Rating' );
$show_field_name                  = $table->get_show_field_name();

if ( ! $field ) {
	return;
}
$image_alt = $lasso_url->name;
?>

<!-- Lasso Rating field -->
<?php if ( $field->is_type_rating() ) : ?>
	<?php $rate_value = ! empty( $table_field_mapping->get_field_value() ) ? $table_field_mapping->get_field_value() : 3.5 ?>
	<?php
		if ( ( ! $is_primary_rating && ! $table->get_show_headers_horizontal() ) || $show_field_name ) {
			echo Lasso_Html_Helper::get_html_field_name_comparison_table( $field->get_field_name() );
		}
	?>
	<div data-tooltip="<?php echo $field->get_field_name(); ?>">
		<div class="lasso-stars" style="--rating: <?php echo $rate_value ?>">
			<?php if ( $is_show_rating_score ) : ?>
			<span class="lasso-stars-value"><?php echo Lasso_Helper::show_decimal_field_rate( $rate_value ); ?></span>
			<?php endif; ?>
		</div>
	</div>
<!-- Lasso other fields -->
<?php else: ?>
	<div class="lasso-fields">
		<?php if ( $field->is_product_name() ) { ?>
			<?php if ($lasso_url->link_from_display_title) : ?>
				<a class="product-name lasso-title" <?php echo $lasso_url->render_attributes( $final_primary_url ) ?> ><?php echo $table_field_mapping->get_field_value() ?></a>
			<?php else: ?>
				<h3 class="product-name lasso-title"><?php echo $table_field_mapping->get_field_value() ?></h3>
			<?php endif; ?>
			<?php if( ! $table->has_field( Fields::PRICE_ID ) && $lasso_url->amazon->is_prime  ): ?>
				<i class="lasso-amazon-prime"></i>
			<?php endif; ?>
		<?php } elseif ( $field->is_type_button() || $field->is_type_image() ) { ?>
			<?php if ( $field->is_type_image() ) : ?>
				<!-- Image field -->
				<div class="img-container">
					<a <?php echo $lasso_url->render_attributes( $final_primary_url ); ?>>
						<img src="<?php echo $table_field_mapping->get_image() ?>" <?php echo Lasso_Html_Helper::build_img_lazyload_attributes() ?> alt="<?php echo $image_alt; ?>"/>
					</a>
				</div>
			<?php elseif ( $field->is_button() ): ?>
				<?php $field_value = json_decode( $table_field_mapping->get_field_value() ); ?>
				<!-- Primary button field -->
				<div class="btn-container">
					<a class="btn lasso-button-1" <?php echo $lasso_url->render_attributes( $lasso_url->public_link ) ?>>
						<?php echo $field_value->button_text ?>
					</a>
				</div>
			<?php elseif ( $field->is_button( false ) ): ?>
				<?php $field_value = json_decode( $table_field_mapping->get_field_value() ); ?>
				<!-- Secondary button field -->
				<div class="btn-container">
					<?php if ( ! empty( $field_value->url ) ) : ?>
						<a class="btn lasso-button-2" <?php echo $lasso_url->render_attributes_second( $field_value->url ) ?>>
							<?php echo $field_value->button_text ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php } elseif ( in_array( $field->get_id(), array(Fields::PROS_FIELD_ID, Fields::CONS_FIELD_ID) ) ) { ?>
			<!-- Pros / Cons field -->
			<div class="lasso-fields-<?php echo sanitize_title( $field->get_field_name() ); ?> lasso-fields-<?php echo $field->get_id(); ?>">
				<?php if ( $is_show_field_name_in_horizontal ) {
					echo Lasso_Html_Helper::get_html_field_name_comparison_table( $field->get_field_name() );
				}
				?>
                <?php echo Lasso_Html_Helper::render_pros_cons_field( $field->get_id(), $table_field_mapping->get_field_value() ); ?>
			</div>
        <?php } elseif ( $field->is_description_field() ) { ?>
            <!-- Description field -->
            <div class="description-content">
                <?php
                    $description_content = ! empty( $table_field_mapping->get_field_value() ) ? $table_field_mapping->get_field_value() : '';
                ?>
                <div class="lasso-fields-single">
					<?php if ( $is_show_field_name_in_horizontal ) {
						echo Lasso_Html_Helper::get_html_field_name_comparison_table( $field->get_field_name() );
					}
					?>
                    <div class="field-value">
                        <span><?php echo $description_content; ?></span>
                    </div>
                </div>
            </div>
		<?php } elseif ( in_array( $field->get_field_type(), array( Model_Field::FIELD_TYPE_BULLETED_LIST, Model_Field::FIELD_TYPE_NUMBERED_LIST ) ) ) { ?><!-- List field -->
			<!-- List fields: bulleted and numbered -->
			<div class="lasso-fields-<?php echo sanitize_title( $field->get_field_name() ); ?> lasso-fields-<?php echo $field->get_id(); ?>">
				<?php if ( $is_show_field_name_in_horizontal ) {
					echo Lasso_Html_Helper::get_html_field_name_comparison_table( $field->get_field_name() );
				}
				?>
				<?php echo Lasso_Html_Helper::render_list_field( $field->get_field_type(), $table_field_mapping->get_field_value() ); ?>
			</div>
		<?php } elseif ( ( $field->is_type_label() && $field->is_price_field() ) ) { ?>
            <!-- Price field -->
			<?php if ( ! $is_horizontal ) {
				echo Lasso_Html_Helper::get_html_field_name_comparison_table( $field->get_field_name() );
			}

			$is_amazon_product = $lasso_url->amazon->amazon_id;
			$price_value = $is_amazon_product ? $lasso_url->price : $table_field_mapping->get_field_value(); 
			$price_value = $price_value ? $price_value : 'N/A';
			?>
			<div class="lasso-price">
				<?php $discount_price_html = $lasso_url->amazon->show_discount_pricing ? $lasso_url->amazon->discount_pricing_html : ''; ?>
				<div class="lasso-price-value"><span class="discount-price"><?php echo $discount_price_html; ?></span><span class="latest-price"><?php echo $price_value; ?></span></div>
				<?php if( $lasso_url->amazon->is_prime ): ?>
					<i class="lasso-amazon-prime"></i>
				<?php endif; ?>
			</div>
		<?php } else { ?>
			<!-- Custom fields -->
			<div class="lasso-fields-single">
				<?php if ( $is_show_field_name_in_horizontal ) {
					echo Lasso_Html_Helper::get_html_field_name_comparison_table( $field->get_field_name() );
				}
				?>
				<div class="field-value">
					<?php
					$class = '';
					if ( Lasso_Helper::compare_string( $table_field_mapping->get_field_value(), "Yes" ) ) {
						$class = 'green';
					} elseif ( Lasso_Helper::compare_string( $table_field_mapping->get_field_value(), "No" ) )  {
						$class = 'red';
					}
					?>
					<span class="<?php echo $class ?>"><?php echo nl2br($table_field_mapping->get_field_value()); ?></span>
				</div>
			</div>
		<?php } ?>
	</div>
<?php endif;?>
