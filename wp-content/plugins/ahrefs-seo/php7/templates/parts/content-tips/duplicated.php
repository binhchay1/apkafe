<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
$url    = add_query_arg(
	[
		'keywords' => Ahrefs_Seo_Table_Content::FILTER_KEYWORDS_DUPLICATED,
	],
	Links::content_audit( Ahrefs_Seo_Data_Content::STATUS4_ALL_ANALYZED )
) . '#view-table';
?>
<!-- duplicated keywords notice -->
<div class="ahrefs-content-tip" data-time="first" data-id="<?php echo esc_attr( $locals['tip_id'] ); ?>">
	<div class="caption"><?php esc_html_e( 'Duplicate keywords found', 'ahrefs-seo' ); ?></div>
	<div class="text"><?php esc_html_e( 'You have articles with duplicate keywords, which might indicate keyword cannibalization issues. We recommend checking them out.', 'ahrefs-seo' ); ?></div>
	<div class="buttons">
		<a class="button button-primary content_tip_show_duplicated" href="<?php echo esc_attr( $url ); ?>"><?php esc_html_e( 'View keyword duplicates', 'ahrefs-seo' ); ?></a>
		<a class="link" href="https://ahrefs.com/blog/keyword-cannibalization/" target="_blank"><?php esc_html_e( 'How to fix keyword cannibalization', 'ahrefs-seo' ); ?> <span class="arrow-right">→</span></a>
	</div>
	<button type="button" class="notice-dismiss suggested-tip-close-button"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'ahrefs-seo' ); ?></span></button>
</div>
