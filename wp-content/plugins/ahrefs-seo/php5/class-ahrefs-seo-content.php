<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Options\Option_Post;
use ahrefs\AhrefsSeo\Options\Settings_Scope;
/**
 * Base class for content, implement options get and save.
 */
class Ahrefs_Seo_Content {

	const OPTION_WAITING_VALUE   = 'ahrefs-seo-content-waiting-value';
	const OPTION_WAITING_UNITS   = 'ahrefs-seo-content-waiting-units';
	const WAITING_UNIT_MONTH     = 'month';
	const WAITING_UNIT_WEEK      = 'week';
	const DEFAULT_WAITING_UNIT   = 'month';
	const DEFAULT_WAITING_MONTHS = 3;
	const DEFAULT_WAITING_WEEKS  = 12;
	/**
	 * Get waiting time, before we analyze a post.
	 *
	 * @return int Number of weeks.
	 */
	public function get_waiting_value() {
		$result = absint( get_option( self::OPTION_WAITING_VALUE, 0 ) );
		if ( 0 === $result ) {
			$result = self::WAITING_UNIT_MONTH === $this->get_waiting_units() ? self::DEFAULT_WAITING_MONTHS : self::DEFAULT_WAITING_WEEKS;
		}
		if ( $result < 1 ) {
			$result = 1;
		}
		return $result;
	}
	/**
	 * Get waiting time units.
	 *
	 * @since 0.8.6
	 *
	 * @return string "week" or "month".
	 */
	public function get_waiting_units() {
		$result = (string) get_option( self::OPTION_WAITING_UNITS, self::DEFAULT_WAITING_WEEKS );
		if ( ! in_array( $result, [ self::WAITING_UNIT_MONTH, self::WAITING_UNIT_WEEK ], true ) ) {
			$result = self::DEFAULT_WAITING_UNIT;
		}
		return $result;
	}
	/**
	 * Get waiting time as timastamp.
	 *
	 * @since 0.8.6
	 *
	 * @return int Timestamp, oldest time for being newly published.
	 */
	public function get_waiting_as_timestamp() {
		$value = $this->get_waiting_value();
		// week is default.
		$unit = self::WAITING_UNIT_MONTH === $this->get_waiting_units() ? 'month' : 'week'; // for strtotime, do not use class const.
		return (int) strtotime( sprintf( '- %d %s', $value, $unit ) );
	}
	/**
	 * Get waiting time as text.
	 *
	 * @since 0.8.6
	 *
	 * @return string Localized string, "n weeks" or "n months".
	 */
	public function get_waiting_as_text() {
		$value = $this->get_waiting_value();
		$unit  = $this->get_waiting_units();
		/* translators: %s: number of waiting weeks or months */
		return sprintf( 'month' === $unit ? _n( '%s month', '%s months', $value, 'ahrefs-seo' ) : _n( '%s week', '%s weeks', $value, 'ahrefs-seo' ), number_format_i18n( $value ) );
	}
	/**
	 * Get enabled by user custom post types
	 *
	 * @since 0.8.0
	 *
	 * @param bool $with_standard_posts Include post, page and product.
	 * @return string[]
	 */
	public function get_custom_post_types_enabled( $with_standard_posts = false ) {
		$result = Settings_Scope::get()->get_enabled_post_types();
		if ( ! $with_standard_posts ) {
			$result = array_diff( $result, [ 'post', 'page', 'product' ] );
		}
		return array_values( $result );
	}
	/**
	 * Is disabled for "product" post type
	 *
	 * @since 0.8.0
	 *
	 * @return bool
	 */
	public function is_disabled_for_products() {
		return ! Settings_Scope::is_enabled_for_post_type( 'product' );
	}
	/**
	 * Set waiting weeks time
	 *
	 * @param int    $waiting_value Time in weeks. Any post is "Newly published" during this time.
	 * @param string $waiting_units One of self::WAITING_UNIT_* values.
	 * @return void
	 */
	protected function set_waiting_value( $waiting_value, $waiting_units ) {
		update_option( self::OPTION_WAITING_VALUE, max( 1, $waiting_value ) );
		update_option( self::OPTION_WAITING_UNITS, self::WAITING_UNIT_MONTH === $waiting_units ? self::WAITING_UNIT_MONTH : self::WAITING_UNIT_WEEK );
	}
	/**
	 * Set categories checked (included to audit)
	 *
	 * @param string[] $values List of category terms ID as strings list.
	 * @return void
	 */
	protected function set_posts_categories_checked( array $values ) {
		Settings_Scope::get()->set_posts_categories_checked( 'post', 'category', $values );
	}
	/**
	 * Clear internal cached data
	 */
	public function clear_cache() {
	}
	/**
	 * Is product custom post type exists
	 *
	 * @since 0.8.0
	 *
	 * @return bool
	 */
	public function products_exists() {
		return Option_Post::exists( 'product' );
	}
}