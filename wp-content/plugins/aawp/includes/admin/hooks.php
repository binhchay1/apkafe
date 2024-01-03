<?php
/**
 * Admin Hooks
 *
 * @since       3.4.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Checking scheduled events frequently when visiting AAWP pages
 */
function aawp_admin_check_scheduled_events() {

    if ( ! isset( $_GET['page'] ) || strpos( $_GET['page'], 'aawp') === false )
        return;

    $last_check = get_transient( 'aawp_check_scheduled_events' );

    if ( empty( $last_check ) ) {
        aawp_check_scheduled_events();

        set_transient( 'aawp_check_scheduled_events', true, 60 * 60 * 24 ); // 1 day
    }
}
add_action( 'admin_init', 'aawp_admin_check_scheduled_events' );

/**
 * Temporarily redirect dashboard page
 */
function aawp_admin_temporarily_redirect_dashboard_page() {

    if ( isset( $_GET['page'] ) && $_GET['page'] == 'aawp-dashboard' && defined( 'AAWP_ADMIN_SETTINGS_URL' ) ) {
        wp_redirect( AAWP_ADMIN_SETTINGS_URL );
        exit;
    }
}
add_action( 'admin_init', 'aawp_admin_temporarily_redirect_dashboard_page' );

function aawp_admin_remove_first_submenu_page() {
    remove_submenu_page( 'index.php', 'my-welcome' );
}
add_action( 'admin_head', 'aawp_admin_remove_first_submenu_page' );

/*
 * Add admin notice
 */
