<?php

/**
 * A class to translate the endpoints in the WooCommerce Endpoints nav menu metabox
 *
 * @since 0.1
 */
class PLLWC_Admin_Menus {

	/**
	 * Constructor
	 *
	 * @since 0.1
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'init' ) );
	}

	/**
	 * Replaces the WooCommerce endpoints metabox by our own
	 *
	 * @since 0.1
	 */
	public function init() {
		pll_remove_anonymous_object_filter( 'admin_head-nav-menus.php', array( 'WC_Admin_Menus', 'add_nav_menu_meta_boxes' ) );
		add_action( 'admin_head-nav-menus.php', array( $this, 'add_meta_box' ) );
	}

	/**
	 * Adds the endpoints metabox
	 *
	 * @since 0.7.5
	 */
	public function add_meta_box() {
		add_meta_box( 'woocommerce_endpoints_nav_link', __( 'WooCommerce endpoints', 'woocommerce' ), array( $this, 'nav_menu_metabox' ), 'nav-menus', 'side', 'low' );
	}

	/**
	 * Displays the WooCommerce endpoints metabox
	 *
	 * @since 0.1
	 */
	public function nav_menu_metabox() {
		?>
		<div id="posttype-woocommerce-endpoints" class="posttypediv">
			<div id="tabs-panel-woocommerce-endpoints" class="tabs-panel tabs-panel-active">
				<ul id="woocommerce-endpoints-checklist" class="categorychecklist form-no-clear">
					<?php $this->nav_menu_links(); ?>
				</ul>
			</div>
			<p class="button-controls">
				<span class="list-controls">
					<a href="<?php echo esc_url( admin_url( 'nav-menus.php?page-tab=all&selectall=1#posttype-woocommerce-endpoints' ) ); ?>" class="select-all"><?php esc_html_e( 'Select all', 'woocommerce' ); ?></a>
				</span>
				<span class="add-to-menu">
					<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to menu', 'woocommerce' ); ?>" name="add-post-type-menu-item" id="submit-posttype-woocommerce-endpoints">
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}

	/**
	 * Displays the endpoints menu items
	 * The titles are translated in the admin interface language
	 * The links are in the language of the admin language filter
	 *
	 * @since 0.1
	 */
	public function nav_menu_links() {
		$i = -1;
		foreach ( $this->get_endpoints() as $key => $title ) {
			// Don't translate the links when the language filter displays "Show all languages"
			$page_id   = empty( PLL()->curlang ) ? wc_get_page_id( 'myaccount' ) : pll_get_post( wc_get_page_id( 'myaccount' ), PLL()->curlang );
			$permalink = get_permalink( $page_id );
			$permalink = apply_filters( 'woocommerce_get_myaccount_page_permalink', $permalink );

			?>
			<li>
				<label class="menu-item-title">
					<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-object-id]" value="<?php echo esc_attr( $i ); ?>" /> <?php echo esc_html( $title ); ?>
				</label>
				<input type="hidden" class="menu-item-type" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-type]" value="custom" />
				<input type="hidden" class="menu-item-title" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-title]" value="<?php echo esc_html( $title ); ?>" />
				<input type="hidden" class="menu-item-url" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-url]" value="<?php echo esc_url( wc_get_endpoint_url( $key, '', $permalink ) ); ?>" />
				<input type="hidden" class="menu-item-classes" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-classes]" />
			</li>
			<?php
			$i--;
		}
	}

	/**
	 * Get endpoints available in the WooCommerce endpoints menu metabox
	 *
	 * @since 0.9.3
	 */
	protected function get_endpoints() {
		// Get items from account menu.
		$endpoints = wc_get_account_menu_items();

		// Remove dashboard item.
		if ( isset( $endpoints['dashboard'] ) ) {
			unset( $endpoints['dashboard'] );
		}

		// Include missing lost password.
		$endpoints['lost-password'] = __( 'Lost password', 'woocommerce' );

		return apply_filters( 'woocommerce_custom_nav_menu_items', $endpoints );
	}
}
