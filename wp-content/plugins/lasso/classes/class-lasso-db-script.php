<?php
/**
 * Declare class Lasso_DB_Script
 *
 * @package Lasso_DB_Script
 */

use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Classes\Setting_Enum;
use Lasso\Classes\Table_Mapping as Lasso_Table_Mapping;

use Lasso\Models\Model;
use Lasso\Models\Affiliate_Programs;
use Lasso\Models\Amazon_Products;
use Lasso\Models\Amazon_Shortened_Url;
use Lasso\Models\Auto_Monetize;
use Lasso\Models\Amazon_Tracking_Ids;
use Lasso\Models\Category_Order;
use Lasso\Models\Content;
use Lasso\Models\Extend_Products;
use Lasso\Models\Field_Mapping;
use Lasso\Models\Fields;
use Lasso\Models\Keyword_Locations;
use Lasso\Models\Link_Locations;
use Lasso\Models\Post_Content_History;
use Lasso\Models\Revert;
use Lasso\Models\Table_Details;
use Lasso\Models\Table_Field_Group;
use Lasso\Models\Table_Field_Group_Detail;
use Lasso\Models\Table_Mapping;
use Lasso\Models\Table_Title_Field;
use Lasso\Models\Tracked_Keywords;
use Lasso\Models\Url_Details;
use Lasso\Models\Url_Issue_Definitions;
use Lasso\Models\Url_Issues;
use Lasso\Models\MetaData;

/**
 * Require WordPress core file
 */
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

/**
 * Lasso_DB_Script
 */
class Lasso_DB_Script {
	/**
	 * Create revert table
	 */
	public function create_revert_table() {
		$model = new Revert();
		$model->create_table();
	}

	/**
	 * Create url_issue_definitions table
	 */
	public function create_url_issue_def_table() {
		$model = new Url_Issue_Definitions();
		$model->create_table();
	}

	/**
	 * Create url_issues table
	 */
	public function create_url_issue_table() {
		$model = new Url_Issues();
		$model->create_table();
	}

	/**
	 * Create link_locations table
	 */
	public function create_link_locations_table() {
		$model = new Link_Locations();
		$model->create_table();
	}

	/**
	 * Create tracked_keywords table
	 */
	public function create_tracked_keyword_table() {
		$model = new Tracked_Keywords();
		$model->create_table();
	}

	/**
	 * Create keyword_locations table
	 */
	public function create_keyword_locations_table() {
		$model = new Keyword_Locations();
		$model->create_table();
	}

	/**
	 * Create amazon_product table
	 */
	public function create_amazon_product_table() {
		$model = new Amazon_Products();
		$model->create_table();
	}

	/**
	 * Create amazon_shortened_url table
	 */
	public function create_amazon_shortened_url_table() {
		$model = new Amazon_Shortened_Url();
		$model->create_table();
	}

	/**
	 * Create auto_monetize table
	 */
	public function create_auto_monetize_table() {
		$model = new Auto_Monetize();
		$model->create_table();
	}

	/**
	 * Create category_order table
	 */
	public function create_category_order_table() {
		$model = new Category_Order();
		$model->create_table();
	}

	/**
	 * Create content table
	 */
	public function create_content_table() {
		$model = new Content();
		$model->create_table();
	}

	/**
	 * Create amazon_tracking table
	 */
	public function create_amazon_tracking_table() {
		$model = new Amazon_Tracking_Ids();
		$model->create_table();
	}

	/**
	 * Create url_details table
	 */
	public function create_url_details_table() {
		$model = new Url_Details();
		$model->create_table();
	}

	/**
	 * Create fields table
	 */
	public function create_fields_table() {
		$model = new Fields();
		$model->create_table();
	}

	/**
	 * Create field_mapping table
	 */
	public function create_field_mapping_table() {
		$model = new Field_Mapping();
		$model->create_table();
	}

	/**
	 * Create affiliate_programs table
	 */
	public function create_affiliate_programs_table() {
		$model = new Affiliate_Programs();
		$model->create_table();
	}

	/**
	 * Create lasso_post_content_history table
	 */
	public function create_lasso_post_content_history_table() {
		$model = new Post_Content_History();
		$model->create_table();
	}

	/**
	 * Create extend_products table
	 */
	public function create_extend_product_table() {
		$model = new Extend_Products();
		$model->create_table();
	}

	/**
	 * Update DB structure for version 198
	 */
	public function upgrade_url_issues_data_198() {
		$model = new Url_Issues();
		$model->update_for_v198();
	}

