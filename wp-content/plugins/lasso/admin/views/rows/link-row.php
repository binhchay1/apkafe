<?php
/**
 * Row
 *
 * @package Row
 */

// phpcs:ignore
?>

<div class="p-4 text-break hover-gray">
	<div class="row align-items-center">
		<!-- URL -->
		<div class="col-lg-5 font-weight-bold text-lg-left text-center pb-lg-0 pb-1">
			<a href="${ element.link_slug_original }" target="_blank" class="black hover-purple-text">${ element.link_slug_original }</a>
		</div>

		<!-- CONTENT TITLE -->
		<div class="col-lg-5 text-lg-left text-center pb-lg-0 pb-1">
			<a href="${ element.post_edit_url }" target="_blank" class="black hover-purple-text">
				${ element.post_title }
			</a>&nbsp;
			<a href="${ element.link_slug_original }" class="green d-none" target="_blank">
				<i class="far fa-external-link-alt"></i>
			</a>
		</div>

		<!-- LINK TYPE -->
		<div class="col-lg-1 text-center pb-lg-0 pb-3 ${ element.link_report_color }-tooltip" data-tooltip="This is ${ element.link_report_tooltip }.">
			<a class="${ element.link_report_color }" data-toggle="modal" 
				data-target="#link-preview" data-id="${ element.link_location_id }">
				<i class="far ${ element.link_report_type } fa-lg"></i>
			</a>            
		</div>

		<div class="d-none js-display-preview js-link-preview-html" data-type="${ display_type }">
			${ element.display_preview }
		</div>

		<!-- MONETIZE TOGGLE -->
		<div class="col-lg-1 text-center">
			<label class="toggle m-0">
				<input class="js-toggle" type="checkbox" data-monetize-status="${ toggle_checked }" ${ toggle_checked } 
					data-old-keyword="${ element.link_slug_original }"
					data-link-type="${ element.link_type }"
					data-post-id="${ element.detection_id }" data-link-id="${ element.link_location_id }">
				<span class="slider"></span>
			</label>
		</div>
	</div>
</div>
