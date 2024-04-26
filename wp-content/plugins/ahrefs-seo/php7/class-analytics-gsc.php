<?php
declare( strict_types=1 );

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Keywords\Data_Clicks_Info;
use ahrefs\AhrefsSeo\Keywords\Data_Keyword;
use ahrefs\AhrefsSeo\Messages\Message;
use ahrefs\AhrefsSeo\Options\Advanced;
use ahrefs\AhrefsSeo\Third_Party\Sources;
use ahrefs\AhrefsSeo_Vendor\Google\Service\SearchConsole as Google_Service_SearchConsole;
use ahrefs\AhrefsSeo_Vendor\Google\Service\SearchConsole\ApiDataRow;
use ahrefs\AhrefsSeo_Vendor\Google\Service\SearchConsole\SearchAnalyticsQueryRequest;
use ahrefs\AhrefsSeo_Vendor\Google\Service\SearchConsole\SearchAnalyticsQueryResponse;
use ahrefs\AhrefsSeo_Vendor\Google_Http_Batch;
use ahrefs\AhrefsSeo_Vendor\Google_Service_Exception;
use Error;
use Exception;
use WP_Post;

trait Analytics_Gsc {
	/**
	 * Check that currently selected GSC account has same domain as current site has.
	 *
	 * @return bool|null Null if nothing to check
	 */
	public function is_gsc_account_correct() : ?bool {
		if ( empty( $this->get_data_tokens()->get_gsc_site() ) ) {
			return null;
		}
		$domain = $this->get_clean_domain();

		$_website = $this->get_clean_domain( $this->get_data_tokens()->get_gsc_site() );

		return $_website === $domain;
	}

	/**
	 * GSC account: find items with the current domain and do some queries here.
	 * Set found account as selected.
	 *
	 * @return string|null
	 */
	public function find_recommended_gsc_id() : ?string {
		$this->set_gsc_disconnect_reason(); // clean any previous error.
		$this->reset_pause( false, true );
		$list = $this->load_gsc_accounts_list();
		// recommended results, with the same domain in websiteUrl.
		$recommended = [];
		$domain      = $this->get_clean_domain();

		foreach ( $list as $item ) {
			$_website = $this->get_clean_domain( (string) $item['site'] );
			if ( $_website === $domain && 'siteUnverifiedUser' !== $item['level'] ) {
				$recommended[] = $item['site'];
			}
		}
		if ( ! count( $recommended ) ) {
			return null;
		}
		$counts = [];
		foreach ( $recommended as $site ) {
			$counts[ $site ] = $this->check_gsc_using_bulk_results( $site );
		}
		arsort( $counts );
		reset( $counts );
		$site = key( $counts );
		// set this account.
		wp_cache_flush();
		$this->get_data_tokens()->tokens_load();
		$this->set_ua( $this->get_data_tokens()->get_ua_id(), $this->get_data_tokens()->get_ua_name(), $this->get_data_tokens()->get_ua_url(), "$site" );

		return (string) $site;
	}

	/**
	 * Return array with Google Search Console accounts list
	 *
	 * @param bool $cached_only Return only cached value.
	 *
	 * @return array<array>
	 */
	public function load_gsc_accounts_list( bool $cached_only = false ) : array {
		// phpcs:disable WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar,WordPress.NamingConventions.ValidVariableName.NotSnakeCase
		if ( is_array( $this->accounts_gsc ) ) { // cached results from last call.
			return $this->accounts_gsc;
		}
		if ( $cached_only ) {
			return (array) json_decode( '' . get_option( self::OPTION_GSC_SITES, '' ), true );
		}
		$result = [];
		try {
			$client = $this->create_client();

			$service_searchconsole = new Google_Service_SearchConsole( $client );
			$sites_list            = $service_searchconsole->sites->listSites();
		} catch ( Error $e ) {
			Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );

			return [];
		} catch ( Exception $e ) {
			$this->handle_exception( $e, false, true, false ); // do not save message.
			$this->set_message( $this->extract_message( $e, __( 'Google Search Console API: failed to get the list of accounts.', 'ahrefs-seo' ) ) );

			return [];
		}

