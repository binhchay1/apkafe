<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo;

/**
 * Class for content audit for any snapshot.
 *
 * Both of these are only int, not null (value assigned at the constructor):
 *
 * @property int $snapshot_id
 * @method int get_snapshot_id()
 */
class Content_Audit_Current extends Content_Audit {

	/**
	 * Constructor
	 *
	 * @param int|null $snapshot_id Snapshot ID to bind the instance. Default is 'current' snapshot.
	 */
	public function __construct( ?int $snapshot_id = null ) {
		parent::__construct();
		if ( is_null( $snapshot_id ) ) {
			// snapshot of current view.
			$snapshot_id = Ahrefs_Seo_Data_Content::get()->snapshot_context_get();
		}
		$this->snapshot_id = $snapshot_id;
	}

	/**
	 * Update items, that require update.
	 * Ignore if the snapshot is new.
	 *
	 * @param bool $run_from_cron It is running from cron job.
	 * @return bool Was something updated.
	 */
	public function maybe_update( bool $run_from_cron = false ) : bool {
		// current snapshot <> new & require update.
		if ( $this->snapshot->get_new_snapshot_id() === $this->snapshot_id || ! $this->require_update() ) {
			return false;
		}
		return $this->update_table( $run_from_cron );
	}

	/**
	 * Does content audit require update?
	 *
	 * @return bool true if current content audit require update.
	 */
	public function require_update() : bool {
		return $this->snapshot->is_require_update( $this->snapshot_id );
	}

}
