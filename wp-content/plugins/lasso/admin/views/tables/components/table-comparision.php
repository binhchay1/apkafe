<?php
/** @var string                 $table_style */
/** @var int                    $field_amount */
/** @var int                    $table_id */
/** @var bool                   $add_field_column */

use Lasso\Classes\Setting_Enum as Lasso_Setting_Enum;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;

use Lasso\Libraries\Lasso_URL;
use Lasso\Libraries\Table\Table_Field_Group_Detail;

use Lasso\Models\Fields;
use Lasso\Models\Table_Details;
use Lasso\Models\Table_Field_Group;
use Lasso\Models\Table_Field_Group_Detail as Model_Table_Field_Group_Detail;
use Lasso\Models\Table_Mapping;

$list_product_mapping_in_table = Table_Mapping::get_list_by_table_id( $table_id );
$field_groups = Table_Field_Group::get_list_field_group_id_by_table_id( $table_id );

$scroll = '';
if ( Lasso_Setting_Enum::TABLE_STYLE_ROW === $table_style ) {
	$scroll =  5 < count( $field_groups ) ? 'scroll-bar' : '';
} else if ( Lasso_Setting_Enum::TABLE_STYLE_COLUMN === $table_style ) {
	$scroll =  5 < count( $list_product_mapping_in_table ) ? 'scroll-bar' : '';
}

$field_group_id_generate = Table_Field_Group::generate_field_group_id();

$field_group_first = null;
$field_groups_remain = array();
if ( ! empty( $field_groups ) ) {
	$field_group_first = $field_groups[0];
	if ( 2 <= count( $field_groups ) ) {
		$field_groups_remain = array_slice( $field_groups , 1 , count( $field_groups ) );
	}
}
$table = Table_Details::get_by_id( $table_id );
$table_theme_class = ! $table || empty( $table->get_theme() ) ? '' : sprintf( 'lasso-table-%s-theme', strtolower( $table->get_theme() ) );
?>
<!-- INITIAL ALTERNATIVE THEME BUILDER|EDIT MODE -->
<div class="lasso-table-wrapper <?php echo $scroll . " " . $table_theme_class?>" >
<?php
    if ( ! count( $list_product_mapping_in_table ) ) {
        Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/education/table-details-empty.php', array(), false );
    }
