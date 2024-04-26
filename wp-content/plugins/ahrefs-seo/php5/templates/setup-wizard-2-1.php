<?php

namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
$view   = Ahrefs_Seo::get()->get_view();
$view->show_part(
	'options/google-missed',
	[
		'page_nonce'  => $locals['page_nonce'],
		'error'       => $locals['error'],
		'no_accounts' => $locals['no_accounts'],
	]
);