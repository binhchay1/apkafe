<?php
/**
 * Modal
 *
 * @package Modal
 */

// phpcs:ignore
?>

<!-- SAVED KEYWORDS -->
<div id="saved-keywords" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content p-0 shadow">

		<!-- HEADER -->
			<div class="row px-5 pt-5">
				<div class="col-lg mb-4 text-lg-left text-center">
					<h2>Saved Keywords</h2>
				</div>
			</div>
			<div class="row px-5">
				<div class="col-lg mb-3">
					<input type="text" class="form-control js-keywords-search" placeholder="Search 328 Keywords">
				</div>
			</div>

			<!-- ROW -->
			<div class="d-none js-rows-template">
				<?php require LASSO_PLUGIN_PATH . '/admin/views/rows/saved-keyword-row.php'; ?>
			</div>
			<div class="js-rows">
			</div>

			<!-- PAGINATION -->
			<div class="px-5">
				<div class="js-pagination-popup pagination row align-items-center simple-pagination">
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
