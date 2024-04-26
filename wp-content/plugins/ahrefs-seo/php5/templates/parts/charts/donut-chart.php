<?php

namespace ahrefs\AhrefsSeo;

$count        = Ahrefs_Seo_Data_Content::get_statuses_for_charts();
$percents     = [];
$percents_sum = 0;
$total        = array_sum( $count );
if ( 0 === $total ) {
	$total = 1;
}
foreach ( $count as $key => $item ) {
	$percents[ $key ] = round( $item / $total * 100, 1 );
	$percents_sum    += $percents[ $key ];
}
if ( $percents_sum < 100 ) {
	// update because total must be 100%.
	foreach ( $percents as $key => $item ) {
		if ( $item > 0 ) {
			$percents[ $key ] += 100 - $percents_sum;
			break;
		}
	}
}
$colors = Ahrefs_Seo_Charts::get_colors();
$tabs   = Ahrefs_Seo_Charts::get_tabs();
?>
<svg width="100%" height="100%" viewBox="0 0 50 50" class="donut" id="svgroot">
	<circle class="donut-ring" cx="25" cy="25" r="16" fill="transparent" stroke="#ebebee" stroke-width="18"></circle>
	<circle class="donut-hole" cx="25" cy="25" r="7" fill="#fff"></circle>
	<?php
	$offset = 25;
	$order  = Ahrefs_Seo_Charts::get_order();
	foreach ( $order as $action ) {
		$item = $count[ $action ];
		if ( $item ) {
			$color   = $colors[ $action ];
			$percent = $percents[ $action ];
			?>
			<circle class="donut-segment" cx="25" cy="25" r="16" fill="none"
				stroke="<?php echo esc_attr( $color ); ?>" stroke-width="18"
				stroke-dasharray="<?php echo esc_attr( $percent . ' ' . ( 100 - $percent ) ); ?>"
				stroke-dashoffset="<?php echo esc_attr( "{$offset}" ); ?>"
				data-tab="<?php echo esc_attr( $tabs[ $action ] ); ?>"></circle>
			<?php
			$offset = $offset + ( 100 - $percent );
			if ( $offset > 100 ) {
				$offset -= 100;
			}
		}
	}
	?>
	<script>
		document.getElementById('svgroot').addEventListener( 'click', svg_send_to_parent, false);

		function svg_send_to_parent(e) {
			// SVGElementInstance objects aren't normal DOM nodes, so fetch the corresponding 'use' element instead
			var target = e.target;
			if ( target.correspondingUseElement )
				target = target.correspondingUseElement;

			// call a method in the parent document if it exists
			if (window.parent.content_svg_clicked) {
				window.parent.content_svg_clicked( target );
			}
			return false;
		}
	</script>
</svg>
<?php 