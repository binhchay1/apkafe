<?php

namespace ahrefs\AhrefsSeo;

$locals    = Ahrefs_Seo_View::get_template_variables();
$analytics = Ahrefs_Seo_Analytics::get();
$gsc_list  = $analytics->load_gsc_accounts_list();
$gsc_site  = $analytics->get_data_tokens()->get_gsc_site();
$incorrect = ! $locals['preselect_accounts'] && ! $analytics->is_gsc_account_correct(); // show error only for already detected account.
if ( ! empty( $locals['preselect_accounts'] ) ) {
	/*
	 * Use the WordPress root URL to pre-select the profile to use.
	 * The selection should default to the https version of the site.
	 * For sites that have a subfolder as WordPress root URL, e.g. xyz.com/blog, the root domain should be used to select the Google profiles.
	 */
	$domain = strtolower( Ahrefs_Seo::get_current_domain() );
	if ( empty( $gsc_site ) && ! empty( $domain ) ) {
		$https_found = false;
		foreach ( $gsc_list as $item ) {
			$domain_current = $item['domain'];
			$scheme_current = $item['scheme'];
			$site_current   = $item['site'];
			if ( ! empty( $ua_id_current ) && $domain_current === $domain ) {
				if ( ! $https_found || 'https' === $scheme_current ) {
					$gsc_site    = $site_current;
					$https_found = 'https' === $scheme_current;
				}
			}
		}
	}
}
?>
<div class="new-token-button">
	<label class="label" for="gsc_account">
	<?php
	esc_html_e( 'Google Search Console site:', 'ahrefs-seo' );
	?>
	</label>
	<select class="account
	<?php
	echo esc_attr( $incorrect ? ' incorrect-value' : '' ); ?>" name="gsc_site" id="gsc_account">
		<option value="" 
		<?php
		selected( $gsc_site, '', true );
		?>
		>
<?php
esc_html_e( 'Please select', 'ahrefs-seo' );
?>
</option>
		<?php
		foreach ( $gsc_list as $item ) {
			$gsc_site_current = $item['site'];
			if ( empty( $gsc_site_current ) ) {
				continue;
			}
			$level = ! empty( $item['level'] ) ? " ({$item['level']})" : '';
			$title = "{$item['site']}" . $level;
			// do not allow user to select GSC item with low permissions level (siteUnverifiedUser).
			?>
			<option value="<?php echo esc_attr( $gsc_site_current ); ?>" 
										<?php
										selected( $gsc_site_current, $gsc_site );
										?>
			<?php
			disabled( 'siteUnverifiedUser', isset( $item['level'] ) ? $item['level'] : '' );
			?>
	>
			<?php
			echo esc_html( $title );
			?>
	</option>
			<?php
		}
		?>
	</select>
</div>
<?php 