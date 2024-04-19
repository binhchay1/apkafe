<?php
/**
 * Modal
 *
 * @package Modal
 */

// phpcs:ignore
?>

<div id="<?php echo $alert_id ?>" class="row alert red-bg white shadow mb-0 collapse show" href="#<?php echo $alert_id ?>" data-toggle="collapse">
	<div class="col text-center p-3">
		<span>
			<strong><i class="far fa-skull-cow"></i> Try again.</strong> For some reason your <?php echo $name ?> didn't save.
		</span>
		<button class="close white" href="#<?php echo $alert_id ?>" data-toggle="collapse">
			<i class="far fa-times-circle"></i>
		</button>
	</div>
</div>
