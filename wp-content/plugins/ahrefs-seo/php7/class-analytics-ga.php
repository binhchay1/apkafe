<?php
declare( strict_types=1 );

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Messages\Message;
use ahrefs\AhrefsSeo\Options\Advanced;
use ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\BatchRunReportsRequest;
use ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\FilterExpressionList;
use ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\Row as Google_Service_AnalyticsData_Row;
use ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\RunReportRequest;
use ahrefs\AhrefsSeo_Vendor\Google\Service\AnalyticsData\RunReportResponse;
use ahrefs\AhrefsSeo_Vendor\Google\Service\GoogleAnalyticsAdmin;
use ahrefs\AhrefsSeo_Vendor\Google_Http_Batch;
use ahrefs\AhrefsSeo_Vendor\Google_Service_Analytics;
use ahrefs\AhrefsSeo_Vendor\Google_Service_AnalyticsData;
use ahrefs\AhrefsSeo_Vendor\Google_Service_AnalyticsData_DateRange;
use ahrefs\AhrefsSeo_Vendor\Google_Service_AnalyticsData_Dimension;
use ahrefs\AhrefsSeo_Vendor\Google_Service_AnalyticsData_Filter;
use ahrefs\AhrefsSeo_Vendor\Google_Service_AnalyticsData_FilterExpression;
use ahrefs\AhrefsSeo_Vendor\Google_Service_AnalyticsData_InListFilter;
use ahrefs\AhrefsSeo_Vendor\Google_Service_AnalyticsData_Metric;
use ahrefs\AhrefsSeo_Vendor\Google_Service_AnalyticsData_StringFilter;
use ahrefs\AhrefsSeo_Vendor\Google_Service_AnalyticsReporting;
use ahrefs\AhrefsSeo_Vendor\Google_Service_AnalyticsReporting_DateRange;
use ahrefs\AhrefsSeo_Vendor\Google_Service_AnalyticsReporting_Dimension;
use ahrefs\AhrefsSeo_Vendor\Google_Service_AnalyticsReporting_DimensionFilter;
use ahrefs\AhrefsSeo_Vendor\Google_Service_AnalyticsReporting_DimensionFilterClause;
use ahrefs\AhrefsSeo_Vendor\Google_Service_AnalyticsReporting_GetReportsRequest;
use ahrefs\AhrefsSeo_Vendor\Google_Service_AnalyticsReporting_Metric;
use ahrefs\AhrefsSeo_Vendor\Google_Service_AnalyticsReporting_ReportRequest;
use ahrefs\AhrefsSeo_Vendor\Google_Service_Exception;
use ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Exception\ConnectException as GuzzleConnectException;
use ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Error;
use Exception;

trait Analytics_Ga {

	/**
	 * GA account: find items with the current domain and do some queries here
	 * Set found account as selected.
	 *
	 * @return string|null
	 */
	public function find_recommended_ga_id() : ?string {
		if ( defined( 'AHREFS_SEO_NO_GA' ) && AHREFS_SEO_NO_GA ) {
			return 'AHREFS_SEO_NO_GA';
		}
		$this->reset_pause( true, false );
		$list = $this->load_accounts_list();
		// recommended results, with the same domain in websiteUrl.
		$recommended = [];
		$details     = [];

		foreach ( $list as $account ) {
			if ( ! empty( $account['values'] ) ) {
				foreach ( $account['values'] as $property_name => $item ) {
					if ( isset( $item['views'] ) && count( $item['views'] ) ) {
						foreach ( $item['views'] as $view ) {
							if ( $this->is_ga_account_correct( $view['website'] ) ) {
								$recommended[]             = $view['ua_id'];
								$details[ $view['ua_id'] ] = [
									'name'    => $view['view'],
									'website' => $view['website'],
								];
							}
						}
					}
					if ( isset( $item['streams'] ) && count( $item['streams'] ) ) {
						$ua_id    = $item['streams'][0]['ua_id'];
						$websites = implode(
							'|',
							array_map(
								function( $stream ) {
									return $stream['website'];
								},
								$item['streams']
							)
						);
						if ( $this->is_ga_account_correct( $websites ) ) {
							$recommended[]     = $ua_id;
							$details[ $ua_id ] = [
								'name'    => $property_name,
								'website' => $websites,
							];
						}
					}
				}
			}
		}
		if ( ! count( $recommended ) ) {
			return null;
		}
		$counts = $this->check_ga_using_top_traffic_pages( $recommended );
		if ( is_null( $counts ) ) {
			return null;
		}
		arsort( $counts );
		reset( $counts );
		$ua_id = key( $counts );
		// set this account.
		if ( isset( $details[ $ua_id ] ) ) {
			$value = $details[ $ua_id ];
			wp_cache_flush();
			$this->get_data_tokens()->tokens_load();
			$this->set_ua( "$ua_id", $value['name'], $value['website'], $this->get_data_tokens()->get_gsc_site() );
		}

		return (string) $ua_id;
	}

	/**
	 * Return array with ua accounts list
	 *
	 * @return array<array>
	 */
	public function load_accounts_list() : array {
		$result = [];
		try {
			// mix ga4 with ga.
			$ga4  = $this->load_accounts_list_ga4();
			$ga   = $this->load_accounts_list_ga();
			$data = array_merge( $ga, $ga4 );
			// sort results.
			usort(
				$data,
				function( $a, $b ) {
					// order by account name.
					$diff = strcasecmp( $a['account_name'], $b['account_name'] );
					if ( 0 !== $diff ) {
						return $diff;
					}

					// then order by name.
					return strcasecmp( $a['name'], $b['name'] );
				}
			);
			// split by account, profile.
			foreach ( $data as $item ) {
				$account      = $item['account'];
				$account_name = $item['account_name'];
				$ua_id        = $item['ua_id'];
				$name         = $item['name'];
				$website      = $item['website'];
				if ( ! isset( $result[ $account ] ) ) {
					$result[ $account ] = [
						'account' => $account,
						'label'   => $account_name,
						'values'  => [],
					];
				}
				if ( ! isset( $result[ $account ]['values'][ $name ] ) ) {
					$result[ $account ]['values'][ $name ] = [];
				}
				$new_item = [
					'ua_id'   => $ua_id,
					'website' => $website,
				];
				$type     = null;
				if ( isset( $item['view'] ) ) {
					$type             = 'views';
					$new_item['view'] = $item['view'];
				} elseif ( isset( $item['stream'] ) ) {
					$type               = 'streams';
					$new_item['stream'] = $item['stream'];
				}
				if ( ! is_null( $type ) ) {
					if ( ! isset( $result[ $account ]['values'][ $name ][ $type ] ) ) {
						$result[ $account ]['values'][ $name ][ $type ] = [];
					}
					$result[ $account ]['values'][ $name ][ $type ][] = $new_item;
				}
			}
		} catch ( Error $e ) {
			$message = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__, __( 'Google Analytics API: failed to get the list of accounts.', 'ahrefs-seo' ) );
			$this->set_message( $message );
		}

