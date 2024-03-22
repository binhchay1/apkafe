<?php

/**
 * Manages compatibility with WooCommerce Bookings
 * Version tested: 1.10.11
 *
 * @since 0.6
 */
class PLLWC_Bookings {
	private $switched_locale;

	/**
	 * Constructor
	 *
	 * @since 0.6
	 */
	public function __construct() {
		// Post types
		add_filter( 'pll_get_post_types', array( $this, 'translate_types' ), 10, 2 );

		if ( PLL() instanceof PLL_admin ) {
			add_action( 'wp_loaded', array( $this, 'custom_columns' ), 20 );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 20 );
		}

		// Bookings
		$statuses = array(
			'unpaid',
			'pending-confirmation',
			'confirmed',
			'paid',
			'complete',
			'in-cart',
			'cancelled',
		);

		foreach ( $statuses as $status ) {
			add_action( 'woocommerce_booking_' . $status, array( $this, 'before_booking_metabox_save' ) );
		}
		add_action( 'woocommerce_booking_process_meta', array( $this, 'after_booking_metabox_save' ) );

		// Create booking
		add_action( 'woocommerce_new_booking', array( $this, 'new_booking' ), 1 );

		// Booking language user has switched between "added to cart" and "completed checkout"
		add_action( 'woocommerce_booking_in-cart_to_unpaid', array( $this, 'set_booking_language_at_checkout' ) );
		add_action( 'woocommerce_booking_in-cart_to_pending-confirmation', array( $this, 'set_booking_language_at_checkout' ) );

		// Products
		add_action( 'pllwc_copy_product', array( $this, 'copy_resources' ), 10, 4 );
		add_action( 'pllwc_copy_product', array( $this, 'copy_persons' ), 10, 4 );
		add_action( 'wp_ajax_woocommerce_remove_bookable_resource', array( $this, 'remove_bookable_resource' ), 5 ); // Before WooCommerce Bookings
		add_action( 'wp_ajax_woocommerce_remove_bookable_person', array( $this, 'remove_bookable_person' ), 5 ); // Before WooCommerce Bookings

		add_action( 'pll_save_post', array( $this, 'save_post' ), 10, 3 );
		add_filter( 'update_post_metadata', array( $this, 'update_post_metadata' ), 99, 4 ); // After Yoast SEO which returns null at priority 10 See https://github.com/Yoast/wordpress-seo/pull/6902
		add_filter( 'get_post_metadata', array( $this, 'get_post_metadata' ), 10, 4 );
		add_filter( 'pll_copy_post_metas', array( $this, 'copy_post_metas' ), 10, 5 );
		add_filter( 'pll_translate_post_meta', array( $this, 'translate_post_meta' ), 10, 3 );

		// Cart
		add_filter( 'pllwc_translate_cart_item', array( $this, 'translate_cart_item' ), 10, 2 );
		add_filter( 'pllwc_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 2 );

		// Emails
		// FIXME booking notification?
		$actions = array(
			// Cancelled booking
			'woocommerce_booking_pending-confirmation_to_cancelled_notification',
			'woocommerce_booking_confirmed_to_cancelled_notification',
			'woocommerce_booking_paid_to_cancelled_notification',
			// Booking confirmed
			'woocommerce_booking_confirmed_notification',
			// Reminder
			'wc-booking-reminder',
			// New booking
			'woocommerce_new_booking_notification',
			'woocommerce_admin_new_booking_notification',
		);

		foreach ( $actions as $action ) {
			add_action( $action, array( PLLWC()->emails, 'before_order_email' ), 1 ); // Switch the language for the email
			add_action( $action, array( PLLWC()->emails, 'after_email' ), 999 ); // Switch the language back after the email has been sent
		}

		add_action( 'change_locale', array( $this, 'change_locale' ) );
		add_action( 'parse_query', array( $this, 'filter_bookings_notifications' ) );

		// Endpoints in emails
		if ( isset( PLL()->translate_slugs ) ) {
			add_action( 'pllwc_email_language', array( PLL()->translate_slugs->slugs_model, 'init_translated_slugs' ) );
		}

		// Bookings endpoint
		add_filter( 'pll_translation_url', array( $this, 'pll_translation_url' ), 10, 2 );
		add_filter( 'pllwc_endpoints_query_vars', array( $this, 'pllwc_endpoints_query_vars' ), 10, 3 );

		if ( PLL() instanceof PLL_Frontend ) {
			add_action( 'parse_query', array( $this, 'parse_query' ), 3 ); // Before Polylang (for orders)
		}
	}

