<?php

namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
/**
* Call action 'ahrefs_progress' from js code until finish status received.
*/
// current progress.
$progress = Ahrefs_Seo_Data_Wizard::get()->get_progress( true );
$percents = $progress['percents'];
$finish   = $progress['finish'];
?>
<form method="post" action="<?php echo esc_attr( Links::wizard_step() ); ?>" class="ahrefs-seo-wizard ahrefs-audit">
	<input type="hidden" name="ahrefs_audit_skip_wizard" value="1">
	<?php
	if ( isset( $locals['page_nonce'] ) ) {
		wp_nonce_field( $locals['page_nonce'] );
	}
	?>
	<div class="card-item">
		<div class="block-title">
		<?php
		esc_html_e( 'Running your first audit', 'ahrefs-seo' );
		?>
		</div>

		<div class="block-text">
			<?php
			esc_html_e( 'We’re hard at work reviewing all your pages, analyzing your site, and preparing recommendations. Feel free to check back in a few minutes’ time. In the future, this process will run in the background.', 'ahrefs-seo' );
			?>
		</div>

		<div id="progressbar" 
		<?php
		if ( $finish ) { // phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace
			?>
	class="completed"
			<?php
		}
		?>
		>
			<div class="position"
				<?php
				if ( $percents > 0 ) {
					?>
					style="<?php echo esc_attr( 'width:' . $percents . '%' ); ?>"
										<?php
				}
				?>
					data-position="<?php echo esc_attr( $percents > 0 ? $percents : '0' ); ?>"
				>
			</div>
			<div class="progress">
			<?php
			echo esc_html( str_replace( '{0}', number_format( $percents, $percents < 10 ? 2 : 1 ), _x( 'Analyzing: {0}%', 'button title', 'ahrefs-seo' ) ) );
			?>
			</div>
			<div class="progress-completed">
			<?php
			esc_html_e( 'Completed', 'ahrefs-seo' );
			?>
			</div>
		</div>
	</div>

	<div class="button-wrap">
		<a href="#" class="button button-hero button-primary"
			id="ahrefs_seo_submit">
			<?php
			esc_html_e( 'View report', 'ahrefs-seo' );
			?>
			</a>
	</div>
</form>
<?php 