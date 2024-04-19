<div class="p-4 text-break hover-gray is-dismissable collapse show" data-toggle="collapse">
	<div class="row align-items-center">
		<!-- KEYWORD WITH CONTEXT POPUP -->
		<div class="col-lg-4 font-weight-bold text-lg-left text-center pb-lg-0 pb-1">
			<a class="black hover-purple-text"
				data-toggle="modal" 
				data-target="#link-preview" 
				data-id="${ element.keyword_location_id }">${ element.anchor_text }</a>
			<?php if ( ! isset( $_GET['keyword'] ) ) { ?>
			&nbsp;
			<a href="edit.php?post_type=lasso-urls&page=keyword-opportunities&keyword=${ element.anchor_text }" class="d-none light-purple hover-purple-text small">
				<i class="far fa-filter"></i>
			</a>
			</a>
			<?php } ?>
		</div>

		<!-- CONTENT TITLE -->
		<div class="col-lg-7 text-lg-left text-center pb-lg-0 pb-1">
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

		<div class="d-none js-display-preview js-link-preview-html" data-type="keyword-link">
			${ element.display_preview }
		</div>

		<!-- MONETIZE TOGGLE -->
		<div class="col-lg-1 text-center">
			<label class="toggle m-0">
				<input class="js-toggle" type="checkbox" data-monetize-status="${ toggle_checked }" ${ toggle_checked } 
					data-old-keyword="${ element.link_slug_original }"
					data-link-type="${ element.link_type }"
					data-post-id="${ element.post_id }" data-link-id="${ element.link_id }" data-keyword-location-id="${ element.link_id }">
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