?>
<?php if ( Lasso_Setting_Enum::TABLE_STYLE_ROW === $table_style ) { ?>
<div class="lasso-table-wrapper-table-row-inner">
	<div class="table-row">
		<div class="main-col col-heading">
			<ul class="table-row-heading-sortable <?php echo count($field_groups) === 1 ? "only-one-group" : "" ?>">
				<?php
				$order = 1;
				foreach ( $list_product_mapping_in_table as $key => $product_mapping ):
					$lasso_url = Lasso_URL::get_by_lasso_id( $product_mapping->get_lasso_id() );
					$tb_field_group_detail = Model_Table_Field_Group_Detail::get_by_table_id_lasso_id_field_id($product_mapping->get_table_id(), $product_mapping->get_lasso_id(), Fields::PRODUCT_NAME_FIELD_ID);
					$title = ! is_null( $tb_field_group_detail ) ? $tb_field_group_detail->get_field_value() : $lasso_url->name;
					$attrs = array(
						'order'    => $order ++,
						'lasso-id' => $product_mapping->get_lasso_id(),
						'name' => $title
					);
				?>
					<li <?php echo Lasso_Html_Helper::render_attrs( $attrs ); ?>>
						<div class="badge_wrapper text-left ">
							<div class="badge_box bg-white">
								<label class="mb-3" for="badge_text_<?php echo $product_mapping->get_lasso_id() ?>">
									<strong>Badge</strong>
								</label>
								<input type="text"
									id="badge_text_<?php echo $product_mapping->get_lasso_id() ?>"
									class="form-control badge_text"
									value="<?php echo $product_mapping->get_badge_text() ?>"
									placeholder="Badge"
									data-lasso-id="<?php echo $product_mapping->get_lasso_id() ?>">
							</div>
						</div>
						<ul class="group-heading">
							<?php
							$tb_field_group_details = Model_Table_Field_Group_Detail::get_list_by_lasso_id_field_group_id( $product_mapping->get_lasso_id(), $field_group_first );
							$attrs['field-group-id'] = $field_group_first;
							$class_selector = 'sortable-column-fields-' . $field_group_first . '-' . $lasso_url->lasso_id;
							?>
							<li data-field-group-id="<?php echo $field_group_first ?>">
								<span class="product-grip">
									<i class="far fa-arrows-alt-v dark-gray"></i>
								</span>
								<ul class="sortable-column-fields <?php echo $class_selector ?> sortable-column-fields-<?php echo $field_group_first ?>" <?php echo Lasso_Html_Helper::render_attrs( $attrs ) ?>>
									<?php
									foreach ( $tb_field_group_details as $tb_field_group_detail ) {
										$html = Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/fields/field-details-row.php', array(
											'is_show_field_visible' => false,
											'is_show_tooltip'       => false,
											'table_field_mapping'   => $tb_field_group_detail
										));
										$class_selector = "class-" . $tb_field_group_detail->get_field_id();
										$attrs_loop = Lasso_Html_Helper::render_attrs(
											array(
												'order'          => $tb_field_group_detail->get_order(),
												'lasso-id'       => $tb_field_group_detail->get_lasso_id(),
												'field-id'       => $tb_field_group_detail->get_field_id(),
												'class'          => $class_selector,
												'id'             => 'id-' . $tb_field_group_detail->get_lasso_id() . '-' . $tb_field_group_detail->get_field_id(),
												'field-group-id' => $field_group_first
											)
										);
									?>
										<li <?php echo $attrs_loop ?> class="<?php echo $class_selector?>"><?php echo $html ?></li>
									<?php } ?>
								</ul>
							</li>
						</ul>

						<?php
						$attrs['tooltip'] = 'Remove this product';
						$html_attrs = Lasso_Html_Helper::render_attrs( $attrs );
						?>
						<span class="btn-delete-product" <?php echo $html_attrs ?> >
							<i class="far fa-trash" ></i>
						</span>

						<?php
						$attrs['tooltip'] = 'Add field';
						$html_attrs = Lasso_Html_Helper::render_attrs( $attrs );
						?>
						<span class='btn-add-field bottom-left cursor-pointer'<?php echo $html_attrs ?> >
							<i class="far fa-plus-circle add-field-icon"></i>
						</span>
						
						<ul class="table-row-heading-fields">
							<?php
							unset( $attrs['tooltip'] );
							foreach ( $field_groups_remain as $field_group_id ) {
								$table_field_group_details = Table_Field_Group_Detail::get_list_by_lasso_id_field_group_id( $product_mapping->get_lasso_id() , $field_group_id );
								$attrs = array(
									'lasso-id'       => $product_mapping->get_lasso_id(),
									'field-group-id' => $field_group_id,
								);
								$html_attrs = Lasso_Html_Helper::render_attrs( $attrs );
								$class_selector = 'table-row-heading-fields-wrapper-' . $field_group_id . '-' .$product_mapping->get_lasso_id();
								?>
								<li <?php echo $html_attrs?> >
									<ul class="<?php echo $class_selector ?> ">
										<?php
										foreach ( $table_field_group_details as $table_field_group_detail ) :
											$html = Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/fields/field-details-row.php', array(
												'is_show_field_visible' => false,
												'is_show_tooltip'       => false,
												'table_field_mapping' => $table_field_group_detail
											));
											$class_selector = 'class-' . $table_field_group_detail->get_field_id();
											$id_selector    = 'id-' . $table_field_group_detail->get_lasso_id() . '-' . $table_field_group_detail->get_field_id();
											$attrs = Lasso_Html_Helper::render_attrs(
												array(
													'order'          => $table_field_group_detail->get_order(),
													'lasso-id'       => $table_field_group_detail->get_lasso_id(),
													'field-id'       => $table_field_group_detail->get_field_id(),
													'field-group-id' => $table_field_group_detail->get_field_group_id(),
													'class'          => $class_selector,
													'id'             => $id_selector
												)
											);
										?>
											<li class="<?php echo $class_selector?>" <?php echo $attrs ?> ><?php echo $html ?></li>
										<?php endforeach; ?>
									</ul>
								</li>
							<?php } ?>
						</ul>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="main-col col_content">
			<div class="sortable_column">
				<?php
				$order = 1;
				$table_field_group = null;
				foreach ( $field_groups_remain as $index => $group_id ):
				?>
					<ul class="column" data-order="<?php echo $order ?>" style="" id="col-<?php echo $order ?>">
						<?php
						foreach ( $list_product_mapping_in_table as $product_mapping ):
							$table_field_group = Table_Field_Group::get_by_table_id_lasso_id_field_group_id( $product_mapping->get_table_id(), $product_mapping->get_lasso_id(), $group_id );
							
							if ( ! $table_field_group ) {
								continue;
							}

							$max_quantity_fields = Table_Field_Group::get_max_quantity_of_field_by_table_id_lasso_id( $product_mapping->get_table_id(), $product_mapping->get_lasso_id() );
							$table_field_group_details = Table_Field_Group_Detail::get_list_by_lasso_id_field_group_id( $product_mapping->get_lasso_id(), $table_field_group->get_field_group_id() );
							$pseudo_element = "pseudo-" . $table_field_group->get_lasso_id() . "-" . $table_field_group->get_field_group_id();
							$css_h100 = ''; // ? class css height 100%
							if ( count( $table_field_group_details ) < $max_quantity_fields ) {
								$css_h100 = 'h100';
							}
							$attrs = array(
								'field-group-id' => $table_field_group->get_field_group_id(),
								'lasso-id'       => $product_mapping->get_lasso_id(),
							);
							$html_attrs = Lasso_Html_Helper::render_attrs( $attrs );
							$class_selector = 'sortable-column-fields-' . $table_field_group->get_field_group_id() . '-' . $product_mapping->get_lasso_id(); ?>
							<li <?php echo $html_attrs ?>>
								<?php if ( $index === 0 ) {
								$attrs['tooltip'] = 'Remove this product';
								$tb_group_detail = Model_Table_Field_Group_Detail::get_by_table_id_lasso_id_field_id( $product_mapping->get_table_id(), $product_mapping->get_lasso_id(), Fields::PRODUCT_NAME_FIELD_ID );
								$attrs['name'] =  ! is_null( $tb_field_group_detail ) ? $tb_field_group_detail->get_field_value() : $lasso_url->name;
								?>
								<?php } ?>

								<?php
								if ( $index === 0 ) {
									$attrs['tooltip'] = '%s product name';
									$attrs['tooltip'] = $product_mapping->is_show_title() ? sprintf( $attrs['tooltip'], "Hide" ) : sprintf( $attrs['tooltip'], "Show" );
								}
								?>

								<ul class="sortable-column-fields <?php echo $css_h100 . " " . $class_selector ?> sortable-column-fields-wrapper-<?php echo $product_mapping->get_lasso_id() ?> sortable-column-fields-<?php echo $group_id ?>" <?php echo $html_attrs ?>>
									<?php
									foreach ( $table_field_group_details as $table_field_group_detail ) :
										$html = Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/fields/field-details-row.php', array(
											'is_show_field_visible' => false,
											'is_show_tooltip'       => false,
											'table_field_mapping'         => $table_field_group_detail
										));
										$class_selector = 'class-' . $table_field_group_detail->get_field_id();
										$id_selector = 'id-' . $table_field_group_detail->get_lasso_id() . '-' . $table_field_group_detail->get_field_id();
										$attrs = array(
											'field-group-id' => $table_field_group_detail->get_field_group_id(),
											'order'          => $table_field_group_detail->get_order(),
											'field-id'       => $table_field_group_detail->get_field_id(),
											'lasso-id'       => $table_field_group_detail->get_lasso_id(),
											'class'          => $class_selector,
											'id'             => $id_selector
										);
										$html_attrs = Lasso_Html_Helper::render_attrs($attrs);
									?>
									<li class="<?php echo $class_selector ?>" <?php echo $html_attrs ?>><?php echo $html; ?></li>
									<?php endforeach; ?>
								</ul>

								<?php 
								$attrs = array(
									'tooltip'		 => 'Add field',
									'field-group-id' => $table_field_group->get_field_group_id(),
									'lasso-id'       => $product_mapping->get_lasso_id(),
								);
								$html_attrs = Lasso_Html_Helper::render_attrs( $attrs );
								?>
								<span class='btn-add-field bottom-left cursor-pointer' <?php echo $html_attrs ?>>
									<i class="far fa-plus-circle add-field-icon"></i>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php
				$order++;
				endforeach;
				if ( $add_field_column ) { ?>
					<ul class="column empty-group " data-order="<?php echo $order ?>" style="" id="col-<?php echo $order ?>">
						<?php foreach ( $list_product_mapping_in_table as $product_mapping ) { ?>
							<li>
								<?php
								$fields_wrapper_selector = 'sortable-column-fields-' . $field_group_id_generate . '-' .$product_mapping->get_lasso_id();
								$fields_wrapper_group_selector = 'sortable-column-fields-' . $field_group_id_generate;
								$attrs = array(
									'field-group-id' => $field_group_id_generate,
									'lasso-id'       => $product_mapping->get_lasso_id(),
								);
								?>
								<ul class="sortable-column-fields <?php echo $fields_wrapper_selector . " " . $fields_wrapper_group_selector ?>" <?php echo Lasso_Html_Helper::render_attrs( $attrs ); ?>>
									<li class="btn-add-field btn-add-field-big" <?php echo Lasso_Html_Helper::render_attrs( $attrs ); ?> data-order="1">
										<div class="row shadow box-add-field url-details-field-box is-dismissable cursor-move icon-container">
											<div>
												<?php echo Lasso_Html_Helper::get_plus_icon(); ?>
												<h5>Click to add a Field</h5>
											</div>
										</div>
									</li>
								</ul>

								<?php $attrs['tooltip'] ='Add field'; ?>
								<span class='btn-add-field bottom-left cursor-pointer' <?php echo Lasso_Html_Helper::render_attrs( $attrs ); ?> >
									<i class="far fa-plus-circle add-field-icon"></i>
								</span>
							</li>
						<?php } ?>
					</ul>
				<?php }
				?>
			</div>
		</div>
	</div>
