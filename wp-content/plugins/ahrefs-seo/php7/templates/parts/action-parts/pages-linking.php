<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals   = Ahrefs_Seo_View::get_template_variables();
$post_tax = $locals['post_tax'];
$items    = Ahrefs_Seo_Advisor::get()->find_internal_links( $post_tax );

if ( ! empty( $items ) ) {
	/* translators: %d: number of pages */
	$subtitle = sprintf( _n( '%d page linking to this', '%d pages linking to this', count( $items ), 'ahrefs-seo' ), count( $items ) );
	?>
	<div class="more-page-content">
		<div class="details-related-item details-related-header">
			<span><?php echo esc_html( $subtitle ); ?></span>
		</div>
		<?php
		foreach ( $items as $item ) {
			$url_view   = $item['url'];
			$url_edit   = $item['url_edit'];
			$url_inline = $item['url'] ?? $item['url_edit'];
			$title      = $item['title'];
			?>
			<div class="details-related-item"><a target="_blank" href="<?php echo esc_attr( $url_inline ?? '#' ); ?>">
					<?php echo esc_html( $title ); ?>
				</a>
				<div class="details-position">
					<div class="block-row-actions">
						<?php if ( $url_edit ) { ?>
							<a class="row-action-item" href="<?php echo esc_attr( $url_edit ); ?>"><?php esc_html_e( 'Edit', 'ahrefs-seo' ); ?></a>
							<?php
						}
						if ( $url_edit && $url_view ) {
							?>
							|
							<?php
						}
						if ( $url_view ) {
							?>
							<a class="row-action-item" href="<?php echo esc_attr( $url_view ); ?>"><?php esc_html_e( 'View', 'ahrefs-seo' ); ?></a>
							<?php
						}
						?>
					</div>
				</div>
			</div>

		<?php } ?>
	</div>
<?php } ?>
