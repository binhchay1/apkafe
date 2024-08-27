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
class Boomdevs_Toc_Settings
{
    public static $plugin_name = BOOMDEVS_TOC_NAME,
        $plugin_file_url = BOOMDEVS_TOC_URL;

    public function __construct()
    {
        add_filter('boomdevs_toc_register_options_panel', array($this, 'register_options_panel'), 1, 2);
    }

    public function register_options_panel($options_panel_func, $options_panel_config)
    {
        return array(
            'func' => $options_panel_func,
            'config' => $options_panel_config,
        );
    }

    public function generate_settings()
    {
        // Set a unique slug-like ID
        $prefix = Boomdevs_Toc_Settings::$plugin_name;

        // Plugin options panel configuration
        $options_panel_func = 'createOptions';
        $options_panel_config = array(
            'framework_title' => __('TOP Table Of Contents Settings', 'boomdevs-toc'),
            'framework_class'         => 'top_table_of_contants_framework',
            'footer_text' => sprintf(
                __('Enjoyed TOP Table Of Contents? Please leave us a <a target="_blank" href="%s">★★★★★ rating.</a> We really appreciate your support!', 'boomdevs-toc'),
                esc_url('https://wordpress.org/support/plugin/top-table-of-contents/reviews/?rate=5#new-post')
            ),
            'footer_credit' => sprintf(
                __('A proud creation of <a href="%s">BoomDevs</a>', 'boomdevs-toc'),
                esc_url('https://boomdevs.com/')
            ),
            'menu_title' => __('TOP Table Of Contents', 'boomdevs-toc'),
            'menu_slug' => 'boomdevs-toc-settings',
            'database' => 'option',
            'transport' => 'refresh',
            'capability' => 'manage_options',
            'save_defaults' => true,
            'enqueue_webfont' => true,
            'async_webfont' => true,
            'output_css' => true,
        );

        $options_panel_builder = apply_filters('boomdevs_toc_register_options_panel', $options_panel_func, $options_panel_config);

        CSF::{$options_panel_builder['func']}

        ($prefix, $options_panel_builder['config']);

        $parent = '';

        if ($options_panel_builder['func'] == 'createCustomizeOptions') {
            // Add to level section if in customizer mode
            CSF::createSection($prefix, array(
                'id' => $prefix . '_boomdevs_toc',
                'title' => __('TOP Table Of Contents', 'boomdevs-toc'),
            ));

            $parent = $prefix . '_boomdevs_toc';
        }

        // Data insert section
        CSF::createSection($prefix, array(
            'parent' => $parent,
            'title' => __('Auto insert', 'boomdevs-toc'),
            'fields' => array(
                array(
                    'type' => 'heading',
                    'content' => __('Post types', 'boomdevs-toc'),
                ),
                array(
                    'id' => 'select_post_type',
                    'type' => 'checkbox',
                    'title' => __('Select post types', 'boomdevs-toc'),
                    'subtitle' => __('Select the post types which will have the table of contents automatically inserted.', 'boomdevs-toc'),
                    'options' => 'post_types',
                    'class' => 'select_post',
                    'query_args' => array(
                        'orderby' => 'post_title',
                        'order' => 'ASC',
                    ),
                    'default' => array(''),
                ),
                array(
                    'id' => 'exclude_post_type',
                    'type' => 'checkbox',
                    'title' => __('Disable Automatic Heading Anchors', 'boomdevs-toc'),
                    'subtitle' => __('Allows users to turn off the automatic generation of anchor tags for specific post types.', 'boomdevs-toc'),
                    'options' => 'post_types',
                    'class' => 'select_post',
                    'query_args' => array(
                        'orderby' => 'post_title',
                        'order' => 'ASC',
                    ),
                    'default' => array(''),
                ),

                array(
                    'id' => 'select_toc_position',
                    'type' => 'select',
                    'title' => __('Select TOC position', 'boomdevs-toc'),
                    'subtitle' => __('Select the position where the table of contents will be inserted.', 'boomdevs-toc'),
                    'options' => array(
                        'before' => __('Before first heading (default)', 'boomdevs-toc'),
                        'after' => __('After first heading', 'boomdevs-toc'),
                        'afterpara' => __('After first paragraph', 'boomdevs-toc'),
                        'top' => __('Top', 'boomdevs-toc'),
                        'bottom' => __('Bottom', 'boomdevs-toc'),
                    ),
                    'default' => 'before',
                ),

            ),
        ));

        //General section

        $default_generator_settings = array(
            array(

                'id' => 'number_of_headings',
                'type' => 'number',
                'title' => __('Show when headings are more than', 'boomdevs-toc'),
                'desc' => $this->get_premium_alert_message(),
                'default' => '0',
                'attributes' => array(
                    'disabled' => true,
                ),
            ),
        );

        $generator_settings = apply_filters('boomdevs_toc_register_generator_settings', $default_generator_settings);

        if (!$generator_settings) {
            $generator_settings = $default_generator_settings;
        }

        CSF::createSection($prefix, array(
            'parent' => $parent,
            'title' => __('General', 'boomdevs-toc'),
            'fields' => array(
                array(
                    'type' => 'heading',
                    'content' => __('Layout', 'boomdevs-toc'),
                ),
                array(
                    'id' => 'initial_view',
                    'type' => 'switcher',
                    'title' => __('Content Visibility', 'boomdevs-toc'),
                    'desc' => __('Display items of content ( Unfolded view )', 'boomdevs-toc'),
                    'default' => true,
                ),

                array(
                    'id' => 'width_height',
                    'type' => 'dimensions',
                    'title' => __('Width', 'boomdevs-toc'),
                    'height' => false,
                    'output' => '.bd_toc_container',
                    'output_important' => true,
                    'default' => array(
                        'width' => '100',
                        'unit' => '%',
                    ),
                ),
                array(
                    'id' => 'layout_padding',
                    'type' => 'spacing',
                    'title' => __('Padding', 'boomdevs-toc'),
                    'output' => '.bd_toc_wrapper',
                    'output_mode' => 'padding',
                    'output_important' => true,
                    'default' => array(
                        'top' => '12',
                        'right' => '12',
                        'bottom' => '12',
                        'left' => '12',
                        'unit' => 'px',
                    ),
                ),
                array(
                    'id' => 'layout_margin',
                    'type' => 'spacing',
                    'title' => __('Margin', 'boomdevs-toc'),
                    'output' => '.bd_toc_container',
                    'output_mode' => 'margin',
                    'output_important' => true,
                    'default' => array(
                        'top' => '0',
                        'right' => '',
                        'bottom' => '30',
                        'left' => '',
                        'unit' => 'px',
                    ),
                ),
                array(
                    'id' => 'bg_color',
                    'type' => 'background',
                    'background_gradient' => false,
                    'background_color' => true,
                    'background_position' => false,
                    'background_image' => false,
                    'background_attachment' => false,
                    'background_size' => false,
                    'background_repeat' => false,
                    'title' => __('Background color', 'boomdevs-toc'),
                    'output' => '.bd_toc_container',
                    'output_important' => true,
                    'default' => array(
                        'background-color' => '#f9f9f9',
                    ),
                ),
                array(
                    'id' => 'layout_border',
                    'type' => 'border',
                    'title' => __('Border', 'boomdevs-toc'),
                    'output' => '.bd_toc_container',
                    'output_important' => true,
                    'default' => array(
                        'style' => 'solid',
                        'color' => '#aaaaaa',
                        'top' => '1',
                        'right' => '1',
                        'bottom' => '1',
                        'left' => '1',
                        'unit' => 'px',
                    ),
                ),
                array(
                    'type' => 'heading',
                    'content' => __('Border radius', 'boomdevs-toc'),
                ),
                array(
                    'id' => 'layout_border_radius_top',
                    'type' => 'number',
                    'unit' => 'px',
                    'default' => '4',
                    'title' => __('Top', 'boomdevs-toc'),
                ),
                array(
                    'id' => 'layout_border_radius_right',
                    'type' => 'number',
                    'unit' => 'px',
                    'default' => '4',
                    'title' => __('Right', 'boomdevs-toc'),
                ),
                array(
                    'id' => 'layout_border_radius_bottom',
                    'type' => 'number',
                    'unit' => 'px',
                    'default' => '4',
                    'title' => __('Bottom', 'boomdevs-toc'),
                ),
                array(
                    'id' => 'layout_border_radius_left',
                    'type' => 'number',
                    'unit' => 'px',
                    'default' => '4',
                    'title' => __('Left', 'boomdevs-toc'),
                ),
                array(
                    'type' => 'heading',
                    'content' => __('Box shadow', 'boomdevs-toc'),
                ),
                array(
                    'id' => 'horizontal_length',
                    'type' => 'number',
                    'unit' => 'px',
                    'default' => 0,
                    'title' => __('Horizontal Length', 'boomdevs-toc'),
                ),
                array(
                    'id' => 'vertical_length',
                    'type' => 'number',
                    'unit' => 'px',
                    'default' => 4,
                    'title' => __('Vertical Length', 'boomdevs-toc'),
                ),
                array(
                    'id' => 'blur_radius',
                    'type' => 'number',
                    'unit' => 'px',
                    'default' => 16,
                    'title' => __('Blur radius', 'boomdevs-toc'),
                ),
                array(
                    'id' => 'spread_radius',
                    'type' => 'number',
                    'unit' => 'px',
                    'default' => 0,
                    'title' => __('Spread radius', 'boomdevs-toc'),
                ),
                array(
                    'id' => 'shadow_color',
                    'type' => 'color',
                    'title' => 'Shadow Color',
                    'default' => 'rgba(0, 0, 0, 0.03)',
                ),
                array(
                    'type' => 'heading',
                    'content' => __('Generator configuration', 'boomdevs-toc'),
                ),
                ...$generator_settings,
                array(
                    'id' => 'heading_top_level',
                    'type' => 'select',
                    'title' => __('Top level heading', 'boomdevs-toc'),
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
                    'id' => 'title_depth',
                    'type' => 'select',
                    'title' => __('Heading depth', 'boomdevs-toc'),
                    'desc' => __('Upper Maximum depth of title tags to be displayed', 'boomdevs-toc'),
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
                    'id' => 'title_hide',
                    'type' => 'select',
                    'title' => __('Exclude heading', 'boomdevs-toc'),
                    'chosen' => true,
                    'multiple' => true,
                    'desc' => __('Select Tag', 'boomdevs-toc'),
                    'options' => array(
                        '1' => 'H1',
                        '2' => 'H2',
                        '3' => 'H3',
                        '4' => 'H4',
                        '5' => 'H5',
                        '6' => 'H6',
                    ),
                    'default' => array('6'),
                ),
            ),
        ));

        // Title section
        CSF::createSection($prefix, array(
            'parent' => $parent,
            'title' => __('Title', 'boomdevs-toc'),
            'fields' => array(
                array(
                    'id' => 'title_show_hide_switcher',
                    'type' => 'switcher',
                    'title' => __('Show title', 'boomdevs-toc'),
                    'default' => true,
                ),
                array(
                    'id' => 'title',
                    'type' => 'text',
                    'title' => __('Title', 'boomdevs-toc'),
                    'dependency' => array('title_show_hide_switcher', '==', 'true'),
                    'default' => __('Table of Contents', 'boomdevs-toc'),
                ),
                array(
                    'id' => 'title_font_family',
                    'title' => 'Typography',
                    'type' => 'typography',
                    'output' => '.bd_toc_header_title',
                    'output_important' => true,
                    'font_family' => true,
                    'font_weight' => true,
                    'subset' => true,
                    'font_style' => true,
                    'font_size' => true,
                    'line_height' => true,
                    'letter_spacing' => true,
                    'text_align' => true,
                    'text_transform' => true,
                    'color' => false,
                    'default' => array(
                        'font-family' => '',
                        'font-size' => '18',
                        'font-weight' => '500',
                        'unit' => 'px',
                        'type' => 'google',
                    ),
                    'dependency' => array('title_show_hide_switcher', '==', 'true'),
                ),
                array(
                    'id' => 'title_bg_color',
                    'type' => 'background',
                    'background_gradient' => false,
                    'background_color' => true,
                    'background_position' => false,
                    'background_image' => false,
                    'background_attachment' => false,
                    'background_size' => false,
                    'background_repeat' => false,
                    'title' => __('Background color', 'boomdevs-toc'),
                    'output' => '.bd_toc_header',
                    'output_important' => true,
                    'dependency' => array('title_show_hide_switcher', '==', 'true'),
                ),
                array(
                    'id' => 'title_color',
                    'type' => 'color',
                    'title' => __('Color', 'boomdevs-toc'),
                    'output' => '.bd_toc_wrapper .bd_toc_header .bd_toc_header_title',
                    'default' => '#2c2f32',
                    'output_important' => true,
                    'dependency' => array('title_show_hide_switcher', '==', 'true'),
                ),
                array(
                    'id' => 'title_hover_color',
                    'type' => 'color',
                    'title' => __('Hover color', 'boomdevs-toc'),
                    'output' => '.bd_toc_wrapper .bd_toc_header:hover .bd_toc_header_title',
                    'default' => '#2c2f32',
                    'output_important' => true,
                    'dependency' => array('title_show_hide_switcher', '==', 'true'),
                ),
                array(
                    'id' => 'title_padding',
                    'type' => 'spacing',
                    'title' => __('Padding', 'boomdevs-toc'),
                    'output' => '.bd_toc_header',
                    'output_mode' => 'padding',
                    'output_important' => true,
                    'default' => array(
                        'top' => '0',
                        'right' => '2',
                        'bottom' => '0',
                        'left' => '0',
                        'unit' => 'px',
                    ),
                    'dependency' => array('title_show_hide_switcher', '==', 'true'),
                ),
                array(
                    'id' => 'title_margin',
                    'type' => 'spacing',
                    'title' => __('Margin', 'boomdevs-toc'),
                    'output' => '.bd_toc_header.active',
                    'output_mode' => 'margin',
                    'output_important' => true,
                    'default' => array(
                        'top' => '0',
                        'right' => '0',
                        'bottom' => '0',
                        'left' => '0',
                        'unit' => 'px',
                    ),
                    'dependency' => array('title_show_hide_switcher', '==', 'true'),
                ),
                array(
                    'type' => 'heading',
                    'content' => __('Border radius', 'boomdevs-toc'),
                    'dependency' => array('title_show_hide_switcher', '==', 'true'),
                ),
                array(
                    'id' => 'title_border_radius_top',
                    'type' => 'number',
                    'unit' => 'px',
                    'title' => __('Top', 'boomdevs-toc'),
                    'default' => 10,
                    'dependency' => array('title_show_hide_switcher', '==', 'true'),
                ),
                array(
                    'id' => 'title_border_radius_right',
                    'type' => 'number',
                    'unit' => 'px',
                    'title' => __('Right', 'boomdevs-toc'),
                    'default' => 10,
                    'dependency' => array('title_show_hide_switcher', '==', 'true'),
                ),
                array(
                    'id' => 'title_border_radius_bottom',
                    'type' => 'number',
                    'unit' => 'px',
                    'title' => __('Bottom', 'boomdevs-toc'),
                    'default' => 10,
                    'dependency' => array('title_show_hide_switcher', '==', 'true'),
                ),
                array(
                    'id' => 'title_border_radius_left',
                    'type' => 'number',
                    'unit' => 'px',
                    'title' => __('Left', 'boomdevs-toc'),
                    'default' => 10,
                    'dependency' => array('title_show_hide_switcher', '==', 'true'),
                ),
                array(
                    'type' => 'heading',
                    'content' => __('Toggle icon', 'boomdevs-toc'),
                    'dependency' => array('title_show_hide_switcher', '==', 'true'),
                ),
                array(
                    'id' => 'icon_show_hide_switcher',
                    'type' => 'switcher',
                    'title' => __('Show toggle icon', 'boomdevs-toc'),
                    'default' => true,
                    'dependency' => array('title_show_hide_switcher', '==', 'true'),
                ),
                array(
                    'id' => 'toggle_icon_color',
                    'type' => 'color',
                    'default' => '#2c2f32',
                    'title' => __('Color', 'boomdevs-toc'),
                    'dependency' => array(
                        array('icon_show_hide_switcher', '==', 'true'),
                        array('title_show_hide_switcher', '==', 'true'),
                    ),
                ),
                array(
                    'id' => 'toggle_icon_hover_color',
                    'type' => 'color',
                    'default' => '#2c2f32',
                    'title' => __('Hover color', 'boomdevs-toc'),
                    'dependency' => array(
                        array('icon_show_hide_switcher', '==', 'true'),
                        array('title_show_hide_switcher', '==', 'true'),
                    ),
                ),
            ),
        ));

        // Heading section

        $headings_toggle_icon_settings = apply_filters('boomdevs_toc_register_heading_toggle_icon_settings', array());
        $bd_toc_layout_toggle = apply_filters('boomdevs_toc_get_bd_toc_layout_toggle_settings', array());

        if (!$headings_toggle_icon_settings) {
            $headings_toggle_icon_settings = array(
                array(
                    'type' => 'subheading',
                    'content' => $this->get_premium_alert_message(),
                ),
            );
        }

        if (!$bd_toc_layout_toggle) {
            $bd_toc_layout_toggle = array(
                array(
                    'type' => 'subheading',
                    'content' => $this->get_premium_alert_message(),
                ),

            );
        }

        CSF::createSection($prefix, array(
            'parent' => $parent,
            'title' => __('Headings', 'boomdevs-toc'),
            'fields' => array(
                ...$bd_toc_layout_toggle,
                array(
                    'id' => 'heading_list_type',
                    'type' => 'select',
                    'title' => __('List style', 'boomdevs-toc'),
                    'options' => array(
                        'disc' => 'Disc',
                        'number' => 'Numeric',
                        'none' => 'None',
                    ),
                    'default' => 'number',
                ),
                array(
                    'id' => 'heading_word_break',
                    'type' => 'select',
                    'title' => __('Word Break', 'boomdevs-toc'),
                    'output' => '.bd_toc_container .bd_toc_wrapper .bd_toc_content_list_item ul li a',
                    'options' => array(
                        'word_break' => __('Word Break', 'boomdevs-toc'),
                        'truncate' => __('Truncate ', 'boomdevs-toc'),
                    ),
                    'default' => 'truncate',
                ),
                array(
                    'id' => 'offset_from_top',
                    'type' => 'number',
                    'unit' => 'px',
                    'title' => __('Offset from top', 'boomdevs-toc'),
                    'default' => 0,
                ),
                array(
                    'id' => 'heading_font_family',
                    'type' => 'typography',
                    'title' => __('Typography', 'boomdevs-toc'),
                    'output' => '.bd_toc_content_list .bd_toc_content_list_item ul > li > a',
                    'output_important' => true,
                    'font_family' => true,
                    'font_weight' => true,
                    'subset' => true,
                    'font_style' => true,
                    'font_size' => true,
                    'line_height' => true,
                    'letter_spacing' => true,
                    'text_align' => true,
                    'text_transform' => true,
                    'color' => false,
                    'default' => array(
                        'font-family' => '',
                        'font-size' => '14',
                        'font-weight' => '400',
                        'unit' => 'px',
                        'type' => 'google',
                    ),
                ),
                array(
                    'id' => 'heading_padding',
                    'type' => 'spacing',
                    'title' => __('Padding', 'boomdevs-toc'),
                    'output' => '.bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list_item ul li a',
                    'output_mode' => 'padding',
                    'output_important' => true,
                    'default' => array(
                        'top' => '0',
                        'right' => '0',
                        'bottom' => '0',
                        'left' => '0',
                        'unit' => 'px',
                    ),
                ),
                array(
                    'id' => 'heading_margin',
                    'type' => 'spacing',
                    'title' => __('Margin', 'boomdevs-toc'),
                    'output' => '.bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list_item ul li a',
                    'output_mode' => 'margin',
                    'output_important' => true,
                    'default' => array(
                        'top' => '0',
                        'right' => '0',
                        'bottom' => '0',
                        'left' => '0',
                        'unit' => 'px',
                    ),
                ),
                array(
                    'id' => 'heading_border',
                    'type' => 'border',
                    'title' => __('Border', 'boomdevs-toc'),
                    'output' => '.bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list ul li a',
                    'default' => array(
                        'top' => '0',
                        'right' => '0',
                        'bottom' => '0',
                        'left' => '0',
                        'style' => 'solid',
                        'color' => '#ffffff',
                        'unit' => 'px',
                    ),
                ),
                array(
                    'id' => 'active_heading_border',
                    'type' => 'border',
                    'title' => __('Active border', 'boomdevs-toc'),
                    'unit' => 'px',
                    'output' => '.bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list ul li.current > a',
                    'default' => array(
                        'top' => '0',
                        'right' => '0',
                        'bottom' => '0',
                        'left' => '0',
                        'style' => 'solid',
                        'color' => '#ffffff',
                        'unit' => 'px',
                    ),
                ),
                array(
                    'type' => 'heading',
                    'content' => __('Active border radius', 'boomdevs-toc'),
                ),
                array(
                    'id' => 'heading_border_radius_top',
                    'type' => 'number',
                    'unit' => 'px',
                    'title' => __('Top', 'boomdevs-toc'),
                    'default' => 10,
                ),
                array(
                    'id' => 'heading_border_radius_right',
                    'type' => 'number',
                    'unit' => 'px',
                    'title' => __('Right', 'boomdevs-toc'),
                    'default' => 10,
                ),
                array(
                    'id' => 'heading_border_radius_bottom',
                    'type' => 'number',
                    'unit' => 'px',
                    'title' => __('Bottom', 'boomdevs-toc'),
                    'default' => 10,
                ),
                array(
                    'id' => 'heading_border_radius_left',
                    'type' => 'number',
                    'unit' => 'px',
                    'title' => __('Left', 'boomdevs-toc'),
                    'default' => 10,
                ),
                array(
                    'type' => 'heading',
                    'content' => __('Color', 'boomdevs-toc'),
                ),
                array(
                    'id' => 'content_bg_color',
                    'type' => 'background',
                    'background_gradient' => false,
                    'background_color' => true,
                    'background_position' => false,
                    'background_image' => false,
                    'background_attachment' => false,
                    'background_size' => false,
                    'background_repeat' => false,
                    'title' => __('Container background color', 'boomdevs-toc'),
                    'output' => '.bd_toc_content',
                    'output_important' => true,
                    'default' => array(
                        'background-color' => '#f9f9f9',
                    ),
                ),
                array(
                    'id' => 'heading_background',
                    'type' => 'color',
                    'title' => __('Background color', 'boomdevs-toc'),
                    'output' => '.bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list ul li a',
                    'output_mode' => 'background-color',
                    'default' => '#f9f9f9',
                    'output_important' => true,
                ),
                array(
                    'id' => 'active_heading_background',
                    'type' => 'color',
                    'title' => __('Active background color', 'boomdevs-toc'),
                    'output' => '.bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list ul li.current > a',
                    'output_mode' => 'background-color',
                    'default' => '#f7f7f700',
                    'output_important' => true,
                ),
                array(
                    'id' => 'heading_font_color',
                    'type' => 'color',
                    'title' => __('Color', 'boomdevs-toc'),
                    'output' => '.bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list ul li a, .bd_toc_container .bd_toc_wrapper .bd_toc_content_list_item ul li .collaps-button .toggle-icon',
                    'default' => '#2c2f32',
                ),
                array(
                    'id' => 'heading_font_hover_color',
                    'type' => 'color',
                    'title' => __('Hover color', 'boomdevs-toc'),
                    'output' => '.bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list ul li a:hover, .bd_toc_container .bd_toc_wrapper .bd_toc_content_list_item ul li .collaps-button .toggle-icon:hover',
                    'default' => '#2c2f32',
                ),
                array(
                    'id' => 'heading_font_active_color',
                    'type' => 'color',
                    'title' => __('Active color', 'boomdevs-toc'),
                    'output' => array('.bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list ul li.current > a', '.bd_toc_container .bd_toc_wrapper .bd_toc_content_list_item ul li.current>.collaps-button .toggle-icon'),
                    'default' => '#2c2f32',
                ),

                array(
                    'type' => 'heading',
                    'content' => __('Toggle icon', 'boomdevs-toc'),
                ),
                ...$headings_toggle_icon_settings,
            ),
        ));

        // Sub Heading section
        $sub_headings_toggle_icon_settings = apply_filters('boomdevs_toc_register_sub_heading_toggle_icon_settings', array());
        $sub_heading_settings = apply_filters('boomdevs_toc_register_sub_heading_settings', array());

        if (!$sub_headings_toggle_icon_settings && !$sub_heading_settings) {
            $sub_headings_toggle_icon_settings = array(
                array(
                    'type' => 'subheading',
                    'content' => $this->get_sub_heading_alert_message(),
                ),
            );
        }

        CSF::createSection($prefix, array(
            'parent' => $parent,
            'title' => __('Sub headings', 'boomdevs-toc'),
            'fields' => array(
                ...$sub_heading_settings,
                ...$sub_headings_toggle_icon_settings,
            ),
        ));

        //Sticky sidebar
        $sticky_sidebar_settings = apply_filters('boomdevs_toc_register_sticky_sidebar_settings', array());

        if (!$sticky_sidebar_settings) {
            $sticky_sidebar_settings = array(
                array(
                    'type' => 'subheading',
                    'content' => $this->get_sticky_sidebar_alert_message(),
                ),
            );
        }

        CSF::createSection($prefix, array(
            'parent' => $parent,
            'title' => __('Sticky sidebar', 'boomdevs-toc'),
            'fields' => array(
                ...$sticky_sidebar_settings
            ),
        ));

        //Floating section
        $widget_floating_settings = apply_filters('boomdevs_toc_get_widget_floating_settings', array());

        if (!$widget_floating_settings) {
            $widget_floating_settings = array(
                array(
                    'type' => 'subheading',
                    'content' => $this->get_floating_premium_alert_message(),
                ),
            );
        }

        CSF::createSection($prefix, array(
            'parent' => $parent,
            'title' => __('Floating', 'boomdevs-toc'),
            'fields' => array(
                ...$widget_floating_settings
            ),
        ));

        //Floating navigation
        $widget_floating_navigation = apply_filters('boomdevs_toc_get_widget_floating_navigation', array());

        if (!$widget_floating_navigation) {
            $widget_floating_navigation = array(
                array(
                    'type' => 'subheading',
                    'content' => $this->get_floating_navigation_premium_alert_message(),
                ),
            );
        }

        CSF::createSection($prefix, array(
            'parent' => $parent,
            'title' => __('Floating navigation', 'boomdevs-toc'),
            'fields' => array(
                ...$widget_floating_navigation
            ),
        ));

        //Progress bar
        $widget_progress_bar = apply_filters('boomdevs_toc_get_widget_progress_bar', array());

        if (!$widget_progress_bar) {
            $widget_progress_bar = array(
                array(
                    'type' => 'subheading',
                    'content' => $this->get_progress_bar_premium_alert_message(),
                ),
            );
        }

        CSF::createSection($prefix, array(
            'parent' => $parent,
            'title' => __('Progress bar', 'boomdevs-toc'),
            'fields' => array(
                ...$widget_progress_bar
            ),
        ));

        // Theme section
        $default_skins = array(
            'default_layout' => self::$plugin_file_url . 'admin/img/default_layout_preview.png',
            'premade_layout_one' => self::$plugin_file_url . 'admin/img/premade_layout_preview_one.png',
            'premade_layout_two' => self::$plugin_file_url . 'admin/img/premade_layout_preview_two.png',
            'premade_layout_three' => self::$plugin_file_url . 'admin/img/premade_layout_preview_three.png',
            'premade_layout_four' => self::$plugin_file_url . 'admin/img/premade_layout_preview_four.png',
            'premade_layout_five' => self::$plugin_file_url . 'admin/img/premade_layout_preview_five.png',
            'premade_layout_six' => self::$plugin_file_url . 'admin/img/premade_layout_preview_six.png',
            'premade_layout_seven' => self::$plugin_file_url . 'admin/img/premade_layout_preview_seven.png',
            'premade_layout_eight' => self::$plugin_file_url . 'admin/img/premade_layout_preview_eight.png',
            'premade_layout_nine' => self::$plugin_file_url . 'admin/img/premade_layout_preview_nine.png',
            'premade_layout_ten' => self::$plugin_file_url . 'admin/img/premade_layout_preview_ten.png',
            'premade_layout_eleven' => self::$plugin_file_url . 'admin/img/premade_layout_preview_eleven.png',
            'premade_layout_twelve' => self::$plugin_file_url . 'admin/img/premade_layout_preview_twelve.png',
            'premade_layout_thirteen' => self::$plugin_file_url . 'admin/img/premade_layout_preview_thirteen.png',
            'premade_layout_fourteen' => self::$plugin_file_url . 'admin/img/premade_layout_preview_fourteen.png',
            'premade_layout_fifteen' => self::$plugin_file_url . 'admin/img/premade_layout_preview_fifteen.png',
            'premade_layout_sixteen' => self::$plugin_file_url . 'admin/img/premade_layout_preview_sixteen.png',
            'premade_layout_seventeen' => self::$plugin_file_url . 'admin/img/premade_layout_preview_seventeen.png',
        );

        $skins = apply_filters('boomdevs_toc_get_skins', $default_skins);

        if (!$skins) {
            $skins = $default_skins;
        }

        CSF::createSection($prefix, array(
            'parent' => $parent,
            'title' => __('Pre-made themes', 'boomdevs-toc'),
            'fields' => array(
                array(
                    'id' => 'premade_layouts',
                    'type' => 'fieldset',
                    'title' => __('Click to import a demo', 'boomdevs-toc'),
                    'subtitle' => sprintf('<strong>%s</strong>: %s', __('Warning', 'boomdevs-toc'), __('This is an irreversible action and will replace all your settings to match the selected skin', 'boomdevs-toc')),
                    'class' => 'premade_layouts',
                    'fields' => array(
                        array(
                            'id' => 'premade_layout',
                            'type' => 'image_select',
                            'class' => 'image_selects',
                            'options' => $skins,
                            'default' => 'default_layout',
                        ),
                    ),
                ),
            ),
        ));

        // Free Vs Pro
        if (!Boomdevs_Toc_Utils::isProActivated()) {
            CSF::createSection($prefix, array(
                'parent' => $parent,
                'title' => __('Free Vs Pro', 'boomdevs-toc'),
                'fields' => array(
                    array(
                        'type' => 'subheading',
                        'content' => $this->Free_VS_Pro(),
                    ),
                ),
            ));
        }

        $is_activated = is_plugin_active('ai-image-alt-text-generator-for-wp/boomdevs-ai-image-alt-text-generator.php');

        if (!Boomdevs_Toc_Utils::isProActivated() && !$is_activated) {
            CSF::createSection($prefix, array(
                'parent' => $parent,
                'title' => __('Ai AltText Generator', 'boomdevs-toc'),
                'fields' => array(
                    array(
                        'type' => 'subheading',
                        'content' => $this->AiAltTextGenerator(),
                    ),
                ),
            ));
        }
    }

