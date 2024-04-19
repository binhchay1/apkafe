<?php
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Table_Detail as Lasso_Table_Detail;
use Lasso\Classes\Setting_Enum as Lasso_Setting_Enum;

use Lasso\Models\Table_Details;

$table_id    = $_GET['id'] ?? '' ;
$table_title = "Untitled Table";
if ( $table_id ) {
	$lasso_table_detail = Table_Details::get_by_id( $table_id );
	$table_title        = $lasso_table_detail->get_title();
} else {
	$table_title = Lasso_Table_Detail::generate_table_title();
}

$page_name = Lasso_Helper::get_page_name();
$show_search_table = true;
if ( $page_name === Lasso_Setting_Enum::PAGE_TABLES ) {
	$page_title = "Tables";
} else {
	$page_title = $table_title;
    $show_search_table = false;
}
$link_count = ( new Table_Details() )->total_count();
?>
<!-- TITLE BAR -->
<div class="row align-items-center">

	<!-- TITLE -->
	<div class="col-lg text-lg-left text-center mb-4">
		<h1 class="m-0 mr-2 d-inline-block align-middle table-title"><?php echo $page_title; ?></h1>
		<a href="https://support.getlasso.co/en/articles/6217135-how-to-use-comparison-tables" target="_blank" class="btn btn-sm learn-btn">
			<i class="far fa-info-circle"></i> Learn
		</a>
		<span id="create-new-table" class="btn ml-1 btn-sm">
			<i class="far fa-plus-circle"></i> Create Table
		</span>
	</div>
    <?php if ($show_search_table) : ?>
        <!-- SEARCH -->
        <div class="col-lg-4 mb-4">
            <div id="search-tables">
                <input type="search" id="tables-search-input" name="tables-search-input" class="form-control" placeholder="Search All <?php echo $link_count ?> Tables">
            </div>
			<span class="total-table d-none"><?php echo $link_count; ?></span>
        </div>
    <?php else: ?>
        <!-- SUB NAVIGATION -->
        <div class="align-items-center mb-4">
            <div class="col-lg js-sub-nav">
                <ul class="nav font-weight-bold">
                    <li class="nav-item mr-3">
                        <div class="shortcode-wrapper <?php echo ! empty( $table_id ) ? "" : "d-none" ?>">
                            <input id="shortcode" type="text" style="opacity: 0;" value='[lasso type="table" id="<?php echo $table_id?>"]'>
                            <div class="save-wrapper">
                                <span class="saved-item"><i class="far fa-check"></i><span class="ml-2">Saved</span></span>
                                <span class="saving-item d-none"><i class="far fa-sync fa-spin"></i><span class="ml-2">Saving</span></span>
                            </div>
                            <a id="copy-shortcode" onclick="copy_shortcode()" class="purple d-inline-block" data-tooltip="Copy this Display to your clipboard."><i class="far fa-pager"></i> <strong>Copy Shortcode</strong></a>
                        </div>
                    </li>
                    <li class="nav-item mx-3">
                        <a class="nav-link purple hover-underline px-0 <?php echo Lasso_Setting_Enum::PAGE_TABLES === $page_name && empty( $table_id ) ? 'active' : '' ?>"
                            href="edit.php?post_type=lasso-urls&page=tables">All Tables <span class="badge px-2 purple-bg white total-table"><?php echo $link_count; ?></span></a>
                    </li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- CREATE TABLE MODEL -->
<?php require_once LASSO_PLUGIN_PATH . '/admin/views/modals/table-create.php'; ?>
