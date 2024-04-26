<?php
/**
 * Notices template
 */

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
$view   = Ahrefs_Seo::get()->get_view();

$unique = Ahrefs_Seo_Errors::unique_errors( $locals['messages'] );
?>
<div class="notice notice-info is-dismissible" id="ahrefs_api_notices">
	<div id="ahrefs-notices">
		<?php
		if ( count( $unique ) ) {
			$view->show_part( 'messages/unique-errors', [ 'unique' => $unique ] );
		}
		?>
	</div>
</div>
<?php
