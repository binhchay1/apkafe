<?php

namespace ahrefs\AhrefsSeo;

/**
 * Internal links.
 *
 * @since 0.10.2
 */
class Links {

	/**
	 * Get link to Content Audit screen
	 *
	 * @param string|null $tab Content audit table tab.
	 * @param string|null $cat Selected category value.
	 *
	 * @return string
	 */
	public static function content_audit( $tab = null, $cat = null ) {
		return add_query_arg(
			[
				'page' => Ahrefs_Seo::SLUG_CONTENT,
				'tab'  => $tab,
				'cat'  => $cat,
			],
			admin_url( 'admin.php' )
		);
	}
	/**
	 * Get link for Wizard step
	 *
	 * @param int|null $step Wizard step.
	 *
	 * @return string
	 */
	public static function wizard_step( $step = null ) {
		return add_query_arg(
			[
				'page' => Ahrefs_Seo::SLUG,
				'step' => $step,
			],
			admin_url( 'admin.php' )
		);
	}
	/**
	 * Get link to Settings tab
	 *
	 * @param string      $tab Tab of settings page.
	 * @param string|null $return_to Return back URL.
	 *
	 * @return string
	 */
	public static function settings( $tab, $return_to = null ) {
		return add_query_arg(
			[
				'page'   => Ahrefs_Seo::SLUG_SETTINGS,
				'tab'    => $tab,
				'return' => ! is_null( $return_to ) ? rawurlencode( $return_to ) : null,
			],
			admin_url( 'admin.php' )
		);
	}
	/**
	 * Link to the Scope Settings with return to Content audit page.
	 *
	 * @return string
	 */
	public static function settings_scope_with_return() {
		return self::settings( Ahrefs_Seo_Screen_Settings::TAB_CONTENT, self::content_audit() );
	}
	/**
	 * Link to the Schedule Settings with return to Content audit page.
	 *
	 * @return string
	 */
	public static function settings_schedule_with_prefill_and_return() {
		return add_query_arg( [ 'prefill-monthly' => 'true' ], self::settings( Ahrefs_Seo_Screen_Settings::TAB_SCHEDULE, self::content_audit() ) );
	}
}