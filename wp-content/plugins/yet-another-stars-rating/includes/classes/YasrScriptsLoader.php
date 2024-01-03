<?php

/**
 * @author Dario Curvino <@dudo>
 * @since  3.0.4
 * Class YasrScriptsLoader
 */
class YasrScriptsLoader {

    /**
     * @author Dario Curvino <@dudo>
     * @since  3.0.5
     */
    public function loadRequiredScripts() {
        //Adds window.var needed in both admin and public
        add_action('wp_enqueue_scripts',            array($this, 'loadWindowVar'), 11);
        add_action('admin_enqueue_scripts',         array($this, 'loadWindowVar'), 11);

        add_action('yasr_add_front_script_css',     array($this, 'loadRtlSupport'));
        add_action('yasr_add_admin_scripts_end',    array($this, 'loadRtlSupport'));

        /*** Css rules for stars set, from version 1.2.7
         * Here I use add_action instead of directly use wp_add_inline_style so I can
         * use remove_action if needed (e.g. Yasr Stylish)
         ***/
        add_action('yasr_add_front_script_css',     array($this, 'loadInlineCss'));
        add_action('yasr_add_admin_scripts_end',    array($this, 'loadInlineCss'));

        //enqueue gutenberg stuff outside blocks
        add_action('enqueue_block_editor_assets',   array($this, 'initGutenMisc'));
        add_action('init',                          array($this, 'initGutenBlocks'));

        //Add yasr constant in gutenberg
        add_action('yasr_add_admin_scripts_end',    array($this, 'addJsConstantInGutenberg'));

        //Save auto insert value to yasrConstantGutenberg
        add_filter('yasr_gutenberg_constants',      array($this, 'yasrGutenbergConstants'));
    }

    /**
     * Helper function that load window.var required by YASR
     *
     * @author Dario Curvino <@dudo>
     * @since 3.0.5
     */
    public function loadWindowVar() {
        //This is required to use wp_localize_script without dependency
        //https://wordpress.stackexchange.com/a/311279/48442
        wp_register_script('yasr-window-var', '', array(), YASR_VERSION_NUM, true);
        wp_enqueue_script('yasr-window-var');

        $yasr_visitor_votes_loader =
            '<div id="yasr-loader" style="display: inline-block">&nbsp; '.
                '<img src="' . esc_url(YASR_IMG_DIR . 'loader.gif').'" 
                 title="yasr-loader" alt="yasr-loader" height="16" width="16">'.
            '</div>';

        //Use this hook to customize loader
        $yasr_visitor_votes_loader     = apply_filters('yasr_custom_loader', $yasr_visitor_votes_loader);

        //Use this hook to customize only the loader url
        //since version 3.1.2, yasrWindowVar.loaderUrl it is not used in YASR, but it is useful to keep it
        $yasr_visitor_votes_loader_url = apply_filters('yasr_custom_loader_url', YASR_IMG_DIR . 'loader.gif');

        $yasr_window_var = array(
            'siteUrl'              => site_url(),
            'adminUrl'             => admin_url(), //keep this for pricing page
            'ajaxurl'              => admin_url('admin-ajax.php'),
            'visitorStatsEnabled'  => YASR_VISITORS_STATS,
            'ajaxEnabled'          => YASR_ENABLE_AJAX,
            'loaderHtml'           => $yasr_visitor_votes_loader,
            'loaderUrl'            => esc_url($yasr_visitor_votes_loader_url),
            'isUserLoggedIn'       => json_encode(is_user_logged_in()),
            'isRtl'                => json_encode(is_rtl()),
            'starSingleForm'       => json_encode(esc_html__('star', 'yet-another-stars-rating')),
            'starsPluralForm'      => json_encode(esc_html__('stars', 'yet-another-stars-rating')),
            'textAfterVr'          => json_encode(YASR_TEXT_AFTER_VR),
            'textRating'           => json_encode(esc_html__('Rating', 'yet-another-stars-rating')),
            'textLoadRanking'      => json_encode(esc_html__('Loading, please wait', 'yet-another-stars-rating')),
            'textVvStats'          => json_encode(esc_html__('out of 5 stars', 'yet-another-stars-rating')),
            'textOrderBy'          => json_encode(esc_html__('Order by', 'yet-another-stars-rating')),
            'textMostRated'        => json_encode(esc_html__('Most Rated', 'yet-another-stars-rating')),
            'textHighestRated'     => json_encode(esc_html__('Highest Rated', 'yet-another-stars-rating')),
            'textLeftColumnHeader' => json_encode(esc_html__('Post', 'yet-another-stars-rating'))
        );

        //check if wp_localize_script has already run before
        //since version 2.9.8 wp_localize_script is used again instead of wp_add_inline_script.
        //For some reasons, wp_add_inline_script simply didn't run in some cases
        // (e.g. https://wordpress.org/support/topic/yellow-star-not-appear/)
        if(!defined('YASR_GLOBAL_DATA_EXISTS')) {
            wp_localize_script(
                'yasr-window-var',
                'yasrWindowVar',
                $yasr_window_var
            );

            //use a constant to be sure that yasrWindowVar is not loaded twice
            define ('YASR_GLOBAL_DATA_EXISTS', true);
        }
    }

