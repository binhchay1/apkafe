<?php
/**
 * Modal
 *
 * @package Modal
 */

// phpcs:ignore
?>

<!-- IMPORT CONFIRMATION -->
<div class="modal fade" id="revert-all-confirm" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content text-center shadow p-5 rounded">

			<h2>Revert All Links?</h2>
			<p>This will transfer all imported links from Lasso into their original plugins. Note: this action removes the links from Lasso.</p>

			<div>
				<button type="button" class="btn mx-1" data-dismiss="modal">
					Cancel
				</button>
				<button type="button" class="btn red-bg mx-1 js-revert-all-button">
					Revert All
				</button>
			</div>

		</div>
	</div>
</div>
