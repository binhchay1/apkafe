<?php

/**
 * The Template for displaying product archives, including the main shop page which is a post type archive.
 *
 * Override this template by copying it to yourtheme/woocommerce/archive-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     8.6.0
 */

if (!defined('ABSPATH')) exit;
$layout = get_post_meta(get_option('woocommerce_shop_page_id'), 'sidebar_layout', true);
$content_padding = get_post_meta(get_option('woocommerce_shop_page_id'), 'content_padding', true);
if ($layout == '') {
	$layout =  ot_get_option('page_layout');
}

global $wp_query, $wpdb;
$cat = $wp_query->get_queried_object();
$getH1 = get_term_meta($cat->term_id, 'h1_category', true);

$isHotTab = false;
$isPopularTab = false;

if (!empty($_GET)) {
	if (array_key_exists('hot_page', $_GET)) {
		$getPaginationHot = $_GET['hot_page'];
		$isHotTab = true;
	}

	if (array_key_exists('popular_page', $_GET)) {
		$getPaginationPopular = $_GET['popular_page'];
		$isPopularTab = true;
	}

	if (array_key_exists('news_page', $_GET)) {
		$getPaginationNews = $_GET['news_page'];
	}
}

if (isset($getPaginationNews)) {
	$current_page_news = $getPaginationNews;
} else {
	$current_page_news = 1;
}

get_header('shop'); ?>
<style>
	.side_list_item .title {
		margin-top: 0;
	}

	.developer {
		color: black;
	}