    /**
     * Load main css file
     *
     * @author Dario Curvino <@dudo>
     * @since  3.0.8
     */
    public static function loadRequiredCss() {
        wp_enqueue_style(
            'yasrcss',
            YASR_CSS_DIR_INCLUDES . 'yasr.css',
            false,
            YASR_VERSION_NUM
        );

        //Run after default css are loaded
        do_action('yasr_add_front_script_css');

        if (YASR_CUSTOM_CSS_RULES) {
            wp_add_inline_style(
                'yasrcss',
                YASR_CUSTOM_CSS_RULES
            );
        }

        do_action('yasr_add_front_script_js');
    }

    /**
     * Load Multiset and ranking css
     *
     * @author Dario Curvino <@dudo>
     * @since  3.0.5
     */
    public static function loadTableCss () {
        $yasr_multiset_theme_handle = 'yasrcsslightscheme';
        $yasr_multiset_theme = 'yasr-table-light.css';

        //default css is the light one
        if (YASR_SCHEME_COLOR === 'dark') {
            $yasr_multiset_theme_handle = 'yasrcssdarkscheme';
            $yasr_multiset_theme = 'yasr-table-dark.css';
        }

        wp_enqueue_style(
            $yasr_multiset_theme_handle,
            YASR_CSS_DIR_INCLUDES . $yasr_multiset_theme,
            '',
            YASR_VERSION_NUM
        );
    }

    /**
     * This function enqueue the js scripts required on both admin and frontend
     *
     * @author Dario Curvino <@dudo>
     * @since 2.8.5
     */
    public static function loadRequiredJs() {
        wp_enqueue_script('jquery');

        wp_enqueue_script(
            'yasr-global-functions',
            YASR_JS_DIR_INCLUDES . 'yasr-globals.js',
            'yasr-window-var',
            YASR_VERSION_NUM,
            true
        );

        if (yasr_is_catch_infinite_sroll_installed() === true) {
            $array_dep = array('jquery', 'yasr-global-functions', 'yasr-window-var', 'wp-element');

            //laod tippy only if the shortcode has loaded it
            $tippy_loaded = wp_script_is('tippy');

            if ($tippy_loaded) {
                $array_dep[] = 'tippy';
            }

            wp_enqueue_script(
                'yasr_catch_infinite',
                YASR_JS_DIR_INCLUDES . 'catch-inifite-scroll.js',
                $array_dep,
                YASR_VERSION_NUM,
                true
            );
        }

    }


