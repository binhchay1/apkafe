<?php
/**
 * Modal
 *
 * @package Modal
 */

$amazon_primary_tracking_id = Lasso_Amazon_Api::get_amazon_tracking_id_by_url( $notification_url );
?>

<div id="amazon-url-detected" class="row alert orange-bg white shadow mb-0 collapse show" data-toggle="collapse">
	<div class="col text-center font-weight-bold p-3">
		<?php if ( '' !== $amazon_primary_tracking_id ) { ?>
			Lasso doesn't have your Amazon Tracking ID saved. Would you like to set 
			<strong><?php echo $amazon_primary_tracking_id; ?></strong> 
			as your primary tracking ID? <a href="#" id="btn-tracking-id-save" data-tracking-id="<?php echo $amazon_primary_tracking_id; ?>" class="btn ml-2">Yes</a> 
			<a href="#amazon-url-detected" class="btn red-bg ml-1" data-toggle="collapse">No</a>
		<?php } else { ?>
			Lasso doesn't have your Amazon Tracking ID saved. <a href="edit.php?post_type=lasso-urls&page=settings-amazon" target="_blank" class="btn ml-2">Set Amazon Tracking ID</a>     
		<?php } ?>
	</div>
</div>
