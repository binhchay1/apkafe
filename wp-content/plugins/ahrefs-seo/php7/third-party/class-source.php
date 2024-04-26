<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Third_Party;

use ahrefs\AhrefsSeo\Ahrefs_Seo;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Content;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Exception;
use ahrefs\AhrefsSeo\Post_Tax;
use Error;
use Exception;
use WP_Term;

/**
 * Import keyword from the source
 *
 * @since 0.8.8
 */
abstract class Source {

	/**
	 * @var bool Is this source available to use (filled when class instance initiated).
	 */
	protected $is_available = false;
	/**
	 * @var string|null Version info or the source plugin.
	 */
	protected $version = null;

	/**
	 * @var string Current source ID.
	 */
	protected $source_id = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		try {
			$this->fill_vars();
		} catch ( Error $e ) {
			Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Error in %s::%s', get_called_class(), __FUNCTION__ ), 0, $e ) );
		} catch ( Exception $e ) {
			Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Error in %s::%s', get_called_class(), __FUNCTION__ ), 0, $e ) );
		}
	}

	/**
	 * Fill internal variables during initialization
	 *
	 * @return void
	 */
	abstract protected function fill_vars() : void;

	/**
	 * Is this source available?
	 *
	 * @return bool
	 */
	public function is_available() : bool {
		return $this->is_available;
	}

	/**
	 * Get version info as string
	 *
	 * @return string|null
	 */
	public function get_version() : ?string {
		return $this->version;
	}

	/**
	 * Try to get noindex value from current source
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return bool|null True - noindex, false - index, null - unknown
	 */
	abstract public function is_noindex( Post_Tax $post_tax ) : ?bool;

	/**
	 * Get source ID.
	 *
	 * @return string
	 */
	public function get_source_id() : string {
		return $this->source_id;
	}

	/**
	 * Get wp_query options for item
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return array|null Arguments for wp_query.
	 * @phpstan-return  array{'p': int, 'post_status': string, 'post_type': string[], 'posts_per_page':int}|array{'category_name': string}|array<string, string|bool>|null Arguments for wp_query.
	 */
	protected function get_post_tax_args( Post_Tax $post_tax ) : ?array {
		if ( $post_tax->is_post() ) {
			return [
				'p'              => $post_tax->get_post_id(),
				'post_status'    => 'publish',
				'post_type'      => ( new Ahrefs_Seo_Content() )->get_custom_post_types_enabled( true ),
				'posts_per_page' => 1,
			];
		}
		$term = get_term_by( 'id', $post_tax->get_post_id(), $post_tax->get_taxonomy() );
		if ( is_object( $term ) && ( $term instanceof WP_Term ) ) {
			if ( 'category' === $term->taxonomy ) {
				return [ 'category_name' => $term->slug ];
			} else {
				return [
					$term->taxonomy => $term->slug,
					'is_tax'        => true,
				];
			}
		}
		return null;
	}

}
