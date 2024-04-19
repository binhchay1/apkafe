<?php
/**
 * Declare class Group
 *
 * @package Group
 */

/**
 * WP_Term Object
 * (
 * [term_id] => 2
 * [name] => Group 1
 * [slug] => group-1
 * [term_group] => 0
 * [term_taxonomy_id] => 2
 * [taxonomy] => lasso-cat
 * [description] => Phone
 * [parent] => 0
 * [count] => 3
 * [filter] => raw
 * )
 */

namespace Lasso\Classes;

/**
 * Group
 */
class Group {

	/**
	 * ID
	 *
	 * @var int $id ID.
	 */
	private $id;

	/**
	 * Total links
	 *
	 * @var int $total_links Count lasso urls.
	 */
	private $total_links;

	/**
	 * Slug
	 *
	 * @var string $slug slug.
	 */
	private $slug;

	/**
	 * Group constructor.
	 *
	 * @param array $data An array from DB.
	 */
	public function __construct( $data = array() ) {
		if ( isset( $data['term_id'] ) ) {
			$this->set_id( $data['term_id'] );
		}

		if ( isset( $data['count'] ) ) {
			$this->set_total_lasso_urls( $data['count'] );
		}

		if ( isset( $data['slug'] ) ) {
			$this->set_slug( $data['slug'] );
		}
	}

	/**
	 * Set ID
	 *
	 * @param int $id ID.
	 */
	private function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Get total lasso urls
	 *
	 * @return int
	 */
	public function get_total_links() {
		return $this->total_links;
	}

	/**
	 * Set slug
	 *
	 * @param string $slug slug.
	 */
	private function set_slug( $slug ) {
		$this->slug = $slug;
	}

	/**
	 * Set total lasso urls
	 *
	 * @param int $total_links Total lasso urls.
	 */
	private function set_total_lasso_urls( $total_links ) {
		$this->total_links = $total_links;
	}

	/**
	 * Get ID
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get slug
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Add a lasso url to group
	 *
	 * @param int $lasso_id Lasso ID.
	 *
	 * @return Group
	 */
	public function add_lasso_url( $lasso_id ) {
		wp_set_object_terms( $lasso_id, $this->get_id(), LASSO_CATEGORY, true );
		$this->set_total_lasso_urls( $this->get_total_links() + 1 );
		return $this;
	}

	/**
	 * Get by ID
	 *
	 * @param int $id ID.
	 *
	 * @return Group|null
	 */
	public static function get_by_id( $id ) {
		$term_data = get_term( $id, LASSO_CATEGORY, ARRAY_A );
		if ( ! empty( $term_data ) && ! is_wp_error( $term_data ) ) {
			return new self( $term_data );
		}
		return null;
	}

	/**
	 * Check a lasso url exist
	 *
	 * @param int $lasso_id Lasso ID.
	 *
	 * @return bool
	 */
	public function has_lasso_url( $lasso_id ) {
		$groups = wp_get_post_terms(
			$lasso_id,
			LASSO_CATEGORY,
			array(
				'term_taxonomy_id' => $this->get_id(),
			)
		);
		if ( ! empty( $groups ) ) {
			return true;
		}
		return false;
	}
}