	/**
	 * Language and translation management for custom post types
	 *
	 * @since 0.6
	 *
	 * @param array $types List of post type names for which Polylang manages language and translations.
	 * @param bool  $hide  True when displaying the list in Polylang settings.
	 * @return array List of post type names for which Polylang manages language and translations.
	 */
	public function translate_types( $types, $hide ) {
		$wc_bookings_types = array( 'bookable_resource', 'bookable_person', 'wc_booking' );
		return $hide ? array_diff( $types, $wc_bookings_types ) : array_merge( $types, $wc_bookings_types );
	}

	/**
	 * Removes the standard languages columns for bookings
	 * and replace them with one unique column as for orders
	 *
	 * @since 0.6
	 */
	public function custom_columns() {
		remove_filter( 'manage_edit-wc_booking_columns', array( PLL()->filters_columns, 'add_post_column' ), 100 );
		remove_action( 'manage_wc_booking_posts_custom_column', array( PLL()->filters_columns, 'post_column' ), 10, 2 );

		add_filter( 'manage_edit-wc_booking_columns', array( PLLWC()->admin_orders, 'add_order_column' ), 100 );
		add_action( 'manage_wc_booking_posts_custom_column', array( PLLWC()->admin_orders, 'order_column' ), 10, 2 );

		// FIXME add a filter in PLLWC for position of the column?
	}

	/**
	 * Removes the language metabox for bookings
	 *
	 * @since 0.6
	 *
	 * @param string $post_type Post type.
	 */
	public function add_meta_boxes( $post_type ) {
		if ( 'wc_booking' === $post_type ) {
			remove_meta_box( 'ml_box', $post_type, 'side' ); // Remove Polylang metabox
		}
	}

