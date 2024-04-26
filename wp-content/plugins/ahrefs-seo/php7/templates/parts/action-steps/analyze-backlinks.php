<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Options\Countries;

$locals      = Ahrefs_Seo_View::get_template_variables();
$post_tax    = $locals['post_tax'];
$ref_domains = $locals['ref_domains'];
$keyword     = $locals['keyword'];
// country code that was used during audit, not from the current settings.
$country_code        = ( new Snapshot() )->get_country_code( $post_tax->get_snapshot_id() );
$country_code_ahrefs = Countries::get_country_code_ahrefs( $country_code );

if ( ! function_exists( 'output_refdomains_count' ) ) {
	/**
	 * Print a block with refdomains count string or a script for loading fresh value
	 *
	 * @param int|null $ref_domains Number of ref.domains.
	 * @param Post_Tax $post_tax Current post or term.
	 * @return void
	 */
	function output_refdomains_count( ?int $ref_domains, Post_Tax $post_tax ) : void {
		?>
		<p class="ref-domains-count-wrap">
			<?php
			if ( ! is_null( $ref_domains ) && ( $ref_domains >= 0 ) ) {
				/* translators: %s: number of referring domains */
				echo esc_html( sprintf( _n( '%s ref. domain', '%s ref. domains', $ref_domains, 'ahrefs-seo' ), (int) $ref_domains ) );
			} else { // show just a loader icon.
				?>
				<span class="row-loader loader-transparent inline-loader" id="loader_refdomains"><span class="loader"></span></span>
				<script type="text/javascript">
					console.log('Updating ref.domains...');
					content.refdomains_refresh( '.ref-domains-count-wrap', <?php echo wp_json_encode( (string) $post_tax ); ?> );
				</script>
			<?php } ?>
		</p>
		<?php
	}
}

if ( $keyword ) {
	$url = "https://app.ahrefs.com/keywords-explorer/google/{$country_code_ahrefs}/overview?keyword=" . rawurlencode( $keyword );
	?>
	<li>
		<p><?php esc_html_e( 'Analyze backlinks', 'ahrefs-seo' ); ?></p>
		<p><?php esc_html_e( 'Not ranking well doesn’t automatically mean that there’s a problem with your content – you might not have enough backlinks to compete in the SERP. To check if this is true, compare your content to its competitors in the SERP below. You’ll be taken to Ahrefs Keywords Explorer. Scroll to the SERP overview, then look at the Domains column of the pages that outrank you and see how you compare to those numbers.', 'ahrefs-seo' ); ?></p>
		<?php output_refdomains_count( $ref_domains, $post_tax ); ?>
		<p class="with-button">
			<a class="button link-like-button" href="<?php echo esc_attr( $url ); ?>" target="_blank"><?php esc_html_e( 'Compare to SERP competitors', 'ahrefs-seo' ); ?></a>
			<a class="link-question" href="https://ahrefs.com/blog/how-to-get-backlinks/" target="_blank"><?php esc_html_e( 'How to get more backlinks', 'ahrefs-seo' ); ?></a>
		</p>
	</li>
	<?php
} else {
	?>
	<li>
		<p><?php esc_html_e( 'Analyze backlinks', 'ahrefs-seo' ); ?></p>
		<p><?php esc_html_e( 'Not ranking well doesn’t automatically mean that there’s a problem with your content – you might not have enough backlinks to compete in the SERP. We suggest checking on your competitors in the SERP and seeing how you compare to their numbers.', 'ahrefs-seo' ); ?></p>
		<?php output_refdomains_count( $ref_domains, $post_tax ); ?>
		<p class="with-button">
			<a class="link-question" href="https://ahrefs.com/blog/how-to-get-backlinks/" target="_blank"><?php esc_html_e( 'How to get more backlinks', 'ahrefs-seo' ); ?></a>
		</p>
	</li>
	<?php
}
