<?php
/**
 * Tip template
 */

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();

$id      = $locals['id'] ?? '';
$title   = $locals['title'];
$message = $locals['message'];
?>
<div class="ahrefs-content-tip tip-warning tip-new-audit-message" data-reason="compatibility" id="<?php echo esc_attr( "message-id-$id" ); ?>" data-id="<?php echo esc_attr( "$id" ); ?>">
	<div class="caption"><?php echo esc_html( $title ); ?></div>
	<div class="text">
		<?php echo esc_html( $message ); ?>
		<a href="https://help.ahrefs.com/en/articles/4858501-why-is-my-wordpress-plugin-incompatible-with-the-ahrefs-seo-wordpress-plugin" target="_blank"><?php esc_html_e( 'Why’s this happening?', 'ahrefs-seo' ); ?></a>
	</div>
	<?php
	require __DIR__ . '/buttons.php';
	?>
	</div>
