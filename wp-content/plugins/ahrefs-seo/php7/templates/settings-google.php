<?php

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Disconnect_Reason\Disconnect_Reason_Google;

$locals = Ahrefs_Seo_View::get_template_variables();
$view   = Ahrefs_Seo::get()->get_view();

if ( ! isset( $locals['button_title'] ) ) {
	$locals['button_title'] = __( 'Continue', 'ahrefs-seo' );
}

$disconnect_url = add_query_arg(
	[ 'disconnect-analytics' => wp_create_nonce( $locals['page_nonce'] ) ],
	'settings' === $locals['disconnect_link'] ? Links::settings( Ahrefs_Seo_Screen_Settings::TAB_ANALYTICS ) : Links::wizard_step( 2 )
);
if ( $locals['token_set'] && ! $locals['no_ga'] && ! $locals['no_gsc'] ) {
	$view->show_part(
		'options/google-connected',
		[
			'is_wizard'          => $locals['is_wizard'],
			'page_nonce'         => $locals['page_nonce'],
			'error'              => $locals['error'],
			'button_title'       => $locals['button_title'],
			'preselect_accounts' => $locals['preselect_accounts'],
			'gsc_uses_uppercase' => $locals['gsc_uses_uppercase'],
			'ga_not_urlencoded'  => $locals['ga_not_urlencoded'],
			'ga_uses_full_url'   => $locals['ga_uses_full_url'],
			'disconnect_link'    => $locals['disconnect_link'],
			'disconnect_url'     => $disconnect_url,
		]
	);
	if ( ! Ahrefs_Seo_Analytics::get()->get_data_tokens()->is_token_set() ) { // token was disconnected while we load accounts list...
		( new Disconnect_Reason_Google() )->save_reason( null ); // do not show same message second time.
		// hide existing but empty block with account details.
		?>
		<script>
			jQuery(function() {
				jQuery( '#ahrefs_seo_google_connected' ).hide();
			});
		</script>
		<?php
		// show block with Google connection.
		$view->show_part(
			'options/google-missed',
			$locals
		);
	}
} else {
	$view->show_part(
		'options/google-missed',
		$locals
	);
}
