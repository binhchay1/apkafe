<?php

namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
$url    = Links::content_audit( Ahrefs_Seo_Data_Content::STATUS4_DROPPED ) . '#view-table';
?>
<!-- drops from well performing tip -->
<div class="ahrefs-content-tip" data-id="<?php echo esc_attr( $locals['tip_id'] ); ?>">
	<div class="caption">
	<?php
	esc_html_e( 'Some articles are no longer “well-performing”', 'ahrefs-seo' );
	?>
	</div>
	<div class="text">
	<?php
	esc_html_e( 'Several articles lost their “well-performing” status. It’s worth checking them out and figuring out the causes.', 'ahrefs-seo' );
	?>
	</div>
	<div class="buttons">
		<a class="button button-primary content_tip_show_dropped" href="<?php echo esc_attr( $url ); ?>">
																					<?php
																					esc_html_e( 'View articles', 'ahrefs-seo' );
																					?>
		</a>
	</div>
	<button type="button" class="notice-dismiss suggested-tip-close-button"><span class="screen-reader-text">
	<?php
	esc_html_e( 'Dismiss this notice.', 'ahrefs-seo' );
	?>
	</span></button>
</div>
<?php 