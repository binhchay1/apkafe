<?php

/**
 * Activation / de-activation class compatible with multisite
 * Based on PLL_Install_Base
 *
 * @since 0.1
 */
class PLLWC_Install {
	protected $plugin_basename;

	/**
	 * Constructor
	 *
	 * @since 0.1
	 *
	 * @param string $plugin_basename Plugin basename.
	 */
	public function __construct( $plugin_basename ) {
		$this->plugin_basename = $plugin_basename;

		// Manages plugin activation and deactivation
		register_activation_hook( $plugin_basename, array( $this, 'activate' ) );
		register_deactivation_hook( $plugin_basename, array( $this, 'deactivate' ) );

		// Blog creation on multisite
		add_action( 'wpmu_new_blog', array( $this, 'wpmu_new_blog' ), 5 ); // Before WP attempts to send mails which can break on some PHP versions
	}

	/**
	 * Allows to detect plugin deactivation
	 *
	 * @since 0.1
	 *
	 * @return bool True if the plugin is currently beeing deactivated.
	 */
	public function is_deactivation() {
		return isset( $_GET['action'], $_GET['plugin'] ) && 'deactivate' === $_GET['action'] && $this->plugin_basename === $_GET['plugin'];  // phpcs:ignore WordPress.Security.NonceVerification
	}

	/**
	 * Activation or deactivation for all blogs
	 *
	 * @since 0.1
	 *
	 * @param string $what        Either 'activate' or 'deactivate'.
	 * @param bool   $networkwide True if the plugin is network activated, false otherwise.
	 */
	protected function do_for_all_blogs( $what, $networkwide ) {
		// Network
		if ( is_multisite() && $networkwide ) {
			global $wpdb;

			foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ) as $blog_id ) {
				switch_to_blog( $blog_id );
				'activate' === $what ? $this->_activate() : $this->_deactivate();
			}
			restore_current_blog();
		}

		// Single blog
		else {
			'activate' === $what ? $this->_activate() : $this->_deactivate();
		}
	}

	/**
	 * Plugin activation for multisite
	 *
	 * @since 0.1
	 *
	 * @param bool $networkwide True if the plugin is network activated, false otherwise.
	 */
	public function activate( $networkwide ) {
		$this->do_for_all_blogs( 'activate', $networkwide );
	}

	/**
	 * Plugin activation
	 *
	 * @since 0.1
	 */
	protected function _activate() {
		delete_option( 'rewrite_rules' );
	}

	/**
	 * Plugin deactivation for multisite
	 *
	 * @since 0.1
	 *
	 * @param bool $networkwide True if the plugin is network activated, false otherwise.
	 */
	public function deactivate( $networkwide ) {
		$this->do_for_all_blogs( 'deactivate', $networkwide );
	}

	/**
	 * Plugin deactivation
	 *
	 * @since 0.1
	 */
	protected function _deactivate() {
		delete_option( 'rewrite_rules' );
	}

	/**
	 * Blog creation on multisite ( to set default options )
	 *
	 * @since 0.9.4
	 *
	 * @param int $blog_id Blog ID.
	 */
	public function wpmu_new_blog( $blog_id ) {
		switch_to_blog( $blog_id );
		$this->_activate();
		restore_current_blog();
	}
}
