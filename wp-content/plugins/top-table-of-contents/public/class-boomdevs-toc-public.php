<?php

require_once BOOMDEVS_TOC_PATH . 'includes/class-boomdevs-toc-settings.php';
require_once BOOMDEVS_TOC_PATH . 'includes/class-boomdevs-toc-utils.php';

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://boomdevs.com/
 * @since      1.0.0
 *
 * @package    Boomdevs_Toc
 * @subpackage Boomdevs_Toc/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Boomdevs_Toc
 * @subpackage Boomdevs_Toc/public
 * @author     BoomDevs <admin@boomdevs.com>
 */
class Boomdevs_Toc_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Boomdevs_Toc_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Boomdevs_Toc_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        $settings = Boomdevs_Toc_Settings::get_settings();
        $layout_border_radius_top        = $settings['layout_border_radius_top'];
        $layout_border_radius_right      = $settings['layout_border_radius_right'];
        $layout_border_radius_bottom     = $settings['layout_border_radius_bottom'];
        $layout_border_radius_left       = $settings['layout_border_radius_left'];
        $layout_shadow_horizontal_length = $settings['horizontal_length'];
        $layout_shadow_vertical_length   = $settings['vertical_length'];
        $layout_shadow_blur_radius       = $settings['blur_radius'];
        $layout_shadow_spread_radius     = $settings['spread_radius'];
        $layout_shadow_color             = $settings['shadow_color'];
        $title_border_radius_top         = $settings['title_border_radius_top'];
        $title_border_radius_right       = $settings['title_border_radius_right'];
        $title_border_radius_bottom      = $settings['title_border_radius_bottom'];
        $title_border_radius_left        = $settings['title_border_radius_left'];
        $heading_border_radius_top       = $settings['heading_border_radius_top'];
        $heading_border_radius_right     = $settings['heading_border_radius_right'];
        $heading_border_radius_bottom    = $settings['heading_border_radius_bottom'];
        $heading_border_radius_left      = $settings['heading_border_radius_left'];
        $title_show_hide                 = $settings['title_show_hide_switcher'];
        $icon_show_hide                  = $settings['icon_show_hide_switcher'];
        $top_level                       = $settings['heading_top_level'] ? $settings['heading_top_level'] : 1;
        $toggle_icon_color               = $settings['toggle_icon_color'];
        $toggle_icon_hover_color         = $settings['toggle_icon_hover_color'];
        $heading_disc_bg                 = $settings['heading_font_color'];
        $heading_disc_top                = $settings['heading_padding']['top'];
        $heading_disc_left               = $settings['heading_padding']['left'];
        $heading_word_break              = $settings['heading_word_break'];
        $initial_view                    = $settings['initial_view'];

        if (Boomdevs_Toc_Utils::isProActivated()) {

            $heading_toggle_icon_position         = $settings['heading_toggle_icon_position'] === "right" ? $settings['heading_toggle_icon_position_right'] : $settings['heading_toggle_icon_position_left'];
            $sub_heading_toggle_icon_position     = $settings['sub_heading_toggle_icon_position'] === "right" ? $settings['sub_heading_toggle_icon_position_right'] : $settings['sub_heading_toggle_icon_position_left'];
            $side_button_bg                       = $settings['layout_slide_button_background_color'];
            $layout_slide_button_icon_color       = $settings['layout_slide_button_icon_color'];
            $heading_toggle_icon_position_top     = $settings['heading_toggle_icon_position_top'];
            $show_heading_toggle_icon             = $settings['show_heading_toggle_icon'];
            $heading_toggle_icon_color            = $settings['heading_toggle_icon_color'];
            $heading_toggle_icon_hover_color      = $settings['heading_toggle_icon_hover_color'];
            $heading_toggle_icon_active_color     = $settings['heading_toggle_icon_active_color'];
            $show_sub_heading_toggle_icon         = $settings['show_sub_heading_toggle_icon'];
            $sub_heading_toggle_icon_position_top = $settings['sub_heading_toggle_icon_position_top'];
            $sub_heading_border_radius_top        = $settings['sub_heading_border_radius_top'];
            $sub_heading_border_radius_right      = $settings['sub_heading_border_radius_right'];
            $sub_heading_border_radius_bottom     = $settings['sub_heading_border_radius_bottom'];
            $sub_heading_border_radius_left       = $settings['sub_heading_border_radius_left'];
            $sub_heading_toggle_icon_color        = $settings['sub_heading_toggle_icon_color'];
            $sub_heading_toggle_icon_hover_color  = $settings['sub_heading_toggle_icon_hover_color'];
            $sub_heading_toggle_icon_active_color = $settings['sub_heading_toggle_icon_active_color'];
            $fiexd_layout_width                   = $settings['fiexd_layout_width']['width'];
            $sticky_mode_position                 = $settings['sticky_mode_position'];
            $sub_heading_text_align               = $settings['sub_heading_font_family'];
            $fiexd_layout_zindex                  = $settings['fiexd_layout_zindex'];
            $sticky_Sidebar_offset                = $settings['bd_toc_sticky_Sidebar_offset'];
            $layout_slide_button_offset           = $settings['layout_slide_button_offset'];

            $widget_floating_position                   = $settings['widget_floating_position'];
            $widget_floating_position_top               = $settings['widget_floating_position_top'];
            $widget_floating_position_top_left          = $settings['widget_floating_position_top_left'];
            $widget_floating_position_top_right         = $settings['widget_floating_position_top_right'];
            $widget_floating_position_bottom            = $settings['widget_floating_position_bottom'];
            $widget_floating_position_bottom_left       = $settings['widget_floating_position_bottom_left'];
            $widget_floating_position_bottom_right      = $settings['widget_floating_position_bottom_right'];

            $widget_floating_title_border_radius_top        = $settings['widget_floating_title_border_radius_top'];
            $widget_floating_title_border_radius_right      = $settings['widget_floating_title_border_radius_right'];
            $widget_floating_title_border_radius_bottom     = $settings['widget_floating_title_border_radius_bottom'];
            $widget_floating_title_border_radius_left       = $settings['widget_floating_title_border_radius_left'];

            $widget_floating_content_border_radius_top      = $settings['widget_floating_content_border_radius_top'];
            $widget_floating_content_border_radius_right    = $settings['widget_floating_content_border_radius_right'];
            $widget_floating_content_border_radius_bottom   = $settings['widget_floating_content_border_radius_bottom'];
            $widget_floating_content_border_radius_left     = $settings['widget_floating_content_border_radius_left'];

            $widget_floating_horizontal_length              = $settings['widget_floating_horizontal_length'];
            $widget_floating_vertical_length                = $settings['widget_floating_vertical_length'];
            $widget_floating_blur_radius                    = $settings['widget_floating_blur_radius'];
            $widget_floating_spread_radius                  = $settings['widget_floating_spread_radius'];
            $widget_floating_shadow_color                   = $settings['widget_floating_shadow_color'];

            $widget_floating_content_disc_bg                = $settings['widget_floating_content_font_color'];
            $widget_floating_content_disc_top               = $settings['widget_floating_content_padding']['top'];
            $widget_floating_content_disc_left              = $settings['widget_floating_content_padding']['left'];

            $widget_floating_nav_left_arrow_position        = $settings['widget_floating_nav_left_arrow_position'];
            $widget_floating_nav_right_arrow_position       = $settings['widget_floating_nav_right_arrow_position'];
            $widget_floating_title_position                 = $settings['widget_floating_title_position'];

            $bd_toc_progress_bar_position                   = $settings['bd_toc_progress_bar_position'];
            $bd_toc_progress_bar_thickness                  = $settings['bd_toc_progress_bar_thickness'];
            $bd_toc_progress_bar_border_radius_right        = $settings['bd_toc_progress_bar_border_radius_right'];
            $bd_toc_progress_bar_border_radius_bottom       = $settings['bd_toc_progress_bar_border_radius_bottom'];

            $sticky_sidebar_collapse_on_off                 = $settings['sticky_sidebar_collapse_on_off'];
            
            if($sub_heading_text_align['text-align'] == 'right'){?>
                <style>
                    .bd_toc_container .bd_toc_wrapper .bd_toc_content_list_item ul li>ul {
                        padding-left: 0;
                        padding-right: 15px;
                    }
                </style>
                <?php
            }
            if( $sticky_mode_position == 'right'){?>
                <style>
                    .bd_toc_container.scroll-to-fixed-fixed.stickyInSide {
                        right: -<?php echo esc_html($fiexd_layout_width); ?>px !important;
                    }
                </style>
                <?php
            }elseif($sticky_mode_position == 'left'){?>
                <style>
                    .bd_toc_container.scroll-to-fixed-fixed.stickyInSide {
                        left: -<?php echo esc_html($fiexd_layout_width); ?>px !important;
                    }
                </style>
                <?php
            }

            if( $sticky_mode_position == 'right'){?>
                <style>
                    .bd_toc_container.scroll-to-fixed-fixed {
                        left: auto !important;
                        right: 0 ;
                        -webkit-animation: fadein .5s ease-in-out;
                        transition: none !important;
                    }
                    .bd_toc_container .layout_toggle_button {
                        left: calc(0% - 124px);
                        transform: rotate(90deg) !important;
                    }
                    @media (max-width: 767px) {
                        .bd_toc_container .layout_toggle_button {
                            left: calc(0% - 62px);
                        }
                    }
                    @keyframes fadein {
                        from { opacity: 0; }
                        to   { opacity: 1; }
                    }
                </style>
                <?php
            }else{?>
                <style>
                    .bd_toc_container.scroll-to-fixed-fixed {
                        left: 0 !important;
                        right: auto;
                        -webkit-animation: fadein .5s ease-in-out;
                        transition: none !important;
                    }
                    @keyframes fadein {
                        from { opacity: 0; }
                        to   { opacity: 1; }
                    }
                </style>
                <?php
            }

            if($sticky_sidebar_collapse_on_off == '1'){
                ?>
                <style>
                    .bd_toc_container.scroll-to-fixed-fixed {
                        left: -<?php echo esc_html($fiexd_layout_width); ?>px !important;
                    }
                </style>
                <?php
            }

            if($widget_floating_position == 'top'){?>
                <style>
                    .bd_toc_widget_floating {
                        top: <?php echo esc_html($widget_floating_position_top); ?>px !important;
                        left: <?php echo esc_html($widget_floating_position_top_left); ?>px !important;
                        right: <?php echo esc_html($widget_floating_position_top_right); ?>px !important;
                    }
                </style>
                <?php
            }else {
                ?>
                <style>
                    .bd_toc_widget_floating {
                        bottom: <?php echo esc_html($widget_floating_position_bottom); ?>px !important;
                        left: <?php echo esc_html($widget_floating_position_bottom_left); ?>px !important;
                        right: <?php echo esc_html($widget_floating_position_bottom_right); ?>px !important;
                    }
                </style>
                <?php
            }

            if($bd_toc_progress_bar_position == 'top'){?>
                <style>
                    .bd_toc_progress_bar.progress_bar_open .bd_toc_widget_progress_bar {
                        top: 0px !important;
                        height:<?php echo esc_html($bd_toc_progress_bar_thickness); ?>px !important;
                    }
                </style>
            <?php
            }else {
                ?>
                <style>
                    .bd_toc_progress_bar.progress_bar_open .bd_toc_widget_progress_bar {
                        bottom: 0px !important;
                        height: <?php echo esc_html($bd_toc_progress_bar_thickness); ?>px !important;
                    }
                </style>
                <?php
            } 
            ?>
            <style>

                .bd_toc_container {
                    z-index: <?php echo esc_html($fiexd_layout_zindex); ?> !important;
                }
                .bd_toc_container.scroll-to-fixed-fixed {
                    top: <?php echo esc_html($sticky_Sidebar_offset); ?>px !important;
                }
                .bd_toc_container .bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list.heading_toggle_icon .bd_toc_content_list_item > ul > li >.collaps-button {
                <?php echo esc_html($settings['heading_toggle_icon_position']); ?>: <?php echo esc_html($heading_toggle_icon_position); ?>px;
                    top: <?php echo esc_html($heading_toggle_icon_position_top); ?>px
                }
                .bd_toc_container .bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list.sub_heading_toggle_icon .bd_toc_content_list_item ul li ul li>.collaps-button {
                <?php echo esc_html($settings['sub_heading_toggle_icon_position']); ?>: <?php echo esc_html($sub_heading_toggle_icon_position); ?>px;
                    top: <?php echo esc_html($sub_heading_toggle_icon_position_top); ?>px
                }
                .bd_toc_container .layout_toggle_button{
                    border-color: <?php echo esc_html($side_button_bg); ?> transparent !important;
                }
                .bd_toc_container .layout_toggle_button .bd_toc_arrow {
                    border-color: <?php echo esc_html($layout_slide_button_icon_color); ?>;
                }
                .heading_toggle_icon .bd_toc_content_list_item > ul > li >.collaps-button .bd_toc_arrow {
                    border-color: <?php echo esc_html($heading_toggle_icon_color); ?>;
                }
                .heading_toggle_icon .bd_toc_content_list_item > ul > li >.collaps-button:hover .bd_toc_arrow {
                    border-color: <?php echo esc_html($heading_toggle_icon_hover_color); ?>;
                }
                .heading_toggle_icon .bd_toc_content_list_item > ul > li.active.current >.collaps-button .bd_toc_arrow {
                    border-color: <?php echo esc_html($heading_toggle_icon_active_color); ?>;
                }
                .sub_heading_toggle_icon .bd_toc_content_list_item ul li ul li>.collaps-button .bd_toc_arrow {
                    border-color: <?php echo esc_html($sub_heading_toggle_icon_color); ?>;
                }
                .sub_heading_toggle_icon .bd_toc_content_list_item ul li ul li>.collaps-button:hover .bd_toc_arrow {
                    border-color: <?php echo esc_html($sub_heading_toggle_icon_hover_color); ?>;
                }
                .sub_heading_toggle_icon .bd_toc_content_list_item ul li ul li.active.current>.collaps-button .bd_toc_arrow {
                    border-color: <?php echo esc_html($sub_heading_toggle_icon_active_color); ?>;
                }
                .bd_toc_container.scroll-to-fixed-fixed {
                    width: <?php echo esc_html($fiexd_layout_width); ?>px !important;
                }
                .bd_toc_widget_item .bd_toc_widget_nav_prev {
                    order: <?php echo esc_html($widget_floating_nav_left_arrow_position); ?>
                }
                .bd_toc_widget_item .bd_toc_widget_nav_next {
                    order: <?php echo esc_html($widget_floating_nav_right_arrow_position); ?>
                }
                .bd_toc_widget_item .current_list_item {
                    order: <?php echo esc_html($widget_floating_title_position); ?>
                }
                .bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list ul li ul li a {
                    border-top-left-radius:     <?php echo esc_html($sub_heading_border_radius_top); ?>px;
                    border-top-right-radius:    <?php echo esc_html($sub_heading_border_radius_right); ?>px;
                    border-bottom-right-radius: <?php echo esc_html($sub_heading_border_radius_bottom); ?>px;
                    border-bottom-left-radius:  <?php echo esc_html($sub_heading_border_radius_left); ?>px;
                }

                .bd_toc_widget_floating {
                    border-top-left-radius:     <?php echo esc_html($widget_floating_title_border_radius_top); ?>px;
                    border-top-right-radius:    <?php echo esc_html($widget_floating_title_border_radius_right); ?>px;
                    border-bottom-right-radius: <?php echo esc_html($widget_floating_title_border_radius_bottom); ?>px;
                    border-bottom-left-radius:  <?php echo esc_html($widget_floating_title_border_radius_left); ?>px;
                }
                .bd_toc_widget_floating {
                    box-shadow: <?php echo esc_html($widget_floating_horizontal_length); ?>px
                                <?php echo esc_html($widget_floating_vertical_length); ?>px
                                <?php echo esc_html($widget_floating_blur_radius); ?>px
                                <?php echo esc_html($widget_floating_spread_radius); ?>px
                <?php echo esc_html($widget_floating_shadow_color); ?>;
                }
                .bd_toc_widget_floating .bd_toc_floating_content {
                    border-top-left-radius:     <?php echo esc_html($widget_floating_content_border_radius_top); ?>px;
                    border-top-right-radius:    <?php echo esc_html($widget_floating_content_border_radius_right); ?>px;
                    border-bottom-right-radius: <?php echo esc_html($widget_floating_content_border_radius_bottom); ?>px;
                    border-bottom-left-radius:  <?php echo esc_html($widget_floating_content_border_radius_left); ?>px;
                }
                .bd_toc_progress_bar.progress_bar_open .bd_toc_widget_progress_bar {
                    border-top-right-radius:    <?php echo esc_html($bd_toc_progress_bar_border_radius_right); ?>px;
                    border-bottom-right-radius: <?php echo esc_html($bd_toc_progress_bar_border_radius_bottom); ?>px;
                }
                .bd_toc_widget_floating .bd_toc_floating_content.list-type-disc .bd_toc_content_floating_list_item ul li a:before {
                    background-color: <?php echo esc_html($widget_floating_content_disc_bg); ?>;
                    top: calc( <?php echo esc_html($widget_floating_content_disc_top); ?>px + 7px );
                    left: calc( <?php echo esc_html($widget_floating_content_disc_left); ?>px - 0px );
                }
                .bd_toc_container .layout_toggle_button{
                    bottom: calc(<?php echo esc_html($layout_slide_button_offset); ?>% + 200px)!important
                }
                /** For Mobile Device */
                @media (max-width: 767px) {

                    .bd_toc_widget_item{
                        position: relative;
                    }
                    .overlay .floating_toc_bg_overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%; z-index: 1; background: transparent; }
                    .bd_toc_widget_nav_overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: transparent; z-index: 1; }

                    .bd_toc_floating_content {
                        position: relative; z-index: 2; padding: 20px 0 !important;
                    }
                    .bd_toc_widget_nav_prev, .bd_toc_widget_nav_next { position: relative; z-index: 2; }

                }
                /** For Mobile Device */

            </style>
            <?php
        }

        if($heading_word_break == 'word_break'){?>
            <style>
                .bd_toc_container .bd_toc_wrapper .bd_toc_content_list_item ul li a {
                    white-space: break-spaces !important;
                }
            </style>
            <?php
        }
        
        if($initial_view == '0'){
            ?>
            <style>
                .fit_content {
                    width: fit-content;
                }
                body .bd_toc_container {
                    transition: all 0.5s ease-in-out !important;
                }
                .bd_toc_header_title {
                    padding-right: 10px !important;
                }
                .bd_toc_container .bd_toc_wrapper .bd_toc_content {
                    display: none;
                }
            </style>
            <?php
        }

        ?>
        <style>
            .bd_toc_container {
                transition: ease-in-out .5s !important;
            }
            .bd_toc_container {
                border-top-left-radius:     <?php echo esc_html($layout_border_radius_top); ?>px;
                border-top-right-radius:    <?php echo esc_html($layout_border_radius_right); ?>px;
                border-bottom-right-radius: <?php echo esc_html($layout_border_radius_bottom); ?>px;
                border-bottom-left-radius:  <?php echo esc_html($layout_border_radius_left); ?>px;
            }
            .bd_toc_container {
                box-shadow: <?php echo esc_html($layout_shadow_horizontal_length); ?>px
                            <?php echo esc_html($layout_shadow_vertical_length); ?>px
                            <?php echo esc_html($layout_shadow_blur_radius); ?>px
                            <?php echo esc_html($layout_shadow_spread_radius); ?>px
            <?php echo esc_html($layout_shadow_color); ?>;
            }
            .bd_toc_container.scroll-to-fixed-fixed {
                margin: 0 !important;
            }
            .bd_toc_wrapper .bd_toc_header .bd_toc_switcher_hide_show_icon .bd_toc_arrow {
                border-color: <?php echo esc_html($toggle_icon_color); ?>
            }
            .bd_toc_wrapper .bd_toc_header:hover .bd_toc_switcher_hide_show_icon .bd_toc_arrow {
                border-color: <?php echo esc_html($toggle_icon_hover_color); ?>
            }
            .bd_toc_header {
                border-top-left-radius:     <?php echo esc_html($title_border_radius_top); ?>px;
                border-top-right-radius:    <?php echo esc_html($title_border_radius_right); ?>px;
                border-bottom-right-radius: <?php echo esc_html($title_border_radius_bottom); ?>px;
                border-bottom-left-radius:  <?php echo esc_html($title_border_radius_left); ?>px;
            }
            .bd_toc_wrapper .bd_toc_wrapper_item .bd_toc_content .bd_toc_content_list ul li a {
                border-top-left-radius:     <?php echo esc_html($heading_border_radius_top); ?>px;
                border-top-right-radius:    <?php echo esc_html($heading_border_radius_right); ?>px;
                border-bottom-right-radius: <?php echo esc_html($heading_border_radius_bottom); ?>px;
                border-bottom-left-radius:  <?php echo esc_html($heading_border_radius_left); ?>px;
            }
            .bd_toc_container .bd_toc_wrapper .bd_toc_content.list-type-disc ul li a:before {
                background-color: <?php echo esc_html($heading_disc_bg); ?>;
                top: calc( <?php echo esc_html($heading_disc_top); ?>px + 7px );
                left: calc( <?php echo esc_html($heading_disc_left); ?>px - 12px );
            }

        </style>
        <?php

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/boomdevs-toc-public.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(  $this->plugin_name.'one-page-nav', plugin_dir_url( __FILE__ ) . 'js/jquery.nav.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/boomdevs-toc-public.js', array( 'jquery' ), $this->version, true );

        if ( Boomdevs_Toc_Utils::isProActivated() ) {
            $settings = Boomdevs_Toc_Settings::get_settings();

            wp_localize_script( $this->plugin_name, 'sticky_mode_position', array(
                'sticky_mode_position' => $settings['sticky_mode_position'],
                'sticky_sidebar_collapse_on_off' => $settings['sticky_sidebar_collapse_on_off'],
            ) );

            wp_localize_script( $this->plugin_name, 'progress_bar_switcher', array(
                'progress_bar_switcher'     => $settings['bd_toc_progress_bar_switcher'],
            ) );

            wp_localize_script( $this->plugin_name, 'widget_floating_option', array(
                'widget_floating_option' => $settings['widget_floating_option']
            ) );
    
            wp_localize_script( $this->plugin_name, 'widget_floating_nav', array(
                'widget_floating_nav' => $settings['widget_floating_nav']
            ) );

            wp_localize_script( $this->plugin_name, 'widget_floating_content', array(
                'widget_floating_content'       => $settings['widget_floating_content'],
                'widget_floating_position'      => $settings['widget_floating_position'],
                'title_border_radius_top'       => $settings['widget_floating_title_border_radius_top'],
                'title_border_radius_right'     => $settings['widget_floating_title_border_radius_right'],
                'title_border_radius_bottom'    => $settings['widget_floating_title_border_radius_bottom'],
                'title_border_radius_left'      => $settings['widget_floating_title_border_radius_left'],
            ) );
        }

        $settings = Boomdevs_Toc_Settings::get_settings();

        wp_localize_script(  $this->plugin_name.'one-page-nav', 'page_nav', array(
            'offset_from_top' => $settings['offset_from_top'],
        ) );
        
        wp_localize_script( $this->plugin_name, 'handle', array(
            'initial_view'              => $settings['initial_view'],
            'isProActivated'              => Boomdevs_Toc_Utils::isProActivated(),
        ) );

    }

}
