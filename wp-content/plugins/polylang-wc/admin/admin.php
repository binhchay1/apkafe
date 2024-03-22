<?php

/**
 * Helper functions used on admin
 *
 * @since 0.6
 */
class PLLWC_Admin {

	/**
	 * Get the preferred language for filters
	 *
	 * @since 0.1
	 *
	 * @return string language slug
	 */
	public static function get_preferred_language() {
		// We rely on the admin language filter
		if ( ! empty( PLL()->curlang ) ) {
			return PLL()->curlang->slug;
		}

		// Or the current locale ( admin language )
		if ( $curlang = PLL()->model->get_language( version_compare( $GLOBALS['wp_version'], '4.7', '<' ) ? get_locale() : get_user_locale() ) ) {
			return $curlang->slug;
		}

		// Or the default language
		return pll_default_language();
	}
}
