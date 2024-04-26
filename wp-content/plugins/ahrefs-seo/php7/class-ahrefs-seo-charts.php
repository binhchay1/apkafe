<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

/**
 * Class for charts.
 */
class Ahrefs_Seo_Charts {

	const CHART_WELL_PERFORMING = 1;
	const CHART_UNDERPERFORMING = 2;
	const CHART_NON_PERFORMING  = 3;
	const CHART_EXCLUDED        = 4;

	/**
	 * Show content for Content Audit (right) chart
	 *
	 * @return void
	 */
	public static function print_svg_donut_chart() : void {
		Ahrefs_Seo::get()->get_view()->show_part( 'charts/donut-chart' );
	}

	/**
	 * Show legend for the Content Audit (right) chart
	 *
	 * @return void
	 */
	public static function print_svg_donut_chart_legend() : void {
		Ahrefs_Seo::get()->get_view()->show_part( 'charts/donut-legend' );
	}

	/**
	 * Show content for Content Audit score (left) chart
	 *
	 * @return void
	 */
	public static function print_content_score_block() : void {
		Ahrefs_Seo::get()->get_view()->show_part( 'charts/score-block' );
	}

	/**
	 * Return charts if requested and they are different from existing charts at the page.
	 *
	 * @global null|int    $_REQUEST['chart_score'] Current chart score from the frontend.
	 * @global null|string $_REQUEST['chart_pie'] Current donut chart statuses as string.
	 *
	 * @return array<string, string> Associative array with charts and legend or empty array if nothing updated.
	 */
	public static function maybe_return_charts() : array {
		$charts = [];
		// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification,WordPress.VIP.SuperGlobalInputUsage.AccessDetected,WordPress.Security.NonceVerification.Recommended -- we already checked nonce.
		// maybe update charts.
		if ( isset( $_REQUEST['chart_score'] ) || isset( $_REQUEST['charts_pie'] ) ) {
			$chart_score_prev = isset( $_REQUEST['chart_score'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['chart_score'] ) ) : '';
			$chart_pie_prev   = isset( $_REQUEST['chart_pie'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['chart_pie'] ) ) : '';
			// phpcs:enable WordPress.CSRF.NonceVerification.NoNonceVerification,WordPress.VIP.SuperGlobalInputUsage.AccessDetected,WordPress.Security.NonceVerification.Recommended
			$count = Ahrefs_Seo_Data_Content::get_statuses_for_charts();
			$total = $count[ self::CHART_WELL_PERFORMING ] + $count[ self::CHART_UNDERPERFORMING ] + $count[ self::CHART_NON_PERFORMING ];
			// leave as is / total.
			$score = $count[1] . '|' . $total;

			if ( implode( '-', $count ) !== $chart_pie_prev ) {
				ob_start();
				self::print_svg_donut_chart();
				$charts['right'] = (string) ob_get_clean();

				ob_start();
				self::print_svg_donut_chart_legend();
				$charts['right_legend'] = (string) ob_get_clean();
			}
			if ( $chart_score_prev !== $score ) {
				ob_start();
				self::print_content_score_block();
				$charts['left'] = (string) ob_get_clean();
			}
		}
		return $charts;
	}

	/**
	 * Get colors for actions at the donut chart
	 *
	 * @return array<int, string> Associative array [chart action => string color].
	 */
	public static function get_colors() : array {
		return [
			self::CHART_WELL_PERFORMING => '#65bd63',
			self::CHART_UNDERPERFORMING => '#fee08b',
			self::CHART_NON_PERFORMING  => '#f86c3b',
			self::CHART_EXCLUDED        => '#d9d9d9',

		];
	}

	/**
	 * Get tab name for actions at the donut chart
	 *
	 * @return array<int, string> Associative array [chart action => string tab name].
	 */
	public static function get_tabs() : array {
		return [
			self::CHART_WELL_PERFORMING => 'well-performing',
			self::CHART_UNDERPERFORMING => 'under-performing',
			self::CHART_NON_PERFORMING  => 'non-performing',
			self::CHART_EXCLUDED        => 'excluded',

		];
	}

	/**
	 * Get order for actions at the donut chart
	 *
	 * @return int[] Chart actions order.
	 */
	public static function get_order() : array {
		return [
			self::CHART_WELL_PERFORMING,
			self::CHART_UNDERPERFORMING,
			self::CHART_NON_PERFORMING,
			self::CHART_EXCLUDED,
		];
	}

	/**
	 * Get title for chart action
	 *
	 * @param int $chart_action Chart action code, one of Ahrefs_Seo_Charts::CHART_*.
	 * @return string
	 */
	public static function get_title( int $chart_action ) : string {
		switch ( $chart_action ) {
			case self::CHART_WELL_PERFORMING:
				return _x( 'Well-performing', 'Title of tab and chart legend', 'ahrefs-seo' );
			case self::CHART_UNDERPERFORMING:
				return _x( 'Under-performing', 'Title of tab and chart legend', 'ahrefs-seo' );
			case self::CHART_NON_PERFORMING:
				return _x( 'Non-performing', 'Title of tab and chart legend', 'ahrefs-seo' );
			case self::CHART_EXCLUDED:
				return _x( 'Excluded', 'Title of tab and chart legend', 'ahrefs-seo' );
		}
		return '';
	}

}
