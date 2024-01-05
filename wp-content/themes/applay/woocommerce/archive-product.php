<?php

/**
 * The Template for displaying product archives, including the main shop page which is a post type archive.
 *
 * Override this template by copying it to yourtheme/woocommerce/archive-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.4.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly
$layout = get_post_meta(get_option('woocommerce_shop_page_id'), 'sidebar_layout', true);
$content_padding = get_post_meta(get_option('woocommerce_shop_page_id'), 'content_padding', true);
if ($layout == '') {
	$layout =  ot_get_option('page_layout');
}
get_header('shop'); ?>

<?php

global $wpdb;

$sql_discover = "SELECT post_id FROM wp_binhchay WHERE key_post='discover' LIMIT 1";
$discover = $wpdb->get_results($sql_discover);
if (empty($discover)) {
	$discover_list_id = [];
} else {
	$discover_list_id = explode(',', $discover[0]->post_id);
}

$sql_popular_game_24h = "SELECT post_id FROM wp_binhchay WHERE key_post='popular_game_in_last_24h' LIMIT 1";
$popular_game_24h = $wpdb->get_results($sql_popular_game_24h);
if (empty($popular_game_24h)) {
	$popular_game_24h_list_id = [];
} else {
	$popular_game_24h_list_id = explode(',', $popular_game_24h[0]->post_id);
}

$sql_popular_app_24h = "SELECT post_id FROM wp_binhchay WHERE key_post='popular_app_in_last_24h' LIMIT 1";
$popular_app_24h = $wpdb->get_results($sql_popular_app_24h);
if (empty($popular_app_24h)) {
	$popular_app_24h_list_id = [];
} else {
	$popular_app_24h_list_id = explode(',', $popular_app_24h[0]->post_id);
}

$sql_popular_app = "SELECT post_id FROM wp_binhchay WHERE key_post='popular_app' LIMIT 1";
$popular_app = $wpdb->get_results($sql_popular_app);
if (empty($popular_app)) {
	$popular_app_list_id = [];
} else {
	$popular_app_list_id = explode(',', $popular_app[0]->post_id);
}

$sql_popular_games = "SELECT post_id FROM wp_binhchay WHERE key_post='popular_games' LIMIT 1";
$popular_games = $wpdb->get_results($sql_popular_games);
if (empty($popular_games)) {
	$popular_games_list_id = [];
} else {
	$popular_games_list_id = explode(',', $popular_games[0]->post_id);
}

$sql_news_popular = "SELECT post_id FROM wp_binhchay WHERE key_post='news_popular' LIMIT 1";
$news_popular = $wpdb->get_results($sql_news_popular);
$news_popular_list_id = explode(',', $news_popular[0]->post_id);
if (empty($technology_trick_popular)) {
	$technology_trick_popular_list_id = [];
} else {
	$technology_trick_popular_list_id = explode(',', $technology_trick_popular[0]->post_id);
}

$sql_technology_trick_popular = "SELECT post_id FROM wp_binhchay WHERE key_post='technology_trick_popular' LIMIT 1";
$technology_trick_popular = $wpdb->get_results($sql_technology_trick_popular);
if (empty($technology_trick_popular)) {
	$technology_trick_popular_list_id = [];
} else {
	$technology_trick_popular_list_id = explode(',', $technology_trick_popular[0]->post_id);
}

$sql_lastest_update_games = "SELECT post_id FROM wp_binhchay WHERE key_post='lastest_update_games' LIMIT 1";
$lastest_update_games = $wpdb->get_results($sql_lastest_update_games);
if (empty($lastest_update_games)) {
	$lastest_update_games_list_id = [];
} else {
	$lastest_update_games_list_id = explode(',', $lastest_update_games[0]->post_id);
}

$sql_lastest_update_app = "SELECT post_id FROM wp_binhchay WHERE key_post='lastest_update_app' LIMIT 1";
$lastest_update_app = $wpdb->get_results($sql_lastest_update_app);
if (empty($lastest_update_app)) {
	$lastest_update_app_list_id = [];
} else {
	$lastest_update_app_list_id = explode(',', $lastest_update_app[0]->post_id);
}

$sql_hot_games = "SELECT post_id FROM wp_binhchay WHERE key_post='hot_game' LIMIT 1";
$hot_games = $wpdb->get_results($sql_hot_games);
if (empty($hot_games)) {
	$hot_games_list_id = [];
} else {
	$hot_games_list_id = explode(',', $hot_games[0]->post_id);
}

$sql_hot_app = "SELECT post_id FROM wp_binhchay WHERE key_post='hot_app' LIMIT 1";
$hot_app = $wpdb->get_results($sql_hot_app);
if (empty($hot_app)) {
	$hot_app_list_id = [];
} else {
	$hot_app_list_id = explode(',', $hot_app[0]->post_id);
}

$sql_search = "SELECT * FROM wp_trending_search";
$search = $wpdb->get_results($sql_search);

$uploads = wp_upload_dir();
$upload_path = $uploads['baseurl'];

$url = home_url();
$listLang = get_template_directory() . '/languages/en.php';
$pos = strpos($url, '/ja');
if ($pos > 0) {
	$listLang = get_template_directory() . '/languages/ja.php';
}

$pos = strpos($url, '/th');
if ($pos > 0) {
	$listLang = get_template_directory() . '/languages/th.php';
}

require $listLang;
?>

<?php get_template_part('templates/header/header', 'heading'); ?>
<?php if (is_front_page() || is_home()) { ?>
	<style>
		@media (min-width: 1155px) {
			.widget-title:before {
				left: 0 !important;
			}
		}

		::-webkit-scrollbar {
			width: 5px;
			height: 5px;
		}

		/* Track */
		::-webkit-scrollbar-track {
			-webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
			-webkit-border-radius: 10px;
			border-radius: 10px;
		}

		/* Handle */
		::-webkit-scrollbar-thumb {
			-webkit-border-radius: 10px;
			border-radius: 10px;
			background: rgba(242, 245, 249, 1);
			-webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.5);
		}

		.list {
			width: 5208px;
			position: absolute;
			padding: 0px;
			margin: 0px;
			transition-duration: 200ms;
		}

		.tempWrap {
			overflow: hidden;
			position: relative;
		}

		.banner-item {
			display: table-cell;
			vertical-align: top;
			width: 875px;
		}
	</style>

	<div class="main-body" style="display:flex">
		<div style="width: 100%;">
			<div class="left">
				<div id="top-slide-banner" class="slide-banner">
					<div class="container" style="padding: 0;">
						<div class="tempWrap">
							<div class="list" style="transform: translate(0px, 0px) translateZ(0px);">
								<a title="Dude Theft War" class="banner-item" href="https://apkafe.com/product/how-to-download-dude-theft-wars-offline-games/">
									<img class="banner-bg lazy loaded" alt="Dude Theft War" src="<?php echo $upload_path . '/slide-show/Dude-1.jpg' ?>">
									<div class="mask"></div>
									<div class="info">
										<img class="icon lazy loaded" alt="Dude Theft War" src="<?php echo $upload_path . '/slide-show/Dude-2.jpg' ?>">
										<div class="name">Dude Theft War</div>
									</div>
								</a>
								<a title="MINE" class="banner-item" href="https://apkafe.com/product/how-to-download-mcpe-minecraft-mods-apk/">
									<img class="banner-bg" alt="MINE" src="<?php echo $upload_path . '/slide-show/MINE-1.jpg' ?>">
									<div class="mask"></div>
									<div class="info">
										<img class="icon lazy loaded" alt="MINE" src="<?php echo $upload_path . '/slide-show/MINE-2.jpg' ?>">
										<div class="name">MINE</div>
									</div>
								</a>
								<a title="Supreme" class="banner-item" href="https://apkafe.com/product/how-to-download-supreme-duelist-stickman-on-mobile/">
									<img class="banner-bg lazy loaded" alt="Supreme" src="<?php echo $upload_path . '/slide-show/Supreme-1.jpg' ?>">
									<div class="mask"></div>
									<div class="info">
										<img class="icon lazy loaded" alt="Supreme" src="<?php echo $upload_path . '/slide-show/Supreme-2.jpg' ?>">
										<div class="name">Supreme</div>
									</div>
								</a>
							</div>
						</div>
					</div>
					<ul class="dots">
						<li class="on" id="dots-1">1</li>
						<li class="" id="dots-2">2</li>
						<li class="" id="dots-3">3</li>
					</ul>
				</div>

				<div class="search-box search-box-m index_r_s">
					<form action="/search/" method="post" class="formsearch"><span class="text-box">
							<span class="twitter-typeahead" style="position: relative; display: inline-block;">
								<input class="autocomplete main-autocomplete tt-hint" autocomplete="off" title="Enter App Name, Package Name, Package ID" type="text" readonly="" spellcheck="false" tabindex="-1" style="position: absolute; top: 0px; left: 0px; border-color: transparent; box-shadow: none; opacity: 1; background: none 0% 0% / auto repeat scroll padding-box border-box rgb(255, 255, 255);" dir="ltr">
								<input class="autocomplete main-autocomplete tt-input" autocomplete="off" title="Enter App Name, Package Name, Package ID" name="s" type="text" placeholder="Apkafe" spellcheck="false" style="position: relative; vertical-align: top; background-color: transparent;">
								<pre aria-hidden="true" style="position: absolute; visibility: hidden; white-space: pre; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; font-size: 14px; font-style: normal; font-variant: normal; font-weight: 400; word-spacing: 0px; letter-spacing: 0px; text-indent: 0px; text-rendering: auto; text-transform: none;"></pre>
								<div class="tt-menu" style="position: absolute; top: 100%; left: 0px; z-index: 100; display: none;" dt-eid="card" dt-params="model_type=1121&amp;module_name=search_panel&amp;position=5">
									<div class="tt-dataset tt-dataset-1"></div>
								</div>
							</span>
						</span>
						<span class="text-btn" title="Search APK"><input class="si" type="submit" value="" dt-eid="search_button" dt-params="small_position=1" dt-imp-once="true" dt-imp-end-ignore="true" dt-send-beacon="true"></span>
					</form>
					<div class="trending-title"><?php echo $lang['Trending Searches'] ?></div>
					<div class="trending-content">
						<?php foreach ($search as $record) { ?>
							<a href="<?php echo $record->url ?>" title="<?php echo $record->title ?>" class="hot"><?php echo $record->title ?></a>
						<?php } ?>
					</div>
				</div>
				<div class="module discover" style="margin-top: 20px;">
					<a class="title more" title="<?php echo $lang['Discover'] ?>">
						<h3 class="name"><?php echo $lang['Discover'] ?></h3>
					</a>
					<div class="apk-list-1001 enable-wrap">
						<?php
						foreach ($discover_list_id as $id) {
						?>
							<a class="apk" title="<?php echo get_the_title($id) ?>" href="<?php echo get_permalink($id) ?>">
								<div class="img-ratio"><img class="icon " alt="<?php echo get_the_title($id) ?>" src="<?php echo wp_get_attachment_image_src(get_post_thumbnail_id($id), 'post')[0] ?>"></div>
								<div class="name double-lines"><?php echo get_the_title($id) ?></div>
							</a>
						<?php };
						?>

					</div>
				</div>
				<div class="module popular-games">
					<a class="title more" title="<?php echo $lang['Popular Games In Last 24 Hours'] ?>">
						<h3 class="name"><?php echo $lang['Popular Games In Last 24 Hours'] ?></h3>
					</a>
					<div class="apk-list-1002">
						<?php foreach ($popular_game_24h_list_id as $id) { ?>
							<a class="apk" title="<?php echo get_the_title($id) ?>" href="<?php echo get_permalink($id) ?>">
								<div class="img-ratio"><img class="icon " alt="<?php echo get_the_title($id) ?>" src="<?php echo wp_get_attachment_image_src(get_post_thumbnail_id($id), 'post')[0] ?>"></div>
								<div class="name double-lines"><?php echo get_the_title($id) ?></div>
								<div class="score"><?php echo mt_rand(3, 4) ?></div>
							</a>
						<?php } ?>
					</div>
				</div>
				<div class="module popular-apps">
					<a class="title more" title="<?php echo $lang['Popular Apps In Last 24 Hours'] ?>">
						<h3 class="name"><?php echo $lang['Popular Apps In Last 24 Hours'] ?></h3>
					</a>
					<div class="apk-list-1002">
						<?php foreach ($popular_app_24h_list_id as $id) { ?>
							<a class="apk" title="<?php echo get_the_title($id) ?>" href="<?php echo get_permalink($id) ?>">
								<div class="img-ratio"><img class="icon " alt="<?php echo get_the_title($id) ?>" src="<?php echo wp_get_attachment_image_src(get_post_thumbnail_id($id), 'post')[0] ?>"></div>
								<div class="name double-lines"><?php echo get_the_title($id) ?></div>
								<div class="score"><?php echo mt_rand(3, 4) ?></div>
							</a>
						<?php } ?>
					</div>
				</div>
				<div class="module popular-games">
					<a class="title more" title="<?php echo $lang['Popular Games'] ?>">
						<h3 class="name"><?php echo $lang['Popular Games'] ?></h3>
					</a>
					<div class="apk-list-1002">
						<?php foreach ($popular_games_list_id as $id) { ?>
							<a class="apk" title="<?php echo get_the_title($id) ?>" href="<?php echo get_permalink($id) ?>">
								<div class="img-ratio"><img class="icon " alt="<?php echo get_the_title($id) ?>" src="<?php echo wp_get_attachment_image_src(get_post_thumbnail_id($id), 'post')[0] ?>"></div>
								<div class="name double-lines"><?php echo get_the_title($id) ?></div>
								<div class="score"><?php echo mt_rand(3, 4) ?></div>
							</a>
						<?php } ?>
					</div>
				</div>
				<div class="module popular-apps">
					<a class="title more" title="<?php echo $lang['Popular Apps'] ?>">
						<h3 class="name"><?php echo $lang['Popular Apps'] ?></h3>
					</a>
					<div class="apk-list-1002">
						<?php foreach ($popular_app_list_id as $id) { ?>
							<a class="apk" title="<?php echo get_the_title($id) ?>" href="<?php echo get_permalink($id) ?>">
								<div class="img-ratio"><img class="icon " alt="<?php echo get_the_title($id) ?>" src="<?php echo wp_get_attachment_image_src(get_post_thumbnail_id($id), 'post')[0] ?>"></div>
								<div class="name double-lines"><?php echo get_the_title($id) ?></div>
								<div class="score"><?php echo mt_rand(3, 4) ?></div>
							</a>
						<?php } ?>
					</div>
				</div>
				<div class="module popular-articles">
					<a class="title more" title="<?php echo $lang['Technology trick popular'] ?>">
						<h3 class="name"><?php echo $lang['Technology trick popular'] ?></h3>
					</a>
					<div class="article-list">
						<?php foreach ($technology_trick_popular_list_id as $id) { ?>
							<a class="article" href="<?php echo get_permalink($id) ?>" title="<?php echo get_the_title($id) ?>">
								<img class="article-banner" alt="<?php echo get_the_title($id) ?>" src="<?php echo wp_get_attachment_image_src(get_post_thumbnail_id($id), 'post')[0] ?>">
								<div class="text">
									<div class="article-title double-lines"><?php echo get_the_title($id) ?></div>
									<div class="updated one-line"><?php echo get_the_date('M d Y', $id)  ?></div>
								</div>
							</a>
						<?php } ?>
					</div>
				</div>
				<div class="module popular-articles">
					<a class="title more" title="<?php echo $lang['News popular'] ?>">
						<h3 class="name"><?php echo $lang['News popular'] ?></h3>
					</a>
					<div class="article-list">
						<?php foreach ($news_popular_list_id as $id) { ?>
							<a class="article" href="<?php echo get_permalink($id) ?>" title="<?php echo get_the_title($id) ?>">
								<img class="article-banner" alt="<?php echo get_the_title($id) ?>" src="<?php echo wp_get_attachment_image_src(get_post_thumbnail_id($id), 'post')[0] ?>">
								<div class="text">
									<div class="article-title double-lines"><?php echo get_the_title($id) ?></div>
									<div class="updated one-line"><?php echo get_the_date('M d Y', $id)  ?></div>
								</div>
							</a>
						<?php } ?>
					</div>
				</div>
			</div>
			<div class="left">
				<div class="module hot-games">
					<a class="title more" title="<?php echo $lang['Hot Games'] ?>" href="/game">
						<h3 class="name"><?php echo $lang['Hot Games'] ?></h3>
					</a>
					<div class="apk-list-1006">
						<?php foreach ($hot_games_list_id as $id) { ?>
							<a class="apk" title="<?php echo get_the_title($id) ?>" href="<?php echo get_permalink($id) ?>">
								<div class="img-ratio"><img class="icon" alt="<?php echo get_the_title($id) ?>" src="<?php echo wp_get_attachment_image_src(get_post_thumbnail_id($id), 'post')[0] ?>"></div>
								<div class="text">
									<div class="name one-line"><?php echo get_the_title($id) ?></div>
								</div>
							</a>
						<?php } ?>
					</div>
				</div>
				<div class="module hot-apps">
					<a class="title more" title="<?php echo $lang['Hot Apps'] ?>" href="/game">
						<h3 class="name"><?php echo $lang['Hot Apps'] ?></h3>
					</a>
					<div class="apk-list-1006">
						<?php foreach ($hot_app_list_id as $id) { ?>
							<a class="apk" title="<?php echo get_the_title($id) ?>" href="<?php echo get_permalink($id) ?>">
								<div class="img-ratio"><img class="icon" alt="<?php echo get_the_title($id) ?>" src="<?php echo wp_get_attachment_image_src(get_post_thumbnail_id($id), 'post')[0] ?>"></div>
								<div class="text">
									<div class="name one-line"><?php echo get_the_title($id) ?></div>
								</div>
							</a>
						<?php } ?>
					</div>
				</div>
				<div class="module latest-update-games">
					<div class="title">
						<h3 class="name"><?php echo $lang['Latest Update Games'] ?></h3>
					</div>
					<div class="apk-list-1008">
						<?php foreach ($lastest_update_games_list_id as $id) { ?>
							<?php $icon = get_post_meta($id, 'app-icon') ?>
							<?php $version = get_post_meta($id, '_product_version') ?>
							<a class="apk" title="<?php echo get_the_title($id) ?>" href="<?php echo get_permalink($id) ?>">
								<?php if (!$icon) { ?>
									<div class="img-ratio"><img class="icon" alt="<?php echo get_the_title($id) ?>" src="<?php echo wp_get_attachment_image_src(get_post_thumbnail_id($id), 'post')[0] ?>" width="102" height="102"></div>
								<?php } else { ?>
									<div class="img-ratio"><img class="icon" alt="<?php echo get_the_title($id) ?>" src="<?php echo $icon[0] ?>" width="102" height="102"></div>
								<?php } ?>
								<div class="text">
									<div class="name double-lines"><?php echo get_the_title($id) ?></div>
									<?php if (!$version) { ?>
										<div class="install-total">1.0.0</div>
									<?php } else { ?>
										<div class="install-total"><?php echo $version[0] ?></div>
									<?php } ?>
								</div>
							</a>
						<?php } ?>
					</div>
				</div>
				<div class="module latest-update-apps">
					<div class="title">
						<h3 class="name"><?php echo $lang['Latest Update Apps'] ?></h3>
					</div>
					<div class="apk-list-1008">
						<?php foreach ($lastest_update_app_list_id as $id) { ?>
							<?php $icon = get_post_meta($id, 'app-icon') ?>
							<?php $version = get_post_meta($id, '_product_version') ?>
							<a class="apk" title="<?php echo get_the_title($id) ?>" href="<?php echo get_permalink($id) ?>">
								<?php if (!$icon) { ?>
									<div class="img-ratio"><img class="icon" alt="<?php echo get_the_title($id) ?>" src="<?php echo wp_get_attachment_image_src(get_post_thumbnail_id($id), 'post')[0] ?>" width="102" height="102"></div>
								<?php } else { ?>
									<div class="img-ratio"><img class="icon" alt="<?php echo get_the_title($id) ?>" src="<?php echo $icon[0] ?>" width="102" height="102"></div>
								<?php } ?>
								<div class="text">
									<div class="name double-lines"><?php echo get_the_title($id) ?></div>
									<?php if (!$version) { ?>
										<div class="install-total">1.0.0</div>
									<?php } else { ?>
										<div class="install-total"><?php echo $version[0] ?></div>
									<?php } ?>
								</div>
							</a>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<div class="hide-mobile">
			<div class="search-box index_r_s">
				<form action="/search/" method="post" class="formsearch"><span class="text-box"><span class="twitter-typeahead" style="position: relative; display: inline-block;">
							<input class="autocomplete main-autocomplete tt-hint" autocomplete="off" title="Enter App Name, Package Name, Package ID" type="text" readonly="" spellcheck="false" tabindex="-1" style="position: absolute; top: 0px; left: 0px; border-color: transparent; box-shadow: none; opacity: 1; background: none 0% 0% / auto repeat scroll padding-box border-box rgb(255, 255, 255);">
							<input class="autocomplete main-autocomplete tt-input" autocomplete="off" title="Enter App Name, Package Name, Package ID" name="s" type="text" placeholder="Apkafe" spellcheck="false" style="position: relative; vertical-align: top; background-color: transparent;">
							<pre aria-hidden="true" style="position: absolute; visibility: hidden; white-space: pre; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; font-size: 14px; font-style: normal; font-variant: normal; font-weight: 400; word-spacing: 0px; letter-spacing: 0px; text-indent: 0px; text-rendering: auto; text-transform: none;"></pre>
							<div class="tt-menu" style="position: absolute; top: 100%; left: 0px; z-index: 100; display: none;">
								<div class="tt-dataset tt-dataset-1"></div>
							</div>
						</span></span><span class="text-btn" title="Search APK"><i class="fa fa-search" style="margin: 10px 20px; font-size: 15px;"></i><input class="si" type="submit" value="">

					</span>
				</form>
				<div class="trending-title"><?php echo $lang['Trending Searches'] ?></div>
				<div class="trending-content">
					<?php foreach ($search as $record) { ?>
						<a href="<?php echo $record->url ?>" title="<?php echo $record->title ?>" class="hot"><?php echo $record->title ?></a>
					<?php } ?>
				</div>
			</div>
			<?php
			if ($layout != 'full' && $layout != 'true-full') {
				do_action('woocommerce_sidebar');
			}
			?>
		</div>

		<div class="clear"></div>
	</div>
	<script>
		var tranfer = 'translate(0px, 0px)';
		var tranferCurrent = -880;
		var dotsID = 'dots-1';

		function slideMainAuto() {
			let split = dotsID.split('-');
			let currentDotsId = split[1];
			let nextDotsId = parseInt(currentDotsId) + 1;
			let presDotsID = currentDotsId;
			if (nextDotsId == 4) {
				nextDotsId = 1;
			}
			dotsID = 'dots-' + nextDotsId;
			let idElementDotsNext = '#' + dotsID;
			let idElementDotsPres = '#dots-' + presDotsID;

			tranfer = 'translate(' + tranferCurrent + 'px, 0px)';
			jQuery('.list').attr('style', 'transform: ' + tranfer + ' translateZ(0px)');
			jQuery(idElementDotsNext).addClass("on");
			jQuery(idElementDotsPres).removeClass("on");

			if (tranferCurrent == 0) {
				tranferCurrent = -880;
			} else {
				tranferCurrent = tranferCurrent + tranferCurrent;
			}

			if (parseInt(tranferCurrent) <= (-3520)) {
				tranferCurrent = 0;
			}
		}

		setInterval(slideMainAuto, 3000);
	</script>
<?php } else { ?>
	<div class="container">
		<?php
		if (function_exists('yoast_breadcrumb')) {
			yoast_breadcrumb('<p id="breadcrumbs">', '</p>');
		}
		?>
		<?php if ($content_padding != 'off') { ?>
			<div class="content-pad-4x">
			<?php } ?>
			<div class="row">
				<?php
				/**
				 * woocommerce_before_main_content hook
				 *
				 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
				 * @hooked woocommerce_breadcrumb - 20
				 */
				//do_action( 'woocommerce_before_main_content' );
				?>
				<div id="content" class="<?php if ($layout != 'full' && $layout != 'true-full') { ?> col-md-9 <?php } else { ?>col-md-12 <?php }
																																		if ($layout == 'left') { ?> revert-layout <?php } ?>">
					<?php // if ( apply_filters( 'woocommerce_show_page_title', true ) ) : 
					?>
					<?php
					if (class_exists('WCV_Vendor_Shop')) {
						WCV_Vendor_Shop::shop_description();
					} ?>
					<!--<h1 class="page-title"><?php // woocommerce_page_title(); 
												?></h1>-->

					<?php //endif; 
					?>

					<?php do_action('woocommerce_archive_description'); ?>

					<?php
					if (have_posts()) {
						/**
						 * Hook: woocommerce_before_shop_loop.
						 *
						 * @hooked wc_print_notices - 10
						 * @hooked woocommerce_result_count - 20
						 * @hooked woocommerce_catalog_ordering - 30
						 */
						do_action('woocommerce_before_shop_loop');

						woocommerce_product_loop_start();

						if (wc_get_loop_prop('total')) {
							while (have_posts()) {
								the_post();

								/**
								 * Hook: woocommerce_shop_loop.
								 *
								 * @hooked WC_Structured_Data::generate_product_data() - 10
								 */
								do_action('woocommerce_shop_loop');

								wc_get_template_part('content', 'product');
							}
						}

						woocommerce_product_loop_end();

						/**
						 * Hook: woocommerce_after_shop_loop.
						 *
						 * @hooked woocommerce_pagination - 10
						 */
						do_action('woocommerce_after_shop_loop');
					} else {
						/**
						 * Hook: woocommerce_no_products_found.
						 *
						 * @hooked wc_no_products_found - 10
						 */
						do_action('woocommerce_no_products_found');
					}
					?>

				</div>
				<?php
				/**
				 * woocommerce_after_main_content hook
				 *
				 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
				 */
				//do_action( 'woocommerce_after_main_content' );
				?>
				<?php
				/**
				 * woocommerce_sidebar hook
				 *
				 * @hooked woocommerce_get_sidebar - 10
				 */
				if ($layout != 'full' && $layout != 'true-full') {
					do_action('woocommerce_sidebar');
				}
				?>
			</div>
			<?php if ($content_padding != 'off') { ?>
			</div><!--/content-pad-4x-->
		<?php } ?>
	</div>
<?php } ?>
<?php get_footer('shop'); ?>