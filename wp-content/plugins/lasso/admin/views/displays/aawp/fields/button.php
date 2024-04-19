<?php

use Lasso\Classes\Setting_Enum;

$theme_name = $settings['theme_name'] ?? Setting_Enum::THEME_CACTUS;
$theme_name = strtolower( 'lasso-' . $theme_name );

if ( '' !== $lasso_url->display->theme ) {
	$theme_name = strtolower( 'lasso-' . $lasso_url->display->theme );
}

// ? Let theme be overridden in shortcode
if ( '' !== $theme ) {
	$theme_name = strtolower( 'lasso-' . $theme );
}
?>
<span class="aawp-fields-button <?php echo $theme_name ?>">
	<a class="lasso-button-1" <?php echo $lasso_url_obj->render_attributes() ?>>
		<?php echo $lasso_url->display->primary_button_text; ?>
	</a>
</span>
