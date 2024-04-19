<?php
/**
 * Post content history - row
 *
 * @package Post content history - row
 */

// phpcs:ignore
?>

<!-- SINGLE POST CONTENT HISTORY -->
<div class="d-block p-4 text-break black hover-gray">
	<div class="row align-items-center">

		<!-- POST ID -->
		<div class="col-lg-1 text-center pb-lg-0 pb-3">
			${ element.post_id }
		</div>

		<!-- POST NAME -->
		<div class="col-lg-5 text-lg-left text-center pb-lg-0 pb-1">
			<span>${ element.post_title }</span>
			&nbsp;
			<a href="${ element.edit_url }" class="d-none light-purple hover-purple-text small cursor-pointer" target="_blank">
				<i class="far fa-filter"></i>
			</a>
			<a href="${ element.view_url }" class="d-none light-purple hover-purple-text small cursor-pointer" target="_blank">
				<i class="far fa-external-link-alt"></i>
			</a>
		</div>

		<!-- POST TYPE -->
		<div class="col-lg-2 d-lg-block">
			${ element.post_type }
		</div>

		<!-- MODIFIED DATE -->
		<div class="col-lg-2 text-center">
			${ element.updated_date }
		</div>

		<!-- ACTION -->
		<div class="large col-lg-2 text-center">
			<a href="edit.php?post_type=lasso-urls&page=post-content-history-detail&id=${ element.history_id }" class="btn green-bg">View</a>
			<button class="btn red-bg post-content-revert"
					data-history-id="${ element.history_id }"
					data-post-name="${ element.post_title }">
				Revert
			</button>
		</div>
	</div>
</div>
