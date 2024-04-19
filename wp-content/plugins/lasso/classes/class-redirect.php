<?php
/**
 * Declare class Redirect
 *
 * @package Redirect
 */

namespace Lasso\Classes;

use Lasso\Classes\Affiliates as Lasso_Affiliates;
use Lasso\Classes\Helper as Lasso_Helper;

use Lasso\Models\Model;

use Lasso_Affiliate_Link;
use Lasso_DB;

/**
 * Redirect
 */
class Redirect {

	/**
	 * Redirect to target url if it's Lasso url
	 */
	public function redirect() {
		$lasso_id    = 0;
		$request_uri = Lasso_Helper::get_server_uri();

		if ( empty( $request_uri ) || Lasso_Helper::is_static_url( $request_uri ) ) {
			return;
		}

		$post_name = trim( $request_uri, '/' );
		// ? check whether WordPress post/page exists
		if ( Lasso_DB::get_wp_post_id_by_slug( $post_name ) ) {
			return;
		}
		$is_lasso_post = Lasso_Helper::is_lasso_post_by_post_name( $post_name );

		// ? not a WP Admin page
		if ( ! is_admin() ) {
			// ? fix the old links after importing to Lasso
			$current_link = Lasso_Helper::get_server_current_url();
			$lasso_link   = Lasso_Affiliate_Link::get_lasso_link_from_old_link( $current_link );
			if ( $lasso_link ) {
				$temp  = wp_parse_url( $request_uri );
				$temp2 = wp_parse_url( $lasso_link );

				// ? check whether current link and the old url are same, this make the redirect loop
				$is_links_are_same = isset( $temp['path'] ) && isset( $temp2['path'] ) && trim( $temp['path'], '/' ) === trim( $temp2['path'], '/' );

				if ( ! $is_links_are_same ) {
					// ? redirect to Lasso link
					wp_redirect( $lasso_link, 301 ); // phpcs:ignore
					die;
				}
			}

			$lasso_id = Lasso_Affiliate_Link::get_lasso_post_id_by_url( site_url() . $request_uri );
		}

		$post = null;
		if ( $lasso_id > 0 && $is_lasso_post ) {
			// phpcs:ignore
			$post = get_post( $lasso_id );
		}

		if ( ! is_object( $post ) || LASSO_POST_TYPE !== $post->post_type || is_search() ) {
			// ? Yoast SEO Premium plugin
			$yoast_seo_redirects = get_option( 'wpseo-premium-redirects-base', array() );
			if ( count( $yoast_seo_redirects ) > 0 ) {
				$redirect = array_filter(
					$yoast_seo_redirects,
					function( $redirect ) use ( $post_name ) {
						return $redirect['origin'] === $post_name;
					}
				);

				if ( count( $redirect ) === 1 ) {
					$redirect = reset( $redirect );
					wp_redirect( '/' . $redirect['url'] . '/', $redirect['type'] ); // phpcs:ignore
					die;
				}
			}

			return;
		}

		$lasso_url    = Lasso_Affiliate_Link::get_lasso_url( $post->ID );
		$redirect_url = $lasso_url->target_url;
		$referer_url  = Lasso_Helper::get_server_param( 'HTTP_REFERER' );

		if ( $lasso_url->enable_nofollow || $lasso_url->link_cloaking ) {
			header( 'X-Robots-Tag: noindex, nofollow' );
		}

		// ? not a WP Admin page
		if ( ! is_admin() ) {
			$redirect_url = $this->pass_through_url_parameters( $redirect_url, null, true, $lasso_url );
		}

		$redirect_url = Lasso_Helper::remove_parameter_from_url( $redirect_url, 'lcid' );

		Lasso_Helper::write_log( '===== ===== =====', 'redirect' );
		Lasso_Helper::write_log( $post->ID, 'redirect' );
		Lasso_Helper::write_log( $redirect_url, 'redirect' );

		do_action( 'lasso_before_link_redirect', $post->ID, $referer_url );

		// phpcs:ignore: header( 'Cache-Control: no-cache, must-revalidate' );
		if ( isset( $redirect_url ) && '' !== $redirect_url ) {
			// phpcs:ignore
			wp_redirect( $redirect_url, 302, 'Lasso' );
		} else {
			// phpcs:ignore
			wp_redirect( home_url(), 302 );
		}
		die;
	}

