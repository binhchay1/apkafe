<?php
/**
 * Declare class Update_DB
 *
 * @package Update_DB
 */

namespace Lasso\Classes;

use Lasso\Models\Auto_Monetize;
use Lasso\Models\Link_Locations;
use Lasso\Models\MetaData;
use Lasso\Models\Table_Mapping;
use Lasso\Models\Table_Title_Field;
use Lasso\Models\Revert;

use Lasso\Classes\Activator as Lasso_Activator;

use Lasso_DB_Script;

/**
 * Update_DB
 */
class Update_DB {
	/**
	 * Update_DB constructor.
	 */
	public function __construct() {
		// ? Create any new tables and update existing schemas.
		$this->update_lasso_tables();

		// ? Perform any additional schema changes and data updates/upgrades.
		$this->update_lasso_database();

		// ? Upgrade the version to the latest LASSO_VERSION
		$lasso_activator = new Lasso_Activator();
		$lasso_activator->update_version();
	}

	/**
	 * Get version from database
	 */
	public function get_version() {
		$version = get_option( 'lasso_version', LASSO_VERSION );
		$version = floatval( $version );

		return $version;
	}

	/**
	 * Update database structure
	 */
	private function update_lasso_tables() {
		$current_version = get_option( 'lasso_version', LASSO_VERSION );
		if ( intval( $current_version ) < intval( LASSO_VERSION ) ) {
			Lasso_Activator::create_lasso_table();
		}
	}

	/**
	 * Update database structure for new version
	 */
	public function update_lasso_database() {
		$db_script    = new Lasso_DB_Script();
		$revert_model = new Revert();

		$version = $this->get_version();

		if ( $version < 198 ) {
			$db_script->create_url_issue_def_table();
			$db_script->create_tracked_keyword_table();

			// ? Data corrections
			$db_script->upgrade_url_issues_data_198();

			// ? Update existing db with issue definitions
			$db_script->url_issue_definitions();
			$db_script->amazon_tracking_default_data();
		}

		if ( $version < 228 ) {
			$db_script->remove_current_timestamp_228();
		}

		if ( $version < 229 ) {
			$db_script->fix_category_order_primary_key_229();
		}

		if ( $version < 232 ) {
			$db_script->upgrade_to_url_details_231();
		}

		if ( $version < 234 ) {
			$db_script->create_revert_table();
			$db_script->create_url_issue_def_table();
			$db_script->create_url_issue_table();
			$db_script->create_tracked_keyword_table();
			$db_script->create_keyword_locations_table();
			$db_script->create_amazon_tracking_table();
			$db_script->create_url_details_table();
		}

		if ( $version < 240 ) {
			$db_script->repair_old_indexes_240();

			$db_script->create_link_locations_table();
			$db_script->create_category_order_table();
		}

		if ( $version < 253 ) {
			$db_script->create_amazon_product_table();
			$db_script->create_content_table();
		}

		if ( $version < 255 ) {
			$db_script->create_affiliate_programs_table();
		}

		if ( $version < 257 ) {
			$db_script->create_url_issue_table();
		}

		if ( $version < 262 ) {
			$db_script->create_link_locations_table();
		}

		if ( $version < 263 ) {
			$db_script->create_fields_table();
			$db_script->create_field_mapping_table();
			$db_script->create_default_fields();

			$db_script->trim_old_primary_and_second_urls_263();
		}

		if ( $version < 269 ) {
			// ? Update character for table lasso_category_order reference to WordPress terms table character
			$db_script->create_category_order_table();
		}

		if ( $version < 271 ) {
			// ? Create new table "lasso_post_content_history"
			$db_script->create_lasso_post_content_history_table();
		}

		if ( $version < 277 ) {
			$db_script->keep_the_first_one_records_of_each_post_277();
		}

		if ( $version < 278 ) {
			$db_script->create_link_locations_table();

			$db_script->upgrade_link_locations_278();
		}

		if ( $version < 282 ) {
			$db_script->upgrade_link_locations_282();
		}

		// ? next release should call this $db_script->upgrade_link_locations() again
		if ( $version < 284 ) {
			$db_script->create_link_locations_table();

			$db_script->upgrade_link_locations_284();
		}

		if ( $version < 288 ) {
			$db_script->create_table_details();
			$db_script->create_table_mapping();
			$db_script->create_table_field_group();
			$db_script->create_table_field_group_detail();
			$db_script->create_fields_table();
			$db_script->create_default_fields();

		}

		if ( $version < 288 ) {
			$db_script->create_extend_product_table();
			$db_script->create_url_details_table();

			$db_script->upgrade_url_details_to_apply_extend_product_288();
			$db_script->update_chewy_image_link_to_https_288();
		}

		if ( $version < 291 ) {
			$db_script->upgrade_url_details_to_apply_extend_product_291();
		}

		if ( $version < 293 ) {
			$db_script->create_link_locations_table(); // ? recreate the table to set default value for is_ignored column
			$db_script->create_fields_table();
		}

		if ( $version < 294 ) {
			$db_script->restore_fields_type();
		}

		if ( $version < 295 ) {
			$db_script->create_table_details();
			$db_script->create_default_fields();
			$db_script->create_table_field_group();
			$db_script->create_table_field_group_detail();
			$db_script->move_table_title_field_to_group_detail();
			$db_script->set_table_fields_default_value();

			$db_script->create_table_metadata();
			$db_script->create_table_mapping(); // ? recreate the table to add new column
		}

		if ( $version < 298 ) {
			$table_mapping = new Table_Mapping();
			$table_mapping->drop_columns( array( 'col_type' ) );

			$model_title_field = new Table_Title_Field();
			$model_title_field->drop_table();

			$model_metadata = new MetaData();
			$model_metadata->drop_index( 'idx_object_id_type_meta_key_meta_value' );
			$model_metadata->create_table();

			$db_script->create_content_table();
		}

		if ( $version < 306 ) {
			$db_script->create_amazon_product_table();
			$db_script->update_rating_and_review_data_from_aawp();
		}

		if ( $version < 308 ) {
			$model_metadata = new MetaData();
			$model_metadata->add_primary_column();
			$model_metadata->create_table();
		}

		if ( $version < 310 ) {
			$db_script->create_amazon_shortened_url_table();
		}

		if ( $version < 313 ) {
			$db_script->create_auto_monetize_table();
			// @codingStandardsIgnoreStart
			// $link_locations = new Link_Locations();
			// $link_locations->update_for_v278();
			// @codingStandardsIgnoreEnd
		}
		if ( $version < 316 ) {
			$model = new Auto_Monetize();
			if ( $model->is_table_created() ) {
				$model->drop_index( 'unique_url' );
			}
			$model->create_table();
			$model->add_url_encrypt_index();
			$model->populate_url_encrypt_data();

			$revert_model->update_for_v316();
		}

		if ( $version < 319 ) {
			$db_script->add_prefix_to_lasso_amazon_permalink();
		}

		if ( $version < 324 ) {
			$model = new Link_Locations();
			$model->create_table();

			$revert_model = new Revert();
			$revert_model->create_table();

			$model = new Auto_Monetize();
			$model->create_table();
		}

		if ( $version < 325 ) {
			$model = new Link_Locations();
			$model->create_table();
		}
	}
}
