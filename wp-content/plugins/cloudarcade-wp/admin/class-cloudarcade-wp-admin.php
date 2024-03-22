<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://cloudarcade.net
 * @since      1.0.0
 *
 * @package    Cloudarcade_Wp
 * @subpackage Cloudarcade_Wp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cloudarcade_Wp
 * @subpackage Cloudarcade_Wp/admin
 * @author     CloudArcade <hello@redfoc.com>
 */
class Cloudarcade_Wp_Admin {

	private $settings_api;

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->settings_api = new cloudarcadeSettingsAPI;

		// initiate cloudarcade_wp_admin_menu
		add_action('admin_menu', [ $this, 'cloudarcade_wp_admin_menu'] );

		//add_game_custom_fields	
		add_action('add_meta_boxes', [ $this, 'add_game_custom_fields'] );

		// save meta box fields
		add_action('save_post_game', [ $this, 'save_game_custom_fields'] );

		// show message on sync or remove
		add_action('admin_init', [ $this, 'cloudarcade_wp_admin_init'] );

		/**
		 * initiate settings
		 */
		add_action( 'admin_init', array($this, 'settings_init') );

		/** highlight game categories */
		add_action( 'parent_file', array($this, 'prefix_highlight_taxonomy_parent_menu') );
	}

	/**
	 * 
	 * initiate settings output
	 */
	function settings_init() {

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }


	function cloudarcade_wp_admin_settings() {
        echo '<div class="wrap">';

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }

	/**
	 * settings sections
	 */
	function get_settings_sections() {
        $sections = array(
            array(
                'id'    => 'cloudarcade_settings',
                'title' => __( 'Basic Settings', CA_TEXTDOMAIN )
            ),
            array(
                'id'    => 'cloudarcade_connect',
                'title' => __( 'Connect', CA_TEXTDOMAIN )
            ),
       
        );
        return $sections;
    }


	/**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        $settings_fields = array(
            'cloudarcade_settings' => array(
           
                array(
                    'name'              => 'games_per_page',
                    'label'             => __( 'Games per page', CA_TEXTDOMAIN ),
                    'desc'              => __( '', CA_TEXTDOMAIN ),
                    'placeholder'       => __( '10', CA_TEXTDOMAIN ),
                    'min'               => 0,
                    'max'               => 100,
                    'step'              => '1',
                    'type'              => 'number',
                    'default'           => cloudarcade_get_setting('games_per_page'),
                    'sanitize_callback' => 'floatval'
                ),
                
                array(
                    'name'              => 'game_slug',
                    'label'             => __( 'Game slug', CA_TEXTDOMAIN ),
                    'desc'              => __( '', CA_TEXTDOMAIN ),
                    'placeholder'       => __( 'game', CA_TEXTDOMAIN ),
                    'step'              => '1',
                    'type'              => 'text',
                    'default'           => cloudarcade_get_setting('game_slug'),
                    'sanitize_callback' => 'sanitize_text_field'
                ),

                array(
                    'name'              => 'games_display_page',
                    'label'             => __( 'Games display page', CA_TEXTDOMAIN ),
                    'desc'              => __( '', CA_TEXTDOMAIN ),
                    'placeholder'       => __( 'games', CA_TEXTDOMAIN ),
                    'step'              => '1',
                    'type'              => 'text',
                    'default'           => cloudarcade_get_setting('games_display_page'),
                    'sanitize_callback' => 'sanitize_text_field'
                ),

                array(
                    'name'              => 'cloudarcade_domain',
                    'label'             => __( 'Cloudarcade domain', CA_TEXTDOMAIN ),
                    'desc'              => __( '', CA_TEXTDOMAIN ),
                    'placeholder'       => __( 'http://your-domain.com', CA_TEXTDOMAIN ),
                    'step'              => '1',
                    'type'              => 'text',
                    'default'           => cloudarcade_get_setting('cloudarcade_domain'),
                    'sanitize_callback' => 'sanitize_text_field'
                ),

			),
            'cloudarcade_connect' => array(
                array(
                    'name'              => 'token',
                    'label'             => __( 'Token', 'wedevs' ),
                    'desc'              => __( '', 'wedevs' ),
                    'placeholder'       => __( '', 'wedevs' ),
                    'type'              => 'text',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                array(
                    'name'              => 'token_pass',
                    'label'             => __( 'Token Password', 'wedevs' ),
                    'desc'              => __( '', 'wedevs' ),
                    'placeholder'       => __( '', 'wedevs' ),
                    'type'              => 'text',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field'
                )
                
            ),
            
        );

        return $settings_fields;
    }

 
	/**
	 * show delete or sync message
	 */
	function cloudarcade_wp_admin_init() {
		if(isset($_POST['cloudarcade_wp_sync_games']) && check_admin_referer('cloudarcade_wp_sync_action', 'cloudarcade_wp_sync_nonce')) {
			$result = cawp_sync_games();
			add_action('admin_notices', function() use ($result) {
				if($result == 'error'){
					?>
					<div class="notice notice-error is-dismissible">
						<p><?php echo 'Error! Failed to connect the database'; ?></p>
					</div>
					<?php
				} else {
					?>
					<div class="notice notice-success is-dismissible">
						<p><?php echo esc_html($result); ?></p>
					</div>
					<?php
				}
			});
		} else if(isset($_POST['cloudarcade_wp_sync_category']) && check_admin_referer('cloudarcade_wp_sync_action', 'cloudarcade_wp_sync_nonce')) {
			$result = cawp_sync_category();
			add_action('admin_notices', function() use ($result) {
				if($result == 'error'){
					?>
					<div class="notice notice-error is-dismissible">
						<p><?php echo 'Error! Failed to connect the database'; ?></p>
					</div>
					<?php
				} else {
					?>
					<div class="notice notice-success is-dismissible">
						<p><?php echo esc_html($result); ?></p>
					</div>
					<?php
				}
			});
		} else if(isset($_POST['cloudarcade_wp_delete_all']) && check_admin_referer('cloudarcade_wp_sync_action', 'cloudarcade_wp_sync_nonce')) {
			$result = cawp_delete_all_game_posts();
			add_action('admin_notices', function() use ($result) {
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php echo esc_html($result); ?></p>
				</div>
				<?php
			});
		}
	}


	/**
	 * save cutom fields
	 */
	function save_game_custom_fields($post_id) {
		// Save the custom fields when the post is saved or updated
		if (isset($_POST['game_instructions'])) {
			update_post_meta($post_id, 'game_instructions', sanitize_textarea_field($_POST['game_instructions']));
		}
		if (isset($_POST['game_thumb1'])) {
			update_post_meta($post_id, 'game_thumb1', esc_url_raw($_POST['game_thumb1']));
		}
		if (isset($_POST['game_thumb2'])) {
			update_post_meta($post_id, 'game_thumb2', esc_url_raw($_POST['game_thumb2']));
		}
		if (isset($_POST['game_url'])) {
			update_post_meta($post_id, 'game_url', esc_url_raw($_POST['game_url']));
		}
	}
	

	/**
	 * game fields output
	 */
	function display_game_fields($post) {
		// Retrieve the existing values of the custom fields
		$instructions = get_post_meta($post->ID, 'game_instructions', true);
		$thumb1 = get_post_meta($post->ID, 'game_thumb1', true);
		$thumb2 = get_post_meta($post->ID, 'game_thumb2', true);
		$url = get_post_meta($post->ID, 'game_url', true);
	
		// Output the HTML for the custom fields
		?>
		<label for="game_instructions">Instructions:</label>
		<textarea name="game_instructions" id="game_instructions" rows="5"><?php echo esc_textarea($instructions); ?></textarea>
	
		<label for="game_thumb1">Thumbnail 1:</label>
		<input type="text" name="game_thumb1" id="game_thumb1" value="<?php echo esc_attr($thumb1); ?>">
	
		<label for="game_thumb2">Thumbnail 2:</label>
		<input type="text" name="game_thumb2" id="game_thumb2" value="<?php echo esc_attr($thumb2); ?>">
	
		<label for="game_url">URL:</label>
		<input type="text" name="game_url" id="game_url" value="<?php echo esc_attr($url); ?>">
		<?php
	}

	/**
	 * meta box insertion
	 */
	function add_game_custom_fields() {
		add_meta_box(
			'game_fields',          // Meta box ID
			'Game Details',         // Title
			[ $this, 'display_game_fields' ],  // Callback function to display the fields
			'game',                 // Post type to add the meta box to
			'normal',               // Context (normal, side, advanced)
			'default'               // Priority (default, high, low)
		);
	}

	function cloudarcade_wp_admin_games() {
		include CLOUDARCADE_WP_ROOT . 'includes/games-page.php';
	}
	
	function cloudarcade_wp_admin_sync() {
		include CLOUDARCADE_WP_ROOT . 'includes/sync-page.php';
	}

	/**
	 * Create arcade game menu and sub menu
	 */
	function cloudarcade_wp_admin_menu() {
		// Add top-level menu item
		add_menu_page(
			'CloudArcade',         // Page title
			'CloudArcade',         // Menu title
			'manage_options',            // Capability
			'cloudarcade-wp-games',      // Menu slug
			[ $this, 'cloudarcade_wp_admin_games'], // Callback function
			'dashicons-games',           // Icon URL (using a built-in Dashicon)
			5                            // Position (5 will place it below 'Posts')
		);

		// add categories for games to main menu
		add_submenu_page( 
			'cloudarcade-wp-games', 
			esc_html__( 'Game Categories' ), 
			esc_html__( 'Game Categories' ), 
			'manage_categories', 
			'edit-tags.php?taxonomy=game_category'
		);
	
		// Add 'Sync' sub-menu item
		add_submenu_page(
			'cloudarcade-wp-games',     // Parent slug
			'Sync',                     // Page title
			'Sync',                     // Menu title
			'manage_options',           // Capability
			'cloudarcade-wp-sync',      // Menu slug
			[ $this, 'cloudarcade_wp_admin_sync'] // Callback function
		);
		// Add 'Settings' sub-menu item
		add_submenu_page(
			'cloudarcade-wp-games',     // Parent slug
			'Settings',                     // Page title
			'Settings',                     // Menu title
			'manage_options',           // Capability
			'cloudarcade-wp-settings',      // Menu slug
			[ $this, 'cloudarcade_wp_admin_settings'] // Callback function
		);
		
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cloudarcade_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cloudarcade_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cloudarcade-wp-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cloudarcade_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cloudarcade_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cloudarcade-wp-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * fix to highlight Game categories in new menu
	 */
	function prefix_highlight_taxonomy_parent_menu( $parent_file ) {
		if ( get_current_screen()->taxonomy == 'game_category' ) {
			$parent_file = 'cloudarcade-wp-games';
		}

		return $parent_file;
	}

}
