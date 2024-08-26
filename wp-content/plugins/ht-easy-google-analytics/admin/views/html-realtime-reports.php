<div class="htga4 htga4_realtime_reports">
	<?php if ( ! $this->is_pro_plugin_active() ) : ?>
		<div class="">
			<div class="htga4-notice notice-warning">
				<p>
				<?php
					printf(
						/* translators: %s: Pro version link */
						__( 'Realtime reports are available in the <a href="%s" target="_blank">Pro version</a>.', 'ht-easy-ga4' ), // phpcs:ignore
						'https://hasthemes.com/plugins/google-analytics-plugin-for-wordpress?utm_source=wp-org&utm_medium=ht-ga4&utm_campaign=htga4_tab_ecommerce_reports'
					);
				?>
				</p>
			</div>
		</div>
		<?php
		endif;

	if ( $this->is_pro_plugin_active() && get_option( 'htga4_email' ) && ! $this->has_proper_request_data() ) {
		echo '<div class="htga4-notice notice-warning"><p>' . esc_html__( 'Select the Account, Property and Measurement ID to display the reports from "General Options" tab.', 'ht-easy-ga4' ) . '</p></div>';
		return;
	} elseif ( $this->is_pro_plugin_active() && ! $this->has_proper_request_data() ) {
		echo '<div class="htga4-notice notice-warning"><p>' . esc_html__( 'Sign in with your Google Analytics account to view the reports!', 'ht-easy-ga4' ) . '</p></div>';
		return;
	}

		do_action( 'htga4_realtime_reports_tab_content', $this );
	?>
</div>