    /**
     * Return plugin all settings.
     *
     * @return string|array Settings values.
     */
    public static function get_settings()
    {
        return get_option(Boomdevs_Toc_Settings::$plugin_name);
    }

    protected function get_premium_alert_message()
    {
        return sprintf('%s <a href="https://boomdevs.com/product/wordpress-table-of-contents/">%s</a>',
            __('This is a premium feature of TOC and requires the pro version of this plugin to unlock.', 'boomdevs-toc'),
            __('Try out the Pro version', 'boomdevs-toc')
        );
    }

    protected function get_sub_heading_alert_message()
    {
        $message = sprintf('%s <a href="https://boomdevs.com/product/wordpress-table-of-contents/">%s</a>',
            __('This is a premium feature of TOC and requires the pro version of this plugin to unlock.', 'boomdevs-toc'),
            __('Try out the Pro version', 'boomdevs-toc')
        );

        ob_start();
        ?>

        <div class="Premium_feature_message"><?php echo $message; ?></div>
        <div class="Premium_feature_video">
            <iframe width="560" height="315"
                    src="https://www.youtube.com/embed/QJZH0b8q8g4?autoplay=1&mute=1&loop=1&controls=0&showinfo=0&playlist=QJZH0b8q8g4"
                    frameborder="0" allow="autoplay" allowfullscreen></iframe>
        </div>
        <p class="Premium_feature_message">Don't settle for basic features - invest in the power of TOC Pro and give
            your website the competitive edge it deserves.</p>

        <?php

        return ob_get_clean();
    }

