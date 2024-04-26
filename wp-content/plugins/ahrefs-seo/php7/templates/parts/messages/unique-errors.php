<?php
/**
 * Unique errors list template
 */

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
$unique = $locals['unique'];

foreach ( $unique as $key => $item ) {
	$title = Ahrefs_Seo_Errors::get_title_for_source( $item['source'] );
	?>
	<p id="<?php echo esc_attr( "message-id-$key" ); ?>" data-count="<?php echo esc_attr( "{$item['count']}" ); ?>" class="ahrefs-message">
		<b><?php echo esc_html( $title ); ?></b>:
		<?php echo esc_html( $item['message'] ); ?>
		<span class="ahrefs-messages-count<?php echo esc_attr( 1 === $item['count'] ? ' hidden' : '' ); ?>"><?php echo esc_html( "{$item['count']}" ); ?></span>
	</p>
	<?php
	if ( 'compatibility' === $item['source'] ) {
		?>
		<p><a href="https://help.ahrefs.com/en/articles/4858501-why-is-my-wordpress-plugin-incompatible-with-the-ahrefs-seo-wordpress-plugin" target="_blank"><?php esc_html_e( 'Why’s this happening?', 'ahrefs-seo' ); ?></a></p>
		<?php
	}
}
