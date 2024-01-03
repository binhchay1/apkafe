<?php
/**
 * Cronjobs
 *
 * @package     AAWP\Functions\Components
 * @since       3.2.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Register cronjobs component
 */
function aawp_settings_register_cronjobs_component( $functions ) {

    $functions[] = 'cronjobs';

    return $functions;
}
add_filter( 'aawp_settings_functions', 'aawp_settings_register_cronjobs_component' );

/*
 * Adding cronjob settings to settings support page
 */
function aawp_support_cronjobs() {

    $options_support = get_option( 'aawp_support', array() );

    $disable_wp_cron = ( isset ( $options_support['disable_wp_cron'] ) && $options_support['disable_wp_cron'] == '1' ) ? 1 : 0;

    $cronjob_key = get_option( 'aawp_cronjob_key', null );

    if ( empty ( $cronjob_key ) ) {
        $cronjob_key = md5( time() );
        update_option( 'aawp_cronjob_key', $cronjob_key );
    }
    ?>
    <tr class="alternate">
        <th><?php _e('Deactivating built-in cronjobs', 'aawp'); ?><span style="color: orange;">*</span></th>
        <td>
            <input type="checkbox" id="aawp_support_disable_wp_cron" name="aawp_support[disable_wp_cron]" value="1" <?php echo($disable_wp_cron == 1 ? 'checked' : ''); ?>>
            <label for="aawp_support_disable_wp_cron"><?php _e("Check if you <u>don't</u> want to use the built-in cronjobs for updating the cache", 'aawp'); ?></label>
        </td>
    </tr>
    <tr>
        <th><?php _e('Using manual cronjobs', 'aawp'); ?><span style="color: orange;">*</span></th>
        <td>
            <p>
                <?php _e('Renewing product data', 'aawp'); ?><br />
                <code style="font-size: 12px;"><?php echo AAWP_PLUGIN_URL; ?>public/jobs/update_cache.php?key=<?php echo $cronjob_key; ?></code>
            </p>
            <p>
                <?php _e('Renewing product ratings', 'aawp'); ?><br />
                <code style="font-size: 12px;"><?php echo AAWP_PLUGIN_URL; ?>public/jobs/update_rating_cache.php?key=<?php echo $cronjob_key; ?></code>
            </p>
        </td>
    </tr>
    <?php
}
add_action( 'aawp_support_cache_table_rows', 'aawp_support_cronjobs' );