</style>
<div class="container">
	<?php
	function get_all_terms($ter, $breadcrumb, $listTermShift)
	{
		$terms_parent = get_term($ter->parent, $ter->taxonomy);
		$listTermShift[] = $terms_parent;
		if ($terms_parent->parent == 0) {
			$listTermShift = array_reverse($listTermShift);
			foreach ($listTermShift as $key => $term) {
				if ($key == (count($listTermShift) - 1)) {
					$breadcrumb .= '<span class="breadcrumb_last" aria-current="page">' . $term->name . '</span></span></span></p>';
				} else {
					$breadcrumb .= '<a href="' . get_term_link($term->slug, $term->taxonomy) . '" rel="tag">' . $term->name . '</a> » ';
				}
			}

			echo $breadcrumb;
		} else {
			get_all_categories($cate_parent, $breadcrumb, $listCategoryShift);
		}
	}

	$term = get_queried_object();
	$breadcrumb = '<p id="breadcrumbs" style="padding: 10px"><span><span><a href="/">Home</a> » ';

	if ($term->parent == 0) {
		$breadcrumb .= '<span class="breadcrumb_last" aria-current="page">' . $term->name . '</span></span></span></p>';

		echo $breadcrumb;
	} else {
		$listTermShift[] = $term;
		get_all_terms($term, $breadcrumb, $listTermShift);
	}
	?>
	<?php if ($content_padding != 'off') { ?>
		<div class="content-pad-4x">
		<?php } ?>
		<div id="content">
			<?php
			if (class_exists('WCV_Vendor_Shop')) {
				WCV_Vendor_Shop::shop_description();
			} ?>

			<?php if ($getH1 != '') { ?>
				<div class="padding-20">
					<h1><?php echo $getH1 ?></h1>
				</div>
			<?php } ?>

			<?php do_action('woocommerce_archive_description'); ?>

			<ul class="sort-controls" id="section-tab-filter">
				<li class="active" style="cursor: pointer;" onclick="handleTabCategory(jQuery(this), 'news')">
					<a><i class="fa fa-sync"></i>News</a>
				</li>
				<li style="cursor: pointer;" onclick="handleTabCategory(jQuery(this), 'hot')">
					<a><i class="fa fa-heartbeat" style="margin-right: 10px;"></i>Hot</a>
				</li>
				<li style="cursor: pointer;" onclick="handleTabCategory(jQuery(this), 'popular')">
					<a><i class="fa fa-line-chart" style="margin-right: 10px;"></i>Popular</a>
				</li>
			</ul>

			<div id="news">
				<?php $args = array(
					'posts_per_page' => 15,
					'paged' => $current_page_news,
					'post_status' => 'published',
					'post_type' => 'product',
					'tax_query'             => array(
						array(
							'taxonomy'      => 'product_cat',
							'field' => 'term_id',
							'terms'         => $term->term_id,
							'operator'      => 'IN'
						)
					)
				);
				$res =  new WP_Query($args);
				$total_page_news = $res->max_num_pages; ?>
				<div class="main_list_item">
					<?php if ($res->have_posts()) { ?>
						<?php foreach ($res->posts as $post) { ?>
							<?php
							$icon = get_post_meta($post->ID, '_thumbnail_id', true);
							$rating = '';
							$developer = '';
							$lasso = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_title = '%s' AND post_type = 'lasso-urls'", $post->post_title));
							foreach ($lasso as $postRes) {
								$rating = get_post_meta($postRes->ID, 'rating');
								$developer = get_post_meta($postRes->ID, 'developer');

								break;
							}

							?>
							<a class="side_list_item" href="<?php echo the_permalink($post->ID) ?>">
								<img src="<?php echo wp_get_attachment_image_url($icon) ?>">
								<p class="title"><?php echo $post->post_title ?></p>
								<?php if (!empty($rating)) { ?>
									<span class="infor-rating" style="--rating:<?php echo $rating[0] ?>;"></span><br>
								<?php } ?>

								<?php if (!empty($developer)) { ?>
									<span class="developer"><?php echo $developer[0] ?></span>
								<?php } ?>
							</a>
						<?php } ?>
					<?php } ?>
				</div>

				<div class="d-flex justify-center margin-top-15">
					<?php echo paginate_links(array(
						'format' => '?news_page=%#%',
						'current' => $current_page_news,
						'total' => $total_page_news,
						'type' => 'list',
						'prev_text' => __('←'),
						'next_text' => __('→'),
					)); ?>
				</div>
			</div>

			<div id="hot">
				<?php $listPostHot = []; ?>
				<?php $getOptionHot = ot_get_option('customize_hot') ?>
				<?php if ($getOptionHot != '') { ?>
					<?php foreach ($getOptionHot as $option) { ?>
						<?php if ($option['title'] == $term->name) { ?>
							<?php foreach ($option['post_select'] as $postSelectHot) { ?>
								<?php $listPostHot[] = $postSelectHot ?>
							<?php } ?>
						<?php } ?>
					<?php } ?>
					<?php if (!empty($listPostHot)) { ?>
						<?php
						if (isset($getPaginationHot)) {
							$current_page_hot = $getPaginationHot;
						} else {
							$current_page_hot = 1;
						}

						$args = array(
							'post__in' => $listPostHot,
							'posts_per_page' => 15,
							'paged' => $current_page_hot,
							'post_status' => 'published',
							'post_type' => 'product',
						);
						$res =  new WP_Query($args);
						$total_page_hot = $res->max_num_pages; ?>
						<div class="main_list_item">
							<?php if ($res->have_posts()) { ?>
								<?php foreach ($res->posts as $post) { ?>
									<?php
									$icon = get_post_meta($post->ID, '_thumbnail_id', true);
									$rating = '';
									$developer = '';
									$lasso = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_title = '%s' AND post_type = 'lasso-urls'", $post->post_title));
									foreach ($lasso as $postRes) {
										$rating = get_post_meta($postRes->ID, 'rating');
										$developer = get_post_meta($postRes->ID, 'developer');

										break;
									}
									?>
									<a class="side_list_item" href="<?php echo the_permalink($post->ID) ?>">
										<img src="<?php echo wp_get_attachment_image_url($icon) ?>">
										<p class="title"><?php echo $post->post_title ?></p>
										<?php if (!empty($rating)) { ?>
											<span class="infor-rating" style="--rating:<?php echo $rating[0] ?>;"></span><br>
										<?php } ?>

										<?php if (!empty($developer)) { ?>
											<span class="developer"><?php echo $developer[0] ?></span>
										<?php } ?>
									</a>
								<?php } ?>
							<?php } ?>
						</div>

						<div class="d-flex justify-center margin-top-15">
							<?php echo paginate_links(array(
								'format' => '?hot_page=%#%',
								'current' => $current_page_hot,
								'total' => $total_page_hot,
								'type' => 'list',
								'prev_text' => __('←'),
								'next_text' => __('→'),
							)); ?>
						</div>
					<?php } ?>
				<?php } ?>
			</div>

			<div id="popular">
				<?php $listPostPopular = []; ?>
				<?php $getOptionPopular = ot_get_option('customize_popular') ?>
				<?php if ($getOptionPopular != '') { ?>
					<?php foreach ($getOptionPopular as $optionPopular) { ?>
						<?php if ($optionPopular['title'] == $term->name) { ?>
							<?php foreach ($optionPopular['post_select'] as $postSelectPopular) { ?>
								<?php $listPostPopular[] = $postSelectPopular ?>
							<?php } ?>
						<?php } ?>
					<?php } ?>
					<?php if (!empty($listPostPopular)) { ?>
						<?php
						if (isset($getPaginationPopular)) {
							$current_page_popular = $getPaginationPopular;
						} else {
							$current_page_popular = 1;
						}

						$args = array(
							'post__in' => $listPostPopular,
							'posts_per_page' => 16,
							'paged' => $current_page_popular,
							'post_status' => 'published',
							'post_type' => 'product',
						);
						$res =  new WP_Query($args);
						$total_page_popular = $res->max_num_pages; ?>
						<div class="main_list_item">
							<?php if ($res->have_posts()) { ?>
								<?php foreach ($res->get_posts() as $post) { ?>
									<?php
									$icon = get_post_meta($post->ID, '_thumbnail_id', true);
									$rating = '';
									$developer = '';
									$lasso = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_title = '%s' AND post_type = 'lasso-urls'", $post->post_title));
									foreach ($lasso as $postRes) {
										$rating = get_post_meta($postRes->ID, 'rating');
										$developer = get_post_meta($postRes->ID, 'developer');

										break;
									}
									?>
									<a class="side_list_item" href="<?php echo the_permalink($post->ID) ?>">
										<img src="<?php echo wp_get_attachment_image_url($icon) ?>">
										<p class="title"><?php echo $post->post_title ?></p>
										<?php if (!empty($rating)) { ?>
											<span class="infor-rating" style="--rating:<?php echo $rating[0] ?>;"></span><br>
										<?php } ?>

										<?php if (!empty($developer)) { ?>
											<span class="developer"><?php echo $developer[0] ?></span>
										<?php } ?>
									</a>
								<?php } ?>
							<?php } ?>
						</div>
						<div class="d-flex justify-center margin-top-15">
							<?php echo paginate_links(array(
								'format' => '?popular_page=%#%',
								'current' => $current_page_popular,
								'total' => $total_page_popular,
								'type' => 'list',
								'prev_text' => __('←'),
								'next_text' => __('→'),
							)); ?>
						</div>
					<?php } ?>
				<?php } ?>
			</div>
		</div>

		<?php if ($content_padding != 'off') { ?>
		</div>
	<?php } ?>
	<?php
	do_action('woocommerce_sidebar');
	?>
