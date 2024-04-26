<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Workers;

use ahrefs\AhrefsSeo\Ahrefs_Seo;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Analytics;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Compatibility;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Data_Content;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Errors;
use ahrefs\AhrefsSeo\Messages\Message;
use ahrefs\AhrefsSeo\Post_Tax;
use Error;
use Exception;

/**
 * Worker_Traffic class.
 * Load traffic details.
 *
 * @since 0.7.3
 */
class Worker_Traffic extends Worker {

	public const API_NAME          = 'ga';
	protected const WHAT_TO_UPDATE = 'traffic';

	/** @var float Delay after successful request to API */
	protected $pause_after_success = 2;

	/**
	 * @var int Load up to (number) items in same request.
	 */
	protected $items_at_once = 2;

	/**
	 * Set up items count at request and tune pauses.
	 *
	 * @return array|Post_Tax[]|null
	 */
	protected function get_next_posts_wrapper() : ?array {
		$this->items_at_once = Ahrefs_Seo_Analytics::get()->get_max_request_items();
		if ( $this->items_at_once > 10 ) {
			$this->pause_after_success = 20;
			$this->pause_after_fail    = 120;
		}
		return parent::get_next_posts_wrapper();
	}
	/**
	 * Run update for items in list
	 *
	 * @param Post_Tax[] $post_taxes Post ID list.
	 * @return bool False if rate limit error received and need to do pause.
	 */
	protected function update_posts( array $post_taxes ) : bool {
		$this->update_posts_info( $post_taxes );
		return ! $this->has_rate_error;
	}

	/**
	 * Update posts with the traffic info from Analytics.
	 *
	 * @param Post_Tax[] $post_taxes Post ID list.
	 */
	public function update_posts_info( array $post_taxes ) : void {
		$page_id_to_slug = [];
		$skipped_results = []; // errors: [ post_id => ['error' => message..]].
		$traffic_raw     = [];
		if ( is_null( $this->api ) || ! ( $this->api instanceof Ahrefs_Seo_Analytics ) ) {
			$this->api = Ahrefs_Seo_Analytics::get();
		}
		if ( $this->api->is_ua_set() ) {
			foreach ( $post_taxes as $post_tax ) {
				$url             = apply_filters( 'ahrefs_seo_search_traffic_url', $post_tax->get_url() );
				$post_tax_string = (string) $post_tax;
				if ( '' !== $url ) {
					$components = wp_parse_url( $url );
					if ( is_array( $components ) ) {
						$page_id_to_slug[ $post_tax_string ] = ( $components['path'] ?? '' ) . ( ! empty( $components['query'] ) ? '?' . $components['query'] : '' ) . ( ! empty( $components['fragment'] ) ? '#' . $components['fragment'] : '' );
					} else {
						$message                             = __( 'Post URL cannot be parsed. It is possible that you’ve archived the post or changed the post ID. Please reload the page & try again.', 'ahrefs-seo' );
						$skipped_results[ $post_tax_string ] = [ 'error' => $message ];
					}
				} else {
					$message                             = __( 'Post cannot be found. It is possible that you’ve archived the post or changed the post ID. Please reload the page & try again.', 'ahrefs-seo' );
					$skipped_results[ $post_tax_string ] = [ 'error' => $message ];
					Ahrefs_Seo_Errors::save_message( 'WordPress', $message, Message::TYPE_NOTICE );
				}
			}
			if ( count( $page_id_to_slug ) ) { // is there anything to load?
				$traffic_raw = $this->load_traffic( $page_id_to_slug );
			}
		} else {
			$message         = __( 'Analytics account is not connected.', 'ahrefs-seo' );
			$skipped_results = $this->prepare_answer( $page_id_to_slug, $message ) ?? [];
			Ahrefs_Seo_Errors::save_message( 'google', $message, Message::TYPE_ERROR );
		}

		if ( ! empty( $skipped_results ) || ! empty( $traffic_raw ) ) {
			$traffic_results = $this->calculate_traffic( $traffic_raw, $page_id_to_slug, $skipped_results );
			$this->update_traffic_values( $traffic_results );
		}
	}

	/**
	 * Fill answers with error message
	 *
	 * @param array<string,string> $page_id_to_slug Array [ page id => page slug].
	 * @param string               $error_message Error message.
	 * @return array Index is page slug, value is ['error' => $error_message].
	 */
	protected function prepare_answer( ?array $page_id_to_slug, string $error_message ) : ?array {
		return is_null( $page_id_to_slug ) ? null : array_map(
			function( $slug ) use ( $error_message ) {
				return [ 'error' => $error_message ];
			},
			array_flip( $page_id_to_slug )
		);
	}

