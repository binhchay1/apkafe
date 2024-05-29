<div class="side_bar">
	<?php

	global $wpdb;
	$table_name = $wpdb->prefix . 'trending_search';
	$resultsTrending = $wpdb->get_results("SELECT * FROM $table_name");
	?>

	<div class="widget">
		<h2 class="widget_head"><i class="fa fa-free-code-camp" style="margin-right: 10px;"></i>Trending Searches</h2>
		<div class="side_cat_list_wrap">
			<div class="search-box index_r_s">
				<form action="/apkafe/search/" method="post" class="formsearch">
					<span class="text-box"><span class="twitter-typeahead" style="position: relative; display: inline-block;">
							<input class="autocomplete main-autocomplete tt-hint" autocomplete="off" title="Enter App Name, Package Name, Package ID" type="text" readonly="" spellcheck="false" tabindex="-1" style="position: absolute; top: 0px; left: 0px; border-color: transparent; box-shadow: none; opacity: 1; background: none 0% 0% / auto repeat scroll padding-box border-box rgb(255, 255, 255);">
							<input class="autocomplete main-autocomplete tt-input" autocomplete="off" title="Enter App Name, Package Name, Package ID" name="s" type="text" placeholder="Apkafe" spellcheck="false" style="position: relative; vertical-align: top; background-color: transparent;">
						</span>
					</span>
					<span class="text-btn d-flex-justify-center" title="Search APK">
						<button type="submit" style="background: none; border: none; margin-left: 13px;">
							<i class="fa fa-search"></i>
						</button>
					</span>
				</form>
				<div class="trending-content">
					<?php foreach ($resultsTrending as $trending) { ?>
						<a href="<?php echo $trending->url ?>" title="<?php echo $trending->title ?>" class="hot"><?php echo $trending->title ?></a>
					<?php } ?>
				</div>
			</div>
		</div>
		<div class="clear"></div>
	</div>

	<div class="clear"></div>
	<div class="widget">
		<h2 class="widget_head"><i class="fa fa-gamepad" style="margin-right: 10px;"></i>Games</h2>
		<div class="side_cat_list_wrap">
			<?php if (ot_get_option('side_bar_game') != '') {  ?>
				<?php foreach (ot_get_option('side_bar_game') as $cat_id) { ?>
					<?php $id_option = 'z_taxonomy_image' . $cat_id ?>
					<?php
					$name_menu = '';
					if (get_cat_name($cat_id) == '') {
						$name_menu = get_term_by('id', $cat_id, 'product_cat')->name;
					} else {
						$name_menu = get_cat_name($cat_id);
					} ?>
					<?php if (get_option($id_option) != '') { ?>
						<a class="side_cat_item" href="<?php echo get_category_link($cat_id) ?>"><img width="30" height="30" class=" lazyloaded" src="<?php echo get_option($id_option); ?>" alt="<?php echo $name_menu ?>"><span style="margin-left: 15px"><?php echo $name_menu ?></span></a>
					<?php } else { ?>
						<a class="side_cat_item" href="<?php echo get_category_link($cat_id) ?>"><img width="30" height="30" class=" lazyloaded" src="<?php echo get_template_directory_uri() . '/inc/category-image/image/placeholder.png'; ?>" alt="<?php echo $name_menu ?>"><span style="margin-left: 15px"><?php echo $name_menu ?></span></a>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</div>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>

	<div class="widget">
		<h2 class="widget_head"><i class="fa fa-mobile-phone" style="margin-right: 10px;"></i>Apps</h2>
		<div class="side_cat_list_wrap">
			<?php if (ot_get_option('side_bar_app') != '') {  ?>
				<?php foreach (ot_get_option('side_bar_app') as $cat_id) { ?>
					<?php
					$name_menu_app = '';
					if (get_cat_name($cat_id) == '') {
						$name_menu_app = get_term_by('id', $cat_id, 'product_cat')->name;
					} else {
						$name_menu_app = get_cat_name($cat_id);
					} ?>
					<?php $id_option = 'z_taxonomy_image' . $cat_id ?>
					<?php if (get_option($id_option) != '') { ?>
						<a class="side_cat_item" href="<?php echo get_category_link($cat_id) ?>"><img width="30" height="30" class=" lazyloaded" src="<?php echo get_option($id_option); ?>" alt="<?php echo $name_menu_app ?>"><span style="margin-left: 15px"><?php echo $name_menu_app ?></span></a>
					<?php } else { ?>
						<a class="side_cat_item" href="<?php echo get_category_link($cat_id) ?>"><img width="30" height="30" class=" lazyloaded" src="<?php echo get_template_directory_uri() . '/inc/category-image/image/placeholder.png'; ?>" alt="<?php echo $name_menu_app ?>"><span style="margin-left: 15px"><?php echo $name_menu_app ?></span></a>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</div>
		<div class="clear"></div>
	</div>

	<div class="widget">
		<h2 class="widget_head"><i class="fa fa-newspaper-o" style="margin-right: 10px;"></i>Hot News</h2>
		<?php if (ot_get_option('side_bar_hot_news') != '') { ?>
			<?php $youtube_embed = ot_get_option('side_bar_hot_news');
			$explode = explode(',', $youtube_embed);
			$link_youtube_embed = $explode[0];
			$auto_play_youtube_embed = $explode[1];
			$parts = parse_url($link_youtube_embed);
			parse_str($parts['query'], $query);
			$v = $query['v'];

			?>
			<div class="side_cat_list_wrap">
				<iframe width="100%" src="https://www.youtube.com/embed/<?php echo $v ?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen>
				</iframe>
			</div>
		<?php } ?>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
</div>
<div class="clear"></div>