    protected function get_sticky_sidebar_alert_message()
    {
        $message = sprintf('%s <a href="https://boomdevs.com/product/wordpress-table-of-contents/">%s</a>',
            __('This is a premium feature of TOC and requires the pro version of this plugin to unlock.', 'boomdevs-toc'),
            __('Try out the Pro version', 'boomdevs-toc')
        );

        ob_start();
        ?>

        <div class="Premium_feature_message"><?php echo $message; ?></div>
        <div class="Premium_feature_video">
            <iframe width="560" height="315"
                    src="https://www.youtube.com/embed/5lE9tr22sD4?autoplay=1&mute=1&loop=1&controls=0&showinfo=0&playlist=5lE9tr22sD4"
                    frameborder="0" allow="autoplay" allowfullscreen></iframe>
        </div>
        <p class="Premium_feature_message">Don't settle for basic features - invest in the power of TOC Pro and give
            your website the competitive edge it deserves.</p>

        <?php

        return ob_get_clean();
    }


    protected function get_floating_premium_alert_message()
    {
        $message = sprintf('%s <a href="https://boomdevs.com/product/wordpress-table-of-contents/">%s</a>',
            __('This is a premium feature of TOC and requires the pro version of this plugin to unlock.', 'boomdevs-toc'),
            __('Try out the Pro version', 'boomdevs-toc')
        );

        ob_start();
        ?>

        <div class="Premium_feature_message"><?php echo $message; ?></div>
        <p class="contents_message">Floating TOC Without Table of Contents</p>
        <div class="Premium_feature_video">
            <iframe width="560" height="315"
                    src="https://www.youtube.com/embed/IXczPkPPxSo?autoplay=1&mute=1&loop=1&controls=0&showinfo=0&playlist=IXczPkPPxSo"
                    frameborder="0" allow="autoplay" allowfullscreen></iframe>
        </div>
        <p class="contents_message">Floating TOC With Table of Contents</p>
        <div class="Premium_feature_video">
            <iframe width="560" height="315"
                    src="https://www.youtube.com/embed/G1qoofj8TTw?autoplay=1&mute=1&loop=1&controls=0&showinfo=0&playlist=G1qoofj8TTw"
                    frameborder="0" allow="autoplay" allowfullscreen></iframe>
        </div>
        <p class="Premium_feature_message">Don't settle for basic features - invest in the power of TOC Pro and give
            your website the competitive edge it deserves.</p>

        <?php

        return ob_get_clean();
    }

