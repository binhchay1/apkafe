<?php
require_once BOOMDEVS_TOC_PATH . 'includes/class-boomdevs-toc-utils.php';
/**
 * The Setting plugin class.
 *
 * @since      1.0.0
 * @package    Boomdevs_Toc
 * @subpackage Boomdevs_Toc_Settings/includes
 * @author     BoomDevs <admin@boomdevs.com>
 */
class Boomdevs_Toc_Settings {
    public static $plugin_name = BOOMDEVS_TOC_NAME,
    $plugin_file_url           = BOOMDEVS_TOC_URL;

    public function __construct() {
        add_filter( 'boomdevs_toc_register_options_panel', array( $this, 'register_options_panel' ), 1, 2 );
    }

    public function register_options_panel( $options_panel_func, $options_panel_config ) {
        return array(
            'func'   => $options_panel_func,
            'config' => $options_panel_config,
        );
    }

    public function generate_settings() {
        // Set a unique slug-like ID
        $prefix = Boomdevs_Toc_Settings::$plugin_name;

        // Plugin options panel configuration
        $options_panel_func   = 'createOptions';
        $options_panel_config = array(
            'framework_title' => __( 'TOP Table Of Contents Settings', 'boomdevs-toc' ),
            'footer_text'     => sprintf(
                __( 'Visit our plugin usage <a href="%s">documentation</a>', 'boomdevs-toc' ),
                esc_url( 'https://boomdevs.com/docs/top-table-of-contents/' )
            ),
            'footer_credit'   => sprintf(
                __( 'A proud creation of <a href="%s">BoomDevs</a>', 'boomdevs-toc' ),
                esc_url( 'https://boomdevs.com/' )
            ),
            'menu_title'      => __( 'TOP Table Of Contents', 'boomdevs-toc' ),
            'menu_slug'       => 'boomdevs-toc-settings',
            'database'        => 'option',
            'transport'       => 'refresh',
            'capability'      => 'manage_options',
            'save_defaults'   => true,
            'enqueue_webfont' => true,
            'async_webfont'   => true,
            'output_css'      => true,
        );

        $options_panel_builder = apply_filters( 'boomdevs_toc_register_options_panel', $options_panel_func, $options_panel_config );

        CSF::{$options_panel_builder['func']}

        ( $prefix, $options_panel_builder['config'] );

        $parent = '';

        if ( $options_panel_builder['func'] == 'createCustomizeOptions' ) {
            // Add to level section if in customizer mode
            CSF::createSection( $prefix, array(
                'id'    => $prefix . '_boomdevs_toc',
                'title' => __( 'TOP Table Of Contents', 'boomdevs-toc' ),
            ) );

            $parent = $prefix . '_boomdevs_toc';
        }

        // Data insert section
        CSF::createSection( $prefix, array(
            'parent' => $parent,
            'title'  => __( 'Auto insert', 'boomdevs-toc' ),
            'fields' => array(
                array(
                    'type'    => 'heading',
                    'content' => __( 'Post types', 'boomdevs-toc' ),
                ),
                array(
                    'id'         => 'select_post_type',
                    'type'       => 'checkbox',
                    'title'      => __( 'Select post types', 'boomdevs-toc' ),
                    'subtitle'   => __( 'Select the post types which will have the table of contents automatically inserted.', 'boomdevs-toc' ),
                    'options'    => 'post_types',
                    'class'      => 'select_post',
                    'query_args' => array(
                        'orderby' => 'post_title',
                        'order'   => 'ASC',
                    ),
                    'default'    => array( '' ),
                ),
                array(
                    'id'         => 'exclude_post_type',
                    'type'       => 'checkbox',
                    'title'      => __( 'Disable Automatic Heading Anchors', 'boomdevs-toc' ),
                    'subtitle'   => __( 'Allows users to turn off the automatic generation of anchor tags for specific post types.', 'boomdevs-toc' ),
                    'options'    => 'post_types',
                    'class'      => 'select_post',
                    'query_args' => array(
                        'orderby' => 'post_title',
                        'order'   => 'ASC',
                    ),
                    'default'    => array( '' ),
                ),

                array(
                    'id'       => 'select_toc_position',
                    'type'     => 'select',
                    'title'    => __( 'Select TOC position', 'boomdevs-toc' ),
                    'subtitle' => __( 'Select the position where the table of contents will be inserted.', 'boomdevs-toc' ),
                    'options'  => array(
                        'before'    => __( 'Before first heading (default)', 'boomdevs-toc' ),
                        'after'     => __( 'After first heading', 'boomdevs-toc' ),
                        'afterpara' => __( 'After first paragraph', 'boomdevs-toc' ),
                        'top'       => __( 'Top', 'boomdevs-toc' ),
                        'bottom'    => __( 'Bottom', 'boomdevs-toc' ),
                    ),
                    'default'  => 'before',
                ),

            ),
        ) );

        //General section

        $default_generator_settings = array(
            array(

                'id'         => 'number_of_headings',
                'type'       => 'number',
                'title'      => __( 'Show when headings are more than', 'boomdevs-toc' ),
                'desc'       => $this->get_premium_alert_message(),
                'default'    => '0',
                'attributes' => array(
                    'disabled' => true,
                ),
            ),
        );

        $generator_settings = apply_filters( 'boomdevs_toc_register_generator_settings', $default_generator_settings );

        if ( ! $generator_settings ) {
            $generator_settings = $default_generator_settings;
        }

        CSF::createSection( $prefix, array(
            'parent' => $parent,
            'title'  => __( 'General', 'boomdevs-toc' ),
            'fields' => array(
                array(
                    'type'    => 'heading',
                    'content' => __( 'Layout', 'boomdevs-toc' ),
                ),
                array(
                    'id'      => 'initial_view',
                    'type'    => 'switcher',
                    'title'   => __( 'Content Visibility', 'boomdevs-toc' ),
                    'desc'    => __( 'Display items of content ( Unfolded view )', 'boomdevs-toc' ),
                    'default' => true,
                ),

                array(
                    'id'               => 'width_height',
                    'type'             => 'dimensions',
                    'title'            => __( 'Width', 'boomdevs-toc' ),
                    'height'           => false,
                    'output'           => '.bd_toc_container',
                    'output_important' => true,
                    'default'          => array(
                        'width' => '100',
                        'unit'  => '%',
                    ),
                ),
                array(
                    'id'               => 'layout_padding',
                    'type'             => 'spacing',
                    'title'            => __( 'Padding', 'boomdevs-toc' ),
                    'output'           => '.bd_toc_wrapper',
                    'output_mode'      => 'padding',
                    'output_important' => true,
                    'default'          => array(
                        'top'    => '12',
                        'right'  => '12',
                        'bottom' => '12',
                        'left'   => '12',
                        'unit'   => 'px',
                    ),
                ),
                array(
                    'id'               => 'layout_margin',
                    'type'             => 'spacing',
                    'title'            => __( 'Margin', 'boomdevs-toc' ),
                    'output'           => '.bd_toc_container',
                    'output_mode'      => 'margin',
                    'output_important' => true,
                    'default'          => array(
                        'top'    => '0',
                        'right'  => '',
                        'bottom' => '30',
                        'left'   => '',
                        'unit'   => 'px',
                    ),
                ),
                array(
                    'id'                    => 'bg_color',
                    'type'                  => 'background',
                    'background_gradient'   => false,
                    'background_color'      => true,
                    'background_position'   => false,
                    'background_image'      => false,
                    'background_attachment' => false,
                    'background_size'       => false,
                    'background_repeat'     => false,
                    'title'                 => __( 'Background color', 'boomdevs-toc' ),
                    'output'                => '.bd_toc_container',
                    'output_important'      => true,
                    'default'               => array(
                        'background-color' => '#f9f9f9',
                    ),
                ),
                array(
                    'id'               => 'layout_border',
                    'type'             => 'border',
                    'title'            => __( 'Border', 'boomdevs-toc' ),
                    'output'           => '.bd_toc_container',
                    'output_important' => true,
                    'default'          => array(
                        'style'  => 'solid',
                        'color'  => '#aaaaaa',
                        'top'    => '1',
                        'right'  => '1',
                        'bottom' => '1',
                        'left'   => '1',
                        'unit'   => 'px',
                    ),
                ),
                array(
                    'type'    => 'heading',
                    'content' => __( 'Border radius', 'boomdevs-toc' ),
                ),
                array(
                    'id'      => 'layout_border_radius_top',
                    'type'    => 'number',
                    'unit'    => 'px',
                    'default' => '4',
                    'title'   => __( 'Top', 'boomdevs-toc' ),
                ),
                array(
                    'id'      => 'layout_border_radius_right',
                    'type'    => 'number',
                    'unit'    => 'px',
                    'default' => '4',
                    'title'   => __( 'Right', 'boomdevs-toc' ),
                ),
                array(
                    'id'      => 'layout_border_radius_bottom',
                    'type'    => 'number',
                    'unit'    => 'px',
                    'default' => '4',
                    'title'   => __( 'Bottom', 'boomdevs-toc' ),
                ),
                array(
                    'id'      => 'layout_border_radius_left',
                    'type'    => 'number',
                    'unit'    => 'px',
                    'default' => '4',
                    'title'   => __( 'Left', 'boomdevs-toc' ),
                ),
                array(
                    'type'    => 'heading',
                    'content' => __( 'Box shadow', 'boomdevs-toc' ),
                ),
                array(
                    'id'      => 'horizontal_length',
                    'type'    => 'number',
                    'unit'    => 'px',
                    'default' => 0,
                    'title'   => __( 'Horizontal Length', 'boomdevs-toc' ),
                ),
                array(
                    'id'      => 'vertical_length',
                    'type'    => 'number',
                    'unit'    => 'px',
                    'default' => 4,
                    'title'   => __( 'Vertical Length', 'boomdevs-toc' ),
                ),
                array(
                    'id'      => 'blur_radius',
                    'type'    => 'number',
                    'unit'    => 'px',
                    'default' => 16,
                    'title'   => __( 'Blur radius', 'boomdevs-toc' ),
                ),
                array(
                    'id'      => 'spread_radius',
                    'type'    => 'number',
                    'unit'    => 'px',
                    'default' => 0,
                    'title'   => __( 'Spread radius', 'boomdevs-toc' ),
                ),
                array(
                    'id'      => 'shadow_color',
                    'type'    => 'color',
                    'title'   => 'Shadow Color',
                    'default' => 'rgba(0, 0, 0, 0.03)',
                ),
                array(
                    'type'    => 'heading',
                    'content' => __( 'Generator configuration', 'boomdevs-toc' ),
                ),
                ...$generator_settings,
                array(
                    'id'      => 'heading_top_level',
                    'type'    => 'select',
                    'title'   => __( 'Top level heading', 'boomdevs-toc' ),
                    'options' => array(
                        '1' => 'H1',
                        '2' => 'H2',
                        '3' => 'H3',
                        '4' => 'H4',
                        '5' => 'H5',
                        '6' => 'H6',
                    ),
                    'default' => '1',
                ),
                array(
                    'id'      => 'title_depth',
                    'type'    => 'select',
                    'title'   => __( 'Heading depth', 'boomdevs-toc' ),
                    'desc'    => __( 'Upper Maximum depth of title tags to be displayed', 'boomdevs-toc' ),
                    'options' => array(
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                        '6' => '6',
                    ),
                    'default' => '5',
                ),
                array(
                    'id'       => 'title_hide',
                    'type'     => 'select',
                    'title'    => __( 'Exclude heading', 'boomdevs-toc' ),
                    'chosen'   => true,
                    'multiple' => true,
                    'desc'     => __( 'Select Tag', 'boomdevs-toc' ),
                    'options'  => array(
                        '1' => 'H1',
                        '2' => 'H2',
                        '3' => 'H3',
                        '4' => 'H4',
                        '5' => 'H5',
                        '6' => 'H6',
                    ),
                    'default'  => array( '6' ),
                ),
            ),
        ) );

        // Title section
        CSF::createSection( $prefix, array(
            'parent' => $parent,
            'title'  => __( 'Title', 'boomdevs-toc' ),
            'fields' => array(
                array(
                    'id'      => 'title_show_hide_switcher',
                    'type'    => 'switcher',
                    'title'   => __( 'Show title', 'boomdevs-toc' ),
                    'default' => true,
                ),
                array(
                    'id'         => 'title',
                    'type'       => 'text',
                    'title'      => __( 'Title', 'boomdevs-toc' ),
                    'dependency' => array( 'title_show_hide_switcher', '==', 'true' ),
                    'default'    => __( 'Table of Contents', 'boomdevs-toc' ),
                ),
                array(
                    'id'               => 'title_font_family',
                    'title'            => 'Typography',
                    'type'             => 'typography',
                    'output'           => '.bd_toc_header_title',
                    'output_important' => true,
                    'font_family'      => true,
                    'font_weight'      => true,
                    'subset'           => true,
                    'font_style'       => true,
                    'font_size'        => true,
                    'line_height'      => true,
                    'letter_spacing'   => true,
                    'text_align'       => true,
                    'text_transform'   => true,
                    'color'            => false,
                    'default'          => array(
                        'font-family' => '',
                        'font-size'   => '18',
                        'font-weight' => '500',
                        'unit'        => 'px',
                        'type'        => 'google',
                    ),
                    'dependency'       => array( 'title_show_hide_switcher', '==', 'true' ),
                ),
                array(
                    'id'                    => 'title_bg_color',
                    'type'                  => 'background',
                    'background_gradient'   => false,
                    'background_color'      => true,
                    'background_position'   => false,
                    'background_image'      => false,
                    'background_attachment' => false,
                    'background_size'       => false,
                    'background_repeat'     => false,
                    'title'                 => __( 'Background color', 'boomdevs-toc' ),
                    'output'                => '.bd_toc_header',
                    'output_important'      => true,
                    'dependency'            => array( 'title_show_hide_switcher', '==', 'true' ),
                ),
                array(
                    'id'               => 'title_color',
                    'type'             => 'color',
                    'title'            => __( 'Color', 'boomdevs-toc' ),
                    'output'           => '.bd_toc_wrapper .bd_toc_header .bd_toc_header_title',
                    'default'          => '#2c2f32',
                    'output_important' => true,
                    'dependency'       => array( 'title_show_hide_switcher', '==', 'true' ),
                ),
                array(
                    'id'               => 'title_hover_color',
                    'type'             => 'color',
                    'title'            => __( 'Hover color', 'boomdevs-toc' ),
                    'output'           => '.bd_toc_wrapper .bd_toc_header:hover .bd_toc_header_title',
                    'default'          => '#2c2f32',
                    'output_important' => true,
                    'dependency'       => array( 'title_show_hide_switcher', '==', 'true' ),
                ),
                array(
                    'id'               => 'title_padding',
                    'type'             => 'spacing',
                    'title'            => __( 'Padding', 'boomdevs-toc' ),
                    'output'           => '.bd_toc_header',
                    'output_mode'      => 'padding',
                    'output_important' => true,
                    'default'          => array(
                        'top'    => '0',
                        'right'  => '2',
                        'bottom' => '0',
                        'left'   => '0',
                        'unit'   => 'px',
                    ),
                    'dependency'       => array( 'title_show_hide_switcher', '==', 'true' ),
                ),
                array(
                    'id'               => 'title_margin',
                    'type'             => 'spacing',
                    'title'            => __( 'Margin', 'boomdevs-toc' ),
                    'output'           => '.bd_toc_header.active',
                    'output_mode'      => 'margin',
                    'output_important' => true,
                    'default'          => array(
                        'top'    => '0',
                        'right'  => '0',
                        'bottom' => '0',
                        'left'   => '0',
                        'unit'   => 'px',
                    ),
                    'dependency'       => array( 'title_show_hide_switcher', '==', 'true' ),
                ),
                array(
                    'type'       => 'heading',
                    'content'    => __( 'Border radius', 'boomdevs-toc' ),
                    'dependency' => array( 'title_show_hide_switcher', '==', 'true' ),
                ),
                array(
                    'id'         => 'title_border_radius_top',
                    'type'       => 'number',
                    'unit'       => 'px',
                    'title'      => __( 'Top', 'boomdevs-toc' ),
                    'default'    => 10,
                    'dependency' => array( 'title_show_hide_switcher', '==', 'true' ),
                ),
                array(
                    'id'         => 'title_border_radius_right',
                    'type'       => 'number',
                    'unit'       => 'px',
                    'title'      => __( 'Right', 'boomdevs-toc' ),
                    'default'    => 10,
                    'dependency' => array( 'title_show_hide_switcher', '==', 'true' ),
                ),
                array(
                    'id'         => 'title_border_radius_bottom',
                    'type'       => 'number',
                    'unit'       => 'px',
                    'title'      => __( 'Bottom', 'boomdevs-toc' ),
                    'default'    => 10,
                    'dependency' => array( 'title_show_hide_switcher', '==', 'true' ),
                ),
                array(
                    'id'         => 'title_border_radius_left',
                    'type'       => 'number',
                    'unit'       => 'px',
                    'title'      => __( 'Left', 'boomdevs-toc' ),
                    'default'    => 10,
                    'dependency' => array( 'title_show_hide_switcher', '==', 'true' ),
                ),
                array(
                    'type'       => 'heading',
                    'content'    => __( 'Toggle icon', 'boomdevs-toc' ),
                    'dependency' => array( 'title_show_hide_switcher', '==', 'true' ),
                ),
                array(
                    'id'         => 'icon_show_hide_switcher',
                    'type'       => 'switcher',
                    'title'      => __( 'Show toggle icon', 'boomdevs-toc' ),
                    'default'    => true,
                    'dependency' => array( 'title_show_hide_switcher', '==', 'true' ),
                ),
                array(
                    'id'         => 'toggle_icon_color',
                    'type'       => 'color',
                    'default'    => '#2c2f32',
                    'title'      => __( 'Color', 'boomdevs-toc' ),
                    'dependency' => array(
                        array( 'icon_show_hide_switcher', '==', 'true' ),
                        array( 'title_show_hide_switcher', '==', 'true' ),
                    ),
                ),
                array(
                    'id'         => 'toggle_icon_hover_color',
                    'type'       => 'color',
                    'default'    => '#2c2f32',
                    'title'      => __( 'Hover color', 'boomdevs-toc' ),
                    'dependency' => array(
                        array( 'icon_show_hide_switcher', '==', 'true' ),
                        array( 'title_show_hide_switcher', '==', 'true' ),
                    ),
                ),
            ),
        ) );

        // Heading section

        $headings_toggle_icon_settings = apply_filters( 'boomdevs_toc_register_heading_toggle_icon_settings', array() );
        $bd_toc_layout_toggle          = apply_filters( 'boomdevs_toc_get_bd_toc_layout_toggle_settings', array() );

        if ( ! $headings_toggle_icon_settings ) {
            $headings_toggle_icon_settings = array(
                array(
                    'type'    => 'subheading',
                    'content' => $this->get_premium_alert_message(),
                ),
            );
        }

        if ( ! $bd_toc_layout_toggle ) {
            $bd_toc_layout_toggle = array(
                array(
                    'type'    => 'subheading',
                    'content' => $this->get_premium_alert_message(),
                ),

            );
        }

        CSF::createSection( $prefix, array(
            'parent' => $parent,
            'title'  => __( 'Headings', 'boomdevs-toc' ),
            'fields' => array(
                ...$bd_toc_layout_toggle,
                array(
                    'id'      => 'heading_list_type',
                    'type'    => 'select',
                    'title'   => __( 'List style', 'boomdevs-toc' ),
                    'options' => array(
                        'disc'   => 'Disc',
                        'number' => 'Numeric',
                        'none'   => 'None',
                    ),
                    'default' => 'number',
                ),
                array(
                    'id'      => 'heading_word_break',
                    'type'    => 'select',
                    'title'   => __( 'Word Break', 'boomdevs-toc' ),
                    'output'  => '.bd_toc_container .bd_toc_wrapper .bd_toc_content_list_item ul li a',
                    'options' => array(
                        'word_break' => __( 'Word Break', 'boomdevs-toc' ),
                        'truncate'   => __( 'Truncate ', 'boomdevs-toc' ),
                    ),
                    'default' => 'truncate',
                ),
                array(
                    'id'                => 'offset_from_top',
                    'type'              => 'number',
                    'unit'              => 'px',
                    'title'             => __( 'Offset from top', 'boomdevs-toc' ),
                    'default'           => 0,
                ),
                array(
                    'id'               => 'heading_font_family',
                    'type'             => 'typography',
                    'title'            => __( 'Typography', 'boomdevs-toc' ),
                    'output'           => '.bd_toc_content_list .bd_toc_content_list_item ul > li > a',
                    'output_important' => true,
                    'font_family'      => true,
                    'font_weight'      => true,
                    'subset'           => true,
                    'font_style'       => true,
                    'font_size'        => true,
                    'line_height'      => true,
                    'letter_spacing'   => true,
                    'text_align'       => true,
                    'text_transform'   => true,
                    'color'            => false,
                    'default'          => array(
                        'font-family' => '',
                        'font-size'   => '14',
                        'font-weight' => '400',
                        'unit'        => 'px',
                        'type'        => 'google',
                    ),
                ),
                array(
                    'id'               => 'heading_padding',
                    'type'             => 'spacing',
                    'title'            => __( 'Padding', 'boomdevs-toc' ),
                    'output'           => '.bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list_item ul li a',
                    'output_mode'      => 'padding',
                    'output_important' => true,
                    'default'          => array(
                        'top'    => '0',
                        'right'  => '0',
                        'bottom' => '0',
                        'left'   => '0',
                        'unit'   => 'px',
                    ),
                ),
                array(
                    'id'               => 'heading_margin',
                    'type'             => 'spacing',
                    'title'            => __( 'Margin', 'boomdevs-toc' ),
                    'output'           => '.bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list_item ul li a',
                    'output_mode'      => 'margin',
                    'output_important' => true,
                    'default'          => array(
                        'top'    => '0',
                        'right'  => '0',
                        'bottom' => '0',
                        'left'   => '0',
                        'unit'   => 'px',
                    ),
                ),
                array(
                    'id'      => 'heading_border',
                    'type'    => 'border',
                    'title'   => __( 'Border', 'boomdevs-toc' ),
                    'output'  => '.bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list ul li a',
                    'default' => array(
                        'top'    => '0',
                        'right'  => '0',
                        'bottom' => '0',
                        'left'   => '0',
                        'style'  => 'solid',
                        'color'  => '#ffffff',
                        'unit'   => 'px',
                    ),
                ),
                array(
                    'id'      => 'active_heading_border',
                    'type'    => 'border',
                    'title'   => __( 'Active border', 'boomdevs-toc' ),
                    'unit'    => 'px',
                    'output'  => '.bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list ul li.current > a',
                    'default' => array(
                        'top'    => '0',
                        'right'  => '0',
                        'bottom' => '0',
                        'left'   => '0',
                        'style'  => 'solid',
                        'color'  => '#ffffff',
                        'unit'   => 'px',
                    ),
                ),
                array(
                    'type'    => 'heading',
                    'content' => __( 'Active border radius', 'boomdevs-toc' ),
                ),
                array(
                    'id'      => 'heading_border_radius_top',
                    'type'    => 'number',
                    'unit'    => 'px',
                    'title'   => __( 'Top', 'boomdevs-toc' ),
                    'default' => 10,
                ),
                array(
                    'id'      => 'heading_border_radius_right',
                    'type'    => 'number',
                    'unit'    => 'px',
                    'title'   => __( 'Right', 'boomdevs-toc' ),
                    'default' => 10,
                ),
                array(
                    'id'      => 'heading_border_radius_bottom',
                    'type'    => 'number',
                    'unit'    => 'px',
                    'title'   => __( 'Bottom', 'boomdevs-toc' ),
                    'default' => 10,
                ),
                array(
                    'id'      => 'heading_border_radius_left',
                    'type'    => 'number',
                    'unit'    => 'px',
                    'title'   => __( 'Left', 'boomdevs-toc' ),
                    'default' => 10,
                ),
                array(
                    'type'    => 'heading',
                    'content' => __( 'Color', 'boomdevs-toc' ),
                ),
                array(
                    'id'                    => 'content_bg_color',
                    'type'                  => 'background',
                    'background_gradient'   => false,
                    'background_color'      => true,
                    'background_position'   => false,
                    'background_image'      => false,
                    'background_attachment' => false,
                    'background_size'       => false,
                    'background_repeat'     => false,
                    'title'                 => __( 'Container background color', 'boomdevs-toc' ),
                    'output'                => '.bd_toc_content',
                    'output_important'      => true,
                    'default'               => array(
                        'background-color' => '#f9f9f9',
                    ),
                ),
                array(
                    'id'               => 'heading_background',
                    'type'             => 'color',
                    'title'            => __( 'Background color', 'boomdevs-toc' ),
                    'output'           => '.bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list ul li a',
                    'output_mode'      => 'background-color',
                    'default'          => '#f9f9f9',
                    'output_important' => true,
                ),
                array(
                    'id'               => 'active_heading_background',
                    'type'             => 'color',
                    'title'            => __( 'Active background color', 'boomdevs-toc' ),
                    'output'           => '.bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list ul li.current > a',
                    'output_mode'      => 'background-color',
                    'default'          => '#f7f7f700',
                    'output_important' => true,
                ),
                array(
                    'id'      => 'heading_font_color',
                    'type'    => 'color',
                    'title'   => __( 'Color', 'boomdevs-toc' ),
                    'output'  => '.bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list ul li a, .bd_toc_container .bd_toc_wrapper .bd_toc_content_list_item ul li .collaps-button .toggle-icon',
                    'default' => '#2c2f32',
                ),
                array(
                    'id'      => 'heading_font_hover_color',
                    'type'    => 'color',
                    'title'   => __( 'Hover color', 'boomdevs-toc' ),
                    'output'  => '.bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list ul li a:hover, .bd_toc_container .bd_toc_wrapper .bd_toc_content_list_item ul li .collaps-button .toggle-icon:hover',
                    'default' => '#2c2f32',
                ),
                array(
                    'id'      => 'heading_font_active_color',
                    'type'    => 'color',
                    'title'   => __( 'Active color', 'boomdevs-toc' ),
                    'output'  => array( '.bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list ul li.current > a', '.bd_toc_container .bd_toc_wrapper .bd_toc_content_list_item ul li.current>.collaps-button .toggle-icon' ),
                    'default' => '#2c2f32',
                ),

                array(
                    'type'    => 'heading',
                    'content' => __( 'Toggle icon', 'boomdevs-toc' ),
                ),
                ...$headings_toggle_icon_settings,
            ),
        ) );

        // Sub Heading section
        $sub_headings_toggle_icon_settings = apply_filters( 'boomdevs_toc_register_sub_heading_toggle_icon_settings', array() );
        $sub_heading_settings              = apply_filters( 'boomdevs_toc_register_sub_heading_settings', array() );

        if ( ! $sub_headings_toggle_icon_settings && ! $sub_heading_settings ) {
            $sub_headings_toggle_icon_settings = array(
                array(
                    'type'    => 'subheading',
                    'content' => $this->get_sub_heading_alert_message(),
                ),
            );
        }

        CSF::createSection( $prefix, array(
            'parent' => $parent,
            'title'  => __( 'Sub headings', 'boomdevs-toc' ),
            'fields' => array(
                ...$sub_heading_settings,
                ...$sub_headings_toggle_icon_settings,
            ),
        ) );

        //Sticky sidebar
        $sticky_sidebar_settings = apply_filters( 'boomdevs_toc_register_sticky_sidebar_settings', array() );

        if ( ! $sticky_sidebar_settings ) {
            $sticky_sidebar_settings = array(
                array(
                    'type'    => 'subheading',
                    'content' => $this->get_sticky_sidebar_alert_message(),
                ),
            );
        }

        CSF::createSection( $prefix, array(
            'parent' => $parent,
            'title'  => __( 'Sticky sidebar', 'boomdevs-toc' ),
            'fields' => array(
                ...$sticky_sidebar_settings
            ),
        ) );

        //Floating section
        $widget_floating_settings = apply_filters( 'boomdevs_toc_get_widget_floating_settings', array() );

        if ( ! $widget_floating_settings) {
            $widget_floating_settings = array(
                array(
                    'type'    => 'subheading',
                    'content' => $this->get_floating_premium_alert_message(),
                ),
            );
        }

        CSF::createSection( $prefix, array(
            'parent' => $parent,
            'title'  => __( 'Floating', 'boomdevs-toc' ),
            'fields' => array(
                ...$widget_floating_settings
            ),
        ) );

        //Floating navigation
        $widget_floating_navigation = apply_filters( 'boomdevs_toc_get_widget_floating_navigation', array() );

        if ( ! $widget_floating_navigation) {
            $widget_floating_navigation = array(
                array(
                    'type'    => 'subheading',
                    'content' => $this->get_floating_navigation_premium_alert_message(),
                ),
            );
        }

        CSF::createSection( $prefix, array(
            'parent' => $parent,
            'title'  => __( 'Floating navigation', 'boomdevs-toc' ),
            'fields' => array(
                ...$widget_floating_navigation
            ),
        ) );

        //Progress bar
        $widget_progress_bar = apply_filters( 'boomdevs_toc_get_widget_progress_bar', array() );

        if ( ! $widget_progress_bar) {
            $widget_progress_bar = array(
                array(
                    'type'    => 'subheading',
                    'content' => $this->get_progress_bar_premium_alert_message(),
                ),
            );
        }

        CSF::createSection( $prefix, array(
            'parent' => $parent,
            'title'  => __( 'Progress bar', 'boomdevs-toc' ),
            'fields' => array(
                ...$widget_progress_bar
            ),
        ) );

        // Theme section
        $default_skins = array(
            'default_layout'       => self::$plugin_file_url . 'admin/img/default_layout_preview.png',
            'premade_layout_one'   => self::$plugin_file_url . 'admin/img/premade_layout_preview_one.png',
            'premade_layout_two'   => self::$plugin_file_url . 'admin/img/premade_layout_preview_two.png',
            'premade_layout_three' => self::$plugin_file_url . 'admin/img/premade_layout_preview_three.png',
            'premade_layout_four'  => self::$plugin_file_url . 'admin/img/premade_layout_preview_four.png',
            'premade_layout_five'  => self::$plugin_file_url . 'admin/img/premade_layout_preview_five.png',
            'premade_layout_six'  => self::$plugin_file_url . 'admin/img/premade_layout_preview_six.png',
            'premade_layout_seven'  => self::$plugin_file_url . 'admin/img/premade_layout_preview_seven.png',
            'premade_layout_eight'  => self::$plugin_file_url . 'admin/img/premade_layout_preview_eight.png',
            'premade_layout_nine'  => self::$plugin_file_url . 'admin/img/premade_layout_preview_nine.png',
            'premade_layout_ten'  => self::$plugin_file_url . 'admin/img/premade_layout_preview_ten.png',
            'premade_layout_eleven'  => self::$plugin_file_url . 'admin/img/premade_layout_preview_eleven.png',
            'premade_layout_twelve'  => self::$plugin_file_url . 'admin/img/premade_layout_preview_twelve.png',
            'premade_layout_thirteen'  => self::$plugin_file_url . 'admin/img/premade_layout_preview_thirteen.png',
            'premade_layout_fourteen'  => self::$plugin_file_url . 'admin/img/premade_layout_preview_fourteen.png',
            'premade_layout_fifteen'  => self::$plugin_file_url . 'admin/img/premade_layout_preview_fifteen.png',
            'premade_layout_sixteen'  => self::$plugin_file_url . 'admin/img/premade_layout_preview_sixteen.png',
            'premade_layout_seventeen'  => self::$plugin_file_url . 'admin/img/premade_layout_preview_seventeen.png',
        );

        $skins = apply_filters( 'boomdevs_toc_get_skins', $default_skins );

        if ( ! $skins ) {
            $skins = $default_skins;
        }

        CSF::createSection( $prefix, array(
            'parent' => $parent,
            'title'  => __( 'Pre-made themes', 'boomdevs-toc' ),
            'fields' => array(
                array(
                    'id'       => 'premade_layouts',
                    'type'     => 'fieldset',
                    'title'    => __( 'Click to import a demo', 'boomdevs-toc' ),
                    'subtitle' => sprintf( '<strong>%s</strong>: %s', __( 'Warning', 'boomdevs-toc' ), __( 'This is an irreversible action and will replace all your settings to match the selected skin', 'boomdevs-toc' ) ),
                    'class'    => 'premade_layouts',
                    'fields'   => array(
                        array(
                            'id'      => 'premade_layout',
                            'type'    => 'image_select',
                            'class'   => 'image_selects',
                            'options' => $skins,
                            'default' => 'default_layout',
                        ),
                    ),
                ),
            ),
        ) );
        
        // Free Vs Pro
        if(!Boomdevs_Toc_Utils::isProActivated()){
            CSF::createSection( $prefix, array(
                'parent' => $parent,
                'title'  => __( 'Free Vs Pro', 'boomdevs-toc' ),
                'fields' => array(
                    array(
                        'type'    => 'subheading',
                        'content' => $this->Free_VS_Pro(),
                    ),
                ),
            ) );
        }
    }

