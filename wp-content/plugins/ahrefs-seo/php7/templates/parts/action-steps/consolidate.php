<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals   = Ahrefs_Seo_View::get_template_variables();
$view     = Ahrefs_Seo::get()->get_view();
$post_tax = $locals['post_tax'];
?>
<li>
	<p><?php esc_html_e( 'Consolidate', 'ahrefs-seo' ); ?></p>
	<p><?php esc_html_e( 'There are multiple articles on your blog targeting the same topic. We suggest consolidating content from all the similar articles into just one well-performing article.', 'ahrefs-seo' ); ?></p>
	<?php
	// Well-performing list.
	$items_well = Ahrefs_Seo_Advisor::get()->find_relevant_top_performing_pages( $post_tax, 3 );
	if ( ! is_null( $items_well ) ) { // has top performing articles.
		/* translators: %d: number of pages */
		$subtitle = sprintf( _n( '%d well-performing page on the same topic', '%d Well-performing pages on the same topic', count( $items_well ), 'ahrefs-seo' ), count( $items_well ) ); // @phpcs:ignore WordPress.WP.I18n.MissingSingularPlaceholder
		$view->show_part(
			'action-parts/pages-list-position',
			[
				'items'    => $items_well,
				'subtitle' => $subtitle,
			]
		);
	}

	// Under-performing list.
	$items = Ahrefs_Seo_Advisor::get()->find_relevant_under_performing_pages( $post_tax, 100 );
	if ( is_null( $items ) ) {
		$items = [];
	}
	array_unshift( $items, $post_tax );
	if ( ! is_null( $items_well ) ) {
		/* translators: %d: number of pages */
		$subtitle = sprintf( _n( '%d under-performing page that can be merged with the well-performing one', '%d under-performing pages that can be merged with the well-performing one', count( $items ), 'ahrefs-seo' ), count( $items ) );
	} else {
		/* translators: %d: number of pages */
		$subtitle = sprintf( _n( '%d page that can be merged into one', '%d pages that can be merged into one', count( $items ), 'ahrefs-seo' ), count( $items ) );
	}
	$view->show_part(
		'action-parts/pages-list-position',
		[
			'items'    => $items,
			'subtitle' => $subtitle,
		]
	);
	?>
	<p class="with-button">
		<a class="link-question" href="https://ahrefs.com/blog/keyword-cannibalization/" target="_blank"><?php esc_html_e( 'How to consolidate pages', 'ahrefs-seo' ); ?></a>
	</p>
</li>
