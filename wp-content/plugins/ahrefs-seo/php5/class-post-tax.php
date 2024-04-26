<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Third_Party\Result_Canonical;
use ahrefs\AhrefsSeo\Third_Party\Result_Redirected;
use Exception;
use WP_Post;
/**
 * Class for work with Post or Taxonomy record from content audit table.
 *
 * @since 0.8.0
 */
class Post_Tax {

	const SEPARATOR = '|';
	/**
	 * Post ID or term id.
	 *
	 * @var int
	 */
	protected $post_id = 0;
	/**
	 * Taxonomy name or empty string
	 *
	 * @var string
	 */
	protected $taxonomy = '';
	/**
	 * Snapshot ID, if specified
	 *
	 * @var int|null
	 */
	protected $snapshot_id = null;
	/** @var bool|null Current user can edit. */
	protected $can_edit = null;
	/** @var bool|null Current user can manage this post in plugin. */
	protected $can_manage = null;
	/**
	 * Constructor.
	 *
	 * @param int      $post_id Post or category ID.
	 * @param string   $taxonomy Taxonomy or empty string.
	 * @param int|null $snapshot_id Snapshot ID.
	 */
	public function __construct( $post_id, $taxonomy = '', $snapshot_id = null ) {
		$this->post_id  = $post_id;
		$this->taxonomy = $taxonomy;
		if ( ! is_null( $snapshot_id ) ) {
			$this->snapshot_id = $snapshot_id;
		}
	}
	/**
	 * Return post tax string
	 *
	 * @return string "post_id|taxonomy|snapshot_id", where "|" as a separator (class const).
	 */
	public function __toString() {
		return (string) $this->post_id . self::SEPARATOR . $this->taxonomy . self::SEPARATOR . (string) $this->snapshot_id;
	}
	/**
	 * Is it a taxonomy term?
	 *
	 * @param string|null $taxonomy Check for exactly this taxonomy.
	 * @return bool Is a same taxonomy as $taxonomy parameter or any taxonomy if parameter is null.
	 */
	public function is_taxonomy( $taxonomy = null ) {
		if ( is_null( $taxonomy ) ) {
			return '' !== $this->taxonomy;
		}
		return '' !== $this->taxonomy && $taxonomy === $this->taxonomy;
	}
	/**
	 * Is it a some post type item?
	 *
	 * @param string|null $post_type Check for exactly this post type.
	 * @return bool Is a same post type as $post_type parameter or any standard or custom post if parameter is null.
	 */
	public function is_post( $post_type = null ) {
		if ( is_null( $post_type ) ) {
			return '' === $this->taxonomy;
		}
		return '' === $this->taxonomy && $post_type === $this->get_post_type();
	}
	/**
	 * Get post or term ID
	 *
	 * @return int
	 */
	public function get_post_id() {
		return $this->post_id;
	}
	/**
	 * Get taxonomy
	 *
	 * @return string Taxonomy or empty string for posts.
	 */
	public function get_taxonomy() {
		return $this->taxonomy;
	}
	/**
	 * Get snapshot id
	 *
	 * @return int|null Snapshot id or null if it was not set.
	 */
	public function get_snapshot_id() {
		return $this->snapshot_id;
	}
	/**
	 * Set snapshot id
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return Post_Tax
	 */
	public function set_snapshot_id( $snapshot_id ) {
		$this->snapshot_id = $snapshot_id;
		return $this;
	}
	/**
	 * Get post type
	 *
	 * @return string Post type (post, page, product, etc.) or empty string for taxonomy term.
	 */
	public function get_post_type() {
		if ( $this->is_post() ) {
			return get_post_type( $this->post_id ) ?: '';
		}
		return '';
	}
	/**
	 * Get post status, if it is a post
	 *
	 * @return string Post status like 'publish' or empty string for taxonomy term.
	 */
	public function get_post_status() {
		if ( $this->is_post() ) {
			return get_post_status( $this->post_id ) ?: '';
		}
		return '';
	}
	/**
	 * Is taxonomy a term or a published post.
	 * Do not check that term really exists.
	 *
	 * @see exists() method for terms.
	 *
	 * @return bool
	 */
	public function is_tax_or_published() {
		return $this->is_taxonomy() || 'publish' === $this->get_post_status();
	}
	/**
	 * This post or term exists
	 *
	 * @return bool
	 */
	public function exists() {
		if ( ! empty( $this->post_id ) ) {
			if ( $this->is_post() ) {
				$post = Helper_Content::get()->get_post( $this->post_id );
				return $post instanceof WP_Post && $post->ID > 0;
			} else {
				return Helper_Content::get()->get_term_field( 'term_id', $this->post_id, $this->taxonomy ) === $this->post_id;
			}
		}
		return false;
	}
	/**
	 * Get url for view post or category/term.
	 * No need to check is_post_type_viewable() or is_taxonomy_viewable() because we work only with publicity viewable post types
	 * and predefined taxonomies (both category and product_cat also are viewable).
	 *
	 * @param bool $use_filter Apply filters and return original url.
	 * @return string
	 */
	public function get_url( $use_filter = false ) {
		if ( $this->is_post() ) {
			$result = Helper_Content::get()->get_post_permalink( (int) $this->post_id );
		} elseif ( $this->is_taxonomy( 'category' ) ) {
			$result = Helper_Content::get()->get_term_link( $this->post_id );
		} else {
			$result = Helper_Content::get()->get_term_link( (int) $this->post_id, $this->taxonomy );
		}
		if ( $use_filter ) {
			$result = (string) apply_filters( 'ahrefs_seo_original_url', $result );
		}
		return $result;
	}
	/**
	 * Get url for edit post or category/term.
	 *
	 * @see user_can_edit() Is current user able to edit post/term.
	 *
	 * @return string
	 */
	public function get_url_edit() {
		if ( $this->is_post() ) {
			return ! empty( Helper_Content::get()->get_edit_post_link( $this->post_id ) ) ? Helper_Content::get()->get_edit_post_link( $this->post_id ) : '';
		} else {
			$result = Helper_Content::get()->get_edit_term_link( $this->post_id, $this->taxonomy );
		}
		return is_string( $result ) ? $result : '';
	}
	/**
	 * Get canonical url of post or category/term and the source.
	 * Loads it using value stored in third party SEO plugins.
	 *
	 * @since 0.9.1
	 *
	 * @return Result_Canonical
	 */
	public function get_canonical_data() {
		return Helper_Content::get()->get_canonical_data( $this );
	}
	/**
	 * Get redirected url of post or category/term.
	 * Loads and cache it using value stored in third party SEO plugins.
	 *
	 * @since 0.9.2
	 *
	 * @return Result_Redirected
	 */
	public function get_redirected_data() {
		return Helper_Content::get()->get_redirected_data( $this );
	}
	/**
	 * Get country code using snapshot ID of current item.
	 *
	 * @since 0.9.6
	 *
	 * @return string
	 */
	public function get_country_code() {
		return Helper_Content::get()->get_country_code( $this );
	}
	/**
	 * Get title of post or category/term.
	 *
	 * @param bool $replace_empty_title Return "#123 (no title)" for post (where 123 is Post ID) or "(no title)" for term with empty title.
	 * @return string
	 */
	public function get_title( $replace_empty_title = false ) {
		global $wpdb;
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT title FROM {$wpdb->ahrefs_content} WHERE post_id = %d AND taxonomy = %s AND snapshot_id = %d", $this->post_id, $this->taxonomy, $this->snapshot_id ) );
		if ( is_null( $result ) ) { // fallback.
			if ( $this->is_post() ) {
				$result = get_the_title( $this->post_id );
			} else {
				$result = Helper_Content::get()->get_term_field( 'name', (int) $this->post_id, $this->taxonomy );
				$result = is_string( $result ) ? $result : '';
			}
		}
		if ( $replace_empty_title && '' === $result ) {
			if ( $this->is_post() ) {
				/* translators: %d: ID of a post. */
				return sprintf( __( '#%d (no title)', 'ahrefs-seo' ), $this->post_id );
			}
			return __( '(no title)', 'ahrefs-seo' );
		}
		return $result;
	}
	/**
	 * Return where part for sql query with snapshot_id, post_id, taxonomy.
	 *
	 * @return array<string, mixed> Associative array with keys snapshot_id, post_id, taxonomy and their values.
	 */
	public function as_where_array() {
		return [
			'snapshot_id' => $this->snapshot_id,
			'post_id'     => $this->post_id,
			'taxonomy'    => $this->taxonomy,
		];
	}
	/**
	 * Return format for where part of sql query with format placeholders.
	 *
	 * @return string[] Placeholders for snapshot_id, post_id, taxonomy.
	 */
	public function as_where_format() {
		return [ '%d', '%d', '%s' ];
	}
	/**
	 * Return content of the post or term description.
	 *
	 * @return string
	 */
	public function load_content() {
		try {
			if ( $this->is_post() ) {
				$post = Helper_Content::get()->get_post( $this->post_id );
				return ! is_null( $post ) ? $post->post_content ? (string) $post->post_content : (string) $post->post_excerpt : '';
			} else {
				$result = Helper_Content::get()->get_term_field( 'description', (int) $this->post_id, $this->taxonomy );
				return is_string( $result ) ? $result : '';
			}
		} catch ( Exception $e ) {
			return '';
		}
	}
	/**
	 * Get recommended action for item.
	 * Read DB.
	 *
	 * @return string|null
	 */
	public function load_action() {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT action FROM {$wpdb->ahrefs_content} WHERE snapshot_id = %d AND post_id = %d AND taxonomy = %s", $this->snapshot_id, $this->post_id, $this->taxonomy ) );
	}
	/**
	 * Current user can edit this post or category/term.
	 * This used only for 'Edit' links.
	 *
	 * @return bool
	 */
	public function user_can_edit() {
		if ( is_null( $this->can_edit ) ) {
			$this->can_edit = '' === $this->taxonomy && current_user_can( 'edit_post', $this->post_id ) || '' !== $this->taxonomy && current_user_can( 'edit_term', $this->post_id );
		}
		return $this->can_edit;
	}
	/**
	 * Can user manage this item in a plugin?
	 * "Manage" is include/exclude/run audit, set/approve target keyword for this post or category/term.
	 * This does not mean that user can edit it (using 'Edit' link).
	 *
	 * @since 0.9.5
	 *
	 * @return bool
	 */
	public function user_can_manage() {
		if ( is_null( $this->can_manage ) ) {
			if ( current_user_can( Ahrefs_Seo::CAP_ROLE_EDITOR ) || current_user_can( Ahrefs_Seo::CAP_ROLE_ADMIN ) ) {
				$this->can_manage = true;
			} else {
				$this->can_manage = '' === $this->taxonomy && current_user_can( 'edit_post', $this->post_id ) || '' !== $this->taxonomy && current_user_can( 'edit_term', $this->post_id );
			}
		}
		return $this->can_manage;
	}
	/**
	 * Create instance from post tax string
	 *
	 * @param string $post_tax_string Post tax as string.
	 * @return Post_Tax Post Tax.
	 */
	public static function create_from_string( $post_tax_string ) {
		if ( strpos( $post_tax_string, self::SEPARATOR ) ) {
			list($post_id, $taxonomy1, $snapshot_id) = explode( self::SEPARATOR, $post_tax_string, 3 );
			/** @psalm-suppress RedundantCast,RedundantCondition,TypeDoesNotContainNull */
			return new self( (int) $post_id, (string) $taxonomy1, intval( isset( $snapshot_id ) ? $snapshot_id : '' ) );
			// @phpstan-ignore-line
		}
		return new self( intval( $post_tax_string ), '' );
	}
	/**
	 * Create instance from array with post_id, taxonomy, maybe snapshot_id from SQL query result
	 *
	 * @throws Ahrefs_Seo_Exception When array does not have post_id index.
	 *
	 * @param array $post_tax_array Post Tax details as array with keys post_id, taxonomy and snapshot_id.
	 * @return Post_Tax Post Tax.
	 */
	public static function create_from_array( array $post_tax_array ) {
		if ( ! isset( $post_tax_array['post_id'] ) ) { // really post_id index always exists, because we create it from query results.
			throw new Ahrefs_Seo_Exception( sprintf( 'Initialize Post_Tax with empty post_id %s', (string) wp_json_encode( $post_tax_array ) ) );
		}
		$post_id     = intval( $post_tax_array['post_id'] );
		$taxonomy    = isset( $post_tax_array['taxonomy'] ) ? $post_tax_array['taxonomy'] : '';
		$snapshot_id = isset( $post_tax_array['snapshot_id'] ) ? (int) $post_tax_array['snapshot_id'] : null;
		return new self( $post_id, (string) $taxonomy, $snapshot_id );
	}
	/**
	 * Load from array of post tax strings.
	 *
	 * @param string[] $post_tax_strings Post tax strings list.
	 * @return Post_Tax[] Post Tax list.
	 */
	public static function create_from_strings( array $post_tax_strings ) {
		return array_map(
			function ( $post_tax_string ) {
				return Post_Tax::create_from_string( "{$post_tax_string}" );
			},
			$post_tax_strings
		);
	}
	/**
	 * Create id: same as post tax string
	 *
	 * @param int    $post_id Post or category ID.
	 * @param string $taxonomy Taxonomy.
	 * @param int    $snapshot_id Snapshot ID.
	 * @return string
	 */
	public static function get_post_tax_string( $post_id, $taxonomy, $snapshot_id ) {
		return "{$post_id}" . self::SEPARATOR . $taxonomy . self::SEPARATOR . "{$snapshot_id}";
	}
	/**
	 * Convert post tax list to ids list
	 *
	 * @param Post_Tax[] $post_taxes Posts or terms list.
	 * @return string[]
	 */
	public static function id( array $post_taxes ) {
		return array_map(
			function ( $post_tax ) {
				return (string) $post_tax;
			},
			$post_taxes
		);
	}
}