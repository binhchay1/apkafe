<?php

/** @var object $field_data $field_data is value join between lasso_fields and lasso_field_mapping tables */

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;

use Lasso\Models\Field_Mapping;
use Lasso\Models\Fields;

$field_mapping = new Field_Mapping( $field_data );
$field = new Fields( $field_data );
?>

<?php if( in_array( $field->get_id(), array(Fields::PROS_FIELD_ID, Fields::CONS_FIELD_ID) ) ) { ?>
	<div class="lasso-fields-<?php echo strtolower($field->get_field_name()); ?> lasso-fields-<?php echo $field->get_id() ?? 'demo'; ?>">
		<strong><?php echo $field->get_field_name(); ?>:</strong>
		<?php echo Lasso_Html_Helper::render_pros_cons_field( $field->get_id(), $field_mapping->get_field_value() ); ?>
	</div>
<?php } elseif( in_array( $field->get_field_type() ?? '', array('rating') ) ) { ?>
<div class="lasso-fields-single">
	<strong><?php echo $field->get_field_name(); ?>:</strong>
	<div class="lasso-stars" style="--rating: <?php echo $field_mapping->get_field_value(); ?>">
		<span class="lasso-stars-value">
			<?php echo Lasso_Helper::show_decimal_field_rate( $field_mapping->get_field_value() ); ?>
		</span>
	</div>
</div>
<?php } elseif ( in_array( $field->get_field_type(), array( Fields::FIELD_TYPE_BULLETED_LIST, Fields::FIELD_TYPE_NUMBERED_LIST ) ) ) { ?><!-- List field -->
<div class="lasso-fields-single">
	<strong><?php echo $field->get_field_name(); ?>:</strong>
	<div class="lasso-fields-<?php echo sanitize_title( $field->get_field_name() ); ?> lasso-fields-<?php echo $field->get_id(); ?>">
		<?php echo Lasso_Html_Helper::render_list_field( $field->get_field_type(), $field_mapping->get_field_value() ); ?>
	</div>
</div>
<?php } else { ?>
<div class="lasso-fields-single">
	<strong><?php echo $field->get_field_name(); ?>:</strong>
	<?php echo nl2br($field_mapping->get_field_value()); ?>
</div>
<?php } ?>
