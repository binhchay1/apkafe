<?php

namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
$error  = isset( $locals['error'] ) ? $locals['error'] : __( 'We are updating the database right now so please check back on this page in a few minutes. Thank you for your patience!', 'ahrefs-seo' );
if ( ! is_null( $locals['header'] ) ) {
	?><p>
	<?php
	esc_html_e( 'Oops, seems like there was an error during database update.', 'ahrefs-seo' );
	?>
	</p>
	<p>
	<?php
	echo wp_kses_post( $locals['header'] );
	?>
	</p>
	<?php
} else {
	if ( $locals['is_error'] ) {
		?>
		<p>
			<?php
			printf(
			/* translators: %s: text "contact Ahrefs support" with link */
				esc_html__( 'Oops, seems like there was an error. Please %s to get it resolved.', 'ahrefs-seo' ),
				sprintf( '<a href="%s">%s</a>', esc_attr( Ahrefs_Seo::get_support_url( true ) ), esc_html__( 'contact Ahrefs support', 'ahrefs-seo' ) )
			);
			?>
		</p>
		<?php
	}
}
?>
<div>
	<textarea style="width: 100%;height: auto;" readonly="readonly">
	<?php
	echo esc_textarea( $error );
	?>
	</textarea>
</div>
<?php
if ( $locals['has_reset_button'] ) {
	$url = add_query_arg(
		[
			'page'        => Ahrefs_Seo::SLUG,
			'reset_error' => wp_create_nonce( 'reset_error' ),
			'r'           => time(),
		],
		admin_url( 'admin.php' )
	);
	?>
	<div>
		<a class="button" id="fatal_reset_button" href="<?php echo esc_attr( $url ); ?>">
																	<?php
																	esc_html_e( 'Retry', 'ahrefs-seo' );
																	?>
	</a>
	</div>
	<?php
}