    /**
     * Rtl support
     *
     * @author Dario Curvino <@dudo>
     */
    public function loadRtlSupport() {
        if (is_rtl()) {
            $yasr_rtl_css =
                '.yasr-star-rating .yasr-star-value {
                    -moz-transform: scaleX(-1);
                    -o-transform: scaleX(-1);
                    -webkit-transform: scaleX(-1);
                    transform: scaleX(-1);
                    filter: FlipH;
                    -ms-filter: "FlipH";
                    right: 0;
                    left: auto;
                }';

            wp_add_inline_style('yasrcss', $yasr_rtl_css);
        }
    }

    /**
     * Load inline css for star set
     *
     * @author Dario Curvino <@dudo>
     */
    public function loadInlineCss() {
        //if star selected is "rater", select the images
        if (YASR_STARS_SET === 'rater') {
            $star_grey   = YASR_IMG_DIR . 'star_0.svg';
            $star_yellow = YASR_IMG_DIR . 'star_1.svg';
        } elseif (YASR_STARS_SET === 'rater-oxy') {
            $star_grey   = YASR_IMG_DIR . 'star_oxy_0.svg';
            $star_yellow = YASR_IMG_DIR . 'star_oxy_1.svg';
        } //by default, use the one provided by Yasr
        else {
            $star_grey   = YASR_IMG_DIR . 'star_2.svg';
            $star_yellow = YASR_IMG_DIR . 'star_3.svg';
        }

        $yasr_st_css = "
            .yasr-star-rating {
                background-image: url('".esc_url($star_grey)."');
            }
            .yasr-star-rating .yasr-star-value {
                background: url('".esc_url($star_yellow)."') ;
            }";

        wp_add_inline_style('yasrcss', $yasr_st_css);
    }

    /**
     * Enqueue visitorVotes.js file
     *
     * @author Dario Curvino <@dudo>
     * @since  2.8.5
     */
    public static function loadVVJs() {
        $array_dep    = array('jquery', 'yasr-global-functions', 'yasr-window-var');
        $tippy_loaded = wp_script_is('tippy');

        if ($tippy_loaded) {
            $array_dep[] = 'tippy';
        }

        wp_enqueue_script(
            'yasr-front-vv',
            YASR_JS_DIR_INCLUDES . 'shortcodes/visitorVotes.js',
            $array_dep,
            YASR_VERSION_NUM,
            true
        );
    }

    /***
     * Enqueue overall-multiset.js file
     *
     * @author Dario Curvino <@dudo>
     * @since  2.8.8
     */
    public static function loadOVMultiJs() {
        wp_enqueue_script(
            'yasr-ov-multi',
            YASR_JS_DIR_INCLUDES . 'shortcodes/overall-multiset.js',
            array('jquery', 'yasr-global-functions', 'yasr-window-var'),
            YASR_VERSION_NUM,
            true
        );
    }

    /**
     * Enqueue rankings.js file
     *
     * @author Dario Curvino <@dudo>
     * @since  2.8.8
     */
    public static function loadRankingsJs() {
        wp_enqueue_script(
            'yasr-rankings',
            YASR_JS_DIR_INCLUDES . 'shortcodes/rankings.js',
            array('jquery', 'yasr-global-functions', 'wp-element', 'yasr-window-var'),
            YASR_VERSION_NUM,
            true
        );
    }

    /**
     * Load file yasr-log-users-fronted.js
     *
     * @author Dario Curvino <@dudo>
     * @since  3.0.4
     */
    public static function loadLogUsersFrontend() {
        wp_enqueue_script(
            'yasr-log-users-frontend',
            YASR_JS_DIR_INCLUDES . 'yasr-log-users.js',
            array('jquery'),
            YASR_VERSION_NUM,
            true
        );
    }

    /**
     * Load tippy if needed
     *
     * @author Dario Curvino <@dudo>
     * @since  2.8.5
     */
    public static function loadTippy() {
        wp_enqueue_script(
            'tippy',
            YASR_JS_DIR_INCLUDES . 'tippy.all.min.js',
            '',
            '3.6.0',
            true
        );
    }

    /******************* Admin methods *******************/

    /**
     * Load Yasr-admin.js
     *
     * @author Dario Curvino <@dudo>
     * @since 3.0.4
     */
    public static function loadYasrAdmin () {
        wp_enqueue_script(
            'yasradmin',
            YASR_JS_DIR_ADMIN . 'yasr-admin.js',
            array('jquery', 'tippy', 'yasr-global-functions'),
            YASR_VERSION_NUM,
            true
        );
    }

    /**
     * Load yasr-editor-screen.js
     *
     * @author Dario Curvino <@dudo>
     * @since 3.0.4
     */
    public static function loadClassicEditor() {
        wp_enqueue_script(
            'yasr-classic-editor',
            YASR_JS_DIR_ADMIN . 'yasr-editor-screen.js',
            array('jquery', 'yasr-global-functions'),
            YASR_VERSION_NUM,
            true
        );
    }

    /**
     * Enqueue the code editor
     *
     * @author Dario Curvino <@dudo>
     * @since  3.0.4
     */
    public static function loadCodeEditor() {
        $cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/css'));
        wp_localize_script('jquery', 'yasr_cm_settings', $cm_settings);
    }

    /**
     * Load yasr-settings.js
     *
     * @author Dario Curvino <@dudo>
     * @since  3.0.4
     */
    public static function loadAdminSettings () {
        wp_enqueue_script(
            'yasradmin-settings',
            YASR_JS_DIR_ADMIN . 'yasr-settings.js',
            array('jquery', 'yasradmin', 'wp-element'),
            YASR_VERSION_NUM,
            true
        );
    }

    /**
     * Load yasr-pricing-page.js
     *
     * @author Dario Curvino <@dudo>
     * @since  3.0.4
     */
    public static function loadPrincingPage () {
        wp_enqueue_script(
            'yasrjs-pricing',
            YASR_JS_DIR_ADMIN . 'yasr-pricing-page.js',
            array('wp-element', 'yasradmin'),
            YASR_VERSION_NUM,
            true
        );
    }

    //this load guten-block.js, only in admin side
    public function initGutenMisc() {
        //I need to enqueue this only in post-new.php or post.php
        $current_screen = get_current_screen();
        if ($current_screen !== null
            && (property_exists($current_screen, 'base') && $current_screen->base === 'post')
            && ($this->isFseElement($current_screen) === false)
        ) {
            //Script
            wp_enqueue_script(
                'yasr-gutenberg',
                YASR_JS_GUTEN . 'yasr-guten-misc.js',
                array(
                    'wp-blocks',
                    'wp-components',
                    'wp-editor',
                    'wp-edit-post',
                    'wp-element',
                    'wp-i18n',
                    'wp-plugins',
                )
            );
        }
    }

    /**
     * Call all the methods
     *
     * @author Dario Curvino <@dudo>
     * @since 2.8.4
     */
    public function initGutenBlocks() {
        global $wp_version;
        //this function exists since version 5.5.0
        if (!function_exists('register_block_type_from_metadata')) {
           return;
        }

        //Yasr blocks use apiVersion 2, that works since WP 5.6
        //so, if wp version is < 5.6.0, return
        if(version_compare($wp_version, '5.6.0') < 0) {
            return;
        }

        wp_register_script(
            'yasr-shortcodes-blocks',
            YASR_JS_GUTEN_BLOCKS . 'shortcodes.js',
            array(
                'wp-blocks',
                'wp-block-editor',
                'wp-components',
                'wp-element',
                'wp-i18n',
            ),
            1
        );

        $use_register_post_type = false;
        //use register_post_type if version is > 5.8.0
        if(version_compare($wp_version, '5.8.0') >= 0) {
            $use_register_post_type = true;
        }

        $this->registerBlocks($use_register_post_type);
    }


    /**
     * @author Dario Curvino <@dudo>
     * @since  3.0.4
     *
     * @param bool $use_register_post_type
     */
    private function registerBlocks($use_register_post_type = false) {

        //get all content in the dir
        $scan = scandir(YASR_ABSOLUTE_BLOCKS_PATH);

        foreach($scan as $dir) {
            //be sure it is a dir, and it is not . and ..
            if ($dir !== '.' && $dir !== '..' && is_dir(YASR_ABSOLUTE_BLOCKS_PATH . '/' . $dir)) {
                //use register_block_type if wp version > 5.8
                if ($use_register_post_type === true) {
                    register_block_type(YASR_ABSOLUTE_BLOCKS_PATH .'/'.$dir.'/block.json');
                }
                //register_block_type_from_metadata if less
                else {
                    register_block_type_from_metadata(YASR_ABSOLUTE_BLOCKS_PATH .'/'.$dir . '/block.json');
                }
            }

        }
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.8.4
     * @param  $hook
     */
    public function addJsConstantInGutenberg($hook) {
        if (
            ($hook === 'post.php'
                || $hook === 'post-new.php'
                || $hook === 'widgets.php'
                || $hook === 'site-editor.php'
                || $hook === 'appearance_page_gutenberg-edit-site'
            )
            && YasrAdmin::isGutenbergPage()
        ) {

            //create an empty array
            //do not add elements here, use yasrGutenbergConstants method instead
            $constants_array = array();

            //apply filters to empty array
            $constants_array = apply_filters('yasr_gutenberg_constants', $constants_array);

            //sanitize
            $constants_array = filter_var_array($constants_array,FILTER_UNSAFE_RAW);

            if(is_array($constants_array) && !empty($constants_array)) {
                wp_localize_script(
                    'yasradmin', //Where to attach the object
                    'yasrConstantGutenberg',
                    $constants_array
                );
            }
        }
    }

    /**
     * Hook into yasr_gutenberg_constants to add constants used in Gutenberg
     *
     * @author Dario Curvino <@dudo>
     * @param $constants_array
     *
     * @return array
     */
    public function yasrGutenbergConstants($constants_array) {

        //add after
        if (YASR_AUTO_INSERT_ENABLED === 1) {
            $auto_insert = YASR_AUTO_INSERT_WHAT;
        } else {
            $auto_insert = 'disabled';
        }

        $auto_insert_array = array (
            'adminurl'     => get_admin_url(),
            'autoInsert'   => $auto_insert,
            'proVersion'   => json_encode(false),
            'lockedClass'  => 'dashicons dashicons-lock',
            'lockedText'   => esc_html__('This feature is available only in the pro version',
                'yet-another-stars-rating'),
            'isFseElement' => json_encode($this->isFseElement())
        );

        return $constants_array + $auto_insert_array;
    }

    /**
     * Return true if the post type of the current screen base is 'appearance_page_gutenberg-edit-site'
     *
     * @author Dario Curvino <@dudo>
     * @since  2.9.6
     *
     * @param null $current_screen
     *
     * @return bool
     */
    public function isFseElement($current_screen = null) {
        if(!$current_screen) {
            $current_screen = get_current_screen();
        }

        if (property_exists($current_screen, 'base')
            && ($current_screen->base === 'appearance_page_gutenberg-edit-site'
                || $current_screen->base === 'site-editor' )
        ) {
            return true;
        }

        return false;
    }
}