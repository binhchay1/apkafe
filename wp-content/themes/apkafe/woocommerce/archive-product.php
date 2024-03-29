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
<?php get_footer(); ?>