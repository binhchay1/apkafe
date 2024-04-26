<?php

namespace ahrefs\AhrefsSeo;

$locals            = Ahrefs_Seo_View::get_template_variables();
$view              = Ahrefs_Seo::get()->get_view();
$locals['enabled'] = Ahrefs_Seo::allow_reports();
?>

<form method="post" action="" class="ahrefs-seo-settings-diagnostics">
	<?php
	if ( isset( $locals['page_nonce'] ) ) {
		wp_nonce_field( $locals['page_nonce'] );
	}
	?>
	<div class="card-item card-notifications">
		<?php
		$view->show_part( 'options/diagnostics', $locals );
		if ( ! isset( $locals['show_from_wizard'] ) || ! $locals['show_from_wizard'] ) {
			$view->show_part( 'options/share-config', $locals );
			$view->show_part( 'options/share-audit-data', $locals );
			$view->show_part( 'options/storing-audit-data', $locals );
		}
		?>
	</div>

	<?php
	if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_DATA_SAVE ) ) {
		?>
		<div class="button-wrap">
			<a href="#" class="button button-hero button-primary" id="ahrefs_diagnostics_submit">
			<?php
			esc_html_e( 'Save', 'ahrefs-seo' );
			?>
	</a>
		</div>
		<?php
	}
	?>
</form>
<?php 