		return $result;
	}

	/**
	 * Return array with ua accounts list from Google Analytics Admin API
	 *
	 * @return array<array>
	 *
	 * @since 0.7.3
	 *
	 * phpcs:ignore Squiz.Commenting.FunctionCommentThrowTag.Missing -- we handle exception.
	 */
	protected function load_accounts_list_ga4() : array {
		// phpcs:disable WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar,WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		if ( is_array( $this->accounts_ga4 ) ) { // cached results from last call.
			return $this->accounts_ga4;
		}
		if ( defined( 'AHREFS_SEO_NO_GA' ) && AHREFS_SEO_NO_GA ) {
			return [];
		}
		$result     = [];
		$accounts   = [];
		$properties = [];
		try {
			$client = $this->create_client();
			$admin  = new GoogleAnalyticsAdmin( $client );

			// accounts and properties list.
			$next_list = '';
			do {
				$params = [
					'pageSize' => self::QUERY_LIST_GA_ACCOUNTS_PAGE_SIZE,
				];
				if ( ! empty( $next_list ) ) {
					$params['pageToken'] = $next_list;
				}
				$account_summaries = $admin->accountSummaries->listAccountSummaries( $params );
				$_accounts         = $account_summaries->getAccountSummaries();
				if ( count( $_accounts ) ) {
					foreach ( $_accounts as $_account ) {
						$account_name              = $_account->getAccount();
						$accounts[ $account_name ] = $_account->getDisplayName();

						$_properties = $_account->getPropertySummaries();
						if ( count( $_properties ) ) {
							foreach ( $_properties as $_property ) {
								$properties[ $_property['property'] ] = [
									'account' => $account_name,
									'label'   => $_property['displayName'],
								];
							}
						}
					}
				}
				$next_list = $account_summaries->getNextPageToken();
			} while ( ! empty( $next_list ) );
			$this->accounts_ga4_raw = $accounts;

			// get web streams for each property: need website urls.
			$streams  = []; // index is property id, value is array with data url.
			$requests = []; // Pending requests, [ property_id => next page token ].
			try {
				$client->setUseBatch( true );
				// prepare all initial requests.
				foreach ( $properties as $_property_id => $_values ) {
					$requests[ $_property_id ] = '';
				}

				$error_set = false;
				while ( ! empty( $requests ) ) {
					$pieces = array_splice( $requests, 0, 5 ); // execute up to 5 requests at once.
					$batch  = $admin->createBatch();
					foreach ( $pieces as $_property_id => $next_page ) {
						$params = [
							'pageSize' => self::QUERY_LIST_GA_ACCOUNTS_PAGE_SIZE,
						];
						if ( ! empty( $next_page ) ) {
							$params['pageToken'] = $next_page;
						}
						$request = $admin->properties_dataStreams->listPropertiesDataStreams( "{$_property_id}", $params );
						$batch->add( $request, $_property_id );
					}

					$responses = [];
					try {
						$responses = $batch->execute();
						do_action_ref_array( 'ahrefs_seo_api_list_ga4', [ &$responses ] );
					} catch ( Exception $e ) { // catch all errors.
						$this->set_message( $this->extract_message( $e ), $e );
						$this->on_error_received( $e );
						throw $e;
					}

					foreach ( $responses as $_property_id => $streams_list ) {
						if ( $streams_list instanceof Exception ) {
							if ( ! $error_set ) {
								$this->set_message( __( 'Could not receive a list of Google accounts. Google Analytics API returned an error. Please try again later or contact Ahrefs support to get it resolved.', 'ahrefs-seo' ) );
								$this->set_message( $this->extract_message( $streams_list ), $streams_list );
								$this->on_error_received( $streams_list );
								$error_set = true;
							}
							continue;
						}
						$_property_id = str_replace( 'response-', '', $_property_id );
						$_streams     = $streams_list->getDataStreams();
						if ( is_array( $_streams ) && count( $_streams ) ) {
							foreach ( $_streams as $_stream ) {
								$web_data = $_stream->webStreamData;
								if ( $web_data ) {
									if ( ! isset( $streams[ "$_property_id" ] ) ) {
										$streams[ "$_property_id" ] = [];
									}
									$streams[ "$_property_id" ][] = [
										'uri'   => $web_data->defaultUri,
										'label' => $_stream->displayName,
									];
								}
							}
						}
						$next_list = $streams_list->getNextPageToken();
						if ( ! empty( $next_list ) ) {
							$requests[ "$_property_id" ] = $next_list;
						}
					}
				}
			} finally {
				$client->setUseBatch( false );
			}

			if ( ! empty( $accounts ) && ! empty( $properties ) ) {
				foreach ( $properties as $property_id => $value ) {
					$account_id     = (string) $value['account'];
					$account_number = explode( '/', $account_id, 2 )[1] ?? '';
					$property_label = $value['label'];
					$account_label  = $accounts[ $account_id ] ?? '---';
					if ( ! empty( $streams[ $property_id ] ) ) {
						foreach ( $streams[ $property_id ] as $stream ) {
							$uri          = $stream['uri'];
							$stream_label = $stream['label'];
							$result[]     = [
								'ua_id'        => $property_id,
								'account'      => $account_number,
								'account_name' => $account_label,
								'name'         => $property_label,
								'stream'       => $stream_label,
								'website'      => $uri,
							];
						}
					}
				}
			}
			$this->accounts_ga4 = $result;
		} catch ( Error $e ) {
			$message = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );
			$this->set_message( $message );
		} catch ( Google_Service_Exception $e ) {
			Ahrefs_Seo::breadcrumbs( 'Events ' . (string) wp_json_encode( $this->get_analytics_client()->get_logged_events() ) );
			Ahrefs_Seo::notify( $e );
			$this->set_message( $this->extract_message( $e, __( 'Google Analytics Admin API: failed to get the list of accounts.', 'ahrefs-seo' ) ) );
		} catch ( Exception $e ) {
			$this->set_message( $this->extract_message( $e, __( 'Google Analytics Admin API: failed to get the list of accounts.', 'ahrefs-seo' ) ), $e );
		}

		return $result;
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar,WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Return array with ua accounts list from Google Analytics Management API
	 *
	 * @return array<array>
	 * @since 0.7.3
	 */
	protected function load_accounts_list_ga() : array {
		// phpcs:disable WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar,WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		if ( is_array( $this->accounts_ga ) ) { // cached results from last call.
			return $this->accounts_ga;
		}
		if ( defined( 'AHREFS_SEO_NO_GA' ) && AHREFS_SEO_NO_GA ) {
			return [
				[
					'ua_id'        => 'AHREFS_SEO_NO_GA',
					'account'      => 'AHREFS_SEO_NO_GA',
					'account_name' => 'AHREFS_SEO_NO_GA',
					'name'         => 'AHREFS_SEO_NO_GA',
					'view'         => __( 'default', 'ahrefs-seo' ),
					'website'      => 'https://' . Ahrefs_Seo::get_current_domain(),
				],
			];
		}
		$result = [];
		// do this call earlier, maybe it is no sence to make another calls if no accounts.
		try {
			$client    = $this->create_client();
			$analytics = new Google_Service_Analytics( $client );

			$ua_list = $analytics->management_webproperties->listManagementWebproperties( '~all' );
			do_action_ref_array( 'ahrefs_seo_api_list_ga_webproperties', [ &$ua_list ] );
		} catch ( Error $e ) {
			Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );

			return [];
		} catch ( Exception $e ) {
			$this->handle_exception( $e, false, true, false ); // do not save message.
			$this->set_message( $this->extract_message( $e, __( 'Google Analytics Management API: failed to get the list of accounts.', 'ahrefs-seo' ) ) );

			return [];
		}

		if ( empty( $ua_list ) ) {
			return [];
		}
		$data = $ua_list->getItems();

		try {
			$accounts_list = $analytics->management_accounts->listManagementAccounts();
			do_action_ref_array( 'ahrefs_seo_api_list_ga_accounts', [ &$accounts_list ] );
		} catch ( Exception $e ) {
			$this->handle_exception( $e );
			$accounts_list = null;
		}

		$accounts = [];
		if ( ! empty( $accounts_list ) ) {
			foreach ( $accounts_list->getItems() as $account ) {
				$accounts[ $account->getId() ] = $account->getName();
			}
			$this->accounts_ga_raw = array_values( $accounts );
		}

		/*
		Workaround to extract defaultProfileId, which some of the older GA accounts lack
		*/
		try {
			$profiles_list = $analytics->management_profiles->listManagementProfiles( '~all', '~all' );
			do_action_ref_array( 'ahrefs_seo_api_list_ga_profiles', [ &$profiles_list ] );
		} catch ( Exception $e ) {
			$this->handle_exception( $e );
			$profiles_list = null;
		}

		$profiles_groups = [];
		if ( ! empty( $profiles_list ) ) {
			foreach ( $profiles_list->getItems() as $profile ) {
				$_web_property_id = $profile->getWebPropertyId();
				if ( ! isset( $profiles_groups[ $_web_property_id ] ) ) {
					$profiles_groups[ $_web_property_id ] = [];
				}
				$profiles_groups[ $_web_property_id ][] = [
					'id'      => $profile->getId(),
					'name'    => $profile->getName(),
					'website' => $profile->getWebsiteUrl(),
				];
			}
		}

		if ( ! empty( $data ) ) {
			/** @var \ahrefs\AhrefsSeo_Vendor\Google_Service_Analytics_Webproperty $item */
			foreach ( $data as $item ) {
				if ( isset( $profiles_groups[ $item->id ] ) ) {
					foreach ( $profiles_groups[ $item->id ] as $_profile ) {
						$result[] = [
							'ua_id'        => $_profile['id'],
							'account'      => $item->accountId,
							'account_name' => $accounts[ $item->accountId ] ?? '---',
							'name'         => $item->name,
							'view'         => $_profile['name'],
							'website'      => $_profile['website'],
						];
					}
				} else {
					// fill default choice.
					$result[] = [
						'ua_id'        => $item->defaultProfileId,
						'account'      => $item->accountId,
						'account_name' => $accounts[ $item->accountId ] ?? '---',
						'name'         => $item->name,
						/* Translators: part of "default view" */
						'view'         => __( 'default', 'ahrefs-seo' ),
						'website'      => $item->websiteUrl,
					];
				}
			}
		}
		$this->accounts_ga = $result;

		return $result;
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar,WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Get visitors traffic by type for page
	 *
	 * @param array<int|string, string>|null $page_slugs Page url starting with '/'.
	 * @param string                         $start_date Start date.
	 * @param string                         $end_date End date.
	 * @param null|int                       $max_results Max results.
	 * @param null|string                    $ua_id UA id.
	 *
	 * @return array<int|string, array<string, mixed>>|null Array, 'slug' => [ traffic type => visitors number].
	 */
	public function get_visitors_by_page( ?array $page_slugs, string $start_date, string $end_date, ?int $max_results = null, ?string $ua_id = null ) : ?array {
		Ahrefs_Seo::breadcrumbs( __METHOD__ . (string) wp_json_encode( func_get_args() ) );
		// is Analytics enabled?
		if ( ! $this->is_analytics_enabled() ) {
			$this->set_message( __( 'Analytics disconnected.', 'ahrefs-seo' ) );
			$this->service_error = [ [ 'reason' => 'internal-no-token' ] ];

			return null;
		}
		if ( is_null( $ua_id ) && ! $this->is_ua_set() ) {
			$this->set_message( __( 'Please choose Analytics profile.', 'ahrefs-seo' ) );
			$this->service_error = [ [ 'reason' => 'internal-no-profile' ] ];

			return null;
		}
		if ( is_array( $page_slugs ) && ! count( $page_slugs ) ) {
			return [];
		}

		$revert = [];
		if ( is_array( $page_slugs ) ) {
			foreach ( $page_slugs as $key => $value ) {
				$new_url            = $this->url_for_ga( $value );
				$revert[ $new_url ] = $value;
				$page_slugs[ $key ] = $new_url;
			}
		}

		if ( defined( 'AHREFS_SEO_NO_GA' ) && AHREFS_SEO_NO_GA ) {
			if ( is_null( $page_slugs ) ) {
				return [];
			} else {
				$result = [];
				foreach ( $page_slugs as $page_slug ) {
					/**
					 * Modify traffic values.
					 *
					 * @param array<string, int> $traffic The traffic values.
					 * @param string $page_slug Page slug.
					 * @param string $start_date Start date.
					 * @param string $end_date End date.
					 */
					$result[ $page_slug ] = apply_filters(
						'ahrefs_seo_no_ga_visitors_by_page',
						[
							Ahrefs_Seo_Analytics::TRAFFIC_TYPE_TOTAL   => 10,
							Ahrefs_Seo_Analytics::TRAFFIC_TYPE_ORGANIC => 5,
						],
						$page_slug,
						$start_date,
						$end_date
					);
				}

				return $result;
			}
		}

		$result = ( 0 === strpos( is_null( $ua_id ) ? $this->get_data_tokens()->get_ua_id() : $ua_id, 'properties/' ) )
			? $this->get_visitors_by_page_ga4( $page_slugs, $start_date, $end_date, $ua_id )
			: $this->get_visitors_by_page_ga( $page_slugs, $start_date, $end_date, $max_results, $ua_id );

		// add total => 0 to each missing slug.
		if ( ! is_null( $result ) && is_array( $page_slugs ) ) {
			foreach ( $page_slugs as $_slug ) {
				if ( ! isset( $result[ $_slug ] ) ) {
					$result[ $_slug ] = [ Ahrefs_Seo_Analytics::TRAFFIC_TYPE_TOTAL => 0 ];
				}
			}
		}

		// set back to original URLs.
		$result2 = [];
		if ( ! is_null( $result ) ) {
			foreach ( $result as $slug => $value ) {
				if ( isset( $revert[ $slug ] ) ) { // sometimes returned value has chars in different case.
					$result2[ $revert[ $slug ] ] = $value;
				} else {
					$result2[ $slug ] = $value;
				}
			}
		}

		Ahrefs_Seo::breadcrumbs( 'get_visitors_by_page: ' . (string) wp_json_encode( $page_slugs ) . ' results: ' . (string) wp_json_encode( $result2 ) );

		return $result2;
	}

	/**
	 * Prepare URL for GA request.
	 *
	 * @param string $url Original URL.
	 *
	 * @return string
	 * @since 0.9.4
	 */
	private function url_for_ga( string $url ) : string {
		$advanced = new Advanced();
		if ( $advanced->get_adv_ga_uses_full_url() ) {
			$url = Ahrefs_Seo::get_current_domain() . $url;
		}

		return $advanced->get_adv_ga_not_urlencoded() ? urldecode( $url ) : $url;
	}

	/**
	 * Get visitors traffic by type for page for GA4 property, use Google Analytics Data API
	 *
	 * @param null|array<int|string, string> $page_slugs_list Page url starting with '/'.
	 * @param string                         $start_date Start date.
	 * @param string                         $end_date End date.
	 * @param null|string                    $ua_id UA id or null if default UA id used.
	 *
	 * @return array<int|string, array<string, mixed>> Array, 'slug' => [ traffic type => visitors number]
	 * @phpstan-return array<int|string, array<Ahrefs_Seo_Analytics::TRAFFIC_TYPE_ORGANIC|Ahrefs_Seo_Analytics::TRAFFIC_TYPE_TOTAL, int>|array<"error", string>>
	 * @since 0.7.3
	 *
	 * phpcs:ignore Squiz.Commenting.FunctionCommentThrowTag.Missing -- we handle exception.
	 */
	protected function get_visitors_by_page_ga4( ?array $page_slugs_list, string $start_date, string $end_date, ?string $ua_id = null ) : ?array {
		$result = [];
		try {
			$client     = $this->create_client();
			$analytics4 = new Google_Service_AnalyticsData( $client );

			if ( is_null( $ua_id ) ) {
				$ua_id = $this->get_data_tokens()->get_ua_id();
			}
			// numeric part only.
			$property_id = str_replace( 'properties/', '', $ua_id );
			$page_slugs  = empty( $page_slugs_list ) ? null : $page_slugs_list; // receive pages info without slug filter.
			$per_page    = is_array( $page_slugs ) ? count( $page_slugs ) : self::QUERY_TRAFFIC_PER_PAGE;
			$offset      = 0;

			try {
				$data = null;
				// analytics additional parameters.
				$params = [
					'quotaUser' => $this->get_api_user(),
				];
				// get results from GA4.
				try {
					$this->maybe_do_a_pause( 'ga4' );
					$batch   = new BatchRunReportsRequest();
					$request = $this->create_ga4_request( $start_date, $end_date, $per_page, $offset );
					if ( ! is_null( $page_slugs ) ) { // request for specified urls list.
						$this->apply_ga4_filter( $request, $page_slugs );
					}
					if ( ! $this->set_ga4_property( $request, $property_id ) ) {
						/* Translators: 1: version string, 2: function name, 3: line number */
						throw new Ahrefs_Seo_Compatibility_Exception( sprintf( __( 'Unsupported Google Analytics Data API version %1$s at %2$s line %3$d', 'ahrefs-seo' ), $analytics4->version, __METHOD__, __LINE__ ) );
					}
					$request->setReturnPropertyQuota( true );

					$request2 = $this->create_ga4_request( $start_date, $end_date, $per_page, $offset );
					// request for organic traffic and specified urls list.
					$this->apply_ga4_filter( $request2, $page_slugs, true );
					if ( ! $this->set_ga4_property( $request2, $property_id ) ) {
						/* Translators: 1: version string, 2: function name, 3: line number */
						throw new Ahrefs_Seo_Compatibility_Exception( sprintf( __( 'Unsupported Google Analytics Data API version %1$s at %2$s line %3$d', 'ahrefs-seo' ), $analytics4->version, __METHOD__, __LINE__ ) );
					}
					$request2->setReturnPropertyQuota( true );

					$batch->setRequests( [ $request, $request2 ] );
					Ahrefs_Seo::breadcrumbs( sprintf( 'ga4-req prop:%s par:%s batch:%s', 'properties/' . $property_id, (string) wp_json_encode( $params ), (string) wp_json_encode( $batch ) ) );

					if ( property_exists( $analytics4, 'properties' ) && is_object( $analytics4->properties ) && method_exists( $analytics4->properties, 'batchRunReports' ) ) { // @phpstan-ignore-line -- currently unstable v1beta used, next version may not have this property.
						$reports = $analytics4->properties->batchRunReports( 'properties/' . $property_id, $batch, $params );
					} else {
						/* Translators: 1: version string, 2: function name, 3: line number */
						throw new Ahrefs_Seo_Compatibility_Exception( sprintf( __( 'Unsupported Google Analytics Data API version %1$s at %2$s line %3$d', 'ahrefs-seo' ), $analytics4->version, __METHOD__, __LINE__ ) );
					}
					do_action_ref_array( 'ahrefs_seo_api_visitors_by_page_batch_ga4', [ &$reports ] );
					$this->maybe_do_a_pause( 'ga4', true );
				} catch ( Google_Service_Exception $e ) { // catch recoverable errors.
					Ahrefs_Seo::breadcrumbs( sprintf( 'batch:%s resp:%s', (string) wp_json_encode( $batch ?? null ), (string) wp_json_encode( $reports ?? null ) ) );
					$this->maybe_do_a_pause( 'ga4', true );
					$this->service_error = $e->getErrors();
					$this->handle_exception( $e );
					$this->on_error_received( $e, $page_slugs_list );
					throw $e;
				} catch ( GuzzleConnectException $e ) { // catch recoverable errors.
					$this->maybe_do_a_pause( 'ga4', true );
					$this->set_message( $this->extract_message( $e ), $e, (string) wp_json_encode( $request ?? null ) );
					$this->on_error_received( $e, $page_slugs_list );
					throw $e;
				}

				if ( ! empty( $reports ) ) {
					$reports_data = $reports->getReports();

					if ( is_array( $reports_data ) && ( 2 === count( $reports_data ) ) && ( 2 === count(
						array_filter(
							$reports_data,
							function( $item ) {
									return is_object( $item ) && ( $item instanceof RunReportResponse );
							}
						)
					) ) ) {
						$report_total   = $reports_data[0];
						$report_organic = $reports_data[1];

						$rows = $report_total->getRows();
						if ( ! empty( $rows ) ) {
							$this->parse_ga4_rows( $result, $rows, Ahrefs_Seo_Analytics::TRAFFIC_TYPE_TOTAL );
						}
						$rows = $report_organic->getRows();
						if ( ! empty( $rows ) ) {
							$this->parse_ga4_rows( $result, $rows, Ahrefs_Seo_Analytics::TRAFFIC_TYPE_ORGANIC );
						}
					} else {
						Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( 'Incorrect ga4 batch response: ' . (string) wp_json_encode( $reports ) ) );
					}
				}
			} catch ( Error $e ) {
				$message = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );
				$this->set_message( $message );
			} catch ( Exception $e ) {
				$this->handle_exception( $e, true );

				return $this->prepare_answer( $page_slugs_list, __( 'Connection error', 'ahrefs-seo' ) );
			}
		} catch ( Error $e ) {
			$message = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );
			$this->set_message( $message );
		}

		return $result;
	}

	/**
	 * @param array                              $result Destination array with results.
	 * @param Google_Service_AnalyticsData_Row[] $rows Results from GA API.
	 * @param string                             $type Traffic type.
	 *
	 * @return void
	 */
	protected function parse_ga4_rows( array &$result, array $rows, string $type = Ahrefs_Seo_Analytics::TRAFFIC_TYPE_TOTAL ) : void {
		foreach ( $rows as $row ) {
			$dimensions     = $row->getDimensionValues(); // page slug + traffic type.
			$_slug          = $dimensions[0]->getValue();
			$_metrics       = $row->getMetricValues();
			$_traffic_count = (int) ( $_metrics[0]->getValue() ?? 0 );

			if ( ! isset( $result[ $_slug ] ) ) {
				$result[ $_slug ] = [];
			}
			if ( ! isset( $result[ $_slug ][ "$type" ] ) ) {
				$result[ $_slug ][ "$type" ] = $_traffic_count;
			} else {
				$result[ $_slug ][ "$type" ] += $_traffic_count;
			}
		}
	}

	/**
	 * Creates filter for pages list.
	 *
	 * @param array $page_slugs Pages slug list.
	 *
	 * @return Google_Service_AnalyticsData_FilterExpression
	 */
	protected function create_filter_paths( array $page_slugs ) : Google_Service_AnalyticsData_FilterExpression {
		$in_list_filter = new Google_Service_AnalyticsData_InListFilter();
		$in_list_filter->setValues( $page_slugs );

		$filter = new Google_Service_AnalyticsData_Filter();
		$filter->setFieldName( 'pagePath' );
		$filter->setInListFilter( $in_list_filter );

		$dimension_filter = new Google_Service_AnalyticsData_FilterExpression();
		$dimension_filter->setFilter( $filter );

		return $dimension_filter;
	}

	/**
	 * Creates filter for "Organic Search" traffic.
	 *
	 * @return Google_Service_AnalyticsData_FilterExpression
	 */
	protected function create_filter_organic() : Google_Service_AnalyticsData_FilterExpression {
		$string_filter = new Google_Service_AnalyticsData_StringFilter();
		$string_filter->setValue( Ahrefs_Seo_Analytics::TRAFFIC_TYPE_ORGANIC );

		$filter = new Google_Service_AnalyticsData_Filter();
		$filter->setFieldName( 'sessionDefaultChannelGrouping' );
		$filter->setStringFilter( $string_filter );

		$dimension_filter = new Google_Service_AnalyticsData_FilterExpression();
		$dimension_filter->setFilter( $filter );

		return $dimension_filter;
	}

	/**
	 * Apply filters to the prepared request
	 *
	 * @param RunReportRequest $request Request.
	 * @param array|null       $page_slugs List of page url starting with '/'.
	 * @param bool             $only_organic_traffic Load data for Organic traffic type only.
	 *
	 * @return void
	 */
	protected function apply_ga4_filter( RunReportRequest &$request, ?array $page_slugs, bool $only_organic_traffic = false ) : void {
		if ( $only_organic_traffic ) {
			if ( ! is_null( $page_slugs ) ) { // filter by sessionDefaultChannelGrouping = "Organic Search" AND for specified urls list.
				$dimension_filter1 = $this->create_filter_paths( $page_slugs );
				$dimension_filter2 = $this->create_filter_organic();

				$filter_expression_list = new FilterExpressionList();
				$filter_expression_list->setExpressions( [ $dimension_filter1, $dimension_filter2 ] );

				$dimension_filter = new Google_Service_AnalyticsData_FilterExpression();
				$dimension_filter->setAndGroup( $filter_expression_list );

				$request->setDimensionFilter( $dimension_filter );
			} else { // filter only by sessionDefaultChannelGrouping = "Organic Search".
				$dimension_filter = $this->create_filter_organic();
				$request->setDimensionFilter( $dimension_filter );
			}
		} else {
			if ( ! is_null( $page_slugs ) ) { // filter for specified urls list.
				$dimension_filter = $this->create_filter_paths( $page_slugs );
				$request->setDimensionFilter( $dimension_filter );
			}
		}
	}

	/**
	 * Create the request
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @param int    $per_page Number of results.
	 * @param int    $offset Results offset.
	 *
	 * @return RunReportRequest
	 */
	protected function create_ga4_request( string $start_date, string $end_date, int $per_page, int $offset ) : RunReportRequest {
		// Create the DateRange object.
		$date_range = new Google_Service_AnalyticsData_DateRange();
		$date_range->setStartDate( $start_date );
		$date_range->setEndDate( $end_date );

		// Create the Metrics object.
		/** @link https://developers.google.com/analytics/devguides/reporting/data/v1/api-schema#metrics */
		$metric = new Google_Service_AnalyticsData_Metric();
		$metric->setName( 'screenPageViews' ); // "ga:uniquePageviews"

		// Create the Dimension object.
		/** @link https://developers.google.com/analytics/devguides/reporting/data/v1/api-schema#dimensions */
		$dimension1 = new Google_Service_AnalyticsData_Dimension();
		$dimension1->setName( 'pagePath' ); // "ga:pagePath".

		// Create the ReportRequest object.
		$request = new RunReportRequest();
		$request->setDateRanges( $date_range );
		$request->setMetrics( array( $metric ) );
		$request->setDimensions( array( $dimension1 ) );
		$request->setLimit( $per_page );
		$request->setOffset( $offset );

		return $request;
	}

	/**
	 * Fill answers with error message
	 *
	 * @param int[]|string[]|null $page_slugs_list Page slugs list.
	 * @param string              $error_message Error message.
	 *
	 * @return array Index is slug, value is ['error' => $error_message].
	 * @since 0.7.3
	 */
	protected function prepare_answer( ?array $page_slugs_list, string $error_message ) : ?array {
		return is_null( $page_slugs_list ) ? null : array_map(
			function( $slug ) use ( $error_message ) {
				return [ 'error' => $error_message ];
			},
			array_flip( $page_slugs_list )
		);
	}

	/**
	 * Get visitors traffic by type for page for GA property, use Google Analytics Reporting API version 4.
	 *
	 * @param array<int|string, string>|null $page_slugs_list Page url starting with '/'.
	 * @param string                         $start_date Start date.
	 * @param string                         $end_date End date.
	 * @param null|int                       $max_results Max results count.
	 * @param null|string                    $ua_id UA id or null if default UA id used.
	 *
	 * @return array<int|string, array<string, mixed>> Array, 'slug' => [ traffic type => visitors number].
	 * @since 0.7.3
	 *
	 * phpcs:ignore Squiz.Commenting.FunctionCommentThrowTag.Missing -- we handle exception.
	 */
	public function get_visitors_by_page_ga( ?array $page_slugs_list, string $start_date, string $end_date, ?int $max_results = null, ?string $ua_id = null ) : ?array {
		$result = [];
		try {
			$client             = $this->create_client();
			$analyticsreporting = new Google_Service_AnalyticsReporting( $client );
			if ( is_null( $ua_id ) ) {
				$ua_id = $this->get_data_tokens()->get_ua_id();
			}
			$page_slugs = is_null( $page_slugs_list ) ? [ null ] : $page_slugs_list; // receive pages info without slug filter.
			$per_page   = is_null( $max_results ) ? self::QUERY_TRAFFIC_PER_PAGE : $max_results;

			$pages_to_load = array_map(
				function( $slug ) {
					return [
						'slug'       => $slug,
						'next_token' => null,
					]; // later we will add next_token or remove item from the list.
				},
				$page_slugs
			);

			do {
				try {
					$requests = []; // up to 5 requests allowed.
					$data     = null;
					// analytics parameters.
					$params = [
						'quotaUser' => $this->get_api_user(),
					];

					// get results from Google Analytics.
					try {
						$this->maybe_do_a_pause( 'ga' );

						foreach ( $pages_to_load as $page_to_load ) {
							$page_slug  = $page_to_load['slug'];
							$next_token = $page_to_load['next_token'] ?? null;

							// Create the DateRange object.
							$request = $this->create_report_request_object( $start_date, $end_date, $ua_id, $per_page );

							if ( ! is_null( $page_slug ) ) {
								// Create the DimensionFilter.
								$dimension_filter = new Google_Service_AnalyticsReporting_DimensionFilter();
								$dimension_filter->setDimensionName( 'ga:pagePath' );
								$dimension_filter->setOperator( 'EXACT' );
								$dimension_filter->setExpressions( array( $page_slug ) );

								// Create the DimensionFilterClauses.
								$dimension_filter_clause = new Google_Service_AnalyticsReporting_DimensionFilterClause();
								$dimension_filter_clause->setFilters( array( $dimension_filter ) );
								$request->setDimensionFilterClauses( array( $dimension_filter_clause ) );
							}

							if ( ! empty( $next_token ) ) {
								$request->setPageToken( $next_token );
							}

							$requests[] = $request;
						}

						$body = new Google_Service_AnalyticsReporting_GetReportsRequest();
						$body->setReportRequests( $requests );
						$data = $analyticsreporting->reports->batchGet( $body, $params );
						do_action_ref_array( 'ahrefs_seo_api_visitors_by_page_ga', [ &$data ] );
						$this->maybe_do_a_pause( 'ga', true );
					} catch ( Google_Service_Exception $e ) { // catch recoverable errors.
						$this->maybe_do_a_pause( 'ga', true );
						$this->service_error = $e->getErrors();
						$this->handle_exception( $e );
						$this->on_error_received( $e, $page_slugs_list );
						throw $e;
					} catch ( GuzzleRequestException $e ) { // catch recoverable errors.
						$this->maybe_do_a_pause( 'ga', true );
						$this->handle_exception( $e );
						$this->on_error_received( $e, $page_slugs_list );
						throw $e;
					}

					if ( ! is_null( $data ) ) {
						$reports = $data->getReports();
						if ( ! empty( $reports ) ) {
							foreach ( $reports as $index => $report ) {
								$data_items                            = $report->getData();
								$pages_to_load[ $index ]['next_token'] = $report->getNextPageToken();

								// load details from rows.
								$rows = $data_items->getRows();
								if ( ! empty( $rows ) ) {
									foreach ( $rows as $row ) {
										list( $_slug, $_type ) = $row->getDimensions(); // page slug + traffic type.

										$_metrics       = $row->getMetrics();
										$_traffic_count = ( $_metrics[0]->getValues() )[0] ?? 0;

										if ( ! isset( $result[ $_slug ] ) ) {
											$result[ $_slug ] = [];
										}
										if ( ! isset( $result[ $_slug ][ "$_type" ] ) ) {
											$result[ $_slug ][ "$_type" ]                                 = (int) $_traffic_count;
											$result[ $_slug ][ Ahrefs_Seo_Analytics::TRAFFIC_TYPE_TOTAL ] = (int) $_traffic_count + ( $result[ $_slug ][ Ahrefs_Seo_Analytics::TRAFFIC_TYPE_TOTAL ] ?? 0 );
										} else {
											$result[ $_slug ][ "$_type" ]                                 += (int) $_traffic_count;
											$result[ $_slug ][ Ahrefs_Seo_Analytics::TRAFFIC_TYPE_TOTAL ] += (int) $_traffic_count;
										}
									}
								}
								if ( ! is_null( $max_results ) && ( count( $rows ) >= $max_results || count( $result ) >= $max_results ) ) {
									$pages_to_load[ $index ]['next_token'] = null; // do not load more.
								}
							}
						} else {
							$pages_to_load = [];
						}
					} else {
						$pages_to_load = [];
					}
					// remove finished pages (without next_token) from load list.
					$pages_to_load = array_values(
						array_filter(
							$pages_to_load,
							function( $value ) {
								return ! empty( $value['next_token'] );
							}
						)
					);
				} catch ( Error $e ) {
					$message = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );
					$this->set_message( $message );
				} catch ( Exception $e ) {
					$this->handle_exception( $e, true );

					return $this->prepare_answer( $page_slugs_list, __( 'Connection error', 'ahrefs-seo' ) );
				}
				// load until any next page exists, but load only first page with results for the generic request without page ($page_slugs_list is null).
			} while ( ! empty( $pages_to_load ) && ! is_null( $page_slugs_list ) && ! is_null( $data ) );
		} catch ( Error $e ) {
			$message = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );
			$this->set_message( $message );
		}

		return $result;
	}

	/**
	 * Get top pages for current GA profile.
	 *
	 * @return string[]|null
	 * @since 0.9.4
	 */
	public function get_top_ga_results() : ?array {
		if ( ! $this->is_analytics_enabled() ) {
			return null;
		}
		$start_date = (string) date( 'Y-m-d', time() - 3 * MONTH_IN_SECONDS );
		$end_date   = (string) date( 'Y-m-d' );
		$ua_id      = $this->get_data_tokens()->get_ua_id();
		if ( '' === $ua_id ) {
				return null;
		}
		if ( 0 === strpos( $ua_id, 'properties/' ) ) {
			$result = $this->get_found_pages_by_ua_id_ga4( [ $ua_id ], $start_date, $end_date, false );
		} else {
			$result = $this->get_found_pages_by_ua_id_ga( [ $ua_id ], $start_date, $end_date, false );
		}
		$item = array_shift( $result );
		return is_array( $item ) ? $item : null;
	}

	/**
	 * Get visitors traffic by type for page for GA property.
	 *
	 * @param string[] $ua_ids UA ids list to check.
	 * @param string   $start_date Start date.
	 * @param string   $end_date End date.
	 * @param bool     $return_count Return count of found pages or pages slugs list.
	 *
	 * @return array<string, null|int>|array<string, null|string[]> Array, [ ua_id => pages_found ].
	 * @phpstan-return ($return_count is true ? array<string, null|int> : array<string, null|string[]>)
	 * @since 0.7.3
	 */
	private function get_found_pages_by_ua_id_ga( array $ua_ids, string $start_date, string $end_date, bool $return_count = true ) : array {
		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$results = [];
		try {
			$client = $this->create_client();
			$client->setUseBatch( true );

			$analyticsreporting = new Google_Service_AnalyticsReporting( $client );

			$per_page = $return_count ? self::QUERY_DETECT_GA_LIMIT : 1000; // used as per page parameter, but really we load first page only.
			do { // for ua_ids parts.
				$ua_id_list = array_splice( $ua_ids, 0, 5 ); // max 5 requests per batch.
				try {
					$data = null;
					// analytics parameters.
					$params = [
						'quotaUser' => $this->get_api_user(),
					];

					// get results from Google Analytics.
					try {
						$this->maybe_do_a_pause( 'ga' );
						$batch = new Google_Http_Batch(
							$client,
							false,
							$analyticsreporting->rootUrl,
							$analyticsreporting->batchPath
						);
						$this->maybe_do_a_pause( 'ga', true );

						foreach ( $ua_id_list as $ua_id ) {
							// Create the DateRange object.
							$request = $this->create_report_request_object( $start_date, $end_date, $ua_id, $per_page );

							$body = new Google_Service_AnalyticsReporting_GetReportsRequest();
							$body->setReportRequests( [ $request ] );
							$prepared_queries = $analyticsreporting->reports->batchGet( $body, $params );
							$batch->add( $prepared_queries, $ua_id );
						}
						$data = $batch->execute();
					} catch ( Google_Service_Exception $e ) { // try to continue, but report error.
						Ahrefs_Seo_Errors::save_message( 'google', $e->getMessage(), Message::TYPE_NOTICE );
						Ahrefs_Seo::notify( $e, 'autodetect ga' );
					} catch ( GuzzleConnectException $e ) { // try to continue, but report error.
						Ahrefs_Seo_Errors::save_message( 'google', $e->getMessage(), Message::TYPE_NOTICE );
						Ahrefs_Seo::notify( $e, 'autodetect ga' );
					}
					if ( ! is_null( $data ) ) {
						foreach ( $data as $index => $values ) {
							$result      = [];
							$result_list = [];
							$index       = str_replace( 'response-', '', $index );
							if ( $values instanceof Exception ) {
								$results[ "$index" ] = null;
								continue;
							}
							$reports = $values->getReports();
							if ( ! empty( $reports ) ) {
								foreach ( $reports as $report ) {
									$data_items = $report->getData();
									// load details from rows.
									$rows = $data_items->getRows();
									if ( ! empty( $rows ) ) {
										foreach ( $rows as $row ) {
											// if we here - the traffic at page is not empty.
											list( $_slug, $_type ) = $row->getDimensions(); // page slug + traffic type.
											if ( ! isset( $result[ $_slug ] ) ) {
												$result[ $_slug ] = true;
												$result_list[]    = $_slug;
											}
										}
									}

									if ( $return_count ) {
										$count = 0;
										if ( ! empty( $result ) ) {
											$result = array_keys( $result );
											array_walk(
												$result,
												function( $slug ) use ( &$count ) {
													$post = get_page_by_path( "$slug", OBJECT, [ 'post', 'page' ] );
													if ( $post instanceof \WP_Post ) {
														$count++;
													}
												}
											);
										}
										$results[ "$index" ] = $count;
									} else {
										$results[ "$index" ] = $result_list;
									}
								}
							}
						}
					}
				} catch ( Error $e ) {
					$message = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );
					$this->set_message( $message );
				} catch ( Exception $e ) {
					$this->handle_exception( $e, true );

					return $results;
				}
				// load until any next page exists, but load only first page with results for the generic request without page ($page_slugs_list is null).
			} while ( ! empty( $ua_ids ) );
		} catch ( Error $e ) {
			$message = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );
			$this->set_message( $message );
		} finally {
			if ( ! empty( $client ) ) {
				$client->setUseBatch( false );
			}
		}

		return $results;
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Check that currently selected GA account has same domain in website property as current site has.
	 * Ignore empty value.
	 *
	 * @param string|null $ua_url Check current GA account if null.
	 *
	 * @return bool|null Null if nothing to check
	 */
	public function is_ga_account_correct( ?string $ua_url = null ) : ?bool {
		if ( is_null( $ua_url ) ) {
			$ua_url = $this->get_data_tokens()->get_ua_url(); // use current account.
		}
		if ( '' === $ua_url ) {
			return null; // nothing to check.
		}
		$domain = strtolower( Ahrefs_Seo::get_current_domain() );
		if ( 0 === strpos( $domain, 'www.' ) ) { // remove www. prefix from domain.
			$domain = substr( $domain, 4 );
		}
		$sites = explode( '|', $ua_url );
		foreach ( $sites as $site_url ) {
			$_website = strtolower( (string) wp_parse_url( $site_url, PHP_URL_HOST ) );
			if ( '' === $_website ) { // incorrect URL, maybe the domain name used here?
				$_website = strtolower( $site_url );
			}
			if ( 0 === strpos( $_website, 'www.' ) ) { // remove www. prefix from domain.
				$_website = substr( $_website, 4 );
			}
			if ( $_website === $domain ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get visitors traffic by type for page for GA4 property.
	 *
	 * @param string[] $ua_ids UA ids list to check.
	 * @param string   $start_date Start date.
	 * @param string   $end_date End date.
	 * @param bool     $return_count Return count of found pages or pages slugs list.
	 *
	 * @return array<string, null|int>|array<string, null|string[]> Array, [ ua_id => pages_found ].
	 * @since 0.7.3
	 *
	 * phpcs:ignore Squiz.Commenting.FunctionCommentThrowTag.Missing -- we handle exception.
	 */
	public function get_found_pages_by_ua_id_ga4( array $ua_ids, string $start_date, string $end_date, bool $return_count = true ) : array {
		$results = [];
		try {
			$client = $this->create_client();
			$client->setUseBatch( true );

			$analytics4 = new Google_Service_AnalyticsData( $client );

			$per_page = $return_count ? self::QUERY_DETECT_GA_LIMIT : 1000;
			do { // for ua_ids parts.
				$ua_id_list = array_splice( $ua_ids, 0, 5 ); // max 5 requests per batch.
				$result     = [];

				try {
					$data = null;
					// analytics parameters.
					$params = [
						'quotaUser' => $this->get_api_user(),
					];

					// get results from Google Analytics.
					try {
						$this->maybe_do_a_pause( 'ga4' );

						$batch = new Google_Http_Batch(
							$client,
							false,
							$analytics4->rootUrl, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
							$analytics4->batchPath // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						);
						$this->maybe_do_a_pause( 'ga4', true );

						foreach ( $ua_id_list as $ua_id ) {
							$property_id = str_replace( 'properties/', '', $ua_id );
							// Create the DateRange object.
							$date_range = new Google_Service_AnalyticsData_DateRange();
							$date_range->setStartDate( $start_date );
							$date_range->setEndDate( $end_date );

							// Create the Metrics object.
							/** @link https://developers.google.com/analytics/devguides/reporting/data/v1/api-schema#metrics */
							$metric = new Google_Service_AnalyticsData_Metric();
							$metric->setName( 'screenPageViews' ); // "ga:uniquePageviews"

							// Create the Dimension object.
							/** @link https://developers.google.com/analytics/devguides/reporting/data/v1/api-schema#dimensions */
							$dimension1 = new Google_Service_AnalyticsData_Dimension();
							$dimension1->setName( 'pagePathPlusQueryString' ); // "ga:pagePath".

							// Create the ReportRequest object.
							$request = new RunReportRequest();
							$request->setDateRanges( $date_range );
							$request->setMetrics( array( $metric ) );
							$request->setDimensions( array( $dimension1 ) );
							$request->setLimit( $per_page );
							$request->setOffset( 1 );
							if ( ! $this->set_ga4_property( $request, $property_id ) ) {
								throw new Ahrefs_Seo_Compatibility_Exception( sprintf( 'Unsupported Google Analytics Data API version %s at %s line %d', $analytics4->version, __METHOD__, __LINE__ ) );
							}

							if ( property_exists( $analytics4, 'properties' ) && is_object( $analytics4->properties ) && method_exists( $analytics4->properties, 'runReport' ) ) { // @phpstan-ignore-line -- next version may not have this property.
								$query = $analytics4->properties->runReport( 'properties/' . $property_id, $request, $params );
								$batch->add( $query, 'properties/' . $property_id );
							} else {
								throw new Ahrefs_Seo_Compatibility_Exception( sprintf( 'Unsupported Google Analytics Data API version %s at %s line %d', $analytics4->version, __METHOD__, __LINE__ ) );
							}
						}
						$data = $batch->execute();
					} catch ( Google_Service_Exception $e ) { // try to continue, but report error.
						Ahrefs_Seo_Errors::save_message( 'google', $e->getMessage(), Message::TYPE_NOTICE );
						Ahrefs_Seo::notify( $e, 'autodetect ga4' );
					} catch ( GuzzleConnectException $e ) { // try to continue, but report error.
						Ahrefs_Seo_Errors::save_message( 'google', $e->getMessage(), Message::TYPE_NOTICE );
						Ahrefs_Seo::notify( $e, 'autodetect ga4' );
					}
					if ( ! is_null( $data ) ) {
						foreach ( $data as $index => $report ) {
							$index = str_replace( 'response-', '', $index );
							if ( $report instanceof Exception ) {
								$results[ "$index" ] = null;
								continue;
							}
							$result      = [];
							$result_list = [];
							$rows        = $report->getRows();
							$count       = 0;
							if ( ! empty( $rows ) ) {
								foreach ( $rows as $row ) {
									$dimensions = $row->getDimensionValues(); // page slug.
									$_slug      = $dimensions[0]->getValue();
									if ( ! isset( $result[ $_slug ] ) ) {
										$result[ $_slug ] = true;
										$result_list[]    = $_slug;
									}
								}

								if ( ! empty( $result ) && $return_count ) {
									$result = array_keys( $result );
									array_walk(
										$result,
										function( $slug ) use ( &$count ) {
											$post = get_page_by_path( "$slug", OBJECT, [ 'post', 'page' ] );
											if ( $post instanceof \WP_Post ) {
												$count++;
											}
										}
									);
								}
							}
							$results[ "$index" ] = $return_count ? $count : $result_list;
						}
					}
				} catch ( Error $e ) {
					$message = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );
					$this->set_message( $message );
				} catch ( Exception $e ) {
					$this->handle_exception( $e, true );

					return $results;
				}
				// load until any next page exists, but load only first page with results for the generic request without page ($page_slugs_list is null).
			} while ( ! empty( $ua_ids ) );
		} catch ( Error $e ) {
			$message = Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );
			$this->set_message( $message );
		} finally {
			if ( ! empty( $client ) ) {
				$client->setUseBatch( false );
			}
		}

		return $results;
	}

	/**
	 * Return number of pages found in GA or GA4 account.
	 *
	 * @param string[] $ua_ids UA ids list.
	 *
	 * @return null|array<string, int|null> Index is ua_id, value is number of found pages.
	 */
	private function check_ga_using_top_traffic_pages( array $ua_ids ) : ?array {
		if ( ! $this->is_analytics_enabled() ) {
			return null;
		}
		$results    = [];
		$start_date = date( 'Y-m-d', time() - 3 * MONTH_IN_SECONDS );
		$end_date   = date( 'Y-m-d' );

		$ua_ids_ga  = [];
		$ua_ids_ga4 = [];

		foreach ( $ua_ids as $ua_id ) {
			if ( 0 === strpos( $ua_id, 'properties/' ) ) {
				$ua_ids_ga4[] = $ua_id;
			} else {
				$ua_ids_ga[] = $ua_id;
			}
		}
		if ( count( $ua_ids_ga ) ) {
			$results = $this->get_found_pages_by_ua_id_ga( $ua_ids_ga, $start_date, $end_date );
		}
		if ( count( $ua_ids_ga4 ) ) {
			$results = $results + $this->get_found_pages_by_ua_id_ga4( $ua_ids_ga4, $start_date, $end_date ); // save indexes.
		}

		return $results;
	}

	/**
	 * Set property id for GA4 request using v1beta API.
	 *
	 * @param RunReportRequest $request Request.
	 * @param string           $property_id Property id.
	 *
	 * @return bool False on error.
	 * @since 0.8.2
	 */
	protected function set_ga4_property( RunReportRequest &$request, string $property_id ) : bool {
		if ( method_exists( $request, 'setProperty' ) ) { // @phpstan-ignore-line -- currently unstable v1beta used, next version may not have this property.
			$request->setProperty( 'properties/' . $property_id );
		} else {
			return false;
		}

		return true;
	}

	/**
	 * Is this a GA4 property?
	 *
	 * @param string $ua_id Identifier to check.
	 *
	 * @return bool
	 * @since 0.9.12
	 */
	private function is_ga4_property( string $ua_id ) : bool {
		return ( 0 === strpos( $ua_id, 'properties/' ) );
	}

	/**
	 * @return int Get number of items in single request.
	 * @since 0.9.12
	 */
	public function get_max_request_items() : int {
		return $this->is_ga4_property( $this->get_data_tokens()->get_ua_id() ) ? self::REQUEST_SIZE_GA4 : self::REQUEST_SIZE_GA;
	}

	/**
	 * Creates ReportRequest instance.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @param string $ua_id UA id.
	 * @param int    $per_page Items count per page.
	 *
	 * @return Google_Service_AnalyticsReporting_ReportRequest
	 */
	private function create_report_request_object( string $start_date, string $end_date, string $ua_id, int $per_page ): Google_Service_AnalyticsReporting_ReportRequest {
		$date_range = new Google_Service_AnalyticsReporting_DateRange();
		$date_range->setStartDate( $start_date );
		$date_range->setEndDate( $end_date );

		// Create the Metrics object.
		$metric1 = new Google_Service_AnalyticsReporting_Metric();
		$metric1->setExpression( 'ga:uniquePageviews' );

		// Create the Dimensions object.
		$dimension1 = new Google_Service_AnalyticsReporting_Dimension();
		$dimension1->setName( 'ga:pagePath' );

		/** @link https://ga-dev-tools.appspot.com/dimensions-metrics-explorer/#ga:channelGrouping */
		$dimension2 = new Google_Service_AnalyticsReporting_Dimension();
		$dimension2->setName( 'ga:channelGrouping' );

		// Create the ReportRequest object.
		$request = new Google_Service_AnalyticsReporting_ReportRequest();

		$request->setViewId( $ua_id );
		$request->setDateRanges( $date_range );
		$request->setDimensions( array( $dimension1, $dimension2 ) );
		$request->setMetrics( array( $metric1 ) );
		$request->setPageSize( $per_page );

		return $request;
	}
}
