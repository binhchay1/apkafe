<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Messages\Message;
use ahrefs\AhrefsSeo\Features\Duplicated_Keywords;
use Error;
use Exception;
use wpdb;
/**
 * Create or update DB structure when plugin activating or DB version changed.
 */
class Ahrefs_Seo_Db {

	const OPTION_COUNTER      = 'ahrefs-seo-db-update-counter';
	const OPTION_LAST_MESSAGE = 'ahrefs-seo-db-update-message';
	const OPTION_STOP_ERROR   = 'ahrefs-seo-db-update-stop-error'; // error, preventing plugin from correct work.
	const TRANSIENT_UPDATING  = 'ahrefs-seo-db-updating';
	const RESET_AFTER         = 5;
	/**
	 * Create or update DB tables
	 *
	 * @param int $previous_version Previous version of table.
	 * @return bool Successful update
	 */
	public static function create_table( $previous_version ) {
		global $wpdb;
		Ahrefs_Seo::breadcrumbs( sprintf( '%s(%d)', __METHOD__, $previous_version ) );
		$stop_error = self::get_stop_error();
		if ( ! empty( $stop_error ) ) {
			$stop_header = self::get_stop_header();
			Ahrefs_Seo::set_fatal_error( $stop_header, $stop_error, true, true );
			return false;
		}
		$transient_name = self::get_transient_name();
		$transient      = get_transient( $transient_name );
		if ( ! empty( $transient ) ) {
			if ( self::counter_get() > self::RESET_AFTER + 1 && '' !== self::get_last_message() ) {
				Ahrefs_Seo::set_fatal_error( null, self::get_last_message(), false, true );
			} else {
				Ahrefs_Seo::set_fatal_error( null, __( 'We are updating the database right now so please check back on this page in a few minutes. Thank you for your patience!', 'ahrefs-seo' ), false, true );
			}
			return false;
		}
		try {
			set_transient( $transient_name, 1, MINUTE_IN_SECONDS ); // set value immediately.
			$counter = self::counter_increase();
			set_transient( $transient_name, $counter, MINUTE_IN_SECONDS );
			Ahrefs_Seo::breadcrumbs( "Try {$counter}" );
			if ( $counter > self::RESET_AFTER + 1 ) {
				set_transient( $transient_name, $counter, HOUR_IN_SECONDS ); // do update once per hour.
				$previous_version = 1; // do a full reset.
			}
			$result          = true;
			$charset_collate = $wpdb->get_charset_collate();
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			if ( $previous_version > 1 ) {
				$table_content_exists  = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->ahrefs_content}';" );
				$unique_post_id_exists = false;
				if ( $table_content_exists ) {
					$info                  = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->ahrefs_content} where key_name = 'post_id'", ARRAY_A );
					$unique_post_id_exists = empty( $info['Non_unique'] );
					$info                  = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->ahrefs_content} where key_name = 'snapshot_and_post_id'", ARRAY_A );
					if ( ! empty( $info ) ) {
						$wpdb->query( "ALTER TABLE {$wpdb->ahrefs_content} DROP INDEX `snapshot_and_post_id`" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange -- index before 0.8, this is plugin table.
						if ( ! empty( $wpdb->last_error ) && self::check_stop_error( $wpdb->last_error ) ) {
							return false;
						}
					}
					$info = $wpdb->get_results( "SHOW INDEX FROM {$wpdb->ahrefs_content} where key_name = 'PRIMARY'", ARRAY_A );
					if ( is_array( $info ) ) {
						$info = array_map(
							function ( $item ) {
								return isset( $item['Column_name'] ) ? $item['Column_name'] : '';
							},
							$info
						);
						if ( 3 !== count( $info ) || 3 !== count( array_intersect( $info, [ 'snapshot_id', 'post_id', 'taxonomy' ] ) ) ) { // other primary key exists.
							$wpdb->query( "ALTER TABLE {$wpdb->ahrefs_content} DROP INDEX `PRIMARY`" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange -- indexes before 0.8, this is plugin table.
							if ( ! empty( $wpdb->last_error ) && self::check_stop_error( $wpdb->last_error ) ) {
								return false;
							}
						}
					}
				}
				if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}ahrefs_seo_keywords';" ) || $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}ahrefs_seo_backlinks';" ) || $unique_post_id_exists ) {
					$previous_version = 1; // force update if old table exists or has old indexes.
				}
				if ( $table_content_exists ) {
					$len = $wpdb->get_col_length( $wpdb->ahrefs_content, 'keyword' );
					if ( ! empty( $len ) && is_array( $len ) && isset( $len['length'] ) && $len['length'] > 191 ) { // need to update column length.
						$wpdb->query( "ALTER TABLE {$wpdb->ahrefs_content} CHANGE `keyword` `keyword` VARCHAR(191) DEFAULT NULL" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange -- this is plugin table.
						if ( ! empty( $wpdb->last_error ) && self::check_stop_error( $wpdb->last_error ) ) {
							return false;
						}
						$wpdb->flush();
					}
				}
			}
			if ( $previous_version > 0 && $previous_version < 57 ) { // do a full reset and run wizard again.
				if ( ! self::reset_before_v0_7() ) {
					return false;
				}
			}
			$sql   = "CREATE TABLE {$wpdb->ahrefs_content} (
		`post_id` bigint(20) unsigned NOT NULL,
		`taxonomy` varchar(32) NOT NULL DEFAULT '',
		`snapshot_id` int(10) unsigned NOT NULL DEFAULT 0,
		`total` int(10) DEFAULT NULL,
		`total_month` int(10) DEFAULT NULL,
		`organic` int(10) DEFAULT NULL,
		`organic_month` int(10) DEFAULT NULL,
		`backlinks` int(10) DEFAULT NULL,
		`refdomains` int(10) DEFAULT NULL,
		`position` float DEFAULT NULL,
		`action` enum('added_since_last','noindex','manually_excluded','out_of_scope','newly_published','error_analyzing','do_nothing','update_yellow','merge','exclude','update_orange','delete','analyzing','analyzing_initial','out_of_scope_initial','analyzing_final','out_of_scope_analyzing','noncanonical','rewrite','redirected') NOT NULL DEFAULT 'added_since_last',
		`inactive` tinyint(1) NOT NULL DEFAULT '0',
		`is_excluded` tinyint(1) NOT NULL DEFAULT '0',
		`is_included` tinyint(1) NOT NULL DEFAULT '0',
		`is_noindex` tinyint(1) DEFAULT NULL,
		`is_noncanonical` tinyint(1) DEFAULT NULL,
		`is_redirected` tinyint(1) DEFAULT NULL,
		`ignore_newly` tinyint(1) NOT NULL DEFAULT '0',
		`ignore_noindex` tinyint(1) NOT NULL DEFAULT '0',
		`is_approved_keyword` tinyint(1) NOT NULL DEFAULT '0',
		`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`tries` smallint(5) unsigned NOT NULL DEFAULT '0',
		`error_traffic` text NULL DEFAULT NULL,
		`error_backlinks` text NULL DEFAULT NULL,
		`error_position` text NULL DEFAULT NULL,
		`keyword` varchar(191) DEFAULT NULL,
		`keyword_manual` varchar(191) DEFAULT NULL,
		`position_need_update` tinyint(1) NOT NULL DEFAULT '1',
		`keywords_need_update` tinyint(1) NOT NULL DEFAULT '1',
		`last_well_date` date DEFAULT NULL,
		`is_duplicated` int(1) unsigned NOT NULL DEFAULT '0',
		`kw_gsc` text DEFAULT NULL,
		`kw_idf` text DEFAULT NULL,
		`kw_imported` text DEFAULT NULL,
		`kw_pos` text DEFAULT NULL,
		`kw_low` tinyint(1) unsigned DEFAULT NULL,
		`kw_source` enum('no-keyword','too-short','gsc','tf-idf','manual','yoast','aioseo','rankmath') DEFAULT NULL,
		`title` text NOT NULL,
		`noindex_data` text DEFAULT NULL,
		`canonical_data` text DEFAULT NULL,
		`redirected_data` text DEFAULT NULL,
		`badge` varchar(20) NOT NULL DEFAULT '',
		`date_updated` datetime DEFAULT NULL,
		KEY `post_id` (`post_id`),
		PRIMARY KEY (`snapshot_id`, `post_id`, `taxonomy`),
		KEY `snapshot_id` (`snapshot_id`),
		KEY `keyword` (`keyword`),
		KEY `inactive` (`inactive`),
		KEY `last_well_date` (`last_well_date`),
		KEY `action` (`action`),
		KEY `is_duplicated` (`is_duplicated`)
		) {$charset_collate};";
			$s     = dbDelta( $sql );
			$error = $wpdb->last_error;
			$query = $wpdb->last_query;
			if ( ! empty( $error ) && ! self::retry_table_update( $sql, $wpdb->ahrefs_content ) ) {
				$message       = 'Unable to create or update Content table.';
				$message_trans = __( 'Unable to create or update Content table.', 'ahrefs-seo' );
				Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( '%s [%s] [%s] [%s] [%s]', $message, $error, (string) wp_json_encode( $sql ), (string) wp_json_encode( $s ), (string) wp_json_encode( $query ) ) ) ); // initial query, result and error.
				Ahrefs_Seo_Errors::save_message( 'database', "{$message_trans} {$error}", Message::TYPE_ERROR );
				if ( is_null( self::get_stop_error() ) ) {
					self::set_error( __( 'Fatal error on DB tables update.', 'ahrefs-seo' ) . ' ' . $error );
				}
				if ( $counter < self::RESET_AFTER + 2 ) {
					delete_transient( $transient_name );
				}
				return false;
			}
			$sql   = "CREATE TABLE {$wpdb->ahrefs_snapshots} (
		`snapshot_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`time_start` datetime,
		`time_end` datetime DEFAULT NULL,
		`snapshot_status` enum('new','current','old','cancelled') NOT NULL DEFAULT 'new',
		`traffic_median` float DEFAULT NULL,
		`snapshot_version` tinyint(1) unsigned NOT NULL DEFAULT 1,
		`require_update` tinyint(1) unsigned NOT NULL DEFAULT 0,
		`rules_version` tinyint(1) unsigned NOT NULL DEFAULT 4,
		`snapshot_type` enum('manual','scheduled','scheduled_restarted','manual_finished','scheduled_finished') NOT NULL DEFAULT 'manual',
		`country` char(3) NOT NULL DEFAULT '',
		PRIMARY KEY  (`snapshot_id`),
		KEY `snapshot_status` (`snapshot_status`)
		) {$charset_collate};";
			$s     = dbDelta( $sql );
			$error = $wpdb->last_error;
			if ( ! empty( $error ) && ! self::retry_table_update( $sql, $wpdb->ahrefs_snapshots ) ) {
				$message       = 'Unable to create or update Snapshots table.';
				$message_trans = __( 'Unable to create or update Snapshots table.', 'ahrefs-seo' );
				Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( '%s [%s] [%s] [%s]', $message, $error, (string) wp_json_encode( $sql ), (string) wp_json_encode( $s ) ) ) ); // initial query, result and error.
				Ahrefs_Seo_Errors::save_message( 'database', "{$message_trans} {$error}", Message::TYPE_ERROR );
				if ( is_null( self::get_stop_error() ) ) {
					self::set_error( __( 'Fatal error on DB tables update', 'ahrefs-seo' ) . ' ' . $error );
				}
				if ( $counter < self::RESET_AFTER + 2 ) {
					delete_transient( $transient_name );
				}
				$result = false;
			}
			if ( $previous_version > 0 && $previous_version < 63 ) { // fill titles and dates.
				self::update_before_v0_8();
			}
			if ( $previous_version > 0 && $previous_version < 68 ) { // fill last well date.
				self::update_before_v0_8_4();
			}
			if ( $previous_version > 0 && $previous_version < 70 ) { // fill is_duplicated.
				self::update_before_v0_8_5();
			}
			self::counter_reset();
			delete_transient( $transient_name );
		} catch ( Error $e ) {
			Ahrefs_Seo::notify( $e, 'db' );
			self::set_stop_error( 'Error during DB update: ' . $e->getMessage(), $e->getTraceAsString() );
			Ahrefs_Seo::set_fatal_error( 'Error during DB update: ' . $e->getMessage(), $e->getTraceAsString(), true, true );
			return false;
		} catch ( Exception $e ) {
			Ahrefs_Seo::notify( $e, 'db' );
			self::set_stop_error( 'Error during DB update: ' . $e->getMessage(), $e->getTraceAsString() );
			Ahrefs_Seo::set_fatal_error( 'Error during DB update: ' . $e->getMessage(), $e->getTraceAsString(), true, true );
			return false;
		}
		return $result;
	}
	/**
	 * Retry table update.
	 * Put additional errors to breadcrumbs.
	 *
	 * @since 0.7.4
	 *
	 * @param string $sql SQL query.
	 * @param string $table_name Table name.
	 * @return bool
	 */
	private static function retry_table_update( $sql, $table_name ) {
		/** @var wpdb $wpdb */
		global $wpdb;
		$last_error = $wpdb->last_error;
		if ( false !== strpos( $last_error, "Unknown character set: 'utf" ) || false !== strpos( $last_error, "Unknown collation: 'utf" ) ) {
			$original_sql = $sql;
			$sql          = str_replace( 'utf-8', 'utf8mb4', $original_sql );
			$s            = dbDelta( $sql );
			if ( ! empty( $wpdb->last_error ) ) {
				Ahrefs_Seo::breadcrumbs( sprintf( '%s [%s] [%s] [%s] [%s]', 'Retry', $wpdb->last_error, (string) wp_json_encode( $sql ), (string) wp_json_encode( $s ), (string) wp_json_encode( $wpdb->last_query ) ) );
				$charset_collate = $wpdb->get_charset_collate();
				if ( '' !== $charset_collate ) {
					$sql = str_replace( $charset_collate, '', $original_sql ); // do not use it at all.
					$s = dbDelta( $sql );
					if ( ! empty( $wpdb->last_error ) ) {
						Ahrefs_Seo::breadcrumbs( sprintf( '%s [%s] [%s] [%s] [%s]', 'Retry', $wpdb->last_error, (string) wp_json_encode( $sql ), (string) wp_json_encode( $s ), (string) wp_json_encode( $wpdb->last_query ) ) );
					}
				}
			}
			return empty( $wpdb->last_error );
		} elseif ( false !== stripos( $last_error, 'Deadlock found when trying to get lock' ) ) {
			Ahrefs_Seo::usleep( 750000 );
			dbDelta( $sql );
			return empty( $wpdb->last_error );
		} elseif ( preg_match( '!Duplicate entry.*?for key!i', $last_error ) || preg_match( '!Multiple primary key defined!i', $last_error ) ) {
			Ahrefs_Seo::breadcrumbs( sprintf( 'Drop table: %s. Reason: %s', $table_name, $last_error ) );
			$wpdb->query( "DROP TABLE {$table_name}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table is correct table name.
			if ( ! empty( $wpdb->last_error ) ) {
				Ahrefs_Seo::breadcrumbs( sprintf( 'Drop table failed: %s', $wpdb->last_error ) );
				if ( self::check_stop_error( $wpdb->last_error ) ) {
					return false;
				}
			}
			Ahrefs_Seo::usleep( 50000 );
			dbDelta( $sql );
			return empty( $wpdb->last_error );
		} else {
			self::check_stop_error( $wpdb->last_error );
		}
		return false;
	}
	/**
	 * Does last error message have stop error?
	 * Set stop error.
	 *
	 * @since 0.9.0
	 *
	 * @param string $last_error Last error.
	 * @return bool True: stop error found, false: no error.
	 */
	private static function check_stop_error( $last_error ) {
		Ahrefs_Seo::breadcrumbs( sprintf( '%s(%d)', __METHOD__, $last_error ) );
		if ( false !== stripos( $last_error, 'CREATE command denied' ) ) {
			self::set_stop_error(
				sprintf(
				/* translators: %s: text "Ahrefs support" with link */
					esc_html__( "CREATE command denied. The database user doesn't have permission to run CREATE queries. Please ask your server host to grant this permission or contact %s for help.", 'ahrefs-seo' ),
					sprintf( '<a href="%s">%s</a>', esc_attr( Ahrefs_Seo::get_support_url( true ) ), esc_html__( 'Ahrefs support', 'ahrefs-seo' ) )
				),
				$last_error
			);
			return true;
		} elseif ( false !== stripos( $last_error, 'ALTER command denied' ) ) {
			self::set_stop_error(
				sprintf(
				/* translators: %s: text "Ahrefs support" with link */
					esc_html__( "ALTER command denied. The database user doesn't have permission to run ALTER queries. Please ask your server host to grant this permission or contact %s for help.", 'ahrefs-seo' ),
					sprintf( '<a href="%s">%s</a>', esc_attr( Ahrefs_Seo::get_support_url( true ) ), esc_html__( 'Ahrefs support', 'ahrefs-seo' ) )
				),
				$last_error
			);
			return true;
		} elseif ( false !== stripos( $last_error, "Can't DROP" ) || false !== stripos( $last_error, 'DROP command denied' ) ) {
			self::set_stop_error(
				sprintf(
				/* translators: %s: text "Ahrefs support" with link */
					esc_html__( 'DROP command failed. Either ALTER or DROP database actions failed due to a misconfiguration or error on the server. Please check with your server host to fix this or contact %s for help.', 'ahrefs-seo' ),
					sprintf( '<a href="%s">%s</a>', esc_attr( Ahrefs_Seo::get_support_url( true ) ), esc_html__( 'Ahrefs support', 'ahrefs-seo' ) )
				),
				$last_error
			);
			return true;
		} elseif ( false !== stripos( $last_error, 'Duplicate key name' ) ) {
			self::set_stop_error(
				sprintf(
				/* translators: %s: text "Ahrefs support" with link */
					esc_html__( "Duplicate key name error. The database user doesn't have permission to perform ALTER or DROP database actions due to a misconfiguration or error on the server. Please check with your server host to fix this or contact %s for help.", 'ahrefs-seo' ),
					sprintf( '<a href="%s">%s</a>', esc_attr( Ahrefs_Seo::get_support_url( true ) ), esc_html__( 'Ahrefs support', 'ahrefs-seo' ) )
				),
				$last_error
			);
			return true;
		} elseif ( false !== stripos( $last_error, 'Multiple primary key' ) ) {
			self::set_stop_error(
				sprintf(
				/* translators: %s: text "Ahrefs support" with link */
					esc_html__( "Multiple primary key defined. The database user doesn't have permission to perform ALTER or DROP database actions due to a misconfiguration or error on the server. Please check with your server host to fix this or contact %s for help.", 'ahrefs-seo' ),
					sprintf( '<a href="%s">%s</a>', esc_attr( Ahrefs_Seo::get_support_url( true ) ), esc_html__( 'Ahrefs support', 'ahrefs-seo' ) )
				),
				$last_error
			);
			return true;
		} elseif ( false !== stripos( $last_error, 'Unknown column' ) ) {
			self::set_stop_error(
				sprintf(
				/* translators: %s: text "Ahrefs support" with link */
					esc_html__( "Unknown column error. The database user doesn't have permission to perform ALTER or DROP database actions due to a misconfiguration or error on the server. Please check with your server host to fix this or contact %s for help.", 'ahrefs-seo' ),
					sprintf( '<a href="%s">%s</a>', esc_attr( Ahrefs_Seo::get_support_url( true ) ), esc_html__( 'Ahrefs support', 'ahrefs-seo' ) )
				),
				$last_error
			);
			return true;
		}
		return false;
	}
	/**
	 * Do a full reset of options and tables.
	 * Will run Wizard again.
	 *
	 * @since 0.7
	 *
	 * @return bool Success.
	 */
	private static function reset_before_v0_7() {
		global $wpdb;
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange
		// remove old tables.
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}ahrefs_seo_keywords';" ) ) {
			$wpdb->query( "DROP TABLE {$wpdb->prefix}ahrefs_seo_keywords" );
			if ( ! empty( $wpdb->last_error ) && self::check_stop_error( $wpdb->last_error ) ) {
				return false;
			}
		}
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}ahrefs_seo_blacklist';" ) ) {
			$wpdb->query( "DROP TABLE {$wpdb->prefix}ahrefs_seo_blacklist" );
			if ( ! empty( $wpdb->last_error ) && self::check_stop_error( $wpdb->last_error ) ) {
				return false;
			}
		}
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}ahrefs_seo_backlinks';" ) ) {
			$wpdb->query( "DROP TABLE {$wpdb->prefix}ahrefs_seo_backlinks" );
			if ( ! empty( $wpdb->last_error ) && self::check_stop_error( $wpdb->last_error ) ) {
				return false;
			}
		}
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->ahrefs_content}';" ) ) {
			$wpdb->query( "DROP TABLE {$wpdb->ahrefs_content}" );
			if ( ! empty( $wpdb->last_error ) && self::check_stop_error( $wpdb->last_error ) ) {
				return false;
			}
		}
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->ahrefs_snapshots}';" ) ) {
			$wpdb->query( "DROP TABLE {$wpdb->ahrefs_snapshots}" );
			if ( ! empty( $wpdb->last_error ) && self::check_stop_error( $wpdb->last_error ) ) {
				return false;
			}
		}
		self::remove_pre_v0_7_options();
		self::reset_google_accounts();
		self::force_wizard_run();
		return true;
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange
	}
	/**
	 * Remove old options from New Backlinks screen.
	 *
	 * @since 0.7
	 *
	 * @return void
	 */
	private static function remove_pre_v0_7_options() {
		$options = [
			// blacklist.
			'ahrefs-seo-blacklist-has-new',
			'ahrefs-seo-blacklist-last-blacklisted-items',
			'ahrefs-seo-blacklist-last-blacklisted-count',
			// new backlinks.
			'ahrefs-seo-last-backlinks-retrieval',
			'ahrefs-seo-backlinks-low_memory',
			'ahrefs-seo-update-backlink-interval',
			'ahrefs-seo-update-backlink-update-links-count',
			'ahrefs-seo-update-backlinks-has-new-links',
			'ahrefs-seo-links-last-time-scheduled',
			'ahrefs-seo-update-backlink-wizard-finished',
			'ahrefs-seo-update-backlink-i-from',
			'ahrefs-seo-update-backlink-i-to',
			'ahrefs-seo-update-backlink-d-from',
			'ahrefs-seo-update-backlink-d-to',
			'ahrefs-seo-update-backlink-d-last',
			// notice.
			'ahrefs-seo-count-prev-time',
			'ahrefs-seo-has-new-links',
			'ahrefs-seo-admin-notice-hide-gsc',
			// wizard.
			'ahrefs-seo-wizard-audit-start',
		];
		array_walk(
			$options,
			function ( $value ) {
				delete_option( $value );
			}
		);
	}
	/**
	 * Reset currently selected Google GA and GCS accounts.
	 * Do not reset existing tokens.
	 *
	 * @since 0.7.1
	 *
	 * @return void
	 */
	private static function reset_google_accounts() {
		$analytics = Ahrefs_Seo_Analytics::get();
		if ( $analytics->get_data_tokens()->is_token_set() ) {
			$analytics->set_ua( '', '', '', '' );
			wp_cache_flush();
		}
	}
	/**
	 * Force Wizard run
	 *
	 * @since 0.7
	 */
	protected static function force_wizard_run() {
		$options = [ '1', '2', '21', '3', '4' ];
		array_walk(
			$options,
			function ( $option ) {
				delete_option( "ahrefs-seo-is-initialized{$option}" );
			}
		);
		delete_option( 'ahrefs-seo-wizard-1-step' );
	}
	/**
	 * Fill empty date values from posts table.
	 * Update existing snapshots.
	 *
	 * @since 0.8.0
	 *
	 * @return void
	 */
	private static function update_before_v0_8() {
		global $wpdb;
		// replace 'Delete' status by Update (orange).
		$wpdb->update( $wpdb->ahrefs_content, [ 'action' => Ahrefs_Seo_Data_Content::ACTION4_REWRITE ], [ 'action' => 'delete' ], [ '%s' ], [ '%s' ] );
		// fill title, badge and date for existing posts from older DB version.
		$post_ids = $wpdb->get_col( "SELECT DISTINCT post_id FROM {$wpdb->ahrefs_content} WHERE taxonomy = '' AND title = ''" );
		if ( $post_ids ) {
			$values = $wpdb->get_results( "SELECT ID as 'id', post_title as 'title', post_type, post_date FROM {$wpdb->posts} WHERE ID IN (" . implode( ',', array_map( 'intval', $post_ids ) ) . ')', ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			if ( count( $values ) ) {
				foreach ( $values as $value ) {
					$wpdb->update(
						$wpdb->ahrefs_content,
						[
							'title'        => $value['title'],
							'badge'        => $value['post_type'],
							'date_updated' => $value['post_date'],
						],
						[
							'post_id'  => $value['id'],
							'taxonomy' => '',
						],
						[ '%s', '%s', '%s' ],
						[ '%d', '%s' ]
					);
				}
			}
		}
	}
	/**
	 * Fill last well date.
	 * Update existing snapshots.
	 *
	 * @since 0.8.4
	 *
	 * @return void
	 */
	private static function update_before_v0_8_4() {
		global $wpdb;
		$snapshot            = new Snapshot();
		$current_snapshot_id = $snapshot->get_current_snapshot_id();
		$info                = $snapshot->get_snapshot_info( $current_snapshot_id );
		if ( is_string( $info['time_end'] ) ) {
			list($date, $time) = explode( ' ', $info['time_end'], 2 );
			$wpdb->update(
				$wpdb->ahrefs_content,
				[ 'last_well_date' => $date ],
				[
					'action'      => Ahrefs_Seo_Data_Content::ACTION4_DO_NOTHING,
					'snapshot_id' => $current_snapshot_id,
				],
				[ '%s' ],
				[ '%s', '%d' ]
			);
			$new_snapshot_id = $snapshot->get_new_snapshot_id();
			if ( ! is_null( $new_snapshot_id ) && $current_snapshot_id !== $new_snapshot_id ) {
				// copy last well date from current snapshot to new, that is being in progress.
				$well_list = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, taxonomy, snapshot_id FROM {$wpdb->ahrefs_content} WHERE last_well_date IS NOT NULL AND snapshot_id = %d", $current_snapshot_id ), ARRAY_A );
				if ( count( $well_list ) ) {
					foreach ( $well_list as $row ) {
						$post_tax = Post_Tax::create_from_array( $row )->set_snapshot_id( $new_snapshot_id );
						$wpdb->update( $wpdb->ahrefs_content, [ 'last_well_date' => $date ], $post_tax->as_where_array(), [ '%s' ], $post_tax->as_where_format() );
					}
				}
			}
		}
	}
	/**
	 * Fill is_duplicated.
	 * Update existing snapshots.
	 *
	 * @since 0.8.5
	 *
	 * @return void
	 */
	private static function update_before_v0_8_5() {
		$snapshot            = new Snapshot();
		$current_snapshot_id = $snapshot->get_current_snapshot_id();
		$info                = $snapshot->get_snapshot_info( $current_snapshot_id );
		if ( is_string( $info['time_end'] ) ) {
			( new Duplicated_Keywords() )->fill_duplicated_for_snapshot( $current_snapshot_id );
		}
	}
	/**
	 * Get transient name based on current DB version
	 *
	 * @since 0.8.1
	 *
	 * @return string
	 */
	private static function get_transient_name() {
		return self::TRANSIENT_UPDATING . Ahrefs_Seo::CURRENT_TABLE_VERSION;
	}
	/**
	 * Reset counter and last error message
	 *
	 * @since 0.8.1
	 *
	 * @return void
	 */
	private static function counter_reset() {
		update_option( self::OPTION_COUNTER, 0, false );
		update_option( self::OPTION_LAST_MESSAGE, '', false );
	}
	/**
	 * Get updates counter value
	 *
	 * @since 0.8.1
	 *
	 * @return int
	 */
	private static function counter_get() {
		return intval( get_option( self::OPTION_COUNTER, 0 ) );
	}
	/**
	 * Increase and return value of counter
	 *
	 * @since 0.8.1
	 *
	 * @return int
	 */
	private static function counter_increase() {
		$result = self::counter_get() + 1;
		update_option( self::OPTION_COUNTER, $result, false );
		return $result;
	}
	/**
	 * Set error message and save it as current fatal error
	 *
	 * @since 0.8.1
	 *
	 * @param string $error Error message.
	 * @return void
	 */
	private static function set_error( $error ) {
		Ahrefs_Seo::set_fatal_error( null, $error );
		update_option( self::OPTION_LAST_MESSAGE, $error, false );
	}
	/**
	 * Get last saved error message
	 *
	 * @since 0.8.1
	 *
	 * @return string Empty string if no last error.
	 */
	private static function get_last_message() {
		return (string) get_option( self::OPTION_LAST_MESSAGE, '' );
	}
	/**
	 * Set stop error
	 *
	 * @since 0.9.0
	 *
	 * @param string $header The title part.
	 * @param string $message Content of textarea with technical message.
	 * @return void
	 */
	private static function set_stop_error( $header, $message ) {
		update_option(
			self::OPTION_STOP_ERROR,
			[
				'header'  => $header,
				'message' => $message,
			]
		);
		update_option( Ahrefs_Seo::OPTION_TABLE_VERSION, 0 ); // reset table version, so we will try to update DB and show stop error.
	}
	/**
	 * Reset stop error
	 *
	 * @since 0.9.0
	 *
	 * @return void
	 */
	public static function reset_stop_error() {
		delete_transient( self::get_transient_name() );
		delete_option( self::OPTION_STOP_ERROR );
	}
	/**
	 * Get stop error message if any
	 *
	 * @since 0.9.0
	 *
	 * @return string|null
	 */
	private static function get_stop_error() {
		$result = get_option( self::OPTION_STOP_ERROR, null );
		return is_array( $result ) && isset( $result['message'] ) && is_string( $result['message'] ) ? $result['message'] : ( is_string( $result ) ? $result : null );
	}
	/**
	 * Get stop error header if any
	 *
	 * @since 0.9.0
	 *
	 * @return string|null
	 */
	private static function get_stop_header() {
		$result = get_option( self::OPTION_STOP_ERROR, null );
		return is_array( $result ) && isset( $result['header'] ) && is_string( $result['header'] ) ? $result['header'] : null;
	}
}