<div class="p-4 text-break hover-gray is-dismissable">
	<div class="row align-items-center">
		<!-- URL -->
		<div class="col-lg-4 font-weight-bold text-lg-left text-center pb-lg-0 pb-1">
			<a href="${ element.link_slug_original }" target="_blank" class="black hover-purple-text">
				${ element.link_slug_original }
			</a>&nbsp;
			<a href="edit.php?post_type=lasso-urls&page=domain-links&filter=${ element.link_slug_domain }" class="d-none light-purple hover-purple-text small cursor-pointer">
				<i class="far fa-filter"></i>
			</a>
		</div>

		<!-- CONTENT TITLE -->
		<div class="col-lg-4 text-lg-left text-center pb-lg-0 pb-1">
			<a href="${ element.post_edit_url }" target="_blank" class="black hover-purple-text">
				${ element.post_title }
			</a>&nbsp;
			<a href="edit.php?post_type=lasso-urls&page=content-links&post_id=${ element.detection_id }" class="d-none light-purple hover-purple-text small cursor-pointer">
				<i class="far fa-filter"></i>
			</a>
			<a href="${ element.permalink }" class="d-none light-purple hover-purple-text small cursor-pointer" target="_blank">
				<i class="far fa-external-link-alt"></i>
			</a>
		</div>

		<!-- MONETIZE SUGGESTION -->
		<div class="col-lg-2 text-lg-left text-center pb-lg-0 pb-1">
			<a href="edit.php?post_type=lasso-urls&page=url-details&post_id=${ element.lasso_id }" target="_blank" class="black hover-green-text">
				${ element.lasso_suggestion }
			</a>&nbsp;
			<?php if ( ! isset( $_GET['post_id'] ) ) { ?>
			<a href="edit.php?post_type=lasso-urls&page=link-opportunities&post_id=${ element.lasso_id }" class="d-none light-purple hover-purple-text small cursor-pointer">
				<i class="far fa-filter"></i>
			</a>
			<?php } ?>
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
					data-post-id="${ element.post_id }" data-link-id="${ element.link_id }">
				<span class="slider"></span>
			</label>
		</div>

		<!-- DISMISS -->
		<div class="opp-dismiss">
			<a href="#" class="js-dismiss-opportunity"
				data-toggle="modal" 
				data-target="#dismiss-opportunity"
				data-link-type="${ element.link_type == `keyword` ? `keyword` : `link` }"
				data-link-id="${ element.link_id }">
				<i class="far fa-times-circle"></i>
			</a>
		</div>
	</div>
</div>
