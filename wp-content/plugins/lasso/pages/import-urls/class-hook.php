<?php
/**
 * Lasso Import - Hook.
 *
 * @package Pages
 */

namespace Lasso\Pages\Import_Urls;

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Import as Lasso_Import;
use Lasso\Classes\Link_Location;
use Lasso\Classes\Verbiage as Lasso_Verbiage;

use Lasso\Models\Model as Lasso_Model;
use Lasso\Models\Link_Locations;

use Lasso_Process_Import_All;
use Lasso_Process_Link_Database;


/**
 * Lasso Import - Hook.
 */
class Hook {
	/**
	 * Declare "Lasso register hook events" to WordPress.
	 */
	public function register_hooks() {
		add_action( Lasso_Import::AFTER_IMPORT_ACTION, array( $this, 'after_import_action' ), 10, 3 );
		add_action( Lasso_Import::AFTER_IMPORT_ACTION, array( $this, 'scan_link_for_site_stripe' ), 10, 4 );
		add_action( Lasso_Import::AFTER_REVERT_ACTION, array( $this, 'after_revert_action' ), 10, 2 );
		add_action( 'lasso_lite_import_all_process', array( $this, 'stop_import_all_process_from_pro' ) );
	}

	/**
	 * After import action
	 *
	 * @param string $amazon_product_id Amazon product id.
	 * @param string $post_type         Import post type.
	 * @param int    $lasso_id          Lasso Id.
	 * @return $this
	 */
	public function after_import_action( $amazon_product_id, $post_type, $lasso_id ) {
		if ( 'aawp' === $post_type ) {
			// ? Support the client scan AAWP shortcode in "affiliate_box_sidebar" ACF
			$site_domain = Lasso_Helper::get_base_domain( site_url() );
			if ( $lasso_id && in_array( $site_domain, Lasso_Verbiage::SUPPORT_SITES['scan_aawp_shortcode_for_acf_affiliate_box_sidebar_key'], true ) ) {
				$acf_key = 'affiliate_box_sidebar';

				// ? Get postmeta rows that including shortcode of importing ID.
				$sql = '
					SELECT pm.meta_id, pm.meta_value
					FROM ' . Lasso_Model::get_wp_table_name( 'postmeta' ) . ' AS pm
						INNER JOIN ' . Lasso_Model::get_wp_table_name( 'posts' ) . ' AS p 
							ON p.ID = pm.post_id
					WHERE pm.meta_key = %s 
						AND p.post_status IN (%s, %s)
						AND pm.meta_value LIKE "%' . $amazon_product_id . '%"
						AND (
							pm.meta_value NOT LIKE "[amazon% id=%"
							AND pm.meta_value NOT LIKE "[aawp% id=%"
						)
						AND (
							pm.meta_value LIKE "[amazon%"
							OR pm.meta_value LIKE "[aawp%"
						)
				';
				$sql = Lasso_Model::prepare( $sql, $acf_key, 'publish', 'draft' );

				$results = Lasso_Model::get_results( $sql );

				if ( is_countable( $results ) && count( $results ) ) {
					foreach ( $results as $result ) {
						$aawp_shortcode  = $result->meta_value;
						$lasso_shortcode = str_replace( '[amazon ', '[lasso id="' . $lasso_id . '" ', $aawp_shortcode );
						$lasso_shortcode = str_replace( '[aawp ', '[lasso id="' . $lasso_id . '" ', $lasso_shortcode );

						$sql = '
							UPDATE ' . Lasso_Model::get_wp_table_name( 'postmeta' ) . '
							SET meta_value = %s
							WHERE meta_id = %d
								AND meta_key = %s
						';
						$sql = Lasso_Model::prepare( $sql, $lasso_shortcode, $result->meta_id, $acf_key );
						Lasso_Model::query( $sql );
					}
				}
			}
		}

		return $this;
	}

	/**
	 * After revert action
	 *
	 * @param int    $lasso_id      Lasso Id.
	 * @param string $import_source Import source.
	 * @return $this
	 */
	public function after_revert_action( $lasso_id, $import_source ) {
		if ( 'AAWP' === $import_source ) {
			$site_domain = Lasso_Helper::get_base_domain( site_url() );
			if ( in_array( $site_domain, Lasso_Verbiage::SUPPORT_SITES['scan_aawp_shortcode_for_acf_affiliate_box_sidebar_key'], true ) ) {
				$acf_key = 'affiliate_box_sidebar';
				// ? Get postmeta rows that including Lasso shortcode using the reverting id.
				$sql = '
					SELECT pm.meta_id, pm.meta_value
					FROM ' . Lasso_Model::get_wp_table_name( 'postmeta' ) . ' AS pm
						INNER JOIN ' . Lasso_Model::get_wp_table_name( 'posts' ) . ' AS p 
							ON p.ID = pm.post_id
					WHERE pm.meta_key = %s 
						AND p.post_status IN (%s, %s)
						AND pm.meta_value LIKE "[lasso id=\"' . $lasso_id . '\"%"
				';
				$sql = Lasso_Model::prepare( $sql, $acf_key, 'publish', 'draft' );

				$results = Lasso_Model::get_results( $sql );

				if ( is_countable( $results ) && count( $results ) ) {
					foreach ( $results as $result ) {
						$lasso_shortcode = $result->meta_value;
						$aawp_shortcode  = str_replace( '[lasso id="' . $lasso_id . '" ', '[amazon ', $lasso_shortcode );

						$sql = '
							UPDATE ' . Lasso_Model::get_wp_table_name( 'postmeta' ) . '
							SET meta_value = %s
							WHERE meta_id = %d
								AND meta_key = %s
						';
						$sql = Lasso_Model::prepare( $sql, $aawp_shortcode, $result->meta_id, $acf_key );
						Lasso_Model::query( $sql );
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Stop Import all process from Pro if Lite is running import all
	 *
	 * @return $this
	 */
	public function stop_import_all_process_from_pro() {
		$import_all_process = new Lasso_Process_Import_All();
		$import_all_process->remove_process();
		update_option( Lasso_Process_Import_All::OPTION, '0' );
		delete_option( Lasso_Process_Import_All::FILTER_PLUGIN );

		return $this;
	}

	/**
	 * After import action
	 *
	 * @param string $amazon_product_id Amazon product id.
	 * @param string $post_type         Import post type.
	 * @param int    $lasso_id          Lasso Id.
	 * @param bool   $is_import_all     Is import all.
	 */
	public function scan_link_for_site_stripe( $amazon_product_id, $post_type, $lasso_id, $is_import_all ) {
		if ( Link_Location::DISPLAY_TYPE_SITE_STRIPE !== $post_type || $is_import_all ) {
			return;
		}

		$post_ids = Link_Locations::get_post_id_by_site_stripe( $amazon_product_id );

		if ( count( $post_ids ) <= 0 ) {
			return;
		}

		$scan_link = new Lasso_Process_Link_Database();
		$scan_link->link_database( $post_ids );
	}
}
