<?php

namespace ahrefs\AhrefsSeo;

$locals  = Ahrefs_Seo_View::get_template_variables();
$id      = isset( $locals['id'] ) ? $locals['id'] : '';
$title   = isset( $locals['title'] ) ? $locals['title'] : '';
$message = isset( $locals['message'] ) ? $locals['message'] : '';
if ( ! empty( $message ) ) {
	?>
	<div class="notice notice-error is-dismissible" id="ahrefs_api_messages">
		<div id="ahrefs-messages">
			<span class="message-expanded-title">
			<?php
			printf(
		/* translators: %s: text "contact Ahrefs support" with link */
				esc_html__( 'Oops, seems like there was an error. Please %s to get it resolved.', 'ahrefs-seo' ),
				sprintf( '<a href="%s">%s</a>', esc_attr( Ahrefs_Seo::get_support_url( true ) ), esc_html__( 'contact Ahrefs support', 'ahrefs-seo' ) )
			);
			?>
			</span>
			<a href="#" class="message-expanded-link">
			<?php
			esc_html_e( '(Show more details)', 'ahrefs-seo' );
			?>
	</a>
			<div class="message-expanded-text">
				<p id="<?php echo esc_attr( "message-id-{$id}" ); ?>" data-count="1" class="ahrefs-message">
					<b>
					<?php
					echo esc_html( $title );
					?>
	</b>:
					<?php
					echo esc_html( $message );
					?>
					<span class="ahrefs-messages-count hidden">1</span>
				</p>
				<?php
				require __DIR__ . '/buttons.php';
				?>
			</div>
		</div>
	</div>
	<?php
}