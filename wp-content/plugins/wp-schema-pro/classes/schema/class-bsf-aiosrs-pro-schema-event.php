<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Event' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema_Event {

		/**
		 * Render Schema.
		 *
		 * @param  array $data Meta Data.
		 * @param  array $post Current Post Array.
		 * @return array
		 */
		public static function render( $data, $post ) {
			$schema = array();

			$schema['@context'] = 'https://schema.org';

			if ( isset( $data['schema-type'] ) && ! empty( $data['schema-type'] ) ) {
				$schema['@type'] = $data['schema-type'];
			}

			$schema = self::prepare_basics( $schema, $data );
			$schema = self::prepare_attendence_mode( $schema, $data );
			$schema = self::prepare_location_by_attendence_mode( $schema, $data );
			$schema = self::prepare_dates( $schema, $data );
			$schema = self::prepare_offer( $schema, $data );
			$schema = self::prepare_performer( $schema, $data );

			return apply_filters( 'wp_schema_pro_schema_event', $schema, $data, $post );
		}

		/**
		 * Prepare location by attendence mode schema field.
		 *
		 * @param  array $schema schema.
		 * @param  array $data data.
		 * @return array
		 */
		public static function prepare_location_by_attendence_mode( $schema, $data ) {

			if ( 'OnlineEventAttendanceMode' === $data['event-attendance-mode'] ) {
				$schema = self::prepare_location( $schema, $data, false );
			} elseif ( 'OfflineEventAttendanceMode' === $data['event-attendance-mode'] ) {
				$schema = self::prepare_location( $schema, $data, true );
			} else {
				$online_location    = self::prepare_location( $schema, $data, false );
				$offline_location   = self::prepare_location( $schema, $data, true );
				$schema['location'] = array( $online_location['location'], $offline_location['location'] );
			}

			return $schema;
		}

		/**
		 * Prepare location schema field.
		 *
		 * @param  array   $schema schema.
		 * @param  array   $data data.
		 * @param  boolean $offline offline.
		 * @return array
		 */
		public static function prepare_location( $schema, $data, $offline = true ) {

			if ( $offline ) {
				if ( isset( $data['location'] ) && ! empty( $data['location'] ) ) {
					$schema['location']['@type'] = 'Place';
					$schema['location']['name']  = esc_html( wp_strip_all_tags( $data['location'] ) );
				}

				$schema['location']['@type']            = 'Place';
				$schema['location']['address']['@type'] = 'PostalAddress';
				if ( isset( $data['location-street'] ) && ! empty( $data['location-street'] ) ) {
					$schema['location']['address']['streetAddress'] = esc_html( wp_strip_all_tags( $data['location-street'] ) );
				}
				if ( isset( $data['location-locality'] ) && ! empty( $data['location-locality'] ) ) {
					$schema['location']['address']['addressLocality'] = esc_html( wp_strip_all_tags( $data['location-locality'] ) );
				}
				if ( isset( $data['location-postal'] ) && ! empty( $data['location-postal'] ) ) {
					$schema['location']['address']['postalCode'] = esc_html( wp_strip_all_tags( $data['location-postal'] ) );
				}
				if ( isset( $data['location-region'] ) && ! empty( $data['location-region'] ) ) {
					$schema['location']['address']['addressRegion'] = esc_html( wp_strip_all_tags( $data['location-region'] ) );
				}
				if ( isset( $data['location-country'] ) && ! empty( $data['location-country'] ) ) {

					$schema['location']['address']['addressCountry']['@type'] = 'Country';
					$schema['location']['address']['addressCountry']['name']  = esc_html( wp_strip_all_tags( $data['location-country'] ) );
				}
			} else {
				$schema['location']['@type'] = 'VirtualLocation';
				$schema['location']['url']   = esc_url( $data['online-location'] );
			}
			return $schema;
		}

		/**
		 * Prepare Offer schema field.
		 *
		 * @param  array $schema schema.
		 * @param  array $data data.
		 * @return array
		 */
		public static function prepare_offer( $schema, $data ) {

			$schema['offers']['@type'] = 'Offer';
			$schema['offers']['price'] = '0';
			if ( isset( $data['price'] ) && ! empty( $data['price'] ) ) {
				$schema['offers']['price'] = esc_html( wp_strip_all_tags( $data['price'] ) );
			}
			if ( isset( $data['avail'] ) && ! empty( $data['avail'] ) ) {
				$schema['offers']['availability'] = esc_html( wp_strip_all_tags( $data['avail'] ) );
			}
			if ( isset( $data['currency'] ) && ! empty( $data['currency'] ) ) {
				$schema['offers']['priceCurrency'] = esc_html( wp_strip_all_tags( $data['currency'] ) );
			}
			if ( isset( $data['valid-from'] ) && ! empty( $data['valid-from'] ) ) {
				$schema['offers']['validFrom'] = esc_html( wp_strip_all_tags( $data['valid-from'] ) );
			}
			if ( isset( $data['ticket-buy-url'] ) && ! empty( $data['ticket-buy-url'] ) ) {
				$schema['offers']['url'] = esc_url( $data['ticket-buy-url'] );
			}

			return $schema;

		}

		/**
		 * Prepare Performer schema field.
		 *
		 * @param  array $schema schema.
		 * @param  array $data data.
		 * @return array
		 */
		public static function prepare_performer( $schema, $data ) {

			if ( isset( $data['performer'] ) && ! empty( $data['performer'] ) ) {
				$schema['performer']['@type'] = 'Person';
				$schema['performer']['name']  = esc_html( wp_strip_all_tags( $data['performer'] ) );
			}
			return $schema;
		}

		/**
		 * Prepare dates schema field.
		 *
		 * @param  array $schema schema.
		 * @param  array $data data.
		 * @return array
		 */
		public static function prepare_dates( $schema, $data ) {

			if ( isset( $data['start-date'] ) && ! empty( $data['start-date'] ) ) {
				$schema['startDate'] = esc_html( wp_strip_all_tags( $data['start-date'] ) );
			}

			if ( isset( $data['end-date'] ) && ! empty( $data['end-date'] ) ) {
				$schema['endDate'] = esc_html( wp_strip_all_tags( $data['end-date'] ) );
			}

			if ( 'EventRescheduled' === $data['event-status'] ) {
				$schema['previousStartDate'] = esc_html( wp_strip_all_tags( $data['previous-date'] ) );
			}

			return $schema;
		}

		/**
		 * Prepare attendence schema field.
		 *
		 * @param  array $schema schema.
		 * @param  array $data data.
		 * @return array
		 */
		public static function prepare_attendence_mode( $schema, $data ) {
			if ( isset( $data['schema-type'] ) && isset( $data['event-attendance-mode'] ) && 'Event' === $data['schema-type'] && ! empty( $data['event-attendance-mode'] ) ) {

				$schema['eventAttendanceMode'] = 'https://schema.org/' . esc_html( wp_strip_all_tags( $data['event-attendance-mode'] ) );
			}

			return $schema;
		}

		/**
		 * Prepare basic schema field.
		 *
		 * @param  array $schema schema.
		 * @param  array $data data.
		 * @return array
		 */
		public static function prepare_basics( $schema, $data ) {

			if ( isset( $data['name'] ) && ! empty( $data['name'] ) ) {
				$schema['name'] = esc_html( wp_strip_all_tags( $data['name'] ) );
			}

			if ( isset( $data['event-status'] ) && ! empty( $data['event-status'] ) ) {
				$schema['eventStatus'] = 'https://schema.org/' . esc_html( wp_strip_all_tags( $data['event-status'] ) );
			}

			if ( isset( $data['image'] ) && ! empty( $data['image'] ) ) {
				$schema['image'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['image'] );
			}

			if ( isset( $data['description'] ) && ! empty( $data['description'] ) ) {
				$schema['description'] = esc_html( wp_strip_all_tags( $data['description'] ) );
			}

			return $schema;
		}
	}
}
