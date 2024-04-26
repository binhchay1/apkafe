<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo;

use WP_Post;

/**
 * Implement hooks for Content audit.
 */
class Content_Hooks {

	/** @var Content_Hooks */
	private static $instance = null;

	/**
	 * Return the instance
	 *
	 * @return Content_Hooks
	 */
	public static function get() : self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
			add_action( 'trashed_post', [ $this, 'remove_post_from_audit' ] );
			add_action( 'deleted_post', [ $this, 'remove_post_from_audit' ] );
			add_action( 'untrashed_post', [ $this, 'add_untrashed_post_post_to_audit' ] );

			add_action( 'transition_post_status', [ $this, 'transition_post_status' ], 10, 3 );
			add_action( 'post_updated', [ $this, 'post_updated' ], 10, 3 );

			add_action( Ahrefs_Seo::ACTION_TOKEN_CHANGED, [ $this, 'clean_backlinks_in_new_snapshot' ] );
			add_action( Ahrefs_Seo::ACTION_DOMAIN_CHANGED, [ $this, 'clean_backlinks_in_new_snapshot' ] );

			add_action( 'created_term', [ $this, 'add_new_term_to_audit' ], 10, 3 );
			add_action( 'delete_category', [ $this, 'remove_term_from_audit' ], 10, 3 );
			add_action( 'delete_product_cat', [ $this, 'remove_term_from_audit' ], 10, 3 );
			add_filter( 'wp_update_term_data', [ $this, 'update_term_data' ], 999, 3 );
	}

	/**
	 * Action hook. Remove post from Content Audit active list when post or page trashed.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function remove_post_from_audit( $post_id ) : void {
		// Note: Callback, do not use parameter types.
		if ( is_int( $post_id ) ) {
			Ahrefs_Seo_Data_Content::get()->delete_post_details( new Post_Tax( $post_id, '', 0 ) ); // use dummy value of snapshot, it ignored by called method.
		}
	}

	/**
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function add_untrashed_post_post_to_audit( $post_id ) : void {
		// Note: Callback, do not use parameter types.
		if ( is_int( $post_id ) && in_array( get_post_type( $post_id ), ( new Ahrefs_Seo_Content() )->get_custom_post_types_enabled( true ), true ) ) {
			Ahrefs_Seo_Data_Content::get()->add_post_as_added_since_last( $post_id, '' );
		}
	}

	/**
	 * Update post title on post fields update.
	 *
	 * @since 0.8.0
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post_after Post object following the update.
	 * @param WP_Post $post_before Post object before the update.
	 * @return void
	 */
	public function post_updated( $post_id, $post_after, $post_before ) : void {
		// Note: Callback, do not use parameter types.
		// @phpstan-ignore-next-line -- ignore types from phpDoc.
		if ( is_int( $post_id ) && is_object( $post_after ) && is_object( $post_before ) && ( $post_after instanceof WP_Post ) && ( $post_before instanceof WP_Post )
			&& in_array( get_post_type( $post_id ), ( new Ahrefs_Seo_Content() )->get_custom_post_types_enabled( true ), true ) && 'publish' === $post_after->post_status ) {
			if ( $post_after->post_title !== $post_before->post_title ) {
				Ahrefs_Seo_Data_Content::get()->update_post_title( new Post_Tax( $post_id, '' ), (string) $post_after->post_title );
			}
		}
	}

	/**
	 * Clean backlinks details in new snapshot, if exists.
	 *
	 * @return void
	 */
	public function clean_backlinks_in_new_snapshot() : void {
		( new Snapshot() )->reset_backlinks_for_new_snapshot();
	}

	/**
	 * @since 0.8.0
	 *
	 * @param int    $term_id Term ID.
	 * @param int    $tt_id Term taxonomy ID.
	 * @param string $taxonomy Taxonomy.
	 * @return void
	 */
	public function add_new_term_to_audit( $term_id, $tt_id, $taxonomy ) : void {
		// Note: Callback, do not use parameter types.
		if ( is_string( $taxonomy ) && in_array( $taxonomy, [ 'category', 'product_cat' ], true ) ) {
			Ahrefs_Seo_Data_Content::get()->add_post_as_added_since_last( (int) $term_id, $taxonomy );
		}
	}

	/**
	 * @since 0.8.0
	 *
	 * @param int    $term_id Term ID.
	 * @param int    $tt_id Term taxonomy ID.
	 * @param string $taxonomy Taxonomy.
	 * @return void
	 */
	public function remove_term_from_audit( $term_id, $tt_id, $taxonomy ) : void {
		// Note: Callback, do not use parameter types.
		if ( is_string( $taxonomy )
		&& in_array( $taxonomy, [ 'category', 'product_cat' ], true ) ) {
			Ahrefs_Seo_Data_Content::get()->delete_post_details( new Post_Tax( (int) $term_id, $taxonomy, 0 ) ); // use dummy value of snapshot, it ignored by called method.
		}
	}

	/**
	 * Add published post to audit, remove unpublished post from audit.
	 * Callback on post transition action.
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post Post object.
	 * @return void
	 */
	public function transition_post_status( $new_status, $old_status, $post ) : void {
		// @phpstan-ignore-next-line -- ignore types from phpDoc.
		if ( is_object( $post ) && ( $post instanceof WP_Post )
			&& in_array( $post->post_type, ( new Ahrefs_Seo_Content() )->get_custom_post_types_enabled( true ), true ) ) {
			if ( 'publish' === $new_status && 'publish' !== $old_status ) { // not published post became published.
				Ahrefs_Seo_Data_Content::get()->add_post_as_added_since_last( (int) $post->ID, '' );
			} elseif ( 'publish' === $new_status && 'publish' !== $old_status ) { // published post became not published.
				Ahrefs_Seo_Data_Content::get()->delete_post_details( new Post_Tax( (int) $post->ID, '', 0 ) ); // use dummy value of snapshot, it ignored by called method.
			}
		}
	}

	/**
	 * Update term title.
	 * Filter.
	 *
	 * @param array  $data Term data to be updated.
	 * @param int    $term_id Term ID.
	 * @param string $taxonomy Taxonomy.
	 * @return array
	 */
	public function update_term_data( $data, $term_id, $taxonomy ) {
		// Note: Callback, do not use parameter types.
		// @phpstan-ignore-next-line -- ignore types from phpDoc.
		if ( is_int( $term_id ) && is_string( $taxonomy )
		&& in_array( $taxonomy, [ 'category', 'product_cat' ], true ) ) {
			$post_tax = new Post_Tax( $term_id, $taxonomy );
			if ( isset( $data['name'] ) && $post_tax->get_title() !== $data['name'] ) {
				Ahrefs_Seo_Data_Content::get()->update_post_title( $post_tax, (string) $data['name'] );
			}
		}
		return $data;
	}
}
