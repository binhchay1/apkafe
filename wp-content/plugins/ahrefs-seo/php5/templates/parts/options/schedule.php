<?php

namespace ahrefs\AhrefsSeo;

$locals           = Ahrefs_Seo_View::get_template_variables();
$content          = new Ahrefs_Seo_Content_Settings();
$content_schedule = new Content_Schedule();
$enabled          = $content_schedule->is_enabled();
$options          = $content_schedule->get_options();
$frequency        = (string) $options['frequency'];
$day_of_week      = (int) $options['day_of_week'];
$day_of_month     = (int) $options['day_of_month'];
$hour             = (int) $options['hour'];
$timezone         = (string) $options['timezone'];
if ( ! $enabled && isset( $_GET['prefill-monthly'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- prefill value.
	$frequency = 'ahrefs_monthly';
}
if ( ! function_exists( 'ahrefs\\AhrefsSeo\\render_options' ) ) {
	/**
	 * Render HTML options
	 *
	 * @param string                   $current Selected item.
	 * @param array<string|int,string> $items Items to show as [value => title].
	 * @return void
	 */
	function render_options( $current, array $items ) {
		foreach ( $items as $key => $title ) {
			?><option value="<?php echo esc_attr( "{$key}" ); ?>"
			<?php
			selected( $current, $key );
			?>
			>
			<?php
			echo esc_html( $title );
			?>
			</option>
			<?php
		}
	}
}
?>
<div class="block-text">
	<?php
	esc_html_e( 'Regularly check your website’s content to have all pages’ metrics, rankings and content suggestions updated.', 'ahrefs-seo' );
	?>
	<br>
</div>
<div class="block-schedule-checkbox">
	<label><input type="checkbox" name="schedule_enabled" value="1"
	<?php
	checked( $enabled );
	?>
	>
<?php
esc_html_e( 'Run scheduled content audits', 'ahrefs-seo' );
?>
</label>
	<input type="hidden" id="schedule_content_audits" value="1">
</div>
<div class="block-schedule-time">
	<div>
		<select name="schedule_frequency" id="schedule_frequency">
			<?php
// same keys as Cron_Scheduled_Audit::cron_schedules_add_interval_new(), Content_Schedule::get_options() used.
			$values = [
				'ahrefs_daily'   => __( 'Daily', 'ahrefs-seo' ),
				'ahrefs_weekly'  => __( 'Weekly', 'ahrefs-seo' ),
				'ahrefs_monthly' => __( 'Monthly', 'ahrefs-seo' ),
			];
			render_options( $frequency, $values );
			?>
		</select>
	</div>
	<div id="schedule_day_wrap" style="display: none;">
		<span id="schedule_each" style="display: none;">
		<?php
		echo esc_html_x( 'each', 'Start of "each Sunday" sentence', 'ahrefs-seo' );
		?>
		</span>
		<select name="schedule_day_of_week" id="schedule_day_of_week" style="display: none;">
			<?php
// week_begins = 0 stands for Sunday.
			$week_begins = (int) get_option( 'start_of_week' );
			$locale      = new \WP_Locale();
			$values      = [];
			for ( $i = 0; $i < 7; $i++ ) { // fill with translated Sunday - Saturday values.
				$k            = ( $i + $week_begins ) % 7;
				$values[ $k ] = $locale->get_weekday( $k );
			}
			render_options( "{$day_of_week}", $values );
			?>
		</select>
		<span class="schedule_every">
		<?php
		echo esc_html_x( 'every', 'Start of "every 31st of the month" sentence', 'ahrefs-seo' );
		?>
		</span>
		<select name="schedule_day_of_month" id="schedule_day_of_month" style="display: none;">
			<?php
			$values = [];
			for ( $i = 1; $i <= 31; $i++ ) {
				// use 1st, 2nd, 3rd... for English, 1, 2, 3... for other locales.
				$values[ $i ] = 0 === stripos( get_user_locale(), 'en' ) ? date( 'jS', (int) mktime( 0, 0, 0, 1, $i ) ) : "{$i}";
			}
			render_options( "{$day_of_month}", $values );
			?>
		</select>
		<span class="schedule_every">
		<?php
		echo esc_html_x( 'of the month', 'End of "every 31st of the month" sentence', 'ahrefs-seo' );
		?>
		</span>
	</div>
	<div>
		<span>
		<?php
		echo esc_html_x( 'at', 'Start of "at 08:00 AM" sentence', 'ahrefs-seo' );
		?>
		</span>
		<select name="schedule_hour" class="margin-right">
			<?php
			$values = [];
			for ( $i = 0; $i <= 23; $i++ ) {
				// use same time format as WordPress used.
				$values[ $i ] = date( __( 'g:i a' ), (int) mktime( $i, 0, 0 ) ); // phpcs:ignore WordPress.WP.I18n.MissingArgDomain -- do not use text domain parameter.
			}
			render_options( "{$hour}", $values );
			?>
		</select>
		<select name="schedule_timezone" class="schedule_timezone">
			<?php
			echo $content_schedule->filter_timezone_values( wp_timezone_choice( $timezone ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this is html with option tags.
			?>
		</select>
	</div>
</div>
<!-- next audit in -->
<div>
	<?php
	$next     = $content_schedule->next_run_time();
	$time_now = time();
	if ( $enabled ) {
		?>
		<p>
			<?php
		/* translators: %s: human-readable interval, like "2 days" */
			printf( esc_html__( 'The next content audit run is scheduled in: %s.', 'ahrefs-seo' ), esc_html( human_time_diff( $time_now, $next['time'] ) ) );
			?>
		</p>
		<?php
	}
	?>
</div>
<?php
if ( ! $enabled && isset( $_GET['prefill-monthly'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- blink with input if not enabled already.
	?>
	<script type="text/javascript">
		jQuery( function() {
			jQuery('.block-schedule-checkbox label, .block-schedule-checkbox input').removeClass( 'item-flash' ).addClass( 'long' );
			setTimeout( function() { jQuery('.block-schedule-checkbox label, .block-schedule-checkbox input').addClass( 'item-flash' ); }, 100 );
		});
	</script>
	<?php
}