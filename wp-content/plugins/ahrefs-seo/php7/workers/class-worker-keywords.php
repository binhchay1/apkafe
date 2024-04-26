<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Workers;

use ahrefs\AhrefsSeo\Ahrefs_Seo_Analytics;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Data_Content;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Keywords;
use ahrefs\AhrefsSeo\Post_Tax;

/**
 * Worker_Keywords class.
 * Load Keywords from GSC, create suggested keywords using TF-IDF.
 * If current keyword's position found - save it. If not - it will be updated later by Worker Position
 *
 * @since 0.7.3
 */
class Worker_Keywords extends Worker_GSC {

	protected const WHAT_TO_UPDATE = 'keywords';

	/**
	 * @var int Load up to (number) items in same request. Will do x2 requests to GSC.
	 */
	protected $items_at_once = 2;

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
	 * Update default keyword for posts.
	 * Update only if post does not have assigned by GSC or user (manual) keywords.
	 *
	 * @param Post_Tax[] $post_taxes Post ID list.
	 *
	 * @return void
	 */
	public function update_posts_info( array $post_taxes ) : void {
		if ( ! is_null( $this->snapshot_id ) ) {
			if ( is_null( $this->api ) || ! ( $this->api instanceof Ahrefs_Seo_Analytics ) ) {
				$this->api = Ahrefs_Seo_Analytics::get();
			}
			$keywords_instance = Ahrefs_Seo_Keywords::get( $this->api );
			$post_taxes_kw     = $keywords_instance->get_full_detail_for_posts( $post_taxes, 10 );
			foreach ( $post_taxes_kw as $post_tax_kw ) {
				$keywords          = $post_tax_kw->get_keywords();
				$keywords2         = $post_tax_kw->get_keywords2();
				$keywords_imported = $post_tax_kw->get_keywords_imported();
				$data_suggested    = $keywords_instance->find_suggested_keyword( $keywords, $keywords2, $keywords_imported->get_keywords(), $post_tax_kw->get_keyword_source() );
				$suggested_keyword = ! is_null( $data_suggested ) ? $data_suggested->get_keyword() : null;

				if ( ! is_null( $suggested_keyword ) && ! $post_tax_kw->get_is_keyword_approved() ) { // do not overwrite approved keyword!
					$keywords_instance->post_keywords_set( $post_tax_kw, $data_suggested, $post_tax_kw->get_keyword_manual(), false );
				} else { // no results, save this and do not call updates more.
					// just reset update flag.
					$keywords_instance->post_keywords_set_updated( $post_tax_kw );
				}
				// fill position of the keyword immediately.
				if ( empty( $keywords ) || empty( $keywords['result'] ) ) { // it makes no sense to load position if GSC already returned no results for keywords.
					$this->content_audit->update_position_values( $post_tax_kw, Ahrefs_Seo_Data_Content::POSITION_MAX, sprintf( '[GSC] URL %s has no keywords info', $post_tax_kw->get_url( true ) ) );
				} elseif ( is_null( $suggested_keyword ) || ( '' === $suggested_keyword ) ) { // if current keyword is empty.
					$this->content_audit->update_position_values( $post_tax_kw, Ahrefs_Seo_Data_Content::POSITION_MAX, sprintf( '[GSC] URL %s skip empty keyword "%s"', $post_tax_kw->get_url(), (string) $post_tax_kw->get_keyword_current() ) );
				}
			}
		} else {
			// no snapshot is set mean error.
			$this->has_rate_error = true;
		}
	}

}
