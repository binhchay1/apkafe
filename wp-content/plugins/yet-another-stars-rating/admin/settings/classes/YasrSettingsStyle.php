<?php

/**
 * Class YasrSettingsStyle
 *
 * @author Dario Curvino <@dudo>
 * @since 3.1.9
 */
class YasrSettingsStyle {
    public function init() {
        //init style options
        add_action('admin_init', array($this, 'styleOptions'));

        //Add setting field to choose the image for the free version
        add_action('yasr_style_options_add_settings_field', array('YasrSettingsStyle', 'settingsFieldFreeChooseImage'));

        //hook into options
        add_filter('yasr_filter_style_options', array($this, 'defaultStarSet'));
    }

    /**
     * Init style options
     *
     * @author Dario Curvino <@dudo>
     * @return void
     */
    public function styleOptions() {
        register_setting(
            'yasr_style_options_group', // A settings group name. Must exist prior to the register_setting call. This must match the group name in settings_fields()
            'yasr_style_options', //The name of an option to sanitize and save.
            array($this, 'styleOptionsSanitize')
        );

        $style_options = json_decode(YASR_STYLE_OPTIONS, true);

        //filter $style_options
        $style_options = apply_filters('yasr_filter_style_options', $style_options);

        $yasr_settings_descriptions = new YasrSettingsDescriptions();

        add_settings_section(
            'yasr_style_options_section_id',
            __('Style Options', 'yet-another-stars-rating'),
            '__return_false',
            'yasr_style_tab'
        );

        do_action('yasr_style_options_add_settings_field', $style_options);

        add_settings_field(
            'yasr_st_upload_stars',
            sprintf(__('Custom Star Set %s', 'yet-another-stars-rating'), YASR_LOCKED_FEATURE),
            array ($this, 'formUploadStars'),
            'yasr_style_tab',
            'yasr_style_options_section_id',
            $style_options);

        add_settings_field(
            'yasr_st_choose_stars_radio',
            sprintf(__('Choose Stars Set %s', 'yet-another-stars-rating'), YASR_LOCKED_FEATURE),
            array ($this, 'chooseStarsRadio'),
            'yasr_style_tab',
            'yasr_style_options_section_id',
            $style_options);


        add_settings_field(
            'yasr_color_scheme_multiset',
            $yasr_settings_descriptions->multisetColorDescription(),
            array($this, 'settingsFieldFreeMultisetHTML'),
            'yasr_style_tab',
            'yasr_style_options_section_id',
            $style_options
        );

        add_settings_field(
            'yasr_style_options_textarea',
            $yasr_settings_descriptions->customCssDescription(),
            array($this,'settingsFieldTextareaHTML'),
            'yasr_style_tab',
            'yasr_style_options_section_id',
            $style_options
        );
    }

    /**
     * Add setting field for free version
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $style_options
     *
     * @return void
     */
    public static function settingsFieldFreeChooseImage($style_options) {
        add_settings_field(
            'yasr_style_options_choose_stars_lite',
            __('Choose Stars Set', 'yet-another-stars-rating'),
            array('YasrSettingsStyle', 'printRadioFreeStars'),
            'yasr_style_tab',
            'yasr_style_options_section_id',
            $style_options
        );
    }

    /**
     * Print the html with the radios to choose the image to use
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.4.1
     *
     * @param $style_options
     *
     * @return void
     */
    public static function printRadioFreeStars($style_options) {
        ?>
        <div class='yasr-select-img-container' id='yasr_pro_custom_set_choosen_stars'>
            <div>
                <input type='radio'
                       name='yasr_style_options[stars_set_free]'
                       value='rater'
                       id="radio-img-rater"
                       class='yasr-general-options-scheme-color'
                    <?php if ($style_options['stars_set_free'] === 'rater') {
                        echo 'checked="checked"';
                    } ?> />
                <label for="radio-img-rater">
                <span class='yasr_pro_stars_set'>
                    <?php
                    echo '<img src="' . esc_url(YASR_IMG_DIR . 'stars_rater.png').'">';
                    ?>
                </span>
                </label>
            </div>
            <div>
                <input type='radio' name='yasr_style_options[stars_set_free]' value='rater-yasr' id="radio-img-yasr"
                       class='yasr-general-options-scheme-color' <?php if ($style_options['stars_set_free'] === 'rater-yasr') {
                    echo 'checked="checked"';
                } ?> />
                <label for="radio-img-yasr">
                <span class='yasr_pro_stars_set'>
                    <?php
                    echo '<img src="' . esc_url(YASR_IMG_DIR . 'stars_rater_yasr.png').'">';
                    ?>
                </span>
                </label>
            </div>
            <div>
                <input type='radio' name='yasr_style_options[stars_set_free]' value='rater-oxy' id="radio-img-oxy"
                       class='yasr-general-options-scheme-color' <?php if ($style_options['stars_set_free'] === 'rater-oxy') {
                    echo 'checked="checked"';
                } ?> />
                <label for="radio-img-oxy">
                    <span class='yasr_pro_stars_set'>
                        <?php
                            echo '<img src="' . esc_url(YASR_IMG_DIR . 'stars_rater_oxy.png').'">';
                        ?>
                    </span>
                </label>
            </div>
        </div>
        <hr />
        <?php
        submit_button(__('Save Settings', 'yet-another-stars-rating'));
    }

