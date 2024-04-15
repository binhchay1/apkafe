<?php
/**
 * The options configuration.
 *
 * @link       https://shapedplugin.com/
 * @since      2.0.0
 *
 * @package    easy-accordion-free
 * @subpackage easy-accordion-free/framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access directly.

//
// Set a unique slug-like ID.
//
$prefix = 'sp_eap_settings';

//
// Create options.
//
SP_EAP::createOptions(
	$prefix,
	array(
		'menu_title'       => __( 'Settings', 'easy-accordion-free' ),
		'menu_slug'        => 'eap_settings',
		'menu_parent'      => 'edit.php?post_type=sp_easy_accordion',
		'menu_type'        => 'submenu',
		'ajax_save'        => true,
		'show_bar_menu'    => false,
		'save_defaults'    => true,
		'show_reset_all'   => false,
		'show_all_options' => false,
		'show_search'      => false,
		'show_footer'      => false,
		'framework_title'  => __( 'Settings', 'easy-accordion-free' ),
		'framework_class'  => 'sp-eap-options',
		'theme'            => 'light',
	)
);

//
// Create a section.
//
SP_EAP::createSection(
	$prefix,
	array(
		'title'  => __( 'Advanced', 'easy-accordion-free' ),
		'icon' => 'fa fa-wrench',
		'fields' => array(
			array(
				'id'         => 'eap_data_remove',
				'type'       => 'checkbox',
				'title'      => __( 'Clean-up Data on Deletion', 'easy-accordion-free' ),
				'title_info' => __( 'Check this box if you would like Easy Accordion to completely remove all of its data when the plugin is deleted.', 'easy-accordion-free' ),
				'default'    => false,
				'sanitize'   => 'rest_sanitize_boolean',
			),
			array(
				'id'         => 'eap_focus_style',
				'type'       => 'checkbox',
				'title'      => __( 'Focus Style for Accessibility', 'easy-accordion-free' ),
				'title_info' => __( 'Check this to enable focus style to improve accessibility.', 'easy-accordion-free' ),
				'default'    => false,
			),
		),
	)
);

//
// Woo commerce faq.
//
SP_EAP::createSection(
	$prefix,
	array(
		'title'  => __( 'WooCommerce FAQs', 'easy-accordion-free' ),
		'icon'   => 'fa fa-shopping-cart',
		'fields' => array(
			array(
				'id'      => 'woocommarce_setting',
				'type'    => 'license',
				'preview' => true,
				'class'   => 'eap-woocommerce-settings',
			),
		),
	)
);

//
// Custom CSS Fields.
//
SP_EAP::createSection(
	$prefix,
	array(
		'id'     => 'custom_css_section',
		'title'  => __( 'Custom CSS & JS', 'easy-accordion-free' ),
		'icon'   => 'fa fa-file-code-o',
		'fields' => array(
			array(
				'id'       => 'ea_custom_css',
				'type'     => 'code_editor',
				'title'    => __( 'Custom CSS', 'easy-accordion-free' ),
				'sanitize' => 'wp_strip_all_tags',
				'settings' => array(
					'mode'  => 'css',
					'theme' => 'monokai',
				),
			),
			array(
				'id'       => 'custom_js',
				'type'     => 'code_editor',
				'title'    => __( 'Custom JS', 'easy-accordion-free' ),
				'sanitize' => 'wp_strip_all_tags',
				'settings' => array(
					'theme' => 'monokai',
					'mode'  => 'javascript',
				),
			),
		),
	)
);

// Custom CSS.
SP_EAP::createSection(
	$prefix,
	array(
		'title'  => __( 'License Key', 'easy-accordion-free' ),
		'icon'   => 'fa fa-key',
		'fields' => array(
			array(
				'id'   => 'license_key',
				'type' => 'license',
			),
		),
	)
);
