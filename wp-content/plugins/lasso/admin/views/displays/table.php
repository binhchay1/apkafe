<?php
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;
use Lasso\Classes\Setting_Enum as Lasso_Setting_Enum;

use Lasso\Libraries\Lasso_URL;
use Lasso\Libraries\Table\Table_Field_Group_Detail;

use Lasso\Models\Table_Details;
use Lasso\Models\Table_Mapping;
use Lasso\Models\Fields;

// ? table_id is used in the admin.
$table_id = $atts['table_id'] ?? null;
if ( is_null( $table_id ) ) {
	// ? id is used in the post
	$table_id = $atts['id'] ?? null;
}
$table = Table_Details::get_by_id( $table_id );

// ? Let theme be overridden in shortcode
if ( '' !== $theme ) {
	$table_theme_class = empty( $theme ) ? '' : sprintf( 'lasso-table-%s-theme', strtolower( $theme ) );
} else {
	$table_theme_class = ! $table || empty( $table->get_theme() ) ? '' : sprintf( 'lasso-table-%s-theme', strtolower( $table->get_theme() ) );	
}

$oldest_amazon = null;
$post = get_post();
$data_nosnippet = $atts['data_nosnippet'] ?? '';
$data_nosnippet_attr = '';
if ( 'enable' === $data_nosnippet ) {
	$data_nosnippet_attr = ' data-nosnippet';
}
?>

<?php if( $table ): ?>
	<?php $table_products = Table_Mapping::get_list_by_table_id( $table->get_id() ); ?>
	<?php
        foreach ( $table_products as $table_product ) {
            $lasso_url = new Lasso_URL( $table_product->get_lasso_id() );
            $title     = ! empty( $table_product->get_title() ) ? $table_product->get_title() : $lasso_url->name;

            $last_updated = $lasso_url->amazon->last_updated ?? '';
            $last_updated_oldest = $oldest_amazon ? ( $oldest_amazon->last_updated ?? '' ) : '';
            if ( ( $last_updated && $oldest_amazon && $last_updated > $oldest_amazon )
				|| ( $last_updated && ! $oldest_amazon )
            ) {
                $oldest_amazon = ! empty( $lasso_url->amazon->latest_price  ) ? $lasso_url->amazon : null;
            }
        }
	?>

	<?php if ( count( $table_products ) ): ?>
		<div id="<?php echo $anchor_id ?>" class="lasso-display-table ls-dp <?php echo $table_theme_class; ?>"<?php echo $data_nosnippet_attr ?>>
			<?php if ( Lasso_Helper::compare_string( Lasso_Setting_Enum::TABLE_STYLE_ROW, $table->get_style() ) ) : ?>
				<?php
				Lasso_Helper::include_with_variables(
					LASSO_PLUGIN_PATH . '/admin/views/displays/table-horizontal.php',
					array(
						'table'          => $table,
						'link_id'        => $link_id,
						'table_products' => $table_products
					),
					false
				);
				?>
			<?php elseif ( Lasso_Helper::compare_string( Lasso_Setting_Enum::TABLE_STYLE_COLUMN, $table->get_style() ) ): ?>
				<?php
				Lasso_Helper::include_with_variables(
					LASSO_PLUGIN_PATH . '/admin/views/displays/table-vertical.php',
					array(
						'table'          => $table,
						'link_id'        => $link_id,
						'table_products' => $table_products
					),
					false
				);
				?>
			<?php endif; ?>
		</div>

		<?php 
			$table_field_group_details = Table_Field_Group_Detail::get_list_field_id_by_table_id( $table->get_id() );
			$show_timestamp = false;
			foreach ($table_field_group_details as $field_group) {
				if ( $field_group->get_field_id() === Fields::PRICE_ID ) {
					$show_timestamp = true;
					break;
				}
			}
		?>
		<?php if ( ! is_null( $oldest_amazon ) && $show_timestamp ) : ?>
		<span class="oldest-updated"><?php echo ! is_null( $oldest_amazon ) && $show_timestamp ? $oldest_amazon->last_updated_format : ''; ?></span>
		<?php endif; ?>

	<?php endif; ?>
	
	<!-- BRAG -->
	<?php echo Lasso_Html_Helper::get_brag_icon(); ?>

<?php endif; ?>
