<?php
/**
 * Schema List Table
 *
 * @package Schema Pro
 **/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load WP_List_Table if not loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Customizer Initialization
 *
 * @since 1.0.0
 */
class BSF_Custom_Post_List_Table extends WP_List_Table {

	/**
	 * Member Variable
	 *
	 * @var int $per_page items per page
	 * @since 1.0.0
	 */
	public $per_page = 30;

	/**
	 * Member Variable
	 *
	 * @var int $per_page items per page
	 * @since 1.0.0
	 */
	public $custom_post_type = 'post';

	/**
	 * BSF_Custom_Post_List_Table
	 *
	 * @param string $post_type Custom post type slug.
	 */
	public function __construct( $post_type ) {
		global $status, $page;

		$this->custom_post_type = $post_type;
		parent::__construct(
			array(
				'singular' => $post_type,
				'plural'   => $post_type . 's',
				'ajax'     => false,
			)
		);
	}


	/**
	 * No Advanced Headers found message
	 */
	public function no_items() {
		$post_type_object = get_post_type_object( $this->custom_post_type );
		echo sprintf(
			/* translators: %s: post type label */
			esc_html__( 'No %s found', 'wp-schema-pro' ),
			esc_html( $post_type_object->labels->singular_name )
		);
	}

	/**
	 * Set default columns
	 *
	 * @param  array $item       default column items.
	 * @param  array $column_name default column names.
	 * @return void
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'post_title':
			case 'date':
		}

		do_action( 'manage_' . $this->custom_post_type . '_posts_custom_column', $column_name, $item['ID'] );
	}

	/**
	 * Set sortable columns
	 *
	 * @return sortable_columns sortable columns.
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'post_title' => array( 'post_title', false ),
			'date'       => array( 'date', false ),
		);
		return apply_filters( 'manage_' . $this->custom_post_type . '_sortable_columns', $sortable_columns );
	}

	/**
	 * Sort columns
	 *
	 * @param  array $a default column items.
	 * @param  array $b default column names.
	 * @return array $result sortable columns.
	 */
	public function usort_reorder( $a, $b ) {
		if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
				return;
		}
		// If no sort, default to title.
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'post_title';
		// If no order, default to asc.
		$order = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'asc';
		// Determine sort order.
		$result = strcmp( $a[ $orderby ], $b[ $orderby ] );
		// Send final sort direction to usort.
		return ( 'asc' === $order ) ? $result : -$result;
	}

	/**
	 * Get columns
	 *
	 * @return array $columns display columns.
	 */
	public function get_columns() {
		$columns = array(
			'cb'         => '<input type="checkbox" />',
			'post_title' => esc_html__( 'Title', 'wp-schema-pro' ),
			'date'       => esc_html__( 'Date', 'wp-schema-pro' ),
		);
		return apply_filters( 'manage_' . $this->custom_post_type . '_posts_columns', $columns );
	}

	/**
	 * Get bulk actions
	 *
	 * @return array $actions bulk actions.
	 */
	public function get_bulk_actions() {
		if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
			return;
		}
		$current = ( ! empty( $_REQUEST['post_status'] ) ? $_REQUEST['post_status'] : 'all' );
		if ( 'trash' === $current ) {
			$actions = array(
				'restore' => esc_html__( 'Restore', 'wp-schema-pro' ),
				'delete'  => esc_html__( 'Delete Permanently', 'wp-schema-pro' ),
			);
		} elseif ( 'draft' === $current ) {
			$actions = array(
				'trash' => esc_html__( 'Move to Trash', 'wp-schema-pro' ),
			);
		} else {
			$actions = array(
				'draft' => esc_html__( 'Draft', 'wp-schema-pro' ),
				'trash' => esc_html__( 'Move to Trash', 'wp-schema-pro' ),
			);
		}
		return $actions;
	}

	/**
	 * Process bulk actions
	 */
	public function process_bulk_action() {

		// security check!
		if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

			$nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
			$action = 'bulk-' . $this->_args['plural'];

			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_die( 'Nope! Security check failed!' );
			}
		}

		// Detect when a bulk action is being triggered...
		if ( 'trash' === $this->current_action() ) {
			foreach ( $_POST[ $this->_args['singular'] ] as $id ) {
				wp_trash_post( $id );
			}
		}
		if ( 'delete' === $this->current_action() ) {
			foreach ( $_POST[ $this->_args['singular'] ] as $id ) {
				wp_delete_post( $id );
			}
		}
		if ( 'draft' === $this->current_action() ) {
			foreach ( $_POST[ $this->_args['singular'] ] as $id ) {
				$post = array(
					'ID'          => $id,
					'post_status' => 'draft',
				);
				wp_update_post( $post );
			}
		}
		if ( 'restore' === $this->current_action() ) {
			foreach ( $_POST[ $this->_args['singular'] ] as $id ) {
				wp_untrash_post( $id );
			}
		}
	}

	/**
	 * Get bulk actions
	 *
	 * @param array $item first columns checkbox.
	 * @return array  check box columns.
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$item['ID']
		);
	}

	/**
	 * Get columns headers
	 *
	 * @param array $item columns header item.
	 * @return array columns.
	 */
	public function column_post_title( $item ) {
		if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
			return;
		}

		$edit_post_link         = get_edit_post_link( $item['ID'] );
		$delete_post_link       = get_delete_post_link( $item['ID'] );
		$force_delete_post_link = get_delete_post_link( $item['ID'], '', true );

		$post_type_object = get_post_type_object( $this->custom_post_type );
		$can_edit_post    = current_user_can( 'edit_post', $item['ID'] );
		$actions          = array();
		$title            = _draft_or_post_title();

		$post_status = ( ! empty( $_REQUEST['post_status'] ) ) ? $_REQUEST['post_status'] : 'all';
		if ( $can_edit_post && 'trash' !== $post_status ) {
			$actions['edit'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				get_edit_post_link( $item['ID'] ),
				/* translators: %s: post title */
				esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'wp-schema-pro' ), $title ) ),
				__( 'Edit', 'wp-schema-pro' )
			);
		}

		if ( current_user_can( 'delete_post', $item['ID'] ) ) {
			if ( 'trash' === $post_status ) {
				$actions['untrash'] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $item['ID'] ) ), 'untrash-post_' . $item['ID'] ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Restore &#8220;%s&#8221; from the Trash', 'wp-schema-pro' ), $title ) ),
					__( 'Restore', 'wp-schema-pro' )
				);
			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = sprintf(
					'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
					get_delete_post_link( $item['ID'] ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Move &#8220;%s&#8221; to the Trash', 'wp-schema-pro' ), $title ) ),
					_x( 'Trash', 'verb', 'wp-schema-pro' )
				);
			}
			if ( 'trash' === $post_status || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = sprintf(
					'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
					get_delete_post_link( $item['ID'], '', true ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Delete &#8220;%s&#8221; permanently', 'wp-schema-pro' ), $title ) ),
					__( 'Delete Permanently', 'wp-schema-pro' )
				);
			}
		}

		if ( is_post_type_viewable( $post_type_object ) ) {
			if ( in_array( $post_status, array( 'pending', 'draft', 'future' ), true ) ) {
				if ( $can_edit_post ) {
					$preview_link    = get_preview_post_link( $post );
					$actions['view'] = sprintf(
						'<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
						esc_url( $preview_link ),
						/* translators: %s: post title */
						esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'wp-schema-pro' ), $title ) ),
						__( 'Preview', 'wp-schema-pro' )
					);
				}
			} elseif ( 'trash' !== $post_status ) {
				$actions['view'] = sprintf(
					'<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
					get_permalink( $item['ID'] ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'wp-schema-pro' ), $title ) ),
					__( 'View', 'wp-schema-pro' )
				);
			}
		}
		global $post;
		$post = get_post( $item['ID'], OBJECT ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );

		ob_start();
		_post_states( $post );
		$post_states = ob_get_clean();

		$actions = apply_filters( 'post_row_actions', $actions, $post );
		wp_reset_postdata();
		if ( 'trash' === $post_status ) {
			return sprintf( '<strong>%1$s%2$s</strong>%3$s', $item['post_title'], $post_states, $this->row_actions( $actions ) );
		} else {
			return sprintf( '<strong><a class="row-title" href="%1$s" aria-label="%2$s">%2$s</a>%3$s</strong>%4$s', $edit_post_link, $item['post_title'], $post_states, $this->row_actions( $actions ) );
		}

	}

	/**
	 * Retrieve the current page number
	 *
	 * @since 1.0.0
	 * @return int Current page number
	 */
	public function get_paged() {
		if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
			return;
		}
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Get date column
	 *
	 * @param array $post date column.
	 */
	public function column_date( $post ) {
		global $mode;

		$post_id = $post['ID'];

		if ( '0000-00-00 00:00:00' === $post['date'] ) {
			$t_time    = __( 'Unpublished', 'wp-schema-pro' );
			$h_time    = __( 'Unpublished', 'wp-schema-pro' );
			$time_diff = 0;
		} else {
			$t_time = get_the_time( __( 'Y/m/d g:i:s a', 'wp-schema-pro' ) );
			$m_time = $post['date'];
			$time   = get_post_time( 'G', true, $post );

			$time_diff = time() - $time;

			if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
				/* translators: 1: time */
				$h_time = sprintf( __( '%s ago', 'wp-schema-pro' ), human_time_diff( $time ) );
			} else {
				$h_time = mysql2date( __( 'Y/m/d', 'wp-schema-pro' ), $m_time );
			}
		}

		if ( 'publish' === get_post_status( $post_id ) ) {
			$status = __( 'Published', 'wp-schema-pro' );
		} elseif ( 'future' === get_post_status( $post_id ) ) {
			if ( $time_diff > 0 ) {
				$status = '<strong class="error-message">' . __( 'Missed schedule', 'wp-schema-pro' ) . '</strong>';
			} else {
				$status = __( 'Scheduled', 'wp-schema-pro' );
			}
		} else {
			$status = __( 'Last Modified', 'wp-schema-pro' );
		}

		/**
		 * Filters the status text of the post.
		 *
		 * @since 4.8.0
		 *
		 * @param string  $status      The status text.
		 * @param WP_Post $post        Post object.
		 * @param string  $column_name The column name.
		 * @param string  $mode        The list display mode ('excerpt' or 'list').
		 */
		$status = apply_filters( 'post_date_column_status', $status, $post, 'date', $mode );

		if ( $status ) {
			echo esc_html( $status ) . '<br />';
		}

		if ( 'excerpt' === $mode ) {
			/**
			 * Filters the published time of the post.
			 *
			 * If `$mode` equals 'excerpt', the published time and date are both displayed.
			 * If `$mode` equals 'list' (default), the publish date is displayed, with the
			 * time and date together available as an abbreviation definition.
			 *
			 * @since 2.5.1
			 *
			 * @param string  $t_time      The published time.
			 * @param WP_Post $post        Post object.
			 * @param string  $column_name The column name.
			 * @param string  $mode        The list display mode ('excerpt' or 'list').
			 */
			echo esc_html( apply_filters( 'post_date_column_time', $t_time, $post, 'date', $mode ) );
		} else {

			/** This filter is documented in wp-admin/includes/class-wp-posts-list-table.php */
			echo '<abbr title="' . esc_html( $t_time ) . '">' . esc_html( apply_filters( 'post_date_column_time', $h_time, $post, 'date', $mode ) ) . '</abbr>';
		}
	}

	/**
	 * Retrieve the total number of {{custom-post}}
	 *
	 * @since 1.0.0
	 * @return int $total Total number of {{custom-post}}
	 */
	public function get_total_custom_posts() {
		$num_posts   = wp_count_posts( $this->custom_post_type, 'readable' );
		$total_posts = array_sum( (array) $num_posts );
		// Subtract post types that are not included in the admin all list.
		foreach ( get_post_stati(
			array(
				'show_in_admin_all_list' => false,
			)
		) as $state ) {
			$total_posts -= $num_posts->$state;
		}
		return $total_posts;
	}

	/**
	 * Get all items.
	 *
	 * @param string $search search string.
	 */
	public function prepare_items( $search = '' ) {

		if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
			return;
		}
		$post_status = ( ! empty( $_REQUEST['post_status'] ) ) ? $_REQUEST['post_status'] : 'any';
		$data        = array();
		$args        = array(
			'post_type'      => $this->custom_post_type,
			'posts_per_page' => $this->per_page,
			'paged'          => $this->get_paged(),
			's'              => $search,
			'post_status'    => $post_status,
		);

		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) :
			$key = 0;
			while ( $the_query->have_posts() ) :
				$the_query->the_post();
				$data[ $key ] = array(
					'ID'         => get_the_ID(),
					'post_title' => get_the_title(),
					'date'       => get_the_date(),
				);
				$key++;
			endwhile;
		endif;
		wp_reset_postdata();

		$columns = $this->get_columns();

		$hidden = array(); // No hidden columns.

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		usort( $data, array( $this, 'usort_reorder' ) );

		$total_items = $this->get_total_custom_posts();

		$this->items = $data;

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);

	}

	/**
	 * Get Views.
	 *
	 * @return array list of all views.
	 */
	public function get_views() {
		if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
			return;
		}
		$status_links = array();
		$num_posts    = wp_count_posts( $this->custom_post_type, 'readable' );
		$class        = '';
		$total_posts  = array_sum( (array) $num_posts );

		// Subtract post types that are not included in the admin all list.
		foreach ( get_post_stati(
			array(
				'show_in_admin_all_list' => false,
			)
		) as $state ) {
			$total_posts -= $num_posts->$state;
		}

		$class         = empty( $class ) && empty( $_REQUEST['post_status'] ) ? ' class="current"' : '';
		$query_all_var = remove_query_arg( 'post_status' );
		/* translators: %s: count */
		$status_links['all'] = "<a href='" . esc_url( $query_all_var ) . "'$class>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_posts, 'posts', 'wp-schema-pro' ), number_format_i18n( $total_posts ) ) . '</a>';

		foreach ( get_post_stati(
			array(
				'show_in_admin_status_list' => true,
			),
			'objects'
		) as $status ) {
			$class       = '';
			$status_name = $status->name;

			if ( ! in_array( $status_name, array( 'publish', 'draft', 'pending', 'trash', 'future', 'private', 'auto-draft' ), true ) ) {
				continue;
			}

			if ( empty( $num_posts->$status_name ) ) {
				continue;
			}

			if ( isset( $_REQUEST['post_status'] ) && $status_name === $_REQUEST['post_status'] ) {
				$class = ' class="current"';
			}

			$label                        = $status->label_count;
			$query_var                    = add_query_arg( 'post_status', $status_name );
			$status_links[ $status_name ] = "<a href='" . esc_url( $query_var ) . "'$class>" . sprintf( translate_nooped_plural( $label, $num_posts->$status_name ), number_format_i18n( $num_posts->$status_name ) ) . '</a>';
		}

		return $status_links;
	}

	/**
	 * Render List Table Markup.
	 */
	public function render_markup() {
		if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
			return;
		}
		$this->prepare_items();
		$post_type = $this->_args['singular'];
		$post_obj  = get_post_type_object( $post_type );
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php echo esc_html( $post_obj->labels->name ); ?></h1>
			<?php
			$post_new_file = 'post-new.php?post_type=' . $post_type;

			if ( isset( $post_new_file ) && current_user_can( $post_obj->cap->create_posts ) ) {
				echo ' <a href="' . esc_url( admin_url( $post_new_file ) ) . '" class="page-title-action">' . esc_html( $post_obj->labels->add_new_item ) . '</a>';
			}
			// Search results for.
			$s = isset( $_REQUEST['s'] ) ? filter_input( INPUT_POST, 's', FILTER_SANITIZE_STRING ) : '';
			if ( isset( $_REQUEST['s'] ) && strlen( $_REQUEST['s'] ) ) {
				/* translators: %s: search keywords */
				printf( '<span class="subtitle">' . esc_html__( 'Search results for &#8220;%s&#8221;', 'wp-schema-pro' ) . '</span>', esc_html( $s ) );
			}
			?>
			<hr class="wp-header-end">
		<?php
		// table post views with count.
		$this->views();
		?>
			<form id="<?php echo esc_attr( $post_type ); ?>-filter" method="post">
			<?php
			if ( isset( $_REQUEST['s'] ) && strlen( $_REQUEST['s'] ) ) {
				$this->prepare_items( $_REQUEST['s'] );
			} else {
				$this->prepare_items();
			}
			$this->search_box(
				sprintf(
					/* translators: %s: Post Label */
					esc_html__( 'Search %s', 'wp-schema-pro' ),
					$post_obj->labels->name
				),
				'search_id'
			);
			$this->display();
			?>
			</form>
		</div>
		<?php
	}
}
