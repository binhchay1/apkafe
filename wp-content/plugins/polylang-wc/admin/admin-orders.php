<?php

/**
 * Adapts the language information displayed for orders (it's not allowed to change it)
 *
 * @since 0.1
 */
class PLLWC_Admin_Orders {
	protected $data_store;

	/**
	 * Constructor
	 *
	 * @since 0.1
	 */
	public function __construct() {
		$this->data_store = PLLWC_Data_Store::load( 'order_language' );

		add_action( 'wp_loaded', array( $this, 'custom_columns' ), 20 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 20 );
		add_filter( 'woocommerce_admin_order_actions', array( $this, 'admin_order_actions' ) );
	}

	/**
	 * Removes the standard languages columns for orders
	 * and replace them with one unique column
	 *
	 * @since 0.1
	 */
	public function custom_columns() {
		$class = PLL()->filters_columns;
		remove_filter( 'manage_edit-shop_order_columns', array( $class, 'add_post_column' ), 100 );
		remove_action( 'manage_shop_order_posts_custom_column', array( $class, 'post_column' ), 10, 2 );

		add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_order_column' ), 100 );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'order_column' ), 10, 2 );
	}

	/**
	 * Adds the language column
	 *
	 * @since 0.1
	 *
	 * @param array $columns List of table columns.
	 * @return array modified list of columns.
	 */
	public function add_order_column( $columns ) {
		// FIXME Backward compatibility with WC < 3.3. In newer versions the column language is just at the end
		if ( $n = array_search( 'customer_message', array_keys( $columns ) ) ) {
			$end = array_slice( $columns, $n );
			$columns = array_slice( $columns, 0, $n );
		}

		// Don't add the column when using a language filter
		if ( empty( PLL()->curlang ) ) {
			$columns['language'] = '<span class="order_language tips" data-tip="' . __( 'Language', 'polylang' ) . '">' . __( 'Language', 'polylang' ) . '</span>';
		}

		return isset( $end ) ? array_merge( $columns, $end ) : $columns;
	}

	/**
	 * Fills the language column
	 *
	 * @since 0.1
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Order ID.
	 */
	public function order_column( $column, $post_id ) {
		$lang = $this->data_store->get_language( $post_id );
		$lang = PLL()->model->get_language( $lang );

		if ( 'language' === $column && $lang ) {
			echo $lang->flag ? $lang->flag . '<span class="screen-reader-text">' . esc_html( $lang->name ) . '</span>' : esc_html( $lang->slug ); // PHPCS:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}


	/**
	 * Removes the language metabox for orders
	 *
	 * @since 0.1
	 *
	 * @param string $post_type Post type name.
	 */
	public function add_meta_boxes( $post_type ) {
		if ( 'shop_order' === $post_type ) {
			remove_meta_box( 'ml_box', $post_type, 'side' ); // Remove Polylang metabox
			add_meta_box( 'pllwc_box', __( 'Language', 'polylang' ), array( $this, 'order_language' ), $post_type, 'side', 'high' );
		}
	}

	/**
	 * Displays the Language metabox
	 *
	 * @since 0.1
	 */
	public function order_language() {
		global $post_ID;
		$order    = wc_get_order( $post_ID ); // FIXME why did I do this?
		$order_id = $order->get_id();

		$lang = $this->data_store->get_language( $order_id );
		$lang = $lang ? $lang : pll_default_language();

		$dropdown = new PLL_Walker_Dropdown();

		wp_nonce_field( 'pll_language', '_pll_nonce' );

		// NOTE: the class "tags-input" allows to include the field in the autosave $_POST ( see autosave.js )
		printf(
			'<p><strong>%1$s</strong></p>
			<label class="screen-reader-text" for="post_lang_choice">%1$s</label>
			<div id="select-post-language">%2$s</div>',
			esc_html__( 'Language', 'polylang' ),
			$dropdown->walk( // PHPCS:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				PLL()->model->get_languages_list(),
				array(
					'name'     => 'post_lang_choice',
					'class'    => 'post_lang_choice tags-input',
					'selected' => $lang, // PHPCS:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'flag'     => true,
				)
			)
		);
	}

	/**
	 * Add our pll_ajax_backend parameter to WooCommerce admin order actions urls
	 *
	 * @since 1.0.4
	 *
	 * @param array $actions Admin order actions.
	 * @return array
	 */
	public function admin_order_actions( $actions ) {
		foreach ( $actions as $key => $arr ) {
			if ( false !== strpos( $arr['url'], 'admin-ajax.php' ) ) {
				$actions[ $key ]['url'] = add_query_arg( 'pll_ajax_backend', 1, $arr['url'] );
			}
		}
		return $actions;
	}
}
