<?php
/**
 * Field details
 *
 * @package Group details
 */

// phpcs:ignore
use Lasso\Models\Fields;

use Lasso\Classes\Config as Lasso_Config;

require LASSO_PLUGIN_PATH . '/admin/views/header-new.php';
$urls            = 0;
$post_id         = 0;
$editable        = 1;
$disabled        = '';
$hide_delete_btn = false;

if ( isset( $_GET['urls'] ) ) {
	$urls = $_GET['urls'];
}

if ( isset( $_GET['post_id'] ) ) {
	$post_id         = $_GET['post_id'];
	$hide_delete_btn = true;
}

$default_fields = array(
	Fields::RATING_FIELD_ID,
	Fields::PROS_FIELD_ID,
	Fields::CONS_FIELD_ID,
	Fields::DESCRIPTION_FIELD_ID,
	Fields::PRODUCT_NAME_FIELD_ID,
	Fields::IMAGE_FIELD_ID,
	Fields::PRIMARY_BTN_ID,
	Fields::SECONDARY_BTN_ID,
	Fields::PRICE_ID,
);
if ( in_array( $post_id, $default_fields ) ) {
	$editable = 0;
	$disabled = 'disabled';
}
?>

<section class="py-5">
	<div class="container">

	<!-- TITLE & NAVIATION -->
		<?php require 'header.php'; ?>

		<form id="field-details" autocomplete="off">
			<input type="hidden" id="post_id" name="" value="<?php echo $post_id; ?>">
			<input type="hidden" id="url_count" name="" value="<?php echo $urls; ?>">
			<input type="hidden" id="field_id" name="field_id" value="<?php echo $post_id; ?>">

			<!-- EDIT DETAILS -->
			<div class="white-bg rounded shadow p-5 mb-5">
				<div class="row align-items-center">
					<div class="col-4">
						<div class="form-group mb-4">
							<label data-tooltip="Ideal title for use in things like a comparison table header."><strong>Field Title</strong> <i class="far fa-info-circle light-purple"></i></label>
							<input id="field-title" type="text" class="form-control" value="<?php echo $field_name; ?>" placeholder="Size, Weight, Year, etc...">
						</div>
					</div>
					<div class="col-2">
						<div class="form-group mb-4">
							<label data-tooltip="Ideal title for use in things like a comparison table header."><strong>Field Type</strong> <i class="far fa-info-circle light-purple"></i></label>
							<select id="field-type-picker" data-show-content="true" class="selectpicker form-control" <?php echo $disabled; ?>>
								<option data-icon="far fa-text" value="<?php echo $field_type?>" <?php echo ($field_type === 'text') ? 'selected' : ''; ?> default>Short Text</option>
								<option data-icon="far fa-paragraph" value="textarea" <?php echo ($field_type === 'textarea') ? 'selected' : ''; ?>>Long Text</option>
								<option data-icon="far fa-hashtag" value="number" <?php echo ($field_type === 'number') ? 'selected' : ''; ?>>Number</option>
								<option data-icon="far fa-star" value="rating" <?php echo ($field_type === 'rating') ? 'selected' : ''; ?>>Rating</option>
								<?php if ( isset( $field ) ) { ?>
									<?php if ( $field_type === 'button' ) { ?>
										<option data-icon="far fa-link" value="button" selected>Hyperlink</option>
									<?php } elseif ( $field_type === 'image' ) { ?>
										<option data-icon="far fa-image" value="image" selected>Image</option>
									<?php } elseif ( $field_type === 'label' ) { ?>
										<option data-icon="far fa-money-bill" value="label" selected>Price</option>
									<?php } elseif ( Fields::FIELD_TYPE_EDITOR === $field_type ) { ?>
										<option data-icon="far fa-edit" value="<?php echo Fields::FIELD_TYPE_EDITOR ?>" selected>Rich text</option>
									<?php }
								}?>
								<option data-icon="far fa-paragraph" value="<?php echo Fields::FIELD_TYPE_BULLETED_LIST ?>" <?php echo ($field_type === Fields::FIELD_TYPE_BULLETED_LIST) ? 'selected' : ''; ?>>Bulleted List</option>
								<option data-icon="far fa-paragraph" value="<?php echo Fields::FIELD_TYPE_NUMBERED_LIST ?>" <?php echo ($field_type === Fields::FIELD_TYPE_NUMBERED_LIST) ? 'selected' : ''; ?>>Numbered List</option>
							</select>
						</div>
					</div>
				</div>
				<div class="row align-items-center">
					<div class="col-6">
						<div class="form-group mb-4">
							<label data-tooltip="Describe how you use this field."><strong>Description</strong> <i class="far fa-info-circle light-purple"></i></label>
							<textarea id="field-description" class="form-control" placeholder="Describe how you use this field." rows="3" <?php echo $disabled; ?>><?php echo $field_description; ?></textarea>
						</div>
						<?php if ( 0 === $editable) { ?>
							<div class=""><i><strong>Note:</strong> This is a built-in field to Lasso and only the title is editable.</i></div>
						<?php } ?>
					</div>
				</div>
			</div>

			<!-- SAVE & DETELE -->
			<div class="row align-items-center">
				<!-- SAVE CHANGES -->
				<div class="col-lg order-lg-2 text-lg-right text-center mb-4">
					<button class="btn" data-toggle="modal" id="field_save">Save Changes</button>
				</div>

				<!-- DELETE URL -->
				<?php if ( 1 === $editable) { ?>
					<div class="col-lg text-lg-left text-center mb-4">
						<a id="field_delete_pop" href="#" class="red hover-red-text" data-toggle="modal"><i class="far fa-trash-alt"></i> Delete This Field</a>
					</div>
				<?php } ?>
			</div>
		</form>

	</div>
</section>

<!-- MODALS -->
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/field-delete.php'; ?>  

<div class="modal fade" id="url-save" data-backdrop="static" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content p-5 shadow text-center">
			<h3>Updating Field</h3>
			<p>Saving your changes now.</p>
			<div class="progress">
				<div class="progress-bar progress-bar-striped progress-bar-animated green-bg" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 75%"></div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="group_not_delete" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content text-center shadow p-5 rounded">
			<h2>Hold Up</h2>
			<p>You can't delete a Field if there are Links using it. Remove all Links using this Field first.</p>
			<div>
				<button type="button" class="btn" data-dismiss="modal">
					Ok
				</button>
			</div>
		</div>
	</div>
</div>

<?php Lasso_Config::get_footer(); ?>
