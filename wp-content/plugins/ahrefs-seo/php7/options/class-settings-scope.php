<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Options;

use ahrefs\AhrefsSeo\Post_Tax;

/**
 * Scope settings.
 *
 * @since 0.9.4
 */
class Settings_Scope {
	/** @var array<Option_Post|Option_Taxonomy> */
	private $options;

	/** @var Settings_Scope */
	private static $instance = null;

	/**
	 * Return the instance
	 *
	 * @return Settings_Scope
	 */
	public static function get() : self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->options = [];
		foreach ( $this->get_post_types_list() as $post_type => $title ) {
			$this->options[] = new Option_Post( $post_type );
		}
		foreach ( $this->get_tax_types_list() as $tax => $title ) {
			$this->options[] = new Option_Taxonomy( $tax, $title );
		}
	}

	/**
	 * Render view with options
	 *
	 * @return void
	 */
	public function render_view() : void {
		$has_singles = false;
		// render the sections.
		foreach ( $this->options as $option ) {
			if ( $option->has_sub_options() ) {
				$option->render_view();
			} else {
				$has_singles = true;
			}
		}
		if ( $has_singles ) {
			$option_section = new Option_Section();
			$option_section->render_view();
			// render the sections with single options for taxonomies.
			foreach ( $this->options as $option ) {
				if ( ! $option->has_sub_options() && ( $option instanceof Option_Taxonomy ) ) {
					$option->render_view();
				}
			}
			// ... and CPT.
			foreach ( $this->options as $option ) {
				if ( ! $option->has_sub_options() && ! ( $option instanceof Option_Taxonomy ) ) {
					$option->render_view();
				}
			}
			$option_section->render_view_close();
		}
	}
	/**
	 * Load options from request
	 *
	 * @return null|string[] Null or options hashes for each module.
	 */
	public function load_all_options_from_request() : ?array {
		$result = false;
		$hashes = [];
		foreach ( $this->options as $option ) {
			$result   = $option->load_options_from_request() || $result;
			$hashes[] = $option->get_options_hash();
		}
		$option_section = new Option_Section();
		$result         = $option_section->load_options_from_request() || $result;
		$hashes[]       = $option_section->get_options_hash();
		if ( ! $result ) {
			return null;
		}
		$hashes = array_filter( $hashes, 'strlen' );
		sort( $hashes );
		return $hashes;
	}

	/**
	 * Get list of public post types.
	 *
	 * @return array<string, string> Key is post slug, value is title.
	 */
	public function get_post_types_list() : array {
		static $result = null;
		if ( is_null( $result ) ) {
			$result = [];
			$values = [];

			/** @var \WP_Post_Type[] $cpt */
			$cpt = get_post_types(
				[
					'public'             => true,
					'publicly_queryable' => true,
					'_builtin'           => false,
				],
				'objects',
				'and'
			);
			array_walk(
				$cpt,
				function( $item ) use ( &$values ) {
					$values[ $item->name ] = $item->label;
				}
			);
			$result['post'] = __( 'Post', 'ahrefs-seo' );
			$result['page'] = __( 'Page', 'ahrefs-seo' );
			if ( isset( $values['product'] ) ) {
				$result['product'] = $values['product'];
			}
			asort( $values );
			foreach ( $values as $key => $value ) {
				$result[ $key ] = $value;
			}
		}
		return $result;
	}
	/**
	 * Get predefined list of public taxonomies.
	 *
	 * @return array<string, string>
	 */
	private function get_tax_types_list() : array {
		static $result = null;
		if ( is_null( $result ) ) {
			$result = [];
			foreach ( [
				'category'    => __( 'Category pages', 'ahrefs-seo' ),
				'product_cat' => __( 'Product category pages', 'ahrefs-seo' ),
				'post_tag'    => __( 'Tag pages', 'ahrefs-seo' ),
			] as $tax => $title ) {
				if ( taxonomy_exists( $tax ) ) {
					$fields = get_taxonomy( $tax );
					if ( $fields ) {
						$result[ $tax ] = $title;
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Set enabled taxonomy values for some post type. Set post type enabled too.
	 *
	 * @param string         $post_type Post type.
	 * @param string         $taxonomy Taxonomy.
	 * @param string[]|int[] $values Tax values.
	 * @return bool Success.
	 */
	public function set_posts_categories_checked( string $post_type, string $taxonomy, array $values ) : bool {
		foreach ( $this->options as $option ) {
			if ( ( $option instanceof Option_Post ) && ( $post_type === $option->get_post_type() ) ) {
				$option->load_options()->enable_tax_values( $taxonomy, $values );
				return true;
			}
		}
		return false;
	}

	/**
	 * Get checked values for the post type and taxonomy pair.
	 *
	 * @param string $post_type Post type.
	 * @param string $taxonomy Taxonomy.
	 * @return int[]|string[] Checked terms ID list.
	 */
	public function get_posts_categories_checked( string $post_type, string $taxonomy ) : array {
		foreach ( $this->options as $option ) {
			if ( ( $option instanceof Option_Post ) && ( $post_type === $option->get_post_type() ) && ! $option->is_id_mode_used() ) {
				return $option->load_options()->get_tax_values( $taxonomy ) ?? [];
			}
		}
		return [];
	}

	/**
	 * Get checked values for the post type that uses ID list.
	 *
	 * @param string $post_type Post type.
	 * @return int[]|string[] Checked terms ID list.
	 */
	public function get_posts_id_checked( string $post_type ) : array {
		foreach ( $this->options as $option ) {
			if ( ( $option instanceof Option_Post ) && ( $post_type === $option->get_post_type() ) && $option->is_id_mode_used() ) {
				return $option->load_options()->get_id_values();
			}
		}
		return [];
	}

	/**
	 * Get list of enabled post types
	 *
	 * @return string[]
	 */
	public function get_enabled_post_types() : array {
		$list = array_keys( $this->get_post_types_list() );
		return array_filter(
			$list,
			function( string $cpt ) {
				return ( new Option_Post( $cpt ) )->is_enabled();
			}
		);
	}

	/**
	 * Is post type enabled?
	 *
	 * @param string $post_type Post type.
	 * @return bool
	 */
	public static function is_enabled_for_post_type( string $post_type ) : bool {
		return Option_Post::exists( $post_type ) && ( new Option_Post( $post_type ) )->is_enabled();
	}

	/**
	 * Is taxonomy enabled?
	 *
	 * @param string $taxonomy Taxonomy.
	 * @return bool
	 */
	public static function is_enabled_for_taxonomy( string $taxonomy ) : bool {
		return Option_Taxonomy::exists( $taxonomy ) && ( new Option_Taxonomy( $taxonomy, '' ) )->is_enabled();
	}

	/**
	 * Add pages to checked (included to audit) pages list. Applied for 'page' post type.
	 *
	 * @param Post_Tax[] $post_taxes Posts or terms list.
	 * @return void
	 */
	public function pages_add_to_checked( array $post_taxes ) : void {
		$list = array_filter(
			$post_taxes,
			function( Post_Tax $item ) {
				return $item->is_post( 'page' );
			}
		); // only the pages use filter by ID for now.
		if ( count( $list ) ) {
			foreach ( $this->options as $option ) {
				if ( ( $option instanceof Option_Post ) && ( 'page' === $option->get_post_type() ) ) {
					$option->add_by_id(
						array_map(
							function( Post_Tax $item ) {
								return $item->get_post_id();
							},
							$list
						)
					);
				}
			}
		}
	}

	/**
	 * Remove pages from checked pages list. Applied for 'page' post type.
	 *
	 * @param Post_Tax[] $post_taxes Posts or terms list.
	 * @return void
	 */
	public function pages_remove_from_checked( array $post_taxes ) : void {
		$list = array_filter(
			$post_taxes,
			function( Post_Tax $item ) {
				return $item->is_post( 'page' );
			}
		); // only the pages use filter by ID for now.
		if ( count( $list ) ) {
			foreach ( $this->options as $option ) {
				if ( ( $option instanceof Option_Post ) && ( 'page' === $option->get_post_type() ) ) {
					$option->remove_by_id(
						array_map(
							function( Post_Tax $item ) {
								return $item->get_post_id();
							},
							$list
						)
					);
				}
			}
		}
	}

}
