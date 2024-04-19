<?php
/**
 * Table
 *
 * @package Tables
 */
// phpcs:ignore
use Lasso\Classes\Config as Lasso_Config;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;
use Lasso\Classes\Setting_Enum as Lasso_Setting_Enum;
use Lasso\Classes\Table_Detail as Lasso_Table_Detail;

use Lasso\Models\Table_Mapping;
use Lasso\Models\Table_Details;

require_once LASSO_PLUGIN_PATH . '/admin/views/header-new.php';

$table_id = $_GET['id'] ?? '' ;
$table_product_count = $table_id ? Table_Mapping::get_count_by_table_id( $table_id ) : 0;
$lasso_table_detail = Table_Details::get_by_id( $table_id );

$select_theme = '<select name="theme_name" id="theme_name" class="form-control">';
foreach ( Lasso_Setting_Enum::THEME_OPTIONS as $theme ) {
	$selected_theme_option = isset( $lasso_table_detail ) && $lasso_table_detail->get_theme() == $theme ? "selected" : '';
	$select_theme .= '<option value="' . $theme . '" ' . $selected_theme_option. ' >' . $theme . '</option>';
}
$select_theme .= '</select>';

$table_id = isset( $lasso_table_detail ) ? $lasso_table_detail->get_id() : '';
$table_name = isset( $lasso_table_detail ) ? $lasso_table_detail->get_title() : '';

$field_verbiage = $lasso_table_detail && Lasso_Helper::compare_string( $lasso_table_detail->get_style(), Lasso_Setting_Enum::TABLE_STYLE_COLUMN ) 
	? 'Add Row' : 'Add Column';
$is_show_vertical_notice = isset( $lasso_table_detail ) 
	&& $lasso_table_detail->get_style() == Lasso_Setting_Enum::TABLE_STYLE_COLUMN
	&& $table_product_count >= Lasso_Table_Detail::VERTICAL_DISPLAY_ITEM_LIMIT;

