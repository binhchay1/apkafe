<?php

namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
$view   = Ahrefs_Seo::get()->get_view();
if ( Ahrefs_Seo_Api::get()->is_disconnected() ) { // no active token.
	$view->show_part( 'options/ahrefs-code', $locals );
} else { // no active token.
	$view->show_part( 'options/ahrefs-connected', $locals );
}