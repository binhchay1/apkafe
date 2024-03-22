<?php

/**
 * Manages compatibility with Admin Columns
 * Version tested: 3.2.3
 *
 * @since 2.4
 */
class PLL_CPAC {

	/**
	 * Add filters
	 *
	 * @since 2.4
	 */
	public function init() {
		foreach ( PLL()->model->get_translated_post_types() as $type ) {
			if ( isset( $_REQUEST['list_screen'] ) ) {
				$filter = 'manage_' . ( 'attachment' == $type ? 'upload' : 'edit-' . $type ) . '_columns';
				add_filter( $filter, array( $this, 'remove_filter_lang' ), 90 ); // Before Polylang
			}

			$filter = 'option_cpac_options_' . ( 'attachment' == $type ? 'wp-media' : $type ) . '__default';
			add_filter( $filter, array( $this, 'filter_default_columns' ) );
		}
	}

	/**
	 * Deactivates the admin language filter on Admin Columns settings page
	 *
	 * @since 2.4
	 *
	 * @param array $columns List of table columns
	 * @return array
	 */
	public function remove_filter_lang( $columns ) {
		PLL()->filters_columns->filter_lang = '';
		return $columns;
	}

	/**
	 * Fix the Polylang columns in default columns
	 *
	 * @since 2.4
	 *
	 * @param array $columns List of table columns
	 * @return array
	 */
	public function filter_default_columns( $columns ) {
		$screen = get_current_screen();

		if ( isset( $screen->base ) ) {
			$is_post_type = 'edit' === $screen->base && has_filter( 'manage_edit-' . $screen->post_type . '_columns', array( PLL()->filters_columns, 'add_post_column' ) );
			$is_media     = 'upload' === $screen->base && has_filter( 'manage_upload_columns', array( PLL()->filters_columns, 'add_post_column' ) );

			if ( $is_post_type || $is_media ) {
				foreach ( pll_languages_list() as $lang ) {
					unset( $columns[ 'language_' . $lang ] );
				}

				$columns = PLL()->filters_columns->add_post_column( $columns );
			}
		}

		return $columns;
	}
}

