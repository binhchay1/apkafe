<?php

namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
$items  = $locals['items'];
if ( count( $items ) ) {
	?>
	<div>
		<table class="small-values-table">
			<?php
			foreach ( $items as $key => $value ) {
				?>
			<tr>
			<td>
				<?php
				echo esc_html( $key );
				?>
		</td>
			<td>
				<?php
				if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
					?>
				<a href="<?php echo esc_attr( $value ); ?>" target="blank">
									<?php
									echo esc_html( $value );
									?>
			</a>
					<?php
				} else {
					echo esc_html( $value );
				}
				?>
			</td>
			</tr>
				<?php
			}
			?>
		</table>
	</div>
	<?php
}