	/**
	 * Update DB structure for version 228
	 */
	public function remove_current_timestamp_228() {
		$ui_model = new Url_Issues();
		$ui_model->update_for_v228();

		$content_model = new Content();
		$content_model->update_for_v228();

		$revert_model = new Revert();
		$revert_model->update_for_v228();
	}

	/**
	 * Fix primary key
	 */
	public function fix_category_order_primary_key_229() {
		$model = new Category_Order();
		$model->update_for_v229();
	}

	/**
	 * Update DB structure, data for version 231
	 */
	public function upgrade_to_url_details_231() {
		$model = new Url_Details();
		$model->create_table();
		$model->update_for_v231();
	}

	/**
	 * Prepare old indexes
	 */
	public function repair_old_indexes_240() {
		$ll_model = new Link_Locations();
		$ll_model->drop_index( 'ix_link_slug_domain' );

		$co_model = new Category_Order();
		$co_model->update_for_v240();
	}

	/**
	 * Remove space (begin/end) from old primary and second urls
	 */
	public function trim_old_primary_and_second_urls_263() {
		// ? Trim old second urls
		$query   = '
			UPDATE ' . Model::get_wp_table_name( 'postmeta' ) . ' 
			SET meta_value = TRIM(meta_value) 
			WHERE meta_key = %s
		';
		$prepare = Model::prepare( $query, 'second_btn_url' );
		Model::query( $prepare );

		// ? Trim redirect_url in table lasso_url_details
		$ud_model = new Url_Details();
		$ud_model->update_for_v263();

		// ? Trim base_url, monetized_url in table lasso_amazon_products
		$amz_model = new Amazon_Products();
		$amz_model->update_for_v263();
	}

	/**
	 * Remove duplicate records and keep only one oldest record in the "lasso_post_content_history" table
	 */
	public function keep_the_first_one_records_of_each_post_277() {
		$model = new Post_Content_History();
		$model->update_for_v277();
	}

	/**
	 * Upgrade link locations
	 */
	public function upgrade_link_locations_278() {
		$model = new Link_Locations();
		$model->update_for_v278();
	}

	/**
	 * Upgrade link locations
	 */
	public function upgrade_link_locations_282() {
		$model = new Link_Locations();
		$model->update_for_v282();
	}

	/**
	 * Upgrade link locations
	 */
	public function upgrade_link_locations_284() {
		$this->upgrade_link_locations_278();
	}

	/**
	 * Upgrade table lasso_url_details structure
	 */
	public function upgrade_url_details_to_apply_extend_product_288() {
		$model = new Url_Details();
		$model->update_for_v288();
	}

	/**
	 * Upgrade table lasso_url_details structure
	 */
	public function upgrade_url_details_to_apply_extend_product_291() {
		$this->upgrade_url_details_to_apply_extend_product_288();
	}

	/**
	 * Update Lasso post image from http://img.chewy.com... to https://img.chewy.com...
	 */
	public function update_chewy_image_link_to_https_288() {
		$query   = '
			UPDATE ' . Model::get_wp_table_name( 'postmeta' ) . ' AS pm 
			SET pm.meta_value = REPLACE(pm.meta_value, %s, %s) 
			WHERE pm.meta_key = %s AND meta_value LIKE %s
		';
		$prepare = Model::prepare( $query, 'http://img.chewy.com', 'https://img.chewy.com', 'lasso_custom_thumbnail', 'http://img.chewy.com' );
		Model::query( $prepare );
	}
	/**
	 * Create table details
	 */
	public function create_table_details() {
		$model = new Table_Details();
		$model->create_table();
	}

	/**
	 * Create table mapping
	 */
	public function create_table_mapping() {
		$model = new Table_Mapping();
		$model->create_table();
	}

	/**
	 * Create table mapping
	 */
	public function create_table_field_group() {
		$model = new Table_Field_Group();
		$model->create_table();
	}

	/**
	 * Create table mapping
	 */
	public function create_table_field_group_detail() {
		$model = new Table_Field_Group_Detail();
		$model->create_table();
	}

	/**
	 * Create table metadata
	 */
	public function create_table_metadata() {
		$model = new MetaData();
		$model->create_table();
	}


	/**
	 * Add default data for the issue tables
	 */
	public function url_issue_definitions() {
		$model = new Url_Issue_Definitions();
		$model->add_default_data();
	}

	/**
	 * Add default data for amazon_tracking table
	 */
	public function amazon_tracking_default_data() {
		$model = new Amazon_Tracking_Ids();
		$model->add_default_data();

	}

	/**
	 * Add default data for lasso plugin
	 */
	public function create_default_fields() {
		$model = new Fields();
		$model->add_default_data();
	}

	/**
	 * Restore built-in fields type for lasso plugin
	 */
	public function restore_fields_type() {
		$model = new Fields();
		$model->restore_fields_type();
	}

