<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Options\Countries;
use ahrefs\AhrefsSeo\Options\Option_Taxonomy;
use ahrefs\AhrefsSeo\Options\Settings_Scope;
use WP_Post;
/**
 * Base class for content settings, implement options get/set.
 */
class Ahrefs_Seo_Content_Settings extends Ahrefs_Seo_Content {

	const OPTION_SCOPE_UPDATED             = 'ahrefs-seo-content-scope-updated';
	const OPTION_SCOPE_LAST_HASH           = 'ahrefs-seo-content-scope-last-hash';
	const OPTION_SCOPE_EXISTING_POST_TYPES = 'ahrefs-seo-content-scope-existing-post-types';
	/**
	 * Set options using global parameters from Wizard form
	 *
	 * @global $_REQUEST
	 * @return bool Is the request successful.
	 */
	public function set_options_from_request() {
        // phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification,WordPress.VIP.SuperGlobalInputUsage.AccessDetected,WordPress.Security.NonceVerification.Recommended -- we already checked nonce.
		if ( ! empty( $_REQUEST['ahrefs_audit_options'] ) && ! empty( $_REQUEST['ah_options_n'] ) ) { // assume that some nonce is already checked before this call.
			$waiting_units = isset( $_REQUEST['waiting_units'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['waiting_units'] ) ) : self::DEFAULT_WAITING_UNIT; // string value.
			$waiting_value = isset( $_REQUEST['waiting_value'] ) ? absint( $_REQUEST['waiting_value'] ) : ( self::WAITING_UNIT_MONTH === $waiting_units ? self::DEFAULT_WAITING_MONTHS : self::DEFAULT_WAITING_WEEKS );
			$country       = isset( $_REQUEST['country'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['country'] ) ) : null;
            // phpcs:enable WordPress.CSRF.NonceVerification.NoNonceVerification,WordPress.VIP.SuperGlobalInputUsage.AccessDetected,WordPress.Security.NonceVerification.Recommended
			$this->set_waiting_value( $waiting_value, $waiting_units );
			$countries = new Countries();
			$countries->set_country( $country );
			$options = Settings_Scope::get()->load_all_options_from_request();
			$list    = [ $options, "{$waiting_value}", $waiting_units ];
			if ( '' !== $countries->get_country() ) {
				$list[] = $countries->get_country();
			}
			$hash = md5( (string) wp_json_encode( $list ) );
			if ( get_option( self::OPTION_SCOPE_LAST_HASH ) !== $hash ) {
				update_option( self::OPTION_SCOPE_UPDATED, true );
				update_option( self::OPTION_SCOPE_LAST_HASH, $hash );
			}
			return true;
		}
		return false;
	}
	/**
	 * Return first 500 published pages.
	 *
	 * @return array<int, string> Key is post_id, value is post_title.
	 */
	public function get_pages_list() {
		$result = [];
		/**
		 * @var WP_Post[] $pages We do not ask for ids.
		 */
		$pages = Helper_Content::get()->get_posts(
			[
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'posts_per_page' => 500, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- Only first 500 pages.
			'orderby'            => 'title',
			'order'              => 'asc',
			]
		);
		if ( $pages ) {
			foreach ( $pages as $page ) {
				$result[ $page->ID ] = $page->post_title;
			}
		}
		return $result;
	}
	/**
	 * Get registered custom post types, do not include products here.
	 * Return only publicly queryable posts.
	 *
	 * @since 0.8.0
	 *
	 * @param bool $skip_product Skip product post type.
	 * @return array<string,string> [slug => label].
	 */
	public function get_custom_post_types( $skip_product = true ) {
		$result = Settings_Scope::get()->get_post_types_list();
		if ( $skip_product ) {
			unset( $result['product'] );
		}
		unset( $result['page'], $result['post'] );
		return $result;
	}
	/**
	 * Turn on options for products if "product" post type exists.
	 *
	 * @since 0.8.0
	 *
	 * @return void
	 */
	public function maybe_turn_on_products() {
		if ( $this->products_exists() ) {
			// enable Products by default.
			$products_categories = Helper_Content::get()->get_all_term_ids( 'product_cat' );
			Settings_Scope::get()->set_posts_categories_checked( 'product', 'product_cat', $products_categories );
			if ( Option_Taxonomy::exists( 'product_cat' ) ) {
				( new Option_Taxonomy( 'product_cat', '' ) )->set_enabled();
			}
		}
		remove_action( 'init', [ $this, 'maybe_turn_on_products' ] );
	}
	/**
	 * Is scope parameters updated and new content audit required
	 *
	 * @since 0.8.0
	 *
	 * @return bool
	 */
	public function is_scope_updated() {
		return (bool) get_option( self::OPTION_SCOPE_UPDATED, false );
	}
	/**
	 * Reset scope updated parameter.
	 *
	 * @since 0.8.0
	 *
	 * @return void
	 */
	public function reset_scope_updated() {
		update_option( self::OPTION_SCOPE_UPDATED, false );
	}
	/**
	 * Do we have new CPT, that was not approved?
	 *
	 * @since 0.8.0
	 *
	 * @return string[] CPT list without already approved or active items, like 'product'.
	 */
	public function has_new_cpt_for_tip() {
		return array_diff( array_keys( $this->get_custom_post_types( false ) ), (array) get_option( self::OPTION_SCOPE_EXISTING_POST_TYPES, [] ), $this->get_custom_post_types_enabled( true ) );
	}
	/**
	 * Add all existing CPT as approved
	 *
	 * @since 0.8.0
	 *
	 * @return void
	 */
	public function approve_existing_cpt() {
		update_option( self::OPTION_SCOPE_EXISTING_POST_TYPES, array_keys( $this->get_custom_post_types( false ) ) );
	}
}