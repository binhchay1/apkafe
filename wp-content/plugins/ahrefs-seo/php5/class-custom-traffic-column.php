<?php

namespace ahrefs\AhrefsSeo;

/**
 * Custom column with traffic details for posts and taxonomies
 */
class Custom_Traffic_Column {

	/**
	 * Add post columns to posts and pages.
	 */
	public function add_post_columns() {
		// Add the custom columns to the post,page post types and checked product and other CPT.
		add_filter( 'manage_pages_columns', [ $this, 'set_custom_columns' ], 10, 2 );
		add_filter( 'manage_posts_columns', [ $this, 'set_custom_columns' ], 10, 2 );
		// Add the data to the custom columns.
		add_action( 'manage_pages_custom_column', [ $this, 'custom_column' ], 10, 2 );
		add_action( 'manage_posts_custom_column', [ $this, 'custom_column' ], 10, 2 );
		// For Categories.
		add_filter( 'manage_edit-category_columns', [ $this, 'set_custom_columns_tax' ] );
		add_filter( 'manage_category_custom_column', [ $this, 'custom_column_category' ], 10, 3 );
		// For Product Categories.
		if ( ! ( new Ahrefs_Seo_Content() )->is_disabled_for_products() ) {
			add_filter( 'manage_edit-product_cat_columns', [ $this, 'set_custom_columns_tax' ] );
			add_filter( 'manage_product_cat_custom_column', [ $this, 'custom_column_product_cat' ], 10, 3 );
		}
	}
	/**
	 * Add custom column for posts and pages screens
	 *
	 * @param array<string, string> $columns Columns list.
	 * @param string                $post_type Current post type.
	 * @return array<string, string> Columns list.
	 */
	public function set_custom_columns( $columns, $post_type = 'page' ) {
		// Callback. Do not use parameter types.
		switch ( $post_type ) {
			case 'page':
			case 'post':
			case '_tax': // predefined string for internal calls.
				$add = true;
				break;
			case 'product':
				$add = ! ( new Ahrefs_Seo_Content() )->is_disabled_for_products();
				break;
			default:
				$add = in_array( $post_type, ( new Ahrefs_Seo_Content() )->get_custom_post_types_enabled(), true );
		}
		if ( $add ) {
			$columns['ahrefs_organic'] = _x( 'Organic traffic', 'Table column title', 'ahrefs-seo' );
		}
		return $columns;
	}
	/**
	 * Add post columns to categories and product categories.
	 *
	 * @since 0.8.0
	 *
	 * @param array<string, string> $columns Columns list.
	 * @return array<string, string> Columns list.
	 */
	public function set_custom_columns_tax( $columns ) {
		// Callback. Do not use parameter types.
		return $this->set_custom_columns( $columns, '_tax' );
	}
	/**
	 * Show content for post custom columns.
	 * Monthly amount of organic traffic + total amount of organic traffic from post created/modified time.
	 *
	 * @param string $column Column name.
	 * @param int    $post_id Post ID.
	 * @param string $taxonomy Taxonomy. Always is empty when called as callback. Filled only when called from other methods.
	 */
	public function custom_column( $column, $post_id, $taxonomy = '' ) {
		// Callback. Do not use parameter types.
		if ( 'ahrefs_organic' === $column ) {
			$post_tax = new Post_Tax( (int) $post_id, (string) $taxonomy );
			$traffic  = ( new Content_Db() )->content_get_organic_traffic_data_for_post( intval( $post_id ), $taxonomy );
			if ( ! $post_tax->is_tax_or_published() || is_null( $traffic ) || is_null( $traffic['organic_month'] ) && is_null( $traffic['organic_total'] ) || $traffic['organic_month'] < 0 || $traffic['organic_total'] < 0 ) {
				?>
				<div class="ahrefs-traffic-wrapper">
					<span aria-hidden="true">—</span>
					<span class="screen-reader-text">
					<?php
					esc_html_e( 'No info', 'ahrefs-seo' );
					?>
				</span>
				</div>
				<?php
			} else {
				?>
				<div class="ahrefs-traffic-wrapper">
					<span class="ahrefs_traffic_month"><span
								class="ahrefs_value">
								<?php
								echo esc_html( isset( $traffic['organic_month'] ) ? $traffic['organic_month'] : '—' );
								?>
				</span> 
				<?php
				esc_html_e( '/month', 'ahrefs-seo' );
				?>
				</span>
					<span class="ahrefs_traffic_total">
					<?php
					esc_html_e( 'Total:', 'ahrefs-seo' );
					?>
				<span
								class="ahrefs_value">
								<?php
								echo esc_html( isset( $traffic['organic_total'] ) ? $traffic['organic_total'] : '—' );
								?>
				</span></span>
				</div>
				<?php
			}
			$this->add_admin_css_posts();
		}
	}
	/**
	 * Show content for category custom columns. Callback.
	 *
	 * @since 0.8.0
	 *
	 * @param string $value Current value.
	 * @param string $column Column name.
	 * @param int    $term_id Term ID.
	 * @return string
	 */
	public function custom_column_category( $value, $column = '', $term_id = 0 ) {
		// Callback. Do not use parameter types.
		ob_start();
		$this->custom_column( (string) $column, intval( $term_id ), 'category' );
		return (string) ob_get_clean();
	}
	/**
	 * Show content for product category custom columns. Callback.
	 *
	 * @since 0.8.0
	 *
	 * @param string $value Initial value.
	 * @param string $column Column name.
	 * @param int    $term_id Term ID.
	 * @return string
	 */
	public function custom_column_product_cat( $value, $column = '', $term_id = 0 ) {
		// Callback. Do not use parameter types.
		ob_start();
		$this->custom_column( (string) $column, intval( $term_id ), 'product_cat' );
		return (string) ob_get_clean();
	}
	/**
	 * Add styles for post columns
	 */
	private function add_admin_css_posts() {
		static $added = null;
		if ( is_null( $added ) ) {
			$added = true;
			?>
			<style>
				.ahrefs-traffic-wrapper .ahrefs_traffic_month, .ahrefs-traffic-wrapper .ahrefs_traffic_total {
					font-family: Arial;
					color: #555555;
				}
				.ahrefs-traffic-wrapper .ahrefs_traffic_month {
					font-family: Arial;
					color: #555555;
					display: block;
				}
				.ahrefs-traffic-wrapper .ahrefs_traffic_month .ahrefs_value {
					font-size: large;
				}
				.ahrefs-traffic-wrapper .ahrefs_value {
					white-space: pre;
				}
			</style>
			<?php
		}
	}
}