    /**
     * Print 2 input fields with button to upload stars
     *
     * @author Dario Curvino <@dudo>
     * @since  2.6.8
     *
     * @param $style_options
     */
    public function formUploadStars($style_options) {
        ?>
        <div>
            <?php
            $this->printUpgradeToProText();
            if (!isset($style_options['custom_image_inactive'])) {
                $style_options['custom_image_inactive'] = null;
            }

            if (!isset($style_options['custom_image_active'])) {
                $style_options['custom_image_active'] = null;
            }

            ?>

            <div class="yasr-stylish-locked" style="width: 100%;">
                <div style="font-size: 14px; color: #23282d; font-weight: 600">
                    <?php esc_html_e('Upload Custom Icons', 'yet-another-stars-rating'); ?>
                </div>
                <br/>
                <div>
                    <label for="yasr-custom-image-inactive" class="yasr-text-upload-image">
                        <?php esc_html_e('"Off" image', 'yet-another-stars-rating') ?>
                    </label>
                    <input class="yasr-input-text-upload-image"
                           type="text"
                           name="yasr_style_options[custom_image_inactive]"
                           id="yasr-custom-image-inactive"
                           size="20"
                           value="<?php echo esc_attr($style_options['custom_image_inactive']); ?>"
                           <?php echo YASR_LOCKED_FEATURE_HTML_ATTRIBUTE ?>
                    >

                    <!-- Print preview -->
                    <?php if ($style_options['custom_image_inactive']) { ?>
                        <span class="yasr_uploaded_stars_preview" id='yasr_pro_star_inactive_preview'>
                            <img src="<?php echo esc_url($style_options['custom_image_inactive']) ?>"
                                 width="32" height="32" alt="inactive"
                            >
                        </span>
                    <?php } ?>

                    <button class="button-primary yasr-pro-upload-image">
                        <?php esc_html_e('Upload', 'yet-another-stars-rating'); ?>
                    </button>
                </div>
                <div>
                    <label for="yasr-custom-image-active" class="yasr-text-upload-image">
                        <?php esc_html_e('"Active" image', 'yet-another-stars-rating') ?>
                    </label>
                    <input class="yasr-input-text-upload-image"
                           type="text"
                           name="yasr_style_options[custom_image_active]"
                           id="yasr-custom-image-active"
                           size="20"
                           value="<?php echo esc_attr($style_options['custom_image_active']); ?>"
                           <?php echo YASR_LOCKED_FEATURE_HTML_ATTRIBUTE ?>
                    >

                    <!-- Print preview -->
                    <?php if ($style_options['custom_image_active']) { ?>
                        <span class="yasr_uploaded_stars_preview" id='yasr_pro_star_active_preview'>
                            <img src='<?php echo esc_url($style_options['custom_image_active']); ?>'
                                 alt="active" width="32" height="32">
                        </span>
                    <?php } ?>

                    <button class="button-primary yasr-pro-upload-image">
                        <?php esc_html_e('Upload', 'yet-another-stars-rating'); ?>
                    </button>
                </div>
                <div class="yasr-indented-answer" style="margin: 25px;">
                    <?php
                    $text = sprintf(
                        __('%s (You need a plugin like %s to upload it). %s Aspect ratio must be 1:1 and width x height at least 32x32',
                            'yet-another-stars-rating'),
                        '<strong>Svg Only.</strong>',
                        '<a href="https://wordpress.org/plugins/safe-svg/">Safe Svg</a>',
                        '<br />');

                    echo yasr_kses($text);
                    ?>
                </div>
            </div>

        </div>
        <?php

    }

    /**
     *
     * @author Dario Curvino <@dudo>
     * @since  2.6.8
     *
     * @param $style_options
     */
    public function chooseStarsRadio($style_options) {

        $folder_img = YASR_ABSOLUTE_PATH_INCLUDES . '/img/stars/thumb/'; //must use absolute path, not plugin_url
        $filetype   = '*.png';

        //create an array with the folder content
        $array_file = glob($folder_img . $filetype);

        //Sorting array in "natural order"
        natsort($array_file);

        if (!isset($style_options['stars_set']) || $style_options['stars_set'] === false) {
            $style_options['stars_set'] = '0yasr';
        }

        $this->printStarsRadios($array_file, $style_options);

    } //End function yasr_pro_choose_stars_radio_callback

