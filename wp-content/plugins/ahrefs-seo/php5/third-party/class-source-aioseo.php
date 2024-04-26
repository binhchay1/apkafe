<?php

namespace ahrefs\AhrefsSeo\Third_Party;

use ahrefs\AhrefsSeo\Ahrefs_Seo;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Exception;
use ahrefs\AhrefsSeo\Keywords\Data_Keyword;
use ahrefs\AhrefsSeo\Post_Tax;
use Error;
use Exception;
use WP_Query;
/**
 * Class for getting details from other SEO plugins.
 *
 * @since 0.8.8
 */
class Source_Aioseo extends Source implements Assigned_Keyword, Canonical_Url {

	/** @var bool */
	private $is_v4;
	/**
	 * Fill internal variables during initialization
	 *
	 * @return void
	 */
	protected function fill_vars() {
		$this->source_id = Sources::SOURCE_AIOSEO;
		if ( function_exists( 'aioseo' ) ) {
			$this->is_available = true;
			$this->is_v4        = true;
			$this->version      = property_exists( aioseo(), 'version' ) ? aioseo()->version : '4.unknown';
		} elseif ( isset( $GLOBALS['aiosp'] ) && is_object( $GLOBALS['aiosp'] ) && class_exists( '\\All_in_One_SEO_Pack' ) && $GLOBALS['aiosp'] instanceof \All_in_One_SEO_Pack ) {
			$this->is_available = true;
			$this->is_v4        = false;
			$this->version      = property_exists( $GLOBALS['aiosp'], 'version' ) ? $GLOBALS['aiosp']->version : '3.unknown';
		}
	}
	/**
	 * Try to get noindex value from All in One SEO plugin.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return bool|null True - noindex, false - index, null - unknown
	 */
	public function is_noindex( Post_Tax $post_tax ) {
		return $this->is_v4 ? $this->is_noindex_v4( $post_tax ) : $this->is_noindex_v3( $post_tax );
	}
    // phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited,WordPress.WP.DiscouragedFunctions.wp_reset_query_wp_reset_query
	/**
	 * Try to get noindex value from All in One SEO plugin version 4.0+
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return bool|null True - noindex, false - index, null - unknown
	 */
	private function is_noindex_v4( Post_Tax $post_tax ) {
		global $wp_query;
		$result = null;
		try {
			if ( $this->is_available ) {
				if ( function_exists( 'aioseo' ) && class_exists( '\\AIOSEO\\Plugin\\AIOSEO' ) && property_exists( aioseo(), 'db' ) ) {
					// @phpstan-ignore-line -- Plugin may be active or not exists.
					if ( $post_tax->is_post() && class_exists( '\\AIOSEO\\Plugin\\Common\\Models\\Post' ) ) {
						$the_post = \AIOSEO\Plugin\Common\Models\Post::getPost( $post_tax->get_post_id() );
						if ( is_object( $the_post ) && property_exists( $the_post, 'robots_noindex' ) ) {
							$result = (bool) $the_post->robots_noindex;
							Ahrefs_Seo::breadcrumbs( sprintf( '%s(%s): %d', __METHOD__, (string) $post_tax, $result ? 1 : 0 ) );
						}
					} else {
						// one option for all terms of whole taxonomy.
						$args = $this->get_post_tax_args( $post_tax );
						if ( is_array( $args ) && class_exists( '\\AIOSEO\\Plugin\\Common\\Meta\\Robots' ) ) {
							$old_wp_query = $wp_query;
							wp_reset_query();
							// define new query.
							$wp_query = new WP_Query( $args );
							try {
								// create robots instance.
								$robots        = new \AIOSEO\Plugin\Common\Meta\Robots();
								$robots_string = false;
								// remove just added actions.
								if ( is_object( $robots ) && method_exists( $robots, 'meta' ) ) {
									if ( method_exists( $robots, 'noindexFeed' ) ) {
										remove_action( 'template_redirect', [ $robots, 'noindexFeed' ] );
									}
									if ( method_exists( $robots, 'disableWpRobotsCore' ) ) {
										remove_action( 'wp_head', [ $robots, 'disableWpRobotsCore' ] );
									}
									$robots_string = $robots->meta(); // string or false.
								}
								if ( is_string( $robots_string ) ) {
									$result = false !== stripos( $robots_string, 'noindex' );
									Ahrefs_Seo::breadcrumbs( sprintf( '%s(%s): %d', __METHOD__, (string) $post_tax, $result ? 1 : 0 ) );
								}
							} finally {
								$GLOBALS['wp_query'] = $old_wp_query; // restore the original query.
							}
						}
					}
				}
			}
		} catch ( Error $e ) {
			Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Error in %s(%s)', __METHOD__, (string) $post_tax ), 0, $e ) );
		} catch ( Exception $e ) {
			Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Error in %s(%s)', __METHOD__, (string) $post_tax ), 0, $e ) );
		}
		return $result;
	}
	/**
	 * Try to get noindex value from All in One SEO plugin version 4.0+
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return bool|null True - noindex, false - index, null - unknown
	 */
	private function is_noindex_v3( Post_Tax $post_tax ) {
		global $aiosp, $wp_query;
		$result = null;
		$args   = $this->get_post_tax_args( $post_tax );
		try {
			if ( $this->is_available ) {
				if ( is_array( $args ) && ! empty( $aiosp ) && class_exists( '\\All_in_One_SEO_Pack' ) && $aiosp instanceof \All_in_One_SEO_Pack && class_exists( '\\AIOSEOP_Robots_Meta' ) && method_exists( '\\AIOSEOP_Robots_Meta', 'get_robots_meta_tag' ) ) {
					$old_wp_query = $wp_query;
					wp_reset_query();
					// define new query.
					$wp_query = new WP_Query( $args );
					try {
						$aioseop_robots_meta = new \AIOSEOP_Robots_Meta();
						$robots_meta_string  = $aioseop_robots_meta->get_robots_meta_tag();
						if ( is_string( $robots_meta_string ) ) {
							$result = false !== stripos( $robots_meta_string, 'noindex' );
							Ahrefs_Seo::breadcrumbs( sprintf( '%s(%s): %d', __METHOD__, (string) $post_tax, $result ? 1 : 0 ) );
						}
					} finally {
						$GLOBALS['wp_query'] = $old_wp_query; // restore the original query.
					}
				}
			}
		} catch ( Error $e ) {
			Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Error in %s(%s)', __METHOD__, (string) $post_tax ), 0, $e ) );
		} catch ( Exception $e ) {
			Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Error in %s(%s)', __METHOD__, (string) $post_tax ), 0, $e ) );
		}
		return $result;
	}
    // phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited,WordPress.WP.DiscouragedFunctions.wp_reset_query_wp_reset_query
	/**
	 * Get post keyword assigned in AIOSEO plugin.
	 * v4: works for posts only.
	 * v3: does not have focus keyword.
	 *
	 * @since 0.9.0
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return Data_Keyword|null Assigned keyword.
	 */
	public function get_assigned_keyword( Post_Tax $post_tax ) {
		$result = null;
		if ( $this->is_v4 && $this->is_available ) {
			if ( $post_tax->is_post() && class_exists( '\\AIOSEO\\Plugin\\Common\\Models\\Post' ) ) {
				$post_data  = \AIOSEO\Plugin\Common\Models\Post::getPost( $post_tax->get_post_id() );
				$keyphrases = ! empty( $post_data ) && ! empty( $post_data->keyphrases ) ? json_decode( $post_data->keyphrases, true ) : [];
				if ( is_array( $keyphrases ) && isset( $keyphrases['focus'] ) && isset( $keyphrases['focus']['keyphrase'] ) && is_string( $keyphrases['focus']['keyphrase'] ) && '' !== $keyphrases['focus']['keyphrase'] ) {
					$result = new Data_Keyword( $keyphrases['focus']['keyphrase'], $this->source_id );
				}
			}
		}
		return $result;
	}
	/**
	 * Get canonical url from AIOSEO plugin.
	 *
	 * @since 0.9.1
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return string|null Canonical URL.
	 */
	public function get_canonical_url( Post_Tax $post_tax ) {
		$result = null;
		if ( $this->is_available ) {
			if ( $this->is_v4 && $this->is_available && function_exists( 'aioseo' ) ) {
				try {
					if ( $post_tax->is_post() && class_exists( '\\AIOSEO\\Plugin\\Common\\Models\\Post' ) ) {
						$post_data = \AIOSEO\Plugin\Common\Models\Post::getPost( $post_tax->get_post_id() );
						if ( ! empty( $post_data ) && ( property_exists( $post_data, 'canonical_url' ) && is_string( $post_data->canonical_url ) && '' !== $post_data->canonical_url ) ) {
							$result = $post_data->canonical_url;
						}
					}
				} catch ( Error $e ) {
					Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Error in %s(%s)', __METHOD__, (string) $post_tax ), 0, $e ) );
				} catch ( Exception $e ) {
					Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Error in %s(%s)', __METHOD__, (string) $post_tax ), 0, $e ) );
				}
			}
		}
		Ahrefs_Seo::breadcrumbs( sprintf( '%s(%s)[url:%s]: %s', __METHOD__, (string) $post_tax, $post_tax->get_url( true ), (string) wp_json_encode( $result ) ) );
		return $result;
	}
}