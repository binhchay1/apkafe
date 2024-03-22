<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'AAWP_Settings_Output' ) ) {

    class AAWP_Settings_Output extends AAWP_Functions {

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
            $this->func_order = 25;
            $this->func_id = 'output';
            $this->func_name = __('Output', 'aawp');

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
                'icon' => 'feedback',
                'title' => __('Output', 'aawp')
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

            register_setting(
                'aawp_output',
                'aawp_output',
                array( &$this, 'settings_output_callback')
            );

            add_settings_section(
                'aawp_output_section',
                __( 'Output settings', 'aawp' ),
                array( &$this, 'settings_output_section_callback' ),
                'aawp_output'
            );

            add_settings_field(
                'aawp_titles',
                __( 'Title', 'aawp' ),
                array( &$this, 'settings_title_render' ),
                'aawp_output',
                'aawp_output_section'
            );

            add_settings_field(
                'aawp_thumbnail',
                __( 'Thumbnail', 'aawp' ),
                array( &$this, 'settings_thumbnail_render' ),
                'aawp_output',
                'aawp_output_section'
            );

            add_settings_field(
                'aawp_description',
                __( 'Description', 'aawp' ),
                array( &$this, 'settings_description_render' ),
                'aawp_output',
                'aawp_output_section'
            );

            add_settings_field(
                'aawp_rating_size',
                __( 'Rating', 'aawp' ),
                array( &$this, 'settings_rating_render' ),
                'aawp_output',
                'aawp_output_section'
            );

            add_settings_field(
                'aawp_pricing',
                __( 'Pricing', 'aawp' ),
                array( &$this, 'settings_pricing_render' ),
                'aawp_output',
                'aawp_output_section'
            );

            add_settings_field(
                'aawp_button',
                __( 'Button', 'aawp' ),
                array( &$this, 'settings_button_render' ),
                'aawp_output',
                'aawp_output_section'
            );

            /*
             * Action to add more settings within this section
             */
            do_action( 'aawp_settings_output_register' );

            // After action fields
            add_settings_field(
                'aawp_custom_css',
                __( 'Custom CSS', 'aawp' ),
                array( &$this, 'settings_custom_css_render' ),
                'aawp_output',
                'aawp_output_section',
                array('label_for' => 'aawp_custom_css')
            );
        }

        /**
         * Settings callbacks
         */

        public function settings_output_callback( $input ) {

            if ( $this->options['output']['star_rating_size'] == '0' && $this->options['output']['show_reviews'] && ( $input['star_rating_size'] != '0' || $input['show_reviews'] != '0' ) ) {
                aawp_renew_cache();
            }

            return $input;
        }

        public function settings_output_section_callback(  ) {

            echo __( 'Here you can specify the output settings.', 'aawp' );

        }

        public function settings_title_render() {

            ?>
            <!-- Limit length -->
            <h4 class="first"><?php _e('Limit length', 'aawp'); ?></h4>
            <p>
                <input type="checkbox" id="aawp_title_length_unlimited" name="aawp_output[title_length_unlimited]" value="1" <?php echo($this->title_length_unlimited == 1 ? 'checked' : ''); ?> data-aawp-toggle-on-change="aawp_title_length_extend">
                <label for="aawp_title_length_unlimited"><?php _e('Unlimited', 'aawp'); ?></label>

                <span id="aawp_title_length_extend"<?php if ($this->title_length_unlimited == '1') echo ' style="display: none;"'; ?>>
                    <input type="range" id="aawp_title_length" class="aawp-vertical-align" name="aawp_output[title_length]"
                           value="<?php echo $this->title_length; ?>" min="0" max="255" step="5" data-aawp-range-slider="aawp_title_length_value" />
                    <span id="aawp_title_length_value" class="aawp-vertical-align"><?php echo $this->title_length; ?></span> <label for="aawp_title_length"><?php _e('Characters', 'aawp'); ?></label>
                </span>
                <?php $this->do_admin_note('shortcode'); ?>
            </p>

            <!-- Adding -->
            <h4><?php _e('Adding', 'aawp'); ?></h4>
            <p>
                <input type="text" id="aawp_title_adding" name="aawp_output[title_adding]" value="<?php echo $this->title_adding; ?>" /><br />
                <small><?php _e('Use this field to show additional content at the end of <u>every</u> product title: e.g. <code>*</code> in order to refer to a footnote.', 'aawp' ) ?></small>
            </p>
            <p>
                <input type="checkbox" id="aawp_image_link_title_adding" name="aawp_output[image_link_title_adding]" value="1" <?php echo($this->image_link_title_adding == 1 ? 'checked' : ''); ?>> <label for="aawp_image_link_title_adding"><?php _e('Apply adding on image link titles', 'aawp'); ?></label>
            </p>

            <?php
        }

        public function settings_thumbnail_render() {

	        $proxy = ( isset( $this->options['output']['image_proxy'] ) && $this->options['output']['image_proxy'] == '1' ) ? 1 : 0;
            $size = ( isset( $this->options['output']['image_size'] ) ) ? $this->options['output']['image_size'] : 'medium';
            $quality = ( isset( $this->options['output']['image_quality'] ) ) ? $this->options['output']['image_quality'] : 'standard';

            $allow_url_fopen_activated = ( ini_get('allow_url_fopen') ) ? true : false;
            $proxy_disabled = ( ! $proxy && ! $allow_url_fopen_activated ) ? true : false;
            ?>
            <h4 class="first"><?php _e('Image Proxy', 'aawp'); ?> (<a href="<?php echo ( aawp_is_lang_de() ) ? 'https://aawp.de/docs/article/dsgvo/' : 'https://getaawp.com/docs/article/gdpr/'; ?>" target="_blank" rel="nofollow"><?php _e('GDPR relevant', 'aawp'); ?></a>)</h4>
	        <?php if ( ! $allow_url_fopen_activated ) { ?>
                <p>
                    <span style="color: red;"><?php _e('PHP "allow_url_fopen" is not activated on your server. Please activate it on your own or get in touch with the support of your hosting provider.', 'aawp' ); ?></span>
                </p>
	        <?php } ?>
            <p>
                <input type="checkbox" id="aawp_image_proxy" name="aawp_output[image_proxy]" value="1" <?php echo($proxy == 1 ? 'checked' : ''); ?><?php if ( $proxy_disabled ) echo ' disabled="disabled"'; ?>>
                <label for="aawp_image_proxy"><?php _e('Check in order to deliver images via a privacy proxy', 'aawp'); ?></label>
            </p>
            <p>
                <small>
                    <?php _e('This way Amazon will not receive the IP addresses of your visitors, as they would, when you embed images directly.', 'aawp'); ?>
	                <?php $proxy_docs_url = ( aawp_is_lang_de() ) ? 'https://aawp.de/docs/article/produktbilder-proxy/' : 'https://getaawp.com/docs/article/image-proxy/'; ?>
                    <?php printf( wp_kses( __( 'For more information please <a href="%s" target="_blank">take a look at this article</a>.', 'aawp' ), array(  'a' => array( 'href' => array(), 'target' => '_blank' ) ) ), esc_url( $proxy_docs_url ) ); ?>
                </small>
            </p>

            <!-- Size -->
            <h4><?php _e('Image Size', 'aawp'); ?></h4>
            <?php
            $sizes = array(
                'small' => __('Small', 'aawp'),
                'medium' => __('Medium', 'aawp'),
                'large' => __('Large', 'aawp')
            );
            ?>
            <select id="aawp_image_size" name="aawp_output[image_size]">
                <?php foreach ( $sizes as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $size, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>
            <p>
                <small><?php _e('In the end it depends on the selected template how it handles the final image size.', 'aawp'); ?></small>
            </p>

            <!-- Quality -->
            <h4><?php _e('Image Quality', 'aawp'); ?></h4>
            <?php
            $sizes = array(
                'standard' => __('Standard', 'aawp'),
                'high' => __('High', 'aawp')
            );
            ?>
            <select id="aawp_image_quality" name="aawp_output[image_quality]">
                <?php foreach ( $sizes as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $quality, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>
            <?php
        }

        public function settings_description_render() {

            $show_mobile = ( isset( $this->options['output']['description_show_mobile'] ) && $this->options['output']['description_show_mobile'] == '1' ) ? 1 : 0;
            $this->teaser = ( isset ( $this->options['output']['teaser'] ) && 'hide' === $this->options['output']['teaser'] ) ? 'hide' : 'show';

            ?>
            <!-- Description items -->
            <h4 class="first"><?php _e('Number of list points', 'aawp'); ?></h4>
            <p>
                <input type="number" id="aawp_description_items" name="aawp_output[description_items]" value="<?php echo $this->description_items; ?>" />
                <?php $this->do_admin_note('shortcode'); ?>
            </p>
            <p>
                <small><?php _e('There might be only a lower amount of description items available.', 'aawp'); ?></small>
            </p>

            <!-- Length -->
            <h4><?php _e('Limit length for each list point', 'aawp'); ?></h4>
            <p>
                <input type="checkbox" id="aawp_description_length_unlimited" name="aawp_output[description_length_unlimited]" value="1" <?php echo($this->description_length_unlimited == 1 ? 'checked' : ''); ?> data-aawp-toggle-on-change="aawp_description_length_extend">
                <label for="aawp_description_length_unlimited"><?php _e('Unlimited', 'aawp'); ?></label>

                <span id="aawp_description_length_extend"<?php if ($this->description_length_unlimited == '1') echo ' style="display: none;"'; ?>>
                    <input type="range" id="aawp_description_length" class="aawp-vertical-align" name="aawp_output[description_length]"
                           value="<?php echo $this->description_length; ?>" min="0" max="255" step="5" data-aawp-range-slider="aawp_description_length_value" />
                    <span id="aawp_description_length_value" class="aawp-vertical-align"><?php echo $this->description_length; ?></span> <label for="aawp_description_length"><?php _e('Characters', 'aawp'); ?></label>
                </span>
                <?php $this->do_admin_note('shortcode'); ?>
            </p>

            <!-- HTML Formatting -->
            <h4><?php _e('HTML formatting', 'aawp'); ?></h4>
            <p>
                <input id="aawp_description_html_enable" type="radio" name="aawp_output[description_html]" value="1" <?php checked('1', $this->description_html); ?> />
                <label for="aawp_description_html_enable"><?php _e('Enabled', 'aawp'); ?></label> &nbsp;
                <input id="aawp_description_html_disable" type="radio" name="aawp_output[description_html]" value="0" <?php checked('0', $this->description_html); ?> />
                <label for="aawp_description_html_disable"><?php _e('Disabled', 'aawp'); ?></label>
            </p>

            <!-- Responsive -->
            <h4><?php _e('Responsive', 'aawp'); ?></h4>
            <p>
                <input type="checkbox" id="aawp_description_show_mobile" name="aawp_output[description_show_mobile]" value="1" <?php echo($show_mobile == 1 ? 'checked' : ''); ?>>
                <label for="aawp_description_show_mobile"><?php _e('Check in order to show descriptions on mobile devices too', 'aawp'); ?></label>
            </p>
            <p>
                <small><?php _e('<strong>Note:</strong> This option might not be valid for all templates.', 'aawp'); ?></small>
            </p>

            <!-- Template: List -->
            <h4><?php _e('List template', 'aawp'); ?></h4>
            <?php
            $teaser_options = array(
                'show' => __( 'Show description', 'aawp' ),
                'hide' => __( 'Hide description', 'aawp' )
            );
            ?>
            <select id="aawp_teaser" name="aawp_output[teaser]">
                <?php foreach ( $teaser_options as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $this->teaser, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>
            <?php $this->do_admin_note('shortcode'); ?>
            <p>
                <?php _e('Define the maximum length of the description text in the "list" template.', 'aawp' ); ?>
            </p>
            <p>
                <input type="checkbox" id="aawp_teaser_length_unlimited" name="aawp_output[teaser_length_unlimited]" value="1" <?php echo($this->teaser_length_unlimited == 1 ? 'checked' : ''); ?> data-aawp-toggle-on-change="aawp_teaser_length_extend">
                <label for="aawp_teaser_length_unlimited"><?php _e('Unlimited', 'aawp'); ?></label>

                <span id="aawp_teaser_length_extend"<?php if ($this->teaser_length_unlimited == '1') echo ' style="display: none;"'; ?>>
                    <input type="range" id="aawp_teaser_length" class="aawp-vertical-align" name="aawp_output[teaser_length]"
                           value="<?php echo $this->teaser_length; ?>" min="0" max="255" step="5" data-aawp-range-slider="aawp_teaser_length_value" />
                    <span id="aawp_teaser_length_value" class="aawp-vertical-align"><?php echo $this->teaser_length; ?></span> <label for="aawp_teaser_length"><?php _e('Characters', 'aawp'); ?></label>
                </span>
                <?php $this->do_admin_note('shortcode'); ?>
            </p>
            <?php
        }

        public function settings_rating_render() {

            ?>
            <!-- Star Rating -->
            <h4 class="first"><?php _e('Star Rating', 'aawp'); ?></h4>
            <?php
            $sizes = array(
                '0' => __('Hide', 'aawp'),
                'small' => __('Small', 'aawp'),
                'medium' => __('Medium', 'aawp'),
                'large' => __('Large', 'aawp')
            );
            ?>
            <label for="aawp_star_rating_size"><?php _e('Size:', 'aawp'); ?></label>
            <select id="aawp_star_rating_size" name="aawp_output[star_rating_size]" data-aawp-star-rating-update-preview="true">
                <?php foreach ( $sizes as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $this->star_rating_size, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>

            <?php do_action( 'aawp_settings_output_star_rating_render' ); ?>

            <?php $star_rating_preview_classes = apply_filters( 'aawp_settings_star_rating_preview_classes', 'aawp-star-rating aawp-star-rating--' . $this->star_rating_size ); ?>

            &nbsp; <span class="<?php echo $star_rating_preview_classes; ?>" data-aawp-star-rating-preview="true"><span style="width=80%;"></span></span>
            <?php $this->do_admin_note('shortcode'); ?>

            <div id="aawp-star-rating-notes">
                <?php do_action( 'aawp_settings_output_star_rating_notes_render' ); ?>
            </div>

            <!-- Star Rating Link -->
            <h4><?php _e('Link target', 'aawp'); ?></h4>
            <?php
            $link_targets = array(
                '0' => __('Hide', 'aawp'),
                'detail_page' => __('Product details', 'aawp'),
                'reviews' => __('Reviews', 'aawp')
            );
            ?>
            <select id="aawp_star_rating_link" name="aawp_output[star_rating_link]">
                <?php foreach ( $link_targets as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $this->star_rating_link, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>
            <?php $this->do_admin_note('shortcode'); ?>
            <br />

            <!-- Reviews -->
            <h4><?php _e('Reviews', 'aawp'); ?></h4>
            <?php
            $reviews = array(
                '0' => __('Hide', 'aawp'),
                '1' => __('Show total amount', 'aawp')
            );
            $reviews_label = ( isset ( $this->options['output']['reviews_label'] ) ) ? $this->options['output']['reviews_label'] : __( 'Reviews', 'aawp' );
            ?>
            <select id="aawp_show_reviews" name="aawp_output[show_reviews]">
                <?php foreach ( $reviews as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $this->show_reviews, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>
            <p>
                <?php _e('Reviews label', 'aawp'); ?>: <input type="text" class="regular-text" id="aawp_pricing_reviews_label" name="aawp_output[reviews_label]" value="<?php echo $reviews_label; ?>" />
            </p>
            <?php
            $ratings_info_url = ( aawp_is_lang_de() ) ? 'https://aawp.de/docs/article/sterne-bewertungen-erscheinen-nicht/' : 'https://getaawp.com/docs/article/star-ratings-reviews-missing/';
            ?>
            <p>
                <small><strong><?php _e('Note:', 'aawp' ); ?></strong> <?php printf( wp_kses( __( 'In case some ratings do not show up correctly, please <a href="%s" target="_blank">take a look at this article</a>.', 'aawp' ), array(  'a' => array( 'href' => array(), 'target' => '_blank' ) ) ), esc_url( $ratings_info_url ) ); ?></small>
            </p>
            <?php

            do_action( 'aawp_settings_output_rating_render' );
        }

        public function settings_pricing_render() {

            $display_price = array(
                'hidden' => __('Hide advertised price', 'aawp'),
                'standard' => __('Standard', 'aawp')
            );

            ?>
            <!-- Advertised price -->
            <h4 class="first"><?php _e('Advertised price', 'aawp'); ?></h4>
            <select id="aawp_pricing_display" name="aawp_output[pricing_advertised_price]">
                <?php foreach ( $display_price as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $this->pricing_advertised_price, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>

            <?php $advertised_price_hide_unavailability = ( isset ( $this->options['output']['pricing_advertised_price_hide_unavailability'] ) && $this->options['output']['pricing_advertised_price_hide_unavailability'] == '1' ) ? 1 : 0; ?>
            <p>
                <input type="checkbox" id="aawp_pricing_advertised_price_hide_unavailability" name="aawp_output[pricing_advertised_price_hide_unavailability]" value="1" <?php echo($advertised_price_hide_unavailability == 1 ? 'checked' : ''); ?>>
                <label for="aawp_pricing_advertised_price_hide_unavailability"><?php _e("Don't show any messages when pricing is not available", 'aawp'); ?></label>
            </p>

            <!-- Price reduction -->
            <?php
            $reduction_options = array(
                //'hidden' => __('Hide all and show final price only', 'aawp'),
                'amount' => __('Total amount (incl. Currency)', 'aawp'),
                'percentage' => __('Percentage', 'aawp')
            );

            $show_old_price = ( isset ( $this->options['output']['pricing_show_old_price'] ) && $this->options['output']['pricing_show_old_price'] == '1' ) ? 1 : 0;
            $show_price_reduction = ( isset ( $this->options['output']['pricing_show_price_reduction'] ) && $this->options['output']['pricing_show_price_reduction'] == '1' ) ? 1 : 0;
            $sale_ribbon_text = ( isset ( $this->options['output']['pricing_sale_ribbon_text'] ) ) ? $this->options['output']['pricing_sale_ribbon_text'] : __( 'Sale', 'aawp' );
            ?>
            <h4><?php _e('Price reduction', 'aawp'); ?></h4>
            <select id="aawp_pricing_reduction" name="aawp_output[pricing_reduction]">
                <?php foreach ( $reduction_options as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $this->pricing_reduction, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>
            <p>
                <input type="checkbox" id="aawp_pricing_show_old_price" name="aawp_output[pricing_show_old_price]" value="1" <?php echo($show_old_price == 1 ? 'checked' : ''); ?>>
                <label for="aawp_pricing_show_old_price"><?php _e("Check in order to show old price", 'aawp'); ?></label>
            </p>
            <p>
                <input type="checkbox" id="aawp_pricing_show_price_reduction" name="aawp_output[pricing_show_price_reduction]" value="1" <?php echo($show_price_reduction == 1 ? 'checked' : ''); ?>>
                <label for="aawp_pricing_show_price_reduction"><?php _e("Check in order to show price reduction", 'aawp'); ?></label>
            </p>
            <p>
                <?php _e('Sale label', 'aawp'); ?>: <input type="text" class="regular-text" id="aawp_pricing_sale_ribbon_text" name="aawp_output[pricing_sale_ribbon_text]" value="<?php echo $sale_ribbon_text; ?>" />
                <?php $this->do_admin_note('shortcode'); ?>
            </p>
            <p>
                <?php aawp_admin_display_placeholders_note( array( 'price_reduction' ) ); ?>
            </p>

            <!-- Currency Format -->
            <?php
            if ( $this->api_country && in_array( $this->api_country, $this->euro_countries ) ) {

                $currency_formats = array(
                    'EUR' => sprintf( esc_html__( 'Declaration in %1$s', 'aawp' ), 'EUR' ),
                    '€' => sprintf( esc_html__( 'Declaration in %1$s', 'aawp' ), '€' )
                );

                $currency_format = ( isset ( $this->options['output']['pricing_currency_format'] ) ) ? $this->options['output']['pricing_currency_format'] : 'EUR';

                ?>
                <!-- Currency Format -->
                <h4><?php _e('Currency Format', 'aawp'); ?></h4>
                <select id="aawp_pricing_currency_format" name="aawp_output[pricing_currency_format]">
                    <?php foreach ( $currency_formats as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $currency_format, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
                <?php
            }

            do_action( 'aawp_settings_output_pricing_render' );
        }

        public function settings_button_render() {

            $button_hide = ( isset ( $this->options['output']['button_hide'] ) && $this->options['output']['button_hide'] == '1' ) ? 1 : 0;
            $button_text = ( !empty( $this->button_text ) ) ? $this->button_text : __('Buy on Amazon', 'aawp');

            $button_icon_hide = ( isset ( $this->options['output']['button_icon_hide'] ) && $this->options['output']['button_icon_hide'] == '1' ) ? 1 : 0;
            ?>
            <!-- Preview -->
            <h4 class="first"><?php _e('Preview', 'aawp'); ?></h4>
            <?php $classes = 'aawp-button aawp-button--' . $this->button_style; ?>
            <?php $classes .= ' aawp-button--icon-' . $this->button_icon; ?>
            <?php if( ! $button_icon_hide ) { $classes .= ' aawp-button--icon'; } ?>
            <a id="aawp-button-preview" href="#aawp-button-preview" class="<?php echo apply_filters( 'aawp_settings_button_preview_classes', $classes, 'standard' ); ?>" data-aawp-button-style="<?php echo $this->button_style; ?>">
                <span><?php echo $button_text; ?></span>
            </a>

            <!-- Hide Button -->
            <input type="checkbox" id="aawp_button_hide" name="aawp_output[button_hide]" value="1" <?php echo($button_hide == 1 ? 'checked' : ''); ?>>
            <label for="aawp_button_hide"><?php _e('Hide button', 'aawp'); ?></label>

            <!-- Style -->
            <?php
            $styles = array(
                'standard' => __('Standard', 'aawp'),
                'amazon' => __('Amazon', 'aawp')
            );

            $styles = apply_filters( 'aawp_settings_button_styles', $styles );
            ?>
            <h4><?php _e('Style', 'aawp'); ?></h4>
            <select id="aawp_button_style" name="aawp_output[button_style]" data-aawp-button-preview-style="aawp-button-preview">
                <?php foreach ( $styles as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $this->button_style, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>
            <?php do_action( 'aawp_settings_button_style' ); ?>

            <!-- Icon -->
            <?php
            $icons = array(
                'amazon-black' => __('Amazon: Black', 'aawp'),
                'amazon-white' => __('Amazon: White', 'aawp'),
                'black' => __('Cart: Black', 'aawp'),
                'white' => __('Cart: White', 'aawp')
            );
            ?>
            <h4><?php _e('Icon', 'aawp'); ?></h4>
            <select id="aawp_button_icon" name="aawp_output[button_icon]">
                <?php foreach ( $icons as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $this->button_icon, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>

            <input type="checkbox" id="aawp_button_icon_hide" name="aawp_output[button_icon_hide]" value="1" <?php echo($button_icon_hide == 1 ? 'checked' : ''); ?> data-aawp-button-icon-hide-preview="aawp-button-preview">
            <label for="aawp_button_icon_hide"><?php _e('Hide icon', 'aawp'); ?></label>

            <!-- Text -->
            <h4><?php _e('Label', 'aawp'); ?></h4>
            <input type="text" id="aawp_button_text" name="aawp_output[button_text]" value="<?php echo esc_html( $button_text ); ?>" data-aawp-button-preview-text="aawp-button-preview" />
            <?php $this->do_admin_note('shortcode'); ?>
            <?php

            do_action( 'aawp_settings_output_button_render' );
        }

        public function settings_custom_css_render() {

            $custom_css_activated = ( isset ( $this->options['output']['custom_css_activated'] ) && $this->options['output']['custom_css_activated'] == '1' ) ? 1 : 0;
            $custom_css = ( !empty ( $this->options['output']['custom_css'] ) ) ? $this->options['output']['custom_css'] : '';
            ?>

            <p>
                <input type="checkbox" id="aawp_custom_css_activated" name="aawp_output[custom_css_activated]" value="1" <?php echo($custom_css_activated == 1 ? 'checked' : ''); ?>>
                <label for="aawp_custom_css_activated"><?php _e('Output custom CSS styles', 'aawp'); ?></label>
            </p>
            <br />
            <textarea id="aawp_custom_css" name="aawp_output[custom_css]" rows="10" cols="80" style="width: 100%;"><?php echo esc_html( $custom_css );//echo stripslashes($custom_css); ?></textarea>
            <p>
                <small><?php _e("Please don't use the <code>style</code> tag. Simply paste you CSS classes/definitions e.g. <code>.aawp .aawp-product { background-color: #000; }</code> or <code>.aawp .aawp-product--horizontal { background-color: #000; }</code>", 'aawp' ) ?></small>
            </p>

            <?php
        }
    }

    new AAWP_Settings_Output();
}