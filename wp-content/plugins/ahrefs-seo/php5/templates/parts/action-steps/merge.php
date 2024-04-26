<?php

namespace ahrefs\AhrefsSeo;

$locals   = Ahrefs_Seo_View::get_template_variables();
$view     = Ahrefs_Seo::get()->get_view();
$post_tax = $locals['post_tax'];
?>
<li>
	<p>
	<?php
	esc_html_e( 'Merge', 'ahrefs-seo' );
	?>
	</p>
	<p>
	<?php
	esc_html_e( 'Add 301 redirects from poor performing articles to the republished one.', 'ahrefs-seo' );
	?>
	</p>
	<p>
	<?php
	esc_html_e( 'If you decide to redirect this page, the best practice is to swap out any internal links pointing to the redirect.', 'ahrefs-seo' );
	?>
	</p>
	<?php
	$view->show_part( 'action-parts/pages-linking', [ 'post_tax' => $post_tax ] );
	?>

	<p class="with-button">
		<a class="link-question" href="https://ahrefs.com/blog/301-redirects/" target="_blank">
		<?php
		esc_html_e( 'How to add 301 redirects', 'ahrefs-seo' );
		?>
		</a>
	</p>
</li>
<?php 