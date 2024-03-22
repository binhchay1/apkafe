<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'AAWP_Extended_Functions' ) ) {

    class AAWP_Extended_Functions extends AAWP_Functions {

        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            // Call parent constructor first
            parent::__construct();

            $this->add_settings();
            $this->hooks();

            // Settings functions
            add_filter( $this->settings_functions_filter, array( &$this, 'add_settings_functions_filter' ) );
        }

        /**
         * Add settings functions
         */
        public function add_settings_functions_filter( $functions ) {

            $functions[] = 'extended';

            return $functions;
        }

        /**
         * Settings: Register section and fields
         */
        public function add_settings() {

            add_action( 'aawp_settings_general_register', array( &$this, 'add_settings_general' ) );
        }

        public function add_settings_general() {

            /*
            add_settings_field(
                'aawp_link_cloaking',
                __( 'URL Shortener', 'aawp' ),
                array( &$this, 'settings_general_link_cloaking_render' ),
                'aawp_general',
                'aawp_general_section',
                array('label_for' => 'aawp_link_cloaking')
            );
            */
        }

        // Link Cloaking
        public function settings_general_link_cloaking_render() {

            $link_cloaking = ( isset ( $this->options['general']['link_cloaking'] ) && $this->options['general']['link_cloaking'] == '1' ) ? 1 : 0;

            $services = array(
                '0' => __('Disabled', 'aawp'),
                'tinyurl' => __('tinyurl.com', 'aawp')
            );

            ?>
            <p>
                <select id="aawp_link_cloaking" name="aawp_general[link_cloaking]">
                    <?php foreach ( $services as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $link_cloaking, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </p>
            <p>
                <small><?php echo sprintf( wp_kses( __( 'Please note that Amazon has not published an explicit statement yet, whether they allow shortened and therefore cloaked affiliate links or not. Making use of this functionality is on your own risk and might result into a exclusion of their partner program. <a href="%s">Read more</a> about the supported services, their functionality and some examples.', 'aawp' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( 'https://getaawp.com/documentation/' ) ); ?></small>
            </p>
            <?php
        }

        /*
         * Hooks & Actions
         */
        public function hooks() {
            add_filter( 'aawp_settings_button_styles', array( &$this, 'settings_extend_button_styles' ) );
            add_action( 'aawp_settings_button_style', array( &$this, 'settings_add_button_styling_options' ) );
            add_filter( 'aawp_settings_button_preview_classes', array( &$this, 'extend_button_classes' ), 10, 2 );
            add_filter( 'aawp_settings_button_detail_preview_classes', array( &$this, 'extend_button_classes' ), 10, 2 );
            add_action( 'aawp_func_button_style', array( &$this, 'extend_button_classes' ), 10, 2 );
            add_action( 'aawp_func_button_detail_style', array( &$this, 'extend_button_classes' ), 10, 2 );
            add_filter( 'aawp_bestseller_items_max', array( &$this, 'allow_items_max_one' ), 10, 2 );
            add_filter( 'aawp_new_releases_items_max', array( &$this, 'allow_items_max_one' ), 10, 2 );
            add_action( 'aawp_settings_output_register', array( &$this, 'settings_add_detail_button' ) );
            add_action( 'aawp_settings_output_pricing_render', array( &$this, 'settings_add_output_pricing_prime_render' ) );
            add_filter( 'aawp_settings_button_detail_styles', array( &$this, 'settings_extend_button_styles' ) );
            add_filter( 'aawp_func_button', array( &$this, 'get_button_detail' ), 10, 3 );
            add_action( 'aawp_settings_general_affiliate_links', array( &$this, 'add_click_tracking_settings' ), 30 );
            add_filter( 'aawp_product_container_attributes', array( &$this, 'add_click_tracking_attributes' ) );
            //add_filter( 'aawp_fields_result', array( &$this, 'add_click_tracking_to_fields' ), 10, 3 );
            add_filter( 'aawp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
        }

        public function settings_extend_button_styles( $styles ) {

            $styles['blue'] = __('Blue', 'aawp');
            $styles['red'] = __('Red', 'aawp');
            $styles['green'] = __('Green', 'aawp');
            $styles['yellow'] = __('Yellow', 'aawp');
            $styles['orange'] = __('Orange', 'aawp');
            $styles['dark'] = __('Dark', 'aawp');

            return $styles;
        }

        public function settings_add_button_styling_options() {

            $rounded = ( isset ( $this->options['output']['button_style_rounded'] ) && $this->options['output']['button_style_rounded'] == '1' ) ? 1 : 0;
            ?>
            &nbsp;
            <input type="checkbox" id="aawp_button_style_rounded" name="aawp_output[button_style_rounded]" value="1" <?php echo($rounded == 1 ? 'checked' : ''); ?> data-aawp-button-preview-rounded="aawp-button-preview" />
            <label for="aawp_button_style_rounded"><?php _e('Rounded', 'aawp'); ?></label>

            <?php
            $shadow = ( isset ( $this->options['output']['button_style_shadow'] ) && $this->options['output']['button_style_shadow'] == '1' ) ? 1 : 0;
            ?>
            &nbsp;
            <input type="checkbox" id="aawp_button_style_shadow" name="aawp_output[button_style_shadow]" value="1" <?php echo($shadow == 1 ? 'checked' : ''); ?> data-aawp-button-preview-shadow="aawp-button-preview" />
            <label for="aawp_button_style_shadow"><?php _e('Shadow', 'aawp'); ?></label>
            <?php
        }

        public function extend_button_classes( $classes, $type ) {

            if ( $type == 'standard' ) {
                $classes .= ( isset ( $this->options['output']['button_style_rounded'] ) && $this->options['output']['button_style_rounded'] == '1' ) ? ' rounded' : '';
                $classes .= ( isset ( $this->options['output']['button_style_shadow'] ) && $this->options['output']['button_style_shadow'] == '1' ) ? ' shadow' : '';
            }

            if ( $type == 'detail' ) {
                $classes .= ( isset ( $this->options['output']['button_detail_style_rounded'] ) && $this->options['output']['button_detail_style_rounded'] == '1' ) ? ' rounded' : '';
                $classes .= ( isset ( $this->options['output']['button_detail_style_shadow'] ) && $this->options['output']['button_detail_style_shadow'] == '1' ) ? ' shadow' : '';
            }

            return $classes;
        }

        public function allow_items_max_one( $max_default, $max_set_by_user ) {
            return $max_set_by_user;
        }

        public function settings_add_detail_button() {

            add_settings_field(
                'aawp_button_detail',
                __( 'Details Button', 'aawp' ),
                array( &$this, 'settings_add_detail_button_render' ),
                'aawp_output',
                'aawp_output_section'
            );
        }

        public function settings_add_detail_button_render() {

            $style = ( isset ( $this->options['output']['button_detail_style'] ) ) ? $this->options['output']['button_detail_style'] : 'standard';
            $text = ( !empty ( $this->options['output']['button_detail_text'] ) ) ? $this->options['output']['button_detail_text'] : __('Details', 'aawp');

            ?>
            <!-- Preview -->
            <h4 class="first"><?php _e('Preview', 'aawp'); ?></h4>
            <?php $classes = 'aawp-button aawp-button--' . $style; ?>
            <a id="aawp-button-detail-preview" href="#aawp-button-detail-preview" class="<?php echo apply_filters( 'aawp_settings_button_detail_preview_classes', $classes, 'detail' ); ?>" data-aawp-button-style="<?php echo $style; ?>">
                <span><?php echo $text; ?></span>
            </a>

            <!-- Style -->
            <?php
            $styles = array(
                'standard' => __('Standard', 'aawp')
            );

            $styles = apply_filters( 'aawp_settings_button_detail_styles', $styles );
            ?>
            <h4><?php _e('Style', 'aawp'); ?></h4>
            <select id="aawp_button_detail_style" name="aawp_output[button_detail_style]" data-aawp-button-preview-style="aawp-button-detail-preview">
                <?php foreach ( $styles as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $style, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>

            <?php
            $rounded = ( isset ( $this->options['output']['button_detail_style_rounded'] ) && $this->options['output']['button_detail_style_rounded'] == '1' ) ? 1 : 0;
            ?>
            &nbsp;
            <input type="checkbox" id="aawp_button_detail_style_rounded" name="aawp_output[button_detail_style_rounded]" value="1" <?php echo($rounded == 1 ? 'checked' : ''); ?> data-aawp-button-preview-rounded="aawp-button-detail-preview" />
            <label for="aawp_button_detail_style_rounded"><?php _e('Rounded', 'aawp'); ?></label>

            <?php
            $shadow = ( isset ( $this->options['output']['button_detail_style_shadow'] ) && $this->options['output']['button_detail_style_shadow'] == '1' ) ? 1 : 0;
            ?>
            &nbsp;
            <input type="checkbox" id="aawp_button_detail_style_shadow" name="aawp_output[button_detail_style_shadow]" value="1" <?php echo($shadow == 1 ? 'checked' : ''); ?> data-aawp-button-preview-shadow="aawp-button-detail-preview" />
            <label for="aawp_button_detail_style_shadow"><?php _e('Shadow', 'aawp'); ?></label>

            <!-- Text -->
            <h4><?php _e('Label', 'aawp'); ?></h4>
            <input type="text" id="aawp_button_detail_text" name="aawp_output[button_detail_text]" value="<?php echo $text; ?>"  data-aawp-button-preview-text="aawp-button-detail-preview" />
             <?php $this->do_admin_note('shortcode'); ?>

            <!-- Link target -->
            <h4><?php _e('Link target', 'aawp'); ?></h4>
            <?php
            $target = ( isset ( $this->options['output']['button_detail_target'] ) && $this->options['output']['button_detail_target'] == '1' ) ? 1 : 0;
            ?>
            <input type="checkbox" id="aawp_button_detail_target" name="aawp_output[button_detail_target]" value="1" <?php echo($target == 1 ? 'checked' : ''); ?> />
            <label for="aawp_button_detail_target"><?php _e('Open links in a new window or tab', 'aawp'); ?></label>
            <?php $this->do_admin_note('shortcode'); ?>
            <?php
        }

        public function get_button_detail( $button_args, $type, $atts ) {

            if ( $type == 'detail' && ! empty( $atts['button_detail'] ) ) {

                // Classes
                $button_classes = 'aawp-button';

                if ( ! empty( $this->options['output']['button_detail_style'] ) && 'standard' != $this->options['output']['button_detail_style'] )
                    $button_classes .= ' aawp-button--' . $this->options['output']['button_detail_style'];

                if ( isset ( $this->options['output']['button_detail_style_rounded'] ) && $this->options['output']['button_detail_style_rounded'] == '1' )
                    $button_classes .= ' rounded';

                if ( isset ( $this->options['output']['button_detail_style_shadow'] ) && $this->options['output']['button_detail_style_shadow'] == '1' )
                    $button_classes .= ' shadow';

                // URL
                $button_url = ( is_numeric( $atts['button_detail'] ) ) ? get_permalink( $atts['button_detail'] ) : $atts['button_detail'];

                // Text
                if ( ! empty( $atts['button_detail_text'] ) ) {
                    $button_text = $atts['button_detail_text'];
                } elseif ( ! empty ( $this->options['output']['button_detail_text'] ) ) {
                    $button_text = $this->options['output']['button_detail_text'];
                } else {
                    $button_text = __( 'Details', 'aawp' );
                }

                // Title
                $button_title = ( ! empty($atts['button_detail_title'] ) ) ? $atts['button_detail_title'] : $button_text;

                // Target
                if ( ! empty( $atts['button_detail_target'] ) ) {
                    $button_target = $atts['button_detail_target'];
                } elseif ( isset ( $this->options['output']['button_detail_target'] ) && $this->options['output']['button_detail_target'] == '1' ) {
                    $button_target = '_blank';
                } else {
                    $button_target = '_self';
                }

                // Rel
                $button_rel = '';

                if ( ! empty( $atts['button_detail_rel'] ) )
                    $button_rel = $atts['button_detail_rel'];

                // Attributes
                $button_attributes = '';

                if ( isset ( $this->options['general']['click_tracking'] ) && 'none' != $this->options['general']['click_tracking'] )
                    $button_attributes = 'data-aawp-prevent-click-tracking="true"';

                $button_args = array(
                    'classes' => $button_classes,
                    'url' => $button_url,
                    'text' => $button_text,
                    'target' => $button_target,
                    'title' => $button_title,
                    'rel' => $button_rel,
                    'attributes' => $button_attributes
                );
            }

            return $button_args;
        }

        public function settings_add_output_pricing_prime_render() {

            // Return if country not yet selected for prime
            if ( ! aawp_country_has_prime( $this->api_country ) )
                return;

            $check_prime = ( isset ( $this->options['output']['check_prime'] ) ) ? $this->options['output']['check_prime'] : 'linked';

            $check_prime_options = array(
                'none' => __('Hide', 'aawp'),
                'logo' => __('Show Prime logo', 'aawp'),
                'linked' => __('Show Prime logo and link to Amazon Prime', 'aawp')
            );

            ?>

            <!-- Check Prime -->
            <h4><?php echo aawp_get_prime_check_logo( $this->api_country ); ?></h4>
            <p>
                <label for="aawp_check_prime"><?php _e('Display Amazon Prime logo besides the advertised price, whenever this service is available for the related product.', 'aawp'); ?></label>
            </p>
            <p>
                <select id="aawp_check_prime" name="aawp_output[check_prime]">
                    <?php foreach ( $check_prime_options as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $check_prime, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </p>
            <?php $check_prime_link = ( $this->lang_de ) ? 'https://partnernet.amazon.de/gp/associates/promo/prime' : aawp_get_amazon_associates_link(); ?>
            <p><small><?php _e( '<strong>Extra benefit:</strong> The Amazon prime logo can be linked to the "Amazon Prime 30-day free trial" landingpage and you receive an extra commission for Amazon Prime referrals.', 'aawp' ); ?><br /><?php echo sprintf( wp_kses( __( 'Check your <a href="%s">Amazon associates page</a> for more information.', 'aawp' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( $check_prime_link ) ); ?></small></p>

            <?php
        }

        public function add_click_tracking_settings() {

            $click_tracking = ( isset ( $this->options['general']['click_tracking'] ) ) ? $this->options['general']['click_tracking'] : 'none';

            $click_tracking_options = array(
                'none' => __('Disabled', 'aawp'),
                'asin' => __('Tracking enabled and using ASIN (Article Number) as label', 'aawp'),
                'title' => __('Tracking enabled and using product title as label', 'aawp')
            );

            ?>
            <h4><?php _e('Click Tracking', 'aawp'); ?></h4>
            <p><?php _e('AAWP can track clicks on affiliate links coming through the plugin by creating events via your favorite tracking tool. Currently supported:', 'aawp'); ?> Google Analytics & Piwik.</p>
            <p>
                <select id="aawp_click_tracking" name="aawp_general[click_tracking]">
                    <?php foreach ( $click_tracking_options as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $click_tracking, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </p>
            <p><small><strong><?php _e('Note:', 'aawp'); ?></strong> <?php _e('In case you created custom templates, please take a look into the documentation.', 'aawp'); ?></small></p>
            <?php
        }

        public function add_click_tracking_attributes( $attributes ) {

            $click_tracking = ( isset ( $this->options['general']['click_tracking'] ) ) ? $this->options['general']['click_tracking'] : 'none';

            if ( 'none' != $click_tracking ) {
                // Click Tracking
                $attributes['click-tracking'] = $click_tracking;
            }

            return $attributes;
        }

        // Deprecated
        public function add_click_tracking_to_fields( $result, $request, $container_attributes ) {
            if ( empty ( $result ) )
                return $result;

            if ( isset ( $this->options['general']['click_tracking'] ) && 'none' != $this->options['general']['click_tracking'] ) {

                if ( ! in_array( $request, array( 'button_detail' ) ) ) { // 'link', 'thumb', 'star_rating', 'prime', 'premium', 'button'
                    //$result = str_replace('<a', '<a ' . $container_attributes, $result );
                }
            }

            return $result;
        }

        public function enqueue_scripts( $enqueue ) {

            // Click tracking
            if ( isset ( $this->options['general']['click_tracking'] ) && 'none' != $this->options['general']['click_tracking'] )
                return true;

            return $enqueue;
        }
    }

    new AAWP_Extended_Functions();
}

/*
 * Detail Button Shortcode
 */
function aawp_shortcode_button_detail( $atts, $content ) {

    extract(shortcode_atts(array(
        'link' => null,
        'url' => null, // Fallback
        'id' => null, // Fallback
        'text' => null,
        'target' => null,
        'title' => null,
        'style' => null,
    ), $atts));

    $args = array();

    // Collect attributes
    if ( ! empty( $link ) ) {
        $args['url'] = $link;
    } elseif ( ! empty( $url ) ) {
        $args['url'] = $url;
    } elseif ( ! empty( $id ) ) {
        $args['url'] = $id;
    }

    if ( ! empty( $text ) )
        $args['text'] = $text;

    if ( ! empty( $target ) )
        $args['target'] = $target;

    if ( ! empty( $title ) )
        $args['title'] = $title;

    if ( ! empty( $style ) )
        $args['style'] = $style;

    return aawp_get_button_detail_html( $args );
    //return aawp_get_field_value( 'none', 'button_detail', $button_atts );
}
add_shortcode( 'aawp_button_detail', 'aawp_shortcode_button_detail' );

/**
 * Get detail button html
 *
 * @param array $args
 *
 * @return string
 */
function aawp_get_button_detail_html( $args = array() ) {

    if ( empty( $args['url'] ) ) {
        return null;
    }

    $output_options = aawp_get_options( 'output' );

    // Defaults
    $classes = 'aawp-button';

    if ( ! empty( $args['style'] ) ) {
        $classes .= ' aawp-button--' . $args['style'];
    } elseif ( ! empty( $output_options['button_detail_style'] ) ) {
        $classes .= ' aawp-button--' . $output_options['button_detail_style'];
    }

    if ( ! empty( $output_options['button_detail_style_rounded'] ) )
        $classes .= ' rounded';

    if ( ! empty( $output_options['button_detail_style_shadow'] ) )
        $classes .= ' shadow';

    $text = ( ! empty ( $output_options['button_detail_text'] ) ) ? $output_options['button_detail_text'] : __('Details', 'aawp');

    if ( ! empty( $args['text'] ) )
        $text = $args['text'];

    $target = ( ! empty( $output_options['button_detail_target'] ) ) ? '_blank' : '_self';

    $button_args = array(
        'classes' => ( ! empty( $args['classes'] ) ) ? $args['classes'] : $classes,
        'url' => ( is_numeric( $args['url'] ) ) ? get_permalink( $args['url'] ) : $args['url'],
        'target' => ( ! empty( $args['target'] ) ) ? $args['target'] : $target,
        'title' => ( ! empty( $args['title'] ) ) ? $args['title'] : $text,
        'text' => $text,
        'rel' => ( ! empty( $args['rel'] ) ) ? $args['rel'] : ''
    );

    return aawp_get_button_html( $button_args );
}


/*
 * Detail Button Shortcode
 */
function aawp_shortcode_button( $atts, $content ) {

    extract( shortcode_atts( array(
        'asin'   => null,
        'url'    => null,
        'link'   => null, // Fallback
        'text'   => null,
        'target' => null,
        'title'  => null,
        'style'  => null,
    ), $atts ) );

    $button_atts = array();

    // API generated or default button?
    $asin = ( ! empty( $asin ) ) ? esc_html( $asin ) : false;

    // Prefix
    $prefix = ( $asin ) ? 'button_' : 'button_detail_';

    // URL
    if ( isset( $atts['url'] ) ) {
        $button_atts['button_detail'] = esc_html( $atts['url'] );

    } elseif ( isset( $atts['link'] ) ) { // Fallback
        $button_atts['button_detail'] = esc_html( $atts['link'] );
    }

    // Text
    if ( ! empty( $text ) )
        $button_atts[$prefix . 'text'] = esc_html( $text );

    // Target
    if ( ! empty( $target ) )
        $button_atts[$prefix . 'target'] = esc_html( $target );

    // Title
    if ( ! empty( $title ) )
        $button_atts[$prefix . 'title'] = esc_html( $title );

    // Style
    if ( ! empty( $style ) )
        $button_atts[$prefix . 'style'] = esc_html( $style );

    /*
    // Collect attributes
    if ( !empty( $link ) ) {
        $button_atts['button_detail'] = $link;
    } elseif ( !empty( $url ) ) {
        $button_atts['button_detail'] = $url;
    } elseif ( !empty( $id ) ) {
        $button_atts['button_detail'] = $id;
    }

    if (

    if ( !empty( $text ) )
        $button_atts['button_detail_text'] = $text;

    if ( !empty( $target ) )
        $button_atts['button_detail_target'] = $target;

    if ( !empty( $title ) )
        $button_atts['button_detail_title'] = $title;

    if ( !empty( $style ) )
        $button_atts['button_detail_style'] = $style;
    */

    if ( $asin ) {
        return aawp_get_field_value( $asin, 'button', $button_atts );
    } else {
        return aawp_get_field_value( 'none', 'button_detail', $button_atts );
    }
}
//add_shortcode( 'aawp_button', 'aawp_shortcode_button' );

/*
 * Amazon Prime links
 */
function aawp_country_has_prime( $country ) {

    if ( empty( $country ) )
        return false;

    return ( 'com.br' === $country ) ? false : true;
}

/*
 * Amazon Prime links
 */
function aawp_get_amazon_prime_url( $country = null, $tracking_id = null ) {

    if ( empty( $country ) ) {
        $country = aawp_get_default_country();
    }

    if ( empty ( $country ) )
        return null;

    $link = 'https://www.amazon.' . $country . '/gp/prime/';

    if ( !empty ( $tracking_id ) )
        $link .= '?tag=' . $tracking_id;

    return $link;
}

function aawp_get_prime_check_logo( $atts = array() ) {

    $output_options = aawp_get_options( 'output' );
    $check_prime = ( isset( $output_options['check_prime'] ) ) ? $output_options['check_prime'] : 'linked';

    $country = apply_filters( 'aawp_amazon_prime_country', aawp_get_default_country(), $atts );

    $title = __('Amazon Prime', 'aawp');

    // Exceptions: Premium
    /*
    if ( 'fr' === $country || 'es' === $country ) {
        $classes = 'aawp-check-premium';
        $title = __('Amazon Premium', 'aawp');
    */
    // Exception: Japan
    if ( 'co.jp' === $country ) {
        $classes = 'aawp-check-prime aawp-check-prime--jp';

    // Default
    } else {
        $classes = 'aawp-check-prime';
    }

    // Output
    $prime_logo = '<span class="' . $classes . '"></span>';

    // Handle reflink & tracking id
    $tracking_id = ( ! empty( $atts['tracking_id'] ) ) ? trim( $atts['tracking_id'] ) : aawp_get_default_tracking_id();
    $ref_link = aawp_get_amazon_prime_url( $country, $tracking_id );

    if ( !empty ( $ref_link ) && 'linked' === $check_prime )
        return '<a class="' . $classes . '" href="' . $ref_link . '" title="' . $title . '" rel="nofollow noopener sponsored" target="_blank"></a>';

    return $prime_logo;
}