<?php

/*

Copyright 2014 Dario Curvino (email : d.curvino@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>
*/
if ( !defined( 'ABSPATH' ) ) {
    exit( 'You\'re not allowed to see this page' );
}
// Exit if accessed directly
/**
 * @since 2.4.7
 *
 * Setting screen
 *
 * Class YasrSettings
 */
class YasrSettings
{
    /**
     * Init Settings
     */
    public function init()
    {
        $yasr_general_options = new YasrSettingsGeneral();
        $yasr_general_options->init();
        $yasr_style_settings = new YasrSettingsStyle();
        $yasr_style_settings->init();
        $yasr_multiset = new YasrSettingsMultiset();
        $yasr_multiset->init();
        //add ajax endpoint to preview the rankings
        add_action( 'wp_ajax_yasr_rankings_preview_shortcode', array( 'YasrSettingsRankings', 'rankingPreview' ) );
        $yasr_import_plugin = new YasrImportRatingPlugins();
        //add ajax actions
        $yasr_import_plugin->addAjaxActions();
        $yasr_settings_footer = new YasrSettingsFooter();
        $yasr_settings_footer->init();
    }
    
    /**
     * @author       Dario Curvino <@dudo>
     * @since        2.4.7
     * @param        $elementsType_array
     * @param        $option
     * @param string $option_prefix
     */
    public static function echoSettingFields( $elementsType_array, $option, $option_prefix = 'yasr_general_options' )
    {
        $string_input = false;
        $type = false;
        foreach ( $elementsType_array as $property ) {
            //concatenate yasr_general_options with property name
            $element_name = $option_prefix . '[' . $property['name'] . ']';
            
            if ( isset( $property['type'] ) ) {
                
                if ( $property['type'] === 'select' ) {
                    $string_input = YasrPhpFieldsHelper::select(
                        '',
                        $property['label'],
                        $property['options'],
                        $property['name'],
                        '',
                        esc_attr( $option[$property['name']] )
                    );
                } elseif ( $property['type'] === 'textarea' ) {
                    $string_input = YasrPhpFieldsHelper::textArea(
                        '',
                        '',
                        $property['name'],
                        '',
                        '',
                        esc_textarea( $option[$property['name']] )
                    );
                }
            
            } else {
                $type = 'text';
                $placeholder = ( isset( $property['placeholder'] ) ? $property['placeholder'] : '' );
                //if description exists, add another <div> before
                $string_input = ( isset( $property['description'] ) && $property['description'] !== '' ? '<div>' : '' );
                $string_input .= YasrPhpFieldsHelper::text(
                    $property['class'],
                    '',
                    $element_name,
                    $property['id'],
                    $placeholder,
                    esc_attr( $option[$property['name']] )
                );
            }
            
            
            if ( isset( $property['description'] ) && $property['description'] !== '' ) {
                $string_input .= '<div class="yasr-element-row-container-description">';
                $string_input .= esc_html( $property['description'] );
                //if this is coming from "text field, close 2 divs"
                $string_input .= ( $type === 'text' ? '</div>' : '' );
                $string_input .= '</div>';
            }
            
            echo  yasr_kses( $string_input ) ;
        }
    }
    
    /**
     * Returns the radio buttons that allow to select stars size
     *
     * @param      $name
     * @param      $class
     * @param bool $db_value
     * @param bool $id
     * @param bool $txt_label
     * @param bool $newline
     * return string
     *
     * @since 2.3.3
     * @return string
     */
    public static function radioSelectSize(
        $name,
        $class,
        $db_value = false,
        $id = false,
        $txt_label = true,
        $newline = false
    )
    {
        $array_size = array( 'small', 'medium', 'large' );
        $span_label = '';
        $html_to_return = '';
        foreach ( $array_size as $size ) {
            $id_string = $id . $size;
            //must be inside for each, or when loop arrive to last element
            //checked is defined
            $checked = '';
            //If db_value === false, there is no need to check for db value
            //so checked is the medium star (i.e. ranking page)
            
            if ( $db_value === false ) {
                if ( $size === 'medium' ) {
                    $checked = 'checked';
                }
            } else {
                if ( $db_value === $size ) {
                    $checked = 'checked';
                }
            }
            
            
            if ( $txt_label !== false ) {
                $span_label = '<span class="yasr-text-options-size">' . __( ucwords( $size ), 'yet-another-stars-rating' ) . '</span>';
                if ( $newline !== false ) {
                    $span_label = '<br />' . $span_label;
                }
            }
            
            $src = YASR_IMG_DIR . 'yasr-stars-' . $size . '.png';
            $html_to_return .= sprintf(
                '<div class="yasr-option-div">
                                 <label for="%s">
                                     <input type="radio"
                                         name="%s"
                                         value="%s"
                                         class="%s"
                                         id="%s"
                                         %s
                                    >
                                     <img src="%s"
                                        class="yasr-img-option-size" alt=%s>
                                     %s
                                 </label>
                            </div>',
                $id_string,
                $name,
                $size,
                $class,
                $id_string,
                $checked,
                $src,
                $size,
                $span_label
            );
        }
        //end foreach
        return $html_to_return;
    }
    
