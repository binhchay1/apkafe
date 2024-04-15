<?php
/**
 * The Metabox configuration.
 *
 * @link       https://shapedplugin.com/
 * @since      2.0.0
 *
 * @package    easy-accordion-free
 * @subpackage easy-accordion-free/framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access pages directly.

//
// Metabox of the uppers section / Upload section.
// Set a unique slug-like ID.
//
$eap_accordion_content_source_settings = 'sp_eap_upload_options';

/**
 * Preview metabox.
 *
 * @param string $prefix The metabox main Key.
 * @return void
 */
SP_EAP::createMetabox(
	'sp_eap_live_preview',
	array(
		'title'        => __( 'Live Preview', 'easy-accordion-free' ),
		'post_type'    => 'sp_easy_accordion',
		'show_restore' => false,
		'context'      => 'normal',
	)
);
SP_EAP::createSection(
	'sp_eap_live_preview',
	array(
		'fields' => array(
			array(
				'type' => 'preview',
			),
		),
	)
);

//
// Metabox of the footer section / shortcode section.
// Set a unique slug-like ID.
//
$eap_display_shortcode = 'sp_eap_display_shortcode_sidebar';

//
// Create a metabox.
//
SP_EAP::createMetabox(
	$eap_display_shortcode,
	array(
		'title'     => 'How To Use',
		'post_type' => 'sp_easy_accordion',
		'context'   => 'side',
	)
);

//
// Create a section.
//
SP_EAP::createSection(
	$eap_display_shortcode,
	array(
		'fields' => array(
			array(
				'type'  => 'shortcode',
				'class' => 'eap-admin-sidebar',
			),
		),
	)
);

//
// Create a metabox.
//
SP_EAP::createMetabox(
	$eap_accordion_content_source_settings,
	array(
		'title'     => __( 'Easy Accordion', 'easy-accordion-free' ),
		'post_type' => 'sp_easy_accordion',
		'context'   => 'normal',
	)
);

//
// Create a section.
//
SP_EAP::createSection(
	$eap_accordion_content_source_settings,
	array(
		'fields' => array(
			array(
				'type'  => 'heading',
				'image' => plugin_dir_url( __DIR__ ) . 'img/ea-logo.svg',
				'after' => '<i class="fa fa-life-ring"></i> Support',
				'link'  => 'https://shapedplugin.com/support/?user=lite',
				'class' => 'eap-admin-header',
			),
			array(
				'id'       => 'eap_accordion_type',
				'type'     => 'button_set',
				'class'    => 'eap_accordion_type',
				'title'    => __( 'Accordion Type', 'easy-accordion-free' ),
				'sanitize' => 'sanitize_text_field',
				'options'  => array(
					'content-accordion' => array(
						'text' => '<img src="' . plugin_dir_url( __DIR__ ) . 'img/ea-content.svg"/>' . __( 'Content', 'easy-accordion-free' ),
					),
					'post-accordion'    => array(
						'text'     => '<img src="' . plugin_dir_url( __DIR__ ) . 'img/ea-post.svg"/>' . __( 'Post', 'easy-accordion-free' ),
						'pro_only' => true,
					),
				),
				'default'  => 'content-accordion',
			),
			// Content Accordion.
			array(
				'id'                     => 'accordion_content_source',
				'type'                   => 'group',
				'title'                  => __( 'Content', 'easy-accordion-free' ),
				'button_title'           => __( 'Add New Item', 'easy-accordion-free' ),
				'class'                  => 'eap_accordion_content_wrapper',
				'accordion_title_prefix' => __( 'Item :', 'easy-accordion-free' ),
				'accordion_title_number' => true,
				'accordion_title_auto'   => true,
				'sanitize'               => 'eapro_sanitize_accordion_title_content',
				'fields'                 => array(
					array(
						'id'         => 'accordion_content_title',
						'class'      => 'accordion_content_title',
						'type'       => 'text',
						'wrap_class' => 'eap_accordion_content_source',
						'title'      => __( 'Title', 'easy-accordion-free' ),
					),
					array(
						'id'           => 'accordion_content_icon',
						'type'         => 'icon',
						'class'        => 'pro_only_field',
						'wrap_class'   => 'eap_accordion_content_source',
						'button_title' => __( 'Custom Icon (Pro)', 'easy-accordion-free' ),
					),
					array(
						'id'         => 'accordion_content_description',
						'type'       => 'wp_editor',
						'wrap_class' => 'eap_accordion_content_source',
						'title'      => __( 'Description', 'easy-accordion-free' ),
						'height'     => '150px',
					),
				),
				'dependency'             => array( 'eap_accordion_type', '==', 'content-accordion' ),
			), // End of Content Accordion.
		), // End of fields array.
	)
);

//
// Metabox for the Accordion Post Type.
// Set a unique slug-like ID.
//
$eap_accordion_shortcode_settings = 'sp_eap_shortcode_options';

