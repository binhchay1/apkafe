<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Ahrefs_Seo_Table class.
 * Extends WP_List_Table, add ajax related functions.
 */
class Ahrefs_Seo_Table extends \WP_List_Table {

	/** Action name. Used in nonce checks. */
	const ACTION = 'ahrefs_table';

	/**
	 * Items per page.
	 *
	 * @var int
	 */
	protected $per_page = 10;

	/**
	 * Order by, 'orderby' parameter.
	 *
	 * @var string
	 */
	public $orderby = '';

	/**
	 * Order, 'order' parameter.
	 *
	 * @var string
	 */
	public $order = 'DESC';

	/**
	 * Search string, 's' parameter.
	 *
	 * @var string
	 */
	public $search_string = '';

	/**
	 * Default orderby value, if missing at the request.
	 *
	 * @var string
	 */
	protected $default_orderby = 'first_time';

	/**
	 * The current screen.
	 *
	 * @var \WP_Screen
	 */
	protected $screen;

	/**
	 * Constructor
	 *
	 * @param array $args Initial options.
	 */
	public function __construct( $args = [] ) {
		parent::__construct( $args );

		// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification,WordPress.VIP.SuperGlobalInputUsage.AccessDetected,WordPress.Security.NonceVerification.Recommended -- we create tables on content audit or backlinks page and load parameters, it must work even without nonce.
		$this->search_string = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
		$this->per_page      = $this->get_items_per_page( $this->get_per_page_option_name(), $this->per_page );

		$this->order      = ! empty( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'desc';
		$this->orderby    = ! empty( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : '';
		$sortable_columns = $this->get_sortable_columns();
		if ( ! in_array(
			$this->orderby,
			array_map(
				function( $item ) {
					return $item[0];
				},
				$sortable_columns
			),
			true
		) ) {
				$this->orderby = $this->get_default_orderby();
		}
		// phpcs:enable WordPress.CSRF.NonceVerification.NoNonceVerification,WordPress.VIP.SuperGlobalInputUsage.AccessDetected,WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Get classes for table
	 *
	 * @return array
	 */
	protected function get_table_classes() {
		return array( 'widefat', 'fixed', $this->_args['plural'] );
	}

	/**
	 * Get name for per page option using current child class name.
	 *
	 * @return string
	 */
	protected function get_per_page_option_name() : string {
		$class = strtolower( get_called_class() );
		$pos   = strrpos( $class, '\\' );
		if ( $pos ) {
			$class = substr( $class, $pos + 1 );
		}
		return $class . '_per_page';
	}

	/**
	 * Add per_page to screen options.
	 */
	public function add_screen_options() : void {
		add_screen_option(
			'per_page',
			array(
				'default' => $this->per_page,
				'option'  => $this->get_per_page_option_name(),
			)
		);
	}

	/**
	 * Override ajax_response method
	 */
	public function ajax_response() : void {
		check_ajax_referer( self::ACTION );

		$this->prepare_items();
		$total_items = $this->_pagination_args['total_items'] ?? null;
		$total_pages = $this->_pagination_args['total_pages'] ?? null;

		ob_start();
		if ( ! empty( $_REQUEST['no_placeholder'] ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected -- load parameters.
			$this->display_rows();
		} else {
			$this->display_rows_or_placeholder();
		}
		$rows = ob_get_clean();

		ob_start();
		$this->print_column_headers();
		$headers = ob_get_clean();

		ob_start();
		$this->pagination( 'top' );
		$pagination_top = ob_get_clean();

		ob_start();
		$this->pagination( 'bottom' );
		$pagination_bottom = ob_get_clean();

		$charts = Ahrefs_Seo_Charts::maybe_return_charts();

		$response                         = array( 'rows' => $rows );
		$response['pagination']['top']    = $pagination_top;
		$response['pagination']['bottom'] = $pagination_bottom;
		$response['column_headers']       = $headers;

		if ( ! is_null( $total_items ) ) {
			/* translators: %s: number of items */
			$response['total_items_i18n'] = sprintf( _n( '%s item', '%s items', $total_items, 'ahrefs-seo' ), number_format_i18n( $total_items ) );
		}

		if ( ! is_null( $total_pages ) ) {
			$response['total_pages']      = $total_pages;
			$response['total_pages_i18n'] = number_format_i18n( $total_pages );
		}
		if ( ! empty( $charts ) ) {
			$response['charts'] = $charts;
		}

		if ( ! empty( $_REQUEST['update_tabs'] ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected -- load parameters.
			ob_start();
			$this->views();
			$response['tabs'] = ob_get_clean();
		}

		wp_send_json_success( $response );
	}

	/**
	 * Return updated items as json answer and terminate.
	 * Must be implemented in child class.
	 *
	 * @param Post_Tax[]           $ids List of posts.
	 * @param array<string, mixed> $additional_fields Additional fields.
	 * @return void
	 */
	public function ajax_response_updated( array $ids, array $additional_fields ) : void {
		wp_send_json_success();
	}

	/**
	 * Get default orderby
	 *
	 * @since 0.8.4
	 * @return string
	 */
	protected function get_default_orderby() : string {
		return $this->default_orderby;
	}
}
