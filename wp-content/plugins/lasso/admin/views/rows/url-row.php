<?php
/**
 * Row
 *
 * @package Row
 */

// phpcs:ignore
?>

<!-- SINGLE URL -->
<div class="d-block p-4 text-break black hover-gray dashboard-row">
	<div class="row align-items-center">

	<!-- IMAGE -->
		<div class="col-lg-1 text-center pb-lg-0 pb-3">
			<a href="${ element.lasso_edit_url }" class="img-wrapper">${ image }</a>
            <div class="w-50 checkbox-wrapper">
                ${ link_count != 0 ?
                `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" name="url-row-checkbox[]" data-post-id="${ element.post_id }" disabled>
                        <label data-tooltip="You can't delete a URL if it has active links or displays"><i class="far fa-info-circle light-purple"></i></label>
                    </div>
                ` :
                `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" name="url-row-checkbox[]" data-post-id="${ element.post_id }">
                    </div>
                `
                }
            </div>
		</div>

		<!-- TITLE -->
		<div class="col-lg-6 text-lg-left text-center pb-lg-0 pb-1">
			<a href="${ element.lasso_edit_url }" class="black hover-purple-text font-weight-bold">
				${ element.post_title }
			</a>
			<br>
			<a href="${ element.link_slug }" class="dark-gray hover-purple-text small cursor-pointer" target="_blank">
				${ element.link_slug }
			</a>
		</div>

		<!-- GROUPS -->
		<div class="col-lg-3 d-lg-block">
			${ categories }
		</div>

		<!-- NOTIFICATIONS -->
		<div class="col-lg-1 text-center">      
			${ element.broken_link == 0 ? 
			`` : 
			`<div class="d-inline-block red-tooltip mx-1" data-tooltip="This URL is broken.">
				<a href="${ element.lasso_edit_url }"><i class="far fa-unlink fa-lg red"></i></a>
			</div>`
			}
			${ element.out_of_stock == 0 ? 
			`` : 
			`<div class="d-inline-block orange-tooltip mx-1" data-tooltip="This product may be out of stock.">
				<a href="${ element.lasso_edit_url }"><i class="far fa-box-open fa-lg orange"></i></a>
			</div>`
			} 
			${ element.opportunities == 0 ? 
			`` : 
			`<div class="d-inline-block green-tooltip mx-1" 
				data-tooltip="This URL has ${ element.opportunities } possible ${ element.opportunities == 1 ? `opportunity` : `opportunities` }.">
				<a href="edit.php?post_type=lasso-urls&page=url-opportunities&post_id=${ element.post_id }"><i class="far fa-lightbulb-on fa-lg green"></i></a>
			</div>`
			} 
		</div>

		<!-- LINKS -->
		<div class="large col-lg-1 text-center">
			<a href="edit.php?post_type=lasso-urls&page=url-links&post_id=${ element.post_id }" class="black hover-purple-text font-weight-bold">${ link_count }</a>
		</div>

	</div>
</div>    