//
// Create a metabox.
//
SP_EAP::createMetabox(
	$eap_accordion_shortcode_settings,
	array(
		'title'     => __( 'Shortcode Section', 'easy-accordion-free' ),
		'post_type' => 'sp_easy_accordion',
		'theme'     => 'light',
		'context'   => 'normal',
	)
);
//
// Create a section.
//
SP_EAP::createSection(
	$eap_accordion_shortcode_settings,
	array(
		'title'  => __( 'Accordion Settings', 'easy-accordion-free' ),
		'icon'   => 'fa fa-list-ul',
		'fields' => array(
			array(
				'id'         => 'eap_accordion_layout',
				'class'      => 'eap_accordion_layout',
				'type'       => 'image_select',
				'title'      => __( 'Accordion Layout', 'easy-accordion-free' ),
				'subtitle'   => __( 'Choose an accordion layout.', 'easy-accordion-free' ),
				'sanitize'   => 'sanitize_text_field',
				'options'    => array(
					'vertical'     => array(
						'image'       => SP_EA_URL . 'admin/img/ea-vertical.svg',
						'option_name' => __( 'Vertical', 'easy-accordion-free' ),
					),
					'multi-column' => array(
						'pro_only'    => true,
						'image'       => SP_EA_URL . 'admin/img/ea-multicolumn.svg',
						'option_name' => __( 'Multicolumn', 'easy-accordion-free' ),
					),
					'horizontal'   => array(
						'pro_only'    => true,
						'image'       => SP_EA_URL . 'admin/img/ea-horizontal.svg',
						'option_name' => __( 'Horizontal', 'easy-accordion-free' ),
					),
				),
				'title_info' => __( '<div class="ea-info-label">Accordion Layout</div> <div class="ea-short-content">Accordion Layout determines how accordion information is displayed, with choices like vertical, horizontal, or multi column layouts.</div>', 'easy-accordion-free' ),
				'desc' => __( 'To create fantastic Accordion FAQ layouts and access to advanced customizations, <a href="https://easyaccordion.io/pricing/" target="_blank"><strong>Upgrade to Pro!</strong></a>', 'easy-accordion-free' ),
				'default'    => 'vertical',
			),
			array(
				'id'         => 'eap_accordion_theme',
				'type'       => 'select',
				'title'      => __( 'Accordion Theme', 'easy-accordion-free' ),
				'preview'    => true,
				'class'      => 'sp_eap_accordion_theme',
				'sanitize'   => 'sanitize_text_field',
				'subtitle'   => __( 'Select an accordion theme style. To unlock 16+ Premium Ready Accordion Themes, <a href="https://easyaccordion.io/pricing/?ref=1" target="_blank"> <b>Upgrade to Pro!</b> </a>', 'easy-accordion-free' ),
				'options'    => array(
					'sp-ea-one'                       => __( 'Default Theme', 'easy-accordion-free' ),
					'sp-ea-two'                       => __( 'Theme Two', 'easy-accordion-free' ),
					'sp-ea-three ea-icon-style-three' => __( 'Theme Three', 'easy-accordion-free' ),
					'sp-ea-four ea-icon-style-three'  => __( 'Theme Four', 'easy-accordion-free' ),
					'sp-ea-five ea-icon-style-three'  => __( 'Theme Five', 'easy-accordion-free' ),
					'sp-ea-six ea-icon-style-three'   => __( 'Theme Six', 'easy-accordion-free' ),
					'sp-ea-seven'                     => __( 'Theme Seven', 'easy-accordion-free' ),
					'sp-ea-eight ea-icon-style-three' => __( 'Theme Eight', 'easy-accordion-free' ),
					'sp-ea-nine ea-icon-style-three'  => __( 'Theme Nine', 'easy-accordion-free' ),
					'sp-ea-ten'                       => __( 'Theme Ten', 'easy-accordion-free' ),
					'sp-ea-eleven'                    => __( 'Theme Eleven', 'easy-accordion-free' ),
					'sp-ea-twelve'                    => __( 'Theme Twelve', 'easy-accordion-free' ),
					'sp-ea-thirteen'                  => __( 'Theme Thirteen', 'easy-accordion-free' ),
					'sp-ea-fourteen sp-ea-thirteen'   => __( 'Theme Fourteen', 'easy-accordion-free' ),
					'sp-ea-fifteen'                   => __( 'Theme Fifteen', 'easy-accordion-free' ),
					'sp-ea-sixteen'                   => __( 'Theme Sixteen', 'easy-accordion-free' ),
					'sp-ea-seventeen'                 => __( 'Theme Seventeen', 'easy-accordion-free' ),
				),
				'default'    => 'sp-ea-one',
				'only_pro'   => true,
				'dependency' => array( 'eap_accordion_layout', '==', 'vertical' ),
			),
			array(
				'id'         => 'eap_accordion_event',
				'type'       => 'button_set',
				'sanitize'   => 'sanitize_text_field',
				'title'      => __( 'Activator Event', 'easy-accordion-free' ),
				'subtitle'   => __( 'Select event click or mouse over to expand accordion.', 'easy-accordion-free' ),
				'options'    => array(
					'ea-click' => array(
						'text' => __( 'Click', 'easy-accordion-free' ),
					),
					'ea-hover' => array(
						'text' => __( 'Mouse Over', 'easy-accordion-free' ),
					),
					'ea-auto'  => array(
						'text'     => __( 'AutoPlay', 'easy-accordion-free' ),
						'pro_only' => true,
					),
				),
				'title_info' => __( '<div class="ea-info-label">Activator Event</div> <div class="ea-short-content">The <strong>Activator Event</strong> option allows you to define the user interaction that triggers accordion transitions, such as clicking, hovering, or autoplaying.</div><div class="info-button"><a class="ea-open-live-demo" href="https://easyaccordion.io/activator-events/" target="_blank">Live Demo</a></div>', 'easy-accordion-free' ),
				'default'    => 'ea-click',
			),
			array(
				'id'         => 'eap_accordion_mode',
				'class'      => 'eap_accordion_open_mode',
				'type'       => 'radio',
				'sanitize'   => 'sanitize_text_field',
				'title'      => __( 'Accordion Mode', 'easy-accordion-free' ),
				'subtitle'   => __( 'Expand or collapse accordion option on page load.', 'easy-accordion-free' ),
				'options'    => array(
					'ea-first-open' => __( 'First Open', 'easy-accordion-free' ),
					'ea-multi-open' => __( 'All Open', 'easy-accordion-free' ),
					'ea-all-close'  => __( 'All Folded', 'easy-accordion-free' ),
					'custom'        => __( 'Custom Open (Pro)', 'easy-accordion-free' ),
				),
				'title_info' => __( '<div class="ea-info-label">Accordion Mode</div> <div class="ea-short-content">The <strong>Accordion Mode</strong> option lets you choose whether the accordion items should be initially expanded or collapsed when the page loads.</div><div class="info-button"><a class="ea-open-live-demo" href="https://easyaccordion.io/accordion-modes/" target="_blank">Live Demo</a></div>', 'easy-accordion-free' ),
				'default'    => 'ea-first-open',
			),
			array(
				'id'         => 'eap_mutliple_collapse',
				'type'       => 'switcher',
				'title'      => __( 'Multiple Opening Together', 'easy-accordion-free' ),
				'subtitle'   => __( 'Don\'t collapse while expanding another item.', 'easy-accordion-free' ),
				'text_on'    => __( 'Enabled', 'easy-accordion-free' ),
				'text_off'   => __( 'Disabled', 'easy-accordion-free' ),
				'text_width' => 94,
				'default'    => false,
				'sanitize'   => 'rest_sanitize_boolean',
				'title_info' => '<div class="ea-img-tag"><img src="' . esc_url( SP_EA_URL ) . 'admin/img/ea-multiple-opening-together.svg" alt="Multiple Active Together"></div><div class="ea-info-label img">' . __( 'Multiple Active or Opening Together', 'easy-accordion-free' ) . '</div>',
			),
			array(
				'id'         => 'eap_accordion_fillspace',
				'type'       => 'checkbox',
				'title'      => __( 'Fixed Content Height', 'easy-accordion-free' ),
				'subtitle'   => __( 'Check to display collapsible accordion content in a limited amount of space.', 'easy-accordion-free' ),
				'default'    => false,
				'title_info' => '<div class="ea-img-tag"><img src="' . esc_url( SP_EA_URL ) . 'admin/img/ea-fixed-content-height.svg" alt="Fixed Content Height"></div><div class="ea-info-label img">' . __( 'Fixed Content Height', 'easy-accordion-free' ) . '</div>',
				'sanitize'   => 'rest_sanitize_boolean',
			),
			array(
				'id'              => 'eap_accordion_fillspace_height',
				'type'            => 'spacing',
				'title'           => __( 'Maximum Height', 'easy-accordion-free' ),
				'subtitle'        => __( 'Set fixed accordion content panel height. Default height 200px.', 'easy-accordion-free' ),
				'class'           => 'accordion-fillspace-height',
				'all'             => true,
				'all_icon'        => __( '<i class="fa fa-arrows-v"></i>', 'easy-accordion-free' ),
				'all_placeholder' => __( 'Height', 'easy-accordion-free' ),
				'units'           => array(
					'px',
				),
				'default'         => array(
					'all' => '200',
				),
				'attributes'      => array(
					'min' => 50,
				),
				'sanitize'        => 'eapro_sanitize_number_array_field',
				'dependency'      => array( 'eap_accordion_fillspace', '==', 'true' ),
			),
			array(
				'id'       => 'eap_nofollow_link',
				'type'     => 'checkbox',
				'title'    => __( 'Add rel="nofollow" to Link', 'easy-accordion-free' ),
				'default'  => false,
				'sanitize' => 'rest_sanitize_boolean',
			),
			array(
				'id'         => 'eap_scroll_to_active_item',
				'type'       => 'switcher',
				'title'      => __( 'Scroll to Active Item', 'easy-accordion-free' ),
				'subtitle'   => __( 'Enable/Disable this option to scroll to  active accordion item.', 'easy-accordion-free' ),
				'text_on'    => __( 'Enabled', 'easy-accordion-free' ),
				'text_off'   => __( 'Disabled', 'easy-accordion-free' ),
				'text_width' => 94,
				'default'    => false,
				'sanitize'   => 'rest_sanitize_boolean',
				'title_info' => __( '<div class="ea-info-label">Scroll to Active Item</div> <div class="ea-short-content">This option allows automatic scrolling to the active accordion item. This provides a smoother and more user-friendly experience when navigating through accordion faqs section.</div><div class="info-button"><a class="ea-open-docs" href="https://docs.shapedplugin.com/docs/easy-accordion-pro/configurations/how-to-enable-accordion-scrolling-to-active-item/" target="_blank">Open Docs</a></div>', 'easy-accordion-free' ),
			),
			array(
				'id'         => 'eap_schema_markup',
				'type'       => 'switcher',
				'title'      => __( 'Schema Markup', 'easy-accordion-free' ),
				'subtitle'   => __( 'Enable/Disable schema markup.', 'easy-accordion-free' ),
				'text_on'    => __( 'Enabled', 'easy-accordion-free' ),
				'text_off'   => __( 'Disabled', 'easy-accordion-free' ),
				'text_width' => 94,
				'default'    => false,
				'sanitize'   => 'rest_sanitize_boolean',
				'title_info' => __( '<div class="ea-info-label">Schema Markup</div> <div class="ea-short-content"><strong>Schema Markup</strong> adds structured data to your Accordion FAQs, enhancing search engine visibility and improving the display of your Accordion FAQs in search results.</div><div class="info-button"><a class="ea-open-docs" href="https://docs.shapedplugin.com/docs/easy-accordion-pro/configurations/how-to-enable-schema-markup/" target="_blank">Open Docs</a></div>', 'easy-accordion-free' ),
			),
			array(
				'id'         => 'eap_preloader',
				'type'       => 'switcher',
				'sanitize'   => 'rest_sanitize_boolean',
				'title'      => __( 'Preloader', 'easy-accordion-free' ),
				'subtitle'   => __( 'Accordion will be hidden until page load completed.', 'easy-accordion-free' ),
				'text_on'    => __( 'Enabled', 'easy-accordion-free' ),
				'text_off'   => __( 'Disabled', 'easy-accordion-free' ),
				'text_width' => 94,
				'default'    => false,
			),
		), // Fields array end.
	)
); // End of Upload section.

