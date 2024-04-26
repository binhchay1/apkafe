<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals      = Ahrefs_Seo_View::get_template_variables();
$view        = Ahrefs_Seo::get()->get_view();
$post_tax    = $locals['post_tax'];
$median      = floatval( ( new Snapshot() )->get_traffic_median( Ahrefs_Seo_Data_Content::get()->snapshot_context_get() ) );
$advisor     = Ahrefs_Seo_Advisor::get();
$has_keyword = $advisor->has_keyword( $post_tax );
?>
<div class="more-wrap">
	<div class="more-column-performance">
		<div class="column-title"><?php esc_html_e( 'Performance', 'ahrefs-seo' ); ?></div>
		<?php
		$view->show_part(
			'action-parts/pages-performance',
			[
				'post_tax'       => $post_tax,
				'no-keyword'     => ! $has_keyword,
				'position'       => 21,
				'decent-traffic' => true,
			]
		);
		?>
	</div>
	<div class="more-column-action">
		<?php $view->show_part( 'action-parts/approve-keyword-tip', [ 'post_tax' => $post_tax ] ); ?>
		<div class="column-title"><?php esc_html_e( 'Recommended action', 'ahrefs-seo' ); ?></div>
		<p>
			<?php
			/* translators: %s: traffic median value */
			echo esc_html( sprintf( __( 'According to Google Analytics, your website’s pages get a median of %s traffic every month.', 'ahrefs-seo' ), number_format( $median, 1 ) ) );
			?>
		</p>
		<p><?php esc_html_e( 'This page is not ranking in the top positions for any keyword, but it is in the top 50th percentile of all pages on your website when it comes to traffic.', 'ahrefs-seo' ); ?></p>
		<p><?php esc_html_e( 'We suggest excluding this article from your content audit so that it doesn’t affect your score.', 'ahrefs-seo' ); ?></p>
		<div class="with-button"><a href="#" class="button action-stop"><span></span><?php esc_html_e( 'Exclude from audit', 'ahrefs-seo' ); ?></a></div>
	</div>
</div>
