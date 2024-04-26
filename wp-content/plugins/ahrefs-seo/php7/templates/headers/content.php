<?php
/**
 * Content header template
 */

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals            = Ahrefs_Seo_View::get_template_variables();
$link              = Links::settings_scope_with_return();
$stat              = Ahrefs_Seo_Data_Content::get()->get_statistics();
$can_run_audit     = Ahrefs_Seo_Data_Content::get()->can_run_new_audit();
$paused            = Content_Audit::audit_is_paused();
$audit_in_progress = $stat['in_progress']; // both active or paused.
$allow_run_audit   = current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_RUN ); // For both [Run new audit] and [Resume] buttons.
$allow_scope       = current_user_can( Ahrefs_Seo::CAP_SETTINGS_AUDIT_VIEW );

if ( ! isset( $locals['header_class'] ) || ! is_array( $locals['header_class'] ) ) {
	$locals['header_class'] = [];
}
?>
<div id="ahrefs_seo_screen" class="<?php echo esc_attr( implode( ' ', $locals['header_class'] ) ); ?>">

<div class="ahrefs-header-wrap">
	<div class="ahrefs-header">

		<div>
			<img src="<?php echo esc_attr( AHREFS_SEO_IMAGES_URL . 'logo.svg' ); ?>" alt="<?php esc_attr_e( 'Content Audit', 'ahrefs-seo' ); ?>" class="logo">
			<span class="header-hint">
				<?php
				foreach ( $stat['last_time'] as $string ) {
					?>
					<span>
						<?php
						echo esc_html( $string );
						?>
					</span>
					<?php
				}
				?>
			</span>
		</div>
		<div class="content-right">
			<div>
				<a class="content-hint-support" href="<?php echo esc_attr( Ahrefs_Seo::get_support_url() ); ?>" target="_blank"><span class="dashicons dashicons-email"></span><?php esc_html_e( 'Support', 'ahrefs-seo' ); ?></a>
			</div><!--
			--><div>
				<a class="content-hint-how" href="https://help.ahrefs.com/en/collections/2253902-wordpress-plugin" target="_blank"> <?php esc_html_e( 'Help', 'ahrefs-seo' ); ?></a>
			</div><!--
			--><div id="content_audit_status" class="<?php echo esc_attr( $audit_in_progress ? 'in-progress' : '' ); ?><?php echo esc_attr( $paused ? ' paused' : '' ); ?>">
				<a
					class="run-audit-button button-orange manual-update-content-link<?php if ( ! $can_run_audit ) { ?> disabled<?php } if ( ! $allow_run_audit ) { ?> disallowed<?php } // phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace ?>"
					href="#"
					<?php
					if ( ! $allow_run_audit ) {
						?> title="<?php esc_attr_e( 'Sorry, you are not allowed to run content audits.', 'ahrefs-seo' ); ?>"<?php } // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentAfterEnd ?>
					>
					<?php
					/* Translators: Button title at the header, must be short */
					echo esc_html_x( 'Run new audit', 'button title', 'ahrefs-seo' );
					?>
				</a>
				<a
					class="paused-audit-button button-dark<?php if ( ! $can_run_audit ) { ?> disabled<?php } if ( ! $allow_run_audit ) { ?> disallowed<?php }  // phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace ?>"
					href="#"
					<?php
					if ( ! $allow_run_audit ) {
						?> title="<?php esc_attr_e( 'Sorry, you are not allowed to run content audits.', 'ahrefs-seo' ); ?>"<?php } // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentAfterEnd ?>
					>
					<?php
					/* Translators: Button title at the header, must be short */
					echo esc_html_x( 'Resume audit', 'button title', 'ahrefs-seo' );
					?>
				</a>
				<div class="audit-progressbar" title="<?php esc_attr_e( 'To make the audit go faster, we recommend leaving the browser tab open.', 'ahrefs-seo' ); ?>&nbsp;<a target='_blank' href='https://help.ahrefs.com/en/articles/5879793-what-does-the-speed-of-the-audit-depend-on' class='internal-hint-a'><?php esc_attr_e( 'Learn more', 'ahrefs-seo' ); ?></a>">
					<div class="position"
						<?php if ( $stat['percents'] > 0 ) { ?> style="width:<?php echo esc_attr( $stat['percents'] . '%' ); // phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace ?>"<?php } ?>
						data-position="<?php echo esc_attr( $stat['percents'] > 0 ? $stat['percents'] : '0' ); ?>"
						>
					</div>
					<div class="progress">
						<?php
						if ( $stat['percents'] > 0 ) {
							echo esc_html( str_replace( '{0}', number_format( $stat['percents'], $stat['percents'] < 10 ? 2 : 1 ), _x( 'Analyzing: {0}%', 'button title', 'ahrefs-seo' ) ) );
						} else {

							/* Translators: Button title at the header, must be short */
							echo esc_html_x( 'Analyzing...', 'button title', 'ahrefs-seo' );
						}
						?>
					</div>
				</div>
			</div><!--
			--><div id="content_audit_cancel" class="<?php echo esc_attr( $audit_in_progress ? 'in-progress' : '' ); ?>">
				<?php
				if ( $allow_run_audit ) {
					?>
					<a id="analysis_cancel_button" class="button" href="#" title="<?php esc_attr_e( 'This will cancel the current audit. The report will keep showing the previous content audit results, if there were any.', 'ahrefs-seo' ); ?>">
						<span class="enabled">
						<?php
						/* Translators: Button title at the header, must be short */
						echo esc_html_x( 'Cancel audit', 'button title', 'ahrefs-seo' );
						?>
							</span><span class="disabled">
							<?php
						/* Translators: Button title at the header, must be short. Meant shortened phrase "Canceling audit". */
							echo esc_html_x( 'Canceling...', 'button title', 'ahrefs-seo' );
							?>
							</span>
					</a>
				<?php } ?>
			</div><!--
			--><div class="uirole-target-settings-scope">
			<?php
			if ( $allow_scope ) {
				?>
				<a id="analysis_setting_button" class="button" href="<?php echo esc_attr( $link ); ?>"></a>
			<?php } ?>
			</div>
		</div>
	</div>
</div>
