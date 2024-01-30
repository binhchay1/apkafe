<?php
global $global_page_layout;
$single_page_layout = get_post_meta(get_the_ID(), 'sidebar_layout', true);
$content_padding = get_post_meta(get_the_ID(), 'content_padding', true);
$layout = $single_page_layout ? $single_page_layout : ($global_page_layout ? $global_page_layout : ot_get_option('post_layout', 'right'));
$global_page_layout = $layout;
get_header();
?>
<?php get_template_part('templates/header/header', 'heading'); ?>
<div id="body">
	<div class="container">
		<?php
		function get_all_categories($cate, $breadcrumb, $listCategoryShift)
		{
			$cate_parent = get_category($cate->category_parent);
			$listCategoryShift[] = $cate_parent;
			if ($cate_parent->category_parent == 0) {
				$listCategoryShift = array_reverse($listCategoryShift);
				foreach ($listCategoryShift as $key => $category) {
					if ($key == (count($listCategoryShift) - 1)) {
						$breadcrumb .= '<a href="' . get_category_link($category) . '" rel="tag">' . $category->name . '</a></span> » <span class="breadcrumb_last" aria-current="page">' . get_the_title(get_the_ID()) . '</span>';
					} else {
						$breadcrumb .= '<a href="' . get_category_link($category) . '" rel="tag">' . $category->name . '</a> » ';
					}
				}

				echo $breadcrumb;
			} else {
				get_all_categories($cate_parent, $breadcrumb, $listCategoryShift);
			}
		}

		$categories = get_the_category(get_the_ID());
		$breadcrumb = '<p id="breadcrumbs"><span><span><a href="/">Home</a> » ';
		if ($categories[0]->category_parent == 0) {
			$breadcrumb = $breadcrumb . '<a href="' . get_category_link($categories[0]) . '" rel="tag">' . $categories[0]->name . '</a></span> » <span class="breadcrumb_last" aria-current="page">' . get_the_title(get_the_ID()) . '</span></span></span></p>';

			echo $breadcrumb;
		} else {
			$listCategoryShift[] = $categories[0];
			get_all_categories($categories[0], $breadcrumb, $listCategoryShift);
		}
		?>
		<?php $meta_key = get_post_meta(get_the_ID(), 'custom_heading');
		echo '<h1>' . $meta_key[0] . '</h1>'
		?>
		<?php if ($content_padding != 'off') { ?>
			<div class="content-pad-4x">
			<?php } ?>
			<div class="row">
				<div id="content" class="<?php if ($layout != 'full') { ?> col-md-9 <?php } else { ?> col-md-12 <?php }
																											if ($layout == 'left') { ?> revert-layout <?php } ?>" role="main">
					<article class="single-post-content single-content">
						<?php
						// The Loop
						while (have_posts()) : the_post();
							get_template_part('templates/single/content', 'featured-image');
							get_template_part('templates/single/content', 'single');
						endwhile;
						?>
					</article>
					<?php if (ot_get_option('enable_author') != 'off' && get_the_author_meta('description')) { ?>
						<div class="about-author">
							<div class="author-avatar">
								<?php
								if (isset($__check_retina) && $__check_retina) {
									echo get_avatar(get_the_author_meta('email'), 100, get_template_directory_uri() . '/images/avatar-big-retina.jpg');
								} else {
									echo get_avatar(get_the_author_meta('email'), 100, get_template_directory_uri() . '/images/avatar-big.jpg');
								} ?>
							</div>
							<div class="author-info">
								<h4 class="font-2"><?php the_author_posts_link(); ?></h4>
								<?php the_author_meta('description'); ?>
							</div>
							<div class="clearfix"></div>
						</div>
					<?php } ?>
					<?php if (ot_get_option('single_navi') != 'off') { ?>
						<div class="single-post-navigation">
							<div class="row">
								<?php
								$p = get_adjacent_post(true, '', true);
								$n = get_adjacent_post(true, '', false);
								?>
								<div class="single-post-navigation-item col-md-6 col-sm-6 col-xs-6 <?php if (empty($n)) { ?> no-border <?php } ?>">
									<?php

									if (!empty($p)) {
										echo '<a href="' . get_permalink($p->ID) . '" title="' . esc_attr($p->post_title) . '" class="maincolor2hover">
                                    <div class="single-post-navigation-item-content">
										<img src="' . wp_get_attachment_image_src(get_post_thumbnail_id($p->ID), 'post')[0]  . '">
                                        <p class="font-2"><i class="fa fa-angle-left"></i> &nbsp;' . $p->post_title . '</p>
                                    </div>
									</a>';
									}
									?>
								</div>
								<div class="single-post-navigation-item col-md-6 col-sm-6 col-xs-6 <?php if (empty($n)) { ?> hidden <?php } ?>">
									<?php
									if (!empty($n)) echo '<a href="' . get_permalink($n->ID) . '" title="' . esc_attr($n->post_title) . '" class="maincolor2hover pull-right">
									<div class="single-post-navigation-item-content">
										<img src="' . wp_get_attachment_image_src(get_post_thumbnail_id($n->ID), 'post')[0]  . '">
										<p class="font-2">' . $n->post_title . '&nbsp; <i class="fa fa-angle-right"></i></p>
									</div>
									</a>';
									?>
								</div>
							</div>
						</div>
					<?php } ?>
					<?php comments_template('', true); ?>
				</div>
				<?php if ($layout != 'full') {
					get_sidebar();
				} ?>
			</div>
			<?php if ($content_padding != 'off') { ?>
			</div>
		<?php } ?>
	</div>
</div>
<?php get_footer(); ?>