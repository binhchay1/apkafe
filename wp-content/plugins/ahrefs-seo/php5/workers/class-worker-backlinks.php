<?php

namespace ahrefs\AhrefsSeo\Workers;

use ahrefs\AhrefsSeo\Ahrefs_Seo_Api;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Errors;
use ahrefs\AhrefsSeo\Data_Api\Data_Metrics_Extended;
use ahrefs\AhrefsSeo\Messages\Message;
use ahrefs\AhrefsSeo\Post_Tax;
/**
 * Worker_Backlinks class.
 * Load traffic details.
 *
 * @since 0.7.3
 */
class Worker_Backlinks extends Worker {

	const API_NAME       = 'ahrefs';
	const WHAT_TO_UPDATE = 'backlinks';
	/**
	 * @var int Load up to (number) items in same request. Ahrefs API does not support bulk requests.
	 */
	protected $items_at_once = 1;
	/**
	 * Run update for items in list
	 *
	 * @param Post_Tax[] $post_taxes Post ID list.
	 * @return bool False if rate limit error received and need to do pause.
	 */
	protected function update_posts( array $post_taxes ) {
		if ( is_null( $this->api ) || ! $this->api instanceof Ahrefs_Seo_Api ) {
			$this->api = Ahrefs_Seo_Api::get();
		}
		$this->update_posts_info( $post_taxes );
		return ! $this->has_rate_error;
	}
	/**
	 * Update post with the info from Ahrefs
	 *
	 * @param Post_Tax[] $post_taxes Post ID list.
	 *
	 * @return void
	 */
	public function update_posts_info( array $post_taxes ) {
		if ( is_null( $this->api ) || ! $this->api instanceof Ahrefs_Seo_Api ) {
			$this->api = Ahrefs_Seo_Api::get();
		}
		$api = $this->api;
		if ( $api->is_disconnected() ) {
			$message = __( 'Ahrefs account is not connected.', 'ahrefs-seo' );
			$this->set_backlinks_error( $post_taxes, $message );
			Ahrefs_Seo_Errors::save_message( 'ahrefs', $message, Message::TYPE_ERROR );
			return;
		}
		foreach ( $post_taxes as $post_tax ) {
			$url = $post_tax->get_url();
			if ( ! empty( $url ) ) {
				// query using page url.
				$data = $api->get_count_by_url( $url );
				if ( ! $data->is_error() ) {
					// update.
					$this->content_audit->update_backlinks_values( $post_tax, $data );
				} else { // some error.
					$error = $api->get_last_error();
					$this->content_audit->update_backlinks_values( $post_tax, $data, $error );
					Ahrefs_Seo_Errors::save_message( 'ahrefs', $error, Message::TYPE_ERROR );
				}
			} else {
				$this->content_audit->update_backlinks_values( $post_tax, Data_Metrics_Extended::error(), __( 'This page cannot be found. It is possible that you’ve archived the page or changed the page ID. Please reload the page & try again.', 'ahrefs-seo' ) );
			}
		}
	}
	/**
	 * Set error in DB for posts.
	 *
	 * @param Post_Tax[] $post_taxes What to update.
	 * @param string     $message Message to set.
	 * @return void
	 */
	protected function set_backlinks_error( array $post_taxes, $message ) {
		foreach ( $post_taxes as $post_tax ) {
			$this->content_audit->update_backlinks_values( $post_tax, Data_Metrics_Extended::error(), $message );
		}
	}
}