<?php
/**
 * Declare class Verbiage
 *
 * @package Verbiage
 */

namespace Lasso\Classes;

/**
 * Verbiage
 */
abstract class Verbiage {
	const PROCESS_DESCRIPTION = array(
		'Lasso_Process_Check_Issue'                       => 'Checking for broken links',
		'Lasso_Process_Data_Sync_Content'                 => 'Syncing data',
		'Lasso_Process_Import_All'                        => 'Importing links',
		'Lasso_Process_Link_Database'                     => 'Updating links',
		'Lasso_Process_Build_Link'                        => 'Updating links',
		'Lasso_Process_Scan_Links_Post_Save'              => 'Saving link changes',
		'Lasso_Process_Remove_Attribute'                  => 'Removing attributes',
		'Lasso_Process_Replace_Shortcode'                 => 'Updating shortcodes',
		'Lasso_Process_Revert_All'                        => 'Reverting imports',
		'Lasso_Process_Scan_Keyword'                      => 'Scanning for keywords',
		'Lasso_Process_Scan_Link'                         => 'Updating links',
		'Lasso_Process_Update_Amazon'                     => 'Updating Amazon data',
		'Lasso_Process_Add_Amazon'                        => 'Adding Amazon data',
		'Lasso_Process_Build_Rewrite_Slug_Links_In_Posts' => 'Updating cloaked link prefixes',
		'Lasso_Process_Force_Scan_All_Posts'              => 'Scan all links',
		'Lasso_Process_Create_Webp_Image'                 => 'Creating Webp image',
		'Lasso_Process_Create_Webp_Image_Table'           => 'Creating Webp image',
		'Lasso_Process_Bulk_Add_Links'                    => 'Bulk Add Links',
		'Lasso_Process_Auto_Monetize'                     => 'Auto Monetize',
	);

	const SUPPORT_SITES = array(
		'fix_link_changed_to_destination_url_issue_by_pretty_link_data' => array(),
		'fix_origin_url_has_been_changed_to_destination_url_issue' => array(
			'besserklettern.com',
		),
		'update_incorrect_monetized_url'           => array(
			'worksion.com',
			'example.org', // ? Unit testing domain
		),
		'fix_shortcode_lasso_amazon_url_causing_amazon_link_issues' => array(
			'example.org', // ? Unit testing domain
		),
		'get_final_url_domain_bls'                 => array(
			'fanatics.93n6tx.net',
			'shareasale.com',
			'shareasale-analytics.com',
			'rstyle.me',
			'link.archeraffiliates.com',
			'lasso.link',
			'click.linksynergy.com',
			'dashboard.m1.com',
		),
		'update_category_for_imported_pretty_link' => array(
			'orlandolocal.com',
			'williambeem.com',
			'suburbiapress.com',
		),
		'scan_aawp_shortcode_for_acf_affiliate_box_sidebar_key' => array(
			'gamecows.com',
			'example.org', // ? Unit testing domain
		),
		'fix_import_revert_post_name'              => array(
			'daveswift.com',
		),
		'fix_import_revert_href'                   => array(
			'greenbudguru.com',
		),
		'fix_revert_internal_link_was_changed_to_lasso_link_having_the_same_permalink' => array(
			'smarthomefans.nl',
			'chromefans.nl',
		),
		'fix_amz_prefix_incorrect'                 => array(
			'justcreative.com', // ? Unit testing domain
		),
	);
}
