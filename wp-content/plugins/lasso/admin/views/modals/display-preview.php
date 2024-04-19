<?php
/**
 * Modal
 *
 * @package Modal
 */

// phpcs:ignore
?>

<!-- DISPLAY PREVIEW -->
<div class="modal fade" id="display" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content shadow p-5 rounded">
			<h2 class="text-center mb-4"><i class="far fa-pager"></i> Single Link Display Preview</h2>
			<div class="js-preview-display">
				<?php echo do_shortcode( '[lasso id="' . $post_id . '"]' ); ?>
			</div>
		</div>
	</div>
</div>
