<?php
/** @var Table_Details $table */

use Lasso\Classes\Table_Mapping as Lasso_Table_Mapping;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;

use Lasso\Libraries\Lasso_URL;
use Lasso\Models\Table_Details;

?>

<div class="d-block p-4 text-break black hover-gray">
	<div class="row align-items-center table-row">
		<div class="col-lg pb-lg-0 pb-2 text-lg-left">
			<a href="<?php echo $table->get_link_detail()?>" class="black hover-purple-text font-weight-bold"><strong><?php echo $table->get_title() ?></strong></a>
		</div>
		<div class="col-lg-4 pl-5">
			<ul class="mb-0">
			<?php
			$links = "";
			$list_product_mapping_in_table = Lasso_Table_Mapping::get_list_by_table_id( $table->get_id() );
			$total_links = count( $list_product_mapping_in_table ) - 1;
			?>

			<?php if ( count( $list_product_mapping_in_table ) ): ?>
			<?php foreach ( $list_product_mapping_in_table as $key => $item ): ?>
				<?php $lasso_url = new Lasso_URL( $item->get_lasso_id() ); ?>
				<li>
					<a href="<?php echo $lasso_url->get_link_detail()?>" class="black hover-purple-text"><?php echo $lasso_url->name ?></a>
				</li>
			<?php endforeach; ?>
			<?php else: ?>
				<label data-tooltip="Click the table to add a product"><i> No products in this table yet. </i> <i class="far fa-info-circle light-purple"></i></label>
			<?php endif; ?>
			</ul>
		</div>
		<div class="col-lg-2 pl-5">
			<?php echo $table->get_theme() ?>
		</div>
		<div class="col-lg-2 px-4 pl-5">
			<?php echo $table->get_style_friendly_name() ?>
		</div>
		<div class="col-lg-1 text-center">
			<?php
			$total_locations = $table->get_total_locations();
			if ( 0 < $total_locations ) { ?>
			<span class="btn-show-table-locations"
				  <?php echo Lasso_Html_Helper::render_attrs(
				  	array(
				  		'table-id'              => $table->get_id(),
						'total-table-locations' => $table->get_total_locations()
					)) ?> ><?php echo $total_locations ?></span>
			<?php } else {
				echo 0;
			} ?>
		</div>
		<div class="col-lg-1 text-center">
			<?php
			$attrs = [
				'name'     => $table->get_title() ,
				'table-id' => $table->get_id() ,
			];
			?>
			<span class="btn-clone-table py-2" <?php echo Lasso_Html_Helper::render_attrs($attrs) ?>>
				<label data-tooltip="This will create a copy of this table."><i class="far fa-clone"></i></label>
			</span>
		</div>
	</div>
</div>