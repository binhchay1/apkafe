<?php
/**
 * Single Product title
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>
<h2 itemprop="name" class="product_title entry-title"><?php the_title(); ?></h2>
<?php
$author = get_post_meta(get_the_ID(),'port-author-name',true);
$release = get_post_meta(get_the_ID(),'port-release',true);
$version = get_post_meta(get_the_ID(),'port-version',true);
$requirement = get_post_meta(get_the_ID(),'port-requirement',true);
if($author || $release || $version || $requirement){
	
$orientation='';
$devide = get_post_meta(get_the_ID(),'devide',true);
if($devide!='def_themeoption' && $devide!='def' && $devide!='' ){
	$orientation = get_post_meta(get_the_ID(),'orientation',true);
}elseif($devide=='def_themeoption' || $devide==''){
	$devide = ot_get_option('devide','iphone5s');
	if($devide!='def'){
		$orientation = ot_get_option('orientation','0');
	}
}

$col = $orientation?3:6; ?>
<div class="app-meta">
	<div class="row">
    	<?php if($author){ ?>
    	<div class="col-md-<?php echo esc_attr($col) ?>">
            <div class="media">
				<div class="pull-left"><i class="fa fa-user"></i></div>
				<div class="media-body">
                	<?php _e('Author','leafcolor') ?>
                	<div class="media-heading"><?php echo esc_attr($author) ?></div>
				</div>
            </div>
        </div>
        <?php } 
		if($release){ ?>
        <div class="col-md-<?php echo esc_attr($col) ?>">
            <div class="media">
				<div class="pull-left"><i class="fa fa-calendar"></i></div>
				<div class="media-body">
                	<?php _e('Release','leafcolor') ?>
                	<div class="media-heading"><?php echo esc_attr($release) ?></div>
				</div>
            </div>
        </div>
        <?php } 
		if($version){ ?>
        <div class="col-md-<?php echo esc_attr($col) ?>">
            <div class="media">
				<div class="pull-left"><i class="fa fa-tag"></i></div>
				<div class="media-body">
                	<?php _e('Version','leafcolor') ?>
                	<div class="media-heading"><?php echo esc_attr($version) ?></div>
				</div>
            </div>
        </div>
        <?php } 
		if($requirement){ ?>
        <div class="col-md-<?php echo esc_attr($col) ?>">
            <div class="media">
				<div class="pull-left"><i class="fa fa-check-square-o"></i></div>
				<div class="media-body">
                	<?php _e('Requirement','leafcolor') ?>
                	<div class="media-heading"><?php echo esc_attr($requirement) ?></div>
				</div>
            </div>
        </div>
        <?php }
		if($metas = get_post_meta(get_the_ID(),'app-custom-meta',true)){
			foreach($metas as $meta){ ?>
        <div class="col-md-<?php echo esc_attr($col) ?>">
            <div class="media">
				<div class="pull-left"><i class="fa <?php echo esc_attr($meta['icon']) ?>"></i></div>
				<div class="media-body">
                	<?php echo esc_html($meta['title']); ?>
                	<div class="media-heading"><?php echo esc_html($meta['value']); ?></div>
				</div>
            </div>
        </div>
        <?php }} ?>
    </div>
</div>
<?php }
