<?php

namespace ahrefs\AhrefsSeo;

$locals          = Ahrefs_Seo_View::get_template_variables();
$id              = isset( $locals['id'] ) ? $locals['id'] : '';
$title           = $locals['title'];
$message         = $locals['message'];
$count           = isset( $locals['count'] ) ? $locals['count'] : 1;
$not_dismissible = ! empty( $locals['not_dismissible'] );
?>
<div class="notice notice-error 
<?php
echo esc_attr( $not_dismissible ? '' : ' is-dismissible' ); ?>" id="<?php echo esc_attr( $id ); ?>">
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
	</div>
</div>
<?php 