<?php
use Lasso\Classes\Helper as Lasso_Helper;

if ( Lasso_Helper::is_wp_elementor_plugin_actived() ) {
	require_once LASSO_PLUGIN_PATH . '/classes/class-elementor.php';
}
