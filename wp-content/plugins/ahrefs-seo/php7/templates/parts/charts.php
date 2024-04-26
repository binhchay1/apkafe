<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

?>
<!-- Charts -->
<div class="content-charts">
	<div class="content-chart chart-score">
		<div class="content-title"><?php esc_html_e( 'Performance Score', 'ahrefs-seo' ); ?></div>
		<div class="chart-wrap" id="charts_block_left">
			<?php
				Ahrefs_Seo_Charts::print_content_score_block();
			?>
		</div>
	</div>
	<div class="content-chart chart-actions">
		<div class="content-title"><?php esc_html_e( 'Posts & pages by performance', 'ahrefs-seo' ); ?></div>
		<div class="chart-wrap">
			<div class="chart">
				<div id="charts_block_right"><?php Ahrefs_Seo_Charts::print_svg_donut_chart(); ?></div>
			</div>
			<div class="content-details">
				<div class="content-item"  id="charts_block_right_legend">
				<?php Ahrefs_Seo_Charts::print_svg_donut_chart_legend(); ?>
				</div>
			</div>
			<div class="clear"></div>
		</div>
	</div>
</div>
