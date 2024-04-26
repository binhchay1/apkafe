<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Third_Party\Sources;
$locals       = Ahrefs_Seo_View::get_template_variables();
$post_tax     = $locals['post_tax'];
$kw           = Post_Tax_With_Keywords::create_from( $post_tax )->load_from_db();
$keyword      = $kw->get_keyword_current();
$is_suggested = ! is_null( $keyword ) && '' !== $keyword && ! $kw->get_is_keyword_approved() && ! in_array( $kw->get_keyword_source(), [ Sources::SOURCE_AIOSEO, Sources::SOURCE_RANKMATH, Sources::SOURCE_YOASTSEO ], true );
if ( $is_suggested ) { // item has non-empty suggested keyword.
	?>
	<!-- approve keyword -->
	<div class="ahrefs-content-tip tip-notice tip-keyword-need-approve">
		<div class="text">
		<?php
		printf(
		/* translators: %s: current keyword in bold */
			esc_html__( 'You haven’t confirmed %s as the target keyword for this page. Please approve it if it’s the keyword that you optimized the page for.', 'ahrefs-seo' ),
			'<b>' . esc_html( $keyword ) . '</b>'
		);
		?>
		</div>
		<?php
		if ( $post_tax->user_can_manage() ) {
			?>
			<div class="buttons">
				<a class="button button-primary button-keyword-approve" id="keyword_approve_button" href="#">
				<?php
				esc_html_e( 'Approve', 'ahrefs-seo' );
				?>
		</a>
				<a class="button-keyword-change" href="#">
				<?php
				esc_html_e( 'Change...', 'ahrefs-seo' );
				?>
		</a>
			</div>
			<?php
		}
		?>
	</div>
	<?php
}