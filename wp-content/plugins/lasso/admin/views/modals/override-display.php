<?php
/**
 * Modal
 *
 * @package Modal
 */

// phpcs:ignore
?>

<!-- IMPORT CONFIRMATION -->
<div class="modal fade" id="override-display" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content text-center shadow p-5 rounded">

			<h2>Update Link Settings</h2>
			<p class="text-left">
				Lasso will <strong class="override-display-set"></strong> the option <strong class="override-display-label"></strong>
			</p>

			<div>
				<button type="button" class="btn mx-1" data-dismiss="modal">
					Only New Links
				</button>
				<button type="button" class="btn mx-1 red-bg" data-dismiss="modal" onclick="overrideLinkSetting()">
					Override All Links
				</button>
			</div>

		</div>
	</div>
</div>

<script>
	function overrideLinkSetting() {
		if(globalOptionName !== '') {
			jQuery.ajax({
				url: ajax_url,
				type: 'post',
				data: {
					action: 'lasso_override_display_option',
					option_name: globalOptionName,
					option_value: globalOptionValue,
				},
			})
			.done(function(res) {
				console.log(res);
			})
		}
	}
</script>
