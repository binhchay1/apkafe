<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Content_Tips\Events;
use ahrefs\AhrefsSeo\Data_Api\Data_Metrics_Extended;
use ahrefs\AhrefsSeo\Messages\Message;
use ahrefs\AhrefsSeo\Messages\Message_Error;
use ahrefs\AhrefsSeo\Messages\Message_Error_Single;
use ahrefs\AhrefsSeo\Messages\Message_Tip_Incompatible;
use ahrefs\AhrefsSeo\Workers\Worker;
use ahrefs\AhrefsSeo\Workers\Worker_Backlinks;
use ahrefs\AhrefsSeo\Workers\Worker_Keywords;
use ahrefs\AhrefsSeo\Workers\Worker_Noindex;
use ahrefs\AhrefsSeo\Workers\Worker_Position;
use ahrefs\AhrefsSeo\Workers\Worker_Traffic;
use ahrefs\AhrefsSeo\Features\Duplicated_Keywords;
use ahrefs\AhrefsSeo\Third_Party\Result_Canonical;
use ahrefs\AhrefsSeo\Third_Party\Result_Noindex;
use ahrefs\AhrefsSeo\Third_Party\Result_Redirected;
use ahrefs\AhrefsSeo\Third_Party\Sources;
use Error;
use Exception;

/*
Do nothing if no 'new' snapshot exists.

1. If we have items with initial status (ACTION4_ANALYZING_INITIAL, ACTION4_OUT OF_SCOPE_INITIAL): run update for it.
function init_item() - copy existing detail and assign temporarily status.
Copy keyword, keyword_manual, is_approved_keyword, is_excluded, is_included from existing (current) snapshot (if it exists) to new item.
Assign new temporary status (ACTION4_ANALYZING) or permanent ACTION4_OUT_OF_SCOPE:
if "is_include" = 1 then set ACTION4_ANALYZING.
if "is_exclude" = 1, then set ACTION4_MANUALLY_EXCLUDED;
otherwise replace ACTION4_ANALYZING_INITIAL with ACTION4_ANALYZING, ACTION4_OUT_OF_SCOPE_INITIAL with ACTION4_OUT_OF_SCOPE.

2. If no item with initial status exists - step 2.
Worker classes + function get_unprocessed_item_from_new()
Update items with any status and empty data (traffic, backlinks, keywords, position).
Note: we do not load any details for ACTION4_OUT_OF_SCOPE, ACTION4_ADDED_SINCE_LAST and ACTION4_MANUALLY_EXCLUDED.

3. When all data is filled (traffic, backlinks, keywords, position) for all items (except ACTION4_ADDED_SINCE_LAST):
detect all inactive items (and set corresponding status);
set ACTION4_ERROR_ANALYZING to all items with errors,
set real status for all ACTION4_OUT_OF_SCOPE_ANALYZING items (this way we exclude all inactive items) - obsolete,
set ACTION4_ANALYZING_FINAL to the rest (active) items,

4. calculate and save traffic median using items with ACTION4_ANALYZING_FINAL status only;

5. If traffic median value exists.
Update only items with ACTION4_ANALYZING_FINAL status.
fill recommended action for each item.

6. When no items with ACTION4_ANALYZING_FINAL exists:
update 'new' snapshot to 'current', and existing 'current' to 'old'.

Update progress:
items (ACTION4_ANALYZING, ACTION4_ANALYZING_INITIAL) without (traffic, backlinks, keywords, position) / ( 4 * total items) * 99%;
last percent mean update of statuses.
*/

/**
 * Class for content audit.
 *
 * This code always works with new snapshot.
 * Instance must be created after new snapshot created.
 */
class Content_Audit {

	private const TRANSIENT_NAME = 'ahrefs-content-running7'; // use same for cron and ping requests.
	/** Max time allowed for single content update (seconds). Will exit after this time end. */
	private const MAX_UPDATE_TIME = 15;

	/** Is audit stopped? */
	private const OPTION_AUDIT_STOPPED = 'ahrefs-seo-audit-stopped';
	/** Reason, why it is not possible to run audit. */
	private const OPTION_AUDIT_STOP_REASON           = 'ahrefs-seo-audit-stop-reason';
	private const OPTION_AUDIT_STOP_REASON_SCHEDULED = 'ahrefs-seo-audit-stop-reason-scheduled';

	/**
	 * @var Snapshot
	 */
	protected $snapshot;
	/**
	 * ID of 'new' snapshot.
	 *
	 * @var int|null
	 */
	protected $snapshot_id = null;

	/** @var Ahrefs_Seo_Analytics|null */
	protected $analytics;

	/** @var Ahrefs_Seo_Api|null */
	protected $api;

	/** @var Ahrefs_Seo_Noindex|null */
	protected $noindex;

	/**
	 * @var float|null Minimal waiting time of workers, value in seconds or null if no pause set.
	 */
	protected $workers_waiting_time = null;