    /**
     * Print settings tabs
     *
     * @param $active_tab
     *
     * @return void
     */
    public static function printTabs( $active_tab )
    {
        $rating_plugin_exists = new YasrImportRatingPlugins();
        $rating_plugin_exists->supportedPluginFound();
        ?>

        <h2 class="nav-tab-wrapper yasr-no-underline">

            <a href="?page=yasr_settings_page&tab=general_settings"
               id="general_settings"
               class="nav-tab <?php 
        if ( $active_tab === 'general_settings' ) {
            echo  'nav-tab-active' ;
        }
        ?>">
                <?php 
        esc_html_e( 'General Settings', 'yet-another-stars-rating' );
        ?>
            </a>

            <a href="?page=yasr_settings_page&tab=style_options"
               id="style_options"
               class="nav-tab <?php 
        if ( $active_tab === 'style_options' ) {
            echo  'nav-tab-active' ;
        }
        ?>">
                <?php 
        esc_html_e( 'Aspect & Styles', 'yet-another-stars-rating' );
        ?>
            </a>

            <a href="?page=yasr_settings_page&tab=manage_multi"
               id="manage_multi"
               class="nav-tab <?php 
        if ( $active_tab === 'manage_multi' ) {
            echo  'nav-tab-active' ;
        }
        ?>">
                <?php 
        esc_html_e( 'Multi Criteria', 'yet-another-stars-rating' );
        ?>
            </a>

            <a href="?page=yasr_settings_page&tab=rankings"
               id="rankings"
               class="nav-tab <?php 
        if ( $active_tab === 'rankings' ) {
            echo  'nav-tab-active' ;
        }
        ?>">
                <?php 
        esc_html_e( 'Rankings', 'yet-another-stars-rating' );
        ?>
            </a>

            <?php 
        /**
         * Hook here to add new settings tab
         */
        do_action( 'yasr_add_settings_tab', $active_tab );
        
        if ( defined( 'YASR_RATING_PLUGIN_FOUND' ) && YASR_RATING_PLUGIN_FOUND !== false ) {
            ?>
                    <a href="?page=yasr_settings_page&tab=migration_tools"
                       id="migration_tools"
                       class="nav-tab <?php 
            if ( $active_tab === 'migration_tools' ) {
                echo  'nav-tab-active' ;
            }
            ?>">
                        <?php 
            esc_html_e( 'Migration Tools', 'yet-another-stars-rating' );
            ?>
                    </a>
                    <?php 
        }
        
        ?>

        </h2>

        <?php 
    }
    
    /**
     * Print tabs content
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.1
     *
     * @param $active_tab
     *
     * @return void
     */
    public static function printTabsContent( $active_tab )
    {
        
        if ( $active_tab === 'general_settings' ) {
            ?>
            <form action="options.php" method="post" id="yasr_settings_form">
                <?php 
            settings_fields( 'yasr_general_options_group' );
            do_settings_sections( 'yasr_general_settings_tab' );
            submit_button( YASR_SAVE_All_SETTINGS_TEXT );
            ?>
            </form>
            <?php 
        }
        
        //End if tab 'general_settings'
        if ( $active_tab === 'manage_multi' ) {
            include YASR_ABSOLUTE_PATH_ADMIN . '/settings/yasr-settings-multiset.php';
        }
        //End if ($active_tab=='manage_multi')
        
        if ( $active_tab === 'style_options' ) {
            ?>
            <form action="options.php" method="post" enctype='multipart/form-data' id="yasr_settings_form">
                <?php 
            settings_fields( 'yasr_style_options_group' );
            do_settings_sections( 'yasr_style_tab' );
            submit_button( YASR_SAVE_All_SETTINGS_TEXT );
            ?>
            </form>
            <?php 
        }
        
        //End tab style
        if ( $active_tab === 'rankings' ) {
            include YASR_ABSOLUTE_PATH_ADMIN . '/settings/yasr-settings-rankings.php';
        }
        //End tab ur options
        if ( $active_tab === 'migration_tools' ) {
            //include migration functions
            include YASR_ABSOLUTE_PATH_ADMIN . '/settings/yasr-settings-migration.php';
        }
        //End tab migration
        /**
         * Hook here to add new settings tab content
         */
        do_action( 'yasr_settings_tab_content', $active_tab );
    }
    
    /**
     * Print a div with class "notice-success"
     * https://digwp.com/2016/05/wordpress-admin-notices/
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $message
     *
     * @since  3.1.7
     * @return void
     */
    public static function printNoticeSuccess( $message )
    {
        ?>
        <div class="notice notice-success">
            <p>
                <strong>
                    <?php 
        echo  esc_html( $message ) ;
        ?>
                </strong>
            </p>
        </div>
        <?php 
    }
    
    /**
     * Print a div with class "notice-error"
     * https://digwp.com/2016/05/wordpress-admin-notices/
     *
     * @author Dario Curvino <@dudo>
     *
     * @param string | array $message
     *
     * @since  3.1.7
     * @return void
     */
    public static function printNoticeError( $message )
    {
        ?>
        <div class="notice notice-error">
            <p>
                <strong>
                    <?php 
        
        if ( is_array( $message ) ) {
            foreach ( $message as $error ) {
                echo  esc_html( $error ) . '<br />' ;
            }
        } else {
            echo  esc_html( $message ) ;
        }
        
        ?>
                </strong>
            </p>
        </div>
        <?php 
    }
    
    /**
     * This is used as helper for the setting sanitize callback.
     * Check if a setting name exists, and if does, return the allowed value for that setting
     * If it doesn't, return default_value to avoid undefined index
     *
     * @author Dario Curvino <@dudo>
     * @since  3.4.1
     *
     * @param $settings_array      array  The entire setting array to check
     * @param $setting_name        string The setting name to check if exists inside $settings_array
     * @param $default_value       string|array The default value to return if $settings_array or $setting_name doesn't exists
     * @param $whitelisted_value   string|array The value to return if setting exists
     *
     * @return array|string
     */
    public static function whitelistSettings(
        $settings_array,
        $setting_name,
        $default_value,
        $whitelisted_value
    )
    {
        if ( !is_array( $settings_array ) ) {
            return $default_value;
        }
        
        if ( !array_key_exists( $setting_name, $settings_array ) ) {
            return $default_value;
        } else {
            return $whitelisted_value;
        }
    
    }

}