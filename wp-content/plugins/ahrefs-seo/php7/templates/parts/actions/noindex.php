<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$view   = Ahrefs_Seo::get()->get_view();
$locals = Ahrefs_Seo_View::get_template_variables();
/** @var Post_Tax $post_tax */
$post_tax = $locals['post_tax'];
$info     = Ahrefs_Seo_Db_Helper::load_additional_data_from_history( $post_tax, 'noindex_data' );

?>
<div class="more-wrap">
	<div class="more-column-action wider">
		<div class="column-title"><?php esc_html_e( 'Why', 'ahrefs-seo' ); ?></div>
		<p><?php esc_html_e( 'This page was not analyzed because a “noindex” tag was found on it. ', 'ahrefs-seo' ); ?></p>
		<?php
		if ( ! is_null( $info ) ) {
			$view->show_part( 'action-parts/table', [ 'items' => $info ] );
		}
		?>
		<p><?php esc_html_e( 'The “noindex” tag prevents pages from showing up in search results, and therefore does not get search traffic.', 'ahrefs-seo' ); ?></p>
		<p><?php esc_html_e( 'If you’d like this page to rank in search engines, you’ll have to remove the “noindex” tag from the page. Once you’ve done that, you can analyze this page again to get it included in the audit.', 'ahrefs-seo' ); ?></p>
		<p><a class="link-small" href="https://ahrefs.com/blog/meta-robots/"><?php esc_html_e( 'Learn more about noindex tags', 'ahrefs-seo' ); ?> <span class="arrow-right">→</span></a></p>
		<div class="with-button">
			<a href="#" class="button action-recheck"><span></span><?php esc_html_e( 'Recheck status', 'ahrefs-seo' ); ?></a>
			<a href="#" class="button action-start"><span></span><?php esc_html_e( 'Include to audit', 'ahrefs-seo' ); ?></a>
		</div>
	</div>
	<div class="more-column-performance">
	</div>
</div>
