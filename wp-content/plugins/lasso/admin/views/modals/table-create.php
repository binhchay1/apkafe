<?php
use Lasso\Classes\Setting_Enum as Lasso_Setting_Enum;

$column_value  = Lasso_Setting_Enum::TABLE_STYLE_COLUMN;
$row_value     = Lasso_Setting_Enum::TABLE_STYLE_ROW;
$table_theme   = Lasso_Setting_Enum::THEME_CACTUS;
?>

<!-- TABLE ADD -->
<div class="modal fade" id="table-create" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow p-4 rounded">
			<form action="#" method="get" id="table-create-form" autocomplete="off">
				<input type="hidden" name="table_style_create" value="<?php echo $column_value; ?>" />
				<input type="hidden" name="table_theme_create" value="<?php echo $table_theme; ?>" />
				<div class="form-group mb-4">
					<label for="table-name"><strong>Table Name</strong></label>
					<input id="table-name" name="table_name_create" type="text" class="form-control" placeholder="Table Name" required>
				</div>

				<div class="row mb-4">
					<div class="col verical-wrapper">
						<div class="demo-style-wrapper" data-table-style="<?php echo $column_value; ?>">
							<div class="text-center">Vertical</div>
							<div class="row">
								<div class="col">
									<div class="vertical-item-demo"></div>
								</div>
								<div class="col">
									<div class="vertical-item-demo"></div>
								</div>
								<div class="col">
									<div class="vertical-item-demo"></div>
								</div>
							</div>
							<span class="checkmark">
								<div class="checkmark_circle"></div>
								<div class="checkmark_stem"></div>
								<div class="checkmark_kick"></div>
							</span>
						</div>
					</div>
					<div class="col horizontal-wrapper">
						<div class="demo-style-wrapper" data-table-style="<?php echo $row_value; ?>">
							<div class="text-center">Horizontal</div>
							<div id="horizontal-demo-items">
								<div class="horizontal-item-demo"></div>
								<div class="horizontal-item-demo"></div>
								<div class="horizontal-item-demo"></div>
							</div>
							<span class="checkmark d-none">
								<div class="checkmark_circle"></div>
								<div class="checkmark_stem"></div>
								<div class="checkmark_kick"></div>
							</span>
						</div>
					</div>
				</div>

				<p id="table-create-error" class="text-danger my-3 text-center"></p>

				<button class="btn save-tb">Create Table</button>
			</form>
        </div>
    </div>
</div>

<script>
	jQuery(document).ready(function () {
		jQuery(document).on('click', '#table-create .demo-style-wrapper', change_table_style);
		jQuery("#table-create-form").on("submit", submit_create_table);
	});

	function change_table_style() {
		let elm_style_wrapper = jQuery(this);
		let selected_style = elm_style_wrapper.data('table-style');
		let parent = elm_style_wrapper.closest('.row');
		parent.find('.checkmark').addClass('d-none'); // Uncheck style for all
		elm_style_wrapper.find('.checkmark').removeClass('d-none'); // Show checked for current clicked element
		jQuery('#table-create input[name="table_style_create"]').val(selected_style); // Set selected table style
	}

	function submit_create_table() {
		let form        = jQuery(this);
		let table_name  = form.find('input[name="table_name_create"]').val();
		let table_style = form.find('input[name="table_style_create"]').val();
		let table_theme = form.find('input[name="table_theme_create"]').val();
		let save_btn    = form.find('.save-tb');
		jQuery('#table-create-error').text(''); // Reset error text

		// Validate Table Name
		if ( ! table_name ) {
			jQuery('#table-create-error').text('Please fill out "Table Name" field');
			return;
		}

		jQuery.ajax({
			url: lassoOptionsData.ajax_url,
			type: 'POST',
			data: {
				action: 'lasso_create_comparison_table',
				table_name: table_name,
				table_style: table_style,
				table_theme: table_theme
			},
			beforeSend: function() {
				save_btn.prop('disabled', true);
				save_btn.html(get_loading_image_small());
			}
		})
		.done(function (res) {
			if (typeof res.data === 'undefined') {
				return;
			}
			
			let redirect_url = res.data.redirect_url;
			location.href = redirect_url;
		})
		.fail(function (xhr, status, error) {
			jQuery('#table-create-error').text(xhr.responseJSON.data);
			save_btn.prop('disabled', false);
			save_btn.text('Create Table');
		});

		return false;
	}
</script>