function aawp_admin_notices() {

    $latest_version = get_option( 'aawp_version', false );

    $notices = array();

    // Dependencies missing
    if ( ! extension_loaded('soap') ) {

        $message = sprintf( wp_kses( __( 'Your server is missing the PHP SOAP extension. <a href="%s" target="_blank">Please take a look at this article</a> in order to fix it.', 'aawp' ), array(  'a' => array( 'href' => array(), 'target' => '_blank' ) ) ), aawp_get_page_url( 'docs:php_soap' ) );

        $notices[] = array(
            'force' => false,
            'type' => 'error',
            'dismiss' => false,
            'message' => $message
        );
    }

    // Upgrade 2.0 rebuild
    if ( get_option('AAWP_aws_access_key', null) || get_option('AAWP_default_provider', null) ) {

        $message = sprintf( wp_kses( __( 'Substantial updates for the Amazon Affiliate plugin require an <a href="%s">automated upgrade</a> of the plugin database in order to continue working.', 'aawp' ), array(  'a' => array( 'href' => array() ) ) ), add_query_arg( 'aawp_admin_action', 'upgrade_rebuild', AAWP_ADMIN_SETTINGS_URL ) );

        $notices[] = array(
            'type' => 'info',
            'dismiss' => false,
            'message' => $message
        );
    }

    /*
    // Upgrade 3.6 rebuild
    if ( $latest_version && version_compare( $latest_version, '3.6.0', '<' ) ) {

        $message = sprintf( wp_kses( __( 'Please <a href="%s">click here</a> in order to run an automated database upgrade.', 'aawp' ), array(  'a' => array( 'href' => array() ) ) ), add_query_arg( 'aawp_admin_action', 'upgrade_rebuild_cpt', AAWP_ADMIN_SETTINGS_URL ) );

        $notices[] = array(
            'type' => 'info',
            'dismiss' => false,
            'message' => $message
        );
    }
    */

    // After update v3.9
    if ( ! empty( get_transient( 'aawp_plugin_update_v3_9_completed' ) ) ) {
        delete_transient( 'aawp_plugin_update_v3_9_completed' );

        ob_start();
        ?>
        <p>
            <?php _e('This plugin update brings support for the new Amazon Product Advertising API v5. This change has the following effects:', 'aawp' ); ?>
        </p>
        <ul style="list-style: decimal inside; margin-left: 1rem;">
            <li><?php printf( wp_kses( __( 'API access keys created using the old "Amazon AWS Console" are no longer functional. If you haven\'t created a new API key yet, you can do so directly from your Amazon Associates account (<a href="%s" target="_blank">see instructions</a>).', 'aawp' ), array(  'a' => array( 'href' => array(), 'target' => '_blank' ) ) ), esc_url( aawp_get_page_url( 'docs:api_keys' ) ) ); ?></li>
            <li><?php _e('The database tables of our plugin had to be completely emptied and recreated. This means that all products and lists have to be fetched from the API again. The loading time of your website may increase at the beginning.', 'aawp' ); ?></li>
            <li><?php _e('Unfortunately, some functions may no longer be available or work different now. This means that you may have to adjust existing shortcodes.', 'aawp' ); ?></li>
        </ul>
        <p>
            <?php printf( wp_kses( __( 'We have collected everything worth knowing for you <a href="%s" target="_blank">here</a>.', 'aawp' ), array(  'a' => array( 'href' => array(), 'target' => '_blank' ) ) ), esc_url( aawp_get_page_url( 'docs:amazon_apiv5' ) ) ); ?>
        </p>
        <?php
        $update_v3_9_message = ob_get_clean();

        $notices[] = array(
            'type' => 'warning',
            'dismiss' => true,
            'message' => sprintf( esc_html__( 'Plugin updated to version %s successfully.', 'my-text-domain' ), AAWP_VERSION ) . $update_v3_9_message
        );
    }

    // AMP plugin
    if ( defined( 'AMPFORWP_VERSION' ) || defined( 'AAWP_FOR_AMP_VERSION' ) || class_exists( 'Better_AMP' ) ) {

        $amp_plugin_search_url = add_query_arg( array(
            's' => 'AMP Project Contributors',
            'tab' => 'search',
            'type' => 'term',
        ), admin_url( 'plugin-install.php' ) );

        $message = sprintf( wp_kses( __( 'Our plugin has built-in <strong>Accelerated Mobile Pages</strong> support for the <a href="%s">official AMP plugin</a>. We recommend to <u>do not use any other plugin</u>.', 'aawp' ), array(  'a' => array( 'href' => array() ), 'u' => array(), 'strong' => array() ) ), $amp_plugin_search_url );

        $notices[] = array(
            'type' => 'warning',
            'dismiss' => true,
            'force' => false,
            'message' => $message
        );
    }

    // Actions
    $admin_notice = ( isset( $_GET['aawp_admin_notice'] ) ) ? $_GET['aawp_admin_notice'] : null;

    if ( $admin_notice === 'upgrade_success' ) {

        $notices[] = array(
            'type' => 'success',
            'dismiss' => true,
            'message' => __('Plugin database upgrade finished. Enjoy all the new options!', 'aawp')
        );
    }

    if ( $admin_notice === 'reset_success' ) {

        $notices[] = array(
            'type' => 'success',
            'dismiss' => true,
            'message' => __('Plugin settings has been successfully reset.', 'aawp')
        );
    }

    // Debug
    /*
    $notices[] = array(
        'type' => 'warning',
        'dismiss' => false,
        'message' => __('Plugin settings has been successfully reset.', 'aawp')
    );
    */

    $notices = apply_filters( 'aawp_admin_notices', $notices );

    $is_plugin_page = aawp_admin_is_plugin_page();

    // Output messages
    if ( sizeof( $notices ) > 0 ) {
        foreach ( $notices as $notice ) {

            // Maybe showing the notice on AAWP related admin pages only
            if ( isset( $notice['force'] ) && false === $notice['force'] && ! $is_plugin_page )
                continue;

            $classes = 'notice';

            if ( ! empty( $notice['type'] ) )
                $classes .= ' notice-' . $notice['type'];

            if ( isset( $notice['dismiss'] ) && true === $notice['dismiss'] )
                $classes .= ' is-dismissible';

            ?>
            <div class="<?php echo $classes; ?>">
                <p><strong>AAWP:</strong>&nbsp;<?php echo $notice['message']; ?></p>
            </div>
            <?php
        }
    }
}

add_action( 'admin_notices', 'aawp_admin_notices' );

add_action('admin_footer', function() {
    ?>
    <script type="text/javascript">
        /* <![CDATA[ */
        var aawp_admin_lang = "<?php echo ( aawp_is_lang_de() ) ? 'de' : 'en'; ?>";
        /* ]]> */
    </script>
    <?php
});