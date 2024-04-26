<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Options\Settings_Scope;
?>
<div class="block-title">
<?php
esc_html_e( 'Scope of audit', 'ahrefs-seo' );
?>
</div>
<div class="block-text">
	<?php
	esc_html_e( 'Uncheck the categories below that don’t need organic search performance improvement, like "Contacts" or "Privacy Policy". This will give you a more accurate analysis and better recommendations.', 'ahrefs-seo' );
	?>
</div>
<?php
Settings_Scope::get()->render_view();
?>
<script type="text/javascript">
	jQuery('.subitems-n input:disabled').each(function(){
		jQuery(this).prop('disabled',false).removeAttr('disabled');
	})
</script>
<?php 