//
// Carousel settings section begin.
//
SP_EAP::createSection(
	$eap_accordion_shortcode_settings,
	array(
		'title'  => __( 'Display Settings', 'easy-accordion-free' ),
		'icon'   => 'fa fa-th-large',
		'fields' => array(
			array(
				'id'         => 'section_title',
				'type'       => 'switcher',
				'title'      => __( 'Accordion Section Title', 'easy-accordion-free' ),
				'subtitle'   => __( 'Show/hide the accordion section title.', 'easy-accordion-free' ),
				'text_on'    => __( 'Show', 'easy-accordion-free' ),
				'text_off'   => __( 'Hide', 'easy-accordion-free' ),
				'text_width' => 80,
				'default'    => false,
				'sanitize'   => 'rest_sanitize_boolean',
			),
			array(
				'id'              => 'accordion_margin_bottom',
				'type'            => 'spacing',
				'title'           => __( 'Space Between', 'easy-accordion-free' ),
				'subtitle'        => __( 'Set a margin to make space between accordion items. Default value is 10px.', 'easy-accordion-free' ),
				'all'             => true,
				'all_icon'        => '<i class="fa fa-arrows-v"></i>',
				'sanitize'        => 'eapro_sanitize_number_array_field',
				'all_placeholder' => 'margin',
				'default'         => array(
					'all' => '10',
				),
				'units'           => array(
					'px',
				),
				'attributes'      => array(
					'min' => 0,
				),
				'title_info'      => '<div class="ea-img-tag"><img src="' . esc_url( SP_EA_URL ) . 'admin/img/ea-accordion-margin.svg" alt="Accordion Margin"></div><div class="ea-info-label img">' . __( 'Space Between Accordion FAQs', 'easy-accordion-free' ) . '</div>',
			),
			array(
				'id'         => 'eap_faq_collapse_button',
				'type'       => 'switcher',
				'class'      => 'only-for-pro-switcher',
				'title'      => __( 'Expand/Collapse All Button', 'easy-accordion-free' ),
				'subtitle'   => __( 'Show/hide expand/collapse all button.', 'easy-accordion-free' ),
				'text_on'    => __( 'Show', 'easy-accordion-free' ),
				'text_off'   => __( 'Hide', 'easy-accordion-free' ),
				'text_width' => 80,
				'default'    => false,
				'sanitize'   => 'rest_sanitize_boolean',
				'title_info' => '<div class="ea-img-tag"><img src="' . esc_url( SP_EA_URL ) . 'admin/img/ea-expand-collapse-all-button.svg" alt="Expand/Collapse All Button"></div><div class="ea-info-label img">' . __( 'Expand/Collapse All Button', 'easy-accordion-free' ) . '</div><div class="info-button img"><a class="ea-open-live-demo" href="https://easyaccordion.io/expand-collapse-all/" target="_blank">Live Demo</a></div>',
			),
			array(
				'id'         => 'eap_faq_search',
				'type'       => 'switcher',
				'class'      => 'only-for-pro-switcher',
				'title'      => __( 'Accordion FAQ Search', 'easy-accordion-free' ),
				'subtitle'   => __( 'Show/hide accordion FAQ search field.', 'easy-accordion-free' ),
				'text_on'    => __( 'Show', 'easy-accordion-free' ),
				'text_off'   => __( 'Hide', 'easy-accordion-free' ),
				'text_width' => 80,
				'default'    => false,
				'sanitize'   => 'rest_sanitize_boolean',
				'title_info' => '<div class="ea-img-tag"><img src="' . esc_url( SP_EA_URL ) . 'admin/img/ea-accordion-faq-search.svg" alt="Accordion FAQ Search"></div><div class="ea-info-label img">' . __( 'Accordion FAQ Search', 'easy-accordion-free' ) . '</div><div class="info-button img"><a class="ea-open-live-demo " href="https://easyaccordion.io/faqs-search-option/" target="_blank">Live Demo</a></div>',
			),
			array(
				'type'    => 'notice',
				'class'   => 'only_pro_notice',
				'content' => __( 'Want to make your FAQ content searchable and add an Expand/Collapse All button? <a href="https://easyaccordion.io/pricing/?ref=1" target="_blank"><b>Upgrade to Pro!</b></a>', 'easy-accordion-free' ),
			),
			array(
				'type'    => 'subheading',
				'content' => __( 'Accordion Expand & Collapse Icon', 'easy-accordion-free' ),
			),
			array(
				'id'         => 'eap_expand_close_icon',
				'type'       => 'switcher',
				'title'      => __( 'Expand & Collapse Icon', 'easy-accordion-free' ),
				'subtitle'   => __( 'Show/hide expand and collapse icon.', 'easy-accordion-free' ),
				'text_on'    => __( 'Show', 'easy-accordion-free' ),
				'text_off'   => __( 'Hide', 'easy-accordion-free' ),
				'text_width' => 80,
				'default'    => true,
				'sanitize'   => 'rest_sanitize_boolean',
			),
			array(
				'id'         => 'eap_expand_collapse_icon',
				'class'      => 'eap_expand_collapse_icon',
				'type'       => 'image_select',
				'title'      => __( 'Expand & Collapse Icon Style', 'easy-accordion-free' ),
				'subtitle'   => __( 'Choose a expand and collapse icon style.', 'easy-accordion-free' ),
				'sanitize'   => 'sanitize_text_field',
				'options'    => array(
					'1'  => array(
						'image' => SP_EA_URL . 'admin/img/collapse-expand-icon/plus-minus.svg',
					),
					'19' => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/plus-times.svg',
						'pro_only' => true,
					),
					'5'  => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/check-times.svg',
						'pro_only' => true,
					),
					'6'  => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/chevron-down-right.svg',
						'pro_only' => true,
					),
					'13' => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/angle-down-up.svg',
						'pro_only' => true,
					),
					'9'  => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/angle-up-down.svg',
						'pro_only' => true,
					),
					'2'  => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/angle-down-right-7.svg',
						'pro_only' => true,
					),
					'18' => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/angle-down-up-18.svg',
						'pro_only' => true,
					),
					'9'  => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/angle-up-down-9.svg',
						'pro_only' => true,
					),
					'3'  => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/angle-double-down-right.svg',
						'pro_only' => true,
					),
					'15' => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/angle-double-down-up.svg',
						'pro_only' => true,
					),
					'10' => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/angle-double-up-down.svg',
						'pro_only' => true,
					),
					'8'  => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/caret-down-right.svg',
						'pro_only' => true,
					),
					'17' => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/caret-up-down-14.svg',
						'pro_only' => true,
					),
					'14' => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/caret-down-up.svg',
						'pro_only' => true,
					),
					'4'  => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/arrow-down-right.svg',
						'pro_only' => true,
					),
					'16' => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/arrow-down-up.svg',
						'pro_only' => true,
					),
					'11' => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/arrow-up-down-18.svg',
						'pro_only' => true,
					),
					'7'  => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/hand-o-down-right.svg',
						'pro_only' => true,
					),
					'20' => array(
						'image'    => SP_EA_URL . 'admin/img/collapse-expand-icon/q-a-img.svg',
						'pro_only' => true,
					),
				),
				'default'    => '1',
				'dependency' => array(
					'eap_expand_close_icon',
					'==',
					'true',
				),
			),

			array(
				'id'              => 'eap_icon_size',
				'type'            => 'spacing',
				'title'           => __( 'Expand & Collapse Icon Size', 'easy-accordion-free' ),
				'subtitle'        => __( 'Set accordion collapse and expand icon size. Default value is 16px.', 'easy-accordion-free' ),
				'all'             => true,
				'all_icon'        => false,
				'all_placeholder' => 'speed',
				'sanitize'        => 'eapro_sanitize_number_array_field',
				'default'         => array(
					'all' => '16',
				),
				'units'           => array(
					'px',
				),
				'attributes'      => array(
					'min' => 0,
				),
				'dependency'      => array(
					'eap_expand_close_icon',
					'==',
					'true',
				),
			),
			array(
				'id'         => 'eap_icon_color_set',
				'type'       => 'color',
				'title'      => __( 'Icon Color', 'easy-accordion-free' ),
				'subtitle'   => __( 'Set icon color.', 'easy-accordion-free' ),
				'default'    => '#444',
				'sanitize'   => 'sanitize_text_field',
				'dependency' => array(
					'eap_expand_close_icon',
					'==',
					'true',
				),
			),

			array(
				'id'         => 'eap_icon_position',
				'type'       => 'button_set',
				'title'      => __( 'Expand & Collapse Icon Position', 'easy-accordion-free' ),
				'sanitize'   => 'sanitize_text_field',
				'subtitle'   => __( 'Set accordion expand and collapse icon position or alignment.', 'easy-accordion-free' ),
				'options'    => array(
					'left'  => array(
						'text' => __( 'Left', 'easy-accordion-free' ),
					),
					'right' => array(
						'text' => __( 'Right', 'easy-accordion-free' ),
					),
				),
				'title_info' => '<div class="ea-img-tag"><img src="' . esc_url( SP_EA_URL ) . 'admin/img/ea-expand-&-collapse-icon-position.svg" alt="Expand & Collapse Icon Position"></div><div class="ea-info-label img">' . __( 'Expand & Collapse Icon Position', 'easy-accordion-free' ) . '</div>',
				'default'    => 'left',
				'dependency' => array(
					'eap_expand_close_icon',
					'==',
					'true',
				),
			),
			array(
				'type'    => 'notice',
				'class'   => 'only_pro_notice',
				'content' => __( 'Want to make your Accordion FAQs visually attractive with tons of flexible options? <a href="https://easyaccordion.io/pricing/?ref=1" target="_blank"><b>Upgrade to Pro!</b></a>', 'easy-accordion-free' ),
			),
			array(
				'type'    => 'subheading',
				'content' => __( 'Accordion Item Title & Description', 'easy-accordion-free' ),
			),
			array(
				'id'         => 'eap_border_css',
				'type'       => 'border',
				'title'      => __( 'Accordion Border', 'easy-accordion-free' ),
				'subtitle'   => __( 'Set accordion item border. Default value is 1px.', 'easy-accordion-free' ),
				'all'        => true,
				'sanitize'   => 'eapro_sanitize_border_field',
				'default'    => array(
					'all'   => 1,
					'style' => 'solid',
					'color' => '#e2e2e2',
				),
				'sanitize'   => 'eapro_sanitize_border_field',
				'title_info' => '<div class="ea-img-tag"><img src="' . esc_url( SP_EA_URL ) . 'admin/img/ea-accordion-border.svg" alt="Accordion Border"></div><div class="ea-info-label img">' . __( 'Accordion Border', 'easy-accordion-free' ) . '</div>',
			),
			array(
				'id'         => 'eap_title_icon',
				'type'       => 'switcher',
				'class'      => 'only-for-pro-switcher',
				'title'      => __( 'Title Icon', 'easy-accordion-free' ),
				'subtitle'   => __( 'Show/hide title icon. e.g. FontAwesome icon before the accordion title.', 'easy-accordion-free' ),
				'text_on'    => __( 'Show', 'easy-accordion-free' ),
				'text_off'   => __( 'Hide', 'easy-accordion-free' ),
				'text_width' => 80,
				'default'    => false,
				'sanitize'   => 'rest_sanitize_boolean',
			),
			array(
				'id'              => 'eap_title_icon_size',
				'type'            => 'spacing',
				'sanitize'        => 'eapro_sanitize_number_array_field',
				'class'           => 'only-for-pro',
				'title'           => __( 'Title Icon Size ', 'easy-accordion-free' ),
				'subtitle'        => __( 'Set title icon size.', 'easy-accordion-free' ),
				'dependency'      => array( 'eap_title_icon', '==', 'true' ),
				'all'             => true,
				'all_icon'        => false,
				'all_placeholder' => '',
				'default'         => array(
					'all'   => 20,
					'units' => 'px',
				),
				'units'           => array(
					'px',
				),
			),
			array(
				'id'       => 'eap_header_bg_color',
				'type'     => 'color',
				'title'    => __( 'Title Background Color', 'easy-accordion-free' ),
				'subtitle' => __( 'Set accordion title background color.', 'easy-accordion-free' ),
				'default'  => '#eee',
				'sanitize' => 'sanitize_text_field',
			),
			array(
				'id'         => 'eap_title_padding',
				'type'       => 'spacing',
				'sanitize'   => 'eapro_sanitize_number_array_field',
				'class'      => 'only-for-pro',
				'title'      => __( 'Title Padding', 'easy-accordion-free' ),
				'subtitle'   => __( 'Set accordion title custom padding.', 'easy-accordion-free' ),
				'units'      => array( 'px' ),
				'default'    => array(
					'left'   => '15',
					'top'    => '15',
					'bottom' => '15',
					'right'  => '15',

				),
				'title_info' => '<div class="ea-img-tag"><img src="' . esc_url( SP_EA_URL ) . 'admin/img/ea-title-padding.svg" alt="Title Padding"></div><div class="ea-info-label img">' . __( 'Title Padding', 'easy-accordion-free' ) . '</div>',
			),
			array(
				'id'         => 'eap_autop',
				'type'       => 'switcher',
				'title'      => __( 'Line Break', 'easy-accordion-free' ),
				'subtitle'   => __( 'wpautop/line break with paragraph in description.', 'easy-accordion-free' ),
				'text_on'    => __( 'Enabled', 'easy-accordion-free' ),
				'text_off'   => __( 'Disabled', 'easy-accordion-free' ),
				'text_width' => 94,
				'default'    => true,
				'sanitize'   => 'rest_sanitize_boolean',
			),
			array(
				'id'       => 'eap_description_bg_color',
				'type'     => 'color',
				'title'    => __( 'Description Background Color', 'easy-accordion-free' ),
				'subtitle' => __( 'Set accordion description background color.', 'easy-accordion-free' ),
				'default'  => '#fff',
				'sanitize' => 'sanitize_text_field',
			),
			array(
				'id'         => 'eap_description_padding',
				'type'       => 'spacing',
				'sanitize'   => 'eapro_sanitize_number_array_field',
				'class'      => 'only-for-pro',
				'title'      => __( 'Description Padding', 'easy-accordion-free' ),
				'subtitle'   => __( 'Set accordion description custom padding.', 'easy-accordion-free' ),
				'units'      => array( 'px' ),
				'default'    => array(
					'left'   => '15',
					'top'    => '15',
					'bottom' => '15',
					'right'  => '15',
				),
				'title_info' => '<div class="ea-img-tag"><img src="' . esc_url( SP_EA_URL ) . 'admin/img/ea-description-padding.svg" alt="Description Padding"></div><div class="ea-info-label img">' . __( 'Description Padding', 'easy-accordion-free' ) . '</div>',
			),
			array(
				'type'    => 'notice',
				'class'   => 'only_pro_notice',
				'content' => __( 'To unlock the full potential of your Accordion FAQs and keep visitors engaged, <a href="https://easyaccordion.io/pricing/?ref=1" target="_blank"><b>Upgrade to Pro!</b></a>', 'easy-accordion-free' ),
			),
			array(
				'type'    => 'subheading',
				'content' => __( 'Accordion Animation', 'easy-accordion-free' ),
			),
			array(
				'id'         => 'eap_animation',
				'type'       => 'switcher',
				'class'      => 'only-for-pro-switcher',
				'title'      => __( 'Animation', 'easy-accordion-free' ),
				'subtitle'   => __( 'Enable/Disable accordion animation.', 'easy-accordion-free' ),
				'text_on'    => __( 'Enabled', 'easy-accordion-free' ),
				'text_off'   => __( 'Disabled', 'easy-accordion-free' ),
				'text_width' => 94,
				'default'    => false,
				'sanitize'   => 'rest_sanitize_boolean',
				'title_info' => __(
					'<div class="ea-info-label">Animation</div> <div class="ea-short-content">The Animation option allows you to control the accordion animation. Customize the visual experience of accordion transitions according to your preference.</div><div class="info-button"><a class="ea-open-live-demo" href="https://easyaccordion.io/accordion-animation/" target="_blank">Live Demo</a></div>',
					'easy-accordion-free'
				),
			),
			array(
				'id'       => 'eap_animation_style',
				'type'     => 'select',
				'class'    => 'only-select-for-pro',
				'title'    => __( 'Animation Style', 'easy-accordion-free' ),
				'subtitle' => __( 'Select an animation style for the description content.', 'easy-accordion-free' ),
				'sanitize' => 'sanitize_text_field',
				'options'  => array(
					'normal'        => __( 'Normal', 'easy-accordion-free' ),
					'fadeIn'        => __( 'fadeIn (Pro)', 'easy-accordion-free' ),
					'fadeInLeft'    => __( 'fadeInLeft (Pro)', 'easy-accordion-free' ),
					'fadeInUp'      => __( 'fadeInUp (Pro)', 'easy-accordion-free' ),
					'fadeInDownBig' => __( 'fadeInDownBig (Pro)', 'easy-accordion-free' ),
					'shake'         => __( 'shake (Pro)', 'easy-accordion-free' ),
					'swing'         => __( 'swing (Pro)', 'easy-accordion-free' ),
					'rollIn'        => __( 'rollIn (Pro)', 'easy-accordion-free' ),
					'bounce'        => __( 'bounce (Pro)', 'easy-accordion-free' ),
					'wobble'        => __( 'wobble (Pro)', 'easy-accordion-free' ),
					'shake'         => __( 'shake (Pro)', 'easy-accordion-free' ),
					'slideInDown'   => __( 'slideInDown (Pro)', 'easy-accordion-free' ),
					'slideInLeft'   => __( 'slideInLeft (Pro)', 'easy-accordion-free' ),
					'slideInUp'     => __( 'slideInUp (Pro)', 'easy-accordion-free' ),
					'zoomIn'        => __( 'zoomIn (Pro)', 'easy-accordion-free' ),
					'zoomInDown'    => __( 'zoomInDown (Pro)', 'easy-accordion-free' ),
					'zoomInUp'      => __( 'zoomInUp (Pro)', 'easy-accordion-free' ),
					'zoomInLeft'    => __( 'zoomInLeft (Pro)', 'easy-accordion-free' ),
					'bounceIn'      => __( 'bounceIn (Pro)', 'easy-accordion-free' ),
					'bounceInDown'  => __( 'bounceInDown (Pro)', 'easy-accordion-free' ),
					'bounceInUp'    => __( 'bounceInUp (Pro)', 'easy-accordion-free' ),
					'jello'         => __( 'jello (Pro)', 'easy-accordion-free' ),
					'swing'         => __( 'swing (Pro)', 'easy-accordion-free' ),
					'rubberBand'    => __( 'rubberBand (Pro)', 'easy-accordion-free' ),
					'shake'         => __( 'shake (Pro)', 'easy-accordion-free' ),
					'swing'         => __( 'swing (Pro)', 'easy-accordion-free' ),
					'rollIn'        => __( 'rollIn (Pro)', 'easy-accordion-free' ),
				),
				'default'  => 'normal',
			),
			array(
				'id'       => 'eap_animation_time',
				'type'     => 'spinner',
				'title'    => __( 'Transition Time', 'easy-accordion-free' ),
				'subtitle' => __( 'Set accordion expand and collapse transition time. Default value is 500 milliseconds.', 'easy-accordion-free' ),
				'unit'     => 'ms',
				'min'      => 0,
				'max'      => 99999,
				'default'  => 300,
				'sanitize' => 'eapro_sanitize_number_field',
			),
			array(
				'id'       => 'eap_accordion_uniq_id',
				'class'    => 'eap_accordion_wrapper_uniq_attribute',
				'type'     => 'text',
				'sanitize' => 'sanitize_text_field',
				'title'    => '',
				'default'  => 'sp_easy_accordion-' . time() . '',
			),
			array(
				'type'    => 'subheading',
				'content' => __( 'Ajax Pagination', 'easy-accordion-free' ),
			),
			array(
				'id'         => 'show_pagination',
				'type'       => 'switcher',
				'class'      => 'only-for-pro-switcher',
				'title'      => __( 'Ajax Pagination', 'easy-accordion-free' ),
				'subtitle'   => __( 'Enabled/Disabled accordion item pagination.', 'easy-accordion-free' ),
				'text_on'    => __( 'Enabled', 'easy-accordion-free' ),
				'text_off'   => __( 'Disabled', 'easy-accordion-free' ),
				'default'    => true,
				'only_pro'   => true,
				'sanitize'   => 'rest_sanitize_boolean',
				'text_width' => 94,
				'title_info' => '<div class="ea-img-tag"><img src="' . esc_url( SP_EA_URL ) . 'admin/img/ea-ajax-pagination.svg" alt="Ajax Pagination"></div><div class="ea-info-label img">' . __( 'Ajax Pagination', 'easy-accordion-free' ) . '</div><div class="info-button img"><a class="ea-open-live-demo" href="https://easyaccordion.io/ajax-paginations/" target="_blank">Live Demo</a></div>',
			),
			array(
				'id'         => 'pagination_type',
				'type'       => 'radio',
				'title'      => __( 'Ajax Pagination Type', 'easy-accordion-free' ),
				'subtitle'   => __( 'Choose an accordion item pagination type.', 'easy-accordion-free' ),
				'class'      => 'only-for-pro',
				'options'    => array(
					'ajax_load_more'     => __( 'Ajax Load More', 'easy-accordion-free' ),
					'ajax_infinite_scrl' => __( 'Ajax Infinite Scroll', 'easy-accordion-free' ),
					'ajax_number'        => __( 'Ajax Number', 'easy-accordion-free' ),
				),
				'default'    => 'ajax_load_more',
				'dependency' => array( 'show_pagination', '==', 'true' ),
			),
			array(
				'id'         => 'load_more_label',
				'type'       => 'text',
				'title'      => __( 'Load More Label', 'easy-accordion-free' ),
				'default'    => __( 'Load More', 'easy-accordion-free' ),
				'subtitle'   => __( 'Change load more label text.', 'easy-accordion-free' ),
				'class'      => 'only-for-pro',
				'sanitize'   => 'sanitize_text_field',
				'only_pro'   => true,
				'dependency' => array( 'show_pagination|pagination_type', '==|==', 'true|ajax_load_more' ),
			),
			array(
				'id'         => 'pagination_show_per_page',
				'type'       => 'spinner',
				'title'      => __( 'Accordion Items Per Page', 'easy-accordion-free' ),
				'subtitle'   => __( 'Set number of accordion items to show per page/click.', 'easy-accordion-free' ),
				'class'      => 'only-for-pro',
				'sanitize'   => 'eapro_sanitize_number_field',
				'default'    => 8,
				'dependency' => array( 'show_pagination|pagination_type', '==|any', 'true|ajax_number,ajax_load_more,ajax_infinite_scrl' ),
			),
			array(
				'id'         => 'pagination_color',
				'class'      => 'pagination_color',
				'type'       => 'color_group',
				'title'      => __( 'Color', 'easy-accordion-free' ),
				'subtitle'   => __( 'Set Pagination color.', 'easy-accordion-free' ),
				'class'      => 'only-for-pro',
				'sanitize'   => 'eapro_sanitize_color_group_field',
				'options'    => array(
					'text_color'        => __( 'Text Color', 'easy-accordion-free' ),
					'text_active_clr'   => __( 'Text Active Color', 'easy-accordion-free' ),
					'border_color'      => __( 'Border Color', 'easy-accordion-free' ),
					'border_active_clr' => __( 'Border Active Color', 'easy-accordion-free' ),
					'background'        => __( 'Background', 'easy-accordion-free' ),
					'active_background' => __( 'Active Background', 'easy-accordion-free' ),
				),
				'default'    => array(
					'text_color'        => '#5e5e5e',
					'text_active_clr'   => '#ffffff',
					'border_color'      => '#bbbbbb',
					'border_active_clr' => '#FE7C4D',
					'background'        => '#ffffff',
					'active_background' => '#FE7C4D',
				),
				'dependency' => array( 'show_pagination', '==', 'true' ),
			),
		),
	)
); // Accordion settings section end.