    protected function get_floating_navigation_premium_alert_message()
    {
        $message = sprintf('%s <a href="https://boomdevs.com/product/wordpress-table-of-contents/">%s</a>',
            __('This is a premium feature of TOC and requires the pro version of this plugin to unlock.', 'boomdevs-toc'),
            __('Try out the Pro version', 'boomdevs-toc')
        );

        ob_start();
        ?>

        <div class="Premium_feature_message"><?php echo $message; ?></div>
        <div class="Premium_feature_video">
            <iframe width="560" height="315"
                    src="https://www.youtube.com/embed/nunlohfoJ6Q?autoplay=1&mute=1&loop=1&controls=0&showinfo=0&playlist=nunlohfoJ6Q"
                    frameborder="0" allow="autoplay" allowfullscreen></iframe>
        </div>
        <p class="Premium_feature_message">Don't settle for basic features - invest in the power of TOC Pro and give
            your website the competitive edge it deserves.</p>

        <?php

        return ob_get_clean();
    }

    protected function get_progress_bar_premium_alert_message()
    {
        $message = sprintf('%s <a href="https://boomdevs.com/product/wordpress-table-of-contents/">%s</a>',
            __('This is a premium feature of TOC and requires the pro version of this plugin to unlock.', 'boomdevs-toc'),
            __('Try out the Pro version', 'boomdevs-toc')
        );

        ob_start();
        ?>

        <div class="Premium_feature_message"><?php echo $message; ?></div>
        <div class="Premium_feature_video">
            <iframe width="560" height="315"
                    src="https://www.youtube.com/embed/77rf5zuiem8?autoplay=1&mute=1&loop=1&color=white&controls=0&modestbranding=1&playsinline=1&rel=0&enablejsapi=1&playlist=77rf5zuiem8"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
        </div>
        <p class="Premium_feature_message">Don't settle for basic features - invest in the power of TOC Pro and give
            your website the competitive edge it deserves.</p>

        <?php

        return ob_get_clean();
    }

