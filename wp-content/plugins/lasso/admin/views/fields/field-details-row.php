<?php
use Lasso\Libraries\Field\Lasso_Object_Field;
use Lasso\Libraries\Lasso_URL;
use Lasso\Libraries\Table\Table_Field_Group_Detail;

use Lasso\Models\Fields as Model_Field;

/** @var bool $is_show_field_visible */
/** @var bool $is_show_remove_field */
/** @var bool $is_show_tooltip */
/** @var Table_Field_Group_Detail $table_field_mapping */

if ( ! isset( $is_show_remove_field ) ) {
	$is_show_remove_field = true;
}
$field     = Lasso_Object_Field::get_by_id( $table_field_mapping->get_field_id() );
$lasso_url = Lasso_URL::get_by_lasso_id( $table_field_mapping->get_lasso_id() );
$is_amazon_product = $lasso_url->amazon->amazon_id ? true : false;

if ( ! $field ) {
	return;
}
$is_list_field = in_array( $field->get_field_type(), array( Model_Field::FIELD_TYPE_BULLETED_LIST, Model_Field::FIELD_TYPE_NUMBERED_LIST ) );
?>
<div class="row shadow url-details-field-box is-dismissable <?php echo $field->get_id() != 1 ? 'cursor-move' : 'static' ?>"
	<?php echo $field->get_id() != 1 ? '' : 'id="static-0"' ?>
	data-field-id="<?php echo $field->get_id() ?>"
	data-lasso-id="<?php echo $table_field_mapping->get_lasso_id() ?>">

	<div class="grip">
		<i class="far fa-grip-vertical dark-gray"></i>
	</div>

	<div class="col">
		<div>
			<?php if ( ! ( $field->is_type_button() || $field->is_image() ) ):
			$tooltip = 'data-tooltip="' . $field->get_field_description() . '"';
			if( $field->is_price_field() && $is_amazon_product ) {
				$is_show_tooltip = true;
				$tooltip = 'data-tooltip="Amazon prices are updated automatically"';
			}
			$tooltip = $is_show_tooltip ? $tooltip : '';
			?>
			<label class="mb-3 label-field-name" <?php echo $tooltip; ?> >
				<strong><?php echo $field->get_field_name() ?></strong>
				<?php if( $field->is_price_field() && $is_amazon_product ) : ?>
					<i class="far fa-info-circle light-purple"></i>
				<?php endif; ?>
			</label>
			<?php endif; ?>

			<?php if ( $field->is_type_text() || $field->is_product_name() ) : ?>
			<input type="text" class="form-control field_value" id="field_<?php echo $table_field_mapping->get_lasso_id()?>_<?php echo $field->get_id()?>" value="<?php echo str_replace( '"', '&quot;', $table_field_mapping->get_field_value() ); ?>"
				placeholder="<?php echo $field->get_field_description() ?>">
			<?php endif ?>

			<?php if ( $field->is_type_textarea() || $is_list_field ) : ?>
			<textarea class="form-control field_value" id="field_<?php echo $table_field_mapping->get_lasso_id()?>_<?php echo $field->get_id()?>" rows="3"
				placeholder="<?php echo $field->get_field_description() ?>"><?php echo $table_field_mapping->get_field_value() ?></textarea>
			<?php endif ?>

			<?php if ( $field->is_type_number() ) : ?>
			<input type="number" class="form-control field_value" id="field_<?php echo $table_field_mapping->get_lasso_id()?>_<?php echo $field->get_id()?>" value="<?php echo $table_field_mapping->get_field_value() ?>"
				placeholder="<?php echo $field->get_field_description() ?>">
			<?php endif ?>

			<?php if ( $field->is_type_rating() ) : ?>
				<div class="rating-container">
					<span class="lasso-stars" style="--rating: <?php echo empty( $table_field_mapping->get_field_value() ) ? "3.5" : $table_field_mapping->get_field_value() ?>;" aria-label="Rating of this product is 3.5 out of 5."></span>
					<input type="number" class="form-control field_value star_value float-left" id="field_<?php echo $table_field_mapping->get_lasso_id()?>_<?php echo $field->get_id()?>" value="<?php echo empty( $table_field_mapping->get_field_value() ) ? "3.5" : $table_field_mapping->get_field_value() ?>"
						placeholder="3.5" maxlength="5" min="1" max="5" step="0.1">
				</div>
			<?php endif ?>

			<?php if ( $field->is_type_button() || $field->is_image() ) : ?>
				<?php
				$primary_button_text = $lasso_url->primary_button_text;
				$primary_link = $lasso_url->target_url;
				$secondary_button_text = $lasso_url->secondary_button_text;
				$secondary_link = $lasso_url->secondary_url;
				?>
				<?php if ( $field->is_image() ) : ?>
					<div class="img-container">
						<input type="hidden" class="field_value" 
							id="field_<?php echo $table_field_mapping->get_lasso_id()?>_<?php echo $field->get_id()?>" 
							value="<?php echo $table_field_mapping->get_image() ?>">
						<div class="image_wrapper d-block">
							<img src="<?php echo $table_field_mapping->get_image() ?>" loading="lazy">
							<div class="image_loading d-none"><div class="py-5"><div class="ls-loader"></div></div></div>
							<div class="image_hover">
								<div class="image_update"><i class="far fa-camera-alt"></i> Update Image</div>
							</div>
						</div>
					</div>
				<?php endif ?>

				<?php if ( $field->is_button()): ?>
					<?php $field_value = json_decode( $table_field_mapping->get_field_value() ); ?>
					<div class="btn-container">
						<a class="btn lasso-button-1" href="<?php echo $lasso_url->public_link ?>" target="_blank">
							<?php echo $field_value->button_text ?>
						</a>
				</div>
				<?php endif; ?>

				<?php if ( $field->is_button( false )): ?>
					<?php $field_value = json_decode( $table_field_mapping->get_field_value() ); ?>
					<div class="btn-container">
						<?php if ( ! empty( $field_value->url ) ) : ?>
							<a class="btn lasso-button-2" href="<?php echo $field_value->url ?>" target="_blank">
								<?php echo $field_value->button_text ?>
							</a>
						<?php else: ?>
							<div class="m-1">
								<span class="lasso-table-warning-secondary-content">
									<i class="far fa-info-circle"></i> No Secondary Button URL set.
								</span>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			<?php endif ?>

			<?php if ( $field->is_price_field() ) :
				$is_amazon_product = $lasso_url->amazon->amazon_id;
				$price_value = $is_amazon_product ? $lasso_url->price : $table_field_mapping->get_field_value(); 
				$price_disabled = $is_amazon_product ? 'disabled' : '';
			?>
				<div class="lasso-price">
					<div class="lasso-price-value">
						<input type="text" class="form-control field_value" 
							<?php echo $price_disabled ?>
							id="field_<?php echo $table_field_mapping->get_lasso_id()?>_<?php echo $field->get_id()?>" 
							value="<?php echo $price_value ?>" placeholder="N/A">
					</div>
				</div>
			<?php endif ?>
			<?php if ( $field->is_description_field() ) : ?>
                <div class="description-content">
					<?php
					$description_content = ! empty( $table_field_mapping->get_field_value() ) ? $table_field_mapping->get_field_value() : '';
					echo $description_content;
					?>
                </div>
			<?php endif ?>

		</div>
	</div>

    <div class="opp-dismiss">
		<?php if ( $field->is_description_field() ) : ?>
            <a class="btn-edit-description cursor-pointer dark-gray"
				data-toggle="modal"
				data-target="#field-description-editor"
				data-field-id="<?php echo $field->get_id()?>"
				data-field-name="<?php echo $field->get_field_name() ?>"
				data-lasso-id="<?php echo $table_field_mapping->get_lasso_id() ?>">
                <i class="far fa-edit"></i>
            </a>
		<?php endif; ?>

		<?php if( $field->is_type_button() ): ?>
			<a href="#" class="js-edit-button-field"
				data-field-group-detail-id="<?php echo $table_field_mapping->get_id()?>"
				data-field-id="<?php echo $field->get_id()?>"
				data-field-name="<?php echo $field->get_field_name() ?>"
				data-field-value="<?php echo str_replace( '"', '_double_quote_', $table_field_mapping->get_field_value() ) ?>"
				data-lasso-id="<?php echo $table_field_mapping->get_lasso_id() ?>"
				data-public-link="<?php echo $lasso_url->public_link ?>">
				<i class="far fa-edit"></i>
			</a>
		<?php endif; ?>
		
		<?php if ( $is_show_remove_field ) : ?>
            <a href="#" class="js-remove-field"
				data-toggle="modal"
				data-target="#field-delete"
				data-field-id="<?php echo $field->get_id()?>"
				data-field-name="<?php echo $field->get_field_name() ?>"
				data-lasso-id="<?php echo $table_field_mapping->get_lasso_id() ?>">
                <i class="far fa-times-circle"></i>
            </a>
		<?php endif; ?>
    </div>
</div>
