<?php
/**
 * Modal
 *
 * @package Modal
 */

// phpcs:ignore
?>

<!-- IMPORT CONFIRMATION -->
<div class="modal fade" id="amazon-auto-monetize" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content text-center shadow p-5 rounded">

			<h2>Enable Auto-Amazon</h2>
			<p class="text-left">
				We recommend creating a backup first. This will allow you to undo this action quickly and ensure everything runs exactly as you hoped.
			</p>

			<div>
				<button type="button" class="btn black white-bg black-border mx-1" data-dismiss="modal" onclick="cancelAutoMonetize()">
					Create a Backup First
				</button>
				<button type="button" class="btn mx-1" data-dismiss="modal" onclick="enableAutoMonetize()">
					Enable
				</button>
			</div>

		</div>
	</div>
</div>

<script>

	function cancelAutoMonetize() {
		let autoMonetize = jQuery('input[name="auto_monetize_amazon"]');
		let amzTrackingIdWhitelist = jQuery('input[name="amazon_multiple_tracking_id"]');
		let amzTrackingIdWhiteListOldChecked = amzTrackingIdWhitelist.attr('data-old-checked') === 'true';

		if (amzTrackingIdWhiteListOldChecked === false) {
			amzTrackingIdWhitelist.trigger('click');
			amzTrackingIdWhitelist.attr('data-old-checked', true);
		}

		autoMonetize.prop('checked', false);
		window.open('https://support.getlasso.co/en/articles/3971900-how-to-create-a-backup-of-your-wordpress-website', '_blank').focus();
	}

	function enableAutoMonetize() {
		let autoMonetize = jQuery('input[name="auto_monetize_amazon"]');
		let trackingId = jQuery('input[name="amazon_tracking_id"]').val() || '';
		
		trackingId = trackingId.trim();

		if(trackingId !== '') {
			autoMonetize.prop('checked', true);
		} else {
			autoMonetize.prop('checked', false);
		}

	}
</script>