$show_header_toggle_classes = isset( $lasso_table_detail ) && $lasso_table_detail->get_style() === Lasso_Setting_Enum::TABLE_STYLE_COLUMN ? 'd-none' : '';
$is_show_vertical_notice_class = $is_show_vertical_notice ? '' : 'd-none';
$show_header_toggle_checked = isset( $lasso_table_detail ) && $lasso_table_detail->get_show_headers_horizontal() ? 'checked' : '';
$show_field_name_toggle_checked = isset( $lasso_table_detail ) && $lasso_table_detail->get_show_field_name() ? 'checked' : '';
$horizontal_style_selected = isset( $lasso_table_detail ) && $lasso_table_detail->get_style() == "Row" ? "selected" : '';
$vertical_style_selected = isset( $lasso_table_detail ) && $lasso_table_detail->get_style() == "Column" ? "selected" : '';
?>
<section class="px-3 py-5">
	<div class="container">
		<!-- HEADER -->
		<?php require_once 'header.php'; ?>

		<div class="white-bg rounded shadow p-4 mb-4 table-content-wrapper lasso-table-builder">
			<div class="row">
				<div class="col-lg-12 mb-lg-0 h-100">
					<div class="form-group mb-4">
						<div class="form-row">
							<div class="col-lg-4">
								<label data-tooltip="Use this to identify your tables">
									<strong>Name</strong> <i class="far fa-info-circle light-purple"></i>
								</label>
								<input id="table_name" name="name" type="text" value="<?php echo $table_name; ?>" class="form-control" placeholder="<?php echo $table_title; ?>">
							</div>
							<div class="col-lg"></div>
                            <div class="col-lg-3">
								<label>
									<strong>Options</strong>
								</label>
								<div id="is-show-field-name-toggle-row">
									<label class="toggle m-0 mt-1 mr-1">
										<input id="is-show-field-name" type="checkbox" <?php echo $show_field_name_toggle_checked; ?>>
										<span class="slider"></span>
									</label>
                                    <label data-tooltip="Show field name in a Table">
										<strong>Show Field Name</strong> <i class="far fa-info-circle light-purple"></i>
									</label>
                                </div>
                                <div id="is-show-headers-toggle-row" class="<?php echo $show_header_toggle_classes; ?>">
									<label class="toggle m-0 mt-1 mr-1">
										<input id="is-show-headers-toggle" type="checkbox" <?php echo $show_header_toggle_checked; ?>>
										<span class="slider"></span>
									</label>
                                    <label data-tooltip="Hide the column header in a Horizontal Table">
										<strong>Show Headers</strong> <i class="far fa-info-circle light-purple"></i>
									</label>
                                </div>
                            </div>
                            <div class="col-lg"></div>
							<div class="col-lg-2">
								<label data-tooltip="This changes the orientation of your table">
									<strong>Style</strong> <i class="far fa-info-circle light-purple"></i>
								</label>
								<select name="table_style" class="form-control">
									<option value="Row" <?php echo $horizontal_style_selected; ?> >Horizontal</option>
									<option value="Column" <?php echo $vertical_style_selected; ?> >Vertical</option>
								</select>
							</div>
							<div class="col-lg"></div>
							<div class="col-lg-2">
                                <label data-tooltip="Change the look and feel of your table"><strong>Theme</strong> <i class="far fa-info-circle light-purple"></i></label>
                                <?php echo $select_theme; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="table-action-btns" class="row d-none">
				<div class="col-lg-12 mb-5 h-100">
					<div class="form-group mb-1">
						<div class="form-row">
							<div class="col-lg">
								<button class="btn mr-4" data-toggle="modal" data-target="#link-monetize" id="btn-add-product">
									Add Product
								</button>
								<button class="btn mr-4" data-toggle="modal" data-target="#group-monetize" id="btn-add-group">
									Add Group
								</button>
								<button class="btn white-bg black black-border" data-toggle="modal" id="btn-add-col-field">
									<?php echo $field_verbiage ?>
								</button>
							</div>
							<div class="col text-right">
								<button class="btn btn-preview-table">Preview Table</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12 h-100">
					<div class="onboarding_display_container pb-3">
						<div id="demo_display_box"></div>
						<div class="image_loading onboarding d-none"></div>
					</div>
					<div id="lasso-table" class="lasso-table-no-item" data-lasso-id="">
					</div>
				</div>
			</div>
		</div>

		<!-- SAVE -->
		<div class="row align-items-center">
			<input type="hidden" name="table_id" id="table_id" value="<?php echo $table_id; ?>">
			<input type="hidden" name="lasso_id" id="lasso_id">
			<input type="hidden" name="field_group_id" id="field_group_id">
			<input type="hidden" name="field_id" id="field_id">
			<input type="hidden" name="order" id="order">
			<div style="visibility: hidden" id="tmp"></div>

			<div class="col-lg text-lg-left text-center mb-4">
				<?php $table_attrs = array(
						'name' => isset( $lasso_table_detail ) ? $lasso_table_detail->get_title() : '',
						'id'   => isset( $lasso_table_detail ) ? $lasso_table_detail->get_id() : ''
					)
				?>
				<a href="#" id="btn-delete-table" class="red hover-red-text" <?php echo Lasso_Html_Helper::render_attrs( $table_attrs ) ?>>
					<i class="far fa-trash-alt"></i> Delete This Table
				</a>
			</div>

		</div>
	</div>
</section>

<?php
Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/modals/field-create.php', array(), false );
Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/modals/field-description.php', array(), false );
Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/modals/url-field-delete.php', array(), false );
Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/modals/table-product-link.php', array(
    'modal_title' => 'Add Product to Table'
), false );
Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/modals/table-product-group.php', array(
    'modal_title' => 'Add group to Table'
), false );
Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/modals/table-edit-button-information.php', array(), false );
?>

<?php Lasso_Config::get_footer(); ?>
