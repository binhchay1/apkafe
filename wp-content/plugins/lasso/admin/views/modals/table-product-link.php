<?php
/**
 * Modal
 *
 * @package Modal
 */

// phpcs:ignore
?>

<!-- TABLE ADD PRODUCT LINK -->
<div id="link-monetize" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content p-0 shadow">

			<!-- HEADER -->
			<div class="row px-5 pt-5">
				<div class="col-lg mb-4 text-lg-left text-center">
					<h2><?php echo isset( $modal_title ) ? esc_html( $modal_title ) : 'Monetize' ?></h2>
				</div>
				<div class="col-lg mb-4">
					<input type="text" class="form-control js-monetize-search" placeholder="Search 328 URLs">
				</div>
			</div>

			<!-- ROW -->
			<div class="d-none js-rows-template">
				<?php require LASSO_PLUGIN_PATH . '/admin/views/rows/monetize-row.php'; ?>
			</div>
			<div class="js-rows">
			</div>

			<!-- PAGINATION -->
			<div class="px-5">
				<div class="js-pagination-popup table-product-popup-pagination row align-items-center simple-pagination">
					<ul class="js-pagination-popup pagination justify-content-end mb-0">
						<li class="page-item disabled"><span class="page-link active prev"><i class="far fa-angle-double-left fa-lg" aria-hidden="true"></i> Previous</span></li>
						<li class="page-item active"><span class="page-link active">1</span></li>
						<li class="page-item disabled"><span class="page-link active next">Next <i class="far fa-angle-double-right fa-lg" aria-hidden="true"></i></span></li>
					</ul>
				</div>
			</div>

		</div>
	</div>
</div>
