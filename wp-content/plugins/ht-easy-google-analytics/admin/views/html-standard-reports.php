<div class="htga4 htga4_standard_reports">
	<?php
	$does_not_have_proper_api_info = ( ! $this->get_option( 'account' ) || ! $this->get_option( 'property' ) || ! $this->get_option( 'data_stream_id' ) );

	// @todo improve the notice.
	if ( $this->analytics_data_permission === false ) {
		$this->render_login_notice( 'insufficient_permission' );
		return;
	}

	if ( get_option( 'htga4_email' ) && $does_not_have_proper_api_info ) {
		echo '<div class="htga4-notice notice-warning"><p>' . esc_html__( 'Select the Account, Property and Measurement ID to display the reports from "General Options" tab.', 'ht-easy-ga4' ) . '</p></div>';
		return;
	} elseif ( ! get_option( 'htga4_email' ) && $does_not_have_proper_api_info ) {
		echo '<div class="htga4-notice notice-warning"><p>' . esc_html__( 'Sign in with your Google Analytics account to view the reports!', 'ht-easy-ga4' ) . '</p></div>';
		return;
	}

	$reports = $this->get_all_reports_data_standard_prepared(); // returns parepared data.

	if ( ! empty( $reports['error'] ) && $reports['error']['message'] ) {
		printf(
			// translators: %s: error message.
			'<div class="htga4-notice notice-warning"><p>%s</p></div>',
			esc_html( implode( ' | ', $reports['error'] ) )
		);
		return;
	}

	require HT_EASY_GA4_PATH . 'admin/views/html-reports-head.php';
	?>
	<div class="ht_easy_ga4_reports_body">
		<div class="ht_easy_ga4_reoport_card_wrapper ht_easy_ga4_reoport_card_wrapper_column-3">
			<div class="ht_easy_ga4_report_card ht_session">
				<h2 class="ht_easy_ga4_report_card_title">
					<i class="dashicons dashicons-admin-users"></i>
					<span class="ht_easy_ga4_report_card_title"><?php echo esc_html__( 'Sessions', 'ht-easy-ga4' ); ?></span>
				</h2>
				<div class="ht_easy_ga4_report_card_head">
					<?php $this->render_growth( $reports['sessions']['previous_total'], $reports['sessions']['current_total'] ); ?>
				</div>
				<div class="ht_easy_ga4_report_card_chart">
					<canvas id="sessions-chart" class="ht_easy_ga4_line_chart" data-report='<?php echo wp_json_encode( $reports['sessions'] ); ?>'></canvas>
				</div>
			</div>
			<div class="ht_easy_ga4_report_card ht_pageview">
				<h2 class="ht_easy_ga4_report_card_title">
					<i class="dashicons dashicons-visibility"></i>
					<span class="ht_easy_ga4_report_card_title"><?php echo esc_html__( 'Page View', 'ht-easy-ga4' ); ?></span>
				</h2>
				<div class="ht_easy_ga4_report_card_head">
					<?php $this->render_growth( $reports['page_views']['previous_total'], $reports['page_views']['current_total'] ); ?>
				</div>
				<div class="ht_easy_ga4_report_card_chart">
					<canvas id="page-view-chart" class="ht_easy_ga4_line_chart" data-report='<?php echo wp_json_encode( $reports['page_views'] ); ?>'></canvas>
				</div>
			</div>
			<div class="ht_easy_ga4_report_card ht_bounce_rate">
				<h2 class="ht_easy_ga4_report_card_title">
					<i class="dashicons dashicons-arrow-down-alt"></i>
					<span class="ht_easy_ga4_report_card_title"><?php echo esc_html__( 'Bounce Rate', 'ht-easy-ga4' ); ?></span>
				</h2>
				<div class="ht_easy_ga4_report_card_head">
					<?php $this->render_growth( $this->calculate_bounce_rate( $reports['bounce_rate']['previous_dataset'] ), $this->calculate_bounce_rate( $reports['bounce_rate']['current_dataset'] ) ); ?>
				</div>
				<div class="ht_easy_ga4_report_card_chart">
					<canvas id="page-view-chart2"  class="ht_easy_ga4_line_chart" data-report='<?php echo wp_json_encode( $reports['bounce_rate'] ); ?>'></canvas>
				</div>
			</div>
		</div>

		<div class="ht_easy_ga4_reoport_card_wrapper ht_easy_ga4_reoport_card_wrapper_column-3">
			<div class="ht_easy_ga4_report_card ht_top_pages">
				<h2 class="ht_easy_ga4_report_card_title">
					<i class="dashicons dashicons-text-page"></i>
					<span class="ht_easy_ga4_report_card_title"><?php echo esc_html__( 'Top Pages', 'ht-easy-ga4' ); ?></span>
				</h2>
				<div class="ht_easy_ga4_report_card_list">
					<ul>
						<li>
							<span><b><?php echo esc_html__( 'Page Path', 'ht-easy-ga4' ); ?></b></span>
							<span><b><?php echo esc_html__( 'Page View', 'ht-easy-ga4' ); ?></b></span>
						</li>

						<?php foreach ( $reports['top_pages'] as $item ) : ?>
						<li>
							<span><?php echo esc_html( $item[1] ); ?></span>
							<span><?php echo esc_html( $item[0] ); ?></span>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>

			<div class="ht_easy_ga4_report_card ht_top_referrers">
				<h2 class="ht_easy_ga4_report_card_title">
					<i class="dashicons dashicons-controls-forward"></i>
					<span class="ht_easy_ga4_report_card_title"><?php echo esc_html__( 'Top Referrers', 'ht-easy-ga4' ); ?></span>
				</h2>
				<div class="ht_easy_ga4_report_card_list">
					<ul>
						<li>
							<span><b><?php echo esc_html__( 'Referrer', 'ht-easy-ga4' ); ?></b></span>
							<span><b><?php echo esc_html__( 'Session', 'ht-easy-ga4' ); ?></b></span>
						</li>
						<?php foreach ( $reports['top_referrers'] as $item ) : ?>
						<li>
							<span><?php echo esc_html( $item[1] ); ?></span>
							<span><?php echo esc_html( $item[0] ); ?></span>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>

			<div class="ht_easy_ga4_report_card ht_top_countries">
				<h2 class="ht_easy_ga4_report_card_title">
					<i class="dashicons dashicons-controls-forward"></i>
					<span class="ht_easy_ga4_report_card_title"><?php echo esc_html__( 'Top country’s', 'ht-easy-ga4' ); ?></span>
				</h2>
				<div class="ht_easy_ga4_report_card_list">
					<ul>
						<li>
							<span><b><?php echo esc_html__( 'Country', 'ht-easy-ga4' ); ?></b></span>
							<span><b><?php echo esc_html__( 'Session', 'ht-easy-ga4' ); ?></b></span>
						</li>
						<?php foreach ( $reports['top_countries'] as $item ) : ?>
						<li>
							<span><?php echo esc_html( $item[1] ); ?></span>
							<span><?php echo esc_html( $item[0] ); ?></span>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>

		<div class="ht_easy_ga4_reoport_card_wrapper ht_easy_ga4_reoport_card_wrapper_column-3">
			<div class="ht_easy_ga4_report_card ht_user_type">
				<h2 class="ht_easy_ga4_report_card_title">
					<i class="dashicons dashicons-admin-users"></i>
					<span class="ht_easy_ga4_report_card_title"><?php echo esc_html__( 'User Types', 'ht-easy-ga4' ); ?></span>
				</h2>
				<div class="ht_easy_ga4_report_card_chart">
					<canvas id="user-types-chart" class="ht_easy_ga4_pie_chart" data-report='<?php echo wp_json_encode( $reports['user_types'] ); ?>'></canvas>
				</div>
			</div>

			<div class="ht_easy_ga4_report_card ht_device_type">
				<h2 class="ht_easy_ga4_report_card_title">
					<i class="dashicons dashicons-welcome-view-site"></i>
					<span class="ht_easy_ga4_report_card_title"><?php echo esc_html__( 'Device Types', 'ht-easy-ga4' ); ?></span>
				</h2>
				<div class="ht_easy_ga4_report_card_chart">
					<canvas id="device-types-chart" class="ht_easy_ga4_pie_chart" data-report='<?php echo wp_json_encode( $reports['device_types'] ); ?>'></canvas>
				</div>
			</div>					
		</div>
	</div><!-- .ht_easy_ga4_reports_body -->
</div>
