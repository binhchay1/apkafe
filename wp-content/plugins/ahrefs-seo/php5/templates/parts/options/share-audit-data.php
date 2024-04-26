<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Export\Export_Audit_Data;
use ahrefs\AhrefsSeo\Messages\Message;
$is_available = ( new Export_Audit_Data() )->has_zip_archive();
$url          = ( new Export_Audit_Data() )->get_export_url();
?>
<div class="input-wrap block-content">
	<a href="#" class="show-last-audit-data show-collapsed block-subtitle">
	<?php
	esc_html_e( 'My latest audit data', 'ahrefs-seo' );
	?>
	</a>
	<div class="collapsed-wrap">
		<div class="block-text block-content">
			<?php
			printf(
	/* translators: %s: text "our support team" with link */
				esc_html__( 'Download the latest audit data as a ZIP archive containing multiple CSV files. If you run into issues, %s might ask you to share this file in order to investigate.', 'ahrefs-seo' ),
				sprintf( '<a href="%s">%s</a>', esc_attr( Ahrefs_Seo::get_support_url( true ) ), esc_html__( 'our support team', 'ahrefs-seo' ) )
			);
			?>
		</div>
		<div class="download-csv-wrap">
			<?php
			if ( current_user_can( Ahrefs_Seo::CAP_EXPORT_ZIP ) ) {
				?>
				<a href="<?php echo esc_attr( $is_available ? $url : '#' ); ?>" class="button button-large with-icon download-button block-content
									<?php
									echo $is_available ? '' : ' disabled'; ?>">
				<?php
				esc_html_e( 'Download', 'ahrefs-seo' );
				?>
	</a>
				<?php
				if ( ! $is_available ) {
					?>
					<div class="notice notice-error">
						<p><strong>
						<?php
						esc_html_e( 'Class "ZipArchive" not found.', 'ahrefs-seo' );
						?>
		</strong></p>
						<p>
						<?php
						_e( 'For the <a href="http://www.php.net/manual/en/class.ziparchive.php">ZipArchive class</a> to be present, PHP needs to have the <a href="http://www.php.net/manual/en/book.zip.php">zip extension</a> installed. See the <a href="http://www.php.net/manual/en/zip.installation.php">Installation instructions</a>.', 'ahrefs-seo' );
						?>
		</p>
					</div>
					<?php
				}
				?>
				<?php
			} else { // not allowed.
				Message::view_not_allowed()->show();
			}
			?>
		</div>
	</div>
</div>
<?php 