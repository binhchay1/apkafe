<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals            = Ahrefs_Seo_View::get_template_variables();
$has_keyword       = $locals['has_keyword'];
$is_keyword_unique = $locals['is_keyword_unique'];
$keyword_current   = $locals['keyword_current'] ?? '';

if ( $has_keyword && $is_keyword_unique ) {
	?>
	<li>
		<p>
			<?php
			printf(
				/* translators: %s: keyword */
				esc_html__( 'Decide if ranking for %s is important', 'ahrefs-seo' ),
				sprintf( '<b>%s</b>', esc_html( $keyword_current ) )
			);
			?>
		</p>
		<p><?php esc_html_e( 'If your page isn’t ranking in the top 20 positions for its target keyword, it’s probably going to take a lot of time and effort to crack the first page. You’ll need to decide if the effort is really worth the reward. If it isn’t, one option is to leave the page as-is and exclude it from future content audits so that it doesn’t affect your overall score. If the page isn’t important to your business at all, you might consider deleting it.', 'ahrefs-seo' ); ?></p>
		<p class="with-button">
			<a href="#" class="button action-stop"><span></span><?php esc_html_e( 'Exclude from audit', 'ahrefs-seo' ); ?></a>
		</p>
	</li>

	<?php
} elseif ( $has_keyword && ! $is_keyword_unique ) {
	?>
	<li>
		<p><?php esc_html_e( 'Decide if this page is important', 'ahrefs-seo' ); ?></p>
		<p><?php esc_html_e( 'If your page isn’t ranking in the top 20 positions for its target keyword, it’s probably going to take a lot of time and effort to crack the first page. You’ll need to decide if the effort is really worth the reward. If it isn’t, one option is to leave the page as-is and exclude it from future content audits so that it doesn’t affect your overall score. If the page isn’t important to your business at all, you might consider deleting it.', 'ahrefs-seo' ); ?></p>
		<p class="with-button">
			<a href="#" class="button action-stop"><span></span><?php esc_html_e( 'Exclude from audit', 'ahrefs-seo' ); ?></a>
		</p>
	</li>
	<?php
} else { // no keyword.
	?>
	<li>
		<p><?php esc_html_e( 'Decide if this page is important', 'ahrefs-seo' ); ?></p>
		<p><?php esc_html_e( 'This page doesn’t seem to be ranking for any keywords. If the page doesn’t need to rank in organic search and is still important for your visitors, leave it as-is and exclude it from future content audits so that it doesn’t affect your overall score. If the page isn’t important to your business at all, you might consider deleting it.', 'ahrefs-seo' ); ?></p>
		<p class="with-button">
			<a href="#" class="button action-stop"><span></span><?php esc_html_e( 'Exclude from audit', 'ahrefs-seo' ); ?></a>
		</p>
	</li>
	<?php
}
?>
