<?php

/**
 * Manages the synchronization of posts across languages
 *
 * @since 2.1
 */
class PLL_Sync_Post {
	public $options, $model, $sync, $duplicate, $filters_post, $buttons, $request;
	protected $temp_synchronized;

	/**
	 * Constructor
	 *
	 * @since 2.1
	 *
	 * @param object $polylang
	 */
	public function __construct( &$polylang ) {
		$this->options      = &$polylang->options;
		$this->model        = &$polylang->model;
		$this->sync         = &$polylang->sync;
		$this->duplicate    = &$polylang->duplicate;
		$this->filters_post = &$polylang->filters_post;

		add_filter( 'pll_copy_taxonomies', array( $this, 'copy_taxonomies' ), 5, 4 );
		add_filter( 'pll_copy_post_metas', array( $this, 'copy_post_metas' ), 5, 4 );
		add_action( 'pll_save_post', array( $this, 'sync_posts' ), 5, 2 ); // Before PLL_Admin_Sync, Before PLL_ACF, Before PLLWC

		// Add a filter just before a REST call for keeping the request and can test it
		add_filter( 'rest_dispatch_request', array( $this, 'get_rest_request' ), 5, 2 );

		// Create buttons
		foreach ( $this->model->get_languages_list() as $language ) {
			$this->buttons[ $language->slug ] = new PLL_Sync_Post_Button( $polylang, $language );
		}

		// Add rest field for synchronization of translations @since 2.4
		$post_types = array_fill_keys( $this->model->get_translated_post_types(), array() );

		foreach ( $post_types as $type => $args ) {
			register_rest_field(
				$type,
				'pll_sync_post',
				array(
					'get_callback' => array( $this, 'get_rest_synchronizations' ),
					'schema'       => array(
						'pll_sync_post' => __( 'Synchronizations', 'polylang-pro' ),
						'type' => 'object',
					),
				)
			);
		}
	}

	/**
	 * Checks if the synchronized post is included in bulk trashing or restoring posts
	 *
	 * @since 2.1.2
	 *
	 * @param int $post_id ID of the target post
	 * @return bool
	 */
	protected function doing_bulk_trash( $post_id ) {
		return 'edit.php' === $GLOBALS['pagenow'] && isset( $_GET['action'], $_GET['post'] ) && in_array( $_GET['action'], array( 'trash', 'untrash' ) ) && in_array( $post_id, $_GET['post'] );
	}

	/**
	 * Copies all taxonomies
	 *
	 * @since 2.1
	 *
	 * @param array $taxonomies List of taxonomy names
	 * @param bool  $sync
	 * @param int   $from       Source post id
	 * @param int   $to         Target post id
	 * @return array
	 */
	public function copy_taxonomies( $taxonomies, $sync, $from, $to ) {
		if ( ! empty( $from ) && ! empty( $to ) && $this->are_synchronized( $from, $to ) ) {
			$taxonomies = array_diff( get_post_taxonomies( $from ), get_taxonomies( array( '_pll' => true ) ) );
		}
		return $taxonomies;
	}

	/**
	 * Copies all custom fields
	 *
	 * @since 2.1
	 *
	 * @param array $keys List of custom fields names
	 * @param bool  $sync True if it is synchronization, false if it is a copy
	 * @param int   $from Id of the post from which we copy the information
	 * @param int   $to   Id of the post to which we paste the information
	 * @return array
	 */
	public function copy_post_metas( $keys, $sync, $from, $to ) {
		if ( ! empty( $from ) && ! empty( $to ) && $this->are_synchronized( $from, $to ) ) {
			$from_keys = array_keys( get_post_custom( $from ) ); // *All* custom fields
			$to_keys   = array_keys( get_post_custom( $to ) ); // Adding custom fields of the destination allow to synchronize deleted custom fields
			$keys      = array_merge( $from_keys, $to_keys );
			$keys      = array_unique( $keys );
			$keys      = array_diff( $keys, array( '_edit_last', '_edit_lock' ) );

			// Trash meta status must not be synchronized when bulk trashing / restoring posts otherwise WP can't restore the right post status
			if ( $this->doing_bulk_trash( $to ) ) {
				$keys = array_diff( $keys, array( '_wp_trash_meta_status', '_wp_trash_meta_time' ) );
			}
		}
		return $keys;
	}

