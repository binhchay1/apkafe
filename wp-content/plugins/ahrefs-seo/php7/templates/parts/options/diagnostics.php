<?php
/**
 * Settings Diagnostics part template
 *
 * @var bool $updated
 */

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Messages\Message;

$locals = Ahrefs_Seo_View::get_template_variables();
if ( empty( $locals['show_from_wizard'] ) ) {
	?>
<div class="block-subtitle"><?php esc_html_e( 'Error diagnostic reports', 'ahrefs-seo' ); ?></div>
	<?php
}
?>
<div class="block-text block-content">
	<?php esc_html_e( 'Help us improve your plugin experience by automatically sending diagnostic reports to our server when an error occurs. This will help with plugin stability and other improvements. We take privacy seriously - we do not send anything if this option is disabled, and send limited error information only if an error occurs.', 'ahrefs-seo' ); ?>
</div>
<?php
if ( ! empty( $locals['show_from_wizard'] ) ) {
	?>
	<div class="block-text">
		<?php esc_html_e( 'You can change this under Settings at any point in time.', 'ahrefs-seo' ); ?>
	</div>
	<?php
}
if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_DATA_SAVE ) ) {
	?>
<div class="input-wrap block-content">
	<input id="allow_reports" type="checkbox" name="allow_reports" value="1" class="checkbox-input" <?php checked( $locals['enabled'] ?? true ); ?>>
	<label for="allow_reports" class="help"><?php esc_html_e( 'Send error diagnostic reports to Ahrefs', 'ahrefs-seo' ); ?></label>
</div>
	<?php
} else {
	Message::view_not_allowed()->show();
}
?>
