<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Workers;

use ahrefs\AhrefsSeo\Ahrefs_Seo;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Errors;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Compatibility;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Noindex;
use ahrefs\AhrefsSeo\Messages\Message;
use ahrefs\AhrefsSeo\Post_Tax;
use ahrefs\AhrefsSeo\Third_Party\Result_Canonical;
use ahrefs\AhrefsSeo\Third_Party\Result_Noindex;
use ahrefs\AhrefsSeo\Third_Party\Result_Redirected;
use Error;
use Exception;

/**
 * Worker_Noindex class.
 * Load is noindex details.
 *
 * @since 0.7.3
 */
class Worker_Noindex extends Worker {

	public const API_NAME          = 'noindex';
	protected const WHAT_TO_UPDATE = 'isnoindex';

	/**
	 * @var null|Ahrefs_Seo_Noindex
	 */
	protected $api = null;

	/**
	 * @var int Load up to 5 pages in same request.
	 */
	protected $items_at_once = 5;

	/** @var float Delay after successful request to API */
	protected $pause_after_success = 1;

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
	 * Update post with the is noindex, is non-canonical, is redirected.
	 *
	 * @param Post_Tax[] $post_taxes What to update.
	 *
	 * @return void
	 */
	public function update_posts_info( array $post_taxes ) : void {
		$page_id_to_url_list = [];
		foreach ( $post_taxes as $post_tax ) {
			$url = $post_tax->get_url();
			if ( '' !== $url ) {
				$page_id_to_url_list[ (string) $post_tax ] = $url;
			} else {
				$this->content_audit->update_noindex_values( ( new Result_Noindex( $post_tax, null, null ) )->set_is_noindex( -1 ) ); // set error.
				$this->content_audit->update_canonical_values( ( new Result_Canonical( $post_tax, null, null ) )->set_is_noncanonical( -1 ) ); // set error.
				$this->content_audit->update_redirected_values( ( new Result_Redirected( $post_tax, null, null ) )->set_is_redirected( -1 ) ); // set error.
			}
		}

		if ( is_null( $this->api ) || ! ( $this->api instanceof Ahrefs_Seo_Noindex ) ) {
			$this->api = new Ahrefs_Seo_Noindex();
		}
		$api = $this->api;

		$results = $api->is_noindex( $page_id_to_url_list );
		$this->update_noindex_values( $results );

		$results = $api->is_noncanonical( $page_id_to_url_list );
		$this->update_noncanonical_values( $results );

		$results = $api->is_redirected( $page_id_to_url_list );
		$this->update_redirected_values( $results );
	}

	/**
	 * Update noindex values using results
	 *
	 * @param array<string, Result_Noindex> $results Associative array page_tax_string => Result_Noindex.
	 * @return void
	 */
	protected function update_noindex_values( array $results ) : void {
		Ahrefs_Seo::breadcrumbs( sprintf( '%s: %s', __METHOD__, (string) wp_json_encode( $results ) ) );
		foreach ( $results as $value ) {
			try {
				$this->content_audit->update_noindex_values( $value );
			} catch ( Error $e ) {
				Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__, __( 'Update "is noindex" info failed.', 'ahrefs-seo' ) );
			} catch ( Exception $e ) {
				Ahrefs_Seo::notify( $e, 'Update "is noindex" info failed.' );
				Ahrefs_Seo_Errors::save_message( 'noindex', __( 'Update "is noindex" info failed.', 'ahrefs-seo' ), Message::TYPE_NOTICE );
			}
		}
	}

	/**
	 * Update non-canonical values using results
	 *
	 * @since 0.9.1
	 *
	 * @param array<string, Result_Canonical> $results Associative array page_tax_string => Result_Canonical.
	 * @return void
	 */
	protected function update_noncanonical_values( array $results ) : void {
		Ahrefs_Seo::breadcrumbs( sprintf( '%s: %s', __METHOD__, (string) wp_json_encode( $results ) ) );
		foreach ( $results as $value ) {
			try {
				$this->content_audit->update_canonical_values( $value );
			} catch ( Error $e ) {
				Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__, __( 'Update "is non-canonical" info failed.', 'ahrefs-seo' ) );
			} catch ( Exception $e ) {
				Ahrefs_Seo::notify( $e, 'Update "is non-canonical" info failed.' );
				Ahrefs_Seo_Errors::save_message( 'noindex', __( 'Update "is non-canonical" info failed.', 'ahrefs-seo' ), Message::TYPE_NOTICE );
			}
		}
	}

	/**
	 * Update redirected values using results
	 *
	 * @since 0.9.2
	 *
	 * @param array<string,Result_Redirected> $results Associative array page_tax_string => Result_Redirected.
	 * @return void
	 */
	protected function update_redirected_values( array $results ) : void {
		Ahrefs_Seo::breadcrumbs( sprintf( '%s: %s', __METHOD__, (string) wp_json_encode( $results ) ) );
		foreach ( $results as $value ) {
			try {
				$this->content_audit->update_redirected_values( $value );
			} catch ( Error $e ) {
				Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__, __( 'Update "is redirected" info failed.', 'ahrefs-seo' ) );
			} catch ( Exception $e ) {
				Ahrefs_Seo::notify( $e, 'Update "is non-canonical" info failed.' );
				Ahrefs_Seo_Errors::save_message( 'noindex', __( 'Update "is redirected" info failed.', 'ahrefs-seo' ), Message::TYPE_NOTICE );
			}
		}
	}

}