    /**
     * @author Dario Curvino <@dudo>
     *
     * @since 3.4.0
     *
     * @param $array_file
     * @param $style_options
     *
     * @return void
     */
    private function printStarsRadios($array_file, $style_options) {
        ?>
        <div class="yasr-stylish-locked">
            <div class="yasr-select-img-container">
                <?php
                    foreach ($array_file as $single_file) {
                        $filename_ext = basename($single_file); //File name with extension
                        $img_url      = YASR_IMG_DIR . 'stars/thumb/' . $filename_ext; //File name absolute path
                        $filename     = pathinfo($filename_ext, PATHINFO_FILENAME); //Filename without ext
                        $id           = 'yasr_pro_choosen_stars_'.$filename;
                        ?>
                        <div>
                            <input type='radio'
                                   name='yasr_style_options[stars_set]'
                                   value='<?php echo esc_attr($filename); ?>'
                                   id='<?php echo esc_attr($id) ?>'
                                   <?php
                                       if ($style_options['stars_set'] === $filename) {
                                           echo " checked='checked' ";
                                       }
                                       echo YASR_LOCKED_FEATURE_HTML_ATTRIBUTE
                                   ?>
                            />
                            <label for='<?php echo esc_attr($id) ?>'>
                                <span>
                                    <img src='<?php echo esc_url($img_url); ?> '
                                         width="32"
                                         height="64"
                                         alt='<?php echo esc_attr($id) ?>'
                                    >
                                </span>
                            </label>
                        </div>
                        <?php
                    }
                    ?>
                    <p>&nbsp;</p>
            </div>

            <button class="button-secondary" id="yasr-st-reset-stars">Reset</button>

            <?php submit_button(__('Save Settings'), 'primary', 'submit', false); ?>

            <p>&nbsp;</p>
        </div>
        <hr />
        <?php
    }

    /**
     * Print the radios to choose the color for multiset
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $style_options
     *
     * @return void
     */
    public function settingsFieldFreeMultisetHTML($style_options) {
        ?>

        <div class="yasr-settings-row-35">
            <?php
            $array_options = array (
                'light' => __('Light', 'yet-another-stars-rating'),
                'dark'  => __('Dark', 'yet-another-stars-rating')
            );
            $default = $style_options['scheme_color_multiset'];
            $name    = 'yasr_style_options[scheme_color_multiset]';
            $class   = 'yasr-general-options-scheme-color';
            $id      = 'yasr-style-options-color-scheme';

            echo yasr_kses(YasrPhpFieldsHelper::radio('', $class, $array_options, $name, $default, $id));
            ?>

            <div id="yasr-color-scheme-preview">
                <?php esc_html_e('Light theme', 'yet-another-stars-rating'); ?>
                <br /><br /><img src="<?php echo esc_url(YASR_IMG_DIR . 'yasr-multi-set.png')?>" alt="light-multiset">

                <br /> <br />

                <?php esc_html_e('Dark theme', 'yet-another-stars-rating'); ?>
                <br /><br /><img src="<?php echo esc_url(YASR_IMG_DIR . 'dark-multi-set.png')?>" alt="dark-multiset">
            </div>

        </div>

        <p>

        <?php
    }

    /**
     * Print the textarea to customize css
     *
     * @author Dario Curvino <@dudo>*
     * @param $style_options
     *
     * @return void
     */
    public function settingsFieldTextareaHTML($style_options) {
        ?>
        <label for='yasr_style_options_textarea'></label><textarea
                rows='17'
                cols='40'
                name='yasr_style_options[textarea]'
                id='yasr_style_options_textarea'><?php echo esc_textarea($style_options['textarea']); ?></textarea>
        <?php
    }

    /**
     * HTML output to print the pro version
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.4.1
     * @return void
     */
    public function printUpgradeToProText() {
        ?>
        <div id='yasr-settings-stylish-text' style="opacity: 1;">
            <?php
            $text = __('Looking for more?', 'yet-another-stars-rating');
            $text .= '<br />';
            $text .= sprintf(__('Upgrade to %s', 'yet-another-stars-rating'), '<a href="?page=yasr_settings_page-pricing">Yasr Pro!</a>');

            echo wp_kses_post($text);
            ?>
        </div>
        <?php
    }

    /**
     * @author Dario Curvino <@dudo>
     *
     * Filter the $style_options and, if a default value doesn't exist,
     * set 'rater-yasr' as default
     * 
     * @param $style_options
     *
     * @return mixed
     */
    public function defaultStarSet($style_options) {
        if (!array_key_exists('stars_set_free', $style_options)) {
            $style_options['stars_set_free'] = 'rater-yasr'; //..default value if not exists
        }
        return $style_options;
    }

    /**
     * Sanitize the input
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $style_options
     *
     * @return array
     */
    function styleOptionsSanitize($style_options) {
        $style_options = apply_filters('yasr_sanitize_style_options', $style_options);
        $output = array();

        foreach ($style_options as $key => $value) {
            if ($key === 'custom_image_inactive' || $key === 'custom_image_active') {
                //if is set (empty is ok)
                if ($value !== '') {
                    $is_svg_and_url = yasr_check_svg_image($value);

                    if ($is_svg_and_url !== true) {
                        wp_die($is_svg_and_url);
                    }
                }
            }

            //all fields are sanitized with sanitize_textarea_field
            $output[$key] = sanitize_textarea_field($value);

        }

        return $output;
    }

}