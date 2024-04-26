<?php

namespace ahrefs\AhrefsSeo;

$locals      = Ahrefs_Seo_View::get_template_variables();
$view        = Ahrefs_Seo::get()->get_view();
$post_tax    = $locals['post_tax'];
$backlinks   = Ahrefs_Seo_Data_Content::get()->content_get_backlinks_for_post( $post_tax );
$ref_domains = Ahrefs_Seo_Data_Content::get()->content_get_ref_domains_for_post( $post_tax ); // load cached value.
$advisor           = Ahrefs_Seo_Advisor::get();
$has_keyword       = $advisor->has_keyword( $post_tax );
$is_keyword_unique = ! $advisor->has_active_pages_with_same_keywords( $post_tax );
$keyword_current   = Post_Tax_With_Keywords::create_from( $post_tax )->load_from_db()->get_keyword_current();
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
				'position'       => 20,
				'unique-keyword' => $has_keyword && $is_keyword_unique,
				'backlinks'      => $backlinks,
			]
		);
		?>
	</div>
	<div class="more-column-action wider">
		<?php
		$view->show_part( 'action-parts/approve-keyword-tip', [ 'post_tax' => $post_tax ] );
		?>
		<div class="column-title with-list">
		<?php
		esc_html_e( 'Recommended actions', 'ahrefs-seo' );
		?>
		</div>
		<ol>
			<?php
			if ( $has_keyword ) { // note: keyword here should be unique, because status for non-unique is merge.
				$view->show_part(
					'action-steps/match-search-intent',
					[
						'post_tax'        => $post_tax,
						'keyword_current' => $keyword_current,
						'if_important'    => false,
					]
				);
				$view->show_part(
					'action-steps/cover-topic',
					[
						'post_tax' => $post_tax,
						'revamp'   => false,
					]
				);
			} else {
				$view->show_part( 'action-steps/keyword-research', [ 'post_tax' => $post_tax ] );
			}
			$view->show_part(
				'action-steps/analyze-backlinks',
				[
					'post_tax'    => $post_tax,
					'ref_domains' => $ref_domains,
					'keyword'     => $keyword_current,
				]
			);
			$view->show_part( 'action-steps/republish-promote', [ 'post_tax' => $post_tax ] );
			?>
		</ol>
	</div>
</div>
<?php 