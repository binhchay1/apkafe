<?php
/**
 * Header HTML
 *
 * @package Header
 */

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting as Lasso_Setting;

$lasso_db             = new Lasso_DB();
$lasso_affiliate_link = new Lasso_Affiliate_Link();
$lasso_cron           = new Lasso_Cron();

$lasso_options = Lasso_Setting::lasso_get_settings();

$post_id = intval( $_GET['post_id'] ?? 0 ); // phpcs:ignore

if ( $lasso_options['general_disable_tooltip'] ) {
	echo '
        <style>
            [data-tooltip]:hover:before, [data-tooltip]:hover:after {visibility: hidden;}
            i.far.fa-info-circle {display: none;}
        </style>
        ';
}

$page = $_GET['page'] ?? ''; // phpcs:ignore

// ? DASHBOARD ACTIVE
if ( in_array( $page, array( 'dashboard', 'url-details', 'url-links' ), true ) ) {
	$dashboard_active = 'active';
} else {
	$dashboard_active = '';
}

// ? OPPORTUNITIES ACTIVE
if ( in_array(
	$page,
	array(
		'link-opportunities',
		'keyword-opportunities',
		'program-opportunities',
		'content-opportunities',
		'content-links',
		'domain-opportunities',
		'domain-links',
	),
	true
) ) {
	$opportunities_active = 'active';
} else {
	$opportunities_active = '';
}

// ? GROUPS ACTIVE
if ( in_array( $page, array( 'groups', 'group-details', 'group-urls' ), true ) ) {
	$groups_active = 'active';
} else {
	$groups_active = '';
}

// ? Tables ACTIVE
if ( in_array( $page, array( 'tables', 'table-details' ), true ) ) {
	$tables_active = 'active';
} else {
	$tables_active = '';
}

// ? SETTINGS ACTIVE
if ( in_array( $page, array( 'settings-general', 'settings-display', 'settings-amazon', 'settings-logs' ), true ) ) {
	$settings_active = 'active';
} else {
	$settings_active = '';
}

$lasso_db        = new Lasso_DB();
$keyword_count   = $lasso_db->saved_keywords_count();
$lasso_setting   = new Lasso_Setting();
$user_email      = get_option( 'admin_email' ); // phpcs:ignore
$user            = get_user_by( 'email', $user_email );
$user_name       = isset( $user->display_name ) ? $user->display_name : get_bloginfo( 'name' );
$user_hash       = get_option( 'lasso_license_hash', '' );
$amazon_valid    = get_option( 'lasso_amazon_valid', false ) ? 1 : 0;
$import_possible = count( Lasso_Setting::get_import_sources() ) > 0 ? 1 : 0;
$sentry_loaded   = SENTRY_LOADED;
$user_email      = get_option( 'lasso_license_email', $user_email ); // phpcs:ignore
$classic_editor  = Lasso_Helper::is_classic_editor() ? 1 : 0;
$ga_set          = $lasso_options['analytics_enable_click_tracking'] ? 1 : 0;
$display_counts  = $lasso_db->get_display_counts_for_intercom();
$single_displays = $display_counts['single_count'];
$grid_displays   = $display_counts['grid_count'];
$list_displays   = $display_counts['list_count'];
?>