</div>
<?php }
else { ?>
	<div id="table-column">
		<ul class="heading <?php echo count($field_groups) === 1 ? "only-one-group" : "" ?>">
			<li>
				<ul class="table-column-heading-sortable">
				<?php
				foreach ( $list_product_mapping_in_table as $key => $product_mapping ):
					$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $product_mapping->get_lasso_id() );
					$tb_field_group_detail = Model_Table_Field_Group_Detail::get_by_table_id_lasso_id_field_id($product_mapping->get_table_id(), $product_mapping->get_lasso_id(), Fields::PRODUCT_NAME_FIELD_ID);
					$title = ! is_null( $tb_field_group_detail ) ? $tb_field_group_detail->get_field_value() : $lasso_url->name;
					$attrs     = array(
						'order'          => ( $key + 1 ),
						'lasso-id'       => $product_mapping->get_lasso_id(),
						'name'           => $title,
						'field-group-id' => $field_group_first
					);
				?>
					<li <?php echo Lasso_Html_Helper::render_attrs( $attrs ) ?> >
						<span class="product-grip">
							<i class="far fa-arrows-h dark-gray"></i>
						</span>
						<div class="badge_wrapper text-left">
							<div class="badge_box bg-white">
								<label class="mb-3" for="badge_text_<?php echo $product_mapping->get_lasso_id() ?>">
									<strong>Badge</strong>
								</label>
								<input type="text"
									   id="badge_text_<?php echo $product_mapping->get_lasso_id() ?>"
									   class="form-control badge_text"
									   value="<?php echo $product_mapping->get_badge_text() ?>"
									   placeholder="Badge"
									   data-lasso-id="<?php echo $product_mapping->get_lasso_id() ?>">
							</div>
						</div>
						<ul class="group-heading">
							<?php
							$tb_field_group_details = Model_Table_Field_Group_Detail::get_list_by_lasso_id_field_group_id( $product_mapping->get_lasso_id(), $field_group_first );
							$class_sort_fields = 'sortable-row-fields-' . $field_group_first . '-'.$lasso_url->lasso_id;
							?>
							<li data-field-group-id="<?php echo $field_group_first ?>">
								<ul class="sortable-row-fields <?php echo $class_sort_fields ?>" <?php echo Lasso_Html_Helper::render_attrs( $attrs ) ?>>
									<?php
									foreach ( $tb_field_group_details as $tb_field_group_detail ) {
										$html = Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/fields/field-details-row.php', array(
											'is_show_field_visible' => false,
											'is_show_tooltip'       => false,
											'table_field_mapping'   => $tb_field_group_detail
										));
										$class_selector = "class-" . $tb_field_group_detail->get_field_id();
										$attrs_loop = Lasso_Html_Helper::render_attrs(
											array(
												'order'          => $tb_field_group_detail->get_order(),
												'lasso-id'       => $tb_field_group_detail->get_lasso_id(),
												'field-id'       => $tb_field_group_detail->get_field_id(),
												'class'          => $class_selector,
												'id'             => 'id-' . $tb_field_group_detail->get_lasso_id() . '-' . $tb_field_group_detail->get_field_id(),
												'field-group-id' => $field_group_first
											) );
									?>
										<li <?php echo $attrs_loop ?> class="<?php echo $class_selector?>"><?php echo $html ?></li>
									<?php } ?>
								</ul>
							</li>
						</ul>

						<?php
						$attrs['tooltip'] = '%s product name';
						$attrs['tooltip'] = $product_mapping->is_show_title() ? sprintf( $attrs['tooltip'], "Hide" ) : sprintf( $attrs['tooltip'], "Show" );
						$html_attrs = Lasso_Html_Helper::render_attrs( $attrs );
						$attrs['tooltip'] = 'Remove this product';
						?>
						<span class="btn-delete-product " <?php echo Lasso_Html_Helper::render_attrs( $attrs ); ?> >
							<i class="far fa-trash" ></i>
						</span>
						<?php
						$attrs['tooltip'] = 'Add field';
						?>
						<span class='btn-add-field bottom-left cursor-pointer'<?php echo Lasso_Html_Helper::render_attrs( $attrs ); ?> ><i class="far fa-plus-circle add-field-icon"></i></span>
						<?php
						if ( 2 <= count( $field_groups ) ) {
							$field_groups_remain = array_slice( $field_groups, 1, count( $field_groups ) );
						?>
						<ul class="table-column-heading-fields visibility-hide">
							<?php
							foreach ( $field_groups_remain as $field_group_id ) {
							?>
							<li>
								<?php
								$tb_field_group_details = Model_Table_Field_Group_Detail::get_list_by_lasso_id_field_group_id( $product_mapping->get_lasso_id(), $field_group_id );
								?>
								<ul >
									<?php
									foreach ( $tb_field_group_details as $tb_field_group_detail ) {
										$html = Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/fields/field-details-row.php', array(
											'is_show_field_visible' => false,
											'is_show_tooltip'       => false,
											'table_field_mapping'   => $tb_field_group_detail
										));
										$class_selector = "class-" . $tb_field_group_detail->get_field_id();
									?>
										<li class="<?php echo $class_selector?>"><?php echo $html ?></li>
									<?php } ?>
								</ul>
							</li>
							<?php } ?>
						</ul>
						<?php } ?>
					</li>
				<?php endforeach; ?>
				</ul>
			</li>
		</ul>
		<div class="clear"></div>
		<ul class="sortable-row visibility-visible">
			<?php
			$order = 1;
			$table_field_group = null;
			foreach ( $field_groups_remain as $key => $field_group_id ):
			?>
				<li class="row-content" data-order="<?php echo $order++ ?>">
					<ul>
						<?php
						foreach ( $list_product_mapping_in_table as $product_mapping ):
							$table_field_group_details = Table_Field_Group_Detail::get_list_by_lasso_id_field_group_id( $product_mapping->get_lasso_id(), $field_group_id );
							$pseudo_group_element = "pseudo-" . $field_group_id;
							$pseudo_element = "pseudo-" . $field_group_id . "-" . $field_group_id;
							$attrs = array(
								'field-group-id' => $field_group_id,
								'lasso-id'       => $product_mapping->get_lasso_id()
							);
							$html_attrs = Lasso_Html_Helper::render_attrs( $attrs );
							$fields_wrapper_selector = "sortable-row-fields-" . $field_group_id .'-' .$product_mapping->get_lasso_id();
						?>
							<li <?php echo $html_attrs ?>>
								<ul class="sortable-row-fields <?php echo $fields_wrapper_selector ?>" <?php echo $html_attrs ?>>
									<?php
									foreach ( $table_field_group_details as $key => $table_field_group_detail ) :
										$html = Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/fields/field-details-row.php', array(
											'is_show_field_visible' => false,
											'is_show_tooltip'       => false,
											'table_field_mapping'         => $table_field_group_detail
										));
										$attrs['field-id']   = $table_field_group_detail->get_field_id();
										$attrs['data-order'] = ( $key + 1 );
										$class_selector = 'class-' . $table_field_group_detail->get_field_id();
										$attrs['class'] = $class_selector;
										$attrs['id'] = 'id-' . $table_field_group_detail->get_lasso_id() . '-' . $table_field_group_detail->get_field_id();
										$html_attrs = Lasso_Html_Helper::render_attrs( $attrs );
									?>
										<li <?php echo $html_attrs ?> class="<?php echo $class_selector ?>" ><?php echo $html ?></li>
									<?php endforeach; ?>
								</ul>
								<div class="action-tool">
									<?php
									$attrs = array(
										'tooltip'        => "Add field",
										'field-id'       => $table_field_group_detail->get_field_id(),
										'field-group-id' => $table_field_group_detail->get_field_group_id(),
										'lasso-id'       => $product_mapping->get_lasso_id()
									);
									?>
									<span class='btn-add-field bottom-left cursor-pointer' <?php echo Lasso_Html_Helper::render_attrs( $attrs ); ?> >
										<i class="far fa-plus-circle add-field-icon"></i>
									</span>

									<?php $attrs['tooltip'] = "Remove same field &#8594;"; ?>
									<span class="btn-delete-field bottom-right cursor-pointer hidden" <?php echo Lasso_Html_Helper::render_attrs( $attrs ); ?>>
										<i class="far fa-trash" ></i>
									</span>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</li>
			<?php endforeach; ?>
			<?php if ( $add_field_column ) { ?>
				<li class="row-content empty-group" data-order="<?php echo $order++ ?>">
					<ul>
					<?php foreach ( $list_product_mapping_in_table as $product_mapping ) { ?>
						<li>
							<?php
							$fields_wrapper_selector = 'sortable-row-fields-' . $field_group_id_generate . '-' .$product_mapping->get_lasso_id();
							$fields_wrapper_group_selector = 'sortable-row-fields-' . $field_group_id_generate;
							$attrs = array(
								'field-group-id' => $field_group_id_generate,
								'lasso-id'       => $product_mapping->get_lasso_id()
							);
							?>
							<ul class="sortable-row-fields <?php echo $fields_wrapper_selector . " " . $fields_wrapper_group_selector ?>" <?php echo Lasso_Html_Helper::render_attrs( $attrs ); ?>>
								<li class="btn-add-field btn-add-field-big" <?php echo Lasso_Html_Helper::render_attrs( $attrs ); ?>>
									<div class="row shadow url-details-field-box is-dismissable cursor-move h-100 icon-container">
										<div>
											<?php echo Lasso_Html_Helper::get_plus_icon(); ?>
											<h5>Click to add a Field</h5>
										</div>
									</div>
								</li>
							</ul>

							<?php $attrs['tooltip'] ='Add field'; ?>
							<span class='btn-add-field bottom-left cursor-pointer' <?php echo Lasso_Html_Helper::render_attrs( $attrs ); ?>>
								<i class="far fa-plus-circle add-field-icon"></i>
							</span>
						</li>
					<?php } ?>
					</ul>
				</li>
			<?php } ?>
		</ul>
	</div>
<?php } ?>
</div>

<div id="image-loading-wrapper" class="d-none">
	<div id="table-image-loading">
		<div class="py-5"><div class="ls-loader"></div></div>
	</div>
</div>
