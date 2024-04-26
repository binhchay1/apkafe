<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Export\Export_Audit_Data;

$locals      = Ahrefs_Seo_View::get_template_variables();
$post_tax    = $locals['post_tax'];
$items       = $locals['items'];
$show_footer = $locals['show_footer'];

$items = array_map(
	function( $row ) {
		$row['pos'] = ! is_null( $row['pos'] ) ? round( $row['pos'], 1 ) : null;
		return $row;
	},
	$items
);

$slug     = str_replace( [ ':', '/', '.' ], '-', trim( (string) wp_parse_url( $post_tax->get_url( true ), PHP_URL_PATH ), '/' ) );
$table_id = $show_footer ? 'positions_list' : 'positions_current';
?>
<div>
	<table class="positions-table positions-values-table<?php echo $show_footer ? ' show-footer' : ' no-footer'; ?>" id="<?php echo esc_attr( $table_id ); ?>">
		<thead>
			<tr>
				<th class="col-posi-keyword"><?php esc_html_e( 'Target keyword', 'ahrefs-seo' ); ?></th>
				<th class="col-posi-pos"><?php esc_html_e( 'Position', 'ahrefs-seo' ); ?></th>
				<th class="col-posi-clicks"><?php esc_html_e( 'Clicks', 'ahrefs-seo' ); ?></th>
				<th class="col-posi-impr"><?php esc_html_e( 'Impressions', 'ahrefs-seo' ); ?></th>
				<th class="col-posi-link">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ( $items as $values ) {
				?>
				<tr>
					<td><?php echo esc_html( $values['query'] ); ?></td>
					<td><?php echo esc_html( $values['pos'] ); ?></td>
					<td><?php echo esc_html( $values['clicks'] ); ?></td>
					<td><?php echo esc_html( $values['impr'] ); ?></td>
					<td><a href="#" class="positions-check-serp" data-keyword="<?php echo esc_attr( $values['query'] ); ?>" target="_blank"><?php esc_html_e( 'Check SERP', 'ahrefs-seo' ); ?></a></td>
				</tr>
				<?php
			}
			?>
		</tbody>
		<?php
		if ( $show_footer ) {
			?>
			<tfoot>
				<tr>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			</tfoot>
			<?php
		}
		?>
	</table>
</div>
<?php
if ( $show_footer ) {
	$export_data = new Export_Audit_Data();
	?>
	<textarea id="csv_data" style="display: none;" data-name="<?php echo esc_attr( $export_data->get_file_name_export_keywords_csv( $slug ) ); ?>"><?php // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
		$export_data->print_keywords_for_textarea( $items );
		// phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentAfterEnd
	?></textarea>
	<?php
}
$values = array_values(
	array_map(
		function( $row ) {
			return [ $row['query'], $row['pos'], $row['clicks'], $row['impr'], '' ];
		},
		$items
	)
);
?>
<script type="text/javascript">
	var ahrefs_positions = <?php echo wp_json_encode( $values ); ?>;
	content.positions_table_init( <?php echo wp_json_encode( "#$table_id" ); ?>, ahrefs_positions, <?php echo $show_footer ? 1 : 0; ?> );
</script>