	/**
	 * Reload Bookings translations
	 * Used for emails and the workaround localized bookings meta keys
	 *
	 * @since 1.0
	 */
	public function change_locale() {
		load_plugin_textdomain( 'woocommerce-bookings', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Reloads the WooCommerce Bookings and WP text domains to workaround localized bookings meta
	 *
	 * @since 0.6
	 *
	 * @param int $post_id Booking ID.
	 */
	public function before_booking_metabox_save( $post_id ) {
		if ( isset( $_POST['post_type'], $_POST['wc_bookings_details_meta_box_nonce'] ) && 'wc_booking' === $_POST['post_type'] ) {  // phpcs:ignore WordPress.Security.NonceVerification
			$booking_locale = pll_get_post_language( $post_id, 'locale' );
			$this->switched_locale = switch_to_locale( $booking_locale );
		}
	}

	/**
	 * Reloads the WooCommerce Bookings and WP text domains to workaround localized bookings meta
	 * Part of the workaround for localized bookings meta keys
	 *
	 * @since 0.6
	 */
	public function after_booking_metabox_save() {
		if ( ! empty( $this->switched_locale ) ) {
			unset( $this->switched_locale );
			restore_previous_locale();
		}
	}

	/**
	 * Assigns the booking and order languages when creating a new booking from admin
	 *
	 * @since 0.6
	 *
	 * @param int $booking_id Booking ID.
	 */
	public function new_booking( $booking_id ) {
		$data_store = PLLWC_Data_Store::load( 'product_language' );

		$booking = get_wc_booking( $booking_id );
		$lang    = $data_store->get_language( $booking->product_id );
		pll_set_post_language( $booking->id, $lang );

		if ( ! empty( $booking->order_id ) ) {
			$data_store = PLLWC_Data_Store::load( 'order_language' );
			$data_store->set_language( $booking->order_id, $lang );
		}
	}

	/**
	 * Assigns the booking language in case a visitor adds the product to cart in a language
	 * and then switches the language when before he completes the checkout
	 *
	 * @since 0.7.3
	 *
	 * @param int $booking_id Booking ID.
	 */
	public function set_booking_language_at_checkout( $booking_id ) {
		$lang = pll_current_language();

		if ( pll_get_post_language( $booking_id ) !== $lang ) {
			pll_set_post_language( $booking_id, $lang );
		}
	}

	/**
	 * Copy or synchronize bookable posts (resource, person)
	 *
	 * @since 0.6
	 *
	 * @param array  $post Bookable post to copy (person or resource).
	 * @param int    $to   id of the product to which we paste informations.
	 * @param string $lang Language slug.
	 * @param bool   $sync True if it is synchronization, false if it is a copy, defaults to false.
	 * @return int Translated bookable post.
	 */
	protected function copy_bookable_post( $post, $to, $lang, $sync ) {
		$id = $post['ID'];
		$tr_id = pll_get_post( $id, $lang );

		if ( $tr_id ) {
			// If the translated bookable_person already exists, make sure it has the right post_parent
			$post = get_post( $tr_id );
			if ( $post->post_parent !== $to ) {
				wp_update_post( array( 'ID' => $tr_id, 'post_parent' => $to ) );
			}
		} else {
			// Creates the bookable_resource if it does not exist yet
			$post['ID']          = null;
			$post['post_parent'] = $to;
			$tr_id               = wp_insert_post( $post );
			pll_set_post_language( $tr_id, $lang );

			$translations = pll_get_post_translations( $id );
			$translations[ pll_get_post_language( $id ) ] = $id; // In case this is the first translation created
			$translations[ $lang ] = $tr_id;
			pll_save_post_translations( $translations );
		}

		// Synchronize metas
		PLL()->sync->post_metas->copy( $id, $tr_id, $lang );

		return $tr_id;
	}

	/**
	 * Copy or synchronize resources
	 *
	 * @since 0.6
	 *
	 * @param int    $from ID of the product from which we copy informations.
	 * @param int    $to   ID of the product to which we paste informations.
	 * @param string $lang Language slug.
	 * @param bool   $sync True if it is synchronization, false if it is a copy, defaults to false.
	 */
	public function copy_resources( $from, $to, $lang, $sync = false ) {
		global $wpdb;
		$relationships = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wc_booking_relationships WHERE product_id = %d", $from ), ARRAY_A );

		foreach ( $relationships as $relationship ) {
			$resource       = get_post( $relationship['resource_id'], ARRAY_A ); // wp_insert_post wants an array
			$tr_resource_id = $this->copy_bookable_post( $resource, $to, $lang, $sync );
			if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wc_booking_relationships WHERE product_id = %d AND resource_id = %d", $to, $tr_resource_id ) ) ) {
				unset( $relationship['ID'] );
				$relationship['product_id']  = $to;
				$relationship['resource_id'] = $tr_resource_id;
				$wpdb->insert( "{$wpdb->prefix}wc_booking_relationships", $relationship );
			}
		}
	}

	/**
	 * Copy or synchronize persons types
	 *
	 * @since 0.6
	 *
	 * @param int    $from ID of the product from which we copy informations.
	 * @param int    $to   ID of the product to which we paste informations.
	 * @param string $lang Language slug.
	 * @param bool   $sync True if it is synchronization, false if it is a copy, defaults to false.
	 */
	public function copy_persons( $from, $to, $lang, $sync = false ) {
		$persons = get_children(
			array(
				'post_parent' => $from,
				'post_type'   => 'bookable_person',
			),
			ARRAY_A // wp_insert_post wants an array
		);

		foreach ( $persons as $post ) {
			$tr_id = $this->copy_bookable_post( $post, $to, $lang, $sync );
		}
	}

	/**
	 * Remove resources in translated products when a resource is removed in Ajax
	 *
	 * @since 0.6
	 */
	public function remove_bookable_resource() {
		global $wpdb;

		check_ajax_referer( 'delete-bookable-resource', 'security' );

		if ( isset( $_POST['post_id'], $_POST['resource_id'] ) ) {
			$product_id  = absint( $_POST['post_id'] );
			$resource_id = absint( $_POST['resource_id'] );

			$data_store = PLLWC_Data_Store::load( 'product_language' );

			foreach ( $data_store->get_translations( $product_id ) as $lang => $tr_id ) {
				if ( $tr_id !== $product_id ) { // Let WooCommerce delete the current relationship.
					$tr_resource_id = pll_get_post( $resource_id, $lang );

					$wpdb->delete(
						"{$wpdb->prefix}wc_booking_relationships",
						array(
							'product_id'  => $tr_id,
							'resource_id' => $tr_resource_id,
						)
					);
				}
			}
		}
	}

	/**
	 * Remove person type in translated products when a person type is removed in Ajax
	 *
	 * @since 0.6
	 */
	public function remove_bookable_person() {
		check_ajax_referer( 'delete-bookable-person', 'security' );

		if ( isset( $_POST['person_id'] ) ) {
			$person_type_id = intval( $_POST['person_id'] );
			$person_type    = get_post( $person_type_id );

			if ( $person_type && 'bookable_person' === $person_type->post_type ) {
				foreach ( pll_get_post_translations( $person_type_id ) as $tr_id ) {
					if ( $tr_id !== $person_type_id ) { // Let WooCommerce delete the current person type.
						wp_delete_post( $tr_id );
					}
				}
			}
		}
	}

	/**
	 * Add bookings metas when creating a new product or resource
	 *
	 * @since 0.9.3
	 *
	 * @param int    $post_id      New product or resource.
	 * @param array  $translations Existing product or resource translations.
	 * @param string $meta_key     Meta to add to the booking.
	 */
	protected function add_metas_to_booking( $post_id, $translations, $meta_key ) {
		global $wpdb;

		if ( ! empty( $translations ) ) { // If there is no translation, the query returns all bookings!
			$query = new WP_Query(
				array(
					'fields'     => 'ids',
					'post_type'  => 'wc_booking',
					'lang'       => '',
					'meta_query' => array(
						array(
							'key'     => $meta_key,
							'value'   => $translations,
							'compare' => 'IN',
						),
						array(
							'key'     => $meta_key,
							'value'   => array( $post_id ),
							'compare' => 'NOT IN',
						),
					),
				)
			);

			if ( ! empty( $query->posts ) ) {
				foreach ( $query->posts as $booking ) {
					$values[] = $wpdb->prepare( '( %d, %s, %d )', $booking, $meta_key, $post_id );
				}

				$wpdb->query( "INSERT INTO {$wpdb->postmeta} ( post_id, meta_key, meta_value ) VALUES " . implode( ',', $values ) ); // // PHPCS:ignore WordPress.DB.PreparedSQL.NotPrepared
			}
		}
	}

	/**
	 * Update bookings associated to translated products (or resource)
	 * when creating a new product (or resource translation)
	 *
	 * @since 0.9.3
	 *
	 * @param int    $post_id      Post id.
	 * @param object $post         Post object.
	 * @param array  $translations Post translations.
	 */
	public function save_post( $post_id, $post, $translations ) {
		$translations = array_diff( $translations, array( $post_id ) );

		if ( 'product' === $post->post_type ) {
			$this->add_metas_to_booking( $post_id, $translations, '_booking_product_id' );
		}

		if ( 'bookable_resource' === $post->post_type ) {
			$this->add_metas_to_booking( $post_id, $translations, '_booking_resource_id' );
		}
	}

	/**
	 * Allows to associate several products or resources to a booking
	 *
	 * @since 0.6
	 *
	 * @param null|bool  $r          Returned value (null by default).
	 * @param int        $post_id    Booking id.
	 * @param string     $meta_key   Meta key.
	 * @param int|string $meta_value Meta value.
	 * @return null|bool
	 */
	public function update_post_metadata( $r, $post_id, $meta_key, $meta_value ) {
		static $once = false;

		if ( in_array( $meta_key, array( '_booking_product_id', '_booking_resource_id' ) ) && ! empty( $meta_value ) && ! $once ) {
			$once = true;
			$r = $this->update_post_meta( $post_id, $meta_key, $meta_value );
		}

		$once = false;
		return $r;
	}

	/**
	 * Associates all products in a translation group to a booking
	 *
	 * @since 0.6
	 *
	 * @param int    $post_id    Booking id.
	 * @param string $meta_key   Meta key.
	 * @param int    $meta_value Product id.
	 * @return bool
	 */
	protected function update_post_meta( $post_id, $meta_key, $meta_value ) {
		$values = get_post_meta( $post_id, $meta_key );

		if ( empty( $values ) ) {
			foreach ( pll_get_post_translations( $meta_value ) as $id ) {
				add_post_meta( $post_id, $meta_key, $id );
			}
		} else {
			$to_keep = array_intersect( $values, pll_get_post_translations( $meta_value ) );
			$olds    = array_values( array_diff( $values, $to_keep ) );
			$news    = array_values( array_diff( pll_get_post_translations( $meta_value ), $to_keep ) );
			foreach ( $olds as $k => $old ) {
				update_post_meta( $post_id, $meta_key, $news[ $k ], $old );
			}
		}

		return true;
	}

	/**
	 * Translates persons ids in _booking_persons meta
	 *
	 * @since 0.6
	 *
	 * @param array  $persons  An array of persons.
	 * @param string $language Language slug.
	 * @return array
	 */
	protected function translate_booking_persons_meta( $persons, $language ) {
		if ( ! empty( $persons ) ) {
			foreach ( $persons as $person => $n ) {
				$_persons[ pll_get_post( $person, $language ) ] = $n;
			}
			$persons = $_persons;
		}
		return $persons;
	}

	/**
	 * Allows to get the booking's associated product and resource in the current language
	 *
	 * @since 0.6
	 *
	 * @param null|bool $r        Returned value (null by default).
	 * @param int       $post_id  Booking id.
	 * @param string    $meta_key Meta key.
	 * @param bool      $single   Whether a single meta value has been requested.
	 * @return null|bool
	 */
	public function get_post_metadata( $r, $post_id, $meta_key, $single ) {
		static $once = false;

		if ( ! $once && $single ) {
			switch ( $meta_key ) {
				case '_booking_product_id':
				case '_booking_resource_id':
					$once     = true;
					$value    = get_post_meta( $post_id, $meta_key, true );
					$language = PLL() instanceof PLL_Frontend ? pll_current_language() : pll_get_post_language( $post_id );
					$once     = false;
					return pll_get_post( $value, $language );
				case '_booking_persons':
					$once  = true;
					$value = get_post_meta( $post_id, $meta_key, true );
					$once  = false;
					return array( $this->translate_booking_persons_meta( $value, pll_get_post_language( $post_id ) ) );
			}
		}

		if ( ! $once && empty( $meta_key ) && 'wc_booking' === get_post_type( $post_id ) ) {
			$once     = true;
			$value    = get_post_meta( $post_id );
			$language = PLL() instanceof PLL_Frontend ? pll_current_language() : pll_get_post_language( $post_id );

			foreach ( array( '_booking_product_id', '_booking_resource_id' ) as $key ) {
				if ( ! empty( $value[ $key ] ) ) {
					$value[ $key ] = array( pll_get_post( reset( $value[ $key ] ), $language ) );
				}
			}

			$value['_booking_persons'] = array( serialize( $this->translate_booking_persons_meta( maybe_unserialize( reset( $value['_booking_persons'] ) ), pll_get_post_language( $post_id ) ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions

			$once = false;
			return $value;
		}

		return $r;
	}

	/**
	 * Adds metas to synchronize when saving a product or resource
	 *
	 * @since 0.6
	 *
	 * @param array  $metas List of custom fields names.
	 * @param bool   $sync  True if it is synchronization, false if it is a copy.
	 * @param int    $from  Id of the post from which we copy informations, optional, defaults to null.
	 * @param int    $to    Id of the post to which we paste informations, optional, defaults to null.
	 * @param string $lang  Language slug, optional, defaults to null.
	 * @return array
	 */
	public function copy_post_metas( $metas, $sync, $from, $to, $lang ) {
		$to_sync = array(
			'_wc_display_cost',
			'_wc_booking_base_cost', // Renamed to _wc_booking_block_cost (in 1.10.9?)
			'_wc_booking_block_cost',
			'_wc_booking_cost',
			'_wc_booking_min_duration',
			'_wc_booking_max_duration',
			'_wc_booking_enable_range_picker',
			'_wc_booking_calendar_display_mode',
			'_wc_booking_qty',
			'_wc_booking_has_persons',
			'_wc_booking_person_qty_multiplier',
			'_wc_booking_person_cost_multiplier',
			'_wc_booking_min_persons_group',
			'_wc_booking_max_persons_group',
			'_wc_booking_has_person_types',
			'_wc_booking_has_resources',
			'_wc_booking_resources_assignment',
			'_wc_booking_duration_type',
			'_wc_booking_duration',
			'_wc_booking_duration_unit',
			'_wc_booking_user_can_cancel',
			'_wc_booking_cancel_limit',
			'_wc_booking_cancel_limit_unit',
			'_wc_booking_max_date',
			'_wc_booking_max_date_unit',
			'_wc_booking_min_date',
			'_wc_booking_min_date_unit',
			'_wc_booking_buffer_period',
			'_wc_booking_first_block_time',
			'_wc_booking_requires_confirmation',
			'_wc_booking_default_date_availability',
			'_wc_booking_check_availability_against',
			'_wc_booking_apply_adjacent_buffer',
			'_wc_booking_availability',
			'_wc_booking_pricing',
			'_wc_booking_has_restricted_days', // Since 1.10.7
			'_wc_booking_restricted_days', // Since 1.10.7
			'_resource_base_costs', // To translate
			'_resource_block_costs', // To translate
		);

		// wc_booking_resource_label is automatically copied, not synced as public meta
		return array_merge( $metas, $to_sync );
	}

	/**
	 * Translate a product meta before it is copied or synchronized
	 *
	 * @since 1.0
	 *
	 * @param mixed  $value Meta value.
	 * @param string $key   Meta key.
	 * @param string $lang  Language of target.
	 * @return mixed
	 */
	public function translate_post_meta( $value, $key, $lang ) {
		if ( in_array( $key, array( '_resource_base_costs', '_resource_block_costs' ) ) ) {
			$tr_value = array();
			foreach ( $value as $post_id => $cost ) {
				if ( $tr_id = pll_get_post( $post_id, $lang ) ) {
					$tr_value[ $tr_id ] = $cost;
				}
			}
			$value = $tr_value;
		}
		return $value;
	}

	/**
	 * Translates bookings items in cart
	 * See WC_Booking_Form::get_posted_data()
	 *
	 * @since 0.6
	 *
	 * @param array  $item Cart item.
	 * @param string $lang Language code.
	 * @return array
	 */
	public function translate_cart_item( $item, $lang ) {
		if ( ! empty( $item['booking'] ) ) {
			$booking = &$item['booking'];

			// Translate persons types
			if ( ! empty( $booking['_persons'] ) ) {
				foreach ( $booking['_persons'] as $id => $n ) {
					$tr_id = pll_get_post( $id, $lang );
					$persons[ $tr_id ] = $n;
					unset( $booking[ get_the_title( $id ) ] );
					$booking[ get_the_title( $tr_id ) ] = $n;
				}

				$booking['_persons'] = $persons;
			}

			// Translate resource
			if ( ! empty( $booking['_resource_id'] ) ) {
				$booking['_resource_id'] = $tr_id = pll_get_post( $booking['_resource_id'], $lang );
				$booking['type']         = get_the_title( $tr_id );
			}

			// Translate date
			if ( ! empty( $booking['date'] ) && ! empty( $booking['_date'] ) ) {
				$booking['date'] = date_i18n( wc_date_format(), strtotime( $booking['_date'] ) );
			}

			// Translate time
			if ( ! empty( $booking['time'] ) && ! empty( $booking['_time'] ) ) {
				$booking['time'] = date_i18n( get_option( 'time_format' ), strtotime( "{$booking['_year']}-{$booking['_month']}-{$booking['_day']} {$booking['_time']}" ) );
			}

			// Translate Duration
			if ( ! empty( $booking['duration'] ) && ! empty( $booking['_duration'] ) && $product = wc_get_product( $item['product_id'] ) ) {
				$total_duration = $booking['_duration'] * $product->wc_booking_duration;

				switch ( $booking['_duration_unit'] ) {
					case 'month':
						$booking['duration'] = $total_duration . ' ' . _n( 'month', 'months', $total_duration, 'woocommerce-bookings' );
						break;
					case 'day':
						if ( $total_duration % 7 ) {
							$booking['duration'] = $total_duration . ' ' . _n( 'day', 'days', $total_duration, 'woocommerce-bookings' );
						} else {
							$booking['duration'] = ( $total_duration / 7 ) . ' ' . _n( 'week', 'weeks', $total_duration, 'woocommerce-bookings' );
						}
						break;
					case 'hour':
						$booking['duration'] = $total_duration . ' ' . _n( 'hour', 'hours', $total_duration, 'woocommerce-bookings' );
						break;
					case 'minute':
						$booking['duration'] = $total_duration . ' ' . _n( 'minute', 'minutes', $total_duration, 'woocommerce-bookings' );
						break;
					case 'night':
						$booking['duration'] = $total_duration . ' ' . _n( 'night', 'nights', $total_duration, 'woocommerce-bookings' );
						break;
					default:
						$booking['duration'] = $total_duration;
						break;
				}
			}

			// We need to set the price
			if ( ! empty( $item['data'] ) && ! empty( $booking['_cost'] ) ) {
				$item['data']->set_price( $booking['_cost'] );
			}
		}

		return $item;
	}

	/**
	 * Adds booking to cart item data when translating the cart
	 *
	 * @since 0.7.4
	 *
	 * @param array $cart_item_data Cart item data.
	 * @param array $item           Cart item.
	 * @return array
	 */
	public function add_cart_item_data( $cart_item_data, $item ) {
		if ( isset( $item['booking'] ) ) {
			$cart_item_data['booking'] = $item['booking'];
		}
		return $cart_item_data;
	}

	/**
	 * Filters bookings when sending notifications to get only bookings in the same language as the chosen product
	 *
	 * @since 0.6
	 *
	 * @param object $query WP_Query object.
	 */
	public function filter_bookings_notifications( $query ) {
		$qvars = &$query->query_vars;

		if ( function_exists( 'get_current_screen' ) && ( $screen = get_current_screen() ) && 'wc_booking_page_booking_notification' === $screen->id && 'wc_booking' === $qvars['post_type'] ) {
			$meta_query = reset( $qvars['meta_query'] );
			$query->set( 'lang', pll_get_post_language( $meta_query['value'] ) );
		}
	}

	/**
	 * Returns the translation of the bookings endpoint url
	 *
	 * @since 0.6
	 *
	 * @param string $url  URL of the translation, to modify.
	 * @param string $lang Language slug.
	 * @return string
	 */
	public function pll_translation_url( $url, $lang ) {
		global $wp;

		$endpoint = apply_filters( 'woocommerce_bookings_account_endpoint', 'bookings' );

		if ( isset( PLL()->translate_slugs->slugs_model, $wp->query_vars[ $endpoint ] ) ) {
			$language = PLL()->model->get_language( $lang );
			$url      = wc_get_endpoint_url( $endpoint, '', $url );
			$url      = PLL()->translate_slugs->slugs_model->switch_translated_slug( $url, $language, 'wc_bookings' );
		}

		return $url;
	}

	/**
	 * Adds Booking endpoint to the list of endpoints to translate
	 *
	 * @since 0.6
	 *
	 * @param array $slugs Endpoints slugs.
	 * @return array
	 */
	public function pllwc_endpoints_query_vars( $slugs ) {
		$slugs[] = apply_filters( 'woocommerce_bookings_account_endpoint', 'bookings' );
		return $slugs;
	}

	/**
	 * Disables the languages filter for a customer to see all bookings whatever the languages
	 *
	 * @since 0.6
	 *
	 * @param object $query WP_Query object.
	 */
	public function parse_query( $query ) {
		$qvars = $query->query_vars;

		// Customers should see all their orders whatever the language
		if ( isset( $qvars['post_type'] ) && ( 'wc_booking' === $qvars['post_type'] || ( is_array( $qvars['post_type'] ) && in_array( 'wc_booking', $qvars['post_type'] ) ) ) ) {
			$query->set( 'lang', 0 );
		}
	}
}
