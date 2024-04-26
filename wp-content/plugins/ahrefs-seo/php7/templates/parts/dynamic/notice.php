<?php
/**
 * Notice template
 */

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals  = Ahrefs_Seo_View::get_template_variables();
$id      = $locals['id'] ?? '';
$title   = $locals['title'];
$message = $locals['message'];
$count   = $locals['count'] ?? 1;
$buttons = $locals['buttons'] ?? [];
?>
<div class="notice notice-info is-dismissible" id="<?php echo esc_attr( $id ); ?>">
	<div id="ahrefs-notices">
		<p id="<?php echo esc_attr( "message-id-$id" ); ?>" data-count="<?php echo esc_attr( "{$count}" ); ?>" class="ahrefs-message">
			<?php
			if ( '' !== $title ) {
				?>
				<b><?php echo esc_html( $title ); ?></b>:
				<?php
			}
			echo esc_html( $message );
			?>
			<span class="ahrefs-messages-count<?php echo esc_attr( 1 === $count ? ' hidden' : '' ); ?>"><?php echo esc_html( "{$count}" ); ?></span>
		</p>
		<?php
		if ( count( $buttons ) ) {
			?>
			<p>
				<?php
				echo wp_kses_post( implode( ' ', $buttons ) );
				?>
			</p>
			<?php
		}
		?>
	</div>
</div>
