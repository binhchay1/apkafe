<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

/**
 * @var array<string, string|bool> $locals {
 *
 *   @type string $tip_id
 *   @type bool $can_run_audit
 * }
 */
$locals = Ahrefs_Seo_View::get_template_variables();
// link to the Schedule Settings.
$link          = Links::settings_schedule_with_prefill_and_return();
$can_run_audit = Ahrefs_Seo_Data_Content::get()->can_run_new_audit();
?>
<!-- last audit notice -->
<div class="ahrefs-content-tip" id="last_content_audit_tip" data-id="<?php echo esc_attr( (string) $locals['tip_id'] ); ?>">
	<div class="caption"><?php esc_html_e( 'The last content audit was over 3 months ago', 'ahrefs-seo' ); ?></div>
	<div class="text"><?php esc_html_e( 'We suggest running an audit at least once a month. This will ensure all your pages’ metrics and rankings are updated.', 'ahrefs-seo' ); ?></div>
	<div class="text"><?php esc_html_e( 'Set up a schedule so the plugin will run audits automatically.', 'ahrefs-seo' ); ?></div>
	<div class="buttons">
		<?php
		if ( current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_RUN ) ) {
			?>
			<a
				class="uirole-target-run-audit-account run-audit-button button button-primary manual-update-content-link<?php if ( ! $can_run_audit ) { ?> disabled<?php } // phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace ?>"
				href="#"><?php esc_html_e( 'Run new audit', 'ahrefs-seo' ); ?></a>
			<?php
		}
		if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_SCHEDULE_VIEW ) ) {
			?>
			<a class="uirole-target-settings-schedule link content-audit-schedule" href="<?php echo esc_attr( $link ); ?>"><span></span><?php esc_html_e( 'Set up a schedule', 'ahrefs-seo' ); ?></a>
			<?php
		}
		?>
	</div>
</div>
