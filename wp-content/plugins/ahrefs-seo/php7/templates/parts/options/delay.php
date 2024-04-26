<?php
/**
 * Settings Delay part template
 *
 * @var bool $updated
 */

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$cron_content = new Cron_Content_Fast();
$delay        = $cron_content->get_recurrence_time();

if ( ! function_exists( 'ahrefs\\AhrefsSeo\\render_options' ) ) {
	/**
	 * Render HTML options
	 *
	 * @param string                   $current Selected item.
	 * @param array<string|int,string> $items Items to show as [value => title].
	 * @return void
	 */
	function render_options( string $current, array $items ) : void {
		foreach ( $items as $key => $title ) {
			?><option value="<?php echo esc_attr( "$key" ); ?>"<?php selected( $current, $key ); ?>><?php echo esc_html( $title ); ?></option>
			<?php
		}
	}
}
?>
<div class="block-title"><?php esc_html_e( 'Delay between requests', 'ahrefs-seo' ); ?></div>
<div class="block-text">
	<?php esc_html_e( 'The speed of scheduled audits depends on the delay between subsequent requests. To speed up your audit, reduce the delay time. Note, that the lower the delay time, the more likely it is to cause a significant load on your web server and slow response times for your visitors. Please consider changing the default settings carefully. ', 'ahrefs-seo' ); ?>
	<a href="https://help.ahrefs.com/en/articles/5879793-what-does-the-speed-of-the-audit-depend-on" class="no-underline"><?php esc_html_e( 'Learn more', 'ahrefs-seo' ); ?></a>
</div>
<?php /** @psalm-suppress TypeDoesNotContainType */  if ( ! defined( 'DISABLE_WP_CRON' ) || ! DISABLE_WP_CRON ) { ?>
	<div class="notice notice-warning">
		<p class="block-title">
			<?php esc_html_e( 'Warning', 'ahrefs-seo' ); ?>
		</p>
		<p class="block-text">
			<?php esc_html_e( 'We noticed that you have WP-Cron enabled on your website. The Ahrefs SEO plugin uses cron jobs to run audits in background, and with WP-Cron the speed you selected is not guaranteed. We recommend switching from WP-Cron to a system cron instead. ', 'ahrefs-seo' ); ?>
			<a href="https://help.ahrefs.com/en/articles/5879793-what-does-the-speed-of-the-audit-depend-on" class="no-underline"><?php esc_html_e( 'Learn how to do this', 'ahrefs-seo' ); ?></a>
		</p>
	</div>
<?php } ?>
<div class="block-delay-time">
	<div>
		<select name="audit_cron_delay" id="audit_cron_delay">
			<?php
			$values = [
				60  => __( '1 minute', 'ahrefs-seo' ),
				90  => __( '1.5 minutes', 'ahrefs-seo' ),
				120 => __( '2 minutes', 'ahrefs-seo' ),
				150 => __( '2.5 minutes', 'ahrefs-seo' ),
				180 => __( '3 minutes', 'ahrefs-seo' ),
				240 => __( '4 minutes', 'ahrefs-seo' ),
				300 => __( '5 minutes', 'ahrefs-seo' ),
				600 => __( '10 minutes', 'ahrefs-seo' ),
			];
			if ( ! isset( $values[ $delay ] ) ) {
				/* Translators: %d: number of seconds. */
				$values[ $delay ] = sprintf( __( '%d seconds', 'ahrefs-seo' ), $delay );
			}
			render_options( "$delay", $values );
			?>
		</select><a id="audit_cron_delay_reset" style="display: none;" href="#"><?php esc_html_e( 'Reset to default', 'ahrefs-seo' ); ?></a>
		<script type="text/javascript">
			(function($){
				$('#audit_cron_delay').on('change', function() {
					if ( '180' !== $(this).val() ) {
						$('#audit_cron_delay_reset').show();
					} else {
						$('#audit_cron_delay_reset').hide();
					}
				})
				$(function(){
					$('#audit_cron_delay').trigger('change');
					$('#audit_cron_delay_reset').on('click', function() {
						$('#audit_cron_delay').val('180').trigger('change');
					});
				})
			})(jQuery);
		</script>
	</div>
</div>
