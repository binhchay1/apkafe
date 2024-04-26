<?php

namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
?>
<div class="content-tip-wrap" style="display: none;" data-id="<?php echo esc_attr( $locals['tip_id'] ); ?>">
<?php 