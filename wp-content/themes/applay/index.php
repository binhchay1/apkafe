<?php 
$layout = ot_get_option('archive_sidebar','right');
$listing_style = ot_get_option('listing_style');
get_header();
?>
	<?php get_template_part( 'templates/header/header', 'heading' ); ?>
    <div id="body">
    	<div class="container">
        	<div class="content-pad-4x">
                <div class="row">
                    <div id="content" class="<?php if($layout!='full'){ ?> col-md-9 <?php }else{?> col-md-12 <?php } if($layout == 'left'){ ?> revert-layout <?php }?>">
                        <div class="blog-listing">
                        <?php
						if(have_posts()){
							if($listing_style=='ajax' && function_exists('wp_ajax_shortcode')){
								echo do_shortcode("[wpajax global_query=1 /]");
							}else{
								// The Loop
								while ( have_posts() ) : the_post();
									get_template_part('templates/blog/loop','item');
								endwhile;
							}
						}else{
							get_template_part('templates/blog/loop','none');
						}
						?>
                        </div>
                        <?php
						if($listing_style!='ajax'){
							if(function_exists('wp_pagenavi')){
								wp_pagenavi();
							}else{
								leafcolor_content_nav('paging');
							}
						}
						?>
                    </div>
                    <?php if($layout != 'full'){get_sidebar();} ?>
                </div>
            </div>
        </div>
    </div>
<?php get_footer(); ?>