    /**
     * Return plugin all settings.
     *
     * @return string|array Settings values.
     */
    public static function get_settings() {
        return get_option( Boomdevs_Toc_Settings::$plugin_name );
    }

    protected function get_premium_alert_message() {
        return sprintf( '%s <a href="https://boomdevs.com/product/wordpress-table-of-contents/">%s</a>',
            __( 'This is a premium feature of TOC and requires the pro version of this plugin to unlock.', 'boomdevs-toc' ),
            __( 'Try out the Pro version', 'boomdevs-toc' )
        );
    }

    protected function get_sub_heading_alert_message(){
        $message = sprintf( '%s <a href="https://boomdevs.com/product/wordpress-table-of-contents/">%s</a>',
            __( 'This is a premium feature of TOC and requires the pro version of this plugin to unlock.', 'boomdevs-toc' ),
            __( 'Try out the Pro version', 'boomdevs-toc' )
        );
    
        ob_start();
        ?>
    
            <div class="Premium_feature_message"><?php echo $message; ?></div>
            <div class="Premium_feature_video">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/QJZH0b8q8g4?autoplay=1&mute=1&loop=1&controls=0&showinfo=0&playlist=QJZH0b8q8g4"  frameborder="0" allow="autoplay" allowfullscreen></iframe>
            </div>
            <p class="Premium_feature_message">Don't settle for basic features - invest in the power of TOC Pro and give your website the competitive edge it deserves.</p>
    
        <?php
    
        return ob_get_clean();
    }
    protected function get_sticky_sidebar_alert_message(){
        $message = sprintf( '%s <a href="https://boomdevs.com/product/wordpress-table-of-contents/">%s</a>',
            __( 'This is a premium feature of TOC and requires the pro version of this plugin to unlock.', 'boomdevs-toc' ),
            __( 'Try out the Pro version', 'boomdevs-toc' )
        );

        ob_start();
        ?>

        <div class="Premium_feature_message"><?php echo $message; ?></div>
        <div class="Premium_feature_video">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/5lE9tr22sD4?autoplay=1&mute=1&loop=1&controls=0&showinfo=0&playlist=5lE9tr22sD4"  frameborder="0" allow="autoplay" allowfullscreen></iframe>
        </div>
        <p class="Premium_feature_message">Don't settle for basic features - invest in the power of TOC Pro and give your website the competitive edge it deserves.</p>

        <?php

        return ob_get_clean();
    }