</div>
<script>
	function handleTabCategory(btn, cate) {
		let listLi = jQuery('#section-tab-filter li');
		let idSection = '#' + cate;
		for (let i = 0; i < listLi.length; i++) {
			if (listLi[i].classList.contains('active')) {
				listLi[i].classList.remove("active");
			}
		}

		btn.addClass('active');
		jQuery('#news').hasClass('')

		if (cate == 'news') {
			jQuery('#hot').hide();
			jQuery('#popular').hide();
		}

		if (cate == 'hot') {
			jQuery('#hot').hide();
			jQuery('#news').hide();
		}

		if (cate == 'popular') {
			jQuery('#hot').hide();
			jQuery('#news').hide();
		}

		jQuery(idSection).show();
	}

	jQuery(document).ready(function() {
		let listLi = jQuery('#section-tab-filter li');
		<?php if ($isHotTab) { ?>
			jQuery('#news').hide();
			jQuery('#popular').hide();
			jQuery('#hot').show();
			for (let i = 0; i < listLi.length; i++) {
				if (listLi[i].classList.contains('active')) {
					listLi[i].classList.remove("active");
				}
			}

			jQuery('#tab-filter-hot').addClass('active');
		<?php } elseif ($isPopularTab) { ?>
			jQuery('#news').hide();
			jQuery('#hot').hide();
			jQuery('#popular').show();
			for (let i = 0; i < listLi.length; i++) {
				if (listLi[i].classList.contains('active')) {
					listLi[i].classList.remove("active");
				}
			}

			jQuery('#tab-filter-popular').addClass('active');
		<?php } else { ?>
			jQuery('#hot').hide();
			jQuery('#popular').hide();
		<?php } ?>

	});
</script>
<?php get_footer(); ?>