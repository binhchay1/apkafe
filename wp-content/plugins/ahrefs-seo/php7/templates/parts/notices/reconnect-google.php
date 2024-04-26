<?php
declare( strict_types=1 );
namespace ahrefs\AhrefsSeo;

$url = Links::settings( Ahrefs_Seo_Screen_Settings::TAB_ANALYTICS ) . '#reconnect';
?>
<!-- need to reconnect Google account notice -->
<div class="ahrefs-content-tip tip-reconnect-google tip-notice">
	<div class="caption"><?php esc_html_e( 'Please reconnect your Google account', 'ahrefs-seo' ); ?></div>
	<div class="text">
		<?php
		if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_SAVE ) ) {
			esc_html_e( 'We’re switching to Google’s new and more secure authorization flow for third-party apps. To continue using the Ahrefs SEO plugin, please go to Settings and reconnect your Google account before January 31, 2023.', 'ahrefs-seo' );
		} else {
			esc_html_e( 'We’re switching to Google’s new and more secure authorization flow for third-party apps. To continue using the Ahrefs SEO plugin, please ask the website owner to reconnect Google account before January 31, 2023. They can do it in the plugin’s settings.', 'ahrefs-seo' );
		}
		?>
	</div>
	<?php
	if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_SAVE ) ) {
		?>
		<div class="buttons">
			<a id="open_google_settings_button" class="button button-primary content_tip_reconnect_google" href="<?php echo esc_attr( $url ); ?>"><?php esc_html_e( 'Settings', 'ahrefs-seo' ); ?></a>
		</div>
		<?php
	}
	?>
</div>
