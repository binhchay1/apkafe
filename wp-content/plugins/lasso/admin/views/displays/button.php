<?php
/**
 * Button
 *
 * @package Button
 */

use Lasso\Classes\Html_Helper as Lasso_Html_Helper;
use Lasso\Classes\Setting_Enum;
use Lasso\Libraries\Lasso_URL;

$theme_name = $settings['theme_name'] ?? Setting_Enum::THEME_CACTUS;
$theme_name = strtolower( 'lasso-' . $theme_name );

if ( '' !== $lasso_url->display->theme ) {
	$theme_name = strtolower( 'lasso-' . $lasso_url->display->theme );
}

// ? Let theme be overridden in shortcode
if ( '' !== $theme ) {
	$theme_name = strtolower( 'lasso-' . $theme );
}

// ? Let primary text button override in shorcode
$primary_button_text = ! empty( $primary_text ) ? $primary_text : $lasso_url->display->primary_button_text;

// ? Let primary url override in shortcode
$primary_button_url = ! empty( $primary_url ) && $primary_url ? $primary_url : $lasso_url->public_link;

// ? Let secondary text button override in shorcode
$secondary_button_text = ! empty( $secondary_text ) && $secondary_text ? $secondary_text : $lasso_url->display->secondary_button_text;

// ? Let secondary url button override in shorcode
$secondary_button_url = ! empty( $secondary_url ) && $secondary_url ? $secondary_url : $lasso_url->display->secondary_url;

// ? Let check show button type
$check_show_button_secondary = '' !== $secondary_button_url && LASSO_SECONDARY_TYPE_BTN === $button_type ? true : null;

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
?>

<div id="<?php echo $anchor_id ?>" class="lasso-container lasso-button-container <?php echo $theme_name; ?>">
	<!-- LASSO BUTTON (https://getlasso.co) -->
	<?php if ( !$check_show_button_secondary ) { ?>
        <a class="lasso-button-1" <?php echo $lasso_url_obj->render_attributes( $primary_button_url ); ?>>
            <?php echo $primary_button_text; ?>
        </a>

	<?php } else { ?>
        <a class="lasso-button-2" <?php echo $lasso_url_obj->render_attributes_second( $secondary_button_url ); ?>>
			<?php echo $secondary_button_text; ?>
		</a>
	<?php } ?>
	<?php echo Lasso_Html_Helper::get_brag_icon(); ?>
</div>
