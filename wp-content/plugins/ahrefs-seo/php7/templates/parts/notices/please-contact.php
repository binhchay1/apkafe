<?php
/**
 * Show Contact Ahrefs block with messages.
 */

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
$view   = Ahrefs_Seo::get()->get_view();

$messages = $locals['messages'] ?? Ahrefs_Seo_Errors::get_current_messages();

if ( ! empty( $messages ) ) {
	$unique = Ahrefs_Seo_Errors::unique_errors( $messages );
	?>
	<div class="notice notice-error is-dismissible" id="ahrefs_api_messages">
		<div id="ahrefs-messages">
			<?php
			if ( count( $unique ) ) {
				?>
				<span class="message-expanded-title">
				<?php
				printf(
					/* translators: %s: text "contact Ahrefs support" with link */
					esc_html__( 'Oops, seems like there was an error. Please %s to get it resolved.', 'ahrefs-seo' ),
					sprintf(
						'<a href="%s">%s</a>',
						esc_attr( Ahrefs_Seo::get_support_url( true ) ),
						esc_html__( 'contact Ahrefs support', 'ahrefs-seo' )
					)
				);
				?>
			</span>
				<a href="#" class="message-expanded-link"><?php esc_html_e( '(Show more details)', 'ahrefs-seo' ); ?></a>
				<div class="message-expanded-text">
					<?php
					$view->show_part( 'messages/unique-errors', [ 'unique' => $unique ] );
					?>
				</div>
				<?php
			}
			?>
		</div>
	</div>
	<?php
}
