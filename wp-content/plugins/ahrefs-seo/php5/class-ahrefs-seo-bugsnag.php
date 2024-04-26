<?php

namespace ahrefs\AhrefsSeo;

use Bugsnag\Breadcrumbs\Breadcrumb;
use ahrefs\AhrefsSeo\Third_Party\Sources;
use ahrefs\AhrefsSeo_Vendor\Google_Client;
use ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Exception\ConnectException;
use ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Exception\RequestException;
use ahrefs\AhrefsSeo_Vendor\Google_Service_Exception;
use Exception;
/**
 * Class for interacting with Bugsnag.
 * No need in translations.
 */
class Ahrefs_Seo_Bugsnag {

	/**
	 * @var string
	 */
	private $api_key = '476ca398513aa4c0b66b02a9cbd0bed4';
	/**
	 * @var ?Ahrefs_Seo_Bugsnag
	 */
	private static $instance = null;
	/**
	 * Get instance
	 *
	 * @return Ahrefs_Seo_Bugsnag
	 */
	public static function get() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Create Bugsnag client instance
	 *
	 * @return \Bugsnag\Client
	 */
	public function create_client() {
		$client = \Bugsnag\Client::make( $this->api_key );
		$client->setAppVersion( AHREFS_SEO_VERSION );
		$client->setReleaseStage( AHREFS_SEO_RELEASE );
		try {
			$client->registerCallback( [ $this, 'report_callback' ] );
			$client->setStripPathRegex( sprintf( '/^%s[\\/\\\\]/', preg_quote( rtrim( ABSPATH, '\\/' ), '/' ) ) );
		} catch ( Exception $e ) {
			$client->notifyException( $e );
		}
		return $client;
	}
	/**
	 * Callback for reports
	 *
	 * @param \Bugsnag\Report $report Bugsnag report instance.
	 * @return false|void
	 */
	public function report_callback( $report ) {
		try {
			// Callback. Do not use parameter types.
			if ( ! $report instanceof \Bugsnag\Report ) {
				return;
			}
			$frames                = $report->getStacktrace()->getFrames();
			$found_in_stacktrace   = 0;
			$is_loading_textdomain = false;
			$translation_in_plugin = false;
			$translation_called    = false;
			foreach ( $frames as &$frame ) {
				if ( false !== strpos( $frame['file'], 'ahrefs-seo' ) && false === strpos( $frame['file'], 'ahrefs-seo' . DIRECTORY_SEPARATOR . 'vendor' ) ) {
					$found_in_stacktrace++;
					if ( false !== strpos( $frame['file'], 'class-ahrefs-seo.php' ) && false !== strpos( $frame['method'], '\\Ahrefs_Seo::load_textdomain' ) ) {
						$is_loading_textdomain = true;
					}
					if ( $translation_called ) {
						$translation_in_plugin = true;
					}
				} else {
					$translation_called = strpos( $frame['file'], DIRECTORY_SEPARATOR . 'l10n.php' ) && in_array( $frame['method'], [ '__', '_e', 'esc_html__', 'esc_html_e' ], true );
				}
			}
			if ( ! $found_in_stacktrace || $is_loading_textdomain && 1 === $found_in_stacktrace ) {
				return false;
			}
			$original_error = $report->getOriginalError();
			if ( is_array( $original_error ) && isset( $original_error['code'] ) && isset( $original_error['file'] ) && E_DEPRECATED === $original_error['code'] && strpos( $original_error['file'], 'vendor-prefixed' . DIRECTORY_SEPARATOR . 'google' ) ) {
				return false;
			}
			if ( $translation_in_plugin ) {
				Ahrefs_Seo::breadcrumbs( 'Translation error: ' . (string) wp_json_encode( $original_error ) . ' frames: ' . (string) wp_json_encode( $frames ), true );
				return false;
			}
			if ( is_array( $original_error ) && isset( $original_error['message'] ) && strpos( $original_error['message'], 'is marked as crashed and should be repaired' ) ) {
				Ahrefs_Seo::breadcrumbs( 'Table crashed: ' . (string) wp_json_encode( $original_error ), true );
				return false;
			}
			$report->setMetaData(
				[
					'info' => [
						'google-client'  => Google_Client::LIBVER,
						'db-tables'      => Ahrefs_Seo::CURRENT_TABLE_VERSION,
						'content-rules'  => Ahrefs_Seo::CURRENT_CONTENT_RULES,
						'transient-time' => Ahrefs_Seo::transient_time(),
						'domain'         => Ahrefs_Seo::get_current_domain(),
						'url'            => home_url(),
						'wpurl'          => site_url(),
						'wordpress'      => get_bloginfo( 'version' ),
						'abspath'        => ABSPATH,
						'multisite'      => is_multisite() ? 'Yes' : 'No',
						'blog'           => get_current_blog_id(),
						'uid'            => get_current_user_id(),
						'thread'         => Ahrefs_Seo::thread_id(),
						'seo-list'       => Sources::get()->get_versions(),
						'translate'      => Helper_Content::get_info(),
						'auth'           => defined( 'AHREFS_SEO_PROXY_ROOT' ) ? AHREFS_SEO_PROXY_ROOT : 'default',
					],
				]
			);
			$report->setUser( [ 'id' => Ahrefs_Seo::get_current_domain() ] );
			if ( $original_error instanceof Exception ) { // only for Exceptions, not for Errors.
				$this->set_grouping_hash( $report );
			} elseif ( is_array( $original_error ) ) {
				$this->set_grouping_hash_for_error( $report, $original_error );
			}
		} catch ( Exception $e ) {
			$report->addBreadcrumb( new Breadcrumb( 'Error in callback: ' . (string) $e, Breadcrumb::ERROR_TYPE ) );
		}
	}
	/**
	 * Set grouping hash for API request reports
	 *
	 * @since 0.8.0
	 *
	 * @param \Bugsnag\Report $report Bugsnag report instance.
	 * @return void
	 */
	public function set_grouping_hash( \Bugsnag\Report $report ) {
		try {
			$api_type = '';
			$methods  = [
				'ahrefs\\AhrefsSeo\\Ahrefs_Seo_Analytics::get_clicks_and_impressions_by_urls' => 'gsc-keywords',
				'ahrefs\\AhrefsSeo\\Ahrefs_Seo_Analytics::get_position_fast' => 'gsc-position',
				'ahrefs\\AhrefsSeo\\Ahrefs_Seo_Analytics::get_visitors_by_page_ga4' => 'ga4-traffic',
				'ahrefs\\AhrefsSeo\\Ahrefs_Seo_Analytics::get_visitors_by_page_ga' => 'ga-traffic',
				'ahrefs\\AhrefsSeo\\Ahrefs_Seo_Noindex::is_noindex'        => 'noindex',
				'ahrefs\\AhrefsSeo\\Ahrefs_Seo_Api::get_count_by_url'      => 'ahrefs-metrics_extended',
				'ahrefs\\AhrefsSeo\\Ahrefs_Seo_Api::get_subscription_info' => 'ahrefs-subscription_info',
			];
			$frames   = $report->getStacktrace()->getFrames();
			foreach ( $frames as &$frame ) {
				if ( false !== strpos( $frame['file'], 'ahrefs-seo' ) && isset( $frame['method'] ) && isset( $methods[ $frame['method'] ] ) ) {
					$api_type = $methods[ $frame['method'] ];
					break;
				}
			}
			if ( '' !== $api_type ) {
				$hash           = null;
				$original_error = $report->getOriginalError();
				if ( $original_error instanceof Exception ) {
					$hash = $this->exception_description( $original_error );
				}
				if ( ! is_null( $hash ) ) {
					$report->setGroupingHash( "{$api_type}-{$hash}" );
				}
			}
		} catch ( Exception $e ) {
			$report->addBreadcrumb( new Breadcrumb( 'Error in grouping: ' . (string) $e, Breadcrumb::ERROR_TYPE ) );
		}
	}
	/**
	 * Set grouping hash for Error reports
	 *
	 * @since 0.9.0
	 *
	 * @param \Bugsnag\Report $report Bugsnag report instance.
	 * @param array           $original_error Array with original error fields.
	 * @return void
	 */
	public function set_grouping_hash_for_error( \Bugsnag\Report $report, array $original_error ) {
		try {
			if ( isset( $original_error['code'] ) && isset( $original_error['message'] ) ) {
				$hash   = '';
				$errors = [
					'!Maximum execution time .*? exceeded!si' => 'max-execution-time',
					'!Allowed memory size .*? exhausted!si' => 'allowed-memory-size-exhausted',
					'!Out of memory!si' => 'out-of-memory',
				];
				foreach ( $errors as $preg => $error_type ) {
					if ( preg_match( $preg, $original_error['message'] ) ) {
						$hash = $error_type;
						break;
					}
				}
				if ( '' !== $hash ) {
					$report->setGroupingHash( "error-{$original_error['code']}-{$hash}" );
				}
			}
		} catch ( Exception $e ) {
			$report->addBreadcrumb( new Breadcrumb( 'Error in grouping error: ' . (string) $e, Breadcrumb::ERROR_TYPE ) );
		}
	}
	/**
	 * Get custom group name for exception
	 *
	 * @since 0.8.0
	 *
	 * @param Exception $e Exception.
	 * @return string|null
	 */
	protected function exception_description( Exception $e ) {
		$hash = 'other';
		if ( $e instanceof Google_Service_Exception ) {
			$hash  = 'google-other';
			$code  = $e->getCode();
			$error = $e->getErrors();
			if ( is_array( $error ) && isset( $error[0] ) && isset( $error[0]['reason'] ) ) {
				$hash = "google-{$code}-{$error[0]['reason']}";
			} elseif ( preg_match( '/<title>Error (\\d+)/', $e->getMessage(), $m ) ) {
				// parse html title.
				$hash = "google-html-{$m[1]}"; // example: 'google-html-502'.
			}
		} elseif ( $e instanceof ConnectException ) {
			$message = $e->getMessage();
			$hash    = 'guzzle-connect-other';
			if ( preg_match( '/^cURL error (\\d+):/', $message, $m ) ) {
				$hash = "guzzle-connect-curl-{$m[1]}"; // example: 'guzzle-connect-curl-28'.
			}
		} elseif ( $e instanceof RequestException ) {
			$message = $e->getMessage();
			$hash    = 'guzzle-request-other';
			if ( preg_match( '/^cURL error (\\d+):/', $message, $m ) ) {
				$hash = "guzzle-request-curl-{$m[1]}"; // example: 'guzzle-request-curl-60'.
			} elseif ( preg_match( '/^(\\w+) error: `[^`]+` resulted in a `(\\d+) ([^`]+)`/', $message, $m ) ) {
				$what = strtolower( $m[1] ); // one of 'client' or 'server'.
				$code = (int) $m[2]; // http error code.
				$hash = "guzzle-{$what}-{$code}"; // example: 'guzzle-client-401' or 'guzzle-server-503'.
			}
		} elseif ( $e instanceof Ahrefs_Seo_Exception ) {
			/** @var Ahrefs_Seo_Exception $e */
			$hash           = 'ahrefs_exception';
			$original_error = $e->getPrevious();
			if ( $original_error instanceof Exception ) {
				$hash .= '-' . $this->exception_description( $original_error );
			}
		}
		return $hash;
	}
}