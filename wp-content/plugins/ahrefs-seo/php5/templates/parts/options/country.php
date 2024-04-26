<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Options\Countries;
$countries = new Countries();
$list      = $countries->get_google_list();
$country   = $countries->get_country();
?>
<div class="block-title">
<?php
esc_html_e( 'Country', 'ahrefs-seo' );
?>
</div>
<div class="block-text">
	<?php
	esc_html_e( 'Choose whether to pull GSC positions for all countries or for a specific one in which you intend to rank.', 'ahrefs-seo' );
	?>
	<div class="country-options">
		<select name="country" class="waiting-units">
			<?php
			foreach ( $list as $code => $name ) {
				?>
				<option value="<?php echo esc_attr( $code ); ?>"
											<?php
											selected( $country, $code );
											?>
	>
				<?php
				echo esc_html( $name );
				?>
	</option>
				<?php
			}
			?>
		</select>
	</div>
</div>
<?php 