	/**
	 * Load traffic info from API.
	 * Set has_rate_error if rate error received.
	 *
	 * @param array<string, string> $page_id_to_slug Associative array, post_tax_string => url.
	 * @return array<string, array<string, mixed>> Results,
	 * associative array page_slug => [traffic details, as Google API returned],
	 * Fill results with errors on error.
	 */
	public function load_traffic( array $page_id_to_slug ) : array {
		$this->has_rate_error = false;

		$start_date = date( 'Y-m-d', Ahrefs_Seo_Data_Content::get()->get_waiting_as_timestamp() );
		$end_date   = date( 'Y-m-d', time() );

		if ( is_null( $this->api ) || ! ( $this->api instanceof Ahrefs_Seo_Analytics ) ) {
			$this->api = Ahrefs_Seo_Analytics::get();
		}

		$result = $this->api->get_visitors_by_page( array_values( $page_id_to_slug ), $start_date, $end_date );
		if ( is_null( $result ) ) { // fill with errors.
			$error  = $this->api->get_message();
			$result = array_map(
				function( $value ) use ( $error ) {
					return [ 'error' => $error ];
				},
				$page_id_to_slug
			);
		}
		return $result; // @phpstan-ignore-line -- ignore array index type, really it is string.
	}

	/**
	 * Update traffic value using loaded pages.
	 *
	 * @param array<string, array<string, mixed>>  $traffic_details Associative array, page_slug => traffic results array.
	 * @param array<string, string>                $post_id_to_slug Associative array, post_tax_string => page slug.
	 * @param array<string, array<string, string>> $skipped_items   Associative array, post_tax_string => traffic results array filled with error only.
	 * @return array<string, array<string, mixed>> Associative array post_tax_string => array traffic results
	 */
	public function calculate_traffic( array $traffic_details, array $post_id_to_slug, array $skipped_items = [] ) : array {
		$results         = [];
		$slug_to_post_id = array_flip( $post_id_to_slug );

		if ( count( $traffic_details ) ) {
			foreach ( $traffic_details as $slug => $values ) {
				$post_tax_string = $slug_to_post_id[ $slug ] ?? null;

				// days count using post publish date.
				$days_count    = $this->content_audit->get_time_period_for();
				$total         = -10;
				$total_month   = -10;
				$organic       = -10;
				$organic_month = -10;
				$error         = $values['error'] ?? null;
				if ( empty( $error ) ) {
					$total         = $values[ Ahrefs_Seo_Analytics::TRAFFIC_TYPE_TOTAL ] ?? 0;
					$organic       = $values[ Ahrefs_Seo_Analytics::TRAFFIC_TYPE_ORGANIC ] ?? 0;
					$total_month   = intval( round( $total / $days_count * 30 ) );
					$organic_month = intval( round( $organic / $days_count * 30 ) );
				}

				$results[ "$post_tax_string" ] = compact( 'total', 'organic', 'total_month', 'organic_month', 'error' );
			}
		}
		if ( count( $skipped_items ) ) {
			foreach ( $skipped_items as $post_tax_string => $values ) {
				$total                         = -10;
				$total_month                   = -10;
				$organic                       = -10;
				$organic_month                 = -10;
				$error                         = $values['error'] ?? '';
				$results[ "$post_tax_string" ] = compact( 'total', 'organic', 'total_month', 'organic_month', 'error' );
			}
		}
		return $results;
	}

	/**
	 * Update traffic values
	 *
	 * @param array<string,array<string, mixed>> $results key is post tax string, value is array [total, organic, total_month, organic_month, error].
	 * @return void
	 */
	protected function update_traffic_values( array $results ) : void {
		Ahrefs_Seo::breadcrumbs( sprintf( '%s: %s', __METHOD__, (string) wp_json_encode( $results ) ) );
		foreach ( $results as $post_tax_string => $values ) {
			try {
				if ( empty( $values['error'] ) ) {
					$this->content_audit->update_traffic_values( Post_Tax::create_from_string( "$post_tax_string" ), $values['total'], $values['organic'], $values['total_month'], $values['organic_month'], null );
				} else {
					$this->content_audit->update_traffic_values( Post_Tax::create_from_string( "$post_tax_string" ), -10, -10, -10, -10, $values['error'] );
				}
			} catch ( Error $e ) {
				Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__, __( 'Traffic values update failed.', 'ahrefs-seo' ) );
			} catch ( Exception $e ) {
				Ahrefs_Seo::notify( $e, 'Traffic values update failed.' );
				/* Translators: %s: URL */
				Ahrefs_Seo_Errors::save_message( 'WordPress', sprintf( __( 'Traffic values update failed for %s', 'ahrefs-seo' ), Post_Tax::create_from_string( "$post_tax_string" )->get_url() ) );
			}
		}
	}
}
