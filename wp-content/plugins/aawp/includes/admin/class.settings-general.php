<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'AAWP_Settings_General' ) ) {

    class AAWP_Settings_General extends AAWP_Functions {

        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            // Call parent constructor first
            parent::__construct();

            if ( ! aawp_is_license_valid() )
                return;

            // Setup identifier
            $this->func_order = 20;
            $this->func_id = 'general';
            $this->func_name = __('General', 'aawp');

            // Settings functions
            add_filter( $this->settings_tabs_filter, array( &$this, 'add_settings_tabs_filter' ) );
            add_filter( $this->settings_functions_filter, array( &$this, 'add_settings_functions_filter' ) );

            add_action( 'admin_init', array( &$this, 'add_settings' ) );
        }

        /**
         * Add settings functions
         */
        public function add_settings_tabs_filter( $tabs ) {

            $tabs[$this->func_order] = array(
                'key' => $this->func_id,
                'icon' => 'admin-generic',
                'title' => __('General', 'aawp')
            );

            return $tabs;
        }

        public function add_settings_functions_filter( $functions ) {

            $functions[] = $this->func_id;

            return $functions;
        }

        /**
         * Settings: Register section and fields
         */
        public function add_settings() {

            register_setting( 'aawp_general', 'aawp_general' );

            add_settings_section(
                'aawp_general_section',
                __( 'Global settings', 'aawp' ),
                array( &$this, 'settings_general_section_callback' ),
                'aawp_general'
            );

            add_settings_field(
                'aawp_shortcode',
                __( 'Shortcode', 'aawp' ),
                array( &$this, 'settings_shortcode_render' ),
                'aawp_general',
                'aawp_general_section',
                array('label_for' => 'aawp_shortcode')
            );

            add_settings_field(
                'aawp_cache',
                __( 'Cache', 'aawp' ),
                array( &$this, 'settings_cache_render' ),
                'aawp_general',
                'aawp_general_section',
                array('label_for' => 'aawp_cache_duration')
            );

	        add_settings_field(
                'aawp_affiliate_links',
                __( 'Affiliate links', 'aawp' ),
                array( &$this, 'settings_affiliate_links_render' ),
                'aawp_general',
                'aawp_general_section',
                array('label_for' => 'aawp_affiliate_links')
            );

            /*
             * Action to add more settings within this section
             */
            do_action( 'aawp_settings_general_register' );

            add_settings_field(
                'aawp_theme_support_shortcodes',
                __( 'Theme Support: Shortcodes', 'aawp' ),
                array( &$this, 'settings_theme_support_shortcodes_render' ),
                'aawp_general',
                'aawp_general_section'
            );

            add_settings_field(
                'aawp_last_update_format',
                __( 'Last update format', 'aawp' ),
                array( &$this, 'settings_last_update_format_render' ),
                'aawp_general',
                'aawp_general_section',
                array('label_for' => 'aawp_last_update_format')
            );

            add_settings_field(
                'aawp_inline_info',
                __( 'Inline info', 'aawp' ),
                array( &$this, 'settings_inline_info_render' ),
                'aawp_general',
                'aawp_general_section',
                array('label_for' => 'aawp_inline_info')
            );

            add_settings_field(
                'aawp_disclaimer',
                __( 'Disclaimer', 'aawp' ),
                array( &$this, 'settings_disclaimer_render' ),
                'aawp_general',
                'aawp_general_section',
                array('label_for' => 'aawp_disclaimer_position')
            );

            add_settings_field(
                'aawp_cleanup_shortcode_output',
                __( 'Cleanup shortcode output', 'aawp' ),
                array( &$this, 'settings_cleanup_shortcode_output_render' ),
                'aawp_general',
                'aawp_general_section',
                array('label_for' => 'aawp_cleanup_shortcode_output')
            );

            add_settings_field(
                'aawp_credits',
                __( 'Credits', 'aawp' ),
                array( &$this, 'settings_credits_render' ),
                'aawp_general',
                'aawp_general_section',
                array('label_for' => 'aawp_credits_position')
            );
        }

        /**
         * Settings callbacks
         */
        public function settings_general_section_callback(  ) {

            echo __( 'Here you can specify the general settings.', 'aawp' );

        }

        public function settings_shortcode_render() {

            $shortcode_options = array(
                'amazon' => '[amazon]',
                'aawp' => '[aawp]',
                'disabled' => __( 'Disabled', 'aawp' )
            );

            ?>
            <select id="aawp_shortcode" name="aawp_general[shortcode]">
                <?php foreach ( $shortcode_options as $value => $label ) { ?>
                    <option value="<?php echo $value; ?>" <?php selected( AAWP_SHORTCODE, $value ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>
            <?php
        }

        public function settings_cache_render() {

            $durations = array(
                720 => __('12 Hours', 'aawp'),
                1440 => __('1 Day', 'aawp'),
                4320 => __('3 Days', 'aawp'),
                10080 => __('1 Week', 'aawp'),
            );

            $duration = ( isset ( $this->options['general']['cache_duration'] ) ) ? $this->options['general']['cache_duration'] : '720';

            $disable_database_garbage_collection = ( isset( $this->options['general']['disable_database_garbage_collection'] ) && $this->options['general']['disable_database_garbage_collection'] == '1' ) ? 1 : 0;
            ?>
            <!-- Cache duration -->
            <h4 class="first"><?php _e('Cache duration', 'aawp'); ?></h4>
            <p>
                <select id="aawp_cache_duration" name="aawp_general[cache_duration]">
                    <?php foreach ( $durations as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $duration, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </p>
            <h4><?php _e( 'Database Garbage Colection', 'aawp' ); ?></h4>
            <p>
                <?php _e( 'The plugin automatically takes care that old and thus no longer needed products and lists are regularly removed from the database. This function helps to reduce the load on your database and also ensures that the Amazon API\'s request limits are not exceeded.', 'aawp' ); ?>
            </p>
            <p>
                <input type="checkbox" id="aawp_disable_database_garbage_collection" name="aawp_general[disable_database_garbage_collection]" value="1" <?php echo( $disable_database_garbage_collection == 1 ? 'checked' : ''); ?>>
                <label for="aawp_disable_database_garbage_collection"><?php _e('Check in order to <u>disable</u> the built in database garbage collection', 'aawp'); ?></label>
            </p>
            <p>
                <?php printf( wp_kses( __( 'More information about the database garbage collection can be found in our <a href="%s" target="_blank">documentation</a>.', 'aawp' ), array(  'a' => array( 'href' => array(), 'target' => '_blank' ) ) ), esc_url( aawp_get_page_url( 'docs:database_garbage_collection' ) ) ); ?>
            </p>
            <?php
            /*
            $smart_caching = ( isset ( $this->options['general']['smart_caching'] ) && $this->options['general']['smart_caching'] == '1' ) ? 1 : 0;
            ?>
            <h4><?php _e('Smart Caching', 'aawp'); ?> <small style="color: coral">(<?php _e('New feature which is still in test phase', 'aawp'); ?>)</small></h4>
            <p>
                <input type="checkbox" id="aawp_smart_caching" name="aawp_general[smart_caching]" value="1" <?php echo($smart_caching == 1 ? 'checked' : ''); ?>>
                <label for="aawp_smart_caching"><?php _e('Check in order to enable the built-in Smart Caching functionality', 'aawp'); ?></label>
            </p>
            <p>
                <small><?php _e('Smart Caching is an intelligent and resource-saving routine which checks wether a product/list needs to be update or not.', 'aawp'); ?></small>
            </p>
            <?php */ ?>

            <?php if ( aawp_is_product_local_images_enabled() ) { ?>
                <?php $local_images = ( isset( $this->options['general']['local_images'] ) && $this->options['general']['local_images'] == '1' ) ? 1 : 0; ?>
                <h4><?php _e('Images', 'aawp'); ?></h4>
                <p>
                    <input type="checkbox" id="aawp_local_images" name="aawp_general[local_images]" value="1" <?php echo($local_images == 1 ? 'checked' : ''); ?>>
                    <label for="aawp_local_images"><?php _e('Check in order to download images and host them locally instead of using Amazon servers', 'aawp'); ?></label>
                </p>
                <p>
                    <small><strong><?php _e( 'Important note:', 'aawp'); ?></strong>&nbsp;<?php _e( "Due to the terms and conditions of Amazon Associates it's not allowed to store images on your server. The use of this feature is on your responsibility and may lead into being banned from their affiliate program.", 'aawp'); ?></small>
                </p>
            <?php } ?>
            <?php
        }

        public function settings_affiliate_links_render() {

            $affiliate_link_types = array(
                'standard' => __('Standard', 'aawp'),
                //'shorted' => __('Shorted', 'aawp')
            );

            $affiliate_link_types = apply_filters( 'aawp_settings_affiliate_link_types', $affiliate_link_types );

            ?>
            <select id="aawp_affiliate_links" name="aawp_general[affiliate_links]">
                <?php foreach ( $affiliate_link_types as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $this->affiliate_links, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>

            <?php /*
            <h4><?php _e('Comparison of the different affiliate links', 'aawp'); ?></h4>
            <table class="widefat aawp-settings-table">
                <thead>
                    <tr>
                        <th><?php _e('Type', 'aawp'); ?></th>
                        <th><?php _e('Example', 'aawp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php _e('Standard', 'aawp'); ?></td>
                        <td style="word-break: break-all;">http://www.amazon.<?php echo ( ! empty( $this->api_country ) ) ? $this->api_country : 'com'; ?>/gp/product/B00UJ3M47A/ref=as_li_tl?ie=UTF8&camp=1638&creative=19454&creativeASIN=B00UJ3M47A&linkCode=as2&tag=aawp-21</td>
                    </tr>
                    <tr>
                        <td><?php _e('Shorted', 'aawp'); ?></td>
                        <td>http://www.amazon.<?php echo ( ! empty( $this->api_country ) ) ? $this->api_country : 'com'; ?>/dp/B00UJ3M47A/?tag=aawp-21</td>
                    </tr>
                    <tr>
                        <td><?php _e('Cloaked', 'aawp'); ?><br /><small style="font-weight: bold; color: cornflowerblue;"><?php _e('Coming soon!', 'aawp'); ?></small></td>
                        <td>http://amzn.to/22EExvK</td>
                    </tr>

                </tbody>
            </table>
             */ ?>
            <?php

            do_action( 'aawp_settings_general_affiliate_links' );
        }

        // Shortcodes support
        public function settings_theme_support_shortcodes_render() {

            $text_widget = ( isset ( $this->options['general']['theme_support_text_widget'] ) && $this->options['general']['theme_support_text_widget'] == '1' ) ? 1 : 0;
            $term_description = ( isset ( $this->options['general']['theme_support_term_description'] ) && $this->options['general']['theme_support_term_description'] == '1' ) ? 1 : 0;

            ?>
            <p>
                <input type="checkbox" id="aawp_theme_support_text_widget" name="aawp_general[theme_support_text_widget]" value="1" <?php echo($text_widget == 1 ? 'checked' : ''); ?> />
                <label for="aawp_theme_support_text_widget"><?php _e('Check in order to execute shortcodes within text widgets', 'aawp'); ?></label>
            </p>
            <p>
                <input type="checkbox" id="aawp_theme_support_term_description" name="aawp_general[theme_support_term_description]" value="1" <?php echo($term_description == 1 ? 'checked' : ''); ?> />
                <label for="aawp_theme_support_term_description"><?php _e('Check in order to execute shortcodes within term/category/archive descriptions', 'aawp'); ?></label>
            </p>
            <?php
        }

        public function settings_last_update_format_render() {

            $last_update_formats = array(
                'date' => __('Date', 'aawp'),
                'date_time' => __('Date and time', 'aawp')
            );

            $last_update_format = ( isset ( $this->options['general']['last_update_format'] ) ) ? $this->options['general']['last_update_format'] : 'date';
            ?>
            <select id="aawp_last_update_format" name="aawp_general[last_update_format]">
                <?php foreach ( $last_update_formats as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $last_update_format, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>
            <?php
        }

        public function settings_inline_info_render() {

            $inline_info_choices = array(
                '0' => __('Disabled', 'aawp'),
                'show' => __('Show info text within each single product box', 'aawp')
            );

            $inline_info = ( isset ( $this->options['general']['inline_info'] ) ) ? $this->options['general']['inline_info'] : '0';
            $inline_info_text = ( isset ( $this->options['general']['inline_info_text'] ) ) ? stripslashes($this->options['general']['inline_info_text']) : __('Price incl. tax, excl. shipping', 'aawp');

            ?>
            <select id="aawp_inline_info" name="aawp_general[inline_info]">
                <?php foreach ( $inline_info_choices as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $inline_info, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>
            <?php
            wp_editor($inline_info_text, 'aawp_inline_info_text', array(
                'media_buttons' => false,
                'textarea_name' => 'aawp_general[inline_info_text]',
                'textarea_rows' => 1,
            ));
            ?>
            <p>
                <small><?php _e('<strong>Note:</strong> The placeholder %last_update% can be used here.', 'aawp'); ?></small>
            </p>
            <?php
        }

        public function settings_disclaimer_render() {

            $disclaimer_positions = array(
                '0' => __('Disabled', 'aawp'),
                'after' => __('Show disclaimer after each single product or list', 'aawp'),
                'bottom' => __('Show disclaimer once at the bottom of the page', 'aawp'),
            );

            $link = '<a href="' . __('https://affiliate-program.amazon.com/gp/advertising/api/detail/agreement.html', 'aawp') . '"
                target="_blank"
                title="' . __('Amazon Product Advertising API License Agreement', 'aawp') . '">' . __('Amazon Product Advertising API License Agreement', 'aawp') . '</a>';
            // TODO: https://partnernet.amazon.de/gp/associates/promo/comparisonshoppingsepde

            $disclaimer_position = ( isset ( $this->options['general']['disclaimer_position'] ) ) ? $this->options['general']['disclaimer_position'] : '0';
            $disclaimer_text = ( isset ( $this->options['general']['disclaimer_text'] ) ) ? stripslashes($this->options['general']['disclaimer_text']) : __('Last update on %last_update% / Affiliate links / Images from Amazon Product Advertising API', 'aawp');
            $disclaimer_widget = ( isset ( $this->options['general']['disclaimer_widget'] ) && $this->options['general']['disclaimer_widget'] == '1' ) ? 1 : 0;

            ?>
            <p>
                <label for="aawp_disclaimer_position">
                    <?php _e('Amazon advised to include a disclaimer adjacent to the pricing or availability information.', 'aawp') ?> <?php echo $link; ?>
                </label>
            </p>
            <br />
            <p>
                <select id="aawp_disclaimer_position" name="aawp_general[disclaimer_position]">
                    <?php foreach ( $disclaimer_positions as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $disclaimer_position, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </p>
            <p>
                <input type="checkbox" id="aawp_disclaimer_widget" name="aawp_general[disclaimer_widget]" value="1" <?php echo($disclaimer_widget == 1 ? 'checked' : ''); ?> />
                <label for="aawp_disclaimer_widget"><?php _e('Check in order to show disclaimer after widgets in any case', 'aawp'); ?></label>
            </p>

            <?php
            wp_editor($disclaimer_text, 'aawp_disclaimer_text', array(
                'media_buttons' => false,
                'textarea_name' => 'aawp_general[disclaimer_text]',
                'textarea_rows' => 2,
            ));
            ?>
            <p>
                <small><?php _e('<strong>Note:</strong> The placeholder %last_update% can be used here.', 'aawp'); ?></small>
            </p>

            <?php
        }

        public function settings_cleanup_shortcode_output_render() {

            $cleanup_shortcode_output = ( isset ( $this->options['general']['cleanup_shortcode_output'] ) && $this->options['general']['cleanup_shortcode_output'] == '1' ) ? 1 : 0;

            ?>
            <p>
                <input type="checkbox" id="aawp_cleanup_shortcode_output" name="aawp_general[cleanup_shortcode_output]" value="1" <?php echo($cleanup_shortcode_output == 1 ? 'checked' : ''); ?> />
                <label for="aawp_cleanup_shortcode_output"><?php _e('Check only in case shortcodes produce unneeded spacing, line-breaks or empty paragraphs.', 'aawp'); ?></label>
            </p>
            <?php
        }

        public function settings_credits_render() {

            $credits_positions = array(
                '0' => __('Disabled', 'aawp'),
                'after' => __('Show credits after each single product or list.', 'aawp'),
                'bottom' => __('Show credits once at the bottom of the page.', 'aawp'),
            );

            $credits_position = ( isset ( $this->options['general']['credits_position'] ) ) ? $this->options['general']['credits_position'] : '0';
            $affiliate_id = ( ! empty ( $this->options['general']['affiliate_id'] ) ) ? $this->options['general']['affiliate_id'] : '';
            ?>

            <p>
                <label for="aawp_credits_position"><?php _e('Feel free to spread it to the world if you are happy with this plugin and want to support me :)', 'aawp') ?></label>
            </p>
            <br />
            <select id="aawp_credits_position" name="aawp_general[credits_position]">
                <?php foreach ( $credits_positions as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $credits_position, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>

            <h4><?php _e('Affiliate ID', 'aawp'); ?></h4>
            <p>
                <?php printf( wp_kses( __( 'As a member of our <a href="%s" target="_blank">affiliate partner program</a>, you will receive a commission for every new AAWP customer you refer. If you wish, you can enter your affiliate ID in the field below. As soon as you have activated the "Credits" function above, your Affiliate ID will automatically be appended to the generated link.', 'aawp' ), array(  'a' => array( 'href' => array(), 'target' => '_blank' ) ) ), esc_url( aawp_get_page_url( 'affiliates' ) ) ); ?>
            </p>
            <p>
                <input type="number" id="aawp_affiliate_id" name="aawp_general[affiliate_id]" value="<?php echo $affiliate_id; ?>" />
            </p>
            <?php
        }
    }

    new AAWP_Settings_General();
}