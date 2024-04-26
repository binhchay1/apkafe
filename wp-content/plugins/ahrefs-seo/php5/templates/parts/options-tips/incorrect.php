<?php

namespace ahrefs\AhrefsSeo;

?>
<!-- not detected tip -->
<div class="ahrefs-content-tip tip-notice">
	<div class="caption">
	<?php
/* translators: %s: current domain */
	printf( esc_html__( "Google profiles selected don't match %s", 'ahrefs-seo' ), esc_html( Ahrefs_Seo::get_current_domain() ) );
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
</div>
<?php 