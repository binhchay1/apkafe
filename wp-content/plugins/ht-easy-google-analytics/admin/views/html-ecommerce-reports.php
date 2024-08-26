<div class="htga4 htga4_ecommerce_reports">
	<?php if ( ! $this->is_pro_plugin_active() ) : ?>
		<div class="">
			<div class="htga4-notice notice-warning" style="text-align: center;"> 
				<p>
					<?php
					printf(
						/* translators: %s: Pro version link */
						__( 'E-Commerce reports are available in the <a href="%s" target="_blank">Pro version</a>. <br> The reports displayed below with a blurred effect are just for demonstration purposes.', 'ht-easy-ga4' ), // phpcs:ignore
						esc_url( 'https://hasthemes.com/plugins/google-analytics-plugin-for-wordpress?utm_source=wp-org&utm_medium=ht-ga4&utm_campaign=htga4_tab_ecommerce_reports' )
					);
					?>
				</p>
			</div>
			<div class="htga4_no_pro">
				<img src="<?php echo esc_url( HT_EASY_GA4_URL . '/admin/assets/images/ecommerce-reports.jpeg' ); ?>" alt="">
			</div>
		</div>
	<?php endif; ?>

	<?php do_action( 'htga4_ecommerce_reports_tab_content', $this ); ?>
</div>
