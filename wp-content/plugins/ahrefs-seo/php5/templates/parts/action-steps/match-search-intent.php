<?php

namespace ahrefs\AhrefsSeo;

$locals          = Ahrefs_Seo_View::get_template_variables();
$keyword_current = $locals['keyword_current'];
$if_important    = $locals['if_important'];
$url             = 'https://www.google.com/search?q=' . rawurlencode( $keyword_current );
?>
<li>
	<p>
	<?php
	printf(
	/* translators: %s: keyword */
		esc_html__( 'Match search intent for %s', 'ahrefs-seo' ),
		sprintf( '<b>%s</b>', esc_html( $keyword_current ) )
	);
	?>
	</p>
	<p>
	<?php
	if ( $if_important ) {
		esc_html_e( 'If the keyword is important, we suggest checking the SERPs first to see if the search intent of your post and your target keyword are aligned.', 'ahrefs-seo' );
	} else {
		esc_html_e( 'This article is targeting a unique topic, but it doesn’t rank in the top three positions. Check the SERPs to see if the search intent of your post and your target keyword are aligned.', 'ahrefs-seo' );
	}
	?>
	</p>
	<p class="with-button">
		<a class="button link-like-button button-long-title" href="<?php echo esc_attr( $url ); ?>" target="_blank">
			<?php
/* translators: %s: current keyword */
			printf( esc_html__( 'SERP for %s', 'ahrefs-seo' ), '<b>' . esc_html( wp_trim_words( $keyword_current, 5 ) ) . '</b>' );
			?>
		</a>
		<a class="link-question" href="https://ahrefs.com/blog/search-intent/" target="_blank">
		<?php
		esc_html_e( 'How to optimize for search intent', 'ahrefs-seo' );
		?>
		</a>
	</p>
</li>
<?php 