	/**
	 * @var bool Set during table_update() method executed, prevent is_duplicated updates.
	 */
	private $table_update_in_progress = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->snapshot    = new Snapshot();
		$this->snapshot_id = $this->snapshot->get_new_snapshot_id();
	}

	/**
	 * Is task already running?
	 *
	 * @since 0.9.2
	 *
	 * @return bool
	 */
	public function is_busy() : bool {
		return ! empty( get_transient( self::TRANSIENT_NAME ) );
	}

	/**
	 * Run table update.
	 * Called on Wizard last step and using "ping" ajax call at the Content Audit screen.
	 *
	 * @param bool $run_from_cron It is running from cron job.
	 * @return bool true Did current run update something?
	 */
	public function update_table( bool $run_from_cron = false ) : bool {
		if ( is_null( $this->snapshot_id ) ) {
			return false; // nothing to update.
		}
		if ( Ahrefs_Seo_Errors::has_stop_error( false ) ) {
			return false;
		}
		$result = false;
		$time   = microtime( true );
		Ahrefs_Seo::breadcrumbs( get_called_class() . '::' . __FUNCTION__ );
		if ( ! $this->is_busy() ) {
			try {
				Ahrefs_Seo::set_time_limit( 300 ); // call it before set transient, because it can update transient time.
				set_transient( self::TRANSIENT_NAME, true, Ahrefs_Seo::transient_time() );
				$this->table_update_in_progress = true;

				try {
					// 1. update initial statuses.
					$initiated = true;
					while ( ! $this->update_initial_statuses() ) {
						if ( $this->maybe_finish( $time ) ) {
							$initiated = false;
							break;
						}
					}
					if ( ! $initiated ) {
						$result = true; // we have more tasks.
					} else {
						if ( $this->has_unprocessed_items() ) {
							$result = true; // return: we have tasks.
							Ahrefs_Seo::ignore_user_abort( true );

							$this->maybe_can_not_proceed();

							// 2. load details for 'analyzing' items.
							// create all workers, this will set max allowed execution time.
							$w_keywords  = ( new Worker_Keywords( $this, $this->get_analytics(), $run_from_cron ) );
							$w_backlinks = ( new Worker_Backlinks( $this, $this->get_api(), $run_from_cron ) );
							$w_traffic   = ( new Worker_Traffic( $this, $this->get_analytics(), $run_from_cron ) );
							$w_noindex   = ( new Worker_Noindex( $this, $this->get_noindex(), $run_from_cron ) );
							$w_position  = ( new Worker_Position( $this, $this->get_analytics(), $run_from_cron ) );

							$has_more_items = true;
							while ( $has_more_items && ! Ahrefs_Seo::should_finish() && ! $this->maybe_finish( $time ) ) {
								// run multi requests, one by one.
								$has_more_items = $w_noindex->execute();
								$has_more_items = $w_keywords->execute() || $has_more_items;
								$has_more_items = $w_backlinks->execute() || $has_more_items;
								$has_more_items = $w_traffic->execute() || $has_more_items;
								$has_more_items = $w_position->execute() || $has_more_items;
								$times          = [ $w_keywords->get_waiting_seconds(), $w_backlinks->get_waiting_seconds(), $w_traffic->get_waiting_seconds(), $w_noindex->get_waiting_seconds(), $w_position->get_waiting_seconds() ];
								$times          = array_filter(
									$times,
									function( $value ) {
										return ! is_null( $value );
									}
								);

								if ( count( $times ) ) {
									$this->workers_waiting_time = (float) min( $times );
								} else {
									$this->workers_waiting_time = null;
								}
								// is it make sense to sleep a bit and continue in current thread?
								if ( ! $has_more_items && ! is_null( $this->workers_waiting_time )
								&& ! Ahrefs_Seo::should_finish( intval( $this->workers_waiting_time + 10 ) )
								&& ( time() - $time + $this->workers_waiting_time <= self::MAX_UPDATE_TIME + 5 ) ) {
									$has_more_items = true;
									Ahrefs_Seo::usleep( intval( 250000 + 1000000 * $this->workers_waiting_time ) );
								}
							}
						} else {
							// we received all details for all analyzing items.
							// 3. update statuses of each inactive item.
							$post_tax = $this->get_unprocessed_item_id_from_prepared();
							if ( ! is_null( $post_tax ) ) {
								$result = true; // return: we have tasks.
								while ( ! is_null( $post_tax ) && ! Ahrefs_Seo::should_finish() && ! $this->maybe_finish( $time ) ) {
									$this->set_recommended_action( $post_tax, true );
									$post_tax = $this->get_unprocessed_item_id_from_prepared();
								}
							} else {
								// 4. calculate and save traffic median.
								$this->maybe_update_snapshot_fields();
								if ( ! is_null( $this->get_traffic_median() ) ) {
									$result = true; // return: we have tasks.
									// 5. update statuses of each item.
									while ( ! Ahrefs_Seo::should_finish() && ! $this->maybe_finish( $time ) ) {
										$post_tax = $this->get_unprocessed_item_id_from_finished();
										if ( ! is_null( $post_tax ) ) {
											$this->set_recommended_action( $post_tax );
										} else {
											// 6. set update is finished, update status of new snapshot.
											if ( ! is_null( $this->snapshot_id ) ) {
												( new Duplicated_Keywords() )->fill_duplicated_for_snapshot( $this->snapshot_id );
												$this->snapshot->set_finished( $this->snapshot_id );
												$result = false; // return: everything updated.
											}
											break;
										}
									}
								} else {
									Ahrefs_Seo::notify( new Exception( 'Empty traffic median.' ) );
								}
							}
						}
					}
					// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
				} catch ( Error $e ) {
					Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__, __( 'Content audit table update failed', 'ahrefs-seo' ) );
				} catch ( Exception $e ) {
					// need to finish and return result.
					Ahrefs_Seo::notify( $e, 'Content audit table update failed' );
				}
			} finally {
				delete_transient( self::TRANSIENT_NAME );
			}
		}
		return $result;
	}

	/**
	 * Remove all post details from DB (content table only) for this snapshot, if this snapshot already exists.
	 * Ignore snapshot_id field of post tax.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return void
	 */
	public function audit_delete_post_details( Post_Tax $post_tax ) : void {
		global $wpdb;
		if ( $this->snapshot_id ) {
			$post_tax->set_snapshot_id( $this->snapshot_id );
			if ( ! is_null( $post_tax->get_snapshot_id() ) ) {
				$wpdb->delete(
					$wpdb->ahrefs_content,
					$post_tax->as_where_array(),
					$post_tax->as_where_format()
				);
			}
		}
	}

	/**
	 * Finish work in allowed time (self::MAX_UPDATE_TIME seconds)
	 *
	 * @param float $initial_time Initial timestamp from microtime(true).
	 * @return bool More that allowed time used.
	 */
	protected function maybe_finish( float $initial_time ) : bool {
		return microtime( true ) - $initial_time >= self::MAX_UPDATE_TIME;
	}

	/**
	 * Return analytics instance
	 *
	 * @return Ahrefs_Seo_Analytics
	 */
	private function get_analytics() : Ahrefs_Seo_Analytics {
		if ( empty( $this->analytics ) ) {
			$this->analytics = Ahrefs_Seo_Analytics::get();
		}
		return $this->analytics;
	}
	/**
	 * Return api instance
	 *
	 * @return Ahrefs_Seo_Api
	 */
	private function get_api() : Ahrefs_Seo_Api {
		if ( empty( $this->api ) ) {
			$this->api = Ahrefs_Seo_Api::get();
		}
		return $this->api;
	}
	/**
	 * Return noindex instance
	 *
	 * @since 0.7.3
	 *
	 * @return Ahrefs_Seo_Noindex
	 */
	private function get_noindex() : Ahrefs_Seo_Noindex {
		if ( empty( $this->noindex ) ) {
			$this->noindex = new Ahrefs_Seo_Noindex();
		}
		return $this->noindex;
	}

	/**
	 * Does content audit require update?
	 *
	 * @return bool true if new snapshot exists?
	 */
	public function require_update() : bool {
		$snapshot     = new Snapshot();
		$snapshot_new = $snapshot->get_new_snapshot_id();
		return ! is_null( $snapshot_new );
	}

	/**
	 * Does content audit have unprocessed items.
	 * The logic of choice is the same as get_unprocessed_item_from_new used.
	 *
	 * @see self::get_unprocessed_item_from_new()
	 *
	 * @return bool true if it has some items pending, false if everything finished.
	 */
	public function has_unprocessed_items() : bool {
		global $wpdb;
		$additional_where   = [];
		$additional_where[] = 'organic is null';
		$additional_where[] = 'backlinks is null';
		$additional_where[] = '(keywords_need_update = 1)';
		$additional_where[] = '(position IS NULL OR position_need_update = 1)';
		$additional_where[] = 'is_noindex IS NULL';
		$additional_where   = ' AND (' . implode( ' OR ', $additional_where ) . ')';
		$additional_where  .= $this->get_where_for_not_noindex_similar_statuses();
		// do not update 'added since' items.
		$sql   = $wpdb->prepare( "SELECT count(*) FROM {$wpdb->ahrefs_content} WHERE snapshot_id = %d $additional_where AND action <> %s AND action <> %s AND action <> %s", $this->snapshot_id, Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE, Ahrefs_Seo_Data_Content::ACTION4_MANUALLY_EXCLUDED ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = (int) $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		Ahrefs_Seo::breadcrumbs( sprintf( '%s::%s: %s', get_called_class(), __FUNCTION__, (string) wp_json_encode( $count ) ) );
		return 0 !== $count;
	}

	/**
	 * Return percent value of unprocessed items.
	 *
	 * @return float 0 mean all is finished, 100 mean nothing proceed.
	 */
	public function content_get_unprocessed_percent() : float {
		global $wpdb;
		if ( is_null( $this->snapshot_id ) ) {
			return 0;
		}
		$additional_where = $this->get_where_for_not_noindex_similar_statuses();
		$count_all        = absint( $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM {$wpdb->ahrefs_content} WHERE snapshot_id = %d AND action <> %s AND action <> %s AND action <> %s $additional_where", $this->snapshot_id, Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE, Ahrefs_Seo_Data_Content::ACTION4_MANUALLY_EXCLUDED ) ) ); // phpcs:ignore -- $additional_where is already escaped.

		if ( 0 === $count_all ) {
			return 1;
		}
		$n = [];
		foreach ( [
			'analytics' => 'organic',
			'backlinks' => 'backlinks',
			'position'  => 'position',
			'noindex'   => 'is_noindex',
		] as $index => $column_name ) {
			$n[ $index ] = absint( $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM {$wpdb->ahrefs_content} WHERE snapshot_id = %d AND $column_name is null $additional_where AND action <> %s AND action <> %s AND action <> %s", $this->snapshot_id, Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE, Ahrefs_Seo_Data_Content::ACTION4_MANUALLY_EXCLUDED ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $column_name pulled from const list, additional_where is already escaped.
		}
		// from 100% to 1% pending, the last percent used for suggested actions assignment.
		return max( 1, floatval( ceil( 990 * ( $n['analytics'] + $n['backlinks'] * 2 + $n['position'] * 4.85 + $n['noindex'] * 0.15 ) / 8 / $count_all ) / 10 ) );
	}

	/**
	 * Copy keywords details from previous snapshot and change status to desired.
	 * Set is_excluded, keyword, keyword_manual, is_approved_keyword column using current snapshot.
	 * Update statuses, include active items from current snapshot, include 'is_included' items.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @param string   $new_action New initial action for it.
	 * @return void
	 */
	private function init_item( Post_Tax $post_tax, string $new_action ) : void {
		global $wpdb;
		Ahrefs_Seo::breadcrumbs( get_called_class() . '::' . __FUNCTION__ . (string) wp_json_encode( [ (string) $post_tax, $new_action ] ) );
		$data                = [];
		$format              = [];
		$current_snapshot_id = $this->snapshot->get_current_snapshot_id(); // new snapshot.

		if ( $post_tax->get_snapshot_id() !== $current_snapshot_id ) { // if we update item not from new snapshot: reset action for same item in new snapshot too.
			// is there any data?
			$data = (array) $wpdb->get_row( $wpdb->prepare( "SELECT keyword, kw_source, keyword_manual, is_approved_keyword, is_excluded, is_included, action, ignore_newly, ignore_noindex, last_well_date FROM {$wpdb->ahrefs_content} WHERE snapshot_id = %d AND post_id = %d AND taxonomy = %s LIMIT 1", $current_snapshot_id, $post_tax->get_post_id(), $post_tax->get_taxonomy() ), ARRAY_A );
			if ( $data ) { // also fill format placeholders for the data.
				if ( '' === $post_tax->get_taxonomy() && $post_tax->is_post( 'page' ) ) { // Note: do not apply 'is_excluded' for pages.
					$data['is_excluded'] = 0;
				}
				$data['kw_source'] = Sources::validate_db_source( $data['kw_source'] );
				$format            = [ '%s', '%s', '%s', '%d', '%d', '%d', /* unset "action" item here, */  '%d', '%d', '%s' ];
				unset( $data['action'] );
			}
		}

		if ( ! empty( $data['is_included'] ) ) {
			// include manually included items.
			$new_action = Ahrefs_Seo_Data_Content::ACTION4_ANALYZING;
		} elseif ( ! empty( $data['is_excluded'] ) ) {
			// exclude manually excluded items.
			$new_action = Ahrefs_Seo_Data_Content::ACTION4_MANUALLY_EXCLUDED;
		}

		$data['action'] = $new_action; // add new action to fields.
		$format[]       = '%s';
		if ( in_array( $new_action, [ Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE, Ahrefs_Seo_Data_Content::ACTION4_MANUALLY_EXCLUDED ], true ) ) {
			$data['inactive'] = 1; // exclude from traffic median calculation.
			$format[]         = '%d';
		}

		Ahrefs_Seo::breadcrumbs( get_called_class() . '::' . __FUNCTION__ . ': ' . (string) wp_json_encode( $data ) );
		$wpdb->update(
			$wpdb->ahrefs_content,
			$data,
			$post_tax->as_where_array(),
			$format,
			$post_tax->as_where_format()
		);
		( new Duplicated_Keywords() )->reset_for_post( $post_tax );
	}

	/**
	 * Update initial statuses to common.
	 * Copy keywords and other details from current snapshot.
	 *
	 * @return bool Everything updated, no need to call again.
	 */
	protected function update_initial_statuses() : bool {
		global $wpdb;
		Ahrefs_Seo::breadcrumbs( get_called_class() . '::' . __FUNCTION__ );

		$items = (array) $wpdb->get_results( $wpdb->prepare( "SELECT post_id, snapshot_id, taxonomy, action FROM {$wpdb->ahrefs_content} WHERE action = %s OR action = %s LIMIT 50", Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_INITIAL, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_INITIAL ), ARRAY_A );
		if ( $items ) {
			foreach ( $items as $item ) {
				$new_action = Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_INITIAL === $item['action'] ? Ahrefs_Seo_Data_Content::ACTION4_ANALYZING : Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE;
				$this->init_item( Post_Tax::create_from_array( $item ), $new_action );
			}
			return false;
		}
		return true;
	}

	/**
	 * Return post ids of unprocessed items, where post status is 'analyzing' and desired parameter what_to_return is missing.
	 * Exclude locked posts.
	 *
	 * @param string $what_to_return Null or one of 'traffic', 'backlinks', 'keywords', 'position', 'isnoindex'.
	 * @param int    $limit Max number of results.
	 *
	 * @return Post_Tax[] List of post ids.
	 */
	public function get_unprocessed_item_from_new( string $what_to_return, int $limit = 10 ) : array {
		global $wpdb;

		$result           = [];
		$additional_where = [];
		switch ( $what_to_return ) {
			case 'traffic':
				$additional_where[] = 'organic IS NULL';
				break;
			case 'backlinks':
				$additional_where[] = 'backlinks IS NULL';
				break;
			case 'keywords':
				$additional_where[] = '(keywords_need_update = 1)';
				break;
			case 'position':
				$additional_where[] = '(position IS NULL OR position_need_update = 1) AND (keywords_need_update != 1)';
				break;
			case 'isnoindex':
				$additional_where[] = 'is_noindex IS NULL';
				break;
		}

		$additional_where  = ' AND (' . implode( ' OR ', $additional_where ) . ')';
		$additional_where .= $this->get_where_for_not_noindex_similar_statuses();
		// no need to update 'added since' items.
		$sql  = $wpdb->prepare( "SELECT post_id, taxonomy, snapshot_id, ( organic IS NULL ) as traffic, (backlinks IS NULL) as backlinks, ( keywords_need_update = 1 ) as keywords, ( position IS NULL OR position_need_update = 1 ) as position, ( is_noindex IS NULL ) as noindex FROM {$wpdb->ahrefs_content} WHERE snapshot_id = %d AND action <> %s AND action <> %s AND action <> %s $additional_where LIMIT %d", $this->snapshot_id, Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE, Ahrefs_Seo_Data_Content::ACTION4_MANUALLY_EXCLUDED, $limit ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$data = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( ! empty( $data ) ) {
			foreach ( $data as $item ) {
				$post_tax = Post_Tax::create_from_array( $item );
				if ( $post_tax->is_tax_or_published() ) {
					$result[] = $post_tax;
				} else {
					$this->delete_not_published_post( $post_tax );
				}
			}
			if ( empty( $result ) ) {
				return $this->get_unprocessed_item_from_new( $what_to_return, $limit ); // call itself again and return result.
			}
		}
		Ahrefs_Seo::breadcrumbs( sprintf( '%s::%s(%s): %s', get_called_class(), __FUNCTION__, $what_to_return, (string) wp_json_encode( Post_Tax::id( $result ) ) ) );
		return $result;
	}

	/**
	 * Get unprocessed item with prepared fields.
	 *
	 * @return Post_Tax|null [post_id, taxonomy] pair or null.
	 */
	protected function get_unprocessed_item_id_from_prepared() : ?Post_Tax {
		global $wpdb;
		if ( is_null( $this->snapshot_id ) ) {
			return null;
		}
		// update "out of scope" or "analyzing" items with error status.
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT post_id, taxonomy, snapshot_id FROM {$wpdb->ahrefs_content} WHERE snapshot_id = %d AND ( action = %s OR action = %s ) AND ( total < 0 OR organic < 0 OR backlinks < 0 OR position < 0 OR is_noindex < 0 ) LIMIT 1",
				$this->snapshot_id,
				Ahrefs_Seo_Data_Content::ACTION4_ANALYZING,
				Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_ANALYZING
			),
			ARRAY_A
		);
		if ( $result ) {
			return Post_Tax::create_from_array( $result );
		}
		// "analyzing" items.
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT post_id, taxonomy, snapshot_id FROM {$wpdb->ahrefs_content} WHERE snapshot_id = %d AND ( action = %s OR action = %s ) LIMIT 1", $this->snapshot_id, Ahrefs_Seo_Data_Content::ACTION4_ANALYZING, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_ANALYZING ), ARRAY_A );
		if ( $result ) {
			return Post_Tax::create_from_array( $result );
		}
		return null;
	}

	/**
	 * Get unprocessed item with prepared fields with ACTION4_ANALYZING_FINAL status
	 *
	 * @return Post_Tax|null [post_id, taxonomy] pair or null.
	 */
	protected function get_unprocessed_item_id_from_finished() : ?Post_Tax {
		global $wpdb;
		if ( is_null( $this->snapshot_id ) ) {
			return null;
		}
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT post_id, taxonomy, snapshot_id FROM {$wpdb->ahrefs_content} WHERE snapshot_id = %d AND ( action = %s OR action = %s )", $this->snapshot_id, Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_FINAL, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_ANALYZING ), ARRAY_A );
		if ( $result ) {
			return Post_Tax::create_from_array( $result );
		}
		return null;
	}

	/**
	 * Update recommended action for items with ACTION4_ANALYZED action status.
	 *
	 * Please update Ahrefs_Seo::CURRENT_CONTENT_RULES value after any changes.
	 *
	 * The priorities for statuses are.
	 * For item being analyzed:
	 * - manually excluded
	 * - error analyzing - if any error happened and any data missing;
	 * - noindex
	 * - non-canonical
	 * - out of scope
	 * - newly published
	 * - any other status
	 *
	 * For any newly added item:
	 * - added since last audit - do not analyze it, until next audit or until user included it.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @param bool     $set_only_inactive_items Update only inactive items.
	 * @return bool Was status of this post updated.
	 */
	protected function set_recommended_action( Post_Tax $post_tax, bool $set_only_inactive_items = false ) : bool {
		global $wpdb;
		if ( ! is_null( $this->snapshot_id ) ) {
			$post_tax->set_snapshot_id( $this->snapshot_id );
			Ahrefs_Seo::breadcrumbs( get_called_class() . '::' . __FUNCTION__ . (string) wp_json_encode( [ (string) $post_tax, $set_only_inactive_items ] ) );
			// load all the details.
			$sql  = $wpdb->prepare( "SELECT date(date_updated) as created, action, total_month as total, organic_month as organic, backlinks, position, is_excluded, is_noindex, is_noncanonical, is_redirected, ignore_newly, ignore_noindex, inactive, keyword FROM {$wpdb->ahrefs_content} WHERE snapshot_id = %d AND post_id = %d AND taxonomy = %s LIMIT 1", $post_tax->get_snapshot_id(), $post_tax->get_post_id(), $post_tax->get_taxonomy() );
			$data = $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			if ( $data && $post_tax->exists() && ( $post_tax->is_tax_or_published() ) ) {
				$old_action       = (string) $data['action'];
				$action           = Ahrefs_Seo_Data_Content::ACTION4_ERROR_ANALYZING;
				$no_total_traffic = is_null( $data['total'] );
				$traffic          = intval( $data['total'] ?: 0 );
				$organic_sessions = intval( $data['organic'] ?: 0 );
				$backlinks        = intval( $data['backlinks'] ?: 0 );
				$position         = floatval( $data['position'] ?: Ahrefs_Seo_Data_Content::POSITION_MAX );
				$published        = ! is_null( $data['created'] ) ? strtotime( $data['created'] ) : 0;
				$ignore_newly     = (bool) $data['ignore_newly']; // ignore newly published.
				$ignore_noindex   = (bool) $data['ignore_noindex']; // ignore noindex, non-canonical, redirected status and include into the scope.
				$noindex          = (int) $data['is_noindex'];
				$is_noncanonical  = (int) $data['is_noncanonical'];
				$is_redirected    = (int) $data['is_redirected'];
				$inactive_prev    = (bool) $data['inactive'] ? 1 : 0;
				/** @var string|null $keyword */
				$keyword = $data['keyword'];

				$waiting_newly  = Ahrefs_Seo_Data_Content::get()->get_waiting_as_timestamp(); // oldest time for being newly published.
				$median_traffic = $this->get_traffic_median();

				if ( (bool) $data['is_excluded'] ) {
					$action = Ahrefs_Seo_Data_Content::ACTION4_MANUALLY_EXCLUDED;
				} elseif ( $traffic < 0 || $organic_sessions < 0 || $backlinks < 0 || $position < 0 || $noindex < 0 || $is_noncanonical < 0 ) { // were there any errors? Ignore possible redirected error.
					$action = Ahrefs_Seo_Data_Content::ACTION4_ERROR_ANALYZING;
				} elseif ( in_array( $old_action, [ Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_ANALYZING ], true ) ) {
					$action = Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE;
				} elseif ( ( 1 === $noindex ) && ! $ignore_noindex ) { // Possible values: -1 (can not detect), 0 (index), 1 (noindex).
					$action = Ahrefs_Seo_Data_Content::ACTION4_NOINDEX_PAGE;
				} elseif ( ( 1 === $is_noncanonical ) && ! $ignore_noindex ) { // Possible values: -1 (can not detect), 0 (false), 1 (url is non-canonical).
					$action = Ahrefs_Seo_Data_Content::ACTION4_NONCANONICAL;
				} elseif ( ( 1 === $is_redirected ) && ! $ignore_noindex ) { // Possible values: -1 (can not detect), 0 (false), 1 (url redirected).
					$action = Ahrefs_Seo_Data_Content::ACTION4_REDIRECTED;
				} elseif ( $no_total_traffic ) { // were there no traffic?
					$action = Ahrefs_Seo_Data_Content::ACTION4_ERROR_ANALYZING;
				} else {
					if ( $published > $waiting_newly && $post_tax->is_post() && ! $ignore_newly ) {
						$action = Ahrefs_Seo_Data_Content::ACTION4_NEWLY_PUBLISHED; // not applicable for taxonomies.
					} elseif ( $position <= 3.5 ) {
						$action = Ahrefs_Seo_Data_Content::ACTION4_DO_NOTHING;
					} elseif ( $position <= 20 ) {
						// Target keyword of post != Target keyword of other post?
						$has_same_keywords = ! is_null( $this->snapshot_id ) ? Ahrefs_Seo_Advisor::get()->has_active_pages_with_same_keywords( $post_tax ) : false;
						$action            = $has_same_keywords ? Ahrefs_Seo_Data_Content::ACTION4_MERGE : Ahrefs_Seo_Data_Content::ACTION4_UPDATE_YELLOW;
					} else { // Position > 20.
						if ( $traffic >= $median_traffic && $median_traffic > 0 && ! ( defined( 'AHREFS_SEO_NO_GA' ) && AHREFS_SEO_NO_GA ) ) { // Traffic of URL >= Median of all traffic, ignore 'Exclude' when median traffic = 0.
							$action = Ahrefs_Seo_Data_Content::ACTION4_EXCLUDE;
						} else { // Traffic of URL < Median of all traffic.
							$action = Ahrefs_Seo_Data_Content::ACTION4_REWRITE;
						}
					}
				}

				$inactive = in_array( $action, [ Ahrefs_Seo_Data_Content::ACTION4_NOINDEX_PAGE, Ahrefs_Seo_Data_Content::ACTION4_NONCANONICAL, Ahrefs_Seo_Data_Content::ACTION4_REDIRECTED, Ahrefs_Seo_Data_Content::ACTION4_MANUALLY_EXCLUDED, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_INITIAL, Ahrefs_Seo_Data_Content::ACTION4_NEWLY_PUBLISHED, Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST, Ahrefs_Seo_Data_Content::ACTION4_ERROR_ANALYZING ], true ) ? 1 : 0;
				if ( $set_only_inactive_items && ! $inactive ) {
					$action = Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_FINAL;
				}
				Ahrefs_Seo::breadcrumbs(
					get_called_class() . '::' . __FUNCTION__ . ': ' . (string) wp_json_encode(
						[
							'post_tax'      => (string) $post_tax,
							'action'        => $action,
							'inactive'      => $inactive,
							'inactive_prev' => $inactive_prev,
						]
					)
				);

				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET action = %s, inactive = %d WHERE snapshot_id = %d AND post_id = %d AND taxonomy = %s", $action, $inactive, $post_tax->get_snapshot_id(), $post_tax->get_post_id(), $post_tax->get_taxonomy() ) );

				// when some new post included into (excluded from) active scope from inactive, update any other active post with the same keyword too.
				// or when new post with duplicated keywords just found (old action was ACTION4_ANALYZING_FINAL).
				if ( ( ! $set_only_inactive_items ) && $inactive_prev !== $inactive || Ahrefs_Seo_Data_Content::ACTION4_MERGE === $action && Ahrefs_Seo_Data_Content::ACTION4_MERGE !== $old_action ) {
					$posts_reanalyze = ! is_null( $this->snapshot_id ) ? Ahrefs_Seo_Advisor::get()->find_active_pages_with_same_keyword( $post_tax ) : null;
					if ( is_array( $posts_reanalyze ) && count( $posts_reanalyze ) ) {
						Ahrefs_Seo::breadcrumbs(
							'posts_reanalyze: ' . (string) wp_json_encode(
								array_map(
									function( $v ) {
										return (string) $v;
									},
									$posts_reanalyze
								)
							)
						);
						foreach ( $posts_reanalyze as $post_tax ) {
							// update recommended action.
							$this->reanalyze_post( $post_tax );
						}
					}
				}
				if ( Ahrefs_Seo_Data_Content::ACTION4_DO_NOTHING === $action ) { // article became well-performing: update "last well done" column.
					Ahrefs_Seo_Db_Helper::set_last_well_date( $post_tax, true );
				} elseif ( in_array( $action, [ Ahrefs_Seo_Data_Content::ACTION4_MANUALLY_EXCLUDED, Ahrefs_Seo_Data_Content::ACTION4_NEWLY_PUBLISHED, Ahrefs_Seo_Data_Content::ACTION4_NOINDEX_PAGE, Ahrefs_Seo_Data_Content::ACTION4_NONCANONICAL, Ahrefs_Seo_Data_Content::ACTION4_REDIRECTED, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE ], true ) ) {
					Ahrefs_Seo_Db_Helper::set_last_well_date( $post_tax, false );
				}

				if ( ! $this->table_update_in_progress ) { // do not update is_duplicated for individual posts during initial update, will do it before snapshot finished in bulk.
					( new Duplicated_Keywords() )->update_is_duplicated( $post_tax, $keyword, (bool) $inactive );
				}

				$snapshot_id = $post_tax->get_snapshot_id();
				if ( ! is_null( $snapshot_id ) ) {
					( new Events() )->on_assign_action( $snapshot_id, $action );
				}

				return true;
			} else {
				// post exists in content audit table, but not exists in posts table; or post is not published.
				Ahrefs_Seo::breadcrumbs( sprintf( '%s::%s: post %d not exists - remove from content table', get_called_class(), __FUNCTION__, (string) $post_tax ) );
				$this->delete_not_published_post( $post_tax );
			}
		}

		return false;
	}

	/**
	 * Update post with position.
	 * Use time range from keywords.
	 *
	 * @param Post_Tax[] $post_taxes Posts or terms list.
	 * @param bool       $fast_update Do not load all details, but load keyword position only.
	 * @return void
	 */
	public function update_post_info_position( array $post_taxes, bool $fast_update = false ) : void {
		Ahrefs_Seo::breadcrumbs( get_called_class() . '::' . __FUNCTION__ . (string) wp_json_encode( func_get_args() ) );
		( new Worker_Position( $this, $this->analytics ) )->update_posts_info( $post_taxes, $fast_update );
	}

	/**
	 * Save traffic values to content table.
	 *
	 * @param Post_Tax    $post_tax Post or term.
	 * @param int|null    $total Total traffic value, since post created/modified time.
	 * @param int         $organic Total organic traffic value, since post created/modified time.
	 * @param int|null    $total_month Monthly amount of total traffic.
	 * @param int         $organic_month Monthly amount of organic traffic.
	 * @param null|string $error Error message if any.
	 * @return void
	 */
	public function update_traffic_values( Post_Tax $post_tax, ?int $total, int $organic, ?int $total_month, int $organic_month, ?string $error = null ) : void {
		global $wpdb;
		if ( ! is_null( $total ) ) {
			$sql = $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET total=%d, organic=%d, total_month=%d, organic_month=%d WHERE snapshot_id = %d AND post_id = %d AND taxonomy = %s", $total, $organic, $total_month, $organic_month, $this->snapshot_id, $post_tax->get_post_id(), $post_tax->get_taxonomy() );
		} else {
			$sql = $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET total=NULL, organic=%d, total_month=NULL, organic_month=%d WHERE snapshot_id = %d AND post_id = %d AND taxonomy = %s", $organic, $organic_month, $this->snapshot_id, $post_tax->get_post_id(), $post_tax->get_taxonomy() );
		}
		$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( ! is_null( $error ) ) {
			$sql = $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET error_traffic = %s WHERE snapshot_id = %d AND post_id = %d AND taxonomy = %s", "$error", $this->snapshot_id, $post_tax->get_post_id(), $post_tax->get_taxonomy() );
			$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	/**
	 * Save backlinks and ref.domains value to content table
	 *
	 * @param Post_Tax              $post_tax Post ID.
	 * @param Data_Metrics_Extended $data Backlinks and ref.domains value.
	 * @param null|string           $error Error message if any.
	 * @return void
	 */
	public function update_backlinks_values( Post_Tax $post_tax, Data_Metrics_Extended $data, ?string $error = null ) : void {
		global $wpdb;
		$backlinks   = ! is_null( $data->get_backlinks() ) ? $data->get_backlinks() : -1;
		$ref_domains = ! is_null( $data->get_ref_domains() ) ? $data->get_ref_domains() : -1;
		$sql         = $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET backlinks=%d, refdomains=%d, error_backlinks = NULL WHERE snapshot_id = %d AND post_id = %d AND taxonomy = %s", $backlinks, $ref_domains, $post_tax->get_snapshot_id(), $post_tax->get_post_id(), $post_tax->get_taxonomy() );
		$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( ! is_null( $error ) ) {
			$sql = $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET error_backlinks = %s WHERE snapshot_id = %d AND post_id = %d AND taxonomy = %s", $error, $post_tax->get_snapshot_id(), $post_tax->get_post_id(), $post_tax->get_taxonomy() );
			$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	/**
	 * Save noindex value to content table
	 *
	 * @param Result_Noindex $result_noindex Noindex result.
	 * @return void
	 */
	public function update_noindex_values( Result_Noindex $result_noindex ) : void {
		global $wpdb;
		$post_tax   = $result_noindex->get_post_tax();
		$is_noindex = $result_noindex->get_is_nonindex();
		$data       = 0 === $is_noindex ? null : (string) wp_json_encode( $result_noindex->as_array() );

		$wpdb->update(
			$wpdb->ahrefs_content,
			[
				'is_noindex'   => $is_noindex,
				'noindex_data' => $data,
			],
			$post_tax->as_where_array(),
			[ '%d', '%s' ],
			$post_tax->as_where_format()
		);
	}

	/**
	 * Save position value to content table.
	 * Reset 'position need update' flag.
	 *
	 * @param Post_Tax    $post_tax Post ID.
	 * @param float|null  $position Position value.
	 * @param null|string $error Error message if any.
	 * @return void
	 */
	public function update_position_values( Post_Tax $post_tax, ?float $position, ?string $error = null ) : void {
		global $wpdb;
		Ahrefs_Seo::breadcrumbs( get_called_class() . '::' . __FUNCTION__ . (string) wp_json_encode( [ (string) $post_tax, $position, $error ] ) );
		if ( is_null( $position ) ) { // is this an error or position not found?
			$position = empty( $error ) ? Ahrefs_Seo_Data_Content::POSITION_MAX : -1;
		}
		$wpdb->update(
			$wpdb->ahrefs_content,
			[
				'position'             => $position,
				'error_position'       => $error,
				'position_need_update' => 0,
			],
			$post_tax->as_where_array(),
			[ '%f', '%s', '%d' ],
			$post_tax->as_where_format()
		);
	}

	/**
	 * Save canonical_url and is_noncanonical values to content table
	 *
	 * @since 0.9.1
	 *
	 * @param Result_Canonical $result_canonical Is non-canonical value value: -1 (error), 0 (false) or 1 (true, post/category url is non-canonical).
	 * @return void
	 */
	public function update_canonical_values( Result_Canonical $result_canonical ) : void {
		global $wpdb;
		$post_tax               = $result_canonical->get_post_tax();
		$original_canonical_url = $result_canonical->get_url_filtered();
		$is_noncanonical        = $result_canonical->get_is_noncanonical();
		$data                   = is_null( $original_canonical_url ) ? null : (string) wp_json_encode( $result_canonical->as_array() );

		$wpdb->update(
			$wpdb->ahrefs_content,
			[
				'is_noncanonical' => $is_noncanonical,
				'canonical_data'  => $data,
			],
			$post_tax->as_where_array(),
			[ '%d', '%s' ],
			$post_tax->as_where_format()
		);
	}

	/**
	 * Save is_redirected values to content table
	 *
	 * @since 0.9.2
	 *
	 * @param Result_Redirected $result_redirected Is redirected value value: -1 (error), 0 (false) or 1 (true, post/category url is redirected).
	 * @return void
	 */
	public function update_redirected_values( Result_Redirected $result_redirected ) : void {
		global $wpdb;
		$post_tax      = $result_redirected->get_post_tax();
		$is_redirected = $result_redirected->get_is_redirected();
		$data          = 0 === $is_redirected ? null : (string) wp_json_encode( $result_redirected->as_array() );

		$wpdb->update(
			$wpdb->ahrefs_content,
			[
				'is_redirected'   => $is_redirected,
				'redirected_data' => $data,
			],
			$post_tax->as_where_array(),
			[ '%d', '%s' ],
			$post_tax->as_where_format()
		);
	}

	/**
	 * Set position value to max value and reset position update flag.
	 * Called when API returned results, but there is no result for current keyword.
	 *
	 * @param Post_Tax[] $post_taxes Post or term.
	 * @return void
	 */
	public function post_positions_set_updated( array $post_taxes ) : void {
		global $wpdb;
		Ahrefs_Seo::breadcrumbs( get_called_class() . '::' . __FUNCTION__ . (string) wp_json_encode( Post_Tax::id( $post_taxes ) ) );
		$placeholders = [];
		$values       = [ Ahrefs_Seo_Data_Content::POSITION_MAX ];
		foreach ( $post_taxes as $post_tax ) {
			$placeholders[] = '(snapshot_id = %d AND post_id = %d AND taxonomy = %s)';
			$values[]       = $post_tax->get_snapshot_id();
			$values[]       = $post_tax->get_post_id();
			$values[]       = $post_tax->get_taxonomy();
		}
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET position = %f, position_need_update = 0 WHERE " . implode( 'OR', $placeholders ), $values ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Get time period for queries.
	 *
	 * @since 0.7.3
	 *
	 * @return int      $days_count Return number of days between start and today dates.
	 */
	public function get_time_period_for() : int {
		$latest_ago = Ahrefs_Seo_Data_Content::get()->get_waiting_as_timestamp();
		$days_count = intval( round( ( time() - $latest_ago ) / DAY_IN_SECONDS ) );
		return max( [ 1, $days_count ] );
	}

	/**
	 * Traffic median calculate using content table traffic.
	 * Exclude inactive and out-of-scope items.
	 *
	 * @return float
	 */
	private function traffic_median_calculate() : float {
		global $wpdb;
		self::audit_clean_pause();
		// Note: do not include items with traffic error (total_month < 0) and excluded items (is_excluded = 1).
		$values = $wpdb->get_col( $wpdb->prepare( "SELECT total_month as traffic FROM {$wpdb->ahrefs_content} WHERE snapshot_id = %d AND total_month >= 0 AND action != %s AND inactive = 0 AND is_excluded = 0 ORDER BY total_month", $this->snapshot_id, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE ) );
		Ahrefs_Seo::breadcrumbs( get_called_class() . '::' . __FUNCTION__ . '(' . $this->snapshot_id . '): ' . (string) wp_json_encode( $values ) );
		if ( empty( $values ) ) {
			return 0;
		}
		$values = array_map( 'intval', $values );
		sort( $values );
		$count  = count( $values );
		$middle = intval( floor( ( $count - 1 ) / 2 ) );

		if ( $count % 2 ) {
			return $values[ $middle ];
		}
		return ( $values[ $middle ] + $values[ $middle + 1 ] ) / 2;
	}

	/**
	 * Return string with error description or null if API is ok.
	 *
	 * @return bool Some account is not connected.
	 */
	public function maybe_can_not_proceed() : bool {
		$no_ahrefs = $this->get_api()->is_disconnected() || $this->get_api()->is_limited_account( true );
		$no_gsc    = ! $this->get_analytics()->is_gsc_set();
		$no_ga     = ! $this->get_analytics()->is_ua_set();

		if ( $no_ahrefs || $no_gsc || $no_ga ) {
			$this->fill_with_errors( $no_ahrefs, $no_gsc, $no_ga );
			return true;
		}
		return false;
	}

	/**
	 * Fill content audit result with errors because some API is missing.
	 *
	 * @param bool $no_ahrefs Ahrefs API is missing.
	 * @param bool $no_gsc GSC API is missing.
	 * @param bool $no_ga GA API is missing.
	 * @return void
	 */
	private function fill_with_errors( bool $no_ahrefs, bool $no_gsc, bool $no_ga ) : void {
		global $wpdb;
		Ahrefs_Seo::breadcrumbs( get_called_class() . '::' . __FUNCTION__ . (string) wp_json_encode( func_get_args() ) );
		if ( $no_ahrefs ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET backlinks = -10, error_backlinks = %s WHERE snapshot_id = %d AND backlinks IS NULL", $this->snapshot_id, 'Ahrefs account is not connected or limited' ) );
		}
		if ( $no_gsc ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET kw_gsc = NULL, position = -1, position_need_update = 0, error_position = %s WHERE snapshot_id = %d AND ( position IS NULL OR position_need_update = 1 )", 'Google Search Console is not connected', $this->snapshot_id ) );
		}
		if ( $no_ga ) {
			if ( $no_ahrefs ) {
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET total = -1, total_month = -1, organic = -1, organic_month = -1, error_traffic = %s WHERE snapshot_id = %d AND ( total IS NULL OR organic IS NULL )", 'Google Analytics is not connected', $this->snapshot_id ) );
			} else { // organic traffic coming from Ahrefs.
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET total = -1, total_month = -1, error_traffic = %s WHERE snapshot_id = %d AND total IS NULL", 'Google Analytics is not connected', $this->snapshot_id ) );
			}
		}
	}

	/**
	 * Calculate traffic median and save into snapshot field.
	 *
	 * @return void
	 */
	private function maybe_update_snapshot_fields() : void {
		$result = $this->get_traffic_median();
		if ( is_null( $result ) && ! is_null( $this->snapshot_id ) ) {
			$result = $this->traffic_median_calculate();
			$this->snapshot->set_traffic_median( $this->snapshot_id, $result );
		}
	}

	/**
	 * Get traffic median
	 *
	 * @return float|null Median value or null.
	 */
	protected function get_traffic_median() : ?float {
		$result = null;
		if ( ! is_null( $this->snapshot_id ) ) {
			$key    = "median{$this->snapshot_id}";
			$result = wp_cache_get( $key, 'ahrefs_seo_audit' );

			if ( is_null( $result ) || false === $result ) {
				$result = $this->snapshot->get_traffic_median( $this->snapshot_id );
				if ( ! is_null( $result ) ) {
					wp_cache_set( $key, $result, 'ahrefs_seo_audit', HOUR_IN_SECONDS );
				}
			}
		}
		return is_null( $result ) ? null : floatval( $result );
	}

	/**
	 * Clear internal cached data
	 */
	public function clear_cache() : void {
		delete_transient( self::TRANSIENT_NAME );
	}

	/**
	 * Reset post errors at content table before update request
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @param bool     $reset_traffic_error Reset traffic error.
	 * @param bool     $reset_backlinks_error Reset backlinks error.
	 * @param bool     $reset_position_error Reset position error.
	 * @return void
	 * @deprecated
	 */
	private function content_reset_post_errors( Post_Tax $post_tax, bool $reset_traffic_error = true, bool $reset_backlinks_error = true, bool $reset_position_error = true ) : void {
		global $wpdb;
		$updates = [];
		$format  = [];
		if ( $reset_traffic_error ) {
			$updates['error_traffic'] = null;
			$format[]                 = [ '%s' ];
		}
		if ( $reset_backlinks_error ) {
			$updates['error_backlinks'] = null;
			$format[]                   = [ '%s' ];
		}
		if ( $reset_position_error ) {
			$updates['error_position'] = null;
			$format[]                  = [ '%s' ];
		}
		if ( count( $updates ) ) {
			$wpdb->update(
				$wpdb->ahrefs_content,
				$updates,
				$post_tax->as_where_array(),
				$format,
				$post_tax->as_where_format()
			);
		}
	}

	/**
	 * Approve keyword and reset position info.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @param string   $approved_keyword Approved keyword.
	 * @param bool     $approve True: approve, false: remove approvement.
	 * @return void
	 */
	public function keyword_approve( Post_Tax $post_tax, string $approved_keyword, bool $approve = true ) : void {
		global $wpdb;
		if ( is_null( $this->snapshot_id ) ) {
			return; // nothing to update.
		}

		// is the same keyword?
		$keyword = Ahrefs_Seo_Keywords::get()->post_keyword_get( $post_tax );
		$data    = [ 'is_approved_keyword' => $approve ? 1 : 0 ];
		$format  = [ '%d' ];
		if ( $keyword !== $approved_keyword ) {
			$data   = [
				'keyword'              => $approved_keyword,
				'position'             => null,
				'error_position'       => null,
				'position_need_update' => 1,
			];
			$format = [ '%s', '%f', '%s', '%d' ];
		}

		$wpdb->update(
			$wpdb->ahrefs_content,
			$data,
			$post_tax->as_where_array(),
			$format,
			$post_tax->as_where_format()
		);
		if ( $keyword !== $approved_keyword ) {
			( new Events() )->on_keyword_changed( $this->snapshot_id );
		}
		( new Events() )->on_keyword_approved( $this->snapshot_id );
	}

	/**
	 * Reanalyze a post using existing info.
	 * Do not make any requests
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return bool Success?
	 */
	public function reanalyze_post( Post_Tax $post_tax ) : bool {
		$result         = false;
		$median_traffic = $this->get_traffic_median();
		if ( ! is_null( $median_traffic ) ) {
			$this->set_recommended_action( $post_tax );
			$result = true;
		}
		return $result;
	}

	/**
	 * Action when user clicked on "add to audit" or similar link, that start the audit of page.
	 * Include item into analysis, or update some fields and run analysis again.
	 *
	 * @param Post_Tax[] $post_taxes Post IDs to include into analysis.
	 * @return Post_Tax[] Errors: list of posts ID, which can not be added.
	 */
	public function audit_include_posts( array $post_taxes ) : array {
		global $wpdb;
		Ahrefs_Seo::breadcrumbs( get_called_class() . '::' . __FUNCTION__ . ': ' . (string) wp_json_encode( Post_Tax::id( $post_taxes ) ) );
		$errors_ids  = []; // result to return.
		$updated_ids = []; // will reset require_update flag of snapshot, if we have some post here.
		if ( ! is_null( $this->snapshot_id ) ) {
			if ( $this->snapshot->get_new_snapshot_id() === $this->snapshot_id && $this->snapshot->get_current_snapshot_id() !== $this->snapshot_id ) {
				// if this is the 'new' snapshot (and current snapshot also exists): simply reinitialize a post using current snapshot.
				// this is a new snapshot, and has another (current) snapshot, to copy initial details from.
				foreach ( $post_taxes as $post_tax ) {
					// copy initial details from existing 'current' snapshot.
					$this->init_item( $post_tax, Ahrefs_Seo_Data_Content::ACTION4_ANALYZING );
					$updated_ids[] = $post_tax;
				}
			} else {
				// Existing logic applied to current snapshot (being viewed by user), current or new (if no current exists).
				foreach ( $post_taxes as $post_tax ) {
					if ( ! $post_tax->is_tax_or_published() ) {
						$errors_ids[] = $post_tax;
						$this->delete_not_published_post( $post_tax );
					} else {
						$action = $post_tax->load_action() ?? Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST;
						switch ( $action ) {
							case Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST:
							case Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE:
							case Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_ANALYZING:
								$wpdb->update(
									$wpdb->ahrefs_content,
									[
										'action'      => Ahrefs_Seo_Data_Content::ACTION4_ANALYZING,
										'is_included' => 1,
									],
									$post_tax->as_where_array(),
									[ '%s', '%d' ],
									$post_tax->as_where_format()
								);
								( new Duplicated_Keywords() )->reset_for_post( $post_tax );
								$updated_ids[] = $post_tax;
								break;
							case Ahrefs_Seo_Data_Content::ACTION4_ANALYZING:
							case Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_FINAL:
							case Ahrefs_Seo_Data_Content::ACTION4_DO_NOTHING:
							case Ahrefs_Seo_Data_Content::ACTION4_EXCLUDE:
							case Ahrefs_Seo_Data_Content::ACTION4_MERGE:
							case Ahrefs_Seo_Data_Content::ACTION4_UPDATE_ORANGE:
							case Ahrefs_Seo_Data_Content::ACTION4_REWRITE:
							case Ahrefs_Seo_Data_Content::ACTION4_UPDATE_YELLOW:
								// Can not add already included to audit post.
								$errors_ids[] = $post_tax;
								break;
							case Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_INITIAL:
							case Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_INITIAL:
								// Initial status. Don't know what to do. Please try again later.
								$errors_ids[]  = $post_tax; // Can not add already included to audit post.
								$updated_ids[] = $post_tax; // but need to analyze it.
								break;
							case Ahrefs_Seo_Data_Content::ACTION4_NOINDEX_PAGE:
							case Ahrefs_Seo_Data_Content::ACTION4_NONCANONICAL:
							case Ahrefs_Seo_Data_Content::ACTION4_REDIRECTED:
								// set is_noindex = null (as well as canonical related fields) and will analyze after data received.
								$wpdb->update(
									$wpdb->ahrefs_content,
									[
										'action'          => Ahrefs_Seo_Data_Content::ACTION4_ANALYZING,
										'is_excluded'     => 0,
										'ignore_noindex'  => 1,
										'is_noindex'      => null,
										'is_noncanonical' => null,
										'is_redirected'   => null,
										'noindex_data'    => null,
										'canonical_data'  => null,
										'redirected_data' => null,
									],
									$post_tax->as_where_array(),
									[ '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s' ],
									$post_tax->as_where_format()
								);
								( new Duplicated_Keywords() )->reset_for_post( $post_tax );
								$updated_ids[] = $post_tax;
								break;
							case Ahrefs_Seo_Data_Content::ACTION4_NEWLY_PUBLISHED:
								// set ignore_newly = 1 and reanalyze immediately.
								$wpdb->update(
									$wpdb->ahrefs_content,
									[
										'action'       => Ahrefs_Seo_Data_Content::ACTION4_ANALYZING,
										'ignore_newly' => 1,
									],
									$post_tax->as_where_array(),
									[ '%s', '%d' ],
									$post_tax->as_where_format()
								);
								( new Duplicated_Keywords() )->reset_for_post( $post_tax );
								$this->reanalyze_post( $post_tax );
								break;
							case Ahrefs_Seo_Data_Content::ACTION4_MANUALLY_EXCLUDED:
								// set is_excluded = 0, is_included = 1 (as we do not want to assign 'out of scope') and reanalyze immediately.
								$wpdb->update(
									$wpdb->ahrefs_content,
									[
										'action'      => Ahrefs_Seo_Data_Content::ACTION4_ANALYZING,
										'is_excluded' => 0,
										'is_included' => 1,
									],
									$post_tax->as_where_array(),
									[ '%s', '%d', '%d' ],
									$post_tax->as_where_format()
								);
								( new Duplicated_Keywords() )->reset_for_post( $post_tax );
								$updated_ids[] = $post_tax;
								break;
							case Ahrefs_Seo_Data_Content::ACTION4_ERROR_ANALYZING:
								// reset all fields and will analyze after data received.
								$this->reset_all_fields( $post_tax );
								$updated_ids[] = $post_tax;
								break;
							default:
								$errors_ids[] = $post_tax;
						}
					}
				}
			}
			// and update everything (update a first item immediately, next items of 'new' snapshot will be updated later using heartbeat or content cron).
			if ( count( $updated_ids ) ) {
				$this->snapshot->set_require_update( (int) $this->snapshot_id ); // this will run Cron Content updates too.
				$this->update_table(); // run first round of updates immediately.
			}
		}
		return $errors_ids;
	}

	/**
	 * Delete not published post from content audit.
	 * It makes no sense to analyze non-published post.
	 *
	 * @since 0.7.5
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return void
	 */
	private function delete_not_published_post( Post_Tax $post_tax ) : void {
		global $wpdb;
		$wpdb->delete(
			$wpdb->ahrefs_content,
			$post_tax->as_where_array(),
			$post_tax->as_where_format()
		);
		$snapshot_id = $post_tax->get_snapshot_id();
		if ( ! is_null( $snapshot_id ) ) {
			( new Events() )->on_assign_action( $snapshot_id, null );
		}
	}

	/**
	 * Action when user clicked on "exclude from audit" or similar link, that stop the audit of page.
	 * Exclude item from analysis.
	 * Can not exclude items with actions:
	 * ACTION4_OUT_OF_SCOPE (has high priority),
	 * ACTION4_MANUALLY_EXCLUDED (already excluded).
	 *
	 * @param Post_Tax[] $post_taxes Post IDs to include into analysis.
	 * @return Post_Tax[] Errors: list of posts ID, which can not be excluded.
	 */
	public function audit_exclude_posts( array $post_taxes ) : array {
		global $wpdb;
		Ahrefs_Seo::breadcrumbs( get_called_class() . '::' . __FUNCTION__ . ': ' . (string) wp_json_encode( Post_Tax::id( $post_taxes ) ) );
		$errors_ids  = []; // result to return.
		$updated_ids = []; // will reset require_update flag of snapshot, if we have some post here.
		if ( ! is_null( $this->snapshot_id ) ) {
			if ( $this->snapshot->get_new_snapshot_id() === $this->snapshot_id && $this->snapshot->get_current_snapshot_id() !== $this->snapshot_id ) {
				// this is a new snapshot, and has another (current) snapshot, to copy initial details from.
				foreach ( $post_taxes as $post_tax ) {
					if ( ! $post_tax->is_tax_or_published() ) {
						$this->delete_not_published_post( $post_tax );
					} else {
						// copy initial details from existing 'current' snapshot.
						$this->init_item( $post_tax, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE );
						$updated_ids[] = $post_tax;
					}
				}
			} else {
				// this is a current or new snapshot (and current is not exists).
				foreach ( $post_taxes as $post_tax ) {
					if ( ! $post_tax->is_tax_or_published() ) {
						$errors_ids[] = $post_tax;
						$this->delete_not_published_post( $post_tax );
					} else {
						$action = $post_tax->load_action();

						switch ( $action ) {
							case Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST:
							case Ahrefs_Seo_Data_Content::ACTION4_ANALYZING:
							case Ahrefs_Seo_Data_Content::ACTION4_DO_NOTHING:
							case Ahrefs_Seo_Data_Content::ACTION4_EXCLUDE:
							case Ahrefs_Seo_Data_Content::ACTION4_MERGE:
							case Ahrefs_Seo_Data_Content::ACTION4_UPDATE_ORANGE:
							case Ahrefs_Seo_Data_Content::ACTION4_REWRITE:
							case Ahrefs_Seo_Data_Content::ACTION4_UPDATE_YELLOW:
							case Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_INITIAL:
							case Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_FINAL:
							case Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_INITIAL:
							case Ahrefs_Seo_Data_Content::ACTION4_NEWLY_PUBLISHED:
							case Ahrefs_Seo_Data_Content::ACTION4_NOINDEX_PAGE:
							case Ahrefs_Seo_Data_Content::ACTION4_NONCANONICAL:
							case Ahrefs_Seo_Data_Content::ACTION4_REDIRECTED:
							case Ahrefs_Seo_Data_Content::ACTION4_ERROR_ANALYZING:
								// set is_excluded, reset is_included, ignore_newly, ignore_noindex and reanalyze immediately using existing data.
								$wpdb->update(
									$wpdb->ahrefs_content,
									[
										'action'         => Ahrefs_Seo_Data_Content::ACTION4_ANALYZING,
										'is_excluded'    => 1,
										'is_included'    => 0,
										'ignore_newly'   => 0,
										'ignore_noindex' => 0,
									],
									$post_tax->as_where_array(),
									[ '%s', '%d', '%d', '%d', '%d' ],
									$post_tax->as_where_format()
								);
								( new Duplicated_Keywords() )->reset_for_post( $post_tax );
								/** @psalm-suppress RedundantCondition -- array includes all excluded actions. */
								if ( ! in_array( $action, [ Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE, Ahrefs_Seo_Data_Content::ACTION4_MANUALLY_EXCLUDED ], true ) ) {
									$this->reanalyze_post( $post_tax ); // update status immediately.
								}
								$updated_ids[] = $post_tax;
								break;
							case Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE:
							case Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_ANALYZING:
							case Ahrefs_Seo_Data_Content::ACTION4_MANUALLY_EXCLUDED:
								$errors_ids[] = $post_tax;
								break;
							default:
								$errors_ids[] = $post_tax;
						}
					}
				}
			}
			// and update everything (update a first item immediately, next items of 'new' snapshot will be updated later using heartbeat or content cron).
			if ( count( $updated_ids ) ) {
				$this->snapshot->set_require_update( (int) $this->snapshot_id ); // this call will turn on Cron Content updates too.
				$this->update_table();
			}
		}
		return $errors_ids;
	}

	/**
	 * Action when user clicked on "Recheck status" or similar link, that recheck status of page.
	 * Recheck status of item.
	 * Can be applied only to items with actions:
	 * ACTION4_NOINDEX_PAGE,
	 * ACTION4_NONCANONICAL,
	 * ACTION4_REDIRECTED
	 *
	 * @param Post_Tax[] $post_taxes Post IDs to recheck statuses.
	 * @return Post_Tax[] Errors: list of posts ID, which can not be processed.
	 */
	public function audit_recheck_posts( array $post_taxes ) : array {
		global $wpdb;
		Ahrefs_Seo::breadcrumbs( get_called_class() . '::' . __FUNCTION__ . ': ' . (string) wp_json_encode( Post_Tax::id( $post_taxes ) ) );
		$errors_ids  = []; // result to return.
		$updated_ids = []; // will reset require_update flag of snapshot, if we have some post here.
		if ( ! is_null( $this->snapshot_id ) ) {
			if ( $this->snapshot->get_new_snapshot_id() === $this->snapshot_id && $this->snapshot->get_current_snapshot_id() !== $this->snapshot_id ) {
				// this is a new snapshot, and has another (current) snapshot, to copy initial details from.
				foreach ( $post_taxes as $post_tax ) {
					if ( ! $post_tax->is_tax_or_published() ) {
						$this->delete_not_published_post( $post_tax );
					} else {
						// copy initial details from existing 'current' snapshot.
						$this->init_item( $post_tax, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE );
						$updated_ids[] = $post_tax;
					}
				}
			} else {
				// this is a current or new snapshot (and current is not exists).
				foreach ( $post_taxes as $post_tax ) {
					if ( ! $post_tax->is_tax_or_published() ) {
						$errors_ids[] = $post_tax;
						$this->delete_not_published_post( $post_tax );
					} else {
						$action = $post_tax->load_action();

						switch ( $action ) {
							case Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST:
							case Ahrefs_Seo_Data_Content::ACTION4_ANALYZING:
							case Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_INITIAL:
							case Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_FINAL:
							case Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_INITIAL:
							case Ahrefs_Seo_Data_Content::ACTION4_NOINDEX_PAGE:
							case Ahrefs_Seo_Data_Content::ACTION4_NONCANONICAL:
							case Ahrefs_Seo_Data_Content::ACTION4_REDIRECTED:
							case Ahrefs_Seo_Data_Content::ACTION4_ERROR_ANALYZING:
								// reset is_noindex, is_noncanonical, is_redirected and reanalyze.
								$wpdb->update(
									$wpdb->ahrefs_content,
									[
										'action'          => Ahrefs_Seo_Data_Content::ACTION4_ANALYZING,
										'is_noindex'      => null,
										'is_noncanonical' => null,
										'is_redirected'   => null,
										'noindex_data'    => null,
										'canonical_data'  => null,
										'redirected_data' => null,
									],
									$post_tax->as_where_array(),
									[ '%s', '%d', '%d', '%d', '%s', '%s', '%s' ],
									$post_tax->as_where_format()
								);
								( new Duplicated_Keywords() )->reset_for_post( $post_tax );
								$updated_ids[] = $post_tax;
								break;
							default:
								$errors_ids[] = $post_tax;
						}
					}
				}
			}
			// and update everything (update a first item immediately, next items of 'new' snapshot will be updated later using heartbeat or content cron).
			if ( count( $updated_ids ) ) {
				$this->snapshot->set_require_update( (int) $this->snapshot_id ); // this call will turn on Cron Content updates too.
				$this->update_table();
			}
		}
		return $errors_ids;
	}

	/**
	 * Reset all fields (inactive, traffic, backlinks, noindex, errors, keywords, position, is_duplicated) of post, set action to analyzing.
	 * Used when we want to reanalyze post with some error.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return bool Was an entry updated.
	 */
	protected function reset_all_fields( Post_Tax $post_tax ) : bool {
		global $wpdb;
		( new Duplicated_Keywords() )->reset_for_post( $post_tax );
		return (bool) $wpdb->update(
			$wpdb->ahrefs_content,
			[
				'action'               => Ahrefs_Seo_Data_Content::ACTION4_ANALYZING,
				'inactive'             => 0,
				'total'                => null,
				'total_month'          => null,
				'organic'              => null,
				'organic_month'        => null,
				'backlinks'            => null,
				'is_noindex'           => null,
				'is_noncanonical'      => null,
				'is_redirected'        => null,

				'noindex_data'         => null,
				'canonical_data'       => null,
				'redirected_data'      => null,
				'error_traffic'        => null,
				'error_backlinks'      => null,
				'error_position'       => null,
				'position_need_update' => 1,
				'keywords_need_update' => 1,
				'kw_gsc'               => null,
				'kw_idf'               => null,
			],
			$post_tax->as_where_array(),
			[ '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s' ],
			$post_tax->as_where_format()
		);
	}

	/**
	 * Return snapshot ID for current content audit.
	 *
	 * @since 0.7.3
	 *
	 * @return null|int Snapshot ID.
	 */
	public function get_snapshot_id() : ?int {
		return $this->snapshot_id;
	}

	/**
	 * Get min waiting time before next content audit can run.
	 *
	 * @since 0.7.3
	 *
	 * @return float|null
	 */
	public function get_waiting_time() : ?float {
		return $this->workers_waiting_time;
	}

	/**
	 * Stop audit because of some permanent reason
	 *
	 * @since 0.7.5
	 *
	 * @param Message[] $messages Messages with a reason, why audit was stopped.
	 * @param bool|null $scheduled_audit These messages are about scheduled audit.
	 * @return void
	 */
	public static function audit_stop( array $messages, ?bool $scheduled_audit = null ) : void {
		$snapshot_id = ( new Content_Audit() )->get_snapshot_id();
		if ( $snapshot_id && is_null( $scheduled_audit ) ) {
			$scheduled_audit = ( new Snapshot() )->is_scheduled_audit( $snapshot_id );
		}

		if ( $scheduled_audit ) { // update message and save all messages.
			$time = $snapshot_id ? ( new Snapshot() )->get_start_time( $snapshot_id ) : time();
			/* translators: %s: is a date */
			$prefix = ! empty( $time ) ? sprintf( __( 'Your scheduled audit on %s did not run as planned.', 'ahrefs-seo' ), gmdate( 'd F Y', $time ) ) : __( 'Your scheduled audit did not run as planned.', 'ahrefs-seo' );
			foreach ( $messages as $message ) {
				$message->add_message_prefix( $prefix . ' ' );
			}
		} else { // save only compatibility errors for manual content audit.
			$messages = array_filter(
				$messages,
				function( $message ) {
					return $message instanceof Message_Tip_Incompatible || $message instanceof Message_Error || $message instanceof Message_Error_Single;
				}
			);
		}
		if ( $scheduled_audit ) {
			update_option( self::OPTION_AUDIT_STOP_REASON_SCHEDULED, $messages );
		} else {
			update_option( self::OPTION_AUDIT_STOP_REASON, $messages );
			update_option( self::OPTION_AUDIT_STOPPED, 1 );
		}
	}

	/**
	 * Is audit paused because of some permanent reason?
	 *
	 * @since 0.7.5
	 *
	 * @return bool
	 */
	public static function audit_is_paused() : bool {
		return ! empty( get_option( self::OPTION_AUDIT_STOPPED ) );
	}

	/**
	 * Get reasons of paused audit.
	 *
	 * @since 0.7.5
	 *
	 * @param bool $with_scheduled_audit Return scheduled audit messages too.
	 * @return Message[]|null
	 */
	public static function audit_get_paused_messages( bool $with_scheduled_audit = false ) : ?array {
		$results = [];
		$result  = apply_filters( 'ahrefs_seo_audit_stop_reason', get_option( self::OPTION_AUDIT_STOP_REASON, [] ), false );
		if ( is_array( $result ) ) {
			$results = $result;
		}
		if ( $with_scheduled_audit ) {
			$result = apply_filters( 'ahrefs_seo_audit_stop_reason', get_option( self::OPTION_AUDIT_STOP_REASON_SCHEDULED, [] ), true );
			if ( is_array( $result ) ) {
				$results = array_merge( $result, $results );
			}
		}
		$results = array_filter(
			$results,
			function( $item ) {
				return $item instanceof Message;
			}
		);
		return ! empty( $results ) ? $results : null;
	}

	/**
	 * Unpause audit
	 *
	 * @since 0.7.5
	 *
	 * @return void
	 */
	public static function audit_clean_pause() : void {
		self::audit_clean_scheduled_message();
		update_option( self::OPTION_AUDIT_STOP_REASON, null );
		update_option( self::OPTION_AUDIT_STOPPED, null );

		$snapshot_id = ( new Content_Audit() )->get_snapshot_id();
		if ( $snapshot_id ) {
			( new Snapshot() )->on_audit_clean_pause( $snapshot_id );
		}
	}
	/**
	 * Cancel the audit
	 *
	 * @since 0.10.2
	 *
	 * @return void
	 */
	public static function audit_cancel() : void {
		self::audit_clean_scheduled_message();
		update_option( self::OPTION_AUDIT_STOP_REASON, __( 'Last audit was cancelled.', 'ahrefs-seo' ) );
		update_option( self::OPTION_AUDIT_STOPPED, null );

		$snapshot_id = ( new Content_Audit() )->get_snapshot_id();
		if ( $snapshot_id ) {
			( new Snapshot() )->set_finished( $snapshot_id, true );
		}
	}
	/**
	 * Clean scheduled audit message
	 *
	 * @since 0.7.5
	 * @return void
	 */
	public static function audit_clean_scheduled_message() : void {
		update_option( self::OPTION_AUDIT_STOP_REASON_SCHEDULED, null );
	}

	/**
	 * Try to resume audit. Will check for compatibility issues
	 *
	 * @since 0.7.5
	 *
	 * @return bool True if audit resumed.
	 */
	public static function audit_resume() : bool {
		if ( Ahrefs_Seo_Errors::has_stop_error( true ) ) {
			return false;
		}
		if ( ! Ahrefs_Seo_Compatibility::quick_compatibility_check() ) { // it will set OPTION_AUDIT_STOP_REASON if failed.
			return false;
		}
		self::audit_clean_pause();
		return true;
	}
	/**
	 * Is audit delayed (some or all workers are paused)?
	 *
	 * @since 0.7.5
	 *
	 * @return bool True if delayed.
	 */
	public static function audit_is_delayed() : bool {
		return Worker::get_max_waiting_time() > 35;
	}


	/**
	 * Get post date using saved in content audit table value.
	 *
	 * @since 0.8.0
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return string Updated date.
	 * @deprecated
	 */
	protected function get_post_date( Post_Tax $post_tax ) : ?string {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT date_updated FROM {$wpdb->ahrefs_content} WHERE snapshot_id = %d AND taxonomy = %s AND post_id = %d", $post_tax->get_snapshot_id(), $post_tax->get_taxonomy(), $post_tax->get_post_id() ) );
	}

	/**
	 * Get where part for is_noindex, is_noncanonical or is_redirected not set.
	 * Exclude definitely noindex, non-canonical, redirected posts/categories from loading any additional info from APIs during content audit run.
	 * Ignore items with "is_included" flag set (they are included into the audit).
	 *
	 * @since 0.9.1
	 *
	 * @return string Escaped string for using in where request.
	 */
	private function get_where_for_not_noindex_similar_statuses() : string {
		global $wpdb;
		return $wpdb->prepare( ' AND ( ( is_noindex <> %d OR is_noindex IS NULL ) AND ( is_noncanonical <> %d OR is_noncanonical IS NULL ) AND ( is_redirected <> %d OR is_redirected IS NULL ) OR ( ignore_noindex = %d ) )', 1, 1, 1, 1 );
	}

	/**
	 * Need to show the First audit screen.
	 *
	 * @since 0.10.2
	 *
	 * @return bool
	 */
	public function show_first_audit() : bool {
		return $this->snapshot->current_snapshot_is_cancelled();
	}
}
