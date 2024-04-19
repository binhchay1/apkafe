<?php
/**
 * Declare class Lasso_Process_Update_Category_For_Imported_Pretty_Links
 *
 * @package Lasso_Process_Update_Category_For_Imported_Pretty_Links
 */

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Import as Lasso_Import;

use Lasso\Models\Revert as Lasso_Revert;

/**
 * Lasso_Process_Link_Database
 */
class Lasso_Process_Update_Category_For_Imported_Pretty_Links extends Lasso_Process {
	const IS_PROCESSED = 'lasso_update_category_for_imported_pretty_links_processed';

	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_update_category_for_imported_pretty_links';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'update_category_for_imported_pretty_links';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $post_id Queue item to iterate over.
	 *
	 * @return mixed
	 */
	public function task( $post_id ) {
		if ( ! $post_id ) {
			return false;
		}

		Lasso_Helper::write_log( 'Post id: ' . $post_id, $this->log_name );

		$import = new Lasso_Import();

		$post = get_post( $post_id );

		if ( empty( $post ) || LASSO_POST_TYPE !== get_post_type( $post_id ) ) {
			Lasso_Helper::write_log( 'Post ID ' . $post_id . ' is null, doing nothing.', $this->log_name );
		} else {
			$pretty_link_category_names       = $import->get_post_category_names( $post_id, Lasso_Import::PRETTY_LINK_CATEGORY_SLUG );
			$pretty_link_tag_names            = $import->get_post_category_names( $post_id, Lasso_Import::PRETTY_LINK_TAG_SLUG );
			$final_pretty_link_category_names = array_merge( $pretty_link_category_names, $pretty_link_tag_names );
			$final_pretty_link_category_names = array_unique( $final_pretty_link_category_names );

			if ( empty( $final_pretty_link_category_names ) ) {
				Lasso_Helper::write_log( 'Not found any categories.', $this->log_name );
			} else {
				// ? Create Lasso categories from Pretty Category names
				$lasso_category_terms = $this->create_lasso_category_from_pretty_link_category_names( $final_pretty_link_category_names );
				Lasso_Helper::write_log( 'Lasso Catergory terms: ', $this->log_name );
				Lasso_Helper::write_log( $lasso_category_terms, $this->log_name );

				// ? Current category ids
				$current_category_ids = wp_get_post_terms( $post_id, LASSO_CATEGORY, array( 'fields' => 'ids' ) );
				$current_category_ids = is_array( $current_category_ids ) ? $current_category_ids : array();
				Lasso_Helper::write_log( 'Current category ids: ', $this->log_name );
				Lasso_Helper::write_log( $current_category_ids, $this->log_name );

				// ? Merge with current post categories to get the final terms
				$final_category_ids = array_merge( $lasso_category_terms, $current_category_ids );
				$final_category_ids = array_unique( $final_category_ids );

				Lasso_Helper::write_log( 'Final category ids: ', $this->log_name );
				Lasso_Helper::write_log( $final_category_ids, $this->log_name );

				// ? update categories
				if ( ! empty( $final_category_ids ) ) {
					wp_set_object_terms( $post_id, $final_category_ids, LASSO_CATEGORY );
				}
			}
		}

		return false;
	}

	/**
	 * Create Lasso categories from Pretty Category names
	 *
	 * @param array $pretty_link_category_names Pretty link category names in array.
	 * @return array
	 */
	private function create_lasso_category_from_pretty_link_category_names( $pretty_link_category_names ) {
		$lasso_category_terms = array();
		foreach ( $pretty_link_category_names as $category_name ) {
			$term_id = get_term_by( 'name', $category_name, LASSO_CATEGORY )->term_id ?? 0;
			// ? Support category name is number and different existed term ids.
			$term_id = $term_id && term_exists( $term_id ) ? $term_id : 0;

			if ( 0 === $term_id && ! empty( $category_name ) ) { // ? add new category
				$result  = wp_insert_term( $category_name, LASSO_CATEGORY );
				$term_id = ! is_wp_error( $result ) ? $result['term_id'] : 0;
			}

			if ( $term_id ) {
				$lasso_category_terms[] = $term_id;
			}
		}

		return $lasso_category_terms;
	}

	/**
	 * Run background process
	 *
	 * @return bool
	 */
	public function process() {
		// ? prevent it runs multiple times
		$is_processed = get_option( self::IS_PROCESSED, 0 );
		if ( 1 === (int) $is_processed ) {
			return false;
		}

		$lasso_revert = new Lasso_Revert();
		$sql          = '
			SELECT lasso_id
			FROM ' . $lasso_revert->get_table_name() . '
			WHERE plugin = %s
		';
		$sql          = Lasso_Revert::prepare( $sql, 'pretty-link' );
		$post_ids     = Lasso_Revert::get_col( $sql );
		$count        = count( $post_ids );

		if ( $count > 0 ) {
			foreach ( $post_ids as $post_id ) {
				if ( empty( $post_id ) ) {
					continue;
				}
				$this->push_to_queue( $post_id );
			}

			$this->set_total( $count );
			$this->set_log_file_name( $this->log_name );
			$this->task_start_log();
			// ? save queue
			$this->save()->dispatch();
		}

		update_option( self::IS_PROCESSED, 1 );
		return true;
	}
}

new Lasso_Process_Update_Category_For_Imported_Pretty_Links();
