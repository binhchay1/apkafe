<?php

namespace ahrefs\AhrefsSeo\Third_Party;

/**
 * Class for getting details popular SEO plugins and WordPress settings.
 *
 * @since 0.8.8
 */
class Sources {

	/** @var Sources|null */
	protected static $instance = null;
	/** @var Source[]|null */
	protected $noindex_sources = null;
	/** @var Assigned_Keyword[]|null */
	protected $keywords_sources = null;
	/** @var Canonical_Url[]|null */
	protected $canonical_url_sources = null;
	/** @var Redirected_Url[]|null */
	protected $redirected_url_sources = null;
	/** @var bool */
	private static $hooks_initialized = false;
	/** @var Source_Yoast|null */
	private static $yoast = null;
	// implement same list as 'kw_source' DB column uses.
	const SOURCE_NO_KEYWORD = 'no-keyword';
	const SOURCE_TOO_SHORT  = 'too-short';
	const SOURCE_GSC        = 'gsc';
	const SOURCE_TF_IDF     = 'tf-idf';
	const SOURCE_MANUAL     = 'manual';
	const SOURCE_YOASTSEO   = 'yoast';
	const SOURCE_AIOSEO     = 'aioseo';
	const SOURCE_RANKMATH   = 'rankmath';
	// additional sources.
	const SOURCE_EXT_WORDPRESS   = 'wp';
	const SOURCE_EXT_SAVED       = 'saved';
	const SOURCE_EXT_REDIRECTION = 'red';
	/**
	 * Get class instance
	 *
	 * @return Sources
	 */
	public static function get() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Set class instance.
	 *
	 * @param Sources $instance Class instance.
	 * @return void
	 */
	public function set( Sources $instance ) {
		self::$instance = $instance;
	}
	/**
	 * Get versions of SEO plugins
	 *
	 * @return array<string, string> Associative array [plugin ID => version string].
	 */
	public function get_versions() {
		$result = [];
		foreach ( $this->get_all_sources() as $source ) {
			if ( $source->is_available() ) {
				$version = $source->get_version();
				if ( ! is_null( $version ) ) {
					$result[ $source->get_source_id() ] = $version;
				}
			}
		}
		return $result;
	}
	/**
	 * Get all sources list.
	 *
	 * @since 0.9.4
	 *
	 * @return Source[] List of sources.
	 */
	private function get_all_sources() {
		return [ new Source_Yoast(), new Source_Aioseo(), new Source_Rankmath(), new Source_Redirection(), new Source_WordPress() ];
	}
	/**
	 * Get sources list for getting "is noidex".
	 *
	 * @return Source[] List of sources.
	 */
	public function get_noindex_sources() {
		if ( is_null( $this->noindex_sources ) ) {
			$this->noindex_sources = array_filter(
				[ new Source_Yoast(), new Source_Aioseo(), new Source_Rankmath(), new Source_WordPress() ],
				function ( Source $source ) {
					return $source->is_available();
				}
			);
		}
		return $this->noindex_sources;
	}
	/**
	 * Get sources list for getting assigned keywords.
	 *
	 * @return Assigned_Keyword[] List of sources.
	 */
	public function get_keywords_sources() {
		if ( is_null( $this->keywords_sources ) ) {
			$this->keywords_sources = array_filter( // return sources, which support Assigned_Keyword interface.
				[ new Source_Yoast(), new Source_Aioseo(), new Source_Rankmath() ],
				function ( Assigned_Keyword $source ) {
					return $source->is_available();
				}
			);
		}
		return $this->keywords_sources;
	}
	/**
	 * Get sources list for getting canonical URL.
	 *
	 * @return Canonical_Url[] List of sources.
	 */
	public function get_canonical_url_sources() {
		if ( is_null( $this->canonical_url_sources ) ) {
			$this->canonical_url_sources = array_filter( // return sources, which support Assigned_Keyword interface.
				[ new Source_Yoast(), new Source_Aioseo(), new Source_Rankmath(), new Source_WordPress() ],
				function ( Canonical_Url $source ) {
					return $source->is_available();
				}
			);
		}
		return $this->canonical_url_sources;
	}
	/**
	 * Get sources list for getting redirected to URL.
	 *
	 * @since 0.9.2
	 *
	 * @return Redirected_Url[] List of sources.
	 */
	public function get_redirected_url_sources() {
		if ( is_null( $this->redirected_url_sources ) ) {
			$this->redirected_url_sources = array_filter( // return sources, which support Assigned_Keyword interface.
				[ new Source_Redirection() ],
				function ( Redirected_Url $source ) {
					return $source->is_available();
				}
			);
		}
		return $this->redirected_url_sources;
	}
	/**
	 * Validate source_id value before saving to DB.
	 *
	 * @param string|null $source_id Raw source value.
	 * @return string|null Null if value is not allowed to save into 'kw_source' DB column.
	 */
	public static function validate_db_source( $source_id = null ) {
		if ( self::SOURCE_EXT_SAVED === $source_id ) {
			$source_id = self::SOURCE_MANUAL;
		}
		return in_array( $source_id, [ self::SOURCE_NO_KEYWORD, self::SOURCE_TOO_SHORT, self::SOURCE_GSC, self::SOURCE_TF_IDF, self::SOURCE_MANUAL, self::SOURCE_YOASTSEO, self::SOURCE_AIOSEO, self::SOURCE_RANKMATH ], true ) ? $source_id : null;
	}
	/**
	 * Source is imported from another plugin
	 *
	 * @param string|null $source_id Raw source value.
	 * @return bool
	 */
	public static function is_source_imported( $source_id = null ) {
		return in_array( $source_id, [ self::SOURCE_YOASTSEO, self::SOURCE_AIOSEO, self::SOURCE_RANKMATH ], true );
	}
	/**
	 * Has active source plugin.
	 *
	 * @since 0.9.1
	 *
	 * @param string $source_id One of Source::SOURCE_* constants.
	 * @return bool Is active.
	 */
	public function has_active_source( $source_id ) {
		static $result = [];
		if ( ! isset( $result[ $source_id ] ) ) {
			$result[ $source_id ] = false;
			switch ( $source_id ) {
				case self::SOURCE_AIOSEO:
					$result[ $source_id ] = ( new Source_Aioseo() )->is_available();
					break;
				case self::SOURCE_RANKMATH:
					$result[ $source_id ] = ( new Source_Rankmath() )->is_available();
					break;
				case self::SOURCE_YOASTSEO:
					$result[ $source_id ] = ( new Source_Yoast() )->is_available();
					break;
				case self::SOURCE_EXT_WORDPRESS:
					$result[ $source_id ] = true;
			}
		}
		return isset( $result[ $source_id ] ) && (bool) $result[ $source_id ];
	}
	/**
	 * Register hooks for post url getting support from third-party plugins.
	 * Adds support of "primary category" from Yoast SEO plugin. It does not add filters for backend (except posts new/edit/list pages).
	 * Note: no custom support for RankMath SEO plugin required. It adds filters by default.
	 *
	 * @since 0.9.1
	 *
	 * @return void
	 */
	public static function register_post_hooks() {
		if ( ! self::$hooks_initialized ) {
			if ( is_null( self::$yoast ) ) {
				self::$yoast = new Source_Yoast();
			}
			if ( self::$yoast->is_available() ) {
				self::$yoast->register_post_hooks();
				self::$hooks_initialized = true;
			}
		}
	}
	/**
	 * Register hooks for post url getting support from third-party plugins.
	 *
	 * @since 0.9.1
	 *
	 * @return void
	 */
	public static function unregister_post_hooks() {
		if ( self::$hooks_initialized ) {
			self::$hooks_initialized = false;
			if ( is_null( self::$yoast ) ) {
				return;
			}
			if ( self::$yoast->is_available() ) {
				self::$yoast->unregister_post_hooks();
			}
		}
	}
	/**
	 * Get title for source
	 *
	 * @since 0.9.4
	 *
	 * @param string $source_id One of self::SOURCE_* const.
	 * @return string Title.
	 */
	public static function get_title( $source_id ) {
		switch ( $source_id ) {
			case self::SOURCE_EXT_WORDPRESS:
				return __( 'WordPress', 'ahrefs-seo' );
			case self::SOURCE_EXT_REDIRECTION:
				return __( 'Redirection', 'ahrefs-seo' );
			case self::SOURCE_AIOSEO:
				return __( 'AIOSEO', 'ahrefs-seo' );
			case self::SOURCE_RANKMATH:
				return __( 'RankMath', 'ahrefs-seo' );
			case self::SOURCE_YOASTSEO:
				return __( 'Yoast', 'ahrefs-seo' );
		}
		return $source_id;
	}
}