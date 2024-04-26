<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();

if ( count( $locals['items'] ) ) {
	?>
	<div class="more-page-content">
		<div class="details-related-item details-related-header">
			<span><?php echo esc_html( $locals['subtitle'] ); ?></span>
			<div class="details-position">
				<span class="details-position-value"><?php esc_html_e( 'Position', 'ahrefs-seo' ); ?></span>
				<div class="block-row-actions"></div>
			</div>
		</div>
		<?php
		/** @var Post_Tax $post_tax */
		foreach ( $locals['items'] as $post_tax ) {
			$url      = $post_tax->get_url();
			$title    = $post_tax->get_title( true );
			$position = Post_Tax_With_Keywords::create_from( $post_tax )->load_from_db()->get_position();
			?>
			<div class="details-related-item">
				<a target="_blank" href="<?php echo esc_attr( $url ); ?>">
					<?php echo esc_html( $title ); ?>
				</a>
				<div class="details-position">
					<span class="details-position-value"><?php Ahrefs_Seo_Table_Content::print_position( $position ); ?></span>
					<div class="block-row-actions">
						<?php if ( $post_tax->user_can_edit() ) { ?>
							<a class="row-action-item" href="<?php echo esc_attr( $post_tax->get_url_edit() ); ?>">Edit</a>
							|
							<?php
						}
						?>
						<a class="row-action-item" href="<?php echo esc_attr( $url ); ?>">View</a>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
<?php } ?>
