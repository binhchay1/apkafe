<?php
/**
 * URL links
 *
 * @package Lasso URL links
 */

$bulk_revert = $_GET['bulk-revert'] ?? '';
?>

<div class="row align-items-center">
	<!-- TITLE -->
	<div class="col-lg mb-4 text-lg-left text-center">
		<h1 class="m-0 mr-2 d-inline-block align-middle">Import</h1>

		<a href="https://support.getlasso.co/en/articles/4005802-how-to-import-link-from-another-plugin" target="_blank" class="btn btn-sm learn-btn">
			<i class="far fa-info-circle"></i> Learn
		</a>

		<button id="btn-bulk-import" class="btn btn-sm">
			Bulk Import
		</button>

		<?php if ( ! empty( $bulk_revert ) ) { ?>
			<button class="btn btn-sm red-bg" data-toggle="modal" data-target="#revert-all-confirm">
				Bulk Revert
			</button>
		<?php } ?>
	</div>

	<!-- FILTERS
	<div class="col-lg text-center large mb-4">
		<ul class="nav justify-content-center font-weight-bold">
			<li class="nav-item mx-3 blue-tooltip" data-tooltip="See all AAWP URLs">
				<a class="nav-link blue hover-underline px-0"><i class="fab fa-amazon"></i> 69</a>
			</li>
			<li class="nav-item mx-3 green-tooltip" data-tooltip="See all Thirsty Affiliate URLs">
				<a class="nav-link green hover-underline px-0"><i class="far fa-beer"></i> 420</a>
			</li>
			<li class="nav-item mx-3 orange-tooltip" data-tooltip="See all Pretty Links URLs">
				<a class="nav-link orange hover-underline px-0"><i class="far fa-star"></i> 311</a>
			</li>
		</ul>
	</div>
	-->

    <div class="col-lg text-center large mb-4">
        <select name="filter_plugin" id="filter-plugin" class="form-control">
            <option value="">All Plugins</option>
        </select>
    </div>

	<!-- IMPORT SEARCH -->
	<div class="col-lg-4 mb-4">
		<form role="search" method="get" id="links-filter" autocomplete="off">
			<div id="search-links">
				<input type="search" id="link-search-input" name="link-search-input" class="form-control" placeholder="Search URLs to Import">
			</div>
		</form>
	</div>

</div>
