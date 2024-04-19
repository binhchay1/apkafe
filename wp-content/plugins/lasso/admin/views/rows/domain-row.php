<?php
/**
 * Row
 *
 * @package Row
 */

// phpcs:ignore
?>

<a href="edit.php?post_type=lasso-urls&page=domain-links&filter=${ encodeURIComponent(element.link_slug_domain) }" class="d-block p-4 text-break black hover-gray">
	<div class="row align-items-center">
		<!-- DOMAIN -->
		<div class="col-lg-10 pb-lg-0 pb-3 font-weight-bold text-lg-left text-center">
			${ element.link_slug_domain }
		</div>

		<!-- NOTIFICATIONS -->
		<div class="col-lg-1 text-center pb-lg-0 pb-3 green-tooltip" data-tooltip="This domain has possible opportunities">
			${ element.monetize_with ? `<i class="far fa-lightbulb-on fa-lg green"></i>` : `` }
		</div>

		<!-- LINKS BUTTON -->
		<div class="col-lg-1 text-center pb-lg-0 pb-3 font-weight-bold large">
			${ element.count }
		</div>
	</div>
</a>   
