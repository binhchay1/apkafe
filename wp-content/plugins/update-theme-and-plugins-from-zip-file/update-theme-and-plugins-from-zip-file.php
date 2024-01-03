<?php
/*
 * Plugin Name: Update Theme and Plugins from Zip File
 * Version: 1.1.0
 * Description: Quickly and easily upgrade plugins and themes from a zip file without having to remove or delete their folders first, and avoid the "destination already exists" error message.
 * Author: Jeff Sherk
 * Author URI: http://www.iwebss.com/contact
 * Plugin URI: http://www.iwebss.com/wordpress/plugins/1431-update-theme-plugins-zip-file-wordpress-plugin
 * Donate link: https://www.paypal.me/jsherk/10usd
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 */

 /* ****************************************************************************** */
if ( is_admin() ) {
    require( dirname( __FILE__ ) . '/admin.php' );
}
  
 /* ****************************************************************************** */
function jdsutpzip_add_links_to_admin_plugins_page($links) {

	$donate_url = 'https://www.paypal.me/jsherk/10usd';
	$donate_url = esc_url($donate_url);
	$donate_link = '<a href="'.$donate_url.'">DONATE</a>'; //DONATE
	
	array_unshift( $links, $donate_link ); //DONATE

	$url = get_admin_url() . 'options-general.php?page=update-theme-and-plugins-from-zip-file';
	$url = esc_url($url);
	$settings_link = '<a href="'.$url.'">Settings</a>';

	array_unshift( $links, $settings_link );

	return $links;
}
add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'jdsutpzip_add_links_to_admin_plugins_page' );


/* ****************************************************************************** */
function jdsutpzip_add_meta_to_admin_plugins_page( $links, $file ) {

	if ( strpos( $file, plugin_basename(__FILE__) ) !== false ) {

		$donate_url = 'https://www.paypal.me/jsherk/10usd';
		$donate_url = esc_url($donate_url);

		$url = get_admin_url() . 'options-general.php?page=update-theme-and-plugins-from-zip-file';
		$url = esc_url($url);
		
		$pluginbut = plugins_url( 'dbut.png', __FILE__ );
		$new_links = array('<a href="'.$url.'">Settings</a>', '<a href="https://wordpress.org/support/plugin/update-theme-and-plugins-from-zip-file/reviews/#new-post">Leave a 5 star Review</a>','<a style="" href="'.$donate_url.'">Thanks for supporting me! <img style="vertical-align:bottom;" height="30" src="'.$pluginbut.'"></a>'); //DONATE
			
		$links = array_merge( $links, $new_links );

	}
	
	return $links;
}
add_filter( 'plugin_row_meta', 'jdsutpzip_add_meta_to_admin_plugins_page', 10, 2 );


/* ****************************************************************************** */
function jdsutpzip_add_admin_settings_menu() {
	add_options_page( 'Update Theme and Plugins from Zip File by Jeff Sherk', 'Update Theme and Plugins from Zip File', 'activate_plugins', 'update-theme-and-plugins-from-zip-file', 'jdsutpzip_update_theme_and_plugins_from_zip_file_options' );
}
add_action( 'admin_menu', 'jdsutpzip_add_admin_settings_menu' );


/* ****************************************************************************** */
function jdsutpzip_update_theme_and_plugins_from_zip_file_options() {

		$settings_saved = false;
	
		if ( isset( $_POST[ 'save' ] ) ) {
	
			$post_data = sanitize_text_field($_POST[ 'jdsutpzip_backup_enabled' ]);
			update_option( 'jdsutpzip_backup_enabled', $post_data, true );

			$post_data = sanitize_text_field($_POST[ 'jdsutpzip_backup_location' ]);
			update_option( 'jdsutpzip_backup_location', $post_data, true );

			$settings_saved = true;
	
		}
		?>

		<?php if ( $settings_saved ) : ?>
			<script>
				jQuery(document).ready(function($){
					$('.fadeout').click(function(){$(this).fadeOut('fast');}); //fadeout on click
					setTimeout(function(){$('.fadeout').fadeOut("slow");},5000); //or fadeout after 5 seconds
				});
			</script>
			<!--
            <div id="message" class="updated fadeout">
				<p><strong><?php _e( 'Options saved.' ) ?></strong></p>
			</div>
            -->
		<?php endif ?>

		<form method="post" action="">

		<h1><?php _e( 'UPDATE THEME AND PLUGINS FROM ZIP FILE' ); ?></h1>
		<p>
			<?php
			$checked = "";
			if (get_option("jdsutpzip_backup_enabled")) {
				$checked = 'checked="checked"';
			}
			?>
			<input type="checkbox" id="jdsutpzip_backup_enabled" name="jdsutpzip_backup_enabled" <?php echo $checked; ?> >
			<label for="jdsutpzip_backup_enabled"><?php _e( 'Check to ENABLE saving a backup copy of old version of theme or plugin when you upload new one' ) ?></label>
			<br><span style="font-size: 85%; font-style: italic;">When enabled, backup ZIP file of theme or plugin will be saved in current WordPress UPLOAD directory or in the THEME directory. A link will be provided when you do the upgrade.<br>When disabled, old version of theme or plugin will be deleted and will NOT be backed up.<br></span>
		</p>

		<!--
        <p>
			<?php
			//$redirect = get_option("jdsutpzip_backup_location");
			?>
			<label for="jdsutpzip_backup_location"><?php //_e( 'Enter the location on your sever where you want the backup of old version of theme or plugin:' ) ?></label>
			<input type="textbox" id="jdsutpzip_backup_location" name="jdsutpzip_backup_location" value="<?php //echo $redirect; ?>" size="50">
			<br><span style="font-size: 85%; font-style: italic;">Default server location is: <br></span>
		</p>
        -->

		<?php $pluginbut = plugins_url( 'dbut.png', __FILE__ ); ?>
		<br><hr>How much is this plugin worth to you? A suggested <a href="https://www.paypal.me/jsherk/10usd">donation of $10 or more</a> will help me feed my kids, pay my bills and keep this plugin updated!<br><a href="https://www.paypal.me/jsherk/10usd"><img width="175" src="<?php echo $pluginbut; ?>"></a><hr>

		<?php if ( $settings_saved ) : ?>
			<div id="message" class="updated fadeout">
				<p><strong><?php _e( 'Options saved.' ) ?></strong></p>
			</div>
		<?php endif ?>

		<p class="submit">
			<input class="button-primary" name="save" type="submit" value="<?php _e( 'Save Changes' ) ?>" />
		</p>
		</form>

<?php
}
?>