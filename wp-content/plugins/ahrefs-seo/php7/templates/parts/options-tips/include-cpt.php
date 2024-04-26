<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

/*
We show this tip after new CPT detected or after WooCommerce Products detected.
*/

$items       = [];
$locals      = Ahrefs_Seo_View::get_template_variables();
$new_cpt     = (array) $locals['new_cpt'];
$for_product = in_array( 'product', $new_cpt, true );
if ( ! $for_product || count( $new_cpt ) > 1 ) {
	$items[] = __( 'Custom Post Types', 'ahrefs-seo' );
}
// Products exists and not included.
if ( $for_product ) {
	$items[] = __( 'WooCommerce Product Pages', 'ahrefs-seo' );
}
$title = implode( ' & ', $items );
?>
<!-- include Custom Post Types notice -->
<div class="ahrefs-content-notice">
	<div class="caption">
	<?php
	/* translators: %s: one or more things, recommended to include */
	echo esc_html( sprintf( __( 'Tip: Include the %s', 'ahrefs-seo' ), $title ) );
	?>
	</div>
	<div class="text">
	<?php
	/* translators: %s: one or more things, recommended to include */
	echo esc_html( sprintf( __( 'We’ve detected %s on your site. Check the options below to include them in the audit.', 'ahrefs-seo' ), $title ) );
	?>
	</div>
	<div class="buttons">
		<a class="button button-primary" href="#" id="options_cpt_tip_got_it"><?php esc_html_e( 'Got it', 'ahrefs-seo' ); ?></a>
	</div>
</div>
