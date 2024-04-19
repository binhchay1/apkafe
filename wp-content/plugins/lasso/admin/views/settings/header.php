<?php
/**
 * Settings header
 *
 * @package Settings header
 */

$page       = $_GET['page'];
$title      = '';
$learn_link = LASSO_LEARN_LINK;


if ( 'settings-general' === $page ) {
	$title = 'General';
}

if ( 'settings-display' === $page ) {
	$title      = 'Display';
	$learn_link = 'https://support.getlasso.co/en/collections/2287037-customizing-displays';
}

if ( 'settings-amazon' === $page ) {
	$title      = 'Amazon';
	$learn_link = 'https://support.getlasso.co/en/collections/1858466-amazon-associates-integration';
}

if ( 'settings-logs' === $page ) {
	$title = 'Logs';
}

if ( 'settings-db' === $page ) {
	$title = 'Lasso Tables';
}

?>

<div class="row align-items-center">
	<!-- TITLE -->
	<div class="col-lg-4 mb-4 text-lg-left text-center">
		<h1 class="m-0 mr-2 d-inline-block align-middle"><?php echo $title; ?></h1>
		<a href="<?php echo $learn_link; ?>" target="_blank" class="btn btn-sm learn-btn">
			<i class="far fa-info-circle"></i> Learn
		</a>
	</div>

	<!-- SUB NAVIGATION -->
	<div class="col-lg">
		<ul class="nav justify-content-lg-end justify-content-center font-weight-bold mb-4">
			<li class="nav-item mr-lg-0 mr-3">
				<a class="nav-link purple hover-underline px-0 
				<?php
				if ( 'settings-general' === $page ) {
					echo 'active'; }
				?>
				" href="edit.php?post_type=lasso-urls&page=settings-general">General</a>
			</li>
			<li class="nav-item ml-4 mr-lg-0 mr-3">
				<a class="nav-link purple hover-underline px-0 
				<?php
				if ( 'settings-display' === $page ) {
					echo 'active'; }
				?>
				" href="edit.php?post_type=lasso-urls&page=settings-display">Display</a>
			</li>
			<li class="nav-item ml-4 mr-lg-0 mr-3">
				<a class="nav-link purple hover-underline px-0 
				<?php
				if ( 'settings-amazon' === $page ) {
					echo 'active'; }
				?>
				" href="edit.php?post_type=lasso-urls&page=settings-amazon">Amazon</a>
			</li>
			<!--
			<li class="nav-item ml-4 mr-lg-0 mr-3">
				<a class="nav-link purple hover-underline px-0 
				<?php
				/**
				// if ( $page == 'settings-logs' ) {
				//  echo 'active';
				// }
				 */
				?>
				" href="edit.php?post_type=lasso-urls&page=settings-logs">Logs</a>
			</li>
			-->
		</ul>
	</div>
</div>

<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/url-save.php'; ?>
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/license-activation.php'; ?>
