<?php
/**
 * Table Horizontal
 *
 * @package table-horizontal
 */

use Lasso\Classes\Helper as Lasso_Helper;

use Lasso\Libraries\Lasso_URL;
use Lasso\Libraries\Table\Table_Field_Group;
use Lasso\Libraries\Table\Table_Field_Group_Detail;

use Lasso\Models\Fields;
use Lasso\Models\Table_Mapping;

/** @var Table_Mapping[] $table_products Table products mapping */
/** @var integer $link_id Link Location ID */

$field_group_ids = Table_Field_Group::get_list_field_group_id_by_table_id( $table->get_id() );
$hide_header_class = ! $table->get_show_headers_horizontal() ? 'hide-column-headers' : '';
$vertical_mobile_items = array();
?>

<div class="row-style template-1 horizontal-table">
	<table class="lasso-table <?php echo $hide_header_class; ?>">
        <?php if ( $table->get_show_headers_horizontal() ) : ?>
        <thead>
            <tr>
                <?php
                foreach ( $field_group_ids as $field_group_id ) {
                    $field_ids  = Table_Field_Group_Detail::get_list_field_id_by_field_group_id( $field_group_id );
                    $field_name = array();
                    foreach ( $field_ids as $field_id ) {
                        $field        = Fields::get_by_id( $field_id );
						if ( $field->get_field_name() ) {
							$field_name[] = $field->get_field_name();
						}
                    }
                    $field_name = implode( ' / ', $field_name );
                    ?>
                    <th scope="col"><span><?php echo ucwords($field_name); ?></span></th>
                <?php } ?>
            </tr>
        </thead>
        <?php endif; ?>
		<tbody>
		<?php foreach ( $table_products as $index => $table_product ) : ?>
			<?php
			$lasso_url = new Lasso_URL( $table_product->get_lasso_id() );
			$badge_text = trim( $table_product->get_badge_text() );
			?>
			<tr>
				<?php
				$field_groups = Table_Field_Group::get_list_field_by_table_id_lasso_id( $table_product->get_table_id(), $table_product->get_lasso_id() );
				foreach ( $field_groups as $i_group => $field_group ) {
					?>
					<td class="td-template-1">
						<div class="cell">
							<?php if ( 0 === $i_group && '' !== $badge_text) { ?>
								<div class="badge-text-wrapper">
									<span class="badge-text"><?php echo $badge_text ?></span>
								</div>
							<?php } ?>
							<?php $table_field_group_details = Table_Field_Group_Detail::get_list_by_lasso_id_field_group_id( $table_product->get_lasso_id(), $field_group->get_field_group_id() ); ?>
							<ul class="field-group">
								<?php
								foreach ( $table_field_group_details as $table_field_group_detail ) {
									$html = Lasso_Helper::include_with_variables(
										LASSO_PLUGIN_PATH . '/admin/views/fields/field-mapping.php',
										array(
											'table_field_mapping' => $table_field_group_detail,
											'table'               => $table,
											'final_primary_url'   => $table_product->get_final_primary_url(),
											'link_id'             => $link_id
										)
									);
									$vertical_mobile_items[ $index ]['badge'] = $badge_text;
									$vertical_mobile_items[ $index ]['fields'][] = array(
										'class' => array( 'field' ),
										'html'  => $html,
									);
									?>
									<li><?php echo $html; ?></li>
								<?php } ?>
							</ul>
						</div>
					</td>
				<?php } ?>
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
