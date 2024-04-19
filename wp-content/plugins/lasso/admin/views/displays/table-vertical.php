<?php
/**
 * Table Vertical
 *
 * @package table-vertical
 */

/** @var integer $link_id Link Location ID */

use Lasso\Classes\Helper as Lasso_Helper;

use Lasso\Libraries\Lasso_URL;
use Lasso\Libraries\Table\Table_Field_Group_Detail;

use Lasso\Models\Table_Mapping;

?>

<?php if( ! empty( $table_products ) ): ?>
	<?php
		$total_links           = count( $table_products );
		$vertical_mobile_items = array();
		$first_link            = $table_products[0];
		$td_width              = round( 100 / $total_links, 2 );
		$column_style          = 'style="width: ' . $td_width . '%"';
		$class = '';
		if ( count( $table_products ) > 4 ) {
			$column_style = '';
			$class = 'table-vertical-scroll';
		}

	?>
	<div class="table-vertical table-vertical-desktop template-1<?php echo Table_Mapping::has_badge( $table_products ) ? ' include-badge' : '' ?> <?php echo $class ?> ">
		<table class="lasso-table table-borderless table-title">
			<tbody>
			<?php
			$table_field_group_details = Table_Field_Group_Detail::get_list_field_id_by_table_id( $table->get_id() );
			foreach ( $table_field_group_details as $i_group => $table_field_group_detail ) : ?>
				<tr>
					<?php $is_set_noboder_left_to_next_cell = false; ?>
					<?php foreach( $table_products as $index => $table_product ) :?>
						<?php
						$lasso_url     = Lasso_URL::get_by_lasso_id( $table_product->get_lasso_id() );

						$field_group_detail = Table_Field_Group_Detail::get_by_field_id_field_group_id_lasso_id( $table_field_group_detail->get_field_id(), $table_field_group_detail->get_field_group_id(), $table_product->get_lasso_id() );
						$html = Lasso_Helper::include_with_variables(
							LASSO_PLUGIN_PATH . '/admin/views/fields/field-mapping.php',
							array(
								'table_field_mapping' => $field_group_detail,
								'table'               => $table,
								'final_primary_url'   => $table_product->get_final_primary_url(),
								'link_id'             => $link_id
							)
						);

						$badge_text = trim( $table_product->get_badge_text() );
						$vertical_mobile_items[ $index ]['badge'] = $badge_text;
						$vertical_mobile_items[ $index ]['fields'][] = array(
							'class' => array( 'field' ),
							'html'  => $html,
						);
						?>
						<td class="text-center field field-group-detail" <?php echo $column_style; ?>>
							<div class="cell<?php echo '' !== $badge_text? ' has-badge-text' : '' ?><?php echo $is_set_noboder_left_to_next_cell ? ' no-border-left' : '' ?>">
								<?php if ( 0 === $i_group ) { ?>
									<div class="badge-text-wrapper">
										<span class="badge-text"><?php echo '' === $badge_text ? '&nbsp;' : $badge_text ?></span>
									</div>
								<?php } ?>
								<div class="cell-content">
								<?php echo $html ?>
								</div>
							</div>
						</td>
						<?php $is_set_noboder_left_to_next_cell = empty( $badge_text ) ? false : true; ?>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<?php 
	Lasso_Helper::include_with_variables(
		LASSO_PLUGIN_PATH . '/admin/views/displays/table-mobile.php', 
		array(
			'vertical_mobile_items' => $vertical_mobile_items,
		),
		false
	); 
	?>
<?php endif; ?>