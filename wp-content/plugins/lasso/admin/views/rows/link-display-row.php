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
		<!-- CONTENT TITLE -->
		<div class="col-lg-7 font-weight-bold text-lg-left text-center pb-lg-0 pb-1">
			<a href="post.php?post=${ element.detection_id }&action=edit" target="_blank" class="black hover-purple-text">
				${ element.post_title }
			</a>&nbsp;
			<a href="edit.php?post_type=lasso-urls&page=content-links&post_id=${ element.detection_id }" class="d-none light-purple hover-purple-text small">
				<i class="far fa-filter"></i>
			</a>
			<br>
			<a href="${ element.detection_slug }" class="dark-gray hover-purple-text small" target="_blank">
				${ element.detection_slug }
			</a>
		</div>

		<!-- ANCHOR TEXT -->
		<div class="col-lg-4 dotted text-lg-left text-center pb-lg-0 pb-1">
			<a data-toggle="modal" 
				data-target="#link-preview" 
				data-id="${ element.display_type == `Text` ? element.link_id : element.lasso_id }"
				class="black hover-purple-text">
				${ element.link_report_type == `fa-link` ? element.anchor_text : `` } 
				${ element.link_report_type == `fa-image` ? element.img_src : `` } 
				${ element.link_report_type == `fa-pager` ? element.display_box : `` } 
			</a>
		</div>

		<div class="col-lg-1 text-center">
			<span class="green green-tooltip" data-tooltip="This is ${ element.link_report_tooltip }.">
				<a data-toggle="modal" 
					data-target="#link-preview" 
					data-id="${ element.display_type == `Text` ? element.link_id : element.lasso_id }"
					class="black hover-purple-text">
					<i class="fa-lg far ${ element.link_report_type } ${ element.link_report_type == `fa-link` ? `` : `fa-lg` }"></i>
				</a>
			</span>
		</div>

		<div class="d-none js-display-preview js-link-preview-html" data-type="${ display_type }">
			${ element.display_preview }
		</div>
	</div>
</div>
