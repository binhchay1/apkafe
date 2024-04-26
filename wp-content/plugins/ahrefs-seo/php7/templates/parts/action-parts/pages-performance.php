<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();

$additional = Ahrefs_Seo_Data_Content::get()->content_get_noindexes_for_post( $locals['post_tax'] );

if ( isset( $locals['no-keyword'] ) && $locals['no-keyword'] ) {
	?><div class="position-info"><span class="icon-red"></span><?php esc_html_e( 'No target keyword found', 'ahrefs-seo' ); ?></div>
	<?php
} elseif ( isset( $locals['position'] ) ) { // do no show position if no keyword.
	$pos = ! is_null( $locals['position'] ) ? floatval( $locals['position'] ) : Ahrefs_Seo_Data_Content::POSITION_MAX;
	if ( $pos <= 3 ) {
		?>
		<div class="position-info"><span class="icon-green"></span><?php esc_html_e( 'Position in top 3', 'ahrefs-seo' ); ?></div>
		<?php
	} elseif ( $pos <= 20 ) {
		?>
		<div class="position-info"><span class="icon-yellow"></span><?php esc_html_e( 'Position below top 3', 'ahrefs-seo' ); ?></div>
		<?php
	} else {
		?>
		<div class="position-info"><span class="icon-red"></span><?php esc_html_e( 'Position below top 20', 'ahrefs-seo' ); ?></div>
		<?php
	}
}

if ( isset( $locals['unique-keyword'] ) ) {
	if ( $locals['unique-keyword'] ) {
		?>
		<div class="position-info"><span class="icon-green"></span><?php esc_html_e( 'Topic is unique', 'ahrefs-seo' ); ?></div>
		<?php
	} else {
		?>
		<div class="position-info"><span class="icon-yellow"></span><?php esc_html_e( 'Topic is not unique', 'ahrefs-seo' ); ?></div>
		<?php
	}
}

if ( isset( $locals['low-traffic'] ) ) {
	?>
	<div class="position-info"><span class="icon-red"></span><?php esc_html_e( 'Low traffic', 'ahrefs-seo' ); ?></div>
	<?php
}

if ( isset( $locals['backlinks'] ) ) {
	if ( (int) $locals['backlinks'] > 0 ) {
		?>
		<div class="position-info"><span class="icon-green"></span><?php // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen -- no additional space.
			/* translators: %d: number of backlinks */
			echo esc_html( sprintf( _n( '%d backlink was obtained', '%d backlinks were obtained', (int) $locals['backlinks'], 'ahrefs-seo' ), $locals['backlinks'] ) );
		?>
		</div>
		<?php
	} else {
		?>
		<div class="position-info"><span class="icon-yellow"></span><?php esc_html_e( 'No backlinks', 'ahrefs-seo' ); ?></div>
		<?php
	}
}

if ( isset( $locals['decent-traffic'] ) ) {
	?>
	<div class="position-info"><span class="icon-green"></span><?php esc_html_e( 'Decent non-organic traffic', 'ahrefs-seo' ); ?></div>
	<?php
}

if ( $additional['is_noindex'] ) {
	?>
		<div class="position-info"><span class="icon-yellow"></span><?php esc_html_e( 'Noindex', 'ahrefs-seo' ); ?></div>
		<?php
}
if ( $additional['is_noncanonical'] ) {
	?>
		<div class="position-info"><span class="icon-yellow"></span><?php esc_html_e( 'Non-canonical', 'ahrefs-seo' ); ?></div>
		<?php
}
if ( $additional['is_redirected'] ) {
	?>
		<div class="position-info"><span class="icon-yellow"></span><?php esc_html_e( 'Redirect', 'ahrefs-seo' ); ?></div>
		<?php
}
