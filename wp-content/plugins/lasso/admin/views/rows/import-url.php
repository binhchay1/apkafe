<?php
/**
 * Row
 *
 * @package Row
 */

// phpcs:ignore
?>

<!-- SINGLE LINK -->
<div class="p-4 hover-gray">
	<div class="row align-items-center">
		<div class="col-3" title="${ element.post_title }">
			<strong>${ element.post_title.length > 100 ? element.post_title.substring(0, 100) + '...' : element.post_title }</strong>
		</div>

		<div class="col-6">
			${ element.shortcode != '' ? 
				`
					${ element.shortcode }
				` 
				: 
				`
				<a href="${ element.import_permalink }" title="${ element.import_permalink }" target="_blank" class="purple underline">
					${ element.import_permalink.length > 100 ? element.import_permalink.substring(0, 100) + '...' : element.import_permalink }
				</a>
				` 
			}
		</div>

		<div class="col-1">
			${ element.import_source }
		</div>

		<div class="col-1 text-center">
			${ 
			element.check_status == `checked` && element.locations > 0 
				? `<button class="btn btn-sm black white-bg black-border show-locations"
						data-import-id="${ element.id }"
						data-locations="${ element.locations }">
						See It
					</button>`
				:``
			}
		</div>

		<div class="col-1 text-center">
			${ element.check_status == `checked` ? 
				`<button ${ element.import_source == `SiteStripe` ? `disabled` : `` }
					class="btn btn-sm red-bg js-toggle-revert" 
					data-import-id="${ element.id }" 
					data-post-title="${ element.post_title }" 
					data-import-permalink="${ element.import_permalink }"
					data-post-type="${ element.post_type }"
					data-import-source="${ element.import_source }">
					Revert
				</button>
				<i class="far fa-check-circle fa-2x gray d-none"></i>
				`
				:
				`<button class="btn btn-sm js-toggle-import" 
					data-import-id="${ element.id }" 
					data-post-title="${ element.post_title }" 
					data-import-permalink="${ element.import_permalink }"
					data-post-type="${ element.post_type }"
					data-import-source="${ element.import_source }">
					Import
				</button>
				<i class="far fa-check-circle fa-2x green d-none"></i>
				`
			}
		</div>
	</div>
</div>