		foreach ( $sites_list->getSiteEntry() as $account ) {
			$url = $account->getSiteUrl();
			if ( ! is_null( $url ) ) {
				$result[] = [
					'site'   => $url ?: '---',
					'domain' => wp_parse_url( $url, PHP_URL_HOST ) ? strtolower( wp_parse_url( $url, PHP_URL_HOST ) ) : '---',
					'scheme' => wp_parse_url( $url, PHP_URL_SCHEME ) ? strtolower( wp_parse_url( $url, PHP_URL_SCHEME ) ) : '---',
					'level'  => $account->getPermissionLevel(),
				];
			}
		}

		// sort results.
		usort(
			$result,
			function( $a, $b ) {
				// order by account name.
				$diff = $a['domain'] <=> $b['domain'];
				if ( 0 !== $diff ) {
					return $diff;
				}

				// then order by name.
				return $a['scheme'] <=> $b['scheme'];
			}
		);
		$result             = apply_filters( 'ahrefs_seo_accounts_gsc', $result );
		$this->accounts_gsc = (array) $result;
		update_option( self::OPTION_GSC_SITES, (string) wp_json_encode( $this->accounts_gsc ) );

		return $result;
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar,WordPress.NamingConventions.ValidVariableName.NotSnakeCase
	}

	/**
	 * Check GSC accounts, return number of pages with results.
	 *
	 * @param string $gsc_site GSC site.
	 *
	 * @return int|null
	 */
	protected function check_gsc_using_bulk_results( string $gsc_site ) : ?int {
		$result = [];
		$urls   = $this->check_gsc_using_bulk_results_strings( $gsc_site ); // page urls, received from GSC.
		if ( is_null( $urls ) ) {
			return null;
		}
		foreach ( $urls as $url ) {
			$result[] = wp_parse_url( $url, PHP_URL_PATH ) ?? '';
		}
		$result = array_unique( $result );
		$count  = 0;
		if ( count( $result ) ) {
			array_walk(
				$result,
				function( $slug ) use ( &$count ) {
					$post = get_page_by_path( (string) $slug, OBJECT, [ 'post', 'page' ] );
					if ( $post instanceof WP_Post ) {
						$count++;
					}
				}
			);
		}

		return $count;
	}

	/**
	 * Check GSC accounts, return pages with results.
	 *
	 * @param string $gsc_site GSC site.
	 * @param bool   $with_clicks_only Return only URLs with non-empty clicks value.
	 *
	 * @return string[]|null URLs list or null.
	 */
	protected function check_gsc_using_bulk_results_strings( string $gsc_site, bool $with_clicks_only = true ) : ?array {
		if ( ! $this->is_gsc_enabled() || ( '' === $gsc_site ) ) {
			return null;
		}
		$start_date = date( 'Y-m-d', time() - 3 * MONTH_IN_SECONDS );
		$end_date   = date( 'Y-m-d' );

		$parameters = [
			'startDate'  => $start_date,
			'endDate'    => $end_date,
			'dimensions' => [
				'page',
			],
			'rowLimit'   => self::QUERY_DETECT_GSC_LIMIT,
			'startRow'   => 0,
		];
		try {
			$client = $this->create_client();

			$service_searchconsole = new Google_Service_SearchConsole( $client );
			// https://developers.google.com/webmaster-tools/search-console-api-original/v3/searchanalytics/query .
			$post_body = new SearchAnalyticsQueryRequest( $parameters );
			$this->maybe_do_a_pause( 'gsc' );
			$response_total = $service_searchconsole->searchanalytics->query( $gsc_site, $post_body );
			$this->maybe_do_a_pause( 'gsc', true );
		} catch ( Error $e ) {
			Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );

			return null;
		} catch ( Exception $e ) {
			$this->maybe_do_a_pause( 'gsc', true );

			// do not handle error, no need to show it or disconnect an account.
			return null;
		}
		$result = []; // page urls, received from GSC.
		foreach ( $response_total->getRows() as $row ) {
			if ( $row instanceof ApiDataRow ) {
				if ( ! $with_clicks_only || ( $row->getClicks() > 0 ) ) { // use only pages with traffic > 0.
					// key[0] is a page url.
					$result[] = $row->getKeys()[0] ?? '';
				}
			}
		}

		return $result;
	}

	/**
	 * Is GSC enabled and profile set?
	 *
	 * @since 0.10.1
	 *
	 * @return bool
	 */
	private function is_enabled_and_set() : bool {
		// is GSC enabled?
		if ( ! $this->is_gsc_enabled() ) {
			$this->set_message( __( 'Google Search Console disconnected.', 'ahrefs-seo' ) );
			$this->service_error = [ [ 'reason' => 'internal-no-token' ] ];

			return false;
		}
		if ( ! $this->is_gsc_set() ) {
			$this->set_message( __( 'Please choose Google Search Console site.', 'ahrefs-seo' ) );
			$this->service_error = [ [ 'reason' => 'internal-no-profile' ] ];

			return false;
		}
		return true;
	}

	/**
	 * Service answer has error instead of data.
	 *
	 * @param SearchAnalyticsQueryResponse|Google_Service_Exception|Exception|null $answer GSC API answer.
	 * @param string                                                               $url Current URL.
	 * @param array                                                                $results Array with results.
	 * @param string                                                               $key Current index in results array.
	 *
	 * @return bool
	 * @since 0.10.1
	 */
	private function answer_has_error( $answer, string $url, array &$results, string $key ) : bool {
		if ( $answer instanceof Google_Service_Exception ) { // catch forbidden error.
			$this->extract_message( $answer );
			$this->handle_exception( $answer, false, true );
			$this->on_error_received( $answer, [ $url ] );
			$this->gsc_paused = true; // do not make additional requests.
			return true;
		} elseif ( $answer instanceof Exception ) {
			$results[ $key ] = [ 'error' => $answer ];
			$this->on_error_received( $answer, [ $url ] );
			Ahrefs_Seo::notify( $answer, 'gsc get_clicks_and_impressions single' );
			Ahrefs_Seo_Errors::save_message( 'google', $this->extract_message( $answer ), Message::TYPE_ERROR );
			$this->gsc_paused = true; // do not make additional requests.
			return true;
		}
		return false;
	}

	/**
	 * Load keywords for url from GSC
	 *
	 * @param array<string, Data_Keyword> $data_url_and_country_list [ post_tax_string => Data_Keyword ] pairs with url and country code filled.
	 * @param string|null                 $start_date Start date.
	 * @param string|null                 $end_date End date.
	 * @param int|null                    $limit Keywords limit.
	 * @param bool                        $without_totals Do not make additional query for total values.
	 * @param Data_Keyword[]              $data_keyword_list Current or imported keyword of post, if is set.
	 *
	 * @return array<string, array{total_clicks:int, total_impr:int, result:array<array{query:string, clicks:int, pos:float, impr:int}>, kw_pos:array<array{query:string, clicks:int, pos:float, impr:int}>|null, error?:Exception}|array{error:Exception|Error}>|null
	 *                                   Array with details [post_tax_string (same index as was in $urls) => results] or null on error.
	 *                                   Each value has indexes:
	 * @type int $total_clicks
	 * @type int $total_impr
	 * @type array $result
	 * @type array $kw_pos
	 * @type string $error Error text if any
	 */
	public function get_clicks_and_impressions_by_urls( array $data_url_and_country_list, string $start_date = null, string $end_date = null, ?int $limit = null, bool $without_totals = false, array $data_keyword_list = [] ) : ?array {
		if ( ! $this->is_enabled_and_set() ) {
			return null;
		}

		Ahrefs_Seo::breadcrumbs( sprintf( '%s %s', __METHOD__, (string) wp_json_encode( func_get_args() ) ) );
		$results      = [];
		$url_to_key   = [];
		$time_wait    = 0;
		$time_query_1 = 0;
		$limit        = $limit ?? self::GSC_KEYWORDS_LIMIT;
		$responses    = null;
		$urls         = array_map(
			function( Data_Keyword $data_keyword ) {
				return $data_keyword->get_url();
			},
			$data_url_and_country_list
		);
		try {
			$client                = $this->create_client();
			$service_searchconsole = new Google_Service_SearchConsole( $client );
			$batch                 = $service_searchconsole->createBatch();
			$client->setUseBatch( true );
			foreach ( $data_url_and_country_list as $key => $data_keyword ) {
				$url                = (string) $data_keyword->get_url(); // we already filtered out empty urls.
				$country_code       = $data_keyword->get_country_code();
				$url_to_key[ $url ] = $key;
				// request must use same scheme, as site parameter has.
				$url = $this->set_scheme_for_url( $url );

				$filters = [
					[
						'dimension'  => 'page',
						'expression' => $this->url_for_gsc( $url ),
					],
				];
				if ( '' !== $country_code ) {
					$filters[] = [
						'dimension'  => 'country',
						'expression' => $country_code,
					];
				}
				$parameters = [
					'startDate'             => $start_date,
					'endDate'               => $end_date,
					'dimensions'            => [], // without any values.
					'dimensionFilterGroups' => [
						[
							'filters' => $filters,
						],
					],
					'rowLimit'              => $limit,
					'startRow'              => 0,
				];

				// Total clicks, positions, impressions.
				if ( ! $without_totals ) {
					$this->prepare_gsc_query(
						"{$key}-total",
						$batch,
						$service_searchconsole,
						$this->get_data_tokens()->get_gsc_site(),
						array_merge(
							$parameters,
							[
								'dimensions' => [
									'page',
								],
							]
						)
					);
				}

				// Top 10 clicks, positions, impressions.
				$this->prepare_gsc_query(
					"{$key}-q",
					$batch,
					$service_searchconsole,
					$this->get_data_tokens()->get_gsc_site(),
					array_merge(
						$parameters,
						[
							'dimensions' => [
								'query',
								'page',
							],
						]
					)
				);
			}

			$e = null;
			try {
				// execute requests.
				$time0 = microtime( true );
				$this->maybe_do_a_pause( 'gsc' );
				$time_wait += microtime( true ) - $time0;
				$time0      = microtime( true );

				$responses = $batch->execute();
				do_action_ref_array( 'ahrefs_seo_api_clicks_and_impressions', [ &$responses, $urls ] );

				$time_query_1 += microtime( true ) - $time0;
			} catch ( Google_Service_Exception $e ) { // catch forbidden error.
				$this->handle_exception( $e, false, true );
				$this->on_error_received( $e, $urls );
			} catch ( Error $e ) {
				$message = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );
				$this->set_message( $message );
				$this->on_error_received( $e, $urls );
			} catch ( Exception $e ) { // catch any errors.
				$this->set_message( $this->extract_message( $e ), $e );
				$this->on_error_received( $e, $urls );
			}
		} catch ( Error $e ) {
			$message = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );
			$this->set_message( $message );
			$e = new Ahrefs_Seo_Exception( $message, 0, $e );
			$this->on_error_received( $e, $urls );
		} finally {
			if ( ! empty( $client ) ) {
				$client->setUseBatch( false );
			}
		}

		if ( is_null( $responses ) ) {
			if ( empty( $e ) ) {
				$e = new Ahrefs_Seo_Exception( 'GSC returned empty response.' );
			}

			// Nothing received - exit earlier.
			return array_map(
				function( $value ) use ( $e ) {
					return [ 'error' => $e ];
				},
				$urls
			);
		}

		// parse requests.
		foreach ( $data_url_and_country_list as $key => $data_keyword ) {
			$url          = $data_keyword->get_url();
			$result       = [];
			$total_clicks = 0;
			$total_impr   = 0;
			$total_filled = false;

			if ( ! $without_totals ) {
				$answer = $responses[ "response-{$key}-total" ] ?? null;
				if ( $this->answer_has_error( $answer, (string) $url, $results, $key ) ) {
					continue;
				}
				$response_total = $this->parse_gsc_response( $answer );
				if ( ! empty( $response_total ) ) {
					foreach ( $response_total as $row ) {
						// save total clicks & impressions.
						$total_clicks = $row['clicks'];
						$total_impr   = $row['impr'];
						$total_filled = true;
						break;
					}
				}
			}
			$answer = $responses[ "response-{$key}-q" ] ?? null;

			if ( $this->answer_has_error( $answer, (string) $url, $results, $key ) ) {
				continue;
			}
			$response = $this->parse_gsc_response( $answer );

			$kw_pos = null;
			if ( ! empty( $response ) ) {
				foreach ( $response as $row ) {
					$result[] = $row;
					$keyword  = $row['query'];
					// exclude some of $data_keyword_list items if already exists in results list.
					if ( count( $data_keyword_list ) ) {
						foreach ( $data_keyword_list as $k => $data_keyword ) {
							if ( ( $url === $data_keyword->get_url() ) && $data_keyword->is_same_keyword( $keyword ) ) {
								// we can lost data for kw_pos here... so save it.
								if ( ! is_array( $kw_pos ) ) {
									$kw_pos = [];
								}
								$kw_pos[] = $row;
								unset( $data_keyword_list[ $k ] );
							}
						}
					}

					if ( ! $total_filled ) {
						// count total clickes & impressions.
						$total_clicks += $row['clicks'];
						$total_impr   += $row['impr'];
					}
				}
			}

			$results[ $key ] = [
				'total_clicks' => $total_clicks,
				'total_impr'   => $total_impr,
				'result'       => $result,
				'kw_pos'       => $kw_pos,
			];
		}

		if ( count( $data_keyword_list ) ) {
			// remove empty values.
			$data_keyword_list_ = array_filter(
				$data_keyword_list,
				function( Data_Keyword $item ) {
					return ! is_null( $item->get_keyword() ) && ( '' !== $item->get_keyword() );
				}
			);
			// remove non unique items.
			$data_keyword_list = [];
			foreach ( $data_keyword_list_ as $item ) {
				$data_keyword_list[ "{$item->get_url()}|" . strtolower( $item->get_keyword() ?? '' ) . '|' . $item->get_country_code() ] = $item;
			}
			$data_keyword_list = array_values( $data_keyword_list );
			unset( $data_keyword_list_ );
		}

		if ( count( $data_keyword_list ) && ! $this->gsc_paused ) {
			// make additional request and load details for current keywords.
			$additional = $this->get_position_fast( $data_keyword_list );
			if ( ! empty( $additional ) ) {
				foreach ( $additional as $data_keyword ) {
					if ( ! is_null( $data_keyword->get_clicks_info() ) ) {
						$key                           = $url_to_key[ $data_keyword->get_url() ];
						$results[ "$key" ]['result'][] = $data_keyword->as_gsc_array();
						if ( ! isset( $results[ "$key" ]['kw_pos'] ) ) {
							$results[ "$key" ]['kw_pos'] = [];
						}
						$results[ "$key" ]['kw_pos'][] = $data_keyword->as_gsc_array();
					}
				}
			}
		}
		$this->gsc_paused = false; // unblock next requests to API.

		$total_clicks = array_map(
			function( $values ) {
				return $values['total_clicks'] ?? null;
			},
			$results
		);
		Ahrefs_Seo::breadcrumbs( sprintf( '%s(%s) (%s) (%s) (%d): wait: %1.3fsec, query:  %1.3fsec. Total clicks: %s', __METHOD__, (string) wp_json_encode( $data_url_and_country_list ), $start_date, $end_date, $limit, $time_wait, $time_query_1, (string) wp_json_encode( $total_clicks ) ) );

		return $results;
	}

	/**
	 * @param string $url URL.
	 *
	 * @return string
	 */
	protected function set_scheme_for_url( string $url ) : string {
		if ( false === strpos( $this->get_data_tokens()->get_gsc_site(), 'sc-domain:' ) && false === strpos( $url, $this->get_data_tokens()->get_gsc_site() ) ) {
			$scheme_current  = explode( '://', $url, 2 );
			$scheme_required = explode( '://', $this->get_data_tokens()->get_gsc_site(), 2 );
			if ( 2 === count( $scheme_current ) && 2 === count( $scheme_required ) ) {
				$url = $scheme_required[0] . '://' . $scheme_current[1];
			}
		}

		return $url;
	}

	/**
	 * Prepare URL for GSC request.
	 *
	 * @param string $url Original URL.
	 *
	 * @return string
	 * @since 0.9.4
	 */
	private function url_for_gsc( string $url ) : string {
		return ( new Advanced() )->get_adv_gsc_uses_uppercase() ? (string) preg_replace_callback(
			'/%[0-9a-f]{2}/',
			function( array $matches ) {
				return strtoupper( $matches[0] );
			},
			$url
		) : $url;
	}

	/**
	 * Prepare query to GSC.
	 *
	 * @param string                       $key Key for responses array.
	 * @param Google_Http_Batch            $batch Batch instance.
	 * @param Google_Service_SearchConsole $service_searchconsole Google Service SearchConsole instance.
	 * @param string                       $gsc_site Site for query.
	 * @param array                        $parameters Other parameters for query.
	 *
	 * @return void
	 * @since 0.7.3
	 */
	protected function prepare_gsc_query( string $key, Google_Http_Batch &$batch, Google_Service_SearchConsole $service_searchconsole, string $gsc_site, array $parameters ) : void {
		try {
			$post_body = new SearchAnalyticsQueryRequest( $parameters );
			$request   = $service_searchconsole->searchanalytics->query( $gsc_site, $post_body, [ 'quotaUser' => $this->get_api_user() ] );
			$batch->add( $request, $key );
		} catch ( Exception $e ) {
			$this->handle_exception( $e, false, true );

			return;
		}
	}

	/**
	 * Parse results of request.
	 *
	 * @param SearchAnalyticsQueryResponse|null $response Response.
	 *
	 * @return array<array{query:string, clicks:int, pos:float, impr:int}>
	 * @since 0.7.3
	 */
	protected function parse_gsc_response( ?SearchAnalyticsQueryResponse $response ) : array {
		$result = [];
		if ( ! empty( $response ) ) {
			foreach ( $response->getRows() as $row ) {
				if ( $row instanceof ApiDataRow ) {
					$keys     = $row->getKeys();
					$clicks   = $row->getClicks();
					$impr     = $row->getImpressions();
					$position = $row->getPosition();
					$result[] = [
						'query'  => $keys[0],
						'impr'   => $impr,
						'clicks' => $clicks,
						'pos'    => $position,
					];
				}
			}
		}

		return $result;
	}

	/**
	 * Load metrics (position, clicks, impressions) of keyword.
	 *
	 * @param Data_Keyword[] $list_url_keyword List with url and keyword fields filled.
	 *
	 * @return Data_Keyword[]|null Null if error, a list of results otherwise
	 */
	public function get_position_fast( array $list_url_keyword ) : ?array {
		Ahrefs_Seo::breadcrumbs(
			__METHOD__ . (string) wp_json_encode(
				array_map(
					function( $item ) {
						return $item->as_array();
					},
					$list_url_keyword
				)
			)
		);
		$start_date = date( 'Y-m-d', strtotime( '- 3 month' ) );
		$end_date   = date( 'Y-m-d' );

		if ( ! $this->is_enabled_and_set() ) {
			return null;
		}

		$results = [];
		try {
			$client                = $this->create_client();
			$service_searchconsole = new Google_Service_SearchConsole( $client );

			$batch = $service_searchconsole->createBatch();
			$client->setUseBatch( true );
			foreach ( $list_url_keyword as $key => $row ) {
				$url = $this->url_for_gsc( $row->get_url() ?? '' );

				$current_keyword = $row->get_keyword();
				$country_code    = $row->get_country_code();
				// request must use same scheme, as site parameter has.
				$url     = $this->set_scheme_for_url( $url );
				$filters = [
					[
						'dimension'  => 'page',
						'expression' => $url,
					],
					[
						'dimension'  => 'query',
						'expression' => $current_keyword,
					],
				];
				if ( '' !== $country_code ) {
					$filters[] = [
						'dimension'  => 'country',
						'expression' => $country_code,
					];
				}
				$parameters = [
					'startDate'             => $start_date,
					'endDate'               => $end_date,
					'dimensions'            => [
						'query',
						'page',
					],
					'dimensionFilterGroups' => [
						[
							'filters' => $filters,
						],
					],
					'rowLimit'              => 1,
					'startRow'              => 0,
				];

				// prepare request and load details for current keyword.
				$this->prepare_gsc_query(
					"{$key}-f",
					$batch,
					$service_searchconsole,
					$this->get_data_tokens()->get_gsc_site(),
					$parameters
				);
			}

			try {
				// execute requests.
				$this->maybe_do_a_pause( 'gsc' );
				$responses = $batch->execute();
				do_action_ref_array( 'ahrefs_seo_api_position_fast', [ &$responses ] );
			} catch ( Exception $e ) { // catch all errors.
				$this->on_error_received(
					$e,
					array_filter(
						array_map(
							function( Data_Keyword $item ) {
								return $item->get_url();
							},
							$list_url_keyword
						)
					)
				);
				$this->handle_exception( $e );

				return null; // exit without success.
			}
		} catch ( Error $e ) {
			$message = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );
			$this->set_message( $message );

			return null;
		} finally {
			if ( ! empty( $client ) ) {
				$client->setUseBatch( false );
			}
		}

		foreach ( $list_url_keyword as $key => $row ) {
			$results[ $key ] = new Data_Keyword( $row->get_keyword(), Sources::SOURCE_GSC, null, $row->get_url(), $row->get_country_code() ); // assign same url, keyword and country code.
			$url             = $row->get_url() ?? '';
			$answer          = $responses[ "response-{$key}-f" ] ?? null;
			if ( $answer instanceof Google_Service_Exception ) { // catch forbidden error.
				$this->extract_message( $answer );
				$this->handle_exception( $answer, false, true );
				$this->on_error_received( $answer, [ $url ] );
				$this->gsc_paused = true; // do not make additional requests.
				continue;
			} elseif ( $answer instanceof Exception ) {
				$results[ $key ]->set_error( $this->extract_message( $answer ) );
				$this->on_error_received( $answer, [ $url ] );
				Ahrefs_Seo::notify( $answer, 'get_position_fast single' );
				Ahrefs_Seo_Errors::save_message( 'google', $this->extract_message( $answer ), Message::TYPE_ERROR );
				continue;
			}
			$response = $this->parse_gsc_response( $answer );
			if ( ! empty( $response ) ) {
				foreach ( $response as $row2 ) {
					$results[ $key ]->set_clicks_info( new Data_Clicks_Info( $row2['clicks'], $row2['pos'], $row2['impr'] ) ); // only 1 row was loaded.
					break;
				}
			}
		}

		return $results;
	}

	/**
	 * Get top pages for current GSC profile.
	 *
	 * @return string[]|null
	 * @since 0.9.4
	 */
	public function get_top_gsc_results() : ?array {
		return $this->check_gsc_using_bulk_results_strings( $this->get_data_tokens()->get_gsc_site() );
	}

	/**
	 * Check that GSC used correct domain and set disconnect reason.
	 * If GSC site URL selected is not the same as WordPress site or GA profile  - should be treated as GSC not connected.
	 *
	 * @return bool False on error.
	 */
	public function gsc_check_domain() : bool {
		$site_domain = $this->get_clean_domain();
		// single or multiple domains.
		$analytics_domains = $this->is_ua_set() ? array_map(
			[
				$this,
				'get_clean_domain',
			],
			explode( '|', $this->get_data_tokens()->get_ua_url() )
		) : [];
		$gsc_domain        = $this->get_clean_domain( $this->get_data_tokens()->get_gsc_site() );
		$result            = ! empty( $analytics_domains ) && in_array( $gsc_domain, $analytics_domains, true ) || $site_domain === $gsc_domain;
		if ( ! $result ) {
			/* translators: 1: domain name, 2: domain name */
			$this->set_gsc_disconnect_reason( sprintf( __( 'Google Search Console has an invalid domain (current domain: %1$s, selected: %2$s).', 'ahrefs-seo' ), $site_domain, $gsc_domain ), false );

			return false;
		} else {
			// check credentials is not "siteUnverifiedUser".
			if ( '' !== $gsc_domain ) {
				$list = $this->load_gsc_accounts_list();
				foreach ( $list as $item ) {
					if ( $this->get_data_tokens()->get_gsc_site() === (string) $item['site'] && 'siteUnverifiedUser' === $item['level'] ) {
						/* Translators: %s: current permission level string */
						$this->set_gsc_disconnect_reason( sprintf( __( 'Google Search Console has an invalid permission level (%s).', 'ahrefs-seo' ), $item['level'] ), false );

						return false;
					}
				}
			}
		}

		return true;
	}
}