	/**
	 * Duplicates the post to one language and optionally saves the synchronization group
	 *
	 * @since 2.2
	 *
	 * @param int    $post_id    Post id of the source post
	 * @param string $lang       Target language
	 * @param bool   $save_group True to update the synchronization group, false otherwise
	 * @return int Post id of the target post
	 */
	public function copy_post( $post_id, $lang, $save_group = true ) {
		global $wpdb;

		$tr_id     = $this->model->post->get( $post_id, $this->model->get_language( $lang ) );
		$tr_post   = $post = get_post( $post_id );
		$languages = array_keys( $this->get( $post_id ) );

		// If it does not exist, create it
		if ( ! $tr_id ) {
			$tr_post->ID = null;
			$tr_id       = wp_insert_post( $tr_post );
			$this->model->post->set_language( $tr_id, $lang ); // Necessary to do it now to share slug

			$translations = $this->model->post->get_translations( $post_id );
			$translations[ $lang ] = $tr_id;
			$this->model->post->save_translations( $post_id, $translations ); // Saves translations in case we created a post

			$languages[] = $lang;

			// Temporarily sync group, even if false === $save_group as we need synchronized posts to copy *all* taxonomies and post metas
			$this->temp_synchronized[ $post_id ][ $tr_id ] = true;

			// Maybe duplicates the featured image
			if ( $this->options['media_support'] ) {
				add_filter( 'pll_translate_post_meta', array( $this->duplicate, 'duplicate_thumbnail' ), 10, 3 );
			}

			add_filter( 'pll_maybe_translate_term', array( $this->duplicate, 'duplicate_term' ), 10, 3 );

			$this->sync->taxonomies->copy( $post_id, $tr_id, $lang );
			$this->sync->post_metas->copy( $post_id, $tr_id, $lang );

			$_POST['post_tr_lang'][ $lang ] = $tr_id; // Hack to avoid creating multiple posts if the original post is saved several times (ex WooCommerce 2.7+)

			/**
			 * Fires after a synchronized post has been created
			 *
			 * @since 2.3.11
			 *
			 * @param int    $post_id ID of the source post
			 * @param int    $tr_id   ID of the newly created post
			 * @param string $lang    Language of the newly created post
			 */
			do_action( 'pll_created_sync_post', $post_id, $tr_id, $lang );

			/** This action is documented in admin/admin-filters-post.php */
			do_action( 'pll_save_post', $post_id, $post, $translations ); // Fire again as we just updated $translations

			unset( $this->temp_synchronized[ $post_id ][ $tr_id ] );
		}

		if ( $save_group ) {
			$this->save_group( $post_id, $languages );
		}

		$tr_post->post_parent = $this->model->post->get( $post->post_parent, $lang ); // Translates post parent
		$tr_post = $this->duplicate->copy_content( $post, $tr_post, $lang );

		// The columns to copy in DB
		$columns = array(
			'post_author',
			'post_date',
			'post_date_gmt',
			'post_content',
			'post_title',
			'post_excerpt',
			'comment_status',
			'ping_status',
			'post_name',
			'post_modified',
			'post_modified_gmt',
			'post_parent',
			'menu_order',
			'post_mime_type',
		);

		// Don't synchronize when trashing / restoring in bulk as it causes an error fired by WP.
		if ( ! $this->doing_bulk_trash( $tr_id ) ) {
			$columns[] = 'post_status';
		}

		/**
		 * Filters the post fields to synchronize when synchronizing posts
		 *
		 * @since 2.3
		 *
		 * @param array  $fields     WP_Post fields to synchronize
		 * @param int    $post_id    Post id of the source post
		 * @param string $lang       Target language
		 * @param bool   $save_group True to update the synchronization group, false otherwise
		 */
		$columns = apply_filters( 'pll_sync_post_fields', array_combine( $columns, $columns ), $post_id, $lang, $save_group );

		$tr_post = array_intersect_key( (array) $tr_post, $columns );
		$wpdb->update( $wpdb->posts, $tr_post, array( 'ID' => $tr_id ) ); // Don't use wp_update_post to avoid conflict (reverse sync)
		clean_post_cache( $tr_id );

		// Keep this here as the 'save_post' action is fired before the sticky status is updated in DB
		isset( $_REQUEST['sticky'] ) && 'sticky' === $_REQUEST['sticky'] ? stick_post( $tr_id ) : unstick_post( $tr_id );

		return $tr_id; // May be useful when the method is used by a 3rd party.
	}

