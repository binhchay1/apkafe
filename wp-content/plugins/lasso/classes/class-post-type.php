<?php
/**
 * Declare class Post_Type
 *
 * @package Post_Type
 */

namespace Lasso\Classes;

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Launch_Darkly as Lasso_Launch_Darkly;
use Lasso\Classes\Setting as Lasso_Setting;

use Lasso_Affiliate_Link;
use Lasso_License;
use Lasso_DB;

/**
 * Post_Type
 */
class Post_Type {

	/**
	 * Use Amazon product url instead Lasso url when search/insert a link into a post/page
	 *
	 * @param array $results An array of associative arrays of query results.
	 */
	public function use_amazon_url_instead_lasso_url( $results ) {
		if ( count( $results ) > 0 ) {
			foreach ( $results as $key => $lasso ) {
				$lasso_id  = $lasso['ID'];
				$post_type = get_post_type( $lasso_id );

				if ( LASSO_POST_TYPE === $post_type ) {
					$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );

					// ? set Amazon product url and correct cloak url
					$results[ $key ]['permalink'] = $lasso_url->public_link;
				}
			}
		}

		return $results;
	}

	/**
	 * Use Amazon product url instead Lasso url when search/insert a link into a post/page
	 *
	 * @param array  $response Response data to send to the client.
	 * @param object $server (WP_REST_Server) Server instance.
	 * @param object $request (WP_REST_Request) Request used to generate the response.
	 */
	public function use_amazon_url_instead_lasso_url_gutenberg( $response, $server, $request ) {
		$params = $request->get_params();
		$type   = $params['type'] ?? '';
		if ( 'post' === $type && is_array( $response ) && '/wp/v2/search' === $request->get_route() && count( $response ) > 0 ) {
			foreach ( $response as $key => $post ) {
				if ( 'post' === $post['type'] && LASSO_POST_TYPE === $post['subtype'] ) {
					$lasso_url               = Lasso_Affiliate_Link::get_lasso_url( $post['id'] );
					$response[ $key ]['url'] = $lasso_url->public_link;
				}
			}
		}

		return $response;
	}

	/**
	 * Redirect "Add New" item to "New Affiliate Link"
	 *
	 * @param object $current_screen (WP_Screen) Current WP_Screen object.
	 */
	public function add_new_redirect_to_affiate_link( $current_screen ) {
		$action    = $current_screen->action;
		$base      = $current_screen->base;
		$post_type = $current_screen->post_type;

		if ( 'add' === $action && 'post' === $base && LASSO_POST_TYPE === $post_type ) {
			$lasso_affiliate_link   = new Lasso_Affiliate_Link();
			$new_affiliate_link_url = $lasso_affiliate_link->get_new_affiliate_link_url();
			// phpcs:ignore
			wp_redirect( $new_affiliate_link_url, 301 );
			exit;
		}
	}

	/**
	 * Register a custom post type (Lasso) and a custom taxonomy (Lasso category)
	 */
	public function add_lasso_urls_post_type() {
		// ? register custom post type
		$menu_icon           = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="20" height="20" viewBox="0 0 100 100"><defs><clipPath id="b"><rect width="100" height="100"/></clipPath></defs><g id="a" clip-path="url(#b)"><g transform="translate(-72.918 -0.438)"><g transform="translate(90.918 0.437)"><path d="M60.056,20.152h0a8.093,8.093,0,0,0-8.244,8.172l.141,3.177c-.118,19.814-5.749,18.873-6.665,18.613V13.183A8.353,8.353,0,0,0,36.819,5h0a8.356,8.356,0,0,0-8.472,8.183V50.541c-7.743,2.007-6.385-20.85-6.385-20.85V21.736c0-4.586-4.435-8.292-9.19-8.292h0c-4.745,0-7.75,3.752-7.75,8.338l-.023,8C7.3,65.234,28.347,62,28.347,62l.011,11.943H45.3L45.29,61.962c21.954-.087,23.324-30.387,23.324-30.387V28.419A8.443,8.443,0,0,0,60.056,20.152Z" transform="translate(-5 -5)" fill="#fff"/><path d="M1.519,12.038c0,3.246,1.971,5.9,4.382,5.9H20.077c2.411,0,4.384-2.656,4.384-5.9L25.975,0H0Z" transform="translate(19.053 82.06)" fill="#fff"/></g><path d="M127.888,425.8H98.6a2.75,2.75,0,0,0-2.424,2.974v4.687a2.75,2.75,0,0,0,2.424,2.974h29.283a2.751,2.751,0,0,0,2.428-2.974v-4.687A2.751,2.751,0,0,0,127.888,425.8Z" transform="translate(9.715 -355.14)" fill="#fff"/></g></g></svg>';
		$rewrite_slug_option = array();
		$rewrite_slug        = Lasso_Setting::lasso_get_setting( 'rewrite_slug' );
		$restrict_prefix     = Lasso_Setting::lasso_get_setting( 'restrict_prefix' );

		if ( $rewrite_slug ) {
			$rewrite_slug_option = array(
				'rewrite' => array(
					'slug'       => $rewrite_slug,
					'with_front' => false,
				),
			);
		}

		register_post_type(
			LASSO_POST_TYPE,
			array_merge(
				array(
					'labels'              => array( 'name' => 'Lasso' ),
					'public'              => ! $restrict_prefix, // phpcs:ignore: only redirect for exact prefix
					'show_ui'             => true,
					'show_in_nav_menus'   => true,
					'publicly_queryable'  => true,
					'exclude_from_search' => true,
					'has_archive'         => false,
					'query_var'           => true,
					'menu_position'       => 20,
					'supports'            => array( 'title', 'thumbnail' ),
					'taxonomies'          => array( LASSO_CATEGORY ),
					'show_in_rest'        => true,
					// WARNING | base64_encode() can be used to obfuscate code which is strongly discouraged.
					// Please verify that the function is used for benign reasons.
					// phpcs:ignore
					'menu_icon'           => 'data:image/svg+xml;base64,' . base64_encode( $menu_icon ),
				),
				$rewrite_slug_option
			)
		);

		// ? register custom taxonomy
		register_taxonomy(
			LASSO_CATEGORY,
			LASSO_POST_TYPE,
			array(
				'label'        => __( 'Lasso Categories' ),
				'rewrite'      => array( 'slug' => LASSO_CATEGORY ),
				'hierarchical' => false,
				'public'       => false,
				'labels'       => array(
					'add_new_item' => __( 'Add New Category' ),
					'edit_item'    => __( 'Edit Categories' ),
				),
			)
		);
	}

	/**
	 * Load lasso js file in Classic editor (TinyMCE)
	 *
	 * @param array $plugin_array An array of external TinyMCE plugins.
	 */
	public function lasso_add_tinymce_plugin( $plugin_array ) {
		$lasso_setting = new Lasso_Setting();
		if ( Lasso_Helper::is_classic_editor() && ( $lasso_setting->is_wordpress_post() || $lasso_setting->is_custom_post() ) ) {
			$plugin_array['lasso_tc_button'] = LASSO_PLUGIN_URL . 'admin/assets/js/lasso-display-modal.js?v=' . strval( filemtime( LASSO_PLUGIN_PATH . '/admin/assets/js/lasso-display-modal.js' ) );
		}

		return $plugin_array;
	}

	/**
	 * Add Lasso button to Classic editor (TinyMCE)
	 *
	 * @param array $buttons First-row list of buttons.
	 */
	public function lasso_register_my_tc_button( $buttons ) {
		array_push( $buttons, 'lasso_tc_button' );
		array_push( $buttons, 'lasso_grid_button' );

		return $buttons;
	}

	/**
	 * Load lasso js file in Gutenberg editor
	 */
	public function lasso_gutenberg_block() {
		wp_enqueue_script(
			'lasso-gutenberg-block',
			LASSO_PLUGIN_URL . 'admin/assets/js/lasso-gutenberg-block.js?v=' . strval( filemtime( LASSO_PLUGIN_PATH . '/admin/assets/js/lasso-gutenberg-block.js' ) ),
			array( 'wp-blocks', 'wp-editor', LASSO_POST_TYPE . '-js' ),
			LASSO_VERSION,
			true
		);

		wp_enqueue_script(
			'rank-math-integration',
			LASSO_PLUGIN_URL . 'admin/assets/js/rank-math-integration.js?v=' . strval( filemtime( LASSO_PLUGIN_PATH . '/admin/assets/js/rank-math-integration.js' ) ),
			array( 'wp-blocks', 'wp-editor', 'wp-hooks', 'rank-math-analyzer' ),
			LASSO_VERSION,
			true
		);
	}

	/**
	 * Customize keyword
	 *
	 * @param array $init An array with TinyMCE config.
	 */
	public function tinymce_init( $init ) {
		if ( isset( $init['extended_valid_elements'] ) ) {
			$init['extended_valid_elements'] .= ',keyword';
		} else {
			$init['extended_valid_elements'] = 'keyword';
		}
		$init['custom_elements'] = '~keyword';

		return $init;
	}

	/**
	 * Order menu and submenu of Lasso
	 *
	 * @param boolean $custom_menu Whether custom ordering is enabled. Default false.
	 */
	public function lasso_order_submenu( $custom_menu ) {
		global $submenu;

		$lasso_submenu = @$submenu[ 'edit.php?post_type=' . LASSO_POST_TYPE ]; // phpcs:ignore
		$status        = Lasso_License::get_license_status();

		if ( ! empty( $lasso_submenu ) ) {
			$new_submenu = array();

			foreach ( $lasso_submenu as $subpage ) {
				if ( ! $status ) {
					if ( 'install' === $subpage[2] ) {
						$new_submenu[20] = $subpage;
					} elseif ( 'uninstall' === $subpage[2] ) {
						$new_submenu[25] = $subpage;
					} elseif ( 'settings-general' === $subpage[2] ) {
						$new_submenu[30] = $subpage;
					}
				} else {
					if ( 'program-opportunities' === $subpage[2] ) {
						$subpage[0]     = 'Opportunities';
						$new_submenu[4] = $subpage;
					} elseif ( 'tables' === $subpage[2] ) {
						$subpage[0]     = 'Tables';
						$new_submenu[5] = $subpage;
					} elseif ( 'groups' === $subpage[2] ) {
						$new_submenu[6] = $subpage;
					} elseif ( 'fields' === $subpage[2] ) {
						$new_submenu[7] = $subpage;
					} elseif ( 'import-urls' === $subpage[2] ) {
						$subpage[0]     = 'Import';
						$new_submenu[8] = $subpage;
					} elseif ( 'settings-general' === $subpage[2] ) {
						$subpage[0]     = 'Settings';
						$new_submenu[9] = $subpage;
					} elseif ( 'post-content-history' === $subpage[2] && Lasso_Launch_Darkly::enable_audit_log() ) {
						$subpage[0]      = 'History';
						$new_submenu[10] = $subpage;
					} elseif ( 'edit.php?post_type=' . LASSO_POST_TYPE === $subpage[2] ) {
						$subpage[0]     = 'Dashboard';
						$new_submenu[2] = $subpage;
					}
				}
			}
		}

		$new_submenu[90] = array(
			'Support',
			'manage_options',
			'https://support.getlasso.co/en/',
		);

		$new_submenu[100] = array(
			'Account',
			'manage_options',
			'https://app.getlasso.co/account',
		);

		ksort( $new_submenu );

		// ? ERROR | Overriding WordPress globals is prohibited. Found assignment to $submenu
		// phpcs:ignore
		$submenu[ 'edit.php?post_type=' . LASSO_POST_TYPE ] = $new_submenu;
	}

	/**
	 * Change title of Dashboard page
	 *
	 * @param string $admin_title The page title, with extra context added.
	 */
	public function change_dashboard_title( $admin_title ) {
		global $current_screen;

		if ( 'lasso-urls_page_dashboard' === $current_screen->id ) {
			return 'Dashboard' . $admin_title;
		} elseif ( 'lasso-urls_page_url-details' === $current_screen->id ) {
			return 'Link Details' . $admin_title;
		}

		return $admin_title;
	}

	/**
	 * Handle slug for Lasso post
	 *
	 * @param object $query Query.
	 */
	public function hide_post_in_author_page( $query ) {
		if ( ! $query->is_main_query() || ! is_author() ) {
			return;
		}

		$ids = Lasso_DB::get_lasso_post_ids();

		// ? Exclude Lasso post ids from the author page
		if ( count( $ids ) > 0 ) {
			$query->set( 'post__not_in', $ids );
		}
	}

	/**
	 * Handle slug for Lasso post
	 *
	 * @param object $query Query.
	 */
	public function custom_post_request( $query ) {
		$request_uri = Lasso_Helper::get_server_uri();

		if ( ! $query->is_main_query() || empty( $request_uri ) || Lasso_Helper::is_static_url( $request_uri ) ) {
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

			$is_lasso_post = Lasso_Helper::is_lasso_post_by_post_name( $post_name );
			$lasso_id      = Lasso_Affiliate_Link::get_lasso_post_id_by_url( home_url() . $request_uri );

			if ( $is_lasso_post ) {
				$query->query = array(
					'page'          => '',
					'name'          => $post_name,
					'post_type'     => LASSO_POST_TYPE,
					LASSO_POST_TYPE => $post_name,
				);

				$query->query_vars['p']               = $lasso_id;
				$query->query_vars['name']            = $post_name;
				$query->query_vars['post_type']       = LASSO_POST_TYPE;
				$query->query_vars[ LASSO_POST_TYPE ] = $post_name;
				$query->query_vars['category_name']   = '';
				$query->query_vars['author_name']     = '';
			}
		}

		if ( ( ( isset( $query->query ) && 2 !== count( $query->query ) ) || ! isset( $query->query['page'] ) ) && ! $is_lasso_post ) {
			return;
		}

		if ( ! empty( $query->query['name'] ) && 0 < $lasso_id ) {
			$query->set( 'post_type', array( 'post', LASSO_POST_TYPE ) );
		}
	}

	/**
	 * Remove custom slug
	 *
	 * @param string $post_link The post's permalink.
	 * @param object $post      The post in question.
	 * @param bool   $leavename Whether to keep the post name.
	 */
	public function remove_custom_slug( $post_link, $post, $leavename ) {
		if ( ! $post || LASSO_POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
			return $post_link;
		}

		$home_url           = home_url();
		$home_url           = trim( $home_url, '/' );
		$rewrite_lasso_slug = Lasso_Setting::lasso_get_setting( 'rewrite_slug' );
		$rewrite_lasso_slug = $rewrite_lasso_slug ? "/$rewrite_lasso_slug" : '';

		$post_link = $home_url . $rewrite_lasso_slug . '/' . $post->post_name . '/'; // ? http://domain.com/post-name or http://domain.com/rewrite-slug/post-name

		return $post_link;
	}
}
