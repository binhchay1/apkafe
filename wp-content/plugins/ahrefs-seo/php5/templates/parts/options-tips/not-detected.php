<?php

namespace ahrefs\AhrefsSeo;

$locals    = Ahrefs_Seo_View::get_template_variables();
$view      = Ahrefs_Seo::get()->get_view();
$is_wizard = $locals['is_wizard']; // print Google connection string inside the notice.
?>
	<!-- not detected tip -->
	<div class="ahrefs-content-tip tip-notice">
		<div class="caption">
		<?php
		esc_html_e( 'Google Analytics & Search Console accounts were not found', 'ahrefs-seo' );
		?>
		</div>
		<div class="text">
		<?php
		esc_html_e( 'You might have authorized the wrong Google account which does not have access to the required traffic & search ranking data for this site.', 'ahrefs-seo' );
		?>
		</div>
		<div class="text">
			<?php
			Ahrefs_Seo::get()->get_view()->learn_more_link( 'https://help.ahrefs.com/en/articles/4666920-how-do-i-connect-google-analytics-search-console-to-the-plugin', __( 'How do I connect the right Google Analytics & Search Console accounts?', 'ahrefs-seo' ) );
			?>
		</div>
		<?php
		if ( $is_wizard ) {
			$view->show_part( 'options/share-config', [ 'is_wizard' => $is_wizard ] );
		}
		?>
	</div>
<?php 