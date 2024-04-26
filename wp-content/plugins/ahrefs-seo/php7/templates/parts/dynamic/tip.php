<?php
/**
 * Tip template
 */

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals    = Ahrefs_Seo_View::get_template_variables();
$id        = $locals['id'] ?? '';
$title     = $locals['title'];
$message   = $locals['message'];
$classes   = $locals['classes'] ?? [ 'tip-notice' ];
$classes[] = 'ahrefs-content-tip';
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" id="<?php echo esc_attr( $id ); ?>" data-id="<?php echo esc_attr( $id ); ?>">
	<div class="caption"><?php echo esc_html( $title ); ?></div>
	<div class="text"><?php echo esc_html( $message ); ?></div>
<?php
require __DIR__ . '/buttons.php';
?>
</div>
