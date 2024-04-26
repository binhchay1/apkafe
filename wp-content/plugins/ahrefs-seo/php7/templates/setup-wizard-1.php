<?php
/**
 * Setup Wizard template, step 1.
 */

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
$view   = Ahrefs_Seo::get()->get_view();

if ( Ahrefs_Seo_Api::get()->is_disconnected() ) { // no active token.
	$view->show_part(
		'options/ahrefs-code',
		[
			'page_nonce' => $locals['page_nonce'],
			'error'      => $locals['error'],
		]
	);
} else { // token is set and active.
	$view->show_part(
		'options/ahrefs-connected',
		[
			'page_nonce'      => $locals['page_nonce'],
			'message'         => $locals['error'],
			'show_button'     => true,
			'disconnect_link' => 'wizard', // do not translate.
		]
	);
}
