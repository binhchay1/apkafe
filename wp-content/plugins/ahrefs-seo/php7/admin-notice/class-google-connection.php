<?php
declare( strict_types=1 );

namespace ahrefs\AhrefsSeo\Admin_Notice;

use ahrefs\AhrefsSeo\Ahrefs_Seo;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Analytics;

/**
 * Show "reconnect google" tip if required.
 *
 * @since 0.9.11
 */
class Google_Connection {
	protected const OPTION_NEED_GOOGLE_RECONNECT = 'ahrefs-seo-notice-need-google-reconnect';

	/**
	 * Show the message if account should be reconnected
	 *
	 * @return void
	 */
	public function maybe_show() : void {
		if ( $this->is_using_old_google_connection() ) {
			Ahrefs_Seo::get()->get_view()->show_part( 'notices/reconnect-google' );
		}
	}

	/**
	 * Need to reconnect Google account
	 *
	 * @return bool
	 */
	public function is_using_old_google_connection() : bool {
		$need_to_show = get_option( self::OPTION_NEED_GOOGLE_RECONNECT, null );
		if ( ! is_string( $need_to_show ) ) {
			$need_to_show = Ahrefs_Seo_Analytics::get()->get_data_tokens()->is_using_direct_connection();
			update_option( self::OPTION_NEED_GOOGLE_RECONNECT, $need_to_show ? '1' : '0' );
		}

		return apply_filters( 'ahrefs_seo_reconnect_google', '1' === $need_to_show );
	}

	/**
	 * Reset an update option
	 *
	 * @return void
	 */
	public function reset() : void {
		delete_option( self::OPTION_NEED_GOOGLE_RECONNECT );
	}
}
