<?php

namespace ahrefs\AhrefsSeo;

$locals              = Ahrefs_Seo_View::get_template_variables();
$view                = Ahrefs_Seo::get()->get_view();
$advisor             = Ahrefs_Seo_Advisor::get();
$post_tax            = $locals['post_tax'];
$revamp              = $locals['revamp'];
$keyword_current     = '';
$current_info        = []; // data for the first table.
$other_keywords_info = []; // data for the second table.
$advisor->fill_current_and_possible_keywords_data( $post_tax, $keyword_current, $current_info, $other_keywords_info );
?>
<li>
	<p>
		<?php
		if ( $revamp ) {
			printf(
				/* translators: %s: keyword */
				esc_html__( 'Revamp the article to cover the %s topic better', 'ahrefs-seo' ),
				sprintf( '<b>%s</b>', esc_html( $keyword_current ) )
			);
		} else {
			printf(
				/* translators: %s: keyword */
				esc_html__( 'Cover the %s topic better', 'ahrefs-seo' ),
				sprintf( '<b>%s</b>', esc_html( $keyword_current ) )
			);
		}
		?>
	</p>
	<p>
	<?php
	esc_html_e( 'Optimize your post for your target keyword by rewriting or updating it with new and unique content.', 'ahrefs-seo' );
	?>
	</p>
	<?php
	$view->show_part(
		'action-parts/table-positions',
		[
			'post_tax'    => $post_tax,
			'items'       => [ $current_info ],
			'show_footer' => false,
		]
	);
	if ( count( $other_keywords_info ) ) {
		?>
		<p>
		<?php
		esc_html_e( 'We also found other keywords that you’re ranking for at positions 4-20. These keywords don’t need to appear in your content verbatim, but it would help to make sure that you cover these topics in some way.', 'ahrefs-seo' );
		?>
	</p>
		<?php
		$view->show_part(
			'action-parts/table-positions',
			[
				'post_tax'    => $post_tax,
				'items'       => $other_keywords_info,
				'show_footer' => true,
			]
		);
	}
	?>
	<p class="with-button">
		<a class="link-question" href="https://ahrefs.com/blog/republishing-content/" target="_blank">
		<?php
		esc_html_e( 'How to update your content', 'ahrefs-seo' );
		?>
		</a>
	</p>
</li>
<?php 