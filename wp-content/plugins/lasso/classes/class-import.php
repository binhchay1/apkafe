<?php
/**
 * Declare class Import
 *
 * @package Import
 */

namespace Lasso\Classes;

use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Link_Location;
use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Classes\Setting_Enum;

use Lasso\Pages\Table_Details\Ajax as Table_Details_Ajax;

use Lasso\Models\Field_Mapping;
use Lasso\Models\Fields as Fields_Model;
use Lasso\Models\Model;
use Lasso\Models\Revert;
use Lasso\Models\Table_Details;
use Lasso\Models\Table_Mapping;

use Lasso_Affiliate_Link;
use Lasso_Amazon_Api;
use Lasso_Cron;
use Lasso_DB;
use Lasso_Process_Replace_Shortcode;

use stdClass;

/**
 * Import
 */
class Import {
	const OBJECT_KEY                 = 'lasso_import';
	const PRETTY_LINK_CATEGORY_SLUG  = 'pretty-link-category';
	const PRETTY_LINK_TAG_SLUG       = 'pretty-link-tag';
	const THIRSTY_LINK_CATEGORY_SLUG = 'thirstylink-category';
	const AFTER_IMPORT_ACTION        = 'lasso_after_import_action';
	const AFTER_REVERT_ACTION        = 'lasso_after_revert_action';

	/**
	 * Revert a single post from Lasso to original plugin
	 *
	 * @param int    $import_id     Post id.
	 * @param string $import_source Type of post (AAWP, earnist,...).
	 * @param string $post_type Type of post (aawp, earnist,...). Default to empty.
	 */
	public function process_single_link_revert( $import_id, $import_source, $post_type = '' ) {
		$lasso_db = new Lasso_DB();
		$cron     = new Lasso_Cron();

		$status = false;

		if ( '' === $import_id || '' === $import_source ) {
			return false;
		}

		// ? plugin: Rank Math
		Lasso_Helper::remove_action( 'delete_post', array( 'RankMath\Links\Links', 'delete_post' ) );

		if ( 'AAWP' === $import_source && 'aawp_list' === $post_type ) {
			$aawp_list       = $lasso_db->get_aawp_list( $import_id );
			$aawp_amazon_ids = $aawp_list->product_asins ?? '';
			$aawp_amazon_ids = '' !== $aawp_amazon_ids ? explode( ',', $aawp_amazon_ids ) : array();

			foreach ( $aawp_amazon_ids as $amazon_id ) {
				$amazon_url = Lasso_Amazon_Api::get_default_product_domain( $amazon_id );
				$url_detail = $lasso_db->get_url_details_by_product_id( $amazon_id, Lasso_Amazon_Api::PRODUCT_TYPE, false, $amazon_url );
				if ( $url_detail ) {
					$import_id = $url_detail->lasso_id;
					wp_update_post(
						array(
							'ID'          => $import_id,
							'post_status' => 'trash',
						)
					);
					$status = $lasso_db->process_revert( $import_id, false );
				}
			}
		} elseif ( in_array( $import_source, array( 'AAWP', 'aawp' ), true ) && 'aawp_table' === $post_type ) {
			$aawp_table_revert_item = ( new Revert() )->get_revert_data( $import_id, 'aawp', '[amazon table="' . $import_id . '"]' );

			if ( $aawp_table_revert_item ) {
				$status = ( new Revert() )->delete_by_col( 'old_uri', '[amazon table="' . $import_id . '"]' );
				( new Table_Details_Ajax() )->lasso_delete_table( $aawp_table_revert_item->get_lasso_id(), true );
			}
		} elseif ( isset( $import_source ) && in_array( $import_source, array( 'AAWP', 'AmaLinks Pro', 'EasyAzon' ), true ) ) {
			wp_update_post(
				array(
					'ID'          => $import_id,
					'post_status' => 'trash',
				)
			);
			$status = $lasso_db->process_revert( $import_id, false );
		} elseif ( Link_Location::DISPLAY_TYPE_SITE_STRIPE === $import_source || Link_Location::DISPLAY_TYPE_SITE_STRIPE === $post_type ) {
			$status = $lasso_db->process_revert( $import_id, false, $import_source );
		} else {
			$model_revert = new Revert();
			$revert_data  = $model_revert->get_one_by_col( 'lasso_id', $import_id );
			$lasso_id     = $import_id;
			$post_type    = get_post_type( $lasso_id );
			$current_url  = '';
			$temp_id      = 0;

			// ? fix: import a duplicate link into lasso, post type won't be update to lasso-urls
			if ( Setting_Enum::THIRSTYLINK_SLUG === $post_type ) {
				list( $process_status, $import_data ) = $this->process_single_link_data( $import_id, Setting_Enum::THIRSTYLINK_SLUG );
				$temp_id                              = $import_data['post']->ID ?? 0;
			} elseif ( Setting_Enum::PRETTY_LINK_SLUG === $post_type ) {
				list( $process_status, $import_data ) = $this->process_single_link_data( $import_id, Setting_Enum::PRETTY_LINK_SLUG );
				$temp_id                              = $import_data['post']->ID ?? 0;
			} elseif ( Setting_Enum::EASY_AFFILIATE_LINK_SLUG === $post_type ) {
				list( $process_status, $import_data ) = $this->process_single_link_data( $import_id, Setting_Enum::EASY_AFFILIATE_LINK_SLUG );
				$temp_id                              = $import_data['post']->ID ?? 0;
			} elseif ( Setting_Enum::SURL_SLUG === $post_type ) {
				list( $process_status, $import_data ) = $this->process_single_link_data( $import_id, Setting_Enum::SURL_SLUG );
				$temp_id                              = $import_data['post']->ID ?? 0;
			}

			if ( $temp_id > 0 && LASSO_POST_TYPE === get_post_type( $temp_id ) ) {
				$lasso_id = $temp_id;
			}

			$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );
			if ( $lasso_url->lasso_id > 0 ) {
				$current_url = $lasso_url->public_link;
			}

			$status = $lasso_db->process_revert( $import_id, true, $import_source );
			delete_post_meta( $import_id, 'affiliate_link_type' );

			if ( $current_url && $revert_data && ! in_array( $revert_data->get_plugin(), array( Setting_Enum::AAWP_SLUG, Setting_Enum::AMA_LINKS_PRO_SLUG, Setting_Enum::EASYAZON_SLUG ), true ) ) {
				Lasso_Helper::write_log( 'REVERT -- OLD URL: "' . $current_url . '"; NEW URL: ' . $revert_data->get_old_uri(), 'import_link_replace' );
				$cron->update_url_everywhere( $current_url, $revert_data->get_old_uri() );
			}
		}

