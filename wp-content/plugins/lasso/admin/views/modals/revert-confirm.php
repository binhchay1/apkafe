<?php
/**
 * Modal
 *
 * @package Modal
 */

// phpcs:ignore
?>                

<!-- IMPORT CONFIRMATION -->
<div class="modal fade" id="revert-confirm" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog" role="document">
		<div class="modal-content text-center shadow p-5 rounded">

			<h2>Revert This Import?</h2>
			<p>This will transfer the link from Lasso back to your old plugin. Note: this action removes the link from Lasso and can not be undone.</p>

			<div>
				<button type="button" class="btn green-bg mx-1 js-revert-cancel" data-dismiss="modal">
					Keep
				</button>
				<button type="button" class="btn red-bg mx-1 js-revert-button">
					Revert
				</button>
			</div>

		</div>
	</div>
</div>
