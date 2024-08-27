<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://boomdevs.com/
 * @since      1.0.0
 *
 * @package    Boomdevs_Toc
 * @subpackage Boomdevs_Toc/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Boomdevs_Toc
 * @subpackage Boomdevs_Toc/includes
 * @author     BoomDevs <admin@boomdevs.com>
 */
class Boomdevs_Toc {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Boomdevs_Toc_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;
    
    /**
     * The real name of this plugin.
     *
     * @access   protected
     * @var      string    $plugin_full_name    The full punctual name of this plugin.
     */
	protected $plugin_full_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {

        if ( defined( 'BOOMDEVS_TOC_VERSION' ) ) {
            $this->version = BOOMDEVS_TOC_VERSION;
        } else {
            $this->version = '1.3.21';
        }
        
        if ( defined( 'BOOMDEVS_FULL_NAME' ) ) {
            $this->plugin_full_name = BOOMDEVS_FULL_NAME;
        } else {
            $this->plugin_full_name = 'TOP Table Of Contents';
        }

        if ( defined( 'BOOMDEVS_TOC_NAME' ) ) {
            $this->plugin_name = BOOMDEVS_TOC_NAME;
        } else {
            $this->plugin_name = 'boomdevs-toc';
        }

        $this->load_dependencies();
        $this->set_locale();
        $this->register_settings();
        $this->register_shortcode();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->register_widgets();
        $this->register_post_type();
        $this->register_ajax_hooks();

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Boomdevs_Toc_Loader. Orchestrates the hooks of the plugin.
     * - Boomdevs_Toc_i18n. Defines internationalization functionality.
     * - Boomdevs_Toc_Admin. Defines all hooks for the admin area.
     * - Boomdevs_Toc_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-boomdevs-toc-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-boomdevs-toc-i18n.php';
        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-boomdevs-toc-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-boomdevs-toc-public.php';

        /**
         * The class responsible for loading codestar framework of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'libs/codestar-framework/codestar-framework.php';

        /**
         * The class responsible for loading all the table of content settings of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-boomdevs-toc-settings.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-boomdevs-toc-metabox.php';


        /**
         * The class responsible for loading shortcode of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-boomdevs-toc-shortcode.php';

        /**
         * The class responsible for loading widgets of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-boomdevs-toc-widgets.php';

        /**
         * The class responsible for loading widgets of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-boomdevs-toc-post-type.php';

        /**
         * The class responsible for loading widgets of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-boomdevs-toc-ajax.php';

        $this->loader = new Boomdevs_Toc_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Boomdevs_Toc_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Boomdevs_Toc_i18n();

        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

    }

    /**
     * Register plugin settings.
     *
     * @access   private
     */
    private function register_settings() {

        $plugin_settings = new Boomdevs_Toc_Settings();
        $plugin_settings->generate_settings();
    }

    /**
     * Register plugin shortcode.
     *
     * @access   private
     */
    private function register_shortcode() {
        
        $plugin_shortcode = new Boomdevs_Toc_Shortcode();
        $plugin_shortcode->shortcode_register();
        $this->loader->add_filter( 'the_content', $plugin_shortcode, 'boomdevs_toc_auto_id_headings' );
    }

    /**
     * Register plugin widgets.
     *
     * @access   private
     */
    private function register_widgets() {
        
        $plugin_widgets = new Boomdevs_Toc_Widgets();
        $this->loader->add_action( 'widgets_init', $plugin_widgets, 'boomdevs_toc_widget' );
    }

    /**
     * Register plugin Pages.
     *
     * @access   private
     */
    private function register_post_type() {
        
        $plugin_pages = new Boomdevs_Toc_Post_Type();
        $this->loader->add_filter( 'the_content', $plugin_pages, 'boomdevs_toc_post_types' );
    }

    /**
     * Register plugin Pages.
     *
     * @access   private
     */
    private function register_ajax_hooks() {

        $plugin_ajax = new Boomdevs_Toc_Ajax();
        $this->loader->add_action('wp_ajax_nopriv_get_premade_layout', $plugin_ajax, 'get_premade_layout');
        $this->loader->add_action('wp_ajax_get_premade_layout', $plugin_ajax, 'get_premade_layout');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new Boomdevs_Toc_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_filter( 'plugin_action_links_' . BOOMDEVS_BASE_NAME, $plugin_admin, 'bd_toc_add_action_plugin', 15, 2 );

    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        $plugin_public = new Boomdevs_Toc_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Boomdevs_Toc_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

}