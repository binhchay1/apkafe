<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.5.0
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}
/**
 * Set constants.
 */
define( 'BSF_AIOSRS_PRO_FILE', __FILE__ );
define( 'BSF_AIOSRS_PRO_DIR', plugin_dir_path( BSF_AIOSRS_PRO_FILE ) );

/**
 * Required uninstall file.
 */
require_once BSF_AIOSRS_PRO_DIR . 'classes/class-bsf-aiosrs-pro-helper.php';
require_once BSF_AIOSRS_PRO_DIR . 'classes/class-bsf-aiosrs-pro-schema-global-uninstall.php';