    protected function get_floating_premium_alert_message(){
    $message = sprintf( '%s <a href="https://boomdevs.com/product/wordpress-table-of-contents/">%s</a>',
        __( 'This is a premium feature of TOC and requires the pro version of this plugin to unlock.', 'boomdevs-toc' ),
        __( 'Try out the Pro version', 'boomdevs-toc' )
    );

    ob_start();
    ?>

        <div class="Premium_feature_message"><?php echo $message; ?></div>
        <p class="contents_message">Floating TOC Without Table of Contents</p>
        <div class="Premium_feature_video">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/IXczPkPPxSo?autoplay=1&mute=1&loop=1&controls=0&showinfo=0&playlist=IXczPkPPxSo"  frameborder="0" allow="autoplay" allowfullscreen></iframe>
        </div>
        <p class="contents_message">Floating TOC With Table of Contents</p>
        <div class="Premium_feature_video">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/G1qoofj8TTw?autoplay=1&mute=1&loop=1&controls=0&showinfo=0&playlist=G1qoofj8TTw"  frameborder="0" allow="autoplay" allowfullscreen></iframe>
        </div>
        <p class="Premium_feature_message">Don't settle for basic features - invest in the power of TOC Pro and give your website the competitive edge it deserves.</p>

    <?php

    return ob_get_clean();
    }

