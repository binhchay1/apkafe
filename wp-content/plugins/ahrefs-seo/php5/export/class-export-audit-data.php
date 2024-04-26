<?php

namespace ahrefs\AhrefsSeo\Export;

use ahrefs\AhrefsSeo\Ahrefs_Seo;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Data_Content;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Screen_Content;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Screen_Settings;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Table_Content;
use ahrefs\AhrefsSeo\Links;
use ahrefs\AhrefsSeo\Post_Tax;
use ahrefs\AhrefsSeo\Snapshot;
use ZipArchive;
/**
 * Export last audit data
 *
 * @since 0.9.2
 */
class Export_Audit_Data {

	/** Action name. Used in nonce checks. */
	const ACTION = 'ahrefs_seo_export';
	/** @var string|null Error message. */
	private $error_message = null;
	/**
	 * Get URL for last content audit details download.
	 *
	 * @param bool   $export_csv True: CSV export, false: last audit details exported as zip.
	 * @param string $tab Tab to export data from, if it is a CSV export.
	 * @return string URL.
	 */
	public function get_export_url( $export_csv = false, $tab = '' ) {
		return add_query_arg(
			[
				'action' => $export_csv ? Ahrefs_Seo_Screen_Content::CSV_TAB_EXPORT : Ahrefs_Seo_Screen_Settings::CSV_DATA_EXPORT,
				'a'      => wp_create_nonce( self::ACTION ),
				'b'      => time(),
			],
			$export_csv ? Links::content_audit( $tab ) : Links::settings( Ahrefs_Seo_Screen_Settings::TAB_DATA )
		);
	}
	/**
	 * Get last error from export.
	 *
	 * @return string|null Error message if any.
	 */
	public function get_error() {
		return $this->error_message;
	}
	/**
	 * Does the site have the class, required for export?
	 *
	 * @return bool
	 */
	public function has_zip_archive() {
		return class_exists( '\\ZipArchive' );
	}
	/**
	 * Export last audit details for diagnosis purposes as zip file.
	 * Open file download dialog and terminate script execution.
	 *
	 * @return bool False: error, otherwise the execution terminated.
	 */
	public function export_data_zip() {
		$this->error_message = null;
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		if ( $this->create_and_output_archive() ) {
			exit;
			// finish with file output.
		}
		if ( is_null( $this->error_message ) ) { // fill error.
			$this->error_message = __( 'Unexpected error', 'ahrefs-seo' );
		}
		return false;
	}
	/**
	 * Export current audit details for current audit.
	 * Open file download dialog and terminate script execution.
	 *
	 * @since 0.9.4
	 *
	 * @param string                $tab Tab for export data from.
	 * @param array<string, string> $columns Columns list to export. list of [ Column ID => Column title ].
	 * @return bool False: error, otherwise the execution terminated.
	 */
	public function export_data_tab( $tab, array $columns ) {
		$this->error_message = null;
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		if ( $this->create_and_output_csv( $tab, $columns ) ) {
			exit;
			// finish with file output.
		}
		if ( is_null( $this->error_message ) ) { // fill error.
			$this->error_message = __( 'Unexpected error', 'ahrefs-seo' );
		}
		return false;
	}
	/**
	 * Returns a filename of a temporary writable file.
	 *
	 * @return string
	 */
	private function get_tmp_file_path() {
		return wp_tempnam( 'ahrefs_seo' );
	}
	/**
	 * Read tables and put the data into the archive. Output archive to user as file download.
	 * Save:
	 * - whole snapshot table;
	 * - content audit table: current and new (if exists) audit details.
	 *
	 * @return bool True: success, false: error.
	 */
	private function create_and_output_archive() {
		$filename = $this->get_file_name_export_zip();
		$snapshot = new Snapshot();
		if ( ! $this->has_zip_archive() ) {
			$this->error_message = __( 'Class "ZipArchive" not found.', 'ahrefs-seo' );
			return false;
		}
		$zip_archive = new ZipArchive();
		$fname       = $this->get_tmp_file_path();
		$res         = $zip_archive->open( $fname, ZipArchive::OVERWRITE );
		if ( true !== $res ) {
			/* Translators: %s: string with error code */
			$this->error_message = sprintf( __( 'Could not create zip archive. Error code %s', 'ahrefs-seo' ), "{$res}" );
			unlink( $fname );
			return false;
		}
		ob_start(); // do not allow any output.
		$list_to_remove = [ $this->read_snapshots( $zip_archive ) ];
		$current_id     = $snapshot->get_current_snapshot_id();
		$new_id         = $snapshot->get_new_snapshot_id();
		$zip_archive->addFromString(
			'info.txt',
			(string) wp_json_encode(
				[
					'date'       => date( '(Y-m-d H:i:s)' ),
					'current_id' => $current_id,
					'new_id'     => $new_id,
				]
			)
		);
		$list_to_remove[] = $this->read_content( $current_id, $zip_archive );
		if ( ! is_null( $new_id ) && $current_id !== $new_id ) {
			$list_to_remove[] = $this->read_content( $new_id, $zip_archive );
		}
		$debug = ob_get_clean(); // save output.
		ob_start(); // do not allow any output.
		if ( '' !== $debug && false !== $debug ) {
			$zip_archive->addFromString( 'debug.txt', $debug );
			$this->error_message = $debug;
		}
		if ( $zip_archive->close() ) { // have the changes been saved in a zip archive?
			ob_end_clean();
			header( 'Content-type: application/zip' );
			header( 'Content-Disposition: attachment; filename=' . $filename );
			readfile( $fname );
		} else {
			ob_end_clean();
			$this->error_message = __( 'Could not create zip archive.', 'ahrefs-seo' );
		}
		$list_to_remove[] = $fname;
		foreach ( $list_to_remove as $fname ) {
			if ( is_string( $fname ) ) {
				unlink( $fname );
			}
		}
		return true;
	}
	/**
	 * Put the content audit data from the tab into the CSV. Output archive to user as file download.
	 * Save:
	 * - content audit table: current audit details from selected tab.
	 * - only visible columns.
	 * - same titles as Content audit table uses.
	 *
	 * @since 0.9.4
	 *
	 * @param string                $tab Tab for export data from.
	 * @param array<string, string> $columns Columns list to export. list of [ Column ID => Column title ].
	 * @return bool True: success, false: error.
	 */
	private function create_and_output_csv( $tab, array $columns ) {
		$tabs         = Ahrefs_Seo_Table_Content::get_tab_names();
		$tab_title    = isset( $tabs[ $tab ] ) ? $tabs[ $tab ] : $tab;
		$filename     = $this->get_file_name_export_data_csv( $tab_title );
		$snapshot     = new Snapshot();
		$snapshot_id  = $snapshot->get_current_snapshot_id();
		$headers      = null;
		$country_code = $snapshot->get_country_code( $snapshot_id );
		$fname        = $this->get_tmp_file_path();
		$fp           = fopen( $fname, 'w' );
		if ( $fp ) {
			fputs( $fp, chr( 239 ) . chr( 187 ) . chr( 191 ) ); // UTF-8 BOM.
			$data = ( new Ahrefs_Seo_Data_Content() )->get_data_for_export_tab( $tab, $snapshot_id );
			if ( ! empty( $data ) ) {
				foreach ( $data as $item ) {
					if ( is_null( $headers ) ) {
						$headers = [];
						foreach ( $columns as $id => $title ) {
							$headers[] = $title;
							switch ( $id ) { // The title, keyword and position columns have additional columns.
								case 'title':
									$headers[] = __( 'Type', 'ahrefs-seo' ); // post, page, category, product, product category.
									$headers[] = __( 'URL', 'ahrefs-seo' );
									break;
								case 'keyword':
									$headers[] = __( 'Keyword badge', 'ahrefs-seo' );
									break;
								case 'position':
									$headers[] = __( 'Country', 'ahrefs-seo' );
									break;
							}
						}
						fputcsv( $fp, $headers, ',', '"', "\0" );
					}
					$fields   = [];
					$post_tax = new Post_Tax( intval( $item->post_id ), $item->taxonomy, $snapshot_id );
					/*
					 * Full list of table columns:
					 * title, author, keyword, categories, position, total, organic, backlinks, refdomains, date, last_well_date, action.
					 * @see Ahrefs_Seo_Data_Content::get_data_for_export_tab().
					 */
					foreach ( $columns as $column_id => $title ) {
						switch ( $column_id ) {
							case 'title':
								$fields[] = $item->title ?: __( '(no title)', 'ahrefs-seo' );
								$fields[] = Ahrefs_Seo_Table_Content::get_post_type_badge( $item->badge );
								$fields[] = $post_tax->get_url();
								break;
							case 'author':
								$fields[] = get_the_author_meta( 'display_name', $item->author );
								break;
							case 'keyword':
								// keyword itself.
								$fields[] = $item->keyword;
								// keyword source.
								ob_start();
								Ahrefs_Seo_Table_Content::print_keyword_source_badge( $item->keyword, $item->is_approved_keyword, $item->kw_low, $item->kw_source );
								$fields[] = function_exists( 'mb_strtolower' ) ? mb_strtolower( trim( str_replace( '✓', '', sanitize_text_field( (string) ob_get_clean() ) ) ) ) : strtolower( trim( str_replace( '✓', '', sanitize_text_field( (string) ob_get_clean() ) ) ) );
								break;
							case 'categories':
								$value = '';
								if ( count( $item->categories ) ) {
									$value = implode( ', ', array_map( 'sanitize_text_field', $item->categories ) );
								}
								$fields[] = $value;
								break;
							case 'position':
								$value = '';
								if ( ! is_null( $item->position ) ) {
									$position = floatval( $item->position );
									if ( $position >= 0 ) {
										if ( $position < Ahrefs_Seo_Data_Content::POSITION_MAX - 1 ) {
											$value = round( 10 * $position ) / 10;
											$value = esc_html( sprintf( '%.1f', $value ) );
										}
									} else {
										$value = 'error';
									}
								}
								$fields[] = $value;
								$fields[] = '' !== $country_code ? $country_code : _x( 'All countries', 'Country name', 'ahrefs-seo' );
								break;
							case 'total': // Total traffic.
								$value = '';
								if ( ! is_null( $item->total ) ) {
									if ( intval( $item->total ) >= 0 ) {
										if ( ! ( defined( 'AHREFS_SEO_NO_GA' ) && AHREFS_SEO_NO_GA ) ) {
											$value = esc_html( $item->total );
										}
									} else {
										$value = 'error';
									}
								}
								$fields[] = $value;
								break;
							case 'organic': // Organic traffic.
								$value = '';
								if ( ! is_null( $item->organic ) ) {
									if ( intval( $item->organic ) >= 0 ) {
										$value = esc_html( $item->organic );
									} else {
										$value = 'error';
									}
								}
								$fields[] = $value;
								break;
							case 'backlinks':
								$value = '';
								if ( ! is_null( $item->backlinks ) ) {
									if ( intval( $item->backlinks ) >= 0 ) {
										$value = esc_html( $item->backlinks );
									} else {
										$value = 'error';
									}
								}
								$fields[] = $value;
								break;
							case 'refdomains':
								$value = '';
								if ( ! is_null( $item->refdomains ) ) {
									if ( intval( $item->refdomains ) >= 0 ) {
										$value = esc_html( $item->refdomains );
									} else {
										$value = 'error';
									}
								}
								$fields[] = $value;
								break;
							case 'date':
								$value = '';
								if ( ! empty( $item->created ) && '' !== str_replace( [ '0', '-' ], '', $item->created ) ) {
									$date = date_create_from_format( 'Y-m-d', (string) $item->created );
									if ( false !== $date ) {
										$value = esc_html( date_format( $date, 'j M Y' ) );
									}
								}
								$fields[] = $value;
								break;
							case 'last_well_date':
								$value = '';
								if ( ! empty( $item->last_well_date ) && '' !== str_replace( [ '0', '-' ], '', $item->last_well_date ) ) {
									$value = esc_html( (string) $item->last_well_date );
								}
								$fields[] = $value;
								break;
							case 'action':
								$action = isset( $item->action ) ? $item->action : Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST;
								// Items never added (action is null) to content audit will have ACTION4_ADDED_SINCE_LAST.
								$fields[] = Ahrefs_Seo_Table_Content::get_action_title( (string) $action );
								break;
							default:
								$fields[] = '-';
						}
					}
					fputcsv( $fp, $fields, ',', '"', "\0" );
				}
			}
			unset( $data );
			header( 'Content-Encoding: UTF-8' );
			header( 'Content-type: text/csv; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename=' . $filename );
			readfile( $fname );
			unlink( $fname );
			return true;
		}
		return false;
	}
	/**
	 * Read data from snapshots table
	 *
	 * @param ZipArchive|null $zip_archive Archive instance.
	 *
	 * @return string List of temporary files to remove.
	 */
	private function read_snapshots( ZipArchive $zip_archive = null ) {
		global $wpdb;
		$headers = null;
		$fname   = $this->get_tmp_file_path();
		$fp      = fopen( $fname, 'w' );
		if ( $fp ) {
			$data = $wpdb->get_results( "SELECT * FROM {$wpdb->ahrefs_snapshots} ORDER BY snapshot_id ASC", ARRAY_A );
			if ( ! empty( $data ) ) {
				foreach ( $data as $row ) {
					if ( is_null( $headers ) ) {
						$headers = array_keys( $row );
						fputcsv( $fp, $headers, ',', '"', "\0" );
					}
					fputcsv( $fp, array_values( $row ), ',', '"', "\0" );
				}
			}
			unset( $data );
			if ( ! is_null( $zip_archive ) ) {
				fclose( $fp );
				$zip_archive->addFile( $fname, 'snapshots.csv' );
				return $fname;
			} else {
				rewind( $fp );
				fpassthru( $fp );
				fclose( $fp );
				unlink( $fname );
			}
		} else {
			$this->error_message = __( 'Could not export snapshots data.', 'ahrefs-seo' );
		}
		return null;
	}
	/**
	 * Read data from content table
	 *
	 * @param int             $snapshot_id Snapshot ID to export details for.
	 * @param ZipArchive|null $zip_archive Archive instance.
	 * @return string List of temporary files to remove.
	 */
	private function read_content( $snapshot_id, ZipArchive $zip_archive = null ) {
		global $wpdb;
		$headers = null;
		$fname   = $this->get_tmp_file_path();
		$fp      = fopen( $fname, 'w' );
		if ( $fp ) {
			$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->ahrefs_content} WHERE snapshot_id = %d ORDER BY post_id, taxonomy", $snapshot_id ), ARRAY_A );
			if ( ! empty( $data ) ) {
				foreach ( $data as $row ) {
					if ( is_null( $headers ) ) {
						$headers = array_keys( $row );
						array_unshift( $headers, 'URL' );
						fputcsv( $fp, $headers, ',', '"', "\0" );
					}
					$post_tax = Post_Tax::create_from_array( $row );
					array_unshift( $row, $post_tax->get_url( true ) );
					fputcsv( $fp, array_values( $row ), ',', '"', "\0" );
				}
			}
			unset( $data );
			if ( ! is_null( $zip_archive ) ) {
				fclose( $fp );
				$zip_archive->addFile( $fname, "content-{$snapshot_id}.csv" );
				// Note: must not remove just added file, until $zip_archive->close() call.
				return $fname;
			} else {
				rewind( $fp );
				fpassthru( $fp );
				fclose( $fp );
				unlink( $fname );
			}
		} else {
			$this->error_message = __( 'Could not export content audit data.', 'ahrefs-seo' );
		}
		return null;
	}
	/**
	 * Get name for zip file
	 *
	 * @since 0.9.8
	 *
	 * @return string
	 */
	private function get_file_name_export_zip() {
		return sprintf( 'ahrefs-seo-%s_%s_export_%s.zip', AHREFS_SEO_VERSION, sanitize_file_name( Ahrefs_Seo::get_current_domain() ), date( 'Y-m-d_H-i-s' ) );
	}
	/**
	 * Get name for csv file
	 *
	 * @since 0.9.8
	 *
	 * @param string $tab_title Title of selected tab.
	 * @return string
	 */
	private function get_file_name_export_data_csv( $tab_title ) {
		return sprintf( 'ahrefs-seo-%s_%s_%s_%s.csv', AHREFS_SEO_VERSION, sanitize_file_name( Ahrefs_Seo::get_current_domain() ), sanitize_title( $tab_title ), date( 'Y-m-d_H-i-s' ) );
	}
	/**
	 * Get name for file with additional keywords
	 *
	 * @since 0.9.8
	 *
	 * @param string $postname Post slug.
	 * @return string
	 */
	public function get_file_name_export_keywords_csv( $postname ) {
		return sprintf( 'ahrefs-seo-%s_%s_additional-keywords_%s.csv', AHREFS_SEO_VERSION, sanitize_file_name( $postname ), date( 'Y-m-d_H-i-s' ) );
	}
	/**
	 * Print additional keywords exported data as csv in a textarea.
	 *
	 * @since 0.9.8
	 *
	 * @param array $items Items to export.
	 * @return void
	 */
	public function print_keywords_for_textarea( array $items ) {
		echo '"' . esc_textarea( __( 'Additional keywords', 'ahrefs-seo' ) ) . '","' . esc_textarea( __( 'Position', 'ahrefs-seo' ) ) . '","' . esc_textarea( __( 'Clicks', 'ahrefs-seo' ) ) . '","' . esc_textarea( __( 'Impressions', 'ahrefs-seo' ) ) . "\"\n";
		foreach ( $items as $values ) {
			echo '"' . esc_textarea( str_replace( '"', '""', $values['query'] ) ) . '",';
			echo esc_textarea( $values['pos'] ) . ',';
			echo esc_textarea( $values['clicks'] ) . ',';
			echo esc_textarea( $values['impr'] ) . "\n";
		}
	}
}