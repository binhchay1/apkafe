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

get_header('shop'); ?>
<style>
	.sort-controls {
		display: flex;
		margin: 20px 0;
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
	$breadcrumb = '<p id="breadcrumbs"><span><span><a href="/">Home</a> » ';

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

				<ul class="sort-controls">
					<li class="active">
						<a onclick="handleTabCategory('new')">New</a>
					</li>
					<li>
						<a onclick="handleTabCategory('hot')">Hot</a>
					</li>
					<li>
						<a onclick="handleTabCategory('popular')">Popular</a>
					</li>
				</ul>

				<div id="news">
					<?php
					if (have_posts()) {
						do_action('woocommerce_before_shop_loop');
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
					<?php foreach ($getOptionHot as $option) { ?>
						<?php if ($option['title'] == $term->name) { ?>
							<?php foreach ($option['post_select'] as $postSelectHot) { ?>
								<?php $listPostHot[] = $postSelectHot ?>
							<?php } ?>
						<?php } ?>
					<?php } ?>
					<?php
					$paged = max(1, get_query_var('page'));
					$args = array(
						'post__in' => $listPostHot,
						'posts_per_page' => 12,
						'paged' => $paged,
						'post_status' => 'published',
						'post_type' => 'any',
					);
					$res =  new WP_Query($args);
					if ($res->have_posts()) { ?>
						<ul>
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
												<img src="<?php echo esc_url($icon); ?>" alt="<?php the_title_attribute(); ?>" width="60" height="60" />
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
						</ul>
					<?php } ?>
				</div>

				<div id="popular">
					<?php $listPostPopular = []; ?>
					<?php $getOptionPopular = ot_get_option('customize_popular') ?>
					<?php foreach ($getOptionPopular as $optionPopular) { ?>
						<?php if ($optionPopular['title'] == $term->name) { ?>
							<?php foreach ($optionPopular['post_select'] as $postSelectPopular) { ?>
								<?php $listPostPopular[] = $postSelectPopular ?>
							<?php } ?>
						<?php } ?>
					<?php } ?>
					<?php
					$paged = max(1, get_query_var('page'));
					$args = array(
						'post__in' => $listPostPopular,
						'posts_per_page' => 12,
						'paged' => $paged,
						'post_status' => 'published',
						'post_type' => 'any',
					);
					$res =  new WP_Query($args);
					if ($res->have_posts()) { ?>
						<ul>
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
												<img src="<?php echo esc_url($icon); ?>" alt="<?php the_title_attribute(); ?>" width="60" height="60" />
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
						</ul>
					<?php } ?>
				</div>
			</div>
		</div>

		<?php if ($content_padding != 'off') { ?>
		</div>
	<?php } ?>
	<?php
	if ($layout != 'full' && $layout != 'true-full') {
		do_action('woocommerce_sidebar');
	}
	?>
</div>
<script>
	function handleTabCategory(cate) {

	}

	jQuery(document).ready(function() {
		jQuery('#hot').hide();
		jQuery('#popular').hide();
	});
</script>
<?php get_footer(); ?>