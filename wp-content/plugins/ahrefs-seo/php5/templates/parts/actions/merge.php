<?php

namespace ahrefs\AhrefsSeo;

$locals    = Ahrefs_Seo_View::get_template_variables();
$view      = Ahrefs_Seo::get()->get_view();
$post_tax  = $locals['post_tax'];
$backlinks = Ahrefs_Seo_Data_Content::get()->content_get_backlinks_for_post( $post_tax );
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
				'unique-keyword' => false,
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
			$view->show_part( 'action-steps/consolidate', [ 'post_tax' => $post_tax ] );
			$view->show_part( 'action-steps/republish', [ 'post_tax' => $post_tax ] );
			$view->show_part( 'action-steps/merge', [ 'post_tax' => $post_tax ] );
			?>
		</ol>
	</div>
</div>
<?php 