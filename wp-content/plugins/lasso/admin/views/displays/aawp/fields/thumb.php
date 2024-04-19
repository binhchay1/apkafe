<?php

use Lasso\Classes\Enum;
use Lasso\Classes\Html_Helper;

$image_src     = $lasso_url->image_src;
$image_size    = $atts['image_size'] ?? 'medium';
$image_alt     = isset($image_alt) && $image_alt ? $image_alt : $lasso_url_obj->name;
$image_classes = $atts['image_class'] ?? '';
$image_wh      = '';

// Handling aligns
if ( isset( $atts['image_align'] ) ) {
	$image_align_class = '';

	if ( 'center' === $atts['image_align'] ) {
		$image_align_class = 'aligncenter';
	} elseif ( 'left' === $atts['image_align'] ) {
		$image_align_class = 'alignleft';
	} elseif ( 'right' === $atts['image_align'] ) {
		$image_align_class = 'alignright';
	}

	if ( ! empty( $image_align_class ) ) {
		$image_classes = ( ! empty( $image_classes ) ) ? $image_classes . ' ' . $image_align_class : $image_align_class;
	}
}

// Handling image width and height
$image_width  = ( isset( $atts['image_width'] ) && is_numeric( $atts['image_width'] ) ) ? intval( $atts['image_width'] ) : 0;
$image_height = ( isset( $atts['image_height'] ) && is_numeric( $atts['image_height'] ) ) ? intval( $atts['image_height'] ) : 0;
if ( $image_width && $image_height ) {
	$image_wh = ' width="' . $image_width . '" height="' . $image_height . '" ';
}

// Handling image size
if ( strpos( $image_src, 'media-amazon' ) !== false ) {
	if ( 'small' === $image_size ) {
		$image_src = str_replace( '.jpg', '._SL75_.jpg', $image_src );
	} elseif ( 'medium' === $image_size ) {
		$image_src = str_replace( '.jpg', '._SL160_.jpg', $image_src );
	}
}

// ? Priority webp image
$webp_image = get_post_meta( $lasso_url->lasso_id, Enum::LASSO_WEBP_THUMBNAIL, true );
$image_src  = $webp_image ? $webp_image : $image_src;
?>

<!-- LASSO IMAGE (https://getlasso.co) -->
<a <?php echo $lasso_url_obj->render_attributes(); ?>>
	<img src="<?php echo $image_src ?>" class="<?php echo $image_classes; ?>" <?php echo $image_wh; ?> <?php echo Html_Helper::build_img_lazyload_attributes() ?> alt="<?php echo $image_alt; ?>">
</a>