	/**
	 * Move title to group detail table. This function only run one time and it will remove on the next version.
	 */
	public function move_table_title_field_to_group_detail() {
		$option_name = 'lasso_move_title_to_group_detail';
		$is_moved    = get_option( $option_name );
		if ( ! $is_moved ) {
			update_option( $option_name, 1 );

			// ? If $GLOBALS['wp_rewrite'] is not existed, we declare this one to use the function get_post_permalink
			if ( ! isset( $GLOBALS['wp_rewrite'] ) ) {
				require_once ABSPATH . WPINC . '/class-wp-rewrite.php';
				$GLOBALS['wp_rewrite'] = new WP_Rewrite(); // phpcs:ignore
			}

			$table_detail = new Table_Details();
			$results      = $table_detail->get_all( 0 );

			foreach ( $results as $result ) {
				// ? Create a group for each table
				$table          = Table_Details::get_inst( $result );
				$table_id       = $table->get_id();
				$table_mappings = Table_Mapping::get_list_by_table_id( $table_id );
				$lasso_id       = ! empty( $table_mappings ) ? $table_mappings[0]->get_lasso_id() : 0;
				if ( $lasso_id > 0 ) {
					$table_mapping_product = Lasso_Table_Mapping::get_by_table_id_lasso_id( $table_id, $lasso_id );
					$order                 = -1;
					$field_group_id        = $table_mapping_product->add_field( Fields::PRODUCT_NAME_FIELD_ID, null, $order );
					$field_group_details   = Table_Field_Group_Detail::get_list_by_field_group_id_field_id( $field_group_id, Fields::PRODUCT_NAME_FIELD_ID );
					// ? Process for title field
					// ? Set value
					foreach ( $field_group_details as $field_group_detail ) {
						$table_mappings = Table_Mapping::get_list_by_table_id( $table_id );
						foreach ( $table_mappings as $table_mapping ) {
							if ( $field_group_detail->get_lasso_id() === $table_mapping->get_lasso_id() ) {
								$field_group_detail->set_field_value( $table_mapping->get_title() );
								$field_group_detail->update();
							}
						}
					}

					// ? Process for other fields
					$table_title_fields = Table_Title_Field::get_list_field_by_table_id_lasso_id( $table_id, $lasso_id );
					foreach ( $table_title_fields as $table_title_field ) {
						$table_mapping_product->add_field( $table_title_field->get_field_id(), $field_group_id, $order + ( -1 ) );
					}

					$table_title_fields = Table_Title_Field::get_list_by_table_id( $table_id );
					foreach ( $table_title_fields as $table_title_field ) {
						$field_group_details = Table_Field_Group_Detail::get_list_by_field_group_id_field_id( $field_group_id, $table_title_field->get_field_id() );
						foreach ( $field_group_details as $field_group_detail ) {
							if ( $table_title_field->get_field_id() === $field_group_detail->get_field_id() && $table_title_field->get_lasso_id() === $field_group_detail->get_lasso_id() ) {
								$field_group_detail->set_field_value( $table_title_field->get_field_value() );
								$field_group_detail->update();
							}
						}
					}
				}
			}

			$sql = 'TRUNCATE TABLE ' . ( new Table_Title_Field() )->get_table_name();
			Model::query( $sql );
		}
	}

