<?php

namespace ahrefs\AhrefsSeo;

$locals    = Ahrefs_Seo_View::get_template_variables();
$is_wizard = $locals['is_wizard']; // print Google connection string inside the notice.
if ( ! function_exists( 'print_share_config' ) ) {
	/**
	 * Print "Share my Google configuration" block.
	 *
	 * @param bool $is_wizard Called from Wizard.
	 *
	 * @return void
	 */
	function print_share_config( $is_wizard ) {
		if ( $is_wizard ) {
			$view = Ahrefs_Seo::get()->get_view();
			$view->show_part( 'options/share-config', [ 'is_wizard' => $is_wizard ] );
		}
	}
}
if ( $locals['no_ga'] && $locals['no_gsc'] ) {
	?>
	<!-- no ga & gsc accounts tip -->
	<div class="ahrefs-content-tip tip-notice">
		<div class="caption">
		<?php
		esc_html_e( 'Google Analytics & Search Console accounts were not found', 'ahrefs-seo' );
		?>
	</div>
		<div class="text">
		<?php
		esc_html_e( 'There are no Google Analytics & Search Console accounts connected to the Google profile. Please create GA and GSC accounts for your website or connect another Google profile.', 'ahrefs-seo' );
		?>
			<br>
			<?php
			Ahrefs_Seo::get()->get_view()->learn_more_link( 'https://help.ahrefs.com/en/articles/4666920-how-do-i-connect-google-analytics-search-console-to-the-plugin', __( 'How do I connect Google accounts?', 'ahrefs-seo' ) );
			?>
		</div>
		<?php
		print_share_config( $is_wizard );
		?>
	</div>
	<?php
} elseif ( $locals['no_ga'] ) {
	if ( $locals['ga_has_account'] ) {
		?>
		<!-- no usable ga account tip -->
		<div class="ahrefs-content-tip tip-notice">
			<div class="caption">
			<?php
			esc_html_e( 'Google Analytics account was found but has no details', 'ahrefs-seo' );
			?>
		</div>
			<div class="text">
			<?php
			esc_html_e( 'Google Analytics account does not have any profiles, suitable for using with Analytics API.', 'ahrefs-seo' );
			?>
				<br>
				<?php
				Ahrefs_Seo::get()->get_view()->learn_more_link( 'https://help.ahrefs.com/en/articles/4666920-how-do-i-connect-google-analytics-search-console-to-the-plugin', __( 'How do I connect Google accounts?', 'ahrefs-seo' ) );
				?>
			</div>
			<?php
			print_share_config( $is_wizard );
			?>
		</div>
		<?php
	} else {
		?>
		<!-- no ga account tip -->
		<div class="ahrefs-content-tip tip-notice">
			<div class="caption">
			<?php
			esc_html_e( 'Google Analytics account was not found', 'ahrefs-seo' );
			?>
		</div>
			<div class="text">
			<?php
			esc_html_e( 'There isn’t a Google Analytics account connected to the Google profile. Please create a Google Analytics account for your website or connect another Google profile.', 'ahrefs-seo' );
			?>
				<br>
				<?php
				Ahrefs_Seo::get()->get_view()->learn_more_link( 'https://help.ahrefs.com/en/articles/4666920-how-do-i-connect-google-analytics-search-console-to-the-plugin', __( 'How do I connect Google accounts?', 'ahrefs-seo' ) );
				?>
			</div>
			<?php
			print_share_config( $is_wizard );
			?>
		</div>
		<?php
	}
} else {
	?>
	<!-- no gsc account tip -->
	<div class="ahrefs-content-tip tip-notice">
		<div class="caption">
		<?php
		esc_html_e( 'Google Search Console account was not found', 'ahrefs-seo' );
		?>
	</div>
		<div class="text">
		<?php
		esc_html_e( 'There isn’t a Google Search Console account connected to the Google profile. Please create a Google Search Console account for your website or connect another Google profile.', 'ahrefs-seo' );
		?>
			<br>
			<?php
			Ahrefs_Seo::get()->get_view()->learn_more_link( 'https://help.ahrefs.com/en/articles/4666920-how-do-i-connect-google-analytics-search-console-to-the-plugin', __( 'How do I connect Google accounts?', 'ahrefs-seo' ) );
			?>
		</div>
		<?php
		print_share_config( $is_wizard );
		?>
	</div>
	<?php
}