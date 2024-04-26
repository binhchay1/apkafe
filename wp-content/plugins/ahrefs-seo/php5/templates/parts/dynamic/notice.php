<?php

namespace ahrefs\AhrefsSeo;

$locals  = Ahrefs_Seo_View::get_template_variables();
$id      = isset( $locals['id'] ) ? $locals['id'] : '';
$title   = $locals['title'];
$message = $locals['message'];
$count   = isset( $locals['count'] ) ? $locals['count'] : 1;
$buttons = isset( $locals['buttons'] ) ? $locals['buttons'] : [];
?>
<div class="notice notice-info is-dismissible" id="<?php echo esc_attr( $id ); ?>">
	<div id="ahrefs-notices">
		<p id="<?php echo esc_attr( "message-id-{$id}" ); ?>" data-count="<?php echo esc_attr( "{$count}" ); ?>" class="ahrefs-message">
			<?php
			if ( '' !== $title ) {
				?>
				<b>
				<?php
				echo esc_html( $title );
				?>
	</b>:
				<?php
			}
			echo esc_html( $message );
			?>
			<span class="ahrefs-messages-count
			<?php
			echo esc_attr( 1 === $count ? ' hidden' : '' ); ?>">
	<?php
		echo esc_html( "{$count}" );
	?>
</span>
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
<?php 