<?php
/**
 * Fired during plugin updates
 *
 * @link       https://shapedplugin.com/
 * @since      2.0.6
 *
 * @package    Easy_Accordion_Free
 * @subpackage Easy_Accordion_Free/includes
 */

// don't call the file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fired during plugin updates.
 *
 * This class defines all code necessary to run during the plugin's updates.
 */
class Easy_Accordion_Free_Updates {

	/**
	 * DB updates that need to be run
	 *
	 * @var array
	 */
	private static $updates = array(
		'2.0.6'  => 'updates/update-2.0.6.php',
		'2.0.7'  => 'updates/update-2.0.7.php',
		'2.1.14' => 'updates/update-2.1.14.php',
		'2.3.0'  => 'updates/update-2.3.0.php',
	);

	/**
	 * Binding all events
	 *
	 * @since 2.0.6
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'do_updates' ) );
	}

	/**
	 * Check if need any update
	 *
	 * @since 2.0.6
	 *
	 * @return boolean
	 */
	public function is_needs_update() {
		$installed_version = get_option( 'easy_accordion_free_version' );
		$first_version     = get_option( 'easy_accordion_free_first_version' );
		$activation_date   = get_option( 'easy_accordion_free_activation_date' );

		if ( false === $installed_version ) {
			update_option( 'easy_accordion_free_version', SP_EA_VERSION );
			update_option( 'easy_accordion_free_db_version', SP_EA_VERSION );
		}
		if ( false === $first_version ) {
			update_option( 'easy_accordion_free_first_version', SP_EA_VERSION );
		}
		if ( false === $activation_date ) {
			update_option( 'easy_accordion_free_activation_date', current_time( 'timestamp' ) );
		}

		if ( version_compare( $installed_version, SP_EA_VERSION, '<' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Do updates.
	 *
	 * @since 2.0.6
	 *
	 * @return void
	 */
	public function do_updates() {
		$this->perform_updates();
	}

	/**
	 * Perform all updates
	 *
	 * @since 2.0.6
	 *
	 * @return void
	 */
	public function perform_updates() {
		if ( ! $this->is_needs_update() ) {
			return;
		}

		$installed_version = get_option( 'easy_accordion_free_version' );

		foreach ( self::$updates as $version => $path ) {
			if ( version_compare( $installed_version, $version, '<' ) ) {
				include $path;
				update_option( 'easy_accordion_free_version', $version );
			}
		}

		update_option( 'easy_accordion_free_version', SP_EA_VERSION );

	}

}
new Easy_Accordion_Free_Updates();
