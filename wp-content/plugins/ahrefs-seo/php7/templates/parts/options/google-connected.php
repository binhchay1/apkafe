<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Admin_Notice\Google_Connection;
use ahrefs\AhrefsSeo\Disconnect_Reason\Disconnect_Reason_GSC;
use ahrefs\AhrefsSeo\Messages\Message;

$locals    = Ahrefs_Seo_View::get_template_variables();
$view      = Ahrefs_Seo::get()->get_view();
$analytics = Ahrefs_Seo_Analytics::get();
// get ua list and current choice.
$connected = $analytics->get_data_tokens()->get_ua_id() && $analytics->get_data_tokens()->get_gsc_site();
$is_wizard = $locals['is_wizard'] ?? false;
if ( empty( $locals['button_title'] ) ) {
	$locals['button_title'] = __( 'Continue', 'ahrefs-seo' );
}
$preselect = $locals['preselect_accounts'];
$classes   = [];
if ( $preselect ) {
	$incorrect_account = ( false === $analytics->is_gsc_account_correct() ) || ( false === $analytics->is_ga_account_correct() );
	$classes[]         = 'autodetect';
	if ( $incorrect_account ) {
		$classes[] = 'no-suitable-account';
	}
} else {
	$incorrect_account = ( ! $analytics->is_gsc_account_correct() ) || ( ! $analytics->is_ga_account_correct() );
	if ( $incorrect_account ) {
		$classes[] = 'incorrect-account';
	}
}

if ( $preselect && ! current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_SAVE ) ) {
	// There is a strange situation: user without required "save" permission opened the Google account page for view and autodetect is pending.
	Message::action_not_allowed( __( 'The account is not connected. Please, contact your site administrator to set it up.', 'ahrefs-seo' ) )->show();
	return;
}
if ( ! $is_wizard ) {
	( new Google_Connection() )->maybe_show();
}
?>
<form method="post" action="" id="ahrefs_seo_google_connected" class="ahrefs-seo-wizard ahrefs-analytics <?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<input type="hidden" name="analytics_step" value="2">
	<?php
	if ( isset( $locals['page_nonce'] ) ) {
		wp_nonce_field( $locals['page_nonce'] );
	}
	?>
	<div class="card-item">
		<div class="help">
			<div class="google-logos">
				<img src="<?php echo esc_attr( AHREFS_SEO_IMAGES_URL . 'google-analytics.svg' ); ?>">
				<img src="<?php echo esc_attr( AHREFS_SEO_IMAGES_URL . 'google-gsc.svg' ); ?>">
			</div>
			<?php esc_html_e( 'Connect your Google Analytics & Search Console accounts to see your pages’ rankings and traffic stats right in WP dashboard. The Content Audit and content suggestion are based on these data.', 'ahrefs-seo' ); ?>
		</div>
		<hr class="hr-shadow" />
		<div class="no-account-detected-tip">
			<?php
			$view->show_part( 'options-tips/not-detected', $locals );
			?>
		</div>
		<div class="incorrect-account-tip">
			<?php
			$view->show_part( 'options-tips/incorrect', $locals );
			?>
		</div>

		<div class="disconnect-wrap">
			<div class="your-account"><?php esc_html_e( 'Your Google Analytics & Search Console accounts are connected', 'ahrefs-seo' ); ?></div>
			<div class="your-account your-account-detecting"><?php esc_html_e( 'Autoselecting the best Google Analytics & Search Console profiles...', 'ahrefs-seo' ); ?></div>
			<div class="your-account no-account-detected"><?php esc_html_e( 'Your Google account is connected but no suitable profiles were detected', 'ahrefs-seo' ); ?></div>
			<?php
			if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_SAVE ) ) {
				?>
				<div class="account-actions">
					<a href="<?php echo esc_attr( $locals['disconnect_url'] ); ?>" class="disconnect-button" id="ahrefs_disconnect"><span class="text"><?php esc_html_e( 'Disconnect', 'ahrefs-seo' ); ?></span></a>
					<?php
					if ( ! $analytics->get_data_tokens()->is_using_direct_connection() ) {
						$connected_sites_url = add_query_arg(
							[
								'google_sites_list' => wp_create_nonce( Ahrefs_Seo_Analytics::NONCE_INTERNAL_REDIRECT ),
								'_'                 => time(),
							],
							'settings' === $locals['disconnect_link'] ? Links::settings( Ahrefs_Seo_Screen_Settings::TAB_ANALYTICS ) : Links::wizard_step( 2 )
						);
						?>
						<a href="<?php echo esc_attr( $connected_sites_url ); ?>" class="sites-button">Connected sites</a>
						<?php
					}
					?>
				</div>
				<?php
			}
			?>
		</div>
		<div class="accounts-wrap">
			<div id="loader_google_detect">
				<div class="row-loader loader-transparent"><div class="loader"></div></div>
			</div>
			<?php
			if ( $preselect ) {
				?>
				<!-- hide accounts loader -->
				<style>#loader_while_accounts_loaded{display:none;}</style>
				<?php
			}
			echo '<!-- padding ' . str_pad( '', 10240, ' ' ) . ' -->'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$view->flush(); // show loader while settings screen is loading.
			$view->show_part( 'options/include/select-ga', $locals );
			$view->show_part( 'options/include/select-gsc', $locals );
			?>
		</div>
		<?php $view->show_part( 'options/include/google-advanced', $locals ); ?>
	</div>
	<?php

	// block with error messages, if any happened.
	$messages         = Ahrefs_Seo_Errors::get_current_messages();
	$gsc_disconnected = ( new Disconnect_Reason_GSC() )->get_reason();
	if ( $messages ) {
		$view->show_part( 'messages', [ 'messages' => $messages ] );
		?>
		<script type="text/javascript">
			jQuery('h1').after( jQuery('.ahrefs_messages_block').detach() );
		</script>
		<?php
	}
	if ( ! is_null( $gsc_disconnected ) ) {
		$gsc_disconnected->show();
		?>
		<script type="text/javascript">
			jQuery('h1').after( jQuery('.tip-google').detach() );
		</script>
		<?php
	}
	?>
	<div class="help-ga-gsc">
		<?php esc_html_e( "GA and GSC data is stored in your website's database, and is never sent to Ahrefs.", 'ahrefs-seo' ); ?>
	</div>
	<?php
	$can_save = current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_SAVE );
	if ( ! $can_save ) {
		Message::edit_not_allowed()->show();
	}
	?>
	<div class="button-wrap">
		<a href="#" class="button button-hero button-primary" id="ahrefs_seo_submit" <?php disabled( ! $connected || ! $can_save ); ?>><?php echo esc_html( $locals['button_title'] ); ?></a>
	</div>
	</form>
<?php
if ( $preselect ) {
	?>
	<script type="text/javascript">
		console.log('autodetect google');
		jQuery(function() {
			ahrefs_settings.autodetect();
		});
	</script>
	<?php
}
