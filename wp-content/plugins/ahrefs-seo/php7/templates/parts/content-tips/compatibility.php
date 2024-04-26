<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$last = Ahrefs_Seo_Compatibility::get_current_incompatibility();
if ( ! is_null( $last ) ) {
	$last->show();
}

