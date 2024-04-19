<?php
/**
 * Modal
 *
 * @package Modal
 */

use Lasso\Classes\Helper;

// phpcs:ignore

$lasso_cron = new Lasso_Process_Force_Scan_All_Posts();
$eta = $lasso_cron->get_eta();
$time = Helper::seconds_to_time( $eta );
?>

<div id="link-build-running" class="row alert orange-bg white shadow mb-0 collapse show">
	<div class="col text-center p-3">
		<span>
			We are creating an index of your links. This will be complete in <?php echo $time ?>. Please wait until then to begin any Imports.
		</span>
	</div>
</div>
