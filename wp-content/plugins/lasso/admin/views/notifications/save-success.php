<?php
/**
 * Modal
 *
 * @package Modal
 */

// phpcs:ignore
?>

<div id="<?php echo $alert_id ?>" class="row alert green-bg white shadow mb-0 collapse" href="#<?php echo $alert_id ?>" data-toggle="collapse">
	<div class="col text-center p-3">
		<span>
			<strong><i class="far fa-hat-cowboy"></i> Yee haw!</strong> Your <?php echo $name ?> saved.
		</span>
		<button class="close white" href="#<?php echo $alert_id ?>" data-toggle="collapse">
			<i class="far fa-times-circle"></i>
		</button>
	</div>
</div>