    protected function get_floating_navigation_premium_alert_message(){
        $message = sprintf( '%s <a href="https://boomdevs.com/product/wordpress-table-of-contents/">%s</a>',
            __( 'This is a premium feature of TOC and requires the pro version of this plugin to unlock.', 'boomdevs-toc' ),
            __( 'Try out the Pro version', 'boomdevs-toc' )
        );
    
        ob_start();
        ?>
    
            <div class="Premium_feature_message"><?php echo $message; ?></div>
            <div class="Premium_feature_video">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/nunlohfoJ6Q?autoplay=1&mute=1&loop=1&controls=0&showinfo=0&playlist=nunlohfoJ6Q"  frameborder="0" allow="autoplay" allowfullscreen></iframe>
            </div>
            <p class="Premium_feature_message">Don't settle for basic features - invest in the power of TOC Pro and give your website the competitive edge it deserves.</p>
    
        <?php
    
        return ob_get_clean();
    }

    protected function get_progress_bar_premium_alert_message(){
        $message = sprintf( '%s <a href="https://boomdevs.com/product/wordpress-table-of-contents/">%s</a>',
            __( 'This is a premium feature of TOC and requires the pro version of this plugin to unlock.', 'boomdevs-toc' ),
            __( 'Try out the Pro version', 'boomdevs-toc' )
        );
    
        ob_start();
        ?>
    
            <div class="Premium_feature_message"><?php echo $message; ?></div>
            <div class="Premium_feature_video">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/77rf5zuiem8?autoplay=1&mute=1&loop=1&color=white&controls=0&modestbranding=1&playsinline=1&rel=0&enablejsapi=1&playlist=77rf5zuiem8"  frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
            <p class="Premium_feature_message">Don't settle for basic features - invest in the power of TOC Pro and give your website the competitive edge it deserves.</p>
    
        <?php
    
        return ob_get_clean();
    }

