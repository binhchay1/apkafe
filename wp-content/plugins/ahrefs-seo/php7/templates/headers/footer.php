<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

/**
 * Footer template.
 */

// add additional divs, if header have opened divs.
if ( defined( 'AHREFS_SEO_FOOTER_ADDITIONAL_DIVS' ) ) {
	for ( $i = 0; $i < AHREFS_SEO_FOOTER_ADDITIONAL_DIVS; $i++ ) {
		?></div>
		<?php
	}
}
?>
</div>
