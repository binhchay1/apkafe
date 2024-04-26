<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$content = new Ahrefs_Seo_Content_Settings();

$waiting_value = $content->get_waiting_value();
$waiting_units = $content->get_waiting_units();
$waiting_text  = $content->get_waiting_as_text();

$month_selected = Ahrefs_Seo_Content::WAITING_UNIT_MONTH === $waiting_units;
$options        = [
	[
		'id'       => Ahrefs_Seo_Content::WAITING_UNIT_WEEK,
		'title'    => __( 'weeks', 'ahrefs-seo' ),
		'selected' => ! $month_selected,
	],
	[
		'id'       => Ahrefs_Seo_Content::WAITING_UNIT_MONTH,
		'title'    => __( 'months', 'ahrefs-seo' ),
		'selected' => $month_selected,
	],
];
?>
<div class="block-title"><?php esc_html_e( 'Waiting time after publication or update', 'ahrefs-seo' ); ?></div>
<div class="block-text">
	<?php
	printf(
		/* translators: %s and %s: time for waiting, like "3 months" or "12 weeks" */
		esc_html__( 'Any page that was published or updated below this threshold will be excluded from the analysis. For example, if you set a value of %1$s here and your page was published less than %2$s ago it will be excluded from this analysis.', 'ahrefs-seo' ),
		esc_html( $waiting_text ),
		esc_html( $waiting_text )
	);
	$max = $month_selected ? 12 : 48;
	?>
	<div class="waiting-options">
		<input id="waiting_value" type="number" min="1" max="<?php echo esc_attr( "$max" ); ?>" name="waiting_value" value="<?php echo esc_attr( "$waiting_value" ); ?>" class="wrapped-input"><!--
		--><select name="waiting_units" class="waiting-units">
			<?php
			foreach ( $options as $option ) {
				?>
				<option value="<?php echo esc_attr( $option['id'] ); ?>"<?php selected( $option['selected'] ); ?>><?php echo esc_html( $option['title'] ); ?></option>
				<?php
			}
			?>
		</select><!--
	--></div>
</div>
<hr class="hr-shadow">
<?php
