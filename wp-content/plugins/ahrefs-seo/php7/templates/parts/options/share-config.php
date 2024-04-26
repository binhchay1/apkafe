<?php
/**
 * Settings Share my Google configuration part template
 *
 * @var bool $updated
 */

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Messages\Message;

$locals           = Ahrefs_Seo_View::get_template_variables();
$google_connected = Ahrefs_Seo_Analytics::get()->get_data_tokens()->is_token_set();
$tokens_string    = $google_connected ? ( new Data_Tokens_Storage() )->get_config() : __( 'Google account is not connected.', 'ahrefs-seo' );
$is_wizard        = $locals['is_wizard'] ?? false;
?>
<div class="input-wrap block-content">
	<a href="#" class="show-my-google-config show-collapsed block-subtitle">
	<?php
	if ( $is_wizard ) {
		esc_html_e( 'Show my Google configuration', 'ahrefs-seo' );
	} else {
		esc_html_e( 'My Google configuration', 'ahrefs-seo' );
	}
	?>
		</a>
	<div class="collapsed-wrap">
		<div class="block-text block-content">
			<?php
			printf(
				/* translators: %s: text "our support team" with link */
				esc_html__( 'The string below contains information about your Google account tokens and currently selected GA and GSC profiles. If you run into issues when connecting Google accounts, %s might ask you to share this string in order to investigate.', 'ahrefs-seo' ),
				sprintf(
					'<a href="%s">%s</a>',
					esc_attr( Ahrefs_Seo::get_support_url( true ) ),
					esc_html__( 'our support team', 'ahrefs-seo' )
				)
			);
			?>
		</div>
		<div class="google-config-wrap">
			<?php
			if ( current_user_can( Ahrefs_Seo::CAP_EXPORT_GOOGLE_CONFIG ) ) { // is admin.
				?>
				<textarea readonly="readonly" id="google_config_input"
					<?php
					if ( ! $google_connected ) {
						?> class="short-textarea"<?php } ?>><?php echo esc_textarea( $tokens_string ); ?></textarea><?php // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentAfterEnd,Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
if ( $google_connected ) {
	?>
					<a href="#" class="button button-large with-icon copy-clipboard-button" id="google_config_button"><?php esc_html_e( 'Copy to clipboard', 'ahrefs-seo' ); ?></a>
			<?php
}
			} else { // not allowed.
				Message::view_not_allowed()->show();
			}
			?>
		</div>
	</div>
</div>
