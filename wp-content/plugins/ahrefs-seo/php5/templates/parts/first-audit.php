<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Messages\Message;
$link            = Links::settings_scope_with_return();
$allow_run_audit = current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_RUN );
$can_run_audit   = Ahrefs_Seo_Data_Content::get()->can_run_new_audit();
if ( ! $allow_run_audit ) {
	Message::action_not_allowed( __( 'Sorry, you are not allowed to run content audits.', 'ahrefs-seo' ) )->show();
}
?>
<div class="first-audit-wrap">
	<form method="post" action="" class="ahrefs-seo-wizard ahrefs-audit">
		<?php
		wp_nonce_field( Ahrefs_Seo_Table::ACTION, 'table_nonce' );
		?>
		<div class="image-wrap-w">
			<img src="<?php echo esc_attr( AHREFS_SEO_IMAGES_URL ); ?>ahrefs-connect.png"
				srcset="<?php echo esc_attr( AHREFS_SEO_IMAGES_URL ); ?>ahrefs-connect-2x.png 2x, 
<?php echo esc_attr( AHREFS_SEO_IMAGES_URL ); ?>ahrefs-connect-3x.png 3x"
				class="ahrefs-wp-plugin_2x">
		</div>
		<h1>
		<?php
		esc_html_e( 'Start your first audit', 'ahrefs-seo' );
		?>
		</h1>
		<p>
		<?php
		printf(
	/* translators: %s: link to plugin settings. */
			esc_html__( "Start your first content audit. Don't forget to check the %s just in case.", 'ahrefs-seo' ),
			sprintf( '<a href="%s">%s</a>', esc_attr( $link ), esc_html__( 'plugin settings', 'ahrefs-seo' ) )
		);
		?>
			</p>

		<div class="button-wrap">
			<a href="#" class="button button-hero button-primary wizard-run-button manual-update-content-link 
			<?php
			if ( ! $allow_run_audit || ! $can_run_audit ) {
				?>
				disabled
				<?php
			} ?>" id="first_audit"><span></span>
	<?php
	esc_html_e( 'Run content audit', 'ahrefs-seo' );
	?>
</a>
		</div>

	</form>
</div>

<?php 