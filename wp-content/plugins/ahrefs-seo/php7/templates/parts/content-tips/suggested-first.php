<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
$url    = add_query_arg(
	[
		'keywords' => Ahrefs_Seo_Table_Content::FILTER_KEYWORDS_SUGGESTED,
	],
	Links::content_audit( Ahrefs_Seo_Data_Content::STATUS4_ALL_ANALYZED )
) . '#view-table';
?>
<!-- first audit notice -->
<div class="ahrefs-content-tip" data-time="first" data-id="<?php echo esc_attr( $locals['tip_id'] ); ?>">
	<div class="caption"><?php esc_html_e( 'Review the suggested target keywords', 'ahrefs-seo' ); ?></div>
	<div class="text"><?php esc_html_e( 'The suggested keywords for the first content audit are based on data pulled from Google Search Console. We recommend going through each suggested keyword to make sure it’s the same keyword you’ve optimized an article for.', 'ahrefs-seo' ); ?></div>
	<div class="text"><?php esc_html_e( 'Choosing the right target keywords is important since the content audit is based on your articles’ ranking performance for these keywords.', 'ahrefs-seo' ); ?></div>
	<div class="buttons">
		<a class="button button-primary content_tip_show_suggested" href="<?php echo esc_attr( $url ); ?>"><?php esc_html_e( 'Check suggested keywords', 'ahrefs-seo' ); ?></a>
		<a class="link" href="https://ahrefs.com/blog/keyword-research/" target="_blank"><?php esc_html_e( 'How to do keyword research', 'ahrefs-seo' ); ?> <span class="arrow-right">→</span></a>
	</div>
	<button type="button" class="notice-dismiss suggested-tip-close-button"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'ahrefs-seo' ); ?></span></button>
</div>
