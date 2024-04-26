<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Disconnect_Reason\Disconnect_Reason_Google;
use ahrefs\AhrefsSeo\Messages\Message;
$locals  = Ahrefs_Seo_View::get_template_variables();
$view    = Ahrefs_Seo::get()->get_view();
$message = ( new Disconnect_Reason_Google() )->get_reason();
if ( ! is_null( $message ) ) { // show only once and clear message.
	$message->show();
	( new Disconnect_Reason_Google() )->save_reason( null );
	?>
	<script type="text/javascript">
		jQuery('h1').after( jQuery('.tip-google').detach() );
	</script>
	<?php
}
$messages = Ahrefs_Seo_Errors::get_current_messages();
if ( $messages ) {
	$view->show_part( 'notices/please-contact', $messages );
}
if ( ! current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_SAVE ) ) {
	Message::action_not_allowed( __( 'The account is not connected. Please, contact your site administrator to set it up.', 'ahrefs-seo' ) )->show();
	return;
}
?>

<form method="post" action="" class="ahrefs-seo-wizard ahrefs-analytics">
	<input type="hidden" name="analytics_step" value="1">
	<input type="hidden" name="google_auth" value="1">
	<?php
	wp_nonce_field( Ahrefs_Seo_Analytics::NONCE_INTERNAL_REDIRECT );
	?>
	<div class="card-item">
		<div class="help">
			<div class="google-logos">
				<img src="<?php echo esc_attr( AHREFS_SEO_IMAGES_URL . 'google-analytics.svg' ); ?>">
				<img src="<?php echo esc_attr( AHREFS_SEO_IMAGES_URL . 'google-gsc.svg' ); ?>">
			</div>
			<?php
			esc_html_e( 'Connect your Google Analytics & Search Console accounts to see your pages’ rankings and traffic stats right in WP dashboard. The Content Audit and content suggestion are based on these data.', 'ahrefs-seo' );
			?>
		</div>
		<?php
		if ( $locals['token_set'] && ( $locals['no_ga'] || $locals['no_gsc'] ) ) {
			$view->show_part( 'options-tips/no-google', $locals );
		}
		?>
		<div class="new-token-button">
			<div class="input_button">
				<a href="#" class="button button-primary" id="step2_1_submit">
				<?php
				esc_html_e( 'Connect GA & GSC accounts', 'ahrefs-seo' );
				?>
				</a>
			</div>
		</div>
	</div>
	<div class="help-ga-gsc">
		<?php
		esc_html_e( "GA and GSC data is stored in your website's database, and is never sent to Ahrefs.", 'ahrefs-seo' );
		?>
	</div>
</form>

<?php 