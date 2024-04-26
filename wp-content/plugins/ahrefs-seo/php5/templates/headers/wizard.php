<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Messages\Message;
$locals = Ahrefs_Seo_View::get_template_variables();
/**
 * Show step header
 *
 * @param int    $step Step number.
 * @param int    $active_step Active step number.
 * @param string $title Step title.
 * @return void
 */
function step_header( $step, $active_step, $title ) {
	$href    = '';
	$classes = [ 'group', "group-{$step}" ];
	if ( $active_step > $step ) {
		$classes[] = 'finished';
		$href      = Links::wizard_step( $step );
	}
	if ( $active_step < $step ) {
		$classes[] = 'inactive';
	}
	if ( $href ) {
		printf( '<a class="%s" href="%s">', esc_attr( implode( ' ', $classes ) ), esc_attr( $href ) );
	} else {
		printf( '<div class="%s">', esc_attr( implode( ' ', $classes ) ) );
	}
	?>
	<span class="icon-ok"><span class="oval"><span class="number">
	<?php
	echo esc_html( "{$step}" );
	?>
	</span><span class="icon">&#10004;</span></span></span>
	<span class="account">
	<?php
	echo esc_html( $title );
	?>
	</span>
	<?php
	echo $href ? '</a>' : '</div>';
}
?>
<div id="ahrefs_seo_screen" class="setup-wizard 
<?php
echo isset( $locals['header_class'] ) ? esc_attr( implode( ' ', $locals['header_class'] ) ) : ''; ?>">

<div class="ahrefs-header">

	<div>
		<img src="<?php echo esc_attr( AHREFS_SEO_IMAGES_URL . 'logo.svg' ); ?>" alt="<?php esc_attr_e( 'Content Audit', 'ahrefs-seo' ); ?>" class="logo">
	</div>
	<div class="steps">
		<?php
		step_header( 1, $locals['step'], __( 'Ahrefs account', 'ahrefs-seo' ) );
		step_header( 2, $locals['step'], __( 'Google account', 'ahrefs-seo' ) );
		step_header( 3, $locals['step'], __( 'Content audit', 'ahrefs-seo' ) );
		?>
	</div>

	<div class="right">
	</div>
</div>

<h1>
<?php
echo esc_html( $locals['title'] );
?>
</h1>
<?php
// Check for compatibility issues.
if ( ! Ahrefs_Seo_Compatibility::quick_compatibility_check() ) {
	Ahrefs_Seo::get()->get_view()->show_part( 'content-tips/compatibility' );
}
// show Google errors.
if ( ! empty( $locals['error'] ) && 2 === $locals['step'] ) {
	if ( is_string( $locals['error'] ) ) {
		?>
		<div class="updated notice error is-dismissible"><p>
				<?php
				echo esc_html( $locals['error'] );
				?>
			</p></div>
		<?php
	} else {
		if ( $locals['error'] instanceof Message ) {
			$locals['error']->show();
		}
	}
}