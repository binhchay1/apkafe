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

global $wp_query;
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
	.sort-controls {
		display: flex;
		margin: 20px;
	}

	.sort-controls li {
		background-color: #e4e4e4;
		padding: 10px 25px;
		font-size: 18px;
		color: black;
		border-radius: 10px;
	}

	.sort-controls li:not(:first-child) {
		margin-left: 15px;
	}

	.sort-controls .active {
		background-color: black;
	}

	.display-block {
		display: block;
	}

	.display-none {
		display: none;
	}

	#news,
	#hot,
	#popular {
		display: flex;
		flex-direction: column;
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
		<div class="row">
			<div id="content" class="<?php if ($layout != 'full' && $layout != 'true-full') { ?> col-md-9 <?php } else { ?>col-md-12 <?php }
																																	if ($layout == 'left') { ?> revert-layout <?php } ?>">
				<?php
				if (class_exists('WCV_Vendor_Shop')) {
					WCV_Vendor_Shop::shop_description();
				} ?>

				<div>
					<?php if ($getH1 != '') { ?>
						<h1><?php echo $getH1 ?></h1>
					<?php } ?>
				</div>

				<?php do_action('woocommerce_archive_description'); ?>

				<ul class="sort-controls" id="section-tab-filter">
					<li class="active" style="cursor: pointer;" onclick="handleTabCategory(jQuery(this), 'news')">
						<a>News</a>
					</li>
					<li style="cursor: pointer;" onclick="handleTabCategory(jQuery(this), 'hot')">
						<a>Hot</a>
					</li>
					<li style="cursor: pointer;" onclick="handleTabCategory(jQuery(this), 'popular')">
						<a>Popular</a>
					</li>
				</ul>

				<div id="news">
					<?php
					if (have_posts()) {
						
						woocommerce_product_loop_start();

						if (wc_get_loop_prop('total')) {
							while (have_posts()) {
								the_post();
								do_action('woocommerce_shop_loop');
								wc_get_template_part('content', 'product');
							}
						}

						woocommerce_product_loop_end();
						do_action('woocommerce_after_shop_loop');
					} else {
						do_action('woocommerce_no_products_found');
					}
					?>
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
								'posts_per_page' => 16,
								'paged' => $current_page_hot,
								'post_status' => 'published',
							);
							$res =  new WP_Query($args); ?>
							<ul>
								<?php if ($res->have_posts()) { ?>
									<?php foreach ($res->posts as $post) { ?>
										<li <?php post_class(); ?>>
											<?php do_action('woocommerce_before_shop_loop_item'); ?>
											<?php
											$icon = get_post_meta($post->ID, 'app-icon', true);
											?>
											<div class="item-content <?php if ($icon) { ?> has-icon <?php } ?>">
												<?php if ($icon) {
													if ($icon_id = ia_get_attachment_id_from_url($icon)) {
														$thumbnail = wp_get_attachment_image_src($icon_id, 'thumbnail', true);
														$icon = isset($thumbnail[0]) ? $thumbnail[0] : $icon;
													}
												?>
													<div class="app-icon">
														<a href="<?php the_permalink($post->ID) ?>" title="<?php $post->post_title ?>">
															<img src="<?php echo esc_url($icon); ?>" alt="<?php $post->post_title ?>" />
														</a>
													</div>
												<?php } ?>
												<p class="product-title"><a href="<?php the_permalink($post->ID) ?>" title="<?php $post->post_title ?>" class="main-color-1-hover"><?php $post->post_title ?></a></p>
												<?php

												do_action('woocommerce_after_shop_loop_item_title');
												?>
											</div>
											<?php do_action('woocommerce_after_shop_loop_item'); ?>
										</li>
									<?php } ?>
								<?php } ?>
							</ul>

							<div>
								<?php echo paginate_links(array(
									'base' => get_pagenum_link(1) . '%_%',
									'format' => 'page/%#%?hot_page=%#%',
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
								'post_type' => 'any',
							);
							$res =  new WP_Query($args); ?>
							<ul>
								<?php if ($res->have_posts()) { ?>
									<?php foreach ($res->get_posts() as $post) { ?>
										<?php die(); ?>
										<li <?php post_class(); ?>>
											<?php do_action('woocommerce_before_shop_loop_item'); ?>
											<?php
											$icon = get_post_meta(get_the_ID(), 'app-icon', true);
											?>
											<div class="item-content <?php if ($icon) { ?> has-icon <?php } ?>">
												<?php if ($icon) {
													if ($icon_id = ia_get_attachment_id_from_url($icon)) {
														$thumbnail = wp_get_attachment_image_src($icon_id, 'thumbnail', true);
														$icon = isset($thumbnail[0]) ? $thumbnail[0] : $icon;
													}
												?>
													<div class="app-icon">
														<a href="<?php the_permalink(get_the_ID()) ?>" title="<?php the_title_attribute() ?>">
															<img src="<?php echo esc_url($icon); ?>" alt="<?php the_title_attribute(); ?>" />
														</a>
													</div>
												<?php } ?>
												<p class="product-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute() ?>" class="main-color-1-hover"><?php the_title(); ?></a></p>
												<?php
												do_action('woocommerce_after_shop_loop_item_title');
												?>
											</div>
											<?php do_action('woocommerce_after_shop_loop_item'); ?>
										</li>
									<?php } ?>
								<?php } ?>
							</ul>
							<div>
								<?php echo paginate_links(array(
									'base' => get_pagenum_link(1) . '%_%',
									'format' => '/page/%#%?popular_page=%#%',
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