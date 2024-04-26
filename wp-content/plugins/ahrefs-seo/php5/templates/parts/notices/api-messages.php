<?php

namespace ahrefs\AhrefsSeo;

$locals   = Ahrefs_Seo_View::get_template_variables();
$view     = Ahrefs_Seo::get()->get_view();
$messages = isset( $locals['messages'] ) ? $locals['messages'] : Ahrefs_Seo_Errors::get_saved_messages( null, 'error' );
// use provided messages or show any errors.
if ( ! empty( $messages ) ) {
	$view->show_part( 'notices/please-contact', [ 'messages' => $messages ] );
}