	/**
	 * Set default value for current Product Image, Primary button and Secondary fields inside "field group detail" from Lasso Post
	 */
	public function set_table_fields_default_value() {
		$option_name  = 'set_table_fields_default_value';
		$is_processed = get_option( $option_name );
		if ( ! $is_processed ) {
			update_option( $option_name, 1 );

			// ? If $GLOBALS['wp_rewrite'] is not existed, we declare this one to use the function get_post_permalink
			if ( ! isset( $GLOBALS['wp_rewrite'] ) ) {
				require_once ABSPATH . WPINC . '/class-wp-rewrite.php';
				$GLOBALS['wp_rewrite'] = new WP_Rewrite(); // phpcs:ignore
			}

			// ? Get list "field group detail" of product image, price, primary button and secondary fields
			$field_group_details = ( new Table_Field_Group_Detail() )->get_list_by_field_ids( array( Fields::IMAGE_FIELD_ID, Fields::PRIMARY_BTN_ID, Fields::SECONDARY_BTN_ID, Fields::PRICE_ID ) );

			// ? Set the default value from Lasso Post
			$lasso_urls = array();
			foreach ( $field_group_details as $field_group_detail ) {
				$lasso_id    = $field_group_detail->get_lasso_id();
				$field_value = null;
				if ( isset( $lasso_urls[ $lasso_id ] ) ) {
					$lasso_url = $lasso_urls[ $lasso_id ];
				} else {
					$lasso_url               = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );
					$lasso_urls[ $lasso_id ] = $lasso_url;
				}

				switch ( $field_group_detail->get_field_id() ) {
					case Fields::IMAGE_FIELD_ID:
						$field_value = $lasso_url->image_src ? $lasso_url->image_src : Lasso_Setting::lasso_get_setting( 'default_thumbnail' );
						break;
					case Fields::PRIMARY_BTN_ID:
						$field_value = array(
							'button_text' => $lasso_url->display->primary_button_text,
							'url'         => $lasso_url->public_link,
						);
						$field_value = wp_json_encode( $field_value );
						break;
					case Fields::SECONDARY_BTN_ID:
						$field_value = array(
							'button_text' => $lasso_url->display->secondary_button_text,
							'url'         => $lasso_url->display->secondary_url,
						);
						$field_value = wp_json_encode( $field_value );
						break;
					case Fields::PRICE_ID:
						$field_value = $lasso_url->price;
						break;
				}

				if ( ! is_null( $field_value ) ) {
					$field_group_detail->set_field_value( $field_value );
					$field_group_detail->update();
				}
			}
		}
	}

	/**
	 * Update rating and reviews data from AAWP product table to Lasso amazon table, so we can support aawp fields shortcode
	 */
	public function update_rating_and_review_data_from_aawp() {
		if ( Model::table_exists( Model::get_wp_table_name( 'aawp_products' ) ) ) {
			$sql = '
				UPDATE ' . Model::get_wp_table_name( LASSO_AMAZON_PRODUCTS_DB ) . ' AS lap
					INNER JOIN ' . Model::get_wp_table_name( 'aawp_products' ) . ' AS aawpp 
						ON CONVERT(lap.amazon_id USING utf8) = CONVERT(aawpp.asin USING utf8)
				SET lap.rating = IF(aawpp.rating IS NOT NULL, aawpp.rating, lap.rating),
					lap.reviews = IF(aawpp.reviews IS NOT NULL, aawpp.reviews, lap.reviews)
			';

			Model::query( $sql );
		}
	}

	/**
	 * Get post by the post_name
	 *
	 * @param string $post_name Post name.
	 */
	public static function get_post_by_post_name( $post_name ) {
		$sql     = '
			SELECT *
			FROM ' . Model::get_wp_table_name( 'posts' ) . '
			WHERE post_name = %s
				AND post_type IN (
					%s,
					%s
				)
		';
		$prepare = Model::prepare( $sql, $post_name, LASSO_POST_TYPE, Setting_Enum::THIRSTYLINK_SLUG );

		return Model::get_row( $prepare );
	}

	/**
	 * Get post by the old post_name
	 *
	 * @param string $old_post_name Old post name.
	 */
	public static function get_post_by_old_slug( $old_post_name ) {
		if ( ! trim( $old_post_name ) ) {
			return null;
		}

		$sql     = '
			SELECT p.*
			FROM ' . Model::get_wp_table_name( 'posts' ) . ' AS p
				LEFT JOIN ' . Model::get_wp_table_name( 'postmeta' ) . ' AS pm
					ON p.ID = pm.post_id
			WHERE pm.meta_key = %s
				AND pm.meta_value = %s
				AND p.post_type = %s
		';
		$prepare = Model::prepare( $sql, 'lasso_old_slug', $old_post_name, LASSO_POST_TYPE );

		return Model::get_row( $prepare );
	}

	/**
	 * Add prefix 'amzn-' to the post_name for the Lasso Amazon post type.
	 *
	 * @return void
	 */
	public static function add_prefix_to_lasso_amazon_permalink() {
		$post_name_length = Model::get_column_length( 'posts', 'post_name' );
		$prefix           = 'amzn-';
		$prefix_length    = strlen( $prefix );
		$sql              = '
			UPDATE ' . Model::get_wp_table_name( 'posts' ) . '
			SET post_name = 
				CASE
					WHEN CHAR_LENGTH(post_name) >= %d - %d
						THEN CONCAT(%s, SUBSTRING(post_name, 6))
					ELSE
						CONCAT(%s, post_name)
				END
			WHERE 
				ID IN (
					SELECT lasso_id
					FROM ' . Model::get_wp_table_name( 'lasso_url_details' ) . '
					WHERE product_type = %s
				)
				AND NOT post_name LIKE %s
		';
		$prepare          = Model::prepare( $sql, $post_name_length, $prefix_length, $prefix, $prefix, 'amazon', $prefix . '%' );

		Model::query( $prepare );
	}
}
