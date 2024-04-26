<?php

namespace ahrefs\AhrefsSeo;

/**
 * Implement work with other cron tasks:
 * Scheduled Audit (by user using options) and Content Audit (when some snapshot require update).
 */
class Ahrefs_Seo_Cron {

	/** @var Ahrefs_Seo_Cron Instance */
	protected static $instance = null;
	/**
	 * @var Cron_Content_Fast
	 */
	protected $content;
	/**
	 * @var Cron_Scheduled_Audit
	 */
	protected $schedule;
	/**
	 * Return the instance
	 *
	 * @return Ahrefs_Seo_Cron
	 */
	public static function get() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->content  = new Cron_Content_Fast( true );
		$this->schedule = new Cron_Scheduled_Audit( true );
	}
	/**
	 * Schedule event, on plugin initialize.
	 * When plugin activated: we add cron task, with interval 1 hour and nearest run in 1 second.
	 */
	public function add_tasks() {
		$this->schedule->update_cron();
	}
	/**
	 * Remove scheduled event, on plugin deactivate.
	 * When plugin is deactivated - we remove our cron task.
	 */
	public function remove_tasks() {
		$this->content->stop_tasks();
		$this->schedule->stop_tasks();
	}
	/**
	 * Start content audit fast updates or change scheduled recurrence/next time.
	 *
	 * @return void
	 */
	public function start_tasks_content() {
		$this->content->start_tasks();
	}
	/**
	 * Update Scheduled audit recurrence or stop it using options.
	 *
	 * @return void
	 */
	public function scheduled_audit_task_update() {
		$this->schedule->update_cron();
	}
}