<script>
	!function(){var analytics=window.analytics=window.analytics||[];if(!analytics.initialize)if(analytics.invoked)window.console&&console.error&&console.error("Segment snippet included twice.");else{analytics.invoked=!0;analytics.methods=["trackSubmit","trackClick","trackLink","trackForm","pageview","identify","reset","group","track","ready","alias","debug","page","once","off","on","addSourceMiddleware","addIntegrationMiddleware","setAnonymousId","addDestinationMiddleware"];analytics.factory=function(e){return function(){var t=Array.prototype.slice.call(arguments);t.unshift(e);analytics.push(t);return analytics}};for(var e=0;e<analytics.methods.length;e++){var key=analytics.methods[e];analytics[key]=analytics.factory(key)}analytics.load=function(key,e){var t=document.createElement("script");t.type="text/javascript";t.async=!0;t.src="https://cdn.segment.com/analytics.js/v1/" + key + "/analytics.min.js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(t,n);analytics._loadOptions=e};analytics._writeKey=lassoOptionsData.segment_analytic_id;analytics.SNIPPET_VERSION="4.13.2";
		analytics.load(lassoOptionsData.segment_analytic_id);
		analytics.page(document.title);

		var isAmazonValid = '<?php echo $amazon_valid; // phpcs:ignore ?>' == 1 ? true : false;
		var isImportPossible = '<?php echo $import_possible; // phpcs:ignore ?>' == 1 ? true : false;
		var isClassicEditor = '<?php echo $classic_editor; // phpcs:ignore ?>' == 1 ? true : false;
		var isGAConnected = '<?php echo $ga_set; // phpcs:ignore ?>' == 1 ? true : false;
		analytics.identify('<?php echo $user_hash; // phpcs:ignore ?>', {
			name: '<?php echo addslashes( $user_name ); // phpcs:ignore ?>',
			email: '<?php echo $user_email; // phpcs:ignore ?>',
			lasso_version: parseInt('<?php echo LASSO_VERSION; // phpcs:ignore ?>'),
			sentry_loaded: '<?php echo $sentry_loaded; // phpcs:ignore ?>',
			amazon_activated: isAmazonValid,
			import_possible: isImportPossible,
			classic_editor: isClassicEditor,
			ga_connected: isGAConnected,
			keyword_count: <?php echo $keyword_count; // phpcs:ignore ?>,
		});
	}}();
</script>

<!-- Changelog -->
<script>
	(function (R, a, p, i, d, r) {
		R.Rapidr = R.Rapidr || function () {
			(R.Rapidr.q = R.Rapidr.q || []).push(arguments);
		};
		d = a.getElementsByTagName('head')[0];
		r = a.createElement('script');
		r.async = 1;
		r.src = p + '/' + i;
		d.appendChild(r);
	})(window, document, 'https://js.rapidr.io', 'sdk.js');
</script>
<script>
	window.Rapidr('changelog', {
		selector: '.js-rapidr-changelog-btn', // CSS selector
		align: 'right', // left OR right
		position: 'bottom', // top OR bottom
		host: 'roadmap.getlasso.co', // you can pass custom domain too
		callback: function(widget) {
		/**
		 * optional callback when Rapidr changelog widget
		 * successfully hooks. You can use this object to programmatically
		 * show, hide, or destroy the changelog widget.
		 **/
		window.rapidrChangelogApp = widget;
		},
	});
</script>
<!-- Changelog - end -->

<!-- REQUEST REVIEW -->
<?php
if ( Lasso_Helper::show_request_review() ) {
	echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/notifications/request-review.php' ); // phpcs:ignore
}
?>

<!-- HEADER -->
<div class="container-fluid">
	<header class="row align-items-center purple-bg p-3 shadow">

		<!-- LASSO LOGO -->
		<div class="col-lg-2">
			<a href="edit.php?post_type=lasso-urls&page=dashboard" class="logo mx-auto mx-lg-0">
				<img src="<?php echo LASSO_PLUGIN_URL; // phpcs:ignore ?>admin/assets/images/lasso-logo.svg">
			</a>
		</div>

		<!-- NAVIGATION -->
		<div class="col-lg py-lg-0 py-3 ml-5">
			<ul class="nav justify-content-center font-weight-bold">
				<li class="nav-item mx-3 ml-5">
					<a class="nav-link px-0 white <?php echo $dashboard_active; // phpcs:ignore ?>" href="edit.php?post_type=lasso-urls&page=dashboard">Dashboard</a>
				</li>
				<li class="nav-item mx-3">
					<a class="nav-link px-0 white <?php echo $opportunities_active; // phpcs:ignore ?>" href="edit.php?post_type=lasso-urls&page=program-opportunities">Opportunities</a>
				</li>
				<li class="nav-item mx-3">
					<a class="nav-link px-0 white <?php echo $tables_active; // phpcs:ignore ?>" href="edit.php?post_type=lasso-urls&page=tables">Tables</a>
				</li>
				<li class="nav-item mx-3">
					<a class="nav-link px-0 white <?php echo $groups_active; // phpcs:ignore ?>" href="edit.php?post_type=lasso-urls&page=groups">Groups</a>
				</li>
				<li class="nav-item mx-3 mr-5">
					<a class="nav-link px-0 white" href="https://app.getlasso.co/performance/" target="_blank">Performance</a>
				</li>

				<li class="nav-item mx-1">
					<a class="nav-link px-2 white js-rapidr-changelog-btn"><i class="far fa-bolt"></i></a>
				</li>
				<li id="lasso-sync-icon" class="nav-item mx-1">
					<a class="nav-link px-2 white"><i class="far fa-sync"></i></a>
					<span id="lasso-sync-number" class="d-none"></span>
				</li>
				<li class="nav-item mx-1">
					<a class="nav-link px-2 white" target="_blank" href="https://getlasso.co/affiliate-program/"><i class="far fa-sack-dollar"></i></a>
				</li>
				<!--
				<li class="nav-item mx-0">
					<a class="nav-link px-2 white" target="_blank" href="https://support.getlasso.co/en/"><i class="far fa-question-circle"></i></a>
				</li>
				-->
			</ul>
		</div>

		<!-- ADD BUTTON -->
		<div class="col-lg-2 text-lg-right text-center pb-lg-0 pb-3 pl-1">
			<?php if ( Lasso_License::get_license_status() ) : ?>
				<button class="btn" data-toggle="modal" data-target="#url-add">
					<?php echo '<i class="far fa-plus-circle large-screen-only"></i> Add New Link'; ?>
				</button>
			<?php else : ?>
				<a href="<?php echo LASSO_UPDATE_LICENSE_URL; // phpcs:ignore ?>" class="btn">
					<?php echo 'Update Account'; ?>
				</a>
			<?php endif ?>
		</div>

	</header>

	<!-- ALERTS -->
	<div id="lasso_notifications">
	</div>
	<div id="lasso-sync-content" class="lasso-sync-content">
	</div>

	<!-- URL ADD MODAL -->
	<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/url-add.php'; ?>
</div>
