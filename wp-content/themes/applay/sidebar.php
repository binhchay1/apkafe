<?php
/**
 * The sidebar containing the main widget area.
 */
?>
<div id="sidebar" class="normal-sidebar" style="margin-left: 30px">
<?php 
if(is_front_page() && is_active_sidebar('frontpage_sidebar')){
	dynamic_sidebar( 'frontpage_sidebar' );
}elseif(is_active_sidebar('woocommerce_sidebar') && function_exists('is_woocommerce') && is_woocommerce()){
	dynamic_sidebar( 'woocommerce_sidebar' );
}elseif(is_active_sidebar('main_sidebar')){
	dynamic_sidebar( 'main_sidebar' );
}
?>
</div><!--#sidebar-->
