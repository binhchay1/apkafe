<?php
/**
 * Settings header template.
 * Include tabs.
 */

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Messages\Message;

$locals = Ahrefs_Seo_View::get_template_variables();
?>
<div id="ahrefs_seo_screen" class="setup-screen <?php echo isset( $locals['header_class'] ) ? esc_attr( implode( ' ', $locals['header_class'] ) ) : ''; ?>">

<div class="ahrefs-header">
	<div>
		<img src="<?php echo esc_attr( AHREFS_SEO_IMAGES_URL . 'logo.svg' ); ?>" alt="<?php esc_attr_e( 'Content Audit', 'ahrefs-seo' ); ?>" class="logo">
		<span class="header-text">/</span>
		<span class="header-text"><?php esc_html_e( 'Settings', 'ahrefs-seo' ); ?></span>
	</div>
	<div class="content-right">
		<div>
			<a class="content-hint-support" href="<?php echo esc_attr( Ahrefs_Seo::get_support_url() ); ?>" target="_blank"><span class="dashicons dashicons-email"></span><?php esc_html_e( 'Support', 'ahrefs-seo' ); ?></a>
		</div>
		<div>
			<a class="content-hint-how" href="https://help.ahrefs.com/en/collections/2253902-wordpress-plugin" target="_blank"> <?php esc_html_e( 'Help', 'ahrefs-seo' ); ?></a>
		</div>
	</div>

</div>

<div class="settings-tabs-and-content">
<div class="settings-tabs">
	<?php
	foreach ( $locals['tabs'] as $tab_slug => $tab_title ) {
		$link = Links::settings( $tab_slug );
		?>
		<a class="submenu <?php if ( $locals['active_tab'] === $tab_slug ) { // phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace ?> active<?php } ?>" href="<?php echo esc_attr( $link ); ?>">
			<?php
			echo esc_html( $tab_title );
			?>
		</a>
		<?php
	}
	?>
</div>
<div class="settings-content">

<h1 class="ahrefs-seo-for-wordpress"><?php echo esc_html( $locals['title'] ); ?></h1>

<?php
if ( isset( $locals['active_tab'] ) && in_array( $locals['active_tab'], [ Ahrefs_Seo_Screen_Settings::TAB_CONTENT, Ahrefs_Seo_Screen_Settings::TAB_ANALYTICS ], true ) ) {
	// Check for compatibility issues.
	if ( ! Ahrefs_Seo_Compatibility::quick_compatibility_check() ) {
		Ahrefs_Seo::get()->get_view()->show_part( 'content-tips/compatibility' );
		// filter errors, do not show same message.
		if ( ! empty( $locals['error'] ) ) {
			Ahrefs_Seo_Compatibility::filter_messages( $locals['error'] );
		}
	}
}
// show messages and error.
if ( ! empty( $locals['updated'] ) ) {
	?>
	<div class="updated notice success is-dismissible"><p><?php esc_html_e( 'Updated.', 'ahrefs-seo' ); ?></p></div>
	<?php
}
if ( ! empty( $locals['error'] ) ) {
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
?>

<?php
// we will close those divs at the footer.
define( 'AHREFS_SEO_FOOTER_ADDITIONAL_DIVS', 2 );
