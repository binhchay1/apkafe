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
	<div class="clear"></div>
</div>
<div class="clear"></div>