	/**
	 * Duplicates the post and saves the synchronization group
	 *
	 * @since 2.1
	 *
	 * @param int    $post_id      post id
	 * @param object $post         post object
	 */
	public function sync_posts( $post_id, $post ) {
		static $avoid_recursion = false;

		if ( $avoid_recursion ) {
			return;
		}

		// is it a REST API request ?
		if ( isset( $this->request ) ) {
			$rest_params = $this->request->get_params();
		}

		if ( ! empty( $_POST['post_lang_choice'] )
			|| ( isset( $rest_params ) && ! empty( $rest_params['lang'] ) ) ) { // Detect the languages metabox

			// We are editing the post from post.php (only place where we can change the option to sync)
			if ( ! empty( $_POST['pll_sync_post'] ) ) {
				$sync_post = array_intersect( $_POST['pll_sync_post'], array( 'true' ) );
			}

			// if we come form a REST API call
			if ( ! empty( $rest_params['pll_sync_post'] ) ) {
				$sync_post = array_intersect( $rest_params['pll_sync_post'], array( 'true' ) );
			}

			if ( empty( $sync_post ) ) {
				$this->save_group( $post_id, array() );
				return;
			}
		} else {
			// Quick edit or bulk edit or any place where the Languages metabox is not displayed
			$sync_post = array_diff( $this->get( $post_id ), array( $post_id ) ); // Just remove this post from the list
		}

		$avoid_recursion = true;

		$languages = array_keys( $sync_post );

		foreach ( $languages as $lang ) {
			$this->copy_post( $post_id, $lang, false ); // Don't save the group inside the loop
		}

		// Save group if the languages metabox is displayed
		if ( ! empty( $_POST['post_lang_choice'] )
			|| ( isset( $rest_params ) && ! empty( $rest_params['lang'] ) ) ) {
			$this->save_group( $post_id, $languages );
		}

		$avoid_recursion = false;
	}

	/**
	 * Saves the synchronization group
	 * This is stored as an array beside the translations in the post_translations term description
	 *
	 * @since 2.1
	 *
	 * @param int   $post_id   Post currently being saved
	 * @param array $sync_post Array of languages to sync with this post
	 */
	public function save_group( $post_id, $sync_post ) {
		$term = $this->model->post->get_object_term( $post_id, 'post_translations' );

		if ( empty( $term ) ) {
			return;
		}

		$d    = unserialize( $term->description );
		$lang = $this->model->post->get_language( $post_id )->slug;

		if ( empty( $sync_post ) ) {
			if ( isset( $d['sync'][ $lang ] ) ) {
				$d['sync'] = array_diff( $d['sync'], array( $d['sync'][ $lang ] ) );
			}
		} else {
			$sync_post[] = $lang;
			$d['sync']   = empty( $d['sync'] ) ? array_fill_keys( $sync_post, $lang ) : array_merge( array_diff( $d['sync'], array( $lang ) ), array_fill_keys( $sync_post, $lang ) );
		}

		wp_update_term( (int) $term->term_id, 'post_translations', array( 'description' => serialize( $d ) ) );
	}

	/**
	 * Get all posts synchronized with a given post
	 *
	 * @since 2.1
	 *
	 * @param int $post_id
	 * @return array An associative array of arrays with language code as key and post id as value
	 */
	public function get( $post_id ) {
		$term = $this->model->post->get_object_term( $post_id, 'post_translations' );

		if ( ! empty( $term ) ) {
			$lang = $this->model->post->get_language( $post_id );
			$d    = unserialize( $term->description );
			if ( ! empty( $d['sync'][ $lang->slug ] ) ) {
				$keys = array_keys( $d['sync'], $d['sync'][ $lang->slug ] );
				return array_intersect_key( $d, array_flip( $keys ) );
			}
		}

		return array();
	}

	/**
	 * Checks whether two posts are synchronized
	 *
	 * @since 2.1
	 *
	 * @param int $post_id
	 * @param int $other_id
	 * @return bool
	 */
	public function are_synchronized( $post_id, $other_id ) {
		return isset( $this->temp_synchronized[ $post_id ][ $other_id ] ) || in_array( $other_id, $this->get( $post_id ) );
	}

	/**
	 * Returns the object synchronizations
	 *
	 * @since 2.4
	 *
	 * @param array $object Post array
	 * @return array
	 */
	public function get_rest_synchronizations( $object ) {
		return array_fill_keys( array_keys( $this->get( $object['id'] ) ), true );
	}

	/**
	 * Get REST API request and keep it for testing it and get parameters from it when it is necessary
	 *
	 * @since 2.4
	 *
	 * @param boolean $halt
	 * @param object  $request
	 * @return boolean
	 */
	public function get_rest_request( $halt, $request ) {
		$this->request = $request;
		return $halt;
	}
}
