<?php

namespace ahrefs\AhrefsSeo;

$view = Ahrefs_Seo::get()->get_view();
?>
<!-- show loader -->
<style>#loader_while_accounts_loaded{position: absolute;top:40vh;max-width:800px;}</style>
<div class="row-loader loader-transparent" id="loader_while_accounts_loaded"><div class="loader"></div></div>
<?php
echo '<!-- padding ' . str_pad( '', 10240, ' ' ) . ' -->'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
// show loader while settings screen is loading, will hide it using inline css at the end of settings block.
$view->flush();