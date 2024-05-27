<div class="side_bar">
	<div class="clear"></div>
	<div class="widget">
		<h2 class="widget_head">Games</h2>
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
						<a class="side_cat_item" href="<?php echo get_category_link($cat_id) ?>"><img width="30" height="30" class=" lazyloaded" src="<?php echo get_option($id_option); ?>" alt="<?php echo $name_menu ?>"><?php echo $name_menu ?></a>
					<?php } else { ?>
						<a class="side_cat_item" href="<?php echo get_category_link($cat_id) ?>"><img width="30" height="30" class=" lazyloaded" src="<?php echo get_template_directory_uri() . '/inc/category-image/image/placeholder.png'; ?>" alt="<?php echo $name_menu ?>"><?php echo $name_menu ?></a>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</div>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
	<div class="clear"></div>
	<div class="widget">
		<h2 class="widget_head">Apps</h2>
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
						<a class="side_cat_item" href="<?php echo get_category_link($cat_id) ?>"><img width="30" height="30" class=" lazyloaded" src="<?php echo get_option($id_option); ?>" alt="<?php echo $name_menu_app ?>"><?php echo $name_menu_app ?></a>
					<?php } else { ?>
						<a class="side_cat_item" href="<?php echo get_category_link($cat_id) ?>"><img width="30" height="30" class=" lazyloaded" src="<?php echo get_template_directory_uri() . '/inc/category-image/image/placeholder.png'; ?>" alt="<?php echo $name_menu_app ?>"><?php echo $name_menu_app ?></a>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</div>
		<div class="clear"></div>
	</div>

	<div class="widget">
		<h2 class="widget_head">Trending search</h2>
		<div class="side_cat_list_wrap">
			<div class="search-box index_r_s">
				<form action="/search/" method="post" class="formsearch"><span class="text-box"><span class="twitter-typeahead" style="position: relative; display: inline-block;">
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
				<div class="trending-title">Trending Searches</div>
				<div class="trending-content">
					<a href="https://apkafe.com/product/how-to-download-mcpe-minecraft-mods-apk/" title="mcpe minecraft mods" class="hot">mcpe minecraft mods</a>
				</div>
			</div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
</div>
<div class="clear"></div>