//
// Typography section begin.
//
SP_EAP::createSection(
	$eap_accordion_shortcode_settings,
	array(
		'title'           => __( 'Typography', 'easy-accordion-free' ),
		'icon'            => 'fa fa-font',
		'enqueue_webfont' => true,
		'fields'          => array(
			array(
				'type'    => 'notice',
				'content' => __( 'Want to customize everything (colors and typography) easily? <a href="https://easyaccordion.io/pricing/?ref=1" target="_blank"> <b>Upgrade to Pro!</b></a> P.S. Note: The <b>color</b> and <b>margin</b> fields work in the lite version.', 'easy-accordion-free' ),
			),
			array(
				'id'         => 'section_title_font_load',
				'type'       => 'switcherf',
				'title'      => __( 'Load Accordion Section Title Font', 'easy-accordion-free' ),
				'subtitle'   => __( 'On/Off google font for the section title.', 'easy-accordion-free' ),
				'default'    => false,
				'sanitize'   => 'rest_sanitize_boolean',
				'dependency' => array(
					'section_title',
					'==',
					'true',
					true,
				),
			),
			array(
				'id'            => 'eap_section_title_typography',
				'type'          => 'typography',
				'title'         => __( 'Accordion Section Title Font', 'easy-accordion-free' ),
				'subtitle'      => __( 'Set Accordion section title font properties.', 'easy-accordion-free' ),
				'default'       => array(
					'font-family'    => 'Open Sans',
					'font-style'     => '600',
					'font-size'      => '28',
					'line-height'    => '32',
					'letter-spacing' => '0',
					'text-align'     => 'left',
					'text-transform' => 'none',
					'type'           => 'google',
					'unit'           => 'px',
					'color'          => '#444',
					'margin-bottom'  => '30',
				),
				'preview'       => 'always',
				'margin_bottom' => true,
				'dependency'    => array(
					'section_title',
					'==',
					'true',
					true,
				),
				'preview_text'  => 'Accordion Section Title',
			),
			array(
				'id'       => 'eap_title_font_load',
				'type'     => 'switcherf',
				'title'    => __( 'Load Accordion Item Title Font', 'easy-accordion-free' ),
				'subtitle' => __( 'On/Off google font for the accordion item title.', 'easy-accordion-free' ),
				'default'  => false,
			),
			array(
				'id'           => 'eap_title_typography',
				'type'         => 'typography',
				'title'        => __( 'Item Title Font', 'easy-accordion-free' ),
				'subtitle'     => __( 'Set accordion item title font properties.', 'easy-accordion-free' ),
				'default'      => array(
					'font-family'    => 'Open Sans',
					'font-style'     => '600',
					'font-size'      => '20',
					'line-height'    => '30',
					'letter-spacing' => '0',
					'color'          => '#444',
					'active_color'   => '#444',
					'hover_color'    => '#444',
					'text-align'     => 'left',
					'text-transform' => 'none',
					'type'           => 'google',
				),
				'preview_text' => 'Accordion Item Title',
				'preview'      => 'always',
				'color'        => true,
			),
			array(
				'id'       => 'eap_desc_font_load',
				'type'     => 'switcherf',
				'title'    => __( 'Load Accordion Item Description Font', 'easy-accordion-free' ),
				'subtitle' => __( 'On/Off google font for the accordion item description.', 'easy-accordion-free' ),
				'default'  => false,
			),
			array(
				'id'           => 'eap_content_typography',
				'type'         => 'typography',
				'title'        => __( 'Description Font', 'easy-accordion-free' ),
				'subtitle'     => __( 'Set accordion item description font properties.', 'easy-accordion-free' ),
				'default'      => array(
					'color'          => '#444',
					'font-family'    => 'Open Sans',
					'font-style'     => '400',
					'font-size'      => '16',
					'line-height'    => '26',
					'letter-spacing' => '0',
					'text-align'     => 'left',
					'text-transform' => 'none',
					'type'           => 'google',
				),
				'preview'      => 'always',
				'preview_text' => 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Vel voluptatum, earum quibusdam quaerat cum quidem Culpa nam placeat iste laudantium illum, in aperiam deserunt ullam cumque libero. Vero, aut pariatur amet consectetur adipisicing elit. Facilis, tempora, quasi repellat reiciendis praesentium accusantium perspiciatis vero vitae numquam blanditiis nisi accusamus saepe eius.',
			),
		), // End of fields array.
	)
); // Style settings section end.
