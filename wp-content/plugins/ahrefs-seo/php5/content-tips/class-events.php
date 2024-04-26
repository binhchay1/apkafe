<?php

namespace ahrefs\AhrefsSeo\Content_Tips;

use ahrefs\AhrefsSeo\Ahrefs_Seo_Data_Content;
use ahrefs\AhrefsSeo\Snapshot;
/**
 * Tip events handler
 *
 * @since 0.8.4
 */
class Events {

	/**
	 * @var TipData
	 */
	protected $data;
	/**
	 * Constructor
	 *
	 * @param TipData|null $data Tip Data instance to use.
	 */
	public function __construct( TipData $data = null ) {
		$this->data = ! is_null( $data ) ? $data : new TipData();
	}
	/**
	 * Set Tip Data instance to use
	 *
	 * @param TipData $data Tip Data instance.
	 * @return void
	 */
	public function set_data( TipData $data ) {
		$this->data = $data;
	}
	/**
	 * Hide tip. Switch from first tip to subsequent tips
	 *
	 * @param string $tip_id Tip ID.
	 * @return void
	 */
	public function on_closed_by_user( $tip_id ) {
		$tip = Tips::get( $tip_id, $this->data );
		if ( ! is_null( $tip ) ) {
			$tip->hide();
		}
	}
	/**
	 * Maybe activate tips if snapshot has suggested keywords
	 * Called on snapshot finished.
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return void
	 */
	public function on_snapshot_created( $snapshot_id ) {
		Tips::set_all_tips_allowed( $this->data );
		$this->data->update_all_options( $snapshot_id );
		$this->data->update_is_first_tip();
		Tips::maybe_do_not_show_more( $this->data );
	}
	/**
	 * Maybe hide suggested keywords tip.
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return void
	 */
	public function on_keyword_approved( $snapshot_id ) {
		if ( ( new Snapshot() )->get_new_snapshot_id() !== $snapshot_id ) { // do not check if snapshot is new.
			$this->data->maybe_set_off_options( $snapshot_id, true, false, false );
		}
	}
	/**
	 * Maybe hide duplicated keyword tip.
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return void
	 */
	public function on_keyword_changed( $snapshot_id ) {
		if ( ( new Snapshot() )->get_new_snapshot_id() !== $snapshot_id ) { // do not check if snapshot is new.
			$this->data->maybe_set_off_options( $snapshot_id, false, true, false );
		}
	}
	/**
	 * Maybe hide dropped (no longer well-performing) posts tip.
	 *
	 * @param int         $snapshot_id Snapshot ID.
	 * @param string|null $action Action or null if post removed.
	 * @return void
	 */
	public function on_assign_action( $snapshot_id, $action = null ) {
		switch ( $action ) {
			case Ahrefs_Seo_Data_Content::ACTION4_DO_NOTHING:
				if ( ( new Snapshot() )->get_new_snapshot_id() !== $snapshot_id ) { // do not check if snapshot is new.
					$this->data->maybe_set_off_options( $snapshot_id, false, false, true ); // maybe this post was a reason of dropped (no longer well-performing) posts tip?
				}
				break;
			case null: // post removed.
			case Ahrefs_Seo_Data_Content::ACTION4_ERROR_ANALYZING: // or moved to "Excluded" tab.
			case Ahrefs_Seo_Data_Content::ACTION4_MANUALLY_EXCLUDED:
			case Ahrefs_Seo_Data_Content::ACTION4_NEWLY_PUBLISHED:
			case Ahrefs_Seo_Data_Content::ACTION4_NOINDEX_PAGE:
			case Ahrefs_Seo_Data_Content::ACTION4_NONCANONICAL:
			case Ahrefs_Seo_Data_Content::ACTION4_REDIRECTED:
			case Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE:
				if ( ( new Snapshot() )->get_new_snapshot_id() !== $snapshot_id ) {
					$this->data->maybe_set_off_options( $snapshot_id, true, true, true ); // maybe excluded post was a reason of suggested or duplicated tip?
				}
				break;
		}
	}
	/**
	 * Maybe hide or activate tips.
	 *
	 * @return void
	 */
	public function on_wizard_skipped() {
		$snapshot_id = Ahrefs_Seo_Data_Content::get()->snapshot_context_get();
		$this->data->update_all_options( $snapshot_id );
	}
}