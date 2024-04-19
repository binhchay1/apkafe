<?php
/**
 * Setting General - Ajax.
 *
 * @package Pages
 */

namespace Lasso\Pages\Settings_General;

use Lasso\Classes\Activator as Lasso_Activator;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Classes\Setting_Enum;

use Lasso\Models\Model as Lasso_Model;
use Lasso\Models\Url_Details;

use Lasso_License;
use Lasso_Process_Link_Database;
use Lasso_Amazon_Api;
use Lasso_Process_Remove_Attribute;
use Lasso_Process_Build_Link;
use Lasso_Process_Build_Rewrite_Slug_Links_In_Posts;

/**
 * Setting General - Ajax.
 */
class Ajax {
	/**
	 * Declare "Lasso ajax requests" to WordPress.
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_lasso_reactivate_license', array( $this, 'lasso_reactivate_license' ) );
		add_action( 'wp_ajax_lasso_get_stats', array( $this, 'lasso_get_stats_new' ) );
		add_action( 'wp_ajax_lasso_remove_lasso_attributes', array( $this, 'lasso_remove_lasso_attributes' ) );
		add_action( 'wp_ajax_lasso_store_settings', array( $this, 'lasso_store_settings' ) );
		add_action( 'wp_ajax_lasso_rescan_lasso_attributes', array( $this, 'lasso_rescan_lasso_attributes' ) );
		add_action( 'wp_ajax_lasso_override_display_option', array( $this, 'lasso_override_display_option' ) );
	}

	/**
	 * Re-activate license again in Setting page
	 */
	public function lasso_reactivate_license() {
		// phpcs:ignore
		$license = wp_unslash( $_POST['license'] ?? '' );

		Lasso_Setting::lasso_set_setting( 'license_serial', $license );
		Lasso_License::lasso_getinfo();
		Lasso_Activator::add_default_data();

		list($license_status, $error_code, $error_message) = Lasso_License::check_license( $license );

		wp_send_json_success(
			array(
				'status'        => $license_status,
				'error_code'    => $error_code,
				'error_message' => $error_message,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Get stats of background processes
	 */
	public function lasso_get_stats_new() {
		$bg      = new Lasso_Process_Link_Database();
		$support = boolval( $_POST['support'] ?? 0 ); // phpcs:ignore
		$stats   = '';
		if ( $bg->is_process_running() && $bg->get_total() > 0 ) {
			// @codeCoverageIgnoreStart
			$stats .= '
				<div class="col-lg mb-3">
					<div class="border rounded p-3">
						<h4 class="h6 purple font-weight-bold">Build Progress</h4>
						<strong>' . $bg->get_total_completed() . '</strong> completed of </strong>' . $bg->get_total() . '</strong> total
					</div>
				</div>
				<div class="col-lg mb-3">
					<div class="border rounded p-3">
						<h4 class="h6 purple font-weight-bold">ETA</h4>
						<strong><span class="eta">' . $bg->get_eta() . '</span></strong>
					</div>
				</div>
				<div class="col-l-12 p-3 gprocessing-text">
					We\'re indexing your links in the background, feel free to do something else.
				</div>
			';
			// @codeCoverageIgnoreEnd
		} else {
			$automation_stats = Lasso_Helper::get_automation_stats();
			$site_total       = 0;
			if ( count( $automation_stats['total'] ) > 0 ) {
				foreach ( $automation_stats['total'] as $obj ) {
					$site_total += $obj->total;
				}
			}

			$stats .= '
				<div class="col-lg mb-3">
					<div class="border rounded p-3">
						<h4 class="h6 purple font-weight-bold">Last Updated</h4>
						' . $automation_stats['latest_date'] . '
					</div>
				</div>
				<div class="col-lg mb-3">
					<div class="border rounded p-3">
						<h4 class="h6 purple font-weight-bold">Links Indexed</h4>
						' . number_format( $site_total ) . '
					</div>
				</div>
			';

			if ( 0 === $site_total ) {
				$stats = '';
			}
		}

		// ? remove attribute process
		$bg_remove_attr   = new Lasso_Process_Remove_Attribute();
		$remove_attr_html = '';
		if ( $bg_remove_attr->is_process_running() || $bg_remove_attr->get_total() > 0 ) {
			// @codeCoverageIgnoreStart
			$remove_attr_html .= '
				<div class="col-lg">
					<div class="border rounded p-3">
						<h4 class="h6 purple font-weight-bold">Removal Progress</h4>
						<strong>' . $bg_remove_attr->get_total_completed() . '</strong> completed of </strong>' . $bg_remove_attr->get_total() . '</strong> total
					</div>
				</div>
				<div class="col-lg">
					<div class="border rounded p-3">
						<h4 class="h6 purple font-weight-bold">ETA</h4>
						<strong><span class="eta">' . $bg_remove_attr->get_eta() . '</span></strong>
					</div>
				</div>
				<div class="col-l-12 p-3 gprocessing-text">
					We\'re removing Lasso attributes in the background, feel free to do something else.
				</div>
			';

			if ( ! $support ) {
				$remove_attr_html .= '
					<script>
						jQuery("#remove_lasso_attributes").hide();
					</script>
				';
			}
			// @codeCoverageIgnoreEnd
		}

		if ( wp_doing_ajax() ) {
			wp_send_json_success(
				array(
					'stats'            => $stats,
					'remove_attribute' => $remove_attr_html,
				)
			);
		}

		return $stats; // @codeCoverageIgnore
	} // @codeCoverageIgnore

	/**
	 * Remove lasso attributes
	 */
	public function lasso_remove_lasso_attributes() {
		update_option( 'lasso_disable_processes', 1, false );

		$bg     = new Lasso_Process_Remove_Attribute();
		$result = $bg->scan_post_page();

		wp_send_json_success(
			array(
				'status' => 1,
				'result' => $result,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Store Lasso settings
	 */
	public function lasso_store_settings() {
		$data = wp_unslash( $_POST ); // phpcs:ignore

		if ( empty( $data['settings'] ) ) {
			wp_send_json_error( 'No settings to save.' );
		}

		$settings = $data['settings'];
		$options  = $settings;

		// ? Loop and check for checkbox values, convert them to boolean.
		foreach ( $settings as $key => $value ) {
			if ( 'amazon_tracking_id_whitelist' === $key && ! is_array( $value ) ) {
				$value = Lasso_Helper::trim( $value );
				$value = explode( ' ', $value );
				sort( $value );
				$value = array_unique( $value );
			}

			if ( is_array( $value ) ) {
				$options[ $key ] = $value;
			} elseif ( 'true' === (string) $value ) {
				$options[ $key ] = true;
			} elseif ( 'false' === (string) $value ) {
				$options[ $key ] = false;
			} else {
				$options[ $key ] = trim( $value );
			}
		}

		// ? Amazon page
		$lasso_options = Lasso_Setting::lasso_get_settings();
		if ( isset( $data['tab'] ) && ( 'amazon' === $data['tab'] ) ) {
			$options['amazon_tracking_id_whitelist'] = $options['amazon_tracking_id_whitelist'] ?? array();

			$current_auto_monetize = $lasso_options['auto_monetize_amazon'] ?? false;
			$new_auto_monetize     = $options['auto_monetize_amazon'] ?? false;
			if ( ! empty( $options['amazon_tracking_id'] ) && ! $current_auto_monetize && $new_auto_monetize ) {
				$bg = new Lasso_Process_Build_Link();
				$bg->link_database_limit();
			}
		}

		// ? Rewrite Lasso url slug
		$origin_rewrite_slug     = Lasso_Setting::lasso_get_setting( 'rewrite_slug' );
		$options['rewrite_slug'] = isset( $options['rewrite_slug'] ) ? sanitize_title( $options['rewrite_slug'], '' ) : $origin_rewrite_slug;
		$new_rewrite_slug        = $options['rewrite_slug'];

		// ? Build new rewrite slug for lasso cloaking links in posts
		if ( $origin_rewrite_slug !== $new_rewrite_slug ) {
			$rewrite_slug_bg = new Lasso_Process_Build_Rewrite_Slug_Links_In_Posts();
			$rewrite_slug_bg->link_database();
		}

		// ? update settings
		Lasso_Setting::lasso_set_settings( $options );

		// ? test Amazon connection
		$status    = 1;
		$error_msg = '';

		if ( ! empty( $options['amazon_tracking_id'] )
			// ? Just the Tracking ID
			&& empty( $options['amazon_access_key_id'] )
			&& empty( $options['amazon_secret_key'] )
		) {
			$status = 1;
			update_option( 'lasso_amazon_valid', true );
		} elseif ( ! empty( $options['amazon_access_key_id'] )
			// ? Attempting to hook up the Amazon API
			|| ! empty( $options['amazon_secret_key'] )
			|| ! empty( $options['amazon_tracking_id'] )
		) {
			$amazon_api = new Lasso_Amazon_Api();
			$result     = $amazon_api->validate_amazon_settings();

			if ( isset( $result[0] ) ) {
				$error_msg = $result[0]->Message;
				$status    = 0;
			}

			// ? Import configurations from Amazon Associates Link Builder
			// ? https://wordpress.org/plugins/amazon-associates-link-builder/
			if ( class_exists( 'AmazonAssociatesLinkBuilder\constants\Db_Constants' ) ) {
				// @codeCoverageIgnoreStart
				$tracking_ids                              = json_decode( get_option( \AmazonAssociatesLinkBuilder\constants\Db_Constants::STORE_IDS ), true );
				$options['amazon_associates_link_builder'] = $tracking_ids;

				// ? update settings
				Lasso_Setting::lasso_set_settings( $options );
				// @codeCoverageIgnoreEnd
			}
		}

		// ? General page
		if ( isset( $data['tab'] ) && ( 'general' === $data['tab'] ) ) {
			Lasso_License::check_license( $options['license_serial'] );
			$options['cpt_support']           = $options['cpt_support'] ?? array( 'post', 'page' );
			$options['custom_fields_support'] = $options['custom_fields_support'] ?? array();

			// @codingStandardsIgnoreStart
			$options['keep_original_url'] = $options['keep_original_url'] ?? array();
			$options['keep_original_url'] = is_array( $options['keep_original_url'] ) ? $options['keep_original_url'] : array();
			// ? Remove https and http
			$options['keep_original_url'] = array_map( function( $value ) {
				return str_replace( array( 'http://', 'https://' ), '', $value );
			}, $options['keep_original_url'] );
			// ? Remove empty values and not domain
			$options['keep_original_url'] = array_filter( $options['keep_original_url'], function( $value ) {
				return Lasso_Helper::validate_url( 'https://' . $value ) && strpos( $value, '.' ) !== false;
			} );
			// @codingStandardsIgnoreEnd

			$options['manually_background_process_limit'] = intval( $options['manually_background_process_limit'] ?? $lasso_options['manually_background_process_limit'] );
			$options['manually_background_process_limit'] = $options['manually_background_process_limit'] < 1 ? 1 : $options['manually_background_process_limit'];
			$options['manually_background_process_limit'] = $options['manually_background_process_limit'] > 3 ? 3 : $options['manually_background_process_limit'];

			Lasso_Setting::lasso_set_settings( $options );
		}

		// ? Display page
		if ( isset( $data['tab'] ) && ( 'display' === $data['tab'] ) ) {
			$custom_css      = $options['custom_css'] ?? '';
			$custom_css      = Lasso_Helper::format_css( $custom_css );
			$disclosure_text = $options['disclosure_text'];
			$show_price      = $options['show_price'];
			$show_disclosure = $options['show_disclosure'];
			$theme_name      = $options['theme_name'];

			$displays = array(
				'custom_css'         => $custom_css, // ? show css in the setting display page
				'custom_css_default' => $custom_css, // ? print css at frontend
				'show_disclosure'    => $show_disclosure,
				'disclosure_text'    => $disclosure_text,
				'show_price'         => $show_price,
				'theme_name'         => $theme_name,
			);
			Lasso_Setting::lasso_set_settings( $displays );
		}

		$amazon_valid = (bool) get_option( 'lasso_amazon_valid', false );

		do_action( Setting_Enum::HOOK_AFTER_SAVED_SETTINGS, $options );

		wp_send_json_success(
			array(
				'options'      => $options,
				'status'       => $status,
				'amazon_valid' => $amazon_valid,
				'error_msg'    => $error_msg,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Re-scan lasso attributes
	 */
	public function lasso_rescan_lasso_attributes() {
		update_option( 'lasso_disable_processes', 0, false );

		$bg     = new Lasso_Process_Link_Database();
		$result = $bg->link_database_limit();

		wp_send_json_success(
			array(
				'status' => 1,
				'result' => $result,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Override display option for all existing Lasso posts
	 */
	public function lasso_override_display_option() {
		$option_name  = $_POST['option_name'] ?? ''; // phpcs:ignore
		$option_value = $_POST['option_value'] ?? ''; // phpcs:ignore

		if ( $option_name && $option_value ) {
			$wp_posts_table    = Lasso_Model::get_wp_table_name( 'posts' );
			$wp_postmeta_table = Lasso_Model::get_wp_table_name( 'postmeta' );
			$url_details_table = ( new Url_Details() )->get_table_name();

			$option_value = Lasso_Helper::cast_to_boolean( $option_value );
			$option_value = $option_value ? '1' : '0';

			// ? update existing metadata
			$sql = '
				UPDATE ' . $wp_postmeta_table . '
				SET meta_value = %s
				WHERE meta_key = %s
					AND post_id IN (
						SELECT ID
						FROM ' . $wp_posts_table . '
						WHERE post_type = %s
					)
			';
			$sql = Lasso_Model::prepare( $sql, $option_value, $option_name, LASSO_POST_TYPE );
			Lasso_Model::query( $sql );

			// ? insert metadata
			$sql = '
				INSERT INTO ' . $wp_postmeta_table . ' (post_id, meta_key, meta_value)
				SELECT lud.lasso_id, %s, %s
				FROM ' . $wp_posts_table . ' AS wpp
					LEFT JOIN ' . $url_details_table . ' AS lud
					ON wpp.ID = lud.lasso_id
				WHERE wpp.post_type = %s
					AND lud.lasso_id NOT IN (
						SELECT post_id
						FROM ' . $wp_postmeta_table . '
						WHERE meta_key = %s
					)
			';
			$sql = Lasso_Model::prepare( $sql, $option_name, $option_value, LASSO_POST_TYPE, $option_name );
			Lasso_Model::query( $sql );
		}

		wp_send_json_success();
	} // @codeCoverageIgnore
}
