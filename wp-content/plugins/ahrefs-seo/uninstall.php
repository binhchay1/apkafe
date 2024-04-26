<?php
/**
 * Uninstall Ahrefs SEO
 *
 * If "Remove my audit data when deleting the plugin" option is set (it set by default)
 * Then remove:
 * - Database tables with content audit data.
 * - Plugin options.
 *
 * @since 0.9.7
 */

namespace ahrefs\AhrefsSeo;

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
/**
 * Check save data option and uninstall all the plugin data.
 *
 * @return void
 */
function ahrefs_seo_uninstall() {
	global $wpdb;
	// no loader, load manually.
	require_once __DIR__ . '/' . ( version_compare( PHP_VERSION, '7.1.0' ) >= 0 ? 'php7' : 'php5' ) . '/class-ahrefs-seo-uninstall.php';

	$uninstall = new Ahrefs_Seo_Uninstall();
	if ( ! is_multisite() ) {
		if ( ! $uninstall->get_option_save_data() ) { // no need to save?
			$uninstall->clean_all_data(); // then remove all the data.
		}
	} else { // uninstall everywhere but respect the "save data on uninstall" option of each site.
		$blog_ids = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = %d AND archived = '0' AND spam = '0' AND deleted = '0'", $wpdb->siteid ) );
		if ( ! empty( $blog_ids ) ) {
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( ! $uninstall->get_option_save_data() ) {
					$uninstall->clean_all_data();
				}
				restore_current_blog();
			}
		}
	}
}
ahrefs_seo_uninstall();
