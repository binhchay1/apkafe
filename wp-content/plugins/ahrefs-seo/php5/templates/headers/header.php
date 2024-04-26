<?php

namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
?>
<div id="ahrefs_seo_screen" class="<?php echo isset( $locals['header_class'] ) ? esc_attr( implode( ' ', $locals['header_class'] ) ) : ''; ?>">

<?php 