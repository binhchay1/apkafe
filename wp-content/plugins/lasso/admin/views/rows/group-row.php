<?php
/**
 * Row
 *
 * @package Row
 */

// phpcs:ignore
?>

<a href="edit.php?post_type=lasso-urls&page=group-urls&post_id=${ element.term_id }&urls=${ element.count }" class="d-block p-4 text-break black hover-gray">
	<div class="row align-items-center">
		<!-- NAME -->
		<div class="col-lg pb-lg-0 pb-3 font-weight-bold text-lg-left text-center">
			${ element.post_title }
		</div>

		<!-- DESCRIPTION -->
		<div class="col-lg pb-lg-0 pb-3 text-lg-left text-center">
			${ element.description }
		</div>

		<!-- LASSO URLs -->
		<div class="col-lg-1 text-center pb-lg-0 pb-3 font-weight-bold large">
			${ element.count }
		</div>
	</div>
</a>  
