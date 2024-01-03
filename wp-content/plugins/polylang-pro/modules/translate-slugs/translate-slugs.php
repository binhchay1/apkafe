<?php

/**
 * Modifies links on both frontend and admin side
 *
 * @since 1.9
 */
class PLL_Translate_Slugs {
	public $curlang;

	/**
	 * Constructor
	 *
	 * @since 1.9
	 *
	 * @param object $slugs_model
	 * @param object $curlang     Current language
	 */
	public function __construct( &$slugs_model, &$curlang ) {
		$this->slugs_model = &$slugs_model;
		$this->curlang     = &$curlang;

		add_filter( 'pll_post_type_link', array( $this, 'pll_post_type_link' ), 10, 3 );
		add_filter( 'pll_term_link', array( $this, 'pll_term_link' ), 10, 3 );
		add_filter( 'post_type_archive_link', array( $this, 'translate_slug' ), 20, 2 );
	}

	/**
	 * Modifies custom post type links
	 *
	 * @since 1.9
	 *
	 * @param string $url
	 * @param object $lang
	 * @param object $post
	 * @return string
	 */
	public function pll_post_type_link( $url, $lang, $post ) {
		if ( ! empty( $GLOBALS['wp_rewrite'] ) ) {
			$url = $this->slugs_model->translate_slug( $url, $lang, 'front' );
		}

		return $this->slugs_model->translate_slug( $url, $lang, $post->post_type );
	}

	/**
	 * Modifies term links
	 *
	 * @since 1.9
	 *
	 * @param string $url
	 * @param object $lang
	 * @param object $term
	 * @return string
	 */
	public function pll_term_link( $url, $lang, $term ) {
		if ( 'post_format' == $term->taxonomy ) {
			$url = $this->slugs_model->translate_slug( $url, $lang, $term->slug ); // Occurs only on frontend
		}

		if ( ! empty( $GLOBALS['wp_rewrite'] ) ) {
			$url = $this->slugs_model->translate_slug( $url, $lang, 'front' );
		}

		return $this->slugs_model->translate_slug( $url, $lang, $term->taxonomy );
	}

	/**
	 * Translate the slugs
	 *
	 * The filter was originally only on frontend but is needed on admin too for
	 * compatibility with the archive link of the ACF link field since ACF 5.4.0
	 *
	 * @since 1.9
	 *
	 * @param string $link
	 * @param string $post_type Optional
	 * @return string Modified link
	 */
	public function translate_slug( $link, $post_type = '' ) {
		if ( empty( $this->curlang ) ) {
			return $link;
		}

		$types = array(
			'post_type_archive_link' => 'archive_' . $post_type,
			'get_pagenum_link'       => 'paged',
			'author_link'            => 'author',
			'attachment_link'        => 'attachment',
			'search_link'            => 'search',
		);

		$link = $this->slugs_model->translate_slug( $link, $this->curlang, $types[ current_filter() ] );

		if ( ! empty( $GLOBALS['wp_rewrite'] ) ) {
			$link = $this->slugs_model->translate_slug( $link, $this->curlang, 'front' );
		}

		return $link;
	}
}