    protected function Free_VS_Pro(){
        ob_start();
        ?>
        <div class="toc_main_wrapper">
            <div class="toc_header_wrapper">
                <div class="container">
                    <div class="title">
                        <h1>Unlock the pro features now</h1>
                    </div>
                    <div class="text">
                        <p>Confirm a well-crafted table of contents that engages readers and search engines.</p>
                    </div>
                    <div class="header_btn">
                        <div class="left_btn">
                            <a class="button button-primary" target="_blank" href="https://demo.boomdevs.com/top-table-of-contents/">View Demo</a>
                        </div>
                        <div class="right_btn">
                            <a class="button button-secondary" target="_blank" href="https://boomdevs.com/products/wordpress-table-of-contents/">Get Pro Now</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="toc_money_back_guarantee_wrapper">
                <div class="container">
                    <div class="money_back_guarantee_logo">
                       <img src="<?php echo self::$plugin_file_url . 'admin/img/money-back-logo.png' ?>" alt="money-back-logo">
                    </div>
                    <div class="money_back_guarantee_text">
                        <h3>14 Days Money Back Guarantee!</h3>
                        <p>Your satisfaction is guaranteed under our 100% No-Risk Double Guarantee. We will<br> happily <a target="_blank" href="https://boomdevs.com/refund-policy/">refund</a> 100% of your money if you don’t think our plugin works well within 14 days.</p>
                    </div>
                    <div class="money_back_guarantee_btn">
                        <a class="button button-primary" target="_blank" href="https://boomdevs.com/product-category/wordpress/wordpress-plugins/">View All Products</a>
                    </div>
                </div>
            </div>
            <div class="toc_pricing_wrapper">
                <div class="container">
                    <div class="toc_pricing_content">
                        <div class="toc_pricing_content_header">
                            <span>Get a quote</span>
                            <h2>Compare Plan</h2>
                            <p>It’s all here!  Check out the comparison of the pricing and features<br> before moving on to the pro version.</p>
                        </div>
                        <div class="toc_pricing_content_table">
                            <table class="pricing-table">
                                <thead>
                                <tr>
                                    <th>Feature</th>
                                    <th>Free</th>
                                    <th>Premium</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>More Eye-Catching & Stunning Premade Templates.</td>
                                    <td class="cross">X</td>
                                    <td><span class="tick">✓</span></td>
                                </tr>
                                <tr>
                                    <td>Sidebar TOC On Scroll.</td>
                                    <td class="cross">X</td>
                                    <td><span class="tick">✓</span></td>
                                </tr>
                                <tr>
                                    <td>Sticky Special TOC On Scroll.</td>
                                    <td class="cross">X</td>
                                    <td><span class="tick">✓</span></td>
                                </tr>
                                <tr>
                                    <td>Floating TOC with Navigation.</td>
                                    <td class="cross">X</td>
                                    <td><span class="tick">✓</span></td>
                                </tr>
                                <tr>
                                    <td>Sub-Heading Toggle Options.</td>
                                    <td class="cross">X</td>
                                    <td><span class="tick">✓</span></td>
                                </tr>
                                <tr>
                                    <td>Progress bar with TOC.</td>
                                    <td class="cross">X</td>
                                    <td><span class="tick">✓</span></td>
                                </tr>
                                <tr>
                                    <td>Active Heading Navigation.</td>
                                    <td class="cross">X</td>
                                    <td><span class="tick">✓</span></td>
                                </tr>
                                <tr>
                                    <td>Collapse/Expand Options For Subheadings.</td>
                                    <td class="cross">X</td>
                                    <td><span class="tick">✓</span></td>
                                </tr>
                                <tr>
                                    <td>Customization Panel With Live Preview.</td>
                                    <td class="cross">X</td>
                                    <td><span class="tick">✓</span></td>
                                </tr>
                                <tr>
                                    <td>A lot more </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="toc_testimonial_wrapper">
                <div class="container">
                    <div class="toc_testimonial_content">
                        <div class="toc_testimonial_content_header">
                            <span>Testimonials</span>
                            <h2>What People Say</h2>
                            <p>We're dedicated to providing the best possible experience for our customers.<br> Here's what a few of them have to say about us</p>
                        </div>
                        <div class="testimonial-cards">
                            <div class="card">
                                <div class="logo">
                                    <img src="<?php echo self::$plugin_file_url . 'admin/img/Alex.png' ?>" alt="mark-hugh">
                                </div>
                                <div class="content">
                                    <p>"It's easy to use, and the fact that it's compatible with all types of posts and pages is amazing. Highly recommended."</p>
                                </div>
                                <div class="details">
                                    <div class="name">
                                        <p>Alex</p>
                                        <span>Web Developer</span>
                                    </div>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="logo">
                                    <img src="<?php echo self::$plugin_file_url . 'admin/img/Jessica.png' ?>" alt="cody-fisher">
                                </div>
                                <div class="content">
                                    <p>"The Pro features are amazing. It makes it easy for readers to find what they're looking for on my site. Thank you, TOP Table of Contents."</p>
                                </div>
                                <div class="details">
                                    <div class="name">
                                        <p>Jessica</p>
                                        <span> Blogger</span>
                                    </div>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="logo">
                                    <img src="<?php echo self::$plugin_file_url . 'admin/img/John.png' ?>" alt="john-doe">
                                </div>
                                <div class="content">
                                    <p>"TOP Table of Contents is a game-changer for SEO. It's easy to use and customize, and it's SEO-friendly. Highly recommended."</p>
                                </div>
                                <div class="details">
                                    <div class="name">
                                        <p>John</p>
                                        <span>Marketer</span>
                                    </div>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="toc_coupon_wrapper">
                <div class="container">
                    <div class="toc_coupon_content">
                        <div class="toc_coupon_content_header">
                            <h2>What People Say About us</h2>
                            <p>We're dedicated to providing the best possible experience for our customers.<br> Here's what a few of them have to say about us</p>
                            <a class="button button-primary" href="#">View Demo</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
