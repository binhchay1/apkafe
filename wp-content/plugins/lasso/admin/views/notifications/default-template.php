<?php
/**
 * Modal
 *
 * @package Modal
 */

// phpcs:ignore
?>

<div id="<?php echo $alert_id ?>" class="row alert <?php echo $alert_bg ?> white shadow mb-0 collapse show" href="#<?php echo $alert_id ?>" data-toggle="collapse">
	<div class="col text-center p-3">
		<span><?php echo $message ?></span>
		<button class="close white" href="#<?php echo $alert_id ?>" data-toggle="collapse">
			<i class="far fa-times-circle"></i>
		</button>
	</div>
</div>
