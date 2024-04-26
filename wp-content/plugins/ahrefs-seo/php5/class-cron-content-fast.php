<?php

namespace ahrefs\AhrefsSeo;

/**
 * Class for fast content audit updates.
 *
 * Start, stop and change recurrence:
 * - We schedule task on Ahrefs_Seo_Cron::get()->start_tasks_content() call.
 * - We check that content audit is not already running from another thread.
 * - We change task recurrence from fast to slow when no task executed(Ahrefs_Seo_Data_Content::update_table()), but we have pending tasks (Ahrefs_Seo_Data_Content::has_unprocessed_items())
 *   (mean: some post is locked, so we can't update it).
 * - We stop scheduled task when all tasks finished (Ahrefs_Seo_Data_Content::has_unprocessed_items()).
 */
class Cron_Content_Fast extends Cron_Any {

	protected $event_name     = 'ahrefs_seo_cron_content';
	protected $transient_name = 'ahrefs-cron-running-content';
	/**
	 * Execute an update.
	 *
	 * @return bool True if we have more tasks, false if everything finished.
	 */
	public function execute() {
		Ahrefs_Seo::thread_id( 'fast' );
		return ( new Content_Audit_Current() )->maybe_update( true ) || ( new Content_Audit() )->update_table( true );
	}
	/**
	 * Has more tasks, but need to switch to slow mode.
	 *
	 * @return bool True if we have pending tasks.
	 */
	public function has_slow_tasks() {
		return ( new Content_Audit() )->require_update();
	}
	/**
	 * Is task already running?
	 *
	 * @since 0.9.2
	 *
	 * @return bool
	 */
	protected function is_busy() {
		return parent::is_busy() || ( new Content_Audit() )->is_busy();
	}
}