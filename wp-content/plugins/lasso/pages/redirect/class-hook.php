<?php
/**
 * Lasso Redirect - Hook.
 *
 * @package Pages
 */

namespace Lasso\Pages\Redirect;

use Lasso\Classes\Redirect as Lasso_Redirect;

/**
 * Lasso Redirect - Hook.
 */
class Hook {
	/**
	 * Declare "Lasso register hook events" to WordPress.
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function register_hooks() {
		$lasso_redirect = new Lasso_Redirect();
		add_filter( 'redirect_canonical', array( $lasso_redirect, 'prevents_lasso_posts_from_canonical_redirect_guessing' ) );
		remove_action( 'template_redirect', 'wp_old_slug_redirect' );
		add_action( 'template_redirect', array( $lasso_redirect, 'lasso_template_redirect' ), 5 );
		add_action( 'template_redirect', array( $lasso_redirect, 'redirect' ), 5 );

		// ? fix conflict with Pretty Link plugin
		add_filter( 'prli-check-if-slug', array( $this, 'conflict_with_pretty_link' ), 10, 2 );
		remove_action( 'template_redirect', 'wp_old_slug_redirect' );

		// ? Rank Math template_redirect hook
		add_filter( 'rank_math/redirection/pre_search', array( $lasso_redirect, 'rank_math_redirect_pre_search' ), 10, 2 );
	}

	/**
	 * Fix conflict with Pretty Link plugin
	 *
	 * @param object $pretty_obj Pretty Link object.
	 * @param string $slug       Pretty link slug.
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function conflict_with_pretty_link( $pretty_obj, $slug ) {
		$pretty_post_type = 'pretty-link';
		$post_id          = $pretty_obj->link_cpt_id ?? 0; // ? this may be a lasso post id after importing from Pretty link
		$post_type        = get_post_type( $post_id );

		return $pretty_post_type !== $post_type ? false : $pretty_obj;
	}
}
