<?php
/**
 * Modal
 *
 * @package Modal
 */

// phpcs:ignore
?>

<!-- DELETE CONFIRMATION -->
<div class="modal fade" id="remove-attributes-confirm" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content text-center shadow p-5 rounded">

			<h2>Remove Lasso attributes?</h2>
			<p>
				If you choose to remove Lasso attributes we will no longer maintain an inventory of your links until you re-enable that feature. 
				Are you sure you want to continue?
			</p>

			<div>
				<button type="button" class="btn mx-1" data-dismiss="modal">
					Cancel
				</button>
				<button onclick="removeLassoAttributes()" type="button" class="btn red-bg mx-1">
					Continue
				</button>
			</div>

		</div>
	</div>
</div>
