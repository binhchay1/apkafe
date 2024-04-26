<?php

namespace ahrefs\AhrefsSeo;

/*
* We show this tip after user check or uncheck any new item in audit scope.
* We show tip until new audit started.
* If it is impossible to start new audit (no account or any another reason) or audit already is in progress - we do not show [Start new audit] button.
*/
$link            = Links::content_audit();
$button_disabled = Ahrefs_Seo_Api::get()->is_disconnected() || ! Ahrefs_Seo_Api::get()->is_disconnected() && Ahrefs_Seo_Api::get()->is_limited_account( true ) || ! Ahrefs_Seo_Analytics::get()->get_data_tokens()->is_token_set() || ! Ahrefs_Seo_Analytics::get()->is_ua_set() || ! Ahrefs_Seo_Analytics::get()->is_gsc_set() || ( new Content_Audit() )->require_update();
// update already in progress.
?>
<!-- scope updated notice -->
<div class="ahrefs-content-notice">
	<div class="caption">
	<?php
	esc_html_e( 'Content Audit options have been updated', 'ahrefs-seo' );
	?>
	</div>
	<div class="text">
	<?php
	if ( $button_disabled ) {
		esc_html_e( 'The scope of audit has been updated. Run an audit to analyze the newly included pages - please note that doing this will consume Ahrefs API rows.', 'ahrefs-seo' );
	} else {
		esc_html_e( 'The scope of audit has been updated. Run an audit right now to analyze the newly included pages - please note that doing this will consume Ahrefs API rows.', 'ahrefs-seo' );
	}
	?>
	</div>
	<?php
	if ( ! $button_disabled && current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_RUN ) ) {
		?>
	<div class="buttons">
		<form action="<?php echo esc_attr( $link ); ?>" method="POST">
			<?php
			wp_nonce_field( Ahrefs_Seo_Screen_Content::ACTION_NEW_AUDIT_RUN, 'scope_updated_nonce' );
			?>
			<input type="hidden" name="run_new_audit" value="1">
			<a class="run-audit-button button-orange manual-update-content-link-submit" href="#">
			<?php
			esc_html_e( 'Run new audit', 'ahrefs-seo' );
			?>
	</a>
		</form>
	</div>
		<?php
	}
	?>
</div>
<?php 