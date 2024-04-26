<?php

namespace ahrefs\AhrefsSeo\Content_Tips;

use ahrefs\AhrefsSeo\Ahrefs_Seo_Data_Content;
use ahrefs\AhrefsSeo\Snapshot;
/**
 * Class for content audit tips: provide and update source data.
 *
 * @since 0.8.4
 */
class TipData {

	const OPTION_HAS_SUGGESTED  = 'ahrefs-seo-content-tip-has-keywords';
	const OPTION_HAS_DUPLICATED = 'ahrefs-seo-content-tip-has-duplicated';
	const OPTION_HAS_DROPS_WELL = 'ahrefs-seo-content-tip-has-drops-well';
	const OPTION_NOT_FIRST_TIP  = 'ahrefs-seo-content-tip-first-tip-content';
	/**
	 * @var string Prefix for tip closed options. Real option's name is the prefix + tip ID.
	 */
	const OPTION_TIP_CLOSED_PREFIX = 'ahrefs-seo-content-tips-';
	/**
	 * Has suggested keywords?
	 * Load cached value from option.
	 *
	 * @return bool
	 */
	public function has_suggested_keywords() {
		return apply_filters( 'ahrefs_seo_tipdata_has_suggested', (bool) get_option( $this::OPTION_HAS_SUGGESTED, false ) );
	}
	/**
	 * Has duplicated keywords?
	 * Load cached value from option.
	 *
	 * @return bool
	 */
	public function has_duplicated_keywords() {
		return apply_filters( 'ahrefs_seo_tipdata_has_duplicated', (bool) get_option( $this::OPTION_HAS_DUPLICATED, false ) );
	}
	/**
	 * Has dropped (no longer well-performing) articles?
	 * Load cached value from option.
	 *
	 * @return bool
	 */
	public function has_dropped_articles() {
		return apply_filters( 'ahrefs_seo_tipdata_has_drops', (bool) get_option( $this::OPTION_HAS_DROPS_WELL, false ) );
	}
	/**
	 * Last audit expired (was over 3 months ago)
	 *
	 * @return bool
	 */
	public function is_last_audit_expired() {
		$last_audit_time = Ahrefs_Seo_Data_Content::get()->get_last_audit_time();
		return apply_filters( 'ahrefs_seo_tipdata_is_expired', ( is_null( $last_audit_time ) || absint( $last_audit_time - time() ) > 3 * MONTH_IN_SECONDS ) && is_null( ( new Snapshot() )->get_new_snapshot_id() ) );
	}
	/**
	 * Update all options.
	 * Turn on or off "has suggested", "has duplicated" keywords, "has dropped" posts options.
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return void
	 */
	public function update_all_options( $snapshot_id ) {
		update_option( $this::OPTION_HAS_SUGGESTED, $this->calculate_has_suggested_keywords( $snapshot_id ) );
		update_option( $this::OPTION_HAS_DUPLICATED, $this->calculate_has_duplicated_keywords( $snapshot_id ) );
		update_option( $this::OPTION_HAS_DROPS_WELL, $this->calculate_has_drops_well_posts( $snapshot_id ) );
	}
	/**
	 * Check and maybe turn off "has suggested", "has duplicated" keywords, "has dropped" posts options.
	 * It does not update already turned off options.
	 *
	 * @param int  $snapshot_id Snapshot ID.
	 * @param bool $update_suggested Update has suggested keywords.
	 * @param bool $update_duplicated Update has duplicated keywords.
	 * @param bool $update_drops Update has dropped (no longer well-performing) posts.
	 * @return void
	 */
	public function maybe_set_off_options( $snapshot_id, $update_suggested = true, $update_duplicated = true, $update_drops = true ) {
		if ( $update_suggested && $this->has_suggested_keywords() ) { // if option value is off, does not allow to set it on.
			update_option( $this::OPTION_HAS_SUGGESTED, $this->calculate_has_suggested_keywords( $snapshot_id ) );
		}
		if ( $update_duplicated && $this->has_duplicated_keywords() ) {
			update_option( $this::OPTION_HAS_DUPLICATED, $this->calculate_has_duplicated_keywords( $snapshot_id ) );
		}
		if ( $update_drops && $this->has_dropped_articles() ) {
			update_option( $this::OPTION_HAS_DROPS_WELL, $this->calculate_has_drops_well_posts( $snapshot_id ) );
		}
	}
	/**
	 * Update is this a first tip. Set tip is not first on next calls.
	 *
	 * @return void
	 */
	public function update_is_first_tip() {
		$is_not_first_tip = get_option( $this::OPTION_NOT_FIRST_TIP, '' );
		if ( '' === $is_not_first_tip ) {
			update_option( $this::OPTION_NOT_FIRST_TIP, 0 );
		} elseif ( '0' === $is_not_first_tip ) {
			update_option( $this::OPTION_NOT_FIRST_TIP, true );
		}
	}
	/**
	 * Is tip closed? So do not show it to user.
	 *
	 * @param Tip $tip Tip instance.
	 * @return bool
	 */
	public function is_tip_closed( Tip $tip ) {
		return (bool) get_option( $this->get_close_option_name( $tip ), false );
	}
	/**
	 * Set tip closed or not by user.
	 *
	 * @param Tip  $tip Tip instance.
	 * @param bool $is_closed Is tip closed by user.
	 * @return void
	 */
	public function set_tip_closed( Tip $tip, $is_closed ) {
		update_option( $this->get_close_option_name( $tip ), $is_closed );
	}
	/**
	 * This is not first tip
	 *
	 * @return bool
	 */
	public function is_not_first_tip() {
		return (bool) get_option( $this::OPTION_NOT_FIRST_TIP, false );
	}
	/**
	 * Get name of option for tip closed.
	 *
	 * @param Tip $tip Tip instance.
	 * @return string
	 */
	private function get_close_option_name( Tip $tip ) {
		return self::OPTION_TIP_CLOSED_PREFIX . sanitize_title( $tip::ID );
	}
	/**
	 * The snapshot has suggested keywords
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return bool
	 */
	protected function calculate_has_suggested_keywords( $snapshot_id ) {
		return Ahrefs_Seo_Data_Content::get()->calculate_suggested_exists( $snapshot_id );
	}
	/**
	 * The snapshot has duplicated keywords
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return bool
	 */
	protected function calculate_has_duplicated_keywords( $snapshot_id ) {
		return Ahrefs_Seo_Data_Content::get()->calculate_duplicated_exists( $snapshot_id );
	}
	/**
	 * The snapshot has posts drops from being Well Performing
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return bool
	 */
	protected function calculate_has_drops_well_posts( $snapshot_id ) {
		return Ahrefs_Seo_Data_Content::get()->calculate_dropped_count( $snapshot_id ) > 0;
	}
}