	/**
	 * Get request url
	 */
	public static function get_request_url() {
		$url = Lasso_Helper::get_server_param( 'REQUEST_URI' );

		return apply_filters( 'redirection_request_url', $url );
	}

	/**
	 * Redirect to old url
	 */
	public static function redirect_to_new_url_by_old_uri() {
		$arg = trim( self::get_request_url(), '/' );
		// @codingStandardsIgnoreStart
		$prepare  = Model::prepare(
			'
			SELECT lasso_id
			FROM ' . Model::get_wp_table_name( LASSO_REVERT_DB ) . '
			WHERE old_uri = %s
		',
			$arg
		);
		// @codingStandardsIgnoreEnd
		$result = Model::get_results( $prepare, ARRAY_A );

		if ( count( $result ) > 0 ) {
			$lasso_id = $result[0]['lasso_id'];
			$new_url  = get_the_permalink( $lasso_id );
			// phpcs:ignore
			wp_redirect( $new_url, 301 );
		}
	}

	/**
	 * Redirect to old url if it's 404 page
	 */
	public function lasso_template_redirect() {
		if ( is_404() ) {
			self::redirect_to_new_url_by_old_uri();
		}
	}

	/**
	 * Able to pass through URL parameters to Lasso short url and auto applied to the final redirect URL
	 * SubIDs for Click Tracking feature: Replace the param that having the value {url} by the referer url. Document: https://support.getlasso.co/en/articles/4257670-how-to-use-subids-for-click-tracking
	 *
	 * @param string $redirect_url   The destination url.
	 * @param string $request_uri    The request URI. Default to null.
	 * @param bool   $subids_feature Is enable SubIDs feature. Default to true.
	 * @param object $lasso_url      The Lasso url. Default to null.
	 * @return string
	 */
	public function pass_through_url_parameters( $redirect_url, $request_uri = null, $subids_feature = true, $lasso_url = null ) {
		try {
			$request_uri       = $request_uri ? $request_uri : ( isset( $_SERVER['REQUEST_URI'] ) ? Lasso_Helper::get_server_param( 'REQUEST_URI' ) : Lasso_Helper::get_server_param( 'REDIRECT_URL' ) );
			$request_uri_parts = wp_parse_url( $request_uri );
			$is_updated_param  = false;
			$affiliates_obj    = null;
			$tracking_click    = false;

			$request_uri_parts['query'] = $request_uri_parts['query'] ?? '';

			parse_str( $request_uri_parts['query'], $request_params );
			$redirect_url_parts = wp_parse_url( $redirect_url );

			if ( isset( $redirect_url_parts['query'] ) ) {
				parse_str( $redirect_url_parts['query'], $redirect_url_params );
			} else {
				$redirect_url_params = array();
			}

			// ? We should detect sid query param. This will allow customers to add human-readable text here in places like YouTube descriptions where our JS file can't live
			$sid     = $request_params['sid'] ?? '';
			$referer = Lasso_Helper::get_server_param( 'HTTP_REFERER' );
			if ( $sid && Lasso_Helper::get_base_domain( $referer ) !== Lasso_Helper::get_base_domain( site_url() ) ) { // phpcs:ignore
				$sid_param_value = $sid;
				$affiliates_obj  = new Lasso_Affiliates();
				$affiliate_slug  = $affiliates_obj->is_affiliate_link( $redirect_url );

				if ( $affiliate_slug && ! in_array( $affiliate_slug, Lasso_Affiliates::DISALLOWED_CHANGE_SUBID_AFFILIATES, true ) ) {
					$sub_ids = $affiliates_obj->affiliates[$affiliate_slug]['sub_ids'] ?? array(); // phpcs:ignore

					if ( ! empty( $sub_ids ) ) {
						$tracking_click                       = true;
						$first_sub_id                         = $sub_ids[0];
						$redirect_url_params[ $first_sub_id ] = $sid_param_value;
						unset( $request_params['sid'] ); // ? Remove sid param from request params
					}
				}
			}

			// ? Add more parameter to redirect's parameters if not existing and param value is not empty
			foreach ( $request_params as $request_param_key => $request_param_value ) {
				if ( 'doing_wp_cron' === $request_param_key ) {
					continue;
				}

				if ( ! isset( $redirect_url_params[ $request_param_key ] ) && $request_param_value ) {
					$redirect_url_params[ $request_param_key ] = $request_param_value;
					$is_updated_param                          = true;
				}
			}

			// ? SubIDs for Click Tracking feature: Replace the param that having the value {url} by the referer url
			if ( $subids_feature ) {
				$referer_url   = Lasso_Helper::get_server_param( 'HTTP_REFERER' );
				$referer_url   = ! empty( $referer_url ) ? $referer_url : Lasso_Helper::get_domain_with_scheme();
				$allow_sub_ids = array( '{url}', '{url_full}' );

				$referer_url_params = Lasso_Helper::get_url_params( $referer_url );

				foreach ( $redirect_url_params as $redirect_url_param_key => $redirect_url_param_value ) {
					if ( in_array( $redirect_url_param_value, $allow_sub_ids, true ) ) {
						$referer_url                                    = Lasso_Helper::remove_parameter_from_url( $referer_url, '', true );
						$redirect_url_params[ $redirect_url_param_key ] = rtrim( $referer_url, '/' );
						$is_updated_param                               = true;

						if ( '{url_full}' === $redirect_url_param_value ) {
							$redirect_url_params = array_merge( $redirect_url_params, $referer_url_params );
						}
					}
				}
			}

			// ? Return $redirect_url if there are no parameter because we don't do anything
			if ( empty( $redirect_url_params ) || ( ! $is_updated_param && ! $tracking_click ) ) {
				return $redirect_url;
			}

			// ? Build the final redirect query parameter
			$redirect_url_parts['query'] = urldecode( http_build_query( $redirect_url_params ) );
			$path                        = $redirect_url_parts['path'] ?? '';
			$query                       = ( $redirect_url_parts['query'] ?? '' ) ? '?' . $redirect_url_parts['query'] : '';
			$fragment                    = ( $redirect_url_parts['fragment'] ?? '' ) ? '#' . $redirect_url_parts['fragment'] : '';
			$redirect_url_final          = $redirect_url_parts['scheme'] . '://' . $redirect_url_parts['host'] . $path . $query . $fragment;

			// ? Tracking click request
			if ( $tracking_click ) {
				$affiliates_obj->track_click( $redirect_url_final, $lasso_url );
			}

			// ? lcid from Lasso cloaked link
			$lcid           = Lasso_Helper::get_request_param( 'lcid' );
			$affiliates_obj = new Lasso_Affiliates();
			$affiliate_slug = $affiliates_obj->is_affiliate_link( $redirect_url_final );
			if ( $lcid && $affiliate_slug && ! in_array( $affiliate_slug, Lasso_Affiliates::DISALLOWED_CHANGE_SUBID_AFFILIATES, true ) ) {
				$redirect_url_final = $affiliates_obj->add_lcid_to_subid( $redirect_url_final, $lcid );
			}

			return $redirect_url_final;
		} catch ( \Exception $e ) {
			Lasso_Helper::write_log( "Pass through url parameter error: {$e->getMessage()}", 'redirect' );
			return $redirect_url;
		}
	}

	/**
	 * Prevents Lasso Posts from canonical redirect guessing feature.
	 * Reference: https://developer.wordpress.org/reference/functions/redirect_canonical/
	 *
	 * @param string $redirect_url Guessing Redirect url.
	 * @return bool|string
	 */
	public function prevents_lasso_posts_from_canonical_redirect_guessing( $redirect_url ) {
		if ( $redirect_url && is_404() ) {
			$request_url_parts = wp_parse_url( $redirect_url );
			$post_name         = $request_url_parts['path'] ?? '';
			$post_name         = trim( $post_name, '/' );
			$is_lasso_post     = Lasso_Helper::is_lasso_post_by_post_name( $post_name );

			if ( $is_lasso_post ) {
				return '';
			}
		}

		return $redirect_url;
	}

	/**
	 * Override the Rank Math's "template_redirect" hook by Lasso "template_redirect" hook if this is the Lasso Post
	 *
	 * @param null   $check Null value from Rank Math.
	 * @param string $uri   Request URI.
	 */
	public function rank_math_redirect_pre_search( $check, $uri ) {
		if ( ! empty( $uri ) && Lasso_Helper::is_lasso_post_by_post_name( $uri ) ) {
			return $this->redirect();
		}

		return $check;
	}
}
