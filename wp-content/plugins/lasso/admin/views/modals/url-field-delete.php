<?php
/**
 * Modal
 *
 * @package Modal
 */

// phpcs:ignore
?>

<!-- FIELD DELETE CONFIRMATION -->
<div class="modal fade" id="field-delete" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content text-center shadow p-5 rounded">

			<h2>Remove "<span id="js-field-name">This</span>"?</h2>
			<p>If removed, you won't be able to get its value back.</p>

			<div>
				<button type="button" class="btn black white-bg black-border mr-3" data-dismiss="modal">
					Cancel
				</button>
				<button id="js-field-remove-button" type="button" class="btn red-bg mx-1">
					Remove
				</button>
			</div>

		</div>
	</div>
</div>
