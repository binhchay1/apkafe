<?php
/**
 * Modal
 *
 * @package Modal
 */

// phpcs:ignore
?>

<!-- IMPORT CONFIRMATION -->
<div class="modal fade" id="import-confirm" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" >
	<div class="modal-dialog" role="document">
		<div class="modal-content text-center shadow p-5 rounded">

			<h2>Import This Link?</h2>
			<p>This will transfer and remove the link or shortcode from your old plugin into Lasso and replace it in all posts.</p>

			<div>
				<button type="button" class="btn red-bg mx-1 js-import-cancel" data-dismiss="modal">
					Cancel
				</button>
				<button type="button" class="btn mx-1 js-import-button">
					Import
				</button>
			</div>

		</div>
	</div>
</div>
