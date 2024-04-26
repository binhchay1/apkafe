<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$count  = Ahrefs_Seo_Data_Content::get_statuses_for_charts();
$order  = Ahrefs_Seo_Charts::get_order();
$colors = Ahrefs_Seo_Charts::get_colors();
$tabs   = Ahrefs_Seo_Charts::get_tabs();

foreach ( $order as $action ) {
	?>
	<p class="chart-legend-item" data-tab="<?php echo esc_attr( $tabs[ $action ] ); ?>">
	<span class="marker" style="<?php echo esc_attr( "background-color:{$colors[ $action ]}" ); ?>"></span>
	<?php echo esc_html( Ahrefs_Seo_Charts::get_title( $action ) ); ?>
	<span class="counter"><?php echo esc_html( "{$count[ $action ]}" ); ?></span>
	</p>
	<?php
}
