<?php
/**
 * Row
 *
 * @package Row
 */

// phpcs:ignore
?>

<!-- SINGLE URL -->
<div class="p-4 text-break black hover-gray" data-post-id="${ element.ID }">
	<div class="row align-items-center">
		<!-- IMAGE -->
		<div class="col-lg-1 text-center pb-lg-0 pb-3">
			<img src="${ element.thumbnail }" loading="lazy" class="rounded border" width="50" height="50">
		</div>

		<!-- NAME -->
		<div class="col-lg font-weight-bold text-lg-left text-center pb-lg-0 pb-1">
			<object><a href="edit.php?post_type=lasso-urls&page=url-details&post_id=${ element.post_id }" class="black hover-purple-text">${ element.post_title }</a></object>
		</div>

		<!-- PERMALINK -->
		<div class="col-lg text-lg-left text-center pb-lg-0 pb-3">
			/${ element.post_name }/
		</div>    
	</div>
</div>    
