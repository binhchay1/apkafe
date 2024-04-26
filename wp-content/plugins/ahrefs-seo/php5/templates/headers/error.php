<?php

namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
?>
<div id="ahrefs_seo_screen" class="setup-screen 
<?php
echo isset( $locals['header_class'] ) ? esc_attr( implode( ' ', $locals['header_class'] ) ) : ''; ?>">

<div class="ahrefs-header">
	<div>
		<img src="<?php echo esc_attr( AHREFS_SEO_IMAGES_URL . 'logo.svg' ); ?>" alt="<?php esc_attr_e( 'Content Audit', 'ahrefs-seo' ); ?>" class="logo">
	</div>
</div>

<div class="error-content">

<h1 class="ahrefs-seo-for-wordpress">
<?php
echo esc_html( $locals['title'] );
?>
</h1>

<?php
// we will close those divs at the footer.
define( 'AHREFS_SEO_FOOTER_ADDITIONAL_DIVS', 2 );