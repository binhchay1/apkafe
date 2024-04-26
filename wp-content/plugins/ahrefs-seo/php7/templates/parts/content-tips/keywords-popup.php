<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
?>
<!-- keywords tip -->
<div class="ahrefs-content-tip" data-id="<?php echo esc_attr( $locals['tip_id'] ); ?>">
	<div class="caption"><?php esc_html_e( 'Tip: How to choose proper target keyword', 'ahrefs-seo' ); ?></div>
	<div class="text"><?php esc_html_e( 'The last content audit was based on the suggested target keywords that were pulled from GSC. We encourage you to check each suggested keyword manually to make sure that it’s the one you’ve optimized an article for. Choosing the right target keywords is important since the whole content audit is based on your articles’ ranking performance for these keywords.', 'ahrefs-seo' ); ?></div>
	<div class="buttons">
		<a class="button button-primary" href="#" id="keywords_tip_got_it"><?php esc_html_e( 'Got it', 'ahrefs-seo' ); ?></a>
		<a class="link" href="https://ahrefs.com/blog/keyword-research/" target="_blank"><?php esc_html_e( 'Learn more about keyword research', 'ahrefs-seo' ); ?> <span class="arrow-right">→</span></a>
	</div>
</div>