		// ? update link locations and shortcode in post content
		if ( in_array( $post_type, array( Setting_Enum::AAWP_SLUG, Setting_Enum::AAWP_TABLE_SLUG, Setting_Enum::AMA_LINKS_PRO_SLUG, Setting_Enum::EASYAZON_SLUG, Setting_Enum::THIRSTYLINK_SLUG ), true ) ) {
			$process_replace_shortcode = new Lasso_Process_Replace_Shortcode();
			$process_replace_shortcode->replace_shortcode( $import_id, $post_type );
		}

		// ? clear cache after reverting
		Lasso_Cache_Per_Process::get_instance()->un_set( self::OBJECT_KEY . '_' . $import_id );
		Lasso_Cache_Per_Process::get_instance()->un_set( 'wp_post_' . $import_id );

		// ? plugin: Rank Math - add hook back after removing for handling data
		if ( class_exists( '\RankMath\Links\Links' ) ) {
			$rank_math = new \RankMath\Links\Links();
			\add_action( 'admin_footer', array( $rank_math, 'delete_post' ) );
		}

		do_action( self::AFTER_REVERT_ACTION, $import_id, $import_source );

		$status = boolval( $status );

		return $status;
	}

	/**
	 * Import a single post into Lasso
	 *
	 * @param int    $import_id        Post id.
	 * @param string $post_type        Type of post (earnist, thirstylink, affiliate_url, aawp,...).
	 * @param string $post_title       Title.
	 * @param string $import_permalink Import permalink.
	 * @param bool   $is_import_all    Is import all. Default to false.
	 */
	public function process_single_link_data( $import_id, $post_type, $post_title = '', $import_permalink = '', $is_import_all = false ) {
		$lasso_cron = new Lasso_Cron();
		$lasso_db   = new Lasso_DB();

		if ( 'pretty-link' === $post_type ) {
			$import_data = $this->get_pretty_link_data( $import_id );
		} elseif ( 'thirstylink' === $post_type ) {
			$import_data = $this->get_thirsty_affiliates_data( $import_id );
		} elseif ( 'surl' === $post_type ) {
			$import_data = $this->get_simple_urls_data( $import_id );
		} elseif ( 'earnist' === $post_type ) {
			$import_data = $this->get_earnist_data( $import_id );
		} elseif ( 'affiliate_url' === $post_type ) {
			$import_data = $this->get_affiliate_urls_data( $import_id );
		} elseif ( 'aawp' === $post_type ) {
			$import_data = $this->get_aawp_data( $import_id );
		} elseif ( 'aawp_list' === $post_type ) {
			return $this->import_aawp_list_data( $import_id );
		} elseif ( 'aawp_table' === $post_type ) {
			$import_data = $this->import_aawp_table_data( $import_id, $post_title );
		} elseif ( 'easyazon' === $post_type ) {
			$import_data = $this->get_easyazon_data( $import_id );
		} elseif ( 'amalinkspro' === $post_type ) {
			$import_data = $this->get_amalinkspro_data( $import_id, $post_title, $import_permalink );
		} elseif ( 'easy_affiliate_link' === $post_type ) {
			$import_data = $this->get_easy_affiliate_link_data( $import_id, $post_title, $import_permalink );
		} elseif ( Link_Location::DISPLAY_TYPE_SITE_STRIPE === $post_type ) {
			$import_data = $this->get_site_stripe_data( $import_id, $post_title, $import_permalink );
		}

		if ( is_null( $import_data ) ) {
			return array( false, $import_data );
		}

		$import_data['post_type'] = $post_type;

		if ( 'aawp_table' !== $post_type ) {
			// ? Make a Lasso Link
			list($status, $import_data) = $this->import_into_lasso( $import_data, $post_type );

			// ? Return if status is false
			if ( ! $status ) {
				return array( $status, $import_data );
			}

			$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $import_data['post']->ID );

			// ? Flip all old links to Lasso
			if ( $lasso_url->lasso_id > 0 ) {
				$import_data['new_url'] = $lasso_url->public_link ?? '';
			} elseif ( LASSO_AMAZON_PRODUCT_TYPE !== $lasso_url->link_type ) {
				$import_post_name = $import_data['post']->post_name ?? '';
				if ( strpos( $import_post_name, '/' ) ) {
					$start_pos = strpos( $import_post_name, '/' ) + 1;
				} else {
					$start_pos = 0;
				}

				$post_name              = substr( $import_post_name, $start_pos );
				$import_data['new_url'] = get_home_url() . '/' . $post_name . '/';
			} else {
				$import_data['new_url'] = $import_data['amazon_product']['product']['url'] ?? '';
			}

			$old_url = $import_data['old_uri'] ?? '';
			$new_url = $import_data['new_url'] ?? '';

			// ? update old link in post content
			if ( ! in_array( $post_type, array( 'aawp', 'amalinkspro', 'easyazon' ), true ) && ! empty( $old_url ) && ! empty( $new_url ) ) {
				Lasso_Helper::write_log( 'IMPORT -- OLD URL: "' . $old_url . '"; NEW URL: ' . $new_url, 'import_link_replace' );
				$lasso_cron->update_url_everywhere( $old_url, $new_url );
			}

			// ? update link locations and shortcode in post content
			if ( in_array( $post_type, array( 'aawp', 'thirstylink', 'amalinkspro', 'easyazon' ), true ) && ! empty( $old_url ) ) {
				$lasso_db->update_lasso_link_locations_when_importing( $lasso_url->lasso_id, $old_url );
				$process_replace_shortcode = new Lasso_Process_Replace_Shortcode();
				$process_replace_shortcode->replace_shortcode( $lasso_url->lasso_id );
			}

			do_action( self::AFTER_IMPORT_ACTION, $import_id, $post_type, $lasso_url->lasso_id, $is_import_all );

			return array( $status, $import_data );
		} else {
			$import_process_result     = ( new Revert() )->process_import_aawp_table( $import_data['aawp_table_id'], $import_data['lasso_table_id'] );
			$process_replace_shortcode = new Lasso_Process_Replace_Shortcode();
			$process_replace_shortcode->replace_shortcode( $import_data['aawp_table_id'], 'aawp_table' );

			return array( true, $import_data );
		}
	}

	/**
	 * Import post data from other plugins into Lasso
	 *
	 * @param array  $import_data Array contains post data.
	 * @param string $post_type   Type of post (post, page, surl, simple_url,...).
	 * @param bool   $does_create_revert_item Does create new revert item. Default to true.
	 */
	private function import_into_lasso( $import_data, $post_type, $does_create_revert_item = true ) {
		$lasso_db             = new Lasso_DB();
		$lasso_affiliate_link = new Lasso_Affiliate_Link();

		$lasso_settings = Lasso_Setting::lasso_get_settings();

		// ? Make sure slug is correct
		$post_id = $import_data['post']->ID ?? '';
		$slug    = $import_data['post']->post_name ?? '';
		$slug    = Lasso_Helper::lasso_unique_post_name( $post_id, $slug );
		$title   = $import_data['post']->post_title ?? '';
		if ( 'pretty-link' === $post_type ) {
			$slug    = $import_data['pretty_link_data']->slug ?? $slug;
			$post_id = $import_data['pretty_link_data']->link_cpt_id ?? $post_id;
			$title   = $import_data['pretty_link_data']->name ?? $title;

			$slug = trim( $slug, '/' );
			if ( false !== strpos( $slug, '/' ) ) {
				$tmp  = explode( '/', $slug );
				$slug = end( $tmp );
			}
		}

		// ? prepare data for saving Lasso post
		$default_btn_txt = Lasso_Setting::lasso_get_setting( 'primary_button_text', 'Buy Now' );
		$affiliate_link  = array(
			'affiliate_name'   => $title,
			'affiliate_url'    => $import_data['redirect_url'] ?? '',
			'post_name'        => $slug,
			'price'            => $import_data['price'] ?? '',
			'affiliate_desc'   => $import_data['description'] ?? '',
			'buy_btn_text'     => $import_data['button_text'] ?? $default_btn_txt,
			'second_btn_url'   => $import_data['second_btn_url'] ?? '',
			'second_btn_text'  => $import_data['second_btn_text'] ?? $lasso_settings['secondary_button_text'],

			'open_new_tab'     => $import_data['open_new_tab'] ?? $lasso_settings['open_new_tab'],
			'enable_nofollow'  => $import_data['enable_nofollow'] ?? $lasso_settings['enable_nofollow'],
			'open_new_tab2'    => $import_data['open_new_tab2'] ?? $lasso_settings['open_new_tab2'],
			'enable_nofollow2' => $import_data['enable_nofollow2'] ?? $lasso_settings['enable_nofollow2'],
			'enable_sponsored' => $import_data['enable_sponsored'] ?? $lasso_settings['enable_sponsored'],
			'show_disclosure'  => $import_data['show_disclosure'] ?? $lasso_settings['show_disclosure'],
			'is_opportunity'   => $import_data['is_opportunity'] ?? 1,
			'badge_text'       => $import_data['badge_text'] ?? '',
			'thumbnail'        => $import_data['thumbnail'] ?? '',
		);

		$cat_ids = $import_data['cat_ids'] ?? array();
		if ( count( $cat_ids ) > 0 ) {
			$affiliate_link['categories'] = $cat_ids;
		}

		$data['post_id']      = $post_id;
		$data['settings']     = $affiliate_link;
		$data['thumbnail_id'] = $import_data['thumbnail_id'][0] ?? '';
		$data['old_uri']      = $import_data['old_uri'] ?? '';

		// ? Use defined "affiliate name" as Lasso Post's name
		if ( in_array( $post_type, array( Setting_Enum::EASY_AFFILIATE_LINK_SLUG, Setting_Enum::SURL_SLUG ), true ) ) {
			$data['use_defined_affiliate_name'] = 1;
		}

		// ? Flip post type and log import
		$import_data['post'] = $import_data['post'] ?? new stdClass();
		$import_data['post'] = is_object( $import_data['post'] ) ? $import_data['post'] : new stdClass();

		// ? Check affiliate_name and affiliate_url and old_uri before function save_lasso_url
		if ( empty( $affiliate_link['affiliate_name'] ) || empty( $affiliate_link['affiliate_url'] ) || empty( $data['old_uri'] ) ) {
			return array( false, $import_data );
		}

		$post_id                 = $lasso_affiliate_link->save_lasso_url( $data );
		$import_data['post']->ID = $post_id;

		$post      = get_post( $post_id );
		$post_name = $post->post_name ?? '';
		$slug      = ! empty( $post_name ) && empty( $slug ) ? $post_name : $slug;

		$old_uri         = $import_data['old_uri'] ?? '';
		$site_stripe_url = $import_data['site_stripe_url'] ?? '';
		$post_data       = $site_stripe_url ? $site_stripe_url : $data['post_id'];

		$status = $does_create_revert_item ? $lasso_db->process_import( $post_id, $slug, $old_uri, $post_type, $post_data ) : true;

		// ? clear cache after importing
		if ( $status ) {
			Lasso_Cache_Per_Process::get_instance()->un_set( self::OBJECT_KEY . '_' . $post_id );
			Lasso_Cache_Per_Process::get_instance()->un_set( 'wp_post_' . $post_id );
		}

		if ( LASSO_POST_TYPE !== get_post_type( $post_id ) ) {
			$status = false;
		}

		return array( $status, $import_data );
	}

	/**
	 * Get post data of Pretty Link plugin
	 *
	 * @param int $import_id Post id.
	 */
	private function get_pretty_link_data( $import_id ) {
		$lasso_db = new Lasso_DB();

		$post             = get_post( $import_id );
		$pretty_link_data = $lasso_db->get_pretty_link_by_id( $import_id );

		if ( ! $pretty_link_data ) {
			return array();
		}

		$post->post_name      = $pretty_link_data->slug;
		$redirect_url         = $pretty_link_data->url;
		$cat_names            = $this->get_post_category_names( $import_id, self::PRETTY_LINK_CATEGORY_SLUG );
		$tag_names            = $this->get_post_category_names( $import_id, self::PRETTY_LINK_TAG_SLUG );
		$final_category_names = array_merge( $cat_names, $tag_names );
		$final_category_names = array_unique( $final_category_names );

		$data = array(
			'post'             => $post,
			'pretty_link_data' => $pretty_link_data,
			'redirect_url'     => $redirect_url,
			'cat_ids'          => $final_category_names,
			'old_uri'          => $this->get_import_permalink( $import_id, $post->post_name, $post->post_type ),
			'thumbnail_id'     => array( '' ),
			'description'      => $pretty_link_data->description,
			'enable_nofollow'  => $pretty_link_data->nofollow ?? '0',
			'enable_sponsored' => $pretty_link_data->sponsored ?? '0',
		);

		return $data;
	}

	/**
	 * Get post data of Thirsty Affiliate plugin
	 *
	 * @param int $import_id Post id.
	 */
	private function get_thirsty_affiliates_data( $import_id ) {
		$post         = get_post( $import_id );
		$redirect_url = get_post_meta( $import_id, '_ta_destination_url', true );
		$rel_tag      = get_post_meta( $import_id, '_ta_rel_tags', true ); // ? get rel tag for Thirsty Affiliates
		$cat_ids      = $this->get_post_category_names( $import_id, self::THIRSTY_LINK_CATEGORY_SLUG );

		$data = array(
			'post'             => $post,
			'redirect_url'     => $redirect_url,
			'cat_ids'          => $cat_ids,
			'old_uri'          => $this->get_import_permalink( $import_id, $post->post_name, $post->post_type ),
			'thumbnail_id'     => get_post_meta( $import_id, '_ta_image_ids', true ),
			'description'      => '',
			'enable_sponsored' => ( false === strpos( strtolower( $rel_tag ), 'sponsored' ) ) ? Lasso_Helper::cast_to_boolean( Lasso_Setting::lasso_get_setting( 'enable_sponsored' ) ) : 1,
		);

		return $data;
	}

	/**
	 * Get post data of Simple Url plugin
	 *
	 * @param int $import_id Post id.
	 */
	private function get_simple_urls_data( $import_id ) {
		$post           = get_post( $import_id );
		$redirect_url   = get_post_meta( $import_id, '_surl_redirect', true );
		$cat_ids        = $this->get_post_category_names( $import_id, LASSO_LITE_CATEGORY );
		$lasso_lite_url = Lasso_Helper::get_lasso_lite_url_to_import( $import_id );

		// ? Import amazon product
		$lasso_amazon_api  = new Lasso_Amazon_Api();
		$amazon_product_id = Lasso_Amazon_Api::get_product_id_by_url( $redirect_url );
		$amazon_product    = $lasso_amazon_api->get_amazon_product_by_id( $amazon_product_id );
		if ( $amazon_product_id && ! $amazon_product ) {
			$this->import_lite_amazon_product_into_lasso( $amazon_product_id, $redirect_url );
		}

		$data = array(
			'post'             => $post,
			'redirect_url'     => $redirect_url,
			'cat_ids'          => $cat_ids,
			'old_uri'          => $this->get_import_permalink( $import_id, $post->post_name, $post->post_type ),
			'thumbnail'        => $lasso_lite_url->custom_thumbnail,
			'button_text'      => $lasso_lite_url->display->primary_button_text,
			'show_disclosure'  => $lasso_lite_url->show_disclosure,
			'description'      => $lasso_lite_url->description,
			'price'            => $lasso_lite_url->price,
			'show_price'       => $lasso_lite_url->display->show_price,
			'second_btn_url'   => $lasso_lite_url->second_btn_url,
			'second_btn_text'  => $lasso_lite_url->second_btn_text,
			'open_new_tab'     => $lasso_lite_url->open_new_tab,
			'enable_nofollow'  => $lasso_lite_url->enable_nofollow,
			'open_new_tab2'    => $lasso_lite_url->open_new_tab2,
			'enable_nofollow2' => $lasso_lite_url->enable_nofollow2,
			'enable_sponsored' => $lasso_lite_url->enable_sponsored,
			'badge_text'       => $lasso_lite_url->display->badge_text,
		);

		return $data;
	}

	/**
	 * Get post data of Earnist plugin
	 *
	 * @param int $import_id Post id.
	 */
	private function get_earnist_data( $import_id ) {
		$post                = get_post( $import_id );
		$redirect_url        = get_post_meta( $import_id, '_earn_url', true );
		$cat_ids             = $this->get_categories_id_of_post( $import_id );
		$earnist_description = get_post_meta( $import_id, '_earn_description', true );
		$earnist_price       = get_post_meta( $import_id, '_earn_price', true );
		$earnist_button_text = get_post_meta( $import_id, '_earn_button_text', true );

		$earnist_image_id  = '';
		$earnist_image_url = '';
		$post_thumbnail    = get_post_thumbnail_id( $import_id );
		if ( ! empty( $post_thumbnail ) ) {
			$earnist_image_id = $post_thumbnail;
		} else {
			$earnist_image_url = get_post_meta( $import_id, '_earn_image_url', true );
			if ( 0 === strpos( $earnist_image_url, '/wp-content/' ) ) {
				$earnist_image_url = get_home_url() . $earnist_image_url;
			}
		}

		$data = array(
			'post'          => $post,
			'redirect_url'  => $redirect_url,
			'cat_ids'       => $cat_ids,
			'old_uri'       => $this->get_import_permalink( $import_id, $post->post_name, $post->post_type ),
			'thumbnail_id'  => array( $earnist_image_id ),
			'thumbnail_url' => $earnist_image_url,
			'description'   => $earnist_description,
			'price'         => $earnist_price,
			'button_text'   => $earnist_button_text,
		);

		return $data;
	}

	/**
	 * Get post data of Affiliate Url plugin
	 *
	 * @param int $import_id Post id.
	 */
	private function get_affiliate_urls_data( $import_id ) {
		$post         = get_post( $import_id );
		$cat_ids      = $this->get_categories_id_of_post( $import_id );
		$redirect_url = get_post_meta( $import_id, '_affiliate_url_redirect', true );

		$data = array(
			'post'         => $post,
			'redirect_url' => $redirect_url,
			'cat_ids'      => $cat_ids,
			'old_uri'      => $this->get_import_permalink( $import_id, $post->post_name, $post->post_type ),
			'thumbnail_id' => array( get_post_thumbnail_id( $import_id ) ),
			'description'  => get_the_excerpt( $import_id ),
		);

		return $data;
	}

	/**
	 * Import amazon product from other plugins into Lasso
	 *
	 * @param string $amazon_id       Amazon product id.
	 * @param array  $default_product Default product data.
	 */
	public function import_aawp_amazon_product_into_lasso( $amazon_id, $default_product = null ) {
		$lasso_db         = new Lasso_DB();
		$lasso_amazon_api = new Lasso_Amazon_Api();

		$product_data = array();

		$product = $lasso_db->get_aawp_product( $amazon_id );

		if ( $product ) {
			$base_url                  = $lasso_amazon_api->get_amazon_product_url( $product->url, false );
			$amazon_product_id_country = Lasso_Amazon_Api::get_product_id_country_by_url( $base_url );
			$product_data              = array(
				'product_id'      => $amazon_product_id_country,
				'title'           => $product->title,
				'price'           => Lasso_Amazon_Api::format_price( $product->price, $product->currency ),
				'default_url'     => $base_url,
				'url'             => $product->url,
				'image'           => $product->image_url,
				'is_prime'        => $product->is_prime,
				'currency'        => $product->currency,
				'features'        => $product->features,
				'savings_amount'  => $product->savings,
				'savings_percent' => $product->savings_percentage,
				'savings_basis'   => $product->savings_basis,
				'rating'          => $product->rating,
				'reviews'         => $product->reviews,
				'is_manual'       => 1,
			);

			$product_data['monetized_url']        = $product_data['url'];
			$product_data['default_product_name'] = $product_data['title'];

			$lasso_amazon_api->update_amazon_product_in_db( $product_data, $product->date_updated );
		} elseif ( ! $default_product ) { // ? Fix case: product does not exist in both lasso amazon table and aawp product table
			$aawp_options  = get_option( 'aawp_api' );
			$country       = $aawp_options['country'] ?? 'com';
			$country_key   = 'www.amazon.' . $country;
			$country_codes = Lasso_Amazon_Api::get_aff_link_and_flag();
			$country_code  = $country_codes[ $country_key ]['code'];
			$country_info  = Lasso_Amazon_Api::get_amazon_api_countries();
			$amazon_url    = 'https://' . $country_info[ $country_code ]['amazon_domain'] . '/dp/' . $amazon_id;
			$product       = $lasso_amazon_api->fetch_product_info( $amazon_id, true, false, $amazon_url );

			if ( 'NotFound' === $product['error_code'] ) {
				$product['product']['title'] = Lasso_Affiliate_Link::DEFAULT_AMAZON_NAME;
			}
			$product_data = 'success' === $product['status'] || 'NotFound' === $product['error_code'] ? $product['product'] : array();
		}

		return $product_data;
	}

	/**
	 * Get post data of Aawp plugin
	 *
	 * @param int $import_id Post id.
	 */
	private function get_aawp_data( $import_id ) {
		$lasso_amazon_api = new Lasso_Amazon_Api();

		$product_data = $lasso_amazon_api->get_amazon_product_from_db( $import_id );
		$product_data = $this->import_aawp_amazon_product_into_lasso( $import_id, $product_data );
		if ( ! $product_data ) {
			$product_data = $this->import_aawp_amazon_product_into_lasso( $import_id );
		} else {
			$product_data['url']   = $product_data['monetized_url'] ?? '';
			$product_data['title'] = $product_data['default_product_name'] ?? '';
		}

		$redirect_url     = $product_data['url'] ?? '';
		$post_title       = $product_data['title'] ?? '';
		$rating           = $product_data['rating'] ?? '';
		$reviews          = $product_data['reviews'] ?? '';
		$post             = new stdClass();
		$post->post_title = $post_title;

		$aawp_options = get_option( 'aawp_output' );
		$button_text  = $aawp_options['button_text'] ?? '';

		$data = array(
			'post'             => $post,
			'redirect_url'     => $redirect_url,
			'old_uri'          => $import_id,
			'description'      => '',
			'enable_sponsored' => 1,
			'button_text'      => $button_text,
			'rating'           => $rating,
			'reviews'          => $reviews,
		);

		return $data;
	}

	/**
	 * Get post data of Aawp plugin
	 *
	 * @param int $import_id Post id.
	 */
	private function import_aawp_list_data( $import_id ) {
		$lasso_db         = new Lasso_DB();
		$lasso_amazon_api = new Lasso_Amazon_Api();

		$list = $lasso_db->get_aawp_list( $import_id );
		if ( ! $list ) {
			return array( false, array() );
		}

		$cat_name = $list->keywords;
		$cat      = term_exists( $cat_name, LASSO_CATEGORY );
		if ( null === $cat || 0 === $cat ) {
			$cat = wp_insert_term( $cat_name, LASSO_CATEGORY );
		}
		$cat_id = is_wp_error( $cat ) ? null : (int) $cat['term_id'];

		$asins = $list->product_asins;
		$asins = explode( ',', $asins );
		foreach ( $asins as $asin ) {
			$product_data = $lasso_amazon_api->get_amazon_product_from_db( $asin );
			if ( ! $product_data ) {
				$this->import_aawp_amazon_product_into_lasso( $asin );
			}

			list($status, $import_data) = $this->process_single_link_data( $asin, 'aawp' );
			$post_id                    = $import_data['post']->ID ?? 0;

			// ? update categories
			if ( $cat_id ) {
				wp_set_object_terms( $post_id, array( $cat_id ), LASSO_CATEGORY );
			}
		}

		return array( true, array() );
	}

	/**
	 * Import AAWP table data
	 *
	 * @param int    $table_id   Table ID.
	 * @param string $table_name Table name.
	 */
	private function import_aawp_table_data( $table_id, $table_name ) {
		$lasso_ids    = array();
		$lasso_badges = array();

		// ? Adding products to Lasso table
		$aawp_table_products = maybe_unserialize( get_post_meta( $table_id, '_aawp_table_products', true ) );
		$aawp_rows           = $this->get_aawp_table_rows( $table_id );
		$mapping_field_ids   = $this->get_aawp_table_mapping_field_ids( $table_id );

		// ? Import table's product to Lasso
		foreach ( $aawp_table_products as $item ) {
			if ( ! $item['status'] ) {
				continue;
			}

			$table_product_id    = $item['asin'];
			$table_product_badge = boolval( $item['highlight'] ?? false );
			$table_product_text  = $item['highlight_text'] ?? '';
			$import_data         = $this->get_aawp_data( $table_product_id );

			$import_data['badge_text'] = '';
			if ( $table_product_badge && $table_product_text ) {
				$import_data['badge_text'] = $table_product_text;
			}

			// ? Make a Lasso Link
			list($status, $import_data) = $this->import_into_lasso( $import_data, 'aawp', false );
			$post_id                    = $import_data['post']->ID ?? 0;
			$lasso_id                   = $post_id;
			$lasso_ids[]                = $lasso_id;
			$lasso_badges[ $lasso_id ]  = $import_data['badge_text'];

			// ? Create a table failed because the process cannot import a product of AAWP table into Lasso.
			if ( ! $post_id ) {
				return null;
			}

			// ? Mapping customer fields
			$rows = $item['rows'] ?? null;
			if ( $rows ) {
				foreach ( $aawp_rows as $index => $field ) {
					$field_type    = $field['type'];
					$support_field = self::map_aawp_fields( $field_type );
					if ( $field['status'] && $support_field ) {
						$label = $field['label'];
						$f     = new Fields_Model();
						$f     = $f->get_one_by_cols(
							array(
								'field_name' => $label,
								'field_type' => $support_field,
							)
						);

						$field_value = $rows[ $index ]['values'][ $field_type ];
						if ( 'reviews' === $field_type ) {
							$field_value = $import_data['reviews'];
						}

						if ( $f->get_id() ) {
							$fm = new Field_Mapping();
							$fm->set_lasso_id( $lasso_id );
							$fm->set_field_id( $f->get_id() );
							$fm->set_field_value( $field_value );
							$fm->insert_on_duplicate_update_field_value();
						}
					} elseif ( 'star_rating' === $field_type ) { // ? Rating
						$fm = new Field_Mapping();
						$fm->set_lasso_id( $lasso_id );
						$fm->set_field_id( Fields_Model::RATING_FIELD_ID );
						$fm->set_field_value( $import_data['rating'] );
						$fm->insert_on_duplicate_update_field_value();
					}
				}
			}
		}

		// ? Create new Lasso vertical table
		$table_detail = new Table_Details();
		$table_detail->set_title( $table_name );
		$table_detail->set_style( Setting_Enum::TABLE_STYLE_COLUMN );
		$table_detail->set_theme( Setting_Enum::THEME_CACTUS );
		$table_detail->set_show_title( 0 );
		$table_detail->set_show_headers_horizontal( Table_Details::ENABLE_SHOW_HEADERS_HORIZONTAL );
		$table_detail->insert();

		foreach ( $lasso_ids as $index => $lasso_id ) {
			$mapping_fields = 0 === $index ? $mapping_field_ids : array();
			Table_Mapping::add_product( $table_detail->get_id(), $lasso_id, $mapping_fields );

			// ? Set badge
			$table_product = Table_Mapping::get_by_table_id_lasso_id( $table_detail->get_id(), $lasso_id );
			$table_product->set_badge_text( trim( $lasso_badges[ $lasso_id ] ) );
			$table_product->update();
		}

		return array(
			'aawp_table_id'  => $table_id,
			'lasso_table_id' => $table_detail->get_id(),
		);
	}

	/**
	 * Get post data of EasyAzon plugin
	 *
	 * @param string $import_id Post id.
	 */
	private function get_easyazon_data( $import_id ) {
		$lasso_db         = new Lasso_DB();
		$lasso_amazon_api = new Lasso_Amazon_Api();

		$product        = $lasso_db->get_easyazon_product( $import_id );
		$product_serial = $product->option_value ?? '';
		$product        = maybe_unserialize( $product_serial ); // phpcs:ignore

		// ? insert amazon product data from easyazon into lasso
		$db_product = $lasso_amazon_api->get_amazon_product_from_db( $product['identifier'] );
		if ( ! $db_product ) {
			$store_data = array(
				'product_id'  => $product['identifier'],
				'title'       => $product['title'],
				'price'       => $product['attributes']['ListPrice'] ?? $product['lowest_price_n'] ?? '',
				'default_url' => $product['url'],
				'url'         => $product['url'],
				'image'       => $product['images'][4]['url'] ?? $product['images'][ count( $product['images'] ) - 1 ]['url'],
				'quantity'    => 200,  // Manual checks won't show out of stock for now. TODO: Add BLS to out of stock checks.
				'is_manual'   => 1,
			);
			$lasso_amazon_api->update_amazon_product_in_db( $store_data );
		}

		$redirect_url     = $product['url'] ?? '';
		$post_title       = $product['title'] ?? '';
		$post_title       = $post_title ? $post_title : 'Amazon';
		$post             = new stdClass();
		$post->post_title = $post_title;

		$data = array(
			'post'         => $post,
			'redirect_url' => $redirect_url,
			'old_uri'      => $import_id,
			'description'  => '',
		);

		return $data;
	}

	/**
	 * Get post data of AmaLinks Pro plugin
	 *
	 * @param string $import_id  Post id.
	 * @param string $post_title Post title.
	 * @param string $import_permalink Import permalink.
	 */
	private function get_amalinkspro_data( $import_id, $post_title, $import_permalink ) {
		$post             = new stdClass();
		$post->post_title = $post_title;

		if ( empty( $post->post_title ) ) {
			$lasso_amazon     = new Lasso_Amazon_Api();
			$product          = $lasso_amazon->get_amazon_product_from_db( $import_id );
			$post->post_title = $product['default_product_name'] ?? $post->post_title;
			if ( ! $product && empty( $post->post_title ) ) {
				$result           = $lasso_amazon->fetch_product_info( $import_id, true, false, $import_permalink );
				$post->post_title = $result['product']['title'] ?? $post->post_title;
			}
		}

		$data = array(
			'post'         => $post,
			'redirect_url' => $import_permalink,
			'old_uri'      => $import_id,
			'description'  => '',
		);

		return $data;
	}

	/**
	 * Get post data of Easy Affiliate Links plugin
	 *
	 * @param string $import_id  Post id.
	 * @param string $post_title Post title.
	 * @param string $import_permalink Import permalink.
	 */
	public function get_easy_affiliate_link_data( $import_id, $post_title, $import_permalink ) {
		$post         = get_post( $import_id );
		$redirect_url = get_post_meta( $import_id, 'eafl_url', true );
		$terms        = get_the_terms( $import_id, 'eafl_category' );
		$cat_ids      = $terms && ! is_wp_error( $terms ) ? wp_list_pluck( $terms, 'name' ) : null;

		$default_settings = get_option( 'eafl_settings' );
		$target           = get_post_meta( $import_id, 'eafl_target', true );
		$description      = get_post_meta( $import_id, 'eafl_description', true );
		if ( 'default' === $target ) {
			$target = $default_settings['default_target'] ?? '_blank';
		}
		$nofollow = get_post_meta( $import_id, 'eafl_nofollow', true );
		if ( 'default' === $nofollow ) {
			$nofollow = $default_settings['default_nofollow'] ?? 'nofollow';
		}
		$sponsored = get_post_meta( $import_id, 'eafl_sponsored', true );

		$data = array(
			'post'             => $post,
			'redirect_url'     => $redirect_url,
			'cat_ids'          => $cat_ids,
			'old_uri'          => get_permalink( $import_id ),
			'description'      => $description,
			'open_new_tab'     => '_blank' === $target ? 1 : 0,
			'enable_nofollow'  => 'nofollow' === $nofollow ? 1 : 0,
			'enable_sponsored' => '1' === $sponsored ? 1 : 0,
		);

		return $data;
	}

	/**
	 * Get post data of Site Stripe link
	 *
	 * @param string $import_id  Post id.
	 * @param string $post_title Post title.
	 * @param string $import_permalink Import permalink.
	 */
	public function get_site_stripe_data( $import_id, $post_title, $import_permalink ) {
		$post             = new stdClass();
		$post->post_title = $post_title;
		$site_stripe_url  = $import_permalink;
		$import_permalink = Lasso_Amazon_Api::get_site_stripe_url( $import_permalink );

		if ( $import_id ) {
			$lasso_amazon     = new Lasso_Amazon_Api();
			$product          = $lasso_amazon->get_amazon_product_from_db( $import_id );
			$post->post_title = $product['default_product_name'] ?? $post->post_title;
			if ( ! $product ) {
				$result           = $lasso_amazon->fetch_product_info( $import_id, true, false, $import_permalink );
				$post->post_title = $result['product']['title'] ?? $post->post_title;
			} else {
				$import_permalink = $product['monetized_url'] ?? $import_permalink;
			}
		}

		$post->post_title = $post->post_title ? $post->post_title : 'Amazon';

		$data = array(
			'post'            => $post,
			'redirect_url'    => $import_permalink,
			'old_uri'         => $import_id,
			'site_stripe_url' => $site_stripe_url,
			'description'     => '',
		);

		return $data;
	}

	/**
	 * Get import permalink
	 *
	 * @param int    $id        Post id.
	 * @param string $post_name Post name.
	 * @param string $post_type Type of post (post, page, pretty-link, surl,...).
	 */
	private function get_import_permalink( $id, $post_name, $post_type ) {
		if ( 'pretty-link' === $post_type ) {
			$prlipro = get_option( 'prlipro_options', array() );
			if ( ! is_array( $prlipro ) ) {
				$prlipro = array();
			}

			$home_url         = get_home_url();
			$base_slug_prefix = $prlipro['base_slug_prefix'] ?? false;

			if ( $base_slug_prefix && '' !== $base_slug_prefix && strpos( $post_name, $base_slug_prefix ) === false ) {
				$post_name        = substr( $post_name, strpos( $post_name, '/' ) );
				$import_permalink = $home_url . '/' . $base_slug_prefix . '/' . $post_name . '/';
			} else {
				$import_permalink = $home_url . '/' . $post_name . '/';
			}
		} else {
			$import_permalink = get_the_permalink( $id );
		}

		return $import_permalink;
	}

	/**
	 * Get all categories of a Lasso post
	 *
	 * @param int $post_id Lasso post id.
	 */
	public function get_categories_id_of_post( $post_id ) {
		$prepare = Model::prepare(
			'SELECT `term_taxonomy_id` FROM ' . Model::get_wp_table_name( 'term_relationships' ) . ' WHERE `object_id` = %d', // phpcs:ignore
			$post_id
		);
		$result  = Model::get_results( $prepare, ARRAY_A );

		$cat_ids = array();

		if ( count( $result ) > 0 ) {
			foreach ( $result as $value ) {
				$cat_ids[] = $value['term_taxonomy_id'];
			}
		}

		return $cat_ids;
	}

	/**
	 * Check whether a post was imported into Lasso or not
	 *
	 * @param int    $post_id Lasso post id.
	 * @param string $type Import type.
	 */
	public static function is_post_imported_into_lasso( $post_id, $type = '' ) {
		$post_id   = intval( $post_id );
		$cache_key = $type ? self::OBJECT_KEY . '_' . $type . '_' . $post_id : self::OBJECT_KEY . '_' . $post_id;
		$results   = Lasso_Cache_Per_Process::get_instance()->get_cache( $cache_key, null );
		if ( null !== $results ) {
			return $results;
		}

		if ( 0 === $post_id ) {
			Lasso_Cache_Per_Process::get_instance()->set_cache( $cache_key, false );
			return false;
		}

		if ( 'aawp_table' === $type ) {
			$sql = '
				SELECT DISTINCT plugin
				FROM ' . Model::get_wp_table_name( LASSO_REVERT_DB ) . "
				WHERE plugin = 'aawp'
					AND old_uri = '[amazon table=\"$post_id\"]'
			";
		} else {
			// ? Cache post by Lasso_Cache_Per_Process
			$post = Lasso_Cache_Per_Process::get_instance()->get_cache( 'wp_post_' . $post_id );
			if ( false === $post ) {
				$post = get_post( $post_id );
				Lasso_Cache_Per_Process::get_instance()->set_cache( 'wp_post_' . $post_id, $post );
			}
			$post_type   = get_post_type( $post );
			$post_status = get_post_status( $post );

			if ( LASSO_POST_TYPE !== $post_type || 'publish' !== $post_status ) {
				Lasso_Cache_Per_Process::get_instance()->set_cache( $cache_key, false );
				return false;
			}

			$sql = '
				select DISTINCT plugin
				from ' . Model::get_wp_table_name( LASSO_REVERT_DB ) . '
				where lasso_id = ' . $post_id . '
			';
		}

		$results = Model::get_results( $sql );

		if ( is_null( $results ) ) {
			Lasso_Cache_Per_Process::get_instance()->set_cache( $cache_key, false );
			return false;
		}

		$results = array_map(
			function( $v ) {
				return $v->plugin;
			},
			$results
		);

		if ( empty( $results ) ) {
			Lasso_Cache_Per_Process::get_instance()->set_cache( $cache_key, false );
			return false;
		}

		// ? Set cache to keep results loaded
		Lasso_Cache_Per_Process::get_instance()->set_cache( $cache_key, $results );
		return $results;
	}

	/**
	 * Get category names by post id
	 *
	 * @param int    $post_id  Post id.
	 * @param string $taxonomy Taxonomy.
	 * @return array
	 */
	public function get_post_category_names( $post_id, $taxonomy ) {
		$result  = array();
		$cat_ids = $this->get_categories_id_of_post( $post_id );

		foreach ( $cat_ids as $key => $cat_id ) {
			$term     = self::get_term_by_taxonomy( $cat_id, $taxonomy );
			$cat_name = $term->name ?? '';
			if ( ! empty( $cat_name ) ) {
				$result[ $key ] = $cat_name;
			}
		}

		return $result;
	}

	/**
	 * Get term by taxonomy id and slug
	 *
	 * @param string $taxonomy_id   Taxonomy id.
	 * @param string $taxonomy_slug Taxonomy slug.
	 */
	public static function get_term_by_taxonomy( $taxonomy_id, $taxonomy_slug ) {
		$sql     = '
			SELECT tt.term_id, name, slug, taxonomy
			FROM ' . Model::get_wp_table_name( 'term_taxonomy' ) . ' AS tt
			LEFT JOIN
				' . Model::get_wp_table_name( 'terms' ) . ' AS t
				ON tt.term_id = t.term_id
			WHERE
				tt.term_taxonomy_id = %d
				AND tt.taxonomy = %s
			LIMIT 1
		';
		$prepare = Model::prepare( $sql, $taxonomy_id, $taxonomy_slug );

		return Model::get_row( $prepare );
	}

	/**
	 * Import amazon product from Lasso Lite
	 *
	 * @param string $amazon_id    Amazon product id.
	 * @param string $redirect_url Redirect Url.
	 * @return array
	 */
	public function import_lite_amazon_product_into_lasso( $amazon_id, $redirect_url ) {
		$lasso_db         = new Lasso_DB();
		$lasso_amazon_api = new Lasso_Amazon_Api();
		$product_data     = array();
		$product          = $lasso_db->get_lasso_lite_product( $amazon_id );

		if ( $product ) {
			$base_url     = $lasso_amazon_api->get_amazon_product_url( $redirect_url, false );
			$product_data = array(
				'product_id'      => $product->amazon_id,
				'title'           => $product->default_product_name,
				'price'           => $product->latest_price,
				'default_url'     => $base_url,
				'url'             => $lasso_amazon_api->get_amazon_product_url( $redirect_url, true ),
				'image'           => $product->default_image,
				'is_prime'        => $product->is_prime,
				'currency'        => $product->currency,
				'features'        => json_decode( $product->features ),
				'savings_amount'  => $product->savings_amount,
				'savings_percent' => $product->savings_percent,
				'savings_basis'   => $product->savings_basis,
				'rating'          => $product->rating,
				'reviews'         => $product->reviews,
				'is_manual'       => 1,
				'quantity'        => intval( $product->out_of_stock ) ? 0 : 1,
			);

			$lasso_amazon_api->update_amazon_product_in_db( $product_data );
		}

		return $product_data;
	}

	/**
	 * Get AAWP table mapping field ids.
	 *
	 * @param int $table_id AAWP table id.
	 * @return array
	 */
	public function get_aawp_table_mapping_field_ids( $table_id ) {
		$results                   = array();
		$aawp_lasso_mapping_fields = array(
			'title'       => Fields_Model::PRODUCT_NAME_FIELD_ID,
			'thumb'       => Fields_Model::IMAGE_FIELD_ID,
			'price'       => Fields_Model::PRICE_ID,
			'button'      => Fields_Model::PRIMARY_BTN_ID,
			'star_rating' => Fields_Model::RATING_FIELD_ID,
		);
		$aawp_rows                 = $this->get_aawp_table_rows( $table_id );

		foreach ( $aawp_rows as $aawp_row ) {
			$field_status = $aawp_row['status'] ?? null;
			$field_type   = $aawp_row['type'] ?? null;
			$field_label  = $aawp_row['label'] ?? null;
			if ( ! $field_status || ! $field_type ) {
				continue;
			}

			$support_field = self::map_aawp_fields( $field_type );
			if ( isset( $aawp_lasso_mapping_fields[ $field_type ] ) ) {
				$results[] = $aawp_lasso_mapping_fields[ $field_type ];
			} elseif ( $support_field && $field_label ) { // ? Support custom fields for table
				$field = new Fields_Model();
				$f     = $field->get_one_by_cols(
					array(
						'field_name' => $field_label,
						'field_type' => $support_field,
					)
				);
				if ( ! $f->get_id() ) {
					$f->set_field_name( $field_label );
					$f->set_field_type( $support_field );
					$f->set_field_description( $field_label . ' from AAWP table.' );
					$f->insert();

				}
				$results[] = $f->get_id();
			}
		}

		return $results;
	}

	/**
	 * Map AAWP table fields to Lasso fields
	 *
	 * @param string $field_type Field type.
	 */
	private static function map_aawp_fields( $field_type ) {
		$support_fields = array(
			'shortcode'     => Fields_Model::FIELD_TYPE_TEXT,
			'custom_button' => Fields_Model::FIELD_TYPE_BUTTON,
			'custom_text'   => Fields_Model::FIELD_TYPE_TEXT_AREA,
			'custom_html'   => Fields_Model::FIELD_TYPE_TEXT_AREA,
			'reviews'       => Fields_Model::FIELD_TYPE_NUMBER,
		);

		if ( ! in_array( $field_type, array_keys( $support_fields ), true ) ) {
			return null;
		}

		return $support_fields[ $field_type ];
	}

	/**
	 * Get AAWP table rows.
	 * Set default label for support field if the label is empty.
	 *
	 * @param int $table_id AAWP table id.
	 * @return array|mixed|string
	 */
	private function get_aawp_table_rows( $table_id ) {
		$aawp_rows            = maybe_unserialize( get_post_meta( $table_id, '_aawp_table_rows', true ) );
		$no_label_field_count = array(
			'shortcode'     => 0,
			'custom_button' => 0,
			'custom_text'   => 0,
			'custom_html'   => 0,
		);

		foreach ( $aawp_rows as $index => $aawp_row ) {
			$field_status = $aawp_row['status'] ?? null;
			$field_type   = $aawp_row['type'] ?? null;
			if ( ! $field_status || ! $field_type ) {
				continue;
			}

			$aawp_rows[ $index ]['label'] = $this->set_aawp_table_field_label( $aawp_row['label'] ?? null, $field_type, $no_label_field_count );
		}

		return $aawp_rows;
	}

	/**
	 * Set default label for support field if the label is empty.
	 *
	 * @param string $field_label Field label.
	 * @param string $field_type Field type.
	 * @param array  $no_label_field_count No label field count.
	 * @return mixed|string
	 */
	private function set_aawp_table_field_label( $field_label, $field_type, &$no_label_field_count ) {
		$support_field_type = array(
			'custom_text',
			'shortcode',
			'custom_html',
		);

		if ( $field_label || ! in_array( $field_type, $support_field_type, true ) ) {
			return $field_label;
		}

		switch ( $field_type ) {
			case 'custom_text':
				$no_label_field_count[ $field_type ]++;
				$field_label = 'Custom text ' . $no_label_field_count[ $field_type ];
				break;
			case 'shortcode':
				$no_label_field_count[ $field_type ]++;
				$field_label = 'Shortcode ' . $no_label_field_count[ $field_type ];
				break;
			case 'custom_html':
				$no_label_field_count[ $field_type ]++;
				$field_label = 'Custom HTML ' . $no_label_field_count[ $field_type ];
				break;
		}

		return $field_label;
	}
}
