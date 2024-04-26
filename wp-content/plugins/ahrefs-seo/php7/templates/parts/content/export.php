<?php
/**
 * Export to CSV button at the right of table tabs list.
 */

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Export\Export_Audit_Data;

$locals      = Ahrefs_Seo_View::get_template_variables();
$current_tab = $locals['tab'] ?? '';
$url         = ( new Export_Audit_Data() )->get_export_url( true, "$current_tab" );

if ( current_user_can( Ahrefs_Seo::CAP_EXPORT_CSV ) ) {
	?>
	<div class="export-wrap">
		<a href="<?php echo esc_attr( $url ); ?>" class="button button-export with-icon download-button">
		<?php esc_html_e( 'Export to CSV', 'ahrefs-seo' ); ?>
		</a>
	</div>
	<?php
}
