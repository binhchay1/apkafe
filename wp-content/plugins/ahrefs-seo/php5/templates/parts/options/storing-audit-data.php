<?php

namespace ahrefs\AhrefsSeo;

$save_data = ( new Ahrefs_Seo_Uninstall() )->get_option_save_data();
?>
<hr class="shadow block-content">

<div class="block-subtitle">
<?php
esc_html_e( 'Storing audit data', 'ahrefs-seo' );
?>
</div>
<div class="block-text block-content">
	<?php
	esc_html_e( "All audit data is stored in your website's database and is never sent to Ahrefs. This includes your GA and GSC statistics. By enabling this option, you're choosing to clear this data when deleting the plugin. Disable this option if you are temporarily uninstalling the plugin and want your settings and previous audit data to be preserved on your next installation.", 'ahrefs-seo' );
	?>
</div>

<input type="hidden" name="remove_data_present" value="1">
<div class="input-wrap block-content" id="remove_data_wrap">
	<input id="remove_data" type="checkbox" name="remove_data" value="1" class="checkbox-input" 
	<?php
	checked( ! $save_data );
	disabled( ! current_user_can( Ahrefs_Seo::CAP_SETTINGS_DATA_SAVE ) );
	?>
	>
	<label for="remove_data" class="help">
	<?php
	esc_html_e( 'Remove my audit data when deleting the plugin', 'ahrefs-seo' );
	?>
	</label>
	<?php
	if ( ! $save_data && isset( $_GET['from-plugins-list'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- blink with input if not enabled already.
		?>
		<script type="text/javascript">
			jQuery( function() {
				jQuery('#remove_data_wrap label, #remove_data_wrap input').removeClass( 'item-flash' ).addClass( 'long' );
				setTimeout( function() { jQuery('#remove_data_wrap label, #remove_data_wrap input').addClass( 'item-flash' ); }, 100 );
			});
		</script>
		<?php
	}
	?>
</div>
<?php 