    protected function Free_VS_Pro()
    {
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
                            <a class="button button-primary" target="_blank"
                               href="https://demo.boomdevs.com/top-table-of-contents/">View Demo</a>
                        </div>
                        <div class="right_btn">
                            <a class="button button-secondary" target="_blank"
                               href="https://boomdevs.com/products/wordpress-table-of-contents/">Get Pro Now</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="toc_money_back_guarantee_wrapper">
                <div class="container">
                    <div class="money_back_guarantee_logo">
                        <img src="<?php echo self::$plugin_file_url . 'admin/img/money-back-logo.png' ?>"
                             alt="money-back-logo">
                    </div>
                    <div class="money_back_guarantee_text">
                        <h3>14 Days Money Back Guarantee!</h3>
                        <p>Your satisfaction is guaranteed under our 100% No-Risk Double Guarantee. We will<br> happily
                            <a target="_blank" href="https://boomdevs.com/refund-policy/">refund</a> 100% of your money
                            if you don’t think our plugin works well within 14 days.</p>
                    </div>
                    <div class="money_back_guarantee_btn">
                        <a class="button button-primary" target="_blank"
                           href="https://boomdevs.com/product-category/wordpress/wordpress-plugins/">View All
                            Products</a>
                    </div>
                </div>
            </div>
            <div class="toc_pricing_wrapper">
                <div class="container">
                    <div class="toc_pricing_content">
                        <div class="toc_pricing_content_header">
                            <span>Get a quote</span>
                            <h2>Compare Plan</h2>
                            <p>It’s all here! Check out the comparison of the pricing and features<br> before moving on
                                to the pro version.</p>
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
                                    <td>A lot more</td>
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
                            <p>We're dedicated to providing the best possible experience for our customers.<br> Here's
                                what a few of them have to say about us</p>
                        </div>
                        <div class="testimonial-cards">
                            <div class="card">
                                <div class="logo">
                                    <img src="<?php echo self::$plugin_file_url . 'admin/img/Alex.png' ?>"
                                         alt="mark-hugh">
                                </div>
                                <div class="content">
                                    <p>"It's easy to use, and the fact that it's compatible with all types of posts and
                                        pages is amazing. Highly recommended."</p>
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
                                    <img src="<?php echo self::$plugin_file_url . 'admin/img/Jessica.png' ?>"
                                         alt="cody-fisher">
                                </div>
                                <div class="content">
                                    <p>"The Pro features are amazing. It makes it easy for readers to find what they're
                                        looking for on my site. Thank you, TOP Table of Contents."</p>
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
                                    <img src="<?php echo self::$plugin_file_url . 'admin/img/John.png' ?>"
                                         alt="john-doe">
                                </div>
                                <div class="content">
                                    <p>"TOP Table of Contents is a game-changer for SEO. It's easy to use and customize,
                                        and it's SEO-friendly. Highly recommended."</p>
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
                            <p>We're dedicated to providing the best possible experience for our customers.<br> Here's
                                what a few of them have to say about us</p>
                            <a class="button button-primary" href="#">View Demo</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function AiAltTextGenerator()
    {
        ob_start();

        $target_url = esc_url(admin_url('/admin.php?page=ai-alt-text-generator'));

        ?>
        <div class="toc-custom-landing-wrapper">

            <div class="toc-custom-landing-inside">
                <div class="toc-custom-landing-top">
                    <img src="<?php echo self::$plugin_file_url . 'admin/img/ai-mage-alt-text-generator.svg'; ?>" alt="toc-logo">
                    <h2>TOP TOC + AI Image Alt text Generator = Your SEO Buddy</h2>
                    <p>Let AI Craft Perfect Alt Text for all images.</p>
                    <a href="#" class="toc-custom-landing-install-btn" data-target-url = "<?php echo $target_url; ?>">
                        <span class="toc-custom-landing-install-btn-txt">Install Ai Alt Text - Free</span>
                        <span class="toc-custom-landing-install-btn-icon">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M3.8335 13.3346L3.8335 14.168C3.8335 15.5487 4.95278 16.668 6.3335 16.668L14.6668 16.668C16.0475 16.668 17.1668 15.5487 17.1668 14.168L17.1668 13.3346M13.8335 10.0013L10.5002 13.3346M10.5002 13.3346L7.16683 10.0013M10.5002 13.3346L10.5002 3.33464" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>
                    </a>
                    <div class="toc-landing-video-player">
                        <iframe src="https://www.youtube-nocookie.com/embed/fKAz49VtbUI?showinfo=0&amp;autoplay=0&amp;mute=0&amp;rel=0"
                            allow="autoplay" title="YouTube video player" frameborder="0"
                            allowfullscreen="">
                        </iframe>
                    </div>
                </div>
                <div class="toc-custom-landing-info-grid">
                    <div class="toc-custom-landing-single-wrap">
                        <div class="toc-custom-landing-single-info">
                            <div>
                                <svg width="21" height="21" viewBox="0 0 21 21" fill="none">
                                    <path d="M13 10V3L4 14H11L11 21L20 10L13 10Z" stroke="#334155" stroke-width="2"
                                          stroke-linecap="round" stroke-linejoin="round"></path>
                                    <circle cx="8" cy="8" r="8" fill="#5733FF" fill-opacity="0.24"></circle>
                                </svg>
                            </div>
                            <h3>Automated Alt Text Generation</h3>
                            <p>Say goodbye to manually entering alt text for each image. Our plugin automatically generates alt text upon image upload, saving you time and effort.</p>
                        </div>
                        <div class="toc-custom-landing-single-info">
                            <div>
                                <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
                                    <path d="M9 12.001L11 14.001L15 10.001M20.6179 5.98531C20.4132 5.99569 20.2072 6.00095 20 6.00095C16.9265 6.00095 14.123 4.84551 11.9999 2.94531C9.87691 4.84544 7.07339 6.00083 4 6.00083C3.79277 6.00083 3.58678 5.99557 3.38213 5.98519C3.1327 6.94881 3 7.9594 3 9.00099C3 14.5925 6.82432 19.2908 12 20.6229C17.1757 19.2908 21 14.5925 21 9.00099C21 7.95944 20.8673 6.94889 20.6179 5.98531Z"
                                          stroke="#334155" stroke-width="2" stroke-linecap="round"
                                          stroke-linejoin="round"></path>
                                    <circle cx="8.33" cy="8" r="8" fill="#5733FF" fill-opacity="0.24"></circle>
                                </svg>
                            </div>
                            <h3>Bulk Alt Text Generation</h3>
                            <p>Have a large number of images? No problem! Our plugin can generate alt text in bulk, making it easy to optimize all your images at once.</p>
                        </div>
                    </div>
                    <div class="toc-custom-landing-single-wrap">
                        <div class="toc-custom-landing-single-info">
                            <div>
                                <svg width="23" height="21" viewBox="0 0 23 21" fill="none">
                                    <path d="M12 5H7C5.89543 5 5 5.89543 5 7V18C5 19.1046 5.89543 20 7 20H18C19.1046 20 20 19.1046 20 18V13M18.5858 3.58579C19.3668 2.80474 20.6332 2.80474 21.4142 3.58579C22.1953 4.36683 22.1953 5.63316 21.4142 6.41421L12.8284 15H10L10 12.1716L18.5858 3.58579Z"
                                          stroke="#334155" stroke-width="2" stroke-linecap="round"
                                          stroke-linejoin="round"></path>
                                    <circle cx="8.66" cy="8" r="8" fill="#5733FF" fill-opacity="0.24"></circle>
                                </svg>
                            </div>
                            <h3>Language Flexibility</h3>
                            <p>Users have the option to choose the language in which the alt text is generated. This ensures that the generated descriptions are relevant and appropriate for the target audience.</p>
                        </div>
                        <div class="toc-custom-landing-single-info">
                            <div>
                                <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
                                    <path d="M4 4V3H3V4H4ZM20 4H21V3H20V4ZM6.29289 11.2929C5.90237 11.6834 5.90237 12.3166 6.29289 12.7071C6.68342 13.0976 7.31658 13.0976 7.70711 12.7071L6.29289 11.2929ZM10 9L10.7071 8.29289C10.3166 7.90237 9.68342 7.90237 9.29289 8.29289L10 9ZM13 12L12.2929 12.7071C12.6834 13.0976 13.3166 13.0976 13.7071 12.7071L13 12ZM17.7071 8.70711C18.0976 8.31658 18.0976 7.68342 17.7071 7.29289C17.3166 6.90237 16.6834 6.90237 16.2929 7.29289L17.7071 8.70711ZM7.29289 20.2929C6.90237 20.6834 6.90237 21.3166 7.29289 21.7071C7.68342 22.0976 8.31658 22.0976 8.70711 21.7071L7.29289 20.2929ZM12 17L12.7071 16.2929C12.3166 15.9024 11.6834 15.9024 11.2929 16.2929L12 17ZM15.2929 21.7071C15.6834 22.0976 16.3166 22.0976 16.7071 21.7071C17.0976 21.3166 17.0976 20.6834 16.7071 20.2929L15.2929 21.7071ZM3 3C2.44772 3 2 3.44772 2 4C2 4.55228 2.44772 5 3 5V3ZM21 5C21.5523 5 22 4.55228 22 4C22 3.44772 21.5523 3 21 3V5ZM4 5H20V3H4V5ZM19 4V16H21V4H19ZM19 16H5V18H19V16ZM5 16V4H3V16H5ZM5 16H3C3 17.1046 3.89543 18 5 18V16ZM19 16V18C20.1046 18 21 17.1046 21 16H19ZM7.70711 12.7071L10.7071 9.70711L9.29289 8.29289L6.29289 11.2929L7.70711 12.7071ZM9.29289 9.70711L12.2929 12.7071L13.7071 11.2929L10.7071 8.29289L9.29289 9.70711ZM13.7071 12.7071L17.7071 8.70711L16.2929 7.29289L12.2929 11.2929L13.7071 12.7071ZM8.70711 21.7071L12.7071 17.7071L11.2929 16.2929L7.29289 20.2929L8.70711 21.7071ZM11.2929 17.7071L15.2929 21.7071L16.7071 20.2929L12.7071 16.2929L11.2929 17.7071ZM3 5H21V3H3V5Z"
                                          fill="#334155"></path>
                                    <circle cx="8" cy="8" r="8" fill="#5733FF" fill-opacity="0.24"></circle>
                                </svg>
                            </div>
                            <h3>Automated Titles & Captions</h3>
                            <p>Enhance your Image SEO further by setting custom automated titles, captions, and descriptions for your images generated by AI.</p>
                        </div>
                    </div>
                    <div class="toc-custom-landing-single-wrap">
                        <div class="toc-custom-landing-single-info">
                            <div>
                                <svg width="21" height="22" viewBox="0 0 21 22" fill="none">
                                    <path d="M12 3L12.4472 2.10557C12.1657 1.96481 11.8343 1.96481 11.5528 2.10557L12 3ZM20 7H21C21 6.62123 20.786 6.27496 20.4472 6.10557L20 7ZM4 7L3.55279 6.10557C3.214 6.27496 3 6.62123 3 7H4ZM20 17L20.4472 17.8944C20.786 17.725 21 17.3788 21 17H20ZM12 21L11.5528 21.8944C11.8343 22.0352 12.1657 22.0352 12.4472 21.8944L12 21ZM4 17H3C3 17.3788 3.214 17.725 3.55279 17.8944L4 17ZM11.5528 3.89443L19.5528 7.89443L20.4472 6.10557L12.4472 2.10557L11.5528 3.89443ZM19.5528 6.10557L11.5528 10.1056L12.4472 11.8944L20.4472 7.89443L19.5528 6.10557ZM12.4472 10.1056L4.44721 6.10557L3.55279 7.89443L11.5528 11.8944L12.4472 10.1056ZM4.44721 7.89443L12.4472 3.89443L11.5528 2.10557L3.55279 6.10557L4.44721 7.89443ZM19.5528 16.1056L11.5528 20.1056L12.4472 21.8944L20.4472 17.8944L19.5528 16.1056ZM12.4472 20.1056L4.44721 16.1056L3.55279 17.8944L11.5528 21.8944L12.4472 20.1056ZM5 17V7H3V17H5ZM21 17V7H19V17H21ZM11 11V21H13V11H11Z"
                                          fill="#334155"></path>
                                    <circle cx="8.33" cy="8" r="8" fill="#5733FF" fill-opacity="0.24"></circle>
                                </svg>
                            </div>
                            <h3>Prefix or Suffix Addition</h3>
                            <p>Add a unique touch to your alt text with customizable prefixes or suffixes. This feature is great for branding or adding specific keywords.</p>
                        </div>
                        <div class="toc-custom-landing-single-info">
                            <div>
                                <svg width="23" height="22" viewBox="0 0 23 22" fill="none">
                                    <path d="M13 8V12L16 15M22 12C22 16.9706 17.9706 21 13 21C8.02944 21 4 16.9706 4 12C4 7.02944 8.02944 3 13 3C17.9706 3 22 7.02944 22 12Z"
                                          stroke="#334155" stroke-width="2" stroke-linecap="round"
                                          stroke-linejoin="round"></path>
                                    <circle cx="8.66" cy="8" r="8" fill="#5733FF" fill-opacity="0.24"></circle>
                                </svg>
                            </div>
                            <h3>Designed specifically for WordPress</h3>
                            <p>It's a WordPress plugin. Installing and using it is simple and you can use all existing images with it.</p>
                        </div>
                    </div>
                </div>
                <div class="toc-custom-landing-bottom">
                    <h2>Your Every Image Deserves an Alt Text<br>
                        Let's use AI to make it. </h2>
                        <a href="#" class="toc-custom-landing-install-btn" data-target-url = "<?php echo $target_url; ?>">
                            <span class="toc-custom-landing-install-btn-txt">Install Ai Alt Text - Free</span>
                            <span class="toc-custom-landing-install-btn-icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M3.8335 13.3346L3.8335 14.168C3.8335 15.5487 4.95278 16.668 6.3335 16.668L14.6668 16.668C16.0475 16.668 17.1668 15.5487 17.1668 14.168L17.1668 13.3346M13.8335 10.0013L10.5002 13.3346M10.5002 13.3346L7.16683 10.0013M10.5002 13.3346L10.5002 3.33464" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                        </a>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
}


