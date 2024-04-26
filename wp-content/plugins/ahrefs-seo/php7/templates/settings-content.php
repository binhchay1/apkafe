<?php
/**
 * Content audit settings template.
 *
 * @var array $option
 * @var array $posts_list
 * @var array $pages_list
 * @var array $posts_checked
 * @var array $pages_checked
 */

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Messages\Message;

$locals = Ahrefs_Seo_View::get_template_variables();
$view   = Ahrefs_Seo::get()->get_view();

if ( ! isset( $locals['button_title'] ) ) {
	$locals['button_title'] = __( 'Continue', 'ahrefs-seo' );
}

// both options below may exist only at content settings, not wizard settings.
if ( ! empty( $locals['updated_scope'] ) ) {
	$view->show_part( 'options-tips/scope-updated' );
}
if ( ! empty( $locals['new_cpt'] ) ) {
	$view->show_part( 'options-tips/include-cpt', $locals );
}
?>
<form method="post" action="" class="ahrefs-seo-wizard ahrefs-audit">
	<input type="hidden" name="ahrefs_audit_options" value="1">
	<?php
	if ( isset( $locals['page_nonce'] ) ) {
		wp_nonce_field( $locals['page_nonce'] );
	}
	?>
	<div class="card-item">
		<?php
		$view->show_part( 'options/scope', $locals );
		$view->show_part( 'options/tresholds', $locals );
		$view->show_part( 'options/country', $locals );
		?>
	</div>

	<?php
	$can_save = current_user_can( Ahrefs_Seo::CAP_SETTINGS_AUDIT_SAVE );
	if ( ! $can_save ) {
		Message::edit_not_allowed()->show();
	}
	?>
	<div class="button-wrap">
		<a href="#" class="button button-hero button-primary" id="ahrefs_seo_submit" <?php disabled( ! $can_save ); ?>><?php echo esc_html( $locals['button_title'] ); ?></a>
	</div>
</form>
