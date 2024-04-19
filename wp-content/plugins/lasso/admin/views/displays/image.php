<?php
/**
 * Image
 *
 * @package Image
 */

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;
use Lasso\Libraries\Lasso_URL;

if ( isset( $is_demo_link ) ) {
	$post_id            = '';
	$lasso_url          = Lasso_Affiliate_Link::get_lasso_url( $post_id );
	$lasso_url          = clone $lasso_url;
	$custom_css_default = '';
} else {
	// ? Apply defaults if needed
	$custom_css_default = $settings['custom_css_default'];

	$lasso_link_type = $lasso_url->link_type;
	if ( LASSO_AMAZON_PRODUCT_TYPE !== $lasso_link_type ) {
		$lasso_url->permalink = $lasso_url->permalink . '?src=image';
	}
}
$lasso_url_obj = new Lasso_URL( $lasso_url );

// webp URL, fix webp file does not exist
$webp_url    = Lasso_Helper::get_webp_url( $lasso_url->lasso_id );

// ? Lasso image priority: Custom image link => Webp Image => Lasso Image
$lasso_image = '' !== $image_url ? $image_url : $webp_url;
$lasso_image = $lasso_image ? $lasso_image : $lasso_url->image_src;

if ( '' === $image_alt ) {
	$image_alt = $lasso_url_obj->name;
}
?>

<div id="<?php echo $anchor_id ?>" class="lasso-container lasso-image-container">
    <!-- LASSO IMAGE (https://getlasso.co) -->
	<a class="lasso-image image-style" id="css-<?php echo $post_id; ?>" <?php echo $lasso_url_obj->render_attributes(); ?>>
		<img src="<?php echo $lasso_image; ?>" <?php echo Lasso_Html_Helper::build_img_lazyload_attributes() ?> height="500" width="500" class="faux-noscript" alt="<?php echo $image_alt; ?>">
	</a>
	<?php echo Lasso_Html_Helper::get_brag_icon(); ?>
</div>
