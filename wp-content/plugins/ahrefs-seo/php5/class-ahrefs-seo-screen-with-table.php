<?php

namespace ahrefs\AhrefsSeo;

/**
 * Abstract class for Screen with Table.
 */
abstract class Ahrefs_Seo_Screen_With_Table extends Ahrefs_Seo_Screen {

	/**
	 * Table embedded to screen.
	 *
	 * @var Ahrefs_Seo_Table_Content|null
	 */
	protected $table;
	/**
	 * Set screen id of admin page for this screen.
	 * Register 'process_post_data' method as action.
	 * Initialize table.
	 *
	 * @param string $screen_id Current (WordPress') screen id.
	 */
	public function set_screen_id( $screen_id ) {
		parent::set_screen_id( $screen_id );
		// initialize table here because we want to have screen options.
		add_action( 'load-' . $screen_id, [ $this, 'initialize_table' ] ); // @phpstan-ignore-line -- returned result instead of void.
	}
	/**
	 * Register AJAX handlers
	 */
	public function register_ajax_handlers() {
		$this->register_table_handlers();
	}
	/**
	 * Get prefix for table.
	 *
	 * @return string
	 */
	protected abstract function get_ajax_table_prefix();
	/**
	 * Create new table
	 *
	 * @return Ahrefs_Seo_Table_Content
	 */
	protected abstract function new_table_instance();
	/**
	 * Return existing table or create new
	 *
	 * @return Ahrefs_Seo_Table
	 */
	public function initialize_table() {
		if ( is_null( $this->table ) ) {
			if ( ! isset( $GLOBALS['hook_suffix'] ) ) { // patch for notice when called using AJAX in wp-admin\includes\class-wp-screen.php .
				$GLOBALS['hook_suffix'] = ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}
			$this->table = $this->new_table_instance();
			$this->table->add_screen_options();
		}
		return $this->table;
	}
	/**
	 * Register ajax handlers for any screen with table.
	 * Actions for update table and initialize table.
	 */
	public function register_table_handlers() {
		$prefix = $this->get_ajax_table_prefix();
		add_action( "wp_ajax_{$prefix}_update", [ $this, 'ajax_table_update' ] ); // ahrefs_seo_table_content_update.
		add_action( "wp_ajax_{$prefix}_init", [ $this, 'ajax_table_init' ] ); // ahrefs_seo_table_content_init.
	}
	/**
	 * Print navigation and placeholder for future table
	 */
	public abstract function show_table_placeholder();
	/**
	 * Ajax handler, echo ajax: table parts
	 */
	public function ajax_table_update() {
		if ( check_ajax_referer( Ahrefs_Seo_Table::ACTION ) && current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_VIEW ) ) {
			if ( ! empty( $_REQUEST['screen_id'] ) && is_string( $_REQUEST['screen_id'] ) ) { // may be POST or GET.
				set_current_screen( sanitize_key( $_REQUEST['screen_id'] ) ); // required for loading table using ajax.
			}
			$this->initialize_table()->ajax_response();
		}
	}
	/**
	 * Action wp_ajax for fetching the table structure first time
	 */
	public function ajax_table_init() {
		if ( check_ajax_referer( Ahrefs_Seo_Table::ACTION ) && current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_VIEW ) ) {
			if ( ! empty( $_POST['screen_id'] ) && is_string( $_POST['screen_id'] ) ) {
				set_current_screen( sanitize_key( $_POST['screen_id'] ) ); // required for loading table using ajax.
			}
			$this->initialize_table()->prepare_items();
			ob_start();
			if ( ! is_null( $this->table ) ) {
				$this->table->display();
			}
			$display = ob_get_clean();
			wp_send_json_success( [ 'display' => $display ] );
		}
	}
	/**
	 * Callback. Required for saving per page options.
	 *
	 * @param bool|int|string $status   Whether to save or skip saving the screen option value.
	 * @param string          $option The option name.
	 * @param int             $value  The number of rows to use.
	 * @return bool|int|string
	 */
	public static function option_filter( $status, $option, $value ) {
		// callback, do not use parameter types.
		if ( 'ahrefs_seo_table_content_per_page' === $option ) {
			return intval( $value );
		}
		return $status;
	}
	/**
	 * Add filter for backlinks and content audit tables 'per page' option save.
	 * Add filter for catch posts status change.
	 *
	 * @return void
	 */
	public static function add_table_and_post_actions() {
		add_filter( 'set-screen-option', [ self::class, 'option_filter' ], 10, 3 );
		add_filter( 'set_screen_option_ahrefs_seo_table_content_per_page', [ self::class, 'option_filter' ], 10, 3 );
	}
}