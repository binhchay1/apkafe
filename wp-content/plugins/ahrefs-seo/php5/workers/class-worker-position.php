<?php

namespace ahrefs\AhrefsSeo\Workers;

use ahrefs\AhrefsSeo\Ahrefs_Seo;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Data_Content;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Keywords;
use ahrefs\AhrefsSeo\Post_Tax;
use ahrefs\AhrefsSeo\Post_Tax_With_Keywords;
/**
 * Worker_Position class.
 * Load position from GSC.
 *
 * @since 0.7.3
 */
class Worker_Position extends Worker_GSC {

	const WHAT_TO_UPDATE = 'position';
	/**
	 * @var int Load up to (number) items in same request.
	 */
	protected $items_at_once = 3;
	/**
	 * Run update for items in list
	 *
	 * @param Post_Tax[] $post_taxes Post ID list.
	 * @return bool False if rate limit error received and need to do pause.
	 */
	protected function update_posts( array $post_taxes ) {
		$this->update_posts_info( $post_taxes );
		return ! $this->has_rate_error;
	}
	/**
	 * Update post with position.
	 * Use time range from keywords.
	 *
	 * @param Post_Tax[] $post_taxes What to update.
	 * @param bool       $fast_update Do not load all details, but load keyword position only.
	 * @return void
	 */
	public function update_posts_info( array $post_taxes, $fast_update = false ) {
		if ( ! is_null( $this->snapshot_id ) ) {
			$keywords   = Ahrefs_Seo_Keywords::get();
			$start_date = '';
			$end_date   = '';
			$keywords->get_time_period_keywords( $start_date, $end_date );
			$skipped_items = []; // post ID list.
			$post_taxes_kw = [];
			foreach ( $post_taxes as $post_tax ) {
				$post_tax_kw = Post_Tax_With_Keywords::create_from( $post_tax )->load_from_db();
				if ( ! empty( $post_tax_kw->get_keyword_current() ) ) {
					$post_taxes_kw[] = $post_tax_kw;
				} else {
					$skipped_items[] = $post_tax_kw;
				}
			}
			unset( $post_tax_kw );
			if ( count( $post_taxes_kw ) ) {
				// Note: do not use cached details, if we need to update positions here - we must query GSC API.
				// query using page slug.
				if ( ! $fast_update ) {
					$post_taxes_kw = $keywords->load_position_value( $post_taxes );
				} else {
					$post_taxes_kw = $keywords->load_position_value_fast( $post_taxes );
				}
				foreach ( $post_taxes_kw as $post_tax_kw ) {
					$row   = ! empty( $post_tax_kw->get_keywords_pos() ) ? $post_tax_kw->get_keywords_pos() : [];
					$data  = count( $row ) && isset( $row[0]['pos'] ) ? $row[0]['pos'] : null;
					$error = $post_tax_kw->get_error_message();
					if ( ! is_null( $data ) ) {
						// update position.
						$this->content_audit->update_position_values( $post_tax_kw, $data );
					} elseif ( ! empty( $error ) ) { // some error.
						$this->content_audit->update_position_values( $post_tax_kw, -1, $error );
					} elseif ( ! is_null( $post_tax_kw->get_position() ) ) {
						$this->content_audit->update_position_values( $post_tax_kw, $post_tax_kw->get_position() );
					} else {
						$this->content_audit->update_position_values( $post_tax_kw, Ahrefs_Seo_Data_Content::POSITION_MAX, sprintf( '[GSC] URL %s has no position info for keyword "%s"', $post_tax_kw->get_url(), (string) $post_tax_kw->get_keyword_current() ) );
					}
					$this->set_pause( 2 * $this->pause_after_success ); // prevent rate error.
				}
				Ahrefs_Seo::breadcrumbs( 'update_post_info_position:' . (string) wp_json_encode( $post_taxes_kw ) );
			}
			// no keyword set.
			if ( count( $skipped_items ) ) {
				$this->content_audit->post_positions_set_updated( $skipped_items );
			}
		} else {
			$this->content_audit->post_positions_set_updated( $post_taxes );
			// no snapshot is set mean error.
			$this->has_rate_error = true;
		}
	}
}