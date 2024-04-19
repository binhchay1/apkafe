<?php
/**
 * Row
 *
 * @package Row
 */

// phpcs:ignore
?>

<a href="edit.php?post_type=lasso-urls&page=content-links&post_id=${ element.post_id }" class="d-block p-4 text-break black hover-gray">
	<div class="row align-items-center">
		<!-- DATE -->
		<div class="col-lg-2 pb-lg-0 pb-3 text-lg-left text-center">
			${ element.post_modified_string }
		</div>

		<!-- CONTENT -->
		<div class="col-lg-5 pb-lg-0 pb-3 font-weight-bold text-lg-left text-center">
			${ element.detection_title }
		</div>

		<!-- PERMALINK -->
		<div class="col-lg-4 pb-lg-0 pb-3 text-lg-left text-center">
			<object><a href="${ element.detection_slug }" class="black hover-purple-text" target="_blank">${ element.short_detection_slug }</a></object>
		</div>

		<!-- TOTAL LINKS (MONETIZED & UNMONETIZED) -->
		<div class="large col-lg-1 text-center pb-lg-0 pb-3 font-weight-bold">
			${ element.count }
		</div>
	</div>
</a>    
