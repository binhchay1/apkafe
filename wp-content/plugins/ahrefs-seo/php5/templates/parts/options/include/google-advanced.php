<?php

namespace ahrefs\AhrefsSeo;

$locals             = Ahrefs_Seo_View::get_template_variables();
$gsc_uses_uppercase = $locals['gsc_uses_uppercase'];
$ga_not_urlencoded  = $locals['ga_not_urlencoded'];
$ga_uses_full_url   = $locals['ga_uses_full_url'];
?>
<hr class="hr-shadow google-advanced">
<div class="input-wrap block-content google-advanced">
	<a href="#" class="show-collapsed block-subtitle">
	<?php
	esc_html_e( 'Advanced settings', 'ahrefs-seo' );
	?>
	</a>
	<div class="collapsed-wrap">
		<div class="block-text block-content">
			<label><input type="checkbox" name="gsc_uses_uppercase" value="1"
			<?php
			checked( $gsc_uses_uppercase );
			?>
			>&nbsp;
<?php
esc_html_e( 'My GSC uses uppercase URL encoded characters', 'ahrefs-seo' );
?>
</label>
		</div>
		<div class="block-text block-content">
			<label><input type="checkbox" name="ga_not_urlencoded" value="1"
			<?php
			checked( $ga_not_urlencoded );
			?>
			>&nbsp;
<?php
esc_html_e( 'My GA does not use URL encoding', 'ahrefs-seo' );
?>
</label>
		</div>
		<div class="block-text block-content">
			<label><input type="checkbox" name="ga_uses_full_url" value="1"
			<?php
			checked( $ga_uses_full_url );
			?>
			>&nbsp;
<?php
esc_html_e( 'My GA reports full page URLs that include the domain name', 'ahrefs-seo' );
?>
</label>
		</div>
	</div>
</div>
<?php 