<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
$view   = Ahrefs_Seo::get()->get_view();
$api    = Ahrefs_Seo_Api::get();
$data   = $api->get_subscription_info(); // this also will refresh result of is_limited_account() call.

$is_free_plan = $api->is_free_account( false ); // uncached result.
$plan         = $data['subscription'] ?? __( 'Unknown', 'ahrefs-seo' );

$rows_left = isset( $data['rows_left'] ) ?
/* translators: %s: number of data rows left */
sprintf( __( '%s left', 'ahrefs-seo' ), number_format( intval( $data['rows_left'] ), 0 ) )
:
__( 'Unknown', 'ahrefs-seo' );

$disconnect_link = add_query_arg(
	[ 'disconnect-ahrefs' => wp_create_nonce( $locals['page_nonce'] ) ],
	'settings' === $locals['disconnect_link'] ? Links::settings( Ahrefs_Seo_Screen_Settings::TAB_ACCOUNT ) : Links::wizard_step( 1 )
);
$message         = $api->get_last_error();
// filter error message.
Ahrefs_Seo_Compatibility::filter_messages( $message );

if ( '' !== $message ) {
	?><div class="updated notice error is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
	<?php
}
?>

<form method="post" action="" class="ahrefs-seo-wizard ahrefs-token">
	<input type="hidden" name="ahrefs_step" value="2">
	<?php
	if ( isset( $locals['page_nonce'] ) ) {
		wp_nonce_field( $locals['page_nonce'] );
	}
	?>
	<div class="card-item">
		<div class="image-wrap">
			<img src="<?php echo esc_attr( AHREFS_SEO_IMAGES_URL ); ?>ahrefs-connect.png"
				srcset="<?php echo esc_attr( AHREFS_SEO_IMAGES_URL ); ?>ahrefs-connect-2x.png 2x,
				<?php echo esc_attr( AHREFS_SEO_IMAGES_URL ); ?>ahrefs-connect-3x.png 3x"
				class="ahrefs-wp-plugin_2x">
		</div>

		<?php
		if ( $is_free_plan ) {
			?>
			<div class="disconnect-wrap">
				<div class="your-account"><?php esc_html_e( 'Free Ahrefs account is connected to your WP dashboard', 'ahrefs-seo' ); ?></div>
				<?php
				if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_SAVE ) ) {
					?>
					<div class="account-actions">
						<a href="<?php echo esc_attr( $disconnect_link ); ?>" class="disconnect-button" id="ahrefs_disconnect"><span class="text"><?php esc_html_e( 'Change account', 'ahrefs-seo' ); ?></span></a>
					</div>
					<?php
				}
				?>
			</div>

			<div class="help">
				<?php esc_html_e( 'The free Ahrefs connection allows you to analyze the performance of all content on your site and get a healthier website with fewer low-quality pages. To unlock monitoring new backlinks pointing to your website and tracking down negative SEO attacks, consider subscribing on one of the Ahrefs plans.', 'ahrefs-seo' ); ?>
			</div>
			<?php
			Ahrefs_Seo::get()->get_view()->learn_more_link( 'https://ahrefs.com/big-data', __( 'Learn more about Ahrefs', 'ahrefs-seo' ) );
		} else {
			?>

			<div class="disconnect-wrap">
				<div class="your-account"><?php esc_html_e( 'Connection success! Time to let the SEO sparks fly.', 'ahrefs-seo' ); ?></div>
				<?php
				if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_SAVE ) ) {
					?>
					<div class="account-actions">
						<a href="<?php echo esc_attr( $disconnect_link ); ?>" class="disconnect-button" id="ahrefs_disconnect"><span class="text"><?php esc_html_e( 'Change account', 'ahrefs-seo' ); ?></span></a>
					</div>
					<?php
				}
				?>
			</div>

			<div class="account-row"><span class="account-title"><?php esc_html_e( 'Plan', 'ahrefs-seo' ); ?></span><span class="account-value"><?php echo esc_html( $plan ); ?></span></div>
			<div class="account-row"><span class="account-title"><?php esc_html_e( 'Data rows', 'ahrefs-seo' ); ?></span><span class="account-value"><?php echo esc_html( $rows_left ); ?></span></div>

			<hr class="hr-shadow" />

			<div class="help"><?php esc_html_e( 'Data rows are consumed when we update for new backlinks.', 'ahrefs-seo' ); ?></div>
			<?php
		}
		?>
	</div>

	<?php
	// block with error messages, if any happened.
	$messages = Ahrefs_Seo_Errors::get_current_messages();
	if ( $messages ) {
		$view->show_part( 'notices/please-contact', $messages );
		?>
		<script type="text/javascript">
			jQuery('h1').after( jQuery('#ahrefs_api_messages').detach() );
		</script>
		<?php
	}
	?>
	<?php if ( ! empty( $locals['show_button'] ) ) { ?>
		<div class="button-wrap">
			<input type="submit" name="submit" id="submit" class="button button-primary button-hero" value="<?php esc_attr_e( 'Continue', 'ahrefs-seo' ); ?>">
		</div>
	<?php } ?>
</form>
