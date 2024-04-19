<?php
/**
 * URL links
 *
 * @package Lasso URL links
 */

use Lasso\Classes\Setting as Lasso_Setting;

$page      = $_GET['page'];
$category  = strtok( $page, '-' );
$main_page = in_array( $page, array( 'domain-links', 'content-links' ), true ) ? $category . '-opportunities' : $page;
	
if ( "domain" === $category ) {
	$category  = "Detected Links";
	$main_page = "program-opportunities";
}

$learn['link']    = 'https://support.getlasso.co/en/articles/4029174-how-to-use-opportunities';
$learn['keyword'] = 'https://getlasso.co/keywording/';
$learn['content'] = '';
$learn['domain']  = '';

$learn_link = '';
/*
if ( '' !== $learn[ $category ] ) {
	$learn_link = ' 
        <a href="' . $learn[ $category ] . '" target="_blank" class="btn btn-sm learn-btn">
            <i class="far fa-info-circle"></i> Learn
        </a>
    ';
} else {
	$learn_link = '';
}
*/

$backspace_link = ' 
    <a href="edit.php?post_type=lasso-urls&page=' . $main_page . '" class="light-purple hover-purple-text">
        <i class="far fa-xs fa-backspace"></i>
    </a>
';

if ( isset( $_GET['keyword'] ) ) {
	$page_title = ucfirst( $category ) . ' <i class="far fa-xs fa-chevron-right"></i> "' . $_GET['keyword'] . '"' . $backspace_link;
} elseif ( isset( $_GET['post_id'] ) ) {
	$page_title = ucfirst( $category ) . ' <i class="far fa-xs fa-chevron-right"></i> ' . get_the_title( $_GET['post_id'] ) . $backspace_link;
} elseif ( isset( $_GET['filter'] ) ) {
	$page_title = ucfirst( $category ) . ' <i class="far fa-xs fa-chevron-right"></i> ' . $_GET['filter'] . $backspace_link;
} else {
	$page_title = ucfirst( $category ) . ' Opportunities' . $learn_link;
}

if ( 'Program Opportunities' === $page_title ) {
	$page_title = "Affiliate Program Opportunities";
}
?>

<!-- TITLE -->
<div class="row align-items-center">
	<div class="col-lg mb-4 text-lg-left text-center">
		<h1 class="m-0 mr-2 d-inline-block align-middle"><?php echo $page_title; ?></h1>
		<!--
		<a href="<?php // echo $learn_link; ?>" target="_blank" class="btn btn-sm learn-btn">
			<i class="far fa-info-circle"></i> Learn
		</a>
		-->
	</div>
</div>

<div class="learn-box-container">
	<?php
		$lasso_setting = new Lasso_Setting();
		
		if ( isset( $_GET['reseteducation'] ) ) {
			$option_update['link-opportunities'] = '';
			$option_update['keyword-opportunities'] = '';
			$option_update['content-opportunities'] = '';
			$option_update['domain-opportunities'] = '';
			Lasso_Setting::lasso_set_settings( $option_update );
			$hide_box = '';
		} else {
			$hide_box = isset( $lasso_options[$category.'-opportunities'] ) ? $lasso_options[$category.'-opportunities'] : '';	
		}
		
		if ( isset( $lasso_options['general_disable_tooltip'] ) && 1 === intval( $lasso_options['general_disable_tooltip'] ) ) {
			$do_nothing = 1;
		} elseif ( $category == 'link' && $hide_box != 'hide' ) {
			include LASSO_PLUGIN_PATH . '/admin/views/education/link-opportunities.php'; 
		} elseif ( $category == 'keyword' && $hide_box != 'hide' ) {
			include LASSO_PLUGIN_PATH . '/admin/views/education/keywords.php';
		} elseif ( $category == 'content' && $hide_box != 'hide' ) {
			include LASSO_PLUGIN_PATH . '/admin/views/education/content.php';
		} elseif ( ( $page_title == 'Affiliate Program Opportunities' || str_contains($page_title, 'Detected Links') ) && $hide_box != 'hide' ) {
			include LASSO_PLUGIN_PATH . '/admin/views/education/domain.php';
		} else {
			$do_nothing = 1;
		}
	?>
	
</div>

<div class="row align-items-center mb-4">
	<!-- NAVIGATION -->
	<div class="col-lg-5">
		<ul class="nav font-weight-bold">
			<li class="nav-item mr-3">
				<a class="nav-link purple hover-underline px-0 
				<?php
				if ( 'program-opportunities' === $page || 'Detected Links' === $category ) {
					echo 'active';
				}
				?>
				" href="edit.php?post_type=lasso-urls&page=program-opportunities">Programs</a>
			</li>
			<li class="nav-item mx-3">
				<a class="nav-link purple hover-underline px-0 
				<?php
				if ( 'keyword' === $category ) {
					echo 'active'; }
				?>
				" href="edit.php?post_type=lasso-urls&page=keyword-opportunities">Keywords</a>
			</li>
			<li class="nav-item ml-3">
				<a class="nav-link purple hover-underline px-0 
				<?php
				if ( 'content' === $category ) {
					echo 'active'; }
				?>
				" href="edit.php?post_type=lasso-urls&page=content-opportunities">Content</a>
			</li>
			<!--
			<li class="nav-item mx-3">
				<a class="nav-link purple hover-underline px-0 
				<?php
				if ( 'link' === $category ) {
					echo 'active'; }
				?>
				" href="edit.php?post_type=lasso-urls&page=link-opportunities">Links</a>
			</li>
			<li class="nav-item ml-3">
				<a class="nav-link purple hover-underline px-0 
				<?php
				if ( 'domain' === $category ) {
					echo 'active'; }
				?>
				" href="edit.php?post_type=lasso-urls&page=domain-opportunities">Domains</a>
			</li>
			-->
		</ul>
	</div>

	<div class="col-lg js-sub-nav">
		<ul class="nav d-flex justify-content-center">
			<li class="nav-item mr-3">
				<span class="nav-link black px-0" id="js-report-result"></span>
			</li>
		</ul>
	</div>

	<div class="col-lg-5 text-right">
		<form role="search" method="get" autocomplete="off">
			<div id="search-links">
				<input type="search" class="form-control" placeholder="Search">
			</div>
		</form>
	</div>

</div>
