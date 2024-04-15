<?php
/**
 * Dynamic style.
 *
 * @link       https://shapedplugin.com/
 * @since      2.0.0
 *
 * @package    easy-accordion-free
 * @subpackage easy-accordion-free/public
 */

$eap_title_font_load   = isset( $shortcode_data['eap_title_font_load'] ) ? $shortcode_data['eap_title_font_load'] : '';
$eap_desc_font_load    = isset( $shortcode_data['eap_desc_font_load'] ) ? $shortcode_data['eap_desc_font_load'] : '';
$eap_preloader         = isset( $shortcode_data['eap_preloader'] ) ? $shortcode_data['eap_preloader'] : false;
$eap_animation_time    = isset( $shortcode_data['eap_animation_time'] ) ? $shortcode_data['eap_animation_time'] : '';
$accordion_layout      = isset( $shortcode_data['eap_accordion_layout'] ) ? $shortcode_data['eap_accordion_layout'] : 'vertical';
$accordion_theme_class = 'sp-ea-one';
$acc_bottom_margin     = isset( $shortcode_data['accordion_margin_bottom']['all'] ) ? $shortcode_data['accordion_margin_bottom']['all'] : '10';

// Accordion title.
$eap_title_typho       = isset( $shortcode_data['eap_title_typography'] ) ? $shortcode_data['eap_title_typography'] : '';
$eap_title_typho_color = isset( $eap_title_typho['color'] ) ? $eap_title_typho['color'] : '#444';
// Section title.
$acc_section_title         = isset( $shortcode_data['section_title'] ) ? $shortcode_data['section_title'] : '';
$section_title_typho       = isset( $shortcode_data['eap_section_title_typography'] ) ? $shortcode_data['eap_section_title_typography'] : '';
$section_title_typho_color = isset( $section_title_typho['color'] ) ? $section_title_typho['color'] : '#444';
// $old_section_title_margin_bottom = isset( $shortcode_data['section_title_margin_bottom'] ) ? $shortcode_data['section_title_margin_bottom'] : '30';
// $acc_section_title_margin_bottom = isset( $shortcode_data['section_title_margin_bottom']['all'] ) ? $shortcode_data['section_title_margin_bottom']['all'] : $old_section_title_margin_bottom;
$acc_section_title_margin_bottom = isset( $section_title_typho['margin-bottom'] ) ? $section_title_typho['margin-bottom'] : '30';

$eap_description_bg                 = isset( $shortcode_data['eap_description_bg_color'] ) ? $shortcode_data['eap_description_bg_color'] : '';
$eap_content_typo                   = isset( $shortcode_data['eap_content_typography'] ) ? $shortcode_data['eap_content_typography'] : '';
$eap_content_typo_color             = isset( $eap_content_typo['color'] ) ? $eap_content_typo['color'] : '#444';
$eap_accordion_fillspace            = isset( $shortcode_data['eap_accordion_fillspace'] ) ? $shortcode_data['eap_accordion_fillspace'] : '';
$old_eap_accordion_fillspace_height = isset( $shortcode_data['eap_accordion_fillspace_height'] ) ? $shortcode_data['eap_accordion_fillspace_height'] : '200';
$eap_accordion_fillspace_height     = isset( $shortcode_data['eap_accordion_fillspace_height']['all'] ) ? $shortcode_data['eap_accordion_fillspace_height']['all'] : $old_eap_accordion_fillspace_height;
$eap_ex_icon_position               = isset( $shortcode_data['eap_icon_position'] ) ? $shortcode_data['eap_icon_position'] : '';
$eap_icon_color                     = isset( $shortcode_data['eap_icon_color_set'] ) && is_string( $shortcode_data['eap_icon_color_set'] ) ? $shortcode_data['eap_icon_color_set'] : '#444';
$old_eap_icon_size                  = isset( $shortcode_data['eap_icon_size'] ) ? $shortcode_data['eap_icon_size'] : '16';
$eap_icon_size                      = isset( $shortcode_data['eap_icon_size']['all'] ) ? $shortcode_data['eap_icon_size']['all'] : $old_eap_icon_size;
$eap_header_bg                      = isset( $shortcode_data['eap_header_bg_color'] ) && is_string( $shortcode_data['eap_header_bg_color'] ) ? $shortcode_data['eap_header_bg_color'] : '#eee';
$eap_border                         = isset( $shortcode_data['eap_border_css'] ) ? $shortcode_data['eap_border_css'] : '';
$old_eap_border_width               = isset( $eap_border['width'] ) ? $eap_border['width'] : '1';
$eap_border_width                   = isset( $eap_border['all'] ) ? $eap_border['all'] : $old_eap_border_width;
$eap_border_style                   = isset( $eap_border['style'] ) ? $eap_border['style'] : '';
$eap_border_color                   = isset( $eap_border['color'] ) ? $eap_border['color'] : '';

$ea_dynamic_css .= '#sp-ea-' . $accordion_id . ' .spcollapsing { height: 0; overflow: hidden; transition-property: height;transition-duration: ' . $eap_animation_time . 'ms;}';
if ( $eap_preloader ) {
	$ea_dynamic_css .= '#sp-ea-' . $accordion_id . '{ position: relative; }#sp-ea-' . $accordion_id . ' .ea-card{ opacity: 0;}#eap-preloader-' . $accordion_id . '{ position: absolute; left: 0; top: 0; height: 100%;width: 100%; text-align: center;display: flex; align-items: center;justify-content: center;}';
}
if ( $acc_section_title ) {
	$ea_dynamic_css .= '.sp-easy-accordion-enabled .eap_section_title_' . $accordion_id . ', body .eap_section_title_' . $accordion_id . ' { color: ' . $section_title_typho_color . '; margin-bottom:  ' . $acc_section_title_margin_bottom . 'px; }';
}
if ( 'vertical' === $accordion_layout ) {
	$ea_dynamic_css .= '#sp-ea-' . $accordion_id . '.sp-easy-accordion>.sp-ea-single {margin-bottom: ' . $acc_bottom_margin . 'px; border: ' . $eap_border_width . 'px ' . $eap_border_style . ' ' . $eap_border_color . '; }#sp-ea-' . $accordion_id . '.sp-easy-accordion>.sp-ea-single>.ea-header a {color: ' . $eap_title_typho_color . ';}#sp-ea-' . $accordion_id . '.sp-easy-accordion>.sp-ea-single>.sp-collapse>.ea-body {background: ' . $eap_description_bg . '; color: ' . $eap_content_typo_color . ';}';
	if ( $eap_accordion_fillspace ) {
		$ea_dynamic_css .= '#sp-ea-' . $accordion_id . '.sp-easy-accordion>.sp-ea-single>.sp-collapse>.ea-body {display: block;height: ' . $eap_accordion_fillspace_height . 'px; overflow: auto;}';
	}
	if ( 'sp-ea-one' === $accordion_theme_class ) {
		$ea_dynamic_css .= '#sp-ea-' . $accordion_id . '.sp-easy-accordion>.sp-ea-single {background: ' . $eap_header_bg . ';}#sp-ea-' . $accordion_id . '.sp-easy-accordion>.sp-ea-single>.ea-header a .ea-expand-icon { float: ' . $eap_ex_icon_position . '; color: ' . $eap_icon_color . ';font-size: ' . $eap_icon_size . 'px;}';
		if ( 'right' === $eap_ex_icon_position ) {
			$ea_dynamic_css .= '#sp-ea-' . $accordion_id . '.sp-easy-accordion>.sp-ea-single>.ea-header a .ea-expand-icon {margin-right: 0;}';
		}
	}
}
