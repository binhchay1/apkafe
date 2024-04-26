<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

?>
<div class="more-wrap">
	<div class="more-column-action">
		<div class="column-title"><?php esc_html_e( 'Why', 'ahrefs-seo' ); ?></div>
		<p><?php esc_html_e( 'This page was not analyzed because it was added after the previous audit was completed. Run an audit again to include in this page in the analysis.', 'ahrefs-seo' ); ?></p>
		<div class="with-button">
			<a href="#" class="button action-start"><span></span><?php esc_html_e( 'Run audit', 'ahrefs-seo' ); ?></a>
			<a href="#" class="button action-stop"><span></span><?php esc_html_e( 'Exclude from audit', 'ahrefs-seo' ); ?></a>
		</div>
	</div>
	<div class="more-column-performance">
	</div>
</div>
