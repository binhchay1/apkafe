<?php

namespace ahrefs\AhrefsSeo;

$locals            = Ahrefs_Seo_View::get_template_variables();
$view              = Ahrefs_Seo::get()->get_view();
$post_tax          = $locals['post_tax'];
$backlinks         = Ahrefs_Seo_Data_Content::get()->content_get_backlinks_for_post( $post_tax );
$advisor           = Ahrefs_Seo_Advisor::get();
$has_keyword       = $advisor->has_keyword( $post_tax );
$is_keyword_unique = ! $advisor->has_active_pages_with_same_keywords( $post_tax );
?>
<div class="more-wrap">
	<div class="more-column-performance">
		<div class="column-title">
		<?php
		esc_html_e( 'Performance', 'ahrefs-seo' );
		?>
		</div>
		<?php
		$view->show_part(
			'action-parts/pages-performance',
			[
				'post_tax'       => $post_tax,
				'position'       => 3,
				'unique-keyword' => $has_keyword && $is_keyword_unique,
				'backlinks'      => $backlinks,
			]
		);
		?>
	</div>
	<div class="more-column-action">
		<?php
		$view->show_part( 'action-parts/approve-keyword-tip', [ 'post_tax' => $post_tax ] );
		?>
		<div class="column-title">
		<?php
		esc_html_e( 'Recommended action', 'ahrefs-seo' );
		?>
		</div>
		<p>
		<?php
		esc_html_e( 'This page is performing well in organic search. Great job! We suggest leaving the page as-is.', 'ahrefs-seo' );
		?>
		</p>
	</div>
</div>
<?php 