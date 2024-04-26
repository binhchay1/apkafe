<?php
namespace ahrefs\AhrefsSeo;

/**
 * Clean options and tables on Ahrefs SEO uninstallation.
 * Called directly on uninstallation. Must not use any other code from the plugin.
 *
 * @since 0.9.7
 */
class Ahrefs_Seo_Uninstall {

	/**
	 * Save settings and audit data on plugin uninstall.
	 */
	private const OPTION_SAVE_DATA_ON_UNINSTALL = 'ahrefs-seo-save-data-on-uninstall';

	/**
	 * Set "save settings and audit data on plugin uninstall" or not.
	 *
	 * @since 0.9.7
	 *
	 * @param bool $save_data Save data.
	 * @return void
	 */
	public function set_option_save_data( bool $save_data ) : void {
		update_option( self::OPTION_SAVE_DATA_ON_UNINSTALL, $save_data );
	}

	/**
	 * Do we need to save data on uninstallation?
	 * Default false.
	 *
	 * @return bool
	 */
	public function get_option_save_data() : bool {
		return (bool) get_option( self::OPTION_SAVE_DATA_ON_UNINSTALL, false );
	}

	/**
	 * Clean data on uninstall.
	 * Called on uninstallation.
	 */
	public function clean_all_data() : void {
		$this->remove_options();
		$this->remove_transients();
		$this->remove_tables();
		// clear WP cache.
		wp_cache_flush();
	}

	/**
	 * Clean options
	 */
	private function remove_options() : void {
		global $wpdb;
		$items = [ 'ahrefs-seo' ];
		foreach ( $items as $item ) {
			$options = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT option_name AS name FROM $wpdb->options
					WHERE option_name LIKE %s",
					$wpdb->esc_like( "{$item}" ) . '%'
				)
			);
			foreach ( $options as $name ) {
				delete_option( $name );
			}
		}
	}

	/**
	 * Clean transients
	 */
	private function remove_transients() : void {
		global $wpdb;
		$items = [ 'ahrefs_seo', 'ahrefs-cron', 'ahrefs-content' ];
		foreach ( $items as $item ) {
			$transients = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT option_name AS name FROM $wpdb->options
					WHERE option_name LIKE %s",
					$wpdb->esc_like( "_transient_{$item}" ) . '%'
				)
			);
			foreach ( $transients as $transient ) {
				delete_transient( str_replace( '_transient_', '', $transient ) );
			}
		}
	}

	/**
	 * Remove tables
	 */
	private function remove_tables() : void {
		global $wpdb;
		$tables = [ 'ahrefs_seo_content', 'ahrefs_seo_snapshots', 'ahrefs_seo_keywords', 'ahrefs_seo_backlinks', 'ahrefs_seo_blacklist' ];
		foreach ( $tables as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$table}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- remove own predefined tables.
		}
	}
}
