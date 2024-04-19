<?php
/**
 * Row
 *
 * @package Row
 */

// phpcs:ignore
?>

<div class="d-block p-4 text-break black hover-gray">
	<div class="row align-items-center">
		<!-- IMAGE -->
		<div class="col-lg-1 text-center pb-lg-0 pb-3">
			<img src="${ element.image_url }" loading="lazy" class="pill border" width="50" height="50">
		</div>

		<!-- AFFILIATE PROGRAM NAME -->
		<div class="col-lg-6 pb-lg-0 pb-3">
			<a href="${ element.permalink }" target="_blank" class="black hover-purple-text"><strong>${ element.affiliate_name }</strong></a>
			<small class="d-block dark-gray">${ element.description }</small>
		</div>

		<!-- COMMISSION -->
		<div class="col-lg-2 pb-lg-0 pb-3 text-center">
			${ element.commission_rate }
		</div>

		<!-- LINKS -->
		<div class="col-lg-2 text-center pb-lg-0 pb-3 font-weight-bold large">
			<a href="edit.php?post_type=lasso-urls&page=domain-links&filter=${ element.link_slug_domain }" class="black hover-purple-text">
				${ element.count }
			</a>
		</div>

		<!-- SIGNUP BUTTON -->
		<div class="col-lg-1 text-center pb-lg-0 px-1 pb-3 font-weight-bold large">
			<a href="${ element.signup_page }" target="_blank" class="btn btn-sm">
				Signup
			</a>
		</div>
	</div>
</div>   
